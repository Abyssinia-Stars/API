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


        $songs = ArtistProfile::with('users:id,name,profile_picture,is_active')
        ->get(['']); // Select columns from the songs table






        $songsData = $songs->map(function ($song) {

            return [
                'id' => $song->id,
                'artist_id' => $song->artist_id,
                'artist_name' => $song->artist->name,
                'title' => $song->title,
                'lyrics' => $song->lyrics,
                'scale_id' => $song->scale_id,
                'link' => $song->link,

                'scale_name' => $song->scale->name,
                'language_name' => $song->language->name
                // Safely access the album title
            ];
        });

        // Return the response as JSON
        return response()->json($songsData);



        
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

        $out = new \Symfony\Component\Console\Output\ConsoleOutput();


        $validation = Validator::make($request->all(), [
            'profile_picture' => 'image',
            'bio' => 'string|max:255',
            'category' => 'required|array',
            'youtube_links' => 'required|array',
            'attachments' => 'required|array|min:1|max:5',
            'attachments.*' => 'file|max:20000', // 200 MB
        ]);

        if ($validation->fails()) {


            return response()->json(['error' => $validation->errors()], 400);
        }


        $out->writeln("i am kkkk" . json_encode($request->all()));





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
            $artistProfile = ArtistProfile::create(
                [
                    'user_id' => $user->id,

                    'bio' => $validatedData['bio'],
                    'category' => $validatedData['category'],
                    'youtube_links' => $validatedData['youtube_links'],
                    'attachments' => $attachmentPaths
                ]
            );

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