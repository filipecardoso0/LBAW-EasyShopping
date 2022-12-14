<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    // Don't add create and update timestamps in database.
    public $timestamps  = false;

    protected $primaryKey = 'ticketid'; //Overrides id as default primary key

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'userid'
    ];
}
