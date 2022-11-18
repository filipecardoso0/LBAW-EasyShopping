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
      'read_status', 'notification_type', 
  ];

  public function users() {
    return $this->belongsToMany('App\Models\User');
  }

  /*
  NotifyWishlist
    public function wishlists() {
    return $this->belongsToMany('App\Models\Wishlist');
  }
  */

  /*
  NotifyReview
    public function reviews() {
    return $this->belongsToMany('App\Models\Review');
  }
  */
}
