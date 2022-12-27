<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class GameOrder extends Model
{
  // Don't add create and update timestamps in database.
  public $timestamps  = false;

    /**
     * The table associated with the model. (Overrides laravel's default naming convention)
     *
     * @var string
     */
    protected $table = 'game_order';

  /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
      'orderid', 'gameid', 'price'
    ];

    public static function userHasBoughtGame($gameid){
        $query = Db::table('game_order')
            ->join('order_', 'game_order.orderid', '=', 'order_.orderid')
            ->where('userid', '=', auth()->user()->userid)
            ->where('gameid', '=', $gameid)
            ->get();

        if($query->count() == 0)
            return 0;
        else
            return 1;
    }

  public function orders() {
    return $this->hasMany('App\Models\Order', 'orderID', 'orderID');
  }

  public function games(){
      return $this->hasMany('App\Models\Game', 'gameid', 'gameid');
  }

  public static function orderGames($orderid){
      $query = DB::table('game_order')
                ->where('game_order.orderid', '=', $orderid)
                ->join('game', 'game.gameid', '=', 'game_order.gameid')
                ->get();

      return $query;
  }
}
