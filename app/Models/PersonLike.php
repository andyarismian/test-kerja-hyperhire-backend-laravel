<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PersonLike extends Model
{
    use HasFactory;

    protected $table = 'person_likes';

    protected $fillable = ['liker_id', 'liked_id', 'is_like'];

    public function liker()
    {
        return $this->belongsTo(Person::class, 'liker_id');
    }

    public function liked()
    {
        return $this->belongsTo(Person::class, 'liked_id');
    }
}
