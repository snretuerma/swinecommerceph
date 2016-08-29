<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Admin;

class Logs extends Model
{
    protected $fillable = ['admin_id', 'admin_name', 'action'];

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }
}
