<?php

namespace App\Http\Controllers;

use App\Http\Requests\Createrekomendasi_terdaftar_dtksRequest;
use App\Http\Requests\Updaterekomendasi_terdaftar_dtksRequest;
use App\Http\Controllers\AppBaseController;
use App\Models\log_sudtks;
use App\Models\Prelist;
use App\Models\rekomendasi_terdaftar_dtks;
use App\Models\Roles;
use App\Models\pelapor;
use App\Repositories\rekomendasi_terdaftar_dtksRepository;
use Illuminate\Http\Request;
use Flash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Laracasts\Flash\Flash as FlashFlash;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class rekomendasi_terdaftar_dtksController extends AppBaseController
{
    /** @var rekomendasi_terdaftar_dtksRepository $rekomendasiTerdaftarDtksRepository*/
    private $rekomendasiTerdaftarDtksRepository;

    public function __construct(rekomendasi_terdaftar_dtksRepository $rekomendasiTerdaftarDtksRepo)
    {
        $this->rekomendasiTerdaftarDtksRepository = $rekomendasiTerdaftarDtksRepo;
    }
    public function getCountKelurahan($name_kelurahan) {
        $countRekomendasiBantuanPendidikan = DB::select("
            SELECT COUNT(rb.id_kelurahan_sudtks) AS counted
            FROM rekomendasi_terdaftar_dtks AS rb
            INNER JOIN indonesia_villages AS iv ON rb.id_kelurahan_sudtks = iv.code
            WHERE iv.name_village = ?
            GROUP BY rb.id_kelurahan_sudtks;
        ", [$name_kelurahan]);
    
        // Check if the result is empty, and if so, set counted to 0
        if (empty($countRekomendasiBantuanPendidikan)) {
            $countRekomendasiBantuanPendidikan[0] = ['counted' => 0];
        }
    
        $countRekomendasiBantuanPendidikanTeruskan = DB::select("
            SELECT COUNT(rb.id_kelurahan_sudtks) AS counted
            FROM rekomendasi_terdaftar_dtks AS rb
            INNER JOIN indonesia_villages AS iv ON rb.id_kelurahan_sudtks = iv.code
            WHERE iv.name_village = ? AND rb.status_alur_bantuan_pendidikans = 'Teruskan'
            GROUP BY rb.id_kelurahan_sudtks;
        ", [$name_kelurahan]);
        
        // Check if the result is empty, and if so, set counted to 0
        if (empty($countRekomendasiBantuanPendidikanTeruskan)) {
            $countRekomendasiBantuanPendidikanTeruskan[0] = ['counted' => 0];
        }
    
        // $countRekomendasiBantuanPendidikanSelesai = DB::select("
        //     SELECT COUNT(rb.status_alur_bantuan_pendidikans) AS counted
        //     FROM rekomendasi_terdaftar_dtks AS rb
        //     INNER JOIN indonesia_villages AS iv ON rb.id_kelurahan_sudtks = iv.code
        //     WHERE rb.status_alur_bantuan_pendidikans = 'Selesai'
        //     WHERE iv.name_village = ?
        //     GROUP BY rb.id_kelurahan_sudtks;
        //     ", [$name_kelurahan]);
        $countRekomendasiBantuanPendidikanSelesai = DB::select("
            SELECT COUNT(rb.id_kelurahan_sudtks) AS counted
            FROM rekomendasi_terdaftar_dtks AS rb
            INNER JOIN indonesia_villages AS iv ON rb.id_kelurahan_sudtks = iv.code
            WHERE iv.name_village = ? AND rb.status_alur_bantuan_pendidikans = 'Selesai'
            GROUP BY rb.id_kelurahan_sudtks;
        ", [$name_kelurahan]);
        
        // Check if the result is empty, and if so, set counted to 0
        if (empty($countRekomendasiBantuanPendidikanSelesai)) {
            $countRekomendasiBantuanPendidikanSelesai[0] = ['counted' => 0];
        }
    
        $countRekomendasiBantuanPendidikanDraft = DB::select("
            SELECT COUNT(rb.status_alur_bantuan_pendidikans) AS counted
            FROM rekomendasi_terdaftar_dtks AS rb
            INNER JOIN indonesia_villages AS iv ON rb.id_kelurahan_sudtks = iv.code
            WHERE rb.status_alur_bantuan_pendidikans = 'Draft'
            GROUP BY rb.id_kelurahan_sudtks;
        ");
    
        // Check if the result is empty, and if so, set counted to 0
        if (empty($countRekomendasiBantuanPendidikanDraft)) {
            $countRekomendasiBantuanPendidikanDraft[0] = ['counted' => 0];
        }
    
        $result = [
            'countRekomendasiBantuanPendidikanSelesai' => $countRekomendasiBantuanPendidikanSelesai,
            'countRekomendasiBantuanPendidikanTeruskan' => $countRekomendasiBantuanPendidikanTeruskan,
            'countRekomendasiBantuanPendidikan' => $countRekomendasiBantuanPendidikan,
            'countRekomendasiBantuanPendidikanDraft' => $countRekomendasiBantuanPendidikanDraft,
        ];
    
        // Kembalikan respons JSON yang berisi hasil kedua query
        return response()->json($result);
    }
    /**
     * Display a listing of the rekomendasi_terdaftar_dtks.
     */
    public function index(Request $request)
    {
        $rekomendasiTerdaftarDtks = $this->rekomendasiTerdaftarDtksRepository->paginate(10);

        return view('rekomendasi_terdaftar_dtks.index')
            ->with('rekomendasiTerdaftarDtks', $rekomendasiTerdaftarDtks);
    }
    public function FilePbb($id)
    {
        // $adminduk = rekomendasi_keringanan_pbb::find($id);
        
        $adminduk =  DB::table('rekomendasi_terdaftar_dtks as w')->select(
            'w.*',
            'prov.name_prov',
            'kota.name_cities',
            'kecamatan.name_districts',
            'b.name_village',
        )
            ->leftjoin('indonesia_provinces as prov', 'prov.code', '=', 'w.id_provinsi_sudtks')
            ->leftjoin('indonesia_cities as kota', 'kota.code', '=', 'w.id_kabkot_sudtks')
            ->leftjoin('indonesia_districts as kecamatan', 'kecamatan.code', '=', 'w.id_kecamatan_sudtks')
            ->leftjoin('indonesia_villages as b', 'b.code', '=', 'w.id_kelurahan_sudtks')
            ->where('w.id', $id)
            ->first();
        // dd($adminduk);
        $getIdDtks = DB::table('rekomendasi_terdaftar_dtks as w')->select(
            'dtks.Id_DTKS',
            'dtks.Bansos_Bpnt',
            'dtks.Bansos_Pkh',
            'dtks.Pbi_Jni'
        )
            ->leftjoin('dtks', 'dtks.Nik', '=', 'w.nik_sudtks')
            // ->where('status_wilayah', '1')
            ->where('dtks.Nik', $adminduk->nik_sudtks)->first();
        
        
        if ($getIdDtks == null) {
            // dd($getIdDtks);
            $getIdDtks = '-';
        } else {
            // dd($getIdDtks);
            $getIdDtks = DB::table('rekomendasi_terdaftar_dtks as w')->select(
                'dtks.Id_DTKS',
                'dtks.Bansos_Bpnt',
                'dtks.Bansos_Pkh',
                'dtks.Pbi_Jni'
            )
                ->leftjoin('dtks', 'dtks.Nik', '=', 'w.nik_sudtks')
                // ->where('status_wilayah', '1')
                ->where('dtks.Nik', $adminduk->nik_sudtks)->first();
        }

       $date = Carbon::parse($adminduk->tgl_lahir_sudtks)->locale('id');

       $date->settings(['formatFunction' => 'translatedFormat']);

       $tanggal = $date->format('j F Y ');


       // dd($tanggal);
        $pdf = PDF::loadHtml(view('rekomendasi_terdaftar_dtks.file_permohonan',compact('getIdDtks','adminduk','tanggal')));
        $pdf->setPaper('F4', 'portrait');
        $filename = 'File Permohonan' . $adminduk->nama_sudtks . '.pdf';
        return $pdf->stream($filename);
    }
    /**
     * Show the form for creating a new rekomendasi_terdaftar_dtks.
     */
    public function create()
    {

        
        $v = rekomendasi_terdaftar_dtks::latest()->first();
        // dd($v);  
        $kecamatans = DB::table('indonesia_districts')->where('city_code', '3273')->get();
        $userid = Auth::user()->id;
        $wilayah = DB::table('wilayahs as w')->select(
            'w.*',
            'b.*',
            'prov.*',
            'kota.*',
            'kecamatan.code as kecamatan_code',
            'kecamatan.name_districts as name_districts',
            'b.name_village as name_village',
            'b.code as kelurahan_code',

        )
            ->leftjoin('indonesia_provinces as prov', 'prov.code', '=', 'w.province_id')
            ->leftjoin('indonesia_cities as kota', 'kota.code', '=', 'w.kota_id')
            ->leftjoin('indonesia_districts as kecamatan', 'kecamatan.code', '=', 'w.kecamatan_id')
            ->leftjoin('indonesia_villages as b', 'b.code', '=', 'w.kelurahan_id')
            ->where('status_wilayah', '1')
            ->where('w.createdby', $userid)->first();
        $user = Auth::user()->id;
        $checkuserrole = DB::table('model_has_roles')
        ->leftjoin('roles', 'roles.id', '=', 'model_has_roles.role_id')
        ->where('model_id', '=', $user)
        ->first();

        // $alur = DB::table('alur')
        //     ->where('name', 'Draft')
        //     // ->where('name', 'supervisor')
        //     ->orWhere('name', 'Teruskan')
        //     ->get();

        //ALUR
        $user = Auth::user();
        $roles = $user->roles()->pluck('name');

        if ($roles->contains('Front Office Kelurahan')) {
            // Jika user memiliki role 'FO-Kota', maka tampilkan alur dengan nama 'Draft' dan 'Teruskan'
            $alur = DB::table('alur')
                ->whereIn('name', ['Draft', 'Teruskan'])
                ->get();
        }else if ($roles->contains('fasilitator')) {
            // Jika user memiliki role 'FO-Kota', maka tampilkan alur dengan nama 'Draft' dan 'Teruskan'
            $alur = DB::table('alur')
                ->whereIn('name', ['Draft', 'Teruskan'])
                ->get();
        } else if ($roles->contains('Back Ofiice kelurahan') || $roles->contains('kepala bidang') || $roles->contains('supervisor')) {
            // Jika user memiliki role 'BO-Kota' atau 'SekertarisDinas', maka tampilkan alur dengan nama 'Kembalikan', 'Tolak', dan 'Teruskan'
            $alur = DB::table('alur')
                ->whereIn('name', ['Kembalikan', 'Tolak', 'Teruskan'])
                ->get();
        } else if ($roles->contains('Back Ofiice Kota')) {
            // Jika user memiliki role 'Kadus', maka tampilkan alur dengan nama 'Selesai' dan 'Tolak'
            $alur = DB::table('alur')
                ->whereIn('name', ['Selesai', 'Tolak'])
                ->get();
        }else if ($roles->contains('fasilitator')) {
            // Jika user memiliki role 'Kadus', maka tampilkan alur dengan nama 'Selesai' dan 'Tolak'
            $alur = DB::table('alur')
                ->whereIn('name', ['Draft', 'Teruskan'])
                ->get();
        } else {
            // Jika user tidak memiliki role yang sesuai, maka tampilkan alur kosong
            $alur = collect();
        }
        $user = Auth::user();
        $roles = $user->roles()->pluck('name');
        if ($roles->contains('Front Office Kelurahan')) {
            $roleid = DB::table('roles')
                ->where('name', 'Back Ofiice Kota')
                // ->orwhere('name','supervisor')
                ->get();
        } else if ($roles->contains('Front Office kota')) {
            $roleid = DB::table('roles')
                ->where('name', ['Back Ofiice Kota', 'Front Ofiice Kelurahan'])
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
        return view('rekomendasi_terdaftar_dtks.create', compact('kecamatans', 'wilayah', 'roleid', 'checkuserrole', 'alur'));
        // return view('rekomendasi_sudtks.create');
    }

    /**
     * Store a newly created rekomendasi_terdaftar_dtks in storage.
     */

     public function cekIdSDTKS(Request $request, $Nik)
     {
         $found = false;
         $table2 = DB::table('dtks')->where('nik_sudtks', $Nik)->first();
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

    public function store(Request $request)
    {
        
        $request->validate([
            // 'status_alur_sudtks' => 'required', // Tambahkan validasi untuk status_alur_sudtks
            'status_dtks_sudtks' => 'required', // Tambahkan validasi untuk status_dtks_sudtks
            // 'file_ktp_terlapor_sudtks' => 'required|file|mimes:jpeg,png,pdf|max:2048',
            // 'file_kk_terlapor_sudtks' => 'required|file|mimes:jpeg,png,pdf|max:2048',
            // 'file_keterangan_dtks_sudtks' => 'required|file|mimes:jpeg,png,pdf|max:2048',
            // 'file_pendukung_sudtks' => 'required|file|mimes:jpeg,png,pdf|max:2048',
            'id_provinsi_sudtks' => 'required',
            'id_kabkot_sudtks' => 'required',
            'id_kecamatan_sudtks' => 'required',
            'id_kelurahan_sudtks' => 'required',
            'jenis_pelapor_sudtks' => 'required',
            'ada_nik_sudtks' => 'required',
            'nik_sudtks' => 'required_if:ada_nik_sudtks,true', // Hanya valid jika ada_nik_sudtks bernilai true
            'no_kk_sudtks' => 'required',
            'nama_sudtks' => 'required',
            'tgl_lahir_sudtks' => 'required|date',
            'tempat_lahir_sudtks' => 'required',
            'jenis_kelamin_sudtks' => 'required|in:Laki-Laki,Perempuan',
            'telp_sudtks' => 'required',
            'alamat_sudtks' => 'required',
            'catatan_sudtks' => 'nullable',
            'tujuan_sudtks' => 'required',
            'status_aksi_sudtks' => 'required',
            'petugas_sudtks' => 'required',
            // Tambahkan validasi untuk field lainnya sesuai kebutuhan
        ]);

        
        if ($request->get('status_alur_sudtks') != 'Draft') {
            // jika status_alur_sudtks sama dengan Draft akan nmasuk kondisi sini
            if ($request->get('status_dtks_sudtks') == 'Terdaftar') {
                // jika status_dtks_sudtks sama dengan terdaftar akan nmasuk kondisi sini
                $data = new rekomendasi_terdaftar_dtks();
                $files = [
                    'file_ktp_terlapor_sudtks' => 'suratdtks/ktp/',
                    'file_kk_terlapor_sudtks' => 'suratdtks/kk/',
                    'file_keterangan_dtks_sudtks' => 'suratdtks/strukturorganisasi/',
                    'file_pendukung_sudtks' => 'suratdtks/wajibpajak/'
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


                // $data->id_alur = $request->get('id_alur_sudtks');
                $data->no_pendaftaran_sudtks = mt_rand(100, 1000);
                $data->id_provinsi_sudtks = $request->get('id_provinsi_sudtks');
                $data->id_kabkot_sudtks = $request->get('id_kabkot_sudtks');
                $data->id_kecamatan_sudtks = $request->get('id_kecamatan_sudtks');
                $data->id_kelurahan_sudtks = $request->get('id_kelurahan_sudtks');
                $data->jenis_pelapor_sudtks = $request->get('jenis_pelapor_sudtks');
                $data->ada_nik_sudtks = $request->get('ada_nik_sudtks');
                $data->nik_sudtks = $request->get('nik_sudtks');
                $data->no_kk_sudtks = $request->get('no_kk_sudtks');
                // $data->no_kis = $request->get('no_kis');
                $data->nama_sudtks = $request->get('nama_sudtks');
                $data->tgl_lahir_sudtks = $request->get('tgl_lahir_sudtks');
                $data->tempat_lahir_sudtks = $request->get('tempat_lahir_sudtks');
                $data->jenis_kelamin_sudtks = $request->get('jenis_kelamin_sudtks');
                $data->telp_sudtks = $request->get('telp_sudtks');
                $data->alamat_sudtks = $request->get('alamat_sudtks');
                $data['catatan_sudtks']  = $request->get('catatan_sudtks');
                $data->status_dtks_sudtks = $request->get('status_dtks_sudtks');
                $data->tujuan_sudtks = $request->get('tujuan_sudtks');
                $data->status_aksi_sudtks = $request->get('status_aksi_sudtks');
                $data->petugas_sudtks = $request->get('petugas_sudtks');
                $data->createdby_sudtks = Auth::user()->id;
                $data->updatedby_sudtks = Auth::user()->id;
             
                $data->save();

                $logpengaduan = new log_sudtks();
                $logpengaduan['id_trx_sudtks'] = $data->id;
                $logpengaduan['id_alur_sudtks'] = $request->get('status_aksi_sudtks');
                $logpengaduan['petugas_sudtks'] = $request->get('petugas_sudtks');
                $logpengaduan['catatan_sudtks']  = $request->get('catatan_sudtks');
                $logpengaduan['draft_rekomendasi_sudtks'] = $request->get('file_pendukung');
                $logpengaduan['tujuan_sudtks'] = $request->get('tujuan_sudtks');
                $logpengaduan['created_by_sudtks'] = Auth::user()->id;
                $logpengaduan['updated_by_sudtks'] = Auth::user()->id;

                $logpengaduan->save();
                // dd($request->get('jenis_pelapor_sudtks'));
                if($request->get('jenis_pelapor_sudtks') == 'Orang Lain'){
                    $pelapor = new pelapor();
                    $pelapor['id_menu'] = '06';
                    $pelapor['id_form'] = $data->id;
                    $pelapor['jenis_peelaporan'] = $request->get('jenis_peelaporan');
                    $pelapor['nama_pelapor']  = $request->get('nama_pelapor');
                    $pelapor['ada_nik_pelapor'] = $request->get('ada_nik_pelapor');
                    $pelapor['nik_pelapor'] = $request->get('nik_pelapor');
                    $pelapor['status_dtks_pelapor'] = $request->get('status_dtks_pelapor');
                    $pelapor['tempat_lahir_pelapor'] = $request->get('tempat_lahir_pelapor');
                    $pelapor['tanggal_lahir_pelapor'] = $request->get('tanggal_lahir_pelapor');
                    $pelapor['jenis_kelamin'] = $request->get('jenis_kelamin');
                    $pelapor['nama_pelapor']  = $request->get('nama_pelapor');
                    $pelapor['telepon_pelapor'] = $request->get('telepon_pelapor');
                    $pelapor['alamat_pelapor'] = $request->get('alamat_pelapor');
                    $pelapor['createdby_pelapor'] = Auth::user()->id;
                    $pelapor['updatedby_pelapor'] = Auth::user()->id;

                    $pelapor->save();
                }else{
                    $pelapor = new pelapor();
                    $pelapor['id_menu'] = '06';
                    $pelapor['id_form'] = $data->id;
                    $pelapor['jenis_peelaporan'] = $request->get('jenis_pelapor_sudtks');
                    $pelapor['nama_pelapor']  =  $request->get('nama_sudtks');
                    $pelapor['ada_nik_pelapor'] = $request->get('ada_nik_sudtks');
                    $pelapor['nik_pelapor'] = $request->get('nik_sudtks');
                    $pelapor['status_dtks_pelapor'] = $request->get('status_dtks_sudtks');
                    $pelapor['tempat_lahir_pelapor'] = $request->get('tempat_lahir_sudtks');
                    $pelapor['tanggal_lahir_pelapor'] = $request->get('tgl_lahir_sudtks');
                    $pelapor['jenis_kelamin'] = $request->get('jenis_kelamin_sudtks');
                    $pelapor['nama_pelapor']  = $request->get('nama_sudtks');
                    $pelapor['telepon_pelapor'] = $request->get('telp_sudtks');
                    $pelapor['alamat_pelapor'] = $request->get('alamat_sudtks');
                    $pelapor['createdby_pelapor'] = Auth::user()->id;
                    $pelapor['updatedby_pelapor'] = Auth::user()->id;
                    // dd($pelapor->id_form);

                    $pelapor->save();
                }
                return redirect('rekomendasi_terdaftar_dtks')->withSuccess('Data Rekomendasi Berhasil Ditambahkan');
            } else {
                // jika status_dtks_sudtks sama dengan terdaftar akan nmasuk kondisi sini
                $cek = Prelist::where('nik', '=', $request->get('nik_sudtks'))->exists();
                if (!$cek) {
                    // jika nik tidak ada nik akan masuk ke sini
                    $data = new Prelist;
                    $data['id_provinsi'] = $request->get('id_provinsi_sudtks');
                    $data['id_kabkot'] = $request->get('id_kabkot_sudtks');
                    $data['id_kecamatan'] = $request->get('id_kecamatan_sudtks');
                    $data['id_kelurahan'] = $request->get('id_kelurahan_sudtks');
                    $data['nik'] = $request->get('nik_sudtks');
                    $data['no_kk'] = $request->get('no_kk_sudtks');
                    // $data['no_kis'] = $request->get('no_kis_sudtks');
                    $data['nama'] = $request->get('nama_sudtks');
                    $data['tgl_lahir'] = $request->get('tgl_lahir_sudtks');
                    // $data['alamat'] = $request->get('alamat_sudtks');
                    $data['telp'] = $request->get('telp_sudtks');
                    // $data['email'] = $request->get('email_sudtks');
                    $data['status_data'] = 'prelistdtks';

                    $data->save();
                    $data = new rekomendasi_terdaftar_dtks();
                    $files = [
                        'file_ktp_terlapor_sudtks' => 'suratdtks/ktp/',
                        'file_kk_terlapor_sudtks' => 'suratdtks/kk/',
                        'file_keterangan_dtks_sudtks' => 'suratdtks/strukturorganisasi/',
                        'file_pendukung_sudtks' => 'suratdtks/wajibpajak/'
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

                    // $data->id_alur = $request->get('id_alur_sudtks');
                    $data->no_pendaftaran_sudtks = mt_rand(100, 1000);
                    $data->id_provinsi_sudtks = $request->get('id_provinsi_sudtks');
                    $data->id_kabkot_sudtks = $request->get('id_kabkot_sudtks');
                    $data->id_kecamatan_sudtks = $request->get('id_kecamatan_sudtks');
                    $data->id_kelurahan_sudtks = $request->get('id_kelurahan_sudtks');
                    $data->jenis_pelapor_sudtks = $request->get('jenis_pelapor_sudtks');
                    $data->ada_nik_sudtks = $request->get('ada_nik_sudtks');
                    $data->nik_sudtks = $request->get('nik_sudtks');
                    $data->no_kk_sudtks = $request->get('no_kk_sudtks');
                    // $data->no_kis = $request->get('no_kis');
                    $data->nama_sudtks = $request->get('nama_sudtks');
                    $data->tgl_lahir_sudtks = $request->get('tgl_lahir_sudtks');
                    $data->tempat_lahir_sudtks = $request->get('tempat_lahir_sudtks');
                    $data->jenis_kelamin_sudtks = $request->get('jenis_kelamin_sudtks');
                    $data->telp_sudtks = $request->get('telp_sudtks');
                    $data->alamat_sudtks = $request->get('alamat_sudtks');
                    $data->status_dtks_sudtks = $request->get('status_dtks_sudtks');
                    $data->tujuan_sudtks = $request->get('tujuan_sudtks');
                    $data->status_aksi_sudtks = $request->get('status_aksi_sudtks');
                    $data->petugas_sudtks = $request->get('petugas_sudtks');
                    $data->createdby_sudtks = Auth::user()->id;
                    $data->updatedby_sudtks = Auth::user()->id;
                    // dd($data);
                    $data->save();
                    $logpengaduan = new log_sudtks();
                    $logpengaduan['id_trx_sudtks'] = $data->id;
                    $logpengaduan['id_alur_sudtks'] = $request->get('status_aksi_sudtks');
                    $logpengaduan['petugas_sudtks'] = $request->get('petugas_sudtks');
                    $logpengaduan['catatan_sudtks']  = $request->get('catatan_sudtks');
                    $logpengaduan['draft_rekomendasi_sudtks'] = $request->get('file_pendukung');
                    $logpengaduan['tujuan_sudtks'] = $request->get('tujuan_sudtks');
                    $logpengaduan['created_by_sudtks'] = Auth::user()->id;
                    $logpengaduan['updated_by_sudtks'] = Auth::user()->id;

                    $logpengaduan->save();
                    if($request->get('jenis_pelapor_sudtks') == 'Orang Lain'){
                        $pelapor = new pelapor();
                        
                        $pelapor['id_menu'] = '06   ';
                        $pelapor['id_form'] = $data->id;
                        $pelapor['jenis_peelaporan'] = $request->get('jenis_peelaporan');
                        $pelapor['nama_pelapor']  = $request->get('nama_pelapor');
                        $pelapor['ada_nik_pelapor'] = $request->get('ada_nik_pelapor');
                        $pelapor['nik_pelapor'] = $request->get('nik_pelapor');
                        $pelapor['status_dtks_pelapor'] = $request->get('status_dtks_pelapor');
                        $pelapor['tempat_lahir_pelapor'] = $request->get('tempat_lahir_pelapor');
                        $pelapor['tanggal_lahir_pelapor'] = $request->get('tanggal_lahir_pelapor');
                        $pelapor['jenis_kelamin'] = $request->get('jenis_kelamin');
                        $pelapor['nama_pelapor']  = $request->get('nama_pelapor');
                        $pelapor['telepon_pelapor'] = $request->get('telepon_pelapor');
                        $pelapor['alamat_pelapor'] = $request->get('alamat_pelapor');
                        $pelapor['createdby_pelapor'] = Auth::user()->id;
                        $pelapor['updatedby_pelapor'] = Auth::user()->id;
    
                        $pelapor->save();
                    }else{
                        $pelapor = new pelapor();
                        $pelapor['id_menu'] = '06';
                        $pelapor['id_form'] = $data->id;
                        $pelapor['jenis_peelaporan'] = $request->get('jenis_pelapor_sudtks');
                        $pelapor['nama_pelapor']  =  $request->get('nama_sudtks');
                        $pelapor['ada_nik_pelapor'] = $request->get('ada_nik_sudtks');
                        $pelapor['nik_pelapor'] = $request->get('nik_sudtks');
                        $pelapor['status_dtks_pelapor'] = $request->get('status_dtks_sudtks');
                        $pelapor['tempat_lahir_pelapor'] = $request->get('tempat_lahir_sudtks');
                        $pelapor['tanggal_lahir_pelapor'] = $request->get('tgl_lahir_sudtks');
                        $pelapor['jenis_kelamin'] = $request->get('jenis_kelamin_sudtks');
                        $pelapor['nama_pelapor']  = $request->get('nama_sudtks');
                        $pelapor['telepon_pelapor'] = $request->get('telp_sudtks');
                        $pelapor['alamat_pelapor'] = $request->get('alamat_sudtks');
                        $pelapor['createdby_pelapor'] = Auth::user()->id;
                        $pelapor['updatedby_pelapor'] = Auth::user()->id;
    
                        $pelapor->save();
                    }
                    return redirect('rekomendasi_terdaftar_dtks')->withSuccess('Data Rekomendasi Berhasil Ditambahkan');
                } else {
                    //jika nik ada di prelist akan masuk ke sini
                    $data = new rekomendasi_terdaftar_dtks();
                    $files = [
                        'file_ktp_terlapor_sudtks' => 'suratdtks/ktp/',
                        'file_kk_terlapor_sudtks' => 'suratdtks/kk/',
                        'file_keterangan_dtks_sudtks' => 'suratdtks/strukturorganisasi/',
                        'file_pendukung_sudtks' => 'suratdtks/wajibpajak/'
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

                    // $data->id_alur = $request->get('id_alur_sudtks');
                    $data->no_pendaftaran_sudtks = mt_rand(100, 1000);
                    $data->id_provinsi_sudtks = $request->get('id_provinsi_sudtks');
                    $data->id_kabkot_sudtks = $request->get('id_kabkot_sudtks');
                    $data->id_kecamatan_sudtks = $request->get('id_kecamatan_sudtks');
                    $data->id_kelurahan_sudtks = $request->get('id_kelurahan_sudtks');
                    $data->jenis_pelapor_sudtks = $request->get('jenis_pelapor_sudtks');
                    $data->ada_nik_sudtks = $request->get('ada_nik_sudtks');
                    $data->nik_sudtks = $request->get('nik_sudtks');
                    $data->no_kk_sudtks = $request->get('no_kk_sudtks');
                    // $data->no_kis = $request->get('no_kis');
                    $data->nama_sudtks = $request->get('nama_sudtks');
                    $data->tgl_lahir_sudtks = $request->get('tgl_lahir_sudtks');
                    $data->tempat_lahir_sudtks = $request->get('tempat_lahir_sudtks');
                    $data->jenis_kelamin_sudtks = $request->get('jenis_kelamin_sudtks');
                    $data->telp_sudtks = $request->get('telp_sudtks');
                    $data->alamat_sudtks = $request->get('alamat_sudtks');
                    $data->status_dtks_sudtks = $request->get('status_dtks_sudtks');
                    $data->tujuan_sudtks = $request->get('tujuan_sudtks');
                    $data->status_aksi_sudtks = $request->get('status_aksi_sudtks');
                    $data->petugas_sudtks = $request->get('petugas_sudtks');
                    $data->createdby_sudtks = Auth::user()->id;
                    $data->updatedby_sudtks = Auth::user()->id;
                    // dd($data);
                    $data->save();
                    $logpengaduan = new log_sudtks();
                    $logpengaduan['id_trx_sudtks'] = $data->id;
                    $logpengaduan['id_alur_sudtks'] = $request->get('status_aksi_sudtks');
                    $logpengaduan['petugas_sudtks'] = $request->get('petugas_sudtks');
                    $logpengaduan['catatan_sudtks']  = $request->get('catatan_sudtks');
                    $logpengaduan['file_pendukung_sudtks'] = $request->get('file_pendukung_sudtks');
                    $logpengaduan['tujuan_sudtks'] = $request->get('tujuan_sudtks');
                    $logpengaduan['created_by_sudtks'] = Auth::user()->id;
                    $logpengaduan['updated_by_sudtks'] = Auth::user()->id;

                    $logpengaduan->save();
                    return redirect('rekomendasi_terdaftar_dtks')->withSuccess('Data Rekomendasi Berhasil Ditambahkan');
                    if($request->get('jenis_pelapor_sudtks') == 'Orang Lain'){
                        $pelapor = new pelapor();
                        
                        $pelapor['id_menu'] = '06   ';
                        $pelapor['id_form'] = $data->id;
                        $pelapor['jenis_peelaporan'] = $request->get('jenis_peelaporan');
                        $pelapor['nama_pelapor']  = $request->get('nama_pelapor');
                        $pelapor['ada_nik_pelapor'] = $request->get('ada_nik_pelapor');
                        $pelapor['nik_pelapor'] = $request->get('nik_pelapor');
                        $pelapor['status_dtks_pelapor'] = $request->get('status_dtks_pelapor');
                        $pelapor['tempat_lahir_pelapor'] = $request->get('tempat_lahir_pelapor');
                        $pelapor['tanggal_lahir_pelapor'] = $request->get('tanggal_lahir_pelapor');
                        $pelapor['jenis_kelamin'] = $request->get('jenis_kelamin');
                        $pelapor['nama_pelapor']  = $request->get('nama_pelapor');
                        $pelapor['telepon_pelapor'] = $request->get('telepon_pelapor');
                        $pelapor['alamat_pelapor'] = $request->get('alamat_pelapor');
                        $pelapor['createdby_pelapor'] = Auth::user()->id;
                        $pelapor['updatedby_pelapor'] = Auth::user()->id;
    
                        $pelapor->save();
                    }else{
                        $pelapor = new pelapor();
                        $pelapor['id_menu'] = '06';
                        $pelapor['id_form'] = $data->id;
                        $pelapor['jenis_peelaporan'] = $request->get('jenis_pelapor_sudtks');
                        $pelapor['nama_pelapor']  =  $request->get('nama_sudtks');
                        $pelapor['ada_nik_pelapor'] = $request->get('ada_nik_sudtks');
                        $pelapor['nik_pelapor'] = $request->get('nik_sudtks');
                        $pelapor['status_dtks_pelapor'] = $request->get('status_dtks_sudtks');
                        $pelapor['tempat_lahir_pelapor'] = $request->get('tempat_lahir_sudtks');
                        $pelapor['tanggal_lahir_pelapor'] = $request->get('tgl_lahir_sudtks');
                        $pelapor['jenis_kelamin'] = $request->get('jenis_kelamin_sudtks');
                        $pelapor['nama_pelapor']  = $request->get('nama_sudtks');
                        $pelapor['telepon_pelapor'] = $request->get('telp_sudtks');
                        $pelapor['alamat_pelapor'] = $request->get('alamat_sudtks');
                        $pelapor['createdby_pelapor'] = Auth::user()->id;
                        $pelapor['updatedby_pelapor'] = Auth::user()->id;
    
                        $pelapor->save();
                    }
                }
            }
        } else {
            //jika status draft adalah ini akan masuk ke sini
            $data = new rekomendasi_terdaftar_dtks();
            $files = [
                'file_ktp_terlapor_sudtks' => 'suratdtks/ktp/',
                'file_kk_terlapor_sudtks' => 'suratdtks/kk/',
                'file_keterangan_dtks_sudtks' => 'suratdtks/strukturorganisasi/',
                'file_pendukung_sudtks' => 'suratdtks/wajibpajak/'
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

            // $data->id_alur = $request->get('id_alur_sudtks');
            $data->no_pendaftaran_sudtks = mt_rand(100, 1000);
            $data->id_provinsi_sudtks = $request->get('id_provinsi_sudtks');
            $data->id_kabkot_sudtks = $request->get('id_kabkot_sudtks');
            $data->id_kecamatan_sudtks = $request->get('id_kecamatan_sudtks');
            $data->id_kelurahan_sudtks = $request->get('id_kelurahan_sudtks');
            $data->jenis_pelapor_sudtks = $request->get('jenis_pelapor_sudtks');
            $data->ada_nik_sudtks = $request->get('ada_nik_sudtks');
            $data->nik_sudtks = $request->get('nik_sudtks');
            $data->no_kk_sudtks = $request->get('no_kk_sudtks');
            // $data->no_kis = $request->get('no_kis');
            $data->nama_sudtks = $request->get('nama_sudtks');
            $data->tgl_lahir_sudtks = $request->get('tgl_lahir_sudtks');
            $data->tempat_lahir_sudtks = $request->get('tempat_lahir_sudtks');
            $data->jenis_kelamin_sudtks = $request->get('jenis_kelamin_sudtks');
            $data->telp_sudtks = $request->get('telp_sudtks');
            $data->status_dtks_sudtks = $request->get('status_dtks_sudtks');
            $data->tujuan_sudtks = $request->get('tujuan_sudtks');
            $data->status_aksi_sudtks = $request->get('status_aksi_sudtks');
            $data->petugas_sudtks = $request->get('petugas_sudtks');
            $data->createdby_sudtks = Auth::user()->id;
            $data->updatedby_sudtks = Auth::user()->id;
            // dd($data);
            $data->save();
            if($request->get('jenis_pelapor_sudtks') == 'Orang Lain'){
                $pelapor = new pelapor();
                
                $pelapor['id_menu'] = '06   ';
                $pelapor['id_form'] = $data->id;
                $pelapor['jenis_peelaporan'] = $request->get('jenis_peelaporan');
                $pelapor['nama_pelapor']  = $request->get('nama_pelapor');
                $pelapor['ada_nik_pelapor'] = $request->get('ada_nik_pelapor');
                $pelapor['nik_pelapor'] = $request->get('nik_pelapor');
                $pelapor['status_dtks_pelapor'] = $request->get('status_dtks_pelapor');
                $pelapor['tempat_lahir_pelapor'] = $request->get('tempat_lahir_pelapor');
                $pelapor['tanggal_lahir_pelapor'] = $request->get('tanggal_lahir_pelapor');
                $pelapor['jenis_kelamin'] = $request->get('jenis_kelamin');
                $pelapor['nama_pelapor']  = $request->get('nama_pelapor');
                $pelapor['telepon_pelapor'] = $request->get('telepon_pelapor');
                $pelapor['alamat_pelapor'] = $request->get('alamat_pelapor');
                $pelapor['createdby_pelapor'] = Auth::user()->id;
                $pelapor['updatedby_pelapor'] = Auth::user()->id;

                $pelapor->save();
            }else{
                $pelapor = new pelapor();
                $pelapor['id_menu'] = '06';
                $pelapor['id_form'] = $data->id;
                $pelapor['jenis_peelaporan'] = $request->get('jenis_pelapor_sudtks');
                $pelapor['nama_pelapor']  =  $request->get('nama_sudtks');
                $pelapor['ada_nik_pelapor'] = $request->get('ada_nik_sudtks');
                $pelapor['nik_pelapor'] = $request->get('nik_sudtks');
                $pelapor['status_dtks_pelapor'] = $request->get('status_dtks_sudtks');
                $pelapor['tempat_lahir_pelapor'] = $request->get('tempat_lahir_sudtks');
                $pelapor['tanggal_lahir_pelapor'] = $request->get('tgl_lahir_sudtks');
                $pelapor['jenis_kelamin'] = $request->get('jenis_kelamin_sudtks');
                $pelapor['nama_pelapor']  = $request->get('nama_sudtks');
                $pelapor['telepon_pelapor'] = $request->get('telp_sudtks');
                $pelapor['alamat_pelapor'] = $request->get('alamat_sudtks');
                $pelapor['createdby_pelapor'] = Auth::user()->id;
                $pelapor['updatedby_pelapor'] = Auth::user()->id;
                dd($pelapor);
                $pelapor->save();
            }
            return redirect('rekomendasi_terdaftar_dtks')->withWarning('NIK Tidak Tersedia Data Disimpan sebagai draft');
        }
    }

    /**
     * Display the specified rekomendasi_terdaftar_dtks.
     */
    public function show($id)
    {
        $userid = Auth::user()->id;
        $rekomendasiTerdaftarDTKSPelapor = DB::table('rekomendasi_terdaftar_dtks')
            ->join('pelapor', function ($join) use ($id) {
                $join->on('rekomendasi_terdaftar_dtks.id', '=', 'pelapor.id_form')
                    ->where('rekomendasi_terdaftar_dtks.id', '=', $id);
            })
            ->select('rekomendasi_terdaftar_dtks.*', 'pelapor.*')
            ->where('pelapor.id_menu', '06')
            ->where('pelapor.id_form', $id)
            ->first();
        // dd($rekomendasiTerdaftarDTKSPelapor);
        
        $rekomendasiTerdaftarDtks = DB::table('rekomendasi_terdaftar_dtks as w')->select(
            'w.*',
            'b.name_village',
            'prov.name_prov',
            'kota.name_cities',
            'kecamatan.name_districts',
            'roles.name as name_roles',
            'users.name',
            // 'w.status_wilayah',
        )
        ->leftjoin('users', 'users.id', '=', 'w.petugas_sudtks')
        ->leftjoin('roles', 'roles.id', '=', 'w.tujuan_sudtks')
        ->leftjoin('indonesia_provinces as prov', 'prov.code', '=', 'w.id_provinsi_sudtks')
        ->leftjoin('indonesia_cities as kota', 'kota.code', '=', 'w.id_kabkot_sudtks')
        ->leftjoin('indonesia_districts as kecamatan', 'kecamatan.code', '=', 'w.id_kecamatan_sudtks')
        ->leftjoin('indonesia_villages as b', 'b.code', '=', 'w.id_kelurahan_sudtks')
        ->where('w.id', $id)->first();
        $log_sudtks = DB::table('log_sudtks as w')->select(
            'w.*',
            'rls.name as name_update',
            'usr.name',
            'roles.name as name_roles',

        )
            ->leftjoin('roles', 'roles.id', '=', 'w.tujuan_sudtks')
            ->leftjoin('users as rls', 'rls.id', '=', 'w.updated_by_sudtks')
            ->leftjoin('users as usr', 'usr.id', '=', 'w.created_by_sudtks')
            ->where('w.id_trx_sudtks', $id)->get();

        return view('rekomendasi_terdaftar_dtks.show', compact('rekomendasiTerdaftarDtks','rekomendasiTerdaftarDTKSPelapor', 'log_sudtks'));
    }

    /**
     * Show the form for editing the specified rekomendasi_terdaftar_dtks.
     */
    public function edit($id)
    {
        $userid = Auth::user()->id;
        $getAuth = DB::table('model_has_roles')
        ->leftJoin('users', 'users.id', '=', 'model_has_roles.model_id')
        ->leftJoin('roles', 'roles.id', '=', 'model_has_roles.role_id')
        ->where('model_id', $userid)
        ->get();
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
            ->leftjoin('rekomendasi_terdaftar_dtks', 'rekomendasi_terdaftar_dtks.createdby_sudtks', '=', 'model_has_roles.model_id')
            ->where('rekomendasi_terdaftar_dtks.id', '=', $id)
            // ->where('status_aksi', '=', 'Draft')
            // ->orwhere('status_aksi', '=', 'Teruskan')
            ->get();
        // dd($checkroles2);
        //Tujuan
        $createdby = DB::table('rekomendasi_terdaftar_dtks')
            ->join('users', 'rekomendasi_terdaftar_dtks.createdby_sudtks', '=', 'users.name')
            ->join('model_has_roles', 'model_has_roles.model_id', '=', 'users.id')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->select('rekomendasi_terdaftar_dtks.id', 'rekomendasi_terdaftar_dtks.createdby_sudtks', 'roles.name')
            ->get();

        // $rekomendasiTerdaftarDtks = rekomendasi_terdaftar_dtks::where('createdby_sudtks', $userid)->get();
        $getdata = DB::table('model_has_roles')
            ->leftjoin('rekomendasi_terdaftar_dtks as b', 'b.tujuan_sudtks', '=', 'model_has_roles.role_id')
            ->where('b.id', $id)
            ->get();
        //alur
        $user = Auth::user();
        $roles = $user->roles()->pluck('name');

        if ($roles->contains('Front Office Kelurahan') || $roles->contains('fasilitator') ) {
            // Jika user memiliki role 'FO-Kota', maka tampilkan alur dengan nama 'Draft' dan 'Teruskan'
            $alur = DB::table('alur')
                ->whereIn('name', ['Draft', 'Teruskan'])
                ->get();
        } elseif ($roles->contains('Back Ofiice kelurahan')) {
            $alur = DB::table('alur')
                ->wherein('name', ['Teruskan', 'Kembalikan', 'Selesai', 'Tolak'])
                ->get();
        } else if ($roles->contains('kepala bidang') || $roles->contains('supervisor')) {
            // Jika user memiliki role 'BO-Kota' atau 'SekertarisDinas', maka tampilkan alur dengan nama 'Kembalikan', 'Tolak', dan 'Teruskan'
            $alur = DB::table('alur')
                ->whereIn('name', ['Kembalikan', 'Tolak', 'Teruskan','selesai'])
                ->get();
        } else if ($roles->contains('Back Ofiice Kota')) {
            // Jika user memiliki role 'Kadus', maka tampilkan alur dengan nama 'Selesai' dan 'Tolak'
            $alur = DB::table('alur')
                ->whereIn('name', ['Kembalikan', 'Tolak', 'Selesai'])
                ->get();
            // dd($alur);
        } else {
            // Jika user tidak memiliki role yang sesuai, maka tampilkan alur kosong
            $alur = collect();
        }


        $user = Auth::user();
        $roles = $user->roles()->pluck('name');

        if ($roles->contains('Front Office Kelurahan')) {
            $roleid = DB::table('roles')
            ->where('name','Back Ofiice Kota')
            // ->where('name', 'supervisor')
            ->orWhere('name', 'supervisor')
            ->get();
        } else if ($roles->contains('Front Office kota')) {
            $roleid = DB::table('roles')
                ->where('name', 'Back Ofiice Kota')
                ->get();
        }else if ($roles->contains('fasilitator')) {
            $roleid = DB::table('roles')
                ->where('name', 'Back Ofiice kelurahan')
                // ->where('name', 'supervisor')
                ->orWhere('name', 'supervisor')
                ->get();
        }
        else if ($roles->contains('supervisor')) {
            $roleid = DB::table('roles')
                ->where('name', 'Back Ofiice Kota')
                ->get();
        }else if ($roles->contains('Back Ofiice kelurahan')) {
            $roleid = DB::table('roles')
                ->where('name', 'Supervisor')
                ->get();
        }else if ($roles->contains('Back Ofiice Kota')) {
            $roleid = DB::table('roles')
                ->where('name', 'kepala bidang')
                ->get();
        }else if ($roles->contains('kepala bidang')) {
            $roleid = DB::table('roles')
                ->where('name', 'Back Ofiice Kota')->get();
            
        }
        $role_id = null;
        $users = DB::table('users as u')
            ->join('model_has_roles as mhr', 'u.id', '=', 'mhr.model_id')
            ->join('roles as r', 'mhr.role_id', '=', 'r.id')
            ->select('u.id', 'u.name', 'u.email', 'r.name as role')
            ->where('mhr.model_type', '=', 'App\Models\User')
            ->where('mhr.role_id', '=', $role_id)
            ->get();

        // $rekomendasiTerdaftarDtks = $this->rekomendasiTerdaftarDtksRepository->find($id);

        $rekomendasiTerdaftarDtks = DB::table('rekomendasi_terdaftar_dtks as w')->select(
            'w.*',
            'rls.name as name_roles',
            'usr.name',
            'prov.name_prov',
            'kota.name_cities',
            'kecamatan.name_districts',
            'b.name_village',
            'p.*'
        )
            ->leftjoin('roles as rls', 'rls.id', '=', 'w.tujuan_sudtks')
            ->leftjoin('users as usr', 'usr.id', '=', 'w.petugas_sudtks')
            ->leftjoin('pelapor as p', 'p.id_form', 'w.id')
            ->leftjoin('indonesia_provinces as prov', 'prov.code', '=', 'w.id_provinsi_sudtks')
            ->leftjoin('indonesia_cities as kota', 'kota.code', '=', 'w.id_kabkot_sudtks')
            ->leftjoin('indonesia_districts as kecamatan', 'kecamatan.code', '=', 'w.id_kecamatan_sudtks')
            ->leftjoin('indonesia_villages as b', 'b.code', '=', 'w.id_kelurahan_sudtks')
            ->where('p.id_menu', '06')
            ->where('w.id', $id)->first();
        // dd($rekomendasiTerdaftarDtks);
        return view('rekomendasi_terdaftar_dtks.edit', compact('getAuth','wilayah', 'rekomendasiTerdaftarDtks', 'roleid', 'getdata', 'alur', 'createdby', 'getUsers'));
    }

    /**
     * Update the specified rekomendasi_terdaftar_dtks in storage.
     */
    public function update($id, Request $request)
    {
        $userid = Auth::user()->id;
        $getdatasudtks = rekomendasi_terdaftar_dtks::where('id', $id)->first();
      
        $pemebuatanDataRekomendasiDtks = DB::table('rekomendasi_terdaftar_dtks as w')
		->join('model_has_roles', 'model_has_roles.model_id', '=', 'w.createdby_sudtks')
		->join('roles as rls', 'rls.id', '=', 'model_has_roles.role_id')

		->leftjoin('users', 'users.id', '=', 'w.createdby_sudtks')
		->select(
					'w.*',
					'rls.name as name_roles',
					// 'usr.name',
					'model_has_roles.*')
		->where('w.id', $id)->first();
        $data = $request->all();
        $files = [
            'file_ktp_terlapor_sudtks',
            'file_kk_terlapor_sudtks',
            'file_keterangan_dtks_sudtks',
            'file_pendukung_sudtks'
        ];
        foreach ($files as $file) {
            if ($request->file($file)) {
                $path = $request->file($file);
                $filename = $file . $path->getClientOriginalName();
                $return = Storage::disk('imagekit')->put($filename, fopen($path->getRealPath(), 'r'));
                $data[$file] = Storage::disk('imagekit')->url($filename);
            } else {
                $data[$file] = $getdatasudtks->$file;
            }
        }
        if ( $request->get('status_aksi_sudtks') == 'Kembalikan' || $request->get('status_aksi_sudtks') == 'Selesai') {
            $data['petugas_sudtks']  = $pemebuatanDataRekomendasiDtks->model_id;
            $data['tujuan_sudtks'] = $pemebuatanDataRekomendasiDtks->role_id;
        }else{
            $data['petugas_sudtks']  = $request->get('petugas_sudtks');
            $data['tujuan_sudtks'] = $request->get('tujuan_sudtks');
        }
        //   dd($data);
        $getdatasudtks->update($data);

        $logpengaduan = new log_sudtks();
        $logpengaduan['id_trx_sudtks'] = $getdatasudtks->id;
        $logpengaduan['id_alur_sudtks'] = $request->get('status_aksi_sudtks');
        
        $logpengaduan['catatan_sudtks']  = $request->get('catatan_sudtks');
        $logpengaduan['file_pendukung_sudtks'] = $request->get('file_pendukung_sudtks');
        if ( $request->get('status_aksi_sudtks') == 'Kembalikan' || $request->get('status_aksi_sudtks') == 'Selesai') {
            $logpengaduan['petugas_sudtks']  = $pemebuatanDataRekomendasiDtks->model_id;
            $logpengaduan['tujuan_sudtks'] = $pemebuatanDataRekomendasiDtks->role_id;
        }else{
            $logpengaduan['petugas_sudtks']  = $request->get('petugas_sudtks');
            $logpengaduan['tujuan_sudtks'] = $request->get('tujuan_sudtks');
        }
        $logpengaduan['created_by_sudtks'] = Auth::user()->id;
        $logpengaduan['updated_by_sudtks'] = Auth::user()->id;
        // dd($logpengaduan);
        $logpengaduan->save();
        $pelapor = pelapor::where('id_menu', '06')->where('id_form', $id)->first();
        if ($pelapor) {
            $dataPelapor = $request->all();
            $dataPelapor['updatedby_pelapor'] = Auth::user()->id;
            $pelapor->update($dataPelapor);
        }
        return redirect('rekomendasi_terdaftar_dtks')->withSuccess('Data Berhasil Diubah');
        // if ($datasudtks->nik_sudtks != null) {

        //     if ($datasudtks->status_dtks_sudtks == 'Terdaftar') {
        //             $files = [
        //                 'file_ktp_terlapor_sudtks' => 'suratdtks/ktp/',
        //                 'file_kk_terlapor_sudtks' => 'suratdtks/kk/',
        //                 'file_keterangan_dtks_sudtks' => 'suratdtks/strukturorganisasi/',
        //                 'file_pendukung_sudtks' => 'suratdtks/wajibpajak/',
        //             ];

        //             foreach ($files as $field => $path) {
        //                 if ($request->file($field)) {
        //                     $file = $request->file($field);
        //                     $nama_file = $path . $file->getClientOriginalName();
        //                     $return = Storage::disk('imagekit')->put($nama_file, fopen($file->getRealPath(), 'r'));
        //                     $datasudtks->{$field} = Storage::disk('imagekit')->url($nama_file);
        //                 } else {
        //                     $datasudtks->{$field} = $datasudtks->{$field};
        //                 }
        //             }
            
        //         $datasudtks['id_provinsi_sudtks'] = $request->get('id_provinsi_sudtks');
        //         $datasudtks['id_kabkot_sudtks'] = $request->get('id_kabkot_sudtks');
        //         $datasudtks['id_kecamatan_sudtks'] = $request->get('id_kecamatan_sudtks');
        //         $datasudtks['id_kelurahan_sudtks'] = $request->get('id_kelurahan_sudtks');
        //         $datasudtks['jenis_pelapor_sudtks'] = $request->get('jenis_pelapor_sudtks');
        //         $datasudtks['ada_nik_sudtks'] = $request->get('ada_nik_sudtks');
        //         $datasudtks['nik_sudtks'] = $request->get('nik_sudtks');
        //         $datasudtks['no_kk_sudtks'] = $request->get('no_kk_sudtks');
        //         $datasudtks['nama_sudtks'] = $request->get('nama_sudtks');
        //         $datasudtks['tgl_lahir_sudtks'] = $request->get('tgl_lahir_sudtks');
        //         $datasudtks['tempat_lahir_sudtks'] = $request->get('tempat_lahir_sudtks');
        //         $datasudtks['status_dtks_sudtks'] = $request->get('status_dtks_sudtks');
        //         $datasudtks['telp_sudtks'] = $request->get('telp_sudtks');
        //         $datasudtks['catatan_sudtks']  = $request->get('catatan_sudtks');
        //         if ( $request->get('status_aksi_sudtks') == 'Kembalikan' || $request->get('status_aksi_sudtks') == 'Selesai') {
        //             $datasudtks['petugas_sudtks']  = $pemebuatanDataRekomendasiDtks->model_id;
        //             $datasudtks['tujuan_sudtks'] = $pemebuatanDataRekomendasiDtks->role_id;
        //         }else{
        //             $datasudtks['petugas_sudtks']  = $request->get('petugas_sudtks');
        //             $datasudtks['tujuan_sudtks'] = $request->get('tujuan_sudtks');
        //         }
        //         // $datasudtks['petugas_sudtks']  = $request->get('petugas_sudtks');
        //         // $datasudtks['tujuan_sudtks'] = $request->get('tujuan_sudtks');
        //         $datasudtks['status_aksi_sudtks'] = $request->get('status_aksi_sudtks');
        //         $datasudtks['validasi_surat'] = $request->get('validasi_surat');
        //         $datasudtks['Nomor_Surat'] = $request->get('Nomor_Surat');
        //         // dd($datasudtks);
        //         $datasudtks->update($datasudtks);
        //         if ($datasudtks->status_aksi == 'Draft') {
        //             $files = [
        //                 'file_ktp_terlapor_sudtks' => 'suratdtks/ktp/',
        //                 'file_kk_terlapor_sudtks' => 'suratdtks/kk/',
        //                 'file_keterangan_dtks_sudtks' => 'suratdtks/strukturorganisasi/',
        //                 'file_pendukung_sudtks' => 'suratdtks/wajibpajak/',
        //             ];

        //             foreach ($files as $field => $path) {
        //                 if ($request->file($field)) {
        //                     $file = $request->file($field);
        //                     $nama_file = $path . $file->getClientOriginalName();
        //                     $return = Storage::disk('imagekit')->put($nama_file, fopen($file->getRealPath(), 'r'));
        //                     $datasudtks->{$field} = Storage::disk('imagekit')->url($nama_file);
        //                 } else {
        //                     $datasudtks->{$field} = $datasudtks->{$field};
        //                 }
        //             }
        //             $datasudtks['id_provinsi_sudtks'] = $request->get('id_provinsi_sudtks');
        //             $datasudtks['id_kabkot_sudtks'] = $request->get('id_kabkot_sudtks');
        //             $datasudtks['id_kecamatan_sudtks'] = $request->get('id_kecamatan_sudtks');
        //             $datasudtks['id_kelurahan_sudtks'] = $request->get('id_kelurahan_sudtks');
        //             $datasudtks['jenis_pelapor_sudtks'] = $request->get('jenis_pelapor_sudtks');
        //             $datasudtks['ada_nik_sudtks'] = $request->get('ada_nik_sudtks');
        //             $datasudtks['nik_sudtks'] = $request->get('nik_sudtks');
        //             $datasudtks['no_kk_sudtks'] = $request->get('no_kk_sudtks');
        //             $datasudtks['nama_sudtks'] = $request->get('nama_sudtks');
        //             $datasudtks['tgl_lahir_sudtks'] = $request->get('tgl_lahir_sudtks');
        //             $datasudtks['tempat_lahir_sudtks'] = $request->get('tempat_lahir_sudtks');
        //             $datasudtks['status_dtks_sudtks'] = $request->get('status_dtks_sudtks');
        //             $datasudtks['telp_sudtks'] = $request->get('telp_sudtks');
        //             $datasudtks['catatan_sudtks']  = $request->get('catatan_sudtks');
        //             if ( $request->get('status_aksi_sudtks') == 'Kembalikan' || $request->get('status_aksi_sudtks') == 'Selesai') {
        //                 $datasudtks['petugas_sudtks']  = $pemebuatanDataRekomendasiDtks->model_id;
        //                 $datasudtks['tujuan_sudtks'] = $pemebuatanDataRekomendasiDtks->role_id;
        //             }else{
        //                 $datasudtks['petugas_sudtks']  = $request->get('petugas_sudtks');
        //                 $datasudtks['tujuan_sudtks'] = $request->get('tujuan_sudtks');
        //             }
        //             // $datasudtks['petugas_sudtks']  = $request->get('petugas_sudtks');
        //             // $datasudtks['tujuan_sudtks'] = $request->get('tujuan_sudtks');
        //             $datasudtks['status_aksi_sudtks'] = $request->get('status_aksi_sudtks');
        //             $datasudtks['validasi_surat'] = $request->get('validasi_surat');
        //             $datasudtks['Nomor_Surat'] = $request->get('Nomor_Surat');
    
        //             // dd($datasudtks);
        //             rekomendasi_terdaftar_dtks::where('id', $id)->update($datasudtks);
        //         }
               
        //     }

        //     $checkuserrole = DB::table('model_has_roles')
        //         ->leftjoin('roles', 'roles.id', '=', 'model_has_roles.role_id')
        //         ->where('model_id', '=', $userid)
        //         ->first();
        //     if ($checkuserrole->name == $checkuserrole->name) {
        //         //   dd($pengaduan);
        //         $logpengaduan = new log_sudtks();
        //         $logpengaduan['id_trx_sudtks'] = $datasudtks->id;
        //         $logpengaduan['id_alur_sudtks'] = $request->get('status_aksi_sudtks');
               
        //         $logpengaduan['catatan_sudtks']  = $request->get('catatan_sudtks');
        //         $logpengaduan['file_pendukung_sudtks'] = $request->get('file_pendukung_sudtks');
        //         if ( $request->get('status_aksi_sudtks') == 'Kembalikan' || $request->get('status_aksi_sudtks') == 'Selesai') {
        //             $logpengaduan['petugas_sudtks']  = $pemebuatanDataRekomendasiDtks->model_id;
        //             $logpengaduan['tujuan_sudtks'] = $pemebuatanDataRekomendasiDtks->role_id;
        //         }else{
        //             $logpengaduan['petugas_sudtks']  = $request->get('petugas_sudtks');
        //             $logpengaduan['tujuan_sudtks'] = $request->get('tujuan_sudtks');
        //         }
        //         $logpengaduan['created_by_sudtks'] = Auth::user()->id;
        //         $logpengaduan['updated_by_sudtks'] = Auth::user()->id;
        //         // dd($logpengaduan);
        //         $logpengaduan->save();


        //         return redirect('rekomendasi_terdaftar_dtks')->withSuccess('Rekomendasi Berhasil Diubah');
        //     } else {

        //         $cek = Prelist::where('nik', '=', $request->get('nik'))->exists();
        //         if ($cek) {
        //             return redirect('rekomendasi_terdaftar_dtks')->withWarning('NIK Sudah Terdaftar Di Prelist');
        //         } else {

        //             $sudtks['id_provinsi'] = $request->get('id_provinsi_sudtks');
        //             $sudtks['id_kabkot'] = $request->get('id_kabkot_sudtks');
        //             $sudtks['id_kecamatan'] = $request->get('id_kecamatan_sudtks');
        //             $sudtks['id_kelurahan'] = $request->get('id_kelurahan_sudtks');
        //             $sudtks['nik'] = $request->get('nik_sudtks');
        //             $sudtks['no_kk'] = $request->get('no_kk_sudtks');
        //             $sudtks['nama'] = $request->get('nama_sudtks');
        //             $sudtks['tgl_lahir'] = $request->get('tgl_lahir_sudtks');;
        //             $sudtks['telp'] = $request->get('telp_sudtks');
        //             // $sudtks['email'] = $request->get('email_sudtks');
        //             // $sudtks['status_data'] = 'prelistdtks';
        //             Prelist::where('id', $id)->update($sudtks);
        //             return redirect('sudtkss')->withSuccess('Data  Berhasil Disimpan Di Prelist');
        //         }
        //     }
        // } else {

        //     $sudtks['id_kabkot_sudtks'] = $request->get('id_kabkot_sudtks');
        //     $sudtks['id_kecamatan_sudtks'] = $request->get('id_kecamatan_sudtks');
        //     $sudtks['id_kelurahan_sudtks'] = $request->get('id_kelurahan_sudtks');
        //     $sudtks['jenis_pelapor_sudtks'] = $request->get('jenis_pelapor_sudtks');
        //     $sudtks['ada_nik_sudtks'] = $request->get('ada_nik_sudtks');
        //     $sudtks['nik_sudtks'] = $request->get('nik_sudtks');
        //     $sudtks['no_kk_sudtks'] = $request->get('no_kk_sudtks');
        //     $sudtks['nama_sudtks'] = $request->get('nama_sudtks');
        //     $sudtks['tgl_lahir_sudtks'] = $request->get('tgl_lahir_sudtks');
        //     $sudtks['tempat_lahir_sudtks'] = $request->get('tempat_lahir_sudtks');
        //     $sudtks['status_dtks_sudtks'] = $request->get('status_dtks_sudtks');
        //     $sudtks['telp_sudtks'] = $request->get('telp_sudtks');
        //     if ( $request->get('status_aksi_sudtks') == 'Kembalikan' || $request->get('status_aksi_sudtks') == 'Selesai') {
        //         $sudtks['petugas_sudtks']  = $pemebuatanDataRekomendasiDtks->model_id;
        //         $sudtks['tujuan_sudtks'] = $pemebuatanDataRekomendasiDtks->role_id;
        //     }else{
        //         $sudtks['petugas_sudtks']  = $request->get('petugas_sudtks');
        //         $sudtks['tujuan_sudtks'] = $request->get('tujuan_sudtks');
        //     }
        //     $sudtks['catatan_sudtks']  = $request->get('catatan_sudtks');
        //     $sudtks['petugas_sudtks']  = $request->get('petugas_sudtks');
        //     $sudtks['tujuan_sudtks'] = $request->get('tujuan_sudtks');
        //     $sudtks['status_aksi_sudtks'] = $request->get('status_aksi_sudtks');
        //     $sudtks['validasi_surat'] = $request->get('validasi_surat');
        //     $sudtks['Nomor_Surat'] = $request->get('Nomor_Surat');
        //     $sudtks['createdby_sudtks'] = Auth::user()->name;
        //     $sudtks['updatedby_sudtks'] = Auth::user()->name;
        //     // dd($sudtks);

        //     rekomendasi_terdaftar_dtks::where('id', $id)->update($sudtks);

        //     return redirect('rekomendasi_terdaftar_dtks')->withSuccess('Data Berhasil Diubah');
        // }
    }

    /**
     * Remove the specified rekomendasi_terdaftar_dtks from storage.
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $rekomendasiTerdaftarDtks = $this->rekomendasiTerdaftarDtksRepository->find($id);

        if (empty($rekomendasiTerdaftarDtks)) {
            Flash::error('Rekomendasi Terdaftar Dtks not found');

            return redirect(route('rekomendasi_terdaftar_dtks.index'));
        }

        $this->rekomendasiTerdaftarDtksRepository->delete($id);

        Flash::success('Rekomendasi Terdaftar Dtks deleted successfully.');

        return redirect(route('rekomendasi_terdaftar_dtks.index'));
    }

    public function draft(Request $request)
    {
        $user_name = Auth::user()->name;
        // dd($user_name);
        $query = DB::table('rekomendasi_terdaftar_dtks')
            ->join('users', 'users.id', '=', 'rekomendasi_terdaftar_dtks.createdby_sudtks')
            ->join('roles', 'users.id', '=', 'rekomendasi_terdaftar_dtks.tujuan_sudtks')
            ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_terdaftar_dtks.id_kecamatan_sudtks')
            ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_terdaftar_dtks.id_kelurahan_sudtks')
            ->select('rekomendasi_terdaftar_dtks.*', 'b.name_village', 'd.name_districts','roles.name')
            ->distinct();
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
        // dd($user_wilayah);
        if ($user_wilayah->name == 'Front Office kota') {
            $query->Where(function ($query) use ($user_wilayah) {
                $query->where('rekomendasi_terdaftar_dtks.id_kabkot_sudtks', $user_wilayah->kota_id);
                $query->where('rekomendasi_terdaftar_dtks.status_aksi_sudtks', 'Draft');
                $query->where('rekomendasi_terdaftar_dtks.createdby_sudtks',  Auth::user()->id);
            })->distinct();
        }
        if ($user_wilayah->name == 'Front Office Kelurahan') {
            $query->Where(function ($query) use ($user_wilayah) {
                $query->where('rekomendasi_terdaftar_dtks.id_kelurahan_sudtks', $user_wilayah->kelurahan_id);
                $query->where('rekomendasi_terdaftar_dtks.status_aksi_sudtks', 'Draft');
                $query->where('rekomendasi_terdaftar_dtks.createdby_sudtks',  Auth::user()->id);
            });
        }
        if ($user_wilayah->name == 'fasilitator') {
            $query->Where(function ($query) use ($user_wilayah) {
                $query->where('rekomendasi_terdaftar_dtks.id_kelurahan_sudtks', $user_wilayah->kelurahan_id);
                $query->where('rekomendasi_terdaftar_dtks.status_aksi_sudtks', 'Draft');
                $query->where('rekomendasi_terdaftar_dtks.createdby_sudtks',  Auth::user()->id);
            });
        }

        if ($user_wilayah->name == 'Front Office Kelurahan'|| $user_wilayah == 'fasilitator') {
            if ($request->has('search') && !empty($request->search['value'])) {
                $search = $request->search['value'];
                $query->Where(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_terdaftar_dtks.id_kelurahan_sudtks', $user_wilayah->kelurahan_id);
                    $query->where('rekomendasi_terdaftar_dtks.status_aksi_sudtks', 'Draft');
                    $query->where('rekomendasi_terdaftar_dtks.createdby_sudtks',  Auth::user()->id);
                })
                ->where('rekomendasi_terdaftar_dtks.no_pendaftaran_sudtks', 'like', "%$search%");
            }
        }elseif($user_wilayah->name == 'Front Office Kota') {
            if ($request->has('search') && !empty($request->search['value'])) {
                $search = $request->search['value'];
                $query->Where(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_terdaftar_dtks.id_kabkot_sudtks', $user_wilayah->kota_id);
                    $query->where('rekomendasi_terdaftar_dtks.status_aksi_sudtks', 'Draft');
                    $query->where('rekomendasi_terdaftar_dtks.createdby_sudtks',  Auth::user()->id);
                })
                ->where('rekomendasi_terdaftar_dtks.no_pendaftaran_sudtks', 'like', "%$search%");
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
        //Add paginate
        $start = $request->start;
        $length = $request->length;
        $query->offset($start)->limit($length);
        $data = $query->get();
        // mengubah data JSON menjadi objek PHP

        return response()->json([
            'draw' => $request->input('draw'),
            'recordsTotal' => rekomendasi_terdaftar_dtks::count(),
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
            ->select('wilayahs.*','roles.name','model_has_roles.role_id')
            ->where('wilayahs.createdby', $user_id)
            ->where(function ($query) {
                $query->where('status_wilayah', 1);
            })
            ->first();
        $query = DB::table('rekomendasi_terdaftar_dtks')
            ->join('users', 'users.id', '=', 'rekomendasi_terdaftar_dtks.petugas_sudtks')
            ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_terdaftar_dtks.id_kecamatan_sudtks')
            ->join('roles', 'roles.id', '=', 'rekomendasi_terdaftar_dtks.tujuan_sudtks')
            ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_terdaftar_dtks.id_kelurahan_sudtks')
            ->select('rekomendasi_terdaftar_dtks.*', 'b.name_village', 'd.name_districts', 'users.name','roles.name');
    
        if ($user_wilayah->name == 'Front Office Kelurahan') {
            // dd
            $query->orWhere(function ($query) use ($user_wilayah) {
                $query->where('rekomendasi_terdaftar_dtks.id_kelurahan_sudtks', '=', $user_wilayah->kelurahan_id)
                    ->where('rekomendasi_terdaftar_dtks.tujuan_sudtks', '=', $user_wilayah->role_id)
                    ->where(function ($query) {
                        $query->where('rekomendasi_terdaftar_dtks.status_aksi_sudtks', '=', 'Teruskan')
                            ->orWhere('rekomendasi_terdaftar_dtks.status_aksi_sudtks', '=', 'kembalikan');
                    });
            });
        }
        if ($user_wilayah->name == 'fasilitator') {
            // dd
            $query->orWhere(function ($query) use ($user_wilayah) {
                $query->where('rekomendasi_terdaftar_dtks.id_kelurahan_sudtks', '=', $user_wilayah->kelurahan_id)
                    ->where('rekomendasi_terdaftar_dtks.tujuan_sudtks', '=', $user_wilayah->role_id)
                    ->where(function ($query) {
                        $query->where('rekomendasi_terdaftar_dtks.status_aksi_sudtks', '=', 'Teruskan')
                            ->orWhere('rekomendasi_terdaftar_dtks.status_aksi_sudtks', '=', 'kembalikan');
                    });
            });
        }
        if ($user_wilayah->name == 'Back Ofiice kelurahan') {
            $query->orWhere(function ($query) use ($user_wilayah) {
                $query->where('rekomendasi_terdaftar_dtks.id_kelurahan_sudtks', '=', $user_wilayah->kelurahan_id)
                    ->where('rekomendasi_terdaftar_dtks.tujuan_sudtks', '=', $user_wilayah->role_id)
                    ->where(function ($query) {
                        $query->where('rekomendasi_terdaftar_dtks.status_aksi_sudtks', '=', 'Teruskan')
                            ->orWhere('rekomendasi_terdaftar_dtks.status_aksi_sudtks', '=', 'kembalikan');
                    });
            });
        }
        if ($user_wilayah->name == 'supervisor') {
            $query->orWhere(function ($query) use ($user_wilayah) {
                $query->where('rekomendasi_terdaftar_dtks.id_kecamatan_sudtks', '=', $user_wilayah->kecamatan_id)
                    ->where('rekomendasi_terdaftar_dtks.tujuan_sudtks', '=', $user_wilayah->role_id)
                    ->where(function ($query) {
                        $query->where('rekomendasi_terdaftar_dtks.status_aksi_sudtks', '=', 'Teruskan')
                            ->orWhere('rekomendasi_terdaftar_dtks.status_aksi_sudtks', '=', 'kembalikan');
                    });
            });
        }
        if ($user_wilayah->name == 'Front Office kota') {
            //  dd($user_wilayah->role_id);

            $query->orWhere(function ($query) use ($user_wilayah) {
                $query->where('rekomendasi_terdaftar_dtks.id_kabkot_sudtks', '=', $user_wilayah->kota_id)
                    ->where('rekomendasi_terdaftar_dtks.tujuan_sudtks', '=', $user_wilayah->role_id)
                    ->where(function ($query) {
                        $query->where('rekomendasi_terdaftar_dtks.status_aksi_sudtks', '=', 'Teruskan')
                            ->orWhere('rekomendasi_terdaftar_dtks.status_aksi_sudtks', '=', 'kembalikan');
                    });
            })->distinct();
        }
        if ($user_wilayah->name == 'Back Ofiice Kota') {
            //  dd($user_wilayah->role_id);

            $query->orWhere(function ($query) use ($user_wilayah) {
                $query->where('rekomendasi_terdaftar_dtks.id_kabkot_sudtks', '=', $user_wilayah->kota_id)
                    ->where('rekomendasi_terdaftar_dtks.tujuan_sudtks', '=', $user_wilayah->role_id)
                    ->where(function ($query) {
                        $query->where('rekomendasi_terdaftar_dtks.status_aksi_sudtks', '=', 'Teruskan')
                            ->orWhere('rekomendasi_terdaftar_dtks.status_aksi_sudtks', '=', 'kembalikan');
                    });
            });
            
        }
        if ($user_wilayah->name == 'kepala bidang') {

            $query->orWhere(function ($query) use ($user_wilayah) {
                $query->where('rekomendasi_terdaftar_dtks.id_kabkot_sudtks', '=', $user_wilayah->kota_id)
                    ->where('rekomendasi_terdaftar_dtks.tujuan_sudtks', '=', $user_wilayah->role_id)
                    ->where(function ($query) {
                        $query->where('rekomendasi_terdaftar_dtks.status_aksi_sudtks', '=', 'Teruskan')
                            ->orWhere('rekomendasi_terdaftar_dtks.status_aksi_sudtks', '=', 'kembalikan');
                    });
                // dd($va);
            });
        }
        if ($user_wilayah->name == 'SekertarisDinas') {
            // dd($user_wilayah);
            $query->orWhere(function ($query) use ($user_wilayah) {
                $query->where('rekomendasi_terdaftar_dtks.id_kabkot_sudtks', '=', $user_wilayah->kota_id)
                    ->where('rekomendasi_terdaftar_dtks.tujuan_sudtks', '=', $user_wilayah->role_id)
                    ->where('rekomendasi_terdaftar_dtks.petugas_sudtks', '=', $user_wilayah->model_id)
                    ->where(function ($query) {
                        $query->where('rekomendasi_terdaftar_dtks.status_aksi_sudtks', '=', 'Teruskan')
                            ->orWhere('rekomendasi_terdaftar_dtks.status_aksi_sudtks', '=', 'kembalikan');
                    });
                // dd($va);
            });
        }
        if ($user_wilayah->name == 'KepalaDinas') {
            // dd($user_wilayah);
            $query->orWhere(function ($query) use ($user_wilayah) {
                $query->where('rekomendasi_terdaftar_dtks.id_kabkot_sudtks', '=', $user_wilayah->kota_id)
                    ->where('rekomendasi_terdaftar_dtks.tujuan_sudtks', '=', $user_wilayah->role_id)
                    ->where('rekomendasi_terdaftar_dtks.petugas_sudtks', '=', $user_wilayah->model_id)
                    ->where(function ($query) {
                        $query->where('rekomendasi_terdaftar_dtks.status_aksi_sudtks', '=', 'Teruskan')
                            ->orWhere('rekomendasi_terdaftar_dtks.status_aksi_sudtks', '=', 'kembalikan');
                    });
                // dd($va);
            });
        }
        if ($user_wilayah->name == 'Front Office Kelurahan' || $user_wilayah->name == 'Back Ofiice kelurahan'|| $user_wilayah->name == 'fasilitator') {
            if ($request->has('search') && !empty($request->search['value'])) {
                $search = $request->search['value'];
                $query->orWhere(function ($query) use ($user_wilayah, $search) {
                    $query->where('rekomendasi_terdaftar_dtks.id_kelurahan_sudtks', '=', $user_wilayah->kelurahan_id)
                        ->where('rekomendasi_terdaftar_dtks.tujuan_sudtks', '=', $user_wilayah->role_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_terdaftar_dtks.status_aksi_sudtks', '=', 'Teruskan')
                                ->orWhere('rekomendasi_terdaftar_dtks.status_aksi_sudtks', '=', 'kembalikan');
                        })
                        ->where('rekomendasi_terdaftar_dtks.no_pendaftaran_sudtks', 'like', "%$search%");

                });
            }
        }elseif($user_wilayah->name == 'Front Office Kota' || $user_wilayah->name == 'Back Ofiice Kota'|| $user_wilayah->name == 'KepalaDinas'||$user_wilayah->name == 'SekertarisDinas'||$user_wilayah->name == 'supervisor') {
            if ($request->has('search') && !empty($request->search['value'])) {
                $search = $request->search['value'];
                $query->orWhere(function ($query) use ($user_wilayah, $search) {
                    $query->where('rekomendasi_terdaftar_dtks.id_kabkot_sudtks', '=', $user_wilayah->kota_id)
                        ->where('rekomendasi_terdaftar_dtks.tujuan_sudtks', '=', $user_wilayah->role_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_terdaftar_dtks.status_aksi_sudtks', '=', 'Teruskan')
                                ->orWhere('rekomendasi_terdaftar_dtks.status_aksi_sudtks', '=', 'kembalikan');
                        })
                        ->where('rekomendasi_terdaftar_dtks.no_pendaftaran_sudtks', 'like', "%$search%");
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
        // dd($request->all());
        //Add paginate
        $start = $request->start;
        // dd($start);
        $length = $request->length;
        // dd($length);
        $query->offset($start)->limit($length);
        $data = $query->get();


        return response()->json([
            'draw' => $request->input('draw'),
            'recordsTotal' => rekomendasi_terdaftar_dtks::count(),
            'recordsFiltered' => $total_filtered_items,
            'data' => $data,
        ]);
    }
    public function teruskan(Request $request)
    {
        $user_name = Auth::user()->name;
        // dd($user_name);

        $query = DB::table('rekomendasi_terdaftar_dtks')
            ->join('users', 'users.id', '=', 'rekomendasi_terdaftar_dtks.createdby_sudtks')
            ->join('log_sudtks', 'log_sudtks.id_trx_sudtks', '=', 'rekomendasi_terdaftar_dtks.id')
            // ->join('model_has_roles', 'model_has_roles.role_id', '=', 'rekomendasi_terdaftar_dtks.tujuan_sudtks')
            ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_terdaftar_dtks.id_kelurahan_sudtks')

            ->select('rekomendasi_terdaftar_dtks.*', 'b.name_village');
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
        //Front Office Kelurahan
        // if ($user_wilayah->name == 'Front Office Kelurahan') {
        //     $query = DB::table('rekomendasi_terdaftar_dtks')
        //         ->join('users', 'users.id', '=', 'rekomendasi_terdaftar_dtks.createdby_sudtks')
        //         ->join('log_sudtks', 'log_sudtks.id_trx_sudtks', '=', 'rekomendasi_terdaftar_dtks.id')
        //          ->join('roles', 'roles.id', '=', 'rekomendasi_terdaftar_dtks.tujuan_sudtks')
        //          ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_terdaftar_dtks.id_kecamatan_sudtks')
        //          ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_terdaftar_dtks.id_kelurahan_sudtks')
        //         ->select('rekomendasi_terdaftar_dtks.*', 'b.name_village','d.name_districts','log_sudtks.tujuan_sudtks', 'log_sudtks.petugas_sudtks','roles.name')
        //         ->orWhere(function ($query) use ($user_wilayah) {
        //             $query->where('rekomendasi_terdaftar_dtks.id_kelurahan_sudtks', $user_wilayah->kelurahan_id)
        //                 ->where('rekomendasi_terdaftar_dtks.tujuan_sudtks', '!=', $user_wilayah->role_id)
        //                 ->where('log_sudtks.created_by_sudtks', '=', auth::user()->id)
        //                 // ->where('rekomendasi_terdaftar_dtks.petugas_sudtks','!=', $user_wilayah->model_id)
        //                 ->where(function ($query) {
        //                     $query->where('rekomendasi_terdaftar_dtks.status_aksi_sudtks', '=', 'Teruskan')
        //                         ->orWhere('rekomendasi_terdaftar_dtks.status_aksi_sudtks', '=', 'kembalikan');
        //                 });
        //         })->distinct();
        // }
    if ($user_wilayah->name == 'Front Office Kelurahan' || $user_wilayah->name == 'Back Ofiice kelurahan'|| $user_wilayah->name == 'fasilitator') {
            $query = DB::table('rekomendasi_terdaftar_dtks')
            ->leftjoin('indonesia_districts as d', 'd.code', '=', 'rekomendasi_terdaftar_dtks.id_kecamatan_sudtks')
            ->leftjoin('indonesia_villages as b', 'b.code', '=', 'rekomendasi_terdaftar_dtks.id_kelurahan_sudtks')
            // ->leftjoin('roles as r', 'r.id', '=', 'rekomendasi_terdaftar_dtks.petugas_sudtks')
            ->leftjoin('users', 'users.id', '=', 'rekomendasi_terdaftar_dtks.petugas_sudtks')
            ->select('rekomendasi_terdaftar_dtks.*', 'b.name_village', 'd.name_districts','users.name')
            // ->selectRaw('IFNULL(r.name,"") name')
            ->where('rekomendasi_terdaftar_dtks.id_kelurahan_sudtks', '=', $user_wilayah->kelurahan_id)
            ->whereIn('rekomendasi_terdaftar_dtks.status_aksi_sudtks', ['Teruskan','Kembalikan'])
            ->where('rekomendasi_terdaftar_dtks.petugas_sudtks', '<>', $user_id)
            ->whereRaw("(SELECT COUNT(l.id) FROM log_sudtks as l WHERE l.id_trx_sudtks = rekomendasi_terdaftar_dtks.id AND l.created_by_sudtks = '".$user_id."') > 0 ");
            // dd($query);
        }elseif($user_wilayah->name == 'Front Office Kota' || $user_wilayah->name == 'Back Ofiice Kota'|| $user_wilayah->name == 'KepalaDinas'||$user_wilayah->name == 'SekertarisDinas') {
            $query = DB::table('rekomendasi_terdaftar_dtks')
            ->leftjoin('indonesia_districts as d', 'd.code', '=', 'rekomendasi_terdaftar_dtks.id_kecamatan_sudtks')
            ->leftjoin('indonesia_villages as b', 'b.code', '=', 'rekomendasi_terdaftar_dtks.id_kelurahan_sudtks')
            // ->leftjoin('roles as r', 'r.id', '=', 'rekomendasi_terdaftar_dtks.petugas_sudtks')
            ->leftjoin('users', 'users.id', '=', 'rekomendasi_terdaftar_dtks.petugas_sudtks')
            ->select('rekomendasi_terdaftar_dtks.*', 'b.name_village', 'd.name_districts','users.name')
            // ->selectRaw('IFNULL(r.name,"") name')
            ->where('rekomendasi_terdaftar_dtks.id_kabkot_sudtks', '=', $user_wilayah->kota_id)
            ->whereIn('rekomendasi_terdaftar_dtks.status_aksi_sudtks', ['Teruskan','Kembalikan'])
            ->where('rekomendasi_terdaftar_dtks.petugas_sudtks', '<>', $user_id)
            ->whereRaw("(SELECT COUNT(l.id) FROM log_sudtks as l WHERE l.id_trx_sudtks = rekomendasi_terdaftar_dtks.id AND l.created_by_sudtks = '".$user_id."') > 0 ");
            // dd($query);
        }else{
            $query = DB::table('rekomendasi_terdaftar_dtks')
            ->leftjoin('indonesia_districts as d', 'd.code', '=', 'rekomendasi_terdaftar_dtks.id_kecamatan_sudtks')
            ->leftjoin('indonesia_villages as b', 'b.code', '=', 'rekomendasi_terdaftar_dtks.id_kelurahan_sudtks')
            // ->leftjoin('roles as r', 'r.id', '=', 'rekomendasi_terdaftar_dtks.petugas_sudtks')
            ->leftjoin('users', 'users.id', '=', 'rekomendasi_terdaftar_dtks.petugas_sudtks')
            ->select('rekomendasi_terdaftar_dtks.*', 'b.name_village', 'd.name_districts','users.name')
            // ->selectRaw('IFNULL(r.name,"") name')
            ->where('rekomendasi_terdaftar_dtks.id_kecamatan_sudtks', '=', $user_wilayah->kecamatan_id)
            ->whereIn('rekomendasi_terdaftar_dtks.status_aksi_sudtks', ['Teruskan','Kembalikan'])
            ->where('rekomendasi_terdaftar_dtks.petugas_sudtks', '<>', $user_id)
            ->whereRaw("(SELECT COUNT(l.id) FROM log_sudtks as l WHERE l.id_trx_sudtks = rekomendasi_terdaftar_dtks.id AND l.created_by_sudtks = '".$user_id."') > 0 ");
            // dd($query);
        }
            if ($user_wilayah->name == 'Front Office Kelurahan' || $user_wilayah->name == 'Back Ofiice kelurahan'|| $user_wilayah->name == 'fasilitator') {
            if ($request->has('search') && !empty($request->search['value'])) {
                $search = $request->search['value'];
                $query = DB::table('rekomendasi_terdaftar_dtks')
                ->leftjoin('indonesia_districts as d', 'd.code', '=', 'rekomendasi_terdaftar_dtks.id_kecamatan_sudtks')
                ->leftjoin('indonesia_villages as b', 'b.code', '=', 'rekomendasi_terdaftar_dtks.id_kelurahan_sudtks')
                // ->leftjoin('roles as r', 'r.id', '=', 'rekomendasi_terdaftar_dtks.petugas_sudtks')
                ->leftjoin('users', 'users.id', '=', 'rekomendasi_terdaftar_dtks.petugas_sudtks')
                ->select('rekomendasi_terdaftar_dtks.*', 'b.name_village', 'd.name_districts','users.name')
                // ->selectRaw('IFNULL(r.name,"") name')
                ->where('rekomendasi_terdaftar_dtks.id_kelurahan_sudtks', '=', $user_wilayah->kelurahan_id)
                ->whereIn('rekomendasi_terdaftar_dtks.status_aksi_sudtks', ['Teruskan','Kembalikan'])
                ->where('rekomendasi_terdaftar_dtks.petugas_sudtks', '<>', $user_id)
                ->whereRaw("(SELECT COUNT(l.id) FROM log_sudtks as l WHERE l.id_trx_sudtks = rekomendasi_terdaftar_dtks.id AND l.created_by_sudtks = '".$user_id."') > 0 ")
                ->where('rekomendasi_terdaftar_dtks.no_pendaftaran_sudtks', 'like', "%$search%");

                // dd($query);
            }
        }elseif($user_wilayah->name == 'Front Office Kota' || $user_wilayah->name == 'Back Ofiice Kota'|| $user_wilayah->name == 'KepalaDinas'||$user_wilayah->name == 'SekertarisDinas') {
            if ($request->has('search') && !empty($request->search['value'])) {
                $search = $request->search['value'];
                $query = DB::table('rekomendasi_terdaftar_dtks')
                ->leftjoin('indonesia_districts as d', 'd.code', '=', 'rekomendasi_terdaftar_dtks.id_kecamatan_sudtks')
                ->leftjoin('indonesia_villages as b', 'b.code', '=', 'rekomendasi_terdaftar_dtks.id_kelurahan_sudtks')
                // ->leftjoin('roles as r', 'r.id', '=', 'rekomendasi_terdaftar_dtks.petugas_sudtks')
                ->leftjoin('users', 'users.id', '=', 'rekomendasi_terdaftar_dtks.petugas_sudtks')
                ->select('rekomendasi_terdaftar_dtks.*', 'b.name_village', 'd.name_districts','users.name')
                // ->selectRaw('IFNULL(r.name,"") name')
                ->where('rekomendasi_terdaftar_dtks.id_kabkot_sudtks', '=', $user_wilayah->kota_id)
                ->whereIn('rekomendasi_terdaftar_dtks.status_aksi_sudtks', ['Teruskan','Kembalikan'])
                ->where('rekomendasi_terdaftar_dtks.petugas_sudtks', '<>', $user_id)
                ->whereRaw("(SELECT COUNT(l.id) FROM log_sudtks as l WHERE l.id_trx_sudtks = rekomendasi_terdaftar_dtks.id AND l.created_by_sudtks = '".$user_id."') > 0 ")
                ->where('rekomendasi_terdaftar_dtks.no_pendaftaran_sudtks', 'like', "%$search%");

                // dd($query);
            }
        }else{
            if ($request->has('search') && !empty($request->search['value'])) {
                $search = $request->search['value'];
                $query = DB::table('rekomendasi_terdaftar_dtks')
                ->leftjoin('indonesia_districts as d', 'd.code', '=', 'rekomendasi_terdaftar_dtks.id_kecamatan_sudtks')
                ->leftjoin('indonesia_villages as b', 'b.code', '=', 'rekomendasi_terdaftar_dtks.id_kelurahan_sudtks')
                // ->leftjoin('roles as r', 'r.id', '=', 'rekomendasi_terdaftar_dtks.petugas_sudtks')
                ->leftjoin('users', 'users.id', '=', 'rekomendasi_terdaftar_dtks.petugas_sudtks')
                ->select('rekomendasi_terdaftar_dtks.*', 'b.name_village', 'd.name_districts','users.name')
                // ->selectRaw('IFNULL(r.name,"") name')
                ->where('rekomendasi_terdaftar_dtks.id_kecamatan_sudtks', '=', $user_wilayah->kecamatan_id)
                ->whereIn('rekomendasi_terdaftar_dtks.status_aksi_sudtks', ['Teruskan','Kembalikan'])
                ->where('rekomendasi_terdaftar_dtks.petugas_sudtks', '<>', $user_id)
                ->whereRaw("(SELECT COUNT(l.id) FROM log_sudtks as l WHERE l.id_trx_sudtks = rekomendasi_terdaftar_dtks.id AND l.created_by_sudtks = '".$user_id."') > 0 ")
                ->where('rekomendasi_terdaftar_dtks.no_pendaftaran_sudtks', 'like', "%$search%");

                // dd($query);
            }
        }
        $total_filtered_items = $query->count();
        // Add ordering
        if ($request->has('order')) {
            $order_column = $request->order[0]['column'];
            $order_direction = $request->order[0]['dir'];
            $query->orderBy($request->input('columns.' . $order_column . '.data'), $order_direction);
        }
        //Add paginate
         $start = $request->start;
        // dd($start);
        $length = $request->length;
        // dd($length);
        $query->offset($start)->limit($length);
        $data = $query->get();

        return response()->json([
            'draw' => $request->input('draw'),
            'recordsTotal' => rekomendasi_terdaftar_dtks::count(),
            'recordsFiltered' => $total_filtered_items,
            'data' => $data,
        ]);
    }
    public function selesai(Request $request)
    {
        $user_name = Auth::user()->name;
        $query = DB::table('rekomendasi_terdaftar_dtks')
            ->join('users', 'users.id', '=', 'rekomendasi_terdaftar_dtks.createdby_sudtks')
            ->join('log_sudtks', 'log_sudtks.id_trx_sudtks', '=', 'rekomendasi_terdaftar_dtks.id')
            ->join('model_has_roles', 'model_has_roles.model_id', '=', 'rekomendasi_terdaftar_dtks.tujuan_sudtks')
            ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_terdaftar_dtks.id_kelurahan_sudtks')
            ->select('rekomendasi_terdaftar_dtks.*', 'b.name_village');
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
                if ($user_wilayah->name == 'Front Office Kelurahan' || $user_wilayah->name == 'Back Ofiice kelurahan'|| $user_wilayah->name == 'fasilitator') {
                
                    $query = DB::table('rekomendasi_terdaftar_dtks')
                    ->leftjoin('indonesia_districts as d', 'd.code', '=', 'rekomendasi_terdaftar_dtks.id_kecamatan_sudtks')
                    ->leftjoin('indonesia_villages as b', 'b.code', '=', 'rekomendasi_terdaftar_dtks.id_kelurahan_sudtks')
                    // ->leftjoin('roles as r', 'r.id', '=', 'rekomendasi_terdaftar_dtks.petugas_sudtks')
                    ->leftjoin('users', 'users.id', '=', 'rekomendasi_terdaftar_dtks.petugas_sudtks')
                    ->select('rekomendasi_terdaftar_dtks.*', 'b.name_village', 'd.name_districts','users.name')
                    // ->selectRaw('IFNULL(r.name,"") name')
                    ->where('rekomendasi_terdaftar_dtks.id_kelurahan_sudtks', '=', $user_wilayah->kelurahan_id)
                    ->whereIn('rekomendasi_terdaftar_dtks.status_aksi_sudtks', ['Selesai','Tolak']);
                    // ->where('rekomendasi_terdaftar_dtks.petugas_sudtks', '<>', $user_id);
                
            }elseif($user_wilayah->name == 'Front Office Kota' || $user_wilayah->name == 'Back Ofiice Kota'|| $user_wilayah->name == 'KepalaDinas'||$user_wilayah->name == 'SekertarisDinas') {
                $query = DB::table('rekomendasi_terdaftar_dtks')
                ->leftjoin('indonesia_districts as d', 'd.code', '=', 'rekomendasi_terdaftar_dtks.id_kecamatan_sudtks')
                ->leftjoin('indonesia_villages as b', 'b.code', '=', 'rekomendasi_terdaftar_dtks.id_kelurahan_sudtks')
                // ->leftjoin('roles as r', 'r.id', '=', 'rekomendasi_terdaftar_dtks.petugas_sudtks')
                ->leftjoin('users', 'users.id', '=', 'rekomendasi_terdaftar_dtks.petugas_sudtks')
                ->select('rekomendasi_terdaftar_dtks.*', 'b.name_village', 'd.name_districts','users.name')
                // ->selectRaw('IFNULL(r.name,"") name')
                ->where('rekomendasi_terdaftar_dtks.id_kabkot_sudtks', '=', $user_wilayah->kota_id)
                ->whereIn('rekomendasi_terdaftar_dtks.status_aksi_sudtks', ['Selesai','Tolak']);
                // ->where('rekomendasi_terdaftar_dtks.petugas_sudtks', '<>', $user_id);
             
            }else{
                $query = DB::table('rekomendasi_terdaftar_dtks')
                ->leftjoin('indonesia_districts as d', 'd.code', '=', 'rekomendasi_terdaftar_dtks.id_kecamatan_sudtks')
                ->leftjoin('indonesia_villages as b', 'b.code', '=', 'rekomendasi_terdaftar_dtks.id_kelurahan_sudtks')
                // ->leftjoin('roles as r', 'r.id', '=', 'rekomendasi_terdaftar_dtks.petugas_sudtks')
                ->leftjoin('users', 'users.id', '=', 'rekomendasi_terdaftar_dtks.petugas_sudtks')
                ->select('rekomendasi_terdaftar_dtks.*', 'b.name_village', 'd.name_districts','users.name')
                // ->selectRaw('IFNULL(r.name,"") name')
                ->where('rekomendasi_terdaftar_dtks.id_kecamatan_sudtks', '=', $user_wilayah->kecamatan_id)
                ->whereIn('rekomendasi_terdaftar_dtks.status_aksi_sudtks', ['Selesai','Tolak']);
                // ->where('rekomendasi_terdaftar_dtks.petugas_sudtks', '<>', $user_id);
            }

        if($user_wilayah->name == 'Front Office Kelurahan' || $user_wilayah == 'Back Ofiice kelurahan'|| $user_wilayah == 'fasilitator') {
            if ($request->has('search') && !empty($request->search['value'])) {
                $search = $request->search['value'];
                $query = DB::table('rekomendasi_terdaftar_dtks')
                ->leftjoin('indonesia_districts as d', 'd.code', '=', 'rekomendasi_terdaftar_dtks.id_kecamatan_sudtks')
                ->leftjoin('indonesia_villages as b', 'b.code', '=', 'rekomendasi_terdaftar_dtks.id_kelurahan_sudtks')
                // ->leftjoin('roles as r', 'r.id', '=', 'rekomendasi_terdaftar_dtks.petugas_sudtks')
                ->leftjoin('users', 'users.id', '=', 'rekomendasi_terdaftar_dtks.petugas_sudtks')
                ->select('rekomendasi_terdaftar_dtks.*', 'b.name_village', 'd.name_districts','users.name')
                // ->selectRaw('IFNULL(r.name,"") name')
                ->where('rekomendasi_terdaftar_dtks.id_kelurahan_sudtks', '=', $user_wilayah->kelurahan_id)
                ->whereIn('rekomendasi_terdaftar_dtks.status_aksi_sudtks', ['Selesai','Tolak'])
                ->where('rekomendasi_terdaftar_dtks.no_pendaftaran_sudtks', 'like', "%$search%")->get();
            }
        }elseif($user_wilayah->name == 'Front Office Kota' || $user_wilayah->name == 'Back Ofiice Kota'|| $user_wilayah->name == 'KepalaDinas'||$user_wilayah->name == 'SekertarisDinas'||$user_wilayah->name == 'supervisor') {
            if ($request->has('search') && !empty($request->search['value'])) {
                $search = $request->search['value'];
                $query = DB::table('rekomendasi_terdaftar_dtks')
                ->leftjoin('indonesia_districts as d', 'd.code', '=', 'rekomendasi_terdaftar_dtks.id_kecamatan_sudtks')
                ->leftjoin('indonesia_villages as b', 'b.code', '=', 'rekomendasi_terdaftar_dtks.id_kelurahan_sudtks')
                // ->leftjoin('roles as r', 'r.id', '=', 'rekomendasi_terdaftar_dtks.petugas_sudtks')
                ->leftjoin('users', 'users.id', '=', 'rekomendasi_terdaftar_dtks.petugas_sudtks')
                ->select('rekomendasi_terdaftar_dtks.*', 'b.name_village', 'd.name_districts','users.name')
                // ->selectRaw('IFNULL(r.name,"") name')
                ->where('rekomendasi_terdaftar_dtks.id_kabkot_sudtks', '=', $user_wilayah->kota_id)
                ->whereIn('rekomendasi_terdaftar_dtks.status_aksi_sudtks', ['Selesai','Tolak'])
                ->where('rekomendasi_terdaftar_dtks.no_pendaftaran_sudtks', 'like', "%$search%")->get();
            }
        }

        // Get total count of filtered items
        $total_filtered_items = $query->count();
        // Add ordering

        // Get paginated data
        $start = $request->start;
        // dd($start);
        $length = $request->length;
        // dd($length);
        $query->offset($start)->limit($length);
        $data = $query->get();
        // dd($data);
        // mengubah data JSON menjadi objek PHP

        return response()->json([
            'draw' => $request->input('draw'),
            'recordsTotal' => rekomendasi_terdaftar_dtks::count(),
            'recordsFiltered' => $total_filtered_items,
            'data' => $data,
        ]);
    }
    public function getPetugaDtks($id)
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
        if ($user_wilayah->name == 'Front Office Kelurahan' || $user_wilayah == 'fasilitator'){
            $users = DB::table('users as u')
                ->leftJoin('model_has_roles as mhr', 'u.id', '=', 'mhr.model_id')
                ->leftJoin('roles as r', 'mhr.role_id', '=', 'r.id')
                ->leftJoin('wilayahs', 'wilayahs.createdby', '=', 'u.id')
                ->select('u.id as user_id', 'u.name', 'u.email', 'r.name as role')
                ->where('mhr.model_type', '=', 'App\Models\User')
                ->where('wilayahs.kelurahan_id', '=',$wilayah->kelurahan_id)
                ->where('mhr.role_id', '=', $id)
                ->get();
            return response()->json($users);
        }elseif ($user_wilayah->name == 'Back Ofiice kelurahan') {
            $users = DB::table('users as u')
            ->leftJoin('model_has_roles as mhr', 'u.id', '=', 'mhr.model_id')
            ->leftJoin('roles as r', 'mhr.role_id', '=', 'r.id')
            ->leftJoin('wilayahs', 'wilayahs.createdby', '=', 'u.id')
            ->select('u.id as user_id', 'u.name', 'u.email', 'r.name as role')
            ->where('mhr.model_type', '=', 'App\Models\User')
            ->where('wilayahs.kecamatan_id', '=',$wilayah->kecamatan_id)
            ->where('mhr.role_id', '=', $id)
            ->get(); 
            // return response()->json($users);
        
            return response()->json($users);
        }elseif($user_wilayah->name == 'Front Office Kota' || $user_wilayah->name == 'Back Ofiice Kota'|| $user_wilayah->name == 'KepalaDinas'||$user_wilayah->name == 'SekertarisDinas') {
            $users = DB::table('users as u')
                    ->leftJoin('model_has_roles as mhr', 'u.id', '=', 'mhr.model_id')
                    ->leftJoin('roles as r', 'mhr.role_id', '=', 'r.id')
                    ->leftJoin('wilayahs', 'wilayahs.createdby', '=', 'u.id')
                    ->leftJoin('rekomendasi_terdaftar_dtks','rekomendasi_terdaftar_dtks.createdby_sudtks','=','u.id')
                    ->where('mhr.model_type', '=', 'App\Models\User')
                    ->where('wilayahs.kota_id', '=',$wilayah->kota_id)
                    ->where('mhr.role_id', '=', $id)
                    // ->whereRaw("(SELECT COUNT(l.id) FROM users as l WHERE l.id = '".$id."') > 0 ")
                    ->whereRaw("(SELECT COUNT(l.id) FROM users as l WHERE l.id = rekomendasi_terdaftar_dtks.id AND l.id = '".$id."') > 0 ")
                    ->select('u.id', 'u.name', 'u.email', 'r.name as role')
                    ->get();
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
                // dd($users);
            }
             
        }else{
             
            $users = DB::table('users as u')
                ->leftJoin('model_has_roles as mhr', 'u.id', '=', 'mhr.model_id')
                ->leftJoin('roles as r', 'mhr.role_id', '=', 'r.id')
                ->leftJoin('wilayahs', 'wilayahs.createdby', '=', 'u.id')
                ->select('u.id', 'u.name', 'u.email', 'r.name as role')
                ->where('mhr.model_type', '=', 'App\Models\User') 
                ->where('wilayahs.kelurahan_id', '=',$wilayah->kelurahan_id)
                ->where('mhr.role_id', '=', $id)
                ->get();
        //    dd($users);
         
        }
        return response()->json($users);
    }
}
