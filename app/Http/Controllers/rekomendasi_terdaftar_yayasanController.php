<?php

namespace App\Http\Controllers;
use App\Http\Controllers\AppBaseController;
use App\Models\logpendidikan;
use App\Models\logYayasan;
use App\Models\Prelist;
use app\Models\rekomendasi_terdaftar_yayasan;
use App\Models\Roles;
use App\Repositories\rekomendasi_terdaftar_yayasanRepository;
use Illuminate\Http\Request;
use app\Models\Pengaduan;
use app\Models\rekomendasi_bantuan_pendidikan;
use App\Models\rekomendasi_rekativasi_pbi_jk;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use stdClass;

class rekomendasi_terdaftar_yayasanController extends AppBaseController
{
    /** @var rekomendasi_terdaftar_yayasanRepository $rekomendasiTerdaftarYayasanRepository*/
    private $rekomendasiTerdaftarYayasanRepository;

    public function __construct(rekomendasi_terdaftar_yayasanRepository $rekomendasiTerdaftarYayasanRepo)
    {
        $this->rekomendasiTerdaftarYayasanRepository = $rekomendasiTerdaftarYayasanRepo;
    }

    /**
     * Display a listing of the rekomendasi_terdaftar_yayasan.
     */

     public function FileRekomYayasan($id)
     {
         $rekomendasiTerdaftaryayasan = rekomendasi_terdaftar_yayasan::find($id);
        //  dd($rekomendasiTerdaftaryayasan);

        $date = Carbon::parse($rekomendasiTerdaftaryayasan->updated_at)->locale('id');

        $date->settings(['formatFunction' => 'translatedFormat']);

        $tanggal = $date->format('j F Y ');


        // dd($tanggal);
         $pdf = PDF::loadHtml(view('rekomendasi_terdaftar_yayasans.file_permohonan',compact('rekomendasiTerdaftaryayasan','tanggal')));
         $filename = 'File Permohonan' . $rekomendasiTerdaftaryayasan->nama . '.pdf';
         return $pdf->stream($filename);
     }
    public function index(Request $request)
    {
        $rekomendasiTerdaftarYayasans = $this->rekomendasiTerdaftarYayasanRepository->paginate(10);
        // dd()
        return view('rekomendasi_terdaftar_yayasans.index')
            ->with('rekomendasiTerdaftarYayasans', $rekomendasiTerdaftarYayasans);
    }
    /**
     * Show the form for creating a new rekomendasi_terdaftar_yayasan.
     */

    public function create()
    {
        $v = rekomendasi_terdaftar_yayasan::latest()->first();
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


        $user = Auth::user();
        $roles = $user->roles()->pluck('name');

        if ($roles->contains('Front Office Kelurahan')) {
            // Jika user memiliki role 'FO-Kota', maka tampilkan alur dengan nama 'Draft' dan 'Teruskan'
            $alur = DB::table('alur')
                ->whereIn('name', ['Draft', 'Teruskan'])
                ->get();
        }else if ($roles->contains('warga')) {
            // Jika user memiliki role 'BO-Kota' atau 'SekertarisDinas', maka tampilkan alur dengan nama 'Kembalikan', 'Tolak', dan 'Teruskan'
            $alur = DB::table('alur')
                ->whereIn('name', ['Draft', 'Teruskan'])
                ->get();
        } else if ($roles->contains('Back Ofiice Kota') || $roles->contains('SekertarisDinas') || $roles->contains('kepala bidang')) {
            // Jika user memiliki role 'BO-Kota' atau 'SekertarisDinas', maka tampilkan alur dengan nama 'Kembalikan', 'Tolak', dan 'Teruskan'
            $alur = DB::table('alur')
                ->whereIn('name', ['Kembalikan', 'Tolak', 'Teruskan'])
                ->get();
        }else if ($roles->contains('Back Ofiice Kota') || $roles->contains('Front Office kota')) {
			// Jika user memiliki role 'BO-Kota' atau 'SekertarisDinas', maka tampilkan alur dengan nama 'Kembalikan', 'Tolak', dan 'Teruskan'
			$alur = DB::table('alur')
				->whereIn('name', ['Draft', 'Kembalikan', 'Tolak', 'Teruskan'])
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
            ->whereIn('name', ['Back Ofiice kelurahan', 'Back Ofiice Kota'])
            ->get();
        } else if ($roles->contains('Front Office kota')) {
            $roleid = DB::table('roles')
                ->where('name', ['Back Ofiice Kota', 'Front Ofiice Kelurahan'])
                ->get();
        }else if ($roles->contains('fasilitator')) {
            $roleid = DB::table('roles')
                ->where('name', ['Back Ofiice Kota', 'Front Ofiice Kelurahan'])
                ->get();
        }
         else if ($roles->contains('Back Ofiice Kota')) {
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
        }else if ($roles->contains('warga')) {
            $roleid = DB::table('roles')
                ->whereIn('name', ['Back Ofiice kelurahan', 'Back Ofiice Kota'])
                ->get();
        }
        $checkroles = Roles::where('name', 'Front Office kota')
            ->orWhere('name', 'SekertarisDinas')
            ->orWhere('name', 'KepalaDinas')
            ->get();
        return view('rekomendasi_terdaftar_yayasans.create', compact('wilayah', 'roleid', 'checkroles', 'alur', 'kecamatans'));
    }

    /**
     * Store a newly created rekomendasi_terdaftar_yayasan in storage.
     */
    public function store(Request $request)
    {
        // dd($request->all());
        $getdata = rekomendasi_terdaftar_yayasan::where('id', Auth::user()->id)->first();
        $data = new rekomendasi_terdaftar_yayasan();
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
            // dd($file);
            if ($request->file($file)) {
                $path = $request->file($file);
                $nama_file = 'yayasan/' . $file . '/' . $path->getClientOriginalName();
                Storage::disk('imagekit')->put($nama_file, fopen($path->getRealPath(), 'r'));
                $data[$file] = Storage::disk('imagekit')->url($nama_file);
            
            } else {
                $data[$file] = null;
            }
        }

        $data['id_alur'] = $request->get('id_alur');
        $data['no_pendaftaran'] = mt_rand(100, 1000);
        $data['id_provinsi'] = $request->get('id_provinsi');
        $data['id_kabkot'] = $request->get('id_kabkot');
        $data['id_kecamatan'] = $request->get('id_kecamatan');
        $data['id_kelurahan'] = $request->get('id_kelurahan');
        $data['nama_pel'] = $request->get('nama_pel');
        $data['nik_pel'] = $request->get('nik_pel');
        $data['status_kepengurusan'] = $request->get('status_kepengurusan');
        $data['alamat_pel'] = $request->get('alamat_pel');
        $data['telp_pel'] = $request->get('telp_pel');
        $data['nama_lembaga'] = $request->get('nama_lembaga');
        $data['alamat_lembaga'] = $request->get('alamat_lembaga');
        $data['nama_notaris'] = $request->get('nama_notaris');
        $data['notgl_akta'] = $request->get('notgl_akta');
        $data['nama_ketua'] = $request->get('nama_ketua');
        $data['status'] = $request->get('status');
        $data['tipe'] = $request->get('tipe');
        $data['no_ahu'] = $request->get('no_ahu');
        $data['tgl_mulai'] = $request->get('tgl_mulai');
        $data['tgl_selesai'] = $request->get('tgl_selesai');
        // $data['draft_rekomendasi'] = $request->get('draft_rekomendasi');
        $data['Lingkup_Wilayah_Kerja'] = $request->get('Lingkup_Wilayah_Kerja');
        $data['catatan'] = $request->get('catatan');
        $data['status_alur'] = $request->get('status_alur');
        $data['tujuan'] = $request->get('tujuan');
        $data['petugas'] = $request->get('petugas');
        $data['createdby'] = Auth::user()->id;
        $data['updatedby'] = Auth::user()->id;
        //  dd($data);
        $data->save();


        $logpengaduan = new logYayasan();
        $logpengaduan['id_trx_yayasan'] = $data->id;
        $logpengaduan['id_alur'] = $request->get('status_alur');
        $logpengaduan['petugas'] = $request->get('petugas');
        $logpengaduan['catatan']  = $request->get('tl_catatan');
        $logpengaduan['file_permohonan'] = $request->get('file_permohonan');
        $logpengaduan['draft_rekomendasi'] = $request->get('draft_rekomendasi');
        $logpengaduan['tujuan'] = $request->get('tujuan');
        $logpengaduan['created_by'] = Auth::user()->id;
        $logpengaduan['updatedby'] = Auth::user()->id;
        if ($data['status_alur'] !== 'Draft') {
            $logpengaduan = new logYayasan();
            $logpengaduan['id_trx_yayasan'] = $data->id;
            $logpengaduan['id_alur'] = $request->get('status_alur');
            $logpengaduan['petugas'] = $request->get('petugas');
            $logpengaduan['catatan']  = $request->get('tl_catatan');
            $logpengaduan['file_permohonan'] = $request->get('file_permohonan');
            $logpengaduan['draft_rekomendasi'] = $request->get('draft_rekomendasi');
            $logpengaduan['tujuan'] = $request->get('tujuan');
            $logpengaduan['created_by'] = Auth::user()->id;
            $logpengaduan['updated_by'] = Auth::user()->id;

            $logpengaduan->save();  
        } else {
            return redirect('rekomendasi_terdaftar_yayasans')->withSuccess('Data Disimpan Kedalam Draft');
        }
        // dd($logpengaduan);

        return redirect('rekomendasi_terdaftar_yayasans')->withSuccess('Data Berhasil Disimpan');
    }

    /**
     * Display the specified rekomendasi_terdaftar_yayasan.
     */
    public function show($id)
    {
        $userid = Auth::user()->id;
        $rekomendasiTerdaftarYayasan = DB::table('rekomendasi_terdaftar_yayasans as w')->select(
            'w.*',
            'b.name_village',
            'prov.name_prov',
            'kota.name_cities',
            'kecamatan.name_districts',
            'roles.name as name_roles',
            'users.name',
            // 'w.status_wilayah',
        )
        ->leftjoin('users', 'users.id', '=', 'w.petugas')
        ->leftjoin('roles', 'roles.id', '=', 'w.tujuan')
        ->leftjoin('indonesia_provinces as prov', 'prov.code', '=', 'w.id_provinsi')
        ->leftjoin('indonesia_cities as kota', 'kota.code', '=', 'w.id_kabkot')
        ->leftjoin('indonesia_districts as kecamatan', 'kecamatan.code', '=', 'w.id_kecamatan')
        ->leftjoin('indonesia_villages as b', 'b.code', '=', 'w.id_kelurahan')
        ->where('w.id', $id)->first();

        $logyayasan = DB::table('log_yayasan as w')->select(
            'w.*',
            'roles.name',
            'users.name',
            // 'alur.name'

        )
        ->leftjoin('users', 'users.id', '=', 'w.petugas')
        ->leftjoin('roles', 'roles.id', '=', 'w.tujuan')
        ->where('w.id_trx_yayasan', $id)->get();

        return view('rekomendasi_terdaftar_yayasans.show', compact('rekomendasiTerdaftarYayasan','logyayasan'));
    }

    /**
     * Show the form for editing the specified rekomendasi_terdaftar_yayasan.
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
        $created_by = DB::table('rekomendasi_terdaftar_yayasans')
            ->join('users', 'rekomendasi_terdaftar_yayasans.createdby', '=', 'users.name')
            ->join('model_has_roles', 'model_has_roles.model_id', '=', 'users.id')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->select('rekomendasi_terdaftar_yayasans.id', 'rekomendasi_terdaftar_yayasans.createdby', 'roles.name')
            ->get();

        $rekomendasiTerdaftarYayasan=DB::table('rekomendasi_terdaftar_yayasans as w')->select(
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
            ->leftjoin('rekomendasi_terdaftar_yayasans as b', 'b.tujuan', '=', 'model_has_roles.role_id')
            ->where('b.id', $id)
            ->get();
        //alur
        $user = Auth::user();
        $roles = $user->roles()->pluck('name');

        if ($roles->contains('Front Office kelurahan')) {
            // Jika user memiliki role 'FO-Kota', maka tampilkan alur dengan nama 'Draft' dan 'Teruskan'
            $alur = DB::table('alur')
                ->whereIn('name', ['Draft', 'Teruskan'])
                ->get();
        } else if ($roles->contains('Back Ofiice Kota') || $roles->contains('Front Office kota')) {
			// Jika user memiliki role 'BO-Kota' atau 'SekertarisDinas', maka tampilkan alur dengan nama 'Kembalikan', 'Tolak', dan 'Teruskan'
			$alur = DB::table('alur')
				->whereIn('name', ['Draft', 'Kembalikan', 'Tolak', 'Teruskan'])
				->get();
		}else if ($roles->contains('SekertarisDinas') || $roles->contains('kepala bidang')) {
            // Jika user memiliki role 'BO-Kota' atau 'SekertarisDinas', maka tampilkan alur dengan nama 'Kembalikan', 'Tolak', dan 'Teruskan'
            $alur = DB::table('alur')
                ->whereIn('name', ['Kembalikan', 'Tolak', 'Teruskan'])
                ->get();
        } else if ($roles->contains('KepalaDinas')) {
            // Jika user memiliki role 'Kadus', maka tampilkan alur dengan nama 'Selesai' dan 'Tolak'
            $alur = DB::table('alur')
                ->whereIn('name', ['Selesai', 'Tolak'])
                ->get();
        } else if ($roles->contains('Back Ofiice kelurahan')) {
            // Jika user memiliki role 'Kadus', maka tampilkan alur dengan nama 'Selesai' dan 'Tolak'
            $alur = DB::table('alur')
                ->whereIn('name', ['Teruskan', 'Tolak','Selesai'])
                ->get();
        } else {
            // Jika user tidak memiliki role yang sesuai, maka tampilkan alur kosong
            $alur = collect();
        }


        $user = Auth::user();
        $roles = $user->roles()->pluck('name');

        if ($roles->contains('Front Office Kelurahan')) {
            // $roleid = DB::table('roles')
            $roleid = DB::table('roles')
            ->whereIn('name', ['Back Ofiice kelurahan', 'Back Ofiice Kota'])
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
            ->whereIn('name', ['Back Ofiice Kota', 'SekertarisDinas'])
                ->get();
            // dd($roleid);
        }else if ($roles->contains('Back Ofiice kelurahan')) {
            $roleid = DB::table('roles')
            ->whereIn('name', ['Back Ofiice Kota', 'SekertarisDinas'])
                ->get();
            // dd($roleid);
        }

        $role_id = null;
        $users = DB::table('users as u')
            ->join('model_has_roles as mhr', 'u.id', '=', 'mhr.model_id')
            ->join('roles as r', 'mhr.role_id', '=', 'r.id')
            ->select('u.id', 'u.name', 'u.email', 'r.name as role')
            ->where('mhr.model_type', '=', 'App\Models\User')
            ->where('mhr.role_id', '=', $role_id)
            ->get();

        // $rekomendasiTerdaftarYayasan = $this->rekomendasiTerdaftarYayasanRepository->find($id);


        return view('rekomendasi_terdaftar_yayasans.edit', compact('wilayah','getAuth','rekomendasiTerdaftarYayasan', 'roleid', 'getdata', 'alur', 'created_by', 'getUsers', 'getAuth'));
    }
    public function getPetugas($id)
    {
        $userid = auth::user()->id;
        // dd($userid);
        $wilayah = DB::table('wilayahs as w')->select(
            'w.id',
            'w.createdby as iduser',
            'b.*',
            'w.*',
            'prov.*',
            'kota.*',
            'kecamatan.*',
            'w.status_wilayah',
            'w.createdby',
        )->leftjoin('indonesia_provinces as prov', 'prov.code', '=', 'w.province_id')
        ->leftjoin('indonesia_cities as kota', 'kota.code', '=', 'w.kota_id')
        ->leftjoin('indonesia_districts as kecamatan', 'kecamatan.code', '=', 'w.kecamatan_id')
        ->leftjoin('indonesia_villages as b', 'b.code', '=', 'w.kelurahan_id')
        ->where('status_wilayah', '1')
        ->where('w.createdby', $userid)->first();
      

        $user_wilayah = DB::table('wilayahs')
                        ->join('model_has_roles', 'model_has_roles.model_id', '=', 'wilayahs.createdby')
                        ->join('users', 'users.id', '=', 'model_has_roles.model_id')
                        ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
                        ->select('wilayahs.*','roles.name','model_has_roles.*')
                        ->where('wilayahs.createdby', $userid)
                        ->where(function ($query) {
                            $query->where('status_wilayah', 1);
                        })
                        ->first();
                    // dd($query);
        if ($user_wilayah->name == 'Front Office Kelurahan' || $user_wilayah == 'fasilitator'|| $user_wilayah == 'warga'){
            $users = DB::table('users as u')
                ->leftJoin('model_has_roles as mhr', 'u.id', '=', 'mhr.model_id')
                ->leftJoin('roles as r', 'mhr.role_id', '=', 'r.id')
                ->leftJoin('wilayahs', 'wilayahs.createdby', '=', 'u.id')
                ->select('u.id', 'u.name', 'u.email', 'r.name as role')
                ->where('mhr.model_type', '=', 'App\Models\User')
                ->where('wilayahs.kota_id', '=',$wilayah->kota_id)
                ->where('mhr.role_id', '=', $id)
                ->get();
            return response()->json($users);
        }elseif ($user_wilayah->name == 'Back Ofiice kelurahan') {

            $users = DB::table('users as u')
            ->leftJoin('model_has_roles as mhr', 'u.id', '=', 'mhr.model_id')
            ->leftJoin('roles as r', 'mhr.role_id', '=', 'r.id')
            ->leftJoin('wilayahs', 'wilayahs.createdby', '=', 'u.id')
            ->select('u.id', 'u.name', 'u.email', 'r.name as role')
            ->where('mhr.model_type', '=', 'App\Models\User')
            ->where('wilayahs.kecamatan_id', '=',$wilayah->kecamatan_id)
            ->where('mhr.role_id', '=', $id)
            ->get(); 
            // dd($users);        
            return response()->json($users);
        }elseif($user_wilayah->name == 'Front Office Kota' || $user_wilayah->name == 'Back Ofiice Kota') {
           
            $users = DB::table('users as u')
                    ->leftJoin('model_has_roles as mhr', 'u.id', '=', 'mhr.model_id')
                    ->leftJoin('roles as r', 'mhr.role_id', '=', 'r.id')
                    ->leftJoin('wilayahs', 'wilayahs.createdby', '=', 'u.id')
                    ->leftJoin('rekomendasi_biaya_perawatans','rekomendasi_biaya_perawatans.createdby_biper','=','u.id')
                    ->where('mhr.model_type', '=', 'App\Models\User')
                    ->where('wilayahs.kota_id', '=',$wilayah->kota_id)
                    ->where('mhr.role_id', '=', $id)
                    // ->whereRaw("(SELECT COUNT(l.id) FROM users as l WHERE l.id = '".$id."') > 0 ")
                    ->whereRaw("(SELECT COUNT(l.id) FROM users as l WHERE l.id = rekomendasi_biaya_perawatans.id AND l.id = '".$id."') > 0 ")
                    ->select('u.id', 'u.name', 'u.email', 'r.name as role')
                    ->get();
            // dd($users); 
            return response()->json($users);
          
            if ($users->empty()) {
                $users = DB::table('users as u')
                ->leftJoin('model_has_roles as mhr', 'u.id', '=', 'mhr.model_id')
                ->leftJoin('roles as r', 'mhr.role_id', '=', 'r.id')
                ->leftJoin('wilayahs', 'wilayahs.createdby', '=', 'u.id')
                ->select('u.id', 'u.name', 'u.email', 'r.name as role')
                ->where('mhr.model_type', '=', 'App\Models\User')
                // ->where('wilayahs.kelurahan_id', '=',$wilayah->kelurahan_id)
                ->where('mhr.role_id', '=', $id)
                ->get(); 
             
                return response()->json($users);
            }else{}
     

        }elseif($user_wilayah ='kepala bidang' || $user_wilayah->name == 'KepalaDinas'||$user_wilayah->name == 'SekertarisDinas') {
            $users = DB::table('users as u')
            ->leftJoin('model_has_roles as mhr', 'u.id', '=', 'mhr.model_id')
            ->leftJoin('roles as r', 'mhr.role_id', '=', 'r.id')
            ->leftJoin('wilayahs', 'wilayahs.createdby', '=', 'u.id')
            ->select('u.id', 'u.name', 'u.email', 'r.name as role')
            ->where('mhr.model_type', '=', 'App\Models\User')
            // ->where('wilayahs.kelurahan_id', '=',$wilayah->kelurahan_id)
            ->where('mhr.role_id', '=', $id)
            ->get(); 
         
            return response()->json($users);
          
            if ($users->empty()) {
                $users = DB::table('users as u')
                ->leftJoin('model_has_roles as mhr', 'u.id', '=', 'mhr.model_id')
                ->leftJoin('roles as r', 'mhr.role_id', '=', 'r.id')
                ->leftJoin('wilayahs', 'wilayahs.createdby', '=', 'u.id')
                ->select('u.id', 'u.name', 'u.email', 'r.name as role')
                ->where('mhr.model_type', '=', 'App\Models\User')
                ->where('wilayahs.kelurahan_id', '=',$wilayah->kelurahan_id)
                ->where('mhr.role_id', '=', $id)
                ->get(); 
             
                return response()->json($users);
            }else{}
     

        }else{
            $users = DB::table('users as u')
                ->leftJoin('model_has_roles as mhr', 'u.id', '=', 'mhr.model_id')
                ->leftJoin('roles as r', 'mhr.role_id', '=', 'r.id')
                ->leftJoin('wilayahs', 'wilayahs.createdby', '=', 'u.id')
                ->select('u.id', 'u.name', 'u.email', 'r.name as role')
                ->where('mhr.model_type', '=', 'App\Models\User') 
                ->where('wilayahs.kota_id', '=',$wilayah->kota_id)
                ->where('mhr.role_id', '=', $id)
                ->get();
        }
        return response()->json($users);
    }
    /**
     * Update the specified rekomendasi_terdaftar_yayasan in storage.
     */
    public function update($id, Request $request)
    {
        $getdata = rekomendasi_terdaftar_yayasan::where('id', $id)->first();
        $data = $request->all();
        // dd($data);
        //    dd($request->all());
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

        $getdata->update($data);

        $logyayasan = new logYayasan();
        $logyayasan['id_trx_yayasan'] = $getdata->id;
        $logyayasan['id_alur'] = $request->get('status_alur');
        $logyayasan['validasi_surat'] = $request->get('validasi_surat');
        $logyayasan['petugas'] = $request->get('petugas');
        $logyayasan['catatan']  = $request->get('tl_catatan');
        $logyayasan['file_permohonan'] = $request->get('file_permohonan');
        $logyayasan['tujuan'] = $request->get('tujuan');
        $logyayasan['created_by'] = Auth::user()->id;
        // dd($logyayasan);
        $logyayasan->save();
        return redirect()->route('rekomendasi_terdaftar_yayasans.index')->with('success', 'Data berhasil diupdate.');
    }

    /**
     * Remove the specified rekomendasi_terdaftar_yayasan from storage.
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

    public function getDataDraft(Request $request)
    {
        $user_name = Auth::user()->name;
        $query = DB::table('rekomendasi_terdaftar_yayasans')
            ->leftjoin('users', 'users.id', '=', 'rekomendasi_terdaftar_yayasans.petugas')
            // ->leftjoin('wilayahs', 'wilayahs.createdby', '=', 'rekomendasi_terdaftar_yayasans.createdby')
            // ->leftjoin('model_has_roles', 'model_has_roles.model_id', '=', 'rekomendasi_terdaftar_yayasans.tujuan_bantuan_pendidikans')
            ->leftjoin('indonesia_villages as b', 'b.code', '=', 'rekomendasi_terdaftar_yayasans.id_kelurahan')
            ->leftjoin('indonesia_districts as d', 'd.code', '=', 'rekomendasi_terdaftar_yayasans.id_kecamatan')
            ->select('rekomendasi_terdaftar_yayasans.*', 'b.name_village', 'd.name_districts', 'users.name')
            ->distinct();
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
        // dd($user_wilayah->kelurahan_id);
        if ($user_wilayah->name == 'Front Office kota') {
            $query->Where(function ($query) use ($user_wilayah) {
                $query->where('rekomendasi_terdaftar_yayasans.id_kabkot', $user_wilayah->kota_id);
                $query->where('rekomendasi_terdaftar_yayasans.status_alur', 'Draft');
                $query->where('rekomendasi_terdaftar_yayasans.createdby',  Auth::user()->id);
            });
        }
        if ($user_wilayah->name == 'Front Office Kelurahan') {
            $query->Where(function ($query) use ($user_wilayah) {
                $query->where('rekomendasi_terdaftar_yayasans.id_kelurahan', $user_wilayah->kelurahan_id);
                $query->where('rekomendasi_terdaftar_yayasans.status_alur', 'Draft');
                $query->where('rekomendasi_terdaftar_yayasans.createdby',  Auth::user()->id);
            });
        }
        if ($user_wilayah->name == 'fasilitator') {
            $query->Where(function ($query) use ($user_wilayah) {
                $query->where('rekomendasi_terdaftar_yayasans.id_kelurahan', $user_wilayah->kelurahan_id);
                $query->where('rekomendasi_terdaftar_yayasans.status_alur', 'Draft');
                $query->where('rekomendasi_terdaftar_yayasans.createdby',  Auth::user()->id);
            });
        } if ($user_wilayah->name == 'warga') {
            $query->Where(function ($query) use ($user_wilayah) {
                $query->where('rekomendasi_terdaftar_yayasans.id_kelurahan', $user_wilayah->kelurahan_id);
                $query->where('rekomendasi_terdaftar_yayasans.status_alur', 'Draft');
                $query->where('rekomendasi_terdaftar_yayasans.createdby',  Auth::user()->id);
            });
        }
        if ($request->has('search')) {
            // dd($query);
            $search = $request->search['value'];
            $query->where(function ($query) use ($search) {
                $query->where('rekomendasi_terdaftar_yayasans.no_pendaftaran', 'like', "%$search%");
            });
        }
        // Get total count of filtered items
        $total_filtered_items = $query->count();
        // Add ordering
        if ($user_wilayah->name == 'Front Office Kelurahan'|| $user_wilayah == 'fasilitator'|| $user_wilayah == 'warga') {
            if ($request->has('search') && !empty($request->search['value'])) {
                $search = $request->search['value'];
                $query->Where(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_terdaftar_yayasans.id_kelurahan', $user_wilayah->kelurahan_id);
                    $query->where('rekomendasi_terdaftar_yayasans.status_alur', 'Draft');
                    $query->where('rekomendasi_terdaftar_yayasans.createdby',  Auth::user()->id);
                });
            }
        }elseif($user_wilayah->name == 'Front Office Kota') {
            if ($request->has('search') && !empty($request->search['value'])) {
                $search = $request->search['value'];
                $query->Where(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_terdaftar_yayasans.id_kabkot', $user_wilayah->kota_id);
                    $query->where('rekomendasi_terdaftar_yayasans.status_alur', 'Draft');
                    $query->where('rekomendasi_terdaftar_yayasans.createdby',  Auth::user()->id);
                });
            }
        }
        // Get paginated data
       //Add paginate
        $start = $request->start;
        // dd($start);
        $length = $request->length;
        // dd($length);
        $query->offset($start)->limit($length);
        $data = $query->get();
        // mengubah data JSON menjadi objek PHP

        return response()->json([
            'draw' => $request->input('draw'),
            'recordsTotal' => rekomendasi_terdaftar_yayasan::count(),
            'recordsFiltered' => $total_filtered_items,
            'data' => $data,
        ]);
    }
    public function getDataDiProses(Request $request)
    {
        $user_id = Auth::user()->id;
        $user_wilayah = DB::table('wilayahs')
            ->join('model_has_roles', 'model_has_roles.model_id', '=', 'wilayahs.createdby')
            ->join('users', 'users.id', '=', 'model_has_roles.model_id')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->select('wilayahs.*','roles.name','model_has_roles.role_id')
            ->where('wilayahs.createdby', $user_id)
            ->where(function ($query) {
                $query->where('status_wilayah', 1);
            })
            ->first();
        if ($user_wilayah->name == 'fasilitator') {
           $query = DB::table('rekomendasi_terdaftar_yayasans')
                        ->join('users', 'users.id', '=', 'rekomendasi_terdaftar_yayasans.petugas')
                        ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_terdaftar_yayasans.id_kecamatan')
                        ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_terdaftar_yayasans.id_kelurahan')
                        ->select('rekomendasi_terdaftar_yayasans.*', 'b.name_village', 'd.name_districts', 'users.name')
                        ->orWhere(function ($query) use ($user_wilayah) {
                            $query->where('rekomendasi_terdaftar_yayasans.id_kelurahan', '=', $user_wilayah->kelurahan_id)
                                ->where('rekomendasi_terdaftar_yayasans.tujuan', '=', $user_wilayah->role_id)
                                ->where('rekomendasi_terdaftar_yayasans.petugas', '=', auth::user()->id)
            
                                ->where(function ($query) {
                                    $query->where('rekomendasi_terdaftar_yayasans.status_alur', '=', 'Teruskan')
                                        ->orWhere('rekomendasi_terdaftar_yayasans.status_alur', '=', 'kembalikan');
                                });
                        });
        } elseif ($user_wilayah->name == 'warga') {
            $query = DB::table('rekomendasi_terdaftar_yayasans')
                         ->join('users', 'users.id', '=', 'rekomendasi_terdaftar_yayasans.petugas')
                         ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_terdaftar_yayasans.id_kecamatan')
                         ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_terdaftar_yayasans.id_kelurahan')
                         ->select('rekomendasi_terdaftar_yayasans.*', 'b.name_village', 'd.name_districts', 'users.name')
                         ->orWhere(function ($query) use ($user_wilayah) {
                             $query->where('rekomendasi_terdaftar_yayasans.id_kabkot', '=', $user_wilayah->kota_id)
                                 ->where('rekomendasi_terdaftar_yayasans.tujuan', '=', $user_wilayah->role_id)
                                 ->where('rekomendasi_terdaftar_yayasans.petugas', '=', auth::user()->id)
             
                                 ->where(function ($query) {
                                     $query->where('rekomendasi_terdaftar_yayasans.status_alur', '=', 'Teruskan')
                                         ->orWhere('rekomendasi_terdaftar_yayasans.status_alur', '=', 'kembalikan');
                                 });
                         });
         }elseif ($user_wilayah->name == 'Front Office kota') {
           $query = DB::table('rekomendasi_terdaftar_yayasans')
                        ->join('users', 'users.id', '=', 'rekomendasi_terdaftar_yayasans.petugas')
                        ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_terdaftar_yayasans.id_kecamatan')
                        ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_terdaftar_yayasans.id_kelurahan')
                        ->select('rekomendasi_terdaftar_yayasans.*', 'b.name_village', 'd.name_districts', 'users.name')
                        ->orWhere(function ($query) use ($user_wilayah) {
                            $query->where('rekomendasi_terdaftar_yayasans.id_kabkot', '=', $user_wilayah->kota_id)
                                ->where('rekomendasi_terdaftar_yayasans.tujuan', '=', $user_wilayah->role_id)
                                ->where('rekomendasi_terdaftar_yayasans.petugas', '=', auth::user()->id)
            
                                ->where(function ($query) {
                                    $query->where('rekomendasi_terdaftar_yayasans.status_alur', '=', 'Teruskan')
                                        ->orWhere('rekomendasi_terdaftar_yayasans.status_alur', '=', 'kembalikan');
                                });
                        });
        } elseif ($user_wilayah->name == 'Front Office Kelurahan') {
           $query = DB::table('rekomendasi_terdaftar_yayasans')
                        ->join('users', 'users.id', '=', 'rekomendasi_terdaftar_yayasans.petugas')
                        ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_terdaftar_yayasans.id_kecamatan')
                        ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_terdaftar_yayasans.id_kelurahan')
                        ->select('rekomendasi_terdaftar_yayasans.*', 'b.name_village', 'd.name_districts', 'users.name')
                        ->orWhere(function ($query) use ($user_wilayah) {
                            $query->where('rekomendasi_terdaftar_yayasans.id_kelurahan', '=', $user_wilayah->kelurahan_id)
                                ->where('rekomendasi_terdaftar_yayasans.tujuan', '=', $user_wilayah->role_id)
                                ->where('rekomendasi_terdaftar_yayasans.petugas', '=', auth::user()->id)
            
                                ->where(function ($query) {
                                    $query->where('rekomendasi_terdaftar_yayasans.status_alur', '=', 'Teruskan')
                                        ->orWhere('rekomendasi_terdaftar_yayasans.status_alur', '=', 'kembalikan');
                                });
                        });
        } elseif ($user_wilayah->name == 'supervisor') {
           
           $query = DB::table('rekomendasi_terdaftar_yayasans')
                        ->join('users', 'users.id', '=', 'rekomendasi_terdaftar_yayasans.petugas')
                        ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_terdaftar_yayasans.id_kecamatan')
                        ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_terdaftar_yayasans.id_kelurahan')
                        ->select('rekomendasi_terdaftar_yayasans.*', 'b.name_village', 'd.name_districts', 'users.name')
                        ->orWhere(function ($query) use ($user_wilayah) {
                            $query->where('rekomendasi_terdaftar_yayasans.id_kecamatan', '=', $user_wilayah->kecamatan_id)
                                ->where('rekomendasi_terdaftar_yayasans.tujuan', '=', $user_wilayah->role_id)
                                ->where('rekomendasi_terdaftar_yayasans.petugas', '=', auth::user()->id)
                                ->where(function ($query) {
                                    $query->where('rekomendasi_terdaftar_yayasans.status_alur', '=', 'Teruskan')
                                        ->orWhere('rekomendasi_terdaftar_yayasans.status_alur', '=', 'kembalikan');
                                });
                     
                        });
        } elseif ($user_wilayah->name == 'Back Ofiice kelurahan') {
           $query = DB::table('rekomendasi_terdaftar_yayasans')
                        ->join('users', 'users.id', '=', 'rekomendasi_terdaftar_yayasans.petugas')
                        ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_terdaftar_yayasans.id_kecamatan')
                        ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_terdaftar_yayasans.id_kelurahan')
                        ->select('rekomendasi_terdaftar_yayasans.*', 'b.name_village', 'd.name_districts', 'users.name')
                        ->orWhere(function ($query) use ($user_wilayah) {
                            $query->where('rekomendasi_terdaftar_yayasans.id_kelurahan', '=', $user_wilayah->kelurahan_id)
                                ->where('rekomendasi_terdaftar_yayasans.tujuan', '=', $user_wilayah->role_id)
                                ->where('rekomendasi_terdaftar_yayasans.petugas', '=', auth::user()->id)
                                ->where(function ($query) {
                                    $query->where('rekomendasi_terdaftar_yayasans.status_alur', '=', 'Teruskan')
                                        ->orWhere('rekomendasi_terdaftar_yayasans.status_alur', '=', 'kembalikan');
                                });
                            // dd($va);
                        });
        } elseif ($user_wilayah->name == 'Back Ofiice Kota') {
           $query = DB::table('rekomendasi_terdaftar_yayasans')
                        ->join('users', 'users.id', '=', 'rekomendasi_terdaftar_yayasans.petugas')
                        ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_terdaftar_yayasans.id_kecamatan')
                        ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_terdaftar_yayasans.id_kelurahan')
                        ->select('rekomendasi_terdaftar_yayasans.*', 'b.name_village', 'd.name_districts', 'users.name')
                        ->orWhere(function ($query) use ($user_wilayah) {
                            $query->where('rekomendasi_terdaftar_yayasans.id_kabkot', '=', $user_wilayah->kota_id)
                                ->where('rekomendasi_terdaftar_yayasans.tujuan', '=', $user_wilayah->role_id)
                                ->where('rekomendasi_terdaftar_yayasans.petugas', '=', auth::user()->id)
                                ->where(function ($query) {
                                    $query->where('rekomendasi_terdaftar_yayasans.status_alur', '=', 'Teruskan')
                                        ->orWhere('rekomendasi_terdaftar_yayasans.status_alur', '=', 'kembalikan');
                                });
                            // dd($va);
                        });
        } elseif ($user_wilayah->name == 'kepala bidang') {
           $query = DB::table('rekomendasi_terdaftar_yayasans')
                        ->join('users', 'users.id', '=', 'rekomendasi_terdaftar_yayasans.petugas')
                        ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_terdaftar_yayasans.id_kecamatan')
                        ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_terdaftar_yayasans.id_kelurahan')
                        ->select('rekomendasi_terdaftar_yayasans.*', 'b.name_village', 'd.name_districts', 'users.name')
                        ->orWhere(function ($query) use ($user_wilayah) {
                            $query->where('rekomendasi_terdaftar_yayasans.id_kabkot', '=', $user_wilayah->kota_id)
                                ->where('rekomendasi_terdaftar_yayasans.tujuan', '=', $user_wilayah->role_id)
                                ->where('rekomendasi_terdaftar_yayasans.petugas', '=', auth::user()->id)
                                ->where(function ($query) {
                                    $query->where('rekomendasi_terdaftar_yayasans.status_alur', '=', 'Teruskan')
                                        ->orWhere('rekomendasi_terdaftar_yayasans.status_alur', '=', 'kembalikan');
                                });
                     
                        });
        } elseif ($user_wilayah->name == 'SekertarisDinas') {
           $query = DB::table('rekomendasi_terdaftar_yayasans')
                        ->join('users', 'users.id', '=', 'rekomendasi_terdaftar_yayasans.petugas')
                        ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_terdaftar_yayasans.id_kecamatan')
                        ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_terdaftar_yayasans.id_kelurahan')
                        ->select('rekomendasi_terdaftar_yayasans.*', 'b.name_village', 'd.name_districts', 'users.name')
                        ->orWhere(function ($query) use ($user_wilayah) {
                            $query->where('rekomendasi_terdaftar_yayasans.id_kabkot', '=', $user_wilayah->kota_id)
                                ->where('rekomendasi_terdaftar_yayasans.tujuan', '=', $user_wilayah->role_id)
                                ->where('rekomendasi_terdaftar_yayasans.petugas', '=', auth::user()->id)
                                ->where(function ($query) {
                                    $query->where('rekomendasi_terdaftar_yayasans.status_alur', '=', 'Teruskan')
                                        ->orWhere('rekomendasi_terdaftar_yayasans.status_alur', '=', 'kembalikan');
                                });
                            // dd($va);
                        });
        } elseif ($user_wilayah->name == 'KepalaDinas') {
           $query = DB::table('rekomendasi_terdaftar_yayasans')
                        ->join('users', 'users.id', '=', 'rekomendasi_terdaftar_yayasans.petugas')
                        ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_terdaftar_yayasans.id_kecamatan')
                        ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_terdaftar_yayasans.id_kelurahan')
                        ->select('rekomendasi_terdaftar_yayasans.*', 'b.name_village', 'd.name_districts', 'users.name')
                        ->orWhere(function ($query) use ($user_wilayah) {
                            $query->where('rekomendasi_terdaftar_yayasans.id_kabkot', '=', $user_wilayah->kota_id)
                                ->where('rekomendasi_terdaftar_yayasans.tujuan', '=', $user_wilayah->role_id)
                                ->where('rekomendasi_terdaftar_yayasans.petugas', '=', auth::user()->id)
                                ->where(function ($query) {
                                    $query->where('rekomendasi_terdaftar_yayasans.status_alur', '=', 'Teruskan')
                                        ->orWhere('rekomendasi_terdaftar_yayasans.status_alur', '=', 'kembalikan');
                                });
                            
                        });

        }
        if ($user_wilayah->name == 'Front Office Kelurahan' || $user_wilayah->name == 'Back Ofiice kelurahan'|| $user_wilayah->name == 'fasilitator') {
            if ($request->has('search') && !empty($request->search['value'])) {
                $search = $request->search['value'];
                $query = DB::table('rekomendasi_terdaftar_yayasans')
                    ->join('users', 'users.id', '=', 'rekomendasi_terdaftar_yayasans.petugas')
                    ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_terdaftar_yayasans.id_kecamatan')
                    ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_terdaftar_yayasans.id_kelurahan')
                    ->select('rekomendasi_terdaftar_yayasans.*', 'b.name_village', 'd.name_districts', 'users.name')
                    ->orWhere(function ($query) use ($user_wilayah, $search) {
                        $query->where('rekomendasi_terdaftar_yayasans.id_kabkot', '=', $user_wilayah->kota_id)
                            ->where('rekomendasi_terdaftar_yayasans.tujuan', '=', $user_wilayah->role_id)
                            ->where('rekomendasi_terdaftar_yayasans.petugas', '=', auth::user()->id)
        
                            ->where(function ($query) {
                                $query->where('rekomendasi_terdaftar_yayasans.status_alur', '=', 'Teruskan')
                                    ->orWhere('rekomendasi_terdaftar_yayasans.status_alur', '=', 'kembalikan');
                            })
                            ->where('rekomendasi_terdaftar_yayasans.no_pendaftaran', 'like', "%$search%");

                    });
            }
        }elseif($user_wilayah->name == 'Front Office Kota' || $user_wilayah->name == 'Back Ofiice Kota'|| $user_wilayah->name == 'KepalaDinas'||$user_wilayah->name == 'SekertarisDinas'||$user_wilayah->name == 'Supervisor') {
            if ($request->has('search') && !empty($request->search['value'])) {
                $search = $request->search['value'];
                $query = DB::table('rekomendasi_terdaftar_yayasans')
                        ->join('users', 'users.id', '=', 'rekomendasi_terdaftar_yayasans.petugas')
                        ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_terdaftar_yayasans.id_kecamatan')
                        ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_terdaftar_yayasans.id_kelurahan')
                        ->select('rekomendasi_terdaftar_yayasans.*', 'b.name_village', 'd.name_districts', 'users.name')
                        ->orWhere(function ($query) use ($user_wilayah, $search) {
                            $query->where('rekomendasi_terdaftar_yayasans.id_kabkot', '=', $user_wilayah->kota_id)
                                ->where('rekomendasi_terdaftar_yayasans.tujuan', '=', $user_wilayah->role_id)
                                ->where('rekomendasi_terdaftar_yayasans.petugas', '=', auth::user()->id)
                                ->where(function ($query) {
                                    $query->where('rekomendasi_terdaftar_yayasans.status_alur', '=', 'Teruskan')
                                        ->orWhere('rekomendasi_terdaftar_yayasans.status_alur', '=', 'kembalikan');
                                })
                                ->where('rekomendasi_terdaftar_yayasans.no_pendaftaran', 'like', "%$search%");
                            
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
        $gabunganData->dataRekom=$tmp;
           array_push($result, $gabunganData);
        }
        return response()->json([
            'draw' => $request->input('draw'),
            'recordsTotal' => rekomendasi_terdaftar_yayasan::count(),
            'recordsFiltered' => $total_filtered_items,
            'data' => $result,
        ]);
    }

    public function getDataTeruskan(Request $request)
    {
        $user_name = Auth::user()->name;
        // dd($user_name);

        $query = DB::table('rekomendasi_terdaftar_yayasans')
            ->join('users', 'users.id', '=', 'rekomendasi_terdaftar_yayasans.petugas')
            ->leftjoin('indonesia_villages as b', 'b.code', '=', 'rekomendasi_terdaftar_yayasans.id_kelurahan')
            ->leftjoin('indonesia_districts as d', 'd.code', '=', 'rekomendasi_terdaftar_yayasans.id_kecamatan')
            ->select('rekomendasi_terdaftar_yayasans.*', 'b.name_village', 'd.name_districts', 'users.name');

        $user_id = Auth::user()->id;
        // dd($user_id);

        $user_wilayah = DB::table('wilayahs')
            ->join('model_has_roles', 'model_has_roles.model_id', '=', 'wilayahs.createdby')
            ->join('users', 'users.id', '=', 'model_has_roles.model_id')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->select('wilayahs.*','roles.name','model_has_roles.role_id')
            ->where('wilayahs.createdby', $user_id)
            ->where(function ($query) {
                $query->where('status_wilayah', 1);
            })
            ->first();
        // dd($user_wilayah);
            if ($user_wilayah->name == 'fasilitator') {
                // dd(auth::user()->id);
                $query = DB::table('rekomendasi_terdaftar_yayasans')
                    ->join('users', 'users.id', '=', 'rekomendasi_terdaftar_yayasans.petugas')
                   
                    // ->join('model_has_roles', 'model_has_roles.model_id', '=', 'rekomendasi_terdaftar_yayasans.tujuan')
                    ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_terdaftar_yayasans.id_kelurahan')
                    ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_terdaftar_yayasans.id_kecamatan')
                    ->select('rekomendasi_terdaftar_yayasans.*', 'b.name_village', 'd.name_districts','users.name')
                    ->orWhere(function ($query) use ($user_wilayah) {
                        $query->where('rekomendasi_terdaftar_yayasans.id_kelurahan', $user_wilayah->kelurahan_id)
                            ->where('rekomendasi_terdaftar_yayasans.tujuan', '!=', $user_wilayah->role_id)
                            // ->where('rekomendasi_terdaftar_yayasans.petugas', '!=', auth::user()->id)
                            // ->where('rekomendasi_terdaftar_yayasans.petugas','!=', $user_wilayah->model_id)
                            ->where(function ($query) {
                                $query->where('rekomendasi_terdaftar_yayasans.status_alur', '=', 'Teruskan')
                                    ->orWhere('rekomendasi_terdaftar_yayasans.status_alur', '=', 'kembalikan');
                            });
                        })->distinct();
            }
            if ($user_wilayah->name == 'Front Office Kelurahan') {
                $query = DB::table('rekomendasi_terdaftar_yayasans')
                    ->join('users', 'users.id', '=', 'rekomendasi_terdaftar_yayasans.petugas')
                   
                    // ->join('model_has_roles', 'model_has_roles.model_id', '=', 'rekomendasi_terdaftar_yayasans.tujuan')
                    ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_terdaftar_yayasans.id_kelurahan')
                    ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_terdaftar_yayasans.id_kecamatan')
                    ->select('rekomendasi_terdaftar_yayasans.*', 'b.name_village', 'd.name_districts','users.name')
                    ->orWhere(function ($query) use ($user_wilayah) {
                        $query->where('rekomendasi_terdaftar_yayasans.id_kelurahan', $user_wilayah->kelurahan_id)
                            ->where('rekomendasi_terdaftar_yayasans.tujuan', '!=', $user_wilayah->role_id)
                            // ->where('rekomendasi_terdaftar_yayasans.petugas', '!=', auth::user()->id)
                            // ->where('rekomendasi_terdaftar_yayasans.petugas','!=', $user_wilayah->model_id)
                            ->where(function ($query) {
                                $query->where('rekomendasi_terdaftar_yayasans.status_alur', '=', 'Teruskan')
                                    ->orWhere('rekomendasi_terdaftar_yayasans.status_alur', '=', 'kembalikan');
                            });
                        })->distinct();
                // dd($query);
    
            }
            if ($user_wilayah->name == 'warga') {
                $query = DB::table('rekomendasi_terdaftar_yayasans')
                    ->join('users', 'users.id', '=', 'rekomendasi_terdaftar_yayasans.petugas')
                   
                    // ->join('model_has_roles', 'model_has_roles.model_id', '=', 'rekomendasi_terdaftar_yayasans.tujuan')
                    ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_terdaftar_yayasans.id_kelurahan')
                    ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_terdaftar_yayasans.id_kecamatan')
                    ->select('rekomendasi_terdaftar_yayasans.*', 'b.name_village', 'd.name_districts','users.name')
                    ->orWhere(function ($query) use ($user_wilayah) {
                        $query->where('rekomendasi_terdaftar_yayasans.id_kelurahan', $user_wilayah->kelurahan_id)
                            ->where('rekomendasi_terdaftar_yayasans.tujuan', '!=', $user_wilayah->role_id)
                            // ->where('rekomendasi_terdaftar_yayasans.petugas', '!=', auth::user()->id)
                            // ->where('rekomendasi_terdaftar_yayasans.petugas','!=', $user_wilayah->model_id)
                            ->where(function ($query) {
                                $query->where('rekomendasi_terdaftar_yayasans.status_alur', '=', 'Teruskan')
                                    ->orWhere('rekomendasi_terdaftar_yayasans.status_alur', '=', 'kembalikan');
                            });
                        })->distinct();
                // dd($query);
    
            }
            if ($user_wilayah->name == 'Front Office kota' ) {
                $query = DB::table('rekomendasi_terdaftar_yayasans')
                    ->join('users', 'users.id', '=', 'rekomendasi_terdaftar_yayasans.petugas')
                    // ->join('model_has_roles', 'model_has_roles.model_id', '=', 'rekomendasi_terdaftar_yayasans.tujuan')
                    ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_terdaftar_yayasans.id_kelurahan')
                    ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_terdaftar_yayasans.id_kecamatan')
                    ->select('rekomendasi_terdaftar_yayasans.*', 'b.name_village', 'd.name_districts', 'users.name')
                    ->orWhere(function ($query) use ($user_wilayah) {
                        $query->where('rekomendasi_terdaftar_yayasans.id_kabkot', $user_wilayah->kota_id)
                            ->where('rekomendasi_terdaftar_yayasans.tujuan', '!=', $user_wilayah->role_id)
                            ->where('rekomendasi_terdaftar_yayasans.updatedby', '=', auth::user()->id)
                            // ->where('rekomendasi_terdaftar_yayasans.petugas','!=', $user_wilayah->model_id)
                            ->where(function ($query) {
                                $query->where('rekomendasi_terdaftar_yayasans.status_alur', '=', 'Teruskan')
                                    ->orWhere('rekomendasi_terdaftar_yayasans.status_alur', '=', 'kembalikan');
                            });
                    });
                // dd($query);

            }
            if ($user_wilayah->name == 'Back Ofiice Kota') {
                // dd($user_wilayah->model_id);
                $query = DB::table('rekomendasi_terdaftar_yayasans')
                    ->join('users', 'users.id', '=', 'rekomendasi_terdaftar_yayasans.petugas')
                
                    // ->join('model_has_roles', 'model_has_roles.model_id', '=', 'rekomendasi_terdaftar_yayasans.tujuan')
                    ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_terdaftar_yayasans.id_kelurahan')
                    ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_terdaftar_yayasans.id_kecamatan')
                    ->select('rekomendasi_terdaftar_yayasans.*', 'b.name_village', 'd.name_districts', 'users.name')
                    ->orWhere(function ($query) use ($user_wilayah) {
                        $query->where('rekomendasi_terdaftar_yayasans.id_kabkot', $user_wilayah->kota_id)
                        ->where('rekomendasi_terdaftar_yayasans.tujuan', '!=', $user_wilayah->role_id)
                        // ->where('rekomendasi_terdaftar_yayasans.updatedby', '=', auth::user()->id)
                            // ->where('rekomendasi_terdaftar_yayasans.petugas','!=', $user_wilayah->model_id)
                            ->where(function ($query) {
                                $query->where('rekomendasi_terdaftar_yayasans.status_alur', '=', 'Teruskan')
                                    ->orWhere('rekomendasi_terdaftar_yayasans.status_alur', '=', 'kembalikan');
                            });
                    });
            }
            if ($user_wilayah->name == 'Back Ofiice kelurahan') {
                // dd($user_wilayah->model_id);
                $query = DB::table('rekomendasi_terdaftar_yayasans')
                ->join('users', 'users.id', '=', 'rekomendasi_terdaftar_yayasans.petugas')
                ->join('log_yayasan', 'log_yayasan.id_trx_yayasan', '=', 'rekomendasi_terdaftar_yayasans.id')
                // ->join('model_has_roles', 'model_has_roles.model_id', '=', 'rekomendasi_terdaftar_yayasans.tujuan')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_terdaftar_yayasans.id_kelurahan')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_terdaftar_yayasans.id_kecamatan')
                ->select('rekomendasi_terdaftar_yayasans.*', 'b.name_village', 'd.name_districts', 'users.name')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_terdaftar_yayasans.id_kabkot', $user_wilayah->kota_id)
                        ->where('rekomendasi_terdaftar_yayasans.tujuan', '!=', $user_wilayah->role_id)
                        // ->where('rekomendasi_terdaftar_yayasans.updatedby', '=', auth::user()->id)
                        // ->where('rekomendasi_terdaftar_yayasans.petugas','!=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_terdaftar_yayasans.status_alur', '=', 'Teruskan')
                                ->orWhere('rekomendasi_terdaftar_yayasans.status_alur', '=', 'kembalikan');
                        });
                });
            }
            if ($user_wilayah->name == 'supervisor') {
                // dd($user_wilayah->model_id);
                $query = DB::table('rekomendasi_terdaftar_yayasans')
                ->join('users', 'users.id', '=', 'rekomendasi_terdaftar_yayasans.petugas')
                
                // ->join('model_has_roles', 'model_has_roles.model_id', '=', 'rekomendasi_terdaftar_yayasans.tujuan')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_terdaftar_yayasans.id_kelurahan')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_terdaftar_yayasans.id_kecamatan')
                ->select('rekomendasi_terdaftar_yayasans.*', 'b.name_village', 'd.name_districts', 'users.name')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_terdaftar_yayasans.id_kabkot', $user_wilayah->kota_id)
                    ->where('rekomendasi_terdaftar_yayasans.tujuan', '!=', $user_wilayah->role_id)
                    // ->where('rekomendasi_terdaftar_yayasans.updatedby', '=', auth::user()->id)
                        // ->where('rekomendasi_terdaftar_yayasans.petugas','!=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_terdaftar_yayasans.status_alur', '=', 'Teruskan')
                                ->orWhere('rekomendasi_terdaftar_yayasans.status_alur', '=', 'kembalikan');
                        });
                });
            }
            if ($user_wilayah->name == 'kepala bidang') {
                // dd( $user_wilayah->role_id);
                $query = DB::table('rekomendasi_terdaftar_yayasans')
                ->join('users', 'users.id', '=', 'rekomendasi_terdaftar_yayasans.petugas')
                // ->join('model_has_roles', 'model_has_roles.model_id', '=', 'rekomendasi_terdaftar_yayasans.tujuan')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_terdaftar_yayasans.id_kelurahan')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_terdaftar_yayasans.id_kecamatan')
                ->select('rekomendasi_terdaftar_yayasans.*', 'b.name_village', 'd.name_districts','users.name')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_terdaftar_yayasans.id_kabkot', $user_wilayah->kota_id)
                    ->where('rekomendasi_terdaftar_yayasans.tujuan', '!=', $user_wilayah->role_id)
                    ->where('rekomendasi_terdaftar_yayasans.updatedby', '!=', auth::user()->id)
                        // ->where('rekomendasi_terdaftar_yayasans.petugas','!=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_terdaftar_yayasans.status_alur', '=', 'Teruskan')
                                ->orWhere('rekomendasi_terdaftar_yayasans.status_alur', '=', 'kembalikan');
                        });
                });
                // dd($query);
            }
            if ($user_wilayah->name == 'SekertarisDinas') {
                // dd($user_wilayah);
                $query = DB::table('rekomendasi_terdaftar_yayasans')
                ->join('users', 'users.id', '=', 'rekomendasi_terdaftar_yayasans.petugas')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_terdaftar_yayasans.id_kelurahan')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_terdaftar_yayasans.id_kecamatan')
                ->select('rekomendasi_terdaftar_yayasans.*', 'b.name_village', 'd.name_districts', 'users.name')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_terdaftar_yayasans.id_kabkot', $user_wilayah->kota_id)
                        ->where('rekomendasi_terdaftar_yayasans.tujuan', '!=', $user_wilayah->role_id)
                        // ->where('rekomendasi_terdaftar_yayasans.updatedby', '=', auth::user()->id)
                        // ->where('rekomendasi_terdaftar_yayasans.petugas','!=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_terdaftar_yayasans.status_alur', '=', 'Teruskan')
                                ->orWhere('rekomendasi_terdaftar_yayasans.status_alur', '=', 'kembalikan');
                        });
                });
            
            }
            if ($user_wilayah->name == 'KepalaDinas') {
                //  dd(auth::user()->id);
                $query = DB::table('rekomendasi_terdaftar_yayasans')
                ->join('users', 'users.id', '=', 'rekomendasi_terdaftar_yayasans.petugas')
                // ->join('model_has_roles', 'model_has_roles.model_id', '=', 'rekomendasi_terdaftar_yayasans.tujuan')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_terdaftar_yayasans.id_kelurahan')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_terdaftar_yayasans.id_kecamatan')
                ->select('rekomendasi_terdaftar_yayasans.*', 'b.name_village', 'd.name_districts', 'users.name')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_terdaftar_yayasans.id_kabkot', $user_wilayah->kota_id)
                    ->where('rekomendasi_terdaftar_yayasans.tujuan', '!=', $user_wilayah->role_id)
                    // ->where('rekomendasi_terdaftar_yayasans.updatedby', '=', auth::user()->id)
                        // ->where('rekomendasi_terdaftar_yayasans.petugas','!=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_terdaftar_yayasans.status_alur', '=', 'Teruskan')
                                ->orWhere('rekomendasi_terdaftar_yayasans.status_alur', '=', 'kembalikan');
                        });
                });
            }
            if ($user_wilayah->name == 'Front Office Kelurahan' || $user_wilayah->name == 'Back Ofiice kelurahan'|| $user_wilayah->name == 'fasilitator') {
                if ($request->has('search') && !empty($request->search['value'])) {
                    $search = $request->search['value'];
                    $query = DB::table('rekomendasi_terdaftar_yayasans')
                        ->join('users', 'users.id', '=', 'rekomendasi_terdaftar_yayasans.petugas')
                        // ->join('model_has_roles', 'model_has_roles.model_id', '=', 'rekomendasi_terdaftar_yayasans.tujuan')
                        ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_terdaftar_yayasans.id_kelurahan')
                        ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_terdaftar_yayasans.id_kecamatan')
                        ->select('rekomendasi_terdaftar_yayasans.*', 'b.name_village', 'd.name_districts', 'users.name')
                        ->orWhere(function ($query) use ($user_wilayah, $search) {
                            $query->where('rekomendasi_terdaftar_yayasans.id_kelurahan', $user_wilayah->kelurahan_id)
                            ->where('rekomendasi_terdaftar_yayasans.tujuan', '!=', $user_wilayah->role_id)
                                // ->where('rekomendasi_terdaftar_yayasans.updatedby', '=', auth::user()->id)
                                // ->where('rekomendasi_terdaftar_yayasans.petugas','!=', $user_wilayah->model_id)
                                ->where(function ($query) {
                                    $query->where('rekomendasi_terdaftar_yayasans.status_alur', '=', 'Teruskan')
                                        ->orWhere('rekomendasi_terdaftar_yayasans.status_alur', '=', 'kembalikan');
                                })
                                ->where('rekomendasi_terdaftar_yayasans.no_pendaftaran', 'like', "%$search%");
                            });
                        // dd($query);
                }
            }elseif($user_wilayah->name == 'Front Office Kota' || $user_wilayah->name == 'Back Ofiice Kota'|| $user_wilayah->name == 'KepalaDinas'||$user_wilayah->name == 'SekertarisDinas'||$user_wilayah->name == 'Supervisor') {
                if ($request->has('search') && !empty($request->search['value'])) {
                    $search = $request->search['value'];
                    $query = DB::table('rekomendasi_terdaftar_yayasans')
                        ->join('users', 'users.id', '=', 'rekomendasi_terdaftar_yayasans.petugas')
                        // ->join('model_has_roles', 'model_has_roles.model_id', '=', 'rekomendasi_terdaftar_yayasans.tujuan')
                        ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_terdaftar_yayasans.id_kelurahan')
                        ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_terdaftar_yayasans.id_kecamatan')
                        ->select('rekomendasi_terdaftar_yayasans.*', 'b.name_village', 'd.name_districts', 'users.name')
                        ->orWhere(function ($query) use ($user_wilayah, $search) {
                            $query->where('rekomendasi_terdaftar_yayasans.id_kabkot', $user_wilayah->kota_id)
                            ->where('rekomendasi_terdaftar_yayasans.tujuan', '!=', $user_wilayah->role_id)
                                // ->where('rekomendasi_terdaftar_yayasans.updatedby', '=', auth::user()->id)
                                // ->where('rekomendasi_terdaftar_yayasans.petugas','!=', $user_wilayah->model_id)
                                ->where(function ($query) {
                                    $query->where('rekomendasi_terdaftar_yayasans.status_alur', '=', 'Teruskan')
                                        ->orWhere('rekomendasi_terdaftar_yayasans.status_alur', '=', 'kembalikan');
                                })
                                ->where('rekomendasi_terdaftar_yayasans.no_pendaftaran', 'like', "%$search%");
                            });
                }
            }
        $total_filtered_items = $query->count();

        $start = $request->start;
        // dd($start);
        $length = $request->length;
        // dd($length);
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
        // dd($result);
        return response()->json([
            'draw' => $request->input('draw'),
            'recordsTotal' => rekomendasi_terdaftar_yayasan::count(),
            'recordsFiltered' => $total_filtered_items,
            // 'data' => $user_wilayah,
            'data' => $result,
            
        ]);
    }

    public function getDataSelesai(Request $request)
    {
        $user_name = Auth::user()->name;
        $query = DB::table('rekomendasi_terdaftar_yayasans')
            ->join('users', 'users.id', 'rekomendasi_terdaftar_yayasans.petugas')
            ->join('log_yayasan', 'log_yayasan.id_trx_yayasan', '=', 'rekomendasi_terdaftar_yayasans.id')
            ->join('roles', 'roles.id', '=', 'rekomendasi_terdaftar_yayasans.tujuan')
            ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_terdaftar_yayasans.id_kelurahan')
            ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_terdaftar_yayasans.id_kecamatan')
            ->select('rekomendasi_terdaftar_yayasans.*', 'roles.name', 'users.name', 'b.name_village', 'd.name_districts');
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
            // ->join('log_yayasan', 'log_yayasan.id_trx_yayasan', '=', 'rekomendasi_terdaftar_yayasans.id')
            ->join('indonesia_villages', 'indonesia_villages.code', '=', 'rekomendasi_terdaftar_yayasans.id_kelurahan')
            ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_terdaftar_yayasans.id_kecamatan')
            ->select('rekomendasi_terdaftar_yayasans.*', 'users.name', 'd.name_districts', 'indonesia_villages.name_village')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_terdaftar_yayasans.id_kelurahan', $user_wilayah->kelurahan_id)
                        // ->where('log_yayasan.tujuan','=', $user_wilayah->role_id);
                        // ->where('log_yayasan.petugas','=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_terdaftar_yayasans.status_alur', '=', 'Tolak')
                                ->orWhere('rekomendasi_terdaftar_yayasans.status_alur', '=', 'Selesai');
                        });
                });
        }elseif ($user_wilayah->name == 'warga') {
            //  dd($user_wilayah->role_id);
            $query = DB::table('rekomendasi_terdaftar_yayasans')
            ->join('users', 'users.id', 'rekomendasi_terdaftar_yayasans.petugas')
            // ->join('log_yayasan', 'log_yayasan.id_trx_yayasan', '=', 'rekomendasi_terdaftar_yayasans.id')
            ->join('indonesia_villages', 'indonesia_villages.code', '=', 'rekomendasi_terdaftar_yayasans.id_kelurahan')
            ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_terdaftar_yayasans.id_kecamatan')
            ->select('rekomendasi_terdaftar_yayasans.*', 'users.name', 'd.name_districts', 'indonesia_villages.name_village')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_terdaftar_yayasans.id_kelurahan', $user_wilayah->kelurahan_id)
                        // ->where('log_yayasan.tujuan','=', $user_wilayah->role_id);
                        // ->where('log_yayasan.petugas','=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_terdaftar_yayasans.status_alur', '=', 'Tolak')
                                ->orWhere('rekomendasi_terdaftar_yayasans.status_alur', '=', 'Selesai');
                        });
                });
        }elseif ($user_wilayah->name == 'Front Office Kelurahan') {
            //  dd($user_wilayah->role_id);
            $query = DB::table('rekomendasi_terdaftar_yayasans')
            ->join('users', 'users.id', 'rekomendasi_terdaftar_yayasans.petugas')
            // ->join('log_yayasan', 'log_yayasan.id_trx_yayasan', '=', 'rekomendasi_terdaftar_yayasans.id')
            ->join('indonesia_villages', 'indonesia_villages.code', '=', 'rekomendasi_terdaftar_yayasans.id_kelurahan')
            ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_terdaftar_yayasans.id_kecamatan')
            ->select('rekomendasi_terdaftar_yayasans.*', 'users.name', 'd.name_districts', 'indonesia_villages.name_village')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_terdaftar_yayasans.id_kelurahan', $user_wilayah->kelurahan_id)
                        // ->where('log_yayasan.tujuan','=', $user_wilayah->role_id);
                        // ->where('log_yayasan.petugas','=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_terdaftar_yayasans.status_alur', '=', 'Tolak')
                                ->orWhere('rekomendasi_terdaftar_yayasans.status_alur', '=', 'Selesai');
                        });
                });
        }elseif ($user_wilayah->name == 'Front Office kota') {
            //  dd($user_wilayah->role_id);
            $query = DB::table('rekomendasi_terdaftar_yayasans')
            ->join('users', 'users.id', 'rekomendasi_terdaftar_yayasans.petugas')
            // ->join('log_yayasan', 'log_yayasan.id_trx_yayasan', '=', 'rekomendasi_terdaftar_yayasans.id')
            ->join('indonesia_villages', 'indonesia_villages.code', '=', 'rekomendasi_terdaftar_yayasans.id_kelurahan')
            ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_terdaftar_yayasans.id_kecamatan')
            ->select('rekomendasi_terdaftar_yayasans.*', 'users.name', 'd.name_districts', 'indonesia_villages.name_village')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_terdaftar_yayasans.id_kabkot', $user_wilayah->kota_id)
                        // ->where('log_yayasan.tujuan','=', $user_wilayah->role_id)
                        // ->where('log_yayasan.petugas','=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_terdaftar_yayasans.status_alur', '=', 'Tolak')
                                ->orWhere('rekomendasi_terdaftar_yayasans.status_alur', '=', 'Selesai');
                        });
                });
        }elseif ($user_wilayah->name == 'Back Ofiice kelurahan') {
            // dd($user_wilayah);
            $query = DB::table('rekomendasi_terdaftar_yayasans')
                ->join('users', 'users.id', 'rekomendasi_terdaftar_yayasans.petugas')
                ->join('log_yayasan', 'log_yayasan.id_trx_yayasan', '=', 'rekomendasi_terdaftar_yayasans.id')
                ->join('indonesia_villages', 'indonesia_villages.code', '=', 'rekomendasi_terdaftar_yayasans.id_kelurahan')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_terdaftar_yayasans.id_kecamatan')
                ->select('rekomendasi_terdaftar_yayasans.*', 'users.name', 'd.name_districts', 'indonesia_villages.name_village')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_terdaftar_yayasans.id_kabkot', $user_wilayah->kota_id)
                        ->where('log_yayasan.tujuan','=', $user_wilayah->role_id)
                        ->where('log_yayasan.petugas','=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_terdaftar_yayasans.status_alur', '=', 'Tolak')
                                ->orWhere('rekomendasi_terdaftar_yayasans.status_alur', '=', 'Selesai');
                        });
                });
            // dd($query); 
        }elseif ($user_wilayah->name == 'kepala bidang') {
            // dd($user_wilayah);
            $query = DB::table('rekomendasi_terdaftar_yayasans')
            ->join('users', 'users.id', 'rekomendasi_terdaftar_yayasans.petugas')
            // ->join('log_yayasan', 'log_yayasan.id_trx_yayasan', '=', 'rekomendasi_terdaftar_yayasans.id')
            ->join('indonesia_villages', 'indonesia_villages.code', '=', 'rekomendasi_terdaftar_yayasans.id_kelurahan')
            ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_terdaftar_yayasans.id_kecamatan')
            ->select('rekomendasi_terdaftar_yayasans.*', 'users.name', 'd.name_districts', 'indonesia_villages.name_village')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_terdaftar_yayasans.id_kabkot', $user_wilayah->kota_id)
                        // ->where('log_yayasan.tujuan','=', $user_wilayah->role_id)
                        // ->where('log_yayasan.petugas','=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_terdaftar_yayasans.status_alur', '=', 'Tolak')
                                ->orWhere('rekomendasi_terdaftar_yayasans.status_alur', '=', 'Selesai');
                        });
                });
        }elseif ($user_wilayah->name == 'Back Ofiice Kota') {
            // dd($user_wilayah->role_id);
            $query = DB::table('rekomendasi_terdaftar_yayasans')
               ->join('users', 'users.id', 'rekomendasi_terdaftar_yayasans.petugas')
                // ->join('log_yayasan', 'log_yayasan.id_trx_yayasan', '=', 'rekomendasi_terdaftar_yayasans.id')
                ->join('indonesia_villages', 'indonesia_villages.code', '=', 'rekomendasi_terdaftar_yayasans.id_kelurahan')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_terdaftar_yayasans.id_kecamatan')
                ->select('rekomendasi_terdaftar_yayasans.*', 'users.name', 'd.name_districts', 'indonesia_villages.name_village')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_terdaftar_yayasans.id_kabkot', $user_wilayah->kota_id)
                        // ->where('log_yayasan.tujuan','=', $user_wilayah->role_id)
                        // ->where('log_yayasan.petugas','=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_terdaftar_yayasans.status_alur', '=', 'Tolak')
                                ->orWhere('rekomendasi_terdaftar_yayasans.status_alur', '=', 'Selesai');
                        });
                });
        }elseif ($user_wilayah->name == 'kepala bidang') {
            // dd($user_wilayah);
            $query = DB::table('rekomendasi_terdaftar_yayasans')
            ->join('users', 'users.id', 'rekomendasi_terdaftar_yayasans.petugas')
            // ->join('log_yayasan', 'log_yayasan.id_trx_yayasan', '=', 'rekomendasi_terdaftar_yayasans.id')
            ->join('indonesia_villages', 'indonesia_villages.code', '=', 'rekomendasi_terdaftar_yayasans.id_kelurahan')
            ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_terdaftar_yayasans.id_kecamatan')
            ->select('rekomendasi_terdaftar_yayasans.*', 'users.name', 'd.name_districts', 'indonesia_villages.name_village')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_terdaftar_yayasans.id_kabkot', $user_wilayah->kota_id)
                        // ->where('log_yayasan.tujuan','=', $user_wilayah->role_id)
                        // ->where('log_yayasan.petugas','=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_terdaftar_yayasans.status_alur', '=', 'Tolak')
                                ->orWhere('rekomendasi_terdaftar_yayasans.status_alur', '=', 'Selesai');
                        });
                });
        }elseif ($user_wilayah->name == 'supervisor') {
            // dd($user_wilayah);
            $query = DB::table('rekomendasi_terdaftar_yayasans')
                ->join('users', 'users.id', 'rekomendasi_terdaftar_yayasans.petugas')
                // ->join('log_yayasan', 'log_yayasan.id_trx_yayasan', '=', 'rekomendasi_terdaftar_yayasans.id')
                ->join('indonesia_villages', 'indonesia_villages.code', '=', 'rekomendasi_terdaftar_yayasans.id_kelurahan')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_terdaftar_yayasans.id_kecamatan')
                ->select('rekomendasi_terdaftar_yayasans.*', 'users.name', 'd.name_districts', 'indonesia_villages.name_village')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_terdaftar_yayasans.id_kabkot', $user_wilayah->kota_id)
                        // ->where('log_yayasan.tujuan','=', $user_wilayah->role_id)
                        // ->where('log_yayasan.petugas','=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_terdaftar_yayasans.status_alur', '=', 'Tolak')
                                ->orWhere('rekomendasi_terdaftar_yayasans.status_alur', '=', 'Selesai');
                        });
                });
        }elseif ($user_wilayah->name == 'SekertarisDinas') {
            // dd($user_wilayah);
            $query = DB::table('rekomendasi_terdaftar_yayasans')
            ->join('users', 'users.id', 'rekomendasi_terdaftar_yayasans.petugas')
            // ->join('log_yayasan', 'log_yayasan.id_trx_yayasan', '=', 'rekomendasi_terdaftar_yayasans.id')
            ->join('indonesia_villages', 'indonesia_villages.code', '=', 'rekomendasi_terdaftar_yayasans.id_kelurahan')
            ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_terdaftar_yayasans.id_kecamatan')
            ->select('rekomendasi_terdaftar_yayasans.*', 'users.name', 'd.name_districts', 'indonesia_villages.name_village')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_terdaftar_yayasans.id_kabkot', $user_wilayah->kota_id)
                        // ->where('log_yayasan.tujuan','=', $user_wilayah->role_id)
                        // ->where('log_yayasan.petugas','=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_terdaftar_yayasans.status_alur', '=', 'Tolak')
                                ->orWhere('rekomendasi_terdaftar_yayasans.status_alur', '=', 'Selesai');
                        });
                });
       
        }
        elseif ($user_wilayah->name == 'KepalaDinas') {
            // dd($user_wilayah);
            $query = DB::table('rekomendasi_terdaftar_yayasans')
                ->join('users', 'users.id', 'rekomendasi_terdaftar_yayasans.petugas')
                // ->join('log_yayasan', 'log_yayasan.id_trx_yayasan', '=', 'rekomendasi_terdaftar_yayasans.id')
                ->join('indonesia_villages', 'indonesia_villages.code', '=', 'rekomendasi_terdaftar_yayasans.id_kelurahan')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_terdaftar_yayasans.id_kecamatan')
                ->select('rekomendasi_terdaftar_yayasans.*', 'users.name', 'd.name_districts', 'indonesia_villages.name_village')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_terdaftar_yayasans.id_kabkot', $user_wilayah->kota_id)
                        // ->where('log_yayasan.tujuan','=', $user_wilayah->role_id)
                        // ->where('log_yayasan.petugas','=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_terdaftar_yayasans.status_alur', '=', 'Tolak')
                                ->orWhere('rekomendasi_terdaftar_yayasans.status_alur', '=', 'Selesai');
                        });
                })->distinct();
                // dd($query);
        }
        // if ($request->has('search') && !empty($request->search['value'])) {
        //     $search = $request->search['value'];
        //     $query = DB::table('rekomendasi_terdaftar_yayasans')
        //     ->join('users', 'users.id', 'rekomendasi_terdaftar_yayasans.petugas')
        //     // ->join('log_yayasan', 'log_yayasan.id_trx_yayasan', '=', 'rekomendasi_terdaftar_yayasans.id')
        //     ->join('indonesia_villages', 'indonesia_villages.code', '=', 'rekomendasi_terdaftar_yayasans.id_kelurahan')
        //     ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_terdaftar_yayasans.id_kecamatan')
        //     ->select('rekomendasi_terdaftar_yayasans.*', 'users.name', 'd.name_districts', 'indonesia_villages.name_village')
        //     ->orWhere(function ($query) use ($user_wilayah, $search) {
        //         $query->where('rekomendasi_terdaftar_yayasans.id_kabkot', $user_wilayah->kota_id)
        //             // ->where('log_yayasan.tujuan','=', $user_wilayah->role_id)
        //             // ->where('log_yayasan.petugas','=', $user_wilayah->model_id)
        //             ->where(function ($query) {
        //                 $query->where('rekomendasi_terdaftar_yayasans.status_alur', '=', 'Tolak')
        //                     ->orWhere('rekomendasi_terdaftar_yayasans.status_alur', '=', 'Selesai');
        //             })
        //             ->where('rekomendasi_terdaftar_yayasans.no_pendaftaran', 'like', "%$search%");
                
        //     })->distinct();
        // }
        if ($user_wilayah->name == 'Front Office Kelurahan' || $user_wilayah->name == 'Back Ofiice kelurahan'|| $user_wilayah->name == 'fasilitator') {
            if ($request->has('search') && !empty($request->search['value'])) {
                $search = $request->search['value'];
                    $query = DB::table('rekomendasi_terdaftar_yayasans')
                    ->join('users', 'users.id', 'rekomendasi_terdaftar_yayasans.petugas')
                    // ->join('log_yayasan', 'log_yayasan.id_trx_yayasan', '=', 'rekomendasi_terdaftar_yayasans.id')
                    ->join('indonesia_villages', 'indonesia_villages.code', '=', 'rekomendasi_terdaftar_yayasans.id_kelurahan')
                    ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_terdaftar_yayasans.id_kecamatan')
                    ->select('rekomendasi_terdaftar_yayasans.*', 'users.name', 'd.name_districts', 'indonesia_villages.name_village')
                    ->orWhere(function ($query) use ($user_wilayah,$search) {
                        $query->where('rekomendasi_terdaftar_yayasans.id_kelurahan', $user_wilayah->kelurahan_id)
                            // ->where('log_yayasan.tujuan','=', $user_wilayah->role_id)
                            // ->where('log_yayasan.petugas','=', $user_wilayah->model_id)
                            ->where(function ($query) {
                                $query->where('rekomendasi_terdaftar_yayasans.status_alur', '=', 'Tolak')
                                    ->orWhere('rekomendasi_terdaftar_yayasans.status_alur', '=', 'Selesai');
                            })
                            ->where('rekomendasi_terdaftar_yayasans.no_pendaftaran', 'like', "%$search%");
                    })->distinct();
                    // dd($query);
            }
        }elseif($user_wilayah->name == 'Front Office Kota' || $user_wilayah->name == 'Back Ofiice Kota'|| $user_wilayah->name == 'KepalaDinas'||$user_wilayah->name == 'SekertarisDinas'||$user_wilayah->name == 'Supervisor') {
            if ($request->has('search') && !empty($request->search['value'])) {
                $search = $request->search['value'];
                    $query = DB::table('rekomendasi_terdaftar_yayasans')
                    ->join('users', 'users.id', 'rekomendasi_terdaftar_yayasans.petugas')
                    // ->join('log_yayasan', 'log_yayasan.id_trx_yayasan', '=', 'rekomendasi_terdaftar_yayasans.id')
                    ->join('indonesia_villages', 'indonesia_villages.code', '=', 'rekomendasi_terdaftar_yayasans.id_kelurahan')
                    ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_terdaftar_yayasans.id_kecamatan')
                    ->select('rekomendasi_terdaftar_yayasans.*', 'users.name', 'd.name_districts', 'indonesia_villages.name_village')
                    ->orWhere(function ($query) use ($user_wilayah,$search) {
                        $query->where('rekomendasi_terdaftar_yayasans.id_kabkot', $user_wilayah->kota_id)
                            // ->where('log_yayasan.tujuan','=', $user_wilayah->role_id)
                            // ->where('log_yayasan.petugas','=', $user_wilayah->model_id)
                            ->where(function ($query) {
                                $query->where('rekomendasi_terdaftar_yayasans.status_alur', '=', 'Tolak')
                                    ->orWhere('rekomendasi_terdaftar_yayasans.status_alur', '=', 'Selesai');
                            })
                            ->where('rekomendasi_terdaftar_yayasans.no_pendaftaran', 'like', "%$search%");
                    })->distinct();
                    // dd($query);
            }
        }

        // Get total count of filtered items
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
        })->first();

        $result = array();

        foreach ($data as $tmp) {
            $gabunganData = new stdClass();
            $gabunganData->userLogin=$userLogin;
            $gabunganData->dataRekom=$tmp;
            array_push($result, $gabunganData);
        }
        // dd($result);
        return response()->json([
            'draw' => $request->input('draw'),
            'recordsTotal' => rekomendasi_terdaftar_yayasan::count(),
            'recordsFiltered' => $total_filtered_items,
            // 'data' => $user_wilayah,
            'data' => $result,

        ]);
    }
    public function prelistPage(Request $request)
    {
        return view('yayasans.index');
    }
    public function prelistDTKS(Request $request)
    {
        $columns = [
            // daftar kolom yang akan ditampilkan pada tabel
            'id_provinsi',
            'id_kabkot',
            'id_kecamatan',
            'id_kelurahan',
            'nik',
            'no_kk',
            'no_kis',
            'nama',
            'tgl_lahir',
            'alamat',
            'telp',
            'status_data',
            'email'
        ];

        // $query = Prelist::where('status_data', 'prelistdtks');
        $query = DB::table('prelist')
            ->join('indonesia_districts as a', 'a.code', '=', 'prelist.id_kecamatan')
            ->join('indonesia_villages as b', 'b.code', '=', 'prelist.id_kelurahan')
            ->select('prelist.*', 'a.name_districts', 'b.name_village');
        // dd($query);
        // menambahkan kondisi pencarian jika ada
        if ($request->has('search')) {
            $searchValue = $request->search['value'];
            $query->where(function ($query) use ($columns, $searchValue) {
                foreach ($columns as $column) {
                    $query->orWhere($column, 'like', '%' . $searchValue . '%');
                }
            });
        }
        // menambahkan kondisi sortir jika ada
        if ($request->has('order')) {
            $orderColumn = $columns[$request->order[0]['column']];
            $orderDirection = $request->order[0]['dir'];
            $query->orderBy($orderColumn, $orderDirection);
        }

        // mengambil data sesuai dengan paginasi yang diminta
        $perPage = $request->length ?: config('app.pagination.per_page');
        $currentPage = $request->start ? ($request->start / $perPage) + 1 : 1;
        $data = $query->paginate($perPage, ['*'], 'page', $currentPage);

        // memformat data untuk dikirim ke client
        $no = 1;
        $formattedData = [];
        foreach ($data as $item) {
            $formattedData[] = [
                'no' => $no++,
                'id_provinsi' => $item->id_provinsi,
                'id_kabkot' => $item->id_kabkot,
                'id_kecamatan' => $item->name_village,
                'id_kelurahan' => $item->name_districts,
                'nik' => $item->nik,
                'no_kk' => $item->no_kk,
                'no_kis' => $item->no_kis,
                'nama' => $item->nama,
                'tgl_lahir' => $item->tgl_lahir,
                'alamat' => $item->alamat,
                'telp' => $item->telp,
                'email' => $item->email,
            ];
        }
        // mengembalikan data dalam format JSON
        return response()->json([
            'draw' => $request->draw,
            'recordsTotal' => Pengaduan::count(),
            'recordsFiltered' => $data->total(),
            'data' => $formattedData
        ]);
    }
    public function detail_pengaduan($id)
    {
        $data2 = DB::table('pengaduans as w')->select(
            'w.*',
            'b.name_village',
            'prov.name_prov',
            'kota.name_cities',
            'kecamatan.name_districts',
            // 'w.status_wilayah',
        )
            ->leftjoin('indonesia_provinces as prov', 'prov.code', '=', 'w.id_provinsi')
            ->leftjoin('indonesia_cities as kota', 'kota.code', '=', 'w.id_kabkot')
            ->leftjoin('indonesia_districts as kecamatan', 'kecamatan.code', '=', 'w.id_kecamatan')
            ->leftjoin('indonesia_villages as b', 'b.code', '=', 'w.id_kelurahan')
            ->where('w.id', $id)->first();
        $data = [
            'data' => $data2
            // 'data' => $data2
        ];
        return response()->json($data);
    }
    public function detail_log_bantuan_pendidikan(Request $request, $id)
    {
        // dd($request);
        $user_name = Auth::user()->name;
        $query = DB::table('rekomendasi_bantuan_pendidikans')
            // ->join('users', 'users.id', '=', 'rekomendasi_bantuan_pendidikans.created_by')
            ->join('log_yayasan', 'log_yayasan.id_trx_yayasan', '=', 'rekomendasi_bantuan_pendidikans.id')
            ->select('log_yayasan.*')->get();

        // dd($query);
        // Add where conditions based on user's wilayah data


        if ($request->has('search') && !empty($request->search['value'])) {
            $search = $request->search['value'];
            $query = DB::table('rekomendasi_bantuan_pendidikans')
                // ->join('users', 'users.id', '=', 'rekomendasi_bantuan_pendidikans.created_by')
                ->join('log_bantuan_pendidikan', 'log_bantuan_pendidikan.id_trx_log_bantuan_pendidikans', '=', 'rekomendasi_bantuan_pendidikans.id')
                ->select('rekomendasi_bantuan_pendidikans.*')
                ->where(function ($query) use ($search) {
                    $query->where('rekomendasi_bantuan_pendidikans.no_pendaftaran', 'like', "%$search%");
                });
        }

        // Get total count of filtered items
        $total_filtered_items = $query->count();
        // dd($total_filtered_items);
        // Add ordering
        if ($request->has('order')) {
            $order_column = $request->order[0]['column'];
            $order_direction = $request->order[0]['dir'];
            $query->orderBy($request->input('columns.' . $order_column . '.data'), $order_direction);
        }
        // Get paginated data
        // dd($query->paginate());
        // $data = $query->paginate($request->input('length'));
        // dd($data);
        // mengubah data JSON menjadi objek PHP
        $data = DB::table('log_bantuan_pendidikan')
            ->join('users as a', 'a.id', '=', 'log_bantuan_pendidikan.created_by_log_bantuan_pendidikans')
            // ->join('users as b', 'b.id', '=', 'rekomendasi_bantuan_pendidikans.created_by')
            ->join('rekomendasi_bantuan_pendidikans', 'rekomendasi_bantuan_pendidikans.id', '=', 'log_bantuan_pendidikan.id_trx_log_bantuan_pendidikans')
            ->select('a.name', 'rekomendasi_bantuan_pendidikans.status_alur_bantuan_pendidikans', 'rekomendasi_bantuan_pendidikans.id', 'rekomendasi_bantuan_pendidikans.file_pendukung_bantuan_pendidikans', 'rekomendasi_bantuan_pendidikans.catatan_bantuan_pendidikans', 'rekomendasi_bantuan_pendidikans.created_at')
            // ->select('a.name')
            ->where('log_bantuan_pendidikan.id_trx_log_bantuan_pendidikans', $id)->get();
        // dd($data);
        return response()->json([
            'draw' => $request->input('draw'),
            'recordsTotal' => logpendidikan::count(),
            'recordsFiltered' => $total_filtered_items,
            'data' => $data,
        ]);
    }
}
