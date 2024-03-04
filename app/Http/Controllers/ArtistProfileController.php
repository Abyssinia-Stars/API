<?php

namespace App\Http\Controllers;

use App\Models\ArtistProfile;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;

class ArtistProfileController extends Controller
{


        public function index(){

            return response()->json(['message' => 'Artist Profile Controller Index']);
        }


        public function store( Request $request){



        $validation = Validator::make($request->all(), [
            'bio'=> 'max:255',

            'user_id' => 'required',



        ]);


        if ($validation->fails()) {
            return response()->json(['error' => $validation->errors()], 400);
        }



        $user = ArtistProfile::create(
            [
                'bio' => $request->bio,
                'user_id' => $request->user_id,
                'catagory' => $request->catagory,
                'attachments' => $request->attachments
            ]
        );


        return response()->json(['user' => $user, 'message' => 'Artist Profile Created Successfully']);



            ;
        }


        public function show($id){

            $user = ArtistProfile::find($id);

            if($user){
                return response()->json(['user' => $user]);
            }else{
                return response()->json(['message' => 'No Artist Profile Found']);
            }
        }


        public function  update(Request $request){


            $validation = Validator::make($request->all(), [
                'bio'=> 'max:255',

                'user_id' => 'required',

            ]);

            if ($validation->fails()) {
                return response()->json(['error' => $validation->errors()], 400);
            }

            $user = ArtistProfile::find($request->id);

            if($user){
                $user->bio = $request->bio;
                $user->user_id = $request->user_id;
                $user->catagory = $request->catagory;
                $user->attachments = $request->attachments;
                $user->save();

                return response()->json(['user' => $user, 'message' => 'Artist Profile Updated Successfully']);

            }else{

                return response()->json(['message' => 'No Artist Profile Found']);
            }
            


        }

}