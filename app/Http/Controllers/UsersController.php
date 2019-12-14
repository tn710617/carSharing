<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helper;
use App\User;

class UsersController extends Controller
{

    public function store(Request $request)
    {
        $toBeValidated = [
            'email'    => 'required|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed|max:255'
        ];
        if ($failMessage = Helper::validation($toBeValidated, $request)) {
            return Helper::result(false, $failMessage, [], '400');
        }

        User::forceCreate([
            'email'    => $request->email,
            'password' => bcrypt($request->password),
        ]);

        return Helper::result(true, '', [], '200');
    }
}
