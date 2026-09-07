<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ConferenceEnded implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $conferenceId;

    public function __construct(int $conferenceId)
    {
        $this->conferenceId = $conferenceId;
    }

    /*
    |--------------------------------------------------------------------------
    | Broadcast Channel
    |--------------------------------------------------------------------------
    */
    public function broadcastOn()
    {
        return new PrivateChannel(
            'conference.' . $this->conferenceId
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Event Name
    |--------------------------------------------------------------------------
    */
    public function broadcastAs()
    {
        return 'conference.ended';
    }

    /*
    |--------------------------------------------------------------------------
    | Data Sent to Clients
    |--------------------------------------------------------------------------
    */
    public function broadcastWith()
    {
        return [
            'conference_id' => $this->conferenceId,
            'status' => 'ended',
        ];
    }
}

