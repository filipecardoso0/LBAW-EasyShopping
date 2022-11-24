<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wishlist extends Model
{
  // Don't add create and update timestamps in database.
  public $timestamps  = false;

  /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
      'game_price', 
  ];

  public function user() {
    return $this->belongsTo('App\Models\User', 'userID');  
  }

  /*
  NotifyWishlist
  public function notification() {
    return $this->hasOne('App\Models\Notification');  
  }
  */

  public function game() {
    return $this->belongsTo('App\Models\Game', 'gameID');  
  }

}
