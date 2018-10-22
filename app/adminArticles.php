<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * @property array|null|string caption
 * @property array|null|string img
 * @property array|null|string description
 */
class adminArticles extends Model
{
    protected $fillable = [
        'caption', 'img','description',
    ];
}
