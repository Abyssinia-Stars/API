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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class VerifyIdEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

 /**
     * The message to be broadcast.
     *
     * @var string
     */
    public $message;

    /**
     * The ID related to the event.
     *
     * @var int
     */
    public $id;

    /**
     * Create a new event instance.
     *
     * @param string $message
     * @param int $id
     * @return void
     */
    public function __construct($message, $id)
    {
        $this->message = $message;
        $this->id = $id;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array
     */
    public function broadcastOn(): array
    {
        
        Log::info($this->id);
        
        
        return [
            new PrivateChannel('idverification.'.$this->id),
        ];
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith(): array
    {
        return [
            'message' => $this->message,
            'title' => 'New Id Verification',
            'created_at' => now()->format('Y-m-d H:i:s'),
        ];
    }


}
