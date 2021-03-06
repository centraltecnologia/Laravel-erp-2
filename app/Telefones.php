<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Combobox_texts;

class Telefones extends Model
{
    protected $table = 'telefones';
    protected $fillable = ['tipo', 'numero', 'contato', 'setor', 'ramal'];

    public function contatos()
    {
        return $this->belongsTo('App\Contatos');
    }
    public function combos()
    {
        return $this->morphMany('App\Combobox_texts', 'combobox_textable');
    }
}
