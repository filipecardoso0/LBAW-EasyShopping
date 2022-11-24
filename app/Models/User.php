<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    // Don't add create and update timestamps in database.
    public $timestamps  = false;

    protected $primaryKey = 'userid'; //Overrides id as default primary key

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'email', 'username', 'password'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'remember_token',
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
        return $this->hasMany('App\Models\Order', 'orderid');
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

    public function cart(){
        return $this->hasMany(ShoppingCart::Class, 'userid', 'userid');
    }

    /* GamePublisher */
    public function game() {
        return $this->hasMany(Game::Class, 'gameid', 'gameid');
    }
}
