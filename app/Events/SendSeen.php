<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

use App\Models\Messages;
use App\Models\Conversations;
use Illuminate\Support\Facades\Log;


class SendSeen implements ShouldBroadcastNow
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
        // Log::info($this->conversation);
        // Log::info($this->message);

        if($this->conversation->participent_id === $this->message->user_id){
            return [
                new PrivateChannel('messageseen.' . $this->conversation->participent_id),
            ];
        }
        
        
        return [
            new PrivateChannel('messageseen.' . $this->conversation->user_id),
        ];
    }

    public function broadcastWith(): array
    {
        return [
         
            'message' => $this->message,
        ];
    }
}
