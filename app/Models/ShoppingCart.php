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
      'payment_method', 'state', 
      'value', 'order_date',
    ];

  public function user() {
      return $this->belongsTo('App\Models\User');
  }

  public function games() {
    return $this->belongsToMany('App\Models\Game');  
  }

  public function gameOrder() {
    return $this->hasOne('App\Models\GameOrder');  
  }
}
