<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

//TODO FINALIZAR CRUD REVIEWS
//TODO ADICIONAR EDIT PROFILE

class Category extends Model
{
    /**
     * The table associated with the model. (Overrides laravel's default naming convention)
     *
     * @var string
     */
    protected $table = 'category';

    protected $primaryKey = 'categoryid';

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

    //Get all Game Categories
    public static function getAllCategories(){
        $query = Category::get();

        return $query;
    }

  //Useless, once Eloquent doesn't support Composite Keys
  public function gamecategories() {
    return $this->belongsToMany(GameCategories::Class, 'game_categories', 'categoryid');
  }
}
