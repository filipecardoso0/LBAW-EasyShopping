<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GameController extends Controller
{
    //Lists all the games
    public function index(){
        return view('pages.games');
    }
}
