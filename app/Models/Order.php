<?php

namespace App\Models;

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
      'gameID', 'userID', 
      'categoryID', 'approved',
  ];
}
