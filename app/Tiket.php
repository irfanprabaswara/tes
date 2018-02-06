<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tiket extends Model
{
    protected $table = 'tiket';

    protected $primaryKey = 'no_tiket';

    protected $fillable = [
        'id',
        'id_driver',
        'chatid',
        'username',
        'pic',
        'tanggal',
        'lokasi',
        'status'
    ];

    public $timestamps = false;
}
