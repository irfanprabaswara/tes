<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Pemesanan extends Model
{
    protected $table = 'pemesanan';

    protected $primaryKey = 'no_tiket';

    protected $fillable = [
        'chatid',
        'username',
        'pic',
        'tanggal',
        'lokasi',
        'status'
    ];

    public $timestamps = false;
}
