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
            <form method="GET" action="{{ route('logistik.log_pengajuan_barang') }}" class="form-inline justify-content-center">
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
                            <th>No</th>
                            <th>Pengajuan</th>
                            <th>Tanggal</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pengajuanBarang as $row)
                        @php
                        // Format tanggal
                        $status = $row->status;
                        $date = $row->created_at;
                        $formattedDate = \Carbon\Carbon::parse($date)->format('d F Y G:i');

                        // Membagi ID barang
                        $array_barang = explode('^', $row->id_barang);

                        $id_barang = explode('^', $row->id_barang);
                        foreach ($id_barang as $key) {
                            $barangId = $key;
                        }
                        $model = (new App\Models\PengajuanBarang);
                        $pengajuan_bahans = $model->getIdBarang($barangId);
                        $id_akutansi = $pengajuan_bahans->id_akutansi;
                        $id_admin = $pengajuan_bahans->id_admin;
                        $id_superadmin = $pengajuan_bahans->id_superadmin;

                        // Status
                        $approve = '';
                        if ($status == '1') {
                            if ($id_akutansi != NULL) {
                                $approve = $model->getNamaUser($id_akutansi);
                            } else {
                                $approve = 'Tidak Ada';
                            }
                        }
                        if ($status == '2') {
                            if ($id_admin != NULL) {
                                $approve = $model->getNamaUser($id_admin);
                            } else {
                                $approve = 'Tidak Ada';
                            }
                        }
                        if ($status == '3') {
                            if ($id_superadmin != NULL) {
                                $approve = $model->getNamaUser($id_superadmin);
                            } else {
                                $approve = 'Tidak Ada';
                            }
                        }
                        if ($status == '4') {
                            if ($id_admin != NULL) {
                                $approve = $model->getNamaUser($id_admin);
                            } else {
                                $approve = 'Tidak Ada';
                            }
                        }
                        if ($status == '5') {
                            if ($id_superadmin != NULL) {
                                $approve = $model->getNamaUser($id_superadmin);
                            } else {
                                $approve = 'Tidak Ada';
                            }
                        }
                        if ($status == '6') {
                                $approve = 'Sudah Selesai';
                        }
                        $rejected = false;
                        if($row->status == 1){
                        $status = 'Akutansi';
                        } elseif($row->status == 2) {
                        $status = 'Admin';
                        } elseif($row->status == 3) {
                        $status = 'Superadmin';
                        } elseif($row->status == 4) {
                        $status = 'admin';
                        } elseif($row->status == 5) {
                        $status = 'Superadmin';
                        } elseif($row->status == 6) {
                        $status = 'selesai';
                        }
                        if($row->deleted_at != NULL){
                            $rejected = true;
                        }
                        @endphp
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <!-- Menampilkan tabel barang -->
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Nama barang</th>
                                            <th>Jumlah barang</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($array_barang as $barang)
                                        @php
                                        // Ambil data barang dan jumlahnya
                                        $id_barang = (new App\Models\PengajuanBarang)->getIdBarang($barang);
                                        $nama_barang = (new App\Models\PengajuanBarang)->getNamaBarang($id_barang->id_barang);
                                        @endphp
                                        <tr>
                                            <td>{{ $nama_barang }}</td>
                                            <td>{{ $id_barang->jumlah }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </td>
                            <td>{{ $formattedDate }}</td>
                            <td>
                                @if($rejected)
                                    <span style="color:red"> Ditolak </span>
                                @else
                                    <span style="color:green">{{ $approve }}</span>
                                @endif
                            </td>
                            <td>

                            </td>
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
