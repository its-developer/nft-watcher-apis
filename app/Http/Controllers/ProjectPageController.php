<?php

namespace App\Http\Controllers;

use App\Models\ProjectPage;
use Illuminate\Http\Request;

class ProjectPageController extends Controller
{
    public function index(Request $request)
    {
        $project_status = ProjectPage::get();
        return response()->json([
            'success' => true,
            'project_status' => $project_status
        ]);
    }

    
}
