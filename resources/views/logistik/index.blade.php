@extends('layouts.masterTemplate')

@section('title', 'Dashboard')

@section('content')
    <div class="container py-5">
        <div class="row mb-4">
            <h1 class="col-md-4 text-center text-md-start mb-0">Data Logistik</h1>
        </div>
        
            @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
        <!-- Table -->
        <div class="table-responsive shadow-sm rounded-lg bg-white">
            <table id="logistikTable" class="table table-bordered table-striped table-hover">
                <thead class="thead-dark">
                    <tr>
                        <th>ID</th>
                        <th>Nama Barang</th>
                        <th>Jumlah Barang</th>
                        <th>Merk Barang</th>
                        <th>Foto</th>
                        <th>Tanggal Diperbarui</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($logistik as $item)
                    <?php
                        $nama_foto = (new App\Models\PengajuanBarang)->getFotoBarang($item->id_master_barang);
                    ?>
                        <tr>
                            <td>{{ $item->id }}</td>
                            <td>{{ $item->nama_barang }}</td>
                            <td>{{ $item->jumlah_barang }}</td>
                            <td>{{ $item->merk_barang }}</td>
                            <td>
                                <img src="{{ asset('storage/master_barang/' . $nama_foto) }}" alt="Foto Barang" class="img-fluid" style="max-width: 100px; cursor: pointer;" data-bs-toggle="modal" data-bs-target="#imageModal{{ $item->id }}">
                            </td>
                            <td>{{ $item->updated_at->format('d-m-Y H:i') }}</td>
                            <td>
                                @if($role_id == 'superadmin')
                                    <a href="{{ route('logistik.edit_master_barang', $item->id_master_barang) }}" class="btn btn-warning btn-sm me-2">Edit</a>
                                    <form action="{{ route('logistik.update_master_barang', $item->id_master_barang) }}" method="POST" class="d-inline" onsubmit="return confirmDelete()">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                    </form>
                                @endif
                                <!-- Button to trigger the modal -->
                                <button class="btn btn-success btn-sm mt-2" data-bs-toggle="modal" data-bs-target="#addToCartModal{{ $item->id }}" data-id="{{ $item->id }}" data-name="{{ $item->nama_barang }}" data-stock="{{ $item->jumlah_barang }}">Masukkan Keranjang</button>
                            </td>
                        </tr>

                        <!-- Modal for viewing image -->
                        <div class="modal fade" id="imageModal{{ $item->id }}" tabindex="-1" aria-labelledby="imageModalLabel{{ $item->id }}" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="imageModalLabel{{ $item->id }}">Foto Barang</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body text-center">
                                        <img src="{{ asset('storage/master_barang/' . $nama_foto) }}" alt="Foto Barang" class="img-fluid" style="max-width: 100%; height: auto;">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Modal for adding to cart (per item) -->
                        <div class="modal fade" id="addToCartModal{{ $item->id }}" tabindex="-1" aria-labelledby="addToCartModalLabel{{ $item->id }}" aria-hidden="true" data-bs-backdrop="static">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="addToCartModalLabel{{ $item->id }}">Masukkan Barang ke Keranjang</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                    <form action="{{ route('logistik.add_to_cart') }}" method="POST">
                                        @csrf
                                        <div class="mb-3">
                                            <label for="itemName{{ $item->id }}" class="form-label">Nama Barang</label>
                                            <input type="text" class="form-control" value="{{ $item->nama_barang }}" disabled>
                                        </div>
                                        <div class="mb-3">
                                            <label for="itemStock{{ $item->id }}" class="form-label">Stok Tersedia</label>
                                            <input type="number" class="form-control" value="{{ $item->jumlah_barang }}" disabled>
                                        </div>
                                        <div class="mb-3">
                                            <label for="quantity{{ $item->id }}" class="form-label">Jumlah yang akan dimasukkan</label>
                                            
                                            <!-- Form Hidden Inputs (for item info) -->
                                            <input type="hidden" name="id" value="{{ $item->id }}">
                                            <input type="hidden" name="id_master_barang" value="{{ $item->id_master_barang }}">
                                            <input type="hidden" name="id_barang" value="{{ $item->id }}">
                                            <input type="hidden" name="stok" value="{{ $item->jumlah_barang }}">
                                            
                                            <!-- Quantity Input -->
                                            <input type="number" name="jumlahAmbil" class="form-control" id="quantity{{ $item->id }}" min="1">
                                            <small id="quantityError{{ $item->id }}" class="text-danger d-none">Jumlah yang dimasukkan melebihi stok.</small>
                                        </div>
                                        <button type="submit" class="btn btn-primary">Masukkan Keranjang</button>
                                    </form>

                                    </div>
                                </div>
                            </div>
                        </div>
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

    <!-- Initialize DataTables -->
    <script>
        $(document).ready(function() {
            $('#logistikTable').DataTable({
                "paging": true,
                "searching": true,
                "ordering": true,
                "info": true,
                "lengthMenu": [10, 25, 50, 100],
                "language": {
                    "search": "Cari:",  // Custom search label
                    "paginate": {
                        "next": "Selanjutnya",
                        "previous": "Sebelumnya"
                    }
                }
            });

            // Handle modal data insertion using jQuery
            $('#addToCartModal').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget);
                var id = button.data('id');
                var name = button.data('name');
                var stock = button.data('stock');

                var modal = $(this);
                modal.find('#itemName').val(name);  // Set the name of the item
                modal.find('#itemStock').val(stock);  // Set the available stock
                modal.find('#quantity').val(1);  // Set initial quantity value
            });
        });

        // JavaScript function to confirm before delete
        function confirmDelete() {
            return confirm("Apakah Anda yakin ingin menghapus data ini?");
        }
    </script>

@endsection
