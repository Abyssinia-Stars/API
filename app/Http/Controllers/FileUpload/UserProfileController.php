<?php

namespace App\Http\Controllers\FileUpload;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\Models\Image; 

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
}
