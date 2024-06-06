<?php

namespace App\Http\Controllers;

use App\Models\Venues;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class VenuesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //

        // Validate the request
        $validation = Validator::make($request->all(), [
            'name' => 'required',
            'location' => 'required',
            'capacity' => 'required|numeric',
            'price' => 'required|numeric',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'phone'=>'required',
            'email'=>'required|email',
            'map'=>'required'

        ]);

        if ($validation->fails()) {
            return response()->json(['error' => $validation->errors()], 401);
        }

        // Handle file upload
        if ($request->hasFile('image')) {
            // Store the file and get the path
            $imagePath = $request->file('image')->store('venueimage', 'public');
            // Store the path relative to the storage directory

            Log::info($imagePath);

            // Create a new venue
            $venue = new Venues();
            $venue->name = $request->name;
            $venue->location = $request->location;
            $venue->capacity = $request->capacity;
            $venue->price = $request->price;
            $venue->image = $imagePath;
            $venue->save();

            return response()->json(['message' => 'Venue created successfully'], 200);
        }



    }

    /**
     * Display the specified resource.
     */
    public function show(Venues $venues)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Venues $venues)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Venues $venues)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Venues $venues)
    {
        //
    }
}