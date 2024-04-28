<?php

namespace App\Http\Controllers;

use App\Models\Messages;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Events\SendMessage;
use App\Events\SendSeen;

use App\Models\Conversations;
use App\Models\User;



class MessagesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($conversationId)
    {
        //
        $id = Auth::user()->id;
        try {
            //code...
            $messages = Messages::where('conversation_id', $conversationId)
            ->get();
            return response()->json(['messages' => $messages]);
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json(['message' =>
            $th->getMessage(),200]);

        }
      
        

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
        //
        Log::info($request->all());
        $user_id = Auth::user()->id;
        $validator = Validator::make($request->all(), [
            'conversation_id' => 'required',
            'user_id' => 'required',
            'message' => 'required',
        ]);


        if ($validator->fails()) {
            return response()->json([
                'message' => 'Bad Request',
                'errors' => $validator->errors()
            ], 400);
        }


        try {
            //code...
            $message = new Messages([
                'conversation_id' => $request->conversation_id,
                'user_id' => $request->user_id,
                'message' => $request->message,
                'seen' => false
            ]);

            $conversation = Conversations::where('id', $request->conversation_id
        

            )->first();

            $user  = User::where('id', $request->user_id)->first();

            if($user->role === "artist"){
                $conversation = new Conversations([
                    'user_id' => $conversation->user_id,
                    'participent_id' => $request->user_id
                ]);
    
            }


            else{
                $conversation = new Conversations([
                    'user_id' => $request->user_id,
                    'participent_id' => $conversation->participent_id
                ]);
            }

        

            $message->save();

            // return response()->json(['message' => $request->user_id], 200);
    
            broadcast(new SendMessage($conversation,$message));

            return response()->json(['message' => $message], 200);
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json(['message' =>
            $th->getMessage()

        ], 500);
        }
    }

    public function update($messageid)
    {
        //
  

        try {

            $message = Messages::where('id',$messageid)->first()->makeHidden(['created_at','updated_at','deleted_at']);
            $message->seen = true;

            $conversation = Conversations::where('id', $message->conversation_id)->first();

    
            $message->save();


            broadcast(new SendSeen($conversation,$message));

    

            return response()->json([ 'message' => $message],200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error updating Message Details: ' . $e->getMessage()], 500);
        }
    }
}
