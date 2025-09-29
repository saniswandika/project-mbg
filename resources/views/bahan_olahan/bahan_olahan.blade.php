@extends('layouts.masterTemplate')

@section('title', 'Dashboard')

@section('content')
    <div class="container-fluid py-2">
        <div class="row">
            <div class="ms-3">
                <h1 class="my-4">Master bahan</h1>
                @if($role_id == 'superadmin' || $role_id == 'admin')
                    <a href="{{ route('bahan_olahan.tambah_bahan_olahan') }}">
                        <button class="btn btn-success">Tambah bahan</button>
                    </a>
                @endif

                <table id="logistikTable" class="display table table-striped">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama bahan</th>
                            <th>Merk bahan</th>
                            <th>Deskripsi</th>
                            <th>Foto</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($bahanPengajuan as $item)
                            <tr>
                                <td>{{ $item->id }}</td>
                                <td>{{ $item->nama_bahan }}</td>
                                <td>{{ $item->merk_bahan }}</td>
                                <td>{{ $item->deskripsi }}</td>
                                <td>
                                    <!-- Gambar kecil di tabel -->
                                    <img src="{{ asset('storage/bahan_olahan/'.$item->foto) }}" alt="Foto bahan" style="max-height:100px; max-width:200px;" data-bs-toggle="modal" data-bs-target="#fotoModal" class="img-thumbnail" id="previewImage" data-image="{{ asset('storage/bahan_olahan/'.$item->foto) }}">

                                    <!-- Modal -->
                                    <div class="modal fade" id="fotoModal" tabindex="-1" aria-labelledby="fotoModalLabel" aria-hidden="true">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <center>
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="fotoModalLabel">Foto bahan</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <!-- Gambar besar akan tampil di sini -->
                                                    <img id="modalImage" src="" alt="Foto bahan" class="img-fluid" />
                                                </div>
                                                </center>
                                            </div>
                                        </div>
                                    </div>

                                    <script>
                                        // JavaScript untuk menangani klik gambar dan menampilkan di modal
                                        document.addEventListener("DOMContentLoaded", function() {
                                            const previewImage = document.querySelectorAll('#previewImage');
                                            previewImage.forEach(image => {
                                                image.addEventListener('click', function() {
                                                    const imageUrl = this.getAttribute('data-image'); // Ambil URL gambar
                                                    const modalImage = document.getElementById('modalImage'); // Ambil elemen gambar modal
                                                    modalImage.src = imageUrl; // Ubah src gambar modal
                                                });
                                            });
                                        });
                                    </script>
                                </td>
                                <td>
                                    <a href="{{ route('bahan_olahan.edit_master_bahan', $item->id) }}" class="btn btn-warning btn-sm">Edit</a>
                                    <form action="{{ route('bahan_olahan.destroy_master_bahan', $item->id) }}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Link CSS Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Script JavaScript Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

@endsection
