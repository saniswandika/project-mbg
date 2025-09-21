<?php

namespace App\Http\Controllers;

use App\Models\Logistik;
use App\Models\pengajuan_barang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

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
        // kepala dapur    // Menampilkan form pengajuan barang
        $masterBarang = DB::table('master_barangs')->get(); // Ambil semua barang dari master_barangs
        // return view('logistik.pengajuan_barang', compact('masterBarang'));
    
        $barangPengajuan = DB::table('pengajuan_barangs')->get();
        return view('logistik.pengajuan_barang.index', compact('barangPengajuan', 'masterBarang'));
    }

        // Proses pengajuan barang
    public function proses_pengajuan_barang(Request $request)
    {
        // Validasi input
        $request->validate([
            'barang_ids' => 'required|array|min:1',
            'jumlah_stok' => 'required|array|min:1',
        ]);

        // Loop melalui barang yang diajukan
        foreach ($request->barang_ids as $barang_id) {
            // Pastikan ada jumlah stok untuk setiap barang yang dipilih
            if (isset($request->jumlah_stok[$barang_id])) {
                $jumlahStok = $request->jumlah_stok[$barang_id];

                // Simpan pengajuan barang ke tabel pengajuan_barangs
                DB::table('pengajuan_barangs')->insert([
                    'barang_id' => $barang_id,
                    'jumlah_stok' => $jumlahStok,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

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
