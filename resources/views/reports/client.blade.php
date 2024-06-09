@php
  \Carbon\Carbon::setLocale('id');
@endphp

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>
  <style>
    body {
      font-family: Arial, sans-serif;
    }

    .header {
      display: flex;
      align-items: center;
      border-bottom: 1px solid black;
      padding-bottom: 10px;
      margin-bottom: 20px;
    }

    .logo {
      width: 100px;
      float: left;
    }

    .title {
      text-align: center;
      margin-left: -80px;
    }

    .title h2,
    .title h3 {
      margin: 5px 0;
    }

    .info {
      margin-bottom: 20px;
    }

    .info p {
      margin: 5px 0;
    }

    .info .label {
      display: inline-block;
      width: 250px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 20px;
    }

    thead th {
      background-color: #f2f2f2;
      border: 2px solid black;
      text-align: center;
      padding: 8px;
    }

    tbody td,
    tfoot td {
      border: 2px solid black;
      padding: 8px;
      text-align: center;
    }

    tfoot td {
      font-weight: bold;
      text-align: right;
    }

    .approval {
      text-align: right;
    }
  </style>
</head>

<body>
  <div class="container">
    <div class="header">
      <img src="images/logo.png" alt="PT Cipto Sarana Nusantara" class="logo">
      <div class="title">
        <h2 style="text-transform: uppercase">PT {{ config('app.name') }}</h2>
        <h3>NEGOSIASI HARGA PEKERJAAN</h3>
        <h3>TAHUN ANGGARAN {{ \Carbon\Carbon::parse($record->created_at)->translatedFormat('Y') }}</h3>
      </div>
    </div>
    <div class="info">
      <p><span class="label">Nama Kegiatan </span> : {{ $record->name }}</p>
      <p><span class="label">Area/Hosbu/Proyek </span> : {{ $record->area }}</p>
    </div>
    <table>
      <thead>
        <tr>
          <th>NO.</th>
          <th>URAIAN PEKERJAAN</th>
          <th>SATUAN</th>
          <th>VOLUME</th>
          <th>HARGA SATUAN (Rp)</th>
          <th>JUMLAH BIAYA (Rp)</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>1</td>
          <td style="text-align: left">Pelanggan KI</td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
        </tr>
        @foreach ($record->clients as $client)
          <tr>
            <td>1.{{ $loop->iteration }}</td>
            <td style="text-align: left">{{ $client->name }}</td>
            <td>{{ $client->unit }}</td>
            <td>{{ $client->volume }}</td>
            <td style="text-align: right">Rp {{ number_format($client->unit_price, 2, ',', '.') }}</td>
            <td style="text-align: right">Rp {{ number_format($client->total_cost, 2, ',', '.') }}</td>
          </tr>
        @endforeach
      </tbody>
      <tfoot>
        <tr>
          <td colspan="5">Total Harga Pekerjaan Exclude PPN</td>
          <td>Rp {{ number_format($record->total_cost_exclude_ppn, 2, ',', '.') }}</td>
        </tr>
        <tr>
          <td colspan="5">Total Harga Pembulatan</td>
          <td>Rp {{ number_format($record->total_cost_rounded, 2, ',', '.') }}</td>
        </tr>
      </tfoot>
    </table>
    <div class="approval">
      <p>Medan, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
      <p>Menyetujui,</p>
      <img src="{{ asset('images/approved.webp') }}" alt="" height="100px">
      <p>PT {{ config('app.name') }}</p>
    </div>
  </div>
</body>

</html>
