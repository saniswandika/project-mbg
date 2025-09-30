@extends('layouts.masterTemplate')

@section('title', 'Keranjang Belanja')

@section('content')
<div class="container py-5">
    <div class="row mb-4">
        <h1 class="col-md-4 text-center text-md-start mb-0">Keranjang Belanja</h1>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <!-- Table Keranjang -->
    <div class="table-responsive shadow-sm rounded-lg bg-white">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama Barang</th>
                    <th>Jumlah Barang</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($cartItems as $item)
                @php 
                    $nama_barang = (new App\Models\PengajuanBarang)->getNamaBarang($item->id_master_barang);
                @endphp 
                    <tr>
                        <td>{{ $item->id }}</td>
                        <td>{{ $nama_barang }}</td>
                        <td>{{ $item->jumlah_barang }}</td>
                        <td>
                            @if($item->status == NULL)
                                <form action="{{ route('logistik.hapus_keranjang', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirmDelete()">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                </form>
                            @endif
                            @if($user)
                                <span style="color:pink;">Menunggu Approve</span>
                            @endif
                            @if($admin && ($role == 'admin' || $role == 'superadmin'))
                                <form action="{{ route('logistik.status_change') }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="id_user" value="{{ Auth::id() }}">
                                    <input type="hidden" name="id" value="{{ $item->id }}">
                                    <input type="hidden" name="status" value="2">
                                    <button type="submit" class="btn btn-success btn-sm">Approve</button>
                                </form>
                                <form action="{{ route('logistik.status_change') }}" method="POST" class="d-inline" onsubmit="return confirmDelete()">
                                    @csrf
                                    <input type="hidden" name="id_user" value="{{ Auth::id() }}">
                                    <input type="hidden" name="id" value="{{ $item->id }}">
                                    <input type="hidden" name="status" value="3">
                                    <button type="submit" class="btn btn-danger btn-sm">Reject</button>
                                </form>
                            @endif
                            @if($serahkan)
                                <form action="{{ route('logistik.status_change') }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="id_user" value="{{ Auth::id() }}">
                                    <input type="hidden" name="id" value="{{ $item->id }}">
                                    <input type="hidden" name="status" value="4">
                                    <button type="submit" class="btn btn-success btn-sm">Terima</button>
                                </form>
                                <form action="{{ route('logistik.status_change') }}" method="POST" class="d-inline" onsubmit="return confirmDelete()">
                                    @csrf
                                    <input type="hidden" name="id_user" value="{{ Auth::id() }}">
                                    <input type="hidden" name="id" value="{{ $item->id }}">
                                    <input type="hidden" name="status" value="5">
                                    <button type="submit" class="btn btn-danger btn-sm">Tidak Menerima</button>
                                </form>
                            @endif
                            @if($item->status == '5' && ($role == 'admin' || $role == 'superadmin' ))
                                <form action="{{ route('logistik.status_change') }}" method="POST" class="d-inline" onsubmit="return confirmDelete()">
                                    @csrf
                                    <input type="hidden" name="id_user" value="{{ Auth::id() }}">
                                    <input type="hidden" name="id" value="{{ $item->id }}">
                                    <input type="hidden" name="status" value="2">
                                    <button type="submit" class="btn btn-danger btn-sm">Konfirmasi Ulang</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@if($cartItems->isNotEmpty())
    @if($cartItems[0]['status'] == NULL)
    <form action="{{ route('logistik.ajukan_pengambilan') }}" method="POST">
        @csrf
        <input type="hidden" name="id_user" value="{{ Auth::id() }}">
        <input type="submit" class="btn btn-primary" name="kirim" value="Ajukan">
    </form>
    @endif
@endif

<script>
    function confirmDelete() {
        return confirm("Apakah Anda yakin ingin menghapus data ini?");
    }
</script>

@endsection
