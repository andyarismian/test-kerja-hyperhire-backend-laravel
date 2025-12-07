<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Person;
use App\Models\PersonLike;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PersonController extends Controller
{
    public function index(Request $request, $id)
    {
        $perPage = (int) $request->query('per_page', 10);
        
        // Get the person whose recommendations we're finding
        $person = Person::findOrFail($id);
        
        // Parse location (format: "City, Country")
        $locationParts = explode(',', $person->location);
        $personCity = trim($locationParts[0] ?? '');
        $personCountry = trim($locationParts[1] ?? '');
        $personAge = $person->age;
        
        $query = Person::query();

        // Get IDs of people who have already been seen/liked/disliked by this person
        // $seenPersonIds = PersonLike::where('liker_id', $id)->pluck('liked_id')->toArray();

        // $query->where('id', '!=', $id) // Exclude self
        //     ->whereNotIn('id', $seenPersonIds); // Exclude already seen people
        
        // Add location priority ordering with age preference
        $query->selectRaw("
            *,
            CASE 
                WHEN location LIKE ? THEN 1
                WHEN location LIKE ? THEN 2
                ELSE 3
            END as location_priority,
            CASE 
                WHEN ? IS NOT NULL AND age BETWEEN ? AND ? THEN 1
                ELSE 2
            END as age_priority
        ", [
            '%' . $personCity . '%',
            '%' . $personCountry . '%',
            $personAge,
            $personAge - 5,
            $personAge + 5
        ])
        ->orderBy('location_priority', 'asc')
        ->orderBy('age_priority', 'asc')
        ->orderBy('likes_count', 'desc');

        return response()->json($query->paginate($perPage));
    }
}
