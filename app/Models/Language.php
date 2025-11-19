<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    public function words()
    {
        return $this->hasMany(Word::class, 'lang_id');
    }
}
