<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\log_yayasanProvinsi;
use Carbon\Carbon;
use App\Models\rekomendasi_terdaftar_yayasan;
use App\Models\RekomendasiYayasansProvinsi;
use App\Repositories\rekomendasi_yayasan_provinsiRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use stdClass;

class rekomendasi_yayasan_provinsiController extends Controller
{
    /** @var rekomendasi_yayasan_provinsiRepository $rekomendasiYayasanProvinsiRepository*/
    private $rekomendasiYayasanProvinsiRepository;

    public function __construct(rekomendasi_yayasan_provinsiRepository $rekomendasiYayasanProvinsiRepo)
    {
        $this->rekomendasiYayasanProvinsiRepository = $rekomendasiYayasanProvinsiRepo;
    }

    /**
     * Display a listing of the rekomendasi_daftar_ulang_yayasan.
     */



    public function index(Request $request)
    {
        $rekomendasiYayasanProvinsi = $this->rekomendasiYayasanProvinsiRepository->paginate(10);
        // dd()
        return view('rekomendasi_yayasan_provinsis.index')
            ->with('rekomendasi_yayasan_provinsis', $rekomendasiYayasanProvinsi);
    }
    public function FileRekomYayasanProvinsi($id)
    {
        $rekomendasiTerdaftaryayasan = RekomendasiYayasansProvinsi::find($id);
        // dd($rekomendasiTerdaftaryayasan);

       $date = Carbon::parse($rekomendasiTerdaftaryayasan->updated_at)->locale('id');

       $date->settings(['formatFunction' => 'translatedFormat']);

       $tanggal = $date->format('j F Y ');


       // dd($tanggal);
        $pdf = PDF::loadHtml(view('rekomendasi_yayasan_provinsis.file_permohonan',compact('rekomendasiTerdaftaryayasan','tanggal')));
        $filename = 'File Permohonan' . $rekomendasiTerdaftaryayasan->nama . '.pdf';
        return $pdf->stream($filename);
    }
    /**
     * Show the form for creating a new rekomendasi_daftar_ulang_yayasan.
     */

    public function create()
    {

    }

    /**
     * Store a newly created rekomendasi_daftar_ulang_yayasan in storage.
     */
    public function store()
    {

    }

    /**
     * Display the specified rekomendasi_daftar_ulang_yayasan.
     */
    public function show($id)
    {
        $userid = Auth::user()->id;
        $rekomendasiYayasanProvinsi =  DB::table('rekomendasi_yayasans_provinsi as w')->select(
            'w.*',
            'rls.name as name_roles',
            'usr.name',
            'prov.name_prov',
            'kota.name_cities',
            'kecamatan.name_districts',
            'b.name_village',
        )
            ->leftjoin('roles as rls', 'rls.id', '=', 'w.tujuan')
            ->leftjoin('users as usr', 'usr.id', '=', 'w.petugas')
            ->leftjoin('indonesia_provinces as prov', 'prov.code', '=', 'w.id_provinsi')
            ->leftjoin('indonesia_cities as kota', 'kota.code', '=', 'w.id_kabkot')
            ->leftjoin('indonesia_districts as kecamatan', 'kecamatan.code', '=', 'w.id_kecamatan')
            ->leftjoin('indonesia_villages as b', 'b.code', '=', 'w.id_kelurahan')
            ->where('w.id', $id)->first();
        // dd($rekomendasiYayasanProvinsi);
        if (empty($rekomendasiYayasanProvinsi)) {
            $rekomendasiYayasanProvinsi =  DB::table('rekomendasi_terdaftar_yayasans as w')->select(
                'w.*',
                'rls.name',
                'usr.name',
                'prov.name_prov',
                'kota.name_cities',
                'kecamatan.name_districts',
                'b.name_village',
            )
                ->leftjoin('roles as rls', 'rls.id', '=', 'w.tujuan')
                ->leftjoin('users as usr', 'usr.id', '=', 'w.petugas')
                ->leftjoin('indonesia_provinces as prov', 'prov.code', '=', 'w.id_provinsi')
                ->leftjoin('indonesia_cities as kota', 'kota.code', '=', 'w.id_kabkot')
                ->leftjoin('indonesia_districts as kecamatan', 'kecamatan.code', '=', 'w.id_kecamatan')
                ->leftjoin('indonesia_villages as b', 'b.code', '=', 'w.id_kelurahan')
                ->where('w.id', $id)->first();
        }
        $roleid = DB::table('roles')
            ->where('name', 'Back Ofiice kelurahan')
            // ->where('name', 'supervisor')
            ->orWhere('name', 'supervisor')
            ->get();
        $checkroles = DB::table('model_has_roles')
            ->leftjoin('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->get();
        $log_yayasanProvinsi = DB::table('log_yayasanprovinsi as w')
            ->select(
                'w.*',
                'roles.name',
                // 'users.name',
                // 'alur.name'
            )
            ->leftJoin('users', 'users.id', '=', 'w.petugas_yaprov')
            ->leftJoin('roles', 'roles.id', '=', 'w.tujuan_yaprov')
            ->where('w.id_trx_yaprov', $id)
            ->get();
        dd($log_yayasanProvinsi);
        if ($log_yayasanProvinsi->isEmpty()) {
            $log_yayasanProvinsi = DB::table('log_yayasan as w')
                ->select(
                    'w.*',
                    'roles.name',
                    'users.name',
                    // 'alur.name'
                )
                ->leftJoin('users', 'users.id', '=', 'w.petugas')
                ->leftJoin('roles', 'roles.id', '=', 'w.tujuan')
                ->where('w.id_trx_yayasan', $id)
                ->get();
        }
        
        // dd($log_yayasanProvinsi);
        
        // dd($log_yayasanProvinsi);
        return view('rekomendasi_yayasan_provinsis.show', compact('rekomendasiYayasanProvinsi', 'roleid', 'checkroles', 'log_yayasanProvinsi'));
    }


    /**
     * Show the form for editing the specified rekomendasi_daftar_ulang_yayasan.
     */
    public function edit($id)
    {
        $userid = Auth::user()->id;
        $wilayah = DB::table('wilayahs as w')->select(
            'w.id',
            'b.*',
            'w.*',
            'prov.*',
            'kota.*',
            'kecamatan.*',
            'w.status_wilayah',
            'w.createdby',
        )
            ->leftjoin('indonesia_provinces as prov', 'prov.code', '=', 'w.province_id')
            ->leftjoin('indonesia_cities as kota', 'kota.code', '=', 'w.kota_id')
            ->leftjoin('indonesia_districts as kecamatan', 'kecamatan.code', '=', 'w.kecamatan_id')
            ->leftjoin('indonesia_villages as b', 'b.code', '=', 'w.kelurahan_id')
            ->where('status_wilayah', '1')
            ->where('w.createdby', $userid)->get();


        $getUsers = DB::table('model_has_roles')
            ->leftjoin('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->leftjoin('rekomendasi_terdaftar_yayasans', 'rekomendasi_terdaftar_yayasans.createdby', '=', 'model_has_roles.model_id')
            ->where('rekomendasi_terdaftar_yayasans.id', '=', $id)
            // ->where('status_aksi', '=', 'Draft')
            // ->orwhere('status_aksi', '=', 'Teruskan')
            ->get();

        $users =  Auth::user()->id;
        $getAuth = DB::table('model_has_roles')
            ->leftJoin('users', 'users.id', '=', 'model_has_roles.model_id')
            ->leftJoin('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('model_id', $users)
            ->get();

        // dd($checkroles2);
        //Tujuan
        $createdby = DB::table('rekomendasi_terdaftar_yayasans')
            ->join('users', 'rekomendasi_terdaftar_yayasans.createdby', '=', 'users.name')
            ->join('model_has_roles', 'model_has_roles.model_id', '=', 'users.id')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->select('rekomendasi_terdaftar_yayasans.id', 'rekomendasi_terdaftar_yayasans.createdby', 'roles.name')
            ->get();

            $rekomendasiYayasanProvinsi=DB::table('rekomendasi_terdaftar_yayasans as w')->select(
                'w.*',
                'rls.name',
                'usr.name',
                'prov.name_prov',
                'kota.name_cities',
                'kecamatan.name_districts',
                'b.name_village',
            )
                ->leftjoin('roles as rls', 'rls.id', '=', 'w.tujuan')
                ->leftjoin('users as usr', 'usr.id', '=', 'w.petugas')
                ->leftjoin('indonesia_provinces as prov', 'prov.code', '=', 'w.id_provinsi')
                ->leftjoin('indonesia_cities as kota', 'kota.code', '=', 'w.id_kabkot')
                ->leftjoin('indonesia_districts as kecamatan', 'kecamatan.code', '=', 'w.id_kecamatan')
                ->leftjoin('indonesia_villages as b', 'b.code', '=', 'w.id_kelurahan')
                ->where('w.no_pendaftaran', $id)->first();
        $getdata = DB::table('model_has_roles')
            ->leftjoin('rekomendasi_terdaftar_yayasans as b', 'b.tujuan', '=', 'model_has_roles.role_id')
            ->where('b.id', $id)
            ->get();
        //alur
        $user = Auth::user();
        $roles = $user->roles()->pluck('name');

        if ($roles->contains('Front Office kelurahan') || $roles->contains('Front Office kota')) {
            // Jika user memiliki role 'FO-Kota', maka tampilkan alur dengan nama 'Draft' dan 'Teruskan'
            $alur = DB::table('alur')
                ->whereIn('name', ['Draft', 'Teruskan'])
                ->get();
        } else if ($roles->contains('SekertarisDinas') || $roles->contains('kepala bidang')|| $roles->contains('Back Ofiice Kota')) {
            // Jika user memiliki role 'BO-Kota' atau 'SekertarisDinas', maka tampilkan alur dengan nama 'Kembalikan', 'Tolak', dan 'Teruskan'
            $alur = DB::table('alur')
                ->whereIn('name', ['Kembalikan', 'Tolak', 'Teruskan'])
                ->get();
        } else if ($roles->contains('KepalaDinas')) {
            // Jika user memiliki role 'Kadus', maka tampilkan alur dengan nama 'Selesai' dan 'Tolak'
            $alur = DB::table('alur')
                ->whereIn('name', ['Selesai', 'Tolak'])
                ->get();
        } else {
            // Jika user tidak memiliki role yang sesuai, maka tampilkan alur kosong
            $alur = collect();
        }


        $user = Auth::user();
        $roles = $user->roles()->pluck('name');

        if ($roles->contains('Front Office Kelurahan')) {
            $roleid = DB::table('roles')
                ->whereIn('name', ['Front Office kota', 'Back Ofiice Kota'])
                ->get();
        } elseif ($roles->contains('Front Office kota')) {
            $roleid = DB::table('roles')
                ->where('name', ['Back Ofiice Kota', 'Front Office kelurahan'])
                ->get();
        } else if ($roles->contains('Back Ofiice Kota')) {
            $roleid = DB::table('roles')
                ->whereIn('name', ['Front Office kota', 'kepala bidang'])
                ->get();
        } else if ($roles->contains('kepala bidang')) {
            $roleid = DB::table('roles')
                ->whereIn('name', ['Back Ofiice Kota', 'SekertarisDinas'])
                ->get();
        } else if ($roles->contains('SekertarisDinas')) {
            $roleid = DB::table('roles')
                ->whereIn('name', ['Back Ofiice Kota', 'KepalaDinas'])
                ->get();
        } else if ($roles->contains('KepalaDinas')) {
            $roleid = DB::table('roles')
                ->where('name', 'Front Office kota')
                ->get();
        }

        $role_id = null;
        $users = DB::table('users as u')
            ->join('model_has_roles as mhr', 'u.id', '=', 'mhr.model_id')
            ->join('roles as r', 'mhr.role_id', '=', 'r.id')
            ->select('u.id', 'u.name', 'u.email', 'r.name as role')
            ->where('mhr.model_type', '=', 'App\Models\User')
            ->where('mhr.role_id', '=', $role_id)
            ->get();
        return view('rekomendasi_yayasan_provinsis.edit', compact('wilayah', 'rekomendasiYayasanProvinsi', 'roleid', 'getdata', 'alur', 'createdby', 'getUsers', 'getAuth'));
    }
    public function prosesSurat($id)
    {
        $userid = Auth::user()->id;
        $wilayah = DB::table('wilayahs as w')->select(
            'w.id',
            'b.*',
            'w.*',
            'prov.*',
            'kota.*',
            'kecamatan.*',
            'w.status_wilayah',
            'w.createdby',
        )
            ->leftjoin('indonesia_provinces as prov', 'prov.code', '=', 'w.province_id')
            ->leftjoin('indonesia_cities as kota', 'kota.code', '=', 'w.kota_id')
            ->leftjoin('indonesia_districts as kecamatan', 'kecamatan.code', '=', 'w.kecamatan_id')
            ->leftjoin('indonesia_villages as b', 'b.code', '=', 'w.kelurahan_id')
            ->where('status_wilayah', '1')
            ->where('w.createdby', $userid)->get();


        $getUsers = DB::table('model_has_roles')
            ->leftjoin('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->leftjoin('rekomendasi_yayasans_provinsi', 'rekomendasi_yayasans_provinsi.createdby', '=', 'model_has_roles.model_id')
            ->where('rekomendasi_yayasans_provinsi.id', '=', $id)
            // ->where('status_aksi', '=', 'Draft')
            // ->orwhere('status_aksi', '=', 'Teruskan')
            ->get();

        $users =  Auth::user()->id;
        $getAuth = DB::table('model_has_roles')
            ->leftJoin('users', 'users.id', '=', 'model_has_roles.model_id')
            ->leftJoin('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('model_id', $users)
            ->get();

        // dd($checkroles2);
        //Tujuan
        $createdby = DB::table('rekomendasi_yayasans_provinsi')
            ->join('users', 'rekomendasi_yayasans_provinsi.createdby', '=', 'users.name')
            ->join('model_has_roles', 'model_has_roles.model_id', '=', 'users.id')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->select('rekomendasi_yayasans_provinsi.id', 'rekomendasi_yayasans_provinsi.createdby', 'roles.name')
            ->get();

            $rekomendasiYayasanProvinsi=DB::table('rekomendasi_yayasans_provinsi as w')->select(
                'w.*',
                'rls.name',
                'usr.name',
                'prov.name_prov',
                'kota.name_cities',
                'kecamatan.name_districts',
                'b.name_village',
            )
                ->leftjoin('roles as rls', 'rls.id', '=', 'w.tujuan')
                ->leftjoin('users as usr', 'usr.id', '=', 'w.petugas')
                ->leftjoin('indonesia_provinces as prov', 'prov.code', '=', 'w.id_provinsi')
                ->leftjoin('indonesia_cities as kota', 'kota.code', '=', 'w.id_kabkot')
                ->leftjoin('indonesia_districts as kecamatan', 'kecamatan.code', '=', 'w.id_kecamatan')
                ->leftjoin('indonesia_villages as b', 'b.code', '=', 'w.id_kelurahan')
                ->where('w.id', $id)->first();
        $getdata = DB::table('model_has_roles')
            ->leftjoin('rekomendasi_yayasans_provinsi as b', 'b.tujuan', '=', 'model_has_roles.role_id')
            ->where('b.id', $id)
            ->get();
        //alur
        $user = Auth::user();
        $roles = $user->roles()->pluck('name');

        if ($roles->contains('Front Office kelurahan') || $roles->contains('Front Office kota')) {
            // Jika user memiliki role 'FO-Kota', maka tampilkan alur dengan nama 'Draft' dan 'Teruskan'
            $alur = DB::table('alur')
                ->whereIn('name', ['Draft', 'Teruskan'])
                ->get();
        } else if ($roles->contains('SekertarisDinas') || $roles->contains('kepala bidang')|| $roles->contains('Back Ofiice Kota')) {
            // Jika user memiliki role 'BO-Kota' atau 'SekertarisDinas', maka tampilkan alur dengan nama 'Kembalikan', 'Tolak', dan 'Teruskan'
            $alur = DB::table('alur')
                ->whereIn('name', ['Kembalikan', 'Tolak', 'Teruskan'])
                ->get();
        } else if ($roles->contains('KepalaDinas')) {
            // Jika user memiliki role 'Kadus', maka tampilkan alur dengan nama 'Selesai' dan 'Tolak'
            $alur = DB::table('alur')
                ->whereIn('name', ['Selesai', 'Tolak'])
                ->get();
        } else {
            // Jika user tidak memiliki role yang sesuai, maka tampilkan alur kosong
            $alur = collect();
        }


        $user = Auth::user();
        $roles = $user->roles()->pluck('name');

        if ($roles->contains('Front Office Kelurahan')) {
            $roleid = DB::table('roles')
                ->whereIn('name', ['Front Office kota', 'Back Ofiice Kota'])
                ->get();
        } elseif ($roles->contains('Front Office kota')) {
            $roleid = DB::table('roles')
                ->where('name', ['Back Ofiice Kota', 'Front Office kelurahan'])
                ->get();
        } else if ($roles->contains('Back Ofiice Kota')) {
            $roleid = DB::table('roles')
                ->whereIn('name', ['Front Office kota', 'kepala bidang'])
                ->get();
        } else if ($roles->contains('kepala bidang')) {
            $roleid = DB::table('roles')
                ->whereIn('name', ['Back Ofiice Kota', 'SekertarisDinas'])
                ->get();
        } else if ($roles->contains('SekertarisDinas')) {
            $roleid = DB::table('roles')
                ->whereIn('name', ['Back Ofiice Kota', 'KepalaDinas'])
                ->get();
        } else if ($roles->contains('KepalaDinas')) {
            $roleid = DB::table('roles')
                ->whereIn('name', ['Back Ofiice Kota', 'Front Office kota'])
                ->get();
        }

        $role_id = null;
        $users = DB::table('users as u')
            ->join('model_has_roles as mhr', 'u.id', '=', 'mhr.model_id')
            ->join('roles as r', 'mhr.role_id', '=', 'r.id')
            ->select('u.id', 'u.name', 'u.email', 'r.name as role')
            ->where('mhr.model_type', '=', 'App\Models\User')
            ->where('mhr.role_id', '=', $role_id)
            ->get();
        return view('rekomendasi_yayasan_provinsis.edit', compact('wilayah', 'rekomendasiYayasanProvinsi', 'roleid', 'getdata', 'alur', 'createdby', 'getUsers', 'getAuth'));
    }
    public function getPetugas($id)
    {
        $users = DB::table('users as u')
            ->join('model_has_roles as mhr', 'u.id', '=', 'mhr.model_id')
            ->join('roles as r', 'mhr.role_id', '=', 'r.id')
            ->select('u.id', 'u.name', 'u.email', 'r.name as role')
            ->where('mhr.model_type', '=', 'App\Models\User')
            ->where('mhr.role_id', '=', $id)
            ->get();

        return response()->json($users);
    }
    /**
     * Update the specified rekomendasi_daftar_ulang_yayasan in storage.
     */
    

    public function update($id , Request $request)
    {
        $getdata = RekomendasiYayasansProvinsi::where('no_pendaftaran', $id)->first();
        $data = $request->all();
        // dd($request->all());
        $files = [
            'akta_notarispendirian',
            'adart',
            'struktur_organisasi',
            'laporan_keuangan',
            'laporan_kegiatan',
            'struktur_organisasi',
            'foto_ktp_pengurus',
            'no_wajibpajak',
            'data_terimalayanan',
            'foto_plang',
            'visi_misi',
            'proker_yayasan',
            'data_aset',
            'data_sdm',
            'form_kelengkapanberkas',
            'file_permohonan',
        ];
        foreach ($files as $file) {
            if ($request->file($file)) {
                $path = $request->file($file);
                $nama_file = 'yayasan/' . $file . '/' . $path->getClientOriginalName();
                Storage::disk('imagekit')->put($nama_file, fopen($path->getRealPath(), 'r'));
                $data[$file] = Storage::disk('imagekit')->url($nama_file);
            } else {
                $data[$file] = $getdata->$file;
            }
        }
        // dd($data);
        $data['updatedby'] = Auth::user()->id;
        // dd($getdata->id);
        $getdata->update($data);
       

        $logpengaduan = new log_yayasanProvinsi();
        $logpengaduan['id_trx_yaprov'] = $getdata->id;
        $logpengaduan['id_alur_yaprov'] = $request->get('keterangan_yayasan_provinsi');
        $logyayasan['validasi_surat'] = $request->get('validasi_surat');
        $logpengaduan['petugas_yaprov'] = $request->get('petugas');
        $logpengaduan['catatan_yaprov']  = $request->get('tl_catatan');
        $logpengaduan['file_pendukung_yaprov'] = $request->get('file_permohonan');
        $logpengaduan['tujuan_yaprov'] = $request->get('tujuan');
        $logpengaduan['created_by_yaprov'] = Auth::user()->id;
        $logpengaduan->save();
        return redirect()->route('rekomendasi_yayasan_provinsis.index')->with('success', 'Data berhasil diupdate.');
    }
    public function proses($id , Request $request)
    {
        $getdata = rekomendasi_terdaftar_yayasan::where('no_pendaftaran', $id)->first();
        $data = $request->all();
        // dd($data);
        $files = [
            'akta_notarispendirian',
            'adart',
            'struktur_organisasi',
            'laporan_keuangan',
            'laporan_kegiatan',
            'struktur_organisasi',
            'foto_ktp_pengurus',
            'no_wajibpajak',
            'data_terimalayanan',
            'foto_plang',
            'visi_misi',
            'proker_yayasan',
            'data_aset',
            'data_sdm',
            'form_kelengkapanberkas',
            'file_permohonan',
        ];
        $data = new RekomendasiYayasansProvinsi();
        foreach ($files as $file) {
            if ($request->file($file)) {
                $path = $request->file($file);
                $nama_file = 'yayasan/' . $file . '/' . $path->getClientOriginalName();
                Storage::disk('imagekit')->put($nama_file, fopen($path->getRealPath(), 'r'));
                $data[$file] = Storage::disk('imagekit')->url($nama_file);
            } else {
                $data[$file] = $getdata->$file;
            }
        }
        $data['no_pendaftaran'] = $getdata->no_pendaftaran;
        $data['id_alur'] =  $getdata->id_alur;
        $data['id_provinsi'] =  $getdata->id_provinsi;
        $data['id_kabkot'] =  $getdata->id_kabkot;
        $data['id_kecamatan'] =  $getdata->id_kecamatan;
        $data['id_kelurahan'] =  $getdata->id_kelurahan;
        $data['nama_pel'] =  $getdata->nama_pel;
        $data['nik_pel'] =  $getdata->nik_pel;
        $data['status_kepengurusan'] =  $getdata->status_kepengurusan;
        $data['alamat_pel'] =  $getdata->alamat_pel;
        $data['telp_pel'] =  $getdata->telp_pel;
        $data['nama_lembaga'] =  $getdata->nama_lembaga;
        $data['alamat_lembaga'] =  $getdata->alamat_lembaga;
        $data['nama_notaris'] =  $getdata->nama_notaris;
        $data['notgl_akta'] =  $getdata->notgl_akta;
        $data['nama_ketua'] =  $getdata->nama_ketua;
        $data['status'] =  $getdata->status;
        $data['tipe'] =  $getdata->tipe;
        $data['no_ahu'] =  $getdata->no_ahu;
        $data['tgl_mulai'] =  $getdata->tgl_mulai;
        $data['tgl_selesai'] =  $getdata->tgl_selesai;
        $data['Lingkup_Wilayah_Kerja'] =  $getdata->Lingkup_Wilayah_Kerja;
        $data['catatan'] = $request->get('catatan');
        $data['status_alur'] = $request->get('status_alur');
        $data['tujuan'] = $request->get('tujuan');
        $data['petugas'] = $request->get('petugas');
        $data['keterangan_yayasan_provinsi'] = $request->get('keterangan_yayasan_provinsi');
        $data['createdby'] = Auth::user()->id;
        $data['updatedby'] = Auth::user()->id;
        // dd($data);
        $data->save();
       

        $logpengaduan = new log_yayasanProvinsi();
        $logpengaduan['id_trx_yaprov'] = $data->id;
        $logpengaduan['id_alur_yaprov'] = $request->get('keterangan_yayasan_provinsi');
        $logyayasan['validasi_surat'] = $request->get('validasi_surat');
        $logpengaduan['petugas_yaprov'] = $request->get('petugas');
        $logpengaduan['catatan_yaprov']  = $request->get('tl_catatan');
        $logpengaduan['file_pendukung_yaprov'] = $request->get('file_permohonan');
        $logpengaduan['tujuan_yaprov'] = $request->get('tujuan');
        $logpengaduan['created_by_yaprov'] = Auth::user()->id;
        $logpengaduan->save();
        return redirect()->route('rekomendasi_yayasan_provinsis.index')->with('success', 'Data berhasil diupdate.');
    }

    /**
     * Remove the specified rekomendasi_daftar_ulang_yayasan from storage.
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $rekomendasi = rekomendasi_terdaftar_yayasan::findOrFail($id);

        // delete uploaded files
        if (!empty($rekomendasi->filektp)) {
            Storage::delete('upload/ktp/' . $rekomendasi->filektp);
        }
        if (!empty($rekomendasi->filekk)) {
            Storage::delete('upload/kk/' . $rekomendasi->filekk);
        }
        if (!empty($rekomendasi->suket)) {
            Storage::delete('upload/suket/' . $rekomendasi->suket);
        }
        if (!empty($rekomendasi->draftfrom)) {
            Storage::delete('upload/draftFrom/' . $rekomendasi->draftfrom);
        }

        // delete the record
        $rekomendasi->delete();

        return redirect()->route('rekomendasi-terdaftar.index')
            ->with('success', 'Rekomendasi terdaftar yayasan berhasil dihapus.');
    }

 
    public function diproses(Request $request)
    {
        $user_id = Auth::user()->id;
        $user_wilayah = DB::table('wilayahs')
            ->leftJoin('model_has_roles', 'model_has_roles.model_id', '=', 'wilayahs.createdby')
            ->leftJoin('users', 'users.id', '=', 'model_has_roles.model_id')
            ->leftJoin('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->select('wilayahs.*','roles.name','model_has_roles.*')
            ->where('createdby', $user_id)
            ->where(function ($query) {
                $query->where('status_wilayah', 1);
            })
            ->first();

        // dd($user_wilayah);
        if ($user_wilayah->name == 'fasilitator') {
            $query = DB::table('rekomendasi_yayasans_provinsi')
                ->join('users', 'users.id', '=', 'rekomendasi_yayasans_provinsi.petugas')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_yayasans_provinsi.id_kecamatan')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_yayasans_provinsi.id_kelurahan')
                ->select('rekomendasi_yayasans_provinsi.*', 'b.name_village', 'd.name_districts', 'users.name');
        } elseif ($user_wilayah->name == 'Front Office kota') {
            $query = DB::table('rekomendasi_yayasans_provinsi')
                ->join('users', 'users.id', '=', 'rekomendasi_yayasans_provinsi.petugas')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_yayasans_provinsi.id_kecamatan')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_yayasans_provinsi.id_kelurahan')
                ->select('rekomendasi_yayasans_provinsi.*', 'b.name_village', 'd.name_districts', 'users.name');
        } elseif ($user_wilayah->name == 'Front Office Kelurahan') {
            $query = DB::table('rekomendasi_yayasans_provinsi')
                ->join('users', 'users.id', '=', 'rekomendasi_yayasans_provinsi.petugas')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_yayasans_provinsi.id_kecamatan')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_yayasans_provinsi.id_kelurahan')
                ->select('rekomendasi_yayasans_provinsi.*', 'b.name_village', 'd.name_districts', 'users.name');
        } elseif ($user_wilayah->name == 'supervisor') {
            $query = DB::table('rekomendasi_yayasans_provinsi')
                ->join('users', 'users.id', '=', 'rekomendasi_yayasans_provinsi.petugas')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_yayasans_provinsi.id_kecamatan')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_yayasans_provinsi.id_kelurahan')
                ->select('rekomendasi_yayasans_provinsi.*', 'b.name_village', 'd.name_districts', 'users.name');
        } elseif ($user_wilayah->name == 'Back Ofiice kelurahan') {
            $query = DB::table('rekomendasi_yayasans_provinsi')
                ->join('users', 'users.id', '=', 'rekomendasi_yayasans_provinsi.petugas')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_yayasans_provinsi.id_kecamatan')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_yayasans_provinsi.id_kelurahan')
                ->select('rekomendasi_yayasans_provinsi.*', 'b.name_village', 'd.name_districts', 'users.name');
        } elseif ($user_wilayah->name == 'Back Ofiice Kota') {
            $query = DB::table('rekomendasi_yayasans_provinsi')
                ->join('users', 'users.id', '=', 'rekomendasi_yayasans_provinsi.petugas')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_yayasans_provinsi.id_kecamatan')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_yayasans_provinsi.id_kelurahan')
                ->select('rekomendasi_yayasans_provinsi.*', 'b.name_village', 'd.name_districts', 'users.name');
        } elseif ($user_wilayah->name == 'kepala bidang') {
            $query = DB::table('rekomendasi_yayasans_provinsi')
                ->join('users', 'users.id', '=', 'rekomendasi_yayasans_provinsi.petugas')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_yayasans_provinsi.id_kecamatan')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_yayasans_provinsi.id_kelurahan')
                ->select('rekomendasi_yayasans_provinsi.*', 'b.name_village', 'd.name_districts', 'users.name');
        } elseif ($user_wilayah->name == 'SekertarisDinas') {
            $query = DB::table('rekomendasi_yayasans_provinsi')
                ->join('users', 'users.id', '=', 'rekomendasi_yayasans_provinsi.petugas')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_yayasans_provinsi.id_kecamatan')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_yayasans_provinsi.id_kelurahan')
                ->select('rekomendasi_yayasans_provinsi.*', 'b.name_village', 'd.name_districts', 'users.name');
        } elseif ($user_wilayah->name == 'KepalaDinas') {
            $query = DB::table('rekomendasi_yayasans_provinsi')
                ->join('users', 'users.id', '=', 'rekomendasi_yayasans_provinsi.petugas')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_yayasans_provinsi.id_kecamatan')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_yayasans_provinsi.id_kelurahan')
                ->select('rekomendasi_yayasans_provinsi.*', 'b.name_village', 'd.name_districts', 'users.name');
        } else {
            $query = DB::table('rekomendasi_yayasans_provinsi')
                ->join('users', 'users.id', '=', 'rekomendasi_yayasans_provinsi.createdby')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_yayasans_provinsi.id_kelurahan')
                ->select('rekomendasi_yayasans_provinsi.*', 'b.name_village');
        }
        if ($user_wilayah->name == 'fasilitator') {
            $query->orWhere(function ($query) use ($user_wilayah) {
                $query->where('rekomendasi_yayasans_provinsi.id_kelurahan', '=', $user_wilayah->kelurahan_id)
                    ->where('rekomendasi_yayasans_provinsi.tujuan', '=', $user_wilayah->role_id)
                    ->where('rekomendasi_yayasans_provinsi.petugas', '=', auth::user()->id)

                    ->where(function ($query) {
                        $query->where('rekomendasi_yayasans_provinsi.keterangan_yayasan_provinsi', '=', 'Rekomendasi Diteruskan')
                            ->orWhere('rekomendasi_yayasans_provinsi.keterangan_yayasan_provinsi', '=', 'Rekomendasi Dikembalikan');
                    });
            });
        }
        if ($user_wilayah->name == 'Front Office Kelurahan') {
            $query->orWhere(function ($query) use ($user_wilayah) {
                $query->where('rekomendasi_yayasans_provinsi.id_kelurahan', '=', $user_wilayah->kelurahan_id)
                    ->where('rekomendasi_yayasans_provinsi.tujuan', '=', $user_wilayah->role_id)
                    ->where('rekomendasi_yayasans_provinsi.petugas', '=', auth::user()->id)

                    ->where(function ($query) {
                        $query->where('rekomendasi_yayasans_provinsi.keterangan_yayasan_provinsi', '=', 'Rekomendasi Diteruskan')
                            ->orWhere('rekomendasi_yayasans_provinsi.keterangan_yayasan_provinsi', '=', 'Rekomendasi Dikembalikan');
                    });
            });
        }
        if ($user_wilayah->name == 'Front Office kota') {
            $query->orWhere(function ($query) use ($user_wilayah) {
                $query->where('rekomendasi_yayasans_provinsi.id_kabkot', '=', $user_wilayah->kota_id)
                    ->where('rekomendasi_yayasans_provinsi.tujuan', '=', $user_wilayah->role_id)
                    ->where('rekomendasi_yayasans_provinsi.petugas', '=', auth::user()->id)

                    ->where(function ($query) {
                        $query->where('rekomendasi_yayasans_provinsi.keterangan_yayasan_provinsi', '=', 'Rekomendasi Diteruskan')
                            ->orWhere('rekomendasi_yayasans_provinsi.keterangan_yayasan_provinsi', '=', 'Rekomendasi Dikembalikan');
                    });
            });
        }
        if ($user_wilayah->name == 'supervisor') {
            $query->orWhere(function ($query) use ($user_wilayah) {
                $query->where('rekomendasi_yayasans_provinsi.id_kabkot', '=', $user_wilayah->kota_id)
                    ->where('rekomendasi_yayasans_provinsi.tujuan', '=', $user_wilayah->role_id)
                    ->where('rekomendasi_yayasans_provinsi.petugas', '=', auth::user()->id)
                    ->where(function ($query) {
                        $query->where('rekomendasi_yayasans_provinsi.keterangan_yayasan_provinsi', '=', 'Rekomendasi Diteruskan')
                            ->orWhere('rekomendasi_yayasans_provinsi.keterangan_yayasan_provinsi', '=', 'Rekomendasi Dikembalikan');
                    });
                // dd($va);
            });
        }



        if ($user_wilayah->name == 'Back Ofiice Kota') {
            $query->orWhere(function ($query) use ($user_wilayah) {
                $query->where('rekomendasi_yayasans_provinsi.id_kabkot', '=', $user_wilayah->kota_id)
                    ->where('rekomendasi_yayasans_provinsi.tujuan', '=', $user_wilayah->role_id)
                    ->where('rekomendasi_yayasans_provinsi.petugas', '=', auth::user()->id)
                    ->where(function ($query) {
                        $query->where('rekomendasi_yayasans_provinsi.keterangan_yayasan_provinsi', '=', 'Rekomendasi Diteruskan')
                            ->orWhere('rekomendasi_yayasans_provinsi.keterangan_yayasan_provinsi', '=', 'Rekomendasi Dikembalikan');
                    });
                // dd($va);
            });
            // dd($query->count());
        }
        if ($user_wilayah->name == 'Back Ofiice kelurahan') {
            $query->orWhere(function ($query) use ($user_wilayah) {
                $query->where('rekomendasi_yayasans_provinsi.id_kelurahan', '=', $user_wilayah->kelurahan_id)
                    ->where('rekomendasi_yayasans_provinsi.tujuan', '=', $user_wilayah->role_id)
                    ->where('rekomendasi_yayasans_provinsi.petugas', '=', auth::user()->id)
                    ->where(function ($query) {
                        $query->where('rekomendasi_yayasans_provinsi.keterangan_yayasan_provinsi', '=', 'Rekomendasi Diteruskan')
                            ->orWhere('rekomendasi_yayasans_provinsi.keterangan_yayasan_provinsi', '=', 'Rekomendasi Dikembalikan');
                    });
                // dd($va);
            });
            // dd($query->count());
        }
        if ($user_wilayah->name == 'kepala bidang') {
            $query->orWhere(function ($query) use ($user_wilayah) {
                $query->where('rekomendasi_yayasans_provinsi.id_kabkot', '=', $user_wilayah->kota_id)
                    ->where('rekomendasi_yayasans_provinsi.tujuan', '=', $user_wilayah->role_id)
                    ->where('rekomendasi_yayasans_provinsi.petugas', '=', auth::user()->id)
                    ->where(function ($query) {
                        $query->where('rekomendasi_yayasans_provinsi.keterangan_yayasan_provinsi', '=', 'Rekomendasi Diteruskan')
                            ->orWhere('rekomendasi_yayasans_provinsi.keterangan_yayasan_provinsi', '=', 'Rekomendasi Dikembalikan');
                    });
                // dd($va);
            });
        }
        if ($user_wilayah->name == 'SekertarisDinas') {
            $query->orWhere(function ($query) use ($user_wilayah) {
                $query->where('rekomendasi_yayasans_provinsi.id_kabkot', '=', $user_wilayah->kota_id)
                    ->where('rekomendasi_yayasans_provinsi.tujuan', '=', $user_wilayah->role_id)
                    ->where('rekomendasi_yayasans_provinsi.petugas', '=', auth::user()->id)
                    ->where(function ($query) {
                        $query->where('rekomendasi_yayasans_provinsi.keterangan_yayasan_provinsi', '=', 'Rekomendasi Diteruskan')
                            ->orWhere('rekomendasi_yayasans_provinsi.keterangan_yayasan_provinsi', '=', 'Rekomendasi Dikembalikan');
                    });
                // dd($va);
            });
        }
        if ($user_wilayah->name == 'KepalaDinas') {
            $query->orWhere(function ($query) use ($user_wilayah) {
                $query->where('rekomendasi_yayasans_provinsi.id_kabkot', '=', $user_wilayah->kota_id)
                    ->where('rekomendasi_yayasans_provinsi.tujuan', '=', $user_wilayah->role_id)
                    ->where('rekomendasi_yayasans_provinsi.petugas', '=', auth::user()->id)
                    ->where(function ($query) {
                        $query->where('rekomendasi_yayasans_provinsi.keterangan_yayasan_provinsi', '=', 'Rekomendasi Diteruskan')
                            ->orWhere('rekomendasi_yayasans_provinsi.keterangan_yayasan_provinsi', '=', 'Rekomendasi Dikembalikan');
                    });
                // dd($va);
            });
        }
        if ($user_wilayah->name == 'Front Office Kelurahan' || $user_wilayah->name == 'Back Ofiice kelurahan'|| $user_wilayah->name == 'fasilitator') {
            if ($request->has('search') && !empty($request->search['value'])) {
                $search = $request->search['value'];
                    $query->orWhere(function ($query) use ($user_wilayah, $search) {
                        $query->where('rekomendasi_yayasans_provinsi.id_kelurahan', '=', $user_wilayah->kelurahan_id)
                            ->where('rekomendasi_yayasans_provinsi.tujuan', '=', $user_wilayah->role_id)
                            ->where('rekomendasi_yayasans_provinsi.petugas', '=', auth::user()->id)
                            ->where(function ($query) {
                                $query->where('rekomendasi_yayasans_provinsi.keterangan_yayasan_provinsi', '=', 'Rekomendasi Diteruskan')
                                    ->orWhere('rekomendasi_yayasans_provinsi.keterangan_yayasan_provinsi', '=', 'Rekomendasi Dikembalikan');
                            })
                            ->where('rekomendasi_yayasans_provinsi.no_pendaftaran', 'like', "%$search%");
                        
                    });
                    // dd($query);
            }
        }elseif($user_wilayah->name == 'Front Office Kota' || $user_wilayah->name == 'Back Ofiice Kota'|| $user_wilayah->name == 'KepalaDinas'||$user_wilayah->name == 'SekertarisDinas'||$user_wilayah->name == 'Supervisor') {
            if ($request->has('search') && !empty($request->search['value'])) {
                $search = $request->search['value'];
                $query->orWhere(function ($query) use ($user_wilayah, $search) {
                    $query->where('rekomendasi_yayasans_provinsi.id_kabkot', '=', $user_wilayah->kota_id)
                        ->where('rekomendasi_yayasans_provinsi.tujuan', '=', $user_wilayah->role_id)
                        ->where('rekomendasi_yayasans_provinsi.petugas', '=', auth::user()->id)
                        ->where('rekomendasi_yayasans_provinsi.no_pendaftaran', 'like', "%$search%")
                        ->where(function ($query) {
                            $query->where('rekomendasi_yayasans_provinsi.keterangan_yayasan_provinsi', '=', 'Rekomendasi Diteruskan')
                                ->orWhere('rekomendasi_yayasans_provinsi.keterangan_yayasan_provinsi', '=', 'Rekomendasi Dikembalikan');
                        });
                    // dd($va);
                });
            }
        }
        // Get total count of filtered items
        $total_filtered_items = $query->count();
        // dd($total_filtered_items);
        // Add ordering
        // if ($request->has('order')) {
        //     $order_column = $request->order[0]['column'];
        //     $order_direction = $request->order[0]['dir'];
        //     $query->orderBy($request->input('columns.' . $order_column . '.data'), $order_direction);
        // }
        // Get paginated data
        $data = $query->get();
        $userLogin = DB::table('wilayahs')
                                ->join('model_has_roles', 'model_has_roles.model_id', '=', 'wilayahs.createdby')
                                ->join('users', 'users.id', '=', 'model_has_roles.model_id')
                                ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
                                ->select('roles.name')
                                ->where('wilayahs.createdby', $user_id)
                                ->where(function ($query) {
                                    $query->where('status_wilayah', 1);
                                })
                                ->first();
        // dd($userLogin);
        $result = array();

        foreach ($data as $tmp) {
        $gabunganData = new stdClass();
        $gabunganData->userLogin=$userLogin;
        $gabunganData->dataRekom=$tmp;
           array_push($result, $gabunganData);
        }

        return response()->json([
            'draw' => $request->input('draw'),
            'recordsTotal' => RekomendasiYayasansProvinsi::count(),
            'recordsFiltered' => $total_filtered_items,
            'data' => $result,
        ]);
    }

    public function teruskan(Request $request)
    {
        $user_name = Auth::user()->name;
        // dd($user_name);

        $query = DB::table('rekomendasi_yayasans_provinsi')
            ->join('users', 'users.id', '=', 'rekomendasi_yayasans_provinsi.petugas')
            ->leftjoin('indonesia_villages as b', 'b.code', '=', 'rekomendasi_yayasans_provinsi.id_kelurahan')
            ->leftjoin('indonesia_districts as d', 'd.code', '=', 'rekomendasi_yayasans_provinsi.id_kecamatan')
            ->select('rekomendasi_yayasans_provinsi.*', 'b.name_village', 'd.name_districts', 'users.name');

        $user_id = Auth::user()->id;
        // dd($user_id);

        $user_wilayah = DB::table('wilayahs')
                        ->leftJoin('model_has_roles', 'model_has_roles.model_id', '=', 'wilayahs.createdby')
                        ->leftJoin('users', 'users.id', '=', 'model_has_roles.model_id')
                        ->leftJoin('roles', 'roles.id', '=', 'model_has_roles.role_id')
                        ->select('wilayahs.*','roles.name','model_has_roles.*')
                        ->where('createdby', $user_id)
                        ->where(function ($query) {
                            $query->where('status_wilayah', 1);
                        })
                        ->first();
        if ($user_wilayah->name == 'fasilitator') {
            $query = DB::table('rekomendasi_yayasans_provinsi')
                ->join('users', 'users.id', '=', 'rekomendasi_yayasans_provinsi.petugas')
                // ->join('log_ulangyayasan', 'log_ulangyayasan.id_trx_ulangYayasan_ulangYayasan', '=', 'rekomendasi_yayasans_provinsi.id')
                ->join('rekomendasi_terdaftar_yayasans', 'rekomendasi_yayasans_provinsi.no_pendaftaran','=', 'rekomendasi_terdaftar_yayasans.no_pendaftaran')
                // ->join('model_has_roles', 'model_has_roles.model_id', '=', 'rekomendasi_yayasans_provinsi.tujuan')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_yayasans_provinsi.id_kelurahan')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_yayasans_provinsi.id_kecamatan')
                ->select(
                    'rekomendasi_yayasans_provinsi.id',
                    'rekomendasi_yayasans_provinsi.no_pendaftaran',
                    'rekomendasi_yayasans_provinsi.nama_lembaga',
                    'rekomendasi_yayasans_provinsi.nama_ketua',
                    'rekomendasi_yayasans_provinsi.alamat_lembaga',
                    'rekomendasi_yayasans_provinsi.status_alur',
                    'rekomendasi_yayasans_provinsi.keterangan_yayasan_provinsi',
                    'rekomendasi_yayasans_provinsi.tujuan',
                    'rekomendasi_terdaftar_yayasans.keterangan_daftar_ulang', 
                    'b.name_village', 
                    'd.name_districts', 
                    'users.name')
                    ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_yayasans_provinsi.id_kelurahan', $user_wilayah->kelurahan_id)
                        ->where('rekomendasi_yayasans_provinsi.tujuan', '!=', $user_wilayah->role_id)
                        // ->where('log_ulangyayasan.created_by_ulangYayasan', '=', auth::user()->id)
                        // ->where('rekomendasi_yayasans_provinsi.petugas','!=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_yayasans_provinsi.keterangan_yayasan_provinsi', '=', 'Rekomendasi diteruskan')
                                ->orWhere('rekomendasi_yayasans_provinsi.keterangan_yayasan_provinsi', '=', 'Rekomendasi Dikembalikan');
                        });
                })->distinct();
            // dd($query);

        }
        if ($user_wilayah->name == 'Front Office Kelurahan') {
            $query = DB::table('rekomendasi_yayasans_provinsi')
                ->join('users', 'users.id', '=', 'rekomendasi_yayasans_provinsi.petugas')
                // ->join('log_ulangyayasan', 'log_ulangyayasan.id_trx_ulangYayasan_ulangYayasan', '=', 'rekomendasi_yayasans_provinsi.id')
                ->join('rekomendasi_terdaftar_yayasans', 'rekomendasi_yayasans_provinsi.no_pendaftaran','=', 'rekomendasi_terdaftar_yayasans.no_pendaftaran')
                // ->join('model_has_roles', 'model_has_roles.model_id', '=', 'rekomendasi_yayasans_provinsi.tujuan')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_yayasans_provinsi.id_kelurahan')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_yayasans_provinsi.id_kecamatan')
                ->select(
                    'rekomendasi_yayasans_provinsi.id',
                    'rekomendasi_yayasans_provinsi.no_pendaftaran',
                    'rekomendasi_yayasans_provinsi.nama_lembaga',
                    'rekomendasi_yayasans_provinsi.nama_ketua',
                    'rekomendasi_yayasans_provinsi.alamat_lembaga',
                    'rekomendasi_yayasans_provinsi.status_alur',
                    'rekomendasi_yayasans_provinsi.keterangan_yayasan_provinsi',
                    'rekomendasi_yayasans_provinsi.tujuan',
                    'rekomendasi_terdaftar_yayasans.keterangan_daftar_ulang', 
                    'b.name_village', 
                    'd.name_districts', 
                    'users.name')
                    ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_yayasans_provinsi.id_kelurahan', $user_wilayah->kelurahan_id)
                        ->where('rekomendasi_yayasans_provinsi.tujuan', '!=', $user_wilayah->role_id)
                        // ->where('log_ulangyayasan.created_by_ulangYayasan', '=', auth::user()->id)
                        // ->where('rekomendasi_yayasans_provinsi.petugas','!=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_yayasans_provinsi.keterangan_yayasan_provinsi', '=', 'Rekomendasi diteruskan')
                                ->orWhere('rekomendasi_yayasans_provinsi.keterangan_yayasan_provinsi', '=', 'Rekomendasi Dikembalikan');
                        });
                })->distinct();
            // dd($query);

        }
        if ($user_wilayah->name == 'Front Office kota') {
            $query = DB::table('rekomendasi_yayasans_provinsi')
                ->join('users', 'users.id', '=', 'rekomendasi_yayasans_provinsi.petugas')
                // ->join('log_ulangyayasan', 'log_ulangyayasan.id_trx_ulangYayasan_ulangYayasan', '=', 'rekomendasi_yayasans_provinsi.id')
                ->join('rekomendasi_terdaftar_yayasans', 'rekomendasi_yayasans_provinsi.no_pendaftaran','=', 'rekomendasi_terdaftar_yayasans.no_pendaftaran')
                // ->join('model_has_roles', 'model_has_roles.model_id', '=', 'rekomendasi_yayasans_provinsi.tujuan')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_yayasans_provinsi.id_kelurahan')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_yayasans_provinsi.id_kecamatan')
                ->select(
                    'rekomendasi_yayasans_provinsi.id',
                    'rekomendasi_yayasans_provinsi.no_pendaftaran',
                    'rekomendasi_yayasans_provinsi.nama_lembaga',
                    'rekomendasi_yayasans_provinsi.nama_ketua',
                    'rekomendasi_yayasans_provinsi.alamat_lembaga',
                    'rekomendasi_yayasans_provinsi.status_alur',
                    'rekomendasi_yayasans_provinsi.keterangan_yayasan_provinsi',
                    'rekomendasi_yayasans_provinsi.tujuan',
                    'rekomendasi_terdaftar_yayasans.keterangan_daftar_ulang', 
                    'b.name_village', 
                    'd.name_districts', 
                    'users.name')
                    ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_yayasans_provinsi.id_kabkot', $user_wilayah->kota_id)
                        ->where('rekomendasi_yayasans_provinsi.tujuan', '!=', $user_wilayah->role_id)
                        // ->where('log_ulangyayasan.created_by_ulangYayasan', '=', auth::user()->id)
                        // ->where('rekomendasi_yayasans_provinsi.petugas','!=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_yayasans_provinsi.keterangan_yayasan_provinsi', '=', 'Rekomendasi diteruskan')
                                ->orWhere('rekomendasi_yayasans_provinsi.keterangan_yayasan_provinsi', '=', 'Rekomendasi Dikembalikan');
                        });
                })->distinct();
            // dd($query);

        }

        //Back office kota 
        if ($user_wilayah->name == 'Back Ofiice Kota') {
            // dd($user_wilayah->model_id);
              $query = DB::table('rekomendasi_yayasans_provinsi')
                ->join('users', 'users.id', '=', 'rekomendasi_yayasans_provinsi.petugas')
                // ->join('log_ulangyayasan', 'log_ulangyayasan.id_trx_ulangYayasan_ulangYayasan', '=', 'rekomendasi_yayasans_provinsi.id')
                ->join('rekomendasi_terdaftar_yayasans', 'rekomendasi_yayasans_provinsi.no_pendaftaran','=', 'rekomendasi_terdaftar_yayasans.no_pendaftaran')
                // ->join('model_has_roles', 'model_has_roles.model_id', '=', 'rekomendasi_yayasans_provinsi.tujuan')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_yayasans_provinsi.id_kelurahan')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_yayasans_provinsi.id_kecamatan')
                ->select(
                    'rekomendasi_yayasans_provinsi.id',
                    'rekomendasi_yayasans_provinsi.no_pendaftaran',
                    'rekomendasi_yayasans_provinsi.nama_lembaga',
                    'rekomendasi_yayasans_provinsi.nama_ketua',
                    'rekomendasi_yayasans_provinsi.alamat_lembaga',
                    'rekomendasi_yayasans_provinsi.status_alur',
                    'rekomendasi_yayasans_provinsi.keterangan_yayasan_provinsi',
                    'rekomendasi_yayasans_provinsi.tujuan',
                    'rekomendasi_terdaftar_yayasans.keterangan_daftar_ulang', 
                    'b.name_village', 
                    'd.name_districts', 
                    'users.name')
                    ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_yayasans_provinsi.id_kabkot', $user_wilayah->kota_id)
                        // ->where('rekomendasi_yayasans_provinsi.tujuan', '!=', $user_wilayah->role_id)
                        // ->where('log_ulangyayasan.created_by_ulangYayasan', '=', auth::user()->id)
                        // ->where('rekomendasi_yayasans_provinsi.petugas','!=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_yayasans_provinsi.keterangan_yayasan_provinsi', '=', 'Rekomendasi diteruskan')
                                ->orWhere('rekomendasi_yayasans_provinsi.keterangan_yayasan_provinsi', '=', 'Rekomendasi Dikembalikan');
                        });
                    })->distinct();
            // dd($query);

        }
        if ($user_wilayah->name == 'Back Ofiice kelurahan') {
            // dd($user_wilayah->model_id);
            $query = DB::table('rekomendasi_yayasans_provinsi')
                ->join('users', 'users.id', '=', 'rekomendasi_yayasans_provinsi.petugas')
                ->join('log_ulangyayasan', 'log_ulangyayasan.id_trx_ulangYayasan', '=', 'rekomendasi_yayasans_provinsi.id')
                // ->join('model_has_roles', 'model_has_roles.model_id', '=', 'rekomendasi_yayasans_provinsi.tujuan')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_yayasans_provinsi.id_kelurahan')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_yayasans_provinsi.id_kecamatan')
                ->select('rekomendasi_yayasans_provinsi.*', 'b.name_village', 'd.name_districts', 'log_ulangyayasan.tujuan_ulangYayasan', 'log_ulangyayasan.petugas_ulangYayasan', 'users.name')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_yayasans_provinsi.id_kelurahan', $user_wilayah->kelurahan_id)
                        ->where('log_ulangyayasan.tujuan_ulangYayasan', '!=', $user_wilayah->role_id)
                        ->where('log_ulangyayasan.created_by_ulangYayasan', '=', auth::user()->id)
                        // ->where('rekomendasi_yayasans_provinsi.petugas','!=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_yayasans_provinsi.keterangan_yayasan_provinsi', '=', 'Rekomendasi diteruskan')
                                ->orWhere('rekomendasi_yayasans_provinsi.keterangan_yayasan_provinsi', '=', 'Rekomendasi Dikembalikan');
                        });
                })->distinct();
        }

        //Back office kota 
        if ($user_wilayah->name == 'supervisor') {
            // dd($user_wilayah->model_id);
            $query = DB::table('rekomendasi_yayasans_provinsi')
                ->join('users', 'users.id', '=', 'rekomendasi_yayasans_provinsi.petugas')
                // ->join('log_ulangyayasan', 'log_ulangyayasan.id_trx_ulangYayasan_ulangYayasan', '=', 'rekomendasi_yayasans_provinsi.id')
                ->join('rekomendasi_terdaftar_yayasans', 'rekomendasi_yayasans_provinsi.no_pendaftaran','=', 'rekomendasi_terdaftar_yayasans.no_pendaftaran')
                // ->join('model_has_roles', 'model_has_roles.model_id', '=', 'rekomendasi_yayasans_provinsi.tujuan')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_yayasans_provinsi.id_kelurahan')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_yayasans_provinsi.id_kecamatan')
                ->select(
                    'rekomendasi_yayasans_provinsi.id',
                    'rekomendasi_yayasans_provinsi.no_pendaftaran',
                    'rekomendasi_yayasans_provinsi.nama_lembaga',
                    'rekomendasi_yayasans_provinsi.nama_ketua',
                    'rekomendasi_yayasans_provinsi.alamat_lembaga',
                    'rekomendasi_yayasans_provinsi.status_alur',
                    'rekomendasi_yayasans_provinsi.keterangan_yayasan_provinsi',
                    'rekomendasi_yayasans_provinsi.tujuan',
                    'rekomendasi_terdaftar_yayasans.keterangan_daftar_ulang', 
                    'b.name_village', 
                    'd.name_districts', 
                    'users.name')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_yayasans_provinsi.id_kabkot', $user_wilayah->kota_id)
                        ->where('rekomendasi_yayasans_provinsi.tujuan', '!=', $user_wilayah->role_id)
                        // ->where('log_ulangyayasan.created_by_ulangYayasan', '=', auth::user()->id)
                        // ->where('rekomendasi_yayasans_provinsi.petugas','!=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_yayasans_provinsi.keterangan_yayasan_provinsi', '=', 'Rekomendasi diteruskan')
                                ->orWhere('rekomendasi_yayasans_provinsi.keterangan_yayasan_provinsi', '=', 'Rekomendasi Dikembalikan');
                        });
                })->distinct();
        }

        if ($user_wilayah->name == 'kepala bidang') {
            // dd( $user_wilayah->role_id);
            $query = DB::table('rekomendasi_yayasans_provinsi')
                ->join('users', 'users.id', '=', 'rekomendasi_yayasans_provinsi.petugas')
                // ->join('log_ulangyayasan', 'log_ulangyayasan.id_trx_ulangYayasan_ulangYayasan', '=', 'rekomendasi_yayasans_provinsi.id')
                ->join('rekomendasi_terdaftar_yayasans', 'rekomendasi_yayasans_provinsi.no_pendaftaran','=', 'rekomendasi_terdaftar_yayasans.no_pendaftaran')
                // ->join('model_has_roles', 'model_has_roles.model_id', '=', 'rekomendasi_yayasans_provinsi.tujuan')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_yayasans_provinsi.id_kelurahan')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_yayasans_provinsi.id_kecamatan')
                ->select(
                    'rekomendasi_yayasans_provinsi.id',
                    'rekomendasi_yayasans_provinsi.no_pendaftaran',
                    'rekomendasi_yayasans_provinsi.nama_lembaga',
                    'rekomendasi_yayasans_provinsi.nama_ketua',
                    'rekomendasi_yayasans_provinsi.alamat_lembaga',
                    'rekomendasi_yayasans_provinsi.status_alur',
                    'rekomendasi_yayasans_provinsi.keterangan_yayasan_provinsi',
                    'rekomendasi_yayasans_provinsi.tujuan',
                    'rekomendasi_terdaftar_yayasans.keterangan_daftar_ulang', 
                    'b.name_village', 
                    'd.name_districts', 
                    'users.name')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_yayasans_provinsi.id_kabkot', $user_wilayah->kota_id)
                        ->where('rekomendasi_yayasans_provinsi.tujuan', '!=', $user_wilayah->role_id)
                        // ->where('log_ulangyayasan.created_by_ulangYayasan', '=', auth::user()->id)
                        // ->where('rekomendasi_yayasans_provinsi.petugas','!=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_yayasans_provinsi.keterangan_yayasan_provinsi', '=', 'Rekomendasi diteruskan')
                                ->orWhere('rekomendasi_yayasans_provinsi.keterangan_yayasan_provinsi', '=', 'Rekomendasi Dikembalikan');
                        });
                })->distinct();
        }
        if ($user_wilayah->name == 'SekertarisDinas') {
            // dd($user_wilayah);
            $query = DB::table('rekomendasi_yayasans_provinsi')
                ->join('users', 'users.id', '=', 'rekomendasi_yayasans_provinsi.petugas')
                // ->join('log_ulangyayasan', 'log_ulangyayasan.id_trx_ulangYayasan_ulangYayasan', '=', 'rekomendasi_yayasans_provinsi.id')
                ->join('rekomendasi_terdaftar_yayasans', 'rekomendasi_yayasans_provinsi.no_pendaftaran','=', 'rekomendasi_terdaftar_yayasans.no_pendaftaran')
                // ->join('model_has_roles', 'model_has_roles.model_id', '=', 'rekomendasi_yayasans_provinsi.tujuan')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_yayasans_provinsi.id_kelurahan')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_yayasans_provinsi.id_kecamatan')
                ->select(
                    'rekomendasi_yayasans_provinsi.id',
                    'rekomendasi_yayasans_provinsi.no_pendaftaran',
                    'rekomendasi_yayasans_provinsi.nama_lembaga',
                    'rekomendasi_yayasans_provinsi.nama_ketua',
                    'rekomendasi_yayasans_provinsi.alamat_lembaga',
                    'rekomendasi_yayasans_provinsi.status_alur',
                    'rekomendasi_yayasans_provinsi.keterangan_yayasan_provinsi',
                    'rekomendasi_yayasans_provinsi.tujuan',
                    'rekomendasi_terdaftar_yayasans.keterangan_daftar_ulang', 
                    'b.name_village', 
                    'd.name_districts', 
                    'users.name')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_yayasans_provinsi.id_kabkot', $user_wilayah->kota_id)
                        ->where('rekomendasi_yayasans_provinsi.tujuan', '!=', $user_wilayah->role_id)
                        // ->where('log_ulangyayasan.created_by_ulangYayasan', '=', auth::user()->id)
                        // ->where('rekomendasi_yayasans_provinsi.petugas','!=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_yayasans_provinsi.keterangan_yayasan_provinsi', '=', 'Rekomendasi diteruskan')
                                ->orWhere('rekomendasi_yayasans_provinsi.keterangan_yayasan_provinsi', '=', 'Rekomendasi Dikembalikan');
                        });
                })->distinct();
        }
        if ($user_wilayah->name == 'KepalaDinas') {
            //  dd(auth::user()->id);
            $query = DB::table('rekomendasi_yayasans_provinsi')
                ->join('users', 'users.id', '=', 'rekomendasi_yayasans_provinsi.petugas')
                // ->join('log_ulangyayasan', 'log_ulangyayasan.id_trx_ulangYayasan_ulangYayasan', '=', 'rekomendasi_yayasans_provinsi.id')
                ->join('rekomendasi_terdaftar_yayasans', 'rekomendasi_yayasans_provinsi.no_pendaftaran','=', 'rekomendasi_terdaftar_yayasans.no_pendaftaran')
                // ->join('model_has_roles', 'model_has_roles.model_id', '=', 'rekomendasi_yayasans_provinsi.tujuan')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_yayasans_provinsi.id_kelurahan')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_yayasans_provinsi.id_kecamatan')
                ->select(
                    'rekomendasi_yayasans_provinsi.id',
                    'rekomendasi_yayasans_provinsi.no_pendaftaran',
                    'rekomendasi_yayasans_provinsi.nama_lembaga',
                    'rekomendasi_yayasans_provinsi.nama_ketua',
                    'rekomendasi_yayasans_provinsi.alamat_lembaga',
                    'rekomendasi_yayasans_provinsi.status_alur',
                    'rekomendasi_yayasans_provinsi.keterangan_yayasan_provinsi',
                    'rekomendasi_yayasans_provinsi.tujuan',
                    'rekomendasi_terdaftar_yayasans.keterangan_daftar_ulang', 
                    'b.name_village', 
                    'd.name_districts', 
                    'users.name')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_yayasans_provinsi.id_kabkot', $user_wilayah->kota_id)
                        ->where('rekomendasi_yayasans_provinsi.tujuan', '!=', $user_wilayah->role_id)
                        // ->where('log_ulangyayasan.created_by_ulangYayasan', '=', auth::user()->id)
                        // ->where('rekomendasi_yayasans_provinsi.petugas','!=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_yayasans_provinsi.keterangan_yayasan_provinsi', '=', 'Rekomendasi diteruskan')
                                ->orWhere('rekomendasi_yayasans_provinsi.keterangan_yayasan_provinsi', '=', 'Rekomendasi Dikembalikan');
                        });
                })->distinct();
        }
        if ($user_wilayah->name == 'Front Office Kelurahan' || $user_wilayah->name == 'Back Ofiice kelurahan'|| $user_wilayah->name == 'fasilitator') {
            if ($request->has('search') && !empty($request->search['value'])) {
                $search = $request->search['value'];
                $query = DB::table('rekomendasi_yayasans_provinsi')
                ->join('users', 'users.id', '=', 'rekomendasi_yayasans_provinsi.petugas')
                // ->join('log_ulangyayasan', 'log_ulangyayasan.id_trx_ulangYayasan_ulangYayasan', '=', 'rekomendasi_yayasans_provinsi.id')
                ->join('rekomendasi_terdaftar_yayasans', 'rekomendasi_yayasans_provinsi.no_pendaftaran','=', 'rekomendasi_terdaftar_yayasans.no_pendaftaran')
                // ->join('model_has_roles', 'model_has_roles.model_id', '=', 'rekomendasi_yayasans_provinsi.tujuan')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_yayasans_provinsi.id_kelurahan')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_yayasans_provinsi.id_kecamatan')
                ->select(
                    'rekomendasi_yayasans_provinsi.id',
                    'rekomendasi_yayasans_provinsi.no_pendaftaran',
                    'rekomendasi_yayasans_provinsi.nama_lembaga',
                    'rekomendasi_yayasans_provinsi.nama_ketua',
                    'rekomendasi_yayasans_provinsi.alamat_lembaga',
                    'rekomendasi_yayasans_provinsi.status_alur',
                    'rekomendasi_yayasans_provinsi.keterangan_yayasan_provinsi',
                    'rekomendasi_yayasans_provinsi.tujuan',
                    'rekomendasi_terdaftar_yayasans.keterangan_daftar_ulang', 
                    'b.name_village', 
                    'd.name_districts', 
                    'users.name')
                    ->orWhere(function ($query) use ($user_wilayah,$search) {
                    $query->where('rekomendasi_yayasans_provinsi.id_kelurahan', $user_wilayah->kelurahan_id)
                        ->where('rekomendasi_yayasans_provinsi.tujuan', '!=', $user_wilayah->role_id)
                        // ->where('log_ulangyayasan.created_by_ulangYayasan', '=', auth::user()->id)
                        ->where('rekomendasi_yayasans_provinsi.no_pendaftaran', 'like', "%$search%")
                        ->where(function ($query) {
                            $query->where('rekomendasi_yayasans_provinsi.keterangan_yayasan_provinsi', '=', 'Rekomendasi diteruskan')
                                ->orWhere('rekomendasi_yayasans_provinsi.keterangan_yayasan_provinsi', '=', 'Rekomendasi Dikembalikan');
                        });
                    })->distinct();
            }
        }elseif($user_wilayah->name == 'Front Office Kota' || $user_wilayah->name == 'Back Ofiice Kota'|| $user_wilayah->name == 'KepalaDinas'||$user_wilayah->name == 'SekertarisDinas'||$user_wilayah->name == 'Supervisor') {
            if ($request->has('search') && !empty($request->search['value'])) {
                $search = $request->search['value'];
                $query = DB::table('rekomendasi_yayasans_provinsi')
                ->join('users', 'users.id', '=', 'rekomendasi_yayasans_provinsi.petugas')
                // ->join('log_ulangyayasan', 'log_ulangyayasan.id_trx_ulangYayasan_ulangYayasan', '=', 'rekomendasi_yayasans_provinsi.id')
                ->join('rekomendasi_terdaftar_yayasans', 'rekomendasi_yayasans_provinsi.no_pendaftaran','=', 'rekomendasi_terdaftar_yayasans.no_pendaftaran')
                // ->join('model_has_roles', 'model_has_roles.model_id', '=', 'rekomendasi_yayasans_provinsi.tujuan')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_yayasans_provinsi.id_kelurahan')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_yayasans_provinsi.id_kecamatan')
                ->select(
                    'rekomendasi_yayasans_provinsi.id',
                    'rekomendasi_yayasans_provinsi.no_pendaftaran',
                    'rekomendasi_yayasans_provinsi.nama_lembaga',
                    'rekomendasi_yayasans_provinsi.nama_ketua',
                    'rekomendasi_yayasans_provinsi.alamat_lembaga',
                    'rekomendasi_yayasans_provinsi.status_alur',
                    'rekomendasi_yayasans_provinsi.keterangan_yayasan_provinsi',
                    'rekomendasi_yayasans_provinsi.tujuan',
                    'rekomendasi_terdaftar_yayasans.keterangan_daftar_ulang', 
                    'b.name_village', 
                    'd.name_districts', 
                    'users.name')
                    ->orWhere(function ($query) use ($user_wilayah,$search) {
                    $query->where('rekomendasi_yayasans_provinsi.id_kabkot', $user_wilayah->kota_id)
                        ->where('rekomendasi_yayasans_provinsi.tujuan', '!=', $user_wilayah->role_id)
                        // ->where('log_ulangyayasan.created_by_ulangYayasan', '=', auth::user()->id)
                        ->where('rekomendasi_yayasans_provinsi.no_pendaftaran', 'like', "%$search%")
                        ->where(function ($query) {
                            $query->where('rekomendasi_yayasans_provinsi.keterangan_yayasan_provinsi', '=', 'Rekomendasi diteruskan')
                                ->orWhere('rekomendasi_yayasans_provinsi.keterangan_yayasan_provinsi', '=', 'Rekomendasi Dikembalikan');
                        });
                    })->distinct();
            }
        }
        $total_filtered_items = $query->count();
        // if ($request->has('order')) {
        //     $order_column = $request->order[0]['column'];
        //     $order_direction = $request->order[0]['dir'];
        //     $query->orderBy($request->input('columns.' . $order_column . '.data'), $order_direction);
        // }
        $start = $request->start;
        $length = $request->length;
        $query->offset($start)->limit($length);
        $data = $query->get();

        return response()->json([
            'draw' => $request->input('draw'),
            'recordsTotal' => RekomendasiYayasansProvinsi::count(),
            'recordsFiltered' => $total_filtered_items,
            'data' => $data,
        ]);
    }

    public function selesai(Request $request)
    {
        $user_name = Auth::user()->name;
    
        $query = DB::table('rekomendasi_yayasans_provinsi')
            ->join('users', 'users.id', 'rekomendasi_yayasans_provinsi.petugas')
            // ->join('log_ulangyayasan', 'log_ulangyayasan.id_trx_ulangYayasan', '=', 'rekomendasi_yayasans_provinsi.id')
            ->join('roles', 'roles.id', '=', 'rekomendasi_yayasans_provinsi.tujuan')
            ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_yayasans_provinsi.id_kelurahan')
            ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_yayasans_provinsi.id_kecamatan')
            ->select('rekomendasi_yayasans_provinsi.*', 'roles.name', 'users.name', 'b.name_village', 'd.name_districts');
        // dd($query);
        $user_id = Auth::user()->id;
        $user_wilayah = DB::table('wilayahs')
            ->leftJoin('model_has_roles', 'model_has_roles.model_id', '=', 'wilayahs.createdby')
            ->leftJoin('users', 'users.id', '=', 'model_has_roles.model_id')
            ->leftJoin('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->select('wilayahs.*','roles.name')
            ->where('createdby', $user_id)
            ->where(function ($query) {
                $query->where('status_wilayah', 1);
            })
            ->first();
        // dd($user_wilayah);
        // Add where conditions based on user's wilayah data
        if ($user_wilayah->name == 'fasilitator') {
            
            $query = DB::table('rekomendasi_yayasans_provinsi')
                ->join('users', 'users.id', 'rekomendasi_yayasans_provinsi.petugas')
                // ->join('log_ulangyayasan', 'log_ulangyayasan.id_trx_ulangYayasan', '=', 'rekomendasi_yayasans_provinsi.id')
                ->join('indonesia_villages', 'indonesia_villages.code', '=', 'rekomendasi_yayasans_provinsi.id_kelurahan')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_yayasans_provinsi.id_kecamatan')
                ->select('rekomendasi_yayasans_provinsi.*', 'users.name', 'd.name_districts', 'indonesia_villages.name_village', 'log_ulangyayasan.tujuan_ulangYayasan', 'log_ulangyayasan.petugas_ulangYayasan')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_yayasans_provinsi.id_kelurahan', $user_wilayah->kelurahan_id)
                        // ->where('log_ulangyayasan.tujuan_ulangYayasan','=', $user_wilayah->role_id);
                        // ->where('log_ulangyayasan.created_by_ulangYayasan','!=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_yayasans_provinsi.keterangan_yayasan_provinsi', '=', 'Rekomendasi Ditolak')
                                ->orWhere('rekomendasi_yayasans_provinsi.keterangan_yayasan_provinsi', '=', 'Rekomendasi Selesai');
                               
                        });
                })->distinct();
                
        } elseif ($user_wilayah->name == 'Front Office Kelurahan') {
            $query = DB::table('rekomendasi_yayasans_provinsi')
                ->join('users', 'users.id', 'rekomendasi_yayasans_provinsi.petugas')
                ->join('indonesia_villages', 'indonesia_villages.code', '=', 'rekomendasi_yayasans_provinsi.id_kelurahan')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_yayasans_provinsi.id_kecamatan')
                ->select('rekomendasi_yayasans_provinsi.*', 'users.name', 'd.name_districts', 'indonesia_villages.name_village')
                ->orWhere(function ($query) use ($user_wilayah,) {
                    $query->where('rekomendasi_yayasans_provinsi.id_kelurahan', $user_wilayah->kelurahan_id)
                    // ->where('log_ulangyayasan.tujuan_ulangYayasan','=', $user_wilayah->role_id);
                    // ->where('log_ulangyayasan.created_by_ulangYayasan','!=', $user_wilayah->model_id)
                    ->where(function ($query) {
                        $query->where('rekomendasi_yayasans_provinsi.keterangan_yayasan_provinsi', '=', 'Rekomendasi Ditolak')
                            ->orWhere('rekomendasi_yayasans_provinsi.keterangan_yayasan_provinsi', '=', 'Rekomendasi Selesai');
                           
                    });
                });
            // dd($query);
        } elseif ($user_wilayah->name == 'Front Office kota') {
            //  dd($user_wilayah->role_id);
            $query = DB::table('rekomendasi_yayasans_provinsi')
                ->join('users', 'users.id', 'rekomendasi_yayasans_provinsi.petugas')
                // ->join('log_ulangyayasan', 'log_ulangyayasan.id_trx_ulangYayasan', '=', 'rekomendasi_yayasans_provinsi.id')
                ->join('indonesia_villages', 'indonesia_villages.code', '=', 'rekomendasi_yayasans_provinsi.id_kelurahan')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_yayasans_provinsi.id_kecamatan')
                ->select('rekomendasi_yayasans_provinsi.*', 'users.name', 'd.name_districts', 'indonesia_villages.name_village')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_yayasans_provinsi.id_kabkot', $user_wilayah->kota_id)
                        // ->where('log_ulangyayasan.tujuan_ulangYayasan','=', $user_wilayah->role_id)
                        // ->where('log_ulangyayasan.created_by_ulangYayasan','!=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_yayasans_provinsi.keterangan_yayasan_provinsi', '=', 'Rekomendasi Ditolak')
                               ->orWhere('rekomendasi_yayasans_provinsi.keterangan_yayasan_provinsi', '=', 'Rekomendasi Selesai');
                               
                        });
                })->distinct();
        } elseif ($user_wilayah->name == 'Back Ofiice kelurahan') {
            // dd($user_wilayah);
            $query = DB::table('rekomendasi_yayasans_provinsi')
                ->join('users', 'users.id', 'rekomendasi_yayasans_provinsi.petugas')
                // ->join('log_ulangyayasan', 'log_ulangyayasan.id_trx_ulangYayasan', '=', 'rekomendasi_yayasans_provinsi.id')
                ->join('indonesia_villages', 'indonesia_villages.code', '=', 'rekomendasi_yayasans_provinsi.id_kelurahan')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_yayasans_provinsi.id_kecamatan')
                ->select('rekomendasi_yayasans_provinsi.*', 'users.name', 'd.name_districts', 'indonesia_villages.name_village')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_yayasans_provinsi.id_kabkot', $user_wilayah->kota_id)
                        // ->where('log_ulangyayasan.tujuan_ulangYayasan','=', $user_wilayah->role_id)
                        // ->where('log_ulangyayasan.created_by_ulangYayasan','!=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_yayasans_provinsi.keterangan_yayasan_provinsi', '=', 'Rekomendasi Ditolak')
                               ->orWhere('rekomendasi_yayasans_provinsi.keterangan_yayasan_provinsi', '=', 'Rekomendasi Selesai');
                               
                        });
                })->distinct();
            // dd($query); 
        } elseif ($user_wilayah->name == 'kepala bidang') {
            // dd($user_wilayah);
            $query = DB::table('rekomendasi_yayasans_provinsi')
                ->join('users', 'users.id', 'rekomendasi_yayasans_provinsi.petugas')
                // ->join('log_ulangyayasan', 'log_ulangyayasan.id_trx_ulangYayasan', '=', 'rekomendasi_yayasans_provinsi.id')
                ->join('indonesia_villages', 'indonesia_villages.code', '=', 'rekomendasi_yayasans_provinsi.id_kelurahan')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_yayasans_provinsi.id_kecamatan')
                ->select('rekomendasi_yayasans_provinsi.*', 'users.name', 'd.name_districts', 'indonesia_villages.name_village')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_yayasans_provinsi.id_kabkot', $user_wilayah->kota_id)
                        // ->where('log_ulangyayasan.tujuan_ulangYayasan','=', $user_wilayah->role_id)
                        // ->where('log_ulangyayasan.created_by_ulangYayasan','!=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_yayasans_provinsi.keterangan_yayasan_provinsi', '=', 'Rekomendasi Ditolak')
                               ->orWhere('rekomendasi_yayasans_provinsi.keterangan_yayasan_provinsi', '=', 'Rekomendasi Selesai');
                               
                        });
                })->distinct();
        } elseif ($user_wilayah->name == 'Back Ofiice Kota') {
            // dd($user_wilayah->role_id);
            $query = DB::table('rekomendasi_yayasans_provinsi')
                ->join('users', 'users.id', 'rekomendasi_yayasans_provinsi.petugas')
                // ->join('log_ulangyayasan', 'log_ulangyayasan.id_trx_ulangYayasan', '=', 'rekomendasi_yayasans_provinsi.id')
                ->join('indonesia_villages', 'indonesia_villages.code', '=', 'rekomendasi_yayasans_provinsi.id_kelurahan')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_yayasans_provinsi.id_kecamatan')
                ->select('rekomendasi_yayasans_provinsi.*', 'users.name', 'd.name_districts', 'indonesia_villages.name_village')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_yayasans_provinsi.id_kabkot', $user_wilayah->kota_id)
                            ->where('rekomendasi_yayasans_provinsi.keterangan_yayasan_provinsi', '=', 'Rekomendasi Ditolak')  
                            ->orWhere('rekomendasi_yayasans_provinsi.keterangan_yayasan_provinsi', '=', 'Rekomendasi Selesai');
                })->distinct();
            
        } elseif ($user_wilayah->name == 'SekertarisDinas') {
            // dd($user_wilayah);
            $query = DB::table('rekomendasi_yayasans_provinsi')
                ->join('users', 'users.id', 'rekomendasi_yayasans_provinsi.petugas')
                // ->join('log_ulangyayasan', 'log_ulangyayasan.id_trx_ulangYayasan', '=', 'rekomendasi_yayasans_provinsi.id')
                ->join('indonesia_villages', 'indonesia_villages.code', '=', 'rekomendasi_yayasans_provinsi.id_kelurahan')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_yayasans_provinsi.id_kecamatan')
                ->select('rekomendasi_yayasans_provinsi.*', 'users.name', 'd.name_districts', 'indonesia_villages.name_village')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_yayasans_provinsi.id_kabkot', $user_wilayah->kota_id)
                        // ->where('log_ulangyayasan.tujuan_ulangYayasan','=', $user_wilayah->role_id)
                        // ->where('log_ulangyayasan.created_by_ulangYayasan','!=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_yayasans_provinsi.keterangan_yayasan_provinsi', '=', 'Rekomendasi Ditolak')
                               ->orWhere('rekomendasi_yayasans_provinsi.keterangan_yayasan_provinsi', '=', 'Rekomendasi Selesai');
                               
                        });
                })->distinct();
        } elseif ($user_wilayah->name == 'supervisor') {
            // dd($user_wilayah);
            $query = DB::table('rekomendasi_yayasans_provinsi')
                ->join('users', 'users.id', 'rekomendasi_yayasans_provinsi.petugas')
                // ->join('log_ulangyayasan', 'log_ulangyayasan.id_trx_ulangYayasan', '=', 'rekomendasi_yayasans_provinsi.id')
                ->join('indonesia_villages', 'indonesia_villages.code', '=', 'rekomendasi_yayasans_provinsi.id_kelurahan')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_yayasans_provinsi.id_kecamatan')
                ->select('rekomendasi_yayasans_provinsi.*', 'users.name', 'd.name_districts', 'indonesia_villages.name_village')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_yayasans_provinsi.id_kabkot', $user_wilayah->kota_id)
                        // ->where('log_ulangyayasan.tujuan_ulangYayasan','=', $user_wilayah->role_id)
                        // ->where('log_ulangyayasan.created_by_ulangYayasan','!=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_yayasans_provinsi.keterangan_yayasan_provinsi', '=', 'Rekomendasi Ditolak')
                               ->orWhere('rekomendasi_yayasans_provinsi.keterangan_yayasan_provinsi', '=', 'Rekomendasi Selesai');
                               
                        });
                })->distinct();
        }
        elseif ($user_wilayah->name == 'KepalaDinas') {
            // dd($user_wilayah);
            $query = DB::table('rekomendasi_yayasans_provinsi')
                ->join('users', 'users.id', 'rekomendasi_yayasans_provinsi.petugas')
                // ->join('log_ulangyayasan', 'log_ulangyayasan.id_trx_ulangYayasan', '=', 'rekomendasi_yayasans_provinsi.id')
                ->join('indonesia_villages', 'indonesia_villages.code', '=', 'rekomendasi_yayasans_provinsi.id_kelurahan')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_yayasans_provinsi.id_kecamatan')
                ->select('rekomendasi_yayasans_provinsi.*', 'users.name', 'd.name_districts', 'indonesia_villages.name_village')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_yayasans_provinsi.id_kabkot', $user_wilayah->kota_id)
                        // ->where('log_ulangyayasan.tujuan_ulangYayasan','=', $user_wilayah->role_id)
                        // ->where('log_ulangyayasan.created_by_ulangYayasan','!=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_yayasans_provinsi.keterangan_yayasan_provinsi', '=', 'Rekomendasi Ditolak')
                               ->orWhere('rekomendasi_yayasans_provinsi.keterangan_yayasan_provinsi', '=', 'Rekomendasi Selesai');
                               
                        });
                })->distinct();
        }
        if ($user_wilayah->name == 'Front Office Kelurahan' || $user_wilayah->name == 'Back Ofiice kelurahan'|| $user_wilayah->name == 'fasilitator') {
            if ($request->has('search') && !empty($request->search['value'])) {
                $search = $request->search['value'];
                $query = DB::table('rekomendasi_yayasans_provinsi')
                ->join('users', 'users.id', 'rekomendasi_yayasans_provinsi.petugas')
                // ->join('log_ulangyayasan', 'log_ulangyayasan.id_trx_ulangYayasan', '=', 'rekomendasi_yayasans_provinsi.id')
                ->join('indonesia_villages', 'indonesia_villages.code', '=', 'rekomendasi_yayasans_provinsi.id_kelurahan')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_yayasans_provinsi.id_kecamatan')
                ->select('rekomendasi_yayasans_provinsi.*', 'users.name', 'd.name_districts', 'indonesia_villages.name_village')
                ->orWhere(function ($query) use ($user_wilayah,$search) {
                    $query->where('rekomendasi_yayasans_provinsi.id_kelurahan', $user_wilayah->kelurahan_id)
                        // ->where('log_ulangyayasan.tujuan_ulangYayasan','=', $user_wilayah->role_id)
                        ->where('rekomendasi_yayasans_provinsi.no_pendaftaran', 'like', "%$search%")
                        ->where(function ($query) {
                            $query->where('rekomendasi_yayasans_provinsi.keterangan_yayasan_provinsi', '=', 'Rekomendasi Ditolak')
                               ->orWhere('rekomendasi_yayasans_provinsi.keterangan_yayasan_provinsi', '=', 'Rekomendasi Selesai');
                               
                        });
                })->distinct();
            }
        }elseif($user_wilayah->name == 'Front Office Kota' || $user_wilayah->name == 'Back Ofiice Kota'|| $user_wilayah->name == 'KepalaDinas'||$user_wilayah->name == 'SekertarisDinas'||$user_wilayah->name == 'Supervisor') {
            if ($request->has('search') && !empty($request->search['value'])) {
                $search = $request->search['value'];
                $query = DB::table('rekomendasi_yayasans_provinsi')
                ->join('users', 'users.id', 'rekomendasi_yayasans_provinsi.petugas')
                // ->join('log_ulangyayasan', 'log_ulangyayasan.id_trx_ulangYayasan', '=', 'rekomendasi_yayasans_provinsi.id')
                ->join('indonesia_villages', 'indonesia_villages.code', '=', 'rekomendasi_yayasans_provinsi.id_kelurahan')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_yayasans_provinsi.id_kecamatan')
                ->select('rekomendasi_yayasans_provinsi.*', 'users.name', 'd.name_districts', 'indonesia_villages.name_village')
                ->orWhere(function ($query) use ($user_wilayah,$search) {
                    $query->where('rekomendasi_yayasans_provinsi.id_kabkot', $user_wilayah->kota_id)
                        // ->where('log_ulangyayasan.tujuan_ulangYayasan','=', $user_wilayah->role_id)
                        ->where('rekomendasi_yayasans_provinsi.no_pendaftaran', 'like', "%$search%")
                        ->where(function ($query) {
                            $query->where('rekomendasi_yayasans_provinsi.keterangan_yayasan_provinsi', '=', 'Rekomendasi Ditolak')
                               ->orWhere('rekomendasi_yayasans_provinsi.keterangan_yayasan_provinsi', '=', 'Rekomendasi Selesai');
                               
                        });
                })->distinct();
            }
        }

        $total_filtered_items = $query->count();
        // Add ordering
        // if ($request->has('order')) {
        //     $order_column = $request->order[0]['column'];
        //     $order_direction = $request->order[0]['dir'];
        //     $query->orderBy($request->input('columns.' . $order_column . '.data'), $order_direction);
        // }
        $start = $request->start;
        $length = $request->length;
        $query->offset($start)->limit($length);
        $data = $query->get();
        $userLogin = DB::table('wilayahs')
        ->join('model_has_roles', 'model_has_roles.model_id', '=', 'wilayahs.createdby')
        ->join('users', 'users.id', '=', 'model_has_roles.model_id')
        ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
        ->select('roles.name')
        ->where('wilayahs.createdby', $user_id)
        ->where(function ($query) {
            $query->where('status_wilayah', 1);
        })
        ->first();
        // dd($userLogin);
        $result = array();

        foreach ($data as $tmp) {
        $gabunganData = new stdClass();
        $gabunganData->userLogin=$userLogin;
        $gabunganData->dataRekom=$tmp;
        array_push($result, $gabunganData);
        }

        return response()->json([
            'draw' => $request->input('draw'),
            'recordsTotal' => RekomendasiYayasansProvinsi::count(),
            'recordsFiltered' => $total_filtered_items,
            'data' => $result,
        ]);
    }
    public function listyayasan(Request $request)
    {
        $user_name = Auth::user()->name;
        // dd($query);
        $user_id = Auth::user()->id;
        $user_wilayah = DB::table('wilayahs')
        ->leftJoin('model_has_roles', 'model_has_roles.model_id', '=', 'wilayahs.createdby')
        ->leftJoin('users', 'users.id', '=', 'model_has_roles.model_id')
        ->leftJoin('roles', 'roles.id', '=', 'model_has_roles.role_id')
        ->select('wilayahs.*','roles.name','model_has_roles.*')
        ->where('createdby', $user_id)
        ->where(function ($query) {
            $query->where('status_wilayah', 1);
        })
        ->first();
        // dd($user_wilayah);
        // Add where conditions based on user's wilayah data
        if ($user_wilayah->name == 'fasilitator') {
            
            $query = DB::table('rekomendasi_terdaftar_yayasans')
                ->join('users', 'users.id', 'rekomendasi_terdaftar_yayasans.petugas')
                ->join('log_ulangyayasan', 'log_ulangyayasan.id_trx_ulangYayasan', '=', 'rekomendasi_terdaftar_yayasans.id')
                ->join('indonesia_villages', 'indonesia_villages.code', '=', 'rekomendasi_terdaftar_yayasans.id_kelurahan')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_terdaftar_yayasans.id_kecamatan')
                ->select('rekomendasi_terdaftar_yayasans.*', 'users.name', 'd.name_districts', 'indonesia_villages.name_village', 'log_ulangyayasan.tujuan_ulangYayasan', 'log_ulangyayasan.petugas_ulangYayasan')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_terdaftar_yayasans.id_kelurahan', $user_wilayah->kelurahan_id)
                        // ->where('log_ulangyayasan.tujuan_ulangYayasan','=', $user_wilayah->role_id);
                        // ->where('log_ulangyayasan.created_by_ulangYayasan','!=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_terdaftar_yayasans.keterangan_yayasan_provinsi', '=', 'Rekomendasi Ditolak')
                                ->orWhere('rekomendasi_terdaftar_yayasans.keterangan_yayasan_provinsi', '=', 'Rekomendasi Selesai')
                                ->orWhere('rekomendasi_terdaftar_yayasans.status_alur', '=', 'Selesai');
                        });
                })->distinct();
                
        } elseif ($user_wilayah->name == 'Front Office Kelurahan') {
            //  dd($user_wilayah->role_id);
            $tanggalSekarang = Carbon::now()->toDateString();
            // $tanggalSekarang = '2024-06-10';
          
            $query = DB::table('rekomendasi_terdaftar_yayasans')
                ->join('users', 'users.id', 'rekomendasi_terdaftar_yayasans.petugas')
                ->join('indonesia_villages', 'indonesia_villages.code', '=', 'rekomendasi_terdaftar_yayasans.id_kelurahan')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_terdaftar_yayasans.id_kecamatan')
                ->select('rekomendasi_terdaftar_yayasans.*', 'users.name', 'd.name_districts', 'indonesia_villages.name_village')
                ->orWhere(function ($query) use ($user_wilayah, $tanggalSekarang) {
                    $query->where('rekomendasi_terdaftar_yayasans.id_kelurahan', $user_wilayah->kelurahan_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_terdaftar_yayasans.keterangan_daftar_ulang', '=', 'Daftar Ulang Selesai')
                                // ->orWhere('rekomendasi_terdaftar_yayasans.keterangan_daftar_ulang_yayasan', '=', 'Rekomendasi Selesai')
                                ->orWhere('rekomendasi_terdaftar_yayasans.status_alur', '=', 'Selesai');
                        })
                        // ->whereDate('rekomendasi_terdaftar_yayasans.tgl_mulai', '<=', $tanggalSekarang)
                        ->whereRaw('? <= DATE(rekomendasi_terdaftar_yayasans.tgl_selesai)', [$tanggalSekarang]);

                        // ->whereDate('rekomendasi_terdaftar_yayasans.tgl_selesai', '=>', );
                });
                
            // dd($query);
        } elseif ($user_wilayah->name == 'Front Office kota') {
            //  dd($user_wilayah->role_id);
             $tanggalSekarang = Carbon::now()->toDateString();
            // $tanggalSekarang = '2024-06-10';
          
            $query = DB::table('rekomendasi_terdaftar_yayasans')
                ->join('users', 'users.id', 'rekomendasi_terdaftar_yayasans.petugas')
                ->join('indonesia_villages', 'indonesia_villages.code', '=', 'rekomendasi_terdaftar_yayasans.id_kelurahan')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_terdaftar_yayasans.id_kecamatan')
                ->select('rekomendasi_terdaftar_yayasans.*', 'users.name', 'd.name_districts', 'indonesia_villages.name_village')
                ->orWhere(function ($query) use ($user_wilayah, $tanggalSekarang) {
                    $query->where('rekomendasi_terdaftar_yayasans.id_kabkot', $user_wilayah->kota_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_terdaftar_yayasans.keterangan_daftar_ulang', '=', 'Daftar Ulang Selesai')
                                // ->orWhere('rekomendasi_terdaftar_yayasans.keterangan_daftar_ulang_yayasan', '=', 'Rekomendasi Selesai')
                                ->orWhere('rekomendasi_terdaftar_yayasans.status_alur', '=', 'Selesai');
                        })
                        // ->whereDate('rekomendasi_terdaftar_yayasans.tgl_mulai', '<=', $tanggalSekarang)
                        ->whereRaw('? <= DATE(rekomendasi_terdaftar_yayasans.tgl_selesai)', [$tanggalSekarang]);

                        // ->whereDate('rekomendasi_terdaftar_yayasans.tgl_selesai', '=>', );
                });
        } elseif ($user_wilayah->name == 'Back Ofiice kelurahan') {
             //  dd($user_wilayah->role_id);
             $tanggalSekarang = Carbon::now()->toDateString();
             // $tanggalSekarang = '2024-06-10';
           
             $query = DB::table('rekomendasi_terdaftar_yayasans')
                 ->join('users', 'users.id', 'rekomendasi_terdaftar_yayasans.petugas')
                 ->join('indonesia_villages', 'indonesia_villages.code', '=', 'rekomendasi_terdaftar_yayasans.id_kelurahan')
                 ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_terdaftar_yayasans.id_kecamatan')
                 ->select('rekomendasi_terdaftar_yayasans.*', 'users.name', 'd.name_districts', 'indonesia_villages.name_village')
                 ->orWhere(function ($query) use ($user_wilayah, $tanggalSekarang) {
                     $query->where('rekomendasi_terdaftar_yayasans.id_kelurahan', $user_wilayah->kelurahan_id)
                         ->where(function ($query) {
                             $query->where('rekomendasi_terdaftar_yayasans.keterangan_daftar_ulang', '=', 'Daftar Ulang Selesai')
                                 // ->orWhere('rekomendasi_terdaftar_yayasans.keterangan_daftar_ulang_yayasan', '=', 'Rekomendasi Selesai')
                                 ->orWhere('rekomendasi_terdaftar_yayasans.status_alur', '=', 'Selesai');
                         })
                         // ->whereDate('rekomendasi_terdaftar_yayasans.tgl_mulai', '<=', $tanggalSekarang)
                         ->whereRaw('? <= DATE(rekomendasi_terdaftar_yayasans.tgl_selesai)', [$tanggalSekarang]);
 
                         // ->whereDate('rekomendasi_terdaftar_yayasans.tgl_selesai', '=>', );
                 });
            // dd($query); 
        } elseif ($user_wilayah->name == 'kepala bidang') {
            // dd($user_wilayah);
            //  dd($user_wilayah->role_id);
            $tanggalSekarang = Carbon::now()->toDateString();
            // $tanggalSekarang = '2024-06-10';
          
            $query = DB::table('rekomendasi_terdaftar_yayasans')
                ->join('users', 'users.id', 'rekomendasi_terdaftar_yayasans.petugas')
                ->join('indonesia_villages', 'indonesia_villages.code', '=', 'rekomendasi_terdaftar_yayasans.id_kelurahan')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_terdaftar_yayasans.id_kecamatan')
                ->select('rekomendasi_terdaftar_yayasans.*', 'users.name', 'd.name_districts', 'indonesia_villages.name_village')
                ->orWhere(function ($query) use ($user_wilayah, $tanggalSekarang) {
                    $query->where('rekomendasi_terdaftar_yayasans.id_kabkot', $user_wilayah->kota_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_terdaftar_yayasans.keterangan_daftar_ulang', '=', 'Daftar Ulang Selesai')
                                // ->orWhere('rekomendasi_terdaftar_yayasans.keterangan_daftar_ulang_yayasan', '=', 'Rekomendasi Selesai')
                                ->orWhere('rekomendasi_terdaftar_yayasans.status_alur', '=', 'Selesai');
                        })
                        // ->whereDate('rekomendasi_terdaftar_yayasans.tgl_mulai', '<=', $tanggalSekarang)
                        ->whereRaw('? <= DATE(rekomendasi_terdaftar_yayasans.tgl_selesai)', [$tanggalSekarang]);

                        // ->whereDate('rekomendasi_terdaftar_yayasans.tgl_selesai', '=>', );
                });
        } elseif ($user_wilayah->name == 'Back Ofiice Kota') {
            //  dd($user_wilayah->role_id);
            $tanggalSekarang = Carbon::now()->toDateString();
            // $tanggalSekarang = '2024-06-10';
          
            $query = DB::table('rekomendasi_terdaftar_yayasans')
                ->join('users', 'users.id', 'rekomendasi_terdaftar_yayasans.petugas')
                ->join('indonesia_villages', 'indonesia_villages.code', '=', 'rekomendasi_terdaftar_yayasans.id_kelurahan')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_terdaftar_yayasans.id_kecamatan')
                ->select('rekomendasi_terdaftar_yayasans.*', 'users.name', 'd.name_districts', 'indonesia_villages.name_village')
                ->orWhere(function ($query) use ($user_wilayah, $tanggalSekarang) {
                    $query->where('rekomendasi_terdaftar_yayasans.id_kabkot', $user_wilayah->kota_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_terdaftar_yayasans.keterangan_daftar_ulang', '=', 'Daftar Ulang Selesai')
                                // ->orWhere('rekomendasi_terdaftar_yayasans.keterangan_daftar_ulang_yayasan', '=', 'Rekomendasi Selesai')
                                ->orWhere('rekomendasi_terdaftar_yayasans.status_alur', '=', 'Selesai');
                        })
                        // ->whereDate('rekomendasi_terdaftar_yayasans.tgl_mulai', '<=', $tanggalSekarang)
                        ->whereRaw('? <= DATE(rekomendasi_terdaftar_yayasans.tgl_selesai)', [$tanggalSekarang]);

                        // ->whereDate('rekomendasi_terdaftar_yayasans.tgl_selesai', '=>', );
                });
        } elseif ($user_wilayah->name == 'SekertarisDinas') {
            // dd($user_wilayah);
            //  dd($user_wilayah->role_id);
            $tanggalSekarang = Carbon::now()->toDateString();
            // $tanggalSekarang = '2024-06-10';
          
            $query = DB::table('rekomendasi_terdaftar_yayasans')
                ->join('users', 'users.id', 'rekomendasi_terdaftar_yayasans.petugas')
                ->join('indonesia_villages', 'indonesia_villages.code', '=', 'rekomendasi_terdaftar_yayasans.id_kelurahan')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_terdaftar_yayasans.id_kecamatan')
                ->select('rekomendasi_terdaftar_yayasans.*', 'users.name', 'd.name_districts', 'indonesia_villages.name_village')
                ->orWhere(function ($query) use ($user_wilayah, $tanggalSekarang) {
                    $query->where('rekomendasi_terdaftar_yayasans.id_kabkot', $user_wilayah->kota_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_terdaftar_yayasans.keterangan_daftar_ulang', '=', 'Daftar Ulang Selesai')
                                // ->orWhere('rekomendasi_terdaftar_yayasans.keterangan_daftar_ulang_yayasan', '=', 'Rekomendasi Selesai')
                                ->orWhere('rekomendasi_terdaftar_yayasans.status_alur', '=', 'Selesai');
                        })
                        // ->whereDate('rekomendasi_terdaftar_yayasans.tgl_mulai', '<=', $tanggalSekarang)
                        ->whereRaw('? <= DATE(rekomendasi_terdaftar_yayasans.tgl_selesai)', [$tanggalSekarang]);

                        // ->whereDate('rekomendasi_terdaftar_yayasans.tgl_selesai', '=>', );
                });
        } elseif ($user_wilayah->name == 'supervisor') {
           //  dd($user_wilayah->role_id);
           $tanggalSekarang = Carbon::now()->toDateString();
           // $tanggalSekarang = '2024-06-10';
         
           $query = DB::table('rekomendasi_terdaftar_yayasans')
               ->join('users', 'users.id', 'rekomendasi_terdaftar_yayasans.petugas')
               ->join('indonesia_villages', 'indonesia_villages.code', '=', 'rekomendasi_terdaftar_yayasans.id_kelurahan')
               ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_terdaftar_yayasans.id_kecamatan')
               ->select('rekomendasi_terdaftar_yayasans.*', 'users.name', 'd.name_districts', 'indonesia_villages.name_village')
               ->orWhere(function ($query) use ($user_wilayah, $tanggalSekarang) {
                   $query->where('rekomendasi_terdaftar_yayasans.id_kabkot', $user_wilayah->kota_id)
                       ->where(function ($query) {
                           $query->where('rekomendasi_terdaftar_yayasans.keterangan_daftar_ulang', '=', 'Daftar Ulang Selesai')
                               // ->orWhere('rekomendasi_terdaftar_yayasans.keterangan_daftar_ulang_yayasan', '=', 'Rekomendasi Selesai')
                               ->orWhere('rekomendasi_terdaftar_yayasans.status_alur', '=', 'Selesai');
                       })
                       // ->whereDate('rekomendasi_terdaftar_yayasans.tgl_mulai', '<=', $tanggalSekarang)
                       ->whereRaw('? <= DATE(rekomendasi_terdaftar_yayasans.tgl_selesai)', [$tanggalSekarang]);

                       // ->whereDate('rekomendasi_terdaftar_yayasans.tgl_selesai', '=>', );
               });
        }
        elseif ($user_wilayah->name == 'KepalaDinas') {
          //  dd($user_wilayah->role_id);
          $tanggalSekarang = Carbon::now()->toDateString();
          // $tanggalSekarang = '2024-06-10';
        
          $query = DB::table('rekomendasi_terdaftar_yayasans')
              ->join('users', 'users.id', 'rekomendasi_terdaftar_yayasans.petugas')
              ->join('indonesia_villages', 'indonesia_villages.code', '=', 'rekomendasi_terdaftar_yayasans.id_kelurahan')
              ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_terdaftar_yayasans.id_kecamatan')
              ->select('rekomendasi_terdaftar_yayasans.*', 'users.name', 'd.name_districts', 'indonesia_villages.name_village')
              ->orWhere(function ($query) use ($user_wilayah, $tanggalSekarang) {
                  $query->where('rekomendasi_terdaftar_yayasans.id_kabkot', $user_wilayah->kota_id)
                      ->where(function ($query) {
                          $query->where('rekomendasi_terdaftar_yayasans.keterangan_daftar_ulang', '=', 'Daftar Ulang Selesai')
                              // ->orWhere('rekomendasi_terdaftar_yayasans.keterangan_daftar_ulang_yayasan', '=', 'Rekomendasi Selesai')
                              ->orWhere('rekomendasi_terdaftar_yayasans.status_alur', '=', 'Selesai');
                      })
                      // ->whereDate('rekomendasi_terdaftar_yayasans.tgl_mulai', '<=', $tanggalSekarang)
                      ->whereRaw('? <= DATE(rekomendasi_terdaftar_yayasans.tgl_selesai)', [$tanggalSekarang]);

                      // ->whereDate('rekomendasi_terdaftar_yayasans.tgl_selesai', '=>', );
              });
        }
        // dd($user_wilayah);
        if ($user_wilayah->name == 'Front Office Kelurahan' || $user_wilayah->name == 'Back Ofiice kelurahan'|| $user_wilayah->name == 'fasilitator') {
            if ($request->has('search') && !empty($request->search['value'])) {
                $search = $request->search['value'];
                $query = DB::table('rekomendasi_terdaftar_yayasans')
                ->join('users', 'users.id', 'rekomendasi_terdaftar_yayasans.petugas')
                ->join('indonesia_villages', 'indonesia_villages.code', '=', 'rekomendasi_terdaftar_yayasans.id_kelurahan')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_terdaftar_yayasans.id_kecamatan')
                ->select('rekomendasi_terdaftar_yayasans.*', 'users.name', 'd.name_districts', 'indonesia_villages.name_village')
                ->orWhere(function ($query) use ($user_wilayah, $tanggalSekarang,$search) {
                    $query->where('rekomendasi_terdaftar_yayasans.id_kelurahan', $user_wilayah->kelurahan_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_terdaftar_yayasans.keterangan_daftar_ulang', '=', 'Daftar Ulang Selesai')
                                // ->orWhere('rekomendasi_terdaftar_yayasans.keterangan_daftar_ulang_yayasan', '=', 'Rekomendasi Selesai')
                                ->orWhere('rekomendasi_terdaftar_yayasans.status_alur', '=', 'Selesai');
                        })
                        // ->whereDate('rekomendasi_terdaftar_yayasans.tgl_mulai', '<=', $tanggalSekarang)
                        ->whereRaw('? <= DATE(rekomendasi_terdaftar_yayasans.tgl_selesai)', [$tanggalSekarang])
                        ->where('rekomendasi_terdaftar_yayasans.no_pendaftaran', 'like', "%$search%");
                });
            
            }
        }elseif($user_wilayah->name == 'Front Office Kota' || $user_wilayah->name == 'Back Ofiice Kota'|| $user_wilayah->name == 'KepalaDinas'||$user_wilayah->name == 'SekertarisDinas'||$user_wilayah->name == 'Supervisor') {
            if ($request->has('search') && !empty($request->search['value'])) {
                $search = $request->search['value'];
                $query = DB::table('rekomendasi_terdaftar_yayasans')
                ->join('users', 'users.id', 'rekomendasi_terdaftar_yayasans.petugas')
                ->join('indonesia_villages', 'indonesia_villages.code', '=', 'rekomendasi_terdaftar_yayasans.id_kelurahan')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_terdaftar_yayasans.id_kecamatan')
                ->select('rekomendasi_terdaftar_yayasans.*', 'users.name', 'd.name_districts', 'indonesia_villages.name_village')
                ->orWhere(function ($query) use ($user_wilayah, $tanggalSekarang,$search) {
                    $query->where('rekomendasi_terdaftar_yayasans.id_kabkot', $user_wilayah->kota_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_terdaftar_yayasans.keterangan_daftar_ulang', '=', 'Daftar Ulang Selesai')
                                // ->orWhere('rekomendasi_terdaftar_yayasans.keterangan_daftar_ulang_yayasan', '=', 'Rekomendasi Selesai')
                                ->orWhere('rekomendasi_terdaftar_yayasans.status_alur', '=', 'Selesai');
                        })
                        // ->whereDate('rekomendasi_terdaftar_yayasans.tgl_mulai', '<=', $tanggalSekarang)
                        ->whereRaw('? <= DATE(rekomendasi_terdaftar_yayasans.tgl_selesai)', [$tanggalSekarang])
                        ->where('rekomendasi_terdaftar_yayasans.no_pendaftaran', 'like', "%$search%");
                });
            
            }
        }
           
        // Get total count of filtered items
        $total_filtered_items = $query->count();
        // Add ordering
        if ($request->has('order')) {
            $order_column = $request->order[0]['column'];
            $order_direction = $request->order[0]['dir'];
            $query->orderBy($request->input('columns.' . $order_column . '.data'), $order_direction);
        }
        $start = $request->start;
        $length = $request->length;
        $query->offset($start)->limit($length);
        $data = $query->get();
      
        // dd($data);
        // mengubah data JSON menjadi objek PHP

        return response()->json([
            'draw' => $request->input('draw'),
            'recordsTotal' => rekomendasi_terdaftar_yayasan::count(),
            'recordsFiltered' => $total_filtered_items,
            'data' => $data,
        ]);
    }
   
}
