<?php

namespace App\Http\Controllers;

use App\Http\Requests\Createrekomendasi_keringanan_pbbRequest;
use App\Http\Requests\Updaterekomendasi_keringanan_pbbRequest;
use App\Http\Controllers\AppBaseController;
use App\Models\log_pbbs;
use App\Models\pelapor;
use App\Models\Prelist;
use App\Models\rekomendasi_keringanan_pbb;
use App\Models\Roles;
use App\Repositories\rekomendasi_keringanan_pbbRepository;
use Illuminate\Http\Request;
use Flash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class rekomendasi_keringanan_pbbController extends AppBaseController
{
    /** @var rekomendasi_keringanan_pbbRepository $rekomendasiKeringananPbbRepository*/
    private $rekomendasiKeringananPbbRepository;

    /**
     * Display a listing of the rekomendasi_keringanan_pbb.
     */
    public function index(Request $request)
    {
        // $rekomendasiKeringananPbbs = $this->rekomendasiKeringananPbbRepository->paginate(10);

        return view('rekomendasi_keringanan_pbbs.index');
            // ->with('rekomendasiKeringananPbbs', $rekomendasiKeringananPbbs);
    }
    public function FilePbb($id)
    {
        // $adminduk = rekomendasi_keringanan_pbb::find($id);
        
        $adminduk =  DB::table('rekomendasi_keringanan_pbbs as w')->select(
            'w.*',
            'prov.name_prov',
            'kota.name_cities',
            'kecamatan.name_districts',
            'b.name_village',
        )
            ->leftjoin('indonesia_provinces as prov', 'prov.code', '=', 'w.id_provinsi_pbb')
            ->leftjoin('indonesia_cities as kota', 'kota.code', '=', 'w.id_kabkot_pbb')
            ->leftjoin('indonesia_districts as kecamatan', 'kecamatan.code', '=', 'w.id_kecamatan_pbb')
            ->leftjoin('indonesia_villages as b', 'b.code', '=', 'w.id_kelurahan_pbb')
            ->where('w.id', $id)
            ->first();
        // dd($rehabsos);
        $getIdDtks = DB::table('rekomendasi_keringanan_pbbs as w')->select(
            'w.*',
            'dtks.Id_DTKS'
        )
            ->leftjoin('dtks', 'dtks.Nik', '=', 'w.nik_pbb')
            // ->where('status_wilayah', '1')
            ->where('dtks.Nik', $adminduk->nik_pbb)->first();
        // dd($getIdDtks);
        
        if (!is_null($getIdDtks) && !is_null($getIdDtks->Id_DTKS)) {
            $data_dtks = $getIdDtks->Id_DTKS;
        } else {
            $data_dtks = '-';
        }

       $date = Carbon::parse($adminduk->tgl_lahir_pbb)->locale('id');

       $date->settings(['formatFunction' => 'translatedFormat']);

       $tanggal = $date->format('j F Y ');


       // dd($tanggal);
        $pdf = PDF::loadHtml(view('rekomendasi_keringanan_pbbs.file_permohonan',compact('adminduk','tanggal','data_dtks')));
        $pdf->setPaper('F4', 'portrait');
        $filename = 'File Permohonan' . $adminduk->nama_pbb . '.pdf';
        return $pdf->stream($filename);
    }
    /**
     * Show the form for creating a new rekomendasi_keringanan_pbb.
     */
    public function create()
    {
        $v = rekomendasi_keringanan_pbb::latest()->first();
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
        $checkuserrole = DB::table('model_has_roles')
        ->leftjoin('roles', 'roles.id', '=', 'model_has_roles.role_id')
		->select('roles.name')
        ->where('model_id', '=', $user->id)
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
        return view('rekomendasi_keringanan_pbbs.create', compact('kecamatans', 'wilayah', 'roleid', 'checkuserrole', 'alur'));
        // return view('rekomendasi_pbb.create');
    }
    /**
     * Store a newly created rekomendasi_keringanan_pbb in storage.
     */
    public function store(Request $request)
    {
        // dd($request->get('status_dtks_pbb'));

        if ($request->get('status_alur_pbb') != 'Draft') {
            // jika status_alur_pbb sama dengan Draft akan nmasuk kondisi sini
            if ($request->get('status_dtks_pbb') == 'Terdaftar') {
                // jika status_dtks_pbb sama dengan terdaftar akan nmasuk kondisi sini
                $data = new rekomendasi_keringanan_pbb();
                $files = [
                    'file_ktp_terlapor_pbb' => 'keringananpbb/ktp/',
                    'file_kk_terlapor_pbb' => 'keringananpbb/kk/',
                    'file_keterangan_dtks_pbb' => 'keringananpbb/strukturorganisasi/',
                    'file_pendukung_pbb' => 'keringananpbb/wajibpajak/'
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


                // $data->id_alur = $request->get('id_alur_pbb');
                $data->no_pendaftaran_pbb = mt_rand(100, 1000);
                $data->id_provinsi_pbb = $request->get('id_provinsi_pbb');
                $data->id_kabkot_pbb = $request->get('id_kabkot_pbb');
                $data->id_kecamatan_pbb = $request->get('id_kecamatan_pbb');
                $data->id_kelurahan_pbb = $request->get('id_kelurahan_pbb');
                $data->jenis_pelapor_pbb = $request->get('jenis_pelapor_pbb');
                $data->ada_nik_pbb = $request->get('ada_nik_pbb');
                $data->nik_pbb = $request->get('nik_pbb');
                $data->no_kk_pbb = $request->get('no_kk_pbb');
                // $data->no_kis = $request->get('no_kis');
                $data->nama_pbb = $request->get('nama_pbb');
                $data->tgl_lahir_pbb = $request->get('tgl_lahir_pbb');
                $data->tempat_lahir_pbb = $request->get('tempat_lahir_pbb');
                $data->jenis_kelamin_pbb = $request->get('jenis_kelamin_pbb');
                $data->telp_pbb = $request->get('telp_pbb');
                $data->alamat_pbb = $request->get('alamat_pbb');
                $data->catatan_pbb = $request->get('catatan_pbb');
                $data->status_dtks_pbb = $request->get('status_dtks_pbb');
                $data->tujuan_pbb = $request->get('tujuan_pbb');
                $data->status_aksi_pbb = $request->get('status_aksi_pbb');
                $data->petugas_pbb = $request->get('petugas_pbb');
                $data->nama_wajib_pajak_pbb = $request->get('nama_wajib_pajak_pbb');

                $data->createdby_pbb = Auth::user()->id;
                $data->updatedby_pbb = Auth::user()->id;
                // dd($data);
                $data->save();
                $logpengaduan = new log_pbbs();
                $logpengaduan['id_trx_pbbs'] = $data->id;
                $logpengaduan['id_alur_pbbs'] = $data->status_aksi_pbb;
                $logpengaduan['tujuan_pbbs'] = $data->tujuan_pbb;
                $logpengaduan['petugas_pbbs'] = $data->petugas_pbb;
                $logpengaduan['catatan_pbbs']  = $data->catatan_pbb;
                $logpengaduan['file_pendukung_pbbs'] = $data->file_pendukung_pbb;
                $logpengaduan['tujuan_pbbs'] = $data->tujuan_pbb;
                $logpengaduan['created_by_pbbs'] = Auth::user()->id;
                $logpengaduan['updated_by_pbbs'] = Auth::user()->id;
                // dd($logpengaduan);
                $logpengaduan->save();
                if ($request->get('jenis_pelapor_pbb') == 'Orang Lain') {
                    $pelapor = new pelapor();
                    $pelapor['id_menu'] = '02';
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

                    $pelapor->save();
                }else{
                    $pelapor = new pelapor();
                    $pelapor['id_menu'] = '02';
                    $pelapor['id_form'] = $data->id;
                    $pelapor['jenis_peelaporan'] = $request->get('jenis_pelapor_pbb');
                    $pelapor['nama_pelapor']  =  $request->get('nama_pbb');
                    $pelapor['ada_nik_pelapor'] = $request->get('ada_nik_pbb');
                    $pelapor['nik_pelapor'] = $request->get('nik_pbb');
                    $pelapor['status_dtks_pelapor'] = $request->get('status_dtks_pbb');
                    $pelapor['tempat_lahir_pelapor'] = $request->get('tempat_lahir_pbb');
                    $pelapor['tanggal_lahir_pelapor'] = $request->get('tgl_lahir_pbb');
                    $pelapor['jenis_kelamin'] = $request->get('jenis_kelamin_pbb');
                    // $pelapor['nama_pelapor']  = $request->get('nama_pbb');
                    $pelapor['telepon_pelapor'] = $request->get('telp_pbb');
                    $pelapor['alamat_pelapor'] = $request->get('alamat_pbb');
                    $pelapor['createdby_pelapor'] = Auth::user()->id;
                    $pelapor['updatedby_pelapor'] = Auth::user()->id;
    
                    $pelapor->save();
                }
                return redirect('rekomendasi_keringanan_pbbs')->withSuccess('Data Rekomendasi Berhasil Ditambahkan');
            } else {
                // jika status_dtks_pbb sama dengan terdaftar akan nmasuk kondisi sini
                $cek = Prelist::where('nik', '=', $request->get('nik_pbb'))->exists();
                if (!$cek) {
                    // jika nik tidak ada nik akan masuk ke sini
                    $data = new Prelist;
                    $data['id_provinsi'] = $request->get('id_provinsi_pbb');
                    $data['id_kabkot'] = $request->get('id_kabkot_pbb');
                    $data['id_kecamatan'] = $request->get('id_kecamatan_pbb');
                    $data['id_kelurahan'] = $request->get('id_kelurahan_pbb');
                    $data['nik'] = $request->get('nik_pbb');
                    $data['no_kk'] = $request->get('no_kk_pbb');
                    // $data['no_kis'] = $request->get('no_kis_pbb');
                    $data['nama'] = $request->get('nama_pbb');
                    $data['tgl_lahir'] = $request->get('tgl_lahir_pbb');
                    // $data['alamat'] = $request->get('alamat_pbb');
                    $data['telp'] = $request->get('telpon_pbb');
                    $data['email'] = $request->get('email_pbb');
                    $data['status_data'] = 'prelistdtks';

                    $data->save();
                    $data = new rekomendasi_keringanan_pbb();
                    $files = [
                        'file_ktp_terlapor_pbb' => 'keringananpbb/ktp/',
                        'file_kk_terlapor_pbb' => 'keringananpbb/kk/',
                        'file_keterangan_dtks_pbb' => 'keringananpbb/strukturorganisasi/',
                        'file_pendukung_pbb' => 'keringananpbb/wajibpajak/'
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

                    // $data->id_alur = $request->get('id_alur_pbb');
                    $data->no_pendaftaran_pbb = mt_rand(100, 1000);
                    $data->id_provinsi_pbb = $request->get('id_provinsi_pbb');
                    $data->id_kabkot_pbb = $request->get('id_kabkot_pbb');
                    $data->id_kecamatan_pbb = $request->get('id_kecamatan_pbb');
                    $data->id_kelurahan_pbb = $request->get('id_kelurahan_pbb');
                    $data->jenis_pelapor_pbb = $request->get('jenis_pelapor_pbb');
                    $data->ada_nik_pbb = $request->get('ada_nik_pbb');
                    $data->nik_pbb = $request->get('nik_pbb');
                    $data->no_kk_pbb = $request->get('no_kk_pbb');
                    $data->nama_wajib_pajak_pbb = $request->get('nama_wajib_pajak_pbb');
                    $data->nama_pbb = $request->get('nama_pbb');
                    $data->tgl_lahir_pbb = $request->get('tgl_lahir_pbb');
                    $data->tempat_lahir_pbb = $request->get('tempat_lahir_pbb');
                    $data->jenis_kelamin_pbb = $request->get('jenis_kelamin_pbb');
                    $data->telp_pbb = $request->get('telp_pbb');
                    $data->alamat_pbb = $request->get('alamat_pbb');
                    $data->status_dtks_pbb = $request->get('status_dtks_pbb');
                    $data->tujuan_pbb = $request->get('tujuan_pbb');
                    $data->status_aksi_pbb = $request->get('status_aksi_pbb');
                    $data->petugas_pbb = $request->get('petugas_pbb');
                    $data->created_by_pbb = Auth::user()->id;
                    $data->updated_by_pbb = Auth::user()->id;

                    // dd($data);
                    $data->save();
                    $logpengaduan = new log_pbbs();
                    $logpengaduan['id_trx_pbbs'] = $data->id;
                    $logpengaduan['id_alur_pbbs'] = $data->status_aksi_pbb;
                    $logpengaduan['tujuan_pbbs'] = $data->tujuan_pbb;
                    $logpengaduan['petugas_pbbs'] = $data->petugas_pbb;
                    $logpengaduan['catatan_pbbs']  = $data->catatan_pbb;
                    $logpengaduan['file_pendukung_pbbs'] = $data->file_pendukung_pbb;
                    $logpengaduan['tujuan_pbbs'] = $data->tujuan_pbb;
                    $logpengaduan['created_by_pbbs'] = Auth::user()->id;
                    $logpengaduan['updated_by_pbbs'] = Auth::user()->id;
                    $logpengaduan->save();
                    if ($request->get('jenis_pelapor_pbb') == 'Orang Lain') {
                        $pelapor = new pelapor();
                        $pelapor['id_menu'] = '02';
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
    
                        $pelapor->save();
                    }else{
                        $pelapor = new pelapor();
                        $pelapor['id_menu'] = '02';
                        $pelapor['id_form'] = $data->id;
                        $pelapor['jenis_peelaporan'] = $request->get('jenis_pelapor_pbb');
                        $pelapor['nama_pelapor']  =  $request->get('nama_pbb');
                        $pelapor['ada_nik_pelapor'] = $request->get('ada_nik_pbb');
                        $pelapor['nik_pelapor'] = $request->get('nik_pbb');
                        $pelapor['status_dtks_pelapor'] = $request->get('status_dtks_pbb');
                        $pelapor['tempat_lahir_pelapor'] = $request->get('tempat_lahir_pbb');
                        $pelapor['tanggal_lahir_pelapor'] = $request->get('tgl_lahir_pbb');
                        $pelapor['jenis_kelamin'] = $request->get('jenis_kelamin_pbb');
                        // $pelapor['nama_pelapor']  = $request->get('nama_pbb');
                        $pelapor['telepon_pelapor'] = $request->get('telp_pbb');
                        $pelapor['alamat_pelapor'] = $request->get('alamat_pbb');
                        $pelapor['createdby_pelapor'] = Auth::user()->id;
                        $pelapor['updatedby_pelapor'] = Auth::user()->id;
        
                        $pelapor->save();
                    }   
                    return redirect('rekomendasi_keringanan_pbbs')->withSuccess('Data Rekomendasi Berhasil Ditambahkan');
                } else {
                    //jika nik ada di prelist akan masuk ke sini
                    $data = new rekomendasi_keringanan_pbb();
                    $files = [
                        'file_ktp_terlapor_pbb' => 'keringananpbb/ktp/',
                        'file_kk_terlapor_pbb' => 'keringananpbb/kk/',
                        'file_keterangan_dtks_pbb' => 'keringananpbb/strukturorganisasi/',
                        'file_pendukung_pbb' => 'keringananpbb/wajibpajak/'
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

                    // $data->id_alur = $request->get('id_alur_pbb');
                    $data->no_pendaftaran_pbb = mt_rand(100, 1000);
                    $data->id_provinsi_pbb = $request->get('id_provinsi_pbb');
                    $data->id_kabkot_pbb = $request->get('id_kabkot_pbb');
                    $data->id_kecamatan_pbb = $request->get('id_kecamatan_pbb');
                    $data->id_kelurahan_pbb = $request->get('id_kelurahan_pbb');
                    $data->jenis_pelapor_pbb = $request->get('jenis_pelapor_pbb');
                    $data->ada_nik_pbb = $request->get('ada_nik_pbb');
                    $data->nik_pbb = $request->get('nik_pbb');
                    $data->no_kk_pbb = $request->get('no_kk_pbb');
                    $data->nama_wajib_pajak_pbb = $request->get('nama_wajib_pajak_pbb');
                    $data->nama_pbb = $request->get('nama_pbb');
                    $data->tgl_lahir_pbb = $request->get('tgl_lahir_pbb');
                    $data->tempat_lahir_pbb = $request->get('tempat_lahir_pbb');
                    $data->jenis_kelamin_pbb = $request->get('jenis_kelamin_pbb');
                    $data->telp_pbb = $request->get('telp_pbb');
                    $data->alamat_pbb = $request->get('alamat_pbb');
                    $data->status_dtks_pbb = $request->get('status_dtks_pbb');
                    $data->tujuan_pbb = $request->get('tujuan_pbb');
                    $data->status_aksi_pbb = $request->get('status_aksi_pbb');
                    $data->petugas_pbb = $request->get('petugas_pbb');
                    $data->created_by_pbb = Auth::user()->id;
                    $data->updated_by_pbb = Auth::user()->id;
                    // dd($data);
                    $data->save();
                    $logpengaduan = new log_pbbs();
                    $logpengaduan['id_trx_pbbs'] = $data->id;
                    $logpengaduan['id_alur_pbbs'] = $data->status_aksi_pbb;
                    $logpengaduan['tujuan_pbbs'] = $data->tujuan_pbb;
                    $logpengaduan['petugas_pbbs'] = $data->petugas_pbb;
                    $logpengaduan['catatan_pbbs']  = $data->catatan_pbb;
                    $logpengaduan['file_pendukung_pbbs'] = $data->file_pendukung_pbb;
                    $logpengaduan['tujuan_pbbs'] = $data->tujuan_pbb;
                    $logpengaduan['created_by_pbbs'] = Auth::user()->id;
                    $logpengaduan['updated_by_pbbs'] = Auth::user()->id;

                    $logpengaduan->save();
                    if ($request->get('jenis_pelapor_pbb') == 'Orang Lain') {
                        $pelapor = new pelapor();
                        $pelapor['id_menu'] = '02';
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
    
                        $pelapor->save();
                    }else{
                        $pelapor = new pelapor();
                        $pelapor['id_menu'] = '02';
                        $pelapor['id_form'] = $data->id;
                        $pelapor['jenis_peelaporan'] = $request->get('jenis_pelapor_pbb');
                        $pelapor['nama_pelapor']  =  $request->get('nama_pbb');
                        $pelapor['ada_nik_pelapor'] = $request->get('ada_nik_pbb');
                        $pelapor['nik_pelapor'] = $request->get('nik_pbb');
                        $pelapor['status_dtks_pelapor'] = $request->get('status_dtks_pbb');
                        $pelapor['tempat_lahir_pelapor'] = $request->get('tempat_lahir_pbb');
                        $pelapor['tanggal_lahir_pelapor'] = $request->get('tgl_lahir_pbb');
                        $pelapor['jenis_kelamin'] = $request->get('jenis_kelamin_pbb');
                        // $pelapor['nama_pelapor']  = $request->get('nama_pbb');
                        $pelapor['telepon_pelapor'] = $request->get('telp_pbb');
                        $pelapor['alamat_pelapor'] = $request->get('alamat_pbb');
                        $pelapor['createdby_pelapor'] = Auth::user()->id;
                        $pelapor['updatedby_pelapor'] = Auth::user()->id;
        
                        $pelapor->save();
                    } 
    
                    return redirect('rekomendasi_keringanan_pbbs')->withSuccess('Data Rekomendasi Berhasil Ditambahkan');
                }
            }
        } else {
            //jika status draft adalah ini akan masuk ke sini
            $data = new rekomendasi_keringanan_pbb();
            $files = [
                'file_ktp_terlapor_pbb' => 'keringananpbb/ktp/',
                'file_kk_terlapor_pbb' => 'keringananpbb/kk/',
                'file_keterangan_dtks_pbb' => 'keringananpbb/strukturorganisasi/',
                'file_pendukung_pbb' => 'keringananpbb/wajibpajak/'
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

            // $data->id_alur = $request->get('id_alur_pbb');
            $data->no_pendaftaran_pbb = mt_rand(100, 1000);
            $data->id_provinsi_pbb = $request->get('id_provinsi_pbb');
            $data->id_kabkot_pbb = $request->get('id_kabkot_pbb');
            $data->id_kecamatan_pbb = $request->get('id_kecamatan_pbb');
            $data->id_kelurahan_pbb = $request->get('id_kelurahan_pbb');
            $data->jenis_pelapor_pbb = $request->get('jenis_pelapor_pbb');
            $data->ada_nik_pbb = $request->get('ada_nik_pbb');
            $data->nik_pbb = $request->get('nik_pbb');
            $data->no_kk_pbb = $request->get('no_kk_pbb');
            $data->nama_wajib_pajak_pbb = $request->get('nama_wajib_pajak_pbb');
            $data->nama_pbb = $request->get('nama_pbb');
            $data->tgl_lahir_pbb = $request->get('tgl_lahir_pbb');
            $data->tempat_lahir_pbb = $request->get('tempat_lahir_pbb');
            $data->jenis_kelamin_pbb = $request->get('jenis_kelamin_pbb');
            $data->telp_pbb = $request->get('telp_pbb');
            $data->status_dtks_pbb = $request->get('status_dtks_pbb');
            $data->tujuan_pbb = $request->get('tujuan_pbb');
            $data->status_aksi_pbb = $request->get('status_aksi_pbb');
            $data->petugas_pbb = $request->get('petugas_pbb');
            $data->created_by_pbb = Auth::user()->id;
            $data->updated_by_pbb = Auth::user()->id;
            // dd($data);
            $data->save();
            if ($request->get('jenis_pelapor_pbb') == 'Orang Lain') {
                $pelapor = new pelapor();
                $pelapor['id_menu'] = '02';
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

                $pelapor->save();
            }else{
                $pelapor = new pelapor();
                $pelapor['id_menu'] = '02';
                $pelapor['id_form'] = $data->id;
                $pelapor['jenis_peelaporan'] = $request->get('jenis_pelapor_pbb');
                $pelapor['nama_pelapor']  =  $request->get('nama_pbb');
                $pelapor['ada_nik_pelapor'] = $request->get('ada_nik_pbb');
                $pelapor['nik_pelapor'] = $request->get('nik_pbb');
                $pelapor['status_dtks_pelapor'] = $request->get('status_dtks_pbb');
                $pelapor['tempat_lahir_pelapor'] = $request->get('tempat_lahir_pbb');
                $pelapor['tanggal_lahir_pelapor'] = $request->get('tgl_lahir_pbb');
                $pelapor['jenis_kelamin'] = $request->get('jenis_kelamin_pbb');
                // $pelapor['nama_pelapor']  = $request->get('nama_pbb');
                $pelapor['telepon_pelapor'] = $request->get('telp_pbb');
                $pelapor['alamat_pelapor'] = $request->get('alamat_pbb');
                $pelapor['createdby_pelapor'] = Auth::user()->id;
                $pelapor['updatedby_pelapor'] = Auth::user()->id;

                $pelapor->save();
            } 

            return redirect('rekomendasi_keringanan_pbbs')->withWarning('NIK Tidak Tersedia Data Disimpan sebagai draft');
        }
    }

    public function cekIdPBB(Request $request, $Nik)
    {
        $found = false;
        $table2 = DB::table('dtks')->where('nik_pbb', $Nik)->first();
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
     * Display the specified rekomendasi_keringanan_pbb.
     */
    public function show($id)
    {
        $userid = Auth::user()->id;
        // $rekomendasiAdminKependudukan = $this->rekomendasiAdminKependudukanRepository->find((int) $id);
       
        $rekomendasiKeringananPbbPelapor = DB::table('rekomendasi_keringanan_pbbs')
            ->join('pelapor', function ($join) use ($id) {
                $join->on('rekomendasi_keringanan_pbbs.id', '=', 'pelapor.id_form')
                    ->where('rekomendasi_keringanan_pbbs.id', '=', $id);
            })
            ->select('rekomendasi_keringanan_pbbs.*', 'pelapor.*')
            ->where('pelapor.id_menu', '02')
            ->where('pelapor.id_form', $id)
            ->first();
        // dd($rekomendasiKeringananPbbPelapor);
        $rekomendasiKeringananPbb = DB::table('rekomendasi_keringanan_pbbs as w')->select(
            'w.*',
            'rls.name as name_roles',
            'usr.name',
            'prov.name_prov',
            'kota.name_cities',
            'kecamatan.name_districts',
            'b.name_village',
        )
            ->leftjoin('roles as rls', 'rls.id', '=', 'w.tujuan_pbb')
            ->leftjoin('users as usr', 'usr.id', '=', 'w.petugas_pbb')
            ->leftjoin('indonesia_provinces as prov', 'prov.code', '=', 'w.id_provinsi_pbb')
            ->leftjoin('indonesia_cities as kota', 'kota.code', '=', 'w.id_kabkot_pbb')
            ->leftjoin('indonesia_districts as kecamatan', 'kecamatan.code', '=', 'w.id_kecamatan_pbb')
            ->leftjoin('indonesia_villages as b', 'b.code', '=', 'w.id_kelurahan_pbb')
            ->where('w.id', $id)->first();

        // dd($rekomendasiAdminKependudukan);
        $data = DB::table('pelapor')
            ->join('rekomendasi_admin_kependudukans', 'pelapor.id_form', '=', 'rekomendasi_admin_kependudukans.id')
            ->select('pelapor.*', 'rekomendasi_admin_kependudukans.*')
            ->get();
        $log_pbb = DB::table('log_pbbs as w')->select(
                'w.*',
                'rls.name as name_update',
                'usr.name',
                'roles.name as name_roles',
            )
                ->leftjoin('roles', 'roles.id', '=', 'w.tujuan_pbbs')
                ->leftjoin('users as rls', 'rls.id', '=', 'w.updated_by_pbbs')
                ->leftjoin('users as usr', 'usr.id', '=', 'w.updated_by_pbbs')
                ->where('w.id_trx_pbbs', $id)->get();

        $roleid = DB::table('roles')
            ->where('name', 'Back Ofiice kelurahan')
            // ->where('name', 'supervisor')
            ->orWhere('name', 'supervisor')
            ->get();
        $checkroles = DB::table('model_has_roles')
            ->leftjoin('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->get();
        // dd($log_minkep); 
        return view('rekomendasi_keringanan_pbbs.show', compact('rekomendasiKeringananPbbPelapor','rekomendasiKeringananPbb', 'roleid', 'checkroles', 'log_pbb'));
    }

    /**
     * Show the form for editing the specified rekomendasi_keringanan_pbb.
     */
    public function edit($id)
    {
        $getUsers = DB::table('model_has_roles')
            ->leftjoin('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->leftjoin('rekomendasi_keringanan_pbbs', 'rekomendasi_keringanan_pbbs.createdby_pbb', '=', 'model_has_roles.model_id')
            ->where('rekomendasi_keringanan_pbbs.id', '=', $id)
            // ->where('status_aksi', '=', 'Draft')
            // ->orwhere('status_aksi', '=', 'Teruskan')
            ->get();

        $users =  Auth::user()->id;
        $getAuth = DB::table('model_has_roles')
            ->leftJoin('users', 'users.id', '=', 'model_has_roles.model_id')
            ->leftJoin('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('model_id', $users)
            ->get();

        $createdby = DB::table('rekomendasi_keringanan_pbbs')
            ->join('users', 'rekomendasi_keringanan_pbbs.createdby_pbb', '=', 'users.name')
            ->join('model_has_roles', 'model_has_roles.model_id', '=', 'users.id')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->select('rekomendasi_keringanan_pbbs.id', 'rekomendasi_keringanan_pbbs.createdby_pbb', 'roles.name')
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
            ->get()
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

        // $rekomendasiAdminKependudukan = $this->rekomendasiAdminKependudukanRepository->find($id);
        $rekomendasiKeringananPbb = DB::table('rekomendasi_keringanan_pbbs as w')->select(
            'w.*',
            'rls.name',
            'usr.name',
            'prov.name_prov',
            'kota.name_cities',
            'kecamatan.name_districts',
            'b.name_village',
        )
            ->leftjoin('roles as rls', 'rls.id', '=', 'w.tujuan_pbb')
            ->leftjoin('users as usr', 'usr.id', '=', 'w.petugas_pbb')
            ->leftjoin('indonesia_provinces as prov', 'prov.code', '=', 'w.id_provinsi_pbb')
            ->leftjoin('indonesia_cities as kota', 'kota.code', '=', 'w.id_kabkot_pbb')
            ->leftjoin('indonesia_districts as kecamatan', 'kecamatan.code', '=', 'w.id_kecamatan_pbb')
            ->leftjoin('indonesia_villages as b', 'b.code', '=', 'w.id_kelurahan_pbb')
            ->where('w.id', $id)->first();
        // dd($rekomendasiKeringananPbb);   
        $rekomendasiKeringananPbbPelapor = DB::table('rekomendasi_keringanan_pbbs')
        ->join('pelapor', function ($join) use ($id) {
            $join->on('rekomendasi_keringanan_pbbs.id', '=', 'pelapor.id_form')
                ->where('rekomendasi_keringanan_pbbs.id', '=', $id);
        })
        ->select('rekomendasi_keringanan_pbbs.*', 'pelapor.*')
        ->where('pelapor.id_menu', '02')
        ->where('pelapor.id_form', $id)
        ->first();

        return view('rekomendasi_keringanan_pbbs.edit', compact('rekomendasiKeringananPbb', 'datawilayah', 'rekomendasiKeringananPbbPelapor', 'roleid', 'getdata', 'alur', 'createdby', 'getUsers', 'getAuth'));
    }

    /**
     * Update the specified rekomendasi_keringanan_pbb in storage.
     */
    public function update($id, Request $request)
    {
		$userid = Auth::user()->id;
		// $dataRekomendasiKeringananPbb = rekomendasi_biaya_perawatan::where('id', $id)->first();
		$dataRekomendasiKeringananPbb = rekomendasi_keringanan_pbb::find($id);
		$pemebuatanDataRekomendasiPbb = DB::table('rekomendasi_keringanan_pbbs as w')
		->join('model_has_roles', 'model_has_roles.model_id', '=', 'w.createdby_pbb')
		->join('roles as rls', 'rls.id', '=', 'model_has_roles.role_id')

		->leftjoin('users', 'users.id', '=', 'w.createdby_pbb')
		->select(
					'w.*',
					'rls.name as name_roles',
					// 'usr.name',
					'model_has_roles.*')
		->where('w.id', $id)->first();
        // dd($pemebuatanDataRekomendasiPbb);
        $data = $request->all();
        $files = [
            'file_ktp_terlapor_pbb',
            'file_kk_terlapor_pbb',
            'file_keterangan_dtks_pbb',
            'file_pendukung_pbb',

        ];
        foreach ($files as $file) {
            if ($request->file($file)) {
                $path = $request->file($file);
                $filename = $file . $path->getClientOriginalName();
                $return = Storage::disk('imagekit')->put($filename, fopen($path->getRealPath(), 'r'));
                $data[$file] = Storage::disk('imagekit')->url($filename);
            } else {
                $data[$file] = $dataRekomendasiKeringananPbb->$file;
            }
        }
        if($request->get('status_aksi_pbb') == 'Kembalikan' || $request->get('status_aksi_pbb') == 'Selesai'){
            $data['tujuan_pbb'] = $pemebuatanDataRekomendasiPbb->role_id;
            $data['petugas_pbb'] = $pemebuatanDataRekomendasiPbb->model_id;
        }
        // $log->created_by_biper = Auth::user()->id;
		// $log->updated_by_biper = Auth::user()->id;
        //   dd($data);
        $dataRekomendasiKeringananPbb->update($data);
	
		// rekomendasi_biaya_perawatan::where('id', $id)->update($biper);
		$log = new log_pbbs();
		$log->id_trx_pbbs = $dataRekomendasiKeringananPbb->id;
		$log->id_alur_pbbs = $request->get('status_aksi_pbb');
		$log->catatan_pbbs = $request->get('catatan_pbb');
		$log->file_pendukung_pbbs = $request->get('file_pendukung_pbbs');
        if($request->get('status_aksi_pbb') == 'Kembalikan' || $request->get('status_aksi_pbb') == 'Selesai'){
			$log->petugas_pbbs = $pemebuatanDataRekomendasiPbb->model_id;
			$log->tujuan_pbbs = $pemebuatanDataRekomendasiPbb->role_id;
			// $dataRekomendasiKeringananPbb->updatedby_pbbs = ;
		}else{
            // dd($dataRekomendasiKeringananPbb->tujuan_pbb);
			$log->petugas_pbbs = $dataRekomendasiKeringananPbb->petugas_pbb;
			$log->tujuan_pbbs = $dataRekomendasiKeringananPbb->tujuan_pbb;
		}
	
		$log->created_by_pbbs = Auth::user()->id;
		$log->updated_by_pbbs = Auth::user()->id;
		// dd($log);
		$log->save();
            return redirect('rekomendasi_keringanan_pbbs')->withSuccess('Data Berhasil Diubah');
    }

    /**
     * Remove the specified rekomendasi_keringanan_pbb from storage.
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $rekomendasiKeringananPbb = $this->rekomendasiKeringananPbbRepository->find($id);

        if (empty($rekomendasiKeringananPbb)) {
            Flash::error('Rekomendasi Keringanan Pbb not found');

            return redirect(route('rekomendasi_keringanan_pbbs.index'));
        }

        $this->rekomendasiKeringananPbbRepository->delete($id);

        Flash::success('Rekomendasi Keringanan Pbb deleted successfully.');

        return redirect(route('rekomendasi_keringanan_pbbs.index'));
    }

    public function draft(Request $request)
    {
        $user_name = Auth::user()->name;

        $query = DB::table('rekomendasi_keringanan_pbbs')
            ->leftjoin('users', 'users.id', '=', 'rekomendasi_keringanan_pbbs.petugas_pbb')
            ->leftjoin('wilayahs', 'wilayahs.createdby', '=', 'rekomendasi_keringanan_pbbs.createdby_pbb')
            ->leftjoin('model_has_roles', 'model_has_roles.model_id', '=', 'rekomendasi_keringanan_pbbs.tujuan_pbb')
            ->leftjoin('indonesia_villages as b', 'b.code', '=', 'rekomendasi_keringanan_pbbs.id_kelurahan_pbb')
            ->leftjoin('indonesia_districts as d', 'd.code', '=', 'rekomendasi_keringanan_pbbs.id_kecamatan_pbb')
            ->select('rekomendasi_keringanan_pbbs.*', 'b.name_village','d.name_districts','users.name')
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
        if ($user_wilayah->name == 'Front Office Kelurahan'|| $user_wilayah == 'fasilitator') {
            $query->orWhere(function ($query) use ($user_wilayah) {
                $query->where('rekomendasi_keringanan_pbbs.id_kelurahan_pbb', $user_wilayah->kelurahan_id);
                $query->where('rekomendasi_keringanan_pbbs.status_aksi_pbb', 'Draft');
                $query->where('rekomendasi_keringanan_pbbs.createdby_pbb',  Auth::user()->id);
            });
        }else{

                $query->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_keringanan_pbbs.id_kabkot_pbb', $user_wilayah->kota_id);
                    $query->where('rekomendasi_keringanan_pbbs.status_aksi_pbb', 'Draft');
                    $query->where('rekomendasi_keringanan_pbbs.createdby_pbb',  Auth::user()->id);
                });
            
        }

        if ($user_wilayah->name == 'Front Office Kelurahan'|| $user_wilayah == 'fasilitator') {
            if ($request->has('search') && !empty($request->search['value'])) {
                $search = $request->search['value'];
                $query = DB::table('rekomendasi_keringanan_pbbs')
                    ->leftjoin('users', 'users.id', '=', 'rekomendasi_keringanan_pbbs.petugas_pbb')
                    ->leftjoin('wilayahs', 'wilayahs.createdby', '=', 'rekomendasi_keringanan_pbbs.createdby_pbb')
                    ->leftjoin('model_has_roles', 'model_has_roles.model_id', '=', 'rekomendasi_keringanan_pbbs.tujuan_pbb')
                    ->leftjoin('indonesia_villages as b', 'b.code', '=', 'rekomendasi_keringanan_pbbs.id_kelurahan_pbb')
                    ->leftjoin('indonesia_districts as d', 'd.code', '=', 'rekomendasi_keringanan_pbbs.id_kecamatan_pbb')
                    ->select('rekomendasi_keringanan_pbbs.*', 'b.name_village','d.name_districts','users.name')
                    ->distinct();
                $query->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_keringanan_pbbs.id_kelurahan_pbb', $user_wilayah->kelurahan_id);
                    $query->where('rekomendasi_keringanan_pbbs.status_aksi_pbb', 'Draft');
                    $query->where('rekomendasi_keringanan_pbbs.createdby_pbb',  Auth::user()->id);
                })
                ->where('rekomendasi_keringanan_pbbs.no_pendaftaran_pbb', 'like', "%$search%");
                // dd($query);
            }
        }elseif($user_wilayah->name == 'Front Office Kota')  {
            if ($request->has('search') && !empty($request->search['value'])) {
                $search = $request->search['value'];
                $query = DB::table('rekomendasi_keringanan_pbbs')
                    ->leftjoin('users', 'users.id', '=', 'rekomendasi_keringanan_pbbs.petugas_pbb')
                    ->leftjoin('wilayahs', 'wilayahs.createdby', '=', 'rekomendasi_keringanan_pbbs.createdby_pbb')
                    ->leftjoin('model_has_roles', 'model_has_roles.model_id', '=', 'rekomendasi_keringanan_pbbs.tujuan_pbb')
                    ->leftjoin('indonesia_villages as b', 'b.code', '=', 'rekomendasi_keringanan_pbbs.id_kelurahan_pbb')
                    ->leftjoin('indonesia_districts as d', 'd.code', '=', 'rekomendasi_keringanan_pbbs.id_kecamatan_pbb')
                    ->select('rekomendasi_keringanan_pbbs.*', 'b.name_village','d.name_districts','users.name')
                    ->distinct();
                $query->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_keringanan_pbbs.id_kabkot_pbb', $user_wilayah->kota_id);
                    $query->where('rekomendasi_keringanan_pbbs.status_aksi_pbb', 'Draft');
                    $query->where('rekomendasi_keringanan_pbbs.createdby_pbb',  Auth::user()->id);
                })
                ->where('rekomendasi_keringanan_pbbs.no_pendaftaran_pbb', 'like', "%$search%");
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
            'recordsTotal' => rekomendasi_keringanan_pbb::count(),
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
        if ($user_wilayah->name == 'Front Office Kelurahan') {
            $query = DB::table('rekomendasi_keringanan_pbbs')
                ->join('users', 'users.id', '=', 'rekomendasi_keringanan_pbbs.petugas_pbb')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_keringanan_pbbs.id_kecamatan_pbb')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_keringanan_pbbs.id_kelurahan_pbb')
                ->select('rekomendasi_keringanan_pbbs.*', 'b.name_village', 'd.name_districts', 'users.name');
        } elseif ($user_wilayah->name == 'Front Office kota') {
            $query = DB::table('rekomendasi_keringanan_pbbs')
                ->join('users', 'users.id', '=', 'rekomendasi_keringanan_pbbs.petugas_pbb')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_keringanan_pbbs.id_kecamatan_pbb')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_keringanan_pbbs.id_kelurahan_pbb')
                ->select('rekomendasi_keringanan_pbbs.*', 'b.name_village', 'd.name_districts', 'users.name');
        }elseif ($user_wilayah->name == 'supervisor') {
            $query = DB::table('rekomendasi_keringanan_pbbs')
                ->join('users', 'users.id', '=', 'rekomendasi_keringanan_pbbs.petugas_pbb')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_keringanan_pbbs.id_kecamatan_pbb')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_keringanan_pbbs.id_kelurahan_pbb')
                ->select('rekomendasi_keringanan_pbbs.*', 'b.name_village', 'd.name_districts', 'users.name');
        }elseif ($user_wilayah->name == 'Back Ofiice kelurahan') {
            $query = DB::table('rekomendasi_keringanan_pbbs')
                ->join('users', 'users.id', '=', 'rekomendasi_keringanan_pbbs.petugas_pbb')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_keringanan_pbbs.id_kecamatan_pbb')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_keringanan_pbbs.id_kelurahan_pbb')
                ->select('rekomendasi_keringanan_pbbs.*', 'b.name_village', 'd.name_districts', 'users.name');
        } elseif ($user_wilayah->name == 'Back Ofiice Kota') {
            $query = DB::table('rekomendasi_keringanan_pbbs')
                ->join('users', 'users.id', '=', 'rekomendasi_keringanan_pbbs.petugas_pbb')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_keringanan_pbbs.id_kecamatan_pbb')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_keringanan_pbbs.id_kelurahan_pbb')
                ->select('rekomendasi_keringanan_pbbs.*', 'b.name_village', 'd.name_districts', 'users.name');
        }elseif ($user_wilayah->name == 'kepala bidang') {
            $query = DB::table('rekomendasi_keringanan_pbbs')
                ->join('users', 'users.id', '=', 'rekomendasi_keringanan_pbbs.petugas_pbb')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_keringanan_pbbs.id_kecamatan_pbb')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_keringanan_pbbs.id_kelurahan_pbb')
                ->select('rekomendasi_keringanan_pbbs.*', 'b.name_village', 'd.name_districts', 'users.name');
        } else {
            $query = DB::table('rekomendasi_keringanan_pbbs')
                ->join('users', 'users.id', '=', 'rekomendasi_keringanan_pbbs.petugas_pbb')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_keringanan_pbbs.id_kelurahan_pbb')
                ->select('rekomendasi_keringanan_pbbs.*', 'b.name_village');
        }
        if ($user_wilayah->name == 'Front Office Kelurahan') {
            //  dd($user_wilayah->role_id);

            $query->orWhere(function ($query) use ($user_wilayah) {
                $query->where('rekomendasi_keringanan_pbbs.id_kelurahan_pbb', '=', $user_wilayah->kelurahan_id)
                    ->where('rekomendasi_keringanan_pbbs.tujuan_pbb', '=', $user_wilayah->role_id)
                    ->where(function ($query) {
                        $query->where('rekomendasi_keringanan_pbbs.status_aksi_pbb', '=', 'Teruskan')
                            ->orWhere('rekomendasi_keringanan_pbbs.status_aksi_pbb', '=', 'kembalikan');
                    });
            });
        }
        if ($user_wilayah->name == 'Back Ofiice kelurahan') {
            //  dd($user_wilayah->role_id);

            $query->orWhere(function ($query) use ($user_wilayah) {
                $query->where('rekomendasi_keringanan_pbbs.id_kelurahan_pbb', '=', $user_wilayah->kelurahan_id)
                    ->where('rekomendasi_keringanan_pbbs.tujuan_pbb', '=', $user_wilayah->role_id)
                    ->where(function ($query) {
                        $query->where('rekomendasi_keringanan_pbbs.status_aksi_pbb', '=', 'Teruskan')
                            ->orWhere('rekomendasi_keringanan_pbbs.status_aksi_pbb', '=', 'kembalikan');
                    });
            });
        }
        if ($user_wilayah->name == 'Front Office kota') {
            //  dd($user_wilayah->role_id);

            $query->orWhere(function ($query) use ($user_wilayah) {
                $query->where('rekomendasi_keringanan_pbbs.id_kelurahan_pbb', '=', $user_wilayah->kelurahan_id)
                    ->where('rekomendasi_keringanan_pbbs.tujuan_pbb', '=', $user_wilayah->role_id)
                    ->where(function ($query) {
                        $query->where('rekomendasi_keringanan_pbbs.status_aksi_pbb', '=', 'Teruskan')
                            ->orWhere('rekomendasi_keringanan_pbbs.status_aksi_pbb', '=', 'kembalikan');
                    });
            });
        }
        if ($user_wilayah->name == 'Back Ofiice Kota') {
            //  dd($user_wilayah->role_id);
            $query->orWhere(function ($query) use ($user_wilayah) {
                $query->where('rekomendasi_keringanan_pbbs.id_kelurahan_pbb', '=', $user_wilayah->kelurahan_id)
                    ->where('rekomendasi_keringanan_pbbs.tujuan_pbb', '=', $user_wilayah->role_id)
                    ->where(function ($query) {
                        $query->where('rekomendasi_keringanan_pbbs.status_aksi_pbb', '=', 'Teruskan')
                            ->orWhere('rekomendasi_keringanan_pbbs.status_aksi_pbb', '=', 'kembalikan');
                    });
            });
        }
        if ($user_wilayah->name == 'kepala bidang') {

            $query->orWhere(function ($query) use ($user_wilayah) {
                $query->where('rekomendasi_keringanan_pbbs.id_kabkot_pbb', '=', $user_wilayah->kota_id)
                    ->where('rekomendasi_keringanan_pbbs.tujuan_pbb', '=', $user_wilayah->role_id)
                    ->where('rekomendasi_keringanan_pbbs.petugas_pbb', '=', $user_wilayah->model_id)
                    ->where(function ($query) {
                        $query->where('rekomendasi_keringanan_pbbs.status_aksi_pbb', '=', 'Teruskan')
                            ->orWhere('rekomendasi_keringanan_pbbs.status_aksi_pbb', '=', 'kembalikan');
                    });
                // dd($va);
            });
        }
        if ($user_wilayah->name == 'supervisor') {
            // dd($user_wilayah);
            $query->orWhere(function ($query) use ($user_wilayah) {
                $query->where('rekomendasi_keringanan_pbbs.id_kecamatan_pbb', '=', $user_wilayah->kecamatan_id)
                    ->where('rekomendasi_keringanan_pbbs.tujuan_pbb', '=', $user_wilayah->role_id)
                    ->where('rekomendasi_keringanan_pbbs.petugas_pbb', '=', $user_wilayah->model_id)
                    ->where(function ($query) {
                        $query->where('rekomendasi_keringanan_pbbs.status_aksi_pbb', '=', 'Teruskan')
                            ->orWhere('rekomendasi_keringanan_pbbs.status_aksi_pbb', '=', 'kembalikan');
                    });
                // dd($va);
            });
        }
        if ($user_wilayah->name == 'KepalaDinas') {
            // dd($user_wilayah);
            $query->orWhere(function ($query) use ($user_wilayah) {
                $query->where('rekomendasi_keringanan_pbbs.id_kelurahan_pbb', '=', $user_wilayah->kelurahan_id)
                    ->where('rekomendasi_keringanan_pbbs.tujuan_pbb', '=', $user_wilayah->role_id)
                    ->where('rekomendasi_keringanan_pbbs.petugas_pbb', '=', $user_wilayah->model_id)
                    ->where(function ($query) {
                        $query->where('rekomendasi_keringanan_pbbs.status_aksi_pbb', '=', 'Teruskan')
                            ->orWhere('rekomendasi_keringanan_pbbs.status_aksi_pbb', '=', 'kembalikan');
                    });
                // dd($va);
            });
        }
        if ($user_wilayah->name == 'Front Office Kelurahan' || $user_wilayah->name == 'Back Ofiice kelurahan'|| $user_wilayah->name == 'fasilitator') {
            if ($request->has('search') && !empty($request->search['value'])) {
                $search = $request->search['value'];
                $query->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_keringanan_pbbs.id_kelurahan_pbb', '=', $user_wilayah->kelurahan_id)
                        ->where('rekomendasi_keringanan_pbbs.tujuan_pbb', '=', $user_wilayah->role_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_keringanan_pbbs.status_aksi_pbb', '=', 'Teruskan')
                                ->orWhere('rekomendasi_keringanan_pbbs.status_aksi_pbb', '=', 'kembalikan');
                        });
                    })->where(function ($query) use ($search) {
                        $query->where('rekomendasi_keringanan_pbbs.no_pendaftaran_pbb', 'like', "%$search%");
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
                    $query->where('rekomendasi_keringanan_pbbs.id_kabkot_pbb', '=', $user_wilayah->kota_id)
                        ->where('rekomendasi_keringanan_pbbs.tujuan_pbb', '=', $user_wilayah->role_id)
                        ->where('rekomendasi_keringanan_pbbs.petugas_pbb', '=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_keringanan_pbbs.status_aksi_pbb', '=', 'Teruskan')
                                ->orWhere('rekomendasi_keringanan_pbbs.status_aksi_pbb', '=', 'kembalikan');
                        });
                    // dd($va);
                })->where(function ($query) use ($search) {
                    $query->where('rekomendasi_keringanan_pbbs.no_pendaftaran_pbb', 'like', "%$search%");
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
        // dd($request->all());
        //Add paginate
        $start = $request->start;
        $length = $request->length;
        $query->offset($start)->limit($length);
        $data = $query->get();


        return response()->json([
            'draw' => $request->input('draw'),
            'recordsTotal' => rekomendasi_keringanan_pbb::count(),
            'recordsFiltered' => $total_filtered_items,
            'data' => $data,
        ]);
    }
    public function teruskan(Request $request)
    {
        $user_name = Auth::user()->name;
        // dd($user_name);

        $query = DB::table('rekomendasi_keringanan_pbbs')
            ->join('users', 'users.id', '=', 'rekomendasi_keringanan_pbbs.petugas_pbb')
            ->join('log_pbbs', 'log_pbbs.id_trx_pbbs', '=', 'rekomendasi_keringanan_pbbs.id')
            // ->join('model_has_roles', 'model_has_roles.role_id', '=', 'rekomendasi_keringanan_pbbs.tujuan_pbb')
            ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_keringanan_pbbs.id_kelurahan_pbb')

            ->select('rekomendasi_keringanan_pbbs.*', 'b.name_village');
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
        
        if ($user_wilayah->name == 'Front Office Kelurahan' || $user_wilayah->name == 'Back Ofiice kelurahan'|| $user_wilayah == 'fasilitator') {
            $query = DB::table('rekomendasi_keringanan_pbbs')
            ->leftjoin('indonesia_districts as d', 'd.code', '=', 'rekomendasi_keringanan_pbbs.id_kecamatan_pbb')
            ->leftjoin('indonesia_villages as b', 'b.code', '=', 'rekomendasi_keringanan_pbbs.id_kelurahan_pbb')
            // ->leftjoin('roles as r', 'r.id', '=', 'rekomendasi_keringanan_pbbs.petugas_pbb')
            ->leftjoin('users', 'users.id', '=', 'rekomendasi_keringanan_pbbs.petugas_pbb')
            ->select('rekomendasi_keringanan_pbbs.*', 'b.name_village', 'd.name_districts','users.name')
            // ->selectRaw('IFNULL(r.name,"") name')
            ->where('rekomendasi_keringanan_pbbs.id_kelurahan_pbb', '=', $user_wilayah->kelurahan_id)
            ->whereIn('rekomendasi_keringanan_pbbs.status_aksi_pbb', ['Teruskan','Kembalikan'])
            ->where('rekomendasi_keringanan_pbbs.petugas_pbb', '<>', $user_id)
			->whereRaw("(SELECT COUNT(l.id) FROM log_pbbs as l WHERE l.id_trx_pbbs = rekomendasi_keringanan_pbbs.id AND l.created_by_pbbs = '".$user_id."') > 0 ");
            // dd($query);
        }elseif($user_wilayah->name == 'Front Office Kota' || $user_wilayah->name == 'Back Ofiice Kota'|| $user_wilayah->name == 'KepalaDinas'||$user_wilayah->name == 'SekertarisDinas') {
            $query = DB::table('rekomendasi_keringanan_pbbs')
            ->leftjoin('indonesia_districts as d', 'd.code', '=', 'rekomendasi_keringanan_pbbs.id_kecamatan_pbb')
            ->leftjoin('indonesia_villages as b', 'b.code', '=', 'rekomendasi_keringanan_pbbs.id_kelurahan_pbb')
            // ->leftjoin('roles as r', 'r.id', '=', 'rekomendasi_keringanan_pbbs.petugas_pbb')
            ->leftjoin('users', 'users.id', '=', 'rekomendasi_keringanan_pbbs.petugas_pbb')
            ->select('rekomendasi_keringanan_pbbs.*', 'b.name_village', 'd.name_districts','users.name')
            // ->selectRaw('IFNULL(r.name,"") name')
            ->where('rekomendasi_keringanan_pbbs.id_kabkota_pbb', '=', $user_wilayah->kota_id)
            ->whereIn('rekomendasi_keringanan_pbbs.status_aksi_pbb', ['Teruskan','Kembalikan'])
            ->where('rekomendasi_keringanan_pbbs.petugas_pbb', '<>', $user_id)
            ->whereRaw("(SELECT COUNT(l.id) FROM log_pbbs as l WHERE l.id_trx_pbbs = rekomendasi_keringanan_pbbs.id AND l.created_by_pbbs = '".$user_id."') > 0 ");
            // dd($query);
        }else{
            $query = DB::table('rekomendasi_keringanan_pbbs')
            ->leftjoin('indonesia_districts as d', 'd.code', '=', 'rekomendasi_keringanan_pbbs.id_kecamatan_pbb')
            ->leftjoin('indonesia_villages as b', 'b.code', '=', 'rekomendasi_keringanan_pbbs.id_kelurahan_pbb')
            // ->leftjoin('roles as r', 'r.id', '=', 'rekomendasi_keringanan_pbbs.petugas_pbb')
            ->leftjoin('users', 'users.id', '=', 'rekomendasi_keringanan_pbbs.petugas_pbb')
            ->select('rekomendasi_keringanan_pbbs.*', 'b.name_village', 'd.name_districts','users.name')
            // ->selectRaw('IFNULL(r.name,"") name')
            ->where('rekomendasi_keringanan_pbbs.id_kecamatan_pbb', '=', $user_wilayah->kecamatan_id)
            ->whereIn('rekomendasi_keringanan_pbbs.status_aksi_pbb', ['Teruskan','Kembalikan'])
            ->where('rekomendasi_keringanan_pbbs.petugas_pbb', '<>', $user_id)
            ->whereRaw("(SELECT COUNT(l.id) FROM log_pbbs as l WHERE l.id_trx_pbbs = rekomendasi_keringanan_pbbs.id AND l.updated_by_pbbs = '".$user_id."') > 0 ");
            // dd($query);
        }
        if ($user_wilayah->name == 'Front Office Kelurahan' || $user_wilayah->name == 'Back Ofiice kelurahan'|| $user_wilayah->name == 'fasilitator') {
            if ($request->has('search') && !empty($request->search['value'])) {
                $search = $request->search['value'];
                $query = DB::table('rekomendasi_keringanan_pbbs')
                ->leftjoin('indonesia_districts as d', 'd.code', '=', 'rekomendasi_keringanan_pbbs.id_kecamatan_pbb')
                ->leftjoin('indonesia_villages as b', 'b.code', '=', 'rekomendasi_keringanan_pbbs.id_kelurahan_pbb')
                // ->leftjoin('roles as r', 'r.id', '=', 'rekomendasi_keringanan_pbbs.petugas_pbb')
                ->leftjoin('users', 'users.id', '=', 'rekomendasi_keringanan_pbbs.petugas_pbb')
                ->select('rekomendasi_keringanan_pbbs.*', 'b.name_village', 'd.name_districts','users.name')
                // ->selectRaw('IFNULL(r.name,"") name')
                ->where('rekomendasi_keringanan_pbbs.id_kecamatan_pbb', '=', $user_wilayah->kecamatan_id)
                ->whereIn('rekomendasi_keringanan_pbbs.status_aksi_pbb', ['Teruskan','Kembalikan'])
                ->where('rekomendasi_keringanan_pbbs.petugas_pbb', '<>', $user_id)
                ->whereRaw("(SELECT COUNT(l.id) FROM log_pbbs as l WHERE l.id_trx_pbbs = rekomendasi_keringanan_pbbs.id AND l.updated_by_pbbs = '".$user_id."') > 0 ")
                ->where(function ($query) use ($search) {
                        $query->where('rekomendasi_keringanan_pbbs.no_pendaftaran_pbb', 'like', "%$search%");
                            // ->orwhere('indonesia_villages.name_village', 'like', "%$search%")
                            // ->orwhere('d.name_districts', 'like', "%$search%")
                            // ->orwhere('indonesia_villages.name_village', 'like', "%$search%")
                            // ->orwhere('rekomendasi_biaya_perawatans.alamat_biper', 'like', "%$search%");
                    });
            }
        }elseif($user_wilayah->name == 'Front Office Kota' || $user_wilayah->name == 'Back Ofiice Kota'|| $user_wilayah->name == 'KepalaDinas'||$user_wilayah->name == 'SekertarisDinas'||$user_wilayah->name == 'Supervisor') {
            if ($request->has('search') && !empty($request->search['value'])) {
                $search = $request->search['value'];
                $query = DB::table('rekomendasi_keringanan_pbbs')
                ->join('users', 'users.id', '=', 'rekomendasi_keringanan_pbbs.petugas_pbb')
                // ->join('log_pbbs', 'log_pbbs.id_trx_pbbs', '=', 'rekomendasi_keringanan_pbbs.id')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_keringanan_pbbs.id_kecamatan_pbb')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_keringanan_pbbs.id_kelurahan_pbb')
                ->select('rekomendasi_keringanan_pbbs.*', 'b.name_village', 'd.name_districts','users.name')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_keringanan_pbbs.id_kabkot_pbb', $user_wilayah->kota_id)
                        ->where('rekomendasi_keringanan_pbbs.tujuan_pbb', '!=', $user_wilayah->role_id)
                        // ->where('log_pbbs.created_by_pbbs', '=', auth::user()->id)
                        // ->where('rekomendasi_keringanan_pbbs.petugas_pbb','!=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_keringanan_pbbs.status_aksi_pbb', '=', 'Teruskan')
                                ->orWhere('rekomendasi_keringanan_pbbs.status_aksi_pbb', '=', 'kembalikan');
                        });
                    })->where(function ($query) use ($search) {
                        $query->where('rekomendasi_keringanan_pbbs.no_pendaftaran_pbb', 'like', "%$search%");
                            // ->orwhere('indonesia_villages.name_village', 'like', "%$search%")
                            // ->orwhere('d.name_districts', 'like', "%$search%")
                            // ->orwhere('indonesia_villages.name_village', 'like', "%$search%")
                            // ->orwhere('rekomendasi_biaya_perawatans.alamat_biper', 'like', "%$search%");
                    });
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
        $length = $request->length;
        $query->offset($start)->limit($length);
        $data = $query->get();

        return response()->json([
            'draw' => $request->input('draw'),
            'recordsTotal' => rekomendasi_keringanan_pbb::count(),
            'recordsFiltered' => $total_filtered_items,
            'data' => $data,
        ]);
    }
    public function selesai(Request $request)
    {
        $user_name = Auth::user()->name;
        $query = DB::table('rekomendasi_keringanan_pbbs')
            ->join('users', 'users.id', '=', 'rekomendasi_keringanan_pbbs.petugas_pbb')
            ->join('log_pbbs', 'log_pbbs.id_trx_pbbs', '=', 'rekomendasi_keringanan_pbbs.id')
            ->join('model_has_roles', 'model_has_roles.model_id', '=', 'rekomendasi_keringanan_pbbs.tujuan_pbb')
            ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_keringanan_pbbs.id_kelurahan_pbb')
            ->select('rekomendasi_keringanan_pbbs.*', 'b.name_village');
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
            $query = DB::table('rekomendasi_keringanan_pbbs')
                ->join('users', 'users.id', '=', 'rekomendasi_keringanan_pbbs.petugas_pbb')
                // ->join('log_pbbs', 'log_pbbs.id_trx_pbbs', '=', 'rekomendasi_keringanan_pbbs.id')
                ->join('indonesia_villages', 'indonesia_villages.code', '=', 'rekomendasi_keringanan_pbbs.id_kelurahan_pbb')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_keringanan_pbbs.id_kecamatan_pbb')
                ->select('rekomendasi_keringanan_pbbs.*', 'd.name_districts', 'indonesia_villages.name_village','users.name')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_keringanan_pbbs.id_kelurahan_pbb', $user_wilayah->kelurahan_id)
                        // ->where('log_pbbs.tujuan_pbbs', '=', $user_wilayah->role_id)
                        // ->where('log_pbbs.created_by_pbbs', '!=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_keringanan_pbbs.status_aksi_pbb', '=', 'Tolak')
                                ->orWhere('rekomendasi_keringanan_pbbs.status_aksi_pbb', '=', 'Selesai');
                        });
                })->distinct();
        } elseif ($user_wilayah->name == 'Back Ofiice kelurahan') {
            //  dd($user_wilayah->role_id);
            $query = DB::table('rekomendasi_keringanan_pbbs')
                ->join('users', 'users.id', '=', 'rekomendasi_keringanan_pbbs.petugas_pbb')
                // ->join('log_pbbs', 'log_pbbs.id_trx_pbbs', '=', 'rekomendasi_keringanan_pbbs.id')
                ->join('indonesia_villages', 'indonesia_villages.code', '=', 'rekomendasi_keringanan_pbbs.id_kelurahan_pbb')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_keringanan_pbbs.id_kecamatan_pbb')
                ->select('rekomendasi_keringanan_pbbs.*', 'd.name_districts', 'indonesia_villages.name_village','users.name')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_keringanan_pbbs.id_kelurahan_pbb', $user_wilayah->kelurahan_id)
                        // ->where('log_pbbs.tujuan_pbbs', '=', $user_wilayah->role_id)
                        // ->where('log_pbbs.created_by_pbbs', '!=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_keringanan_pbbs.status_aksi_pbb', '=', 'Tolak')
                                ->orWhere('rekomendasi_keringanan_pbbs.status_aksi_pbb', '=', 'Selesai');
                        });
                })->distinct();
        }elseif ($user_wilayah->name == 'Front Office kota') {
            //  dd($user_wilayah->role_id);
            $query = DB::table('rekomendasi_keringanan_pbbs')
                ->join('users', 'users.id', '=', 'rekomendasi_keringanan_pbbs.petugas_pbb')
                // ->join('log_pbbs', 'log_pbbs.id_trx_pbbs', '=', 'rekomendasi_keringanan_pbbs.id')
                ->join('indonesia_villages', 'indonesia_villages.code', '=', 'rekomendasi_keringanan_pbbs.id_kelurahan_pbb')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_keringanan_pbbs.id_kecamatan_pbb')
                ->select('rekomendasi_keringanan_pbbs.*', 'd.name_districts', 'indonesia_villages.name_village','users.name')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_keringanan_pbbs.id_kelurahan_pbb', $user_wilayah->kelurahan_id)
                        // ->where('log_pbbs.tujuan_pbbs', '=', $user_wilayah->role_id)
                        // ->where('log_pbbs.created_by_pbbs', '!=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_keringanan_pbbs.status_aksi_pbb', '=', 'Tolak')
                                ->orWhere('rekomendasi_keringanan_pbbs.status_aksi_pbb', '=', 'Selesai');
                        });
                })->distinct();
        } elseif ($user_wilayah->name == 'Back Ofiice Kota') {
            // dd($user_wilayah);
            $query = DB::table('rekomendasi_keringanan_pbbs')
                ->join('users', 'users.id', '=', 'rekomendasi_keringanan_pbbs.petugas_pbb')
                // ->join('log_pbbs', 'log_pbbs.id_trx_pbbs', '=', 'rekomendasi_keringanan_pbbs.id')
                // ->join('model_has_roles', 'model_has_roles.model_id', '=', 'rekomendasi_keringanan_pbbs.tujuan_pbb')
                ->join('indonesia_villages', 'indonesia_villages.code', '=', 'rekomendasi_keringanan_pbbs.id_kelurahan_pbb')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_keringanan_pbbs.id_kecamatan_pbb')
                ->select('rekomendasi_keringanan_pbbs.*', 'd.name_districts', 'indonesia_villages.name_village', 'users.name')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_keringanan_pbbs.id_kelurahan_pbb', $user_wilayah->kelurahan_id)
                        // ->where('log_pbbs.tujuan_pbbs', '!=', $user_wilayah->role_id)
                        // ->where('log_pbbs.created_by_pbbs', '=', auth::user()->id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_keringanan_pbbs.status_aksi_pbb', '=', 'Tolak')
                                ->orWhere('rekomendasi_keringanan_pbbs.status_aksi_pbb', '=', 'Selesai');
                        });
                })->distinct();
            // dd($query); 
        } elseif ($user_wilayah->name == 'kepala bidang') {
            // dd($user_wilayah);
            $query = DB::table('rekomendasi_keringanan_pbbs')
                ->join('users', 'users.id', '=', 'rekomendasi_keringanan_pbbs.petugas_pbb')
                // ->join('log_pbbs', 'log_pbbs.id_trx_pbbs', '=', 'rekomendasi_keringanan_pbbs.id')
                // ->join('model_has_roles', 'model_has_roles.model_id', '=', 'rekomendasi_keringanan_pbbs.tujuan_pbb')
                ->join('indonesia_villages', 'indonesia_villages.code', '=', 'rekomendasi_keringanan_pbbs.id_kelurahan_pbb')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_keringanan_pbbs.id_kecamatan_pbb')
                ->select('rekomendasi_keringanan_pbbs.*', 'd.name_districts', 'indonesia_villages.name_village','users.name')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_keringanan_pbbs.id_kabkot_pbb', $user_wilayah->kota_id)
                        // ->where('log_pbbs.tujuan_pbbs', '!=', $user_wilayah->role_id)
                        // ->where('log_pbbs.created_by_pbbs', '=', auth::user()->id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_keringanan_pbbs.status_aksi_pbb', '=', 'Tolak')
                                ->orWhere('rekomendasi_keringanan_pbbs.status_aksi_pbb', '=', 'Selesai');
                        });
                })->distinct();
        } elseif ($user_wilayah->name == 'supervisor') {
            // dd($user_wilayah->role_id);
            $query = DB::table('rekomendasi_keringanan_pbbs')
                ->join('users', 'users.id', '=', 'rekomendasi_keringanan_pbbs.petugas_pbb')
                ->join('log_pbbs', 'log_pbbs.id_trx_pbbs', '=', 'rekomendasi_keringanan_pbbs.id')
                ->join('indonesia_villages', 'indonesia_villages.code', '=', 'rekomendasi_keringanan_pbbs.id_kelurahan_pbb')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_keringanan_pbbs.id_kecamatan_pbb')
                ->select('rekomendasi_keringanan_pbbs.*', 'd.name_districts', 'indonesia_villages.name_village','users.name')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_keringanan_pbbs.id_kecamatan_pbb', $user_wilayah->kecamatan_id)
                        // ->where('log_pbbs.tujuan_pbbs', '!=', $user_wilayah->role_id)
                        // ->where('log_pbbs.created_by_pbbs', '=', auth::user()->id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_keringanan_pbbs.status_aksi_pbb', '=', 'Tolak')
                                ->orWhere('rekomendasi_keringanan_pbbs.status_aksi_pbb', '=', 'Selesai');
                        });
                })->distinct();
        } elseif ($user_wilayah->name == 'KepalaDinas') {
            // dd($user_wilayah);
            $query = DB::table('rekomendasi_keringanan_pbbs')
                ->join('users', 'users.id', '=', 'rekomendasi_keringanan_pbbs.petugas_pbb')
                ->join('log_pbbs', 'log_pbbs.id_trx_pbbs', '=', 'rekomendasi_keringanan_pbbs.id')
                // ->join('model_has_roles', 'model_has_roles.model_id', '=', 'rekomendasi_keringanan_pbbs.tujuan_pbb')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_keringanan_pbbs.id_kecamatan_pbb')
                ->select('rekomendasi_keringanan_pbbs.*', 'd.name_districts', 'indonesia_villages.name_village','users.name')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_keringanan_pbbs.id_kabkot_pbb', $user_wilayah->kota_id)
                        ->where('log_pbbs.tujuan_pbbs', '=', $user_wilayah->role_id)
                        ->where('log_pbbs.petugas_pbbs', '=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_keringanan_pbbs.status_aksi_pbb', '=', 'Tolak')
                                ->orWhere('rekomendasi_keringanan_pbbs.status_aksi_pbb', '=', 'Selesai');
                        });
                });
        }

        if ($user_wilayah->name == 'Front Office Kelurahan' || $user_wilayah->name == 'Back Ofiice kelurahan'|| $user_wilayah->name == 'fasilitator') {
            if ($request->has('search') && !empty($request->search['value'])) {
                $search = $request->search['value'];
                $query = DB::table('rekomendasi_keringanan_pbbs')
                ->join('users', 'users.id', '=', 'rekomendasi_keringanan_pbbs.petugas_pbb')
                ->join('log_pbbs', 'log_pbbs.id_trx_pbbs', '=', 'rekomendasi_keringanan_pbbs.id')
                // ->join('model_has_roles', 'model_has_roles.model_id', '=', 'rekomendasi_keringanan_pbbs.tujuan_pbb')
                ->join('indonesia_villages', 'indonesia_villages.code', '=', 'rekomendasi_keringanan_pbbs.id_kelurahan_pbb')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_keringanan_pbbs.id_kecamatan_pbb')
                ->select('rekomendasi_keringanan_pbbs.*', 'd.name_districts', 'indonesia_villages.name_village','users.name')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_keringanan_pbbs.id_kelurahan_pbb', $user_wilayah->kelurahan_id)
                        // ->where('log_pbbs.tujuan_pbbs', '!=', $user_wilayah->role_id)
                        // ->where('log_pbbs.created_by_pbbs', '=', auth::user()->id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_keringanan_pbbs.status_aksi_pbb', '=', 'Tolak')
                                ->orWhere('rekomendasi_keringanan_pbbs.status_aksi_pbb', '=', 'Selesai');
                        });
                    })->where(function ($query) use ($search) {
                        $query->where('rekomendasi_keringanan_pbbs.no_pendaftaran_pbb', 'like', "%$search%");
                            // ->orwhere('indonesia_villages.name_village', 'like', "%$search%")
                            // ->orwhere('d.name_districts', 'like', "%$search%")
                            // ->orwhere('indonesia_villages.name_village', 'like', "%$search%")
                            // ->orwhere('rekomendasi_biaya_perawatans.alamat_biper', 'like', "%$search%");
                    });
            }
        }elseif($user_wilayah->name == 'Front Office Kota' || $user_wilayah->name == 'Back Ofiice Kota'|| $user_wilayah->name == 'KepalaDinas'||$user_wilayah->name == 'SekertarisDinas'||$user_wilayah->name == 'Supervisor') {
            if ($request->has('search') && !empty($request->search['value'])) {
                $search = $request->search['value'];
                $query = DB::table('rekomendasi_keringanan_pbbs')
                ->join('users', 'users.id', '=', 'rekomendasi_keringanan_pbbs.petugas_pbb')
                // ->join('log_pbbs', 'log_pbbs.id_trx_pbbs', '=', 'rekomendasi_keringanan_pbbs.id')
                // ->join('model_has_roles', 'model_has_roles.model_id', '=', 'rekomendasi_keringanan_pbbs.tujuan_pbb')
                ->join('indonesia_villages', 'indonesia_villages.code', '=', 'rekomendasi_keringanan_pbbs.id_kelurahan_pbb')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_keringanan_pbbs.id_kecamatan_pbb')
                ->select('rekomendasi_keringanan_pbbs.*', 'd.name_districts', 'indonesia_villages.name_village','users.name')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_keringanan_pbbs.id_kelurahan_pbb', $user_wilayah->kelurahan_id)
                        // ->where('log_pbbs.tujuan_pbbs', '!=', $user_wilayah->role_id)
                        // ->where('log_pbbs.created_by_pbbs', '=', auth::user()->id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_keringanan_pbbs.status_aksi_pbb', '=', 'Tolak')
                                ->orWhere('rekomendasi_keringanan_pbbs.status_aksi_pbb', '=', 'Selesai');
                        });
                    })->where(function ($query) use ($search) {
                        $query->where('rekomendasi_keringanan_pbbs.no_pendaftaran_pbb', 'like', "%$search%");
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

        // Get paginated data
        $start = $request->start;
        $length = $request->length;
        $query->offset($start)->limit($length);
        $data = $query->get();
        // dd($data);
        // mengubah data JSON menjadi objek PHP

        return response()->json([
            'draw' => $request->input('draw'),
            'recordsTotal' => rekomendasi_keringanan_pbb::count(),
            'recordsFiltered' => $total_filtered_items,
            'data' => $data,
        ]);
    }
    public function getPetugasPbb($id)
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
                ->select('u.id', 'u.name', 'u.email', 'r.name as role')
                ->where('mhr.model_type', '=', 'App\Models\User')
                ->where('wilayahs.kecamatan_id', '=',$wilayah->kecamatan_id)
                ->where('mhr.role_id', '=', $id)
                ->get(); 
                return response()->json($users);
        }elseif($user_wilayah->name == 'Front Office Kota' || $user_wilayah->name == 'Back Ofiice Kota'|| $user_wilayah->name == 'KepalaDinas'||$user_wilayah->name == 'SekertarisDinas') {
            $users = DB::table('users as u')
                    ->leftJoin('model_has_roles as mhr', 'u.id', '=', 'mhr.model_id')
                    ->leftJoin('roles as r', 'mhr.role_id', '=', 'r.id')
                    ->leftJoin('wilayahs', 'wilayahs.createdby', '=', 'u.id')
                    ->leftJoin('rekomendasi_keringanan_pbbs','rekomendasi_keringanan_pbbs.createdby_pbb','=','u.id')
                    ->where('mhr.model_type', '=', 'App\Models\User')
                    ->where('wilayahs.kota_id', '=',$wilayah->kota_id)
                    ->where('mhr.role_id', '=', $id)
                    // ->whereRaw("(SELECT COUNT(l.id) FROM users as l WHERE l.id = '".$id."') > 0 ")
                    ->whereRaw("(SELECT COUNT(l.id) FROM users as l WHERE l.id = rekomendasi_keringanan_pbbs.id AND l.id = '".$id."') > 0 ")
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
        }
        return response()->json($users);
    }
}
