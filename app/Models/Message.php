<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory; 
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'conference_id',
        'participation_id',
        'message'
    ];

    public function conference()
    {
        return $this->belongsTo(Conference::class);
    }

    public function participation()
    {
        return $this->belongsTo(ConferenceParticipation::class);
    }
}
