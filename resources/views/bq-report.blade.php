@php
  \Carbon\Carbon::setLocale('id');
@endphp

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>BQ Project</title>

  <style>
    body {
      font-family: Arial, sans-serif;
    }

    table {
      width: 100%;
      border-collapse: collapse;
    }

    table,
    th,
    td {
      border: 1px solid black;
    }

    th,
    td {
      padding: 10px;
    }

    td {
      text-align: left;
    }

    th {
      text-align: center;
      background-color: #ddd;
    }

    td:first-child {
      text-align: center;
    }

    .works__title {
      font-weight: bold;
    }
  </style>
</head>

<body>
  <table>
    <tr>
      <td rowspan="3" style="width: 200px;">
        <img src="{{ asset('images/logo.webp') }}" alt="" height="80">
      </td>
      <th style="background: none; border-bottom: none;">PT PERUSAHAAN GAS NEGARA Tbk</th>
    </tr>
    <tr>
      <th style="background: none; border-top: none; border-bottom: none;">NEGOSIASI HARGA PEKERJAAN</th>
    </tr>
    <tr>
      <th style="background: none; border-top: none;">TAHUN ANGGARAN {{ date('Y', strtotime($record->created_at)) }}
      </th>
    </tr>
  </table>
  <br>
  <table style="border: none">
    <tr>
      <th style="background: none; border: none; text-align: left; width: 200px; vertical-align: top;">Nama Kegiatan
      </th>
      <th style="background: none; border: none; text-align: left; vertical-align: top;">:</th>
      <th style="background: none; border: none; text-align: left; vertical-align: top;">{{ $record->activity_name }}
      </th>
    </tr>
    <tr>
      <th style="background: none; border: none; text-align: left; vertical-align: top; width: 200px;">Area/Hosbu/Proyek
      </th>
      <th style="background: none; border: none; text-align: left; vertical-align: top;">:</th>
      <th style="background: none; border: none; text-align: left; vertical-align: top;">{{ $record->area }}</th>
    </tr>
  </table>
  <br>
  <table>
    <tr>
      <th>NO.</th>
      <th>URAIAN PEKERJAAN</th>
      <th>SATUAN</th>
      <th>VOLUME</th>
      <th>HARGA SATUAN (Rp)</th>
      <th>JUMLAH BIAYA (Rp)</th>
    </tr>
    <tr class="works__title">
      <td>1</td>
      <td>{{ $record->works_title }}</td>
      <td></td>
      <td></td>
      <td></td>
      <td></td>
    </tr>
    @foreach ($record->works as $work)
      <tr>
        <td>1.{{ $loop->iteration }}</td>
        <td>{{ $work->name }}</td>
        <td>{{ $work->unit }}</td>
        <td style="text-align: right">{{ $work->volume }}</td>
        <td style="text-align: right">{{ number_format($work->unit_price, 2, ',', '.') }}</td>
        <td style="text-align: right">{{ number_format($work->total_price, 2, ',', '.') }}</td>
      </tr>
    @endforeach
    <tr>
      <th colspan="5" style="text-align: right">Total Harga Pekerjaan Exclude PPN</th>
      <td style="text-align: right">{{ number_format($record->total_cost_exclude_ppn, 2, ',', '.') }}</td>
    </tr>
    <tr>
      <th colspan="5" style="text-align: right">Total Harga Pembulatan</th>
      <td style="text-align: right">{{ number_format($record->total_cost_rounded, 2, ',', '.') }}</td>
    </tr>
  </table>
  <br>
  <br>
  <br>
  <table style="border: none">
    <tr>
      <td style="background: none; border: none; text-align: right;">Medan,
        {{ \Carbon\Carbon::parse($record->created_at)->translatedFormat('d F Y') }}</td>
    </tr>
    <tr>
      <td style="background: none; border: none; text-align: right;">Disetujui Oleh,</td>
    </tr>
    <tr>
      <td style="background: none; border: none; text-align: right;">
        <img src="{{ asset('images/approved.webp') }}" alt="">
      </td>
    <tr>
      <td style="background: none; border: none; text-align: right;">PT Perusahaan Gas Negara Tbk</td>
    </tr>
  </table>
</body>

</html>
