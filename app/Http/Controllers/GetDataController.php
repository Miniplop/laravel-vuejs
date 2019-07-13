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
            return $response;
        } else {
            return response()->json(json_decode('{}'));
        }
    }
}
