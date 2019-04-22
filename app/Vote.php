<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Vote extends Model
{
  protected $primaryKey = ['video_id', 'session_id'];

  public $incrementing = false;
}
