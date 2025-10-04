@extends('layouts.masterTemplate')

@section('title', 'management Role User')


@section('content')
    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>

@if ($message = Session::get('success'))
    <div class="alert alert-success">
        <p>{{ $message }}</p>
    </div>
@endif

<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Table Absensi</h6>
        </div>
        <div class="card-body">
     
            <div class="table-responsive">
               <table id="AbsenPegawaiTable" class="display">
                    <thead>
                        <tr>
                        <th>no</th>
                        <th>Nama Pegawai</th>
                        <th>tanggal dan Waktu Absen</th>
                        <th>action</th>
                        </tr>
                    </thead>
                </table>
                  
            </div>
        </div>
    </div>

</div>

@endsection