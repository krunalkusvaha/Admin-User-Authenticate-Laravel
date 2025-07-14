<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    Public function dashboard(Request $request)
    {
        return view('admin.dashboard');
    }
}
