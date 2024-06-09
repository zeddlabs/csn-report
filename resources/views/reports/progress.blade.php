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
      justify-content: space-between;
      margin-bottom: 20px;
      padding-bottom: 10px;
      border-bottom: 2px solid black;
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
      width: 200px;
      /* Adjust width as needed */
      font-weight: bold;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 20px;
    }

    thead th {
      background-color: #f2f2f2;
      border: 1px solid black;
      text-align: center;
      padding: 8px;
    }

    tbody td,
    tfoot td {
      border: 1px solid black;
      padding: 8px;
      text-align: center;
    }

    tbody td:first-child {
      text-align: left;
    }

    tfoot td {
      font-weight: bold;
      text-align: right;
    }

    .section-header {
      background-color: #f2f2f2;
      font-weight: bold;
    }

    .description {
      text-align: left;
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
        <h3>TAHUN ANGGARAN {{ \Carbon\Carbon::parse($progress->client->project->created_at)->translatedFormat('Y') }}
        </h3>
      </div>
    </div>
    <div class="info">
      <p><span class="label">Nama Kegiatan</span>: {{ $progress->client->project->name }}</p>
      <p><span class="label">Area/Hosbu/Proyek</span>: {{ $progress->client->project->area }}</p>
      <p><span class="label">Pelanggan</span>: {{ $progress->client->name }}</p>
    </div>
    <table>
      <thead>
        <tr>
          <th>NO.</th>
          <th>URAIAN PEKERJAAN</th>
          <th>SAT</th>
          <th>Quantity (Plan)</th>
          <th>Unit Price</th>
          <th>Quantity (Progress)</th>
          <th>Total Price</th>
          <th>Weight Factory</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($workTypes as $workType)
          <tr class="section-header">
            <td>{{ NumConvert::roman($loop->iteration) }}</td>
            <td class="description" style="text-transform: uppercase">{{ $workType->name }}</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
          </tr>
          @foreach ($workType->works as $work)
            <tr>
              <td>{{ $loop->iteration }}</td>
              <td class="description">{{ $work->name }}</td>
              <td></td>
              <td></td>
              <td></td>
              <td></td>
              <td></td>
              <td></td>
            </tr>
            @foreach ($work->materials as $material)
              <tr>
                <td></td>
                <td class="description" style="padding-left: 1.5rem">{{ $material->name }}</td>
                <td>{{ $material->unit }}</td>
                <td>{{ $material->quantity_plan }}</td>
                <td style="text-align: right">Rp {{ number_format($material->unit_price, 2, ',', '.') }}</td>
                <td>{{ $material->quantity_progress }}</td>
                <td style="text-align: right">Rp {{ number_format($material->total_price, 2, ',', '.') }}</td>
                <td>{{ $material->weight_factory }}%</td>
              </tr>
            @endforeach
          @endforeach
        @endforeach
      </tbody>
      <tfoot>
        <tr>
          <td colspan="6">Total Progress Pekerjaan (%)</td>
          <td colspan="2">{{ $progress->total_progress }}%</td>
        </tr>
        <tr>
          <td colspan="6">Biaya Konstruksi</td>
          <td colspan="2">Rp {{ number_format($progress->construction_cost, 2, ',', '.') }}</td>
        </tr>
        <tr>
          <td colspan="6">PPN</td>
          <td colspan="2">Rp {{ number_format($progress->ppn, 2, ',', '.') }}</td>
        </tr>
        <tr>
          <td colspan="6">Total Biaya Konstruksi</td>
          <td colspan="2">Rp {{ number_format($progress->total_construction_cost, 2, ',', '.') }}</td>
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
