<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
  // Don't add create and update timestamps in database.
  public $timestamps  = false;

  /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
      'payment_method', 'state',
      'value', 'order_date' 
  ];

  /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
      'approved',
  ];

  public function user() {
    return $this->belongsTo('App\Models\User', 'userID');
  }

  public function gameOrder() {
    return $this->hasMany('App\Models\GameOrder', 'userID', 'gameID');
  }

  /*
  Admnistrator
  public function administrator() {
    return $this->belongsTo('App\Models\User');
  }
  */
}
