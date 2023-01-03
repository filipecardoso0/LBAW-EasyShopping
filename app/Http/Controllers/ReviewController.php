<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request){

        $validator = $request->validate([
            'gameid' => 'required|integer',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string'
        ]);

        //Inserts data into the database
        Review::create([
            'userid' => $request->user()->userid,
            'gameid' => (int)$request->get('gameid'),
            'comment' => $request->get('comment'),
            'rating' => (int)$request->get('rating')
        ]);

    }

    public function destroy(Request $request){

        $validator = $request->validate([
            'gameid' => 'required|integer'
        ]);

        Review::where('gameid', '=', $request->get('gameid'))
            ->where('userid', '=', $request->user()->userid)
            ->delete();

    }

    public function update(Request $request){

        $validator = $request->validate([
            'gameid' => 'required|integer',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string',
            'date' => 'required|string'
        ]);

        $gameid = $request->get('gameid');
        $date = $request->get('date');
        $comment = $request->get('comment');
        $rating = $request->get('rating');

        $updateDetails = [
            'date' => $date,
            'comment' => $comment,
            'rating' => $rating,
            'status' => true
        ];

        Review::where('gameid', '=', $gameid)
                ->where('userid', '=', $request->user()->userid)
                ->update($updateDetails);
    }
}
