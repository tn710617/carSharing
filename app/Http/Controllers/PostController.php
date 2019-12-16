<?php

namespace App\Http\Controllers;

use App\Helper;
use App\Http\Resources\PostCollection;
use App\Post;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use GuzzleHttp\Client;

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

        $departure_date = $request->departure_date ? Carbon::parse($request->departure_date)->toDateString() : null;

        $posts->when($departure_date, function ($query, $departure_date) {
            $query->where('departure_date', $departure_date);
        })->when($request->type == null, function ($query) {
            $query->when(request()->departure, function ($query) {
                $query->where(function ($query) {
                    $query->where('departure', 'LIKE', '%'.request()->departure.'%')
                        ->orWhere('description', 'LIKE', '%'.request()->departure.'%')
                        ->orWhere('subject', 'LIKE', '%'.request()->departure.'%');
                });
            })->when(request()->destination, function ($query) {
                $query->where(function ($query) {
                    $query->where('destination', 'LIKE', '%'.request()->destination.'%')
                        ->orWhere('description', 'LIKE', '%'.request()->destination.'%')
                        ->orWhere('subject', 'LIKE', '%'.request()->destination.'%');
                });
            });
        })->when(request()->type == Post::TYPE_INTERNAL,
            function ($query) {
                $query->where('type', request()->type)
                    ->when(request()->departure, function ($query) {
                        $query->where('departure', 'LIKE', '%'.request()->departure.'%');
                    })->when(request()->destination, function ($query) {
                        $query->where('destination', 'LIKE', '%'.request()->destination.'%');
                    });
            })->when(request()->type == Post::TYPE_PTT,
            function ($query) {
                $query->where('type', request()->type)
                    ->when(request()->departure, function ($query) {
                        $query->where(function ($query) {
                            $query->where('description', 'LIKE', '%'.request()->departure.'%')
                                ->orWhere('subject', 'LIKE', '%'.request()->departure.'%');
                        });
                    })->when(request()->destination, function ($query) {
                        $query->where(function ($query) {
                            $query->where('description', 'LIKE', '%'.request()->destination.'%')
                                ->orWhere('subject', 'LIKE', '%'.request()->destination.'%');
                        });
                    });
            });

        return new PostCollection($posts->latest('created_at')->paginate($row)->appends(request()->query->all()));
    }

    public function getPTTInfo()
    {
        $client = new Client();
        $resFromPTT = $client->request('GET', 'http://104.199.202.121/ptt-hiking');
        $pttCollection = collect(json_decode($resFromPTT->getBody()->getContents()));
        $pttCollection = $pttCollection->map(function ($obj) {
            return (array) $obj;
        });
        $pttCollection->map(function ($arr) {
            if (Post::where('ptt_id', $arr['ptt_id'])->count() == 0) {
                $arr['departure_date'] = Carbon::parse($arr['departure_date']);
                $arr['type'] = Post::TYPE_PTT;
                Post::create($arr);
            }
        });

    }
}
