<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    public $message;
    public $data;

    public function __construct($message, array $data = [])
    {
        if (is_string($message)) {
            $this->message = $message;
            $this->data = array_merge(['message' => $message], $data);
        } else if (is_array($message)) {
            $this->data = $message;
            $this->message = $message['message'] ?? 'New message';
        } else {
            $this->message = 'New message';
            $this->data = [];
        }
    }

    // Broadcast on public 'chat' channel
    public function broadcastOn(): Channel
    {
        return new Channel('chat');
    }

    // The event's broadcast name (this is what the client will listen for)
    public function broadcastAs(): string
    {
        return 'message';
    }

    // The data to broadcast with the event
    public function broadcastWith(): array
    {
        return array_merge([
            'message' => $this->message,
            'time' => now()->toDateTimeString()
        ], $this->data);
    }
}
