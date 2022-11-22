<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    // Don't add create and update timestamps in database.
  public $timestamps  = false;

    /**
     * The table associated with the model. (Overrides laravel's default naming convention)
     *
     * @var string
     */
    protected $table = 'review';

  /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
      'date', 'comment', 'rating',
  ];

    public function user() {
        return $this->belongsTo(User::Class, 'userid', 'userid');
    }

    public function game() {
        return $this->belongsTo(Game::Class, 'gameid', 'gameid');
    }

    /*
    NotifyReview
    public functionnotifications() {
        return $this->balongsToMany('App\Models\Notification');
    }
    */
}
