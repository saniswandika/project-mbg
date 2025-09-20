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
        $barangPengajuan = DB::table('pengajuan_barangs')->get();
        
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
            'nama' => $request->nama,
            'merk' => $request->merk,
            'deskripsi' => $request->deskripsi,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        // Handle file upload jika ada
        if ($request->hasFile('foto')) {
            $file = $request->file('foto');
            $filename = time() . '_' . $file->getClientOriginalName();
            // Simpan file di storage dan simpan nama file ke data
            $path = $file->storeAs('public/master_barang', $filename);
            $data['foto'] = $filename; // Masukkan nama file ke array data
        }

        // Insert data ke database tanpa model
        DB::table('master_barangs')->insert($data);

        return redirect()->route('tambah_master_barang')
                         ->with('success', 'Master barang berhasil ditambahkan!');
    }


    public function pengajuan_barang()
    {
                // Mendapatkan ID pengguna yang sedang login
        $userid = Auth::id();
        $role = auth()->user()->role;
        $role_id = $this->get_role($userid);
        // kepala dapur
        $barangPengajuan = DB::table('pengajuan_barangs')->get();
        // dd($barangPengajuan);
        // akuntansi

        // Menampilkan daftar pengajuan barang
        // $barangPengajuan = pengajuan_barang::all();
        return view('logistik.pengajuan_barang', compact('barangPengajuan'));
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
