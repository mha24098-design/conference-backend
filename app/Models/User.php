<?php



namespace App\Models;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    protected $hidden = [
    'password',
];
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'role'
    ];



public function createdConferences()
{
    return $this->hasMany(
        Conference::class,
        'admin_id'
    );
}

public function participations()
{
    return $this->hasMany(
        ConferenceParticipation::class
    );
}
}
