<?php

namespace App\Http\Controllers;

use App\Models\Conversations;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\User;
use App\Models\Messages;


class ConversationsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($id)
    {

        //
        $user = User::where('id', $id)->first();
        // return response()->json(['user' => $user], 200);
        $conversations = [];
        $userWithDetail = [];
        $getTheLastMessage = [];
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
            $conversations = Conversations::where('user_id', $id)
            ->get();
        
        foreach ($conversations as $conversation) {
            $user = User::where('id', $conversation->participent_id)->first();
            $getTheLastMessage = Messages::where('conversation_id', $conversation->id)->orderBy('created_at', 'desc')->first();
            $userWithDetail[] = [
                'conversation' => $conversation,
                'detail' => $user,
                'last_message' => $getTheLastMessage
            ];
        }
    }
    return response()->json(['conversations' => $userWithDetail], 200);

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
    public function getConversationData(Request $request,$participent_id)
    {
        //
        $user_id = Auth::user()->id;
        $user = User::where('id', $participent_id)->first();


        try {
         
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
    public function show(Conversations $conversations)
    {
        //
    }

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
    public function destroy(Conversations $conversations)
    {
        //
    }
}
