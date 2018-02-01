<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    protected $table = 'driver';

    protected $primaryKey = 'id';

    protected $fillable = [
        'id',
        'nama'
        'status'
    ];

    public $timestamps = false;
}
