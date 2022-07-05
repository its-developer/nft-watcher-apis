<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Newsletter;
use Illuminate\Support\Facades\Validator;

class NewslettersController extends Controller
{
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'	=> 'required|unique:nft_watcher_newsletters',
        ]);


        if ($validator->fails()) {
            if ($validator->getMessageBag()->getMessages()['email'] ?? null != null) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->getMessageBag()->getMessages()['email'][0]
                ]);
            }
        }

        Newsletter::create($request->all());

        return response()->json([
            'success' => true,
            'message' => "Newsletter Subscribed Successful!"
        ]);
    }
}
