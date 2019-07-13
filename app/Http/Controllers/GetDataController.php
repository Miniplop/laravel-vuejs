<?php

namespace App\Http\Controllers;

use App\Http\Services\AirHackApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use function GuzzleHttp\json_encode;

class GetDataController extends Controller
{
    public function getData(Request $request)
    {
        if (file_exists("database.json")) {
            $response = file_get_contents("database.json");

            $data = json_decode($response, true);

            $taskerMap = [];

            foreach ($data['tasks'] as $task) {
                $taskerId = $task['assignee_id'];
                if (!array_key_exists($taskerId, $taskerMap)) {
                    $taskerMap[$taskerId] = [
                        'tasker_id' => $taskerId,
                        'task_number' => 1,
                        'first_task' => $task['dueTime'],
                        'last_task' => $task['dueTime'],
                        'working_time' => 30,
                    ];
                } else {
                    $taskerMap[$taskerId]['task_number'] ++;
                    $taskerMap[$taskerId]['last_task'] = $task['dueTime'];
                    $taskerMap[$taskerId]['working_time'] += 30;
                }
            }

            $data['plannings'] = array_values($taskerMap);

            return $data;
        } else {
            return response()->json(json_decode('{}'));
        }
    }
}
