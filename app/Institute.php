<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Institute extends Model
{
    protected $primaryKey = 'user_id';

    protected $fillable = [
        'user_id', 'institute_name', 'address_line1', 'address_line2', 'city', 'province', 'country', 'postal_code'
    ];

    protected $hidden = ['user_id'];

    public function user()
    {
        return $this->belongsTo('App\User');
    }
}
