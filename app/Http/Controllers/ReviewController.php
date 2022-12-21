<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    //TODO REVER A CENA DE IR BUSCAR AS REVIEWS DE UM JOGO À DATABASE. DEVE ESTAR AQUI COM O METODO INDEX

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

        return back();
    }
}
