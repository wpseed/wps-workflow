<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

abstract class AbstractPackage extends Model
{
    public function getID()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getVersions()
    {
        return $this->versions;
    }
}
