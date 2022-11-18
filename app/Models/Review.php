<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    // Don't add create and update timestamps in database.
  public $timestamps  = false;

  /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
      'date', 'comment', 'rating',
  ];

    public function reviewUser() {
        return $this->belongsTo('App\Models\User', 'userID');  
    }

    public function game() {
        return $this->belongsTo('App\Models\Game', 'gameID');  
    }

    /*
    NotifyReview
    public functionnotifications() {
        return $this->balongsToMany('App\Models\Notification');  
    }
    */
}