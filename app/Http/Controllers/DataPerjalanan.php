<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DataPerjalanan extends Controller
{
    public function tampilData()
    {
      $month= date("m");
      // dd($month);
      $bebas = DB::table('driver')->leftJoin('tiket','tiket.id_driver','=','driver.id')
                                  // ->whereDate('tanggal', '=', date('Y-m-d'))
                                  ->whereMonth('tanggal','=',$month)
                                  ->where('status','=','SELESAI')
                                  ->get();
      // return $bebas;
      return view ('Rekap',compact('bebas'));
    }
}
