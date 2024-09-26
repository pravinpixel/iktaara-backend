<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function responseSuccess($message, $data = null){

        if(isset($data) && !empty($data)){
            return response()->json([
                "status" => "success",
                "data" => $data,
                "message" => $message
            ]);
        }else{
            return response()->json([
                "status" => "success",
                "message" => $message
            ]);
        }
    }

    public function responseError($message){
       
        return response()->json([
            "status" => "failed",
            "message" => $message
        ]);
    }
}
