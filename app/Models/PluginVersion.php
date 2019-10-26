<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PluginVersion extends Model
{
    /**
     * Get the plugin.
     */
    public function plugin()
    {
        return $this->belongsTo('App\Models\Plugin');
    }
}
