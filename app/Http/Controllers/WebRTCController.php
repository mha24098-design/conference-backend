<?php

namespace App\Http\Controllers;

use App\Events\WebRTCSignal;
use App\Models\ConferenceParticipation;
use Illuminate\Http\Request;

class WebRTCController extends Controller
{
    public function signal(Request $request)
    {
        $request->validate([
            'conference_id' => 'required|exists:conferences,id',
            'receiver_id' => 'required|exists:users,id',
            'type' => 'required|in:offer,answer,ice-candidate',
            'data' => 'required|array',
        ]);

        $senderId = $request->user()->id;

        // Check sender participation
        $senderParticipation = ConferenceParticipation::where(
            'conference_id',
            $request->conference_id
        )
        ->where('user_id', $senderId)
        ->where('participation_status', 'accepted')
        ->exists();

        if (!$senderParticipation) {
            return response()->json([
                'message' => 'You are not an accepted participant in this conference'
            ], 403);
        }

        // Check receiver participation
        $receiverParticipation = ConferenceParticipation::where(
            'conference_id',
            $request->conference_id
        )
        ->where('user_id', $request->receiver_id)
        ->where('participation_status', 'accepted')
        ->exists();

        if (!$receiverParticipation) {
            return response()->json([
                'message' => 'Receiver is not an accepted participant in this conference'
            ], 403);
        }

        // Prevent sending signal to yourself
        if ($senderId == $request->receiver_id) {
            return response()->json([
                'message' => 'You cannot send a WebRTC signal to yourself'
            ], 422);
        }

        event(new WebRTCSignal(
            $request->conference_id,
            $senderId,
            $request->receiver_id,
            $request->type,
            $request->data
        ));

        return response()->json([
            'success' => true,
            'message' => 'WebRTC signal sent successfully'
        ]);
    }
}
