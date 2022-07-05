<?php

namespace App\Http\Controllers;

use App\Models\NftWatcherProject;
use Illuminate\Http\Request;

class ProjectsController extends Controller
{
    public function store(Request $request)
    {
        NftWatcherProject::create($request->all());

        return response()->json([
            'success' => true,
            'message' => "Your Request Has Been Submitted. You Can Expect An Email From Us Within 12 Hours.",
        ]);
    }
}
