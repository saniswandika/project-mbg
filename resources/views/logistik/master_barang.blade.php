@extends('layouts.masterTemplate')

@section('title', 'Dashboard')

@section('content')
    <div class="container-fluid py-2">
        <div class="row">
            <div class="ms-3">
            <h1 class="my-4">Master Barang</h1>
            @if($role_id == 'superadmin' || $role_id == 'admin')
            <a href="{{ route('tambah_master_barang') }}"><button class="btn btn-success">Tambah Barang</button></a>
            @endif

            <table id="logistikTable" class="display table table-striped">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Barang</th>
                        <th>Merk Barang</th>
                        <th>Deskripsi</th>
                        <th>Foto</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($barangPengajuan as $item)
                        <tr>
                            <td>{{ $item->id }}</td>
                            <td>{{ $item->nama_barang }}</td>
                            <td>{{ $item->merk_barang }}</td>
                            <td>{{ $item->deskripsi }}</td>
                            <td>{{ $item->foto }}</td>
                            <td>
                                <a href="{{ route('logistik.edit', $item->id) }}" class="btn btn-warning btn-sm">Edit</a>
                                <form action="{{ route('logistik.destroy', $item->id) }}" method="POST" style="display:inline;">
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
