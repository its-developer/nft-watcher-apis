<?php

namespace App\Http\Controllers;

use App\Models\NftWatcherDrop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class DropsController extends Controller
{
    public function index(Request $request)
    {
        $drops = NftWatcherDrop::orderBy('launched_date', 'ASC');
        if ($request->has('search')) {
            if (strlen($request->get('search')) > 0) {
                $drops->where('title', 'LIKE', '%' . $request->get('search') . '%');
            }
        }
        $drops = $drops->get();
        return response()->json($drops);
    }

    public function show(Request $request, $name)
    {
        $drops = NftWatcherDrop::where('title', $name)->first();
        return response()->json($drops);
    }

    public function rankedByDates(Request $request)
    {
        $drops = NftWatcherDrop::orderBy('launched_date', 'ASC');
        if ($request->has('search')) {
            if (strlen($request->get('search')) > 0) {
                $drops->where('title', 'LIKE', '%' . $request->get('search') . '%');
            }
        }
        $drops = $drops->get();
        $dateRankedDrops = array();
        foreach ($drops as $drop) {
            if ($drop->launched_date) {
                $updatedLaunchedDate = date('Y-m-d', strtotime($drop->launched_date . ' + 2 days'));
                if ($updatedLaunchedDate >= date('Y-m-d')) {
                    array_push($dateRankedDrops, $drop);
                }
            }
        }
        return response()->json($dateRankedDrops);
        $drops = NftWatcherDrop::where('launched_date', '>=', date('Y-m-d'))->get();
        return response()->json($drops);
    }

    public function rankedByVotes(Request $request)
    {

        $drops = NftWatcherDrop::orderBy('votes', 'desc');
        if ($request->has('search')) {
            if (strlen($request->get('search')) > 0) {
                $drops->where('title', 'LIKE', '%' . $request->get('search') . '%');
            }
        }
        $drops = $drops->get();
        $voteRandkedDrops = array();
        foreach ($drops as $drop) {
            if ($drop->launched_date) {
                if ($drop->votes >= 100) {
                    array_push($voteRandkedDrops, $drop);
                }
            }
        }
        $voteRandkedDrops = collect($voteRandkedDrops);
        $voteRandkedDrops = $voteRandkedDrops->map(function ($vote) {
            $vote->votes = intval($vote->votes);
            return $vote;
        });
        $voteRandkedDrops = $voteRandkedDrops->sortByDesc('votes')->values()->toArray();
        return response()->json($voteRandkedDrops);
    }

    public function newlyDrops(Request $request)
    {
        $drops = NftWatcherDrop::orderBy('launched_date', 'ASC');
        if ($request->has('search')) {
            if (strlen($request->get('search')) > 0) {
                $drops->where('title', 'LIKE', '%' . $request->get('search') . '%');
            }
        }

        $drops = $drops->get();
        $newlyDrops = array();
        foreach ($drops as $drop) {
            if ($drop->launched_date) {
                if ($drop->votes < 100) {
                    array_push($newlyDrops, $drop);
                }
            }
        }
        $newlyDrops = collect($newlyDrops);
        $newlyDrops = $newlyDrops->map(function ($vote) {
            $vote->votes = intval($vote->votes);
            return $vote;
        });
        $newlyDrops = $newlyDrops->sortBy('votes')->values()->toArray();
        return response()->json($newlyDrops);
    }

    public function completedDrops(Request $request)
    {
        $drops = NftWatcherDrop::orderBy('launched_date', 'DESC');
        if ($request->has('search')) {
            if (strlen($request->get('search')) > 0) {
                $drops->where('title', 'LIKE', '%' . $request->get('search') . '%');
            }
        }
        $drops = $drops->get();
        $completedDrops = array();
        foreach ($drops as $drop) {
            $launchedDate = date('Y-m-d', strtotime($drop->launched_date . ' + 2 days'));
            if ($launchedDate < date('Y-m-d')) {
                array_push($completedDrops, $drop);
            }
        }
        return response()->json($completedDrops);
    }

    public function getFeatured(Request $request)
    {
        $drops = NftWatcherDrop::where('is_featured', true)->orderBy('created_at', 'DESC');
        if ($request->has('search')) {
            if (strlen($request->get('search')) > 0) {
                $drops->where('title', 'LIKE', '%' . $request->get('search') . '%');
            }
        }
        $drops = $drops->get();
        return response()->json($drops);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,webp,jpg,gif,svg|max:2048',
            'title' => 'required|unique:nft_watcher_drops',
        ]);

        if (str_contains($request->title, "-")) {
            return response()->json([
                'success' => true,
                'message' => 'Invalid Operation (' . $request->title . ') used in Drop\'s Title.',
            ]);
        }

        if ($validator->fails()) {
            if ($validator->getMessageBag()->getMessages()['image'] ?? null != null) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->getMessageBag()->getMessages()['image'][0],
                ]);
            }

            if ($validator->getMessageBag()->getMessages()['title'] != null) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->getMessageBag()->getMessages()['title'][0],
                ]);
            }
        }

        $files = $request->file('image');
        $folderName = '/uploaded-images/';
        $destinationPath = public_path("$folderName");
        // Upload Orginal Image
        $image = date('YmdHis') . "." . $files->getClientOriginalExtension();
        $files->move($destinationPath, $image);

        $newDrop = $request->all();
        $newDrop['image'] = $folderName . $image;
        $newDrop['base_url_for_get_image'] = url('');

        $newDrop['votes'] = 0;
        $newDrop['reaction_one'] = 0;
        $newDrop['reaction_two'] = 0;
        $newDrop['reaction_three'] = 0;
        $newDrop['reaction_four'] = 0;
        $newDrop['reaction_five'] = 0;
        $newDrop['reaction_six'] = 0;

        NftWatcherDrop::create($newDrop);
        return response()->json([
            'success' => true,
            'message' => "Thanks For Your Submission! Get 100 Votes To Be Officially Listed!",
        ]);
    }

    public function vote(Request $request)
    {
        $drop = NftWatcherDrop::where('_id', $request->drop_id)->first();
        if ($drop) {
            $drop->update(['votes' => $drop->votes + 1]);
        }

        return response()->json([
            'success' => true,
            'message' => "Vote Added Successful!",
            'votes' => $drop->votes,
        ]);
    }

    public function reactionVote(Request $request)
    {
        $drop = NftWatcherDrop::where('_id', $request->drop_id)->first();
        $drop->update([$request->reaction_name => $drop[$request->reaction_name] + 1]);

        return response()->json([
            'success' => true,
            'message' => "Reaction Added Successful!",
            $request->reaction_name => $drop[$request->reaction_name],
        ]);
    }

}
