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

    public static function getUseridbyUsername($username){
        $user = User::where('username', '=', $username)->get();

        return $user;
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

    public function showAllUsers(){
        $users = User::orderByRaw('userid')->paginate(30);

        return view('pages.adminpage.showusers')
            ->with('users', $users);
    }

    public function findUserbyUsername(Request $request){

        $username = $request->get('username');

        $user = User::where('username', '=', $username)->get();

        return view('pages.adminpage.showuser')
            ->with('user', $user);
    }

    public function updateBanStatus(Request $request){

        $validated = $request->validate([
            'userid' => 'required|integer',
            'status' => 'required|string'
        ]);

        $banned = false;

        if($request->get('status') === 'unban'){
            $banned = false;
        }
        else{
            $banned = true;
        }

        $updateDetails = [
            'banned' => $banned
        ];

        User::where('userid', '=', $request->get('userid'))
            ->update($updateDetails);
    }

    public function showadminCreateUserAccount(){
        return view('pages.adminpage.registeruser');
    }

    public function storeadminCreateUserAccount(Request $request){

        $validated = $request->validate([
            'username' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|regex:/^.+@.+$/i|unique:users',
            'password' => 'required|string|min:6|confirmed'
        ]);

        return User::create([
            'email' => $request->get('email'),
            'username' => $request->get('username'),
            'password' => bcrypt($request->get('password')),
        ]);
    }

}
