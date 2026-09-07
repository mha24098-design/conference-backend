<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Models\ConferenceParticipation;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    public function sendMessage(Request $request)
    {
        $request->validate([
            'conference_id' => 'required|exists:conferences,id',
            'message' => 'required|string|max:1000'
        ]);

        $participation = ConferenceParticipation::where('user_id', Auth::id())
            ->where('conference_id', $request->conference_id)
            ->where('participation_status', 'accepted')
            ->first();

        if (!$participation) {
            return response()->json([
                'success' => false,
                'message' => 'You are not part of this conference'
            ], 403);
        }

        $message = Message::create([
            'conference_id' => $request->conference_id,
            'participation_id' => $participation->id,
            'message' => $request->message,
        ]);

        broadcast(new MessageSent($message))->toOthers();

        return response()->json([
            'success' => true,
            'message' => 'Message sent successfully',
            'data' => [
                'id' => $message->id,
                'conference_id' => $message->conference_id,
                'participation_id' => $message->participation_id,
                'message' => $message->message,
            ]
        ], 201);
    }


    public function getMessages($conference)
    {
        $participation = ConferenceParticipation::where('user_id', Auth::id())
            ->where('conference_id', $conference)
            ->where('participation_status', 'accepted')
            ->first();

        if (!$participation) {
            return response()->json([
                'success' => false,
                'message' => 'You are not part of this conference'
            ], 403);
        }

        $messages = Message::where('conference_id', $conference)
            ->orderBy('id', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $messages
        ]);
    }
}
