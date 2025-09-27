<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Models\PengajuanBahan;

class BahanOlahanController extends Controller
{    
    public function bahan_olahan()
    {
        // Ambil semua data logistik
        $userid = Auth::id();
        $role_id = $this->get_role($userid);
        $bahanPengajuan = DB::table('bahan_olahans')->get();
        
        return view('bahan_olahan.bahan_olahan', compact('bahanPengajuan', 'role_id', 'userid'));
    }    

    public function tambah_bahan_olahan()
    {
        // Ambil semua data logistik
        $userid = Auth::id();
        $role_id = $this->get_role($userid);
        if($role_id == 'superadmin' || $role_id == 'admin'){
            return view('bahan_olahan.tambah_bahan_olahan', compact('role_id', 'userid'));
        }else{
            return redirect()->back()->with('error', 'Akses ditolak! Anda tidak memiliki izin untuk mengakses halaman ini.');
        }
    }

    // Fungsi untuk memproses penyimpanan bahan
    public function proses_tambah_bahan_master(Request $request)
    {
        // Validasi input
        $request->validate([
            'nama' => 'required|string|max:255',
            'merk' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Persiapkan data yang akan dimasukkan ke database
        $data = [
            'nama_bahan' => $request->nama,
            'merk_bahan' => $request->merk,
            'deskripsi' => $request->deskripsi,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        // Handle file upload jika ada
        if ($request->hasFile('foto')) {
            $file = $request->file('foto');
            $filename = time() . '.' . $file->getClientOriginalExtension(); // Menggunakan ekstensi asli
            // Simpan file di storage dan simpan nama file ke data
            $path = $file->storeAs('public/bahan_olahan', $filename);
            $data['foto'] = $filename; // Masukkan nama file dengan ekstensi ke array data
        }

        // Insert data ke database tanpa model
        $save = DB::table('bahan_olahans')->insert($data);

        if($save){
            return redirect()->route('bahan_olahan.bahan_olahan')
                        ->with('success', 'Bahan Olahan berhasil ditambahkan!');
        }else{
            return redirect()->back()->with('error', 'Akses ditolak! Anda tidak memiliki izin untuk mengakses halaman ini.');
        }
    }

    public function edit_bahan_olahan($id)
    {
        $bahan = DB::table('bahan_olahans')->where('id', $id)->first(); // Ambil data barang berdasarkan ID
        // dd($barang)->All();
        return view('bahan_olahan.edit', compact('bahan')); // Tampilkan form edit dengan data barang
    }

    public function update_bahan_olahan(Request $request, $id)
    {
        // Validasi input
        $request->validate([
            'nama' => 'required|string|max:255',
            'merk' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Ambil data barang berdasarkan ID
        $barang = DB::table('bahan_olahans')->where('id', $id)->first();

        // Persiapkan data yang akan diperbarui
        $data = [
            'nama_bahan' => $request->nama,
            'merk_bahan' => $request->merk,
            'deskripsi' => $request->deskripsi,
            'updated_at' => now(),
        ];

        // Handle file upload jika ada foto baru
        if ($request->hasFile('foto')) {
            // Hapus foto lama jika ada
            if ($barang->foto) {
                $oldImagePath = public_path('storage/bahan_olahan/' . $barang->foto);
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath); // Hapus foto lama dari storage
                }
            }

            // Simpan foto baru
            $file = $request->file('foto');
            $filename = time() . '.' . $file->getClientOriginalExtension(); // Nama file baru dengan ekstensi
            $path = $file->storeAs('public/bahan_olahan', $filename); // Simpan file

            $data['foto'] = $filename; // Masukkan nama file foto baru
        }

        // Update data barang di database
        $save = DB::table('bahan_olahans')->where('id', $id)->update($data);

        if($save){
            return redirect()->route('bahan_olahan.bahan_olahan')
                         ->with('success', 'Bahan olahan berhasil diperbarui!');
        }else{
            return redirect()->back()->with('error', 'Akses ditolak! Anda tidak memiliki izin untuk mengakses halaman ini.');
        }
    }

        // Fungsi untuk menghapus barang berdasarkan ID
    public function destroy_master_bahan($id)
    {
        // Ambil data barang berdasarkan ID
        $barang = DB::table('bahan_olahans')->where('id', $id)->first();

        if ($barang) {
            // Menghapus file gambar dari storage jika ada
            if ($barang->foto) {
                $imagePath = public_path('storage/bahan_olahan/' . $barang->foto);
                if (file_exists($imagePath)) {
                    unlink($imagePath); // Menghapus gambar dari server
                }
            }

            // Menghapus data barang dari database
            DB::table('bahan_olahans')->where('id', $id)->delete();

            // Redirect dengan pesan sukses
            return redirect()->route('bahan_olahan.bahan_olahan')
                             ->with('success', 'Master bahan berhasil dihapus!');
        } else {
            // Jika barang tidak ditemukan
            return redirect()->route('bahan_olahan.bahan_olahan')
                             ->with('error', 'bahan tidak ditemukan!');
        }
    }

    

    public function pengajuan_bahan()
    {
                // Mendapatkan ID pengguna yang sedang login
        $userid = Auth::id();
        $role = auth()->user()->role;
        $role_id = $this->get_role($userid);
        $masterbahan = DB::table('bahan_olahans')->get(); // Ambil semua bahan dari master_bahans
        $pengajuanbahan = DB::table('list_pengajuan_bahans')
                            ->where('id_pengaju', $userid)
                            ->get();
        foreach($pengajuanbahan AS $row){
            if(empty($row->id_bahan)){
                $delete = DB::table('list_pengajuan_bahans')->where('id', $row->id)->delete();
            }
        }   
    
        $bahanPengajuan = DB::table('pengajuan_bahans')
            ->where(function ($query) use ($userid) {
                $query->where('id_pengaju', $userid)
                    ->orWhere('id_akutansi', $userid)
                    ->orWhere('id_admin', $userid)
                    ->orWhere('id_superadmin', $userid);
            })
            ->get();
        // dd($bahanPengajuan)->All();
        return view('bahan_olahan.pengajuan_bahan.index', compact('bahanPengajuan', 'masterbahan','pengajuanbahan', 'userid', 'role'));
    }

    public function detail_pengajuan_bahan($id)
    {
                // Mendapatkan ID pengguna yang sedang login
        $userid = Auth::id();
        $role = auth()->user()->role;
        $role_id = $this->get_role($userid);
        $pengajuanBahan = DB::table('list_pengajuan_bahans')
                            ->where('id', $id)
                            ->get();
        $bahanPengajuan = DB::table('pengajuan_bahans')->get();
        return view('bahan_olahan.pengajuan_bahan.detail', compact('bahanPengajuan','pengajuanBahan', 'userid', 'id', 'role_id'));
    }

    public function revisi_pengajuan_bahan(Request $request, $id)
    {
        // Menggunakan query builder untuk menemukan data berdasarkan ID
        $barang = DB::table('pengajuan_bahans')->where('id', $id)->first();

        // Cek apakah data barang ditemukan
        if ($barang) {
            // Update jumlah barang menggunakan query builder
            DB::table('pengajuan_bahans')
                ->where('id', $id)
                ->update([
                    'jumlah' => $request->jumlah,
                    'deskripsi' => $request->keterangan
                ]);

            return redirect()->back()->with('success', 'Jumlah bahan berhasil diperbarui.');
        }

        return redirect()->back()->with('error', 'Bahan tidak ditemukan.');
    }

    public function hapus_pengajuan_bahan($id, $id_home)
    {
        // Cari barang berdasarkan ID menggunakan query builder
        $barang = DB::table('pengajuan_bahans')->where('id', $id)->first();
        if(empty($barang)){
            return redirect()->route('pengajuan_bahan');
        }

        // Cek apakah barang ditemukan
        if ($barang) {
            // Hapus barang berdasarkan ID menggunakan query builder
            $delete = DB::table('pengajuan_bahans')->where('id', $id)->delete();
            if($delete){
            $cek_list = DB::table('list_pengajuan_bahans')->where('id', $id_home)->first();
            $array_id_bahan = explode('^', $cek_list->id_bahan);
            $build_up = [];
            foreach($array_id_bahan AS $a){
                if($id != $a){
                    $build_up[] = $a;
                }
            }
            $update_data = implode('^', $build_up);
            DB::table('list_pengajuan_bahans')
                ->where('id', $id_home)
                ->update([
                    'id_bahan' => $update_data
                ]);
            }
            return redirect()->back()->with('success', 'Bahan berhasil dihapus.');
        }

        return redirect()->back()->with('error', 'Bahan tidak ditemukan.');
    }

    public function proses_pengajuan_bahan(Request $request)
    {
        $request->validate([
            'bahan_ids' => 'required|array',
            'jumlah_stok' => 'required|array',
        ]);
        $jumlahStok = $request->input('jumlah_stok');
        $barangIds = $request->input('bahan_ids');
        $userid = Auth::id();
        $kepaladapur_id = DB::table('model_has_roles')
                        ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                        ->where('roles.name', 'kepaladapur')
                        ->first();
        $akutansi_id    = DB::table('model_has_roles')
                        ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                        ->where('roles.name', 'akutansi')
                        ->first();
        $admin_id       = DB::table('model_has_roles')
                        ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                        ->where('roles.name', 'admin')
                        ->first();
        $superadmin_id  = DB::table('model_has_roles')
                        ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                        ->where('roles.name', 'superadmin')
                        ->first();
        
        if(!empty($kepaladapur_id)){
            $kepaladapur    = $kepaladapur_id->model_id;
        }else{
            $kepaladapur          = $admin_id->model_id;
        }
        if(!empty($akutansi_id)){
            $akutansi       = $akutansi_id->model_id;
        }else{
            $akutansi          = $admin_id->model_id;
        }
        if(!empty($admin_id)){
            $admin          = $admin_id->model_id;
        }else{
            $admin     = $superadmin_id->model_id;
        }
        if(!empty($superadmin_id)){
            $superadmin     = $superadmin_id->model_id;
        }else{
            $superadmin     = NULL;
        }
        $ambil_id = [];
        foreach ($jumlahStok as $index => $stok) {
            $barangId = $barangIds[$index];
            // Perbaiki data input untuk memasukkan hanya satu 'jumlah'
            $id = DB::table('pengajuan_bahans')->insertGetId([
                'id_bahan'     => $barangId,
                'id_pengaju'    => $userid,
                'id_akutansi'   => $akutansi,
                'id_admin'      => $admin,
                'id_superadmin' => $superadmin,
                'jumlah'        => $stok, 
                'merk_bahan'   => '', 
                'harga_bahan'  => '', 
                'status'        => 1, 
                'deskripsi'     => '', 
                'foto'          => '', 
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);

            $ambil_id[] = $id;
            // Ambil ID yang baru saja dimasukkan
            // echo "ID Pengajuan Barang yang baru: " . $id;

        }
            $array = $ambil_id;
            $combined = implode('^', $array);
            $id = DB::table('list_pengajuan_bahans')->insert([
                'id_bahan'     => $combined,
                'deskripsi'     => '',
                'status'        => 1,
                'id_pengaju'    => $userid,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);

        if($id){
            return redirect()->route('bahan_olahan.pengajuan_bahan')
                         ->with('success', 'Pengajuan stok bahan berhasil!');
        }else{
            return redirect()->route('bahan_olahan.pengajuan_bahan')
                         ->with('error', 'Pengajuan stok bahan Gagal!');
        }
    }

    public function verifyApprove(Request $request, $id)
    {
        // Ambil data pengajuan barang berdasarkan ID
        $bahan = DB::table('list_pengajuan_bahans')->where('id', $id)->first();
        
        // Cek apakah bahan ditemukan
        if (!$bahan) {
            return redirect()->back()->with('error', 'Bahan tidak ditemukan.');
        }

        // Validasi file yang di-upload (Bukti Pembayaran, Struk Pembayaran, dan Foto Bukti Barang)
        $validated = $request->validate([
            'password' => 'required',
            'payment_proof' => 'nullable|image|mimes:jpg,jpeg,png,pdf|max:2048', // Validasi gambar atau PDF
            'receipt_proof' => 'nullable|image|mimes:jpg,jpeg,png,pdf|max:2048', // Validasi gambar atau PDF
            'item_photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048', // Validasi gambar
        ]);

        // Verifikasi password (gunakan Auth untuk mendapatkan user saat ini)
        if (Hash::check($request->password, Auth::user()->password)) {
            $status = $bahan->status;
            
            $userid = Auth::id();
            $role_id = $this->get_role($userid);
            $input = 1;
            
            // Menentukan status berdasarkan role
            if ($role_id == 'akutansi') {
                $input = 2;
            }
            if ($role_id == 'admin') {
                $input = 3;
            }
            if ($role_id == 'superadmin') {
                $input = $status + 1;
            }
            if($input == 7){
                $input = 6;
            }
            
            // Proses upload file (hanya jika ada file baru)
            $payment_proof_path = $bahan->payment_proof; // Gunakan file lama jika tidak ada file baru
            $receipt_proof_path = $bahan->receipt_proof; // Gunakan file lama jika tidak ada file baru
            $item_photo_path = $bahan->item_photo; // Gunakan file lama jika tidak ada file baru

            // Jika ada file Bukti Pembayaran baru
            if ($request->hasFile('payment_proof')) {
                // Hapus file lama jika ada
                Storage::delete('public/' . $bahan->payment_proof);
                $payment_proof_path = $request->file('payment_proof')->store('uploads/payment_proofs', 'public');
            }

            // Jika ada file Struk Pembayaran baru
            if ($request->hasFile('receipt_proof')) {
                // Hapus file lama jika ada
                Storage::delete('public/' . $bahan->receipt_proof);
                $receipt_proof_path = $request->file('receipt_proof')->store('uploads/receipt_proofs', 'public');
            }

            // Jika ada file Foto Bukti Barang baru
            if ($request->hasFile('item_photo')) {
                // Hapus file lama jika ada
                Storage::delete('public/' . $bahan->item_photo);
                $item_photo_path = $request->file('item_photo')->store('uploads/item_photos', 'public');
            }

            // Mengupdate status pengajuan bahan
            $array_id_bahan = explode('^', $bahan->id_bahan);
            DB::table('list_pengajuan_bahans')
                ->where('id', $id)
                ->update([
                    'status' => $input,
                    'payment_proof' => $payment_proof_path,
                    'receipt_proof' => $receipt_proof_path,
                    'item_photo' => $item_photo_path,
                ]);

            // Jika status menjadi 5, insert data ke tabel logistik
            if ($input == 6) {
                foreach ($array_id_bahan as $a) {
                    // Ambil data bahan terkait untuk dimasukkan ke logistik
                    $bahan_data = DB::table('pengajuan_bahans')->where('id', $a)->first();
                    $id_bahan = $bahan_data->id_bahan;  // ID Barang dari pengajuan_bahans
                    
                    // Mendapatkan nama bahan berdasarkan ID
                    $pengajuanBarang = new PengajuanBahan();
                    $namaBarang = $pengajuanBarang->getNamaBarang($id_bahan);  // Memanggil method untuk mendapatkan nama bahan

                    // Mengecek apakah bahan sudah ada di logistik
                    $cek_bahan = DB::table('logistiks')->where('id_master_bahan', $id_bahan)->first();

                    // Menghitung jumlah bahan yang akan dimasukkan
                    if (!empty($cek_bahan)) {
                        $jumlah = $cek_bahan->jumlah + $bahan_data->jumlah;  // Jika bahan ada, tambah jumlahnya
                    } else {
                        $jumlah = $bahan_data->jumlah;  // Jika bahan belum ada, pakai jumlah yang baru
                    }

                    // Jika data bahan ditemukan, masukkan ke tabel logistik
                    if ($bahan_data) {
                        DB::table('logistiks')->insert([
                            'nama_bahan' => $namaBarang,  // Nama bahan yang diambil dari master_bahans
                            'jumlah_bahan' => $jumlah,  // Jumlah bahan
                            'id_master_bahan' => $id_bahan,  // ID Barang yang sesuai dengan master_bahans
                            'merk_bahan' => $namaBarang,  // Menggunakan nama bahan yang sama untuk merk
                            'status' => 'baru',  // Status bahan baru
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }


            // Mengupdate status pengajuan bahan per ID bahan
            foreach ($array_id_bahan as $a) {
                DB::table('pengajuan_bahans')
                    ->where('id', $a)
                    ->update([
                        'status' => $input,
                    ]);
            }

            return redirect()->back()->with('success', 'Pengajuan berhasil disetujui.');
        } else {
            // Jika password salah
            return redirect()->back()->with('error', 'Password salah. Aksi tidak dapat dilanjutkan.');
        }
    }

    private function get_role(int $userid){
        $collection = DB::table('model_has_roles')
            ->leftJoin('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('model_id', $userid)
            ->get();

        // Ambil role_id dari koleksi
        $role_id = $collection->pluck('name')->first();
        return $role_id;
    }
}
