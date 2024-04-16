<?php

namespace App\Http\Controllers;

use App\Models\Plans;
use Illuminate\Support\Facades\Auth;

class PlansController extends Controller
{
    public function index()
    {
        return Plans::all();
    }
}
