<?php

namespace App\Models;
 use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conference extends Model
{
    use HasFactory;

    protected $fillable = [
    'title',
    'description',
    'admin_id',
    'category_id',
    'status',
    'stream_url',
    'qr_code'
];

   public function admin()
{
    return $this->belongsTo(User::class, 'admin_id');
}

public function category()
{
    return $this->belongsTo(Category::class);
}

public function participations()
{
    return $this->hasMany(ConferenceParticipation::class);
}
}
