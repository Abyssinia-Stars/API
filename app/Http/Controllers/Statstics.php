<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Work;
use Illuminate\Http\Request;

class Statstics extends Controller
{
    //


    public function index()
    {

        $total_artists = User::where('is_active', 1)->where('role', 'artist')->count();
        $total_users = User::where('is_active', 1)
        ->whereIn('role', ['artist', 'customer'])
        ->count();

        $total_jobs=Work::where('status','completed')->count();

        // Placeholder values for total_orders and total_revenue
        $total_orders = 5000; // This should be replaced with the actual query
        $total_revenue = 1000000; // This should be replaced with the actual query

        $data = [
            'total_artists' => $total_artists,
            'total_users' => $total_users,
            'total_jobs' => $total_jobs,
        ];

        return response()->json($data);
    }
}