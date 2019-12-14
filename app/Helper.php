<?php


namespace App;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class Helper
{
    public static function validation(Array $toBeValidated, Request $request)
    {
        $validator = validator::make($request->all(), $toBeValidated);
        if ($validator->fails())
        {
            return $validator->errors()->first();
        }
    }

    public static function result($result, $message, $data, $statusCode)
    {
        return Response::json(['result' => $result, 'message' => $message, 'data' => $data], $statusCode);
    }
}
