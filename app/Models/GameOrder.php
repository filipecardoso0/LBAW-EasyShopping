<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
  // Don't add create and update timestamps in database.
  public $timestamps  = false;

  /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
      'price',  
    ];

  public function order() {
    return $this->belongsTo('App\Models\Order');
  }

  public function cart() {
    return $this->belongsTo('App\Models\ShoppingCart', 'userID', 'gameID');
  }
}
