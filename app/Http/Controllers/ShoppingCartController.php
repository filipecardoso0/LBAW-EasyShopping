<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\ShoppingCart;
use App\Models\ShoppingCartGuest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;

class ShoppingCartController extends Controller
{
    //Show Cart info
    public function index(Request $request){
        //Authenticated View
        if($request->user()){
            $cartitems = ShoppingCart::userCartItems();

            return view('pages.shoppingcart')
                ->with('items', $cartitems);
        }
        //Guest View
        else{
            if($request->session()->has('shoppingcart'))
                $cartitems = $request->session()->get('shoppingcart');
            else
                $cartitems = null;

            return view('pages.shoppingcart')
                ->with('items', $cartitems);
        }
    }

    //Adds game to the user shopping cart table -> User is logged in
    public function store(Request $request){

        $gameid = $request->get('gameid');
        $userid = $request->user()->userid;
        $gameprice = Game::find($gameid)->price;

        $games = ShoppingCart::where('gameid', '=', $gameid)->where('userid', '=', $userid)->get();
        if($games->count()){
            //It has already been added to the user's wishlist
            //TODO DISPLAY ERROR
            return 1;
        }
        else{
            //Insert data into the database
            ShoppingCart::create([
                'userid' => $userid,
                'gameid' => $gameid,
                'game_price' => $gameprice
            ]);

            return 0;
        }

    }

    //Removes a game from the user shopping cart -> User is logged in
    public function destroy(Request $request){
        DB::table('shopping_cart')
            ->where('userid', '=', auth()->user()->userid)
            ->where('gameid', '=', $request->get('gameid'))
            ->delete();
        return back();
    }

    //Creates a shopping Cart using sessions
    public function addToCartGuest(Request $request, int $gameid){
        //Gets the game info from the database
        $game = Game::find($gameid);

        //Shopping Cart Already Exists
        if($request->session()->has('shoppingcart')){
            $oldcart = $request->session()->get('shoppingcart');
        }
        else
            $oldcart = null;

        //Gets old games
        $cart = new ShoppingCartGuest($oldcart);

        //Inserts the new game
        if($cart->addItemToCart($game) == 1){
            $request->session()->put('shoppingcart', $cart);
            return 1;
        }

        //Creates a new session and stores the (new) cart details
        $request->session()->put('shoppingcart', $cart);

        return 0;
    }

    public function removeFromCartGuest(Request $request, int $gameid){

        //Shopping Cart Exists
        if($request->session()->has('shoppingcart')){
            $oldcart = $request->session()->get('shoppingcart');
        }
        //Shopping Cart does not exist, so it makes no sense to remove a game
        else
            return back();

        //Gets game info from the database
        $game = Game::find($gameid);

        //Gets old shopping cart values
        $cart = new ShoppingCartGuest($oldcart);
        //Removes the game from the shopping cart
        $cart->removeItemFromCart($game);

        //Creates a new session and stores the (new) cart details
        $request->session()->put('shoppingcart', $cart);

        //Goes back to the game page that the user was previously in
        return back();
    }

    /*
     * IMPORTANT -> WHEN USER LOGS IN THE SESSION CHANGES, SO ALL THE ITEMS IN THE SHOPPING CART WILL BE LOST
     * SO, IN ORDER TO MAINTAIN THE TRACK OF THE ITEMS THE USER HAD ADDED TO HIS SHOPPING CART WE ARE GOING TO USE COOKIES
     */

    //When guest presses checkout we are going to temporarily store the session cart items in a cookie
    //And then move to log in view
    public function guestItemstoCookie(Request $request){

        //Verify if is not empty
        if($request->session()->has('shoppingcart')){
            $cart = $request->session()->get('shoppingcart')->gameids;
        }
        else{
            //We don't need to pass it to a cookie, because the cart is empty
            return view('auth.login');
        }

        //If Cookie already exists delete it
        if(Cookie::has('cart')){
            Cookie::queue('cart', json_encode($cart), 0); //Sets minutes to 0 in order to delete it
        }

        //Creates a new cookie
        Cookie::queue('cart', json_encode($cart), 2);

        //Sends user to the login page
        return view('auth.login');
    }

}
