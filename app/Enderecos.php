<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Contatos;

class Enderecos extends Model
{
  use SoftDeletes;
    //
  public function contato()
  {
      return $this->belongsTo('App\Contatos');
  }
}
