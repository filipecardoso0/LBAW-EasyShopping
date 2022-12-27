<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function __construct(){
        $this->middleware(['auth']);
    }

    public function showProfilePage(){
        return view('pages.userpage.profilepage');
    }

    //TODO TROCAR ESTE CODIGO PARA O CONTROLLER DA WISHLIST FAZ MAIS SENTIDO
    public function showWishlist(){
        //Gets user wishlist games
        $games = User::getUserWishListGames();

        return view('pages.userpage.wishlist')
            ->with('games', $games);
    }

    public function showOrders(){

        $query = User::getUserGameOrders();

        return view('pages.userpage.userorders')
            ->with('data', $query);
    }

    public function showAdminDashboard(){
        return view('pages.adminpage.dashboard');
    }

}
