<?php

use Illuminate\Support\Facades\Broadcast;

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
return (int) $user->id === (int) $userId;
});

Broadcast::channel('messages.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
    });

    
// Broadcast::channel('idverification', function ($user){
//     return true;
// });
// Broadcast::channel('artistIdVerification', function ($user){
//     return true;
// });