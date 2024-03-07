<?php

namespace App\Http\Controllers;

use App\Models\ArtistProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Laravel\Prompts\Output\ConsoleOutput;

use function Laravel\Prompts\error;

class ArtistProfileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {


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

        $validation = Validator::make($request->all(), [
            'profile_picture' => 'image',
            'bio' => 'string|max:255',
            'category' => 'required|array',
            'youtube_links' => 'required|array',
            'attachments' => 'required|array|min:1|max:5',
            'attachments.*' => 'file|max:200000', // 200 MB
        ]);

        if ($validation->fails()) {
            return response()->json(['error' => $validation->errors()], 400);
        }




        $validatedData = $request->all();

        // Upload the cover picture
        $profilePicturePath = Storage::url($request->file('profile_picture')->store('public/profile_pictures'));

        // Upload the attachments
        $attachmentPaths = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $attachment) {
                $attachmentPath = Storage::url($attachment->store('public/attachments'));
                $attachmentPaths[] = $attachmentPath;
            }
        }

        try {
            $user = Auth::user();

            // Create the ArtistProfile with the validated data
            $artistProfile = ArtistProfile::create([
                'user_id' => $user->id,
                'bio' => $validatedData['bio'],
                'category' => $validatedData['category'],
                'youtube_links' => $validatedData['youtube_links'],
                'attachments' => $attachmentPaths
            ]);

            // Update the user's profile picture if provided in the request
            if (isset($validatedData['profile_picture'])) {
                $user->profile_picture = $profilePicturePath;
                $user->save();
                // return response()->json(['message' => 'Profile picture updated successfully']);
            }

            // Optionally, associate the user with the artist profile here
            // For example, $artistProfile->user_id = $user->id;
            // $artistProfile->save();
            return response()->json(['message' => 'Artist profile created successfully', 'artist_profile' => $artistProfile]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error creating artist profile: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ArtistProfile $artistProfile)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ArtistProfile $artistProfile)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ArtistProfile $artistProfile)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ArtistProfile $artistProfile)
    {
        //
    }
}