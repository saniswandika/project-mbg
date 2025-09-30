@extends('layouts.masterTemplate')

@section('title', 'History Keranjang Belanja')

@section('content')
<div class="container py-5">
    <div class="row mb-4">
        <span class="col-md-4 text-center text-md-start mb-0 display-6 font-weight-bold text-primary">History Pengambilan</span>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong>Success!</strong> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Error!</strong> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Form Filter Tanggal -->
    <div class="row mb-4">
        <div class="col-12 text-center">
            <h4 class="text-muted">Filter Berdasarkan Tanggal</h4>
            <form method="GET" action="{{ route('logistik.history_keranjang') }}" class="form-inline justify-content-center">
                @csrf
                <div class="form-group mx-2">
                    <label for="start_date" class="form-label">Dari:</label>
                    <input type="date" name="start_date" id="start_date" class="form-control form-control-lg" value="{{ old('start_date', $old_start) }}">
                </div>
                <div class="form-group mx-2">
                    <label for="end_date" class="form-label">Ke:</label>
                    <input type="date" name="end_date" id="end_date" class="form-control form-control-lg" value="{{ old('end_date', $old_end) }}">
                </div>
                <button type="submit" class="btn btn-success btn-lg mx-2">Filter</button>
            </form>
        </div>
    </div>

    <!-- Table History Keranjang -->
    <div class="table-responsive shadow-lg rounded-lg bg-white p-3">
        <table class="table table-bordered table-striped table-hover" id="cartTable">
            <thead class="thead-dark bg-success text-white">
                <tr>
                    <th>ID</th>
                    <th>Nama Barang</th>
                    <th>Jumlah Barang</th>
                    @if($role == 'admin' || $role == 'superadmin')
                    <th>User</th>
                    @endif
                    <th>Tanggal Diperbarui</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($cartItems as $item)
                @php 
                    $nama_barang = (new App\Models\PengajuanBarang)->getNamaBarang($item->id_master_barang);
                    $nama_user = (new App\Models\PengajuanBarang)->getNamaUser($item->id_user);
                @endphp
                <tr class="text-dark">
                    <td>{{ $item->id }}</td>
                    <td>{{ $nama_barang }}</td>
                    <td>{{ $item->jumlah_barang }}</td>
                    @if($role == 'admin' || $role == 'superadmin')
                    <td>{{ $nama_user }}</td>
                    @endif
                    <td>{{ $item->updated_at->format('d F Y H:i') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

    <!-- Add CSS for DataTables -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Add DataTables Script -->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#cartTable').DataTable({
                "paging": true,
                "searching": true,
                "ordering": true,
                "info": true,
                "lengthMenu": [10, 25, 50, 100],  // Menampilkan pilihan jumlah data per halaman
                "language": {
                    "search": "Cari:",  // Label untuk kolom pencarian
                    "paginate": {
                        "next": "Selanjutnya",
                        "previous": "Sebelumnya"
                    }
                }
            });
        });
    </script>

@endsection
