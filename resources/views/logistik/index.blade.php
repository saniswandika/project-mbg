@extends('layouts.masterTemplate')

@section('title', 'Dashboard')

@section('content')
    <div class="container-fluid py-2">
        <div class="row">
            <div class="ms-3">
            <h3 class="mb-0 h4 font-weight-bolder">Dashboard</h3>
            <p class="mb-4">
                Check the sales, value and bounce rate by country.
            </p>
            </div>
            <h1 class="my-4">Data Logistik</h1>
            
            <table id="logistikTable" class="display table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama Barang</th>
                        <th>Jumlah Barang</th>
                        <th>Merk Barang</th>
                        <th>Status</th>
                        <th>Deskripsi</th>
                        <th>Foto</th>
                        <th>Aktif</th>
                        <th>Tanggal Dibuat</th>
                        <th>Tanggal Diperbarui</th>
                        <th>Tanggal Dihapus</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($logistik as $item)
                        <tr>
                            <td>{{ $item->id }}</td>
                            <td>{{ $item->nama_barang }}</td>
                            <td>{{ $item->jumlah_barang }}</td>
                            <td>{{ $item->merk_barang }}</td>
                            <td>{{ $item->status }}</td>
                            <td>{{ $item->deskripsi }}</td>
                            <td>{{ $item->foto }}</td>
                            <td>{{ $item->is_active ? 'Aktif' : 'Tidak Aktif' }}</td>
                            <td>{{ $item->created_at }}</td>
                            <td>{{ $item->updated_at }}</td>
                            <td>{{ $item->deleted_at }}</td>
                            <td>
                                <a href="{{ route('logistik.edit_master_barang', $item->id_master_barang) }}" class="btn btn-warning btn-sm">Edit</a>
                                <form action="{{ route('logistik.update_master_barang', $item->id_master_barang) }}" method="POST" style="display:inline;">
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

@endsection
