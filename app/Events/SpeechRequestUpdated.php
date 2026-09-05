<?php

namespace App\Events;

use App\Models\SpeechRequest;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SpeechRequestUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public SpeechRequest $speechRequest;

    public function __construct(SpeechRequest $speechRequest)
    {
        $this->speechRequest = $speechRequest;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel(
                'conference.' . $this->speechRequest->conference_id
            ),
        ];
    }

    public function broadcastAs(): string
    {
        return 'speech.request.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->speechRequest->id,
            'participation_id' => $this->speechRequest->participation_id,
            'conference_id' => $this->speechRequest->conference_id,
            'status' => $this->speechRequest->status,
            'current_status' => $this->speechRequest->participation->current_status,
        ];
    }
}
