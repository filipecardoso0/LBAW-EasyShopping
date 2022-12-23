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

    public function finalizeOrderPaypal(Request $request){

        $validated = $request->validate([
            'value' => 'required|integer',
            'cartitems' => 'required|string'
        ]);

        //Creates a new Order
        $val = Order::create([
            'userid' => $request->user()->userid,
            'type' => 'PayPal',
            'state' => true,
            'value' => $request->get('value')
        ]);


        $orderid = $val->orderid;
        $cartitems = json_decode($request->get('cartitems'));

        //Add Games to the game_order table
        foreach($cartitems->cartitems as $cartitem){
            GameOrder::create([
                'orderid' => $orderid,
                'gameid' => $cartitem->gameid,
                'price' => $cartitem->price,
            ]);
        }

        //Erase Shopping Cart
        ShoppingCart::eraseShoppingCart($request->user()->userid);

    }

}
