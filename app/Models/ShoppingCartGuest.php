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
    public $gameids = [];

    public function __construct($oldCart){
        if($oldCart){
            $this->items = $oldCart->items;
            $this->totalQuantity = $oldCart->totalQuantity;
            $this->totalPrice = $oldCart->totalPrice;
            $this->gameids = $oldCart->gameids;
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
                    return 1;
                }
            }
        }

        $finalprice = $game->price-($game->price*$game->discount);
        array_push($this->items, $game);
        array_push($this->gameids, $game->gameid);
        $this->totalQuantity++;
        $this->totalPrice += $finalprice;
    }

    //Removes Items from Cart
    public function removeItemFromCart($game){
        //Asserts if shopping cart is not empty
        $cntr = 0;
        $found = false;
        if($this->items){
            foreach($this->items as $item){
                if($item->gameid == $game->gameid){
                    //Game exists
                    unset($this->items[$cntr]);
                    $this->items = array_values($this->items); //Reorders indexes again
                    $found = true;
                    break;
                }
                $cntr++;
            }
            $cntr1 = 0;
            if($found){
                foreach($this->gameids as $gameid){
                    if($gameid == $game->gameid){
                        //Remove the game id from the game id array
                        unset($this->gameids[$cntr1]);
                        $this->gameids = array_values($this->gameids); //Reorders indexes again
                        break;
                    }
                    $cntr1++;
                }
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
