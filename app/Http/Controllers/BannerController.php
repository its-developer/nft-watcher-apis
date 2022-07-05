<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\NftWatcherAds;

class BannerController extends Controller
{
    public function index(Request $request)
    {
        $banner = NftWatcherAds::where('name', 'banner')->first();

        return response()->json([
            'success' => true,
            'banner' => $banner
        ]);
    }
}
