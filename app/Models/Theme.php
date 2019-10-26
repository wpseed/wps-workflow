<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Theme extends AbstractPackage
{
    /**
     * Get the versions for the theme.
     */
    public function theme_versions()
    {
        return $this->hasMany('App\Models\ThemeVersion');
    }
}
