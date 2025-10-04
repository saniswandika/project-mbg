@extends('layouts.masterTemplate')

@section('content')
<div class="container">
    <div class="card mt-4 p-4">
        <h3 class="text-center mb-4">Edit Pegawai</h3>

        <form action="{{ route('pegawai.update', $pegawai->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT') {{-- Untuk HTTP PUT Method --}}

            <div class="row">
                {{-- Nama Lengkap --}}
                <div class="col-xs-12 col-sm-12 col-md-12">
                    <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
                    <div class="input-group input-group-outline mb-3">
                        <input type="text" id="nama_lengkap" name="nama_lengkap" 
                               class="form-control" value="{{ old('nama_lengkap', $pegawai->nama_lengkap) }}" required>
                    </div>
                </div>

                {{-- NIK --}}
                <div class="col-xs-12 col-sm-12 col-md-12">
                    <label for="nik" class="form-label">NIK</label>
                    <div class="input-group input-group-outline mb-3">
                        <input type="text" id="nik" name="nik" 
                               class="form-control" value="{{ old('nik', $pegawai->nik) }}" required>
                    </div>
                </div>

                {{-- No. KK --}}
                <div class="col-xs-12 col-sm-12 col-md-12">
                    <label for="no_kk" class="form-label">No. Kartu Keluarga</label>
                    <div class="input-group input-group-outline mb-3">
                        <input type="text" id="no_kk" name="no_kk" 
                               class="form-control" value="{{ old('no_kk', $pegawai->no_kk) }}" required>
                    </div>
                </div>

                {{-- Alamat --}}
                <div class="col-xs-12 col-sm-12 col-md-12">
                    <label for="alamat" class="form-label">Alamat</label>
                    <div class="input-group input-group-outline mb-3">
                        <input type="text" id="alamat" name="alamat" 
                               class="form-control" value="{{ old('alamat', $pegawai->alamat) }}">
                    </div>
                </div>

                {{-- Foto KTP --}}
                <div class="col-xs-12 col-sm-12 col-md-12 mb-3">
                    <label for="foto_ktp" class="form-label">Foto KTP</label>
                    <div class="mb-2">
                        @if ($pegawai->foto_ktp)
                            <img src="{{ asset('storage/ktp/' . $pegawai->foto_ktp) }}" 
                                 alt="Foto KTP" class="img-fluid rounded mb-2" 
                                 style="max-width: 300px;">
                        @endif
                    </div>
                    <div class="input-group input-group-outline">
                        <input type="file" id="foto_ktp" name="foto_ktp" class="form-control">
                    </div>
                    <small class="text-muted">Biarkan kosong jika tidak ingin mengganti foto.</small>
                </div>

                {{-- No BPJS --}}
                <div class="col-xs-12 col-sm-12 col-md-12">
                    <label for="no_bpjs" class="form-label">No. BPJS</label>
                    <div class="input-group input-group-outline mb-3">
                        <input type="text" id="no_bpjs" name="no_bpjs" 
                               class="form-control" value="{{ old('no_bpjs', $pegawai->no_bpjs) }}">
                    </div>
                </div>

                {{-- No Rekening --}}
                <div class="col-xs-12 col-sm-12 col-md-12">
                    <label for="no_rekening" class="form-label">No. Rekening</label>
                    <div class="input-group input-group-outline mb-3">
                        <input type="text" id="no_rekening" name="no_rekening" 
                               class="form-control" value="{{ old('no_rekening', $pegawai->no_rekening) }}">
                    </div>
                </div>

                {{-- Bank --}}
                <div class="col-xs-12 col-sm-12 col-md-12">
                    <label for="bank" class="form-label">Bank</label>
                    <div class="input-group input-group-outline mb-3">
                        <input type="text" id="bank" name="bank" 
                               class="form-control" value="{{ old('bank', $pegawai->bank) }}">
                    </div>
                </div>

                {{-- Atas Nama Rekening --}}
                <div class="col-xs-12 col-sm-12 col-md-12">
                    <label for="atas_nama_rekening" class="form-label">Atas Nama Rekening</label>
                    <div class="input-group input-group-outline mb-3">
                        <input type="text" id="atas_nama_rekening" name="atas_nama_rekening" 
                               class="form-control" value="{{ old('atas_nama_rekening', $pegawai->atas_nama_rekening) }}">
                    </div>
                </div>

                <div class="text-center mt-4">
                    <button type="submit" class="btn btn-primary">Update</button>
                    <a href="{{ url('/pegawai') }}" class="btn btn-secondary">Kembali</a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
