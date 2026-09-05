<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Conference;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index()
    {

        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'message' => 'User not authenticated'
            ],401);
        }


        $userId = $user->id;


        $myConferences = Conference::with('category')
            ->where('admin_id',$userId)
            ->get();


        $joinedConferences = Conference::with('category')
            ->whereHas('participations', function($query) use ($userId){

                $query->where('user_id',$userId)
                      ->where('participation_status','accepted');

            })
            ->get();


        $categories = Category::all();


        return response()->json([

            'my_conferences'=>$myConferences,

            'joined_conferences'=>$joinedConferences,

            'categories'=>$categories

        ]);

    }
}
