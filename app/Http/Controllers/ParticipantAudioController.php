<?php

namespace App\Http\Controllers;

use App\Events\ParticipantMuted;
use App\Events\ParticipantUnmuted;
use App\Models\Conference;
use App\Models\ConferenceParticipation;
use Illuminate\Http\Request;

class ParticipantAudioController extends Controller
{
    public function mute(Request $request, Conference $conference, $user)
    {

        if ($conference->admin_id != $request->user()->id) {
            return response()->json([
                'message' => 'Only the conference admin can mute participants'
            ], 403);
        }


        $participation = ConferenceParticipation::where(
            'conference_id',
            $conference->id
        )
        ->where('user_id', $user)
        ->where('participation_status', 'accepted')
        ->first();

        if (!$participation) {
            return response()->json([
                'message' => 'User is not an accepted participant in this conference'
            ], 404);
        }


        if ($conference->admin_id == $user) {
            return response()->json([
                'message' => 'The conference admin cannot mute themselves'
            ], 422);
        }

        $participation->update([
            'is_muted' => true,
            'forced_muted_by_admin' => true,
        ]);


        event(new ParticipantMuted(
            $conference->id,
            $user
        ));

        return response()->json([
            'success' => true,
            'message' => 'Participant muted successfully',
            'user_id' => (int) $user,
            'is_muted' => true,
            'forced_muted_by_admin' => true,
        ]);
    }

    public function unmute(Request $request, Conference $conference, $user)
    {

        if ($conference->admin_id != $request->user()->id) {
            return response()->json([
                'message' => 'Only the conference admin can unmute participants'
            ], 403);
        }

        $participation = ConferenceParticipation::where(
            'conference_id',
            $conference->id
        )
        ->where('user_id', $user)
        ->where('participation_status', 'accepted')
        ->first();

        if (!$participation) {
            return response()->json([
                'message' => 'User is not an accepted participant in this conference'
            ], 404);
        }


        if ($conference->admin_id == $user) {
            return response()->json([
                'message' => 'The conference admin cannot unmute themselves'
            ], 422);
        }


        $participation->update([
            'is_muted' => false,
            'forced_muted_by_admin' => false,
        ]);

   
        event(new ParticipantUnmuted(
            $conference->id,
            $user
        ));

        return response()->json([
            'success' => true,
            'message' => 'Participant unmuted successfully',
            'user_id' => (int) $user,
            'is_muted' => false,
            'forced_muted_by_admin' => false,
        ]);
    }
}
