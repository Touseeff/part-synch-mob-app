<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function adminDashboard()
    {
        return response()->json([
            'message' => 'Welcome to the Admin Dashboard',
        ], 200);
    }

    public function userDashboard()
    {
        return response()->json([
            'message' => 'Welcome to the User Dashboard',
        ], 200);
    }
}
