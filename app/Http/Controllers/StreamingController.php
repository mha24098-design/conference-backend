<?php

namespace App\Http\Controllers;
use App\Events\ConferenceEnded;
use App\Events\StreamStarted;
use App\Events\StreamStopped;
use App\Models\Conference;
use App\Models\ConferenceParticipation;
use Illuminate\Http\Request;

class StreamingController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Start Streaming
    |--------------------------------------------------------------------------
    */
    public function start(Request $request, int $conferenceId)
    {
        $userId = $request->user()->id;

        // Find conference
        $conference = Conference::find($conferenceId);

        if (!$conference) {
            return response()->json([
                'success' => false,
                'message' => 'Conference not found'
            ], 404);
        }

        // Cannot start a conference that has already ended
        if ($conference->status === 'ended') {
            return response()->json([
                'success' => false,
                'message' => 'The conference has already ended',
                'conference_status' => 'ended'
            ], 403);
        }

        // Check if user is an accepted participant
        $participation = ConferenceParticipation::where(
            'conference_id',
            $conferenceId
        )
        ->where('user_id', $userId)
        ->where('participation_status', 'accepted')
        ->first();

        if (!$participation) {
            return response()->json([
                'success' => false,
                'message' => 'You are not an accepted participant in this conference'
            ], 403);
        }

        // Check start time
        if (now()->lt($conference->start_date)) {
            return response()->json([
                'success' => false,
                'message' => 'Streaming cannot start before the conference start time',
                'start_date' => $conference->start_date
            ], 403);
        }

        /*
        |--------------------------------------------------------------------------
        | Important:
        | end_date does NOT automatically end the conference.
        | The admin decides when the conference actually ends.
        |--------------------------------------------------------------------------
        */

        // Change conference status to live
        if ($conference->status !== 'live') {
            $conference->update([
                'status' => 'live'
            ]);
        }

        // Start streaming for this participant
        $participation->update([
            'is_streaming' => true
        ]);

        // Broadcast stream started event
        event(new StreamStarted(
            $conferenceId,
            $userId
        ));

        return response()->json([
            'success' => true,
            'message' => 'Streaming started successfully',
            'is_streaming' => true,
            'conference_status' => 'live',
            'start_date' => $conference->start_date,
            'end_date' => $conference->end_date
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Stop Streaming
    |--------------------------------------------------------------------------
    |
    | This stops streaming for the current participant only.
    | It does NOT end the conference.
    |
    */
    public function stop(Request $request, int $conferenceId)
    {
        $userId = $request->user()->id;

        // Find conference
        $conference = Conference::find($conferenceId);

        if (!$conference) {
            return response()->json([
                'success' => false,
                'message' => 'Conference not found'
            ], 404);
        }

        // Check if user is an accepted participant
        $participation = ConferenceParticipation::where(
            'conference_id',
            $conferenceId
        )
        ->where('user_id', $userId)
        ->where('participation_status', 'accepted')
        ->first();

        if (!$participation) {
            return response()->json([
                'success' => false,
                'message' => 'You are not an accepted participant in this conference'
            ], 403);
        }

        // Stop streaming for this participant only
        $participation->update([
            'is_streaming' => false
        ]);

        // Broadcast stream stopped event
        event(new StreamStopped(
            $conferenceId,
            $userId
        ));



        return response()->json([
            'success' => true,
            'message' => 'Streaming stopped successfully',
            'is_streaming' => false,
            'conference_status' => $conference->status
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | End Conference
    |--------------------------------------------------------------------------
    |
    | Only the conference admin can end the entire conference.
    |
    */
    public function end(Request $request, int $conferenceId)
    {
        $userId = $request->user()->id;

        // Find conference
        $conference = Conference::find($conferenceId);

        if (!$conference) {
            return response()->json([
                'success' => false,
                'message' => 'Conference not found'
            ], 404);
        }

        // Check if current user is the conference admin
        if ($conference->admin_id !== $userId) {
            return response()->json([
                'success' => false,
                'message' => 'Only conference admin can end the conference'
            ], 403);
        }

        // Check if conference is already ended
        if ($conference->status === 'ended') {
            return response()->json([
                'success' => false,
                'message' => 'Conference is already ended'
            ], 400);
        }

        // Stop streaming for all participants
        ConferenceParticipation::where('conference_id', $conferenceId)
            ->update([
                'is_streaming' => false
            ]);

        // End the conference
        $conference->update([
            'status' => 'ended'
        ]);

        // Broadcast conference ended event
event(new ConferenceEnded($conferenceId));

        return response()->json([
            'success' => true,
            'message' => 'Conference ended successfully',
            'conference_status' => 'ended'
        ]);
    }
}
