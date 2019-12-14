<?php

namespace App\Http\Controllers;

use App\Helper;
use App\Http\Resources\PostCollection;
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

    public function index(Request $request)
    {
        $toBeValidated = [
            'departure_date' => 'nullable|date',
            'departure'      => 'nullable|string',
            'destination'    => 'nullable|string',
            'type'           => 'nullable|in:1,2'
        ];

        if ($failMessage = Helper::validation($toBeValidated, $request)) {
            return Helper::result(false, $failMessage, [], Response::HTTP_BAD_REQUEST);
        }

        $row = $request->row ?? (new Post())->getPerPage();

        $posts = Post::with('user');

        $posts->when($request->departure_date, function ($query, $departure_date) {
            return $query->where('departure_date', Carbon::parse($departure_date)->toDateString());
        });

        $posts->when($request->departure, function ($query, $departure) {
            $query->where(function ($query) use ($departure) {
                $query->where('departure', 'LIKE', '%'.$departure.'%');
            });
        });

        $posts->when($request->destination, function ($query, $destination) {
            $query->where(function ($query) use ($destination) {
                $query->where('destination', 'LIKE', '%'.$destination.'%');
            });
        });

        $posts->when($request->type, function ($query, $type) {
            $query->where(function ($query) use ($type) {
                $query->where('type', $type);
            });
        });

        $posts->when($request->type == Post::TYPE_PTT, function ($query) {
            $query->where(function ($query) {
                $query->where('description', 'LIKE', '%'.request()->departure.'%')
                    ->orWhere('destination', 'LIKE', '%'.request()->destination.'%')
                    ->orWhere('subject', 'LIKE', '%'.request()->departure.'%')
                    ->orWhere('subject', 'LIKE', '%'.request()->destination.'%')
                    ->orWhere('departure_date', request()->departure_date);
            });
        });

        return new PostCollection($posts->paginate($row)->appends(request()->query->all()));
    }
}
