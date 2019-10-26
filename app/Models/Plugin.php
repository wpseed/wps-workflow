<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plugin extends AbstractPackage
{
    /**
     * Get the versions for the plugin.
     */
    public function plugin_versions()
    {
        return $this->hasMany('App\Models\PluginVersion');
    }
}
