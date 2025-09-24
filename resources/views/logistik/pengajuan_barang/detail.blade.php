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
                            $text = 'Approve Akutansi';
                            $color = '<span style="color:green">';
                                }elseif($id_barang->status == '3'){
                                $text = 'Approve Admin';
                                $color = '<span style="color:green">';
                                    }elseif($id_barang->status == '4'){
                                    $text = 'Pembelian Admin';
                                    $color = '<span style="color:red">';
                                        }elseif($id_barang->status == '5'){
                                        $text = 'Approve Superadmin';
                                        $color = '<span style="color:red">';
                                            }elseif($id_barang->status == '5'){
                                            $text = 'Rejected';
                                            $color = '<span style="color:red">';
                                                }elseif($id_barang->status == '6'){
                                                $text = 'Approved';
                                                $color = '<span style="color:black">';
                                                }else{
                                                $text = '';
                                                $color = '<span style="color:black">';
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
            <div class="p-6 bg-white shadow-md rounded-lg border border-gray-200">
                <?php
                // var_dump($pengajuanBarang);die();
                $model = (new App\Models\PengajuanBarang);
                foreach ($pengajuanBarang as $value) {
                    $status = $value->status;
                    $id_barang = explode('^', $value->id_barang);
                    foreach ($id_barang as $key) {
                        $barangId = $key;
                    }
                    $pengajuan_barangs = $model->getIdBarang($barangId);
                    $id_akutansi = $pengajuan_barangs->id_akutansi;
                    $id_admin = $pengajuan_barangs->id_admin;
                    $id_superadmin = $pengajuan_barangs->id_superadmin;
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
                }

                ?>

                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Bukti Pembayaran</th>
                            <th>Struk Pembayaran</th>
                            <th>Foto Bukti Barang</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pengajuanBarang as $barang)
                        <tr>
                            <td>
                                @if($barang->payment_proof)
                                <button class="btn btn-info" data-toggle="modal" data-target="#paymentProofModal{{ $barang->id }}">
                                    Lihat Bukti Pembayaran
                                </button>
                                @else
                                <span>Belum Ada</span>
                                @endif
                            </td>
                            <td>
                                @if($barang->receipt_proof)
                                <button class="btn btn-info" data-toggle="modal" data-target="#receiptProofModal{{ $barang->id }}">
                                    Lihat Struk Pembayaran
                                </button>
                                @else
                                <span>Belum Ada</span>
                                @endif
                            </td>
                            <td>
                                @if($barang->item_photo)
                                <button class="btn btn-info" data-toggle="modal" data-target="#itemPhotoModal{{ $barang->id }}">
                                    Lihat Foto Bukti Barang
                                </button>
                                @else
                                <span>Belum Ada</span>
                                @endif
                            </td>
                            <td>
                                @php
                                // Menentukan kondisi untuk masing-masing status dan peran
                                $canApprove = false;
                                $canReject = false;
                                $canDetail = false;
                                if (($row->status == '1' && $pengajuan_barangs->id_akutansi == $userid) || ($row->status == '1' && $pengajuan_barangs->id_superadmin == $userid)) {
                                $canDetail = true;
                                $canApprove = true;
                                $canReject = true;
                                } elseif (($row->status == '2' && $pengajuan_barangs->id_admin == $userid) || ($row->status == '2' && $pengajuan_barangs->id_superadmin == $userid)) {
                                $canDetail = true;
                                $canApprove = true;
                                $canReject = true;
                                } elseif (($row->status == '3' && $pengajuan_barangs->id_superadmin == $userid) || ($row->status == '3' && $pengajuan_barangs->id_superadmin == $userid)) {
                                $canDetail = true;
                                $canApprove = true;
                                $canReject = true;
                                } elseif (($row->status == '4' && $pengajuan_barangs->id_admin == $userid) || ($row->status == '4' && $pengajuan_barangs->id_superadmin == $userid)) {
                                $canDetail = true;
                                $canApprove = true;
                                $canReject = true;
                                } elseif (($row->status == '5' && $pengajuan_barangs->id_superadmin == $userid) || ($row->status == '5' && $pengajuan_barangs->id_superadmin == $userid)) {
                                $canDetail = true;
                                $canApprove = true;
                                $canReject = true;
                                } else {
                                $canDetail = false;
                                $canApprove = false;
                                $canReject = false;
                                }
                                @endphp

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

                                                    <!-- Upload Foto Bukti Barang -->
                                                    <div class="form-group">
                                                        <label for="item_photo">Foto Bukti Barang</label>
                                                        <input type="file" class="form-control" name="item_photo" id="item_photo" required>
                                                        <div id="item_photo_preview" style="margin-top: 10px;">
                                                            <!-- Preview foto bukti barang -->
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
                                <a href="{{ route('logistik.reject_pengajuan_barang', ['id' => $row->id]) }}">
                                    <button class="btn btn-danger">Reject</button>
                                </a>
                                @endif

                            </td>
                        </tr>

                        <!-- Modal untuk Lihat Bukti Pembayaran -->
                        @if($barang->payment_proof)
                        <div class="modal fade" id="paymentProofModal{{ $barang->id }}" tabindex="-1" role="dialog" aria-labelledby="paymentProofModalLabel{{ $barang->id }}" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="paymentProofModalLabel{{ $barang->id }}">Bukti Pembayaran</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <img src="{{ asset('storage/' . $barang->payment_proof) }}" class="img-fluid" alt="Bukti Pembayaran">
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Modal untuk Lihat Struk Pembayaran -->
                        @if($barang->receipt_proof)
                        <div class="modal fade" id="receiptProofModal{{ $barang->id }}" tabindex="-1" role="dialog" aria-labelledby="receiptProofModalLabel{{ $barang->id }}" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="receiptProofModalLabel{{ $barang->id }}">Struk Pembayaran</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <img src="{{ asset('storage/' . $barang->receipt_proof) }}" class="img-fluid" alt="Struk Pembayaran">
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Modal untuk Lihat Foto Bukti Barang -->
                        @if($barang->item_photo)
                        <div class="modal fade" id="itemPhotoModal{{ $barang->id }}" tabindex="-1" role="dialog" aria-labelledby="itemPhotoModalLabel{{ $barang->id }}" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="itemPhotoModalLabel{{ $barang->id }}">Foto Bukti Barang</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <img src="{{ asset('storage/' . $barang->item_photo) }}" class="img-fluid" alt="Foto Bukti Barang">
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                        @endforeach
                    </tbody>
                </table>


                @if($role_id == 'superadmin')
                <label for="">Menunggu Approve</label>
                <span style="color:green">{{ $approve }}</span>
                @endif
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