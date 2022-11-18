<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Game extends Model
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
      'approved',
  ];

  public function reviews() {
    return $this->hasMany('App\Models\Review', 'userID');  
  }

  public function wishlists() {
    return $this->hasMany('App\Models\Wishlist');  
  }

  /*
  GamePublisher
  public function user() {
    return $this->belongsTo('App\Models\User');  
  }
  */

  /*
  Administrator
  public function user() {
    return $this->belongsTo('App\Models\User');  
  }
  */

  public function carts() {
    return $this->begongsToMany('App\Models\ShoppingCart');  
  }

  public function categories() {
    return $this->begongsToMany('App\Models\Category', 'categoryID');  
  }
 
}
