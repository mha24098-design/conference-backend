<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory ;
use Illuminate\Database\Eloquent\Model;

class SpeechRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'participation_id',
        'conference_id',
        'status'
    ];

    public $timestamps = false;

    public function participation()
    {
        return $this->belongsTo(ConferenceParticipation::class);
    }

    public function conference()
    {
        return $this->belongsTo(Conference::class);
    }
}
