<?php
namespace App\Http\Controllers;

use App\Models\ConferenceParticipation;
use Illuminate\Support\Facades\Auth;

class InvitationController extends Controller
{

    public function index()
    {
        $invitations = ConferenceParticipation::with('conference.category')
            ->where('user_id', Auth::id())
            ->where('participation_status', 'pending')
            ->orderBy('id', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'invitations' => $invitations
        ]);
    }


    public function accept(int $id)
    {
        $invitation = ConferenceParticipation::where('id', $id)
            ->where('user_id', Auth::id())
            ->where('participation_status', 'pending')
            ->first();

        if (!$invitation) {
            return response()->json([
                'success' => false,
                'message' => 'Pending invitation not found'
            ], 404);
        }


        $invitation->update([
            'participation_status' => 'accepted',
            'current_status' => 'listening',
            'is_muted' => false,
            'forced_muted_by_admin' => false,
            'is_streaming' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Invitation accepted',
            'data' => [
                'participation_id' => $invitation->id,
                'conference_id' => $invitation->conference_id,
                'participation_status' => $invitation->participation_status,
                'current_status' => $invitation->current_status,
                'channel' => 'private-conference.' . $invitation->conference_id
            ]
        ]);
    }


    public function reject(int $id)
    {
        $invitation = ConferenceParticipation::where('id', $id)
            ->where('user_id', Auth::id())
            ->where('participation_status', 'pending')
            ->first();

        if (!$invitation) {
            return response()->json([
                'success' => false,
                'message' => 'Pending invitation not found'
            ], 404);
        }

        $invitation->update([
            'participation_status' => 'rejected'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Invitation rejected',
            'data' => [
                'participation_id' => $invitation->id,
                'conference_id' => $invitation->conference_id,
                'participation_status' => $invitation->participation_status
            ]
        ]);
    }
}

