<?php

namespace App\Http\Controllers;

use App\Models\EventBlogs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class EventBlogsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //

        //  $events = EventBlogs::all();
        return response()->json(['Message' => 'Hello'], 200);
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
        // Validate the request
        $validation = Validator::make($request->all(), [
            'title' => 'required',
            'description' => 'required',
            'event_date' => 'required|date',
            'price' => 'required|numeric',
            'location' => 'required',
            'organizer_name' => 'required',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($validation->fails()) {
            return response()->json(['error' => $validation->errors()], 401);
        }

        // Handle file upload
        if ($request->hasFile('image')) {
            // Store the file and get the path
            $imagePath = $request->file('image')->store('eventimage', 'public');
            // Store the path relative to the storage directory

            Log::info($imagePath);


            $request->merge(['image' => $imagePath]);
        }

        // Create the event
        $event = EventBlogs::create([
            'title' => $request->title,
            'description' => $request->description,
            'event_date' => $request->event_date,
            'price' => $request->price,
            'location' => $request->location,
            'organizer_name' => $request->organizer_name,
            'image' => $imagePath ?? null,
        ]);

        return response()->json($event, 201);
    }


    /**
     * Display the specified resource.
     */
    public function show(EventBlogs $eventBlogs, $id, Request $request)
    {
        //

        $event = EventBlogs::find($id);

        return response()->json($event);


    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(EventBlogs $eventBlogs)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // Retrieve the event by ID
        $event = EventBlogs::find($id);

        if (!$event) {
            return response()->json(['error' => 'Event not found'], 404);
        }

        // Log request method and headers
        Log::info('Request method: ' . $request->method());
        Log::info('Request headers:', $request->headers->all());
        Log::info('Request data:', $request->all());

        // Validate the request
        $validation = Validator::make($request->all(), [
            'title' => 'required',
            'description' => 'required',
            'event_date' => 'required|date',
            'price' => 'required|numeric',
            'location' => 'required',
            'organizer_name' => 'required',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($validation->fails()) {
            Log::info('Validation errors:', $validation->errors()->all());
            return response()->json(['error' => $validation->errors()], 401);
        }

        // Handle file upload
        if ($request->hasFile('image')) {
            // Store the file and get the path
            $imagePath = $request->file('image')->store('eventimage', 'public');

            // Merge the image path into the request
            $request->merge(['image' => $imagePath]);
        }

        // Update the event
        $event->title = $request->input('title');
        $event->description = $request->input('description');
        $event->event_date = $request->input('event_date');
        $event->price = $request->input('price');
        $event->location = $request->input('location');
        $event->organizer_name = $request->input('organizer_name');
        if ($request->has('image')) {
            $event->image = $request->input('image');
        }
        $event->save();

        return response()->json(['message' => 'Event updated successfully', 'data' => $event], 200);
    }



    /**

     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //

        $event = EventBlogs::find($id);

        if (!$event) {
            return response()->json(['error' => 'Event not found'], 404);
        }

        $event->delete();

        return response()->json(['message' => 'Event deleted successfully'], 200);


    }
}