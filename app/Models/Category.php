<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

//TODO REVER OS METODOS DO BELONG TO E HAS MANY

class Category extends Model
{
    /**
     * The table associated with the model. (Overrides laravel's default naming convention)
     *
     * @var string
     */
    protected $table = 'category';


    // Don't add create and update timestamps in database.
    public $timestamps  = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
      'name',
  ];

  //TODO REVER AS CATEGORIAS
  public function games() {
    return $this->belongsToMany(Game::Class, 'gameid', 'gameid');
  }
}
