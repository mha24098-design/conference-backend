<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConferenceParticipation extends Model
{
    use HasFactory;

    protected $table = 'conference_participation';

    protected $fillable = [
    'user_id',
    'conference_id',
    'device_id',
    'participation_status',
    'current_status',
    'is_muted',
    'forced_muted_by_admin',
    'is_streaming'
];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function conference()
    {
        return $this->belongsTo(Conference::class);
    }

    public function device()
    {
        return $this->belongsTo(Device::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class, 'participation_id');
    }

    public function speechRequests()
    {
        return $this->hasMany(SpeechRequest::class, 'participation_id');
    }

    public function attendanceLogs()
    {
        return $this->hasMany(AttendanceLog::class, 'participation_id');
    }
}
