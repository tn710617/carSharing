<?php

namespace App\Http\Controllers;

use App\Helper;
use App\Post;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PostController extends Controller
{

    public function store(Request $request)
    {
        $toBeValidated = [
            'subject'        => 'required|max:20',
            'seat'           => 'required|int',
            'departure_date' => 'required|date',
            'departure'      => 'required|string',
            'destination'    => 'required|string',
            'description'    => 'required|string',
        ];

        if ($failMessage = Helper::validation($toBeValidated, $request)) {
            return Helper::result(false, $failMessage, [], Response::HTTP_BAD_REQUEST);
        }

        $toBeInserted = ['user_id' => auth()->user()->id];

        $toBeInserted = array_merge($toBeInserted, $request->toArray());

        $toBeInserted['departure_date'] = Carbon::parse($toBeInserted['departure_date']);

        $post = Post::create($toBeInserted)->refresh();

        return Helper::result(true, '', [new \App\Http\Resources\Post($post)], 200);
    }
}
