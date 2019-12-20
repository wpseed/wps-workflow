<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ThemeVersion extends Model
{
    /**
     * Get the theme.
     */
    public function theme()
    {
        return $this->belongsTo('App\Models\Theme');
    }
}
