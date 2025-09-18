<?php

namespace App\Http\Controllers;

use App\Http\Requests\Createrekomendasi_rekativasi_pbi_jkRequest;
use App\Http\Requests\Updaterekomendasi_rekativasi_pbi_jkRequest;
use App\Http\Controllers\AppBaseController;
use App\Models\log_pbijk;
use App\Models\logYayasan;
use App\Models\pbijk;
use App\Models\pelapor;
use App\Models\Prelist;
use App\Models\rekomendasi_rekativasi_pbi_jk;
use App\Models\Roles;
use App\Repositories\rekomendasi_rekativasi_pbi_jkRepository;
use Illuminate\Http\Request;
use Flash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

use function PHPUnit\Framework\isNull;

class rekomendasi_rekativasi_pbi_jkController extends AppBaseController
{
    /** @var rekomendasi_rekativasi_pbi_jkRepository $rekomendasiRekativasiPbiJkRepository*/
    private $rekomendasiRekativasiPbiJkRepository;

    public function __construct(rekomendasi_rekativasi_pbi_jkRepository $rekomendasiRekativasiPbiJkRepo)
    {
        $this->rekomendasiRekativasiPbiJkRepository = $rekomendasiRekativasiPbiJkRepo;
    }

    /**
     * Display a listing of the rekomendasi_rekativasi_pbi_jk.
     */
    public function index(Request $request)
    {
        $rekomendasiRekativasiPbiJks = $this->rekomendasiRekativasiPbiJkRepository->paginate(10);

        return view('rekomendasi_rekativasi_pbi_jks.index')
            ->with('rekomendasiRekativasiPbiJks', $rekomendasiRekativasiPbiJks);
    }
    public function FileReaktivasipbijkn($id)
    {
        $ReaktivasiPbijkn = rekomendasi_rekativasi_pbi_jk::find($id);
        // dd($ReaktivasiPbijkn);
        $getIdDtks = DB::table('rekomendasi_rekativasi_pbi_jks as w')->select(
            'w.*',
            'dtks.Id_DTKS'
        )
            ->leftjoin('dtks', 'dtks.Nik', '=', 'w.nik_pbijk')
            // ->where('status_wilayah', '1')
            ->where('dtks.Nik', $ReaktivasiPbijkn->nik_pbijk)->first();
        // dd($getIdDtks);
        
        if (!is_null($getIdDtks) && !is_null($getIdDtks->Id_DTKS)) {
            $data_dtks = $getIdDtks->Id_DTKS;
        } else {
            $data_dtks = '-';
        }

       $date = Carbon::parse($ReaktivasiPbijkn->tgl_lahir_pbijk)->locale('id');

       $date->settings(['formatFunction' => 'translatedFormat']);

       $tanggal = $date->format('j F Y ');


       // dd($tanggal);
        $pdf = PDF::loadHtml(view('rekomendasi_rekativasi_pbi_jks.file_permohonan',compact('ReaktivasiPbijkn','tanggal','data_dtks')));
        $filename = 'File Permohonan' . $ReaktivasiPbijkn->nama . '.pdf';
        return $pdf->stream($filename);
    }
    /**
     * Show the form for creating a new rekomendasi_rekativasi_pbi_jk.
     */
    public function create()
    {
        $v = rekomendasi_rekativasi_pbi_jk::latest()->first();
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
        } else if ($roles->contains('Back Ofiice Kota') || $roles->contains('Front Office kota')) {
            // Jika user memiliki role 'BO-Kota' atau 'SekertarisDinas', maka tampilkan alur dengan nama 'Kembalikan', 'Tolak', dan 'Teruskan'
            $alur = DB::table('alur')
                ->whereIn('name', ['Kembalikan', 'Tolak', 'Teruskan'])
                ->get();
        } else if ($roles->contains('kepala bidang')) {
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
                ->where('name', 'Back Ofiice kelurahan')
                ->orwhere('name','supervisor')
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
        return view('rekomendasi_rekativasi_pbi_jks.create', compact('checkuserrole','kecamatans', 'wilayah', 'roleid', 'checkroles', 'alur'));
        // return view('rekomendasi_pbijk.create');
    }

    /**
     * Store a newly created rekomendasi_rekativasi_pbi_jk in storage.
     */

     public function cekIdPBI(Request $request, $Nik)
     {
         $found = false;
         $table2 = DB::table('dtks')->where('nik_pbijk', $Nik)->first(); 
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
        if ($request->get('status_alur_pbijk') != 'Draft') {
            
            if ($request->get('status_dtks_pbijk') == 'Terdaftar') {
           

                // jika status_dtks_pbijk sama dengan terdaftar akan nmasuk kondisi sini
                $data = new rekomendasi_rekativasi_pbi_jk();
                $files = [
                    'file_ktp_terlapor_pbijk' => 'rekativasi/ktp/',
                    'file_kk_terlapor_pbijk' => 'rekativasi/kk/',
                    'file_keterangan_dtks_pbijk' => 'rekativasi/strukturorganisasi/',
                    'file_pendukung_pbijk' => 'rekativasi/wajibpajak/'
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
                

                // $data->id_alur = $request->get('id_alur_pbijk');
                $data->no_pendaftaran_pbijk = mt_rand(100, 1000);
                $data->id_provinsi_pbijk = $request->get('id_provinsi_pbijk');
                $data->id_kabkot_pbijk = $request->get('id_kabkot_pbijk');
                $data->id_kecamatan_pbijk = $request->get('id_kecamatan_pbijk');
                $data->id_kelurahan_pbijk = $request->get('id_kelurahan_pbijk');
                $data->jenis_pelapor_pbijk = $request->get('jenis_pelapor_pbijk');
                $data->ada_nik_pbijk = $request->get('ada_nik_pbijk');
                $data->nik_pbijk = $request->get('nik_pbijk');
                $data->no_kk_pbijk = $request->get('no_kk_pbijk');
                // $data->no_kis = $request->get('no_kis');
                $data->nama_pbijk = $request->get('nama_pbijk');
                $data->tgl_lahir_pbijk = $request->get('tgl_lahir_pbijk');
                $data->tempat_lahir_pbijk = $request->get('tempat_lahir_pbijk');
                $data->jenis_kelamin_pbijk = $request->get('jenis_kelamin_pbijk');
                $data->telp_pbijk = $request->get('telp_pbijk');
                $data->alamat_pbijk = $request->get('alamat_pbijk');
                $data['catatan_pbijk']  = $request->get('catatan_pbijk');
                $data->status_dtks_pbijk = $request->get('status_dtks_pbijk');
                $data->tujuan_pbijk = $request->get('tujuan_pbijk');
                $data->status_aksi_pbijk = $request->get('status_aksi_pbijk');
                $data->petugas_pbijk = $request->get('petugas_pbijk');
                $data->createdby_pbijk = Auth::user()->id;
                $data->updatedby_pbijk = Auth::user()->id;
                // dd($data);
                $data->save();
                // dd($data->tujuan_pbijk);
                $logpengaduan = new log_pbijk();
                $logpengaduan['id_trx_pbijk'] = $data->id;
                $logpengaduan['id_alur_pbijk'] = $request->get('status_aksi_pbijk');
                // $logpengaduan->tujuan_pbijk = $request->get('tujuan_pbijk');
                $logpengaduan['petugas_pbijk'] = $request->get('petugas_pbijk');
                $logpengaduan['catatan_pbijk']  = $request->get('catatan_pbijk');
                $logpengaduan['file_pendukung_pbijk'] = $request->get('file_pendukung');
                $logpengaduan['tujuan_pbijk'] = $request->get('tujuan_pbijk');
                $logpengaduan['created_by_pbijk'] = Auth::user()->id;
                $logpengaduan['updated_by_pbijk'] = Auth::user()->id;
                // dd($logpengaduan);
                // dd($request->get('tujuan_pbijk'));
                $logpengaduan->save();
             
                if($request->get('jenis_pelapor_pbijk') == 'Orang Lain'){
                    // dd($data);
                $pelapor = new pelapor();
                $pelapor['id_menu'] = '04';
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
                dd($pelapor);
                $pelapor->save();
                }else{
                    $pelapor = new pelapor();
                    $pelapor['id_menu'] = '04';
                    $pelapor['id_form'] = $data->id;
                    $pelapor['jenis_peelaporan'] = $request->get('jenis_pelapor_pbijk');
                    $pelapor['nama_pelapor']  =  $request->get('nama_pbijk');
                    $pelapor['ada_nik_pelapor'] = $request->get('ada_nik_pbijk');
                    $pelapor['nik_pelapor'] = $request->get('nik_pbijk');
                    $pelapor['status_dtks_pelapor'] = $request->get('status_dtks_pbijk');
                    $pelapor['tempat_lahir_pelapor'] = $request->get('tempat_lahir_pbijk');
                    $pelapor['tanggal_lahir_pelapor'] = $request->get('tgl_lahir_pbijk');
                    $pelapor['jenis_kelamin'] = $request->get('jenis_kelamin_pbijk');
                    $pelapor['nama_pelapor']  = $request->get('nama_pbijk');
                    $pelapor['telepon_pelapor'] = $request->get('telp_pbijk');
                    $pelapor['alamat_pelapor'] = $request->get('alamat_pbijk');
                    $pelapor['createdby_pelapor'] = Auth::user()->id;
                    $pelapor['updatedby_pelapor'] = Auth::user()->id;

                    $pelapor->save();
                }
                
                return redirect('rekomendasi_rekativasi_pbi_jks')->withSuccess('Data Rekomendasi Berhasil Ditambahkan');
            } else {
                // jika status_dtks_pbijk sama dengan terdaftar akan nmasuk kondisi sini
                  
                $cek = Prelist::where('nik', '=', $request->get('nik_pbijk'))->exists();
                if (!$cek) {
                    // jika nik tidak ada nik akan masuk ke sini
                    $data = new Prelist;
                    $data['id_provinsi'] = $request->get('id_provinsi_pbijk');
                    $data['id_kabkot'] = $request->get('id_kabkot_pbijk');
                    $data['id_kecamatan'] = $request->get('id_kecamatan_pbijk');
                    $data['id_kelurahan'] = $request->get('id_kelurahan_pbijk');
                    $data['nik'] = $request->get('nik_pbijk');
                    $data['no_kk'] = $request->get('no_kk_pbijk');
                    // $data['no_kis'] = $request->get('no_kis_pbijk');
                    $data['nama'] = $request->get('nama_pbijk');
                    $data['tgl_lahir'] = $request->get('tgl_lahir_pbijk');
                    // $data['alamat'] = $request->get('alamat_pbijk');
                    $data['telp'] = $request->get('telpon_pbijk');
                    // $data['email'] = $request->get('email_pbijk');
                    $data['status_data'] = 'prelistdtks';

                    $data->save();
                    $data = new rekomendasi_rekativasi_pbi_jk();
                    $files = [
                        'file_ktp_terlapor_pbijk' => 'rekativasi/ktp/',
                        'file_kk_terlapor_pbijk' => 'rekativasi/kk/',
                        'file_keterangan_dtks_pbijk' => 'rekativasi/strukturorganisasi/',
                        'file_pendukung_pbijk' => 'rekativasi/wajibpajak/'
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
                    
                    // $data->id_alur = $request->get('id_alur_pbijk');
                    $data->no_pendaftaran_pbijk = mt_rand(100, 1000);
                    $data->id_provinsi_pbijk = $request->get('id_provinsi_pbijk');
                    $data->id_kabkot_pbijk = $request->get('id_kabkot_pbijk');
                    $data->id_kecamatan_pbijk = $request->get('id_kecamatan_pbijk');
                    $data->id_kelurahan_pbijk = $request->get('id_kelurahan_pbijk');
                    $data->jenis_pelapor_pbijk = $request->get('jenis_pelapor_pbijk');
                    $data->ada_nik_pbijk = $request->get('ada_nik_pbijk');
                    $data->nik_pbijk = $request->get('nik_pbijk');
                    $data->no_kk_pbijk = $request->get('no_kk_pbijk');
                    // $data->no_kis = $request->get('no_kis');
                    $data->nama_pbijk = $request->get('nama_pbijk');
                    $data->tgl_lahir_pbijk = $request->get('tgl_lahir_pbijk');
                    $data->tempat_lahir_pbijk = $request->get('tempat_lahir_pbijk');
                    $data->jenis_kelamin_pbijk = $request->get('jenis_kelamin_pbijk');
                    $data->telp_pbijk = $request->get('telp_pbijk');
                    $data->alamat_pbijk = $request->get('alamat_pbijk');
                    $data->status_dtks_pbijk = $request->get('status_dtks_pbijk');
                    $data->tujuan_pbijk = $request->get('tujuan_pbijk');
                    $data->status_aksi_pbijk = $request->get('status_aksi_pbijk');
                    $data->petugas_pbijk = $request->get('petugas_pbijk');
                    $data->createdby_pbijk = Auth::user()->id;
                    $data->updatedby_pbijk = Auth::user()->id;
                    // dd($data);
                    $data->save();
                    $logpengaduan = new log_pbijk();
                    $logpengaduan['id_trx_pbijk'] = $data->id;
                    $logpengaduan['id_alur_pbijk'] = $request->get('status_aksi_pbijk');
                    $logpengaduan['tujuan_pbijk'] = $request->get('tujuan_pbijk');
                    $logpengaduan['petugas_pbijk'] = $request->get('petugas_pbijk');
                    $logpengaduan['catatan_pbijk']  = $request->get('catatan_pbijk');
                    $logpengaduan['file_pendukung_pbijk'] = $request->get('file_pendukung');
                    $logpengaduan['tujuan_pbijk'] = $request->get('tujuan');
                    $logpengaduan['created_by_pbijk'] = Auth::user()->id;
                    $logpengaduan['updated_by_pbijk'] = Auth::user()->id;
                    // dd($logpengaduan);
                    $logpengaduan->save();
             

                    $logpengaduan->save();
                    if($request->get('jenis_pelapor_pbijk') == 'Orang Lain'){
                        $pelapor = new pelapor();
                    $pelapor['id_menu'] = '04';
                    $pelapor['id_form'] = $data->id;
                    $pelapor['jenis_peelaporan'] = $request->get('jenis_peelaporan');
                    $pelapor['nama_pelapor']  = $request->get('nama_pelapor');
                    $pelapor['nik_pelapor'] = $request->get('nik_pelapor');
                    $pelapor['ada_nik_pelapor'] = $request->get('ada_nik_pelapor');
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
                        $pelapor['id_menu'] = '04';
                        $pelapor['id_form'] = $data->id;
                        $pelapor['jenis_peelaporan'] = $request->get('jenis_pelapor_pbijk');
                        $pelapor['nama_pelapor']  =  $request->get('nama_pbijk');
                        $pelapor['ada_nik_pelapor'] = $request->get('ada_nik_pbijk');
                        $pelapor['nik_pelapor'] = $request->get('nik_pbijk');
                        $pelapor['status_dtks_pelapor'] = $request->get('status_dtks_pbijk');
                        $pelapor['tempat_lahir_pelapor'] = $request->get('tempat_lahir_pbijk');
                        $pelapor['tanggal_lahir_pelapor'] = $request->get('tgl_lahir_pbijk');
                        $pelapor['jenis_kelamin'] = $request->get('jenis_kelamin_pbijk');
                        $pelapor['nama_pelapor']  = $request->get('nama_pbijk');
                        $pelapor['telepon_pelapor'] = $request->get('telp_pbijk');
                        $pelapor['alamat_pelapor'] = $request->get('alamat_pbijk');
                        $pelapor['createdby_pelapor'] = Auth::user()->id;
                        $pelapor['updatedby_pelapor'] = Auth::user()->id;
    
                        $pelapor->save();
                    }
                    
                    return redirect('rekomendasi_rekativasi_pbi_jks')->withSuccess('Data Rekomendasi Berhasil Ditambahkan');
                } else {
                    //jika nik ada di prelist akan masuk ke sini
                    $data = new rekomendasi_rekativasi_pbi_jk();
                    $files = [
                        'file_ktp_terlapor_pbijk' => 'rekativasi/ktp/',
                        'file_kk_terlapor_pbijk' => 'rekativasi/kk/',
                        'file_keterangan_dtks_pbijk' => 'rekativasi/strukturorganisasi/',
                        'file_pendukung_pbijk' => 'rekativasi/wajibpajak/'
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
                    
                    // $data->id_alur = $request->get('id_alur_pbijk');
                    $data->no_pendaftaran_pbijk = mt_rand(100, 1000);
                    $data->id_provinsi_pbijk = $request->get('id_provinsi_pbijk');
                    $data->id_kabkot_pbijk = $request->get('id_kabkot_pbijk');
                    $data->id_kecamatan_pbijk = $request->get('id_kecamatan_pbijk');
                    $data->id_kelurahan_pbijk = $request->get('id_kelurahan_pbijk');
                    $data->jenis_pelapor_pbijk = $request->get('jenis_pelapor_pbijk');
                    $data->ada_nik_pbijk = $request->get('ada_nik_pbijk');
                    $data->nik_pbijk = $request->get('nik_pbijk');
                    $data->no_kk_pbijk = $request->get('no_kk_pbijk');
                    // $data->no_kis = $request->get('no_kis');
                    $data->nama_pbijk = $request->get('nama_pbijk');
                    $data->tgl_lahir_pbijk = $request->get('tgl_lahir_pbijk');
                    $data->tempat_lahir_pbijk = $request->get('tempat_lahir_pbijk');
                    $data->jenis_kelamin_pbijk = $request->get('jenis_kelamin_pbijk');
                    $data->telp_pbijk = $request->get('telp_pbijk');
                    $data->alamat_pbijk = $request->get('alamat_pbijk');
                    $data->status_dtks_pbijk = $request->get('status_dtks_pbijk');
                    $data->tujuan_pbijk = $request->get('tujuan_pbijk');
                    $data->status_aksi_pbijk = $request->get('status_aksi_pbijk');
                    $data->petugas_pbijk = $request->get('petugas_pbijk');
                    $data->createdby_pbijk = Auth::user()->id;
                    $data->updatedby_pbijk = Auth::user()->id;
                    // dd($data);
                    $data->save();
                    $logpengaduan = new log_pbijk();

                    $logpengaduan['id_trx_pbijk'] = $data->id;
                    $logpengaduan['id_alur_pbijk'] = $request->get('status_aksi_pbijk');
                    $logpengaduan['tujuan_pbijk'] = $request->get('tujuan_pbijk');
                    $logpengaduan['petugas_pbijk'] = $request->get('petugas_pbijk');
                    $logpengaduan['catatan_pbijk']  = $request->get('catatan_pbijk');
                    $logpengaduan['file_pendukung_pbijk'] = $request->get('file_pendukung');
                    $logpengaduan['tujuan_pbijk'] = $request->get('tujuan');
                    $logpengaduan['created_by_pbijk'] = Auth::user()->id;
                    $logpengaduan['updated_by_pbijk'] = Auth::user()->id;
                  
                    $logpengaduan->save();    

                    $logpengaduan->save();
                    if($request->get('jenis_pelapor_pbijk') == 'Orang Lain'){
                        $pelapor = new pelapor();
                    $pelapor['id_menu'] = '04';
                    $pelapor['id_form'] = $data->id;
                    $pelapor['jenis_peelaporan'] = $request->get('jenis_peelaporan');
                    $pelapor['nama_pelapor']  = $request->get('nama_pelapor');
                    $pelapor['nik_pelapor'] = $request->get('nik_pelapor');
                    $pelapor['ada_nik_pelapor'] = $request->get('ada_nik_pelapor');
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
                        $pelapor['id_menu'] = '04';
                        $pelapor['id_form'] = $data->id;
                        $pelapor['jenis_peelaporan'] = $request->get('jenis_pelapor_pbijk');
                        $pelapor['nama_pelapor']  =  $request->get('nama_pbijk');
                        $pelapor['ada_nik_pelapor'] = $request->get('ada_nik_pbijk');
                        $pelapor['nik_pelapor'] = $request->get('nik_pbijk');
                        $pelapor['status_dtks_pelapor'] = $request->get('status_dtks_pbijk');
                        $pelapor['tempat_lahir_pelapor'] = $request->get('tempat_lahir_pbijk');
                        $pelapor['tanggal_lahir_pelapor'] = $request->get('tgl_lahir_pbijk');
                        $pelapor['jenis_kelamin'] = $request->get('jenis_kelamin_pbijk');
                        $pelapor['nama_pelapor']  = $request->get('nama_pbijk');
                        $pelapor['telepon_pelapor'] = $request->get('telp_pbijk');
                        $pelapor['alamat_pelapor'] = $request->get('alamat_pbijk');
                        $pelapor['createdby_pelapor'] = Auth::user()->id;
                        $pelapor['updatedby_pelapor'] = Auth::user()->id;
    
                        $pelapor->save();
                    }
                    
                    return redirect('rekomendasi_rekativasi_pbi_jks')->withSuccess('Data Rekomendasi Berhasil Ditambahkan');
                }
            }
        } else {
            //jika status draft adalah ini akan masuk ke sini
            $data = new rekomendasi_rekativasi_pbi_jk();
            $files = [
                'file_ktp_terlapor_pbijk' => 'rekativasi/ktp/',
                'file_kk_terlapor_pbijk' => 'rekativasi/kk/',
                'file_keterangan_dtks_pbijk' => 'rekativasi/strukturorganisasi/',
                'file_pendukung_pbijk' => 'rekativasi/wajibpajak/'
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
            
            // $data->id_alur = $request->get('id_alur_pbijk');
            $data->no_pendaftaran_pbijk = mt_rand(100, 1000);
            $data->id_provinsi_pbijk = $request->get('id_provinsi_pbijk');
            $data->id_kabkot_pbijk = $request->get('id_kabkot_pbijk');
            $data->id_kecamatan_pbijk = $request->get('id_kecamatan_pbijk');
            $data->id_kelurahan_pbijk = $request->get('id_kelurahan_pbijk');
            $data->jenis_pelapor_pbijk = $request->get('jenis_pelapor_pbijk');
            $data->ada_nik_pbijk = $request->get('ada_nik_pbijk');
            $data->nik_pbijk = $request->get('nik_pbijk');
            $data->no_kk_pbijk = $request->get('no_kk_pbijk');
            // $data->no_kis = $request->get('no_kis');
            $data->nama_pbijk = $request->get('nama_pbijk');
            $data->tgl_lahir_pbijk = $request->get('tgl_lahir_pbijk');
            $data->tempat_lahir_pbijk = $request->get('tempat_lahir_pbijk');
            $data->jenis_kelamin_pbijk = $request->get('jenis_kelamin_pbijk');
            $data->telp_pbijk = $request->get('telp_pbijk');
            $data->status_dtks_pbijk = $request->get('status_dtks_pbijk');
            $data->tujuan_pbijk = $request->get('tujuan_pbijk');
            $data->status_aksi_pbijk = $request->get('status_aksi_pbijk');
            $data->petugas_pbijk = $request->get('petugas_pbijk');
            $data->createdby_pbijk = Auth::user()->id;
            $data->updatedby_pbijk = Auth::user()->id;
            // dd($data);
            $data->save();
            // if($request->get('jenis_pelapor_pbijk') == 'Orang Lain'){
                $pelapor = new pelapor();
            $pelapor['id_menu'] = '04';
            $pelapor['id_form'] = $data->id;
            $pelapor['jenis_peelaporan'] = $request->get('jenis_peelaporan');
            $pelapor['nama_pelapor']  = $request->get('nama_pelapor');
            $pelapor['ada_nik_pelapor'] = $request->get('ada_nik_pelapor');
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
            // }
            
            return redirect('rekomendasi_rekativasi_pbi_jks')->withWarning('NIK Tidak Tersedia Data Disimpan sebagai draft');
        }
    }

    /**
     * Display the specified rekomendasi_rekativasi_pbi_jk.
     */
    public function show($id)
    {
        $userid = Auth::user()->id;
        $rekomendasiRekativasiPbiJk = DB::table('rekomendasi_rekativasi_pbi_jks as w')->select(
            'w.*',
            'rls.name',
            'usr.name',
            'prov.name_prov',
            'kota.name_cities',
            'kecamatan.name_districts',
            'b.name_village',
            'p.*',
            // 'w.status_wilayah',
        )
            ->leftjoin('roles as rls', 'rls.id', '=', 'w.tujuan_pbijk')
            ->leftjoin('users as usr', 'usr.id', '=', 'w.petugas_pbijk')
            ->leftjoin('pelapor as p', 'p.id_form', 'w.id')
            ->leftjoin('indonesia_provinces as prov', 'prov.code', '=', 'w.id_provinsi_pbijk')
            ->leftjoin('indonesia_cities as kota', 'kota.code', '=', 'w.id_kabkot_pbijk')
            ->leftjoin('indonesia_districts as kecamatan', 'kecamatan.code', '=', 'w.id_kecamatan_pbijk')
            ->leftjoin('indonesia_villages as b', 'b.code', '=', 'w.id_kelurahan_pbijk')
            ->where('p.id_menu', '04')
            ->where('w.id', $id)->first();
        // dd($rekomendasiRekativasiPbiJk);

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

        if (empty($rekomendasiRekativasiPbiJk)) {
            Flash::error('Rekomendasi not found');

            // return redirect(route('rekomendasi_rekativasi_pbi_jks.show'));
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

        // $log_pbijk = log_pbijk::where('id_trx_pbijk', $id)->get();

        $log_pbijk = DB::table('log_pbijk as w')->select(
            'w.*',
            'roles.name',
            'users.name',
            // 'alur.name'

        )
        ->leftjoin('users', 'users.id', '=', 'w.petugas_pbijk')
        ->leftjoin('roles', 'roles.id', '=', 'w.tujuan_pbijk')
        ->where('w.id_trx_pbijk', $id)->get();
        $log_pbijk = DB::table('log_pbijk as w')->select(
            'w.*',
            'rls.name as name_update',
            'usr.name',
            'roles.name as name_roles',

        )
            ->leftjoin('roles', 'roles.id', '=', 'w.tujuan_pbijk')
            ->leftjoin('users as rls', 'rls.id', '=', 'w.updated_by_pbijk')
            ->leftjoin('users as usr', 'usr.id', '=', 'w.created_by_pbijk')
            ->where('w.id_trx_pbijk', $id)->get();
        return view('rekomendasi_rekativasi_pbi_jks.show', compact('rekomendasiRekativasiPbiJk', 'roleid', 'wilayah', 'checkroles', 'log_pbijk'));
    }
    /**
     * Show the form for editing the specified rekomendasi_rekativasi_pbi_jk.
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
                ->leftjoin('indonesia_provinces as prov', 'prov.code', '=', 'w.province_id')
                ->leftjoin('indonesia_cities as kota', 'kota.code', '=', 'w.kota_id')
                ->leftjoin('indonesia_districts as kecamatan', 'kecamatan.code', '=', 'w.kecamatan_id')
                ->leftjoin('indonesia_villages as b', 'b.code', '=', 'w.kelurahan_id')
                ->where('status_wilayah', '1')
                ->where('w.createdby', $userid)->get();

            // $rekomendasiPbiJkPelapor = DB::table('rekomendasi_biaya_perawatans')
            //     ->join('pelapor', function ($join) use ($id) {
            //         $join->on('rekomendasi_biaya_perawatans.id', '=', 'pelapor.id_form')
            //             ->where('rekomendasi_biaya_perawatans.id', '=', $id);
            //     })
            //     ->select('rekomendasi_biaya_perawatans.*', 'pelapor.*')
            //     ->where('pelapor.id_menu', '04')
            //     ->where('pelapor.id_form', $id)
            //     ->first();
            // dd($rekomendasiPbiJkPelapor);
            $getUsers = DB::table('model_has_roles')
                ->leftjoin('roles', 'roles.id', '=', 'model_has_roles.role_id')
                ->leftjoin('rekomendasi_rekativasi_pbi_jks', 'rekomendasi_rekativasi_pbi_jks.createdby_pbijk', '=', 'model_has_roles.model_id')
                ->where('rekomendasi_rekativasi_pbi_jks.id', '=', $id)
                // ->where('status_aksi', '=', 'Draft')
                // ->orwhere('status_aksi', '=', 'Teruskan')
                ->get();
            // dd($getUsers);
            //Tujuan
            $createdby = DB::table('rekomendasi_rekativasi_pbi_jks')
                ->join('users', 'rekomendasi_rekativasi_pbi_jks.createdby_pbijk', '=', 'users.name')
                ->join('model_has_roles', 'model_has_roles.model_id', '=', 'users.id')
                ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                ->select('rekomendasi_rekativasi_pbi_jks.id', 'rekomendasi_rekativasi_pbi_jks.createdby_pbijk', 'roles.name')
                ->get();
            $rekomendasiRekativasiPbiJk = DB::table('rekomendasi_rekativasi_pbi_jks as w')->select(
                    'w.*',
                    'rls.name',
                    'usr.name',
                    'prov.name_prov',
                    'kota.name_cities',
                    'kecamatan.name_districts',
                    'b.name_village',
                    'p.*',
                    // 'w.status_wilayah',
                )
                    ->leftjoin('roles as rls', 'rls.id', '=', 'w.tujuan_pbijk')
                    ->leftjoin('users as usr', 'usr.id', '=', 'w.petugas_pbijk')
                    ->leftjoin('pelapor as p', 'p.id_form', 'w.id')
                    ->leftjoin('indonesia_provinces as prov', 'prov.code', '=', 'w.id_provinsi_pbijk')
                    ->leftjoin('indonesia_cities as kota', 'kota.code', '=', 'w.id_kabkot_pbijk')
                    ->leftjoin('indonesia_districts as kecamatan', 'kecamatan.code', '=', 'w.id_kecamatan_pbijk')
                    ->leftjoin('indonesia_villages as b', 'b.code', '=', 'w.id_kelurahan_pbijk')
                    ->where('p.id_menu', '04')
                    ->where('p.id_form', $id)
                    ->where('w.id', $id)->first();
            // dd($rekomendasiRekativasiPbiJk);
            $getdata = DB::table('model_has_roles')
                ->leftjoin('rekomendasi_rekativasi_pbi_jks as b', 'b.tujuan_pbijk', '=', 'model_has_roles.role_id')
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

            // $rekomendasiRekativasiPbiJk = $this->rekomendasiRekativasiPbiJkRepository->find($id);


            return view('rekomendasi_rekativasi_pbi_jks.edit', compact('wilayah','getAuth', 'rekomendasiRekativasiPbiJk', 'roleid', 'getdata', 'alur', 'createdby', 'getUsers'));
    }


    /**
     * Update the specified rekomendasi_rekativasi_pbi_jk in storage.
     */
    public function update($id, Request $request)
    {
        $userid = Auth::user()->id;
        $datasudtks = rekomendasi_rekativasi_pbi_jk::where('id', $id)->first();
        $pemebuatanDataRekomendasiPbijk = DB::table('rekomendasi_rekativasi_pbi_jks as w')
		->join('model_has_roles', 'model_has_roles.model_id', '=', 'w.createdby_pbijk')
		->join('roles as rls', 'rls.id', '=', 'model_has_roles.role_id')
		->leftjoin('users', 'users.id', '=', 'w.createdby_pbijk')
		->select(
					'w.*',
					'rls.name as name_roles',
					// 'usr.name',
					'model_has_roles.*')
		->where('w.id', $id)->first();
   
        $data = $request->all();
        $files = [
            'file_ktp_terlapor_pbijk',
            'file_kk_terlapor_pbijk',
            'file_keterangan_dtks_pbijk',
            'file_pendukung_pbijk' ,
        ];
        foreach ($files as $file) {
            if ($request->file($file)) {
                $path = $request->file($file);
                $filename = $file . $path->getClientOriginalName();
                $return = Storage::disk('imagekit')->put($filename, fopen($path->getRealPath(), 'r'));
                $data[$file] = Storage::disk('imagekit')->url($filename);
            } else {
                $data[$file] = $pemebuatanDataRekomendasiPbijk->$file;
            }
        }
        if ( $request->get('status_aksi_pbijk') == 'Kembalikan' || $request->get('status_aksi_pbijk') == 'Selesai') {
            // dd($pemebuatanDataRekomendasiPbijk);
                $data['petugas_pbijk']  = $pemebuatanDataRekomendasiPbijk->model_id;
                $data['tujuan_pbijk'] = $pemebuatanDataRekomendasiPbijk->role_id;
        }else{
            $data['petugas_pbijk']  = $request->get('petugas_pbijk');
            $data['tujuan_pbijk'] = $request->get('tujuan_pbijk');
        }
        $datasudtks->update($data);
        $logpengaduan = new log_pbijk();
        $logpengaduan['id_trx_pbijk'] = $datasudtks->id;
        $logpengaduan['id_alur_pbijk'] = $request->get('status_aksi_pbijk');
        // $logpengaduan['petugas_pbijk'] = $request->get('petugas_pbijk');
        $logpengaduan['catatan_pbijk']  = $request->get('catatan_pbijk');
        $logpengaduan['file_pendukung_pbijk'] = $request->get('file_pendukung_pbijk');
        // $logpengaduan['tujuan_pbijk'] = $request->get('tujuan_pbijk');
        if ( $request->get('status_aksi_pbijk') == 'Kembalikan' || $request->get('status_aksi_pbijk') == 'Selesai') {
            $logpengaduan['petugas_pbijk']  = $pemebuatanDataRekomendasiPbijk->model_id;
            $logpengaduan['tujuan_pbijk'] = $pemebuatanDataRekomendasiPbijk->role_id;
        }else{
            $logpengaduan['petugas_pbijk']  = $request->get('petugas_pbijk');
            $logpengaduan['tujuan_pbijk'] = $request->get('tujuan_pbijk');
        }
        $logpengaduan['created_by_pbijk'] = Auth::user()->id;
        $logpengaduan['updated_by_pbijk'] = Auth::user()->id;
        // petugas_pbijk
        $logpengaduan->save();
        $pelapor['id_menu'] = '04';
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

            return redirect('rekomendasi_rekativasi_pbi_jks')->withSuccess('Data Berhasil Diubah');
    }


    /**
     * Remove the specified rekomendasi_rekativasi_pbi_jk from storage.
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $rekomendasiRekativasiPbiJk = $this->rekomendasiRekativasiPbiJkRepository->find($id);

        if (empty($rekomendasiRekativasiPbiJk)) {
            Flash::error('Rekomendasi Rekativasi Pbi Jk not found');

            return redirect(route('rekomendasi_rekativasi_pbi_jks.index'));
        }

        $this->rekomendasiRekativasiPbiJkRepository->delete($id);

        Flash::success('Rekomendasi Rekativasi Pbi Jk deleted successfully.');

        return redirect(route('rekomendasi_rekativasi_pbi_jks.index'));
    }
    public function draft(Request $request)
    {
        $user_name = Auth::user()->name;
        $query = DB::table('rekomendasi_rekativasi_pbi_jks')
            ->leftjoin('users', 'users.id', '=', 'rekomendasi_rekativasi_pbi_jks.createdby_pbijk')
            ->leftjoin('wilayahs', 'wilayahs.createdby', '=', 'rekomendasi_rekativasi_pbi_jks.createdby_pbijk')
            ->leftjoin('roles', 'roles.id', '=', 'rekomendasi_rekativasi_pbi_jks.tujuan_pbijk')
            ->leftjoin('indonesia_districts as d', 'd.code', '=', 'rekomendasi_rekativasi_pbi_jks.id_kecamatan_pbijk')
            ->leftjoin('indonesia_villages as b', 'b.code', '=', 'rekomendasi_rekativasi_pbi_jks.id_kelurahan_pbijk')
            ->select('rekomendasi_rekativasi_pbi_jks.*', 'b.name_village', 'd.name_districts','users.name','roles.name')
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
        if ($user_wilayah->name == 'Front Office Kelurahan'||$user_wilayah->name == 'fasilitator') {
            $query->orWhere(function ($query) use ($user_wilayah) {
                $query->where('rekomendasi_rekativasi_pbi_jks.id_kelurahan_pbijk', $user_wilayah->kelurahan_id);
                $query->where('rekomendasi_rekativasi_pbi_jks.status_aksi_pbijk', 'Draft');
                $query->where('rekomendasi_rekativasi_pbi_jks.createdby_pbijk',  Auth::user()->id);
            });
        }
        if ($user_wilayah->name == 'Front Office kota') {
            $query->orWhere(function ($query) use ($user_wilayah) {
                $query->where('rekomendasi_rekativasi_pbi_jks.id_kelurahan_pbijk', $user_wilayah->kelurahan_id);
                $query->where('rekomendasi_rekativasi_pbi_jks.status_aksi_pbijk', 'Draft');
                $query->where('rekomendasi_rekativasi_pbi_jks.createdby_pbijk',  Auth::user()->id);
            });
        }
        
        if ($user_wilayah->name == 'Front Office Kelurahan'|| $user_wilayah == 'fasilitator') {
            if ($request->has('search') && !empty($request->search['value'])) {
                $search = $request->search['value'];
                $query->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_rekativasi_pbi_jks.id_kelurahan_pbijk', $user_wilayah->kelurahan_id);
                    $query->where('rekomendasi_rekativasi_pbi_jks.status_aksi_pbijk', 'Draft');
                    $query->where('rekomendasi_rekativasi_pbi_jks.createdby_pbijk',  Auth::user()->id);
                })
                ->where('rekomendasi_rekativasi_pbi_jks.no_pendaftaran_pbijk', 'like', "%$search%");
            }
        }elseif($user_wilayah->name == 'Front Office Kota')  {
            if ($request->has('search') && !empty($request->search['value'])) {
                $search = $request->search['value'];
                $query->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_rekativasi_pbi_jks.id_kabkot_pbijk', $user_wilayah->kota_id);
                    $query->where('rekomendasi_rekativasi_pbi_jks.status_aksi_pbijk', 'Draft');
                    $query->where('rekomendasi_rekativasi_pbi_jks.createdby_pbijk',  Auth::user()->id);
                })
                ->where('rekomendasi_rekativasi_pbi_jks.no_pendaftaran_pbijk', 'like', "%$search%");
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
            // Get paginated data
       //Add paginate
        $start = $request->start;
        // dd($start);
        $length = $request->length;
        // dd($length);
        $query->offset($start)->limit($length);
        $data = $query->get();
        // dd($data);   
        // mengubah data JSON menjadi objek PHP
        // mengubah data JSON menjadi objek PHP

        return response()->json([
            'draw' => $request->input('draw'),
            'recordsTotal' => rekomendasi_rekativasi_pbi_jk::count(),
            'recordsFiltered' => $total_filtered_items,
            'data' => $data,
        ]);
    }
    public function diproses(Request $request)
    {

        $user_id = Auth::user()->id;
        // $user_id = 22;
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
            $query = DB::table('rekomendasi_rekativasi_pbi_jks')
            ->join('users', 'users.id', '=', 'rekomendasi_rekativasi_pbi_jks.petugas_pbijk')
            ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_rekativasi_pbi_jks.id_kecamatan_pbijk')
            ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_rekativasi_pbi_jks.id_kelurahan_pbijk')
            ->join('roles as r', 'r.id', '=', 'rekomendasi_rekativasi_pbi_jks.tujuan_pbijk')
            ->select('rekomendasi_rekativasi_pbi_jks.*', 'b.name_village', 'd.name_districts','users.name','r.name');
      
        // dd($user_wilayah->name);
        if ($user_wilayah->name == 'Front Office Kelurahan') {
            $query = DB::table('rekomendasi_rekativasi_pbi_jks')
                ->join('users', 'users.id', '=', 'rekomendasi_rekativasi_pbi_jks.petugas_pbijk')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_rekativasi_pbi_jks.id_kecamatan_pbijk')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_rekativasi_pbi_jks.id_kelurahan_pbijk')
                ->join('roles as r', 'r.id', '=', 'rekomendasi_rekativasi_pbi_jks.tujuan_pbijk')
                ->select('rekomendasi_rekativasi_pbi_jks.*', 'b.name_village', 'd.name_districts','users.name','r.name');
                $query->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_rekativasi_pbi_jks.id_kelurahan_pbijk', '=', $user_wilayah->kelurahan_id)
                        ->where('rekomendasi_rekativasi_pbi_jks.tujuan_pbijk', '=', $user_wilayah->role_id)
                        ->where('rekomendasi_rekativasi_pbi_jks.petugas_pbijk', '=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_rekativasi_pbi_jks.status_aksi_pbijk', '=', 'Teruskan')
                                ->orWhere('rekomendasi_rekativasi_pbi_jks.status_aksi_pbijk', '=', 'kembalikan');
                        });
                });
            // dd($query);
        }elseif ($user_wilayah->name == 'Front Office kota') {
            //  dd($user_wilayah->role_id);
            $query = DB::table('rekomendasi_rekativasi_pbi_jks')
            ->join('users', 'users.id', '=', 'rekomendasi_rekativasi_pbi_jks.petugas_pbijk')
            ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_rekativasi_pbi_jks.id_kecamatan_pbijk')
            ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_rekativasi_pbi_jks.id_kelurahan_pbijk')
            ->join('roles as r', 'r.id', '=', 'rekomendasi_rekativasi_pbi_jks.tujuan_pbijk')
            ->select('rekomendasi_rekativasi_pbi_jks.*', 'b.name_village', 'd.name_districts','users.name','r.name')
            ->orWhere(function ($query) use ($user_wilayah) {
                $query->where('rekomendasi_rekativasi_pbi_jks.id_kabkot_pbijk', '=', $user_wilayah->kota_id)
                    ->where('rekomendasi_rekativasi_pbi_jks.tujuan_pbijk', '=', $user_wilayah->role_id)
                    ->where('rekomendasi_rekativasi_pbi_jks.petugas_pbijk', '=', $user_wilayah->model_id)
                    ->where(function ($query) {
                        $query->where('rekomendasi_rekativasi_pbi_jks.status_aksi_pbijk', '=', 'Teruskan')
                            ->orWhere('rekomendasi_rekativasi_pbi_jks.status_aksi_pbijk', '=', 'kembalikan');
                    });
            });
          
        }elseif ($user_wilayah->name == 'supervisor') {
            //  dd($user_wilayah->role_id);
            $query = DB::table('rekomendasi_rekativasi_pbi_jks')
            ->join('users', 'users.id', '=', 'rekomendasi_rekativasi_pbi_jks.petugas_pbijk')
            ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_rekativasi_pbi_jks.id_kecamatan_pbijk')
            ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_rekativasi_pbi_jks.id_kelurahan_pbijk')
            ->join('roles as r', 'r.id', '=', 'rekomendasi_rekativasi_pbi_jks.tujuan_pbijk')
            ->select('rekomendasi_rekativasi_pbi_jks.*', 'b.name_village', 'd.name_districts','users.name','r.name')
            ->orWhere(function ($query) use ($user_wilayah) {
                $query->where('rekomendasi_rekativasi_pbi_jks.id_kecamatan_pbijk', '=', $user_wilayah->kecamatan_id)
                    ->where('rekomendasi_rekativasi_pbi_jks.tujuan_pbijk', '=', $user_wilayah->role_id)
                    ->where('rekomendasi_rekativasi_pbi_jks.petugas_pbijk', '=', $user_wilayah->model_id)
                    ->where(function ($query) {
                        $query->where('rekomendasi_rekativasi_pbi_jks.status_aksi_pbijk', '=', 'Teruskan')
                            ->orWhere('rekomendasi_rekativasi_pbi_jks.status_aksi_pbijk', '=', 'kembalikan');
                    });
            });
          
        }
        if ($user_wilayah->name == 'Back Ofiice kelurahan') {
            //  dd($user_wilayah->role_id);
            $query->orWhere(function ($query) use ($user_wilayah) {
                $query->where('rekomendasi_rekativasi_pbi_jks.id_kabkot_pbijk', '=', $user_wilayah->kota_id)
                    ->where('rekomendasi_rekativasi_pbi_jks.tujuan_pbijk', '=', $user_wilayah->role_id)
                    ->where('rekomendasi_rekativasi_pbi_jks.petugas_pbijk', '=', $user_wilayah->model_id)
                    ->where(function ($query) {
                        $query->where('rekomendasi_rekativasi_pbi_jks.status_aksi_pbijk', '=', 'Teruskan')
                            ->orWhere('rekomendasi_rekativasi_pbi_jks.status_aksi_pbijk', '=', 'kembalikan');
                    });
            });
            // dd($query);
        }
        if ($user_wilayah->name == 'Back Ofiice Kota') {
            //  dd($user_wilayah->role_id);
            $query->orWhere(function ($query) use ($user_wilayah) {
                $query->where('rekomendasi_rekativasi_pbi_jks.id_kabkot_pbijk', '=', $user_wilayah->kota_id)
                    ->where('rekomendasi_rekativasi_pbi_jks.tujuan_pbijk', '=', $user_wilayah->role_id)
                    ->where('rekomendasi_rekativasi_pbi_jks.petugas_pbijk', '=', $user_wilayah->model_id)
                    ->where(function ($query) {
                        $query->where('rekomendasi_rekativasi_pbi_jks.status_aksi_pbijk', '=', 'Teruskan')
                            ->orWhere('rekomendasi_rekativasi_pbi_jks.status_aksi_pbijk', '=', 'kembalikan');
                    });
            });
            // dd($query);
        }
        if ($user_wilayah->name == 'kepala bidang') {

            $query->orWhere(function ($query) use ($user_wilayah) {
                $query->where('rekomendasi_rekativasi_pbi_jks.id_kabkot_pbijk', '=', $user_wilayah->kota_id)
                    ->where('rekomendasi_rekativasi_pbi_jks.tujuan_pbijk', '=', $user_wilayah->role_id)
                    ->where('rekomendasi_rekativasi_pbi_jks.petugas_pbijk', '=', $user_wilayah->model_id)
                    ->where(function ($query) {
                        $query->where('rekomendasi_rekativasi_pbi_jks.status_aksi_pbijk', '=', 'Teruskan')
                            ->orWhere('rekomendasi_rekativasi_pbi_jks.status_aksi_pbijk', '=', 'kembalikan');
                    });
                // dd($va);
            });
        }
        if ($user_wilayah->name == 'SekertarisDinas') {
            // dd($user_wilayah);
            $query->orWhere(function ($query) use ($user_wilayah) {
                $query->where('rekomendasi_rekativasi_pbi_jks.id_kelurahan_pbijk', '=', $user_wilayah->kelurahan_id)
                    ->where('rekomendasi_rekativasi_pbi_jks.tujuan_pbijk', '=', $user_wilayah->role_id)
                    ->where('rekomendasi_rekativasi_pbi_jks.petugas_pbijk', '=', $user_wilayah->model_id)
                    ->where(function ($query) {
                        $query->where('rekomendasi_rekativasi_pbi_jks.status_aksi_pbijk', '=', 'Teruskan')
                            ->orWhere('rekomendasi_rekativasi_pbi_jks.status_aksi_pbijk', '=', 'kembalikan');
                    });
                // dd($va);
            });
        }
        if ($user_wilayah->name == 'KepalaDinas') {
            // dd($user_wilayah);
            $query->orWhere(function ($query) use ($user_wilayah) {
                $query->where('rekomendasi_rekativasi_pbi_jks.id_kelurahan_pbijk', '=', $user_wilayah->kelurahan_id)
                    ->where('rekomendasi_rekativasi_pbi_jks.tujuan_pbijk', '=', $user_wilayah->role_id)
                    ->where('rekomendasi_rekativasi_pbi_jks.petugas_pbijk', '=', $user_wilayah->model_id)
                    ->where(function ($query) {
                        $query->where('rekomendasi_rekativasi_pbi_jks.status_aksi_pbijk', '=', 'Teruskan')
                            ->orWhere('rekomendasi_rekativasi_pbi_jks.status_aksi_pbijk', '=', 'kembalikan');
                    });
                // dd($va);
            });
        }
        if ($user_wilayah->name == 'Front Office Kelurahan' || $user_wilayah->name == 'Back Ofiice kelurahan'|| $user_wilayah->name == 'fasilitator') {
            if ($request->has('search') && !empty($request->search['value'])) {
                $search = $request->search['value'];
                 $query = DB::table('rekomendasi_rekativasi_pbi_jks')
                ->join('users', 'users.id', '=', 'rekomendasi_rekativasi_pbi_jks.petugas_pbijk')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_rekativasi_pbi_jks.id_kecamatan_pbijk')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_rekativasi_pbi_jks.id_kelurahan_pbijk')
                ->join('roles as r', 'r.id', '=', 'rekomendasi_rekativasi_pbi_jks.tujuan_pbijk')
                ->select('rekomendasi_rekativasi_pbi_jks.*', 'b.name_village', 'd.name_districts','users.name','r.name');
                $query->orWhere(function ($query) use ($user_wilayah,$search) {
                    $query->where('rekomendasi_rekativasi_pbi_jks.id_kelurahan_pbijk', '=', $user_wilayah->kelurahan_id)
                        ->where('rekomendasi_rekativasi_pbi_jks.tujuan_pbijk', '=', $user_wilayah->role_id)
                        ->where('rekomendasi_rekativasi_pbi_jks.petugas_pbijk', '=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_rekativasi_pbi_jks.status_aksi_pbijk', '=', 'Teruskan')
                                ->orWhere('rekomendasi_rekativasi_pbi_jks.status_aksi_pbijk', '=', 'kembalikan');
                        })
                        ->where('rekomendasi_rekativasi_pbi_jks.no_pendaftaran_pbijk', 'like', "%$search%");

                });
            }
        }elseif($user_wilayah->name == 'Front Office Kota' || $user_wilayah->name == 'Back Ofiice Kota'|| $user_wilayah->name == 'KepalaDinas'||$user_wilayah->name == 'SekertarisDinas'||$user_wilayah->name == 'Supervisor') {
            if ($request->has('search') && !empty($request->search['value'])) {
                $search = $request->search['value'];
                $query = DB::table('rekomendasi_rekativasi_pbi_jks')
                ->join('users', 'users.id', '=', 'rekomendasi_rekativasi_pbi_jks.petugas_pbijk')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_rekativasi_pbi_jks.id_kecamatan_pbijk')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_rekativasi_pbi_jks.id_kelurahan_pbijk')
                ->join('roles as r', 'r.id', '=', 'rekomendasi_rekativasi_pbi_jks.tujuan_pbijk')
                ->select('rekomendasi_rekativasi_pbi_jks.*', 'b.name_village', 'd.name_districts','users.name','r.name');
                $query->orWhere(function ($query) use ($user_wilayah,$search) {
                    $query->where('rekomendasi_rekativasi_pbi_jks.id_kabkot_pbijk', $user_wilayah->kota_id)
                        ->where('rekomendasi_rekativasi_pbi_jks.tujuan_pbijk', '=', $user_wilayah->role_id)
                        ->where('rekomendasi_rekativasi_pbi_jks.petugas_pbijk', '=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_rekativasi_pbi_jks.status_aksi_pbijk', '=', 'Teruskan')
                                ->orWhere('rekomendasi_rekativasi_pbi_jks.status_aksi_pbijk', '=', 'kembalikan');
                        })
                        ->where('rekomendasi_rekativasi_pbi_jks.no_pendaftaran_pbijk', 'like', "%$search%");

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
        // dd($start);
        $length = $request->length;
        // dd($length);
        $query->offset($start)->limit($length);
        $data = $query->get();
        // dd($data);   
        // mengubah data JSON menjadi objek PHP
        // dd($data);


        return response()->json([
            'draw' => $request->input('draw'),
            'recordsTotal' => rekomendasi_rekativasi_pbi_jk::count(),
            'recordsFiltered' => $total_filtered_items,
            'data' => $data
        ]);
    }

    public function teruskan(Request $request)
    {
        $user_name = Auth::user()->name;
        // dd($user_name);

        $query = DB::table('rekomendasi_rekativasi_pbi_jks')
            ->join('users', 'users.id', '=', 'rekomendasi_rekativasi_pbi_jks.petugas_pbijk')
            ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_rekativasi_pbi_jks.id_kecamatan_pbijk')
            ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_rekativasi_pbi_jks.id_kelurahan_pbijk')
            ->select('rekomendasi_rekativasi_pbi_jks.*', 'b.name_village', 'd.name_districts','users.name')

            ->select('rekomendasi_rekativasi_pbi_jks.*', 'b.name_village');
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
        //Front Office Kelurahan
        // if ($user_wilayah->name == 'Front Office Kelurahan') {
        // $query = DB::table('rekomendasi_rekativasi_pbi_jks')
        //         ->leftJoin('users', 'users.id', '=', 'rekomendasi_rekativasi_pbi_jks.petugas_pbijk')
        //         // ->leftJoin('log_pbijk', 'log_pbijk.id_trx_pbijk', '=', 'rekomendasi_rekativasi_pbi_jks.id')
        //         // ->leftJoin('model_has_roles', 'model_has_roles.model_id', '=', 'rekomendasi_rekativasi_pbi_jks.tujuan_pbijk')
        //        ->leftJoin('indonesia_districts as d', 'd.code', '=', 'rekomendasi_rekativasi_pbi_jks.id_kecamatan_pbijk')
        //         ->leftJoin('indonesia_villages as b', 'b.code', '=', 'rekomendasi_rekativasi_pbi_jks.id_kelurahan_pbijk')
        //         ->leftJoin('roles as r', 'r.id', '=', 'rekomendasi_rekativasi_pbi_jks.tujuan_pbijk','d.name_districts','users.name')
        //         ->select('rekomendasi_rekativasi_pbi_jks.*', 'b.name_village', 'd.name_districts','users.name','r.name')
        //         ->orWhere(function ($query) use ($user_wilayah) {
        //             $query->where('rekomendasi_rekativasi_pbi_jks.id_kelurahan_pbijk', $user_wilayah->kelurahan_id)
        //                 ->where('rekomendasi_rekativasi_pbi_jks.tujuan_pbijk', '!=', $user_wilayah->role_id)
        //                 ->where(function ($query) {
        //                     $query->where('rekomendasi_rekativasi_pbi_jks.status_aksi_pbijk', '=', 'Teruskan')
        //                         ->orWhere('rekomendasi_rekativasi_pbi_jks.status_aksi_pbijk', '=', 'kembalikan');
        //                 });
        //         })->orderBy('updated_at', 'desc');
        // }
        if ($user_wilayah->name == 'Front Office Kelurahan' || $user_wilayah->name == 'Back Ofiice kelurahan'|| $user_wilayah->name == 'fasilitator') {
            $query = DB::table('rekomendasi_rekativasi_pbi_jks')
            ->leftjoin('indonesia_districts as d', 'd.code', '=', 'rekomendasi_rekativasi_pbi_jks.id_kecamatan_pbijk')
            ->leftjoin('indonesia_villages as b', 'b.code', '=', 'rekomendasi_rekativasi_pbi_jks.id_kelurahan_pbijk')
            // ->leftjoin('roles as r', 'r.id', '=', 'rekomendasi_rekativasi_pbi_jks.petugas_pbijk')
            ->leftjoin('users', 'users.id', '=', 'rekomendasi_rekativasi_pbi_jks.petugas_pbijk')
            ->select('rekomendasi_rekativasi_pbi_jks.*', 'b.name_village', 'd.name_districts','users.name')
            // ->selectRaw('IFNULL(r.name,"") name')
            ->where('rekomendasi_rekativasi_pbi_jks.id_kelurahan_pbijk', '=', $user_wilayah->kelurahan_id)
            ->whereIn('rekomendasi_rekativasi_pbi_jks.status_aksi_pbijk', ['Teruskan','Kembalikan'])
            ->where('rekomendasi_rekativasi_pbi_jks.petugas_pbijk', '<>', $user_id)
            ->whereRaw("(SELECT COUNT(l.id) FROM log_pbijk as l WHERE l.id_trx_pbijk = rekomendasi_rekativasi_pbi_jks.id AND l.updated_by_pbijk = '".$user_id."') > 0 ");
            // dd($query);
        }elseif($user_wilayah->name == 'Front Office Kota' || $user_wilayah->name == 'Back Ofiice Kota'|| $user_wilayah->name == 'KepalaDinas'||$user_wilayah->name == 'SekertarisDinas') {
            $query = DB::table('rekomendasi_rekativasi_pbi_jks')
            ->leftjoin('indonesia_districts as d', 'd.code', '=', 'rekomendasi_rekativasi_pbi_jks.id_kecamatan_pbijk')
            ->leftjoin('indonesia_villages as b', 'b.code', '=', 'rekomendasi_rekativasi_pbi_jks.id_kelurahan_pbijk')
            // ->leftjoin('roles as r', 'r.id', '=', 'rekomendasi_rekativasi_pbi_jks.petugas_pbijk')
            ->leftjoin('users', 'users.id', '=', 'rekomendasi_rekativasi_pbi_jks.petugas_pbijk')
            ->select('rekomendasi_rekativasi_pbi_jks.*', 'b.name_village', 'd.name_districts','users.name')
            // ->selectRaw('IFNULL(r.name,"") name')
            ->where('rekomendasi_rekativasi_pbi_jks.id_kabkot_pbijk', '=', $user_wilayah->kota_id)
            ->whereIn('rekomendasi_rekativasi_pbi_jks.status_aksi_pbijk', ['Teruskan','Kembalikan'])
            ->where('rekomendasi_rekativasi_pbi_jks.petugas_pbijk', '<>', $user_id)
            ->whereRaw("(SELECT COUNT(l.id) FROM log_pbijk as l WHERE l.id_trx_pbijk = rekomendasi_rekativasi_pbi_jks.id AND l.updated_by_pbijk = '".$user_id."') > 0 ");
            // dd($query);
        }else{
            $query = DB::table('rekomendasi_rekativasi_pbi_jks')
            ->leftjoin('indonesia_districts as d', 'd.code', '=', 'rekomendasi_rekativasi_pbi_jks.id_kecamatan_pbijk')
            ->leftjoin('indonesia_villages as b', 'b.code', '=', 'rekomendasi_rekativasi_pbi_jks.id_kelurahan_pbijk')
            // ->leftjoin('roles as r', 'r.id', '=', 'rekomendasi_rekativasi_pbi_jks.petugas_pbijk')
            ->leftjoin('users', 'users.id', '=', 'rekomendasi_rekativasi_pbi_jks.petugas_pbijk')
            ->select('rekomendasi_rekativasi_pbi_jks.*', 'b.name_village', 'd.name_districts','users.name')
            // ->selectRaw('IFNULL(r.name,"") name')
            ->where('rekomendasi_rekativasi_pbi_jks.id_kecamatan_pbijk', '=', $user_wilayah->kecamatan_id)
            ->whereIn('rekomendasi_rekativasi_pbi_jks.status_aksi_pbijk', ['Teruskan','Kembalikan'])
            ->where('rekomendasi_rekativasi_pbi_jks.petugas_pbijk', '<>', $user_id)
            ->whereRaw("(SELECT COUNT(l.id) FROM log_pbijk as l WHERE l.id_trx_pbijk = rekomendasi_rekativasi_pbi_jks.id AND l.updated_by_pbijk = '".$user_id."') > 0 ");
            // dd($query);
        }
        if ($user_wilayah->name == 'Front Office Kelurahan' || $user_wilayah->name == 'Back Ofiice kelurahan'|| $user_wilayah->name == 'fasilitator') {
            if ($request->has('search') && !empty($request->search['value'])) {
                $search = $request->search['value'];
                $query = DB::table('rekomendasi_rekativasi_pbi_jks')
                ->leftjoin('indonesia_districts as d', 'd.code', '=', 'rekomendasi_rekativasi_pbi_jks.id_kecamatan_pbijk')
                ->leftjoin('indonesia_villages as b', 'b.code', '=', 'rekomendasi_rekativasi_pbi_jks.id_kelurahan_pbijk')
                // ->leftjoin('roles as r', 'r.id', '=', 'rekomendasi_rekativasi_pbi_jks.petugas_pbijk')
                ->leftjoin('users', 'users.id', '=', 'rekomendasi_rekativasi_pbi_jks.petugas_pbijk')
                ->select('rekomendasi_rekativasi_pbi_jks.*', 'b.name_village', 'd.name_districts','users.name')
                // ->selectRaw('IFNULL(r.name,"") name')
                ->where('rekomendasi_rekativasi_pbi_jks.id_kelurahan_pbijk', '=', $user_wilayah->kelurahan_id)
                ->whereIn('rekomendasi_rekativasi_pbi_jks.status_aksi_pbijk', ['Teruskan','Kembalikan'])
                ->where('rekomendasi_rekativasi_pbi_jks.petugas_pbijk', '<>', $user_id)
                ->whereRaw("(SELECT COUNT(l.id) FROM log_pbijk as l WHERE l.id_trx_pbijk = rekomendasi_rekativasi_pbi_jks.id AND l.created_by_pbijk = '".$user_id."') > 0 ")
                ->where('rekomendasi_rekativasi_pbi_jks.no_pendaftaran_pbijk', 'like', "%$search%");
            }
        }elseif($user_wilayah->name == 'Front Office Kota' || $user_wilayah->name == 'Back Ofiice Kota'|| $user_wilayah->name == 'KepalaDinas'||$user_wilayah->name == 'SekertarisDinas') {
            if ($request->has('search') && !empty($request->search['value'])) {
                $search = $request->search['value'];
                $query = DB::table('rekomendasi_rekativasi_pbi_jks')
                ->leftjoin('indonesia_districts as d', 'd.code', '=', 'rekomendasi_rekativasi_pbi_jks.id_kecamatan_pbijk')
                ->leftjoin('indonesia_villages as b', 'b.code', '=', 'rekomendasi_rekativasi_pbi_jks.id_kelurahan_pbijk')
                // ->leftjoin('roles as r', 'r.id', '=', 'rekomendasi_rekativasi_pbi_jks.petugas_pbijk')
                ->leftjoin('users', 'users.id', '=', 'rekomendasi_rekativasi_pbi_jks.petugas_pbijk')
                ->select('rekomendasi_rekativasi_pbi_jks.*', 'b.name_village', 'd.name_districts','users.name')
                // ->selectRaw('IFNULL(r.name,"") name')
                ->where('rekomendasi_rekativasi_pbi_jks.id_kabkot_pbijk', '=', $user_wilayah->kota_id)
                ->whereIn('rekomendasi_rekativasi_pbi_jks.status_aksi_pbijk', ['Teruskan','Kembalikan'])
                ->where('rekomendasi_rekativasi_pbi_jks.petugas_pbijk', '<>', $user_id)
                ->whereRaw("(SELECT COUNT(l.id) FROM log_pbijk as l WHERE l.id_trx_pbijk = rekomendasi_rekativasi_pbi_jks.id AND l.created_by_pbijk = '".$user_id."') > 0 ")
                ->where('rekomendasi_rekativasi_pbi_jks.no_pendaftaran_pbijk', 'like', "%$search%");
            }
        }else{
            if ($request->has('search') && !empty($request->search['value'])) {
                $search = $request->search['value'];
                $query = DB::table('rekomendasi_rekativasi_pbi_jks')
                ->leftjoin('indonesia_districts as d', 'd.code', '=', 'rekomendasi_rekativasi_pbi_jks.id_kecamatan_pbijk')
                ->leftjoin('indonesia_villages as b', 'b.code', '=', 'rekomendasi_rekativasi_pbi_jks.id_kelurahan_pbijk')
                // ->leftjoin('roles as r', 'r.id', '=', 'rekomendasi_rekativasi_pbi_jks.petugas_pbijk')
                ->leftjoin('users', 'users.id', '=', 'rekomendasi_rekativasi_pbi_jks.petugas_pbijk')
                ->select('rekomendasi_rekativasi_pbi_jks.*', 'b.name_village', 'd.name_districts','users.name')
                // ->selectRaw('IFNULL(r.name,"") name')
                ->where('rekomendasi_rekativasi_pbi_jks.id_kecamatan_pbijk', '=', $user_wilayah->kecamatan_id)
                ->whereIn('rekomendasi_rekativasi_pbi_jks.status_aksi_pbijk', ['Teruskan','Kembalikan'])
                ->where('rekomendasi_rekativasi_pbi_jks.petugas_pbijk', '<>', $user_id)
                ->whereRaw("(SELECT COUNT(l.id) FROM log_pbijk as l WHERE l.id_trx_pbijk = rekomendasi_rekativasi_pbi_jks.id AND l.created_by_pbijk = '".$user_id."') > 0 ")
                ->where('rekomendasi_rekativasi_pbi_jks.no_pendaftaran_pbijk', 'like', "%$search%");
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
            // Get paginated data
       //Add paginate
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
            'recordsTotal' => rekomendasi_rekativasi_pbi_jk::count(),
            'recordsFiltered' => $total_filtered_items,
            'data' => $data,
        ]);
    }

    public function selesai(Request $request)
    {
        $user_name = Auth::user()->name;
        $query = DB::table('rekomendasi_rekativasi_pbi_jks')
            ->join('users', 'users.id', '=', 'rekomendasi_rekativasi_pbi_jks.petugas_pbijk')
            ->join('log_pbijk', 'log_pbijk.id_trx_pbijk', '=', 'rekomendasi_rekativasi_pbi_jks.id')
            ->join('model_has_roles', 'model_has_roles.model_id', '=', 'rekomendasi_rekativasi_pbi_jks.tujuan_pbijk')
            ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_rekativasi_pbi_jks.id_kelurahan_pbijk')
            ->select('rekomendasi_rekativasi_pbi_jks.*', 'b.name_village');
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

        if ($user_wilayah->name == 'Front Office Kelurahan') {
            //  dd($user_wilayah->role_id);
            $query = DB::table('rekomendasi_rekativasi_pbi_jks')
                ->join('users', 'users.id', '=', 'rekomendasi_rekativasi_pbi_jks.petugas_pbijk')
                // ->join('log_pbijk', 'log_pbijk.id_trx_pbijk', '=', 'rekomendasi_rekativasi_pbi_jks.id')
                ->join('indonesia_villages', 'indonesia_villages.code', '=', 'rekomendasi_rekativasi_pbi_jks.id_kelurahan_pbijk')
                // ->join('roles as r', 'r.id', '=', 'rekomendasi_rekativasi_pbi_jks.tujuan_pbijk')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_rekativasi_pbi_jks.id_kecamatan_pbijk')
                ->select('rekomendasi_rekativasi_pbi_jks.*', 'd.name_districts','indonesia_villages.name_village','users.name')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_rekativasi_pbi_jks.id_kelurahan_pbijk', $user_wilayah->kelurahan_id)
                        // ->where('rekomendasi_rekativasi_pbi_jks.tujuan_pbijk', '=', $user_wilayah->role_id)
                        // ->where('log_pbijk.created_by_pbijk', '!=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_rekativasi_pbi_jks.status_aksi_pbijk', '=', 'Tolak')
                                ->orWhere('rekomendasi_rekativasi_pbi_jks.status_aksi_pbijk', '=', 'Selesai');
                        });
                })->distinct();
        }elseif ($user_wilayah->name == 'Back Ofiice kelurahan') {
            //  dd($user_wilayah->role_id);
            $query = DB::table('rekomendasi_rekativasi_pbi_jks')
                ->join('users', 'users.id', '=', 'rekomendasi_rekativasi_pbi_jks.petugas_pbijk')
                // ->join('log_pbijk', 'log_pbijk.id_trx_pbijk', '=', 'rekomendasi_rekativasi_pbi_jks.id')
                ->join('indonesia_villages', 'indonesia_villages.code', '=', 'rekomendasi_rekativasi_pbi_jks.id_kelurahan_pbijk')
                // ->join('roles as r', 'r.id', '=', 'rekomendasi_rekativasi_pbi_jks.tujuan_pbijk')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_rekativasi_pbi_jks.id_kecamatan_pbijk')
                ->select('rekomendasi_rekativasi_pbi_jks.*', 'd.name_districts','indonesia_villages.name_village','users.name')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_rekativasi_pbi_jks.id_kelurahan_pbijk', $user_wilayah->kelurahan_id)
                        // ->where('rekomendasi_rekativasi_pbi_jks.tujuan_pbijk', '=', $user_wilayah->role_id)
                        // ->where('log_pbijk.created_by_pbijk', '!=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_rekativasi_pbi_jks.status_aksi_pbijk', '=', 'Tolak')
                                ->orWhere('rekomendasi_rekativasi_pbi_jks.status_aksi_pbijk', '=', 'Selesai');
                        });
                })->distinct();
        }  elseif ($user_wilayah->name == 'Front Office kota') {
            //  dd($user_wilayah->role_id);
            $query = DB::table('rekomendasi_rekativasi_pbi_jks')
                ->join('users', 'users.id', '=', 'rekomendasi_rekativasi_pbi_jks.petugas_pbijk')
                // ->join('log_pbijk', 'log_pbijk.id_trx_pbijk', '=', 'rekomendasi_rekativasi_pbi_jks.id')
                ->join('indonesia_villages', 'indonesia_villages.code', '=', 'rekomendasi_rekativasi_pbi_jks.id_kelurahan_pbijk')
                // ->join('roles as r', 'r.id', '=', 'rekomendasi_rekativasi_pbi_jks.tujuan_pbijk')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_rekativasi_pbi_jks.id_kecamatan_pbijk')
                ->select('rekomendasi_rekativasi_pbi_jks.*', 'd.name_districts','indonesia_villages.name_village','users.name')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_rekativasi_pbi_jks.id_kabkot_pbijk', $user_wilayah->kota_id)
                        // ->where('rekomendasi_rekativasi_pbi_jks.tujuan_pbijk', '=', $user_wilayah->role_id)
                        // ->where('log_pbijk.created_by_pbijk', '!=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_rekativasi_pbi_jks.status_aksi_pbijk', '=', 'Tolak')
                                ->orWhere('rekomendasi_rekativasi_pbi_jks.status_aksi_pbijk', '=', 'Selesai');
                        });
                })->distinct();
        } elseif ($user_wilayah->name == 'Back Ofiice Kota') {
            // dd($user_wilayah);
            $query = DB::table('rekomendasi_rekativasi_pbi_jks')
            ->join('users', 'users.id', '=', 'rekomendasi_rekativasi_pbi_jks.petugas_pbijk')
            // ->join('log_pbijk', 'log_pbijk.id_trx_pbijk', '=', 'rekomendasi_rekativasi_pbi_jks.id')
            ->join('indonesia_villages', 'indonesia_villages.code', '=', 'rekomendasi_rekativasi_pbi_jks.id_kelurahan_pbijk')
            // ->join('roles as r', 'r.id', '=', 'rekomendasi_rekativasi_pbi_jks.tujuan_pbijk')
            ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_rekativasi_pbi_jks.id_kecamatan_pbijk')
            ->select('rekomendasi_rekativasi_pbi_jks.*', 'd.name_districts','indonesia_villages.name_village','users.name')
            ->orWhere(function ($query) use ($user_wilayah) {
                $query->where('rekomendasi_rekativasi_pbi_jks.id_kabkot_pbijk', $user_wilayah->kota_id)
                    // ->where('rekomendasi_rekativasi_pbi_jks.tujuan_pbijk', '=', $user_wilayah->role_id)
                    // ->where('log_pbijk.created_by_pbijk', '!=', $user_wilayah->model_id)
                    ->where(function ($query) {
                        $query->where('rekomendasi_rekativasi_pbi_jks.status_aksi_pbijk', '=', 'Tolak')
                            ->orWhere('rekomendasi_rekativasi_pbi_jks.status_aksi_pbijk', '=', 'Selesai');
                    });
            })->distinct();
            // dd($query); 
        } elseif ($user_wilayah->name == 'kepala bidang') {
            // dd($user_wilayah);
            $query = DB::table('rekomendasi_rekativasi_pbi_jks')
            ->join('users', 'users.id', '=', 'rekomendasi_rekativasi_pbi_jks.petugas_pbijk')
            // ->join('log_pbijk', 'log_pbijk.id_trx_pbijk', '=', 'rekomendasi_rekativasi_pbi_jks.id')
            ->join('indonesia_villages', 'indonesia_villages.code', '=', 'rekomendasi_rekativasi_pbi_jks.id_kelurahan_pbijk')
            // ->join('roles as r', 'r.id', '=', 'rekomendasi_rekativasi_pbi_jks.tujuan_pbijk')
            ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_rekativasi_pbi_jks.id_kecamatan_pbijk')
            ->select('rekomendasi_rekativasi_pbi_jks.*', 'd.name_districts','indonesia_villages.name_village','users.name')
            ->orWhere(function ($query) use ($user_wilayah) {
                $query->where('rekomendasi_rekativasi_pbi_jks.id_kabkot_pbijk', $user_wilayah->kota_id)
                    // ->where('rekomendasi_rekativasi_pbi_jks.tujuan_pbijk', '=', $user_wilayah->role_id)
                    // ->where('log_pbijk.created_by_pbijk', '!=', $user_wilayah->model_id)
                    ->where(function ($query) {
                        $query->where('rekomendasi_rekativasi_pbi_jks.status_aksi_pbijk', '=', 'Tolak')
                            ->orWhere('rekomendasi_rekativasi_pbi_jks.status_aksi_pbijk', '=', 'Selesai');
                    });
            })->distinct();
        } elseif ($user_wilayah->name == 'supervisor') {
            // dd($user_wilayah->role_id);
            $query = DB::table('rekomendasi_rekativasi_pbi_jks')
                ->join('users', 'users.id', '=', 'rekomendasi_rekativasi_pbi_jks.petugas_pbijk')
                ->join('log_pbijk', 'log_pbijk.id_trx_pbijk', '=', 'rekomendasi_rekativasi_pbi_jks.id')
                ->join('indonesia_villages', 'indonesia_villages.code', '=', 'rekomendasi_rekativasi_pbi_jks.id_kelurahan_pbijk')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_rekativasi_pbi_jks.id_kecamatan_pbijk')
                ->select('rekomendasi_rekativasi_pbi_jks.*', 'd.name_districts', 'indonesia_villages.name_village', 'users.name')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_rekativasi_pbi_jks.id_kelurahan_pbijk', $user_wilayah->kelurahan_id)
                        // ->where('log_pbijk.tujuan_pbijk', '!=', $user_wilayah->role_id)
                        // ->where('log_pbijk.created_by_pbijk', '=', auth::user()->id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_rekativasi_pbi_jks.status_aksi_pbijk', '=', 'Tolak')
                                ->orWhere('rekomendasi_rekativasi_pbi_jks.status_aksi_pbijk', '=', 'Selesai');
                        });
                })->distinct();
        } elseif ($user_wilayah->name == 'KepalaDinas') {
            // dd($user_wilayah);
            $query = DB::table('rekomendasi_rekativasi_pbi_jks')
                ->join('users', 'users.id', '=', 'rekomendasi_rekativasi_pbi_jks.petugas_pbijk')
                ->join('log_pbijk', 'log_pbijk.id_trx_pbijk', '=', 'rekomendasi_rekativasi_pbi_jks.id')
                // ->join('model_has_roles', 'model_has_roles.model_id', '=', 'rekomendasi_rekativasi_pbi_jks.tujuan_pbijk')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_rekativasi_pbi_jks.id_kecamatan_pbijk')
                ->select('rekomendasi_rekativasi_pbi_jks.*', 'd.name_districts', 'indonesia_villages.name_village', 'log_pbijk.tujuan_pbijk', 'log_pbijk.petugas_pbijk')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_rekativasi_pbi_jks.id_kelurahan_pbijk', $user_wilayah->kelurahan_id)
                        ->where('log_pbijk.tujuan_pbijk', '=', $user_wilayah->role_id)
                        ->where('log_pbijk.petugas_pbijk', '=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_rekativasi_pbi_jks.status_aksi_pbijk', '=', 'Tolak')
                                ->orWhere('rekomendasi_rekativasi_pbi_jks.status_aksi_pbijk', '=', 'Selesai');
                        });
                });
        }

        if ($user_wilayah->name == 'Front Office Kelurahan' || $user_wilayah->name == 'Back Ofiice kelurahan'|| $user_wilayah->name == 'fasilitator') {
            if ($request->has('search') && !empty($request->search['value'])) {
                $search = $request->search['value'];
                $query = DB::table('rekomendasi_rekativasi_pbi_jks')
                ->join('users', 'users.id', '=', 'rekomendasi_rekativasi_pbi_jks.petugas_pbijk')
                // ->join('log_pbijk', 'log_pbijk.id_trx_pbijk', '=', 'rekomendasi_rekativasi_pbi_jks.id')
                ->join('indonesia_villages', 'indonesia_villages.code', '=', 'rekomendasi_rekativasi_pbi_jks.id_kelurahan_pbijk')
                // ->join('roles as r', 'r.id', '=', 'rekomendasi_rekativasi_pbi_jks.tujuan_pbijk')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_rekativasi_pbi_jks.id_kecamatan_pbijk')
                ->select('rekomendasi_rekativasi_pbi_jks.*', 'd.name_districts','indonesia_villages.name_village','users.name')
                ->orWhere(function ($query) use ($user_wilayah,$search) {
                    $query->where('rekomendasi_rekativasi_pbi_jks.id_kelurahan_pbijk', $user_wilayah->kelurahan_id)
                        // ->where('rekomendasi_rekativasi_pbi_jks.tujuan_pbijk', '=', $user_wilayah->role_id)
                        // ->where('log_pbijk.created_by_pbijk', '!=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_rekativasi_pbi_jks.status_aksi_pbijk', '=', 'Tolak')
                                ->orWhere('rekomendasi_rekativasi_pbi_jks.status_aksi_pbijk', '=', 'Selesai');
                        })
                        ->where('rekomendasi_rekativasi_pbi_jks.no_pendaftaran_pbijk', 'like', "%$search%");
                })->distinct();
            }
        }elseif($user_wilayah->name == 'Front Office Kota' || $user_wilayah->name == 'Back Ofiice Kota'|| $user_wilayah->name == 'KepalaDinas'||$user_wilayah->name == 'SekertarisDinas'||$user_wilayah->name == 'Supervisor') {
            if ($request->has('search') && !empty($request->search['value'])) {
                $search = $request->search['value'];
                $query = DB::table('rekomendasi_rekativasi_pbi_jks')
                ->join('users', 'users.id', '=', 'rekomendasi_rekativasi_pbi_jks.petugas_pbijk')
                // ->join('log_pbijk', 'log_pbijk.id_trx_pbijk', '=', 'rekomendasi_rekativasi_pbi_jks.id')
                ->join('indonesia_villages', 'indonesia_villages.code', '=', 'rekomendasi_rekativasi_pbi_jks.id_kelurahan_pbijk')
                // ->join('roles as r', 'r.id', '=', 'rekomendasi_rekativasi_pbi_jks.tujuan_pbijk')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_rekativasi_pbi_jks.id_kecamatan_pbijk')
                ->select('rekomendasi_rekativasi_pbi_jks.*', 'd.name_districts','indonesia_villages.name_village','users.name')
                ->orWhere(function ($query) use ($user_wilayah,$search) {
                    $query->where('rekomendasi_rekativasi_pbi_jks.id_kabkot_pbijk', $user_wilayah->kota_id)
                        // ->where('rekomendasi_rekativasi_pbi_jks.tujuan_pbijk', '=', $user_wilayah->role_id)
                        // ->where('log_pbijk.created_by_pbijk', '!=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_rekativasi_pbi_jks.status_aksi_pbijk', '=', 'Tolak')
                                ->orWhere('rekomendasi_rekativasi_pbi_jks.status_aksi_pbijk', '=', 'Selesai');
                        })
                        ->where('rekomendasi_rekativasi_pbi_jks.no_pendaftaran_pbijk', 'like', "%$search%");
                })->distinct();
            }
        }

        // Get total count of filtered items
        $total_filtered_items = $query->count();
        // Add ordering

        // Get paginated data
            // Get paginated data
       //Add paginate
        $start = $request->start;
        // dd($start);
        $length = $request->length;
        // dd($length);
        $query->offset($start)->limit($length);
        $data = $query->get();
        // dd($data);   
        // mengubah data JSON menjadi objek PHP
        // dd($data);
        // mengubah data JSON menjadi objek PHP

        return response()->json([
            'draw' => $request->input('draw'),
            'recordsTotal' => rekomendasi_rekativasi_pbi_jk::count(),
            'recordsFiltered' => $total_filtered_items,
            'data' => $data,
        ]);
    }
    public function getPetugaPbiJk($id)
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
                    ->leftJoin('rekomendasi_rekativasi_pbi_jks','rekomendasi_rekativasi_pbi_jks.createdby_pbijk','=','u.id')
                    ->where('mhr.model_type', '=', 'App\Models\User')
                    ->where('wilayahs.kota_id', '=',$wilayah->kota_id)
                    ->where('mhr.role_id', '=', $id)
                    // ->whereRaw("(SELECT COUNT(l.id) FROM users as l WHERE l.id = '".$id."') > 0 ")
                    // ->whereRaw("(SELECT COUNT(l.id) FROM users as l WHERE l.id = rekomendasi_rekativasi_pbi_jks.id AND l.id = '".$id."') > 0 ")
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
