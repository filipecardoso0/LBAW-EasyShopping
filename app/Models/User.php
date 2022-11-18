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
        'banned',
    ];

    public function userCarts() {
        return $this->hasMany('App\Models\ShoppingCart');  
    }
    
    /*
    User -> Administrator
    public function () {
        return $this->belongsTo('App\Models\User');
    }
    */

    public function reviews() {
        return $this->hasMany('App\Models\Review');  
    }

    public function wishlist() {
        return $this->hasOne('App\Models\Wishlist');  
    }

    public function orders() {
        return $this->hasMany('App\Models\Order');  
    }

    public function notifications() {
        return $this->belongsToMany('App\Models\Notification');  
    }

    /*
    Administrator
    public function orders() {
        return $this->hasMany('App\Models\Order');
    }
    */

    /*
    Administrator
    public function games() {
        return $this->hasMany('App\Models\Game');
    }
    */

    /*
    GamePublisher
    public function game() {
        return $this->hasOne('App\Models\Game');
    }
    */
}
