@extends('layouts.masterTemplate')

@section('content')
<div class="container">
    <div class="card mt-4 p-4">
        <h3 class="text-center mb-4">Detail Pegawai</h3>

        <div class="row">
            {{-- Nama Lengkap --}}
            <div class="col-xs-12 col-sm-12 col-md-12">
                <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
                <div class="input-group input-group-outline mb-3">
                    <input type="text" id="nama_lengkap" name="nama_lengkap" 
                        class="form-control" value="{{ $pegawai->nama_lengkap }}" disabled>
                </div>
            </div>

            {{-- NIK --}}
            <div class="col-xs-12 col-sm-12 col-md-12">
                <label for="nik" class="form-label">NIK</label>
                <div class="input-group input-group-outline mb-3">
                    <input type="text" id="nik" name="nik" 
                        class="form-control" value="{{ $pegawai->nik }}" disabled>
                </div>
            </div>

            {{-- No. KK --}}
            <div class="col-xs-12 col-sm-12 col-md-12">
                <label for="no_kk" class="form-label">No. Kartu Keluarga</label>
                <div class="input-group input-group-outline mb-3">
                    <input type="text" id="no_kk" name="no_kk" 
                        class="form-control" value="{{ $pegawai->no_kk }}" disabled>
                </div>
            </div>

            {{-- Alamat --}}
            <div class="col-xs-12 col-sm-12 col-md-12">
                <label for="alamat" class="form-label">Alamat</label>
                <div class="input-group input-group-outline mb-3">
                    <input type="text" id="alamat" name="alamat" 
                        class="form-control" value="{{ $pegawai->alamat }}" disabled>
                </div>
            </div>

            {{-- Foto KTP (hanya tampil gambar) --}}
            <div class="col-xs-12 col-sm-12 col-md-12 mb-3">
                <label class="form-label">Foto KTP</label>
                <div>
                    @if ($pegawai->foto_ktp)
                        <img src="{{ asset('storage/ktp/' . $pegawai->foto_ktp) }}" 
                             alt="Foto KTP" class="img-fluid rounded" 
                             style="max-width: 300px;">
                    @else
                        <p class="text-muted">Tidak ada foto KTP</p>
                    @endif
                </div>
            </div>

            {{-- No BPJS --}}
            <div class="col-xs-12 col-sm-12 col-md-12">
                <label for="no_bpjs" class="form-label">No. BPJS</label>
                <div class="input-group input-group-outline mb-3">
                    <input type="text" id="no_bpjs" name="no_bpjs" 
                        class="form-control" value="{{ $pegawai->no_bpjs }}" disabled>
                </div>
            </div>

            {{-- No Rekening --}}
            <div class="col-xs-12 col-sm-12 col-md-12">
                <label for="no_rekening" class="form-label">No. Rekening</label>
                <div class="input-group input-group-outline mb-3">
                    <input type="text" id="no_rekening" name="no_rekening" 
                        class="form-control" value="{{ $pegawai->no_rekening }}" disabled>
                </div>
            </div>

            {{-- Bank --}}
            <div class="col-xs-12 col-sm-12 col-md-12">
                <label for="bank" class="form-label">Bank</label>
                <div class="input-group input-group-outline mb-3">
                    <input type="text" id="bank" name="bank" 
                        class="form-control" value="{{ $pegawai->bank }}" disabled>
                </div>
            </div>

            {{-- Atas Nama Rekening --}}
            <div class="col-xs-12 col-sm-12 col-md-12">
                <label for="atas_nama_rekening" class="form-label">Atas Nama Rekening</label>
                <div class="input-group input-group-outline mb-3">
                    <input type="text" id="atas_nama_rekening" name="atas_nama_rekening" 
                        class="form-control" value="{{ $pegawai->atas_nama_rekening }}" disabled>
                </div>
            </div>

            <div class="text-center mt-4">
                <a href="{{ url('/pegawai') }}" class="btn btn-secondary">Kembali</a>
            </div>
        </div>
    </div>
</div>
@endsection
