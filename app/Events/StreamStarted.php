<?php
namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StreamStarted implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $conferenceId;
    public int $userId;

    public function __construct(int $conferenceId, int $userId)
    {
        $this->conferenceId = $conferenceId;
        $this->userId = $userId;
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
        return 'stream.started';
    }

    public function broadcastWith(): array
    {
        return [
            'user_id' => $this->userId,
            'conference_id' => $this->conferenceId,
        ];
    }
}

