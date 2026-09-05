<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'participation_id',
        'check_in',
        'check_out'
    ];

    public function participation()
    {
        return $this->belongsTo(ConferenceParticipation::class);
    }
}
