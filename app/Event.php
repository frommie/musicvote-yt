<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $primaryKey = ['session_id', 'event_type'];

    public $incrementing = false;
}
