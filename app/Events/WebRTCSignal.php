<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WebRTCSignal implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $conferenceId;
    public int $senderId;
    public int $receiverId;
    public string $type;
    public mixed $data;

    public function __construct(
        int $conferenceId,
        int $senderId,
        int $receiverId,
        string $type,
        mixed $data
    ) {
        $this->conferenceId = $conferenceId;
        $this->senderId = $senderId;
        $this->receiverId = $receiverId;
        $this->type = $type;
        $this->data = $data;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel(
                'conference.' . $this->conferenceId
            ),
        ];
    }

    public function broadcastAs(): string
    {
        return 'webrtc.signal';
    }

    public function broadcastWith(): array
    {
        return [
            'sender_id' => $this->senderId,
            'receiver_id' => $this->receiverId,
            'type' => $this->type,
            'data' => $this->data,
        ];
    }
}
