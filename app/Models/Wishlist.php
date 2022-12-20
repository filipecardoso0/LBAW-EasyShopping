<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wishlist extends Model
{
    // Don't add create and update timestamps in database.
    public $timestamps  = false;

    /**
     * The table associated with the model. (Overrides laravel's default naming convention)
     *
     * @var string
     */
    protected $table = 'wishlist';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'userid', 'gameid'
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
