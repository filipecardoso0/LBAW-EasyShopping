<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
      return $game->user->username;
  }


  /*
  Administrator
  public function user() {
    return $this->belongsTo('App\Models\User');
  }
  */

  public function carts() {
    return $this->belongsToMany('App\Models\ShoppingCart');
  }

  public function categories() {
    return $this->belongsToMany(Category::Class, 'categoryid', 'categoryid');
  }

}
