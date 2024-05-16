<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Messages;
use App\Models\Conversations;
use Illuminate\Support\Facades\Log;

class SendMessage implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        private Conversations $conversation,
        private Messages $message
    )
    {
        
        //
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        Log::info($this->conversation);
        // Log::info($this->message);
       
        if($this->conversation->user_id === $this->message->user_id){
            return [
                new PrivateChannel('messages.' . $this->conversation->participent_id),
            ];
        }
        if($this->conversation->participent_id === $this->message->user_id){
            return [
                new PrivateChannel('messages.' . $this->conversation->user_id),
            ];
        }
           
        // return [
        //     new PrivateChannel('messages.' . $this->conversation->participent_id),
           
        // ];
    }

    public function broadcastWith(): array
    {
        return [
            'message' => $this->message
        ];
    }
}
