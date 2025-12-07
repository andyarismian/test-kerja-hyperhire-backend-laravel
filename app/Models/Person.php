<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Person extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'age', 'pictures', 'location', 'likes_count', 'notified_at'
    ];

    protected $casts = [
        'pictures' => 'array',
        'notified_at' => 'datetime',
    ];

    // People this person has liked
    public function likedPeople()
    {
        return $this->belongsToMany(Person::class, 'person_likes', 'liker_id', 'liked_id')
                    ->withPivot('is_like')
                    ->withTimestamps();
    }

    // People who liked this person
    public function likedByPeople()
    {
        return $this->belongsToMany(Person::class, 'person_likes', 'liked_id', 'liker_id')
                    ->withPivot('is_like')
                    ->withTimestamps();
    }

    // All like records for this person
    public function likes()
    {
        return $this->hasMany(PersonLike::class, 'liked_id');
    }
}
