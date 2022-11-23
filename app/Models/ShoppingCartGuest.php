<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;


class ShoppingCartGuest extends Model
{
    // Don't add create and update timestamps in database.
    public $timestamps  = false;

    public $items = [];
    public $totalQuantity = 0;
    public $totalPrice = 0;

    public function __construct($oldCart){
        if($oldCart){
            $this->items = $oldCart->items;
            $this->totalQuantity = $oldCart->totalQuantity;
            $this->totalPrice = $oldCart->totalPrice;
        }
    }

    //Adds Item to Cart
    public function addItemToCart($game){
        //Asserts if shopping cart is not empty
        if($this->items){
            foreach($this->items as $item){
                //Game has already been added
                if($item->gameid == $game->gameid){
                    //Game exists
                    //Send back message error
                    return;
                }
                $id++;
            }
        }

        $finalprice = $game->price-($game->price*$game->discount);
        array_push($this->items, $game);
        $this->totalQuantity++;
        $this->totalPrice += $finalprice;
    }

    //Removes Items from Cart
    public function removeItemFromCart($game){
        //Asserts if shopping cart is not empty
        $id = 0;
        $found = false;
        if($this->items){
            foreach($this->items as $item){
                if($item->gameid == $game->gameid){
                    //Game exists
                    unset($this->items[$id]);
                    $found = true;
                }
                $id++;
            }
        }

        if(!$found){
            //Item does not exist on the cart
            //Send back an error message
            return;
        }

        $this->totalQuantity--;
        $this->totalPrice -= ($game->price-($game->price*$game->discount));
    }

}
