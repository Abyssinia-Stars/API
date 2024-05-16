<?php

namespace App\Http\Controllers;

use App\Models\Conversations;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;


use App\Models\User;
use App\Models\Messages;
use App\Models\ArtistProfile;
use Carbon\Carbon;


class ConversationsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($id)
    {

        $user = Auth::user();

    
        $conversations = [];
        $userWithDetail = [];
        $getTheLastMessage = [];


        if($id == $user->id){
      
            
            return response()->json(['message' => 'You cannot chat with yourself'], 400);
        }

        
        if($user->role === "artist"){

            $conversations = Conversations::where('participent_id', $id)
            ->get();                    
    
            
                    foreach ($conversations as $conversation) {
                        $user = User::where('id', $conversation->user_id)->first();
                        $getTheLastMessage = Messages::where('conversation_id', $conversation->id)->orderBy('created_at', 'desc')->first();
                        $userWithDetail[] = [
                            'conversation' => $conversation,
                            'detail' => $user,
                            'last_message' => $getTheLastMessage
                        ];
                    }

          
        }
        else{
            $conversations = Conversations::where('user_id', $user->id)
            ->get();

            $duplicateConversation = Conversations::where('user_id', $user->id)
            ->where('participent_id', $id)
            ->get();

      

            if(
                count($duplicateConversation) === 0
            ){

                $artist = ArtistProfile::where('user_id', $id)->first();
                if($artist->manager_id){
                    $id = $artist->manager_id;
                }

                
                $conversation = Conversations::create(
                    [
                        'user_id' => $user->id,
                        'participent_id' => $id
                    
                    ]
                );

    
            }
        
        foreach ($conversations as $conversation) {
            $user = User::where('id', $conversation->participent_id)->first();
            $getTheLastMessage = Messages::where('conversation_id', $conversation->id)->orderBy('created_at', 'desc')->first();
            $userWithDetail[] = [
                'conversation' => $conversation,
                'detail' => $user,
                'last_message' => $getTheLastMessage,
                'messages' => null
            ];
        }
    }
    return response()->json(['conversations' => $userWithDetail], 200);

    }



    /**
     * Show the form for creating a new resource.
     */
    public function show()
    {
        //
        //get all the conversations for the user 
        $user = Auth::user();
        $conversations = [];
        $userProfile = [];
        $userWithDetail = [];
        $getTheLastMessage = [];
        
        //:TODO 
        
        if($user->role === 'artist' || $user->role==="manager"){
            if($user->role === "artist"){

                $artistProfile = ArtistProfile::where('user_id', $user->id)->first();
                if($artistProfile->manager_id){
                    return response()->json(['message' => 'You are not allowed to view this page'], 400);
                }
            }
            $conversations = Conversations::where('participent_id', $user->id)->get();
            foreach ($conversations as $conversation) {
               
                $userProfile = User::where('id', $conversation->user_id)->first(); 
                $getTheLastMessage = Messages::where('conversation_id', $conversation->id)->orderBy('created_at', 'desc')->first();
                // $unRead = Messages::where('conversation_id', $conversation->id)->where('seen', 0)->count();
                    $unRead = 0;


                 //check if the unread message is from the user or the participent
                //if the user is the participent then the unread message is from the user
                if($user->id === $conversation->participent_id){
                    $unRead = Messages::where('conversation_id', $conversation->id)->where('seen', 0)->where('user_id', $conversation->user_id)->count();
                

                }

                //if the user is the user then the unread message is from the participent
        
            
           
                $userWithDetail[] = [
                    'conversation' => $conversation,
                    'detail' => $userProfile,
                    'last_message' => $getTheLastMessage,
                    'unread' => $unRead,
                ];
            }
            return response()->json(['conversations' => $userWithDetail], 200);
    

        }
        else{

            $conversations = Conversations::where('user_id', $user->id)->get();
            foreach ($conversations as $conversation) {
              $userProfile = User::where('id', $conversation->participent_id)->first();
                $getTheLastMessage = Messages::where('conversation_id', $conversation->id)->orderBy('created_at', 'desc')->first();
                // $unRead = Messages::where('conversation_id', $conversation->id)->where('seen', 0)->count();
                $unRead = 0;
                //check if the unread message is from the user or the participent
                //if the user is the participent then the unread message is from the user
                if($user->id === $conversation->user_id){
                    $unRead = Messages::where('conversation_id', $conversation->id)->where('seen', 0)->where('user_id', $conversation->participent_id)->count();
                

                }

                //check if conversation created time is less than 1minutes
                $userWithDetail[] = [
                    'conversation' => $conversation,
                    'detail' => $userProfile,
                    'last_message' => $getTheLastMessage,
                    'unread' => $unRead,
                ];
            }
            return response()->json(['conversations' => $userWithDetail], 200);
    
        }
       
    }

    /**
     * Store a newly created resource in storage.
     */
    public function getConversationData(Request $request,$participent_id)
    {
        //
        $user_id = Auth::user()->id;
        $user = User::where('id', $participent_id)->first();


        try {
            $artistProfile = ArtistProfile::where('user_id', $user_id)->first();
            if($artistProfile->manager_id){
                return response()->json(['message' => 'You are not allowed to view this page'], 400);
            }
         
            $doesExist = Conversations::where('user_id', $user_id)->where('participent_id', $participent_id)->first();
            
            if (!$doesExist) {
                $doesUserExist = User::where('id', $participent_id)->first();
                if (!$doesUserExist) {
                    return response()->json(['message' => "User does not exist"], 404);
                }
                
                $conversation = Conversations::create([
                    'user_id' => $user_id,
                    'participent_id' => $participent_id,
                ]);

                
                
    
                return response()->json(['conversation' => $conversation,"detail" => $user], 200);
            }
            $messages = Messages::where('conversation_id', $doesExist->id)->get();
            $getTheLastMessage = Messages::where('conversation_id', $doesExist->id)->orderBy('created_at', 'desc')->first();
            
            return response()->json(['conversation' => $doesExist,"detail" => $user,"messages" => $messages, "last_message" => $getTheLastMessage], 200);


        } catch (\Throwable $th) {
            //throw $th;
            return response()->json(['conversation' => $th->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Conversations $conversations)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Conversations $conversations)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //

        $conversation = Conversations::find($id);
        
        $conversation->delete();
        return response()->json(['message' => 'Conversation deleted successfully'], 200);

    }
}
