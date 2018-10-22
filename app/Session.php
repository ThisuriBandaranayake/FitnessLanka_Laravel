<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Session extends Model
{
    public $table = 'oauth_access_tokens';
    protected $primaryKey = 'session_id';
    protected $fillable = ['revoked', 'browser_name', 'platform_name', 'device_family', 'device_model'];
    protected $hidden = ['id', 'client_id', 'name', 'scopes'];
}
