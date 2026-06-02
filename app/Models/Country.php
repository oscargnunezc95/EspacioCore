<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $fillable = ['name', 'code', 'tax_id_label', 'tax_id_regex', 'currency_code', 'currency_symbol'];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function studios()
    {
        return $this->hasMany(Studio::class);
    }
}