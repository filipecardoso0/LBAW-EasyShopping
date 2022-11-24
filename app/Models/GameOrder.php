<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GameOrder extends Model
{
  // Don't add create and update timestamps in database.
  public $timestamps  = false;

    /**
     * The table associated with the model. (Overrides laravel's default naming convention)
     *
     * @var string
     */
    protected $table = 'game_order';

  /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
      'orderid', 'gameid', 'price'
    ];

  public function orders() {
    return $this->hasMany('App\Models\Order', 'orderID', 'orderID');
  }

  public function games(){
      return $this->hasMany('App\Models\Game', 'gameid', 'gameid');
  }
}
