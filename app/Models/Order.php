<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Order extends Model
{
  // Don't add create and update timestamps in database.
  public $timestamps  = false;

    /**
     * The table associated with the model. (Overrides laravel's default naming convention)
     *
     * @var string
     */
    protected $table = 'order_';

    protected $primaryKey = 'orderid';


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
      'type', 'state',
      'value', 'userid'
  ];

  /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
    ];

  public static function getOrdersWithDetail($i){
      $query = DB::table('order_')
          ->select('users.username', 'order_.type', 'order_.state', 'order_.order_date', 'order_.value', 'order_.orderid')
          ->join('users', 'users.userid', '=', 'order_.userid')
          ->orderBy('order_.orderid')
          ->take($i)
          ->get();

      return $query;
  }

    public static function getOrdersWithDetailAndPagination($i){
        $query = DB::table('order_')
            ->select('users.username', 'order_.type', 'order_.state', 'order_.order_date', 'order_.value', 'order_.orderid')
            ->join('users', 'users.userid', '=', 'order_.userid')
            ->orderBy('order_.orderid')
            ->paginate($i);

        return $query;
    }

  public function user() {
    return $this->hasMany('App\Models\User', 'userid');
  }

  public function gameOrders(){
      return $this->hasMany('App\Models\GameOrder', 'game_orderID', 'game_orderID');
  }

  /*
  Admnistrator
  public function administrator() {
    return $this->belongsTo('App\Models\User');
  }
  */
}
