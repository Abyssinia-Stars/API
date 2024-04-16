<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Favorites;
use App\Models\Review;
use App\Models\ArtistProfile;


use Illuminate\Support\Facades\Validator;

class CustomerController extends Controller
{
    //
    public function getRandomAritsts(){
        $artists = User::where('role', 'artist')->inRandomOrder()->limit(10)->get();
        $artistsWithRating = [];

        foreach($artists as $artist){
           
            $averageRating = $this->calculateAverageRating($artist);
        
                $artistsWithRating[] = [
                    'artist' => $artist,
                    'rating' => $averageRating
                ];
            }
        return response()->json([
            'artists' => $artistsWithRating
        ]);
        
    }

    public function getPopularArtistsByRating(){

        $artists = User::where('role', 'artist')->get();
        $artistProfiles = ArtistProfile::whereIn('user_id', $artists->pluck('id'))->get();
        $artistsWithRating = [];
        foreach($artists as $artist){
           
            $averageRating = $this->calculateAverageRating($artist);
            // if($averageRating > 0){


                if($artistProfiles->firstWhere('user_id', $artist->id) == null){
                    continue;
                }

                $artistsWithRating[] = [
                    'artist' => $artist,

                    'rating' => $averageRating,
                    'profile' => $artistProfiles->firstWhere('user_id', $artist->id),
                ];
            // }
        }

        usort($artistsWithRating, function($a, $b){
  
            return $b['rating'] <=> $a['rating'];
        });
 
        
        return response()->json([

            'artists' => $artistsWithRating,
            
        ]);




    }
    public function calculateAverageRating($artist){
        $ratings = Review::where('artist_id', $artist->id)->get();
        if(count($ratings) == 0){
            return 0;
        }

        $totalRating = 0;
        $totalReviews = Review::where('artist_id', $artist->id)->count();
        foreach($ratings as $rate){
            $totalRating += $rate->rating;
        }
        return $totalRating/$totalReviews;
    }
    public function calculateAverageRatings($review){
       
        if(count($review) == 0){
            return 0;
        }
        return $review->avg('rating');

    
    }
public function getArtistByParams(Request $request){

    $validate = Validator::make($request->all(), [
        'q' => 'string|nullable',
        'category' => 'array',
        'limit' => 'integer|min:1|max:100',
        'page' => 'integer|min:1'
    ]);

    if($validate->fails()){
        return response()->json([
            'message' => 'Bad Request',
            'errors' => $validate->errors()
        ], 400);
    }

    $limit = $request->input('limit', 10);
    $page = $request->input('page', 1);
    $categories = $request->input('category',[]);
    $q = $request->input('q', '');



    $users = User::where('role', 'artist')
    ->where('is_verified', 'verified')
    ->where('is_active', true)
    ->where(function ($query) use ($q) {
        $query->where('name', 'like', "%$q%")
            ->orWhere('email', 'like', "%$q%");
    })
    ->get();

  

$artistProfiles = ArtistProfile::where(function ($query) use ($categories) {
    
    if(in_array("all", $categories) || in_array("All", $categories)){
        return;
    }
    $query->whereJsonContains('category', $categories);

    
    })
    ->whereIn('user_id', $users->pluck('id')) 
    ->get();

// $reviewProfiles = Review::where('artist_id', $users->pluck('id'))->get();
$averageRatings = [];
 


$results = [];
foreach ($users as $user) {
    $profile = $artistProfiles->firstWhere('user_id', $user->id);
    // $ratings = $reviewProfiles->firstWhere('artist_id', $user->id);
    $averageRating = $this->calculateAverageRating($user);
    if(!$profile){
        continue;
    }
    $results[] = [
        'user' => $user,
        'profile' => $profile,
        'rating' => $averageRating
    ];
}


$results = collect($results)->forPage($page, $limit)->values();
    return response()->json([
        'artists' => $results,
        'total' => count($results),
        'previousPage' => $page > 1,
        'nextPage' => $results->count() > $page + 1
    ]);

}

    public function addArtistToFavorites($userId){

        $artist = User::where('id', $userId)->where('role', 'artist')->first();
        if(!$artist){
            return response()->json([
                'message' => 'user not found or not an artist'
            ], 404);
        }

        $alreadyAdded = Favorites::where('user_id', auth()->id())->where('artist_id', $artist->id)->first();
        if($alreadyAdded){
            return response()->json([
                'message' => 'Artist already added to favorites'
            ], 400);
        }

        try {

            $favorite = Favorites::create([
                'user_id' => auth()->user()->id,
                'artist_id' => $artist->id
            ]);
    
            return response()->json([
                'message' => 'Artist added to favorites',
                'favorite' => $favorite
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'An error occurred',
                'error' => $th->getMessage()
            ], 500);
            //throw $th;
        }

    }

    public function getFavorites(){

        $favorites = Favorites::where('user_id', auth()->user()->id)->get();
        $favoritesWithName = [];
        foreach($favorites as $favorite){
            $artist = User::find($favorite->artist_id);
            $userProfile = ArtistProfile::where('user_id', $artist->id)->first();
            $artist->profile = $userProfile;
            $favoritesWithName[] = $artist;
        }
        return response()->json([
            'favorites' => $favoritesWithName
        ]);

    }

    public function removeArtistFromFavorites($userId){


        $favorite = Favorites::where('user_id', auth()->user()->id)->where('artist_id', $userId)->first();
        if(!$favorite){
            return response()->json([
                'message' => 'Artist not found in favorites'
            ], 404);
        }

        try {
            $favorite->delete();
       
            return response()->json([
                'message' => 'Artist removed from favorites'
            ]);
        } catch (\Throwable $th) {
            
            return response()->json([
                'message' => 'An error occurred',
                'error' => $th->getMessage()
            ], 500);
        }

    }

    public function addReview(Request $request, $userId){
        
        $validate = Validator::make($request->all(), [
            'rating' => 'required|numeric|min:1|max:5',
            'review' => 'required|string',
            'description' => 'string|nullable'
        ]);
        
        if($validate->fails()){
            return response()->json([
                'message' => 'Bad Request',
                'errors' => $validate->errors()
            ], 400);
        }   
    $artist = User::where('id', $userId)->where('role', 'artist')->first();
    
    if(!$artist){
        return response()->json([
            'message' => 'user not found or not an artist'
        ], 404);}


        $reviewAlreadyExists = Review::where('user_id', auth()->id())->where('artist_id', $artist->id)->first();
        if($reviewAlreadyExists){
            return response()->json([
                'message' => 'Review already exists'
            ], 400);
        }
        try{

            $review = Review::create([
                'user_id' => auth()->id(),
                'artist_id' => $artist->id,
                'rating' => $request->rating,
                'review' => $request->review,
                'description' => $request->description
            ]);

            return response()->json([
                'message' => 'Review added successfully',
                'review' => $review
            ]);
        }catch(\Exception $e){
            return response()->json([
                'message' => 'An error occurred',
                'error' => $e->getMessage()
            ], 500);
        }
        

    }

    public function getReviews(){

        $reviews = Review::where('user_id', auth()->user()->id)->get();
        return response()->json([
            'reviews' => $reviews
        ]);
    }

    public function removeReview($reviewId){

        $review = Review::find($reviewId);
        if(!$review){
            return response()->json([
                'message' => 'Review not found'
            ], 404);
        }


        try {
            $review->delete();
            return response()->json([
                'message' => 'Review removed successfully'
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'An error occurred',
                'error' => $th->getMessage()
            ], 500);
        }

    }



}
