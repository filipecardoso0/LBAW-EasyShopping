<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function __construct(){
        $this->middleware(['auth']);
    }

    public function index(){

        //Gets user orders and info abou the games he bought
        $query = DB::table('order_')
            ->select('order_.userid', 'order_.orderid', 'order_.type', 'order_.state', 'order_.value', 'order_.order_date', 'game.gameid', 'game.price', 'game.title')
            ->join('game_order', 'game_order.orderid', '=', 'order_.orderid')
            ->join('game', 'game.gameid', '=', 'game_order.gameid')
            ->where('order_.userid', '=', auth()->user()->userid)
            ->get();

        return view('pages.dashboard')
            ->with('data', $query);
    }
}
