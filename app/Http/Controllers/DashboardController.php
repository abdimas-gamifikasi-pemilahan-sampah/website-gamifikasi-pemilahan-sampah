<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $path = $request->path();

        if (view()->exists($path)) {
            return view($path);
        }

        $hiddenPath = '_unused_template.' . str_replace('/', '.', $path);
        if (view()->exists($hiddenPath)) {
            return view($hiddenPath);
        }

        abort(404);
    }
}
