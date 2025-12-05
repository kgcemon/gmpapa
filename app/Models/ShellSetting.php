<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShellSetting extends Model
{
    protected $table = 'shell_settings';
    protected $fillable = ['username', 'password','key','servername','status'];
}
