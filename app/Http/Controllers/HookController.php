<?php

namespace App\Http\Controllers;

use App\Http\Services\AirHackApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class HookController extends Controller
{
    function date_sort($task1, $task2)
    {
        return strtotime($task1["dueTime"]) - strtotime($task2["dueTime"]);
    }

    public function incomingTasks(Request $request)
    {

        $result = $request->all();

        $tasks = $result["tasks"];
        usort($tasks, array($this, "date_sort"));

        $currentTaskId = 0; // Start with the first task

        $taskersCount = $result["taskersCount"];
        $taskersCurrentTask = array_fill(0, $taskersCount, null);

        $tasks[0]['assignee_id'] = 0;
        $taskersCurrentTask[0] = $tasks[$currentTaskId];

        foreach ($tasks as $taskIndex => $task) {
            $nonAssignedTasker = null;
            $canExecuteTaskers = array();
            if ($currentTaskId !== $taskIndex) {
                foreach ($taskersCurrentTask as $tasker => $currentTaskerTask) {
                    if ($currentTaskerTask == null) {
                        $nonAssignedTasker = $tasker;
                    } else if ($this->isExecutable(
                        $currentTaskerTask,
                        $task
                    )) {
                        array_push($canExecuteTaskers, $tasker);
                    }
                }
                $selectedTasker = null;

                if (count($canExecuteTaskers) == 0 && null == $nonAssignedTasker) {
                    continue;
                }

                if (count($canExecuteTaskers) == 0) {
                    $selectedTasker = $nonAssignedTasker;
                } else {
                    $worstGain = null;
                    foreach ($canExecuteTaskers as $tasker) {
                        $currentTaskerTask = $tasks[$tasker];
                        $distance = $this->distance($currentTaskerTask['lat'], $currentTaskerTask['lng'], $task['lat'], $task['lng']);
                        $gain = $distance - $this->computeTaskerMinDist($tasks, $currentTaskerTask, $task["id"]);
                        if ($worstGain == null || $gain < $worstGain) {
                            $worstGain = $gain;
                            $selectedTasker = $tasker;
                        }
                    }
                }
                $tasks[$taskIndex]['assignee_id'] = $selectedTasker;
                $taskersCurrentTask[$selectedTasker] = $task;
            }
            $currentTaskId = $taskIndex;
        }

        $result["tasks"] = $tasks;
        file_put_contents("database.json", json_encode($result));
        $apiService = app(AirHackApiService::class);
        $response = $apiService->postResult($result);
        return response()->json($response);
    }

    public function health()
    {
        return response()->json(['success' => true]);
    }

    private function isExecutable($currentTask, $taskToCompare)
    {
        $time2 = ($this->distance($currentTask['lat'], $currentTask['lng'], $taskToCompare['lat'], $taskToCompare['lng']) / 10);
        $timeToGo = sprintf('%02d:%02d', (int) $time2, fmod($time2, 1) * 60);

        $secs = strtotime($timeToGo) - strtotime("00:00:00");
        $result = date("H:i:s", strtotime($currentTask["dueTime"] . '+ 30 minutes') + $secs);
        return (new \DateTime($result) < new \DateTime($taskToCompare['dueTime']));
    }

    private function distance($lat1, $lon1, $lat2, $lon2)
    {
        $radius = 6371;
        $lat = deg2rad($lat1 - $lat2);
        $lng = deg2rad($lon1 - $lon2);
        $a = sin($lat / 2) * sin($lat / 2) + cos(deg2rad($lat2)) * cos(deg2rad($lat1)) * sin($lng / 2) * sin($lng / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $d = $radius * $c;

        return $d;
    }

    private function computeTaskerMinDist($tasks, $taskerFutureTask, $currentTaskId)
    {
        $minDist = null;
        foreach ($tasks as $task) {
            if ($task["id"] != $currentTaskId) {
                if ($this->isExecutable($taskerFutureTask, $task)) {
                    $distance = $this->distance($taskerFutureTask['lat'], $taskerFutureTask['lng'], $task['lat'], $task['lng']);
                    if (!$minDist || $minDist > $distance) {
                        $minDist  = $distance;
                    }
                }
            }
        }
        return $minDist;
    }
}
