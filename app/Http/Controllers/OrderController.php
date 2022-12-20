<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\GameOrder;
use App\Models\Order;
use App\Models\ShoppingCart;
use Illuminate\Http\Request;

//TODO REPLACE THIS FAKE PAYMENT GATEWAY BY THE SPLICE/PAYPAL ONE

class OrderController extends Controller
{
    public function __construct(){
        $this->middleware(['auth']);
    }

    public function showPaymentGateway(){
        //Gets user shopping cart
       return view('pages.checkout');
    }

    //Receives payment gateway details and finalize order
    public function finalizePayment(Request $request){

        //Verify if card's number is according the standard
        if($request->paymentmethod == 'Visa'){
            $verify = 'required|integer|min:4000000000000000|max:4999999999999999';
        }
        else{
            $verify = 'required|integer|min:5100000000000000|max:5599999999999999';
        }

        $validated = $request->validate([
            'address1' => 'required|string',
            'address2' => 'string',
            'state' => 'required|string',
            'zip' => 'required|string',
            'cardnumber' => $verify,
            'cvc' => 'required|integer|min:0|max:999',
            'expmonth' => 'required|integer|min:1|max:12',
            'expyear' => 'required|integer|min:2022|max:9999'
        ]);

        //Gest user shopping cart
        $cartitems = ShoppingCart::get()->where('userid', '=', $request->user()->userid);
        $totalprice = 0;

        //Gets items total price
        foreach($cartitems as $cartitem){
            $game =  Game::find($cartitem->gameid);
            $totalprice += $game->price - ($game->price*$game->discount);
        }

        //Creates a new Order
        $val = Order::create([
            'userid' => $request->user()->userid,
            'type' => $request->get('paymentmethod'),
            'state' => true,
            'value' => $totalprice,
        ]);

        $orderid = $val->orderid;

        //Add Games to the game_order table
        foreach($cartitems as $cartitem){
            GameOrder::create([
                'orderid' => $orderid,
                'gameid' => $cartitem->gameid,
                'price' => Game::find($cartitem->gameid)->gameid,
            ]);
        }

        //Erase Shopping Cart
        ShoppingCart::eraseShoppingCart($request->user()->userid);

        //Redirects user to his dashboard
        return redirect()->route('userorders');
    }

}
