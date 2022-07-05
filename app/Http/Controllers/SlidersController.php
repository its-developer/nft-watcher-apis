<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\NftWatcherAds;


class SlidersController extends Controller
{
    public function index(Request $request)
    {
        $ads = NftWatcherAds::where('name', 'slider')->get();
        return response()->json($ads);
    }
}
