<?php

namespace App\Http\Controllers;

use App\Models\Wishlist;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function __construct(){
        $this->middleware(['auth']);
    }

    public function store(Request $request){
        $validated = $request->validate([
            'gameid' => 'required|integer'
        ]);

        $games = Wishlist::where('gameid', '=', $request->get('gameid'))->where('userid', '=', $request->user()->userid)->get();
        if($games->count()){
            //It has already been added to the user's wishlist
            //TODO DISPLAY ERROR
        }
        else{
            //Inserts data into the database
            Wishlist::create([
                'userid' => $request->user()->userid,
                'gameid' => $request->get('gameid')
            ]);
        }

        return back();
    }

    //Removes a game from the user wishlist
    public function destroy(Request $request){

        //Query in order to remove item from user's wishlist
        Wishlist::query()
            ->where('userid', '=', auth()->user()->userid)
            ->where('gameid', '=', $request->get('gameid'))
            ->delete();
    }
}
