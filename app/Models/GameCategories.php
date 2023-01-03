<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;


class GameCategories extends Model
{
    /**
     * The table associated with the model. (Overrides laravel's default naming convention)
     *
     * @var string
     */
    protected $table = 'game_categories';

    // Don't add create and update timestamps in database.
    public $timestamps  = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'gameid', 'categoryid'
    ];

    public static function getGameCategories(int $gameid){
        $query = DB::table('game_categories')
                ->select('category.categoryid', 'category.name')
                ->join('category', 'category.categoryid', '=', 'game_categories.categoryid')
                ->where('game_categories.gameid', '=', $gameid)
                ->get();
        return $query;
    }

    //Eloquent Relationships -> In this case useless, once Eloquent doesn't support Composite Primary Keys
    public function games(){
        return $this->belongsToMany(Game::class, 'game', 'gameid', 'gameid');
    }

    public function categories(){
        return $this->belongsToMany(Category::class, 'category', 'categoryid');
    }
}
