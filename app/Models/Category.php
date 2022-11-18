<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
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

  public function userNotifications() {
    return $this->belongsToMany('App\Models\Notification');
  }
}