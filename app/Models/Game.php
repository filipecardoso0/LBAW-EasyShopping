<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Game extends Model
{
  // Don't add create and update timestamps in database.
  public $timestamps  = false;

    /**
     * The table associated with the model. (Overrides laravel's default naming convention)
     *
     * @var string
     */
    protected $table = 'game';

    protected $primaryKey = 'gameid'; //Overrides laravel's default pk for game table


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
      'title', 'description', 'price',
      'release_date', 'classification',
      'discount',
  ];

  /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
      'approved',
  ];

  public static function getLatestGames(int $limit){
    $query = DB::table('game')
            ->select('game.gameid', 'game.price', 'game.title', 'game.release_date')
            ->orderbyRaw('game.release_date DESC')
            ->take($limit)
            ->get();
    return $query;
  }

    //Fetches game info from the game with the biggest amount of sales to the one with the least amount of sales
    public static function getBestSellersWithPagination(){
        $query = DB::table('game')
            ->select('game.gameid', 'game.title', 'game.price', 'game.discount', 'game.classification', 'order_.state', (DB::raw('count(*) as salesnum')))
            ->join('game_order', 'game_order.gameid', '=', 'game.gameid')
            ->join('order_', 'order_.orderid', '=', 'game_order.orderid')
            ->where('order_.state', '=', 'true')
            ->groupBy('game.gameid', 'order_.state')
            ->orderByRaw('salesnum DESC')
            ->paginate(12);

        return $query;
    }

    //Fetch the info about First x Games with the highest ammount of sales
    public static function getBestSellers(int $x){
        //Fetches the game info from orders table join with game table in descending order and having state=true(have been bought)
        if($x > 0){
            //Select game_order.gameid, count(*) as salesgame from easyshopping.game_order Group by(gameid) Order by salesgame DESC;

            $query = DB::table('game')
                ->select('game.gameid', 'game.title', 'game.price', 'game.discount', 'game.classification', (DB::raw('count(*) as salesnum')))
                ->join('game_order', 'game_order.gameid', '=', 'game.gameid')
                ->join('order_', 'order_.orderid', '=', 'game_order.orderid')
                ->where('order_.state', '=', 'true')
                ->groupBy('game.gameid')
                ->orderByRaw('salesnum DESC')
                ->take($x)
                ->get();
            return $query;
        }

        $query = DB::table('game')
            ->select('game.gameid', 'game.title', 'game.price', 'game.discount', 'game.classification', 'order_.state', (DB::raw('count(*) as salesnum')))
            ->join('game_order', 'game_order.gameid', '=', 'game.gameid')
            ->join('order_', 'order_.orderid', '=', 'game_order.orderid')
            ->where('order_.state', '=', 'true')
            ->groupBy('game.gameid', 'order_.state')
            ->orderByRaw('salesnum DESC')
            ->get();

        return $query;
    }

    //Eloquent Relational Mapping

  //Gets game reviews
  public function reviews() {
    return $this->hasMany(Review::Class, 'gameid', 'gameid');
  }

  public function wishlists() {
    return $this->hasMany('App\Models\Wishlist');
  }

  /* GamePublisher */
  public function user() {
    return $this->belongsTo(User::Class, 'userid', 'userid');
  }

  //Get GamePublisher through the gameid
  public static function getOwnerNameByGameId(int $id){
      $game = Game::find($id);
      return $game->user->publisher_name;
  }

  /*
  Administrator
  public function user() {
    return $this->belongsTo('App\Models\User');
  }
  */

  public function carts() {
    return $this->hasMany('App\Models\ShoppingCart');
  }

  public function gamecategories() {
      return $this->belongsToMany(GameCategories::Class, 'game_categories', 'gameid', 'gameid');
  }

}
