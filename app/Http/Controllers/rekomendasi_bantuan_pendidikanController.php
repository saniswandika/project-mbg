<?php

namespace App\Http\Controllers;

use App\Http\Requests\Createrekomendasi_bantuan_pendidikanRequest;
use App\Http\Requests\Updaterekomendasi_bantuan_pendidikanRequest;
use App\Http\Controllers\AppBaseController;
use App\Models\logpendidikan;
use App\Models\pelapor;
use App\Models\Prelist;
use App\Repositories\rekomendasi_bantuan_pendidikanRepository;
use Illuminate\Http\Request;
use ImageKit\ImageKit;
use App\Models\rekomendasi_bantuan_pendidikan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Roles;
use Illuminate\Support\Facades\Storage;
use Flash;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class rekomendasi_bantuan_pendidikanController extends AppBaseController
{
    /** @var rekomendasi_bantuan_pendidikanRepository $rekomendasiBantuanPendidikanRepository*/
    private $rekomendasiBantuanPendidikanRepository;

    public function __construct(rekomendasi_bantuan_pendidikanRepository $rekomendasiBantuanPendidikanRepo)
    {
        $this->rekomendasiBantuanPendidikanRepository = $rekomendasiBantuanPendidikanRepo;
    }

    public function getCountKelurahan($name_kelurahan) {
        $countRekomendasiBantuanPendidikan = DB::select("
            SELECT COUNT(rb.id_kelurahan_bantuan_pendidikans) AS counted
            FROM rekomendasi_bantuan_pendidikans AS rb
            INNER JOIN indonesia_villages AS iv ON rb.id_kelurahan_bantuan_pendidikans = iv.code
            WHERE iv.name_village = ?
            GROUP BY rb.id_kelurahan_bantuan_pendidikans;
        ", [$name_kelurahan]);
    
        // Check if the result is empty, and if so, set counted to 0
        if (empty($countRekomendasiBantuanPendidikan)) {
            $countRekomendasiBantuanPendidikan[0] = ['counted' => 0];
        }
    
        $countRekomendasiBantuanPendidikanTeruskan = DB::select("
            SELECT COUNT(rb.id_kelurahan_bantuan_pendidikans) AS counted
            FROM rekomendasi_bantuan_pendidikans AS rb
            INNER JOIN indonesia_villages AS iv ON rb.id_kelurahan_bantuan_pendidikans = iv.code
            WHERE iv.name_village = ? AND rb.status_alur_bantuan_pendidikans = 'Teruskan'
            GROUP BY rb.id_kelurahan_bantuan_pendidikans;
        ", [$name_kelurahan]);
        
        // Check if the result is empty, and if so, set counted to 0
        if (empty($countRekomendasiBantuanPendidikanTeruskan)) {
            $countRekomendasiBantuanPendidikanTeruskan[0] = ['counted' => 0];
        }
    
        // $countRekomendasiBantuanPendidikanSelesai = DB::select("
        //     SELECT COUNT(rb.status_alur_bantuan_pendidikans) AS counted
        //     FROM rekomendasi_bantuan_pendidikans AS rb
        //     INNER JOIN indonesia_villages AS iv ON rb.id_kelurahan_bantuan_pendidikans = iv.code
        //     WHERE rb.status_alur_bantuan_pendidikans = 'Selesai'
        //     WHERE iv.name_village = ?
        //     GROUP BY rb.id_kelurahan_bantuan_pendidikans;
        //     ", [$name_kelurahan]);
        $countRekomendasiBantuanPendidikanSelesai = DB::select("
            SELECT COUNT(rb.id_kelurahan_bantuan_pendidikans) AS counted
            FROM rekomendasi_bantuan_pendidikans AS rb
            INNER JOIN indonesia_villages AS iv ON rb.id_kelurahan_bantuan_pendidikans = iv.code
            WHERE iv.name_village = ? AND rb.status_alur_bantuan_pendidikans = 'Selesai'
            GROUP BY rb.id_kelurahan_bantuan_pendidikans;
        ", [$name_kelurahan]);
        
        // Check if the result is empty, and if so, set counted to 0
        if (empty($countRekomendasiBantuanPendidikanSelesai)) {
            $countRekomendasiBantuanPendidikanSelesai[0] = ['counted' => 0];
        }
    
        $countRekomendasiBantuanPendidikanDraft = DB::select("
            SELECT COUNT(rb.status_alur_bantuan_pendidikans) AS counted
            FROM rekomendasi_bantuan_pendidikans AS rb
            INNER JOIN indonesia_villages AS iv ON rb.id_kelurahan_bantuan_pendidikans = iv.code
            WHERE rb.status_alur_bantuan_pendidikans = 'Draft'
            GROUP BY rb.id_kelurahan_bantuan_pendidikans;
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
     * Display a listing of the rekomendasi_bantuan_pendidikan.
     */
    public function index(Request $request)
    {
        $rekomendasiBantuanPendidikans = $this->rekomendasiBantuanPendidikanRepository->paginate(10);

        return view('rekomendasi_bantuan_pendidikans.index')
            ->with('rekomendasiBantuanPendidikans', $rekomendasiBantuanPendidikans);
    }
    public function fileSuratPendidikan($id)
    {
        $adminduk = DB::table('rekomendasi_bantuan_pendidikans as w')->select(
            'w.*',
            'dtks.Id_DTKS',
            'b.name_village',
            'kecamatan.name_districts'
        )
            ->leftjoin('dtks', 'dtks.Nik', '=', 'w.nik_bantuan_pendidikans')
            ->leftjoin('indonesia_districts as kecamatan', 'kecamatan.code', '=', 'w.id_kecamatan_bantuan_pendidikans')
            ->leftjoin('indonesia_villages as b', 'b.code', '=', 'w.id_kelurahan_bantuan_pendidikans')
            ->where('w.id', $id)->first();
        // dd($adminduk);

        $getIdDtks = DB::table('rekomendasi_bantuan_pendidikans as w')->select(
            'w.*',
            'dtks.Id_DTKS'
        )
            ->leftjoin('dtks', 'dtks.Nik', '=', 'w.nik_bantuan_pendidikans')
            // ->where('status_wilayah', '1')
            ->where('dtks.Nik', $adminduk->nik_bantuan_pendidikans)->first();
        // dd($getIdDtks);
        
        if (!is_null($getIdDtks) && !is_null($getIdDtks->Id_DTKS)) {
            $data_dtks = $getIdDtks->Id_DTKS;
        } else {
            $data_dtks = '-';
        }

       $date = Carbon::parse($adminduk->tgl_lahir_bantuan_pendidikans)->locale('id');

       $date->settings(['formatFunction' => 'translatedFormat']);

       $tanggal = $date->format('j F Y ');


       // dd($tanggal);
        $pdf = PDF::loadHtml(view('rekomendasi_bantuan_pendidikans.file_permohonan',compact('adminduk','tanggal','data_dtks')));
        // $pdf->setPaper('F4', 'portrait');
        $filename = 'File Permohonan' . $adminduk->nama_bantuan_pendidikans . '.pdf';
        return $pdf->stream($filename);
    }




    /**
     * Show the form for creating a new rekomendasi_bantuan_pendidikan.
     */
    public function create()
    {
        $v = rekomendasi_bantuan_pendidikan::latest()->first();
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
            // Jika user memiliki role 'Kadus', maka tampilkan alur dengan nama 'Selesai' dan 'Tolak'
            $alur = DB::table('alur')
                ->whereIn('name', ['Selesai', 'Tolak'])
                ->get();
        } else {
            // Jika user tidak memiliki role yang sesuai, maka tampilkan alur kosong
            $alur = collect();
        }


        $user = Auth::user()->id;
        $checkuserrole = DB::table('model_has_roles')
        ->leftjoin('roles', 'roles.id', '=', 'model_has_roles.role_id')
        ->where('model_id', '=', $user)
        ->first();
        // dd($user);
        // $roles = $user->roles()->pluck('name');
        if ($checkuserrole->name == 'fasilitator') {
            $roleid = DB::table('roles')
                ->where('name', 'Back Ofiice Kelurahan')
                // ->where('name', 'supervisor')
                ->orWhere('name', 'supervisor')
                ->get();
                // dd($roles);
        }elseif($checkuserrole->name == 'Front Office kota') {
            // dd($roles->name);
            $roleid = DB::table('roles')
                ->where('name', 'Back Ofiice Kota')
                // ->where('name', 'supervisor')
                // ->orWhere('name', 'supervisor')
                ->get();
            // dd($roles);
        }elseif ($checkuserrole->name == 'Front Office Kelurahan') {
            $roleid = DB::table('roles')
                ->where('name', 'Back Ofiice Kota')
                ->get();
        }
        $checkroles = Roles::where('name', 'Front Office kota')
            ->orWhere('name', 'SekertarisDinas')
            ->orWhere('name', 'KepalaDinas')
            ->get();
        return view('rekomendasi_bantuan_pendidikans.create', compact('kecamatans','wilayah', 'roleid', 'checkuserrole', 'alur'));
        // return view('rekomendasi_bantuan_pendidikans.create');
    }

    /**
     * Store a newly created rekomendasi_bantuan_pendidikan in storage.
     */
    public function store(Request $request)
    {
        // $request->validate([
        //     // 'status_alur_bantuan_pendidikans' => 'required|in:Draft', // Tambahkan validasi untuk status_alur_bantuan_pendidikans
        //     'status_dtks_bantuan_pendidikans' => 'required|in:Terdaftar', // Tambahkan validasi untuk status_dtks_bantuan_pendidikans
        //     'file_ktp_terlapor_bantuan_pendidikans' => 'required|file|mimes:jpeg,png,pdf|max:2048',
        //     'file_kk_terlapor_bantuan_pendidikans' => 'required|file|mimes:jpeg,png,pdf|max:2048',
        //     'file_keterangan_dtks_bantuan_pendidikans' => 'required|file|mimes:jpeg,png,pdf|max:2048',
        //     // 'file_pendukung_bantuan_pendidikans' => 'required|file|mimes:jpeg,png,pdf|max:2048',
        //     'id_provinsi_bantuan_pendidikans' => 'required',
        //     'id_kabkot_bantuan_pendidikans' => 'required',
        //     'id_kecamatan_bantuan_pendidikans' => 'required',
        //     'id_kelurahan_bantuan_pendidikans' => 'required',
        //     'jenis_pelapor_bantuan_pendidikans' => 'required',
        //     'ada_nik_bantuan_pendidikans' => 'required',
        //     'nik_bantuan_pendidikans' => 'required_if:ada_nik_bantuan_pendidikans,true', // Hanya valid jika ada_nik_bantuan_pendidikans bernilai true
        //     // 'no_kk_bantuan_pendidikans' => 'required',
        //     'nama_bantuan_pendidikans' => 'required',
        //     'tgl_lahir_bantuan_pendidikans' => 'required|date',
        //     'tempat_lahir_bantuan_pendidikans' => 'required',
        //     'jenis_kelamin_bantuan_pendidikans' => 'required|in:Laki-Laki,Perempuan',
        //     'telp_bantuan_pendidikans' => 'required',
        //     'alamat_bantuan_pendidikans' => 'required',
        //     'catatan_bantuan_pendidikans' => 'nullable',
        //     'tujuan_bantuan_pendidikans' => 'required',
        //     'status_alur_bantuan_pendidikans' => 'required',
        //     'petugas_bantuan_pendidikans' => 'required',
        //     // Tambahkan validasi untuk field lainnya sesuai kebutuhan
        // ]);   
        if ($request->get('status_alur_bantuan_pendidikans') != 'Draft') {
             // jika status_alur_bantuan_pendidikans sama dengan Draft akan nmasuk kondisi sini
            if ($request->get('status_dtks_bantuan_pendidikans') == 'Terdaftar') {
                // dd($request->all()); 

                // jika status_dtks_bantuan_pendidikans sama dengan terdaftar akan nmasuk kondisi sini
                $data = new rekomendasi_bantuan_pendidikan();
                if($request->file('file_ktp_terlapor_bantuan_pendidikans')){
                    // dd($request->file('tl_file'));
                   
                    $path = $request->file('file_ktp_terlapor_bantuan_pendidikans');
                    $filname = 'bantuan-pendidikan/ktp/'.$path->getClientOriginalName();
                    // dd($filname);
                    // $update['filename'] = $filname;
                    $return = Storage::disk('imagekit')->put($filname, fopen($path->getRealPath(), 'r') );
                    $data->file_ktp_terlapor_bantuan_pendidikans =  Storage::disk('imagekit')->url($filname);
                    // dd($data);
                }
                if($request->file('file_kk_terlapor_bantuan_pendidikans')){
                    // dd($request->file('tl_file'));
                                        
                    $path = $request->file('file_kk_terlapor_bantuan_pendidikans');
                    $filname = 'bantuan-pendidikan/kk/'.$path->getClientOriginalName();
                    // dd($filname);
                    // $update['filename'] = $filname;
                    $return = Storage::disk('imagekit')->put($filname, fopen($path->getRealPath(), 'r') );
                    $data->file_kk_terlapor_bantuan_pendidikans =  Storage::disk('imagekit')->url($filname);
                    // dd($data);
                }
                if($request->file('file_pendukung_bantuan_pendidikans')){
                     // dd($request->file('tl_file'));
                   
                     $path = $request->file('file_pendukung_bantuan_pendidikans');
                     $filname = 'bantuan-pendidikan/file_pendukung/'.$path->getClientOriginalName();
                     // dd($filname);
                     // $update['filename'] = $filname;
                     $return = Storage::disk('imagekit')->put($filname, fopen($path->getRealPath(), 'r') );
                     $data->file_pendukung_bantuan_pendidikans =  Storage::disk('imagekit')->url($filname);
                     // dd($data);
                }
                if($request->file('file_keterangan_dtks_bantuan_pendidikans')){
                    // dd($request->file('tl_file'));
                  
                    $path = $request->file('file_keterangan_dtks_bantuan_pendidikans');
                    $filname = 'bantuan-pendidikan/file_keterangan_dtks/'.$path->getClientOriginalName();
                    // dd($filname);
                    // $update['filename'] = $filname;
                    $return = Storage::disk('imagekit')->put($filname, fopen($path->getRealPath(), 'r') );
                    $data->file_keterangan_dtks_bantuan_pendidikans =  Storage::disk('imagekit')->url($filname);
                    // dd($data);
               }
                // $data->id_alur = $request->get('id_alur_bantuan_pendidikans');
                $data->no_pendaftaran_bantuan_pendidikans = mt_rand(100, 1000);
                $data->id_provinsi_bantuan_pendidikans = $request->get('id_provinsi_bantuan_pendidikans');
                $data->id_kabkot_bantuan_pendidikans = $request->get('id_kabkot_bantuan_pendidikans');
                $data->id_kecamatan_bantuan_pendidikans = $request->get('id_kecamatan_bantuan_pendidikans');
                $data->id_kelurahan_bantuan_pendidikans = $request->get('id_kelurahan_bantuan_pendidikans');
                $data->jenis_pelapor_bantuan_pendidikans = $request->get('jenis_pelapor_bantuan_pendidikans');
                $data->ada_nik_bantuan_pendidikans = $request->get('ada_nik_bantuan_pendidikans');
                $data->nik_bantuan_pendidikans = $request->get('nik_bantuan_pendidikans');
                $data->nama_sekolah = $request->get('nama_sekolah');
                // $data->no_kis = $request->get('no_kis');
                $data->nama_bantuan_pendidikans = $request->get('nama_bantuan_pendidikans');
                $data->tgl_lahir_bantuan_pendidikans = $request->get('tgl_lahir_bantuan_pendidikans');
                $data->tempat_lahir_bantuan_pendidikans = $request->get('tempat_lahir_bantuan_pendidikans');
                $data->alamat_bantuan_pendidikans = $request->get('alamat_bantuan_pendidikans');
                $data->telp_bantuan_pendidikans = $request->get('telp_bantuan_pendidikans');
                // $data->email = $request->get('email');
                // $data->hubungan_terlapor = $request->get('hubungan_terlapor');
                // $data->id_program_sosial = $request->get('id_program_sosial');
                // $data->id_program_sosial = json_encode($request->input('id_program_sosial'));            
                // $data->kepesertaan_program = $request->get('kepesertaan_program');
                // $data->no_peserta = json_encode($request->input('no_peserta'));
                // $data->level_program = $request->get('level_program');
                // $data->sektor_program = $request->get('sektor_program');
                // $data->no_kartu_program = $request->get('no_kartu_program');
                // $data->ringkasan_pengaduan  = $request->get('ringkasan_pengaduan');
                $data->jenis_kelamin_bantuan_pendidikans  = $request->get('jenis_kelamin_bantuan_pendidikans');
                $data->catatan_bantuan_pendidikans  = $request->get('catatan_bantuan_pendidikans');
                $data->status_dtks_bantuan_pendidikans = $request->get('status_dtks_bantuan_pendidikans');
                $data->tujuan_bantuan_pendidikans = $request->get('tujuan_bantuan_pendidikans');
                $data->status_alur_bantuan_pendidikans = $request->get('status_alur_bantuan_pendidikans'); 
                $data->petugas_bantuan_pendidikans = $request->get('petugas_bantuan_pendidikans'); 
                $data->createdby_bantuan_pendidikans = Auth::user()->id;
                $data->updatedby_bantuan_pendidikans = Auth::user()->id;
              
                $data->save();
                // dd($data);
                $logpengaduan = new logpendidikan();
                $logpengaduan['id_trx_log_bantuan_pendidikans'] = $data->id;
                $logpengaduan['id_alur_log_bantuan_pendidikans'] =  $data->status_alur_bantuan_pendidikans;
                $logpengaduan['petugas_log_bantuan_pendidikans'] = $data->petugas_bantuan_pendidikans; 
                $logpengaduan['catatan_log_bantuan_pendidikans']  =  $data->catatan_bantuan_pendidikans ;
                $logpengaduan['file_permohonan_bantuan_pendidikans'] = $data->file_keterangan_dtks_bantuan_pendidikans;
                $logpengaduan['tujuan_log_bantuan_pendidikans'] =  $data->tujuan_bantuan_pendidikans;
                $logpengaduan['updated_by_log_bantuan_pendidikans'] = Auth::user()->id;
                $logpengaduan['updated_by_log_bantuan_pendidikans'] = Auth::user()->id;
                // dd($logpengaduan);
                $logpengaduan->save();
                if ($request->get('jenis_pelapor_bantuan_pendidikans') == 'Orang Lain') {
                    $pelapor = new pelapor();
                    $pelapor['id_menu'] = '07';
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
                    $pelapor['id_menu'] = '07';
                    $pelapor['id_form'] = $data->id;
                    $pelapor['jenis_peelaporan'] = $request->get('jenis_pelapor_bantuan_pendidikans');
                    $pelapor['nama_pelapor']  =  $request->get('nama_bantuan_pendidikans');
                    $pelapor['ada_nik_pelapor'] = $request->get('ada_nik_bantuan_pendidikans');
                    $pelapor['nik_pelapor'] = $request->get('nik_bantuan_pendidikans');
                    $pelapor['status_dtks_pelapor'] = $request->get('status_dtks_bantuan_pendidikans');
                    $pelapor['tempat_lahir_pelapor'] = $request->get('tempat_lahir_bantuan_pendidikans');
                    $pelapor['tanggal_lahir_pelapor'] = $request->get('tgl_lahir_bantuan_pendidikans');
                    $pelapor['jenis_kelamin'] = $request->get('jenis_kelamin_bantuan_pendidikans');
                    // $pelapor['nama_pelapor']  = $request->get('nama_bantuan_pendidikans');
                    $pelapor['telepon_pelapor'] = $request->get('telp_bantuan_pendidikans');
                    $pelapor['alamat_pelapor'] = $request->get('alamat_bantuan_pendidikans');
                    $pelapor['createdby_pelapor'] = Auth::user()->id;
                    $pelapor['updatedby_pelapor'] = Auth::user()->id;
    
                    $pelapor->save();
                } 
                return redirect('rekomendasi_bantuan_pendidikans')->withSuccess('Data Rekomendasi Berhasil Ditambahkan');
            } else {
                  // jika status_dtks_bantuan_pendidikans sama dengan terdaftar akan nmasuk kondisi sini
                $cek = Prelist::where('nik', '=', $request->get('nik_bantuan_pendidikans'))->exists();
                if (!$cek) {
                    // jika nik tidak ada nik akan masuk ke sini
                    $data = new Prelist;
                    $data['id_provinsi'] = $request->get('id_provinsi_bantuan_pendidikans');
                    $data['id_kabkot'] = $request->get('id_kabkot_bantuan_pendidikans');
                    // $data['id_kecamatan_bantuan_pendidikans'] = $request->get('id_kecamatan_bantuan_pendidikans');
                    $data['id_kelurahan'] = $request->get('id_kelurahan_bantuan_pendidikans');
                    $data['nik'] = $request->get('nik_bantuan_pendidikans');
                    // $data['no_kk'] = $request->get('no_kk');
                    // $data['no_kis'] = $request->get('no_kis');
                    $data['nama'] = $request->get('nama_bantuan_pendidikans');
                    $data['tgl_lahir'] = $request->get('tgl_lahir_bantuan_pendidikans');
                    $data['alamat'] = $request->get('alamat_bantuan_pendidikans');
                    $data['telp'] = $request->get('telp_bantuan_pendidikans');
                    // $data['email'] = $request->get('email');
                    $data['status_data'] = 'prelistdtks';
                
                    $data->save();
                    $data = new rekomendasi_bantuan_pendidikan();
                    if($request->file('file_ktp_terlapor_bantuan_pendidikans')){
                        // dd($request->file('tl_file'));
                       
                        $path = $request->file('file_ktp_terlapor_bantuan_pendidikans');
                        $filname = 'bantuan-pendidikan/ktp/'.$path->getClientOriginalName();
                        // dd($filname);
                        // $update['filename'] = $filname;
                        $return = Storage::disk('imagekit')->put($filname, fopen($path->getRealPath(), 'r') );
                        $data->file_ktp_terlapor_bantuan_pendidikans =  Storage::disk('imagekit')->url($filname);
                        // dd($data);
                    }
                    if($request->file('file_kk_terlapor_bantuan_pendidikans')){
                        // dd($request->file('tl_file'));
                                            
                        $path = $request->file('file_kk_terlapor_bantuan_pendidikans');
                        $filname = 'bantuan-pendidikan/kk/'.$path->getClientOriginalName();
                        // dd($filname);
                        // $update['filename'] = $filname;
                        $return = Storage::disk('imagekit')->put($filname, fopen($path->getRealPath(), 'r') );
                        $data->file_kk_terlapor_bantuan_pendidikans =  Storage::disk('imagekit')->url($filname);
                        // dd($data);
                    }
                    if($request->file('file_pendukung_bantuan_pendidikans')){
                         // dd($request->file('tl_file'));
                       
                         $path = $request->file('file_pendukung_bantuan_pendidikans');
                         $filname = 'bantuan-pendidikan/file_pendukung/'.$path->getClientOriginalName();
                         // dd($filname);
                         // $update['filename'] = $filname;
                         $return = Storage::disk('imagekit')->put($filname, fopen($path->getRealPath(), 'r') );
                         $data->file_pendukung_bantuan_pendidikans =  Storage::disk('imagekit')->url($filname);
                         // dd($data);
                    }
                    if($request->file('file_keterangan_dtks_bantuan_pendidikans')){
                        // dd($request->file('tl_file'));
                      
                        $path = $request->file('file_keterangan_dtks_bantuan_pendidikans');
                        $filname = 'bantuan-pendidikan/file_keterangan_dtks_bantuan_pendidikans/'.$path->getClientOriginalName();
                        // dd($filname);
                        // $update['filename'] = $filname;
                        $return = Storage::disk('imagekit')->put($filname, fopen($path->getRealPath(), 'r') );
                        $data->file_keterangan_dtks_bantuan_pendidikans =  Storage::disk('imagekit')->url($filname);
                        // dd($data);
                    }
                    // $data->id_alur = $request->get('id_alur_bantuan_pendidikans');
                    $data->no_pendaftaran_bantuan_pendidikans = mt_rand(100, 1000);
                    $data->id_provinsi_bantuan_pendidikans = $request->get('id_provinsi_bantuan_pendidikans');
                    $data->id_kabkot_bantuan_pendidikans = $request->get('id_kabkot_bantuan_pendidikans');
                    $data->id_kecamatan_bantuan_pendidikans = $request->get('id_kecamatan_bantuan_pendidikans');
                    $data->id_kelurahan_bantuan_pendidikans = $request->get('id_kelurahan_bantuan_pendidikans');
                    $data->jenis_pelapor_bantuan_pendidikans = $request->get('jenis_pelapor_bantuan_pendidikans');
                    $data->ada_nik_bantuan_pendidikans = $request->get('ada_nik_bantuan_pendidikans');
                    $data->nik_bantuan_pendidikans = $request->get('nik_bantuan_pendidikans');
                    $data->nama_sekolah = $request->get('nama_sekolah');
                   // $data->no_kis = $request->get('no_kis');
                    $data->nama_bantuan_pendidikans = $request->get('nama_bantuan_pendidikans');
                    $data->tgl_lahir_bantuan_pendidikans = $request->get('tgl_lahir_bantuan_pendidikans');
                    $data->tempat_lahir_bantuan_pendidikans = $request->get('tempat_lahir_bantuan_pendidikans');
                    $data->alamat_bantuan_pendidikans = $request->get('alamat_bantuan_pendidikans');
                    // $data->telp = $request->get('telpon');
                    $data->telp_bantuan_pendidikans = $request->get('telp_bantuan_pendidikans');
                    // $data->hubungan_terlapor = $request->get('hubungan_terlapor');
                    // $data->id_program_sosial = $request->get('id_program_sosial');
                    // $data->id_program_sosial = json_encode($request->input('id_program_sosial'));            
                    // $data->kepesertaan_program = $request->get('kepesertaan_program');
                    // $data->no_peserta = json_encode($request->input('no_peserta'));
                    // $data->level_program = $request->get('level_program');
                    // $data->sektor_program = $request->get('sektor_program');
                    // $data->no_kartu_program = $request->get('no_kartu_program');
                    // $data->ringkasan_pengaduan  = $request->get('ringkasan_pengaduan');

                    $data->jenis_kelamin_bantuan_pendidikans  = $request->get('jenis_kelamin_bantuan_pendidikans');
                    $data->catatan_bantuan_pendidikans  = $request->get('catatan_bantuan_pendidikans');
                    $data->status_dtks_bantuan_pendidikans = $request->get('status_dtks_bantuan_pendidikans');
                    $data->tujuan_bantuan_pendidikans = $request->get('tujuan_bantuan_pendidikans');
                    $data->status_alur_bantuan_pendidikans = $request->get('status_alur_bantuan_pendidikans'); 
                    $data->petugas_bantuan_pendidikans = $request->get('petugas_bantuan_pendidikans'); 
                    $data->createdby_bantuan_pendidikans = Auth::user()->id;
                    $data->updatedby_bantuan_pendidikans = Auth::user()->id;
                    // dd($data);
                    $data->save();
                    $logpengaduan = new logpendidikan();
                    $logpengaduan['id_trx_log_bantuan_pendidikans'] = $data->id;
                    $logpengaduan['id_alur_log_bantuan_pendidikans'] =  $data->status_alur_bantuan_pendidikans;
                    $logpengaduan['petugas_log_bantuan_pendidikans'] = $data->petugas_bantuan_pendidikans; 
                    $logpengaduan['catatan_log_bantuan_pendidikans']  =  $data->catatan_bantuan_pendidikans ;
                    $logpengaduan['file_permohonan_bantuan_pendidikans'] = $data->file_keterangan_dtks_bantuan_pendidikans;
                    $logpengaduan['tujuan_log_bantuan_pendidikans'] =  $data->tujuan_bantuan_pendidikans;
                    $logpengaduan['updated_by_log_bantuan_pendidikans'] = Auth::user()->id;
                    $logpengaduan['updated_by_log_bantuan_pendidikans'] = Auth::user()->id;
                    // dd($logpengaduan);

                    $logpengaduan->save();
                    if ($request->get('jenis_pelapor_bantuan_pendidikans') == 'Orang Lain') {
                        $pelapor = new pelapor();
                        $pelapor['id_menu'] = '07';
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
                        $pelapor['id_menu'] = '07';
                        $pelapor['id_form'] = $data->id;
                        $pelapor['jenis_peelaporan'] = $request->get('jenis_pelapor_bantuan_pendidikans');
                        $pelapor['nama_pelapor']  =  $request->get('nama_bantuan_pendidikans');
                        $pelapor['ada_nik_pelapor'] = $request->get('ada_nik_bantuan_pendidikans');
                        $pelapor['nik_pelapor'] = $request->get('nik_bantuan_pendidikans');
                        $pelapor['status_dtks_pelapor'] = $request->get('status_dtks_bantuan_pendidikans');
                        $pelapor['tempat_lahir_pelapor'] = $request->get('tempat_lahir_bantuan_pendidikans');
                        $pelapor['tanggal_lahir_pelapor'] = $request->get('tgl_lahir_bantuan_pendidikans');
                        $pelapor['jenis_kelamin'] = $request->get('jenis_kelamin_bantuan_pendidikans');
                        // $pelapor['nama_pelapor']  = $request->get('nama_bantuan_pendidikans');
                        $pelapor['telepon_pelapor'] = $request->get('telp_bantuan_pendidikans');
                        $pelapor['alamat_pelapor'] = $request->get('alamat_bantuan_pendidikans');
                        $pelapor['createdby_pelapor'] = Auth::user()->id;
                        $pelapor['updatedby_pelapor'] = Auth::user()->id;
        
                        $pelapor->save();
                    }
                    return redirect('rekomendasi_bantuan_pendidikans')->withWarning('Data Rekomendasi Berhasil Ditambahkan');
                }else{
                    //jika nik ada di prelist akan masuk ke sini
                    $data = new rekomendasi_bantuan_pendidikan();
                    if($request->file('file_ktp_terlapor_bantuan_pendidikans')){
                        // dd($request->file('tl_file'));
                       
                        $path = $request->file('file_ktp_terlapor_bantuan_pendidikans');
                        $filname = 'bantuan-pendidikan/ktp/'.$path->getClientOriginalName();
                        // dd($filname);
                        // $update['filename'] = $filname;
                        $return = Storage::disk('imagekit')->put($filname, fopen($path->getRealPath(), 'r') );
                        $data->file_ktp_terlapor_bantuan_pendidikans =  Storage::disk('imagekit')->url($filname);
                        // dd($data);
                    }
                    if($request->file('file_kk_terlapor_bantuan_pendidikans')){
                        // dd($request->file('tl_file'));
                                            
                        $path = $request->file('file_kk_terlapor_bantuan_pendidikans');
                        $filname = 'bantuan-pendidikan/kk/'.$path->getClientOriginalName();
                        // dd($filname);
                        // $update['filename'] = $filname;
                        $return = Storage::disk('imagekit')->put($filname, fopen($path->getRealPath(), 'r') );
                        $data->file_kk_terlapor_bantuan_pendidikans =  Storage::disk('imagekit')->url($filname);
                        // dd($data);
                    }
                    if($request->file('file_pendukung_bantuan_pendidikans')){
                         // dd($request->file('tl_file'));
                       
                         $path = $request->file('file_pendukung_bantuan_pendidikans');
                         $filname = 'bantuan-pendidikan/file_pendukung/'.$path->getClientOriginalName();
                         // dd($filname);
                         // $update['filename'] = $filname;
                         $return = Storage::disk('imagekit')->put($filname, fopen($path->getRealPath(), 'r') );
                         $data->file_pendukung_bantuan_pendidikans =  Storage::disk('imagekit')->url($filname);
                         // dd($data);
                    }

                    if($request->file('file_keterangan_dtks_bantuan_pendidikans')){
                        // dd($request->file('tl_file'));
                      
                        $path = $request->file('file_keterangan_dtks_bantuan_pendidikans');
                        $filname = 'bantuan-pendidikan/file_keterangan_dtks/'.$path->getClientOriginalName();
                        // dd($filname);
                        // $update['filename'] = $filname;
                        $return = Storage::disk('imagekit')->put($filname, fopen($path->getRealPath(), 'r') );
                        $data->file_keterangan_dtks_bantuan_pendidikans =  Storage::disk('imagekit')->url($filname);
                        // dd($data);
                   }
                    // $data->id_alur = $request->get('id_alur_bantuan_pendidikans');
                    $data->no_pendaftaran_bantuan_pendidikans = mt_rand(100, 1000);
                    $data->id_provinsi_bantuan_pendidikans = $request->get('id_provinsi_bantuan_pendidikans');
                    $data->id_kabkot_bantuan_pendidikans = $request->get('id_kabkot_bantuan_pendidikans');
                    $data->id_kecamatan_bantuan_pendidikans = $request->get('id_kecamatan_bantuan_pendidikans');
                    $data->id_kelurahan_bantuan_pendidikans = $request->get('id_kelurahan_bantuan_pendidikans');
                    $data->jenis_pelapor_bantuan_pendidikans = $request->get('jenis_pelapor_bantuan_pendidikans');
                    $data->ada_nik_bantuan_pendidikans = $request->get('ada_nik_bantuan_pendidikans');
                    $data->nik_bantuan_pendidikans = $request->get('nik_bantuan_pendidikans');
                    // $data->no_kk_bantuan_pendidikans = $request->get('no_kk_bantuan_pendidikans');
                    // $data->no_kis = $request->get('no_kis');
                    $data->nama_bantuan_pendidikans = $request->get('nama_bantuan_pendidikans');
                    $data->tgl_lahir_bantuan_pendidikans = $request->get('tgl_lahir_bantuan_pendidikans');
                    $data->tempat_lahir_bantuan_pendidikans = $request->get('tempat_lahir_bantuan_pendidikans');
                    $data->alamat_bantuan_pendidikans = $request->get('alamat_bantuan_pendidikans');
                    // $data->telp = $request->get('telpon');
                    $data->telp_bantuan_pendidikans = $request->get('telp_bantuan_pendidikans');
                    // $data->hubungan_terlapor = $request->get('hubungan_terlapor');
                    // $data->id_program_sosial = $request->get('id_program_sosial');
                    // $data->id_program_sosial = json_encode($request->input('id_program_sosial'));            
                    // $data->kepesertaan_program = $request->get('kepesertaan_program');
                    // $data->no_peserta = json_encode($request->input('no_peserta'));
                    // $data->level_program = $request->get('level_program');
                    // $data->sektor_program = $request->get('sektor_program');
                    // $data->no_kartu_program = $request->get('no_kartu_program');
                    // $data->ringkasan_pengaduan  = $request->get('ringkasan_pengaduan');
                    $data->jenis_kelamin_bantuan_pendidikans  = $request->get('detail_pengaduan');
                    $data->catatan_bantuan_pendidikans  = $request->get('catatan_bantuan_pendidikans');
                    $data->status_dtks_bantuan_pendidikans = $request->get('status_dtks_bantuan_pendidikans');
                    $data->tujuan_bantuan_pendidikans = $request->get('tujuan_bantuan_pendidikans');
                    $data->status_alur_bantuan_pendidikans = $request->get('status_alur_bantuan_pendidikans'); 
                    $data->petugas_bantuan_pendidikans = $request->get('petugas_bantuan_pendidikans'); 
                    $data->createdby_bantuan_pendidikans = Auth::user()->id;
                    $data->updatedby_bantuan_pendidikans = Auth::user()->id;
                    // dd($data);
                    $data->save();
                    $logpengaduan = new logpendidikan();
                    $logpengaduan['id_trx_log_bantuan_pendidikans'] = $data->id;
                    $logpengaduan['id_alur_log_bantuan_pendidikans'] =  $data->status_alur_bantuan_pendidikans;
                    $logpengaduan['petugas_log_bantuan_pendidikans'] = $data->petugas_bantuan_pendidikans; 
                    $logpengaduan['catatan_log_bantuan_pendidikans']  =  $data->catatan_bantuan_pendidikans ;
                    $logpengaduan['file_permohonan_bantuan_pendidikans'] = $data->file_keterangan_dtks_bantuan_pendidikans;
                    $logpengaduan['tujuan_log_bantuan_pendidikans'] =  $data->tujuan_bantuan_pendidikans;
                    $logpengaduan['updated_by_log_bantuan_pendidikans'] = Auth::user()->id;
                    $logpengaduan['updated_by_log_bantuan_pendidikans'] = Auth::user()->id;
                    // dd($logpengaduan);
                    $logpengaduan->save();
                    if ($request->get('jenis_pelapor_bantuan_pendidikans') == 'Orang Lain') {
                        $pelapor = new pelapor();
                        $pelapor['id_menu'] = '07';
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
                        $pelapor['id_menu'] = '07';
                        $pelapor['id_form'] = $data->id;
                        $pelapor['jenis_peelaporan'] = $request->get('jenis_pelapor_bantuan_pendidikans');
                        $pelapor['nama_pelapor']  =  $request->get('nama_bantuan_pendidikans');
                        $pelapor['ada_nik_pelapor'] = $request->get('ada_nik_bantuan_pendidikans');
                        $pelapor['nik_pelapor'] = $request->get('nik_bantuan_pendidikans');
                        $pelapor['status_dtks_pelapor'] = $request->get('status_dtks_bantuan_pendidikans');
                        $pelapor['tempat_lahir_pelapor'] = $request->get('tempat_lahir_bantuan_pendidikans');
                        $pelapor['tanggal_lahir_pelapor'] = $request->get('tgl_lahir_bantuan_pendidikans');
                        $pelapor['jenis_kelamin'] = $request->get('jenis_kelamin_bantuan_pendidikans');
                        // $pelapor['nama_pelapor']  = $request->get('nama_bantuan_pendidikans');
                        $pelapor['telepon_pelapor'] = $request->get('telp_bantuan_pendidikans');
                        $pelapor['alamat_pelapor'] = $request->get('alamat_bantuan_pendidikans');
                        $pelapor['createdby_pelapor'] = Auth::user()->id;
                        $pelapor['updatedby_pelapor'] = Auth::user()->id;
        
                        $pelapor->save();
                    }
                    return redirect('rekomendasi_bantuan_pendidikans')->withWarning('Data Rekomendasi Berhasil Ditambahkan');
                }
            }
        } else {
            //jika status draft adalah ini akan masuk ke sini
            $data = new rekomendasi_bantuan_pendidikan();
                if($request->file('file_ktp_terlapor_bantuan_pendidikans')){
                    // dd($request->file('tl_file'));
                
                    $path = $request->file('file_ktp_terlapor_bantuan_pendidikans');
                    $filname = 'bantuan-pendidikan/ktp/'.$path->getClientOriginalName();
                    // dd($filname);
                    // $update['filename'] = $filname;
                    $return = Storage::disk('imagekit')->put($filname, fopen($path->getRealPath(), 'r') );
                    $data->file_ktp_terlapor_bantuan_pendidikans =  Storage::disk('imagekit')->url($filname);
                    // dd($data);
                }
                if($request->file('file_kk_terlapor_bantuan_pendidikans')){
                    // dd($request->file('tl_file'));
                                        
                    $path = $request->file('file_kk_terlapor_bantuan_pendidikans');
                    $filname = 'bantuan-pendidikan/kk/'.$path->getClientOriginalName();
                    // dd($filname);
                    // $update['filename'] = $filname;
                    $return = Storage::disk('imagekit')->put($filname, fopen($path->getRealPath(), 'r') );
                    $data->file_kk_terlapor_bantuan_pendidikans =  Storage::disk('imagekit')->url($filname);
                    // dd($data);
                }
                if($request->file('file_pendukung_bantuan_pendidikans')){
                    // dd($request->file('tl_file'));
                
                    $path = $request->file('file_pendukung_bantuan_pendidikans');
                    $filname = 'bantuan-pendidikan/file_pendukung/'.$path->getClientOriginalName();
                    // dd($filname);
                    // $update['filename'] = $filname;
                    $return = Storage::disk('imagekit')->put($filname, fopen($path->getRealPath(), 'r') );
                    $data->file_pendukung_bantuan_pendidikans =  Storage::disk('imagekit')->url($filname);
                    // dd($data);
                }
                if($request->file('file_keterangan_dtks_bantuan_pendidikans')){
                    // dd($request->file('tl_file'));
                  
                    $path = $request->file('file_keterangan_dtks_bantuan_pendidikans');
                    $filname = 'bantuan-pendidikan/file_keterangan_dtks/'.$path->getClientOriginalName();
                    // dd($filname);
                    // $update['filename'] = $filname;
                    $return = Storage::disk('imagekit')->put($filname, fopen($path->getRealPath(), 'r') );
                    $data->file_keterangan_dtks_bantuan_pendidikans =  Storage::disk('imagekit')->url($filname);
                    // dd($data);
               }
                    // $data->id_alur = $request->get('id_alur_bantuan_pendidikans');
                    $data->no_pendaftaran_bantuan_pendidikans = mt_rand(100, 1000);
                    $data->id_provinsi_bantuan_pendidikans = $request->get('id_provinsi_bantuan_pendidikans');
                    $data->id_kabkot_bantuan_pendidikans = $request->get('id_kabkot_bantuan_pendidikans');
                    $data->id_kecamatan_bantuan_pendidikans = $request->get('id_kecamatan_bantuan_pendidikans');
                    $data->id_kelurahan_bantuan_pendidikans = $request->get('id_kelurahan_bantuan_pendidikans');
                    $data->jenis_pelapor_bantuan_pendidikans = $request->get('jenis_pelapor_bantuan_pendidikans');
                    $data->ada_nik_bantuan_pendidikans = $request->get('ada_nik_bantuan_pendidikans');
                    $data->nik_bantuan_pendidikans = $request->get('nik_bantuan_pendidikans');
                    $data->nama_sekolah = $request->get('nama_sekolah');
                    // $data->no_kk_bantuan_pendidikans = $request->get('no_kk_bantuan_pendidikans');
                    // $data->no_kis = $request->get('no_kis');
                    $data->nama_bantuan_pendidikans = $request->get('nama_bantuan_pendidikans');
                    $data->tgl_lahir_bantuan_pendidikans = $request->get('tgl_lahir_bantuan_pendidikans');
                    $data->tempat_lahir_bantuan_pendidikans = $request->get('tempat_lahir_bantuan_pendidikans');
                    $data->alamat_bantuan_pendidikans = $request->get('alamat_bantuan_pendidikans');
                    // $data->telp = $request->get('telpon');
                    $data->telp_bantuan_pendidikans = $request->get('telp_bantuan_pendidikans');
                    // $data->hubungan_terlapor = $request->get('hubungan_terlapor');
                    // $data->id_program_sosial = $request->get('id_program_sosial');
                    // $data->id_program_sosial = json_encode($request->input('id_program_sosial'));            
                    // $data->kepesertaan_program = $request->get('kepesertaan_program');
                    // $data->no_peserta = json_encode($request->input('no_peserta'));
                    // $data->level_program = $request->get('level_program');
                    // $data->sektor_program = $request->get('sektor_program');
                    // $data->no_kartu_program = $request->get('no_kartu_program');
                    // $data->ringkasan_pengaduan  = $request->get('ringkasan_pengaduan');
                    $data->jenis_kelamin_bantuan_pendidikans  = $request->get('jenis_kelamin_bantuan_pendidikans');
                    $data->catatan_bantuan_pendidikans  = $request->get('catatan_bantuan_pendidikans');
                    $data->status_dtks_bantuan_pendidikans = $request->get('status_dtks_bantuan_pendidikans');
                    $data->tujuan_bantuan_pendidikans = $request->get('tujuan_bantuan_pendidikans');
                    $data->status_alur_bantuan_pendidikans = $request->get('status_alur_bantuan_pendidikans'); 
                    $data->petugas_bantuan_pendidikans = $request->get('petugas_bantuan_pendidikans'); 
                    $data->createdby_bantuan_pendidikans = Auth::user()->id;
                    $data->updatedby_bantuan_pendidikans = Auth::user()->id;
              
                    $data->save();
                    if ($request->get('jenis_pelapor_bantuan_pendidikans') == 'Orang Lain') {
                        // dd($request->get('jenis_pelapor_bantuan_pendidikans'));
                        $pelapor = new pelapor();
                        $pelapor['id_menu'] = '07';
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
                        // dd($pelapor);
                        $pelapor->save();
                    }else{
                        $pelapor = new pelapor();
                        $pelapor['id_menu'] = '07';
                        $pelapor['id_form'] = $data->id;
                        $pelapor['jenis_peelaporan'] = $request->get('jenis_pelapor_bantuan_pendidikans');
                        $pelapor['nama_pelapor']  =  $request->get('nama_bantuan_pendidikans');
                        $pelapor['ada_nik_pelapor'] = $request->get('ada_nik_bantuan_pendidikans');
                        $pelapor['nik_pelapor'] = $request->get('nik_bantuan_pendidikans');
                        $pelapor['status_dtks_pelapor'] = $request->get('status_dtks_bantuan_pendidikans');
                        $pelapor['tempat_lahir_pelapor'] = $request->get('tempat_lahir_bantuan_pendidikans');
                        $pelapor['tanggal_lahir_pelapor'] = $request->get('tgl_lahir_bantuan_pendidikans');
                        $pelapor['jenis_kelamin'] = $request->get('jenis_kelamin_bantuan_pendidikans');
                        // $pelapor['nama_pelapor']  = $request->get('nama_bantuan_pendidikans');
                        $pelapor['telepon_pelapor'] = $request->get('telp_bantuan_pendidikans');
                        $pelapor['alamat_pelapor'] = $request->get('alamat_bantuan_pendidikans');
                        $pelapor['createdby_pelapor'] = Auth::user()->id;
                        $pelapor['updatedby_pelapor'] = Auth::user()->id;
        
                        $pelapor->save();
                    }
                    return redirect('rekomendasi_bantuan_pendidikans')->withWarning('NIK Tidak Tersedia Data Disimpan sebagai draft');
        }
    }

    /**
     * Display the specified rekomendasi_bantuan_pendidikan.
     */
    public function show($id)
    {
        // dd($rekomendasi_bantuan_pendidikans);
        $DetailRekomendasiBantuanPendidikan = DB::table('rekomendasi_bantuan_pendidikans as w')->select(
            'w.*',
            'b.name_village',
            'prov.name_prov',
            'kota.name_cities',
            'kecamatan.name_districts',
            'roles.name as name_roles',
            'users.name'
            // 'w.status_wilayah',
        )
        ->leftjoin('users', 'users.id', '=', 'w.petugas_bantuan_pendidikans')
        ->leftjoin('roles', 'roles.id', '=', 'w.tujuan_bantuan_pendidikans')
        ->leftJoin('pelapor','pelapor.id_form','=','w.id')
        ->leftjoin('indonesia_provinces as prov', 'prov.code', '=', 'w.id_provinsi_bantuan_pendidikans')
        ->leftjoin('indonesia_cities as kota', 'kota.code', '=', 'w.id_kabkot_bantuan_pendidikans')
        ->leftjoin('indonesia_districts as kecamatan', 'kecamatan.code', '=', 'w.id_kecamatan_bantuan_pendidikans')
        ->leftjoin('indonesia_villages as b', 'b.code', '=', 'w.id_kelurahan_bantuan_pendidikans')
        ->where('pelapor.id_menu', '07')
        ->where('w.id', $id)->first();
        // dd($DetailRekomendasiBantuanPendidikan);
        $DetailLogBantuanPendidikan = DB::table('log_bantuan_pendidikan as w')->select(
            'w.*',
            'rls.name as name_update',
            'usr.name',
            'roles.name as name_roles',

        )
            ->leftjoin('roles', 'roles.id', '=', 'w.tujuan_log_bantuan_pendidikans')
            ->leftjoin('users as rls', 'rls.id', '=', 'w.updated_by_log_bantuan_pendidikans')
            ->leftjoin('users as usr', 'usr.id', '=', 'w.updated_by_log_bantuan_pendidikans')
            ->where('w.id_trx_log_bantuan_pendidikans', $id)->orderBy('w.created_at', 'asc')->get();
        // dd($DetailLogBantuanPendidikan);
        $DetailRekomendasiBantuanPendidikanPelapor = DB::table('rekomendasi_bantuan_pendidikans')
        ->join('pelapor', function ($join) use ($id) {
            $join->on('rekomendasi_bantuan_pendidikans.id', '=', 'pelapor.id_form')
                ->where('rekomendasi_bantuan_pendidikans.id', '=', $id);
        })
        ->select('rekomendasi_bantuan_pendidikans.*', 'pelapor.*')
        ->where('pelapor.id_menu', '07')
        ->where('pelapor.id_form', $id)
        ->first();


        return view('rekomendasi_bantuan_pendidikans.show', compact('DetailRekomendasiBantuanPendidikanPelapor','DetailLogBantuanPendidikan','DetailRekomendasiBantuanPendidikan'));

    }
    
    public function file_kk_terlapor_bantuan_pendidikans(rekomendasi_bantuan_pendidikan $rekomendasi_bantuan_pendidikan)
    {
    //    dd($rekomendasi_bantuan_pendidikan->id);
        $data2 = DB::table('rekomendasi_bantuan_pendidikans as w')->select(
            'w.file_kk_terlapor_bantuan_pendidikans',
            'w.file_keterangan_dtks_bantuan_pendidikans',
            'w.file_pendukung_bantuan_pendidikans',
            'w.file_ktp_terlapor_bantuan_pendidikans',
        )
        // ->leftjoin('indonesia_provinces as prov', 'prov.code', '=', 'w.id_provinsi_bantuan_pendidikans')
        // ->leftjoin('indonesia_cities as kota', 'kota.code', '=', 'w.id_kabkot_bantuan_pendidikans')
        // ->leftjoin('indonesia_districts as kecamatan', 'kecamatan.code', '=', 'w.id_kecamatan_bantuan_pendidikans')
        // ->leftjoin('indonesia_villages as b', 'b.code', '=', 'w.id_kelurahan_bantuan_pendidikans')
        ->where('w.id', $rekomendasi_bantuan_pendidikan->id)->first();
        // dd($data2);
        $data = [
            'data' => $data2
            // 'data' => $data2
          ];
        return response()->json($data);
    }
    /**
     * Show the form for editing the specified rekomendasi_bantuan_pendidikan.
     */
    public function edit($id)
    {
        $userid = Auth::user()->id;
        $getAuth = DB::table('model_has_roles')
        ->leftJoin('users', 'users.id', '=', 'model_has_roles.model_id')
        ->leftJoin('roles', 'roles.id', '=', 'model_has_roles.role_id')
        ->where('model_id', $userid)
        ->get();
        // dd($getAuth);
        
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
            // ->leftjoin('rekomendasi_bantuan_pendidikans as rbp', 'rbp.', '=', 'w.province_id')
            ->leftjoin('indonesia_provinces as prov', 'prov.code', '=', 'w.province_id')
            ->leftjoin('indonesia_cities as kota', 'kota.code', '=', 'w.kota_id')
            ->leftjoin('indonesia_districts as kecamatan', 'kecamatan.code', '=', 'w.kecamatan_id')
            ->leftjoin('indonesia_villages as b', 'b.code', '=', 'w.kelurahan_id')
            ->where('status_wilayah', '1')
            ->where('w.createdby', $userid)->get();


        $checkuserrole = DB::table('model_has_roles')
            ->leftjoin('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('model_id', '=', $userid)
            ->first();
        
        
        // $rekomendasiBantuanPendidikan = rekomendasi_bantuan_pendidikan::where('id', $id)->first();
        $rekomendasiBantuanPendidikan = DB::table('rekomendasi_bantuan_pendidikans as w')->select(
            'w.*',
            'roles.name as name_roles',
            'users.name as name_pembuat',
            'prov.name_prov',
            'kota.name_cities',
            'kecamatan.name_districts',
            'b.name_village',
            'p.*'
        )
            ->leftjoin('model_has_roles', 'model_has_roles.model_id', '=', 'w.createdby_bantuan_pendidikans')
            ->leftjoin('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->leftjoin('users', 'users.id', '=', 'w.createdby_bantuan_pendidikans')
            // ->leftjoin('roles as rls', 'rls.id', '=', 'w.tujuan_bantuan_pendidikans')
            // ->leftjoin('users as usr', 'usr.id', '=', 'w.petugas_bantuan_pendidikans')
            ->leftjoin('pelapor as p', 'p.id_form', 'w.id')
            ->leftjoin('indonesia_provinces as prov', 'prov.code', '=', 'w.id_provinsi_bantuan_pendidikans')
            ->leftjoin('indonesia_cities as kota', 'kota.code', '=', 'w.id_kabkot_bantuan_pendidikans')
            ->leftjoin('indonesia_districts as kecamatan', 'kecamatan.code', '=', 'w.id_kecamatan_bantuan_pendidikans')
            ->leftjoin('indonesia_villages as b', 'b.code', '=', 'w.id_kelurahan_bantuan_pendidikans')
            ->where('p.id_menu', '07')
            ->where('w.id', $id)->first();
        // dd($rekomendasiBantuanPendidikan);
        $getdata = DB::table('model_has_roles')
            ->leftjoin('rekomendasi_bantuan_pendidikans as b', 'b.tujuan_bantuan_pendidikans', '=', 'model_has_roles.role_id')
            ->where('b.id', $id)
            ->get();
        // dd($getdata);
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
        } else {
            // Jika user tidak memiliki role yang sesuai, maka tampilkan alur kosong
            $alur = collect();
        }


        $user = Auth::user();
        $roles = $user->roles()->pluck('name');

        if ($roles->contains('Front Office Kelurahan')) {
            $roleid = DB::table('roles')
            ->where('name', 'Back Ofiice Kelurahan')
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
                ->where('name', 'supervisor')
                ->get();
        }else if ($roles->contains('Back Ofiice Kota')) {
            $roleid = DB::table('roles')
                ->where('name', 'kepala bidang')
                ->get();
        }else if ($roles->contains('kepala bidang')) {
            $roleid = DB::table('roles')
                ->where('name', 'Back Ofiice Kota')->get();
            
        }
        $checkroles2 = DB::table('model_has_roles')
            ->leftjoin('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->leftjoin('rekomendasi_bantuan_pendidikans', 'rekomendasi_bantuan_pendidikans.createdby_bantuan_pendidikans', '=', 'model_has_roles.model_id')
            ->where('rekomendasi_bantuan_pendidikans.id', '=', $id)
            // ->where('status_aksi', '=', 'Draft')
            // ->orwhere('status_aksi', '=', 'Teruskan')
            ->get();
        $checkroles = DB::table('model_has_roles')
            ->leftjoin('roles', 'roles.id', '=', 'model_has_roles.role_id')
            // ->leftjoin('pengaduans', 'pengaduans.createdby', '=', 'model_has_roles.model_id')
            ->where('model_has_roles.model_id', '=', auth::user()->id)
            // ->where('status_aksi', '=', 'Draft')
            // ->orwhere('status_aksi', '=', 'Teruskan')
            ->get();
        // dd($checkroles); 
        $rolebackoffice = DB::table('roles')
            ->where('name', 'Back Ofiice kelurahan')
            // ->where('name', 'supervisor')
            // ->orWhere('name', 'supervisor')
            ->get();


        return view('rekomendasi_bantuan_pendidikans.edit', compact('getAuth','wilayah', 'rekomendasiBantuanPendidikan', 'roleid', 'getdata', 'alur', 'checkroles', 'rolebackoffice','checkroles2'));

    }

    /**
     * Update the specified rekomendasi_bantuan_pendidikan in storage.
     */
    public function update(Request $request,$id)
    {
        $databantuanpendidikans = rekomendasi_bantuan_pendidikan::where('id', $id)->first();
        $rekomendasiBantuanPendidikans = DB::table('rekomendasi_bantuan_pendidikans as w')
            ->join('model_has_roles', 'model_has_roles.model_id', '=', 'w.createdby_bantuan_pendidikans')
            ->join('roles as rls', 'rls.id', '=', 'model_has_roles.role_id')

            ->leftjoin('users', 'users.id', '=', 'w.createdby_bantuan_pendidikans')
            ->select(
                        'w.*',
                        'rls.name as name_roles',
                        // 'usr.name',
                        'model_has_roles.*')
            ->where('w.id', $id)->first();
        if ($request->get('status_alur_bantuan_pendidikans') == 'Draft') {
               if($request->file('file_ktp_terlapor_bantuan_pendidikans')){
                   dd($request->file('tl_file'));
                  
                   $path = $request->file('file_ktp_terlapor_bantuan_pendidikans');
                   $filname = 'bantuan-pendidikan/ktp/'.$path->getClientOriginalName();
                   // dd($filname);
                   // $update['filename'] = $filname;
                   $return = Storage::disk('imagekit')->put($filname, fopen($path->getRealPath(), 'r') );
                    //    $datapengaduan->file_ktp_terlapor_bantuan_pendidikans =  Storage::disk('imagekit')->url($filname);
                   $datapengaduan['file_ktp_terlapor_bantuan_pendidikans'] =  Storage::disk('imagekit')->url($filname);
                //    dd($datapengaduan);
               }
               if($request->file('file_kk_terlapor_bantuan_pendidikans')){
                   // dd($request->file('tl_file'));
                                       
                   $path = $request->file('file_kk_terlapor_bantuan_pendidikans');
                   $filname = 'bantuan-pendidikan/kk/'.$path->getClientOriginalName();
                   // dd($filname);
                   // $update['filename'] = $filname;
                   $return = Storage::disk('imagekit')->put($filname, fopen($path->getRealPath(), 'r') );
                   $datapengaduan['file_kk_terlapor_bantuan_pendidikans'] =  Storage::disk('imagekit')->url($filname);
                   // dd($data);
               }
               if($request->file('file_pendukung_bantuan_pendidikans')){
                    // dd($request->file('tl_file'));
                  
                    $path = $request->file('file_pendukung_bantuan_pendidikans');
                    $filname = 'bantuan-pendidikan/file_pendukung/'.$path->getClientOriginalName();
                    // dd($filname);
                    // $update['filename'] = $filname;
                    $return = Storage::disk('imagekit')->put($filname, fopen($path->getRealPath(), 'r') );
                    $datapengaduan['file_pendukung_bantuan_pendidikans'] =  Storage::disk('imagekit')->url($filname);
                    // dd($datapengaduan);
               }
               if($request->file('file_keterangan_dtks_bantuan_pendidikans')){
                   // dd($request->file('tl_file'));
                 
                   $path = $request->file('file_keterangan_dtks_bantuan_pendidikans');
                   $filname = 'bantuan-pendidikan/file_keterangan_dtks/'.$path->getClientOriginalName();
                   // dd($filname);
                   // $update['filename'] = $filname;
                   $return = Storage::disk('imagekit')->put($filname, fopen($path->getRealPath(), 'r') );
                   $datapengaduan['file_keterangan_dtks_bantuan_pendidikans'] =  Storage::disk('imagekit')->url($filname);
                   // dd($datapengaduan);
              }
               // $datapengaduan->id_alur = $request->get('id_alur_bantuan_pendidikans');
               $datapengaduan['no_pendaftaran_bantuan_pendidikans'] = $databantuanpendidikans->no_pendaftaran_bantuan_pendidikans;
                // dd($datapengaduan['no_pendaftaran_bantuan_pendidikans']);
               
               $datapengaduan['id_provinsi_bantuan_pendidikans'] = $request->get('id_provinsi_bantuan_pendidikans');
               $datapengaduan['id_kabkot_bantuan_pendidikans'] = $request->get('id_kabkot_bantuan_pendidikans');
               $datapengaduan['id_kecamatan_bantuan_pendidikans'] = $request->get('id_kecamatan_bantuan_pendidikans');
               $datapengaduan['id_kelurahan_bantuan_pendidikans'] = $request->get('id_kelurahan_bantuan_pendidikans');
               $datapengaduan['jenis_pelapor_bantuan_pendidikans'] = $request->get('jenis_pelapor_bantuan_pendidikans');
               $datapengaduan['ada_nik_bantuan_pendidikans'] = $request->get('ada_nik_bantuan_pendidikans');
               $datapengaduan['nik_bantuan_pendidikans'] = $request->get('nik_bantuan_pendidikans');

               // $datapengaduan->no_kk_bantuan_pendidikans = $request->get('no_kk_bantuan_pendidikans');
               // $datapengaduan->no_kis = $request->get('no_kis');
               $datapengaduan['nama_bantuan_pendidikans'] = $request->get('nama_bantuan_pendidikans');
               $datapengaduan['tgl_lahir_bantuan_pendidikans'] = $request->get('tgl_lahir_bantuan_pendidikans');
               $datapengaduan['tempat_lahir_bantuan_pendidikans'] = $request->get('tempat_lahir_bantuan_pendidikans');
               $datapengaduan['alamat_bantuan_pendidikans'] = $request->get('alamat_bantuan_pendidikans');
               $datapengaduan['telp_bantuan_pendidikans'] = $request->get('telp_bantuan_pendidikans');
               $datapengaduan['Nomor_Surat'] = $request->get('Nomor_Surat');
               $datapengaduan['validasi_surat'] = $request->get('validasi_surat');
            //    $datapengaduan->Nomor_Surat = $request->get('Nomor_Surat');
               // $datapengaduan->hubungan_terlapor = $request->get('hubungan_terlapor');
               // $datapengaduan->id_program_sosial = $request->get('id_program_sosial');
               // $datapengaduan->id_program_sosial = json_encode($request->input('id_program_sosial'));            
               // $datapengaduan->kepesertaan_program = $request->get('kepesertaan_program');
               // $datapengaduan->no_peserta = json_encode($request->input('no_peserta'));
               // $datapengaduan->level_program = $request->get('level_program');
               // $datapengaduan->sektor_program = $request->get('sektor_program');
               // $datapengaduan->no_kartu_program = $request->get('no_kartu_program');
               // $datapengaduan->ringkasan_pengaduan  = $request->get('ringkasan_pengaduan');
               $datapengaduan['jenis_kelamin_bantuan_pendidikans']  = $request->get('jenis_kelamin_bantuan_pendidikans');
               $datapengaduan['catatan_bantuan_pendidikans']  = $request->get('catatan_bantuan_pendidikans');
               $datapengaduan['status_dtks_bantuan_pendidikans'] = $request->get('status_dtks_bantuan_pendidikans');
               $datapengaduan['tujuan_bantuan_pendidikans'] = $request->get('tujuan_bantuan_pendidikans');
               $datapengaduan['status_alur_bantuan_pendidikans'] = $request->get('status_alur_bantuan_pendidikans'); 
               $datapengaduan['petugas_bantuan_pendidikans'] = $request->get('petugas_bantuan_pendidikans'); 
            //    $datapengaduan['createdby_bantuan_pendidikans'] = Auth::user()->id;
               $datapengaduan['updatedby_bantuan_pendidikans'] = Auth::user()->id;
            //    dd($datapengaduan);
                if($request->get('status_alur_bantuan_pendidikans') == 'Kembalikan' || $request->get('status_alur_bantuan_pendidikans') == 'Selesai'){
            
                    $datapengaduan['tujuan_bantuan_pendidikans'] = $rekomendasiBantuanPendidikans->role_id;
                    $datapengaduan['petugas_bantuan_pendidikans'] = $rekomendasiBantuanPendidikans->model_id;
                    // dd($data);
                }else{
                    $data['updatedby_bantuan_pendidikans'] = auth::user()->id;
                }
                $databerhasil = $databantuanpendidikans->update($datapengaduan);
               
               return redirect('rekomendasi_bantuan_pendidikans')->withSuccess('Data Rekomendasi Berhasil DiProses');
        }else{
            // dd($request->file('tl_file'));
                   //jika nik ada di prelist akan masuk ke sini
                //    $data = new rekomendasi_bantuan_pendidikan();
                    if($request->file('file_ktp_terlapor_bantuan_pendidikans')){
                        
                    
                        $path = $request->file('file_ktp_terlapor_bantuan_pendidikans');
                        $filname = 'bantuan-pendidikan/ktp/'.$path->getClientOriginalName();
                        // dd($filname);
                        // $update['filename'] = $filname;
                        $return = Storage::disk('imagekit')->put($filname, fopen($path->getRealPath(), 'r') );
                        //    $datapengaduan->file_ktp_terlapor_bantuan_pendidikans =  Storage::disk('imagekit')->url($filname);
                        $datapengaduan['file_ktp_terlapor_bantuan_pendidikans'] =  Storage::disk('imagekit')->url($filname);
                        // dd($datapengaduan);
                    }
                    if($request->file('file_kk_terlapor_bantuan_pendidikans')){
                        // dd($request->file('tl_file'));
                                            
                        $path = $request->file('file_kk_terlapor_bantuan_pendidikans');
                        $filname = 'bantuan-pendidikan/kk/'.$path->getClientOriginalName();
                        // dd($filname);
                        // $update['filename'] = $filname;
                        $return = Storage::disk('imagekit')->put($filname, fopen($path->getRealPath(), 'r') );
                        $datapengaduan['file_kk_terlapor_bantuan_pendidikans'] =  Storage::disk('imagekit')->url($filname);
                        // dd($data);
                    }
                    if($request->file('file_pendukung_bantuan_pendidikans')){
                        // dd($request->file('tl_file'));
                    
                        $path = $request->file('file_pendukung_bantuan_pendidikans');
                        $filname = 'bantuan-pendidikan/file_pendukung/'.$path->getClientOriginalName();
                        // dd($filname);
                        // $update['filename'] = $filname;
                        $return = Storage::disk('imagekit')->put($filname, fopen($path->getRealPath(), 'r') );
                        $datapengaduan['file_pendukung_bantuan_pendidikans'] =  Storage::disk('imagekit')->url($filname);
                        // dd($datapengaduan);
                    }
                    if($request->file('file_keterangan_dtks_bantuan_pendidikans')){
                        // dd($request->file('tl_file'));
                    
                        $path = $request->file('file_keterangan_dtks_bantuan_pendidikans');
                        $filname = 'bantuan-pendidikan/file_keterangan_dtks/'.$path->getClientOriginalName();
                        // dd($filname);
                        // $update['filename'] = $filname;
                        $return = Storage::disk('imagekit')->put($filname, fopen($path->getRealPath(), 'r') );
                        $datapengaduan['file_keterangan_dtks_bantuan_pendidikans'] =  Storage::disk('imagekit')->url($filname);
                        // dd($datapengaduan);
                }
                    // $datapengaduan->id_alur = $request->get('id_alur_bantuan_pendidikans');
                    $datapengaduan['no_pendaftaran_bantuan_pendidikans'] = $databantuanpendidikans->no_pendaftaran_bantuan_pendidikans;;
                    // dd($datapengaduan['no_pendaftaran_bantuan_pendidikans']);
                    
                    $datapengaduan['id_provinsi_bantuan_pendidikans'] = $request->get('id_provinsi_bantuan_pendidikans');
                    $datapengaduan['id_kabkot_bantuan_pendidikans'] = $request->get('id_kabkot_bantuan_pendidikans');
                    $datapengaduan['id_kecamatan_bantuan_pendidikans'] = $request->get('id_kecamatan_bantuan_pendidikans');
                    $datapengaduan['id_kelurahan_bantuan_pendidikans'] = $request->get('id_kelurahan_bantuan_pendidikans');
                    $datapengaduan['jenis_pelapor_bantuan_pendidikans'] = $request->get('jenis_pelapor_bantuan_pendidikans');
                    $datapengaduan['ada_nik_bantuan_pendidikans'] = $request->get('ada_nik_bantuan_pendidikans');
                    $datapengaduan['nik_bantuan_pendidikans'] = $request->get('nik_bantuan_pendidikans');
    
                    // $datapengaduan->no_kk_bantuan_pendidikans = $request->get('no_kk_bantuan_pendidikans');
                    // $datapengaduan->no_kis = $request->get('no_kis');
                    $datapengaduan['nama_bantuan_pendidikans'] = $request->get('nama_bantuan_pendidikans');
                    $datapengaduan['tgl_lahir_bantuan_pendidikans'] = $request->get('tgl_lahir_bantuan_pendidikans');
                    $datapengaduan['tempat_lahir_bantuan_pendidikans'] = $request->get('tempat_lahir_bantuan_pendidikans');
                    $datapengaduan['alamat_bantuan_pendidikans'] = $request->get('alamat_bantuan_pendidikans');
                    $datapengaduan['telp_bantuan_pendidikans'] = $request->get('telp_bantuan_pendidikans');
                    $datapengaduan['Nomor_Surat'] = $request->get('Nomor_Surat');
                    $datapengaduan['validasi_surat'] = $request->get('validasi_surat');
                    $datapengaduan['jenis_kelamin_bantuan_pendidikans']  = $request->get('jenis_kelamin_bantuan_pendidikans');
                    $datapengaduan['catatan_bantuan_pendidikans']  = $request->get('catatan_bantuan_pendidikans');
                    $datapengaduan['status_dtks_bantuan_pendidikans'] = $request->get('status_dtks_bantuan_pendidikans');
                    $datapengaduan['tujuan_bantuan_pendidikans'] = $request->get('tujuan_bantuan_pendidikans');
                    $datapengaduan['status_alur_bantuan_pendidikans'] = $request->get('status_alur_bantuan_pendidikans'); 
                    // $datapengaduan['createdby_bantuan_pendidikans'] = Auth::user()->id;
                    $datapengaduan['updatedby_bantuan_pendidikans'] = Auth::user()->id;
                    if($request->get('status_alur_bantuan_pendidikans') == 'Kembalikan' || $request->get('status_alur_bantuan_pendidikans') == 'Selesai'){
            
                        $datapengaduan['tujuan_bantuan_pendidikans'] = $rekomendasiBantuanPendidikans->role_id;
                        $datapengaduan['petugas_bantuan_pendidikans'] = $rekomendasiBantuanPendidikans->model_id;
                        // dd($datapengaduan);
                    }else{
                        $datapengaduan['petugas_bantuan_pendidikans'] = $request->get('petugas_bantuan_pendidikans'); 
                        $datapengaduan['tujuan_bantuan_pendidikans'] = $request->get('tujuan_bantuan_pendidikans');
                    }
                    // $databerhasil = $databantuanpendidikans->update($datapengaduan);
                   
                    rekomendasi_bantuan_pendidikan::where('id',$id)->update($datapengaduan);
                    // dd($databantuanpendidikans->tujuan_bantuan_pendidikans);
                    $logpengaduan = new logpendidikan();
                   $logpengaduan['id_trx_log_bantuan_pendidikans'] = $databantuanpendidikans->id;
                   $logpengaduan['id_alur_log_bantuan_pendidikans'] =  $databantuanpendidikans->status_alur_bantuan_pendidikans;
                   $logpengaduan['petugas_log_bantuan_pendidikans'] = $databantuanpendidikans->petugas_bantuan_pendidikans; 
                   $logpengaduan['catatan_log_bantuan_pendidikans']  =  $request->get('catatan_bantuan_pendidikans');
                   $logpengaduan['file_permohonan_bantuan_pendidikans'] = $databantuanpendidikans->file_keterangan_dtks_bantuan_pendidikans;
                   $logpengaduan['tujuan_log_bantuan_pendidikans'] =  $databantuanpendidikans->tujuan_bantuan_pendidikans;
                //    $logpengaduan['updated_by_log_bantuan_pendidikans'] = Auth::user()->id;
                   $logpengaduan['updated_by_log_bantuan_pendidikans'] = Auth::user()->id;
                //    dd($logpengaduan);
                   $logpengaduan->save();
                   $pelapor = pelapor::where('id_menu', '07')->where('id_form', $id)->first();
                   if ($pelapor) {
                       $dataPelapor = $request->all();
                       $dataPelapor['updatedby_pelapor'] = Auth::user()->id;
                       $pelapor->update($dataPelapor);
                   }  
                   return redirect('rekomendasi_bantuan_pendidikans')->withSuccess('Data Rekomendasi Berhasil DiProses');
                }
    }

    /**
     * Remove the specified rekomendasi_bantuan_pendidikan from storage.
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $rekomendasiBantuanPendidikan = $this->rekomendasiBantuanPendidikanRepository->find($id);

        if (empty($rekomendasiBantuanPendidikan)) {
            Flash::error('Rekomendasi Bantuan Pendidikan not found');

            return redirect(route('rekomendasi_bantuan_pendidikans.index'));
        }

        $this->rekomendasiBantuanPendidikanRepository->delete($id);

        Flash::success('Rekomendasi Bantuan Pendidikan deleted successfully.');

        return redirect(route('rekomendasi_bantuan_pendidikans.index'));
    }
    public function draft(Request $request)
    {
        $user_name = Auth::user()->name;
        $query = DB::table('rekomendasi_bantuan_pendidikans')
            ->leftjoin('users', 'users.id', '=', 'rekomendasi_bantuan_pendidikans.petugas_bantuan_pendidikans')
            // ->leftjoin('wilayahs', 'wilayahs.createdby', '=', 'rekomendasi_bantuan_pendidikans.createdby_bantuan_pendidikans')
            // ->leftjoin('model_has_roles', 'model_has_roles.model_id', '=', 'rekomendasi_bantuan_pendidikans.tujuan_bantuan_pendidikans')
            ->leftjoin('indonesia_villages as b', 'b.code', '=', 'rekomendasi_bantuan_pendidikans.id_kelurahan_bantuan_pendidikans')
            ->leftjoin('indonesia_districts as d', 'd.code', '=', 'rekomendasi_bantuan_pendidikans.id_kecamatan_bantuan_pendidikans')
            ->select('rekomendasi_bantuan_pendidikans.*', 'b.name_village','d.name_districts','users.name')
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
                // $query->where('rekomendasi_bantuan_pendidikans.id_kelurahan_bantuan_pendidikans', $user_wilayah->kelurahan_id);
                $query->where('rekomendasi_bantuan_pendidikans.status_alur_bantuan_pendidikans', 'Draft');
                $query->where('rekomendasi_bantuan_pendidikans.createdby_bantuan_pendidikans',  Auth::user()->id);
            });
        }
        if ($user_wilayah->name == 'Front Office Kelurahan') {
            $query->Where(function ($query) use ($user_wilayah) {
                $query->where('rekomendasi_bantuan_pendidikans.id_kelurahan_bantuan_pendidikans', $user_wilayah->kelurahan_id);
                $query->where('rekomendasi_bantuan_pendidikans.status_alur_bantuan_pendidikans', 'Draft');
                $query->where('rekomendasi_bantuan_pendidikans.createdby_bantuan_pendidikans',  Auth::user()->id);
            });
        }
        if ($user_wilayah->name == 'fasilitator') {
            $query->Where(function ($query) use ($user_wilayah) {
                $query->where('rekomendasi_bantuan_pendidikans.id_kelurahan_bantuan_pendidikans', $user_wilayah->kelurahan_id);
                $query->where('rekomendasi_bantuan_pendidikans.status_alur_bantuan_pendidikans', 'Draft');
                $query->where('rekomendasi_bantuan_pendidikans.createdby_bantuan_pendidikans',  Auth::user()->id);
            });
        }
        if ($user_wilayah->name == 'Front Office Kelurahan' || $user_wilayah->name == 'Back Ofiice kelurahan'|| $user_wilayah->name == 'fasilitator') {
            if ($request->has('search') && !empty($request->search['value'])) {
                $search = $request->search['value'];
				$query = DB::table('rekomendasi_bantuan_pendidikans')
                ->leftjoin('users', 'users.id', '=', 'rekomendasi_bantuan_pendidikans.petugas_bantuan_pendidikans')
                // ->leftjoin('wilayahs', 'wilayahs.createdby', '=', 'rekomendasi_bantuan_pendidikans.createdby_bantuan_pendidikans')
                // ->leftjoin('model_has_roles', 'model_has_roles.model_id', '=', 'rekomendasi_bantuan_pendidikans.tujuan_bantuan_pendidikans')
                ->leftjoin('indonesia_villages as b', 'b.code', '=', 'rekomendasi_bantuan_pendidikans.id_kelurahan_bantuan_pendidikans')
                ->leftjoin('indonesia_districts as d', 'd.code', '=', 'rekomendasi_bantuan_pendidikans.id_kecamatan_bantuan_pendidikans')
                ->select('rekomendasi_bantuan_pendidikans.*', 'b.name_village','d.name_districts','users.name')
                ->distinct();
				$query->Where(function ($query) use ($user_wilayah) {
                        $query->where('rekomendasi_bantuan_pendidikans.id_kelurahan_bantuan_pendidikans', $user_wilayah->kelurahan_id);
                        $query->where('rekomendasi_bantuan_pendidikans.status_alur_bantuan_pendidikans', 'Draft');
                        $query->where('rekomendasi_bantuan_pendidikans.createdby_bantuan_pendidikans',  Auth::user()->id);
				})
				->where(function ($query) use ($search) {
					$query->where('rekomendasi_bantuan_pendidikans.nik_bantuan_pendidikans', 'like', "%$search%")
						->orwhere('rekomendasi_bantuan_pendidikans.nama_bantuan_pendidikans', 'like', "%$search%")
						->orwhere('b.name_village', 'like', "%$search%")
						->orwhere('d.name_districts', 'like', "%$search%")
						// ->orwhere('indonesia_villages.name_village', 'like', "%$search%")
						->orwhere('rekomendasi_bantuan_pendidikans.alamat_bantuan_pendidikans', 'like', "%$search%");
				})->get();
				// dd($query);
            }
        }elseif($user_wilayah->name == 'Front Office Kota' || $user_wilayah->name == 'Back Ofiice Kota'|| $user_wilayah->name == 'KepalaDinas'||$user_wilayah->name == 'SekertarisDinas'||$user_wilayah->name == 'Supervisor') {
			if ($request->has('search') && !empty($request->search['value'])) {
                $search = $request->search['value'];
				$query = DB::table('rekomendasi_bantuan_pendidikans')
                ->leftjoin('users', 'users.id', '=', 'rekomendasi_bantuan_pendidikans.petugas_bantuan_pendidikans')
                // ->leftjoin('wilayahs', 'wilayahs.createdby', '=', 'rekomendasi_bantuan_pendidikans.createdby_bantuan_pendidikans')
                // ->leftjoin('model_has_roles', 'model_has_roles.model_id', '=', 'rekomendasi_bantuan_pendidikans.tujuan_bantuan_pendidikans')
                ->leftjoin('indonesia_villages as b', 'b.code', '=', 'rekomendasi_bantuan_pendidikans.id_kelurahan_bantuan_pendidikans')
                ->leftjoin('indonesia_districts as d', 'd.code', '=', 'rekomendasi_bantuan_pendidikans.id_kecamatan_bantuan_pendidikans')
                ->select('rekomendasi_bantuan_pendidikans.*', 'b.name_village','d.name_districts','users.name')
                ->distinct();
				$query->Where(function ($query) use ($user_wilayah) {
                        $query->where('rekomendasi_bantuan_pendidikans.id_kabkot_bantuan_pendidikans', $user_wilayah->kota_id);
                        $query->where('rekomendasi_bantuan_pendidikans.status_alur_bantuan_pendidikans', 'Draft');
                        $query->where('rekomendasi_bantuan_pendidikans.createdby_bantuan_pendidikans',  Auth::user()->id);
				})
				->where(function ($query) use ($search) {
					$query->where('rekomendasi_bantuan_pendidikans.nik_bantuan_pendidikans', 'like', "%$search%")
						->orwhere('rekomendasi_bantuan_pendidikans.nama_bantuan_pendidikans', 'like', "%$search%")
						->orwhere('b.name_village', 'like', "%$search%")
						->orwhere('d.name_districts', 'like', "%$search%")
						// ->orwhere('indonesia_villages.name_village', 'like', "%$search%")
						->orwhere('rekomendasi_bantuan_pendidikans.alamat_bantuan_pendidikans', 'like', "%$search%");
				})->get();
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
            'recordsTotal' => rekomendasi_bantuan_pendidikan::count(),
            'recordsFiltered' => $total_filtered_items,
            'data' => $data,
        ]);
    }
    public function teruskan(Request $request)
    {
        $user_name = Auth::user()->name;
        // dd($user_name);

        $query = DB::table('rekomendasi_bantuan_pendidikans')
            ->join('users', 'users.id', '=', 'rekomendasi_bantuan_pendidikans.petugas_bantuan_pendidikans')
            ->leftjoin('indonesia_villages as b', 'b.code', '=', 'rekomendasi_bantuan_pendidikans.id_kelurahan_bantuan_pendidikans')
            ->leftjoin('indonesia_districts as d', 'd.code', '=', 'rekomendasi_bantuan_pendidikans.id_kecamatan_bantuan_pendidikans')
            ->select('rekomendasi_bantuan_pendidikans.*', 'b.name_village','d.name_districts','users.name');
            
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
        
        if ($user_wilayah->name == 'Front Office Kelurahan'||$user_wilayah->name == 'fasilitator' ) {
           $query = DB::table('rekomendasi_bantuan_pendidikans')
            ->leftjoin('indonesia_districts as d', 'd.code', '=', 'rekomendasi_bantuan_pendidikans.id_kecamatan_bantuan_pendidikans')
            ->leftjoin('indonesia_villages as b', 'b.code', '=', 'rekomendasi_bantuan_pendidikans.id_kelurahan_bantuan_pendidikans')
            // ->leftjoin('roles as r', 'r.id', '=', 'rekomendasi_bantuan_pendidikans.petugas_bantuan_pendidikans')
            ->leftjoin('users', 'users.id', '=', 'rekomendasi_bantuan_pendidikans.petugas_bantuan_pendidikans')
            ->select('rekomendasi_bantuan_pendidikans.*', 'b.name_village', 'd.name_districts','users.name')
            // ->selectRaw('IFNULL(r.name,"") name')
            ->where('rekomendasi_bantuan_pendidikans.id_kelurahan_bantuan_pendidikans', '=', $user_wilayah->kelurahan_id)
            ->whereIn('rekomendasi_bantuan_pendidikans.status_alur_bantuan_pendidikans', ['Teruskan','Kembalikan'])
            ->where('rekomendasi_bantuan_pendidikans.petugas_bantuan_pendidikans', '<>', $user_id)
            ->whereRaw("(SELECT COUNT(l.id) FROM log_bantuan_pendidikan as l WHERE l.id_trx_log_bantuan_pendidikans = rekomendasi_bantuan_pendidikans.id AND l.updated_by_log_bantuan_pendidikans = '".$user_id."') > 0 ");
            // dd($query);

        }
        if ($user_wilayah->name == 'Front Office kota') {
            $query = DB::table('rekomendasi_bantuan_pendidikans')
            ->leftjoin('indonesia_districts as d', 'd.code', '=', 'rekomendasi_bantuan_pendidikans.id_kecamatan_bantuan_pendidikans')
            ->leftjoin('indonesia_villages as b', 'b.code', '=', 'rekomendasi_bantuan_pendidikans.id_kelurahan_bantuan_pendidikans')
            // ->leftjoin('roles as r', 'r.id', '=', 'rekomendasi_bantuan_pendidikans.petugas_bantuan_pendidikans')
            ->leftjoin('users', 'users.id', '=', 'rekomendasi_bantuan_pendidikans.petugas_bantuan_pendidikans')
            ->select('rekomendasi_bantuan_pendidikans.*', 'b.name_village', 'd.name_districts','users.name')
            // ->selectRaw('IFNULL(r.name,"") name')
            ->where('rekomendasi_bantuan_pendidikans.id_kabkot_bantuan_pendidikans', '=', $user_wilayah->kota_id)
            ->whereIn('rekomendasi_bantuan_pendidikans.status_alur_bantuan_pendidikans', ['Teruskan','Kembalikan'])
            ->where('rekomendasi_bantuan_pendidikans.petugas_bantuan_pendidikans', '<>', $user_id)
            ->whereRaw("(SELECT COUNT(l.id) FROM log_bantuan_pendidikan as l WHERE l.id_trx_log_bantuan_pendidikans = rekomendasi_bantuan_pendidikans.id AND l.updated_by_log_bantuan_pendidikans = '".$user_id."') > 0 ");
            // dd($query);


        }
        //Back office kota 
        if ($user_wilayah->name == 'Back Ofiice Kota') {
            // dd($user_wilayah->model_id);
            $query = DB::table('rekomendasi_bantuan_pendidikans')
            ->leftjoin('indonesia_districts as d', 'd.code', '=', 'rekomendasi_bantuan_pendidikans.id_kecamatan_bantuan_pendidikans')
            ->leftjoin('indonesia_villages as b', 'b.code', '=', 'rekomendasi_bantuan_pendidikans.id_kelurahan_bantuan_pendidikans')
            // ->leftjoin('roles as r', 'r.id', '=', 'rekomendasi_bantuan_pendidikans.petugas_bantuan_pendidikans')
            ->leftjoin('users', 'users.id', '=', 'rekomendasi_bantuan_pendidikans.petugas_bantuan_pendidikans')
            ->select('rekomendasi_bantuan_pendidikans.*', 'b.name_village', 'd.name_districts','users.name')
            // ->selectRaw('IFNULL(r.name,"") name')
            ->where('rekomendasi_bantuan_pendidikans.id_kabkot_bantuan_pendidikans', '=', $user_wilayah->kota_id)
            ->whereIn('rekomendasi_bantuan_pendidikans.status_alur_bantuan_pendidikans', ['Teruskan','Kembalikan'])
            ->where('rekomendasi_bantuan_pendidikans.petugas_bantuan_pendidikans', '<>', $user_id)
            ->whereRaw("(SELECT COUNT(l.id) FROM log_bantuan_pendidikan as l WHERE l.id_trx_log_bantuan_pendidikans = rekomendasi_bantuan_pendidikans.id AND l.updated_by_log_bantuan_pendidikans = '".$user_id."') > 0 ");
            // dd($query);
        }
         //Back office kota 
         if ($user_wilayah->name == 'supervisor') {
            // dd($user_wilayah->model_id);
            $query = DB::table('rekomendasi_bantuan_pendidikans')
            ->leftjoin('indonesia_districts as d', 'd.code', '=', 'rekomendasi_bantuan_pendidikans.id_kecamatan_bantuan_pendidikans')
            ->leftjoin('indonesia_villages as b', 'b.code', '=', 'rekomendasi_bantuan_pendidikans.id_kelurahan_bantuan_pendidikans')
            // ->leftjoin('roles as r', 'r.id', '=', 'rekomendasi_bantuan_pendidikans.petugas_bantuan_pendidikans')
            ->leftjoin('users', 'users.id', '=', 'rekomendasi_bantuan_pendidikans.petugas_bantuan_pendidikans')
            ->select('rekomendasi_bantuan_pendidikans.*', 'b.name_village', 'd.name_districts','users.name')
            // ->selectRaw('IFNULL(r.name,"") name')
            ->where('rekomendasi_bantuan_pendidikans.id_kecamatan_bantuan_pendidikans', '=', $user_wilayah->kecamatan_id)
            ->whereIn('rekomendasi_bantuan_pendidikans.status_alur_bantuan_pendidikans', ['Teruskan','Kembalikan'])
            ->where('rekomendasi_bantuan_pendidikans.petugas_bantuan_pendidikans', '<>', $user_id)
            ->whereRaw("(SELECT COUNT(l.id) FROM log_bantuan_pendidikan as l WHERE l.id_trx_log_bantuan_pendidikans = rekomendasi_bantuan_pendidikans.id AND l.updated_by_log_bantuan_pendidikans = '".$user_id."') > 0 ");
            // dd($query);
        }
        if ($user_wilayah->name == 'Back Ofiice kelurahan') {
            // dd($user_wilayah->model_id);
            $query = DB::table('rekomendasi_bantuan_pendidikans')
            ->leftjoin('indonesia_districts as d', 'd.code', '=', 'rekomendasi_bantuan_pendidikans.id_kecamatan_bantuan_pendidikans')
            ->leftjoin('indonesia_villages as b', 'b.code', '=', 'rekomendasi_bantuan_pendidikans.id_kelurahan_bantuan_pendidikans')
            // ->leftjoin('roles as r', 'r.id', '=', 'rekomendasi_bantuan_pendidikans.petugas_bantuan_pendidikans')
            ->leftjoin('users', 'users.id', '=', 'rekomendasi_bantuan_pendidikans.petugas_bantuan_pendidikans')
            ->select('rekomendasi_bantuan_pendidikans.*', 'b.name_village', 'd.name_districts','users.name')
            // ->selectRaw('IFNULL(r.name,"") name')
            ->where('rekomendasi_bantuan_pendidikans.id_kelurahan_bantuan_pendidikans', '=', $user_wilayah->kelurahan_id)
            ->whereIn('rekomendasi_bantuan_pendidikans.status_alur_bantuan_pendidikans', ['Teruskan','Kembalikan'])
            ->where('rekomendasi_bantuan_pendidikans.petugas_bantuan_pendidikans', '<>', $user_id)
            ->whereRaw("(SELECT COUNT(l.id) FROM log_bantuan_pendidikan as l WHERE l.id_trx_log_bantuan_pendidikans = rekomendasi_bantuan_pendidikans.id AND l.updated_by_log_bantuan_pendidikans = '".$user_id."') > 0 ");
            // dd($query);
        }

        if ($user_wilayah->name == 'kepala bidang') {
            // dd( $user_wilayah->role_id);
            $query = DB::table('rekomendasi_bantuan_pendidikans')
            ->leftjoin('indonesia_districts as d', 'd.code', '=', 'rekomendasi_bantuan_pendidikans.id_kecamatan_bantuan_pendidikans')
            ->leftjoin('indonesia_villages as b', 'b.code', '=', 'rekomendasi_bantuan_pendidikans.id_kelurahan_bantuan_pendidikans')
            // ->leftjoin('roles as r', 'r.id', '=', 'rekomendasi_bantuan_pendidikans.petugas_bantuan_pendidikans')
            ->leftjoin('users', 'users.id', '=', 'rekomendasi_bantuan_pendidikans.petugas_bantuan_pendidikans')
            ->select('rekomendasi_bantuan_pendidikans.*', 'b.name_village', 'd.name_districts','users.name')
            // ->selectRaw('IFNULL(r.name,"") name')
            ->where('rekomendasi_bantuan_pendidikans.id_kabkot_bantuan_pendidikans', '=', $user_wilayah->kota_id)
            ->whereIn('rekomendasi_bantuan_pendidikans.status_alur_bantuan_pendidikans', ['Teruskan','Kembalikan'])
            ->where('rekomendasi_bantuan_pendidikans.petugas_bantuan_pendidikans', '<>', $user_id)
            ->whereRaw("(SELECT COUNT(l.id) FROM log_bantuan_pendidikan as l WHERE l.id_trx_log_bantuan_pendidikans = rekomendasi_bantuan_pendidikans.id AND l.updated_by_log_bantuan_pendidikans = '".$user_id."') > 0 ");
            // dd($query);
        }
        if ($user_wilayah->name == 'SekertarisDinas') {
            // dd($user_wilayah);
            $query = DB::table('rekomendasi_bantuan_pendidikans')
            ->join('users', 'users.id', '=', 'rekomendasi_bantuan_pendidikans.petugas_bantuan_pendidikans')
            ->join('log_bantuan_pendidikan', 'log_bantuan_pendidikan.id_trx_log_bantuan_pendidikans', '=', 'rekomendasi_bantuan_pendidikans.id')
            // ->join('model_has_roles', 'model_has_roles.model_id', '=', 'rekomendasi_bantuan_pendidikans.tujuan')
            ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_bantuan_pendidikans.id_kelurahan_bantuan_pendidikans')
            ->select('rekomendasi_bantuan_pendidikans.*', 'b.name_village')
            ->orWhere(function ($query) use ($user_wilayah) {
                $query->where('rekomendasi_bantuan_pendidikans.id_kelurahan_bantuan_pendidikans', $user_wilayah->kelurahan_id)
                    ->where('log_bantuan_pendidikan.tujuan_log_bantuan_pendidikans', '!=', $user_wilayah->role_id)
                    ->where('log_bantuan_pendidikan.updated_by_log_bantuan_pendidikans', '=', auth::user()->id)
                    // ->where('rekomendasi_bantuan_pendidikans.petugas','!=', $user_wilayah->model_id)
                    ->where(function ($query) {
                        $query->where('rekomendasi_bantuan_pendidikans.status_alur_bantuan_pendidikans', '=', 'Teruskan')
                            ->orWhere('rekomendasi_bantuan_pendidikans.status_alur_bantuan_pendidikans', '=', 'kembalikan');
                    });
            })->distinct();
        }
        if ($user_wilayah->name == 'KepalaDinas') {
            //  dd(auth::user()->id);
            $query = DB::table('rekomendasi_bantuan_pendidikans')
                ->join('users', 'users.id', '=', 'rekomendasi_bantuan_pendidikans.createdby')
                ->join('log_yayasan', 'log_yayasan.id_trx_yayasan', '=', 'rekomendasi_bantuan_pendidikans.id')
                //  ->join('model_has_roles', 'model_has_roles.model_id', '=', 'rekomendasi_bantuan_pendidikans.tujuan')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_bantuan_pendidikans.id_kelurahan')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_bantuan_pendidikans.id_kecamatan_bantuan_pendidikans')
                ->select('rekomendasi_bantuan_pendidikans.*', 'b.name_village')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_bantuan_pendidikans.id_kelurahan', $user_wilayah->kelurahan_id)
                        ->where('log_yayasan.tujuan', '!=', $user_wilayah->role_id)
                        ->where('log_yayasan.created_by', '=', auth::user()->id)
                        // ->where('rekomendasi_bantuan_pendidikans.petugas','!=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_bantuan_pendidikans.status_alur', '=', 'Teruskan')
                                ->orWhere('rekomendasi_bantuan_pendidikans.status_alur', '=', 'kembalikan');
                        });
                })->distinct();
        }
        if ($user_wilayah->name == 'Front Office Kelurahan' || $user_wilayah->name  == 'Back Ofiice kelurahan'|| $user_wilayah->name  == 'fasilitator') {
            if ($request->has('search') && !empty($request->search['value'])) {
                $search = $request->search['value'];
                $query = DB::table('rekomendasi_bantuan_pendidikans')
                ->join('users', 'users.id', '=', 'rekomendasi_bantuan_pendidikans.petugas_bantuan_pendidikans')
                ->join('log_bantuan_pendidikan', 'log_bantuan_pendidikan.id_trx_log_bantuan_pendidikans', '=', 'rekomendasi_bantuan_pendidikans.id')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_bantuan_pendidikans.id_kelurahan_bantuan_pendidikans')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_bantuan_pendidikans.id_kecamatan_bantuan_pendidikans')
                ->select('rekomendasi_bantuan_pendidikans.*', 'b.name_village', 'd.name_districts', 'log_bantuan_pendidikan.tujuan_log_bantuan_pendidikans', 'log_bantuan_pendidikan.petugas_log_bantuan_pendidikans','users.name')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_bantuan_pendidikans.id_kelurahan_bantuan_pendidikans', $user_wilayah->kelurahan_id)
                        ->where('log_bantuan_pendidikan.tujuan_log_bantuan_pendidikans', '!=', $user_wilayah->role_id)
                        ->where('log_bantuan_pendidikan.updated_by_log_bantuan_pendidikans', '=', auth::user()->id)
                        // ->where('rekomendasi_bantuan_pendidikans.petugas','!=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_bantuan_pendidikans.status_alur_bantuan_pendidikans', '=', 'Teruskan')
                                ->orWhere('rekomendasi_bantuan_pendidikans.status_alur_bantuan_pendidikans', '=', 'kembalikan');
                        });
                })
				->where(function ($query) use ($search) {
					$query->where('rekomendasi_bantuan_pendidikans.nik_bantuan_pendidikans', 'like', "%$search%")
						->orwhere('rekomendasi_bantuan_pendidikans.nama_bantuan_pendidikans', 'like', "%$search%")
						->orwhere('b.name_village', 'like', "%$search%")
						->orwhere('d.name_districts', 'like', "%$search%")
						// ->orwhere('indonesia_villages.name_village', 'like', "%$search%")
						->orwhere('rekomendasi_bantuan_pendidikans.alamat_bantuan_pendidikans', 'like', "%$search%");
				});
            }
        }elseif($user_wilayah->name == 'Front Office Kota' || $user_wilayah->name == 'Back Ofiice Kota'|| $user_wilayah->name == 'KepalaDinas'||$user_wilayah->name == 'SekertarisDinas'||$user_wilayah->name == 'Supervisor') {
			if ($request->has('search') && !empty($request->search['value'])) {
                $search = $request->search['value'];
                $query = DB::table('rekomendasi_bantuan_pendidikans')
                ->join('users', 'users.id', '=', 'rekomendasi_bantuan_pendidikans.petugas_bantuan_pendidikans')
                ->join('log_bantuan_pendidikan', 'log_bantuan_pendidikan.id_trx_log_bantuan_pendidikans', '=', 'rekomendasi_bantuan_pendidikans.id')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_bantuan_pendidikans.id_kelurahan_bantuan_pendidikans')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_bantuan_pendidikans.id_kecamatan_bantuan_pendidikans')
                ->select('rekomendasi_bantuan_pendidikans.*', 'b.name_village', 'd.name_districts', 'log_bantuan_pendidikan.tujuan_log_bantuan_pendidikans', 'log_bantuan_pendidikan.petugas_log_bantuan_pendidikans','users.name')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_bantuan_pendidikans.id_kabkot_bantuan_pendidikans', $user_wilayah->kota_id)
                        ->where('log_bantuan_pendidikan.tujuan_log_bantuan_pendidikans', '!=', $user_wilayah->role_id)
                        ->where('log_bantuan_pendidikan.updated_by_log_bantuan_pendidikans', '=', auth::user()->id)
                        // ->where('rekomendasi_bantuan_pendidikans.petugas','!=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_bantuan_pendidikans.status_alur_bantuan_pendidikans', '=', 'Teruskan')
                                ->orWhere('rekomendasi_bantuan_pendidikans.status_alur_bantuan_pendidikans', '=', 'kembalikan');
                        });
                })
				->where(function ($query) use ($search) {
					$query->where('rekomendasi_bantuan_pendidikans.nik_bantuan_pendidikans', 'like', "%$search%")
						->orwhere('rekomendasi_bantuan_pendidikans.nama_bantuan_pendidikans', 'like', "%$search%")
						->orwhere('b.name_village', 'like', "%$search%")
						->orwhere('d.name_districts', 'like', "%$search%")
						// ->orwhere('indonesia_villages.name_village', 'like', "%$search%")
						->orwhere('rekomendasi_bantuan_pendidikans.alamat_bantuan_pendidikans', 'like', "%$search%");
				});
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
            'recordsTotal' => rekomendasi_bantuan_pendidikan::count(),
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
            $query = DB::table('rekomendasi_bantuan_pendidikans')
                ->join('users', 'users.id', '=', 'rekomendasi_bantuan_pendidikans.petugas_bantuan_pendidikans')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_bantuan_pendidikans.id_kecamatan_bantuan_pendidikans')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_bantuan_pendidikans.id_kelurahan_bantuan_pendidikans')
                ->select('rekomendasi_bantuan_pendidikans.*', 'b.name_village', 'd.name_districts','users.name');

        } elseif ($user_wilayah->name == 'Front Office kota') {
            $query = DB::table('rekomendasi_bantuan_pendidikans')
                ->join('users', 'users.id', '=', 'rekomendasi_bantuan_pendidikans.petugas_bantuan_pendidikans')
                // ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_bantuan_pendidikans.id_kecamatan_bantuan_pendidikans')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_bantuan_pendidikans.id_kelurahan_bantuan_pendidikans')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_bantuan_pendidikans.id_kecamatan_bantuan_pendidikans')
                ->select('rekomendasi_bantuan_pendidikans.*', 'b.name_village', 'd.name_districts','users.name');

        }elseif ($user_wilayah->name == 'Front Office Kelurahan') {
            $query = DB::table('rekomendasi_bantuan_pendidikans')
                ->join('users', 'users.id', '=', 'rekomendasi_bantuan_pendidikans.petugas_bantuan_pendidikans')
                // ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_bantuan_pendidikans.id_kecamatan_bantuan_pendidikans')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_bantuan_pendidikans.id_kelurahan_bantuan_pendidikans')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_bantuan_pendidikans.id_kecamatan_bantuan_pendidikans')
                ->select('rekomendasi_bantuan_pendidikans.*', 'b.name_village', 'd.name_districts','users.name');

        }elseif ($user_wilayah->name == 'supervisor') {
            $query = DB::table('rekomendasi_bantuan_pendidikans')
                ->join('users', 'users.id', '=', 'rekomendasi_bantuan_pendidikans.petugas_bantuan_pendidikans')
                // ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_bantuan_pendidikans.id_kecamatan_bantuan_pendidikans')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_bantuan_pendidikans.id_kelurahan_bantuan_pendidikans')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_bantuan_pendidikans.id_kecamatan_bantuan_pendidikans')
                ->select('rekomendasi_bantuan_pendidikans.*', 'b.name_village', 'd.name_districts','users.name');

        }elseif ($user_wilayah->name == 'Back Ofiice kelurahan') {
            $query = DB::table('rekomendasi_bantuan_pendidikans')
                ->join('users', 'users.id', '=', 'rekomendasi_bantuan_pendidikans.petugas_bantuan_pendidikans')
                // ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_bantuan_pendidikans.id_kecamatan_bantuan_pendidikans')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_bantuan_pendidikans.id_kelurahan_bantuan_pendidikans')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_bantuan_pendidikans.id_kecamatan_bantuan_pendidikans')
                ->select('rekomendasi_bantuan_pendidikans.*', 'b.name_village', 'd.name_districts','users.name');
                
        } elseif ($user_wilayah->name == 'Back Ofiice Kota') {
            $query = DB::table('rekomendasi_bantuan_pendidikans')
                ->join('users', 'users.id', '=', 'rekomendasi_bantuan_pendidikans.petugas_bantuan_pendidikans')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_bantuan_pendidikans.id_kecamatan_bantuan_pendidikans')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_bantuan_pendidikans.id_kelurahan_bantuan_pendidikans')
                ->select('rekomendasi_bantuan_pendidikans.*', 'b.name_village', 'd.name_districts','users.name');
        } elseif ($user_wilayah->name == 'kepala bidang') {
            $query = DB::table('rekomendasi_bantuan_pendidikans')
                ->join('users', 'users.id', '=', 'rekomendasi_bantuan_pendidikans.petugas_bantuan_pendidikans')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_bantuan_pendidikans.id_kecamatan_bantuan_pendidikans')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_bantuan_pendidikans.id_kelurahan_bantuan_pendidikans')
                ->select('rekomendasi_bantuan_pendidikans.*', 'b.name_village', 'd.name_districts','users.name');
        }else{
            $query = DB::table('pengaduans')
                    ->join('users', 'users.id', '=', 'pengaduans.createdby')
                    ->join('indonesia_villages as b', 'b.code', '=', 'pengaduans.id_kelurahan')
                    ->select('pengaduans.*', 'b.name_village');
        }
        if ($user_wilayah->name == 'fasilitator') {
            $query->orWhere(function($query) use ($user_wilayah) {
                $query->where('rekomendasi_bantuan_pendidikans.id_kelurahan_bantuan_pendidikans', '=' , $user_wilayah->kelurahan_id)
                        ->where('rekomendasi_bantuan_pendidikans.tujuan_bantuan_pendidikans', '=' , $user_wilayah->role_id)
                        ->where('rekomendasi_bantuan_pendidikans.petugas_bantuan_pendidikans', '=' , auth::user()->id)
                     
                     ->where(function($query){
                         $query->where('rekomendasi_bantuan_pendidikans.status_alur_bantuan_pendidikans', '=', 'Teruskan')
                               ->orWhere('rekomendasi_bantuan_pendidikans.status_alur_bantuan_pendidikans', '=', 'kembalikan');
                     }); 
            });
        }
        if ($user_wilayah->name == 'supervisor') {
            $query->orWhere(function($query) use ($user_wilayah) {
                $query->where('rekomendasi_bantuan_pendidikans.tujuan_bantuan_pendidikans', '=' , $user_wilayah->role_id)
                        // ->where('rekomendasi_bantuan_pendidikans.tujuan_bantuan_pendidikans', '=' , $user_wilayah->role_id)
                        ->where('rekomendasi_bantuan_pendidikans.petugas_bantuan_pendidikans', '=' , auth::user()->id)
                     
                     ->where(function($query){
                         $query->where('rekomendasi_bantuan_pendidikans.status_alur_bantuan_pendidikans', '=', 'Teruskan')
                               ->orWhere('rekomendasi_bantuan_pendidikans.status_alur_bantuan_pendidikans', '=', 'kembalikan');
                     }); 
            });
        }
        if ($user_wilayah->name == 'Front Office Kelurahan') {
            $query->orWhere(function($query) use ($user_wilayah) {
                $query->where('rekomendasi_bantuan_pendidikans.id_kelurahan_bantuan_pendidikans', '=' , $user_wilayah->kelurahan_id)
                        ->where('rekomendasi_bantuan_pendidikans.tujuan_bantuan_pendidikans', '=' , $user_wilayah->role_id)
                        ->where('rekomendasi_bantuan_pendidikans.petugas_bantuan_pendidikans', '=' , auth::user()->id)
                     
                     ->where(function($query){
                         $query->where('rekomendasi_bantuan_pendidikans.status_alur_bantuan_pendidikans', '=', 'Teruskan')
                               ->orWhere('rekomendasi_bantuan_pendidikans.status_alur_bantuan_pendidikans', '=', 'kembalikan');
                     }); 
            });
        }
        if ($user_wilayah->name == 'Front Office kota') {
            $query->orWhere(function($query) use ($user_wilayah) {
                $query->where('rekomendasi_bantuan_pendidikans.id_kabkot_bantuan_pendidikans', '=' , $user_wilayah->kota_id)
                        ->where('rekomendasi_bantuan_pendidikans.tujuan_bantuan_pendidikans', '=' , $user_wilayah->role_id)
                        ->where('rekomendasi_bantuan_pendidikans.petugas_bantuan_pendidikans', '=' , auth::user()->id)
                     
                     ->where(function($query){
                         $query->where('rekomendasi_bantuan_pendidikans.status_alur_bantuan_pendidikans', '=', 'Teruskan')
                               ->orWhere('rekomendasi_bantuan_pendidikans.status_alur_bantuan_pendidikans', '=', 'kembalikan');
                     }); 
            });
        }
        if ($user_wilayah->name == 'Back Ofiice Kota') {
            $query->orWhere(function($query) use ($user_wilayah) {
                $query->where('rekomendasi_bantuan_pendidikans.id_kabkot_bantuan_pendidikans', '=' , $user_wilayah->kota_id)
                ->where('rekomendasi_bantuan_pendidikans.tujuan_bantuan_pendidikans', '=' , $user_wilayah->role_id)
                ->where('rekomendasi_bantuan_pendidikans.petugas_bantuan_pendidikans', '=' , auth::user()->id)
                ->where(function($query){
                   $query->where('rekomendasi_bantuan_pendidikans.status_alur_bantuan_pendidikans', '=', 'Teruskan')
                         ->orWhere('rekomendasi_bantuan_pendidikans.status_alur_bantuan_pendidikans', '=', 'kembalikan');
               }); 
                // dd($va);
            });
            // dd($query->count());
        }
        if ($user_wilayah->name == 'Back Ofiice kelurahan') {
            $query->orWhere(function($query) use ($user_wilayah) {
                $query->where('rekomendasi_bantuan_pendidikans.id_kelurahan_bantuan_pendidikans', '=' , $user_wilayah->kelurahan_id)
                ->where('rekomendasi_bantuan_pendidikans.tujuan_bantuan_pendidikans', '=' , $user_wilayah->role_id)
                ->where('rekomendasi_bantuan_pendidikans.petugas_bantuan_pendidikans', '=' , auth::user()->id)
                ->where(function($query){
                   $query->where('rekomendasi_bantuan_pendidikans.status_alur_bantuan_pendidikans', '=', 'Teruskan')
                         ->orWhere('rekomendasi_bantuan_pendidikans.status_alur_bantuan_pendidikans', '=', 'kembalikan');
               }); 
                // dd($va);
            });
            // dd($query->count());
        }
        if ($user_wilayah->name == 'kepala bidang') {
            $query->orWhere(function($query) use ($user_wilayah) {
                $query->where('rekomendasi_bantuan_pendidikans.id_kabkot_bantuan_pendidikans', '=' , $user_wilayah->kota_id)
                ->where('rekomendasi_bantuan_pendidikans.tujuan_bantuan_pendidikans', '=' , $user_wilayah->role_id)
                ->where('rekomendasi_bantuan_pendidikans.petugas_bantuan_pendidikans', '=' , auth::user()->id)
                ->where(function($query){
                   $query->where('rekomendasi_bantuan_pendidikans.status_alur_bantuan_pendidikans', '=', 'Teruskan')
                         ->orWhere('rekomendasi_bantuan_pendidikans.status_alur_bantuan_pendidikans', '=', 'kembalikan');
               }); 
                // dd($va);
            });
        }
        if ($user_wilayah->name == 'Front Office Kelurahan' || $user_wilayah->name == 'Back Ofiice kelurahan'|| $user_wilayah->name == 'fasilitator') {
            if ($request->has('search') && !empty($request->search['value'])) {
                $search = $request->search['value'];
                $query->orWhere(function($query) use ($user_wilayah) {
                    $query->where('rekomendasi_bantuan_pendidikans.id_kelurahan_bantuan_pendidikans', '=' , $user_wilayah->kelurahan_id)
                            ->where('rekomendasi_bantuan_pendidikans.tujuan_bantuan_pendidikans', '=' , $user_wilayah->role_id)
                            ->where('rekomendasi_bantuan_pendidikans.petugas_bantuan_pendidikans', '=' , auth::user()->id)
                            ->where(function($query){
                                $query->where('rekomendasi_bantuan_pendidikans.status_alur_bantuan_pendidikans', '=', 'Teruskan')
                                    ->orWhere('rekomendasi_bantuan_pendidikans.status_alur_bantuan_pendidikans', '=', 'kembalikan');
                            }); 
                })
				->where(function ($query) use ($search) {
					$query->where('rekomendasi_bantuan_pendidikans.nik_bantuan_pendidikans', 'like', "%$search%")
						->orwhere('rekomendasi_bantuan_pendidikans.nama_bantuan_pendidikans', 'like', "%$search%")
						->orwhere('b.name_village', 'like', "%$search%")
						->orwhere('d.name_districts', 'like', "%$search%")
						// ->orwhere('indonesia_villages.name_village', 'like', "%$search%")
						->orwhere('rekomendasi_bantuan_pendidikans.alamat_bantuan_pendidikans', 'like', "%$search%");
				})->get();
            }
        }elseif($user_wilayah->name == 'Front Office Kota' || $user_wilayah->name == 'Back Ofiice Kota'|| $user_wilayah->name == 'KepalaDinas'||$user_wilayah->name == 'SekertarisDinas'||$user_wilayah->name == 'Supervisor') {
			if ($request->has('search') && !empty($request->search['value'])) {
                $search = $request->search['value'];
                $query->orWhere(function($query) use ($user_wilayah) {
                    $query->where('rekomendasi_bantuan_pendidikans.id_kabkot_bantuan_pendidikans', '=' , $user_wilayah->kota_id)
                            ->where('rekomendasi_bantuan_pendidikans.tujuan_bantuan_pendidikans', '=' , $user_wilayah->role_id)
                            ->where('rekomendasi_bantuan_pendidikans.petugas_bantuan_pendidikans', '=' , auth::user()->id)
                            ->where(function($query){
                                $query->where('rekomendasi_bantuan_pendidikans.status_alur_bantuan_pendidikans', '=', 'Teruskan')
                                    ->orWhere('rekomendasi_bantuan_pendidikans.status_alur_bantuan_pendidikans', '=', 'kembalikan');
                            }); 
                })
				->where(function ($query) use ($search) {
					$query->where('rekomendasi_bantuan_pendidikans.nik_bantuan_pendidikans', 'like', "%$search%")
						->orwhere('rekomendasi_bantuan_pendidikans.nama_bantuan_pendidikans', 'like', "%$search%")
						->orwhere('b.name_village', 'like', "%$search%")
						->orwhere('d.name_districts', 'like', "%$search%")
						// ->orwhere('indonesia_villages.name_village', 'like', "%$search%")
						->orwhere('rekomendasi_bantuan_pendidikans.alamat_bantuan_pendidikans', 'like', "%$search%");
				})->get();
            }
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
        $start = $request->start;
        $length = $request->length;
        $query->offset($start)->limit($length);
        $data = $query->get();
        // dd($data);   
        // mengubah data JSON menjadi objek PHP

        return response()->json([
            'draw' => $request->input('draw'),
            'recordsTotal' => rekomendasi_bantuan_pendidikan::count(),
            'recordsFiltered' => $total_filtered_items,
            'data' => $data,
        ]);
    }
    public function selesai(Request $request)
    {
        $user_name = Auth::user()->name;
        $query = DB::table('rekomendasi_bantuan_pendidikans')
            ->join('users', 'users.id', 'rekomendasi_bantuan_pendidikans.petugas_bantuan_pendidikans')
            ->join('log_bantuan_pendidikan', 'log_bantuan_pendidikan.id_trx_log_bantuan_pendidikans', '=', 'rekomendasi_bantuan_pendidikans.id')
            ->join('roles', 'roles.id', '=', 'rekomendasi_bantuan_pendidikans.tujuan_bantuan_pendidikans')
            ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_bantuan_pendidikans.id_kelurahan_bantuan_pendidikans')
            ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_bantuan_pendidikans.id_kecamatan_bantuan_pendidikans')
            ->select('rekomendasi_bantuan_pendidikans.*','roles.name','users.name','b.name_village','d.name_districts');
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
            if ($user_wilayah->name == 'fasilitator' ) {
                $query = DB::table('rekomendasi_bantuan_pendidikans')
                    ->join('users', 'users.id', '=', 'rekomendasi_bantuan_pendidikans.petugas_bantuan_pendidikans')
                    // ->join('log_bantuan_pendidikan', 'log_bantuan_pendidikan.id_trx_log_bantuan_pendidikans', '=', 'rekomendasi_bantuan_pendidikans.id')
                    ->join('indonesia_villages', 'indonesia_villages.code', '=', 'rekomendasi_bantuan_pendidikans.id_kelurahan_bantuan_pendidikans')
                    ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_bantuan_pendidikans.id_kecamatan_bantuan_pendidikans')
                    ->select('rekomendasi_bantuan_pendidikans.*','users.name','d.name_districts','indonesia_villages.name_village')
                    ->orWhere(function($query) use ($user_wilayah) {
                        $query->where('rekomendasi_bantuan_pendidikans.id_kelurahan_bantuan_pendidikans', $user_wilayah->kelurahan_id)
                                    // ->where('log_bantuan_pendidikan.tujuan_log_bantuan_pendidikans','=', $user_wilayah->role_id)
                                    // ->where('log_bantuan_pendidikan.updated_by_log_bantuan_pendidikans','!=', $user_wilayah->model_id)
                                    ->where(function($query){
                                        $query->where('rekomendasi_bantuan_pendidikans.status_alur_bantuan_pendidikans', '=', 'Tolak')
                                            ->orWhere('rekomendasi_bantuan_pendidikans.status_alur_bantuan_pendidikans', '=', 'Selesai');
                                    });
                    })->distinct();
            }elseif ($user_wilayah->name == 'Front Office Kelurahan' ) {
                //  dd($user_wilayah->role_id);
                $query = DB::table('rekomendasi_bantuan_pendidikans')
                    ->join('users', 'users.id', '=', 'rekomendasi_bantuan_pendidikans.petugas_bantuan_pendidikans')
                    // ->join('log_bantuan_pendidikan', 'log_bantuan_pendidikan.id_trx_log_bantuan_pendidikans', '=', 'rekomendasi_bantuan_pendidikans.id')
                    ->join('indonesia_villages', 'indonesia_villages.code', '=', 'rekomendasi_bantuan_pendidikans.id_kelurahan_bantuan_pendidikans')
                    ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_bantuan_pendidikans.id_kecamatan_bantuan_pendidikans')
                    ->select('rekomendasi_bantuan_pendidikans.*','d.name_districts','indonesia_villages.name_village','users.name')
                    ->orWhere(function($query) use ($user_wilayah) {
                        $query->where('rekomendasi_bantuan_pendidikans.id_kelurahan_bantuan_pendidikans', $user_wilayah->kelurahan_id)
                                    // ->where('log_bantuan_pendidikan.tujuan_log_bantuan_pendidikans','=', $user_wilayah->role_id)
                                    // ->where('log_bantuan_pendidikan.updated_by_log_bantuan_pendidikans','!=', $user_wilayah->model_id)
                                    ->where(function($query){
                                        $query->where('rekomendasi_bantuan_pendidikans.status_alur_bantuan_pendidikans', '=', 'Tolak')
                                            ->orWhere('rekomendasi_bantuan_pendidikans.status_alur_bantuan_pendidikans', '=', 'Selesai');
                                    });
                    })->distinct();

            }elseif ($user_wilayah->name == 'Front Office kota' ) {
                //  dd($user_wilayah->role_id);
                $query = DB::table('rekomendasi_bantuan_pendidikans')
                        ->join('users', 'users.id', '=', 'rekomendasi_bantuan_pendidikans.petugas_bantuan_pendidikans')
                        // ->join('log_bantuan_pendidikan', 'log_bantuan_pendidikan.id_trx_log_bantuan_pendidikans', '=', 'rekomendasi_bantuan_pendidikans.id')
                        ->join('indonesia_villages', 'indonesia_villages.code', '=', 'rekomendasi_bantuan_pendidikans.id_kelurahan_bantuan_pendidikans')
                        ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_bantuan_pendidikans.id_kecamatan_bantuan_pendidikans')
                        ->select('rekomendasi_bantuan_pendidikans.*','d.name_districts','indonesia_villages.name_village','users.name' )
                        ->orWhere(function($query) use ($user_wilayah) {
                            $query->where('rekomendasi_bantuan_pendidikans.id_kabkot_bantuan_pendidikans', $user_wilayah->kota_id)
                                        // ->where('log_bantuan_pendidikan.tujuan_log_bantuan_pendidikans','=', $user_wilayah->role_id)
                                        // ->where('log_bantuan_pendidikan.updated_by_log_bantuan_pendidikans','!=', $user_wilayah->model_id)
                                        ->where(function($query){
                                            $query->where('rekomendasi_bantuan_pendidikans.status_alur_bantuan_pendidikans', '=', 'Tolak')
                                                ->orWhere('rekomendasi_bantuan_pendidikans.status_alur_bantuan_pendidikans', '=', 'Selesai');
                                        });
                        })->distinct();
            }elseif ($user_wilayah->name == 'Back Ofiice kelurahan' ) {
                // dd($user_wilayah);
                $query = DB::table('rekomendasi_bantuan_pendidikans')
                    ->join('users', 'users.id', 'rekomendasi_bantuan_pendidikans.petugas_bantuan_pendidikans')
                    // ->join('log_bantuan_pendidikan', 'log_bantuan_pendidikan.id_trx_log_bantuan_pendidikans', '=', 'rekomendasi_bantuan_pendidikans.id')
                    ->join('roles', 'roles.id', '=', 'rekomendasi_bantuan_pendidikans.tujuan_bantuan_pendidikans')
                    ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_bantuan_pendidikans.id_kelurahan_bantuan_pendidikans')
                    ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_bantuan_pendidikans.id_kecamatan_bantuan_pendidikans')
                    ->select('rekomendasi_bantuan_pendidikans.*','roles.name','users.name','b.name_village','d.name_districts')
                    ->orWhere(function($query) use ($user_wilayah) {
                        $query->where('rekomendasi_bantuan_pendidikans.id_kelurahan_bantuan_pendidikans', $user_wilayah->kelurahan_id)
                                    // ->where('log_bantuan_pendidikan.tujuan_log_bantuan_pendidikans','=', $user_wilayah->role_id)
                                    // ->where('log_bantuan_pendidikan.updated_by_log_bantuan_pendidikans','!=', $user_wilayah->model_id)
                                    ->where(function($query){
                                        $query->where('rekomendasi_bantuan_pendidikans.status_alur_bantuan_pendidikans', '=', 'Tolak')
                                            ->orWhere('rekomendasi_bantuan_pendidikans.status_alur_bantuan_pendidikans', '=', 'Selesai');
                                    });
                    })->distinct();
                // dd($query); 
            }elseif ($user_wilayah->name == 'kepala bidang' ) {
                // dd($user_wilayah);
                $query = DB::table('rekomendasi_bantuan_pendidikans')
               ->join('users', 'users.id', 'rekomendasi_bantuan_pendidikans.petugas_bantuan_pendidikans')
                // ->join('log_bantuan_pendidikan', 'log_bantuan_pendidikan.id_trx_log_bantuan_pendidikans', '=', 'rekomendasi_bantuan_pendidikans.id')
                ->join('roles', 'roles.id', '=', 'rekomendasi_bantuan_pendidikans.tujuan_bantuan_pendidikans')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_bantuan_pendidikans.id_kelurahan_bantuan_pendidikans')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_bantuan_pendidikans.id_kecamatan_bantuan_pendidikans')
                ->select('rekomendasi_bantuan_pendidikans.*','roles.name','users.name','b.name_village','d.name_districts')
                ->orWhere(function($query) use ($user_wilayah) {
                    $query->where('rekomendasi_bantuan_pendidikans.id_kabkot_bantuan_pendidikans', $user_wilayah->kota_id)
                                // ->where('log_bantuan_pendidikan.tujuan_log_bantuan_pendidikans','=', $user_wilayah->role_id)
                                // ->where('log_bantuan_pendidikan.updated_by_log_bantuan_pendidikans','!=', $user_wilayah->model_id)
                                ->where(function($query){
                                    $query->where('rekomendasi_bantuan_pendidikans.status_alur_bantuan_pendidikans', '=', 'Tolak')
                                        ->orWhere('rekomendasi_bantuan_pendidikans.status_alur_bantuan_pendidikans', '=', 'Selesai');
                                });
                })->distinct();
               
            }elseif ($user_wilayah->name == 'Back Ofiice Kota' ) {
                // dd($user_wilayah->role_id);
                $query = DB::table('rekomendasi_bantuan_pendidikans')
               ->join('users', 'users.id', 'rekomendasi_bantuan_pendidikans.petugas_bantuan_pendidikans')
                // ->join('log_bantuan_pendidikan', 'log_bantuan_pendidikan.id_trx_log_bantuan_pendidikans', '=', 'rekomendasi_bantuan_pendidikans.id')
                ->join('roles', 'roles.id', '=', 'rekomendasi_bantuan_pendidikans.tujuan_bantuan_pendidikans')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_bantuan_pendidikans.id_kelurahan_bantuan_pendidikans')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_bantuan_pendidikans.id_kecamatan_bantuan_pendidikans')
                ->select('rekomendasi_bantuan_pendidikans.*','roles.name','users.name','b.name_village','d.name_districts')
                ->orWhere(function($query) use ($user_wilayah) {
                    $query->where('rekomendasi_bantuan_pendidikans.id_kabkot_bantuan_pendidikans', $user_wilayah->kota_id)
                                // ->where('log_bantuan_pendidikan.tujuan_log_bantuan_pendidikans','=', $user_wilayah->role_id)
                                // ->where('log_bantuan_pendidikan.updated_by_log_bantuan_pendidikans','!=', $user_wilayah->model_id)
                                ->where(function($query){
                                    $query->where('rekomendasi_bantuan_pendidikans.status_alur_bantuan_pendidikans', '=', 'Tolak')
                                        ->orWhere('rekomendasi_bantuan_pendidikans.status_alur_bantuan_pendidikans', '=', 'Selesai');
                                });
                })->distinct();
            }elseif ($user_wilayah->name == 'kepala bidang' ) {
                        // dd($user_wilayah);
                $query = DB::table('rekomendasi_bantuan_pendidikans')
               ->join('users', 'users.id', 'rekomendasi_bantuan_pendidikans.petugas_bantuan_pendidikans')
                // ->join('log_bantuan_pendidikan', 'log_bantuan_pendidikan.id_trx_log_bantuan_pendidikans', '=', 'rekomendasi_bantuan_pendidikans.id')
                ->join('roles', 'roles.id', '=', 'rekomendasi_bantuan_pendidikans.tujuan_bantuan_pendidikans')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_bantuan_pendidikans.id_kelurahan_bantuan_pendidikans')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_bantuan_pendidikans.id_kecamatan_bantuan_pendidikans')
                ->select('rekomendasi_bantuan_pendidikans.*','roles.name','users.name','b.name_village','d.name_districts')
                ->orWhere(function($query) use ($user_wilayah) {
                    $query->where('rekomendasi_bantuan_pendidikans.id_kabkot_bantuan_pendidikans', $user_wilayah->kota_id)
                                // ->where('log_bantuan_pendidikan.tujuan_log_bantuan_pendidikans','=', $user_wilayah->role_id)
                                // // ->where('log_bantuan_pendidikan.updated_by_log_bantuan_pendidikans','!=', $user_wilayah->model_id)
                                ->where(function($query){
                                    $query->where('rekomendasi_bantuan_pendidikans.status_alur_bantuan_pendidikans', '=', 'Tolak')
                                        ->orWhere('rekomendasi_bantuan_pendidikans.status_alur_bantuan_pendidikans', '=', 'Selesai');
                                });
                })->distinct();
            }elseif ($user_wilayah->name == 'supervisor' ) {
                // dd($user_wilayah);
                $query = DB::table('rekomendasi_bantuan_pendidikans')
                ->join('users', 'users.id', 'rekomendasi_bantuan_pendidikans.petugas_bantuan_pendidikans')
                // ->join('log_bantuan_pendidikan', 'log_bantuan_pendidikan.id_trx_log_bantuan_pendidikans', '=', 'rekomendasi_bantuan_pendidikans.id')
                ->join('roles', 'roles.id', '=', 'rekomendasi_bantuan_pendidikans.tujuan_bantuan_pendidikans')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_bantuan_pendidikans.id_kelurahan_bantuan_pendidikans')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_bantuan_pendidikans.id_kecamatan_bantuan_pendidikans')
                ->select('rekomendasi_bantuan_pendidikans.*','roles.name','users.name','b.name_village','d.name_districts')
                ->orWhere(function($query) use ($user_wilayah) {
                    $query->where('rekomendasi_bantuan_pendidikans.id_kecamatan_bantuan_pendidikans', $user_wilayah->kecamatan_id)
                                // ->where('log_bantuan_pendidikan.tujuan_log_bantuan_pendidikans','=', $user_wilayah->role_id)
                                // ->where('log_bantuan_pendidikan.updated_by_log_bantuan_pendidikans','!=', $user_wilayah->model_id)
                                ->where(function($query){
                                    $query->where('rekomendasi_bantuan_pendidikans.status_alur_bantuan_pendidikans', '=', 'Tolak')
                                        ->orWhere('rekomendasi_bantuan_pendidikans.status_alur_bantuan_pendidikans', '=', 'Selesai');
                                });
                })->distinct();
            }
          
           
            if ($user_wilayah->name == 'Front Office Kelurahan' || $user_wilayah->name == 'Back Ofiice kelurahan'|| $user_wilayah->name == 'fasilitator') {
                if ($request->has('search') && !empty($request->search['value'])) {
                    $search = $request->search['value'];
                    $query = DB::table('rekomendasi_bantuan_pendidikans')
                    ->join('users', 'users.id', 'rekomendasi_bantuan_pendidikans.petugas_bantuan_pendidikans')
                    // ->join('log_bantuan_pendidikan', 'log_bantuan_pendidikan.id_trx_log_bantuan_pendidikans', '=', 'rekomendasi_bantuan_pendidikans.id')
                    ->join('roles', 'roles.id', '=', 'rekomendasi_bantuan_pendidikans.tujuan_bantuan_pendidikans')
                    ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_bantuan_pendidikans.id_kelurahan_bantuan_pendidikans')
                    ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_bantuan_pendidikans.id_kecamatan_bantuan_pendidikans')
                    ->select('rekomendasi_bantuan_pendidikans.*','roles.name','users.name','b.name_village','d.name_districts')
                    ->orWhere(function($query) use ($user_wilayah) {
                        $query->where('rekomendasi_bantuan_pendidikans.id_kelurahan_bantuan_pendidikans', $user_wilayah->kelurahan_id)
                                    // ->where('log_bantuan_pendidikan.tujuan_log_bantuan_pendidikans','=', $user_wilayah->role_id)
                                    // ->where('log_bantuan_pendidikan.updated_by_log_bantuan_pendidikans','!=', $user_wilayah->model_id)
                                    ->where(function($query){
                                        $query->where('rekomendasi_bantuan_pendidikans.status_alur_bantuan_pendidikans', '=', 'Tolak')
                                            ->orWhere('rekomendasi_bantuan_pendidikans.status_alur_bantuan_pendidikans', '=', 'Selesai');
                                    });
                    })
                    ->where(function ($query) use ($search) {
                        $query->where('rekomendasi_bantuan_pendidikans.nik_bantuan_pendidikans', 'like', "%$search%")
                            ->orwhere('rekomendasi_bantuan_pendidikans.nama_bantuan_pendidikans', 'like', "%$search%")
                            ->orwhere('b.name_village', 'like', "%$search%")
                            ->orwhere('d.name_districts', 'like', "%$search%")
                            // ->orwhere('indonesia_villages.name_village', 'like', "%$search%")
                            ->orwhere('rekomendasi_bantuan_pendidikans.alamat_bantuan_pendidikans', 'like', "%$search%");
                    });
                }
            }elseif($user_wilayah->name == 'Front Office Kota' || $user_wilayah->name == 'Back Ofiice Kota'|| $user_wilayah->name == 'KepalaDinas'||$user_wilayah->name == 'SekertarisDinas'||$user_wilayah->name == 'Supervisor') {
                if ($request->has('search') && !empty($request->search['value'])) {
                    $search = $request->search['value'];
                    $query = DB::table('rekomendasi_bantuan_pendidikans')
                    ->join('users', 'users.id', '=', 'rekomendasi_bantuan_pendidikans.petugas_bantuan_pendidikans')
                    // ->join('log_bantuan_pendidikan', 'log_bantuan_pendidikan.id_trx_log_bantuan_pendidikans', '=', 'rekomendasi_bantuan_pendidikans.id')
                    ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_bantuan_pendidikans.id_kelurahan_bantuan_pendidikans')
                    ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_bantuan_pendidikans.id_kecamatan_bantuan_pendidikans')
                    ->select('rekomendasi_bantuan_pendidikans.*', 'b.name_village', 'd.name_districts', 'log_bantuan_pendidikan.tujuan_log_bantuan_pendidikans', 'log_bantuan_pendidikan.petugas_log_bantuan_pendidikans','users.name')
                    ->orWhere(function ($query) use ($user_wilayah) {
                        $query->where('rekomendasi_bantuan_pendidikans.id_kabkot_bantuan_pendidikans', $user_wilayah->kota_id)
                            ->where('log_bantuan_pendidikan.tujuan_log_bantuan_pendidikans', '!=', $user_wilayah->role_id)
                            // ->where('log_bantuan_pendidikan.updated_by_log_bantuan_pendidikans', '<>', auth::user()->id)
                            // ->where('rekomendasi_bantuan_pendidikans.petugas','!=', $user_wilayah->model_id)
                            ->where(function ($query) {
                                $query->where('rekomendasi_bantuan_pendidikans.status_alur_bantuan_pendidikans', '=', 'Teruskan')
                                    ->orWhere('rekomendasi_bantuan_pendidikans.status_alur_bantuan_pendidikans', '=', 'kembalikan');
                            });
                    })
                    ->where(function ($query) use ($search) {
                        $query->where('rekomendasi_bantuan_pendidikans.nik_bantuan_pendidikans', 'like', "%$search%")
                            ->orwhere('rekomendasi_bantuan_pendidikans.nama_bantuan_pendidikans', 'like', "%$search%")
                            ->orwhere('b.name_village', 'like', "%$search%")
                            ->orwhere('d.name_districts', 'like', "%$search%")
                            // ->orwhere('indonesia_villages.name_village', 'like', "%$search%")
                            ->orwhere('rekomendasi_bantuan_pendidikans.alamat_bantuan_pendidikans', 'like', "%$search%");
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
        // dd($data);
        // mengubah data JSON menjadi objek PHP

        return response()->json([
            'draw' => $request->input('draw'),
            'recordsTotal' => rekomendasi_bantuan_pendidikan::count(),
            'recordsFiltered' => $total_filtered_items,
            'data' => $data,
        ]);
    }
    public function getPetugasBantuanPendidikan($id)
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
       
        
        if ($user_wilayah->name == 'Front Office Kelurahan' || $user_wilayah == 'fasilitator'){
            $users = DB::table('users as u')
                ->leftJoin('model_has_roles as mhr', 'u.id', '=', 'mhr.model_id')
                ->leftJoin('roles as r', 'mhr.role_id', '=', 'r.id')
                ->leftJoin('wilayahs', 'wilayahs.createdby', '=', 'u.id')
                ->select('u.id as user_id', 'u.name', 'u.email', 'r.name as role')
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
            ->where('mhr.model_type', '=', 'App\Models\User')
            ->where('wilayahs.kota_id', '=', $wilayah->kota_id)
            ->where('mhr.role_id', '=', $id)
            ->select('u.id as user_id', 'u.name', 'u.email', 'r.name as role')
            ->get();
			return response()->json($users);
        if ($users->empty()) {
                $users = DB::table('users as u')
                ->leftJoin('model_has_roles as mhr', 'u.id', '=', 'mhr.model_id')
                ->leftJoin('roles as r', 'mhr.role_id', '=', 'r.id')
                ->leftJoin('wilayahs', 'wilayahs.createdby', '=', 'u.id')
                ->select('u.id as user_id', 'u.name', 'u.email', 'r.name as role')
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
                ->select('u.id as user_id', 'u.name', 'u.email', 'r.name as role')
                ->where('mhr.model_type', '=', 'App\Models\User') 
                ->where('wilayahs.kelurahan_id', '=',$wilayah->kelurahan_id)
                ->where('mhr.role_id', '=', $id)
                ->get();
        //    dd($users);
         
        }
        return response()->json($users);
    }
    
}
