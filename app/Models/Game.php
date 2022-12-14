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
