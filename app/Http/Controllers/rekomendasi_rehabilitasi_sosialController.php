<?php

namespace App\Http\Controllers;

use App\Http\Controllers\AppBaseController;
use App\Models\log_resos;
use App\Models\pelapor;
use App\Models\Prelist;
use App\Models\rekomendasi_rehabilitasi_sosial;
use App\Models\Roles;
use App\Repositories\rekomendasi_rehabilitasi_sosialRepository;
use Illuminate\Http\Request;
use Flash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use ImageKit\ImageKit;


class rekomendasi_rehabilitasi_sosialController extends AppBaseController
{
    /** @var rekomendasi_rehabilitasi_sosialRepository $rekomendasiRehabilitasiSosialRepository*/
    private $rekomendasiRehabilitasiSosialRepository;

    public function __construct(rekomendasi_rehabilitasi_sosialRepository $rekomendasiRehabilitasiSosialRepo)
    {
        $this->rekomendasiRehabilitasiSosialRepository = $rekomendasiRehabilitasiSosialRepo;
    }

    /**
     * Display a listing of the rekomendasi_rehabilitasi_sosial.
     */
    public function index(Request $request)
    {
        $rekomendasiRehabilitasiSosials = $this->rekomendasiRehabilitasiSosialRepository->paginate(10);

        return view('rekomendasi_rehabilitasi_sosials.index')
            ->with('rekomendasiRehabilitasiSosials', $rekomendasiRehabilitasiSosials);
    }
    public function FileRehabsos($id)
    {
        $rehabsos = rekomendasi_rehabilitasi_sosial::find($id);
        // dd($rehabsos);
        $getIdDtks = DB::table('rekomendasi_rehabilitasi_sosials as w')->select(
            'w.*',
            'dtks.Id_DTKS'
        )
            ->leftjoin('dtks', 'dtks.Nik', '=', 'w.nik_resos')
            // ->where('status_wilayah', '1')
            ->where('dtks.Nik', $rehabsos->nik_resos)->first();
        // dd($getIdDtks);
        
        if (!is_null($getIdDtks) && !is_null($getIdDtks->Id_DTKS)) {
            $data_dtks = $getIdDtks->Id_DTKS;
        } else {
            $data_dtks = '-';
        }

       $date = Carbon::parse($rehabsos->tgl_lahir_resos)->locale('id');

       $date->settings(['formatFunction' => 'translatedFormat']);

       $tanggal = $date->format('j F Y ');


       // dd($tanggal);
        $pdf = PDF::loadHtml(view('rekomendasi_rehabilitasi_sosials.file_permohonan',compact('rehabsos','tanggal','data_dtks')));
        $pdf->setPaper('F4', 'portrait');
        $filename = 'File Permohonan' . $rehabsos->nama . '.pdf';
        return $pdf->stream($filename);
    }
 
    /**
     * Show the form for creating a new rekomendasi_rehabilitasi_sosial.
     */
    public function create()
    {
        $v = rekomendasi_rehabilitasi_sosial::latest()->first();
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
        // dd($wilayah);

        // $alur = DB::table('alur')
        //     ->where('name', 'Draft')
        //     // ->where('name', 'supervisor')
        //     ->orWhere('name', 'Teruskan')
        //     ->get();

        $user = Auth::user();
        $roles = $user->roles()->pluck('name');

        if ($roles->contains('Front Office Kelurahan')) {
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


        $user = Auth::user()->id;
        $checkuserrole = DB::table('model_has_roles')
        ->leftjoin('roles', 'roles.id', '=', 'model_has_roles.role_id')
        ->where('model_id', '=', $user)
        ->first();
        // dd($checkuserrole);
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
                ->where('name', 'Back Ofiice Kelurahan')
                // ->where('name', 'supervisor')
                ->orWhere('name', 'supervisor')
                ->get();
        }
        $checkroles = Roles::where('name', 'Front Office kota')
            ->orWhere('name', 'SekertarisDinas')
            ->orWhere('name', 'KepalaDinas')
            ->get();
        return view('rekomendasi_rehabilitasi_sosials.create', compact('kecamatans', 'wilayah', 'roleid', 'checkuserrole', 'alur'));
        // return view('rekomendasi_resos.create');
    }

    public function cekIdRehab(Request $request, $Nik)
    {
        $found = false;
        $table2 = DB::table('dtks')->where('nik_resos', $Nik)->first();
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
     * Store a newly created rekomendasi_rehabilitasi_sosial in storage.
     */
    public function store(Request $request)
    {
        // dd($request->get('id_kabkot_resos'));
        if ($request->get('status_alur_resos') != 'Draft') {
            // jika status_alur_resos sama dengan Draft akan nmasuk kondisi sini
            if ($request->get('status_dtks_resos') == 'Terdaftar') {
               
                // jika status_dtks_resos sama dengan terdaftar akan nmasuk kondisi sini
                $data = new rekomendasi_rehabilitasi_sosial();
                $files = [
                    'file_ktp_terlapor_resos' => 'rehabilitasi_sosial/ktp/',
                    'file_kk_terlapor_resos' => 'rehabilitasi_sosial/kk/',
                    'file_keterangan_dtks_resos' => 'rehabilitasi_sosial/strukturorganisasi/',
                    'file_pendukung_resos' => 'rehabilitasi_sosial/wajibpajak/'
                ];
                foreach ($files as $file => $directory) {
                    if ($request->file($file)) {
                        $path = $request->file($file);
                        $filename = $directory . $path->getClientOriginalName();
                        $return = Storage::disk('imagekit')->put($filename, fopen($path->getRealPath(), 'r'));
                        // $return = Storage::disk('imagekit')->put($filename, File::get($path));
                        // dd($return);
                        $data->$file = Storage::disk('imagekit')->url($filename);
                        // dd($data->$file );
                    } else {
                        $data->$file = null;
                    }
                }


                // $data->id_alur = $request->get('id_alur_resos');
                $data->no_pendaftaran_resos = mt_rand(100, 1000);
                $data->id_provinsi_resos = $request->get('id_provinsi_resos');
                $data->id_kabkot_resos = $request->get('id_kabkot_resos');
                $data->id_kecamatan_resos = $request->get('id_kecamatan_resos');
                $data->id_kelurahan_resos = $request->get('id_kelurahan_resos');
                $data->jenis_pelapor_resos = $request->get('jenis_pelapor_resos');
                $data->ada_nik_resos = $request->get('ada_nik_resos');
                $data->nik_resos = $request->get('nik_resos');
                $data->no_kk_resos = $request->get('no_kk_resos');
                // $data->no_kis = $request->get('no_kis');
                $data->nama_resos = $request->get('nama_resos');
                $data->tgl_lahir_resos = $request->get('tgl_lahir_resos');
                $data->tempat_lahir_resos = $request->get('tempat_lahir_resos');
                $data->jenis_kelamin_resos = $request->get('jenis_kelamin_resos');
                $data->telp_resos = $request->get('telp_resos');
                $data->alamat_resos = $request->get('alamat_resos');
                $data->status_dtks_resos = $request->get('status_dtks_resos');
                $data['catatan_resos']  = $request->get('catatan_resos');
                $data->tujuan_resos = $request->get('tujuan_resos');
                $data->status_aksi_resos = $request->get('status_aksi_resos');
                $data->petugas_resos = $request->get('petugas_resos');
                $data->createdby_resos = Auth::user()->id;
                $data->updatedby_resos = Auth::user()->id;
                // dd($data);
                $data->save();
                $logpengaduan = new log_resos();
                $logpengaduan['id_trx_resos'] = $data->id;
                $logpengaduan['id_alur_resos'] = $request->get('status_aksi_resos');
                $logpengaduan['petugas_resos'] = $request->get('petugas_resos');
                $logpengaduan['catatan_resos']  = $request->get('catatan_resos');
                $logpengaduan['draft_rekomendasi_resos'] = $request->get('file_pendukung');
                $logpengaduan['tujuan_resos'] = $request->get('tujuan_resos');
                $logpengaduan['created_by_resos'] = Auth::user()->id;
                $logpengaduan['updated_by_resos'] = Auth::user()->id;

                $logpengaduan->save();
                // dd($request->get('jenis_pelapor_resos'));
               if($request->get('jenis_pelapor_resos') == 'Orang Lain'){
                    $pelapor = new pelapor();
                    
                    $pelapor['id_menu'] = '03';
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
                    $pelapor['id_menu'] = '03';
                    $pelapor['id_form'] = $data->id;
                    $pelapor['jenis_peelaporan'] = $request->get('jenis_pelapor_resos');
                    $pelapor['nama_pelapor']  =  $request->get('nama_resos');
                    $pelapor['ada_nik_pelapor'] = $request->get('ada_nik_resos');
                    $pelapor['nik_pelapor'] = $request->get('nik_resos');
                    $pelapor['status_dtks_pelapor'] = $request->get('status_dtks_resos');
                    $pelapor['tempat_lahir_pelapor'] = $request->get('tempat_lahir_resos');
                    $pelapor['tanggal_lahir_pelapor'] = $request->get('tgl_lahir_resos');
                    $pelapor['jenis_kelamin'] = $request->get('jenis_kelamin_resos');
                    // $pelapor['nama_pelapor']  = $request->get('nama_resos');
                    $pelapor['telepon_pelapor'] = $request->get('telp_resos');
                    $pelapor['alamat_pelapor'] = $request->get('alamat_resos');
                    $pelapor['createdby_pelapor'] = Auth::user()->id;
                    $pelapor['updatedby_pelapor'] = Auth::user()->id;

                    $pelapor->save();
                }
             

                return redirect('rekomendasi_rehabilitasi_sosials')->withSuccess('Data Rekomendasi Berhasil Ditambahkan');
            } else {
                // jika status_dtks_resos sama dengan terdaftar akan nmasuk kondisi sini
                $cek = Prelist::where('nik', '=', $request->get('nik_resos'))->exists();
                if (!$cek) {
                    // jika nik tidak ada nik akan masuk ke sini
                    $data = new Prelist;
                    $data['id_provinsi'] = $request->get('id_provinsi_resos');
                    $data['id_kabkot'] = $request->get('id_kabkot_resos');
                    $data['id_kecamatan'] = $request->get('id_kecamatan_resos');
                    $data['id_kelurahan'] = $request->get('id_kelurahan_resos');
                    $data['nik'] = $request->get('nik_resos');
                    $data['no_kk'] = $request->get('no_kk_resos');
                    // $data['no_kis'] = $request->get('no_kis_resos');
                    $data['nama'] = $request->get('nama_resos');
                    $data['tgl_lahir'] = $request->get('tgl_lahir_resos');
                    // $data['alamat'] = $request->get('alamat_resos');
                    $data['telp'] = $request->get('telpon_resos');
                    $data['email'] = $request->get('email_resos');
                    $data['status_data'] = 'prelistdtks';

                    $data->save();
                    $data = new rekomendasi_rehabilitasi_sosial();
                    $files = [
                        'file_ktp_terlapor_resos' => 'rehabilitasi_sosial/ktp/',
                        'file_kk_terlapor_resos' => 'rehabilitasi_sosial/kk/',
                        'file_keterangan_dtks_resos' => 'rehabilitasi_sosial/strukturorganisasi/',
                        'file_pendukung_resos' => 'rehabilitasi_sosial/wajibpajak/'
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

                    // $data->id_alur = $request->get('id_alur_resos');
                    $data->no_pendaftaran_resos = mt_rand(100, 1000);
                    $data->id_provinsi_resos = $request->get('id_provinsi_resos');
                    $data->id_kabkot_resos = $request->get('id_kabkot_resos');
                    $data->id_kecamatan_resos = $request->get('id_kecamatan_resos');
                    $data->id_kelurahan_resos = $request->get('id_kelurahan_resos');
                    $data->jenis_pelapor_resos = $request->get('jenis_pelapor_resos');
                    $data->ada_nik_resos = $request->get('ada_nik_resos');
                    $data->nik_resos = $request->get('nik_resos');
                    $data->no_kk_resos = $request->get('no_kk_resos');
                    // $data->no_kis = $request->get('no_kis');
                    $data->nama_resos = $request->get('nama_resos');
                    $data->tgl_lahir_resos = $request->get('tgl_lahir_resos');
                    $data->tempat_lahir_resos = $request->get('tempat_lahir_resos');
                    $data->jenis_kelamin_resos = $request->get('jenis_kelamin_resos');
                    $data->telp_resos = $request->get('telp_resos');
                    $data->alamat_resos = $request->get('alamat_resos');
                    $data['catatan_resos']  = $request->get('catatan_resos');
                    $data->status_dtks_resos = $request->get('status_dtks_resos');
                    $data->tujuan_resos = $request->get('tujuan_resos');
                    $data->status_aksi_resos = $request->get('status_aksi_resos');
                    $data->petugas_resos = $request->get('petugas_resos');
                    $data->createdby_resos = Auth::user()->id;
                    $data->updatedby_resos = Auth::user()->id;
                    // dd($data);
                    $data->save();
                    $logpengaduan = new log_resos();
                    $logpengaduan['id_trx_resos'] = $data->id;
                    $logpengaduan['id_alur_resos'] = $request->get('status_aksi_resos');
                    $logpengaduan['petugas_resos'] = $request->get('petugas_resos');
                    $logpengaduan['catatan_resos']  = $request->get('catatan_resos');
                    $logpengaduan['draft_rekomendasi_resos'] = $request->get('file_pendukung');
                    $logpengaduan['tujuan_resos'] = $request->get('tujuan_resos');
                    $logpengaduan['created_by_resos'] = Auth::user()->id;
                    $logpengaduan['updated_by_resos'] = Auth::user()->id;

                    $logpengaduan->save();
                    return redirect('rekomendasi_rehabilitasi_sosials')->withSuccess('Data Rekomendasi Berhasil Ditambahkan');
                    if($request->get('jenis_pelapor_resos') == 'Orang Lain'){
                        $pelapor = new pelapor();
                        
                        $pelapor['id_menu'] = '03';
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
                        $pelapor['id_menu'] = '03';
                        $pelapor['id_form'] = $data->id;
                        $pelapor['jenis_peelaporan'] = $request->get('jenis_pelapor_resos');
                        $pelapor['nama_pelapor']  =  $request->get('nama_resos');
                        $pelapor['ada_nik_pelapor'] = $request->get('ada_nik_resos');
                        $pelapor['nik_pelapor'] = $request->get('nik_resos');
                        $pelapor['status_dtks_pelapor'] = $request->get('status_dtks_resos');
                        $pelapor['tempat_lahir_pelapor'] = $request->get('tempat_lahir_resos');
                        $pelapor['tanggal_lahir_pelapor'] = $request->get('tgl_lahir_resos');
                        $pelapor['jenis_kelamin'] = $request->get('jenis_kelamin_resos');
                        // $pelapor['nama_pelapor']  = $request->get('nama_resos');
                        $pelapor['telepon_pelapor'] = $request->get('telp_resos');
                        $pelapor['alamat_pelapor'] = $request->get('alamat_resos');
                        $pelapor['createdby_pelapor'] = Auth::user()->id;
                        $pelapor['updatedby_pelapor'] = Auth::user()->id;
    
                        $pelapor->save();
                    }
                } else {
                    //jika nik ada di prelist akan masuk ke sini
                    $data = new rekomendasi_rehabilitasi_sosial();
                    $files = [
                        'file_ktp_terlapor_resos' => 'rehabilitasi_sosial/ktp/',
                        'file_kk_terlapor_resos' => 'rehabilitasi_sosial/kk/',
                        'file_keterangan_dtks_resos' => 'rehabilitasi_sosial/strukturorganisasi/',
                        'file_pendukung_resos' => 'rehabilitasi_sosial/wajibpajak/'
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

                    // $data->id_alur = $request->get('id_alur_resos');
                    $data->no_pendaftaran_resos = mt_rand(100, 1000);
                    $data->id_provinsi_resos = $request->get('id_provinsi_resos');
                    $data->id_kabkot_resos = $request->get('id_kabkot_resos');
                    $data->id_kecamatan_resos = $request->get('id_kecamatan_resos');
                    $data->id_kelurahan_resos = $request->get('id_kelurahan_resos');
                    $data->jenis_pelapor_resos = $request->get('jenis_pelapor_resos');
                    $data->ada_nik_resos = $request->get('ada_nik_resos');
                    $data->nik_resos = $request->get('nik_resos');
                    $data->no_kk_resos = $request->get('no_kk_resos');
                    // $data->no_kis = $request->get('no_kis');
                    $data->nama_resos = $request->get('nama_resos');
                    $data->tgl_lahir_resos = $request->get('tgl_lahir_resos');
                    $data->tempat_lahir_resos = $request->get('tempat_lahir_resos');
                    $data->jenis_kelamin_resos = $request->get('jenis_kelamin_resos');
                    $data->telp_resos = $request->get('telp_resos');
                    $data->alamat_resos = $request->get('alamat_resos');
                    $data->status_dtks_resos = $request->get('status_dtks_resos');
                    $data['catatan_resos']  = $request->get('catatan_resos');
                    $data->tujuan_resos = $request->get('tujuan_resos');
                    $data->status_aksi_resos = $request->get('status_aksi_resos');
                    $data->petugas_resos = $request->get('petugas_resos');
                    $data->createdby_resos = Auth::user()->id;
                    $data->updatedby_resos = Auth::user()->id;
                    // dd($data);
                    $data->save();
                    $logpengaduan = new log_resos();
                    $logpengaduan['id_trx_resos'] = $data->id;
                    $logpengaduan['id_alur_resos'] = $request->get('status_aksi_resos');
                    $logpengaduan['petugas_resos'] = $request->get('petugas_resos');
                    $logpengaduan['catatan_resos']  = $request->get('catatan_resos');
                    $logpengaduan['draft_rekomendasi_resos'] = $request->get('file_pendukung_resos');
                    $logpengaduan['tujuan_resos'] = $request->get('tujuan_resos');
                    $logpengaduan['created_by_resos'] = Auth::user()->id;
                    $logpengaduan['updated_by_resos'] = Auth::user()->id;

                    $logpengaduan->save();
                    return redirect('rekomendasi_rehabilitasi_sosials')->withSuccess('Data Rekomendasi Berhasil Ditambahkan');
                    if($request->get('jenis_pelapor_resos') == 'Orang Lain'){
                        $pelapor = new pelapor();
                        
                        $pelapor['id_menu'] = '03';
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
                        $pelapor['id_menu'] = '03';
                        $pelapor['id_form'] = $data->id;
                        $pelapor['jenis_peelaporan'] = $request->get('jenis_pelapor_resos');
                        $pelapor['nama_pelapor']  =  $request->get('nama_resos');
                        $pelapor['ada_nik_pelapor'] = $request->get('ada_nik_resos');
                        $pelapor['nik_pelapor'] = $request->get('nik_resos');
                        $pelapor['status_dtks_pelapor'] = $request->get('status_dtks_resos');
                        $pelapor['tempat_lahir_pelapor'] = $request->get('tempat_lahir_resos');
                        $pelapor['tanggal_lahir_pelapor'] = $request->get('tgl_lahir_resos');
                        $pelapor['jenis_kelamin'] = $request->get('jenis_kelamin_resos');
                        // $pelapor['nama_pelapor']  = $request->get('nama_resos');
                        $pelapor['telepon_pelapor'] = $request->get('telp_resos');
                        $pelapor['alamat_pelapor'] = $request->get('alamat_resos');
                        $pelapor['createdby_pelapor'] = Auth::user()->id;
                        $pelapor['updatedby_pelapor'] = Auth::user()->id;
    
                        $pelapor->save();
                    }
                }
            }
        } else {
            //jika status draft adalah ini akan masuk ke sini
            $data = new rekomendasi_rehabilitasi_sosial();
            $files = [
                'file_ktp_terlapor_resos' => 'rehabilitasi_sosial/ktp/',
                'file_kk_terlapor_resos' => 'rehabilitasi_sosial/kk/',
                'file_keterangan_dtks_resos' => 'rehabilitasi_sosial/strukturorganisasi/',
                'file_pendukung_resos' => 'rehabilitasi_sosial/wajibpajak/'
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

            // $data->id_alur = $request->get('id_alur_resos');
            $data->no_pendaftaran_resos = mt_rand(100, 1000);
            $data->id_provinsi_resos = $request->get('id_provinsi_resos');
            $data->id_kabkot_resos = $request->get('id_kabkot_resos');
            $data->id_kecamatan_resos = $request->get('id_kecamatan_resos');
            $data->id_kelurahan_resos = $request->get('id_kelurahan_resos');
            $data->jenis_pelapor_resos = $request->get('jenis_pelapor_resos');
            $data->ada_nik_resos = $request->get('ada_nik_resos');
            $data->nik_resos = $request->get('nik_resos');
            $data->no_kk_resos = $request->get('no_kk_resos');
            // $data->no_kis = $request->get('no_kis');
            $data->nama_resos = $request->get('nama_resos');
            $data->tgl_lahir_resos = $request->get('tgl_lahir_resos');
            $data->tempat_lahir_resos = $request->get('tempat_lahir_resos');
            $data->jenis_kelamin_resos = $request->get('jenis_kelamin_resos');
            $data->telp_resos = $request->get('telp_resos');
            $data->status_dtks_resos = $request->get('status_dtks_resos');
            $data['catatan_resos']  = $request->get('catatan_resos');
            $data->tujuan_resos = $request->get('tujuan_resos');
            $data->status_aksi_resos = $request->get('status_aksi_resos');
            $data->petugas_resos = $request->get('petugas_resos');
            $data->createdby_resos = Auth::user()->id;
            $data->updatedby_resos = Auth::user()->id;
            // dd($data);
            $data->save();
            return redirect('rekomendasi_rehabilitasi_sosials')->withWarning('NIK Tidak Tersedia Data Disimpan sebagai draft');
            if($request->get('jenis_pelapor_resos') == 'Orang Lain'){
                $pelapor = new pelapor();
                
                $pelapor['id_menu'] = '03';
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
                $pelapor['id_menu'] = '03';
                $pelapor['id_form'] = $data->id;
                $pelapor['jenis_peelaporan'] = $request->get('jenis_pelapor_resos');
                $pelapor['nama_pelapor']  =  $request->get('nama_resos');
                $pelapor['ada_nik_pelapor'] = $request->get('ada_nik_resos');
                $pelapor['nik_pelapor'] = $request->get('nik_resos');
                $pelapor['status_dtks_pelapor'] = $request->get('status_dtks_resos');
                $pelapor['tempat_lahir_pelapor'] = $request->get('tempat_lahir_resos');
                $pelapor['tanggal_lahir_pelapor'] = $request->get('tgl_lahir_resos');
                $pelapor['jenis_kelamin'] = $request->get('jenis_kelamin_resos');
                // $pelapor['nama_pelapor']  = $request->get('nama_resos');
                $pelapor['telepon_pelapor'] = $request->get('telp_resos');
                $pelapor['alamat_pelapor'] = $request->get('alamat_resos');
                $pelapor['createdby_pelapor'] = Auth::user()->id;
                $pelapor['updatedby_pelapor'] = Auth::user()->id;

                $pelapor->save();
            }
        }
    }

    /**
     * Display the specified rekomendasi_rehabilitasi_sosial.
     */
    public function show($id)
    {
        $userid = Auth::user()->id;
        $rekomendasiRehabilitasiSosial = DB::table('rekomendasi_rehabilitasi_sosials as w')->select(
            'w.*',
            'b.name_village',
            'prov.name_prov',
            'kota.name_cities',
            'kecamatan.name_districts',
            'roles.name as name_roles',
            'users.name',
            // 'w.status_wilayah',
        )
        ->leftjoin('users', 'users.id', '=', 'w.petugas_resos')
        ->leftjoin('roles', 'roles.id', '=', 'w.tujuan_resos')
        ->leftJoin('pelapor','pelapor.id_form','=','w.id')
        ->leftjoin('indonesia_provinces as prov', 'prov.code', '=', 'w.id_provinsi_resos')
        ->leftjoin('indonesia_cities as kota', 'kota.code', '=', 'w.id_kabkot_resos')
        ->leftjoin('indonesia_districts as kecamatan', 'kecamatan.code', '=', 'w.id_kecamatan_resos')
        ->leftjoin('indonesia_villages as b', 'b.code', '=', 'w.id_kelurahan_resos')
        ->where('w.id', $id)->first();
        // dd($rekomendasiRehabilitasiSosial);
        // $rekomendasiRehabilitasiSosial = $this->rekomendasiRehabilitasiSosialRepository->find((int) $id);
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
        $rekomendasiRehabilitasSosialPelapor = DB::table('rekomendasi_rehabilitasi_sosials')
            ->join('pelapor', function ($join) use ($id) {
                $join->on('rekomendasi_rehabilitasi_sosials.id', '=', 'pelapor.id_form')
                    ->where('rekomendasi_rehabilitasi_sosials.id', '=', $id);
            })
            ->select('rekomendasi_rehabilitasi_sosials.*', 'pelapor.*')
            ->where('pelapor.id_menu', '03')
            ->where('pelapor.id_form', $id)
            ->first();

        if (empty($rekomendasiRehabilitasiSosial)) {
            Flash::error('Rekomendasi not found');

            return redirect(route('rekomendasi_rehabilitasi_sosials.index'));
        }
        $roleid = DB::table('roles')
            ->where('name', 'Back Ofiice kelurahan')
            // ->where('name', 'supervisor')
            ->orWhere('name', 'supervisor')
            ->get();
        $checkroles = DB::table('model_has_roles')
            ->leftjoin('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->get();
        // dd($checkroles);
        $log_resos = DB::table('log_resos as w')->select(
            'w.*',
            'roles.name',
            'users.name',
            // 'rekomendasi_rehabilitasi_sosials.validsi_surat',
            // 'alur.name'

        )
        ->leftjoin('users', 'users.id', '=', 'w.created_by_resos')
        ->leftjoin('roles', 'roles.id', '=', 'w.tujuan_resos')
        // ->leftjoin('rekomendasi_rehabilitasi_sosials', 'w.id_trx_resos', '=', 'rekomendasi_rehabilitasi_sosials.id')
        ->where('w.id_trx_resos', $id)->get();
        
        $log_resos = DB::table('log_resos as w')->select(
            'w.*',
            'rls.name as name_update',
            'usr.name',
            'roles.name as name_roles',

        )
            ->leftjoin('roles', 'roles.id', '=', 'w.tujuan_resos')
            ->leftjoin('users as rls', 'rls.id', '=', 'w.updated_by_resos')
            ->leftjoin('users as usr', 'usr.id', '=', 'w.created_by_resos')
            ->where('w.id_trx_resos', $id)->get();
        // dd($log_resos);
        // $log_resos = log_resos::where('id_trx_resos', $id)->get();

        return view('rekomendasi_rehabilitasi_sosials.show', compact('rekomendasiRehabilitasSosialPelapor','rekomendasiRehabilitasiSosial', 'roleid', 'wilayah', 'checkroles', 'log_resos'));
    }

    /**
     * Show the form for editing the specified rekomendasi_rehabilitasi_sosial.
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
            ->leftjoin('rekomendasi_rehabilitasi_sosials', 'rekomendasi_rehabilitasi_sosials.createdby_resos', '=', 'model_has_roles.model_id')
            ->where('rekomendasi_rehabilitasi_sosials.id', '=', $id)
            // ->where('status_aksi', '=', 'Draft')
            // ->orwhere('status_aksi', '=', 'Teruskan')
            ->get();
        // dd($checkroles2);
        //Tujuan
        $createdby = DB::table('rekomendasi_rehabilitasi_sosials')
            ->join('users', 'rekomendasi_rehabilitasi_sosials.createdby_resos', '=', 'users.name')
            ->join('model_has_roles', 'model_has_roles.model_id', '=', 'users.id')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->select('rekomendasi_rehabilitasi_sosials.id', 'rekomendasi_rehabilitasi_sosials.createdby_resos', 'roles.name')
            ->get();
        $rekomendasiRehabilitasiSosial = DB::table('rekomendasi_rehabilitasi_sosials as w')->select(
            'w.*',
            'rls.name',
            'usr.name',
            'prov.name_prov',
            'kota.name_cities',
            'kecamatan.name_districts',
            'b.name_village',
            'p.*'
        )
            ->leftjoin('roles as rls', 'rls.id', '=', 'w.tujuan_resos')
            ->leftjoin('users as usr', 'usr.id', '=', 'w.petugas_resos')
            ->leftjoin('pelapor as p', 'p.id_form', 'w.id')
            ->leftjoin('indonesia_provinces as prov', 'prov.code', '=', 'w.id_provinsi_resos')
            ->leftjoin('indonesia_cities as kota', 'kota.code', '=', 'w.id_kabkot_resos')
            ->leftjoin('indonesia_districts as kecamatan', 'kecamatan.code', '=', 'w.id_kecamatan_resos')
            ->leftjoin('indonesia_villages as b', 'b.code', '=', 'w.id_kelurahan_resos')
            ->where('w.id', $id)->first();
        $rekomendasiRehabilitasSosialPelapor = DB::table('rekomendasi_rehabilitasi_sosials')
            ->join('pelapor', function ($join) use ($id) {
                $join->on('rekomendasi_rehabilitasi_sosials.id', '=', 'pelapor.id_form')
                    ->where('rekomendasi_rehabilitasi_sosials.id', '=', $id);
            })
            ->select('rekomendasi_rehabilitasi_sosials.*', 'pelapor.*')
            ->where('pelapor.id_menu', '03')
            ->where('pelapor.id_form', $id)
            ->first();
        // dd($rekomendasiRehabilitasSosialPelapor);
        $getdata = DB::table('model_has_roles')
            ->leftjoin('rekomendasi_rehabilitasi_sosials as b', 'b.tujuan_resos', '=', 'model_has_roles.role_id')
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
                ->whereIn('name', ['Kembalikan', 'Tolak', 'Teruskan'])
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
                ->where('name', 'kepala bidang ')
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

       
        // dd($rekomendasiKeringananPbb);   
        

        return view('rekomendasi_rehabilitasi_sosials.edit', compact('rekomendasiRehabilitasSosialPelapor','getAuth','wilayah', 'rekomendasiRehabilitasiSosial', 'roleid', 'getdata', 'alur', 'createdby', 'getUsers'));
    }
    /**
     * Update the specified rekomendasi_rehabilitasi_sosial in storage.
     */
    public function update($id, Request $request)
    {
        $userid = Auth::user()->id;
        $dataresos = rekomendasi_rehabilitasi_sosial::where('id', $id)->first();
        $pemebuatanDataRekomendasiResos = DB::table('rekomendasi_rehabilitasi_sosials as w')
		->join('model_has_roles', 'model_has_roles.model_id', '=', 'w.createdby_resos')
		->join('roles as rls', 'rls.id', '=', 'model_has_roles.role_id')

		->leftjoin('users', 'users.id', '=', 'w.createdby_resos')
		->select(
					'w.*',
					'rls.name as name_roles',
					// 'usr.name',
					'model_has_roles.*')
		->where('w.id', $id)->first();
        // dd($request->all());
        $data = $request->all();
        $files = [
            'file_ktp_terlapor_resos',
            'file_kk_terlapor_resos',
            'file_keterangan_dtks_resos',
            'file_pendukung_resos',
        ];
        foreach ($files as $file) {
            if ($request->file($file)) {
                $path = $request->file($file);
                $filename = $file . $path->getClientOriginalName();
                $return = Storage::disk('imagekit')->put($filename, fopen($path->getRealPath(), 'r'));
                $data[$file] = Storage::disk('imagekit')->url($filename);
            } else {
                $data[$file] = $pemebuatanDataRekomendasiResos->$file;
            }
        }
        if ( $request->get('status_aksi_resos') == 'Kembalikan' || $request->get('status_aksi_resos') == 'Selesai') {
                $data['petugas_resos']  = $pemebuatanDataRekomendasiResos->model_id;
                $resos['tujuan_resos'] = $pemebuatanDataRekomendasiResos->role_id;
        }else{
            $data['petugas_resos']  = $request->get('petugas_resos');
            $data['tujuan_resos'] = $request->get('tujuan_resos');
        }
        //   dd($data);
        $dataresos->update($data);
        $logpengaduan = new log_resos();
        
        $logpengaduan['id_trx_resos'] = $dataresos->id;
        $logpengaduan['id_alur_resos'] = $request->get('status_aksi_resos');
        // $logpengaduan['petugas_resos'] = $request->get('petugas_resos');
        $logpengaduan['catatan_resos']  = $request->get('catatan_resos');
        // $logpengaduan['file_pendukung_resos'] = $request->get('file_pendukung_resos');
        // $logpengaduan['tujuan_resos'] = $request->get('tujuan_resos');
        $logpengaduan['created_by_resos'] = Auth::user()->id;
        if ( $request->get('status_aksi_resos') == 'Kembalikan' || $request->get('status_aksi_resos') == 'Selesai'){
            $logpengaduan['petugas_resos']  = $pemebuatanDataRekomendasiResos->model_id;
            $logpengaduan['tujuan_resos'] = $pemebuatanDataRekomendasiResos->role_id;
        }else{
            $logpengaduan['petugas_resos']  = $request->get('petugas_resos');
            $logpengaduan['tujuan_resos'] = $request->get('tujuan_resos');
        }
        $logpengaduan['updated_by_resos'] = Auth::user()->id;
        // dd($logpengaduan);
        $logpengaduan->save();

        $pelapor['id_menu'] = '03';
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
        // $pelapor['createdby_pelapor'] = Auth::user()->id;
        $pelapor['updatedby_pelapor'] = Auth::user()->id;

        pelapor::where('id_form', $id)->update($pelapor);
        
        return redirect('rekomendasi_rehabilitasi_sosials')->withSuccess('Data Berhasil Diubah');

    }

    /**
     * Remove the specified rekomendasi_rehabilitasi_sosial from storage.
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $rekomendasiRehabilitasiSosial = $this->rekomendasiRehabilitasiSosialRepository->find($id);

        if (empty($rekomendasiRehabilitasiSosial)) {
            Flash::error('Rekomendasi Rehabilitasi Sosial not found');

            return redirect(route('rekomendasi_rehabilitasi_sosials.index'));
        }

        $this->rekomendasiRehabilitasiSosialRepository->delete($id);

        Flash::success('Rekomendasi Rehabilitasi Sosial deleted successfully.');

        return redirect(route('rekomendasi_rehabilitasi_sosials.index'));
    }
    public function draft(Request $request)
    {
        $user_name = Auth::user()->name;
        $query = DB::table('rekomendasi_rehabilitasi_sosials')
            ->leftjoin('users', 'users.id', '=', 'rekomendasi_rehabilitasi_sosials.createdby_resos')
            ->leftjoin('wilayahs', 'wilayahs.createdby', '=', 'rekomendasi_rehabilitasi_sosials.createdby_resos')
            ->leftjoin('model_has_roles', 'model_has_roles.model_id', '=', 'rekomendasi_rehabilitasi_sosials.tujuan_resos')
            ->leftjoin('indonesia_villages as b', 'b.code', '=', 'rekomendasi_rehabilitasi_sosials.id_kelurahan_resos')
            ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_rehabilitasi_sosials.id_kecamatan_resos')
            ->select('rekomendasi_rehabilitasi_sosials.*', 'b.name_village','d.name_districts')
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
        if ($user_wilayah->name == 'Front Office Kelurahan' ||$user_wilayah->name == 'fasilitator' ) {
            $query->orWhere(function ($query) use ($user_wilayah) {
                $query->where('rekomendasi_rehabilitasi_sosials.id_kelurahan_resos', $user_wilayah->kelurahan_id);
                $query->where('rekomendasi_rehabilitasi_sosials.status_aksi_resos', 'Draft');
                $query->where('rekomendasi_rehabilitasi_sosials.createdby_resos',  Auth::user()->id);
            });
        }
        if ($user_wilayah->name == 'Front Office kota') {
            $query->orWhere(function ($query) use ($user_wilayah) {
                $query->where('rekomendasi_rehabilitasi_sosials.id_kabkot_resos', $user_wilayah->kota_id);
                $query->where('rekomendasi_rehabilitasi_sosials.status_aksi_resos', 'Draft');
                $query->where('rekomendasi_rehabilitasi_sosials.createdby_resos',  Auth::user()->id);
            });
        }
        if ($user_wilayah->name == 'Front Office Kelurahan'|| $user_wilayah == 'fasilitator') {
            if ($request->has('search') && !empty($request->search['value'])) {
                $search = $request->search['value'];
                $query->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_rehabilitasi_sosials.id_kelurahan_resos', $user_wilayah->kelurahan_id);
                    $query->where('rekomendasi_rehabilitasi_sosials.status_aksi_resos', 'Draft');
                    $query->where('rekomendasi_rehabilitasi_sosials.createdby_resos',  Auth::user()->id);
                })
                ->where('rekomendasi_rehabilitasi_sosials.no_pendaftaran_resos', 'like', "%$search%");
            }
        }elseif($user_wilayah->name == 'Front Office Kota')  {
            if ($request->has('search') && !empty($request->search['value'])) {
                $search = $request->search['value'];
                $query->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_rehabilitasi_sosials.id_kabkot_resos', $user_wilayah->kota_id);
                    $query->where('rekomendasi_rehabilitasi_sosials.status_aksi_resos', 'Draft');
                    $query->where('rekomendasi_rehabilitasi_sosials.createdby_resos',  Auth::user()->id);
                })
                ->where('rekomendasi_rehabilitasi_sosials.no_pendaftaran_resos', 'like', "%$search%");
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
            'recordsTotal' => rekomendasi_rehabilitasi_sosial::count(),
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
        if ($user_wilayah->name == 'Front Office Kelurahan') {
            //  dd($user_wilayah->role_id);

            $query = DB::table('rekomendasi_rehabilitasi_sosials')
            ->join('users', 'users.id', '=', 'rekomendasi_rehabilitasi_sosials.petugas_resos')
            ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_rehabilitasi_sosials.id_kecamatan_resos')
            ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_rehabilitasi_sosials.id_kelurahan_resos')
            ->join('roles as r', 'r.id', '=', 'rekomendasi_rehabilitasi_sosials.tujuan_resos')
            ->select('rekomendasi_rehabilitasi_sosials.*', 'b.name_village', 'd.name_districts','users.name','r.name');
            $query->orWhere(function ($query) use ($user_wilayah) {
                $query->where('rekomendasi_rehabilitasi_sosials.id_kelurahan_resos', '=', $user_wilayah->kelurahan_id)
                    ->where('rekomendasi_rehabilitasi_sosials.tujuan_resos', '=', $user_wilayah->role_id)
                    ->where('rekomendasi_rehabilitasi_sosials.petugas_resos', '=', $user_wilayah->model_id)
                    ->where(function ($query) {
                        $query->where('rekomendasi_rehabilitasi_sosials.status_aksi_resos', '=', 'Teruskan')
                            ->orWhere('rekomendasi_rehabilitasi_sosials.status_aksi_resos', '=', 'kembalikan');
                    });
            });
            // dd($query);
        }
        if ($user_wilayah->name == 'Back Ofiice kelurahan') {
            //  dd($user_wilayah->role_id);

            $query = DB::table('rekomendasi_rehabilitasi_sosials')
            ->join('users', 'users.id', '=', 'rekomendasi_rehabilitasi_sosials.petugas_resos')
            ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_rehabilitasi_sosials.id_kecamatan_resos')
            ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_rehabilitasi_sosials.id_kelurahan_resos')
            ->join('roles as r', 'r.id', '=', 'rekomendasi_rehabilitasi_sosials.tujuan_resos')
            ->select('rekomendasi_rehabilitasi_sosials.*', 'b.name_village', 'd.name_districts','users.name','r.name');
            $query->orWhere(function ($query) use ($user_wilayah) {
                $query->where('rekomendasi_rehabilitasi_sosials.id_kelurahan_resos', '=', $user_wilayah->kelurahan_id)
                    ->where('rekomendasi_rehabilitasi_sosials.tujuan_resos', '=', $user_wilayah->role_id)
                    ->where('rekomendasi_rehabilitasi_sosials.petugas_resos', '=', $user_wilayah->model_id)
                    ->where(function ($query) {
                        $query->where('rekomendasi_rehabilitasi_sosials.status_aksi_resos', '=', 'Teruskan')
                            ->orWhere('rekomendasi_rehabilitasi_sosials.status_aksi_resos', '=', 'kembalikan');
                    });
            });
            // dd($query);
        }
        if ($user_wilayah->name == 'supervisor') {
            //  dd($user_wilayah->role_id);

            $query = DB::table('rekomendasi_rehabilitasi_sosials')
            ->join('users', 'users.id', '=', 'rekomendasi_rehabilitasi_sosials.petugas_resos')
            ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_rehabilitasi_sosials.id_kecamatan_resos')
            ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_rehabilitasi_sosials.id_kelurahan_resos')
            ->join('roles as r', 'r.id', '=', 'rekomendasi_rehabilitasi_sosials.tujuan_resos')
            ->select('rekomendasi_rehabilitasi_sosials.*', 'b.name_village', 'd.name_districts','users.name','r.name');
            $query->orWhere(function ($query) use ($user_wilayah) {
                $query->where('rekomendasi_rehabilitasi_sosials.id_kecamatan_resos', '=', $user_wilayah->kecamatan_id)
                    ->where('rekomendasi_rehabilitasi_sosials.tujuan_resos', '=', $user_wilayah->role_id)
                    ->where('rekomendasi_rehabilitasi_sosials.petugas_resos', '=', $user_wilayah->model_id)
                    ->where(function ($query) {
                        $query->where('rekomendasi_rehabilitasi_sosials.status_aksi_resos', '=', 'Teruskan')
                            ->orWhere('rekomendasi_rehabilitasi_sosials.status_aksi_resos', '=', 'kembalikan');
                    });
            });
            // dd($query);
        }
        if ($user_wilayah->name == 'Front Office kota') {
            //  dd($user_wilayah->role_id);

            $query = DB::table('rekomendasi_rehabilitasi_sosials')
            ->join('users', 'users.id', '=', 'rekomendasi_rehabilitasi_sosials.petugas_resos')
            ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_rehabilitasi_sosials.id_kecamatan_resos')
            ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_rehabilitasi_sosials.id_kelurahan_resos')
            ->join('roles as r', 'r.id', '=', 'rekomendasi_rehabilitasi_sosials.tujuan_resos')
            ->select('rekomendasi_rehabilitasi_sosials.*', 'b.name_village', 'd.name_districts','users.name','r.name');
            $query->orWhere(function ($query) use ($user_wilayah) {
                $query->where('rekomendasi_rehabilitasi_sosials.id_kabkot_resos', '=', $user_wilayah->kota_id)
                    ->where('rekomendasi_rehabilitasi_sosials.tujuan_resos', '=', $user_wilayah->role_id)
                    ->where('rekomendasi_rehabilitasi_sosials.petugas_resos', '=', $user_wilayah->model_id)
                    ->where(function ($query) {
                        $query->where('rekomendasi_rehabilitasi_sosials.status_aksi_resos', '=', 'Teruskan')
                            ->orWhere('rekomendasi_rehabilitasi_sosials.status_aksi_resos', '=', 'kembalikan');
                    });
            });
          
        }
        if ($user_wilayah->name == 'Back Ofiice Kota') {
            //  dd($user_wilayah->role_id);
            $query = DB::table('rekomendasi_rehabilitasi_sosials')
            ->join('users', 'users.id', '=', 'rekomendasi_rehabilitasi_sosials.petugas_resos')
            ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_rehabilitasi_sosials.id_kecamatan_resos')
            ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_rehabilitasi_sosials.id_kelurahan_resos')
            ->join('roles as r', 'r.id', '=', 'rekomendasi_rehabilitasi_sosials.tujuan_resos')
            ->select('rekomendasi_rehabilitasi_sosials.*', 'b.name_village', 'd.name_districts','users.name','r.name');
            $query->orWhere(function ($query) use ($user_wilayah) {
                $query->where('rekomendasi_rehabilitasi_sosials.id_kabkot_resos', '=', $user_wilayah->kota_id)
                    ->where('rekomendasi_rehabilitasi_sosials.tujuan_resos', '=', $user_wilayah->role_id)
                    ->where('rekomendasi_rehabilitasi_sosials.petugas_resos', '=', $user_wilayah->model_id)
                    ->where(function ($query) {
                        $query->where('rekomendasi_rehabilitasi_sosials.status_aksi_resos', '=', 'Teruskan')
                            ->orWhere('rekomendasi_rehabilitasi_sosials.status_aksi_resos', '=', 'kembalikan');
                    });
            });
        }
        if ($user_wilayah->name == 'kepala bidang') {

            $query = DB::table('rekomendasi_rehabilitasi_sosials')
            ->join('users', 'users.id', '=', 'rekomendasi_rehabilitasi_sosials.petugas_resos')
            ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_rehabilitasi_sosials.id_kecamatan_resos')
            ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_rehabilitasi_sosials.id_kelurahan_resos')
            ->join('roles as r', 'r.id', '=', 'rekomendasi_rehabilitasi_sosials.tujuan_resos')
            ->select('rekomendasi_rehabilitasi_sosials.*', 'b.name_village', 'd.name_districts','users.name','r.name');
            $query->orWhere(function ($query) use ($user_wilayah) {
                $query->where('rekomendasi_rehabilitasi_sosials.id_kabkot_resos', '=', $user_wilayah->kota_id)
                    ->where('rekomendasi_rehabilitasi_sosials.tujuan_resos', '=', $user_wilayah->role_id)
                    ->where('rekomendasi_rehabilitasi_sosials.petugas_resos', '=', $user_wilayah->model_id)
                    ->where(function ($query) {
                        $query->where('rekomendasi_rehabilitasi_sosials.status_aksi_resos', '=', 'Teruskan')
                            ->orWhere('rekomendasi_rehabilitasi_sosials.status_aksi_resos', '=', 'kembalikan');
                    });
            });
        }
        if ($user_wilayah->name == 'Front Office Kelurahan' || $user_wilayah->name == 'Back Ofiice kelurahan'|| $user_wilayah->name == 'fasilitator') {
            if ($request->has('search') && !empty($request->search['value'])) {
                $search = $request->search['value'];
                $query = DB::table('rekomendasi_rehabilitasi_sosials')
                    ->join('users', 'users.id', '=', 'rekomendasi_rehabilitasi_sosials.petugas_resos')
                    ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_rehabilitasi_sosials.id_kecamatan_resos')
                    ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_rehabilitasi_sosials.id_kelurahan_resos')
                    ->join('roles as r', 'r.id', '=', 'rekomendasi_rehabilitasi_sosials.tujuan_resos')
                    ->select('rekomendasi_rehabilitasi_sosials.*', 'b.name_village', 'd.name_districts','users.name','r.name');
                    $query->orWhere(function ($query) use ($user_wilayah,$search) {
                        $query->where('rekomendasi_rehabilitasi_sosials.id_kelurahan_resos', '=', $user_wilayah->kota_id)
                            ->where('rekomendasi_rehabilitasi_sosials.tujuan_resos', '=', $user_wilayah->role_id)
                            ->where('rekomendasi_rehabilitasi_sosials.petugas_resos', '=', $user_wilayah->model_id)
                            ->where(function ($query) {
                                $query->where('rekomendasi_rehabilitasi_sosials.status_aksi_resos', '=', 'Teruskan')
                                    ->orWhere('rekomendasi_rehabilitasi_sosials.status_aksi_resos', '=', 'kembalikan');
                            })
                        ->where('rekomendasi_rehabilitasi_sosials.no_pendaftaran_resos', 'like', "%$search%");

                    });
            }
        }elseif($user_wilayah->name == 'Front Office Kota' || $user_wilayah->name == 'Back Ofiice Kota'|| $user_wilayah->name == 'KepalaDinas'||$user_wilayah->name == 'SekertarisDinas'||$user_wilayah->name == 'Supervisor') {
            if ($request->has('search') && !empty($request->search['value'])) {
                $search = $request->search['value'];
                $query = DB::table('rekomendasi_rehabilitasi_sosials')
                ->join('users', 'users.id', '=', 'rekomendasi_rehabilitasi_sosials.petugas_resos')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_rehabilitasi_sosials.id_kecamatan_resos')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_rehabilitasi_sosials.id_kelurahan_resos')
                ->join('roles as r', 'r.id', '=', 'rekomendasi_rehabilitasi_sosials.tujuan_resos')
                ->select('rekomendasi_rehabilitasi_sosials.*', 'b.name_village', 'd.name_districts','users.name','r.name');
                $query->orWhere(function ($query) use ($user_wilayah,$search) {
                    $query->where('rekomendasi_rehabilitasi_sosials.id_kabkot_resos', '=', $user_wilayah->kota_id)
                        ->where('rekomendasi_rehabilitasi_sosials.tujuan_resos', '=', $user_wilayah->role_id)
                        ->where('rekomendasi_rehabilitasi_sosials.petugas_resos', '=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_rehabilitasi_sosials.status_aksi_resos', '=', 'Teruskan')
                                ->orWhere('rekomendasi_rehabilitasi_sosials.status_aksi_resos', '=', 'kembalikan');
                        })
                        ->where('rekomendasi_rehabilitasi_sosials.no_pendaftaran_resos', 'like', "%$search%");
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
   //Add paginate
        $start = $request->start;
        // dd($start);
        $length = $request->length;
        // dd($length);
        $query->offset($start)->limit($length);
        $data = $query->get();


        return response()->json([
            'draw' => $request->input('draw'),
            'recordsTotal' => rekomendasi_rehabilitasi_sosial::count(),
            'recordsFiltered' => $total_filtered_items,
            'data' => $data,
        ]);
    }
    public function teruskan(Request $request)
    {
        $user_name = Auth::user()->name;
        // dd($user_name);

        $query = DB::table('rekomendasi_rehabilitasi_sosials')
            ->join('users', 'users.id', '=', 'rekomendasi_rehabilitasi_sosials.petugas_resos')
            ->join('log_resos', 'log_resos.id_trx_resos', '=', 'rekomendasi_rehabilitasi_sosials.id')
            ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_rehabilitasi_sosials.id_kecamatan_resos')
            ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_rehabilitasi_sosials.id_kelurahan_resos')
            ->select('rekomendasi_rehabilitasi_sosials.*', 'b.name_village','d.name_districts');
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
        // dd($user_wilayah);
        //Front Office Kelurahan
        if ($user_wilayah->name == 'Front Office Kelurahan' || $user_wilayah->name == 'Back Ofiice kelurahan'|| $user_wilayah->name == 'fasilitator') {
            $query = DB::table('rekomendasi_rehabilitasi_sosials')
            ->leftjoin('indonesia_districts as d', 'd.code', '=', 'rekomendasi_rehabilitasi_sosials.id_kecamatan_resos')
            ->leftjoin('indonesia_villages as b', 'b.code', '=', 'rekomendasi_rehabilitasi_sosials.id_kelurahan_resos')
            // ->leftjoin('roles as r', 'r.id', '=', 'rekomendasi_rehabilitasi_sosials.petugas_resos')
            ->leftjoin('users', 'users.id', '=', 'rekomendasi_rehabilitasi_sosials.petugas_resos')
            ->select('rekomendasi_rehabilitasi_sosials.*', 'b.name_village', 'd.name_districts','users.name')
            // ->selectRaw('IFNULL(r.name,"") name')
            ->where('rekomendasi_rehabilitasi_sosials.id_kelurahan_resos', '=', $user_wilayah->kelurahan_id)
            ->whereIn('rekomendasi_rehabilitasi_sosials.status_aksi_resos', ['Teruskan','Kembalikan'])
            ->where('rekomendasi_rehabilitasi_sosials.petugas_resos', '<>', $user_id)
            ->whereRaw("(SELECT COUNT(l.id) FROM log_resos as l WHERE l.id_trx_resos = rekomendasi_rehabilitasi_sosials.id AND l.updated_by_resos = '".$user_id."') > 0 ");
            // dd($query);
        }elseif($user_wilayah->name == 'Front Office Kota' || $user_wilayah->name == 'Back Ofiice Kota'|| $user_wilayah->name == 'KepalaDinas'||$user_wilayah->name == 'SekertarisDinas') {
            $query = DB::table('rekomendasi_rehabilitasi_sosials')
            ->leftjoin('indonesia_districts as d', 'd.code', '=', 'rekomendasi_rehabilitasi_sosials.id_kecamatan_resos')
            ->leftjoin('indonesia_villages as b', 'b.code', '=', 'rekomendasi_rehabilitasi_sosials.id_kelurahan_resos')
            // ->leftjoin('roles as r', 'r.id', '=', 'rekomendasi_rehabilitasi_sosials.petugas_resos')
            ->leftjoin('users', 'users.id', '=', 'rekomendasi_rehabilitasi_sosials.petugas_resos')
            ->select('rekomendasi_rehabilitasi_sosials.*', 'b.name_village', 'd.name_districts','users.name')
            // ->selectRaw('IFNULL(r.name,"") name')
            ->where('rekomendasi_rehabilitasi_sosials.id_kabkot_resos', '=', $user_wilayah->kota_id)
            ->whereIn('rekomendasi_rehabilitasi_sosials.status_aksi_resos', ['Teruskan','Kembalikan'])
            ->where('rekomendasi_rehabilitasi_sosials.petugas_resos', '<>', $user_id)
            ->whereRaw("(SELECT COUNT(l.id) FROM log_resos as l WHERE l.id_trx_resos = rekomendasi_rehabilitasi_sosials.id AND l.created_by_resos = '".$user_id."') > 0 ");
            // dd($query);
        }else{
            $query = DB::table('rekomendasi_rehabilitasi_sosials')
            ->leftjoin('indonesia_districts as d', 'd.code', '=', 'rekomendasi_rehabilitasi_sosials.id_kecamatan_resos')
            ->leftjoin('indonesia_villages as b', 'b.code', '=', 'rekomendasi_rehabilitasi_sosials.id_kelurahan_resos')
            // ->leftjoin('roles as r', 'r.id', '=', 'rekomendasi_rehabilitasi_sosials.petugas_resos')
            ->leftjoin('users', 'users.id', '=', 'rekomendasi_rehabilitasi_sosials.petugas_resos')
            ->select('rekomendasi_rehabilitasi_sosials.*', 'b.name_village', 'd.name_districts','users.name')
            // ->selectRaw('IFNULL(r.name,"") name')
            ->where('rekomendasi_rehabilitasi_sosials.id_kecamatan_resos', '=', $user_wilayah->kecamatan_id)
            ->whereIn('rekomendasi_rehabilitasi_sosials.status_aksi_resos', ['Teruskan','Kembalikan'])
            ->where('rekomendasi_rehabilitasi_sosials.petugas_resos', '<>', $user_id)
            ->whereRaw("(SELECT COUNT(l.id) FROM log_resos as l WHERE l.id_trx_resos = rekomendasi_rehabilitasi_sosials.id AND l.created_by_resos = '".$user_id."') > 0 ");
            // dd($query);
        }
        if ($user_wilayah->name == 'Front Office Kelurahan' || $user_wilayah->name == 'Back Ofiice kelurahan'|| $user_wilayah->name == 'fasilitator') {
            if ($request->has('search') && !empty($request->search['value'])) {
                $search = $request->search['value'];
                $query = DB::table('rekomendasi_rehabilitasi_sosials')
                ->leftjoin('indonesia_districts as d', 'd.code', '=', 'rekomendasi_rehabilitasi_sosials.id_kecamatan_resos')
                ->leftjoin('indonesia_villages as b', 'b.code', '=', 'rekomendasi_rehabilitasi_sosials.id_kelurahan_resos')
                // ->leftjoin('roles as r', 'r.id', '=', 'rekomendasi_rehabilitasi_sosials.petugas_resos')
                ->leftjoin('users', 'users.id', '=', 'rekomendasi_rehabilitasi_sosials.petugas_resos')
                ->select('rekomendasi_rehabilitasi_sosials.*', 'b.name_village', 'd.name_districts','users.name')
                // ->selectRaw('IFNULL(r.name,"") name')
                ->where('rekomendasi_rehabilitasi_sosials.id_kelurahan_resos', '=', $user_wilayah->kelurahan_id)
                ->whereIn('rekomendasi_rehabilitasi_sosials.status_aksi_resos', ['Teruskan','Kembalikan'])
                ->where('rekomendasi_rehabilitasi_sosials.petugas_resos', '<>', $user_id)
                ->whereRaw("(SELECT COUNT(l.id) FROM log_resos as l WHERE l.id_trx_resos = rekomendasi_rehabilitasi_sosials.id AND l.created_by_resos = '".$user_id."') > 0 ")
                ->where('rekomendasi_rehabilitasi_sosials.no_pendaftaran_resos', 'like', "%$search%");
            }
        }elseif($user_wilayah->name == 'Front Office Kota' || $user_wilayah->name == 'Back Ofiice Kota'|| $user_wilayah->name == 'KepalaDinas'||$user_wilayah->name == 'SekertarisDinas') {
            if ($request->has('search') && !empty($request->search['value'])) {
                $search = $request->search['value'];
                $query = DB::table('rekomendasi_rehabilitasi_sosials')
                ->leftjoin('indonesia_districts as d', 'd.code', '=', 'rekomendasi_rehabilitasi_sosials.id_kecamatan_resos')
                ->leftjoin('indonesia_villages as b', 'b.code', '=', 'rekomendasi_rehabilitasi_sosials.id_kelurahan_resos')
                // ->leftjoin('roles as r', 'r.id', '=', 'rekomendasi_rehabilitasi_sosials.petugas_resos')
                ->leftjoin('users', 'users.id', '=', 'rekomendasi_rehabilitasi_sosials.petugas_resos')
                ->select('rekomendasi_rehabilitasi_sosials.*', 'b.name_village', 'd.name_districts','users.name')
                // ->selectRaw('IFNULL(r.name,"") name')
                ->where('rekomendasi_rehabilitasi_sosials.id_kabkota_resos', '=', $user_wilayah->kota_id)
                ->whereIn('rekomendasi_rehabilitasi_sosials.status_aksi_resos', ['Teruskan','Kembalikan'])
                ->where('rekomendasi_rehabilitasi_sosials.petugas_resos', '<>', $user_id)
                ->whereRaw("(SELECT COUNT(l.id) FROM log_resos as l WHERE l.id_trx_resos = rekomendasi_rehabilitasi_sosials.id AND l.created_by_resos = '".$user_id."') > 0 ")
                ->where('rekomendasi_rehabilitasi_sosials.no_pendaftaran_resos', 'like', "%$search%");
            }
        }else{
            if ($request->has('search') && !empty($request->search['value'])) {
                $search = $request->search['value'];
                $query = DB::table('rekomendasi_rehabilitasi_sosials')
                ->leftjoin('indonesia_districts as d', 'd.code', '=', 'rekomendasi_rehabilitasi_sosials.id_kecamatan_resos')
                ->leftjoin('indonesia_villages as b', 'b.code', '=', 'rekomendasi_rehabilitasi_sosials.id_kelurahan_resos')
                // ->leftjoin('roles as r', 'r.id', '=', 'rekomendasi_rehabilitasi_sosials.petugas_resos')
                ->leftjoin('users', 'users.id', '=', 'rekomendasi_rehabilitasi_sosials.petugas_resos')
                ->select('rekomendasi_rehabilitasi_sosials.*', 'b.name_village', 'd.name_districts','users.name')
                // ->selectRaw('IFNULL(r.name,"") name')
                ->where('rekomendasi_rehabilitasi_sosials.id_kecamatan_resos', '=', $user_wilayah->kecamatan_id)
                ->whereIn('rekomendasi_rehabilitasi_sosials.status_aksi_resos', ['Teruskan','Kembalikan'])
                ->where('rekomendasi_rehabilitasi_sosials.petugas_resos', '<>', $user_id)
                ->whereRaw("(SELECT COUNT(l.id) FROM log_resos as l WHERE l.id_trx_resos = rekomendasi_rehabilitasi_sosials.id AND l.created_by_resos = '".$user_id."') > 0 ")
                ->where('rekomendasi_rehabilitasi_sosials.no_pendaftaran_resos', 'like', "%$search%");
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
   //Add paginate
        $start = $request->start;
        // dd($start);
        $length = $request->length;
        // dd($length);
        $query->offset($start)->limit($length);
        $data = $query->get();

        return response()->json([
            'draw' => $request->input('draw'),
            'recordsTotal' => rekomendasi_rehabilitasi_sosial::count(),
            'recordsFiltered' => $total_filtered_items,
            'data' => $data,
        ]);
    }
    public function selesai(Request $request)
    {
        $user_name = Auth::user()->name;
        $query = DB::table('rekomendasi_rehabilitasi_sosials')
            ->join('users', 'users.id', '=', 'rekomendasi_rehabilitasi_sosials.petugas_resos')
            ->join('log_resos', 'log_resos.id_trx_resos', '=', 'rekomendasi_rehabilitasi_sosials.id')
            ->join('model_has_roles', 'model_has_roles.model_id', '=', 'rekomendasi_rehabilitasi_sosials.tujuan_resos')
            ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_rehabilitasi_sosials.id_kelurahan_resos')
            ->select('rekomendasi_rehabilitasi_sosials.*', 'b.name_village');
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

        if ($user_wilayah->name == 'Front Office Kelurahan') {
            //  dd($user_wilayah->role_id);
            $query = DB::table('rekomendasi_rehabilitasi_sosials')
            ->join('users', 'users.id', '=', 'rekomendasi_rehabilitasi_sosials.petugas_resos')
            // ->join('log_resos', 'log_resos.id_trx_resos', '=', 'rekomendasi_rehabilitasi_sosials.id')
            ->join('indonesia_villages', 'indonesia_villages.code', '=', 'rekomendasi_rehabilitasi_sosials.id_kelurahan_resos')
            ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_rehabilitasi_sosials.id_kecamatan_resos')
            ->select('rekomendasi_rehabilitasi_sosials.*', 'd.name_districts', 'indonesia_villages.name_village','d.name_districts','users.name')
            ->Where(function ($query) use ($user_wilayah) {
                $query->where('rekomendasi_rehabilitasi_sosials.id_kelurahan_resos', $user_wilayah->kelurahan_id)
                    // ->where('rekomendasi_rehabilitasi_sosials.tujuan_resos', '=', $user_wilayah->role_id)
                    // ->where('log_resos.created_by_resos', '!=', $user_wilayah->model_id)
                    ->where(function ($query) {
                        $query->where('rekomendasi_rehabilitasi_sosials.status_aksi_resos', '=', 'Tolak')
                            ->orWhere('rekomendasi_rehabilitasi_sosials.status_aksi_resos', '=', 'Selesai');
                    });
            })->distinct();
        } elseif ($user_wilayah->name == 'Back Ofiice kelurahan') {
            //  dd($user_wilayah->role_id);
            $query = DB::table('rekomendasi_rehabilitasi_sosials')
                ->join('users', 'users.id', '=', 'rekomendasi_rehabilitasi_sosials.petugas_resos')
                // ->join('log_resos', 'log_resos.id_trx_resos', '=', 'rekomendasi_rehabilitasi_sosials.id')
                ->join('indonesia_villages', 'indonesia_villages.code', '=', 'rekomendasi_rehabilitasi_sosials.id_kelurahan_resos')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_rehabilitasi_sosials.id_kecamatan_resos')
                ->select('rekomendasi_rehabilitasi_sosials.*', 'd.name_districts', 'indonesia_villages.name_village','d.name_districts','users.name')
                ->Where(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_rehabilitasi_sosials.id_kelurahan_resos', $user_wilayah->kelurahan_id)
                        // ->where('rekomendasi_rehabilitasi_sosials.tujuan_resos', '=', $user_wilayah->role_id)
                        // ->where('log_resos.created_by_resos', '!=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_rehabilitasi_sosials.status_aksi_resos', '=', 'Tolak')
                                ->orWhere('rekomendasi_rehabilitasi_sosials.status_aksi_resos', '=', 'Selesai');
                        });
                })->distinct();
        }elseif ($user_wilayah->name == 'Front Office kota') {
            //  dd($user_wilayah->role_id);
            $query = DB::table('rekomendasi_rehabilitasi_sosials')
            ->join('users', 'users.id', '=', 'rekomendasi_rehabilitasi_sosials.petugas_resos')
            // ->join('log_resos', 'log_resos.id_trx_resos', '=', 'rekomendasi_rehabilitasi_sosials.id')
            ->join('indonesia_villages', 'indonesia_villages.code', '=', 'rekomendasi_rehabilitasi_sosials.id_kelurahan_resos')
            ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_rehabilitasi_sosials.id_kecamatan_resos')
            ->select('rekomendasi_rehabilitasi_sosials.*', 'd.name_districts', 'indonesia_villages.name_village','d.name_districts','users.name')
            ->Where(function ($query) use ($user_wilayah) {
                $query->where('rekomendasi_rehabilitasi_sosials.id_kabkot_resos', $user_wilayah->kota_id)
                    // ->where('rekomendasi_rehabilitasi_sosials.tujuan_resos', '=', $user_wilayah->role_id)
                    // ->where('log_resos.created_by_resos', '!=', $user_wilayah->model_id)
                    ->where(function ($query) {
                        $query->where('rekomendasi_rehabilitasi_sosials.status_aksi_resos', '=', 'Tolak')
                            ->orWhere('rekomendasi_rehabilitasi_sosials.status_aksi_resos', '=', 'Selesai');
                    });
            })->distinct();
        } elseif ($user_wilayah->name == 'Back Ofiice Kota') {
            // dd($user_wilayah);
            $query = DB::table('rekomendasi_rehabilitasi_sosials')
            ->join('users', 'users.id', '=', 'rekomendasi_rehabilitasi_sosials.petugas_resos')
            // ->join('log_resos', 'log_resos.id_trx_resos', '=', 'rekomendasi_rehabilitasi_sosials.id')
            ->join('indonesia_villages', 'indonesia_villages.code', '=', 'rekomendasi_rehabilitasi_sosials.id_kelurahan_resos')
            ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_rehabilitasi_sosials.id_kecamatan_resos')
            ->select('rekomendasi_rehabilitasi_sosials.*', 'd.name_districts', 'indonesia_villages.name_village','d.name_districts','users.name')
            ->Where(function ($query) use ($user_wilayah) {
                $query->where('rekomendasi_rehabilitasi_sosials.id_kabkot_resos', $user_wilayah->kota_id)
                    // ->where('rekomendasi_rehabilitasi_sosials.tujuan_resos', '=', $user_wilayah->role_id)
                    // ->where('log_resos.created_by_resos', '!=', $user_wilayah->model_id)
                    ->where(function ($query) {
                        $query->where('rekomendasi_rehabilitasi_sosials.status_aksi_resos', '=', 'Tolak')
                            ->orWhere('rekomendasi_rehabilitasi_sosials.status_aksi_resos', '=', 'Selesai');
                    });
            })->distinct();
            // dd($query); 
        }elseif ($user_wilayah->name == 'supervisor') {
            // dd($user_wilayah);
            $query = DB::table('rekomendasi_rehabilitasi_sosials')
            ->join('users', 'users.id', '=', 'rekomendasi_rehabilitasi_sosials.petugas_resos')
            // ->join('log_resos', 'log_resos.id_trx_resos', '=', 'rekomendasi_rehabilitasi_sosials.id')
            ->join('indonesia_villages', 'indonesia_villages.code', '=', 'rekomendasi_rehabilitasi_sosials.id_kelurahan_resos')
            ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_rehabilitasi_sosials.id_kecamatan_resos')
            ->select('rekomendasi_rehabilitasi_sosials.*', 'd.name_districts', 'indonesia_villages.name_village','d.name_districts','users.name')
            ->Where(function ($query) use ($user_wilayah) {
                $query->where('rekomendasi_rehabilitasi_sosials.id_kecamatan_resos', $user_wilayah->kecamatan_id)
                    // ->where('rekomendasi_rehabilitasi_sosials.tujuan_resos', '=', $user_wilayah->role_id)
                    // ->where('log_resos.created_by_resos', '!=', $user_wilayah->model_id)
                    ->where(function ($query) {
                        $query->where('rekomendasi_rehabilitasi_sosials.status_aksi_resos', '=', 'Tolak')
                            ->orWhere('rekomendasi_rehabilitasi_sosials.status_aksi_resos', '=', 'Selesai');
                    });
            })->distinct();
            // dd($query); 
        } elseif ($user_wilayah->name == 'kepala bidang') {
            // dd($user_wilayah);
            $query = DB::table('rekomendasi_rehabilitasi_sosials')
            ->join('users', 'users.id', '=', 'rekomendasi_rehabilitasi_sosials.petugas_resos')
            // ->join('log_resos', 'log_resos.id_trx_resos', '=', 'rekomendasi_rehabilitasi_sosials.id')
            ->join('indonesia_villages', 'indonesia_villages.code', '=', 'rekomendasi_rehabilitasi_sosials.id_kelurahan_resos')
            ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_rehabilitasi_sosials.id_kecamatan_resos')
            ->select('rekomendasi_rehabilitasi_sosials.*', 'd.name_districts', 'indonesia_villages.name_village','d.name_districts','users.name')
            ->Where(function ($query) use ($user_wilayah) {
                $query->where('rekomendasi_rehabilitasi_sosials.id_kabkot_resos', $user_wilayah->kota_id)
                    // ->where('rekomendasi_rehabilitasi_sosials.tujuan_resos', '=', $user_wilayah->role_id)
                    // ->where('log_resos.created_by_resos', '!=', $user_wilayah->model_id)
                    ->where(function ($query) {
                        $query->where('rekomendasi_rehabilitasi_sosials.status_aksi_resos', '=', 'Tolak')
                            ->orWhere('rekomendasi_rehabilitasi_sosials.status_aksi_resos', '=', 'Selesai');
                    });
            })->distinct();
        } elseif ($user_wilayah->name == 'SekertarisDinas') {
            // dd($user_wilayah->role_id);
            $query = DB::table('rekomendasi_rehabilitasi_sosials')
                ->join('users', 'users.id', '=', 'rekomendasi_rehabilitasi_sosials.petugas_resos')
                ->join('log_resos', 'log_resos.id_trx_resos', '=', 'rekomendasi_rehabilitasi_sosials.id')
                // ->join('model_has_roles', 'model_has_roles.model_id', '=', 'rekomendasi_rehabilitasi_sosials.tujuan_resos')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_rehabilitasi_sosials.id_kecamatan_resos')
                ->select('rekomendasi_rehabilitasi_sosials.*', 'd.name_districts', 'indonesia_villages.name_village', 'log_resos.tujuan_resos', 'log_resos.petugas_resos','users.name')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_rehabilitasi_sosials.id_kelurahan_resos', $user_wilayah->kelurahan_id)
                        ->where('log_resos.tujuan_resos', '!=', $user_wilayah->role_id)
                        ->where('log_resos.created_by_resos', '=', auth::user()->id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_rehabilitasi_sosials.status_aksi_resos', '=', 'Tolak')
                                ->orWhere('rekomendasi_rehabilitasi_sosials.status_aksi_resos', '=', 'Selesai');
                        });
                })->distinct();
        } elseif ($user_wilayah->name == 'KepalaDinas') {
            // dd($user_wilayah);
            $query = DB::table('rekomendasi_rehabilitasi_sosials')
                ->join('users', 'users.id', '=', 'rekomendasi_rehabilitasi_sosials.petugas_resos')
                ->join('log_resos', 'log_resos.id_trx_resos', '=', 'rekomendasi_rehabilitasi_sosials.id')
                // ->join('model_has_roles', 'model_has_roles.model_id', '=', 'rekomendasi_rehabilitasi_sosials.tujuan_resos')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_rehabilitasi_sosials.id_kecamatan_resos')
                ->select('rekomendasi_rehabilitasi_sosials.*', 'd.name_districts', 'indonesia_villages.name_village', 'log_resos.tujuan_resos', 'log_resos.petugas_resos','users.name')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_rehabilitasi_sosials.id_kelurahan_resos', $user_wilayah->kelurahan_id)
                        ->where('log_resos.tujuan_resos', '=', $user_wilayah->role_id)
                        ->where('log_resos.petugas_resos', '=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_rehabilitasi_sosials.status_aksi_resos', '=', 'Tolak')
                                ->orWhere('rekomendasi_rehabilitasi_sosials.status_aksi_resos', '=', 'Selesai');
                        });
                });
        }

        if ($user_wilayah->name == 'Front Office Kelurahan' || $user_wilayah->name == 'Back Ofiice kelurahan'|| $user_wilayah->name == 'fasilitator') {
            if ($request->has('search') && !empty($request->search['value'])) {
                $search = $request->search['value'];
                $query = DB::table('rekomendasi_rehabilitasi_sosials')
                ->join('users', 'users.id', '=', 'rekomendasi_rehabilitasi_sosials.petugas_resos')
                // ->join('log_resos', 'log_resos.id_trx_resos', '=', 'rekomendasi_rehabilitasi_sosials.id')
                ->join('indonesia_villages', 'indonesia_villages.code', '=', 'rekomendasi_rehabilitasi_sosials.id_kelurahan_resos')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_rehabilitasi_sosials.id_kecamatan_resos')
                ->select('rekomendasi_rehabilitasi_sosials.*', 'd.name_districts', 'indonesia_villages.name_village','d.name_districts','users.name')
                ->Where(function ($query) use ($user_wilayah,$search) {
                    $query->where('rekomendasi_rehabilitasi_sosials.id_kelurahan_resos', $user_wilayah->kelurahan_id)
                        // ->where('rekomendasi_rehabilitasi_sosials.tujuan_resos', '=', $user_wilayah->role_id)
                        // ->where('log_resos.created_by_resos', '!=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_rehabilitasi_sosials.status_aksi_resos', '=', 'Tolak')
                                ->orWhere('rekomendasi_rehabilitasi_sosials.status_aksi_resos', '=', 'Selesai');
                        })
                        ->where('rekomendasi_rehabilitasi_sosials.no_pendaftaran_resos', 'like', "%$search%");
                })->distinct();
            }
        }elseif($user_wilayah->name == 'Front Office Kota' || $user_wilayah->name == 'Back Ofiice Kota'|| $user_wilayah->name == 'KepalaDinas'||$user_wilayah->name == 'SekertarisDinas'||$user_wilayah->name == 'Supervisor') {
            if ($request->has('search') && !empty($request->search['value'])) {
                $search = $request->search['value'];
                $query = DB::table('rekomendasi_rehabilitasi_sosials')
                ->join('users', 'users.id', '=', 'rekomendasi_rehabilitasi_sosials.petugas_resos')
                // ->join('log_resos', 'log_resos.id_trx_resos', '=', 'rekomendasi_rehabilitasi_sosials.id')
                ->join('indonesia_villages', 'indonesia_villages.code', '=', 'rekomendasi_rehabilitasi_sosials.id_kelurahan_resos')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_rehabilitasi_sosials.id_kecamatan_resos')
                ->select('rekomendasi_rehabilitasi_sosials.*', 'd.name_districts', 'indonesia_villages.name_village','d.name_districts','users.name')
                ->Where(function ($query) use ($user_wilayah,$search) {
                    $query->where('rekomendasi_rehabilitasi_sosials.id_kabkot_resos', $user_wilayah->kota_id)
                        // ->where('rekomendasi_rehabilitasi_sosials.tujuan_resos', '=', $user_wilayah->role_id)
                        // ->where('log_resos.created_by_resos', '!=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_rehabilitasi_sosials.status_aksi_resos', '=', 'Tolak')
                                ->orWhere('rekomendasi_rehabilitasi_sosials.status_aksi_resos', '=', 'Selesai');
                        })
                        ->where('rekomendasi_rehabilitasi_sosials.no_pendaftaran_resos', 'like', "%$search%");
                })->distinct();
            }
        }

        // Get total count of filtered items
        $total_filtered_items = $query->count();
       
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
            'recordsTotal' => rekomendasi_rehabilitasi_sosial::count(),
            'recordsFiltered' => $total_filtered_items,
            'data' => $data,
        ]);
    }
    public function getPetugaRehabilitasiSosial($id)
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
                ->select('u.id', 'u.name', 'u.email', 'r.name as role')
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
            ->select('u.id', 'u.name', 'u.email', 'r.name as role')
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
                    ->leftJoin('rekomendasi_rehabilitasi_sosials','rekomendasi_rehabilitasi_sosials.createdby_resos','=','u.id')
                    ->where('mhr.model_type', '=', 'App\Models\User')
                    ->where('wilayahs.kota_id', '=',$wilayah->kota_id)
                    ->where('mhr.role_id', '=', $id)
                    // ->whereRaw("(SELECT COUNT(l.id) FROM users as l WHERE l.id = '".$id."') > 0 ")
                    // ->whereRaw("(SELECT COUNT(l.id) FROM users as l WHERE l.id = rekomendasi_rehabilitasi_sosials.id AND l.id = '".$id."') > 0 ")
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
                ->where('wilayahs.kelurahan_id', '=',$wilayah->kelurahan_id)
                ->where('mhr.role_id', '=', $id)
                ->get(); 
                return response()->json($users);
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
