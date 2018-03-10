<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title>Rekap</title>
  </head>
  <body>
    <table border="1">
      <thead>
        <tr>
          <th>NOMOR TIKET</th>
          <th>NAMA DRIVER</th>
          <th>BAGIAN PEMESAN</th>
          <th>TANGGAL</th>
          <th>LOKASI</th>
        </tr>
      </thead>
      <tbody>
        @foreach($bebas as $sabeb)
        <tr>
          <td>{{$sabeb->id}}</td>
          <td>{{$sabeb->nama}}</td>
          <td>{{$sabeb->pic}}</td>
          <td>{{$sabeb->tanggal}}</td>
          <td>{{$sabeb->lokasi}}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </body>
</html>
