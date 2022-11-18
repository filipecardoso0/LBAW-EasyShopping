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
      'gameID', 'userID', 
      'categoryID', 'approved',
  ];

  /**
   * The card this item belongs to.
   */
  public function card() {
    return $this->belongsTo('App\Models\Card');
  }
}
