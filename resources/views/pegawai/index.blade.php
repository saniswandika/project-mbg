@extends('layouts.masterTemplate')

@section('title', 'Pegawai')


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
            <h6 class="m-0 font-weight-bold text-primary">Table Pegawai</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-lg-12 margin-tb">
                    <div class="pull-right">
                        <div class="d-sm-flex align-items-center justify-content-between mb-4">
                            <h1 class="h3 mb-0 text-gray-800"></h1>
                            {{-- @can('pemakaian-create') --}}
                                <a class="btn btn-success" href="{{ route('pegawai.create') }}"> Tambah Pegawai </a>
                            {{-- @endcan --}}
                        </div>
                    
                    </div>
                </div>
            </div>
            <div class="table-responsive">
               <table id="PegawaiTable" class="display">
                    <thead>
                        <tr>
                            <th>no</th>
                            <th>Nama Lengkap</th>
                            <th>Nik</th>
                            <th>No Kartu Keluarga</th>
                            <th>Alamat</th>
                            <th>action</th>
                        </tr>
                    </thead>
                </table>
                  
            </div>
        </div>
    </div>

</div>

@endsection