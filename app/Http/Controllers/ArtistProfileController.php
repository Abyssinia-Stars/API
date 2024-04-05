<?php

namespace App\Http\Controllers;

use App\Models\ArtistProfile;
use App\Models\User;
use App\Models\Notification;
use App\Models\Review;
use App\Models\Favorites;

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
    public function index(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'q' => 'string|nullable',
            'catagory' => 'array', // Changed 'string[]' to 'array'
            'limit' => 'integer|min:1|max:100',
            'page' => 'integer|min:1',
        ]);

        if ($validation->fails()) {
            return response()->json(['error' => $validation->errors()], 400);
        }

        $limit = $request->input('limit', 10);
        $page = $request->input('page', 1);
        $catagory = $request->input('catagory');
        $q = $request->input('q', '');

        $query = ArtistProfile::join('users', 'artist_profiles.user_id', '=', 'users.id')
            ->where('users.role', 'artist')
            ->where('users.is_verified', 'verified')
            ->where('users.is_active', true)
            ->where('users.name', 'like', "%$q%");

        if ($catagory) {
            $query->whereJsonContains('category', $catagory);
        }

        // Specify the columns you want to retrieve from both tables
        $artists = $query->select(
            'artist_profiles.id',
            'artist_profiles.user_id',
            'artist_profiles.bio',
            'artist_profiles.category',
            'users.name as name',
            'users.email',
            'users.profile_picture'
        )->paginate($limit, ['*'], 'page', $page);

        return $artists;
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
            'attachments' => 'array|min:1|max:5',
            'attachments.*' => 'file|max:20000', // 200 MB
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
    public function show($id)
    {
        $artist = ArtistProfile::where('id', $id)
            ->with('user')
            ->whereHas('user', function ($query) {
                $query->where('role', 'artist')
                    ->where('is_verified', 'verified')
                    ->where('is_active', true);
            })
            ->first();

        if (!$artist) {
            return response()->json(['error' => 'Artist not found'], 404);
        }

        return response()->json([
            'id' => $artist->id,
            'user_id' => $artist->user_id,
            'bio' => $artist->bio,
            'user' => [
                'id' => $artist->user->id,
                'name' => $artist->user->name,
                'email' => $artist->user->email,
                // Add other user columns as needed
            ]
        ]);
    }
    public function calculateAverageRating($review){
       
        if(count($review) == 0){
            return 0;
        }
        return $review->avg('rating');

    
    }
    
    public function getArtistProfile($id){

       
        $artist = ArtistProfile::where('user_id', $id)->first();
        $userProfile  = User::where('id', $id)->first();
        $reviews= Review::where('artist_id', $id)->get();
        $averageRating = $this->calculateAverageRating($reviews);

   
    

        if (!$artist) {
            return response()->json([
                'id' => $userProfile->id,
                'user_id' => $userProfile->id,
                'bio' => null,
                'name' => $userProfile->name,
                'email' => $userProfile->email,
                'category' => null,
                'attchments' => null,
                'youtube_links' =>null,
                'profile_picture' => $userProfile->profile_picture,
                'reviews' => $reviews,
                'average_rating' => $averageRating,
            ]);
        }

        return response()->json([
            'id' => $artist->id  ,
            'user_id' => $artist->user_id,
            'bio' => $artist->bio,
            'name' => $artist->user->name,
            'email' => $artist->user->email,
            'category' => $artist->category,
            'attchments' => $artist->attachments,
            'youtube_links' => $artist->youtube_links,
            'profile_picture' => $userProfile->profile_picture,
            'reviews' => $reviews,
            'average_rating' => $averageRating,

                // Add other user columns as needed
        ]);

    }

    public function getArtistProfileWithAuth($id,$auth){

        // return response()->json([
        //     'id' => $id,
        //     'auth' => $auth
        // ]);
        $isFavorite = false;
        $artist = ArtistProfile::where('user_id', $id)->first();
        $userProfile  = User::where('id', $id)->first();
        $reviews= Review::where('artist_id', $id)->get();
        $averageRating = $this->calculateAverageRating($reviews);

        if($auth){

            $isFavoriteVal = Favorites::where('user_id', auth()->user()->id)->where('artist_id', $id)->first();
            if($isFavoriteVal){
                $isFavorite = true;
            }else{
                $isFavorite = false;
            }
        }

    

        if (!$artist) {
            return response()->json([
                'id' => $userProfile->id,
                'user_id' => $userProfile->id,
                'bio' => null,
                'name' => $userProfile->name,
                'email' => $userProfile->email,
                'category' => null,
                'attchments' => null,
                'youtube_links' =>null,
                'profile_picture' => $userProfile->profile_picture,
                'reviews' => $reviews,
                'average_rating' => $averageRating,
                'is_favorite' => $isFavorite
            ]);
        }

        return response()->json([
            'id' => $artist->id  ,
            'user_id' => $artist->user_id,
            'bio' => $artist->bio,
            'name' => $artist->user->name,
            'email' => $artist->user->email,
            'category' => $artist->category,
            'attchments' => $artist->attachments,
            'youtube_links' => $artist->youtube_links,
            'profile_picture' => $userProfile->profile_picture,
            'reviews' => $reviews,
            'average_rating' => $averageRating,
            'is_favorite' => $isFavorite

                // Add other user columns as needed
        ]);

    }

    public function sendRequest($userId)
    {

        if(auth()->user()->role != 'artist'){
            return response()->json([
                'message' => 'User is not an artist'
            ], 400);
        }

        $user = User::where('id', $userId)->first();
        if($user->role != 'manager'){
            return response()->json([
                'message' => 'User is not an manager'
            ], 400);
        }

        try{
            $notificationAlreadyExists = Notification::where('user_id', $user->id)
                ->where('source_id', auth()->user()->id)
                ->where('notification_type', 'artist_manager')
                ->where('status', 'pending')
                ->first();

            if($notificationAlreadyExists){
                return response()->json([
                    'message' => 'Request already sent'
                ], 400);
            }

            $notification = Notification::create([
                'user_id' => $user->id,
                'notification_type' => 'artist_manager',
                'source_id' => auth()->user()->id,
                'message' => 'Artist wants to work with you',
                'status' => 'pending'
            ]);
            
            return response()->json([
                'message' => 'Request sent successfully',
                'notification' => $notification
            ]);
        }
        catch(\Exception $e){
            return response()->json([
                'message' => 'An error occurred',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function handleResponse(Request $request, $notificationId){

        if(auth()->user()->role != 'artist'){
            return response()->json([
                'message' => 'User is not an artist'
            ], 400);
        }

        $validate = Validator::make($request->all(), [
            'status' => 'required|in:accepted,rejected'
        ]);

        $notification = Notification::find($notificationId);

        if(!$notification){
            return response()->json([
                'message' => 'Notification not found'
            ], 404);
        }

        if($notification->status != 'pending'){
            return response()->json([
                'message' => 'Notification has already been responded to'
            ], 400);
        }

        $notification->status = $request->status;
        $notification->save();

        return response()->json([
            'message' => 'Response sent successfully',
            'notification' => $notification
        ]);

    }

    public function getNotifications(){
        $notifications = Notification::where('user_id', auth()->user()->id)->get();
        return response()->json($notifications);
    }
    public function getReviews(){

        $reviews = Review::where('artist_id', auth()->user()->id)->get();
        return response()->json([
            'reviews' => $reviews
        ]);
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
