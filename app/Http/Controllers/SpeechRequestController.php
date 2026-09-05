<?php

namespace App\Http\Controllers;

use App\Events\SpeechRequestUpdated;
use App\Models\ConferenceParticipation;
use App\Models\SpeechRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SpeechRequestController extends Controller
{
    public function requestToSpeak(Request $request)
    {
        $request->validate([
            'conference_id' => 'required|exists:conferences,id',
        ]);

        $participation = ConferenceParticipation::where('user_id', Auth::id())
            ->where('conference_id', $request->conference_id)
            ->where('participation_status', 'accepted')
            ->first();

        if (!$participation) {
            return response()->json([
                'success' => false,
                'message' => 'You are not an accepted participant in this conference'
            ], 403);
        }

        if ($participation->current_status === 'speaking') {
            return response()->json([
                'success' => false,
                'message' => 'You are already speaking'
            ], 400);
        }

        $existingRequest = SpeechRequest::where('participation_id', $participation->id)
            ->where('conference_id', $request->conference_id)
            ->where('status', 'pending')
            ->first();

        if ($existingRequest) {
            return response()->json([
                'success' => false,
                'message' => 'You already have a pending speech request'
            ], 400);
        }

        $speechRequest = SpeechRequest::create([
            'participation_id' => $participation->id,
            'conference_id' => $request->conference_id,
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Speech request sent successfully',
            'data' => $speechRequest
        ], 201);
    }

    public function pendingRequests()
    {
        $requests = SpeechRequest::with([
            'participation.user',
            'conference'
        ])
            ->where('status', 'pending')
            ->whereHas('conference', function ($query) {
                $query->where('admin_id', Auth::id());
            })
            ->orderBy('request_time', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $requests
        ]);
    }

    public function approveRequest(int $id)
    {
        $speechRequest = SpeechRequest::with('conference', 'participation')
            ->where('id', $id)
            ->where('status', 'pending')
            ->first();

        if (!$speechRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Pending speech request not found'
            ], 404);
        }


        if ($speechRequest->conference->admin_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to approve this request'
            ], 403);
        }

        $currentSpeaker = ConferenceParticipation::where(
                'conference_id',
                $speechRequest->conference_id
            )
            ->where('current_status', 'speaking')
            ->where('id', '!=', $speechRequest->participation_id)
            ->exists();

        if ($currentSpeaker) {
            return response()->json([
                'success' => false,
                'message' => 'There is already a speaker in this conference'
            ], 400);
        }


        $speechRequest->update([
            'status' => 'approved'
        ]);


        $speechRequest->participation->update([
            'current_status' => 'speaking'
        ]);

        event(new SpeechRequestUpdated($speechRequest));

        return response()->json([
            'success' => true,
            'message' => 'Speech request approved successfully',
            'data' => [
                'speech_request' => $speechRequest,
                'participation' => $speechRequest->participation
            ]
        ]);
    }

    public function rejectRequest(int $id)
    {
        $speechRequest = SpeechRequest::with('conference', 'participation')
            ->where('id', $id)
            ->where('status', 'pending')
            ->first();

        if (!$speechRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Pending speech request not found'
            ], 404);
        }


        if ($speechRequest->conference->admin_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to reject this request'
            ], 403);
        }


        $speechRequest->update([
            'status' => 'rejected'
        ]);

     
        event(new SpeechRequestUpdated($speechRequest));

        return response()->json([
            'success' => true,
            'message' => 'Speech request rejected successfully',
            'data' => [
                'speech_request' => $speechRequest
            ]
        ]);
    }

    public function endSpeaking(Request $request)
{
    $request->validate([
        'conference_id' => 'required|exists:conferences,id',
    ]);

    $participation = ConferenceParticipation::where('user_id', Auth::id())
        ->where('conference_id', $request->conference_id)
        ->where('current_status', 'speaking')
        ->first();

    if (!$participation) {
        return response()->json([
            'success' => false,
            'message' => 'You are not currently speaking in this conference'
        ], 400);
    }

    $participation->update([
        'current_status' => 'listening'
    ]);

    $speechRequest = SpeechRequest::where('participation_id', $participation->id)
        ->where('conference_id', $request->conference_id)
        ->where('status', 'approved')
        ->latest('id')
        ->first();

    if ($speechRequest) {
        event(new SpeechRequestUpdated($speechRequest));
    }

    return response()->json([
        'success' => true,
        'message' => 'Speaking ended successfully',
        'data' => [
            'participation_id' => $participation->id,
            'conference_id' => $request->conference_id,
            'current_status' => $participation->current_status
        ]
    ]);
}
}
