<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SectionsContent;


class SectionsController extends Controller
{
    public function index(Request $request, $key)
    {
        $section = SectionsContent::where('key', $key)->first();
        return response()->json($section);
    }
}
