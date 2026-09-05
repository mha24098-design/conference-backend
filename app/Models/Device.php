<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory; 
use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'device_name',
        'ip_address',
        'mac_address',
        'is_authorized'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function participations()
    {
        return $this->hasMany(ConferenceParticipation::class);
    }
}
