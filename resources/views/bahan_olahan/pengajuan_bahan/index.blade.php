@extends('layouts.masterTemplate')

@section('title', 'Pengajuan Bahan')

@section('content')
<div class="container mx-auto py-8">
    <div class="row">
        <div class="ms-3">
            <h1 class="text-3xl font-semibold mb-4">Pengajuan Stok Bahan</h1>
            @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <form action="{{ route('bahan_olahan.proses_pengajuan_bahan') }}" method="POST" class="p-6 bg-white shadow-md rounded-lg border border-gray-200">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                    <!-- Pilih Bahan dan Masukkan Jumlah -->
                    <div class="col-span-1">
                        <div class="form-group">
                            <label for="bahan" class="block text-sm font-medium text-gray-700 mb-2">Pilih Bahan:</label>
                            <select name="bahan_ids[]" id="bahan" class="form-control mt-1 block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                @foreach ($masterbahan as $item)
                                <option value="{{ $item->id }}">{{ $item->nama_bahan }} ({{ $item->merk_bahan }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>


                    <div class="col-span-1">
                        <div class="form-group">
                            <label for="jumlah" class="block text-sm font-medium text-gray-700">Jumlah Stok:</label>
                            <input type="number" name="jumlah_stok" class="form-control mt-1 block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" id="jumlah" min="1" placeholder="Masukkan jumlah stok" />
                        </div>
                    </div>
                </div>

                <!-- Button untuk Menambahkan Bahan ke Daftar Pengajuan -->
                <div class="col-span-1 text-center mb-4">
                    <button type="button" class="btn btn-info bg-blue-500 text-white py-2 px-6 rounded-md hover:bg-blue-600" id="addItemButton">Tambah Bahan</button>
                </div>

                <!-- Daftar Bahan yang Diajukan -->
                <div class="col-span-1">
                    <h4 class="mt-4 text-xl font-semibold">Bahan yang Diajukan:</h4>
                    <!-- <table id="logistikTable" class="display table table-striped"> -->
                    <table class="display table table-striped" id="BahanTable">
                        <thead>
                            <tr>
                                <th class="border border-gray-300 py-2 px-4">Bahan</th>
                                <th class="border border-gray-300 py-2 px-4">Jumlah</th>
                                <th class="border border-gray-300 py-2 px-4">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Items akan ditambahkan secara dinamis menggunakan JavaScript -->
                        </tbody>
                    </table>
                </div>

                <!-- Button Submit -->
                <div class="col-xs-12 col-sm-12 col-md-12 text-center mt-6">
                    <button type="submit" class="btn btn-success">Ajukan Stok</button>
                </div>
            </form>

            <h1 class="text-3xl font-semibold mb-4">Daftar Pengajuan Bahan</h1>
            <div class="p-6 bg-white shadow-md rounded-lg border border-gray-200">
                <table class="display table table-striped" id="BahanTable">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Pengajuan</th>
                            <th>Tanggal</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pengajuanbahan as $row)
                        @php
                        // Format tanggal
                        $date = $row->created_at;
                        $formattedDate = \Carbon\Carbon::parse($date)->format('d F Y G:i');

                        // Membagi ID Bahan
                        $array_Bahan = explode('^', $row->id_bahan);

                        // Status
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
                        @endphp
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <!-- Menampilkan tabel Bahan -->
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Nama Bahan</th>
                                            <th>Jumlah Bahan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($array_Bahan as $Bahan)
                                        @php
                                        // Ambil data Bahan dan jumlahnya
                                        $id_Bahan = '';
                                        $nama_bahan = '';
                                        if(!empty($Bahan)){
                                            $id_Bahan = (new App\Models\PengajuanBahan)->getIdBahan($Bahan);
                                            if(!empty($id_Bahan->id_bahan)){
                                            $nama_Bahan = (new App\Models\PengajuanBahan)->getNamaBahan($id_Bahan->id_bahan);
                                            }
                                        }
                                        if(!empty($id_Bahan->jumlah)){
                                            $jumlah = $id_Bahan->jumlah;
                                        }else{
                                            $jumlah = 0;
                                        }
                                        @endphp
                                        <tr>
                                            <td>{{ $nama_Bahan }}</td>
                                            <td>{{ $jumlah }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </td>
                            <td>{{ $formattedDate }}</td>
                            <td><span style="color:green">{{ $status }}</span></td>
                            <td>
                                @php
                                // Menentukan kondisi untuk masing-masing status dan peran
                                $canApprove = false;
                                $canReject = false;
                                $canDetail = false;
                                if (($row->status == '1' && $id_Bahan->id_akutansi == $userid) || ($row->status == '1' && $id_Bahan->id_superadmin == $userid)) {
                                $canDetail = true;
                                $canApprove = true;
                                $canReject = true;
                                } elseif (($row->status == '2' && $id_Bahan->id_admin == $userid) || ($row->status == '2' && $id_Bahan->id_superadmin == $userid)) {
                                $canDetail = true;
                                $canApprove = true;
                                $canReject = true;
                                } elseif (($row->status == '3' && $id_Bahan->id_superadmin == $userid) || ($row->status == '3' && $id_Bahan->id_superadmin == $userid)) {
                                $canDetail = true;
                                $canApprove = true;
                                $canReject = true;
                                } elseif (($row->status == '4' && $id_Bahan->id_admin == $userid) || ($row->status == '4' && $id_Bahan->id_superadmin == $userid)) {
                                $canDetail = true;
                                $canApprove = true;
                                $canReject = true;
                                } elseif (($row->status == '5' && $id_Bahan->id_superadmin == $userid) || ($row->status == '5' && $id_Bahan->id_superadmin == $userid)) {
                                $canDetail = true;
                                $canApprove = true;
                                $canReject = true;
                                } else {
                                $canDetail = false;
                                $canApprove = false;
                                $canReject = false;
                                }
                                @endphp

                                <!-- Tombol Detail -->
                                @if($canDetail)
                                <a href="{{ route('bahan_olahan.detail_pengajuan_bahan', ['id' => $row->id]) }}">
                                    <button class="btn btn-primary">Detail</button>
                                </a>
                                @endif

                                <!-- Tombol Approve -->
                                @if($canApprove)
                                <!-- Tombol Approve -->
                                <button class="btn btn-primary" data-toggle="modal" data-target="#approveModal{{ $row->id }}">Approve</button>

                                <!-- Modal untuk verifikasi password -->
                                <div class="modal fade" id="approveModal{{ $row->id }}" tabindex="-1" role="dialog" aria-labelledby="approveModalLabel" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="approveModalLabel">Verifikasi Password</h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                <!-- Form untuk memverifikasi password -->
                                                <form action="{{ route('logistik.verify_approve', ['id' => $row->id]) }}" method="POST" enctype="multipart/form-data">
                                                    @csrf
                                                    <div class="form-group">
                                                        <label for="password">Masukkan Password</label>
                                                        <input type="password" class="form-control" name="password" id="password" required>
                                                    </div>

                                                    @if($row->status == 4)
                                                    <!-- Upload Bukti Pembayaran dan Struk Pembayaran -->
                                                    <div class="form-group">
                                                        <label for="payment_proof">Bukti Pembayaran</label>
                                                        <input type="file" class="form-control" name="payment_proof" id="payment_proof" required>
                                                        <div id="payment_proof_preview" style="margin-top: 10px;">
                                                            <!-- Preview bukti pembayaran -->
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="receipt_proof">Struk Pembayaran</label>
                                                        <input type="file" class="form-control" name="receipt_proof" id="receipt_proof" required>
                                                        <div id="receipt_proof_preview" style="margin-top: 10px;">
                                                            <!-- Preview struk pembayaran -->
                                                        </div>
                                                    </div>

                                                    <!-- Upload Foto Bukti Bahan -->
                                                    <div class="form-group">
                                                        <label for="item_photo">Foto Bukti Bahan</label>
                                                        <input type="file" class="form-control" name="item_photo" id="item_photo" required>
                                                        <div id="item_photo_preview" style="margin-top: 10px;">
                                                            <!-- Preview foto bukti Bahan -->
                                                        </div>
                                                    </div>
                                                    @endif

                                                    <button type="submit" class="btn btn-primary">Verifikasi</button>
                                                </form>
                                            </div>


                                        </div>
                                    </div>
                                </div>
                                @endif


                                <!-- Tombol Reject -->
                                @if($canReject)
                                <a href="{{ route('bahan_olahan.reject_pengajuan_bahan', ['id' => $row->id]) }}">
                                    <button class="btn btn-danger">Reject</button>
                                </a>
                                @endif

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
<style>
    /* Menambahkan margin untuk memastikan modal berada di tengah */
    .modal-dialog {
        max-width: 500px;
        /* Ukuran modal */
        margin: 100px auto;
        /* Menjaga modal berada di tengah halaman */
    }

    /* Menambahkan styling untuk tombol close */
    .modal-header .close {
        color: #000;
        /* Warna untuk tombol close */
        font-size: 30px;
    }

    /* Gaya untuk form input */
    .modal-body input[type="password"] {
        font-size: 18px;
    }
</style>
<script>
    // Preview Bukti Pembayaran
    document.getElementById('payment_proof').addEventListener('change', function(event) {
        var file = event.target.files[0];
        var preview = document.getElementById('payment_proof_preview');

        // Cek jika file ada dan tipe file adalah gambar
        if (file && file.type.startsWith('image')) {
            var reader = new FileReader();

            reader.onload = function(e) {
                preview.innerHTML = '<img src="' + e.target.result + '" class="img-fluid" style="max-height: 200px; max-width: 100%;">';
            };

            reader.readAsDataURL(file);
        } else {
            preview.innerHTML = '<p>File yang dipilih bukan gambar</p>';
        }
    });

    // Preview Struk Pembayaran
    document.getElementById('receipt_proof').addEventListener('change', function(event) {
        var file = event.target.files[0];
        var preview = document.getElementById('receipt_proof_preview');

        // Cek jika file ada dan tipe file adalah gambar
        if (file && file.type.startsWith('image')) {
            var reader = new FileReader();

            reader.onload = function(e) {
                preview.innerHTML = '<img src="' + e.target.result + '" class="img-fluid" style="max-height: 200px; max-width: 100%;">';
            };

            reader.readAsDataURL(file);
        } else {
            preview.innerHTML = '<p>File yang dipilih bukan gambar</p>';
        }
    });

    // Preview Foto Bukti Bahan
    document.getElementById('item_photo').addEventListener('change', function(event) {
        var file = event.target.files[0];
        var preview = document.getElementById('item_photo_preview');

        // Cek jika file ada dan tipe file adalah gambar
        if (file && file.type.startsWith('image')) {
            var reader = new FileReader();

            reader.onload = function(e) {
                preview.innerHTML = '<img src="' + e.target.result + '" class="img-fluid" style="max-height: 200px; max-width: 100%;">';
            };

            reader.readAsDataURL(file);
        } else {
            preview.innerHTML = '<p>File yang dipilih bukan gambar</p>';
        }
    });
</script>

<script>
    // Menambahkan Bahan yang dipilih ke tabel pengajuan
    document.getElementById('addItemButton').addEventListener('click', function() {
        const BahanSelect = document.getElementById('bahan');
        const jumlahInput = document.getElementById('jumlah');
        const BahanIds = Array.from(BahanSelect.selectedOptions).map(option => option.value);
        const BahanNames = Array.from(BahanSelect.selectedOptions).map(option => option.text);
        const jumlah = jumlahInput.value;

        // Validasi jika tidak ada Bahan yang dipilih atau jumlah tidak diisi
        if (BahanIds.length === 0 || jumlah === '') {
            alert('Pilih Bahan dan masukkan jumlah stok');
            return;
        }

        const tableBody = document.getElementById('BahanTable').getElementsByTagName('tbody')[0];

        // Menambahkan setiap Bahan yang dipilih
        BahanIds.forEach((id, index) => {
            const row = tableBody.insertRow();

            const cell1 = row.insertCell(0);
            const cell2 = row.insertCell(1);
            const cell3 = row.insertCell(2);

            cell1.innerHTML = BahanNames[index];
            cell2.innerHTML = jumlah;
            cell3.innerHTML = `<button type="button" class="btn btn-danger" onclick="removeItem(this)">Hapus</button>`;

            // Menambahkan input tersembunyi untuk mengirim data Bahan dan jumlahnya
            const hiddenInput1 = document.createElement('input');
            hiddenInput1.type = 'hidden';
            hiddenInput1.name = 'bahan_ids[]';
            hiddenInput1.value = id;
            row.appendChild(hiddenInput1);

            const hiddenInput2 = document.createElement('input');
            hiddenInput2.type = 'hidden';
            hiddenInput2.name = 'jumlah_stok[]';
            hiddenInput2.value = jumlah;
            row.appendChild(hiddenInput2);
        });

        // Reset form setelah menambah Bahan
        BahanSelect.value = '';
        jumlahInput.value = '';
    });

    // Fungsi untuk menghapus item dari tabel pengajuan
    function removeItem(button) {
        const row = button.parentNode.parentNode;
        row.parentNode.removeChild(row);
    }
</script>
@endsection