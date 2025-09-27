@extends('layouts.masterTemplate')

@section('title', 'Pengajuan Bahan')

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


            <h1 class="text-3xl font-semibold mb-4">Daftar Pengajuan Bahan</h1>
            <div class="p-6 bg-white shadow-md rounded-lg border border-gray-200">
                <table class="display table table-striped" id="bahanTable">
                    <thead>
                        <tr>
                            <th>Nama bahan</th>
                            <th>Jumlah bahan</th>
                            <th>Setatus</th>
                            <th>Keterangan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    @foreach($pengajuanBahan as $row)
                    @php
                    $array_bahan = explode('^', $row->id_bahan);
                    @endphp
                    @endforeach
                    <tbody>
                        @foreach($array_bahan as $bahan)
                        @php
                        // Ambil data bahan dan jumlahnya
                        $id_bahan = (new App\Models\PengajuanBahan)->getIdBahan($bahan);
                        $nama_bahan = (new App\Models\PengajuanBahan)->getNamaBahan($id_bahan->id_bahan);
                        $color_ttp = '</span>';
                        if($id_bahan->status == '1'){
                        $text = 'Menunggu Kepastian';
                        $color = '<span style="color:orange">';
                            }elseif($id_bahan->status == '2'){
                            $text = 'Approve Akutansi';
                            $color = '<span style="color:green">';
                                }elseif($id_bahan->status == '3'){
                                $text = 'Approve Admin';
                                $color = '<span style="color:green">';
                                    }elseif($id_bahan->status == '4'){
                                    $text = 'Pembelian Admin';
                                    $color = '<span style="color:red">';
                                        }elseif($id_bahan->status == '5'){
                                        $text = 'Approve Superadmin';
                                        $color = '<span style="color:red">';
                                            }elseif($id_bahan->status == '5'){
                                            $text = 'Rejected';
                                            $color = '<span style="color:red">';
                                                }elseif($id_bahan->status == '6'){
                                                $text = 'Approved';
                                                $color = '<span style="color:black">';
                                                }else{
                                                $text = '';
                                                $color = '<span style="color:black">';
                                                    }
                                                @endphp
                                                <tr>
                                                    <td>{{ $nama_bahan }}</td>
                                                    <td>{{ $id_bahan->jumlah }}</td>
                                                    <td><?= $color ?>{{ $text }} <?= $color_ttp ?></td>
                                                    <td>{{ $id_bahan->deskripsi }}</td>
                                                    <td>
                                                        <!-- Link untuk Revisi dengan route yang benar -->
                                                        <button class="btn btn-warning" data-toggle="modal" data-target="#revisiModal{{ $id_bahan->id }}">Revisi</button>

                                                        <!-- Modal untuk revisi jumlah bahan -->
                                                        <div class="modal fade" id="revisiModal{{ $id_bahan->id }}" tabindex="-1" role="dialog" aria-labelledby="revisiModalLabel" aria-hidden="true">
                                                            <div class="modal-dialog" role="document">
                                                                <div class="modal-content">
                                                                    <div class="modal-header">
                                                                        <h5 class="modal-title" id="revisiModalLabel">Revisi Jumlah Bahan</h5>
                                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                            <span aria-hidden="true">&times;</span>
                                                                        </button>
                                                                    </div>
                                                                    <div class="modal-body">
                                                                        <!-- Form untuk mengubah jumlah bahan -->
                                                                        <form action="{{ route('bahan_olahan.revisi_pengajuan_bahan', ['id' => $id_bahan->id]) }}" method="POST">
                                                                            @csrf
                                                                            @method('PUT')
                                                                            <div class="form-group">
                                                                                <label for="jumlah">Jumlah Bahan</label>
                                                                                <input type="number" class="form-control" name="jumlah" id="jumlah" value="{{ $id_bahan->jumlah }}" required>
                                                                                <label for="jumlah">Keterangan</label>
                                                                                <textarea type="number" class="form-control" name="keterangan"></textarea>
                                                                            </div>
                                                                            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                                                                        </form>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- Tombol Hapus Bahan -->
                                                        <button class="btn btn-danger" data-toggle="modal" data-target="#hapusModal{{ $id_bahan->id }}">Hapus</button>

                                                        <!-- Modal konfirmasi hapus -->
                                                        <div class="modal fade" id="hapusModal{{ $id_bahan->id }}" tabindex="-1" role="dialog" aria-labelledby="hapusModalLabel" aria-hidden="true">
                                                            <div class="modal-dialog" role="document">
                                                                <div class="modal-content">
                                                                    <div class="modal-header">
                                                                        <h5 class="modal-title" id="hapusModalLabel">Konfirmasi Penghapusan Bahan</h5>
                                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                            <span aria-hidden="true">&times;</span>
                                                                        </button>
                                                                    </div>
                                                                    <div class="modal-body">
                                                                        Apakah Anda yakin ingin menghapus bahan ini?
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <form action="{{ route('bahan_olahan.hapus_pengajuan_bahan', ['id' => $id_bahan->id, 'id_home' => $id]) }}" method="POST">
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
                // var_dump($pengajuanBahan);die();
                $model = (new App\Models\PengajuanBahan);
                foreach ($pengajuanBahan as $value) {
                    $status = $value->status;
                    $id_bahan = explode('^', $value->id_bahan);
                    foreach ($id_bahan as $key) {
                        $bahanId = $key;
                    }
                    $pengajuan_bahans = $model->getIdBahan($bahanId);
                    $id_akutansi = $pengajuan_bahans->id_akutansi;
                    $id_admin = $pengajuan_bahans->id_admin;
                    $id_superadmin = $pengajuan_bahans->id_superadmin;
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
                            <th>Foto Bukti Bahan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pengajuanBahan as $bahan)
                        <tr>
                            <td>
                                @if($bahan->payment_proof)
                                <button class="btn btn-info" data-toggle="modal" data-target="#paymentProofModal{{ $bahan->id }}">
                                    Lihat Bukti Pembayaran
                                </button>
                                @else
                                <span>Belum Ada</span>
                                @endif
                            </td>
                            <td>
                                @if($bahan->receipt_proof)
                                <button class="btn btn-info" data-toggle="modal" data-target="#receiptProofModal{{ $bahan->id }}">
                                    Lihat Struk Pembayaran
                                </button>
                                @else
                                <span>Belum Ada</span>
                                @endif
                            </td>
                            <td>
                                @if($bahan->item_photo)
                                <button class="btn btn-info" data-toggle="modal" data-target="#itemPhotoModal{{ $bahan->id }}">
                                    Lihat Foto Bukti Bahan
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
                                if (($row->status == '1' && $pengajuan_bahans->id_akutansi == $userid) || ($row->status == '1' && $pengajuan_bahans->id_superadmin == $userid)) {
                                $canDetail = true;
                                $canApprove = true;
                                $canReject = true;
                                } elseif (($row->status == '2' && $pengajuan_bahans->id_admin == $userid) || ($row->status == '2' && $pengajuan_bahans->id_superadmin == $userid)) {
                                $canDetail = true;
                                $canApprove = true;
                                $canReject = true;
                                } elseif (($row->status == '3' && $pengajuan_bahans->id_superadmin == $userid) || ($row->status == '3' && $pengajuan_bahans->id_superadmin == $userid)) {
                                $canDetail = true;
                                $canApprove = true;
                                $canReject = true;
                                } elseif (($row->status == '4' && $pengajuan_bahans->id_admin == $userid) || ($row->status == '4' && $pengajuan_bahans->id_superadmin == $userid)) {
                                $canDetail = true;
                                $canApprove = true;
                                $canReject = true;
                                } elseif (($row->status == '5' && $pengajuan_bahans->id_superadmin == $userid) || ($row->status == '5' && $pengajuan_bahans->id_superadmin == $userid)) {
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
                                                <form action="{{ route('bahan_olahan.verify_approve', ['id' => $row->id]) }}" method="POST" enctype="multipart/form-data">
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
                                                            <!-- Preview foto bukti bahan -->
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

                        <!-- Modal untuk Lihat Bukti Pembayaran -->
                        @if($bahan->payment_proof)
                        <div class="modal fade" id="paymentProofModal{{ $bahan->id }}" tabindex="-1" role="dialog" aria-labelledby="paymentProofModalLabel{{ $bahan->id }}" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="paymentProofModalLabel{{ $bahan->id }}">Bukti Pembayaran</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <img src="{{ asset('storage/' . $bahan->payment_proof) }}" class="img-fluid" alt="Bukti Pembayaran">
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Modal untuk Lihat Struk Pembayaran -->
                        @if($bahan->receipt_proof)
                        <div class="modal fade" id="receiptProofModal{{ $bahan->id }}" tabindex="-1" role="dialog" aria-labelledby="receiptProofModalLabel{{ $bahan->id }}" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="receiptProofModalLabel{{ $bahan->id }}">Struk Pembayaran</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <img src="{{ asset('storage/' . $bahan->receipt_proof) }}" class="img-fluid" alt="Struk Pembayaran">
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Modal untuk Lihat Foto Bukti Bahan -->
                        @if($bahan->item_photo)
                        <div class="modal fade" id="itemPhotoModal{{ $bahan->id }}" tabindex="-1" role="dialog" aria-labelledby="itemPhotoModalLabel{{ $bahan->id }}" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="itemPhotoModalLabel{{ $bahan->id }}">Foto Bukti Bahan</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <img src="{{ asset('storage/' . $bahan->item_photo) }}" class="img-fluid" alt="Foto Bukti Bahan">
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