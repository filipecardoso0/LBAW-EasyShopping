<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    // Don't add create and update timestamps in database.
    public $timestamps  = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'email', 'username', 
        'user_type', 'publisher_name',  
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
        'banned', 'userID',
    ];

    /**
     * The notifications this user belongs to.
     */
    public function userNotifications() {
        return $this->belongsToMany('App\Models\Notification');
    }
      
    /**
     * The wishlist this user has.
     */
     public function userWishlist() {
        return $this->hasOne('App\Models\Wishlist');
    }

    /**
     * The orders this user owns.
     */
    public function userOrders() {
        return $this->hasMany('App\Models\Order');
    }
    
    /**
     * The shopping carts this user owns.
     */
    public function userCarts() {
        return $this->hasMany('App\Models\ShoppingCart');
    }

    /**
     * The reviews carts this user owns.
     */
    public function publishedReviews() {
        return $this->hasMany('App\Models\Review');
    }

    // User * -> Administrator 1 (banned)
    // GamePublisher 1 -> Game 1 (PublishedGame)
    // Administrator 0..1 -> Game * (Approved)
    // Administrator 1 -> Order * (ChangeState)
}
