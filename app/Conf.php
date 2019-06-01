<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Conf extends Model
{
  protected $fillable = ['key'];

  public static function get($key) {
    if (self::has($key)) {
      return self::get_all()->where('key', '=', $key)->first()->value;
    }
  }

  public static function set($key, $value) {
    $c = self::firstOrCreate(['key' => $key]);
    $c->value = $value;
    $c->save();
    return $c;
  }

  public static function has($key) {
    return (boolean) self::get_all()->whereStrict('key', $key)->count();
  }

  public static function get_all() {
    return self::all();
  }
}
