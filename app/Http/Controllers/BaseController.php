<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BaseController extends Controller
{
    protected function success($data,$message='Success')
    {
        return response()->json([
            'status'=>true,
            'message'=>$message,
            'data'=>$data
        ]);
    }

    protected function error($message,$code=400)
    {
        return response()->json([
            'status'=>false,
            'message'=>$message
        ],$code);
    }
}

