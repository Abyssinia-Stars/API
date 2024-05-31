<?php

namespace App\Http\Controllers\FileUpload;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

use Illuminate\Http\Request;

use App\Models\Image; 
use App\Models\User;
use App\Models\ArtistProfile;
use App\Models\Offer;


class UserProfileController extends Controller
{
    //
    public function store(Request $request)
    {
        // $request->validate([
        //     'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // Adjust the validation rules as needed
        // ]);

        // Handle the image upload
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time().'.'.$image->getClientOriginalExtension();
            $image->storeAs('images', $imageName);
            
            $imageModel = new Image();
            $imageModel->path = 'images/' . $imageName; // Assuming you're storing images in the 'images' directory
            $imageModel->save();// Store the image in the 'images' directory
            return response()->json(['message' => 'Image uploaded successfully']);
        }

        return response()->json(['message' => 'No image was uploaded']);


        // You can also return the image path or any other response as needed
    }



    public function update(Request $request)
    {
     
       
        
        $validateData = Validator::make($request->all(), [
            'name' => "string|max:255",
            'password' => "string|max:255",
            'old_password' => "string|max:255",
            "confirm_password" => "string|max:255",
            "backup_email" => "email|max:255",
            'attachments' => 'array|min:1|max:5',
            'attachments.*' => 'file|max:20000',
            'image' => 'image|mimes:jpeg,png,jpg,gif|max:2048', // Adjust the validation rules as needed
            'location' => 'string|max:255',
            "gender" => "string|max:255",
            'price_rate' => 'string|max:255',
            "bio" => "string|max:255",
        ]);

        if ($validateData->fails()) {
            return response()->json(['error' => $validateData->errors()], 400);
        }
        

        $id = Auth::user()->id;
        // return response()->json(['message' => $request->backup_email],200);
    
        //add attachments path to the array 




        $attachmentPaths = [];
        if ($request->hasFile('attachments')) {
        
            $artistProfile = ArtistProfile::where('user_id', $id)->first();

        
          
            if($artistProfile->is_subscribed == false){
                
                if(count($artistProfile->attachments) > 5){
                    return response()->json(['message' => 'You can only upload a maximum of 5 attachments'],401);
                }
               
            }
            foreach ($request->file('attachments') as $attachment) {
                $attachmentPath = Storage::url($attachment->store('public/attachments'));
                $attachmentPaths[] = $attachmentPath;
            }

            
            $artistProfile->attachments = array_merge($artistProfile->attachments , $attachmentPaths);

          
    
    
          
            $artistProfile->save();
        }

        // Handle the image upload
        if ($request->hasFile('image')) {
            $profilePicturePath = Storage::url($request->file('image')->store('public/profile_pictures'));
            $user = User::find($id);
            $user->profile_picture = $profilePicturePath;
            $user->save();
         
        }

        if($request->has('name')){
            $user = User::find($id);
            $user->name = $request->name;
            $user->save();
        }

        if($request->has('location')){
            $artistProfile = ArtistProfile::where('user_id', $id)->first();
            $artistProfile->location = $request->location;
            $artistProfile->save();
        }
        if($request->has('bio')){
            $artistProfile = ArtistProfile::where('user_id', $id)->first();
            $artistProfile->bio = $request->bio;
            $artistProfile->save();
        }

        if($request->has('gender')){
            $artistProfile = ArtistProfile::where('user_id', $id)->first();
            $artistProfile->gender = $request->gender;
            $artistProfile->save();
        }
        if($request->has('price_rate')){
            $artistProfile = ArtistProfile::where('user_id', $id)->first();
            $artistProfile->price_rate = $request->price_rate;
            $artistProfile->save();
        }

        if($request->has('password')){
            $user = User::find($id);
            if(Hash::check($request->old_password, $user->password)){
                $user->password = Hash::make($request->password);
                if($request->has('confirm_password')){
                    if($request->password !== $request->confirm_password){
                        return response()->json(['message' => 'Passwords do not match'],400);
                    }
                    
                }
                $user->save();


            }
            else{
                return response()->json(['message' => 'Old password is incorrect'], 400);
            }
            
        }

        if($request->has('backup_email')){
            $user = User::find($id);
            $user->backup_email = $request->backup_email;
            $user->save();
        }

        return response()->json(['message' => 'Profile updated successfully'],200);
}

        public function destroy(){
            $id = Auth::user()->id;
            $user = User::find($id);
            $areAllOffersCompleted = true;

            $offers = [];

            if($user->role === "artist"){
                $offers = Offer::where("artist_id", $id)->get();
                
               
            }

            if($user->role === "customer"){
                $offers = Offer::where("client_id", $id)->get();

            }
            if($user->role === "manager"){
                $areAllOffersCompleted = true;
                $artistProfile = ArtistProfile::where('manager_id', $id)->get();
    
                
 
                foreach ($artistProfile as $artist) {
                    $offers = Offer::where("artist_id", $artist->user_id)->get();
                    foreach($offers as $offer){
                         if($offer->status == "accepted" || $offer->status == "pending"){
                             $areAllOffersCompleted = false;
                                 break;
                     } }
                }
                // return $areAllOffersCompleted;
                if($areAllOffersCompleted){
                    $user->is_deleted = true;
                    $user->save();
        
              
                    $profilePicture = $user->profile_picture;
                    $getTheFile = explode('/', $profilePicture);
                    $profilePicture = end($getTheFile);
                   
                    if($profilePicture != null){
                        Storage::delete('public/profile_pictures/'.$profilePicture);
                        $user->profile_picture = null;
                    }
                    
                    return response()->json(["message"=> "success"], 200);
                    
        
                }
                else{
                    return response()->json(["message"=> "you have unfinished offers"], 400);
                }
    }

                foreach ($offers as $offer) {
                 
                   if($offer->status !== "completed"){
                    $areAllOffersCompleted = false;
                    break;
                   }
                }

               
                if($areAllOffersCompleted){
                    $user->is_deleted = true;
                    $user->save();
        
              
                    $profilePicture = $user->profile_picture;
                    $getTheFile = explode('/', $profilePicture);
                    $profilePicture = end($getTheFile);
                   
                    if($profilePicture != null){
                        Storage::delete('public/profile_pictures/'.$profilePicture);
                        $user->profile_picture = null;
                    }

                    
                    if($user->role === "artist"){
                    try{
                        $attachments = ArtistProfile::where('user_id', $id)->first()->attachments; 
                        if($attachments == null){
                            return response()->json(['message' => 'Profile deleted successfully'],200);
                        }
                        $attachments = explode(',', $attachments);
            
                        foreach($attachments as $attachment){
                            Storage::delete($attachment);
                        }
        
                    }catch(\Exception $e){
                        return response()->json(['message' => 'Profile deleted successfully'],200);
                    }}

                    return response()->json(['message' => 'Profile deleted successfully'],200);
        
                }
                else{
                    return response()->json(["message"=> "you have unfinished offers"], 400);
                }
        



    

            return response()->json(['message' => 'Profile deleted successfully'],200);
        }

}
