@extends('layouts.masterTemplate')

@section('title', 'Pengajuan Barang')

@section('content')
<div class="container mx-auto py-8">
    <div class="row">
        <div class="ms-3">
@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

            
            <h1 class="text-3xl font-semibold mb-4">Daftar Pengajuan Barang</h1>
            <div class="p-6 bg-white shadow-md rounded-lg border border-gray-200">
                <table class="display table table-striped" id="barangTable">        
                                    <thead>
                                        <tr>
                                            <th>Nama barang</th>
                                            <th>Jumlah barang</th>
                                            <th>Setatus</th>
                                            <th>Keterangan</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                        @foreach($pengajuanBarang as $row)
                            @php 
                                $array_barang = explode('^', $row->id_barang);
                            @endphp
                        @endforeach
                                    <tbody>
                                        @foreach($array_barang as $barang)
                                        @php 
                                            // Ambil data barang dan jumlahnya
                                            $id_barang = (new App\Models\PengajuanBarang)->getIdBarang($barang);
                                            $nama_barang = (new App\Models\PengajuanBarang)->getNamaBarang($id_barang->id_barang);
                                            $color_ttp = '</span>';
                                            if($id_barang->status == '1'){
                                                $text = 'Menunggu Kepastian';
                                                $color = '<span style="color:orange">';
                                            }elseif($id_barang->status == '2'){
                                                $text = 'Diterima';
                                                $color = '<span style="color:green">';
                                            }elseif($id_barang->status == '3'){
                                                $text = 'Ditolak';
                                                $color = '<span style="color:red">';
                                            }
                                        @endphp
                                        <tr>
                                            <td>{{ $nama_barang }}</td>
                                            <td>{{ $id_barang->jumlah }}</td>
                                            <td><?= $color ?>{{ $text }} <?= $color_ttp ?></td>
                                            <td>{{ $id_barang->deskripsi }}</td>
                                            <td>
                                                <!-- Link untuk Revisi dengan route yang benar -->
                                                <button class="btn btn-warning" data-toggle="modal" data-target="#revisiModal{{ $id_barang->id }}">Revisi</button>

                                                <!-- Modal untuk revisi jumlah barang -->
                                                <div class="modal fade" id="revisiModal{{ $id_barang->id }}" tabindex="-1" role="dialog" aria-labelledby="revisiModalLabel" aria-hidden="true">
                                                    <div class="modal-dialog" role="document">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="revisiModalLabel">Revisi Jumlah Barang</h5>
                                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <!-- Form untuk mengubah jumlah barang -->
                                                                <form action="{{ route('logistik.revisi_pengajuan_barang', ['id' => $id_barang->id]) }}" method="POST">
                                                                    @csrf
                                                                    @method('PUT')
                                                                    <div class="form-group">
                                                                        <label for="jumlah">Jumlah Barang</label>
                                                                        <input type="number" class="form-control" name="jumlah" id="jumlah" value="{{ $id_barang->jumlah }}" required>
                                                                        <label for="jumlah">Keterangan</label>
                                                                        <textarea type="number" class="form-control" name="keterangan"></textarea>
                                                                    </div>
                                                                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Tombol Hapus Barang -->
                                                <button class="btn btn-danger" data-toggle="modal" data-target="#hapusModal{{ $id_barang->id }}">Hapus</button>

                                                <!-- Modal konfirmasi hapus -->
                                                <div class="modal fade" id="hapusModal{{ $id_barang->id }}" tabindex="-1" role="dialog" aria-labelledby="hapusModalLabel" aria-hidden="true">
                                                    <div class="modal-dialog" role="document">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="hapusModalLabel">Konfirmasi Penghapusan Barang</h5>
                                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>
                                                            </div>
                                                            <div class="modal-body">
                                                                Apakah Anda yakin ingin menghapus barang ini?
                                                            </div>
                                                            <div class="modal-footer">
                                                                <form action="{{ route('logistik.hapus_pengajuan_barang', ['id' => $id_barang->id, 'id_home' => $id]) }}" method="POST">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                                                    <button type="submit" class="btn btn-danger">Hapus</button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap CSS -->
<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
<!-- jQuery, Popper.js, and Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

@endsection
