<?php

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Log;

use App\Models\Conversations;
/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

// Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
//     return (int) $user->id === (int) $id;
// });

Broadcast::channel('notifications', function ($user) {
return true;
});
Broadcast::channel('channel-for-everyone', function ($user) {
return true;
});

//a private channel with user id
Broadcast::channel('idverification.{userId}', function ($user, $userId) {
    Log::info($userId);
return (int) $user->id === (int) $userId;
});

Broadcast::channel('messages.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
    });

    Broadcast::channel('messageseen.{userId}', function ($user, $userId) {
        return (int) $user->id === (int) $userId;
        }); 

    
Broadcast::channel('isOnline.{conversationId}', function ($user, $conversationId) {
    
    if($user->role=="artist"){
        $userIsInConversation = Conversations::where("id", $conversationId)->where("participent_id", $user->id)->get();
        //check if userIsInConversation is not empty
        if(!$userIsInConversation->isEmpty()){
            return ['id' => $user->id, 'name' => $user->name];
        }
    }
    else{
        $userIsInConversation = Conversations::where("id", $conversationId)->where("user_id", $user->id)->get();
        //check if userIsInConversation is not empty
        if(!$userIsInConversation->isEmpty()){
            return ['id' => $user->id, 'name' => $user->name];
        }
    }

    return;
  });
