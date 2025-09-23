<?php

namespace App\Http\Controllers;

use App\Models\Logistik;
use App\Models\pengajuan_barang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LogistikController extends Controller
{
    public function index()
    {
        // Ambil semua data logistik
        $logistik = Logistik::all();
        
        return view('logistik.index', compact('logistik'));
    }

    public function master_barang()
    {
        // Ambil semua data logistik
        $userid = Auth::id();
        $role_id = $this->get_role($userid);
        $barangPengajuan = DB::table('master_barangs')->get();
        
        return view('logistik.master_barang', compact('barangPengajuan', 'role_id', 'userid'));
    }
    
    public function tambah_master_barang()
    {
        // Ambil semua data logistik
        $userid = Auth::id();
        $role_id = $this->get_role($userid);
        if($role_id == 'superadmin' || $role_id == 'admin'){
            return view('logistik.tambah_master_barang', compact('role_id', 'userid'));
        }else{
            return redirect()->back()->with('error', 'Akses ditolak! Anda tidak memiliki izin untuk mengakses halaman ini.');
        }
    }

// Fungsi untuk memproses penyimpanan barang
public function proses_tambah_barang_master(Request $request)
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
        'nama_barang' => $request->nama,
        'merk_barang' => $request->merk,
        'deskripsi' => $request->deskripsi,
        'created_at' => now(),
        'updated_at' => now(),
    ];

    // Handle file upload jika ada
    if ($request->hasFile('foto')) {
        $file = $request->file('foto');
        $filename = time() . '.' . $file->getClientOriginalExtension(); // Menggunakan ekstensi asli
        // Simpan file di storage dan simpan nama file ke data
        $path = $file->storeAs('public/master_barang', $filename);
        $data['foto'] = $filename; // Masukkan nama file dengan ekstensi ke array data
    }

    // Insert data ke database tanpa model
    DB::table('master_barangs')->insert($data);

    return redirect()->route('logistik.master_barang')
                     ->with('success', 'Master barang berhasil ditambahkan!');
}


        // Fungsi untuk menampilkan halaman edit
    public function edit_master_barang($id)
    {
        $barang = DB::table('master_barangs')->where('id', $id)->first(); // Ambil data barang berdasarkan ID
        // dd($barang)->All();
        return view('logistik.edit', compact('barang')); // Tampilkan form edit dengan data barang
    }

    // Fungsi untuk memperbarui data barang
    public function update_master_barang(Request $request, $id)
    {
        // Validasi input
        $request->validate([
            'nama' => 'required|string|max:255',
            'merk' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Ambil data barang berdasarkan ID
        $barang = DB::table('master_barangs')->where('id', $id)->first();

        // Persiapkan data yang akan diperbarui
        $data = [
            'nama_barang' => $request->nama,
            'merk_barang' => $request->merk,
            'deskripsi' => $request->deskripsi,
            'updated_at' => now(),
        ];

        // Handle file upload jika ada foto baru
        if ($request->hasFile('foto')) {
            // Hapus foto lama jika ada
            if ($barang->foto) {
                $oldImagePath = public_path('storage/master_barang/' . $barang->foto);
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath); // Hapus foto lama dari storage
                }
            }

            // Simpan foto baru
            $file = $request->file('foto');
            $filename = time() . '.' . $file->getClientOriginalExtension(); // Nama file baru dengan ekstensi
            $path = $file->storeAs('public/master_barang', $filename); // Simpan file

            $data['foto'] = $filename; // Masukkan nama file foto baru
        }

        // Update data barang di database
        DB::table('master_barangs')->where('id', $id)->update($data);

        return redirect()->route('logistik.master_barang')
                         ->with('success', 'Master barang berhasil diperbarui!');
    }


    // Fungsi untuk menghapus barang berdasarkan ID
    public function destroy_master_barang($id)
    {
        // Ambil data barang berdasarkan ID
        $barang = DB::table('master_barangs')->where('id', $id)->first();

        if ($barang) {
            // Menghapus file gambar dari storage jika ada
            if ($barang->foto) {
                $imagePath = public_path('storage/master_barang/' . $barang->foto);
                if (file_exists($imagePath)) {
                    unlink($imagePath); // Menghapus gambar dari server
                }
            }

            // Menghapus data barang dari database
            DB::table('master_barangs')->where('id', $id)->delete();

            // Redirect dengan pesan sukses
            return redirect()->route('logistik.master_barang')
                             ->with('success', 'Master barang berhasil dihapus!');
        } else {
            // Jika barang tidak ditemukan
            return redirect()->route('logistik.master_barang')
                             ->with('error', 'Barang tidak ditemukan!');
        }
    }


    public function pengajuan_barang()
    {
                // Mendapatkan ID pengguna yang sedang login
        $userid = Auth::id();
        $role = auth()->user()->role;
        $role_id = $this->get_role($userid);
        $masterBarang = DB::table('master_barangs')->get(); // Ambil semua barang dari master_barangs
        $pengajuanBarang = DB::table('list_pengajuans')
                            ->where('id_pengaju', $userid)
                            ->get();
                            // ->where('status', 'NOT LIKE', '5')
        // return view('logistik.pengajuan_barang', compact('masterBarang'));
    
        $barangPengajuan = DB::table('pengajuan_barangs')->get();
        return view('logistik.pengajuan_barang.index', compact('barangPengajuan', 'masterBarang','pengajuanBarang', 'userid', 'role'));
    }

    public function detail_pengajuan_barang($id)
    {
                // Mendapatkan ID pengguna yang sedang login
        $userid = Auth::id();
        $role = auth()->user()->role;
        $role_id = $this->get_role($userid);
        $pengajuanBarang = DB::table('list_pengajuans')
                            ->where('id', $id)
                            ->get();
        $barangPengajuan = DB::table('pengajuan_barangs')->get();
        return view('logistik.pengajuan_barang.detail', compact('barangPengajuan','pengajuanBarang', 'userid', 'id'));
    }

// Method untuk revisi jumlah barang
    public function revisi_pengajuan_barang(Request $request, $id)
    {
        // Menggunakan query builder untuk menemukan data berdasarkan ID
        $barang = DB::table('pengajuan_barangs')->where('id', $id)->first();

        // Cek apakah data barang ditemukan
        if ($barang) {
            // Update jumlah barang menggunakan query builder
            DB::table('pengajuan_barangs')
                ->where('id', $id)
                ->update([
                    'jumlah' => $request->jumlah,
                    'deskripsi' => $request->keterangan
                ]);

            return redirect()->back()->with('success', 'Jumlah barang berhasil diperbarui.');
        }

        return redirect()->back()->with('error', 'Barang tidak ditemukan.');
    }

    // Method untuk verifikasi password dan approve
    public function verifyApprove(Request $request, $id)
    {
        // Ambil data pengajuan barang berdasarkan ID
        $barang = DB::table('list_pengajuans')->where('id', $id)->first();
        
        // Cek apakah barang ditemukan
        if (!$barang) {
            return redirect()->back()->with('error', 'Barang tidak ditemukan.');
        }

        // Verifikasi password (gunakan Auth untuk mendapatkan user saat ini)
        if (Hash::check($request->password, Auth::user()->password)) {
            $status = $barang->status;
            
            $userid = Auth::id();
            $role_id = $this->get_role($userid);
            $input = 1;
            if($role_id == 'akutansi'){
                $input = 2;
            }
            if($role_id == 'admin'){
                $input = 3;
            }
            if($role_id == 'superadmin'){
                $input = $status + 1;
            }
            $array_id_barang = explode('^', $barang->id_barang);
            DB::table('list_pengajuans')
                ->where('id', $id)
                ->update([
                    'status' => $input
                ]);
            foreach($array_id_barang AS $a){
                DB::table('pengajuan_barangs')
                    ->where('id', $a)
                    ->update([
                        'status' => $input
                    ]);
            }

            return redirect()->back()->with('success', 'Pengajuan berhasil disetujui.');
        } else {
            // Jika password salah
            return redirect()->back()->with('error', 'Password salah. Aksi tidak dapat dilanjutkan.');
        }
    }

    // Method untuk menghapus barang
    public function hapus_pengajuan_barang($id, $id_home)
    {
        // Cari barang berdasarkan ID menggunakan query builder
        $barang = DB::table('pengajuan_barangs')->where('id', $id)->first();

        // Cek apakah barang ditemukan
        if ($barang) {
            // Hapus barang berdasarkan ID menggunakan query builder
            $delete = DB::table('pengajuan_barangs')->where('id', $id)->delete();
            if($delete){
            $cek_list = DB::table('list_pengajuans')->where('id', $id_home)->first();
            $array_id_barang = explode('^', $cek_list->id_barang);
            $build_up = [];
            foreach($array_id_barang AS $a){
                if($id != $a){
                    $build_up[] = $a;
                }
            }
            $update_data = implode('^', $build_up);
            DB::table('list_pengajuans')
                ->where('id', $id_home)
                ->update([
                    'id_barang' => $update_data
                ]);
            }
            return redirect()->back()->with('success', 'Barang berhasil dihapus.');
        }

        return redirect()->back()->with('error', 'Barang tidak ditemukan.');
    }


        // Proses pengajuan barang
    public function proses_pengajuan_barang(Request $request)
    {
        $request->validate([
            'barang_ids' => 'required|array|min:1',
            'jumlah_stok' => 'required|array|min:1',
        ]);
        $jumlahStok = $request->input('jumlah_stok');
        $barangIds = $request->input('barang_ids');
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
            $id = DB::table('pengajuan_barangs')->insertGetId([
                'id_barang'     => $barangId,
                'id_pengaju'    => $userid,
                'id_akutansi'   => $akutansi,
                'id_admin'      => $admin,
                'id_superadmin' => $superadmin,
                'jumlah'        => $stok, 
                'merk_barang'   => '', 
                'harga_barang'  => '', 
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
            $id = DB::table('list_pengajuans')->insertGetId([
                'id_barang'     => $combined,
                'deskripsi'     => '',
                'status'        => 1,
                'id_pengaju'    => $userid,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);

        return redirect()->route('logistik.pengajuan_barang')
                         ->with('success', 'Pengajuan stok barang berhasil!');
    }

    public function create()
    {
        // Menampilkan form pengajuan barang
        return view('barang_pengajuan.create');
    }

    public function store(Request $request)
    {
        // Validasi input
        $request->validate([
            'nama_barang' => 'required|string|max:255',
            'jumlah_barang' => 'required|integer',
            'deskripsi' => 'required|string',
        ]);

        // Simpan data pengajuan barang
        pengajuan_barang::create([
            'nama_barang' => $request->nama_barang,
            'jumlah_barang' => $request->jumlah_barang,
            'deskripsi' => $request->deskripsi,
        ]);

        // Redirect ke halaman pengajuan
        return redirect()->route('barang_pengajuan.index');
    }

    public function approve($id)
    {
        // Menyetujui pengajuan barang
        $barang = pengajuan_barang::findOrFail($id);
        $barang->status = 'approved';
        $barang->save();

        return redirect()->route('barang_pengajuan.index');
    }

    public function reject($id)
    {
        // Menolak pengajuan barang
        $barang = pengajuan_barang::findOrFail($id);
        $barang->status = 'rejected';
        $barang->save();

        return redirect()->route('barang_pengajuan.index');
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
