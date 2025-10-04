<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
class PegawaiController extends Controller
{
    // 🟢 Tampilkan semua data pegawai
    public function index()
    {
        $pegawai = Pegawai::with('user')->get();
        return view('pegawai.index', compact('pegawai'));
    }

    // 🟡 Form tambah pegawai
    public function create()
    {
        return view('pegawai.create');
    }
    public function tambah_akun(Request $request)
    {   
        $id = $request->id;
        $roles = DB::table('roles')->get();

        return view('users.create', compact('id','roles'));

    }
    // 🟦 Simpan data pegawai baru
    public function store(Request $request)
    {
        $request->validate([
            'nama_lengkap' => 'required|string|max:100',
            'nik' => 'required|string|max:20|unique:pegawai,nik',
            'no_kk' => 'required|string|max:20',
            'alamat' => 'nullable|string',
            'foto_ktp' => 'nullable|image|max:2048',
            'no_bpjs' => 'nullable|string|max:30',
            'no_rekening' => 'nullable|string|max:50',
            'bank' => 'nullable|string|max:50',
            'atas_nama_rekening' => 'nullable|string|max:100',
        ]);

        $data = $request->except('foto_ktp');

        // 🔸 Upload foto jika ada
        if ($request->hasFile('foto_ktp')) {
            $file = $request->file('foto_ktp');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('public/ktp', $filename);
            
            $data['foto_ktp'] = $filename;
        }

        Pegawai::create($data);

        return redirect()->route('pegawai.index')->with('success', 'Data pegawai berhasil ditambahkan.');
    }

    // 🟡 Tampilkan form edit profil pegawai
    public function edit($id)
    {
        $pegawai = Pegawai::where('id', $id)->first();
        return view('pegawai.edit', compact('pegawai'));
    }
    public function show($id)
    {
        $pegawai = Pegawai::where('id', $id)->first();
        return view('pegawai.show', compact('pegawai'));
    }
    // 🟠 Update data pegawai
    public function update(Request $request, $id)
    {
        $pegawai = Pegawai::where('id', $id)->firstOrFail();

        $request->validate([
            'nama_lengkap' => 'required|string|max:100',
            'nik' => 'required|string|max:20|unique:pegawai,nik,' . $pegawai->id,
            'no_kk' => 'required|string|max:20',
            'alamat' => 'nullable|string',
            'foto_ktp' => 'nullable|image|max:2048',
            'no_bpjs' => 'nullable|string|max:30',
            'no_rekening' => 'nullable|string|max:50',
            'bank' => 'nullable|string|max:50',
            'atas_nama_rekening' => 'nullable|string|max:100',
        ]);

        $pegawai->fill($request->except('foto_ktp'));

        if ($request->hasFile('foto_ktp')) {
            if ($pegawai->foto_ktp) {
                Storage::delete('public/ktp/' . $pegawai->foto_ktp);
            }
            $file = $request->file('foto_ktp');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('public/ktp', $filename);
            $pegawai->foto_ktp = $filename;
        }

        $pegawai->save();

        return redirect()->back()->with('success', 'Data pegawai berhasil diperbarui.');
    }

    // 🔴 Hapus data pegawai
    public function destroy($id)
    {
        $pegawai = Pegawai::findOrFail($id);
        if ($pegawai->foto_ktp) {
            Storage::delete('public/ktp/' . $pegawai->foto_ktp);
        }
        $pegawai->delete();

        return redirect()->route('pegawai.index')->with('success', 'Data pegawai berhasil dihapus.');
    }
}
