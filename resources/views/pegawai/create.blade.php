@extends('layouts.masterTemplate')

@section('content')
<div class="container">
     <div class="card mt-4 p-4">
        <div class="row">
                <h3 class="text-center mb-4">Tambah Pegawai</h3>

                <form action="{{ route('pegawai.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    {{-- <div class="col-xs-12 col-sm-12 col-md-12">

                        <label for="user_id" class="form-label">User</label>

                        <div class="input-group input-group-outline mb-3">
                            <input type="number" name="user_id" id="user_id" class="form-control" placeholder="ID User">
                        </div>
                    </div> --}}
                    <div class="col-xs-12 col-sm-12 col-md-12">

                        <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
                        <div class="input-group input-group-outline mb-3">
                            <input type="text" name="nama_lengkap" id="nama_lengkap" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-12 col-md-12">
                        <label for="nik" class="form-label">NIK</label>
                        <div class="input-group input-group-outline mb-3">
                            <input type="text" name="nik" id="nik" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-12 col-md-12">
                        <label for="no_kk" class="form-label">No. Kartu Keluarga</label>
                        <div class="input-group input-group-outline mb-3">
                            <input type="text" name="no_kk" id="no_kk" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-12 col-md-12">
                        <label for="alamat" class="form-label">Alamat</label>
                        <div class="input-group input-group-outline mb-3">
                            <textarea name="alamat" id="alamat" class="form-control"></textarea>
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-12 col-md-12">
                        <label for="foto_ktp" class="form-label">Foto KTP</label>
                        <div class="input-group input-group-outline mb-3">
                            <input type="file" name="foto_ktp" id="foto_ktp" class="form-control">
                        </div>
                    </div>

                    <div class="col-xs-12 col-sm-12 col-md-12">
                        <label for="no_bpjs" class="form-label">No. BPJS</label>
                        <div class="input-group input-group-outline mb-3">
                            <input type="text" name="no_bpjs" id="no_bpjs" class="form-control">
                    </div>
                    <div class="col-xs-12 col-sm-12 col-md-12">
                        <label for="no_rekening" class="form-label">No. Rekening</label>
                        <div class="input-group input-group-outline mb-3">
                            <input type="text" name="no_rekening" id="no_rekening" class="form-control">
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-12 col-md-12">
                        <label for="bank" class="form-label">Bank</label>
                        <div class="input-group input-group-outline mb-3">
                            <input type="text" name="bank" id="bank" class="form-control">
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-12 col-md-12">
                            <label for="atas_nama_rekening" class="form-label">Atas Nama Rekening</label>

                        <div class="input-group input-group-outline mb-3">
                            <input type="text" name="atas_nama_rekening" id="atas_nama_rekening" class="form-control">
                        </div>
                    </div>
                    <div class="text-center">
                        <button type="submit" class="btn btn-primary">Submit</button>
                        <a href="{{ url('/pegawai') }}" class="btn btn-secondary">Kembali</a>

                    </div>
                </form>
        </div>
     </div>
</div>
@endsection
