<?php
namespace App\Http\Controllers;

use App\Events\StreamStarted;
use App\Events\StreamStopped;
use App\Models\ConferenceParticipation;
use Illuminate\Http\Request;

class StreamingController extends Controller
{

    public function start(Request $request, int $conferenceId)
    {
        $userId = $request->user()->id;

        $participation = ConferenceParticipation::where(
            'conference_id',
            $conferenceId
        )
        ->where('user_id', $userId)
        ->where('participation_status', 'accepted')
        ->first();

        if (!$participation) {
            return response()->json([
                'message' => 'You are not an accepted participant in this conference'
            ], 403);
        }

        $participation->update([
            'is_streaming' => true
        ]);

        event(new StreamStarted(
            $conferenceId,
            $userId
        ));

        return response()->json([
            'success' => true,
            'message' => 'Streaming started successfully',
            'is_streaming' => true
        ]);
    }



    public function stop(Request $request,int $conferenceId)
    {
        $userId = $request->user()->id;

        $participation = ConferenceParticipation::where(
            'conference_id',
            $conferenceId
        )
        ->where('user_id', $userId)
        ->where('participation_status', 'accepted')
        ->first();

        if (!$participation) {
            return response()->json([
                'message' => 'You are not an accepted participant in this conference'
            ], 403);
        }

        $participation->update([
            'is_streaming' => false
        ]);

        event(new StreamStopped(
            $conferenceId,
            $userId
        ));

        return response()->json([
            'success' => true,
            'message' => 'Streaming stopped successfully',
            'is_streaming' => false
        ]);
    }
}

