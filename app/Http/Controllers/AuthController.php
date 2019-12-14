<?php

namespace App\Http\Controllers;

use App\Helper;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AuthController extends Controller
{

    public function login()
    {
        $credentials = request(['email', 'password']);

        if (!$token = auth('api')->attempt($credentials)) {
            return Helper::result(false, 'Wrong credentials', [], Response::HTTP_UNAUTHORIZED);
        }

        return Helper::result(true, '', [['token' => $token]], 200);
    }

    public function logout()
    {
        auth('api')->logout();

        return Helper::result(true, '', [], 200);
    }
}
