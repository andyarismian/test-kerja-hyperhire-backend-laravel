<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Person;
use App\Models\PersonLike;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PersonLikeController extends Controller
{
    public function like(Request $request, $id)
    {
        $likerId = $request->input('liker_id');
        if (!$likerId) {
            return response()->json(['message' => 'liker_id is required'], 400);
        }
        
        $person = Person::findOrFail($id);
        if (!$person) {
            return response()->json(['message' => 'Person not found'], 404);
        }

        $like = PersonLike::updateOrCreate(
            ['liker_id' => $likerId, 'liked_id' => $person->id],
            ['is_like' => true]
        );

        // Recalculate likes count
        $likes = PersonLike::where('liked_id', $person->id)->where('is_like', true)->count();
        $person->likes_count = $likes;
        $person->save();

        return response()->json(['status' => 'liked', 'likes_count' => $likes]);
    }

    public function dislike(Request $request, $id)
    {
        $likerId = $request->input('liker_id');
        if (!$likerId) {
            return response()->json(['message' => 'liker_id is required'], 400);
        }
        
        $person = Person::findOrFail($id);

        $like = PersonLike::updateOrCreate(
            ['liker_id' => $likerId, 'liked_id' => $person->id],
            ['is_like' => false]
        );

        // Recalculate likes count
        $likes = PersonLike::where('liked_id', $person->id)->where('is_like', true)->count();
        $person->likes_count = $likes;
        $person->save();

        return response()->json(['status' => 'disliked', 'likes_count' => $likes]);
    }

    public function getLikes($id)
    {
        $person = Person::find($id);

        if (!$person) {
            return response()->json(['message' => 'Person not found'], 404);
        }

        $listedPeople = PersonLike::where('liked_id', $id)
            ->where('is_like', true)
            ->with('liked')
            ->get()
            ->map(function ($like) {
                return $like->liked;
            });
        return response()->json($listedPeople);
    }
}