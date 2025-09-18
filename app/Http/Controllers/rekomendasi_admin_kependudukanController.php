<?php

namespace App\Http\Controllers;

use App\Http\Requests\Createrekomendasi_admin_kependudukanRequest;
use App\Http\Requests\Updaterekomendasi_admin_kependudukanRequest;
use App\Http\Controllers\AppBaseController;
use App\Models\log_minkep;
use App\Models\pelapor;
use App\Models\Prelist;
use App\Models\rekomendasi_admin_kependudukan;
use App\Models\rekomendasi_pengangkatan_anak;
use App\Models\Roles;
use App\Repositories\rekomendasi_admin_kependudukanRepository;
use Illuminate\Http\Request;
use Flash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class rekomendasi_admin_kependudukanController extends AppBaseController
{
    /** @var rekomendasi_admin_kependudukanRepository $rekomendasiAdminKependudukanRepository*/
    private $rekomendasiAdminKependudukanRepository;

    public function __construct(rekomendasi_admin_kependudukanRepository $rekomendasiAdminKependudukanRepo)
    {
        $this->rekomendasiAdminKependudukanRepository = $rekomendasiAdminKependudukanRepo;
    }

    /**
     * Display a listing of the rekomendasi_admin_kependudukan.
     */
    public function index(Request $request)
    {
        $rekomendasiAdminKependudukans = $this->rekomendasiAdminKependudukanRepository->paginate(10);

        return view('rekomendasi_admin_kependudukans.index')
            ->with('rekomendasiAdminKependudukans', $rekomendasiAdminKependudukans);
    }
    public function FileAdminduk($id)
    {
        $adminduk = rekomendasi_admin_kependudukan::find($id);
        // dd($rehabsos);
        $getIdDtks = DB::table('rekomendasi_admin_kependudukans as w')->select(
            'w.*',
            'dtks.Id_DTKS'
        )
            ->leftjoin('dtks', 'dtks.Nik', '=', 'w.nik_minkep')
            // ->where('status_wilayah', '1')
            ->where('dtks.Nik', $adminduk->nik_minkep)->first();
        // dd($getIdDtks);
        
        if (!is_null($getIdDtks) && !is_null($getIdDtks->Id_DTKS)) {
            $data_dtks = $getIdDtks->Id_DTKS;
        } else {
            $data_dtks = '-';
        }

       $date = Carbon::parse($adminduk->tgl_lahir_minkep)->locale('id');

       $date->settings(['formatFunction' => 'translatedFormat']);

       $tanggal = $date->format('j F Y ');


       // dd($tanggal);
        $pdf = PDF::loadHtml(view('rekomendasi_admin_kependudukans.file_permohonan',compact('adminduk','tanggal','data_dtks')));
        $pdf->setPaper('F4', 'portrait');
        $filename = 'File Permohonan' . $adminduk->nama . '.pdf';
        return $pdf->stream($filename);
    }

    /**
     * Show the form for creating a new rekomendasi_admin_kependudukan.
     */
    public function create()
    {
        $v = rekomendasi_admin_kependudukan::latest()->first();
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
                ->where('name', 'Back Ofiice Kelurahan')
                // ->where('name', 'supervisor')
                ->orWhere('name', 'supervisor')
                ->get();
        }
        $checkroles = Roles::where('name', 'Front Office kota')
            ->orWhere('name', 'SekertarisDinas')
            ->orWhere('name', 'KepalaDinas')
            ->get();
        return view('rekomendasi_admin_kependudukans.create', compact('kecamatans', 'wilayah', 'roleid', 'checkuserrole', 'alur'));
        // return view('rekomendasi_minkep.create');
    }
    /**
     * Store a newly created rekomendasi_admin_kependudukan in storage.
     */


    public function cekIdMinkep(Request $request, $Nik)
    {
        $found = false;
        $table2 = DB::table('dtks')->where('nik_minkep', $Nik)->first();
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
        // dd($request->get('status_dtks_minkep'));
        if ($request->get('status_alur_minkep') != 'Draft') {
            // jika status_alur_minkep sama dengan Draft akan nmasuk kondisi sini
            if ($request->get('status_dtks_minkep') == 'Terdaftar') {

                // jika status_dtks_minkep sama dengan terdaftar akan nmasuk kondisi sini
                $data = new rekomendasi_admin_kependudukan();
                $files = [
                    'file_ktp_terlapor_minkep' => 'adminkependudukan/ktp/',
                    'file_kk_terlapor_minkep' => 'adminkependudukan/kk/',
                    'file_keterangan_dtks_minkep' => 'adminkependudukan/strukturorganisasi/',
                    'file_pendukung_minkep' => 'adminkependudukan/wajibpajak/'
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


                // $data->id_alur = $request->get('id_alur_minkep');
                $data->no_pendaftaran_minkep = mt_rand(100, 1000);
                $data->id_provinsi_minkep = $request->get('id_provinsi_minkep');
                $data->id_kabkot_minkep = $request->get('id_kabkot_minkep');
                $data->id_kecamatan_minkep = $request->get('id_kecamatan_minkep');
                $data->id_kelurahan_minkep = $request->get('id_kelurahan_minkep');
                $data->jenis_pelapor_minkep = $request->get('jenis_pelapor_minkep');
                $data->ada_nik_minkep = $request->get('ada_nik_minkep');
                $data->nik_minkep = $request->get('nik_minkep');
                $data->no_kk_minkep = $request->get('no_kk_minkep');
                // $data->no_kis = $request->get('no_kis');
                $data->nama_minkep = $request->get('nama_minkep');
                $data->tgl_lahir_minkep = $request->get('tgl_lahir_minkep');
                $data->tempat_lahir_minkep = $request->get('tempat_lahir_minkep');
                $data->jenis_kelamin_minkep = $request->get('jenis_kelamin_minkep');
                $data->telp_minkep = $request->get('telp_minkep');
                $data->alamat_minkep = $request->get('alamat_minkep');
                $data->status_dtks_minkep = $request->get('status_dtks_minkep');
                $data->catatan_minkep = $request->get('catatan_minkep');
                $data->tujuan_minkep = $request->get('tujuan_minkep');
                $data->status_aksi_minkep = $request->get('status_aksi_minkep');
                $data->petugas_minkep = $request->get('petugas_minkep');
                $data->createdby_minkep = Auth::user()->id;
                $data->updatedby_minkep = Auth::user()->id;
                // dd($data->file_pendukung_minkep);
                $data->save();
                $logpengaduan = new log_minkep();
                $logpengaduan['id_trx_minkep'] = $data->id;
                $logpengaduan['id_alur_minkep'] = $request->get('status_aksi_minkep');
                $logpengaduan['petugas_minkep'] = $request->get('petugas_minkep');
                $logpengaduan['catatan_minkep']  = $request->get('catatan_minkep');
                $logpengaduan['file_permohonan_minkep'] = $data->file_pendukung_minkep;
                $logpengaduan['tujuan_minkep'] = $request->get('tujuan_minkep');
                $logpengaduan['created_by_minkep'] = Auth::user()->id;
                $logpengaduan['updated_by_minkep'] = Auth::user()->id;

                $logpengaduan->save();
                if ($request->get('jenis_pelapor_minkep') == 'Orang Lain') {
                    // dd($request->get('jenis_pelapor_minkep'));
                    $pelapor = new pelapor();
                    $pelapor['id_menu'] = '01';
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
                    $pelapor['id_menu'] = '01';
                    $pelapor['id_form'] = $data->id;
                    $pelapor['jenis_peelaporan'] = $request->get('jenis_pelapor_minkep');
                    $pelapor['nama_pelapor']  =  $request->get('nama_minkep');
                    $pelapor['ada_nik_pelapor'] = $request->get('ada_nik_minkep');
                    $pelapor['nik_pelapor'] = $request->get('nik_minkep');
                    $pelapor['status_dtks_pelapor'] = $request->get('status_dtks_minkep');
                    $pelapor['tempat_lahir_pelapor'] = $request->get('tempat_lahir_minkep');
                    $pelapor['tanggal_lahir_pelapor'] = $request->get('tgl_lahir_minkep');
                    $pelapor['jenis_kelamin'] = $request->get('jenis_kelamin_minkep');
                    // $pelapor['nama_pelapor']  = $request->get('nama_minkep');
                    $pelapor['telepon_pelapor'] = $request->get('telp_minkep');
                    $pelapor['alamat_pelapor'] = $request->get('alamat_minkep');
                    $pelapor['createdby_pelapor'] = Auth::user()->id;
                    $pelapor['updatedby_pelapor'] = Auth::user()->id;
    
                    $pelapor->save();
                }

                return redirect('rekomendasi_admin_kependudukans')->withSuccess('Data Rekomendasi Berhasil Ditambahkan');
            } else {
                // jika status_dtks_minkep sama dengan terdaftar akan nmasuk kondisi sini
                $cek = Prelist::where('nik', '=', $request->get('nik_minkep'))->exists();
                if (!$cek) {
                    // jika nik tidak ada nik akan masuk ke sini
                    $data = new Prelist;
                    $data['id_provinsi'] = $request->get('id_provinsi_minkep');
                    $data['id_kabkot'] = $request->get('id_kabkot_minkep');
                    $data['id_kecamatan'] = $request->get('id_kecamatan_minkep');
                    $data['id_kelurahan'] = $request->get('id_kelurahan_minkep');
                    $data['nik'] = $request->get('nik_minkep');
                    $data['no_kk'] = $request->get('no_kk_minkep');
                    // $data['no_kis'] = $request->get('no_kis_minkep');
                    $data['nama'] = $request->get('nama_minkep');
                    $data['tgl_lahir'] = $request->get('tgl_lahir_minkep');
                    // $data['alamat'] = $request->get('alamat_minkep');
                    $data['telp'] = $request->get('telpon_minkep');
                    $data['email'] = $request->get('email_minkep');
                    $data['status_data'] = 'prelistdtks';

                    $data->save();
                    $data = new rekomendasi_admin_kependudukan();
                    $files = [
                        'file_ktp_terlapor_minkep' => 'adminkependudukan/ktp/',
                        'file_kk_terlapor_minkep' => 'adminkependudukan/kk/',
                        'file_keterangan_dtks_minkep' => 'adminkependudukan/strukturorganisasi/',
                        'file_pendukung_minkep' => 'adminkependudukan/wajibpajak/'
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

                    // $data->id_alur = $request->get('id_alur_minkep');
                    $data->no_pendaftaran_minkep = mt_rand(100, 1000);
                    $data->id_provinsi_minkep = $request->get('id_provinsi_minkep');
                    $data->id_kabkot_minkep = $request->get('id_kabkot_minkep');
                    $data->id_kecamatan_minkep = $request->get('id_kecamatan_minkep');
                    $data->id_kelurahan_minkep = $request->get('id_kelurahan_minkep');
                    $data->jenis_pelapor_minkep = $request->get('jenis_pelapor_minkep');
                    $data->ada_nik_minkep = $request->get('ada_nik_minkep');
                    $data->nik_minkep = $request->get('nik_minkep');
                    $data->no_kk_minkep = $request->get('no_kk_minkep');
                    // $data->no_kis = $request->get('no_kis');
                    $data->nama_minkep = $request->get('nama_minkep');
                    $data->tgl_lahir_minkep = $request->get('tgl_lahir_minkep');
                    $data->tempat_lahir_minkep = $request->get('tempat_lahir_minkep');
                    $data->jenis_kelamin_minkep = $request->get('jenis_kelamin_minkep');
                    $data->telp_minkep = $request->get('telp_minkep');
                    $data->alamat_minkep = $request->get('alamat_minkep');
                    $data->status_dtks_minkep = $request->get('status_dtks_minkep');
                    $data->catatan_minkep = $request->get('catatan_minkep');
                    $data->tujuan_minkep = $request->get('tujuan_minkep');
                    $data->status_aksi_minkep = $request->get('status_aksi_minkep');
                    $data->petugas_minkep = $request->get('petugas_minkep');
                    $data->createdby_minkep = Auth::user()->id;
                    $data->updatedby_minkep = Auth::user()->id;
                    // dd($data);
                    $data->save();
                    $logpengaduan = new log_minkep();
                    $logpengaduan['id_trx_minkep'] = $data->id;
                    $logpengaduan['id_alur_minkep'] = $request->get('status_aksi_minkep');
                    $logpengaduan['petugas_minkep'] = $request->get('petugas_minkep');
                    $logpengaduan['catatan_minkep']  = $request->get('catatan_minkep');
                    $logpengaduan['file_permohonan_minkep'] = $data->file_pendukung_minkep;
                    $logpengaduan['tujuan_minkep'] = $request->get('tujuan_minkep');
                    $logpengaduan['created_by_minkep'] = Auth::user()->id;
                    $logpengaduan['updated_by_minkep'] = Auth::user()->id;

                    $logpengaduan->save();
                    return redirect('rekomendasi_admin_kependudukans')->withSuccess('Data Rekomendasi Berhasil Ditambahkan');
                    if ($request->get('jenis_pelapor_minkep') == 'Orang Lain') {
                        $pelapor = new pelapor();
                        $pelapor['id_menu'] = '01';
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
                        $pelapor['id_menu'] = '01';
                        $pelapor['id_form'] = $data->id;
                        $pelapor['jenis_peelaporan'] = $request->get('jenis_pelapor_minkep');
                        $pelapor['nama_pelapor']  =  $request->get('nama_minkep');
                        $pelapor['ada_nik_pelapor'] = $request->get('ada_nik_minkep');
                        $pelapor['nik_pelapor'] = $request->get('nik_minkep');
                        $pelapor['status_dtks_pelapor'] = $request->get('status_dtks_minkep');
                        $pelapor['tempat_lahir_pelapor'] = $request->get('tempat_lahir_minkep');
                        $pelapor['tanggal_lahir_pelapor'] = $request->get('tgl_lahir_minkep');
                        $pelapor['jenis_kelamin'] = $request->get('jenis_kelamin_minkep');
                        // $pelapor['nama_pelapor']  = $request->get('nama_minkep');
                        $pelapor['telepon_pelapor'] = $request->get('telp_minkep');
                        $pelapor['alamat_pelapor'] = $request->get('alamat_minkep');
                        $pelapor['createdby_pelapor'] = Auth::user()->id;
                        $pelapor['updatedby_pelapor'] = Auth::user()->id;
        
                        $pelapor->save();
                    }
                } else {
                    //jika nik ada di prelist akan masuk ke sini
                    $data = new rekomendasi_admin_kependudukan();
                    $files = [
                        'file_ktp_terlapor_minkep' => 'adminkependudukan/ktp/',
                        'file_kk_terlapor_minkep' => 'adminkependudukan/kk/',
                        'file_keterangan_dtks_minkep' => 'adminkependudukan/strukturorganisasi/',
                        'file_pendukung_minkep' => 'adminkependudukan/wajibpajak/'
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

                    // $data->id_alur = $request->get('id_alur_minkep');
                    $data->no_pendaftaran_minkep = mt_rand(100, 1000);
                    $data->id_provinsi_minkep = $request->get('id_provinsi_minkep');
                    $data->id_kabkot_minkep = $request->get('id_kabkot_minkep');
                    $data->id_kecamatan_minkep = $request->get('id_kecamatan_minkep');
                    $data->id_kelurahan_minkep = $request->get('id_kelurahan_minkep');
                    $data->jenis_pelapor_minkep = $request->get('jenis_pelapor_minkep');
                    $data->ada_nik_minkep = $request->get('ada_nik_minkep');
                    $data->nik_minkep = $request->get('nik_minkep');
                    $data->no_kk_minkep = $request->get('no_kk_minkep');
                    // $data->no_kis = $request->get('no_kis');
                    $data->nama_minkep = $request->get('nama_minkep');
                    $data->tgl_lahir_minkep = $request->get('tgl_lahir_minkep');
                    $data->tempat_lahir_minkep = $request->get('tempat_lahir_minkep');
                    $data->jenis_kelamin_minkep = $request->get('jenis_kelamin_minkep');
                    $data->telp_minkep = $request->get('telp_minkep');
                    $data->alamat_minkep = $request->get('alamat_minkep');
                    $data->status_dtks_minkep = $request->get('status_dtks_minkep');
                    $data->catatan_minkep = $request->get('catatan_minkep');
                    $data->tujuan_minkep = $request->get('tujuan_minkep');
                    $data->status_aksi_minkep = $request->get('status_aksi_minkep');
                    $data->petugas_minkep = $request->get('petugas_minkep');
                    $data->createdby_minkep = Auth::user()->id;
                    $data->updatedby_minkep = Auth::user()->id;
                    // dd($data);
                    $data->save();
                    $logpengaduan = new log_minkep();
                    $logpengaduan['id_trx_minkep'] = $data->id;
                    $logpengaduan['id_alur_minkep'] = $request->get('status_aksi_minkep');
                    $logpengaduan['petugas_minkep'] = $request->get('petugas_minkep');
                    $logpengaduan['catatan_minkep']  = $request->get('catatan_minkep');
                    $logpengaduan['file_permohonan_minkep'] = $data->file_pendukung_minkep;
                    $logpengaduan['tujuan_minkep'] = $request->get('tujuan_minkep');
                    $logpengaduan['created_by_minkep'] = Auth::user()->id;
                    $logpengaduan['updated_by_minkep'] = Auth::user()->id;

                    $logpengaduan->save();
                    return redirect('rekomendasi_admin_kependudukans')->withSuccess('Data Rekomendasi Berhasil Ditambahkan');
                    if ($request->get('jenis_pelapor_minkep') == 'Orang Lain') {
                        $pelapor = new pelapor();
                        $pelapor['id_menu'] = '01';
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
                        $pelapor['id_menu'] = '01';
                        $pelapor['id_form'] = $data->id;
                        $pelapor['jenis_peelaporan'] = $request->get('jenis_pelapor_minkep');
                        $pelapor['nama_pelapor']  =  $request->get('nama_minkep');
                        $pelapor['ada_nik_pelapor'] = $request->get('ada_nik_minkep');
                        $pelapor['nik_pelapor'] = $request->get('nik_minkep');
                        $pelapor['status_dtks_pelapor'] = $request->get('status_dtks_minkep');
                        $pelapor['tempat_lahir_pelapor'] = $request->get('tempat_lahir_minkep');
                        $pelapor['tanggal_lahir_pelapor'] = $request->get('tgl_lahir_minkep');
                        $pelapor['jenis_kelamin'] = $request->get('jenis_kelamin_minkep');
                        // $pelapor['nama_pelapor']  = $request->get('nama_minkep');
                        $pelapor['telepon_pelapor'] = $request->get('telp_minkep');
                        $pelapor['alamat_pelapor'] = $request->get('alamat_minkep');
                        $pelapor['createdby_pelapor'] = Auth::user()->id;
                        $pelapor['updatedby_pelapor'] = Auth::user()->id;
        
                        $pelapor->save();
                    }
                }
            }
        } else {
            //jika status file adalah ini akan masuk ke sini
            $data = new rekomendasi_admin_kependudukan();
            $files = [
                'file_ktp_terlapor_minkep' => 'adminkependudukan/ktp/',
                'file_kk_terlapor_minkep' => 'adminkependudukan/kk/',
                'file_keterangan_dtks_minkep' => 'adminkependudukan/strukturorganisasi/',
                'file_pendukung_minkep' => 'adminkependudukan/wajibpajak/'
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

            // $data->id_alur = $request->get('id_alur_minkep');
            $data->no_pendaftaran_minkep = mt_rand(100, 1000);
            $data->id_provinsi_minkep = $request->get('id_provinsi_minkep');
            $data->id_kabkot_minkep = $request->get('id_kabkot_minkep');
            $data->id_kecamatan_minkep = $request->get('id_kecamatan_minkep');
            $data->id_kelurahan_minkep = $request->get('id_kelurahan_minkep');
            $data->jenis_pelapor_minkep = $request->get('jenis_pelapor_minkep');
            $data->ada_nik_minkep = $request->get('ada_nik_minkep');
            $data->nik_minkep = $request->get('nik_minkep');
            $data->no_kk_minkep = $request->get('no_kk_minkep');
            // $data->no_kis = $request->get('no_kis');
            $data->nama_minkep = $request->get('nama_minkep');
            $data->tgl_lahir_minkep = $request->get('tgl_lahir_minkep');
            $data->tempat_lahir_minkep = $request->get('tempat_lahir_minkep');
            $data->jenis_kelamin_minkep = $request->get('jenis_kelamin_minkep');
            $data->telp_minkep = $request->get('telp_minkep');
            $data->status_dtks_minkep = $request->get('status_dtks_minkep');
            $data->catatan_minkep = $request->get('catatan_minkep');
            $data->tujuan_minkep = $request->get('tujuan_minkep');
            $data->status_aksi_minkep = $request->get('status_aksi_minkep');
            $data->petugas_minkep = $request->get('petugas_minkep');
            $data->createdby_minkep = Auth::user()->id;
            $data->updatedby_minkep = Auth::user()->id;
            // dd($data);
            $data->save();
            return redirect('rekomendasi_admin_kependudukans')->withWarning('NIK Tidak Tersedia Data Disimpan sebagai Draft');
            $pelapor = new pelapor();
            if ($request->get('jenis_pelapor_minkep') == 'Orang Lain') {
                $pelapor = new pelapor();
                $pelapor['id_menu'] = '01';
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
                $pelapor['id_menu'] = '01';
                $pelapor['id_form'] = $data->id;
                $pelapor['jenis_peelaporan'] = $request->get('jenis_pelapor_minkep');
                $pelapor['nama_pelapor']  =  $request->get('nama_minkep');
                $pelapor['ada_nik_pelapor'] = $request->get('ada_nik_minkep');
                $pelapor['nik_pelapor'] = $request->get('nik_minkep');
                $pelapor['status_dtks_pelapor'] = $request->get('status_dtks_minkep');
                $pelapor['tempat_lahir_pelapor'] = $request->get('tempat_lahir_minkep');
                $pelapor['tanggal_lahir_pelapor'] = $request->get('tgl_lahir_minkep');
                $pelapor['jenis_kelamin'] = $request->get('jenis_kelamin_minkep');
                // $pelapor['nama_pelapor']  = $request->get('nama_minkep');
                $pelapor['telepon_pelapor'] = $request->get('telp_minkep');
                $pelapor['alamat_pelapor'] = $request->get('alamat_minkep');
                $pelapor['createdby_pelapor'] = Auth::user()->id;
                $pelapor['updatedby_pelapor'] = Auth::user()->id;

                $pelapor->save();
            }
        }
    }


    /**
     * Display the specified rekomendasi_admin_kependudukan.
     */
    public function show($id)
    {
        $userid = Auth::user()->id;
        // $rekomendasiAdminKependudukan = $this->rekomendasiAdminKependudukanRepository->find((int) $id);
        $rekomendasiAdminKependudukanPelapor = DB::table('rekomendasi_admin_kependudukans')
            ->join('pelapor', function ($join) use ($id) {
                $join->on('rekomendasi_admin_kependudukans.id', '=', 'pelapor.id_form')
                    ->where('rekomendasi_admin_kependudukans.id', '=', $id);
            })
            ->select('rekomendasi_admin_kependudukans.*', 'pelapor.*')
            ->where('pelapor.id_menu', '01')
            ->where('pelapor.id_form', $id)
            ->first();
        // dd($rekomendasiAdminKependudukanPelapor);

        $rekomendasiAdminKependudukan = DB::table('rekomendasi_admin_kependudukans as w')->select(
            'w.*',
            'rls.name as name_roles',
            'usr.name',
            'prov.name_prov',
            'kota.name_cities',
            'kecamatan.name_districts',
            'b.name_village',
        )
            ->leftjoin('roles as rls', 'rls.id', '=', 'w.tujuan_minkep')
            ->leftjoin('users as usr', 'usr.id', '=', 'w.petugas_minkep')
            ->leftjoin('indonesia_provinces as prov', 'prov.code', '=', 'w.id_provinsi_minkep')
            ->leftjoin('indonesia_cities as kota', 'kota.code', '=', 'w.id_kabkot_minkep')
            ->leftjoin('indonesia_districts as kecamatan', 'kecamatan.code', '=', 'w.id_kecamatan_minkep')
            ->leftjoin('indonesia_villages as b', 'b.code', '=', 'w.id_kelurahan_minkep')
            ->where('w.id', $id)->first();

        // dd($rekomendasiAdminKependudukan);
        $data = DB::table('pelapor')
            ->join('rekomendasi_admin_kependudukans', 'pelapor.id_form', '=', 'rekomendasi_admin_kependudukans.id')
            ->select('pelapor.*', 'rekomendasi_admin_kependudukans.*')
            ->get();

        $log_minkep = DB::table('log_minkep as w')->select(
            'w.*',
            'rls.name as name_update',
            'usr.name',
            'roles.name as name_roles',

        )
            ->leftjoin('roles', 'roles.id', '=', 'w.tujuan_minkep')
            ->leftjoin('users as rls', 'rls.id', '=', 'w.updated_by_minkep')
            ->leftjoin('users as usr', 'usr.id', '=', 'w.created_by_minkep')
            ->where('w.id_trx_minkep', $id)->get();
    
        // dd($log_minkep);    

        return view('rekomendasi_admin_kependudukans.show', compact('rekomendasiAdminKependudukan','log_minkep', 'rekomendasiAdminKependudukanPelapor'));
    }

    /**
     * Show the form for editing the specified rekomendasi_admin_kependudukan.
     */
    public function edit($id)
    {
        $getUsers = DB::table('model_has_roles')
            ->leftjoin('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->leftjoin('rekomendasi_admin_kependudukans', 'rekomendasi_admin_kependudukans.createdby_minkep', '=', 'model_has_roles.model_id')
            ->where('rekomendasi_admin_kependudukans.id', '=', $id)
            // ->where('status_aksi', '=', 'Draft')
            // ->orwhere('status_aksi', '=', 'Teruskan')
            ->get();

        $users =  Auth::user()->id;
        $getAuth = DB::table('model_has_roles')
            ->leftJoin('users', 'users.id', '=', 'model_has_roles.model_id')
            ->leftJoin('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('model_id', $users)
            ->get();

        $createdby = DB::table('rekomendasi_admin_kependudukans')
            ->join('users', 'rekomendasi_admin_kependudukans.createdby_minkep', '=', 'users.name')
            ->join('model_has_roles', 'model_has_roles.model_id', '=', 'users.id')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->select('rekomendasi_admin_kependudukans.id', 'rekomendasi_admin_kependudukans.createdby_minkep', 'roles.name')
            ->get();


        $datawilayah =  DB::table('rekomendasi_admin_kependudukans as w')->select(
            'w.*',
            'rls.name',
            'usr.name',
            'prov.name_prov',
            'kota.name_cities',
            'kecamatan.name_districts',
            'b.name_village',
        )
            ->leftjoin('roles as rls', 'rls.id', '=', 'w.tujuan_minkep')
            ->leftjoin('users as usr', 'usr.id', '=', 'w.petugas_minkep')
            ->leftjoin('indonesia_provinces as prov', 'prov.code', '=', 'w.id_provinsi_minkep')
            ->leftjoin('indonesia_cities as kota', 'kota.code', '=', 'w.id_kabkot_minkep')
            ->leftjoin('indonesia_districts as kecamatan', 'kecamatan.code', '=', 'w.id_kecamatan_minkep')
            ->leftjoin('indonesia_villages as b', 'b.code', '=', 'w.id_kelurahan_minkep')
            ->where('w.id', $id)
            ->first();


        $getdata = DB::table('model_has_roles')
            ->leftjoin('rekomendasi_admin_kependudukans as b', 'b.tujuan_minkep', '=', 'model_has_roles.role_id')
            ->where('b.id', $id)
            ->get();


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
                ->where('name', 'kepala bidang  ')
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

        // $rekomendasiAdminKependudukan = $this->rekomendasiAdminKependudukanRepository->find($id);
        $rekomendasiAdminKependudukan = DB::table('rekomendasi_admin_kependudukans as w')->select(
            'w.*',
            'rls.name as name_roles',
            'usr.name',
            'prov.name_prov',
            'kota.name_cities',
            'kecamatan.name_districts',
            'b.name_village',
        )
            ->leftjoin('model_has_roles', 'model_has_roles.model_id', '=', 'w.createdby_minkep')
            // ->leftjoin('model_has_roles', 'modelid', '=', 'w.createdby_minkep')
            ->leftjoin('roles as rls', 'rls.id', '=', 'model_has_roles.role_id')
            ->leftjoin('users as usr', 'usr.id', '=', 'w.createdby_minkep')
            ->leftjoin('indonesia_provinces as prov', 'prov.code', '=', 'w.id_provinsi_minkep')
            ->leftjoin('indonesia_cities as kota', 'kota.code', '=', 'w.id_kabkot_minkep')
            ->leftjoin('indonesia_districts as kecamatan', 'kecamatan.code', '=', 'w.id_kecamatan_minkep')
            ->leftjoin('indonesia_villages as b', 'b.code', '=', 'w.id_kelurahan_minkep')
            ->where('w.id', $id)->first();
        // dd($rekomendasiAdminKependudukan);   
        $rekomendasiMinkepPelapor = DB::table('rekomendasi_admin_kependudukans')
            ->join('pelapor', function ($join) use ($id) {
                $join->on('rekomendasi_admin_kependudukans.id', '=', 'pelapor.id_form')
                    ->where('rekomendasi_admin_kependudukans.id', '=', $id);
            })
            ->select('rekomendasi_admin_kependudukans.*', 'pelapor.*')
            ->where('pelapor.id_menu', '01')
            ->where('pelapor.id_form', $id)
            ->first();

        return view('rekomendasi_admin_kependudukans.edit', compact('rekomendasiAdminKependudukan', 'datawilayah', 'rekomendasiMinkepPelapor', 'roleid', 'getdata', 'alur', 'createdby', 'getUsers', 'getAuth'));
    }

    /**
     * Update the specified rekomendasi_admin_kependudukan in storage.
     */
    public function update($id, Request $request)
    {
        $getdata = rekomendasi_admin_kependudukan::where('id', $id)->first();
        $rekomendasiAdminKependudukan = DB::table('rekomendasi_admin_kependudukans as w')->select(
            'w.*',
            'rls.name as name_roles',
            'usr.name',
            'model_has_roles.*'
            // 'indonesia_village.name_village'
        )
            ->leftjoin('model_has_roles', 'model_has_roles.model_id', '=', 'w.createdby_minkep')
            // ->leftjoin('indonesia_village', 'modelid', '=', 'w.createdby_minkep')
            ->leftjoin('roles as rls', 'rls.id', '=', 'model_has_roles.role_id')
            ->leftjoin('users as usr', 'usr.id', '=', 'w.createdby_minkep')
            ->where('w.id', $id)->first();
        // dd($rekomendasiAdminKependudukan);
        $data = $request->all();
        
        $files = [
            'file_ktp_terlapor_minkep',
            'file_kk_terlapor_minkep',
            'file_keterangan_dtks_minkep',
            'file_pendukung_minkep',

        ];
        foreach ($files as $file) {
            if ($request->file($file)) {
                $path = $request->file($file);
                $filename = $file . $path->getClientOriginalName();
                $return = Storage::disk('imagekit')->put($filename, fopen($path->getRealPath(), 'r'));
                $data[$file] = Storage::disk('imagekit')->url($filename);
            } else {
                $data[$file] = $getdata->$file;
            }
        }
        if($request->get('status_aksi_minkep') == 'Kembalikan' ||$request->get('status_aksi_minkep') == 'Selesai' ){
           
            $data['tujuan_minkep'] = $rekomendasiAdminKependudukan->role_id;
            $data['petugas_minkep'] = $rekomendasiAdminKependudukan->model_id;
            // dd($data);
        }else{
            $data['updatedby_minkep'] = auth::user()->id;
        }
        
        

        $getdata->update($data);
         
        $logpengaduan = new log_minkep();
    //    dd($getdata->tujuan_minkep);

        $logpengaduan['id_trx_minkep'] = $getdata->id;
        $logpengaduan['id_alur_minkep'] = $request->get('status_aksi_minkep');
        $logpengaduan['petugas_minkep'] = $getdata->petugas_minkep;
        $logpengaduan['catatan_minkep']  = $request->get('catatan_minkep');
        $logpengaduan['file_permohonan_minkep'] = $getdata->file_pendukung_minkep;
        $logpengaduan['tujuan_minkep'] = $getdata->tujuan_minkep;
        // $logpengaduan['created_by_minkep'] = Auth::user()->id;
        $logpengaduan['updated_by_minkep'] = Auth::user()->id;
        // dd($logpengaduan);
        $logpengaduan->save();
        return redirect()->route('rekomendasi_admin_kependudukans.index')->with('success', 'Data berhasil diupdate.');
    }

    /**
     * Remove the specified rekomendasi_admin_kependudukan from storage.
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $rekomendasiAdminKependudukan = $this->rekomendasiAdminKependudukanRepository->find($id);

        if (empty($rekomendasiAdminKependudukan)) {
            Flash::error('Rekomendasi Admin Kependudukan not found');

            return redirect(route('rekomendasi_admin_kependudukans.index'));
        }

        $this->rekomendasiAdminKependudukanRepository->delete($id);

        Flash::success('Rekomendasi Admin Kependudukan deleted successfully.');

        return redirect(route('rekomendasi_admin_kependudukans.index'));
    }


    public function draft(Request $request)
    {
        $user_name = Auth::user()->name;
        $query = DB::table('rekomendasi_admin_kependudukans')
            ->leftjoin('users', 'users.id', '=', 'rekomendasi_admin_kependudukans.petugas_minkep')
            // ->leftjoin('wilayahs', 'wilayahs.createdby', '=', 'rekomendasi_admin_kependudukans.createdby_minkep')
            // ->leftjoin('model_has_roles', 'model_has_roles.model_id', '=', 'rekomendasi_admin_kependudukans.tujuan_minkep_bantuan_pendidikans')
            ->leftjoin('indonesia_villages as b', 'b.code', '=', 'rekomendasi_admin_kependudukans.id_kelurahan_minkep')
            ->leftjoin('indonesia_districts as d', 'd.code', '=', 'rekomendasi_admin_kependudukans.id_kecamatan_minkep')
            ->select('rekomendasi_admin_kependudukans.*', 'b.name_village', 'd.name_districts', 'users.name')
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
                $query->where('rekomendasi_admin_kependudukans.id_kabkot_minkep', $user_wilayah->kota_id);
                $query->where('rekomendasi_admin_kependudukans.status_aksi_minkep', 'Draft');
                $query->where('rekomendasi_admin_kependudukans.createdby_minkep',  Auth::user()->id);
            });
        }
        if ($user_wilayah->name == 'Front Office Kelurahan') {
            $query->Where(function ($query) use ($user_wilayah) {
                $query->where('rekomendasi_admin_kependudukans.id_kelurahan_minkep', $user_wilayah->kelurahan_id);
                $query->where('rekomendasi_admin_kependudukans.status_aksi_minkep', 'Draft');
                $query->where('rekomendasi_admin_kependudukans.createdby_minkep',  Auth::user()->id);
            });
        }
        if ($user_wilayah->name == 'fasilitator') {
            $query->Where(function ($query) use ($user_wilayah) {
                $query->where('rekomendasi_admin_kependudukans.id_kelurahan_minkep', $user_wilayah->kelurahan_id);
                $query->where('rekomendasi_admin_kependudukans.status_aksi_minkep', 'Draft');
                $query->where('rekomendasi_admin_kependudukans.createdby_minkep',  Auth::user()->id);
            });
        }
        if ($user_wilayah->name == 'Front Office Kelurahan' || $user_wilayah == 'Back Ofiice kelurahan'|| $user_wilayah == 'fasilitator') {
            if ($request->has('search') && !empty($request->search['value'])) {
                $search = $request->search['value'];
                $query->Where(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_admin_kependudukans.id_kelurahan_minkep', $user_wilayah->kelurahan_id);
                    $query->where('rekomendasi_admin_kependudukans.status_aksi_minkep', 'Draft');
                    $query->where('rekomendasi_admin_kependudukans.createdby_minkep',  Auth::user()->id);
                })
                ->where(function ($query) use ($search) {
                    $query->where('rekomendasi_admin_kependudukans.no_pendaftaran_minkep', 'like', "%$search%");
                        // ->orwhere('rekomendasi_bantuan_pendidikans.nama_bantuan_pendidikans', 'like', "%$search%")
                        // ->orwhere('b.name_village', 'like', "%$search%")
                        // ->orwhere('d.name_districts', 'like', "%$search%")
                        // // ->orwhere('indonesia_villages.name_village', 'like', "%$search%")
                        // ->orwhere('rekomendasi_bantuan_pendidikans.alamat_bantuan_pendidikans', 'like', "%$search%");
                });
            }
        }elseif($user_wilayah->name == 'Front Office Kota' || $user_wilayah->name == 'Back Ofiice Kota'|| $user_wilayah->name == 'KepalaDinas'||$user_wilayah->name == 'SekertarisDinas'||$user_wilayah->name == 'Supervisor') {
            if ($request->has('search') && !empty($request->search['value'])) {
                $search = $request->search['value'];
                $query->Where(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_admin_kependudukans.id_kabkot_minkep', $user_wilayah->kota_id);
                    $query->where('rekomendasi_admin_kependudukans.status_aksi_minkep', 'Draft');
                    $query->where('rekomendasi_admin_kependudukans.createdby_minkep',  Auth::user()->id);
                })
                ->where(function ($query) use ($search) {
                    $query->where('rekomendasi_admin_kependudukans.no_pendaftaran_minkep', 'like', "%$search%");
                        // ->orwhere('rekomendasi_bantuan_pendidikans.nama_bantuan_pendidikans', 'like', "%$search%")
                        // ->orwhere('b.name_village', 'like', "%$search%")
                        // ->orwhere('d.name_districts', 'like', "%$search%")
                        // // ->orwhere('indonesia_villages.name_village', 'like', "%$search%")
                        // ->orwhere('rekomendasi_bantuan_pendidikans.alamat_bantuan_pendidikans', 'like', "%$search%");
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
            $query = DB::table('rekomendasi_admin_kependudukans')
                ->join('users', 'users.id', '=', 'rekomendasi_admin_kependudukans.petugas_minkep')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_admin_kependudukans.id_kecamatan_minkep')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_admin_kependudukans.id_kelurahan_minkep')
                ->select('rekomendasi_admin_kependudukans.*', 'b.name_village', 'd.name_districts', 'users.name');
        } elseif ($user_wilayah->name == 'Front Office kota') {
            $query = DB::table('rekomendasi_admin_kependudukans')
                ->join('users', 'users.id', '=', 'rekomendasi_admin_kependudukans.petugas_minkep')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_admin_kependudukans.id_kecamatan_minkep')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_admin_kependudukans.id_kelurahan_minkep')
                ->select('rekomendasi_admin_kependudukans.*', 'b.name_village', 'd.name_districts', 'users.name');
        } elseif ($user_wilayah->name == 'Front Office Kelurahan') {
            $query = DB::table('rekomendasi_admin_kependudukans')
                ->join('users', 'users.id', '=', 'rekomendasi_admin_kependudukans.petugas_minkep')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_admin_kependudukans.id_kecamatan_minkep')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_admin_kependudukans.id_kelurahan_minkep')
                ->select('rekomendasi_admin_kependudukans.*', 'b.name_village', 'd.name_districts', 'users.name');
        } elseif ($user_wilayah->name == 'supervisor') {
            $query = DB::table('rekomendasi_admin_kependudukans')
                ->join('users', 'users.id', '=', 'rekomendasi_admin_kependudukans.petugas_minkep')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_admin_kependudukans.id_kecamatan_minkep')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_admin_kependudukans.id_kelurahan_minkep')
                ->select('rekomendasi_admin_kependudukans.*', 'b.name_village', 'd.name_districts', 'users.name');
        } elseif ($user_wilayah->name == 'Back Ofiice kelurahan') {
            $query = DB::table('rekomendasi_admin_kependudukans')
                ->join('users', 'users.id', '=', 'rekomendasi_admin_kependudukans.petugas_minkep')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_admin_kependudukans.id_kecamatan_minkep')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_admin_kependudukans.id_kelurahan_minkep')
                ->select('rekomendasi_admin_kependudukans.*', 'b.name_village', 'd.name_districts', 'users.name');
        } elseif ($user_wilayah->name == 'Back Ofiice Kota') {
            $query = DB::table('rekomendasi_admin_kependudukans')
                ->join('users', 'users.id', '=', 'rekomendasi_admin_kependudukans.petugas_minkep')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_admin_kependudukans.id_kecamatan_minkep')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_admin_kependudukans.id_kelurahan_minkep')
                ->select('rekomendasi_admin_kependudukans.*', 'b.name_village', 'd.name_districts', 'users.name');
        } elseif ($user_wilayah->name == 'kepala bidang') {
            $query = DB::table('rekomendasi_admin_kependudukans')
                ->join('users', 'users.id', '=', 'rekomendasi_admin_kependudukans.petugas_minkep')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_admin_kependudukans.id_kecamatan_minkep')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_admin_kependudukans.id_kelurahan_minkep')
                ->select('rekomendasi_admin_kependudukans.*', 'b.name_village', 'd.name_districts', 'users.name');
        } elseif ($user_wilayah->name == 'SekertarisDinas') {
            $query = DB::table('rekomendasi_admin_kependudukans')
                ->join('users', 'users.id', '=', 'rekomendasi_admin_kependudukans.petugas_minkep')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_admin_kependudukans.id_kecamatan_minkep')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_admin_kependudukans.id_kelurahan_minkep')
                ->select('rekomendasi_admin_kependudukans.*', 'b.name_village', 'd.name_districts', 'users.name');
        } elseif ($user_wilayah->name == 'KepalaDinas') {
            $query = DB::table('rekomendasi_admin_kependudukans')
                ->join('users', 'users.id', '=', 'rekomendasi_admin_kependudukans.petugas_minkep')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_admin_kependudukans.id_kecamatan_minkep')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_admin_kependudukans.id_kelurahan_minkep')
                ->select('rekomendasi_admin_kependudukans.*', 'b.name_village', 'd.name_districts', 'users.name');
        } else {
            $query = DB::table('pengaduans')
                ->join('users', 'users.id', '=', 'pengaduans.createdby')
                ->join('indonesia_villages as b', 'b.code', '=', 'pengaduans.id_kelurahan')
                ->select('pengaduans.*', 'b.name_village');
        }
        if ($user_wilayah->name == 'fasilitator') {
            $query->orWhere(function ($query) use ($user_wilayah) {
                $query->where('rekomendasi_admin_kependudukans.id_kelurahan_minkep', '=', $user_wilayah->kelurahan_id)
                    ->where('rekomendasi_admin_kependudukans.tujuan_minkep', '=', $user_wilayah->role_id)
                    ->where('rekomendasi_admin_kependudukans.petugas_minkep', '=', auth::user()->id)

                    ->where(function ($query) {
                        $query->where('rekomendasi_admin_kependudukans.status_aksi_minkep', '=', 'Teruskan')
                            ->orWhere('rekomendasi_admin_kependudukans.status_aksi_minkep', '=', 'kembalikan');
                    });
            });
        }
        if ($user_wilayah->name == 'Front Office Kelurahan') {
            $query->orWhere(function ($query) use ($user_wilayah) {
                $query->where('rekomendasi_admin_kependudukans.id_kelurahan_minkep', '=', $user_wilayah->kelurahan_id)
                    ->where('rekomendasi_admin_kependudukans.tujuan_minkep', '=', $user_wilayah->role_id)
                    ->where('rekomendasi_admin_kependudukans.petugas_minkep', '=', auth::user()->id)

                    ->where(function ($query) {
                        $query->where('rekomendasi_admin_kependudukans.status_aksi_minkep', '=', 'Teruskan')
                            ->orWhere('rekomendasi_admin_kependudukans.status_aksi_minkep', '=', 'kembalikan');
                    });
            });
        }
        if ($user_wilayah->name == 'Front Office kota') {
            $query->orWhere(function ($query) use ($user_wilayah) {
                $query->where('rekomendasi_admin_kependudukans.id_kabkot_minkep', '=', $user_wilayah->kota_id)
                    ->where('rekomendasi_admin_kependudukans.tujuan_minkep', '=', $user_wilayah->role_id)
                    ->where('rekomendasi_admin_kependudukans.petugas_minkep', '=', auth::user()->id)

                    ->where(function ($query) {
                        $query->where('rekomendasi_admin_kependudukans.status_aksi_minkep', '=', 'Teruskan')
                            ->orWhere('rekomendasi_admin_kependudukans.status_aksi_minkep', '=', 'kembalikan');
                    });
            });
        }
        if ($user_wilayah->name == 'supervisor') {
            $query->orWhere(function ($query) use ($user_wilayah) {
                $query->where('rekomendasi_admin_kependudukans.id_kabkot_minkep', '=', $user_wilayah->kota_id)
                    ->where('rekomendasi_admin_kependudukans.tujuan_minkep', '=', $user_wilayah->role_id)
                    ->where('rekomendasi_admin_kependudukans.petugas_minkep', '=', auth::user()->id)
                    ->where(function ($query) {
                        $query->where('rekomendasi_admin_kependudukans.status_aksi_minkep', '=', 'Teruskan')
                            ->orWhere('rekomendasi_admin_kependudukans.status_aksi_minkep', '=', 'kembalikan');
                    });
                // dd($va);
            });
        }



        if ($user_wilayah->name == 'Back Ofiice Kota') {
            $query->orWhere(function ($query) use ($user_wilayah) {
                $query->where('rekomendasi_admin_kependudukans.id_kabkot_minkep', '=', $user_wilayah->kota_id)
                    ->where('rekomendasi_admin_kependudukans.tujuan_minkep', '=', $user_wilayah->role_id)
                    ->where('rekomendasi_admin_kependudukans.petugas_minkep', '=', auth::user()->id)
                    ->where(function ($query) {
                        $query->where('rekomendasi_admin_kependudukans.status_aksi_minkep', '=', 'Teruskan')
                            ->orWhere('rekomendasi_admin_kependudukans.status_aksi_minkep', '=', 'kembalikan');
                    });
                // dd($va);
            });
            // dd($query->count());
        }
        if ($user_wilayah->name == 'Back Ofiice kelurahan') {
            $query->orWhere(function ($query) use ($user_wilayah) {
                $query->where('rekomendasi_admin_kependudukans.id_kelurahan_minkep', '=', $user_wilayah->kelurahan_id)
                    ->where('rekomendasi_admin_kependudukans.tujuan_minkep', '=', $user_wilayah->role_id)
                    ->where('rekomendasi_admin_kependudukans.petugas_minkep', '=', auth::user()->id)
                    ->where(function ($query) {
                        $query->where('rekomendasi_admin_kependudukans.status_aksi_minkep', '=', 'Teruskan')
                            ->orWhere('rekomendasi_admin_kependudukans.status_aksi_minkep', '=', 'kembalikan');
                    });
                // dd($va);
            });
            // dd($query->count());
        }
        if ($user_wilayah->name == 'kepala bidang') {
            $query->orWhere(function ($query) use ($user_wilayah) {
                $query->where('rekomendasi_admin_kependudukans.id_kabkot_minkep', '=', $user_wilayah->kota_id)
                    ->where('rekomendasi_admin_kependudukans.tujuan_minkep', '=', $user_wilayah->role_id)
                    ->where('rekomendasi_admin_kependudukans.petugas_minkep', '=', auth::user()->id)
                    ->where(function ($query) {
                        $query->where('rekomendasi_admin_kependudukans.status_aksi_minkep', '=', 'Teruskan')
                            ->orWhere('rekomendasi_admin_kependudukans.status_aksi_minkep', '=', 'kembalikan');
                    });
                // dd($va);
            });
        }
        if ($user_wilayah->name == 'SekertarisDinas') {
            $query->orWhere(function ($query) use ($user_wilayah) {
                $query->where('rekomendasi_admin_kependudukans.id_kabkot_minkep', '=', $user_wilayah->kota_id)
                    ->where('rekomendasi_admin_kependudukans.tujuan_minkep', '=', $user_wilayah->role_id)
                    ->where('rekomendasi_admin_kependudukans.petugas_minkep', '=', auth::user()->id)
                    ->where(function ($query) {
                        $query->where('rekomendasi_admin_kependudukans.status_aksi_minkep', '=', 'Teruskan')
                            ->orWhere('rekomendasi_admin_kependudukans.status_aksi_minkep', '=', 'kembalikan');
                    });
                // dd($va);
            });
        }
        if ($user_wilayah->name == 'KepalaDinas') {
            $query->orWhere(function ($query) use ($user_wilayah) {
                $query->where('rekomendasi_admin_kependudukans.id_kabkot_minkep', '=', $user_wilayah->kota_id)
                    ->where('rekomendasi_admin_kependudukans.tujuan_minkep', '=', $user_wilayah->role_id)
                    ->where('rekomendasi_admin_kependudukans.petugas_minkep', '=', auth::user()->id)
                    ->where(function ($query) {
                        $query->where('rekomendasi_admin_kependudukans.status_aksi_minkep', '=', 'Teruskan')
                            ->orWhere('rekomendasi_admin_kependudukans.status_aksi_minkep', '=', 'kembalikan');
                    });
                // dd($va);
            });
        }
        if ($user_wilayah->name == 'Front Office Kelurahan' || $user_wilayah == 'Back Ofiice kelurahan'|| $user_wilayah == 'fasilitator') {
            if ($request->has('search') && !empty($request->search['value'])) {
                $search = $request->search['value'];
                $query->where('rekomendasi_admin_kependudukans.id_kelurahan_minkep', '=', $user_wilayah->kelurahan_id)
                ->where('rekomendasi_admin_kependudukans.tujuan_minkep', '=', $user_wilayah->role_id)
                ->where('rekomendasi_admin_kependudukans.petugas_minkep', '=', auth::user()->id)
                ->where(function ($query) {
                    $query->where('rekomendasi_admin_kependudukans.status_aksi_minkep', '=', 'Teruskan')
                        ->orWhere('rekomendasi_admin_kependudukans.status_aksi_minkep', '=', 'kembalikan');
                })
                ->where(function ($query) use ($search) {
                    $query->where('rekomendasi_admin_kependudukans.no_pendaftaran_minkep', 'like', "%$search%");
                        // ->orwhere('rekomendasi_bantuan_pendidikans.nama_bantuan_pendidikans', 'like', "%$search%")
                        // ->orwhere('b.name_village', 'like', "%$search%")
                        // ->orwhere('d.name_districts', 'like', "%$search%")
                        // // ->orwhere('indonesia_villages.name_village', 'like', "%$search%")
                        // ->orwhere('rekomendasi_bantuan_pendidikans.alamat_bantuan_pendidikans', 'like', "%$search%");
                });
            }
        }elseif($user_wilayah->name == 'Front Office Kota' || $user_wilayah->name == 'Back Ofiice Kota'|| $user_wilayah->name == 'KepalaDinas'||$user_wilayah->name == 'SekertarisDinas'||$user_wilayah->name == 'Supervisor') {
            if ($request->has('search') && !empty($request->search['value'])) {
                $search = $request->search['value'];
                $query->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_admin_kependudukans.id_kabkot_minkep', '=', $user_wilayah->kota_id)
                        ->where('rekomendasi_admin_kependudukans.tujuan_minkep', '=', $user_wilayah->role_id)
                        ->where('rekomendasi_admin_kependudukans.petugas_minkep', '=', auth::user()->id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_admin_kependudukans.status_aksi_minkep', '=', 'Teruskan')
                                ->orWhere('rekomendasi_admin_kependudukans.status_aksi_minkep', '=', 'kembalikan');
                        });
                    // dd($va);
                })
                ->where(function ($query) use ($search) {
                    $query->where('rekomendasi_admin_kependudukans.no_pendaftaran_minkep', 'like', "%$search%");
                        // ->orwhere('rekomendasi_bantuan_pendidikans.nama_bantuan_pendidikans', 'like', "%$search%")
                        // ->orwhere('b.name_village', 'like', "%$search%")
                        // ->orwhere('d.name_districts', 'like', "%$search%")
                        // // ->orwhere('indonesia_villages.name_village', 'like', "%$search%")
                        // ->orwhere('rekomendasi_bantuan_pendidikans.alamat_bantuan_pendidikans', 'like', "%$search%");
                });
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
            'recordsTotal' => rekomendasi_pengangkatan_anak::count(),
            'recordsFiltered' => $total_filtered_items,
            'data' => $data,
        ]);
    }

    public function teruskan(Request $request)
    {
        $user_name = Auth::user()->name;
        // dd($user_name);

        $query = DB::table('rekomendasi_admin_kependudukans')
            ->join('users', 'users.id', '=', 'rekomendasi_admin_kependudukans.petugas_minkep')
            ->leftjoin('indonesia_villages as b', 'b.code', '=', 'rekomendasi_admin_kependudukans.id_kelurahan_minkep')
            ->leftjoin('indonesia_districts as d', 'd.code', '=', 'rekomendasi_admin_kependudukans.id_kecamatan_minkep')
            ->select('rekomendasi_admin_kependudukans.*', 'b.name_village', 'd.name_districts', 'users.name');

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
            $query = DB::table('rekomendasi_admin_kependudukans')
            ->leftjoin('indonesia_districts as d', 'd.code', '=', 'rekomendasi_admin_kependudukans.id_kecamatan_minkep')
            ->leftjoin('indonesia_villages as b', 'b.code', '=', 'rekomendasi_admin_kependudukans.id_kelurahan_minkep')
            // ->leftjoin('roles as r', 'r.id', '=', 'rekomendasi_admin_kependudukans.petugas_minkep')
            ->leftjoin('users', 'users.id', '=', 'rekomendasi_admin_kependudukans.petugas_minkep')
            ->select('rekomendasi_admin_kependudukans.*', 'b.name_village', 'd.name_districts','users.name')
            // ->selectRaw('IFNULL(r.name,"") name')
            ->where('rekomendasi_admin_kependudukans.id_kabkot_minkep', '=', $user_wilayah->kota_id)
            ->whereIn('rekomendasi_admin_kependudukans.status_aksi_minkep', ['Teruskan','Kembalikan'])
            ->where('rekomendasi_admin_kependudukans.petugas_minkep', '<>', $user_id)
            ->whereRaw("(SELECT COUNT(l.id) FROM log_minkep as l WHERE l.id_trx_minkep = rekomendasi_minkep.id AND l.updated_by_minkep = '".$user_id."') > 0 ");
            // dd($query);
            // dd($query);

        }
        if ($user_wilayah->name == 'Front Office Kelurahan') {
            $query = DB::table('rekomendasi_admin_kependudukans')
            ->leftjoin('indonesia_districts as d', 'd.code', '=', 'rekomendasi_admin_kependudukans.id_kecamatan_minkep')
            ->leftjoin('indonesia_villages as b', 'b.code', '=', 'rekomendasi_admin_kependudukans.id_kelurahan_minkep')
            // ->leftjoin('roles as r', 'r.id', '=', 'rekomendasi_admin_kependudukans.petugas_minkep')
            ->leftjoin('users', 'users.id', '=', 'rekomendasi_admin_kependudukans.petugas_minkep')
            ->select('rekomendasi_admin_kependudukans.*', 'b.name_village', 'd.name_districts','users.name')
            // ->selectRaw('IFNULL(r.name,"") name')
            ->where('rekomendasi_admin_kependudukans.id_kelurahan_minkep', '=', $user_wilayah->kelurahan_id)
            ->whereIn('rekomendasi_admin_kependudukans.status_aksi_minkep', ['Teruskan','Kembalikan'])
            ->where('rekomendasi_admin_kependudukans.petugas_minkep', '<>', $user_id)   
            ->whereRaw("(SELECT COUNT(l.id) FROM log_minkep as l WHERE l.id_trx_minkep = rekomendasi_admin_kependudukans.id AND l.updated_by_minkep = '".$user_id."') > 0 ");
            // dd($query);

        }
        if ($user_wilayah->name == 'Front Office kota') {
            $query = DB::table('rekomendasi_admin_kependudukans')
            ->leftjoin('indonesia_districts as d', 'd.code', '=', 'rekomendasi_admin_kependudukans.id_kecamatan_minkep')
            ->leftjoin('indonesia_villages as b', 'b.code', '=', 'rekomendasi_admin_kependudukans.id_kelurahan_minkep')
            // ->leftjoin('roles as r', 'r.id', '=', 'rekomendasi_admin_kependudukans.petugas_minkep')
            ->leftjoin('users', 'users.id', '=', 'rekomendasi_admin_kependudukans.petugas_minkep')
            ->select('rekomendasi_admin_kependudukans.*', 'b.name_village', 'd.name_districts','users.name')
            // ->selectRaw('IFNULL(r.name,"") name')
            ->where('rekomendasi_admin_kependudukans.id_kabkot_minkep', '=', $user_wilayah->kota_id)
            ->whereIn('rekomendasi_admin_kependudukans.status_aksi_minkep', ['Teruskan','Kembalikan'])
            ->where('rekomendasi_admin_kependudukans.petugas_minkep', '<>', $user_id)
            ->whereRaw("(SELECT COUNT(l.id) FROM log_minkep as l WHERE l.id_trx_minkep = rekomendasi_admin_kependudukans.id AND l.updated_by_minkep = '".$user_id."') > 0 ");
            // dd($query);

        }

        //Back office kota 
        if ($user_wilayah->name == 'Back Ofiice Kota') {
            // dd($user_wilayah->model_id);
            $query = DB::table('rekomendasi_admin_kependudukans')
            ->leftjoin('indonesia_districts as d', 'd.code', '=', 'rekomendasi_admin_kependudukans.id_kecamatan_minkep')
            ->leftjoin('indonesia_villages as b', 'b.code', '=', 'rekomendasi_admin_kependudukans.id_kelurahan_minkep')
            // ->leftjoin('roles as r', 'r.id', '=', 'rekomendasi_admin_kependudukans.petugas_minkep')
            ->leftjoin('users', 'users.id', '=', 'rekomendasi_admin_kependudukans.petugas_minkep')
            ->select('rekomendasi_admin_kependudukans.*', 'b.name_village', 'd.name_districts','users.name')
            // ->selectRaw('IFNULL(r.name,"") name')
            ->where('rekomendasi_admin_kependudukans.id_kabkot_minkep', '=', $user_wilayah->kota_id)
            ->whereIn('rekomendasi_admin_kependudukans.status_aksi_minkep', ['Teruskan','Kembalikan'])
            ->where('rekomendasi_admin_kependudukans.petugas_minkep', '<>', $user_id)
            ->whereRaw("(SELECT COUNT(l.id) FROM log_minkep as l WHERE l.id_trx_minkep = rekomendasi_admin_kependudukans.id AND l.updated_by_minkep = '".$user_id."') > 0 ");
            // dd($query);
        }
        if ($user_wilayah->name == 'Back Ofiice kelurahan') {
            // dd($user_wilayah->model_id);
            $query = DB::table('rekomendasi_admin_kependudukans')
            ->leftjoin('indonesia_districts as d', 'd.code', '=', 'rekomendasi_admin_kependudukans.id_kecamatan_minkep')
            ->leftjoin('indonesia_villages as b', 'b.code', '=', 'rekomendasi_admin_kependudukans.id_kelurahan_minkep')
            // ->leftjoin('roles as r', 'r.id', '=', 'rekomendasi_admin_kependudukans.petugas_minkep')
            ->leftjoin('users', 'users.id', '=', 'rekomendasi_admin_kependudukans.petugas_minkep')
            ->select('rekomendasi_admin_kependudukans.*', 'b.name_village', 'd.name_districts','users.name')
            // ->selectRaw('IFNULL(r.name,"") name')
            ->where('rekomendasi_admin_kependudukans.id_kelurahan_minkep', '=', $user_wilayah->kelurahan_id)
            ->whereIn('rekomendasi_admin_kependudukans.status_aksi_minkep', ['Teruskan','Kembalikan'])
            ->where('rekomendasi_admin_kependudukans.petugas_minkep', '<>', $user_id)
            ->whereRaw("(SELECT COUNT(l.id) FROM log_minkep as l WHERE l.id_trx_minkep = rekomendasi_admin_kependudukans.id AND l.updated_by_minkep = '".$user_id."') > 0 ");
            // dd($query);
        }

        //Back office kota 
        if ($user_wilayah->name == 'supervisor') {
            // dd($user_wilayah->model_id);
            $query = DB::table('rekomendasi_admin_kependudukans')
            ->leftjoin('indonesia_districts as d', 'd.code', '=', 'rekomendasi_admin_kependudukans.id_kecamatan_minkep')
            ->leftjoin('indonesia_villages as b', 'b.code', '=', 'rekomendasi_admin_kependudukans.id_kelurahan_minkep')
            // ->leftjoin('roles as r', 'r.id', '=', 'rekomendasi_admin_kependudukans.petugas_minkep')
            ->leftjoin('users', 'users.id', '=', 'rekomendasi_admin_kependudukans.petugas_minkep')
            ->select('rekomendasi_admin_kependudukans.*', 'b.name_village', 'd.name_districts','users.name')
            // ->selectRaw('IFNULL(r.name,"") name')
            ->where('rekomendasi_admin_kependudukans.id_kecamatan_minkep', '=', $user_wilayah->kecamatan_id)
            ->whereIn('rekomendasi_admin_kependudukans.status_aksi_minkep', ['Teruskan','Kembalikan'])
            ->where('rekomendasi_admin_kependudukans.petugas_minkep', '<>', $user_id)
            ->whereRaw("(SELECT COUNT(l.id) FROM log_minkep as l WHERE l.id_trx_minkep = rekomendasi_admin_kependudukans.id AND l.updated_by_minkep = '".$user_id."') > 0 ");
            // dd($query);
        }

        if ($user_wilayah->name == 'kepala bidang') {
            // dd( $user_wilayah->role_id);
            $query = DB::table('rekomendasi_admin_kependudukans')
            ->leftjoin('indonesia_districts as d', 'd.code', '=', 'rekomendasi_admin_kependudukans.id_kecamatan_minkep')
            ->leftjoin('indonesia_villages as b', 'b.code', '=', 'rekomendasi_admin_kependudukans.id_kelurahan_minkep')
            // ->leftjoin('roles as r', 'r.id', '=', 'rekomendasi_admin_kependudukans.petugas_minkep')
            ->leftjoin('users', 'users.id', '=', 'rekomendasi_admin_kependudukans.petugas_minkep')
            ->select('rekomendasi_admin_kependudukans.*', 'b.name_village', 'd.name_districts','users.name')
            // ->selectRaw('IFNULL(r.name,"") name')
            ->where('rekomendasi_admin_kependudukans.id_kabkot_minkep', '=', $user_wilayah->kota_id)
            ->whereIn('rekomendasi_admin_kependudukans.status_aksi_minkep', ['Teruskan','Kembalikan'])
            ->where('rekomendasi_admin_kependudukans.petugas_minkep', '<>', $user_id)
            ->whereRaw("(SELECT COUNT(l.id) FROM log_minkep as l WHERE l.id_trx_minkep = rekomendasi_admin_kependudukans.id AND l.updated_by_minkep = '".$user_id."') > 0 ");
            // dd($query);
        }
        if ($user_wilayah->name == 'SekertarisDinas') {
            // dd($user_wilayah);
            $query = DB::table('rekomendasi_admin_kependudukans')
                ->join('users', 'users.id', '=', 'rekomendasi_admin_kependudukans.petugas_minkep')
                // ->join('log_minkep', 'log_minkep.id_trx_minkep', '=', 'rekomendasi_admin_kependudukans.id')
                // ->join('model_has_roles', 'model_has_roles.model_id', '=', 'rekomendasi_admin_kependudukans.tujuan_minkep')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_admin_kependudukans.id_kelurahan_minkep')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_admin_kependudukans.id_kecamatan_minkep')
                ->select('rekomendasi_admin_kependudukans.*', 'b.name_village', 'd.name_districts', 'log_minkep.tujuan_minkep', 'log_minkep.petugas_minkep', 'users.name')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_admin_kependudukans.id_kabkot_minkep', $user_wilayah->kota_id)
                        ->where('log_minkep.tujuan_minkep', '!=', $user_wilayah->role_id)
                        ->where('log_minkep.created_by_minkep', '=', auth::user()->id)
                        // ->where('rekomendasi_admin_kependudukans.petugas_minkep','!=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_admin_kependudukans.status_aksi_minkep', '=', 'Teruskan')
                                ->orWhere('rekomendasi_admin_kependudukans.status_aksi_minkep', '=', 'kembalikan');
                        });
                })->distinct();
        }
        if ($user_wilayah->name == 'KepalaDinas') {
            //  dd(auth::user()->id);
            $query = DB::table('rekomendasi_admin_kependudukans')
                ->join('users', 'users.id', '=', 'rekomendasi_admin_kependudukans.petugas_minkep')
                // ->join('log_minkep', 'log_minkep.id_trx_minkep', '=', 'rekomendasi_admin_kependudukans.id')
                // ->join('model_has_roles', 'model_has_roles.model_id', '=', 'rekomendasi_admin_kependudukans.tujuan_minkep')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_admin_kependudukans.id_kelurahan_minkep')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_admin_kependudukans.id_kecamatan_minkep')
                ->select('rekomendasi_admin_kependudukans.*', 'b.name_village', 'd.name_districts', 'log_minkep.tujuan_minkep', 'log_minkep.petugas_minkep', 'users.name')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_admin_kependudukans.id_kabkot_minkep', $user_wilayah->kota_id)
                        ->where('rekomendasi_admin_kependudukans.tujuan_minkep', '!=', $user_wilayah->role_id)
                        ->where('rekomendasi_admin_kependudukans.petugas_minkep', '=', auth::user()->id)
                        // ->where('rekomendasi_admin_kependudukans.petugas_minkep','!=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_admin_kependudukans.status_aksi_minkep', '=', 'Teruskan')
                                ->orWhere('rekomendasi_admin_kependudukans.status_aksi_minkep', '=', 'kembalikan');
                        });
                })->distinct();
        }
        if ($user_wilayah->name == 'Front Office Kelurahan' || $user_wilayah->name == 'Back Ofiice kelurahan'|| $user_wilayah->name == 'fasilitator') {
            if ($request->has('search') && !empty($request->search['value'])) {
                $search = $request->search['value'];
                // dd($user_wilayah);
                $query = DB::table('rekomendasi_admin_kependudukans')
                ->leftjoin('indonesia_districts as d', 'd.code', '=', 'rekomendasi_admin_kependudukans.id_kecamatan_minkep')
                ->leftjoin('indonesia_villages as b', 'b.code', '=', 'rekomendasi_admin_kependudukans.id_kelurahan_minkep')
                // ->leftjoin('roles as r', 'r.id', '=', 'rekomendasi_admin_kependudukans.petugas_minkep')
                ->leftjoin('users', 'users.id', '=', 'rekomendasi_admin_kependudukans.petugas_minkep')
                ->select('rekomendasi_admin_kependudukans.*', 'b.name_village', 'd.name_districts','users.name')
                ->where('rekomendasi_admin_kependudukans.id_kabkot_minkep', '=', $user_wilayah->kota_id)
                ->where('rekomendasi_admin_kependudukans.no_pendaftaran_minkep', 'like', "%$search%")
                ->whereIn('rekomendasi_admin_kependudukans.status_aksi_minkep', ['Teruskan','Kembalikan'])
                ->where('rekomendasi_admin_kependudukans.petugas_minkep', '<>', $user_id)
                ->whereRaw("(SELECT COUNT(l.id) FROM log_minkep as l WHERE l.id_trx_minkep = rekomendasi_admin_kependudukans.id AND l.updated_by_minkep = '".$user_id."') > 0 ");
                // dd($query);
               
                        // ->orwhere('rekomendasi_bantuan_pendidikans.nama_bantuan_pendidikans', 'like', "%$search%")
                        // ->orwhere('b.name_village', 'like', "%$search%")
                        // ->orwhere('d.name_districts', 'like', "%$search%")
                        // // ->orwhere('indonesia_villages.name_village', 'like', "%$search%")
                        // ->orwhere('rekomendasi_bantuan_pendidikans.alamat_bantuan_pendidikans', 'like', "%$search%");
             
            }
        }elseif($user_wilayah->name == 'Front Office Kota' || $user_wilayah->name == 'Back Ofiice Kota'|| $user_wilayah->name == 'KepalaDinas'||$user_wilayah->name == 'SekertarisDinas'||$user_wilayah->name == 'Supervisor') {
            if ($request->has('search') && !empty($request->search['value'])) {
                $search = $request->search['value'];
                $query = DB::table('rekomendasi_admin_kependudukans')
                ->join('users', 'users.id', '=', 'rekomendasi_admin_kependudukans.petugas_minkep')
                ->join('log_minkep', 'log_minkep.id_trx_minkep', '=', 'rekomendasi_admin_kependudukans.id')
                // ->join('model_has_roles', 'model_has_roles.model_id', '=', 'rekomendasi_admin_kependudukans.tujuan_minkep')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_admin_kependudukans.id_kelurahan_minkep')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_admin_kependudukans.id_kecamatan_minkep')
                ->select('rekomendasi_admin_kependudukans.*', 'b.name_village', 'd.name_districts', 'log_minkep.tujuan_minkep', 'log_minkep.petugas_minkep', 'users.name')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_admin_kependudukans.id_kabkot_minkep', $user_wilayah->kota_id)
                        ->where('rekomendasi_admin_kependudukans.tujuan_minkep', '!=', $user_wilayah->role_id)
                        ->where('log_minkep.created_by_minkep', '=', auth::user()->id)
                        // ->where('rekomendasi_admin_kependudukans.petugas_minkep','!=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_admin_kependudukans.status_aksi_minkep', '=', 'Teruskan')
                                ->orWhere('rekomendasi_admin_kependudukans.status_aksi_minkep', '=', 'kembalikan');
                        });
                })
                ->where(function ($query) use ($search) {
                    $query->where('rekomendasi_admin_kependudukans.no_pendaftaran_minkep', 'like', "%$search%");
                        // ->orwhere('rekomendasi_bantuan_pendidikans.nama_bantuan_pendidikans', 'like', "%$search%")
                        // ->orwhere('b.name_village', 'like', "%$search%")
                        // ->orwhere('d.name_districts', 'like', "%$search%")
                        // // ->orwhere('indonesia_villages.name_village', 'like', "%$search%")
                        // ->orwhere('rekomendasi_bantuan_pendidikans.alamat_bantuan_pendidikans', 'like', "%$search%");
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
            'recordsTotal' => rekomendasi_pengangkatan_anak::count(),
            'recordsFiltered' => $total_filtered_items,
            'data' => $data,
        ]);
    }

    public function selesai(Request $request)
    {
        $user_name = Auth::user()->name;
        $query = DB::table('rekomendasi_admin_kependudukans')
            ->join('users', 'users.id', 'rekomendasi_admin_kependudukans.petugas_minkep')
            // ->join('log_minkep', 'log_minkep.id_trx_minkep', '=', 'rekomendasi_admin_kependudukans.id')
            ->join('roles', 'roles.id', '=', 'rekomendasi_admin_kependudukans.tujuan_minkep')
            ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_admin_kependudukans.id_kelurahan_minkep')
            ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_admin_kependudukans.id_kecamatan_minkep')
            ->select('rekomendasi_admin_kependudukans.*', 'roles.name', 'users.name', 'b.name_village', 'd.name_districts');
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
            $query = DB::table('rekomendasi_admin_kependudukans')
                ->join('users', 'users.id', 'rekomendasi_admin_kependudukans.petugas_minkep')
                // ->join('log_minkep', 'log_minkep.id_trx_minkep', '=', 'rekomendasi_admin_kependudukans.id')
                ->join('indonesia_villages', 'indonesia_villages.code', '=', 'rekomendasi_admin_kependudukans.id_kelurahan_minkep')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_admin_kependudukans.id_kecamatan_minkep')
                ->select('rekomendasi_admin_kependudukans.*', 'users.name', 'd.name_districts', 'indonesia_villages.name_village', 'users.name')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_admin_kependudukans.id_kelurahan_minkep', $user_wilayah->kelurahan_id)
                        // ->where('log_minkep.tujuan_minkep','=', $user_wilayah->role_id);
                        // ->where('log_minkep.created_by_minkep','!=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_admin_kependudukans.status_aksi_minkep', '=', 'Tolak')
                                ->orWhere('rekomendasi_admin_kependudukans.status_aksi_minkep', '=', 'Selesai');
                        });
                })->distinct();
        } elseif ($user_wilayah->name == 'Front Office Kelurahan') {
            //  dd($user_wilayah->role_id);
            $query = DB::table('rekomendasi_admin_kependudukans')
                ->join('users', 'users.id', 'rekomendasi_admin_kependudukans.petugas_minkep')
                // ->join('log_minkep', 'log_minkep.id_trx_minkep', '=', 'rekomendasi_admin_kependudukans.id')
                ->join('indonesia_villages', 'indonesia_villages.code', '=', 'rekomendasi_admin_kependudukans.id_kelurahan_minkep')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_admin_kependudukans.id_kecamatan_minkep')
                ->select('rekomendasi_admin_kependudukans.*', 'users.name', 'd.name_districts', 'indonesia_villages.name_village')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_admin_kependudukans.id_kelurahan_minkep', $user_wilayah->kelurahan_id)
                        // ->where('log_minkep.tujuan_minkep','=', $user_wilayah->role_id);
                        // ->where('log_minkep.created_by_minkep','!=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_admin_kependudukans.status_aksi_minkep', '=', 'Tolak')
                                ->orWhere('rekomendasi_admin_kependudukans.status_aksi_minkep', '=', 'Selesai');
                        });
                })->distinct();
        } elseif ($user_wilayah->name == 'Front Office kota') {
            //  dd($user_wilayah->role_id);
            $query = DB::table('rekomendasi_admin_kependudukans')
                ->join('users', 'users.id', 'rekomendasi_admin_kependudukans.petugas_minkep')
                // ->join('log_minkep', 'log_minkep.id_trx_minkep', '=', 'rekomendasi_admin_kependudukans.id')
                ->join('indonesia_villages', 'indonesia_villages.code', '=', 'rekomendasi_admin_kependudukans.id_kelurahan_minkep')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_admin_kependudukans.id_kecamatan_minkep')
                ->select('rekomendasi_admin_kependudukans.*', 'users.name', 'd.name_districts', 'indonesia_villages.name_village')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_admin_kependudukans.id_kabkot_minkep', $user_wilayah->kota_id)
                        // ->where('log_minkep.tujuan_minkep','=', $user_wilayah->role_id)
                        // ->where('log_minkep.created_by_minkep','!=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_admin_kependudukans.status_aksi_minkep', '=', 'Tolak')
                                ->orWhere('rekomendasi_admin_kependudukans.status_aksi_minkep', '=', 'Selesai');
                        });
                })->distinct();
        } elseif ($user_wilayah->name == 'Back Ofiice kelurahan') {
            // dd($user_wilayah);
            $query = DB::table('rekomendasi_admin_kependudukans')
                ->join('users', 'users.id', 'rekomendasi_admin_kependudukans.petugas_minkep')
                // ->join('log_minkep', 'log_minkep.id_trx_minkep', '=', 'rekomendasi_admin_kependudukans.id')
                ->join('indonesia_villages', 'indonesia_villages.code', '=', 'rekomendasi_admin_kependudukans.id_kelurahan_minkep')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_admin_kependudukans.id_kecamatan_minkep')
                ->select('rekomendasi_admin_kependudukans.*', 'users.name', 'd.name_districts', 'indonesia_villages.name_village')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_admin_kependudukans.id_kabkot_minkep', $user_wilayah->kota_id)
                        // ->where('log_minkep.tujuan_minkep','=', $user_wilayah->role_id)
                        // ->where('log_minkep.created_by_minkep','!=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_admin_kependudukans.status_aksi_minkep', '=', 'Tolak')
                                ->orWhere('rekomendasi_admin_kependudukans.status_aksi_minkep', '=', 'Selesai');
                        });
                })->distinct();
            // dd($query); 
        } elseif ($user_wilayah->name == 'kepala bidang') {
            // dd($user_wilayah);
            $query = DB::table('rekomendasi_admin_kependudukans')
                ->join('users', 'users.id', 'rekomendasi_admin_kependudukans.petugas_minkep')
                // ->join('log_minkep', 'log_minkep.id_trx_minkep', '=', 'rekomendasi_admin_kependudukans.id')
                ->join('indonesia_villages', 'indonesia_villages.code', '=', 'rekomendasi_admin_kependudukans.id_kelurahan_minkep')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_admin_kependudukans.id_kecamatan_minkep')
                ->select('rekomendasi_admin_kependudukans.*', 'users.name', 'd.name_districts', 'indonesia_villages.name_village')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_admin_kependudukans.id_kabkot_minkep', $user_wilayah->kota_id)
                        // ->where('log_minkep.tujuan_minkep','=', $user_wilayah->role_id)
                        // ->where('log_minkep.created_by_minkep','!=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_admin_kependudukans.status_aksi_minkep', '=', 'Tolak')
                                ->orWhere('rekomendasi_admin_kependudukans.status_aksi_minkep', '=', 'Selesai');
                        });
                })->distinct();
        } elseif ($user_wilayah->name == 'Back Ofiice Kota') {
            // dd($user_wilayah->role_id);
            $query = DB::table('rekomendasi_admin_kependudukans')
                ->join('users', 'users.id', 'rekomendasi_admin_kependudukans.petugas_minkep')
                // ->join('log_minkep', 'log_minkep.id_trx_minkep', '=', 'rekomendasi_admin_kependudukans.id')
                ->join('indonesia_villages', 'indonesia_villages.code', '=', 'rekomendasi_admin_kependudukans.id_kelurahan_minkep')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_admin_kependudukans.id_kecamatan_minkep')
                ->select('rekomendasi_admin_kependudukans.*', 'users.name', 'd.name_districts', 'indonesia_villages.name_village')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_admin_kependudukans.id_kabkot_minkep', $user_wilayah->kota_id)
                        // ->where('log_minkep.tujuan_minkep','=', $user_wilayah->role_id)
                        // ->where('log_minkep.created_by_minkep','!=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_admin_kependudukans.status_aksi_minkep', '=', 'Tolak')
                                ->orWhere('rekomendasi_admin_kependudukans.status_aksi_minkep', '=', 'Selesai');
                        });
                })->distinct();
        } elseif ($user_wilayah->name == 'kepala bidang') {
            // dd($user_wilayah);
            $query = DB::table('rekomendasi_admin_kependudukans')
                ->join('users', 'users.id', 'rekomendasi_admin_kependudukans.petugas_minkep')
                // ->join('log_minkep', 'log_minkep.id_trx_minkep', '=', 'rekomendasi_admin_kependudukans.id')
                ->join('indonesia_villages', 'indonesia_villages.code', '=', 'rekomendasi_admin_kependudukans.id_kelurahan_minkep')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_admin_kependudukans.id_kecamatan_minkep')
                ->select('rekomendasi_admin_kependudukans.*', 'users.name', 'd.name_districts', 'indonesia_villages.name_village')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_admin_kependudukans.id_kabkot_minkep', $user_wilayah->kota_id)
                        // ->where('log_minkep.tujuan_minkep','=', $user_wilayah->role_id)
                        // ->where('log_minkep.created_by_minkep','!=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_admin_kependudukans.status_aksi_minkep', '=', 'Tolak')
                                ->orWhere('rekomendasi_admin_kependudukans.status_aksi_minkep', '=', 'Selesai');
                        });
                })->distinct();
        } elseif ($user_wilayah->name == 'supervisor') {
            // dd($user_wilayah);
            $query = DB::table('rekomendasi_admin_kependudukans')
                ->join('users', 'users.id', 'rekomendasi_admin_kependudukans.petugas_minkep')
                // ->join('log_minkep', 'log_minkep.id_trx_minkep', '=', 'rekomendasi_admin_kependudukans.id')
                ->join('indonesia_villages', 'indonesia_villages.code', '=', 'rekomendasi_admin_kependudukans.id_kelurahan_minkep')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_admin_kependudukans.id_kecamatan_minkep')
                ->select('rekomendasi_admin_kependudukans.*', 'users.name', 'd.name_districts', 'indonesia_villages.name_village')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_admin_kependudukans.id_kabkot_minkep', $user_wilayah->kota_id)
                        // ->where('log_minkep.tujuan_minkep','=', $user_wilayah->role_id)
                        // ->where('log_minkep.created_by_minkep','!=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_admin_kependudukans.status_aksi_minkep', '=', 'Tolak')
                                ->orWhere('rekomendasi_admin_kependudukans.status_aksi_minkep', '=', 'Selesai');
                        });
                })->distinct();
        }
        if ($user_wilayah->name == 'Front Office Kelurahan' || $user_wilayah == 'Back Ofiice kelurahan'|| $user_wilayah == 'fasilitator') {
            if ($request->has('search') && !empty($request->search['value'])) {
                $search = $request->search['value'];
                $query = DB::table('rekomendasi_admin_kependudukans')
                ->join('users', 'users.id', 'rekomendasi_admin_kependudukans.petugas_minkep')
                // ->join('log_minkep', 'log_minkep.id_trx_minkep', '=', 'rekomendasi_admin_kependudukans.id')
                ->join('indonesia_villages', 'indonesia_villages.code', '=', 'rekomendasi_admin_kependudukans.id_kelurahan_minkep')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_admin_kependudukans.id_kecamatan_minkep')
                ->select('rekomendasi_admin_kependudukans.*', 'users.name', 'd.name_districts', 'indonesia_villages.name_village')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_admin_kependudukans.id_kelurahan_minkep', $user_wilayah->kelurahan_id)
                        // ->where('log_minkep.tujuan_minkep','=', $user_wilayah->role_id)
                        // ->where('log_minkep.created_by_minkep','!=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_admin_kependudukans.status_aksi_minkep', '=', 'Tolak')
                                ->orWhere('rekomendasi_admin_kependudukans.status_aksi_minkep', '=', 'Selesai');
                        });
                })
                ->where(function ($query) use ($search) {
                    $query->where('rekomendasi_admin_kependudukans.no_pendaftaran_minkep', 'like', "%$search%");
                        // ->orwhere('rekomendasi_bantuan_pendidikans.nama_bantuan_pendidikans', 'like', "%$search%")
                        // ->orwhere('b.name_village', 'like', "%$search%")
                        // ->orwhere('d.name_districts', 'like', "%$search%")
                        // // ->orwhere('indonesia_villages.name_village', 'like', "%$search%")
                        // ->orwhere('rekomendasi_bantuan_pendidikans.alamat_bantuan_pendidikans', 'like', "%$search%");
                });
            }
        }elseif($user_wilayah->name == 'Front Office Kota' || $user_wilayah->name == 'Back Ofiice Kota'|| $user_wilayah->name == 'KepalaDinas'||$user_wilayah->name == 'SekertarisDinas'||$user_wilayah->name == 'Supervisor') {
            if ($request->has('search') && !empty($request->search['value'])) {
                $search = $request->search['value'];
                $query = DB::table('rekomendasi_admin_kependudukans')
                    ->join('users', 'users.id', 'rekomendasi_admin_kependudukans.petugas_minkep')
                    // ->join('log_minkep', 'log_minkep.id_trx_minkep', '=', 'rekomendasi_admin_kependudukans.id')
                    ->join('indonesia_villages', 'indonesia_villages.code', '=', 'rekomendasi_admin_kependudukans.id_kelurahan_minkep')
                    ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_admin_kependudukans.id_kecamatan_minkep')
                    ->select('rekomendasi_admin_kependudukans.*', 'users.name', 'd.name_districts', 'indonesia_villages.name_village')
                    ->orWhere(function ($query) use ($user_wilayah) {
                        $query->where('rekomendasi_admin_kependudukans.id_kabkot_minkep', $user_wilayah->kota_id)
                            // ->where('log_minkep.tujuan_minkep','=', $user_wilayah->role_id);
                            // ->where('log_minkep.created_by_minkep','!=', $user_wilayah->model_id)
                            ->where(function ($query) {
                                $query->where('rekomendasi_admin_kependudukans.status_aksi_minkep', '=', 'Tolak')
                                    ->orWhere('rekomendasi_admin_kependudukans.status_aksi_minkep', '=', 'Selesai');
                            });
                })
                ->where(function ($query) use ($search) {
                    $query->where('rekomendasi_admin_kependudukans.no_pendaftaran_minkep', 'like', "%$search%");
                        // ->orwhere('rekomendasi_bantuan_pendidikans.nama_bantuan_pendidikans', 'like', "%$search%")
                        // ->orwhere('b.name_village', 'like', "%$search%")
                        // ->orwhere('d.name_districts', 'like', "%$search%")
                        // // ->orwhere('indonesia_villages.name_village', 'like', "%$search%")
                        // ->orwhere('rekomendasi_bantuan_pendidikans.alamat_bantuan_pendidikans', 'like', "%$search%");
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
            'recordsTotal' => rekomendasi_pengangkatan_anak::count(),
            'recordsFiltered' => $total_filtered_items,
            'data' => $data,
        ]);
    }
    public function getPetugasAdminduk($id)
    {
        
        $userid = auth::user()->id;
       
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
        if ($user_wilayah->name == 'Front Office Kelurahan' || $user_wilayah->name == 'Back Ofiice kelurahan'|| $user_wilayah == 'fasilitator'){
           
            $users = DB::table('users as u')
                ->leftJoin('model_has_roles as mhr', 'u.id', '=', 'mhr.model_id')
                ->leftJoin('roles as r', 'mhr.role_id', '=', 'r.id')
                ->leftJoin('wilayahs', 'wilayahs.createdby', '=', 'u.id')
                ->select('u.id as user_id', 'u.name', 'u.email', 'r.name as role')
                ->where('mhr.model_type', '=', 'App\Models\User')
                ->where('wilayahs.kelurahan_id', '=',$wilayah->kelurahan_id)
                ->where('mhr.role_id', '=', $id)
                ->get();
           
            if ($users->empty()) {
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

                // return response()->json($users);
            }
            return response()->json($users);
        }elseif($user_wilayah->name == 'Front Office Kota' || $user_wilayah->name == 'Back Ofiice Kota'|| $user_wilayah->name == 'KepalaDinas'||$user_wilayah->name == 'SekertarisDinas') {
            $users = DB::table('users as u')
                    ->leftJoin('model_has_roles as mhr', 'u.id', '=', 'mhr.model_id')
                    ->leftJoin('roles as r', 'mhr.role_id', '=', 'r.id')
                    ->leftJoin('wilayahs', 'wilayahs.createdby', '=', 'u.id')
                    ->leftJoin('rekomendasi_admin_kependudukans','rekomendasi_admin_kependudukans.createdby_minkep','=','u.id')
                    ->where('mhr.model_type', '=', 'App\Models\User')
                    ->where('wilayahs.kota_id', '=',$wilayah->kota_id)
                    ->where('mhr.role_id', '=', $id)
                    // ->whereRaw("(SELECT COUNT(l.id) FROM users as l WHERE l.id = '".$id."') > 0 ")
                    ->whereRaw("(SELECT COUNT(l.id) FROM users as l WHERE l.id = rekomendasi_admin_kependudukans.id AND l.id = '".$id."') > 0 ")
                    ->select('u.id', 'u.name', 'u.email', 'r.name as role')
                    ->get();
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
