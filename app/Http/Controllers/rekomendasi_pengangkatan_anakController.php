<?php

namespace App\Http\Controllers;

use App\Http\Controllers\AppBaseController;
use App\Models\log_pengan;
use App\Models\rekomendasi_pengangkatan_anak;
use App\Models\Roles;
use App\Repositories\rekomendasi_pengangkatan_anakRepository;
use Illuminate\Http\Request;
use Flash;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use stdClass;

class rekomendasi_pengangkatan_anakController extends AppBaseController
{
    /** @var rekomendasi_pengangkatan_anakRepository $rekomendasiPengangkatanAnakRepository*/
    private $rekomendasiPengangkatanAnakRepository;

    public function __construct(rekomendasi_pengangkatan_anakRepository $rekomendasiPengangkatanAnakRepo)
    {
        $this->rekomendasiPengangkatanAnakRepository = $rekomendasiPengangkatanAnakRepo;
    }

    /**
     * Display a listing of the rekomendasi_pengangkatan_anak.
     */
    public function index(Request $request)
    {
        $rekomendasiPengangkatanAnaks = $this->rekomendasiPengangkatanAnakRepository->paginate(10);

        return view('rekomendasi_pengangkatan_anaks.index')
            ->with('rekomendasiPengangkatanAnaks', $rekomendasiPengangkatanAnaks);
    }

    /**
     * Show the form for creating a new rekomendasi_pengangkatan_anak.
     */
    public function create()
    {
        $v = rekomendasi_pengangkatan_anak::latest()->first();
        // dd($v);  
        $kecamatans = DB::table('indonesia_districts')->where('city_code', '3273')->get();
        $userid = Auth::user()->id;
        $wilayah = DB::table('wilayahs as w')->select(
            'w.*',
            'b.*',
            'prov.*',
            'kota.*',
            'kecamatan.*'
        )
            ->leftjoin('indonesia_provinces as prov', 'prov.code', '=', 'w.province_id')
            ->leftjoin('indonesia_cities as kota', 'kota.code', '=', 'w.kota_id')
            ->leftjoin('indonesia_districts as kecamatan', 'kecamatan.code', '=', 'w.kecamatan_id')
            ->leftjoin('indonesia_villages as b', 'b.code', '=', 'w.kelurahan_id')
            ->where('status_wilayah', '1')
            ->where('w.createdby', $userid)->get();

        // $alur = DB::table('alur')
        //     ->where('name', 'Draft')
        //     // ->where('name', 'supervisor')
        //     ->orWhere('name', 'Teruskan')
        //     ->get();

        //ALUR
        $user = Auth::user();
        $roles = $user->roles()->pluck('name');
        if ($roles->contains('Front Office kota') || $roles->contains('Front Office Kelurahan') || $roles->contains('fasilitator')) {
            // Jika user memiliki role 'FO-Kota', maka tampilkan alur dengan nama 'Draft' dan 'Teruskan'
            $alur = DB::table('alur')
                ->whereIn('name', ['Draft', 'Teruskan'])
                ->get();
        } else if ($roles->contains('Back Ofiice Kota') || $roles->contains('SekertarisDinas') || $roles->contains('kepala bidang')) {
            // Jika user memiliki role 'BO-Kota' atau 'SekertarisDinas', maka tampilkan alur dengan nama 'Kembalikan', 'Tolak', dan 'Teruskan'
            $alur = DB::table('alur')
                ->whereIn('name', ['Kembalikan', 'Tolak', 'Teruskan'])
                ->get();
        } else if ($roles->contains('KepalaDinas')) {
            $alur = DB::table('alur')
                ->whereIn('name', ['Selesai', 'Tolak'])
                ->get();
        } else {
            $alur = collect();
        }


        $user = Auth::user();
        $roles = $user->roles()->pluck('name');
        if ($roles->contains('Front Office Kelurahan')) {
            $roleid = DB::table('roles')
                ->where('name', 'Back Ofiice kelurahan')
                ->orwhere('name','supervisor')
                ->get();
        } else if ($roles->contains('Front Office kota')) {
            $roleid = DB::table('roles')
                ->where('name', ['Back Ofiice Kota'])
                ->get();
        }else if ($roles->contains('fasilitator')) {
            $roleid = DB::table('roles')
                ->where('name','Back Ofiice Kota')
                ->orwhere('name','supervisor')
                ->get();
            // dd($roleid);
        } else if ($roles->contains('Back Ofiice Kota')) {
            $roleid = DB::table('roles')
                ->whereIn('name', ['Front Office kota', 'kepala bidang'])
                ->get();
        } else if ($roles->contains('kepala bidang')) {
            $roleid = DB::table('roles')
                ->whereIn('name', ['Back Ofiice kota', 'SekertarisDinas'])
                ->get();
        } else if ($roles->contains('SekertarisDinas')) {
            $roleid = DB::table('roles')
                ->whereIn('name', ['Back Ofiice kota', 'KepalaDinas'])
                ->get();
        } else if ($roles->contains('KepalaDinas')) {
            $roleid = DB::table('roles')
                ->where('name', 'Front Office kota')
                ->get();
        }
        $checkroles = Roles::where('name', 'Front Office kota')
            ->orWhere('name', 'SekertarisDinas')
            ->orWhere('name', 'KepalaDinas')
            ->get();
        return view('rekomendasi_pengangkatan_anaks.create', compact('kecamatans','wilayah', 'roleid', 'checkroles', 'alur'));
        // return view('rekomendasi_pengangkatan_anaks.create');
    }
    public function cekIdPengan(Request $request, $Nik)
    {
        $found = false;
        $table2 = DB::table('dtks')->where('nik_pengan', $Nik)->first();
        if ($table2) {
            $found = true;
            $Id_DTKS = $table2->Id_DTKS; // Ambil data nama jika ID ditemukan
        } else {
            $found = false;
            $Id_DTKS = 'not found data';
        }
        return response()->json([
            'found' => $found,
            'Id_DTKS' => $Id_DTKS
        ]);
    }

    /**
     * Store a newly created rekomendasi_pengangkatan_anak in storage.
     */
    public function store(Request $request)
    {
        $getdata = rekomendasi_pengangkatan_anak::where('id', Auth::user()->id)->first();
        // dd($request->all());
        $data = new rekomendasi_pengangkatan_anak();
        $files = [
            'surat_izin_pengan' => 'pengangkatananak/surat_izin_pengan/',
            'surat_sehat_pengan' => 'pengangkatananak/surat_sehat_pengan/',
            'surat_sehat_jiwa_pengan' => 'pengangkatananak/surat_sehat_jiwa_pengan/',
            'surat_kandungan' => 'pengangkatananak/surat_kandungan/',
            'akta_cota_pengan' => 'pengangkatananak/akta_cota_pengan/',
            'skck_pengan' => 'pengangkatananak/skck_pengan/',
            'akta_nikah_cota_pengan' => 'pengangkatananak/akta_nikah_cota_pengan/',
            'KK_cota_pengan' => 'pengangkatananak/KK_cota_pengan/',
            'KTP_cota_pengan' => 'pengangkatananak/KTP_cota_pengan/',
            'KK_ortuangkat_pengan' => 'pengangkatananak/KK_ortuangkat_pengan/',
            'KTP_ortuangkat_pengan' => 'pengangkatananak/KTP_ortuangkat_pengan/',
            'akta_canak_pengan' => 'pengangkatananak/akta_canak_pengan/',
            'super_canak_pengan' => 'pengangkatananak/super_canak_pengan/',
            'izincota_suami_pengan' => 'pengangkatananak/izincota_suami_pengan/',
            'izincota_istri_pengan' => 'pengangkatananak/izincota_istri_pengan/',
            'super_terbaik_pengan' => 'pengangkatananak/super_terbaik_pengan/',
            'super_notdiskriminasi_pengan' => 'pengangkatananak/super_notdiskriminasi_pengan/',
            'sudok_fakta_pengan' => 'pengangkatananak/sudok_fakta_pengan/',
            'super_asalusul_pengan' => 'pengangkatananak/super_asalusul_pengan/',
            'super_motivasi_pengan' => 'pengangkatananak/super_motivasi_pengan/',
            'super_notwalinikah_pengan' => 'pengangkatananak/super_notwalinikah_pengan/',
            'super_hibah' => 'pengangkatananak/super_hibah/',
            'super_asuransi' => 'pengangkatananak/super_asuransi/',
        ];
        foreach ($files as $file => $directory) {
            if ($request->file($file)) {
                $path = $request->file($file);
                $filename = $directory . $path->getClientOriginalName();
                $return = Storage::disk('imagekit')->put($filename, fopen($path->getRealPath(), 'r'));
                $data->$file = Storage::disk('imagekit')->url($filename);
            } else {
                $data->$file = null;
            }
        }

        $data->no_pendaftaran_pengan = mt_rand(100, 1000);
        $data->id_provinsi_pengan = $request->get('id_provinsi_pengan');
        $data->id_kabkot_pengan = $request->get('id_kabkot_pengan');
        $data->id_kecamatan_pengan = $request->get('id_kecamatan_pengan');
        $data->id_kelurahan_pengan = $request->get('id_kelurahan_pengan');
        $data->nik_pengan = $request->get('nik_pengan');
        $data->nama_pengan = $request->get('nama_pengan');
        $data->Nama_ibu_angkat = $request->get('Nama_ibu_angkat');
        $data->Nama_Bapak_angkat = $request->get('Nama_Bapak_angkat');
        $data->telp_pengan = $request->get('telp_pengan');
        $data->alamat_pengan = $request->get('alamat_pengan');
        $data->email_pengan = $request->get('email_pengan');
        $data->nama_anak_pengan = $request->get('nama_anak_pengan');
        $data->catatan_pengan = $request->get('catatan_pengan');
        $data->super_asuransi = $request->get('super_asuransi');
        $data->tujuan_pengan = $request->get('tujuan_pengan');
        $data->status_aksi_pengan = $request->get('status_aksi_pengan');
        $data->petugas_pengan = $request->get('petugas_pengan');
        $data->createdby_pengan = Auth::user()->id;
        $data->updatedby_pengan = Auth::user()->id;
        // dd($data);       
        $data->save();
        $logpengaduan = new log_pengan();
        $logpengaduan['id_trx_pengan'] = $data->id;
        $logpengaduan['id_alur_pengan'] = $request->get('status_aksi_pengan');
        $logpengaduan['petugas_pengan'] = $request->get('petugas_pengan');
        $logpengaduan['catatan_pengan'] = $request->get('catatan_pengan');
        $logpengaduan['tujuan_pengan'] = $request->get('tujuan_pengan');
        $logpengaduan['created_by_pengan'] = Auth::user()->id;
        $logpengaduan['updated_by_pengan'] = Auth::user()->id;

        if ($data['status_aksi_pengan'] !== 'Draft') {
            $logpengaduan->save();
            return redirect('rekomendasi_pengangkatan_anaks')->withSuccess('Data Rekomendasi Berhasil Ditambahkan');
        } else {
            return redirect('rekomendasi_pengangkatan_anaks')->withSuccess('Data Disimpan Kedalam Draft');
        }
    }

    public function getUrlUpload(Request $request)
    {
        $files = [
            'surat_izin_pengan' => 'pengangkatananak/surat_izin_pengan/',
            'surat_sehat_pengan' => 'pengangkatananak/surat_sehat_pengan/',
            'surat_sehat_jiwa_pengan' => 'pengangkatananak/surat_sehat_jiwa_pengan/',
            'surat_kandungan' => 'pengangkatananak/surat_kandungan/',
            'akta_cota_pengan' => 'pengangkatananak/akta_cota_pengan/',
            'skck_pengan' => 'pengangkatananak/skck_pengan/',
            'akta_nikah_cota_pengan' => 'pengangkatananak/akta_nikah_cota_pengan/',
            'KK_cota_pengan' => 'pengangkatananak/KK_cota_pengan/',
            'KTP_cota_pengan' => 'pengangkatananak/KTP_cota_pengan/',
            'KK_ortuangkat_pengan' => 'pengangkatananak/KK_ortuangkat_pengan/',
            'KTP_ortuangkat_pengan' => 'pengangkatananak/KTP_ortuangkat_pengan/',
            'akta_canak_pengan' => 'pengangkatananak/akta_canak_pengan/',
            'super_canak_pengan' => 'pengangkatananak/super_canak_pengan/',
            'izincota_suami_pengan' => 'pengangkatananak/izincota_suami_pengan/',
            'izincota_istri_pengan' => 'pengangkatananak/izincota_istri_pengan/',
            'super_terbaik_pengan' => 'pengangkatananak/super_terbaik_pengan/',
            'super_notdiskriminasi_pengan' => 'pengangkatananak/super_notdiskriminasi_pengan/',
            'sudok_fakta_pengan' => 'pengangkatananak/sudok_fakta_pengan/',
            'super_asalusul_pengan' => 'pengangkatananak/super_asalusul_pengan/',
            'super_motivasi_pengan' => 'pengangkatananak/super_motivasi_pengan/',
            'super_notwalinikah_pengan' => 'pengangkatananak/super_notwalinikah_pengan/',
            'super_hibah' => 'pengangkatananak/super_hibah/',
            'super_asuransi' => 'pengangkatananak/super_asuransi/',
        ];
        foreach ($files as $file => $directory) {
            if ($request->file($file)) {
                $path = $request->file($file);
                $filename = $directory . $path->getClientOriginalName();
                $return = Storage::disk('imagekit')->put($filename, fopen($path->getRealPath(), 'r'));
                $url = Storage::disk('imagekit')->url($filename);
                $response[$file] = $url;
            } else {
                $url = null;
                
            }
        }
        return response()->json($response);
      
    }

    /**
     * Display the specified rekomendasi_pengangkatan_anak.
     */
    public function show($id)
    {
        $userid = Auth::user()->id;
        $rekomendasiPengangkatanAnak =  DB::table('rekomendasi_pengangkatan_anaks as w')->select(
            'w.*',
            'rls.name as name_roles',
            'usr.name',
            'prov.name_prov',
            'kota.name_cities',
            'kecamatan.name_districts',
            'b.name_village',
        )
            ->leftjoin('roles as rls', 'rls.id', '=', 'w.tujuan_pengan')
            ->leftjoin('users as usr', 'usr.id', '=', 'w.petugas_pengan')
            ->leftjoin('indonesia_provinces as prov', 'prov.code', '=', 'w.id_provinsi_pengan')
            ->leftjoin('indonesia_cities as kota', 'kota.code', '=', 'w.id_kabkot_pengan')
            ->leftjoin('indonesia_districts as kecamatan', 'kecamatan.code', '=', 'w.id_kecamatan_pengan')
            ->leftjoin('indonesia_villages as b', 'b.code', '=', 'w.id_kelurahan_pengan')
            ->where('w.id', $id)->first();

        if (empty($rekomendasiPengangkatanAnak)) {
            Flash::error('Rekomendasi not found');

            return redirect(route('rekomendasi_pengangkatan_anaks.index'));
        }
        $roleid = DB::table('roles')
            ->where('name', 'Back Ofiice kelurahan')
            // ->where('name', 'supervisor')
            ->orWhere('name', 'supervisor')
            ->get();
        $checkroles = DB::table('model_has_roles')
            ->leftjoin('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->get();
        // dd($checkroles

        $log_pengan =  DB::table('log_pengan as w')->select(
            'w.*','rls.name', 'usr.name'
        )
            ->leftjoin('roles as rls', 'rls.id', '=', 'w.tujuan_pengan')
            ->leftjoin('users as usr', 'usr.id', '=', 'w.petugas_pengan')
            // ->select('w.*', 'rls.name', 'usr.name')
            ->where('w.id_trx_pengan', $id)->get();
      

        return view('rekomendasi_pengangkatan_anaks.show', compact('rekomendasiPengangkatanAnak', 'roleid', 'checkroles', 'log_pengan'));
    }

    /**
     * Show the form for editing the specified rekomendasi_pengangkatan_anak.
     */
    public function edit($id)
    {
        
        $getUsers = DB::table('model_has_roles')
            ->leftjoin('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->leftjoin('rekomendasi_pengangkatan_anaks', 'rekomendasi_pengangkatan_anaks.createdby_pengan', '=', 'model_has_roles.model_id')
            ->where('rekomendasi_pengangkatan_anaks.id', '=', $id)
            // ->where('status_aksi', '=', 'Draft')
            // ->orwhere('status_aksi', '=', 'Teruskan')
            ->get();

        $users =  Auth::user()->id;
        $getAuth = DB::table('model_has_roles')
            ->leftJoin('users', 'users.id', '=', 'model_has_roles.model_id')
            ->leftJoin('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('model_id', $users)
            ->get();
        
        $createdby = DB::table('rekomendasi_pengangkatan_anaks')
            ->join('users', 'rekomendasi_pengangkatan_anaks.createdby_pengan', '=', 'users.name')
            ->join('model_has_roles', 'model_has_roles.model_id', '=', 'users.id')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->select('rekomendasi_pengangkatan_anaks.id', 'rekomendasi_pengangkatan_anaks.createdby_pengan', 'roles.name')
            ->get();
        

        $rekomendasiPengangkatanAnak =  DB::table('rekomendasi_pengangkatan_anaks as w')->select(
            'w.*',
            'rls.name',
            'usr.name',
            'prov.name_prov',
            'kota.name_cities',
            'kecamatan.name_districts',
            'b.name_village',
        )
            ->leftjoin('roles as rls', 'rls.id', '=', 'w.tujuan_pengan')
            ->leftjoin('users as usr', 'usr.id', '=', 'w.petugas_pengan')
            ->leftjoin('indonesia_provinces as prov', 'prov.code', '=', 'w.id_provinsi_pengan')
            ->leftjoin('indonesia_cities as kota', 'kota.code', '=', 'w.id_kabkot_pengan')
            ->leftjoin('indonesia_districts as kecamatan', 'kecamatan.code', '=', 'w.id_kecamatan_pengan')
            ->leftjoin('indonesia_villages as b', 'b.code', '=', 'w.id_kelurahan_pengan')
            ->where('w.id', $id)->first();
        // dd($rekomendasiPengangkatanAnak);
        $getdata = DB::table('model_has_roles')
            ->leftjoin('rekomendasi_pengangkatan_anaks as b', 'b.tujuan_pengan', '=', 'model_has_roles.role_id')
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
        } else if ($roles->contains('SekertarisDinas') || $roles->contains('kepala bidang') || $roles->contains('Back Ofiice Kota') || $roles->contains('Back Ofiice kelurahan')) {
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
                ->where('name', 'Front Office kota')
                ->get();
        } elseif ($roles->contains('Front Office kota')) {
            $roleid = DB::table('roles')
                ->where('name', ['Back Ofiice Kota', 'Front Office kelurahan'])
                ->get();
        }elseif ($roles->contains('Back Ofiice kelurahan')) {
            $roleid = DB::table('roles')
                ->where('name', ['Back Ofiice Kota', 'Front Office kelurahan'])
                ->get();
        } else if ($roles->contains('Back Ofiice Kota')) {
            $roleid = DB::table('roles')
                ->whereIn('name', ['Front Office kota', 'kepala bidang'])
                ->get();
        }else if ($roles->contains('supervisor')) {
            $roleid = DB::table('roles')
                ->whereIn('name', ['Front Office kota', 'Back Ofiice Kota'])
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

        return view('rekomendasi_pengangkatan_anaks.edit', compact( 'rekomendasiPengangkatanAnak', 'roleid', 'getdata', 'alur', 'createdby', 'getUsers', 'getAuth'));
    }
    /**
     * Update the specified rekomendasi_pengangkatan_anak in storage.
     */
    public function update($id, Request $request)
    {
        $getdata = rekomendasi_pengangkatan_anak::where('id', $id)->first();
        $datapengan = $request->all();
        // dd($getdata);
        $files = [
            'surat_izin_pengan',
            'surat_sehat_pengan',
            'surat_sehat_jiwa_pengan',
            'surat_kandungan',
            'akta_cota_pengan',
            'skck_pengan',
            'akta_nikah_cota_pengan',
            'KK_cota_pengan',
            'KTP_cota_pengan',
            'KK_ortuangkat_pengan',
            'KTP_ortuangkat_pengan',
            'akta_canak_pengan',
            'super_canak_pengan',
            'izincota_suami_pengan',
            'izincota_istri_pengan',
            'super_terbaik_pengan',
            'super_notdiskriminasi_pengan',
            'sudok_fakta_pengan',
            'super_asalusul_pengan',
            'super_motivasi_pengan',
            'super_notwalinikah_pengan',
            'super_hibah',
            'super_asuransi' 
        ];
        foreach ($files as $file) {
            if ($request->file($file)) {
                // dd($request->file($file));
                $path = $request->file($file);
                $nama_file = 'yayasan/' . $file . '/' . $path->getClientOriginalName();
                Storage::disk('imagekit')->put($nama_file, fopen($path->getRealPath(), 'r'));
                $datapengan[$file] = Storage::disk('imagekit')->url($nama_file);
            } else {
                $datapengan[$file] =  $getdata->file ;
            }
        }
        $datapengan['updatedby_pengan'] = Auth::user()->id;
        // dd($datapengan);
        $getdata->update($datapengan);

        $logpengaduan = new log_pengan();
        $logpengaduan['id_trx_pengan'] = $getdata->id;
        $logpengaduan['id_alur_pengan'] = $request->get('status_aksi_pengan');
        $logpengaduan['petugas_pengan'] = $request->get('petugas_pengan');
        $logpengaduan['catatan_pengan']  = $request->get('catatan_pengan');
        $logpengaduan['tujuan_pengan'] = $request->get('tujuan_pengan');
        $logpengaduan['created_by_pengan'] = Auth::user()->id;
        $logpengaduan['updated_by_pengan'] = Auth::user()->id;
        $logpengaduan->save();

        return redirect()->route('rekomendasi_pengangkatan_anaks.index')->with('success', 'Data berhasil diupdate.');
    }

    /**
     * Remove the specified rekomendasi_pengangkatan_anak from storage.
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $rekomendasiPengangkatanAnak = $this->rekomendasiPengangkatanAnakRepository->find($id);

        if (empty($rekomendasiPengangkatanAnak)) {
            Flash::error('Rekomendasi Pengangkatan Anak not found');

            return redirect(route('rekomendasi_pengangkatan_anaks.index'));
        }

        $this->rekomendasiPengangkatanAnakRepository->delete($id);

        Flash::success('Rekomendasi Pengangkatan Anak deleted successfully.');

        return redirect(route('rekomendasi_pengangkatan_anaks.index'));
    }
    public function draft(Request $request)
    {
        $user_name = Auth::user()->name;
        $query = DB::table('rekomendasi_pengangkatan_anaks')
            ->leftjoin('users', 'users.id', '=', 'rekomendasi_pengangkatan_anaks.petugas_pengan')
            // ->leftjoin('wilayahs', 'wilayahs.createdby', '=', 'rekomendasi_pengangkatan_anaks.createdby_pengan')
            // ->leftjoin('model_has_roles', 'model_has_roles.model_id', '=', 'rekomendasi_pengangkatan_anaks.tujuan_pengan_bantuan_pendidikans')
            ->leftjoin('indonesia_villages as b', 'b.code', '=', 'rekomendasi_pengangkatan_anaks.id_kelurahan_pengan')
            ->leftjoin('indonesia_districts as d', 'd.code', '=', 'rekomendasi_pengangkatan_anaks.id_kecamatan_pengan')
            ->select('rekomendasi_pengangkatan_anaks.*', 'b.name_village', 'd.name_districts', 'users.name')
            ->distinct();
        $user_id = Auth::user()->id;
        $user_wilayah = DB::table('wilayahs')
        ->join('model_has_roles', 'model_has_roles.model_id', '=', 'wilayahs.createdby')
        ->join('users', 'users.id', '=', 'model_has_roles.model_id')
        ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
        ->select('wilayahs.*','roles.name','model_has_roles.*')
        ->where('wilayahs.createdby', $user_id)
        ->where(function ($query) {
            $query->where('status_wilayah', 1);
        })
        ->first();
        // dd($user_wilayah);
        if ($user_wilayah->name == 'Front Office kota') {
            $query->Where(function ($query) use ($user_wilayah) {
                $query->where('rekomendasi_pengangkatan_anaks.id_kabkot_pengan', $user_wilayah->kota_id);
                $query->where('rekomendasi_pengangkatan_anaks.status_aksi_pengan', 'Draft');
                $query->where('rekomendasi_pengangkatan_anaks.createdby_pengan',  Auth::user()->id);
            });
        }
        if ($user_wilayah->name == 'Front Office Kelurahan') {
            $query->Where(function ($query) use ($user_wilayah) {
                $query->where('rekomendasi_pengangkatan_anaks.id_kelurahan_pengan', $user_wilayah->kelurahan_id);
                $query->where('rekomendasi_pengangkatan_anaks.status_aksi_pengan', 'Draft');
                $query->where('rekomendasi_pengangkatan_anaks.createdby_pengan',  Auth::user()->id);
            });
        }
        if ($user_wilayah->name == 'fasilitator') {
            $query->Where(function ($query) use ($user_wilayah) {
                $query->where('rekomendasi_pengangkatan_anaks.id_kelurahan_pengan', $user_wilayah->kelurahan_id);
                $query->where('rekomendasi_pengangkatan_anaks.status_aksi_pengan', 'Draft');
                $query->where('rekomendasi_pengangkatan_anaks.createdby_pengan',  Auth::user()->id);
            });
        }
        if ($user_wilayah->name == 'Front Office Kelurahan'|| $user_wilayah == 'fasilitator') {
            if ($request->has('search') && !empty($request->search['value'])) {
                $search = $request->search['value'];
                $query->Where(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_pengangkatan_anaks.id_kelurahan_pengan', $user_wilayah->kelurahan_id);
                    $query->where('rekomendasi_pengangkatan_anaks.status_aksi_pengan', 'Draft');
                    $query->where('rekomendasi_pengangkatan_anaks.createdby_pengan',  Auth::user()->id);
                })->where(function ($query) use ($search) {
                    $query->where('rekomendasi_pengangkatan_anaks.no_pendaftaran_pengan', 'like', "%$search%");
                        // ->orwhere('indonesia_villages.name_village', 'like', "%$search%")
                        // ->orwhere('d.name_districts', 'like', "%$search%")
                        // ->orwhere('indonesia_villages.name_village', 'like', "%$search%")
                        // ->orwhere('rekomendasi_biaya_perawatans.alamat_biper', 'like', "%$search%");
                });
            }
        }elseif($user_wilayah->name == 'Front Office Kota')  {
            if ($request->has('search') && !empty($request->search['value'])) {
                $search = $request->search['value'];
                $query->Where(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_pengangkatan_anaks.id_kabkot_pengan', $user_wilayah->kota_id);
                    $query->where('rekomendasi_pengangkatan_anaks.status_aksi_pengan', 'Draft');
                    $query->where('rekomendasi_pengangkatan_anaks.createdby_pengan',  Auth::user()->id);
                })->where(function ($query) use ($search) {
                    $query->where('rekomendasi_pengangkatan_anaks.no_pendaftaran_pengan', 'like', "%$search%");
                        // ->orwhere('indonesia_villages.name_village', 'like', "%$search%")
                        // ->orwhere('d.name_districts', 'like', "%$search%")
                        // ->orwhere('indonesia_villages.name_village', 'like', "%$search%")
                        // ->orwhere('rekomendasi_biaya_perawatans.alamat_biper', 'like', "%$search%");
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
        // Get paginated data
        $start = $request->start;
        $length = $request->length;
        $query->offset($start)->limit($length);
        $data = $query->get();
        // mengubah data JSON menjadi objek PHP

        return response()->json([
            'draw' => $request->input('draw'),
            'recordsTotal' => rekomendasi_pengangkatan_anak::count(),
            'recordsFiltered' => $total_filtered_items,
            'data' => $data,
        ]);
    }
    public function diproses(Request $request)
    {
        $user_id = Auth::user()->id;
        $user_wilayah = DB::table('wilayahs')
        ->join('model_has_roles', 'model_has_roles.model_id', '=', 'wilayahs.createdby')
        ->join('users', 'users.id', '=', 'model_has_roles.model_id')
        ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
        ->select('wilayahs.*','roles.name','model_has_roles.*')
        ->where('wilayahs.createdby', $user_id)
        ->where(function ($query) {
            $query->where('status_wilayah', 1);
        })
        ->first();

    // dd($user_wilayah);
        if ($user_wilayah->name == 'fasilitator') {
            $query = DB::table('rekomendasi_pengangkatan_anaks')
                ->join('users', 'users.id', '=', 'rekomendasi_pengangkatan_anaks.petugas_pengan')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_pengangkatan_anaks.id_kecamatan_pengan')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_pengangkatan_anaks.id_kelurahan_pengan')
                ->select('rekomendasi_pengangkatan_anaks.*', 'b.name_village', 'd.name_districts', 'users.name');
        } elseif ($user_wilayah->name == 'Front Office kota') {
            $query = DB::table('rekomendasi_pengangkatan_anaks')
                ->join('users', 'users.id', '=', 'rekomendasi_pengangkatan_anaks.petugas_pengan')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_pengangkatan_anaks.id_kecamatan_pengan')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_pengangkatan_anaks.id_kelurahan_pengan')
                ->select('rekomendasi_pengangkatan_anaks.*', 'b.name_village', 'd.name_districts', 'users.name');
        } elseif ($user_wilayah->name == 'Front Office Kelurahan') {
            $query = DB::table('rekomendasi_pengangkatan_anaks')
                ->join('users', 'users.id', '=', 'rekomendasi_pengangkatan_anaks.petugas_pengan')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_pengangkatan_anaks.id_kecamatan_pengan')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_pengangkatan_anaks.id_kelurahan_pengan')
                ->select('rekomendasi_pengangkatan_anaks.*', 'b.name_village', 'd.name_districts', 'users.name');
        } elseif ($user_wilayah->name == 'supervisor') {
            $query = DB::table('rekomendasi_pengangkatan_anaks')
                ->join('users', 'users.id', '=', 'rekomendasi_pengangkatan_anaks.petugas_pengan')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_pengangkatan_anaks.id_kecamatan_pengan')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_pengangkatan_anaks.id_kelurahan_pengan')
                ->select('rekomendasi_pengangkatan_anaks.*', 'b.name_village', 'd.name_districts', 'users.name');
        } elseif ($user_wilayah->name == 'Back Ofiice kelurahan') {
            $query = DB::table('rekomendasi_pengangkatan_anaks')
                ->join('users', 'users.id', '=', 'rekomendasi_pengangkatan_anaks.petugas_pengan')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_pengangkatan_anaks.id_kecamatan_pengan')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_pengangkatan_anaks.id_kelurahan_pengan')
                ->select('rekomendasi_pengangkatan_anaks.*', 'b.name_village', 'd.name_districts', 'users.name');
        } elseif ($user_wilayah->name == 'Back Ofiice Kota') {
            $query = DB::table('rekomendasi_pengangkatan_anaks')
                ->join('users', 'users.id', '=', 'rekomendasi_pengangkatan_anaks.petugas_pengan')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_pengangkatan_anaks.id_kecamatan_pengan')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_pengangkatan_anaks.id_kelurahan_pengan')
                ->select('rekomendasi_pengangkatan_anaks.*', 'b.name_village', 'd.name_districts', 'users.name');
        } elseif ($user_wilayah->name == 'kepala bidang') {
            $query = DB::table('rekomendasi_pengangkatan_anaks')
                ->join('users', 'users.id', '=', 'rekomendasi_pengangkatan_anaks.petugas_pengan')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_pengangkatan_anaks.id_kecamatan_pengan')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_pengangkatan_anaks.id_kelurahan_pengan')
                ->select('rekomendasi_pengangkatan_anaks.*', 'b.name_village', 'd.name_districts', 'users.name');
        } elseif ($user_wilayah->name == 'SekertarisDinas') {
            $query = DB::table('rekomendasi_pengangkatan_anaks')
                ->join('users', 'users.id', '=', 'rekomendasi_pengangkatan_anaks.petugas_pengan')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_pengangkatan_anaks.id_kecamatan_pengan')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_pengangkatan_anaks.id_kelurahan_pengan')
                ->select('rekomendasi_pengangkatan_anaks.*', 'b.name_village', 'd.name_districts', 'users.name');
        } elseif ($user_wilayah->name == 'KepalaDinas') {
            $query = DB::table('rekomendasi_pengangkatan_anaks')
                ->join('users', 'users.id', '=', 'rekomendasi_pengangkatan_anaks.petugas_pengan')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_pengangkatan_anaks.id_kecamatan_pengan')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_pengangkatan_anaks.id_kelurahan_pengan')
                ->select('rekomendasi_pengangkatan_anaks.*', 'b.name_village', 'd.name_districts', 'users.name');
        } else {
            $query = DB::table('pengaduans')
                ->join('users', 'users.id', '=', 'pengaduans.createdby')
                ->join('indonesia_villages as b', 'b.code', '=', 'pengaduans.id_kelurahan')
                ->select('pengaduans.*', 'b.name_village');
        }
        if ($user_wilayah->name == 'fasilitator') {
            $query->orWhere(function ($query) use ($user_wilayah) {
                $query->where('rekomendasi_pengangkatan_anaks.id_kelurahan_pengan', '=', $user_wilayah->kelurahan_id)
                    ->where('rekomendasi_pengangkatan_anaks.tujuan_pengan', '=', $user_wilayah->role_id)
                    ->where('rekomendasi_pengangkatan_anaks.petugas_pengan', '=', auth::user()->id)

                    ->where(function ($query) {
                        $query->where('rekomendasi_pengangkatan_anaks.status_aksi_pengan', '=', 'Teruskan')
                            ->orWhere('rekomendasi_pengangkatan_anaks.status_aksi_pengan', '=', 'kembalikan');
                    });
            });
        }
        if ($user_wilayah->name == 'Front Office Kelurahan') {
            $query->orWhere(function ($query) use ($user_wilayah) {
                $query->where('rekomendasi_pengangkatan_anaks.id_kelurahan_pengan', '=', $user_wilayah->kelurahan_id)
                    ->where('rekomendasi_pengangkatan_anaks.tujuan_pengan', '=', $user_wilayah->role_id)
                    ->where('rekomendasi_pengangkatan_anaks.petugas_pengan', '=', auth::user()->id)

                    ->where(function ($query) {
                        $query->where('rekomendasi_pengangkatan_anaks.status_aksi_pengan', '=', 'Teruskan')
                            ->orWhere('rekomendasi_pengangkatan_anaks.status_aksi_pengan', '=', 'kembalikan');
                    });
            });
        }
        if ($user_wilayah->name == 'Front Office kota') {
            $query->orWhere(function ($query) use ($user_wilayah) {
                $query->where('rekomendasi_pengangkatan_anaks.id_kabkot_pengan', '=', $user_wilayah->kota_id)
                    ->where('rekomendasi_pengangkatan_anaks.tujuan_pengan', '=', $user_wilayah->role_id)
                    ->where('rekomendasi_pengangkatan_anaks.petugas_pengan', '=', auth::user()->id)

                    ->where(function ($query) {
                        $query->where('rekomendasi_pengangkatan_anaks.status_aksi_pengan', '=', 'Teruskan')
                            ->orWhere('rekomendasi_pengangkatan_anaks.status_aksi_pengan', '=', 'kembalikan');
                    });
            });
        }
        if ($user_wilayah->name == 'supervisor') {
            $query->orWhere(function ($query) use ($user_wilayah) {
                $query->where('rekomendasi_pengangkatan_anaks.id_kabkot_pengan', '=', $user_wilayah->kota_id)
                    ->where('rekomendasi_pengangkatan_anaks.tujuan_pengan', '=', $user_wilayah->role_id)
                    ->where('rekomendasi_pengangkatan_anaks.petugas_pengan', '=', auth::user()->id)
                    ->where(function ($query) {
                        $query->where('rekomendasi_pengangkatan_anaks.status_aksi_pengan', '=', 'Teruskan')
                            ->orWhere('rekomendasi_pengangkatan_anaks.status_aksi_pengan', '=', 'kembalikan');
                    });
                // dd($va);
            });
        }



        if ($user_wilayah->name == 'Back Ofiice Kota') {
            $query->orWhere(function ($query) use ($user_wilayah) {
                $query->where('rekomendasi_pengangkatan_anaks.id_kabkot_pengan', '=', $user_wilayah->kota_id)
                    ->where('rekomendasi_pengangkatan_anaks.tujuan_pengan', '=', $user_wilayah->role_id)
                    ->where('rekomendasi_pengangkatan_anaks.petugas_pengan', '=', auth::user()->id)
                    ->where(function ($query) {
                        $query->where('rekomendasi_pengangkatan_anaks.status_aksi_pengan', '=', 'Teruskan')
                            ->orWhere('rekomendasi_pengangkatan_anaks.status_aksi_pengan', '=', 'kembalikan');
                    });
                // dd($va);
            });
            // dd($query->count());
        }
        if ($user_wilayah->name == 'Back Ofiice kelurahan') {
            $query->orWhere(function ($query) use ($user_wilayah) {
                $query->where('rekomendasi_pengangkatan_anaks.id_kelurahan_pengan', '=', $user_wilayah->kelurahan_id)
                    ->where('rekomendasi_pengangkatan_anaks.tujuan_pengan', '=', $user_wilayah->role_id)
                    ->where('rekomendasi_pengangkatan_anaks.petugas_pengan', '=', auth::user()->id)
                    ->where(function ($query) {
                        $query->where('rekomendasi_pengangkatan_anaks.status_aksi_pengan', '=', 'Teruskan')
                            ->orWhere('rekomendasi_pengangkatan_anaks.status_aksi_pengan', '=', 'kembalikan');
                    });
                // dd($va);
            });
            // dd($query->count());
        }
        if ($user_wilayah->name == 'kepala bidang') {
            $query->orWhere(function ($query) use ($user_wilayah) {
                $query->where('rekomendasi_pengangkatan_anaks.id_kabkot_pengan', '=', $user_wilayah->kota_id)
                    ->where('rekomendasi_pengangkatan_anaks.tujuan_pengan', '=', $user_wilayah->role_id)
                    ->where('rekomendasi_pengangkatan_anaks.petugas_pengan', '=', auth::user()->id)
                    ->where(function ($query) {
                        $query->where('rekomendasi_pengangkatan_anaks.status_aksi_pengan', '=', 'Teruskan')
                            ->orWhere('rekomendasi_pengangkatan_anaks.status_aksi_pengan', '=', 'kembalikan');
                    });
                // dd($va);
            });
        }
        if ($user_wilayah->name == 'SekertarisDinas') {
            $query->orWhere(function ($query) use ($user_wilayah) {
                $query->where('rekomendasi_pengangkatan_anaks.id_kabkot_pengan', '=', $user_wilayah->kota_id)
                    ->where('rekomendasi_pengangkatan_anaks.tujuan_pengan', '=', $user_wilayah->role_id)
                    ->where('rekomendasi_pengangkatan_anaks.petugas_pengan', '=', auth::user()->id)
                    ->where(function ($query) {
                        $query->where('rekomendasi_pengangkatan_anaks.status_aksi_pengan', '=', 'Teruskan')
                            ->orWhere('rekomendasi_pengangkatan_anaks.status_aksi_pengan', '=', 'kembalikan');
                    });
                // dd($va);
            });
            // dd($query);
        }
        if ($user_wilayah->name == 'KepalaDinas') {
            $query->orWhere(function ($query) use ($user_wilayah) {
                $query->where('rekomendasi_pengangkatan_anaks.id_kabkot_pengan', '=', $user_wilayah->kota_id)
                    ->where('rekomendasi_pengangkatan_anaks.tujuan_pengan', '=', $user_wilayah->role_id)
                    ->where('rekomendasi_pengangkatan_anaks.petugas_pengan', '=', auth::user()->id)
                    ->where(function ($query) {
                        $query->where('rekomendasi_pengangkatan_anaks.status_aksi_pengan', '=', 'Teruskan')
                            ->orWhere('rekomendasi_pengangkatan_anaks.status_aksi_pengan', '=', 'kembalikan');
                    });
                // dd($va);
            });
        }
        if ($user_wilayah->name == 'Front Office Kelurahan' || $user_wilayah == 'Back Ofiice kelurahan'|| $user_wilayah == 'fasilitator') {
            if ($request->has('search') && !empty($request->search['value'])) {
                $search = $request->search['value'];
                $query->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_pengangkatan_anaks.id_kelurahan_pengan', '=', $user_wilayah->kelurahan_id)
                        ->where('rekomendasi_pengangkatan_anaks.tujuan_pengan', '=', $user_wilayah->role_id)
                        ->where('rekomendasi_pengangkatan_anaks.petugas_pengan', '=', auth::user()->id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_pengangkatan_anaks.status_aksi_pengan', '=', 'Teruskan')
                                ->orWhere('rekomendasi_pengangkatan_anaks.status_aksi_pengan', '=', 'kembalikan');
                        });
                    // dd($va);
                })->where(function ($query) use ($search) {
                    $query->where('rekomendasi_pengangkatan_anaks.no_pendaftaran_pengan', 'like', "%$search%");
                        // ->orwhere('indonesia_villages.name_village', 'like', "%$search%")
                        // ->orwhere('d.name_districts', 'like', "%$search%")
                        // ->orwhere('indonesia_villages.name_village', 'like', "%$search%")
                        // ->orwhere('rekomendasi_biaya_perawatans.alamat_biper', 'like', "%$search%");
                });
            }
        }elseif($user_wilayah->name == 'Front Office Kota' || $user_wilayah->name == 'Back Ofiice Kota'|| $user_wilayah->name == 'KepalaDinas'||$user_wilayah->name == 'SekertarisDinas'||$user_wilayah->name == 'Supervisor') {
            if ($request->has('search') && !empty($request->search['value'])) {
                $search = $request->search['value'];
                $query->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_pengangkatan_anaks.id_kabkot_pengan', '=', $user_wilayah->kota_id)
                        ->where('rekomendasi_pengangkatan_anaks.tujuan_pengan', '=', $user_wilayah->role_id)
                        ->where('rekomendasi_pengangkatan_anaks.petugas_pengan', '=', auth::user()->id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_pengangkatan_anaks.status_aksi_pengan', '=', 'Teruskan')
                                ->orWhere('rekomendasi_pengangkatan_anaks.status_aksi_pengan', '=', 'kembalikan');
                        });
                    // dd($va);
                })->where(function ($query) use ($search) {
                    $query->where('rekomendasi_pengangkatan_anaks.no_pendaftaran_pengan', 'like', "%$search%");
                        // ->orwhere('indonesia_villages.name_village', 'like', "%$search%")
                        // ->orwhere('d.name_districts', 'like', "%$search%")
                        // ->orwhere('indonesia_villages.name_village', 'like', "%$search%")
                        // ->orwhere('rekomendasi_biaya_perawatans.alamat_biper', 'like', "%$search%");
                });
            }
        }

        $total_filtered_items = $query->count();
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
        $gabunganData = new stdClass;
        $gabunganData->userLogin=$userLogin;
        $gabunganData->dataRekomPengan=$tmp;
           array_push($result, $gabunganData);
        }

        return response()->json([
            'draw' => $request->input('draw'),
            'recordsTotal' => rekomendasi_pengangkatan_anak::count(),
            'recordsFiltered' => $total_filtered_items,
            'data' => $result,
        ]);
    }

    public function teruskan(Request $request)
    {
        $user_name = Auth::user()->name;
        // dd($user_name);

        $query = DB::table('rekomendasi_pengangkatan_anaks')
            ->join('users', 'users.id', '=', 'rekomendasi_pengangkatan_anaks.petugas_pengan')
            ->leftjoin('indonesia_villages as b', 'b.code', '=', 'rekomendasi_pengangkatan_anaks.id_kelurahan_pengan')
            ->leftjoin('indonesia_districts as d', 'd.code', '=', 'rekomendasi_pengangkatan_anaks.id_kecamatan_pengan')
            ->select('rekomendasi_pengangkatan_anaks.*', 'b.name_village', 'd.name_districts', 'users.name');

        $user_id = Auth::user()->id;
        // dd($user_id);

        $user_wilayah = DB::table('wilayahs')
        ->join('model_has_roles', 'model_has_roles.model_id', '=', 'wilayahs.createdby')
        ->join('users', 'users.id', '=', 'model_has_roles.model_id')
        ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
        ->select('wilayahs.*','roles.name','model_has_roles.*')
        ->where('wilayahs.createdby', $user_id)
        ->where(function ($query) {
            $query->where('status_wilayah', 1);
        })
        ->first();
        if ($user_wilayah->name == 'fasilitator') {
            $query = DB::table('rekomendasi_pengangkatan_anaks')
                ->join('users', 'users.id', '=', 'rekomendasi_pengangkatan_anaks.petugas_pengan')
                ->join('log_pengan', 'log_pengan.id_trx_pengan', '=', 'rekomendasi_pengangkatan_anaks.id')
                // ->join('model_has_roles', 'model_has_roles.model_id', '=', 'rekomendasi_pengangkatan_anaks.tujuan_pengan')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_pengangkatan_anaks.id_kelurahan_pengan')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_pengangkatan_anaks.id_kecamatan_pengan')
                ->select('rekomendasi_pengangkatan_anaks.*', 'b.name_village', 'd.name_districts','log_pengan.tujuan_pengan', 'log_pengan.petugas_pengan' , 'users.name')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_pengangkatan_anaks.id_kelurahan_pengan', $user_wilayah->kelurahan_id)
                        ->where('rekomendasi_pengangkatan_anaks.tujuan_pengan', '!=', $user_wilayah->role_id)
                        ->where('log_pengan.created_by_pengan', '=', auth::user()->id)
                        // ->where('rekomendasi_pengangkatan_anaks.petugas_pengan','!=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_pengangkatan_anaks.status_aksi_pengan', '=', 'Teruskan')
                                ->orWhere('rekomendasi_pengangkatan_anaks.status_aksi_pengan', '=', 'kembalikan');
                        });
                })->distinct();
            // dd($query);

        }
        if ($user_wilayah->name == 'Front Office Kelurahan') {
            $query = DB::table('rekomendasi_pengangkatan_anaks')
                ->join('users', 'users.id', '=', 'rekomendasi_pengangkatan_anaks.petugas_pengan')
                // ->join('log_pengan', 'log_pengan.id_trx_pengan', '=', 'rekomendasi_pengangkatan_anaks.id')
                // ->join('model_has_roles', 'model_has_roles.model_id', '=', 'rekomendasi_pengangkatan_anaks.tujuan_pengan')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_pengangkatan_anaks.id_kelurahan_pengan')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_pengangkatan_anaks.id_kecamatan_pengan')
                ->select('rekomendasi_pengangkatan_anaks.*', 'b.name_village', 'd.name_districts',  'users.name')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_pengangkatan_anaks.id_kelurahan_pengan', $user_wilayah->kelurahan_id)
                        ->where('rekomendasi_pengangkatan_anaks.tujuan_pengan', '!=', $user_wilayah->role_id)
                        // ->where('log_pengan.created_by_pengan', '=', auth::user()->id)
                        // ->where('rekomendasi_pengangkatan_anaks.petugas_pengan','!=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_pengangkatan_anaks.status_aksi_pengan', '=', 'Teruskan')
                                ->orWhere('rekomendasi_pengangkatan_anaks.status_aksi_pengan', '=', 'kembalikan');
                        });
                })->distinct();
            // dd($query);

        }
        if ($user_wilayah->name == 'Front Office kota') {
            $query = DB::table('rekomendasi_pengangkatan_anaks')
                ->join('users', 'users.id', '=', 'rekomendasi_pengangkatan_anaks.petugas_pengan')
                ->join('log_pengan', 'log_pengan.id_trx_pengan', '=', 'rekomendasi_pengangkatan_anaks.id')
                // ->join('model_has_roles', 'model_has_roles.model_id', '=', 'rekomendasi_pengangkatan_anaks.tujuan_pengan')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_pengangkatan_anaks.id_kelurahan_pengan')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_pengangkatan_anaks.id_kecamatan_pengan')
                ->select('rekomendasi_pengangkatan_anaks.*', 'b.name_village', 'd.name_districts','users.name')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_pengangkatan_anaks.id_kabkot_pengan', $user_wilayah->kota_id)
                        ->where('rekomendasi_pengangkatan_anaks.tujuan_pengan', '!=', $user_wilayah->role_id)
                        ->where('log_pengan.created_by_pengan', '=', auth::user()->id)
                        // ->where('rekomendasi_pengangkatan_anaks.petugas_pengan','!=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_pengangkatan_anaks.status_aksi_pengan', '=', 'Teruskan')
                                ->orWhere('rekomendasi_pengangkatan_anaks.status_aksi_pengan', '=', 'kembalikan');
                        });
                })->distinct();


            // dd($query);

        }

        //Back office kota 
        if ($user_wilayah->name == 'Back Ofiice Kota') {
            // dd($user_wilayah->model_id);
            $query = DB::table('rekomendasi_pengangkatan_anaks')
                ->join('users', 'users.id', '=', 'rekomendasi_pengangkatan_anaks.petugas_pengan')
                // ->join('log_pengan', 'log_pengan.id_trx_pengan', '=', 'rekomendasi_pengangkatan_anaks.id')
                // ->join('model_has_roles', 'model_has_roles.model_id', '=', 'rekomendasi_pengangkatan_anaks.tujuan_pengan')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_pengangkatan_anaks.id_kelurahan_pengan')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_pengangkatan_anaks.id_kecamatan_pengan')
                ->select('rekomendasi_pengangkatan_anaks.*', 'b.name_village', 'd.name_districts','users.name')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_pengangkatan_anaks.id_kabkot_pengan', $user_wilayah->kota_id)
                        ->where('rekomendasi_pengangkatan_anaks.tujuan_pengan', '!=', $user_wilayah->role_id)
                        // ->where('rekomendasi_pengangkatan_anaks.created_by_pengan', '=', auth::user()->id)
                        // ->where('rekomendasi_pengangkatan_anaks.petugas_pengan','!=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_pengangkatan_anaks.status_aksi_pengan', '=', 'Teruskan')
                                ->orWhere('rekomendasi_pengangkatan_anaks.status_aksi_pengan', '=', 'kembalikan');
                        });
                });
                // ->get();
            // dd($query);
        }
        if ($user_wilayah->name == 'Back Ofiice kelurahan') {
            // dd($user_wilayah->model_id);
            $query = DB::table('rekomendasi_pengangkatan_anaks')
                ->join('users', 'users.id', '=', 'rekomendasi_pengangkatan_anaks.petugas_pengan')
                // ->join('log_pengan', 'log_pengan.id_trx_pengan', '=', 'rekomendasi_pengangkatan_anaks.id')
                // ->join('model_has_roles', 'model_has_roles.model_id', '=', 'rekomendasi_pengangkatan_anaks.tujuan_pengan')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_pengangkatan_anaks.id_kelurahan_pengan')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_pengangkatan_anaks.id_kecamatan_pengan')
                ->select('rekomendasi_pengangkatan_anaks.*', 'b.name_village', 'd.name_districts','users.name')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_pengangkatan_anaks.id_kelurahan_pengan', $user_wilayah->kelurahan_id)
                        ->where('rekomendasi_pengangkatan_anaks.tujuan_pengan', '!=', $user_wilayah->role_id)
                        // ->where('rekomendasi_pengangkatan_anaks.createdby_pengan', '=', auth::user()->id)
                        // ->where('rekomendasi_pengangkatan_anaks.petugas_pengan','!=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_pengangkatan_anaks.status_aksi_pengan', '=', 'Teruskan')
                                ->orWhere('rekomendasi_pengangkatan_anaks.status_aksi_pengan', '=', 'kembalikan');
                        });
                });
        }

        //Back office kota 
        if ($user_wilayah->name == 'supervisor') {
            // dd($user_wilayah->model_id);
            $query = DB::table('rekomendasi_pengangkatan_anaks')
                ->join('users', 'users.id', '=', 'rekomendasi_pengangkatan_anaks.petugas_pengan')
                // ->join('log_pengan', 'log_pengan.id_trx_pengan', '=', 'rekomendasi_pengangkatan_anaks.id')
                // ->join('model_has_roles', 'model_has_roles.model_id', '=', 'rekomendasi_pengangkatan_anaks.tujuan_pengan')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_pengangkatan_anaks.id_kelurahan_pengan')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_pengangkatan_anaks.id_kecamatan_pengan')
                ->select('rekomendasi_pengangkatan_anaks.*', 'b.name_village', 'd.name_districts','users.name')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_pengangkatan_anaks.id_kabkot_pengan', $user_wilayah->kota_id)
                        ->where('rekomendasi_pengangkatan_anaks.tujuan_pengan', '!=', $user_wilayah->role_id)
                        // ->where('log_pengan.created_by_pengan', '=', auth::user()->id)
                        // ->where('rekomendasi_pengangkatan_anaks.petugas_pengan','!=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_pengangkatan_anaks.status_aksi_pengan', '=', 'Teruskan')
                                ->orWhere('rekomendasi_pengangkatan_anaks.status_aksi_pengan', '=', 'kembalikan');
                        });
                });
        }

        if ($user_wilayah->name == 'kepala bidang') {
            // dd( $user_wilayah->role_id);
            $query = DB::table('rekomendasi_pengangkatan_anaks')
                ->join('users', 'users.id', '=', 'rekomendasi_pengangkatan_anaks.petugas_pengan')
                // ->join('log_pengan', 'log_pengan.id_trx_pengan', '=', 'rekomendasi_pengangkatan_anaks.id')
                // ->join('model_has_roles', 'model_has_roles.model_id', '=', 'rekomendasi_pengangkatan_anaks.tujuan_pengan')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_pengangkatan_anaks.id_kelurahan_pengan')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_pengangkatan_anaks.id_kecamatan_pengan')
                ->select('rekomendasi_pengangkatan_anaks.*', 'b.name_village', 'd.name_districts','users.name')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_pengangkatan_anaks.id_kabkot_pengan', $user_wilayah->kota_id)
                        ->where('rekomendasi_pengangkatan_anaks.tujuan_pengan', '!=', $user_wilayah->role_id)
                        // ->where('log_pengan.created_by_pengan', '=', auth::user()->id)
                        // ->where('rekomendasi_pengangkatan_anaks.petugas_pengan','!=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_pengangkatan_anaks.status_aksi_pengan', '=', 'Teruskan')
                                ->orWhere('rekomendasi_pengangkatan_anaks.status_aksi_pengan', '=', 'kembalikan');
                        });
                });
        }
        if ($user_wilayah->name == 'SekertarisDinas') {
            // dd($user_wilayah);
            $query = DB::table('rekomendasi_pengangkatan_anaks')
                ->join('users', 'users.id', '=', 'rekomendasi_pengangkatan_anaks.petugas_pengan')
                // ->join('log_pengan', 'log_pengan.id_trx_pengan', '=', 'rekomendasi_pengangkatan_anaks.id')
                // ->join('model_has_roles', 'model_has_roles.model_id', '=', 'rekomendasi_pengangkatan_anaks.tujuan_pengan')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_pengangkatan_anaks.id_kelurahan_pengan')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_pengangkatan_anaks.id_kecamatan_pengan')
                ->select('rekomendasi_pengangkatan_anaks.*', 'b.name_village', 'd.name_districts','users.name')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_pengangkatan_anaks.id_kabkot_pengan', $user_wilayah->kota_id)
                        ->where('rekomendasi_pengangkatan_anaks.tujuan_pengan', '!=', $user_wilayah->role_id)
                        // ->where('log_pengan.created_by_pengan', '=', auth::user()->id)
                        // ->where('rekomendasi_pengangkatan_anaks.petugas_pengan','!=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_pengangkatan_anaks.status_aksi_pengan', '=', 'Teruskan')
                                ->orWhere('rekomendasi_pengangkatan_anaks.status_aksi_pengan', '=', 'kembalikan');
                        });
                });
        }
        if ($user_wilayah->name == 'KepalaDinas') {
            //  dd(auth::user()->id);
            $query = DB::table('rekomendasi_pengangkatan_anaks')
                ->join('users', 'users.id', '=', 'rekomendasi_pengangkatan_anaks.petugas_pengan')
                // ->join('log_pengan', 'log_pengan.id_trx_pengan', '=', 'rekomendasi_pengangkatan_anaks.id')
                // ->join('model_has_roles', 'model_has_roles.model_id', '=', 'rekomendasi_pengangkatan_anaks.tujuan_pengan')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_pengangkatan_anaks.id_kelurahan_pengan')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_pengangkatan_anaks.id_kecamatan_pengan')
                ->select('rekomendasi_pengangkatan_anaks.*', 'b.name_village', 'd.name_districts','users.name')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_pengangkatan_anaks.id_kabkot_pengan', $user_wilayah->kota_id)
                        ->where('rekomendasi_pengangkatan_anaks.tujuan_pengan', '!=', $user_wilayah->role_id)
                        // ->where('log_pengan.created_by_pengan', '=', auth::user()->id)
                        // ->where('rekomendasi_pengangkatan_anaks.petugas_pengan','!=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_pengangkatan_anaks.status_aksi_pengan', '=', 'Teruskan')
                                ->orWhere('rekomendasi_pengangkatan_anaks.status_aksi_pengan', '=', 'kembalikan');
                        });
                });
        }
        if ($user_wilayah->name == 'Front Office Kelurahan' || $user_wilayah->name == 'Back Ofiice kelurahan'|| $user_wilayah->name == 'fasilitator') {
            if ($request->has('search') && !empty($request->search['value'])) {
                $search = $request->search['value'];
                $query = DB::table('rekomendasi_pengangkatan_anaks')
                ->join('users', 'users.id', '=', 'rekomendasi_pengangkatan_anaks.petugas_pengan')
                // ->join('log_pengan', 'log_pengan.id_trx_pengan', '=', 'rekomendasi_pengangkatan_anaks.id')
                // ->join('model_has_roles', 'model_has_roles.model_id', '=', 'rekomendasi_pengangkatan_anaks.tujuan_pengan')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_pengangkatan_anaks.id_kelurahan_pengan')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_pengangkatan_anaks.id_kecamatan_pengan')
                ->select('rekomendasi_pengangkatan_anaks.*', 'b.name_village', 'd.name_districts','users.name')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_pengangkatan_anaks.id_kabkot_pengan', $user_wilayah->kota_id)
                        ->where('rekomendasi_pengangkatan_anaks.tujuan_pengan', '=', $user_wilayah->role_id)
                        // ->where('log_pengan.created_by_pengan', '=', auth::user()->id)
                        // ->where('rekomendasi_pengangkatan_anaks.petugas_pengan','!=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_pengangkatan_anaks.status_aksi_pengan', '=', 'Teruskan')
                                ->orWhere('rekomendasi_pengangkatan_anaks.status_aksi_pengan', '=', 'kembalikan');
                        });
                    })->where(function ($query) use ($search) {
                        $query->where('rekomendasi_pengangkatan_anaks.no_pendaftaran_pengan', 'like', "%$search%");
                            // ->orwhere('indonesia_villages.name_village', 'like', "%$search%")
                            // ->orwhere('d.name_districts', 'like', "%$search%")
                            // ->orwhere('indonesia_villages.name_village', 'like', "%$search%")
                            // ->orwhere('rekomendasi_biaya_perawatans.alamat_biper', 'like', "%$search%");
                    });
            }
        }elseif($user_wilayah->name == 'Front Office Kota' || $user_wilayah->name == 'Back Ofiice Kota'|| $user_wilayah->name == 'KepalaDinas'||$user_wilayah->name == 'SekertarisDinas'||$user_wilayah->name == 'Supervisor') {
            if ($request->has('search') && !empty($request->search['value'])) {
                $search = $request->search['value'];
                $query = DB::table('rekomendasi_pengangkatan_anaks')
                ->join('users', 'users.id', '=', 'rekomendasi_pengangkatan_anaks.petugas_pengan')
                // ->join('log_pengan', 'log_pengan.id_trx_pengan', '=', 'rekomendasi_pengangkatan_anaks.id')
                // ->join('model_has_roles', 'model_has_roles.model_id', '=', 'rekomendasi_pengangkatan_anaks.tujuan_pengan')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_pengangkatan_anaks.id_kelurahan_pengan')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_pengangkatan_anaks.id_kecamatan_pengan')
                ->select('rekomendasi_pengangkatan_anaks.*', 'b.name_village', 'd.name_districts','users.name')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_pengangkatan_anaks.id_kabkot_pengan', $user_wilayah->kota_id)
                        ->where('rekomendasi_pengangkatan_anaks.tujuan_pengan', '=', $user_wilayah->role_id)
                        // ->where('log_pengan.created_by_pengan', '=', auth::user()->id)
                        // ->where('rekomendasi_pengangkatan_anaks.petugas_pengan','!=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_pengangkatan_anaks.status_aksi_pengan', '=', 'Teruskan')
                                ->orWhere('rekomendasi_pengangkatan_anaks.status_aksi_pengan', '=', 'kembalikan');
                        });
                    })->where(function ($query) use ($search) {
                        $query->where('rekomendasi_pengangkatan_anaks.no_pendaftaran_pengan', 'like', "%$search%");
                            // ->orwhere('indonesia_villages.name_village', 'like', "%$search%")
                            // ->orwhere('d.name_districts', 'like', "%$search%")
                            // ->orwhere('indonesia_villages.name_village', 'like', "%$search%")
                            // ->orwhere('rekomendasi_biaya_perawatans.alamat_biper', 'like', "%$search%");
                    });
            }
        }
        $total_filtered_items = $query->count();
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
        $gabunganData->dataRekomPengan=$tmp;
           array_push($result, $gabunganData);
        }

        return response()->json([
            'draw' => $request->input('draw'),
            'recordsTotal' => rekomendasi_pengangkatan_anak::count(),
            'recordsFiltered' => $total_filtered_items,
            'data' => $result,
        ]);
    }

    public function selesai(Request $request)
    {
        $user_name = Auth::user()->name;
        $query = DB::table('rekomendasi_pengangkatan_anaks')
            ->join('users', 'users.id', 'rekomendasi_pengangkatan_anaks.petugas_pengan')
            ->join('roles', 'roles.id', '=', 'rekomendasi_pengangkatan_anaks.tujuan_pengan')
            ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_pengangkatan_anaks.id_kelurahan_pengan')
            ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_pengangkatan_anaks.id_kecamatan_pengan')
            ->select('rekomendasi_pengangkatan_anaks.*', 'roles.name', 'users.name', 'b.name_village', 'd.name_districts');
        // dd($query);
        $user_id = Auth::user()->id;
        $user_wilayah = DB::table('wilayahs')
        ->join('model_has_roles', 'model_has_roles.model_id', '=', 'wilayahs.createdby')
        ->join('users', 'users.id', '=', 'model_has_roles.model_id')
        ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
        ->select('wilayahs.*','roles.name','model_has_roles.*')
        ->where('wilayahs.createdby', $user_id)
        ->where(function ($query) {
            $query->where('status_wilayah', 1);
        })
        ->first();
        // dd($user_wilayah);
        // Add where conditions based on user's wilayah data
        if ($user_wilayah->name == 'fasilitator') {
            $query = DB::table('rekomendasi_pengangkatan_anaks')
                ->join('users', 'users.id', 'rekomendasi_pengangkatan_anaks.petugas_pengan')
                ->join('indonesia_villages', 'indonesia_villages.code', '=', 'rekomendasi_pengangkatan_anaks.id_kelurahan_pengan')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_pengangkatan_anaks.id_kecamatan_pengan')
                ->select('rekomendasi_pengangkatan_anaks.*', 'users.name', 'd.name_districts', 'indonesia_villages.name_village', )
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_pengangkatan_anaks.id_kelurahan_pengan', $user_wilayah->kelurahan_id)
                        // ->where('log_pengan.tujuan_pengan','=', $user_wilayah->role_id);
                        // ->where('log_pengan.created_by_pengan','!=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_pengangkatan_anaks.status_aksi_pengan', '=', 'Tolak')
                                ->orWhere('rekomendasi_pengangkatan_anaks.status_aksi_pengan', '=', 'Selesai');
                        });
                });
        } elseif ($user_wilayah->name == 'Front Office Kelurahan') {
            //  dd($user_wilayah->role_id);
            $query = DB::table('rekomendasi_pengangkatan_anaks')
                ->join('users', 'users.id', 'rekomendasi_pengangkatan_anaks.petugas_pengan')
                ->join('indonesia_villages', 'indonesia_villages.code', '=', 'rekomendasi_pengangkatan_anaks.id_kelurahan_pengan')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_pengangkatan_anaks.id_kecamatan_pengan')
                ->select('rekomendasi_pengangkatan_anaks.*', 'users.name', 'd.name_districts', 'indonesia_villages.name_village', )
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_pengangkatan_anaks.id_kelurahan_pengan', $user_wilayah->kelurahan_id)
                        // ->where('log_pengan.tujuan_pengan','=', $user_wilayah->role_id);
                        // ->where('log_pengan.created_by_pengan','!=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_pengangkatan_anaks.status_aksi_pengan', '=', 'Tolak')
                                ->orWhere('rekomendasi_pengangkatan_anaks.status_aksi_pengan', '=', 'Selesai');
                        });
                });
        } elseif ($user_wilayah->name == 'Front Office kota') {
            //  dd($user_wilayah->role_id);
            $query = DB::table('rekomendasi_pengangkatan_anaks')
                ->join('users', 'users.id', 'rekomendasi_pengangkatan_anaks.petugas_pengan')
                ->join('indonesia_villages', 'indonesia_villages.code', '=', 'rekomendasi_pengangkatan_anaks.id_kelurahan_pengan')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_pengangkatan_anaks.id_kecamatan_pengan')
                ->select('rekomendasi_pengangkatan_anaks.*', 'users.name', 'd.name_districts', 'indonesia_villages.name_village', )
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_pengangkatan_anaks.id_kabkot_pengan', $user_wilayah->kota_id)
                        // ->where('log_pengan.tujuan_pengan','=', $user_wilayah->role_id)
                        // ->where('log_pengan.created_by_pengan','!=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_pengangkatan_anaks.status_aksi_pengan', '=', 'Tolak')
                                ->orWhere('rekomendasi_pengangkatan_anaks.status_aksi_pengan', '=', 'Selesai');
                        });
                });
        } elseif ($user_wilayah->name == 'Back Ofiice kelurahan') {
            // dd($user_wilayah);
            $query = DB::table('rekomendasi_pengangkatan_anaks')
                ->join('users', 'users.id', 'rekomendasi_pengangkatan_anaks.petugas_pengan')
                ->join('indonesia_villages', 'indonesia_villages.code', '=', 'rekomendasi_pengangkatan_anaks.id_kelurahan_pengan')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_pengangkatan_anaks.id_kecamatan_pengan')
                ->select('rekomendasi_pengangkatan_anaks.*', 'users.name', 'd.name_districts', 'indonesia_villages.name_village', )
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_pengangkatan_anaks.id_kabkot_pengan', $user_wilayah->kota_id)
                        // ->where('log_pengan.tujuan_pengan','=', $user_wilayah->role_id)
                        // ->where('log_pengan.created_by_pengan','!=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_pengangkatan_anaks.status_aksi_pengan', '=', 'Tolak')
                                ->orWhere('rekomendasi_pengangkatan_anaks.status_aksi_pengan', '=', 'Selesai');
                        });
                });
            // dd($query); 
        } elseif ($user_wilayah->name == 'Back Ofiice Kota') {
            // dd($user_wilayah->role_id);
            $query = DB::table('rekomendasi_pengangkatan_anaks')
                ->join('users', 'users.id', 'rekomendasi_pengangkatan_anaks.petugas_pengan')
                ->join('indonesia_villages', 'indonesia_villages.code', '=', 'rekomendasi_pengangkatan_anaks.id_kelurahan_pengan')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_pengangkatan_anaks.id_kecamatan_pengan')
                ->select('rekomendasi_pengangkatan_anaks.*', 'users.name', 'd.name_districts', 'indonesia_villages.name_village', )
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_pengangkatan_anaks.id_kabkot_pengan', $user_wilayah->kota_id)
                        // ->where('log_pengan.tujuan_pengan','=', $user_wilayah->role_id)
                        // ->where('log_pengan.created_by_pengan','!=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_pengangkatan_anaks.status_aksi_pengan', '=', 'Tolak')
                                ->orWhere('rekomendasi_pengangkatan_anaks.status_aksi_pengan', '=', 'Selesai');
                        });
                });
        }elseif ($user_wilayah->name == 'supervisor') {
            // dd($user_wilayah);
            $query = DB::table('rekomendasi_pengangkatan_anaks')
                ->join('users', 'users.id', 'rekomendasi_pengangkatan_anaks.petugas_pengan')
                ->join('indonesia_villages', 'indonesia_villages.code', '=', 'rekomendasi_pengangkatan_anaks.id_kelurahan_pengan')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_pengangkatan_anaks.id_kecamatan_pengan')
                ->select('rekomendasi_pengangkatan_anaks.*', 'users.name', 'd.name_districts', 'indonesia_villages.name_village', )
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_pengangkatan_anaks.id_kabkot_pengan', $user_wilayah->kota_id)
                        // ->where('log_pengan.tujuan_pengan','=', $user_wilayah->role_id)
                        // ->where('log_pengan.created_by_pengan','!=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_pengangkatan_anaks.status_aksi_pengan', '=', 'Tolak')
                                ->orWhere('rekomendasi_pengangkatan_anaks.status_aksi_pengan', '=', 'Selesai');
                        });
                });
        } elseif ($user_wilayah->name == 'kepala bidang') {
            // dd($user_wilayah);
            $query = DB::table('rekomendasi_pengangkatan_anaks')
                ->join('users', 'users.id', 'rekomendasi_pengangkatan_anaks.petugas_pengan')
                ->join('indonesia_villages', 'indonesia_villages.code', '=', 'rekomendasi_pengangkatan_anaks.id_kelurahan_pengan')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_pengangkatan_anaks.id_kecamatan_pengan')
                ->select('rekomendasi_pengangkatan_anaks.*', 'users.name', 'd.name_districts', 'indonesia_villages.name_village', )
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_pengangkatan_anaks.id_kabkot_pengan', $user_wilayah->kota_id)
                        // ->where('log_pengan.tujuan_pengan','=', $user_wilayah->role_id)
                        // ->where('log_pengan.created_by_pengan','!=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_pengangkatan_anaks.status_aksi_pengan', '=', 'Tolak')
                                ->orWhere('rekomendasi_pengangkatan_anaks.status_aksi_pengan', '=', 'Selesai');
                        });
                });
        }elseif ($user_wilayah->name == 'SekertarisDinas') {
            // dd($user_wilayah);
            $query = DB::table('rekomendasi_pengangkatan_anaks')
                ->join('users', 'users.id', 'rekomendasi_pengangkatan_anaks.petugas_pengan')
                ->join('indonesia_villages', 'indonesia_villages.code', '=', 'rekomendasi_pengangkatan_anaks.id_kelurahan_pengan')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_pengangkatan_anaks.id_kecamatan_pengan')
                ->select('rekomendasi_pengangkatan_anaks.*', 'users.name', 'd.name_districts', 'indonesia_villages.name_village', )
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_pengangkatan_anaks.id_kabkot_pengan', $user_wilayah->kota_id)
                        // ->where('log_pengan.tujuan_pengan','=', $user_wilayah->role_id)
                        // ->where('log_pengan.created_by_pengan','!=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_pengangkatan_anaks.status_aksi_pengan', '=', 'Tolak')
                                ->Where('rekomendasi_pengangkatan_anaks.status_aksi_pengan', '=', 'Selesai');
                        });
                });
        } elseif ($user_wilayah->name == 'KepalaDinas') {
            // dd($user_wilayah);
            $query = DB::table('rekomendasi_pengangkatan_anaks')
                ->join('users', 'users.id', 'rekomendasi_pengangkatan_anaks.petugas_pengan')

                ->join('indonesia_villages', 'indonesia_villages.code', '=', 'rekomendasi_pengangkatan_anaks.id_kelurahan_pengan')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_pengangkatan_anaks.id_kecamatan_pengan')
                ->select('rekomendasi_pengangkatan_anaks.*', 'users.name', 'd.name_districts', 'indonesia_villages.name_village')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_pengangkatan_anaks.id_kabkot_pengan', $user_wilayah->kota_id)
                        ->Where('rekomendasi_pengangkatan_anaks.status_aksi_pengan', '=', 'Selesai');
                })
                ->orderBy('rekomendasi_pengangkatan_anaks.created_at', 'desc')
                ->limit(1)
                ->distinct();
        } 
        if ($user_wilayah->name == 'Front Office Kelurahan' || $user_wilayah->name == 'Back Ofiice kelurahan'|| $user_wilayah->name == 'fasilitator') {
            if ($request->has('search') && !empty($request->search['value'])) {
                $search = $request->search['value'];
                $query = DB::table('rekomendasi_pengangkatan_anaks')
                ->join('users', 'users.id', 'rekomendasi_pengangkatan_anaks.petugas_pengan')
                ->join('indonesia_villages', 'indonesia_villages.code', '=', 'rekomendasi_pengangkatan_anaks.id_kelurahan_pengan')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_pengangkatan_anaks.id_kecamatan_pengan')
                ->select('rekomendasi_pengangkatan_anaks.*', 'users.name', 'd.name_districts', 'indonesia_villages.name_village', )
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_pengangkatan_anaks.id_kelurahan_pengan', $user_wilayah->kelurahan_id)
                        // ->where('log_pengan.tujuan_pengan','=', $user_wilayah->role_id)
                        // ->where('log_pengan.created_by_pengan','!=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_pengangkatan_anaks.status_aksi_pengan', '=', 'Tolak')
                                ->orWhere('rekomendasi_pengangkatan_anaks.status_aksi_pengan', '=', 'Selesai');
                        });
                    })->where(function ($query) use ($search) {
                        $query->where('rekomendasi_pengangkatan_anaks.no_pendaftaran_pengan', 'like', "%$search%");
                            // ->orwhere('indonesia_villages.name_village', 'like', "%$search%")
                            // ->orwhere('d.name_districts', 'like', "%$search%")
                            // ->orwhere('indonesia_villages.name_village', 'like', "%$search%")
                            // ->orwhere('rekomendasi_biaya_perawatans.alamat_biper', 'like', "%$search%");
                    });
            }
        }elseif($user_wilayah->name == 'Front Office Kota' || $user_wilayah->name == 'Back Ofiice Kota'|| $user_wilayah->name == 'KepalaDinas'||$user_wilayah->name == 'SekertarisDinas'||$user_wilayah->name == 'Supervisor') {
            if ($request->has('search') && !empty($request->search['value'])) {
                $search = $request->search['value'];
                $query = DB::table('rekomendasi_pengangkatan_anaks')
                ->join('users', 'users.id', 'rekomendasi_pengangkatan_anaks.petugas_pengan')
                ->join('indonesia_villages', 'indonesia_villages.code', '=', 'rekomendasi_pengangkatan_anaks.id_kelurahan_pengan')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_pengangkatan_anaks.id_kecamatan_pengan')
                ->select('rekomendasi_pengangkatan_anaks.*', 'users.name', 'd.name_districts', 'indonesia_villages.name_village', )
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_pengangkatan_anaks.id_kabkot_pengan', $user_wilayah->kota_id)
                        // ->where('log_pengan.tujuan_pengan','=', $user_wilayah->role_id)
                        // ->where('log_pengan.created_by_pengan','!=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_pengangkatan_anaks.status_aksi_pengan', '=', 'Tolak')
                                ->orWhere('rekomendasi_pengangkatan_anaks.status_aksi_pengan', '=', 'Selesai');
                        });
                    })->where(function ($query) use ($search) {
                        $query->where('rekomendasi_pengangkatan_anaks.no_pendaftaran_pengan', 'like', "%$search%");
                            // ->orwhere('indonesia_villages.name_village', 'like', "%$search%")
                            // ->orwhere('d.name_districts', 'like', "%$search%")
                            // ->orwhere('indonesia_villages.name_village', 'like', "%$search%")
                            // ->orwhere('rekomendasi_biaya_perawatans.alamat_biper', 'like', "%$search%");
                    });
            }
        }

        // Get total count of filtered items
        $total_filtered_items = $query->count();
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
        $gabunganData->dataRekomPengan=$tmp;
           array_push($result, $gabunganData);
        }
        // dd($data);
        // mengubah data JSON menjadi objek PHP

        return response()->json([
            'draw' => $request->input('draw'),
            'recordsTotal' => rekomendasi_pengangkatan_anak::count(),
            'recordsFiltered' => $total_filtered_items,
            'data' => $result,
        ]);
    }
    public function FileRekomPengangkatanAnak($id)
    {
        $queryRekomPengakatanAnak = rekomendasi_pengangkatan_anak::find($id);
       //  dd($queryRekomPengakatanAnak);
        $pdf = PDF::loadHtml(view('rekomendasi_pengangkatan_anaks.file_permohonan',compact('queryRekomPengakatanAnak')));
        $filename = 'File Permohonan' . $queryRekomPengakatanAnak->nama . '.pdf';
        return $pdf->stream($filename);
    }
}
