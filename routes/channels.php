<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\ConferenceParticipation;

Broadcast::channel('conference.{id}', function ($user, $id) {
    return ConferenceParticipation::where('user_id', $user->id)
        ->where('conference_id', $id)
        ->where('participation_status', 'accepted')
        ->exists();
});
