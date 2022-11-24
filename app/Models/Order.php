<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
  // Don't add create and update timestamps in database.
  public $timestamps  = false;

    /**
     * The table associated with the model. (Overrides laravel's default naming convention)
     *
     * @var string
     */
    protected $table = 'order_';

    protected $primaryKey = 'orderid';


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
      'type', 'state',
      'value', 'userid'
  ];

  /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
    ];

  public function user() {
    return $this->hasMany('App\Models\User', 'userid');
  }

  public function gameOrders(){
      return $this->hasMany('App\Models\GameOrder', 'game_orderID', 'game_orderID');
  }

  /*
  Admnistrator
  public function administrator() {
    return $this->belongsTo('App\Models\User');
  }
  */
}
