<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;


class ShoppingCart extends Model
{

    //TODO A9->NAO ESQUECER DE RESOLVER OS CASOS EM QUE O JOGO AINDA NAO FOI APROVADO
    //TODO Colocar em Eloquent o que der para colocar

    // Don't add create and update timestamps in database.
    public $timestamps  = false;

    /**
     * The table associated with the model. (Overrides laravel's default naming convention)
     *
     * @var string
     */
    protected $table = 'shopping_cart';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
      'userid', 'gameid', 'game_price'
    ];


    //Gets user cart products count -> Esta deve daar par acolocar em eloquent
    public static function userCartGamesCount(){
        $query = DB::table('shopping_cart')
          ->select((DB::raw('count(*) as cartgamescount')))
          ->where('userid', '=', auth()->user()->userid)
          ->groupBy('userid')
          ->get();

        return $query;
      }

    //Gets user cart games count
    public static function userCartItems(){
        $query = DB::table('shopping_cart')
            ->select('game.gameid', 'game.userid', 'game.title', 'game.price', 'game.discount')
            ->where('shopping_cart.userid', '=', auth()->user()->userid)
            ->join('game', 'game.gameid', '=', 'shopping_cart.gameid')
            ->get();

        return $query;
    }

    /*
  public function user() {
    return $this->belongsTo('App\Models\User', 'userid', 'userid');
  }

  public function games() {
    return $this->belongsToMany('App\Models\Game', 'gameID');
  }

  public function gameOrder() {
    return $this->hasOne('App\Models\GameOrder');
  }
    */
}
