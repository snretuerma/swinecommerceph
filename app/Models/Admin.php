<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use App\Models\Logs;

class Admin extends Model
{
    public function logs()
    {
        return $this->hasMany(Logs::class);
    }
}
