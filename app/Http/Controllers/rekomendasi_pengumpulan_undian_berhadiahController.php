<?php

namespace App\Http\Controllers;

use App\Http\Requests\Createrekomendasi_pengumpulan_undian_berhadiahRequest;
use App\Http\Requests\Updaterekomendasi_pengumpulan_undian_berhadiahRequest;
use App\Http\Controllers\AppBaseController;
use App\Models\log_pub;
use App\Models\Prelist;
use App\Models\rekomendasi_pengumpulan_undian_berhadiah;
use App\Models\Roles;
use App\Repositories\rekomendasi_pengumpulan_undian_berhadiahRepository;
use Illuminate\Http\Request;
use Flash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Laracasts\Flash\Flash as FlashFlash;
class rekomendasi_pengumpulan_undian_berhadiahController extends AppBaseController
{
    /** @var rekomendasi_pengumpulan_undian_berhadiahRepository $rekomendasiPengumpulanUndianBerhadiahRepository*/
    private $rekomendasiPengumpulanUndianBerhadiahRepository;

    public function __construct(rekomendasi_pengumpulan_undian_berhadiahRepository $rekomendasiPengumpulanUndianBerhadiahRepo)
    {
        $this->rekomendasiPengumpulanUndianBerhadiahRepository = $rekomendasiPengumpulanUndianBerhadiahRepo;
    }

    /**
     * Display a listing of the rekomendasi_pengumpulan_undian_berhadiah.
     */
    public function index(Request $request)
    {
        $rekomendasiPengumpulanUndianBerhadiahs = $this->rekomendasiPengumpulanUndianBerhadiahRepository->paginate(10);

        return view('rekomendasi_pengumpulan_undian_berhadiahs.index')
            ->with('rekomendasiPengumpulanUndianBerhadiahs', $rekomendasiPengumpulanUndianBerhadiahs);
    }
  

    /**
     * Show the form for creating a new rekomendasi_pengumpulan_undian_berhadiah.
     */
    public function create()
    {
        $v = rekomendasi_pengumpulan_undian_berhadiah::latest()->first();
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
                ->where('name', 'Front Office kota')
                ->get();
        } else if ($roles->contains('Front Office kota')) {
            $roleid = DB::table('roles')
                ->where('name', ['Back Ofiice Kota', 'Front Ofiice Kelurahan'])
                ->get();
        } else if ($roles->contains('Back Ofiice Kota')) {
            $roleid = DB::table('roles')
                ->whereIn('name', ['Front Office kota', 'kepala bidang'])
                ->get();
        } else if ($roles->contains('kepala bidang')) {
            $roleid = DB::table('roles')
                ->whereIn('name', ['Back Ofiice kota', 'SekertarisDinas'])
                ->get();
        }
        $checkroles = Roles::where('name', 'Front Office kota')
            ->orWhere('name', 'SekertarisDinas')
            ->orWhere('name', 'KepalaDinas')
            ->get();
        return view('rekomendasi_pengumpulan_undian_berhadiahs.create', compact('kecamatans', 'wilayah', 'roleid', 'checkroles', 'alur'));
        // return view('rekomendasi_pub.create');
    }
    public function cekIdRehab(Request $request, $Nik)
    {
        $found = false;
        $table2 = DB::table('dtks')->where('nik_pub', $Nik)->first();
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
     * Store a newly created rekomendasi_pengumpulan_undian_berhadiah in storage.
     */
    public function store(Request $request)
    {
        if ($request->get('status_alur_pub') != 'Draft') {
            // jika status_alur_pub sama dengan Draft akan nmasuk kondisi sini
            if ($request->get('status_dtks_pub') == 'Terdaftar') {
                // jika status_dtks_pub sama dengan terdaftar akan nmasuk kondisi sini
                $data = new rekomendasi_pengumpulan_undian_berhadiah();
                $files = [
                    'file_ktp_terlapor_pub' => 'pengumpulan_undian/ktp/',
                    'file_kk_terlapor_pub' => 'pengumpulan_undian/kk/',
                    'file_keterangan_dtks_pub' => 'pengumpulan_undian/strukturorganisasi/',
                    'file_pendukung_pub' => 'pengumpulan_undian/wajibpajak/'
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


                // $data->id_alur = $request->get('id_alur_pub');
                $data->no_pendaftaran_pub = mt_rand(100, 1000);
                $data->id_provinsi_pub = $request->get('id_provinsi_pub');
                $data->id_kabkot_pub = $request->get('id_kabkot_pub');
                $data->id_kecamatan_pub = $request->get('id_kecamatan_pub');
                $data->id_kelurahan_pub = $request->get('id_kelurahan_pub');
                $data->jenis_pelapor_pub = $request->get('jenis_pelapor_pub');
                $data->ada_nik_pub = $request->get('ada_nik_pub');
                $data->nik_pub = $request->get('nik_pub');
                $data->no_kk_pub = $request->get('no_kk_pub');
                // $data->no_kis = $request->get('no_kis');
                $data->nama_pub = $request->get('nama_pub');
                $data->tgl_lahir_pub = $request->get('tgl_lahir_pub');
                $data->tempat_lahir_pub = $request->get('tempat_lahir_pub');
                $data->jenis_kelamin_pub = $request->get('jenis_kelamin_pub');
                $data->telp_pub = $request->get('telp_pub');
                $data->alamat_pub = $request->get('alamat_pub');
                $data->status_dtks_pub = $request->get('status_dtks_pub');
                $data->tujuan_pub = $request->get('tujuan_pub');
                $data->status_aksi_pub = $request->get('status_aksi_pub');
                $data->petugas_pub = $request->get('petugas_pub');
                $data->createdby_pub = Auth::user()->id;
                $data->updatedby_pub = Auth::user()->id;
                dd($data);
                $data->save();
                $logpengaduan = new log_pub();
                $logpengaduan['id_trx_pub'] = $data->id;
                $logpengaduan['id_alur_pub'] = $request->get('status_aksi_pub');
                $logpengaduan['petugas_pub'] = $request->get('petugas_pub');
                $logpengaduan['catatan_pub']  = $request->get('catatan_pub');
                $logpengaduan['draft_rekomendasi_pub'] = $request->get('file_pendukung');
                $logpengaduan['tujuan_pub'] = $request->get('tujuan');
                $logpengaduan['created_by_pub'] = Auth::user()->id;
                $logpengaduan['updated_by_pub'] = Auth::user()->id;

                $logpengaduan->save();
                return redirect('rekomendasi_pengumpulan_undian_berhadiahs')->withSuccess('Data Rekomendasi Berhasil Ditambahkan');
            } else {
                // jika status_dtks_pub sama dengan terdaftar akan nmasuk kondisi sini
                $cek = Prelist::where('nik', '=', $request->get('nik_pub'))->exists();
                if (!$cek) {
                    // jika nik tidak ada nik akan masuk ke sini
                    $data = new Prelist;
                    $data['id_provinsi'] = $request->get('id_provinsi_pub');
                    $data['id_kabkot'] = $request->get('id_kabkot_pub');
                    $data['id_kecamatan'] = $request->get('id_kecamatan_pub');
                    $data['id_kelurahan'] = $request->get('id_kelurahan_pub');
                    $data['nik'] = $request->get('nik_pub');
                    $data['no_kk'] = $request->get('no_kk_pub');
                    // $data['no_kis'] = $request->get('no_kis_pub');
                    $data['nama'] = $request->get('nama_pub');
                    $data['tgl_lahir'] = $request->get('tgl_lahir_pub');
                    // $data['alamat'] = $request->get('alamat_pub');
                    $data['telp'] = $request->get('telpon_pub');
                    $data['email'] = $request->get('email_pub');
                    $data['status_data'] = 'prelistdtks';

                    $data->save();
                    $data = new rekomendasi_pengumpulan_undian_berhadiah();
                    $files = [
                        'file_ktp_terlapor_pub' => 'pengumpulan_undian/ktp/',
                        'file_kk_terlapor_pub' => 'pengumpulan_undian/kk/',
                        'file_keterangan_dtks_pub' => 'pengumpulan_undian/strukturorganisasi/',
                        'file_pendukung_pub' => 'pengumpulan_undian/wajibpajak/'
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

                    // $data->id_alur = $request->get('id_alur_pub');
                    $data->no_pendaftaran_pub = mt_rand(100, 1000);
                    $data->id_provinsi_pub = $request->get('id_provinsi_pub');
                    $data->id_kabkot_pub = $request->get('id_kabkot_pub');
                    $data->id_kecamatan_pub = $request->get('id_kecamatan_pub');
                    $data->id_kelurahan_pub = $request->get('id_kelurahan_pub');
                    $data->jenis_pelapor_pub = $request->get('jenis_pelapor_pub');
                    $data->ada_nik_pub = $request->get('ada_nik_pub');
                    $data->nik_pub = $request->get('nik_pub');
                    $data->no_kk_pub = $request->get('no_kk_pub');
                    // $data->no_kis = $request->get('no_kis');
                    $data->nama_pub = $request->get('nama_pub');
                    $data->tgl_lahir_pub = $request->get('tgl_lahir_pub');
                    $data->tempat_lahir_pub = $request->get('tempat_lahir_pub');
                    $data->jenis_kelamin_pub = $request->get('jenis_kelamin_pub');
                    $data->telp_pub = $request->get('telp_pub');
                    $data->alamat_pub = $request->get('alamat_pub');
                    $data->status_dtks_pub = $request->get('status_dtks_pub');
                    $data->tujuan_pub = $request->get('tujuan_pub');
                    $data->status_aksi_pub = $request->get('status_aksi_pub');
                    $data->petugas_pub = $request->get('petugas_pub');
                    $data->createdby_pub = Auth::user()->id;
                    $data->updatedby_pub = Auth::user()->id;
                    // dd($data);
                    $data->save();
                    $logpengaduan = new log_pub();
                    $logpengaduan['id_trx_pub'] = $data->id;
                    $logpengaduan['id_alur_pub'] = $request->get('status_aksi_pub');
                    $logpengaduan['petugas_pub'] = $request->get('petugas_pub');
                    $logpengaduan['catatan_pub']  = $request->get('catatan_pub');
                    $logpengaduan['draft_rekomendasi_pub'] = $request->get('file_pendukung');
                    $logpengaduan['tujuan_pub'] = $request->get('tujuan');
                    $logpengaduan['created_by_pub'] = Auth::user()->id;
                    $logpengaduan['updated_by_pub'] = Auth::user()->id;

                    $logpengaduan->save();
                    return redirect('rekomendasi_pengumpulan_undian_berhadiahs')->withSuccess('Data Rekomendasi Berhasil Ditambahkan');
                } else {
                    //jika nik ada di prelist akan masuk ke sini
                    $data = new rekomendasi_pengumpulan_undian_berhadiah();
                    $files = [
                        'file_ktp_terlapor_pub' => 'pengumpulan_undian/ktp/',
                        'file_kk_terlapor_pub' => 'pengumpulan_undian/kk/',
                        'file_keterangan_dtks_pub' => 'pengumpulan_undian/strukturorganisasi/',
                        'file_pendukung_pub' => 'pengumpulan_undian/wajibpajak/'
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

                    // $data->id_alur = $request->get('id_alur_pub');
                    $data->no_pendaftaran_pub = mt_rand(100, 1000);
                    $data->id_provinsi_pub = $request->get('id_provinsi_pub');
                    $data->id_kabkot_pub = $request->get('id_kabkot_pub');
                    $data->id_kecamatan_pub = $request->get('id_kecamatan_pub');
                    $data->id_kelurahan_pub = $request->get('id_kelurahan_pub');
                    $data->jenis_pelapor_pub = $request->get('jenis_pelapor_pub');
                    $data->ada_nik_pub = $request->get('ada_nik_pub');
                    $data->nik_pub = $request->get('nik_pub');
                    $data->no_kk_pub = $request->get('no_kk_pub');
                    // $data->no_kis = $request->get('no_kis');
                    $data->nama_pub = $request->get('nama_pub');
                    $data->tgl_lahir_pub = $request->get('tgl_lahir_pub');
                    $data->tempat_lahir_pub = $request->get('tempat_lahir_pub');
                    $data->jenis_kelamin_pub = $request->get('jenis_kelamin_pub');
                    $data->telp_pub = $request->get('telp_pub');
                    $data->alamat_pub = $request->get('alamat_pub');
                    $data->status_dtks_pub = $request->get('status_dtks_pub');
                    $data->tujuan_pub = $request->get('tujuan_pub');
                    $data->status_aksi_pub = $request->get('status_aksi_pub');
                    $data->petugas_pub = $request->get('petugas_pub');
                    $data->createdby_pub = Auth::user()->id;
                    $data->updatedby_pub = Auth::user()->id;
                    // dd($data);
                    $data->save();
                    $logpengaduan = new log_pub();
                    $logpengaduan['id_trx_pub'] = $data->id;
                    $logpengaduan['id_alur_pub'] = $request->get('status_aksi_pub');
                    $logpengaduan['petugas_pub'] = $request->get('petugas_pub');
                    $logpengaduan['catatan_pub']  = $request->get('catatan_pub');
                    $logpengaduan['file_pendukung_pub'] = $request->get('file_pendukung_pub');
                    $logpengaduan['tujuan_pub'] = $request->get('tujuan');
                    $logpengaduan['created_by_pub'] = Auth::user()->id;
                    $logpengaduan['updated_by_pub'] = Auth::user()->id;

                    $logpengaduan->save();
                    return redirect('rekomendasi_pengumpulan_undian_berhadiahs')->withSuccess('Data Rekomendasi Berhasil Ditambahkan');
                }
            }
        } else {
            //jika status draft adalah ini akan masuk ke sini
            $data = new rekomendasi_pengumpulan_undian_berhadiah();
            $files = [
                'file_ktp_terlapor_pub' => 'pengumpulan_undian/ktp/',
                'file_kk_terlapor_pub' => 'pengumpulan_undian/kk/',
                'file_keterangan_dtks_pub' => 'pengumpulan_undian/strukturorganisasi/',
                'file_pendukung_pub' => 'pengumpulan_undian/wajibpajak/'
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

            // $data->id_alur = $request->get('id_alur_pub');
            $data->no_pendaftaran_pub = mt_rand(100, 1000);
            $data->id_provinsi_pub = $request->get('id_provinsi_pub');
            $data->id_kabkot_pub = $request->get('id_kabkot_pub');
            $data->id_kecamatan_pub = $request->get('id_kecamatan_pub');
            $data->id_kelurahan_pub = $request->get('id_kelurahan_pub');
            $data->jenis_pelapor_pub = $request->get('jenis_pelapor_pub');
            $data->ada_nik_pub = $request->get('ada_nik_pub');
            $data->nik_pub = $request->get('nik_pub');
            $data->no_kk_pub = $request->get('no_kk_pub');
            // $data->no_kis = $request->get('no_kis');
            $data->nama_pub = $request->get('nama_pub');
            $data->tgl_lahir_pub = $request->get('tgl_lahir_pub');
            $data->tempat_lahir_pub = $request->get('tempat_lahir_pub');
            $data->jenis_kelamin_pub = $request->get('jenis_kelamin_pub');
            $data->telp_pub = $request->get('telp_pub');
            $data->status_dtks_pub = $request->get('status_dtks_pub');
            $data->tujuan_pub = $request->get('tujuan_pub');
            $data->status_aksi_pub = $request->get('status_aksi_pub');
            $data->petugas_pub = $request->get('petugas_pub');
            $data->createdby_pub = Auth::user()->id;
            $data->updatedby_pub = Auth::user()->id;
            // dd($data);
            $data->save();
            return redirect('rekomendasi_pengumpulan_undian_berhadiahs')->withWarning('NIK Tidak Tersedia Data Disimpan sebagai draft');
        }
    }

    /**
     * Display the specified rekomendasi_pengumpulan_undian_berhadiah.
     */
    public function show($id)
    {
        $userid = Auth::user()->id;
        $rekomendasiPengumpulanUndianBerhadiah = $this->rekomendasiPengumpulanUndianBerhadiahRepository->find((int) $id);
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

        if (empty($rekomendasiPengumpulanUndianBerhadiah)) {
            Flash::error('Rekomendasi not found');

            return redirect(route('rekomendasi_pengumpulan_undian_berhadiahs.index'));
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

        $log_pub = log_pub::where('id_trx_pub', $id)->get();

        return view('rekomendasi_pengumpulan_undian_berhadiahs.show', compact('rekomendasiPengumpulanUndianBerhadiah', 'roleid', 'wilayah', 'checkroles', 'log_pub'));
    }

    /**
     * Show the form for editing the specified rekomendasi_pengumpulan_undian_berhadiah.
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
            ->leftjoin('rekomendasi_pengumpulan_undian_berhadiahs', 'rekomendasi_pengumpulan_undian_berhadiahs.createdby_pub', '=', 'model_has_roles.model_id')
            ->where('rekomendasi_pengumpulan_undian_berhadiahs.id', '=', $id)
            // ->where('status_aksi', '=', 'Draft')
            // ->orwhere('status_aksi', '=', 'Teruskan')
            ->get();
        // dd($checkroles2);
        //Tujuan
        $createdby = DB::table('rekomendasi_pengumpulan_undian_berhadiahs')
            ->join('users', 'rekomendasi_pengumpulan_undian_berhadiahs.createdby_pub', '=', 'users.name')
            ->join('model_has_roles', 'model_has_roles.model_id', '=', 'users.id')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->select('rekomendasi_pengumpulan_undian_berhadiahs.id', 'rekomendasi_pengumpulan_undian_berhadiahs.createdby_pub', 'roles.name')
            ->get();

        $rekomendasiPengumpulanUndianBerhadiah = rekomendasi_pengumpulan_undian_berhadiah::where('createdby_pub', $userid)->get();
        $getdata = DB::table('model_has_roles')
            ->leftjoin('rekomendasi_pengumpulan_undian_berhadiahs as b', 'b.tujuan_pub', '=', 'model_has_roles.role_id')
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
                ->where('name', 'Front Office kota')
                ->get();
        } elseif ($roles->contains('Front Office kota')) {
            $roleid = DB::table('roles')
                ->where('name', ['Back Office kota', 'Front Office kelurahan'])
                ->get();
        } else if ($roles->contains('Back Ofiice Kota')) {
            $roleid = DB::table('roles')
                ->whereIn('name', ['Front Office kota', 'kepala bidang'])
                ->get();
        } else if ($roles->contains('kepala bidang')) {
            $roleid = DB::table('roles')
                ->whereIn('name', ['Back Ofiice kota', 'SekertarisDinas'])
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

        $rekomendasiPengumpulanUndianBerhadiah = $this->rekomendasiPengumpulanUndianBerhadiahRepository->find($id);


        return view('rekomendasi_pengumpulan_undian_berhadiahs.edit', compact('wilayah', 'rekomendasiPengumpulanUndianBerhadiah', 'roleid', 'getdata', 'alur', 'createdby', 'getUsers'));
    }

    /**
     * Update the specified rekomendasi_pengumpulan_undian_berhadiah in storage.
     */
    public function update($id, Request $request)
    {
        $userid = Auth::user()->id;
        $datapub = rekomendasi_pengumpulan_undian_berhadiah::where('id', $id)->first();
        // dd();

        if ($datapub->nik != null) {

            if ($datapub->status_dtks == 'Terdaftar') {

                if ($datapub->status_aksi == 'Teruskan' || $datapub->status_aksi == 'Kembalikan') {
                    // dd($request->get('status_dtks') );
                    $pub['petugas_pub']  = $request->get('petugas_puba');
                    $pub['tujuan_pub'] = $request->get('tujuan_pub');
                    $pub['status_aksi_pub'] = $request->get('status_aksi_pub');
                    // dd($pub);
                    rekomendasi_pengumpulan_undian_berhadiah::where('id', $id)->update($pub);
                }
                if ($datapub->status_aksi == 'Draft') {
                    $files = [
                        'file_ktp_terlapor_pub' => 'pengumpulan_undian/ktp/',
                        'file_kk_terlapor_pub' => 'pengumpulan_undian/kk/',
                        'file_keterangan_dtks_pub' => 'pengumpulan_undian/strukturorganisasi/',
                        'file_pendukung_pub' => 'pengumpulan_undian/wajibpajak/',
                    ];

                    foreach ($files as $field => $path) {
                        if ($request->file($field)) {
                            $file = $request->file($field);
                            $nama_file = $path . $file->getClientOriginalName();
                            $return = Storage::disk('imagekit')->put($nama_file, fopen($file->getRealPath(), 'r'));
                            $datapub->{$field} = Storage::disk('imagekit')->url($nama_file);
                        } else {
                            $datapub->{$field} = null;
                        }
                    }
                }
                $pub['id_provinsi_pub'] = $request->get('id_provinsi_pub');
                $pub['id_kabkot_pub'] = $request->get('id_kabkot_pub');
                $pub['id_kecamatan_pub'] = $request->get('id_kecamatan_pub');
                $pub['id_kelurahan_pub'] = $request->get('id_kelurahan_pub');
                $pub['jenis_pelapor_pub'] = $request->get('jenis_pelapor_pub');
                $pub['ada_nik_pub'] = $request->get('memiliki_nik');
                $pub['nik_pub'] = $request->get('nik_pub');
                $pub['no_kk_pub'] = $request->get('no_kk_pub');
                $pub['nama_pub'] = $request->get('nama_pub');
                $pub['tgl_lahir_pub'] = $request->get('tgl_lahir_pub');
                $pub['tempat_lahir_pub'] = $request->get('tempat_lahir_pub');
                $pub['status_dtks_pub'] = $request->get('status_dtks_pub');
                $pub['telp_pub'] = $request->get('telpon_pub');
                $pub['email_pub'] = $request->get('email_pub');
                $pub['petugas_pub']  = $request->get('petugas_pub');
                $pub['tujuan_pub'] = $request->get('tujuan_pub');
                $pub['status_aksi_pub'] = $request->get('status_aksi_pub');

                rekomendasi_pengumpulan_undian_berhadiah::where('id', $id)->update($pub);
            }

            $checkuserrole = DB::table('model_has_roles')
                ->leftjoin('roles', 'roles.id', '=', 'model_has_roles.role_id')
                ->where('model_id', '=', $userid)
                ->first();
            if ($checkuserrole->name == $checkuserrole->name) {
                //   dd($pengaduan);
                $logpengaduan = new log_pub();
                $logpengaduan['id_trx_pub'] = $datapub->id;
                $logpengaduan['id_alur_pub'] = $request->get('status_aksi_pub');
                $logpengaduan['petugas_pub'] = $request->get('petugas_pub');
                $logpengaduan['catatan_pub']  = $request->get('catatan_pub');
                $logpengaduan['file_pendukung_pub'] = $request->get('file_pendukung_pub');
                $logpengaduan['tujuan_pub'] = $request->get('tujuan');
                $logpengaduan['created_by_pub'] = Auth::user()->id;
                $logpengaduan['updated_by_pub'] = Auth::user()->id;
                // dd($logpengaduan);
                $logpengaduan->save();


                return redirect('rekomendasi_pengumpulan_undian_berhadiahs')->withSuccess('Rekomendasi Berhasil Diubah');
            } else {

                $cek = Prelist::where('nik', '=', $request->get('nik'))->exists();
                if ($cek) {
                    return redirect('rekomendasi_pengumpulan_undian_berhadiahs')->withWarning('NIK Sudah Terdaftar Di Prelist');
                } else {

                    $pub['id_provinsi'] = $request->get('id_provinsi_pub');
                    $pub['id_kabkot'] = $request->get('id_kabkot_pub');
                    $pub['id_kecamatan'] = $request->get('id_kecamatan_pub');
                    $pub['id_kelurahan'] = $request->get('id_kelurahan_pub');
                    $pub['nik'] = $request->get('nik_pub');
                    $pub['no_kk'] = $request->get('no_kk_pub');
                    $pub['nama'] = $request->get('nama_pub');
                    $pub['tgl_lahir'] = $request->get('tgl_lahir_pub');;
                    $pub['telp'] = $request->get('telpon_pub');
                    $pub['email'] = $request->get('email_pub');
                    // $pub['status_data'] = 'prelistdtks';
                    Prelist::where('id', $id)->update($pub);
                    return redirect('pubs')->withSuccess('Data  Berhasil Disimpan Di Prelist');
                }
            }
        } else {

            $pub['id_kabkot_pub'] = $request->get('id_kabkot_pub');
            $pub['id_kecamatan_pub'] = $request->get('id_kecamatan_pub');
            $pub['id_kelurahan_pub'] = $request->get('id_kelurahan_pub');
            $pub['jenis_pelapor_pub'] = $request->get('jenis_pelapor_pub');
            $pub['ada_nik_pub'] = $request->get('memiliki_nik');
            $pub['nik_pub'] = $request->get('nik_pub');
            $pub['no_kk_pub'] = $request->get('no_kk_pub');
            $pub['nama_pub'] = $request->get('nama_pub');
            $pub['tgl_lahir_pub'] = $request->get('tgl_lahir_pub');
            $pub['tempat_lahir_pub'] = $request->get('tempat_lahir_pub');
            $pub['status_dtks_pub'] = $request->get('status_dtks_pub');
            $pub['telp_pub'] = $request->get('telpon_pub');
            $pub['email_pub'] = $request->get('email_pub');
            $pub['petugas_pub']  = $request->get('petugas_pub');
            $pub['tujuan_pub'] = $request->get('tujuan_pub');
            $pub['status_aksi_pub'] = $request->get('status_aksi_pub');
            // $pub['tl_file']  = $request->get('detail_pub');  
            $pub['createdby_pub'] = Auth::user()->name;
            $pub['updatedby_pub'] = Auth::user()->name;
            // dd($pub);

            rekomendasi_pengumpulan_undian_berhadiah::where('id', $id)->update($pub);

            return redirect('   ')->withSuccess('Data Berhasil Diubah');
        }
    }
    /**
     * Remove the specified rekomendasi_pengumpulan_undian_berhadiah from storage.
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $rekomendasiPengumpulanUndianBerhadiah = $this->rekomendasiPengumpulanUndianBerhadiahRepository->find($id);

        if (empty($rekomendasiPengumpulanUndianBerhadiah)) {
            Flash::error('Rekomendasi Pengumpulan Undian Berhadiah not found');

            return redirect(route('rekomendasi_pub.index'));
        }

        $this->rekomendasiPengumpulanUndianBerhadiahRepository->delete($id);

        Flash::success('Rekomendasi Pengumpulan Undian Berhadiah deleted successfully.');

        return redirect(route('rekomendasi_pub.index'));
    }

    public function draft(Request $request)
    {
        $user_name = Auth::user()->name;
        $query = DB::table('rekomendasi_pengumpulan_undian_berhadiahs')
            ->leftjoin('users', 'users.id', '=', 'rekomendasi_pengumpulan_undian_berhadiahs.createdby_pub')
            ->leftjoin('wilayahs', 'wilayahs.createdby', '=', 'rekomendasi_pengumpulan_undian_berhadiahs.createdby_pub')
            ->leftjoin('model_has_roles', 'model_has_roles.model_id', '=', 'rekomendasi_pengumpulan_undian_berhadiahs.tujuan_pub')
            ->leftjoin('indonesia_villages as b', 'b.code', '=', 'rekomendasi_pengumpulan_undian_berhadiahs.id_kelurahan_pub')
            ->select('rekomendasi_pengumpulan_undian_berhadiahs.*', 'b.name_village')
            ->distinct();
        $user_id = Auth::user()->id;
        $user_wilayah = DB::table('wilayahs')
            ->leftJoin('model_has_roles', 'model_has_roles.model_id', '=', 'wilayahs.createdby')
            ->leftJoin('users', 'users.id', '=', 'model_has_roles.model_id')
            ->leftJoin('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('createdby', $user_id)
            ->where(function ($query) {
                $query->where('status_wilayah', 1);
            })
            ->first();
        // dd($user_wilayah);
        if ($user_wilayah->name == 'Front Office Kelurahan') {
            $query->orWhere(function ($query) use ($user_wilayah) {
                $query->where('rekomendasi_pengumpulan_undian_berhadiahs.id_kelurahan_pub', $user_wilayah->kelurahan_id);
                $query->where('rekomendasi_pengumpulan_undian_berhadiahs.status_aksi_pub', 'Draft');
                $query->where('rekomendasi_pengumpulan_undian_berhadiahs.createdby_pub',  Auth::user()->id);
            });
        }

        if ($request->has('search')) {
            // dd($query);
            $search = $request->search['value'];
            $query->where(function ($query) use ($search) {
                $query->where('rekomendasi_pengumpulan_undian_berhadiahs.no_pendaftaran_pub', 'like', "%$search%");
            });
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
        $data = $query->paginate($request->input('length'));
        // mengubah data JSON menjadi objek PHP

        return response()->json([
            'draw' => $request->input('draw'),
            'recordsTotal' => rekomendasi_pengumpulan_undian_berhadiah::count(),
            'recordsFiltered' => $total_filtered_items,
            'data' => $data,
        ]);
    }
    public function diproses(Request $request)
    {

        $user_id = Auth::user()->id;
        $user_wilayah = DB::table('wilayahs')
            ->leftJoin('model_has_roles', 'model_has_roles.model_id', '=', 'wilayahs.createdby')
            ->leftJoin('users', 'users.id', '=', 'model_has_roles.model_id')
            ->leftJoin('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('createdby', $user_id)
            ->where(function ($query) {
                $query->where('status_wilayah', 1);
            })
            ->first();

        // dd($user_wilayah);
        if ($user_wilayah->name == 'Front Office Kelurahan') {
            $query = DB::table('rekomendasi_pengumpulan_undian_berhadiahs')
                ->join('users', 'users.id', '=', 'rekomendasi_pengumpulan_undian_berhadiahs.createdby_pub')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_pengumpulan_undian_berhadiahs.id_kecamatan_pub')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_pengumpulan_undian_berhadiahs.id_kelurahan_pub')
                ->select('rekomendasi_pengumpulan_undian_berhadiahs.*', 'b.name_village', 'd.name_districts');
        } elseif ($user_wilayah->name == 'Front Office kota') {
            $query = DB::table('rekomendasi_pengumpulan_undian_berhadiahs')
                ->join('users', 'users.id', '=', 'rekomendasi_pengumpulan_undian_berhadiahs.createdby_pub')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_pengumpulan_undian_berhadiahs.id_kecamatan_pub')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_pengumpulan_undian_berhadiahs.id_kelurahan_pub')
                ->select('rekomendasi_pengumpulan_undian_berhadiahs.*', 'b.name_village', 'd.name_districts');
        } elseif ($user_wilayah->name == 'Back Ofiice kota') {
            $query = DB::table('rekomendasi_pengumpulan_undian_berhadiahs')
                ->join('users', 'users.id', '=', 'rekomendasi_pengumpulan_undian_berhadiahs.createdby_pub')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_pengumpulan_undian_berhadiahs.id_kecamatan_pub')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_pengumpulan_undian_berhadiahs.id_kelurahan_pub')
                ->select('rekomendasi_pengumpulan_undian_berhadiahs.*', 'b.name_village', 'd.name_districts');
        } else {
            $query = DB::table('rekomendasi_pengumpulan_undian_berhadiahs')
                ->join('users', 'users.id', '=', 'rekomendasi_pengumpulan_undian_berhadiahs.createdby_pub')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_pengumpulan_undian_berhadiahs.id_kelurahan_pub')
                ->select('rekomendasi_pengumpulan_undian_berhadiahs.*', 'b.name_village');
        }
        if ($user_wilayah->name == 'Front Office kelurahan') {
            //  dd($user_wilayah->role_id);

            $query->orWhere(function ($query) use ($user_wilayah) {
                $query->where('rekomendasi_pengumpulan_undian_berhadiahs.id_kelurahan_pub', '=', $user_wilayah->kelurahan_id)
                    ->where('rekomendasi_pengumpulan_undian_berhadiahs.tujuan_pub', '=', $user_wilayah->role_id)
                    ->where(function ($query) {
                        $query->where('rekomendasi_pengumpulan_undian_berhadiahs.status_aksi_pub', '=', 'Teruskan')
                            ->orWhere('rekomendasi_pengumpulan_undian_berhadiahs.status_aksi_pub', '=', 'kembalikan');
                    });
            });
        }
        if ($user_wilayah->name == 'Front Office kota') {
            //  dd($user_wilayah->role_id);

            $query->orWhere(function ($query) use ($user_wilayah) {
                $query->where('rekomendasi_pengumpulan_undian_berhadiahs.id_kelurahan_pub', '=', $user_wilayah->kelurahan_id)
                    ->where('rekomendasi_pengumpulan_undian_berhadiahs.tujuan_pub', '=', $user_wilayah->role_id)
                    ->where(function ($query) {
                        $query->where('rekomendasi_pengumpulan_undian_berhadiahs.status_aksi_pub', '=', 'Teruskan')
                            ->orWhere('rekomendasi_pengumpulan_undian_berhadiahs.status_aksi_pub', '=', 'kembalikan');
                    });
            });
        }
        if ($user_wilayah->name == 'Back Ofiice Kota') {
            //  dd($user_wilayah->role_id);
            $query->orWhere(function ($query) use ($user_wilayah) {
                $query->where('rekomendasi_pengumpulan_undian_berhadiahs.id_kelurahan_pub', '=', $user_wilayah->kelurahan_id)
                    ->where('rekomendasi_pengumpulan_undian_berhadiahs.tujuan_pub', '=', $user_wilayah->role_id)
                    ->where(function ($query) {
                        $query->where('rekomendasi_pengumpulan_undian_berhadiahs.status_aksi_pub', '=', 'Teruskan')
                            ->orWhere('rekomendasi_pengumpulan_undian_berhadiahs.status_aksi_pub', '=', 'kembalikan');
                    });
            });
        }
        if ($user_wilayah->name == 'kepala bidang') {

            $query->orWhere(function ($query) use ($user_wilayah) {
                $query->where('rekomendasi_pengumpulan_undian_berhadiahs.id_kelurahan_pub', '=', $user_wilayah->kelurahan_id)
                    ->where('rekomendasi_pengumpulan_undian_berhadiahs.tujuan_pub', '=', $user_wilayah->role_id)
                    ->where('rekomendasi_pengumpulan_undian_berhadiahs.petugas_pub', '=', $user_wilayah->model_id)
                    ->where(function ($query) {
                        $query->where('rekomendasi_pengumpulan_undian_berhadiahs.status_aksi_pub', '=', 'Teruskan')
                            ->orWhere('rekomendasi_pengumpulan_undian_berhadiahs.status_aksi_pub', '=', 'kembalikan');
                    });
                // dd($va);
            });
        }
        if ($user_wilayah->name == 'SekertarisDinas') {
            // dd($user_wilayah);
            $query->orWhere(function ($query) use ($user_wilayah) {
                $query->where('rekomendasi_pengumpulan_undian_berhadiahs.id_kelurahan_pub', '=', $user_wilayah->kelurahan_id)
                    ->where('rekomendasi_pengumpulan_undian_berhadiahs.tujuan_pub', '=', $user_wilayah->role_id)
                    ->where('rekomendasi_pengumpulan_undian_berhadiahs.petugas_pub', '=', $user_wilayah->model_id)
                    ->where(function ($query) {
                        $query->where('rekomendasi_pengumpulan_undian_berhadiahs.status_aksi_pub', '=', 'Teruskan')
                            ->orWhere('rekomendasi_pengumpulan_undian_berhadiahs.status_aksi_pub', '=', 'kembalikan');
                    });
                // dd($va);
            });
        }
        if ($user_wilayah->name == 'KepalaDinas') {
            // dd($user_wilayah);
            $query->orWhere(function ($query) use ($user_wilayah) {
                $query->where('rekomendasi_pengumpulan_undian_berhadiahs.id_kelurahan_pub', '=', $user_wilayah->kelurahan_id)
                    ->where('rekomendasi_pengumpulan_undian_berhadiahs.tujuan_pub', '=', $user_wilayah->role_id)
                    ->where('rekomendasi_pengumpulan_undian_berhadiahs.petugas_pub', '=', $user_wilayah->model_id)
                    ->where(function ($query) {
                        $query->where('rekomendasi_pengumpulan_undian_berhadiahs.status_aksi_pub', '=', 'Teruskan')
                            ->orWhere('rekomendasi_pengumpulan_undian_berhadiahs.status_aksi_pub', '=', 'kembalikan');
                    });
                // dd($va);
            });
        }
        if ($request->has('search') && !empty($request->search['value'])) {
            $search = $request->search['value'];
            $query = DB::table('rekomendasi_pengumpulan_undian_berhadiahs')
                ->join('users', 'users.id', '=', 'rekomendasi_pengumpulan_undian_berhadiahs.createdby_pub')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_pengumpulan_undian_berhadiahs.id_kecamatan_pub')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_pengumpulan_undian_berhadiahs.id_kelurahan_pub')
                ->select('rekomendasi_pengumpulan_undian_berhadiahs.*', 'b.name_village', 'd.name_districts')
                ->where(function ($query) use ($search) {
                    $query->where('rekomendasi_pengumpulan_undian_berhadiahs.no_pendaftaran_pub', 'like', "%$search%");
                });
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
        $data = $query->paginate($request->input('length'));


        return response()->json([
            'draw' => $request->input('draw'),
            'recordsTotal' => rekomendasi_pengumpulan_undian_berhadiah::count(),
            'recordsFiltered' => $total_filtered_items,
            'data' => $data,
        ]);
    }
    public function teruskan(Request $request)
    {
        $user_name = Auth::user()->name;
        // dd($user_name);

        $query = DB::table('rekomendasi_pengumpulan_undian_berhadiahs')
            ->join('users', 'users.id', '=', 'rekomendasi_pengumpulan_undian_berhadiahs.createdby_pub')
            ->join('log_pub', 'log_pub.id_trx_pub', '=', 'rekomendasi_pengumpulan_undian_berhadiahs.id')
            // ->join('model_has_roles', 'model_has_roles.role_id', '=', 'rekomendasi_pengumpulan_undian_berhadiahs.tujuan_pub')
            ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_pengumpulan_undian_berhadiahs.id_kelurahan_pub')

            ->select('rekomendasi_pengumpulan_undian_berhadiahs.*', 'b.name_village');
        $user_id = Auth::user()->id;
        // dd($user_id);

        $user_wilayah = DB::table('wilayahs')
            ->join('model_has_roles', 'model_has_roles.model_id', '=', 'wilayahs.createdby')
            ->join('users', 'users.id', '=', 'model_has_roles.model_id')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('wilayahs.createdby', $user_id)
            ->where(function ($query) {
                $query->where('status_wilayah', 1);
            })
            ->first();
        // dd($user_wilayah);
        //Front Office kelurahan
        if ($user_wilayah->name == 'Front Office Kelurahan') {
            $query = DB::table('rekomendasi_pengumpulan_undian_berhadiahs')
                ->join('users', 'users.id', '=', 'rekomendasi_pengumpulan_undian_berhadiahs.createdby_pub')
                ->join('log_pub', 'log_pub.id_trx_pub', '=', 'rekomendasi_pengumpulan_undian_berhadiahs.id')
                // ->join('model_has_roles', 'model_has_roles.model_id', '=', 'rekomendasi_pengumpulan_undian_berhadiahs.tujuan_pub')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_pengumpulan_undian_berhadiahs.id_kelurahan_pub')
                ->select('rekomendasi_pengumpulan_undian_berhadiahs.*', 'b.name_village', 'log_pub.tujuan_pub', 'log_pub.petugas_pub')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_pengumpulan_undian_berhadiahs.id_kelurahan_pub', $user_wilayah->kelurahan_id)
                        ->where('rekomendasi_pengumpulan_undian_berhadiahs.tujuan_pub', '!=', $user_wilayah->role_id)
                        ->where('log_pub.created_by_pub', '=', auth::user()->id)
                        // ->where('rekomendasi_pengumpulan_undian_berhadiahs.petugas_pub','!=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_pengumpulan_undian_berhadiahs.status_aksi_pub', '=', 'Teruskan')
                                ->orWhere('rekomendasi_pengumpulan_undian_berhadiahs.status_aksi_pub', '=', 'kembalikan');
                        });
                })->distinct();
        }
        //Front Office Kota
        if ($user_wilayah->name == 'Front Office kota') {
            $query = DB::table('rekomendasi_pengumpulan_undian_berhadiahs')
                ->join('users', 'users.id', '=', 'rekomendasi_pengumpulan_undian_berhadiahs.createdby_pub')
                ->join('log_pub', 'log_pub.id_trx_pub', '=', 'rekomendasi_pengumpulan_undian_berhadiahs.id')
                // ->join('model_has_roles', 'model_has_roles.model_id', '=', 'rekomendasi_pengumpulan_undian_berhadiahs.tujuan_pub')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_pengumpulan_undian_berhadiahs.id_kelurahan_pub')
                ->select('rekomendasi_pengumpulan_undian_berhadiahs.*', 'b.name_village', 'log_pub.tujuan_pub', 'log_pub.petugas_pub')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_pengumpulan_undian_berhadiahs.id_kelurahan_pub', $user_wilayah->kelurahan_id)
                        ->where('rekomendasi_pengumpulan_undian_berhadiahs.tujuan_pub', '!=', $user_wilayah->role_id)
                        ->where('log_pub.created_by_pub', '=', auth::user()->id)
                        // ->where('rekomendasi_pengumpulan_undian_berhadiahs.petugas_pub','!=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_pengumpulan_undian_berhadiahs.status_aksi_pub', '=', 'Teruskan')
                                ->orWhere('rekomendasi_pengumpulan_undian_berhadiahs.status_aksi_pub', '=', 'kembalikan');
                        });
                })->distinct();
        }
        //Back office kota 
        if ($user_wilayah->name == 'Back Ofiice kota') {
            // dd($user_wilayah->model_id);
            $query = DB::table('rekomendasi_pengumpulan_undian_berhadiahs')
                ->join('users', 'users.id', '=', 'rekomendasi_pengumpulan_undian_berhadiahs.createdby_pub')
                ->join('log_pub', 'log_pub.id_trx_pub', '=', 'rekomendasi_pengumpulan_undian_berhadiahs.id')
                // ->join('model_has_roles', 'model_has_roles.model_id', '=', 'rekomendasi_pengumpulan_undian_berhadiahs.tujuan_pub')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_pengumpulan_undian_berhadiahs.id_kelurahan_pub')
                ->select('rekomendasi_pengumpulan_undian_berhadiahs.*', 'b.name_village')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_pengumpulan_undian_berhadiahs.id_kelurahan_pub', $user_wilayah->kelurahan_id)
                        ->where('log_pub.tujuan_pub', '!=', $user_wilayah->role_id)
                        ->where('log_pub.created_by_pub', '=', auth::user()->id)
                        // ->where('rekomendasi_pengumpulan_undian_berhadiahs.petugas_pub','!=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_pengumpulan_undian_berhadiahs.status_aksi_pub', '=', 'Teruskan')
                                ->orWhere('rekomendasi_pengumpulan_undian_berhadiahs.status_aksi_pub', '=', 'kembalikan');
                        });
                });
            // ->get();
            // dd($query);
        }

        if ($user_wilayah->name == 'kepala bidang') {
            // dd( $user_wilayah->role_id);
            $query = DB::table('rekomendasi_pengumpulan_undian_berhadiahs')
                ->join('users', 'users.id', '=', 'rekomendasi_pengumpulan_undian_berhadiahs.createdby_pub')
                ->join('log_pub', 'log_pub.id_trx_pubs', '=', 'rekomendasi_pengumpulan_undian_berhadiahs.id')
                // ->join('model_has_roles', 'model_has_roles.model_id', '=', 'rekomendasi_pengumpulan_undian_berhadiahs.tujuan_pub')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_pengumpulan_undian_berhadiahs.id_kelurahan_pub')
                ->select('rekomendasi_pengumpulan_undian_berhadiahs.*', 'b.name_village')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_pengumpulan_undian_berhadiahs.id_kelurahan_pub', $user_wilayah->kelurahan_id)
                        ->where('log_pub.tujuan_pub', '!=', $user_wilayah->role_id)
                        ->where('log_pub.created_by_pub', '=', auth::user()->id)
                        // // ->where('rekomendasi_pengumpulan_undian_berhadiahs.petugas_pub','!=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_pengumpulan_undian_berhadiahs.status_aksi_pub', '=', 'Teruskan')
                                ->orWhere('rekomendasi_pengumpulan_undian_berhadiahs.status_aksi_pub', '=', 'kembalikan');
                        });
                })->distinct();
        }
        if ($user_wilayah->name == 'SekertarisDinas') {
            // dd($user_wilayah);
            $query = DB::table('rekomendasi_pengumpulan_undian_berhadiahs')
                ->join('users', 'users.id', '=', 'rekomendasi_pengumpulan_undian_berhadiahs.createdby_pub')
                ->join('log_pub', 'log_pub.id_trx_pub', '=', 'rekomendasi_pengumpulan_undian_berhadiahs.id')
                // ->join('model_has_roles', 'model_has_roles.model_id', '=', 'rekomendasi_pengumpulan_undian_berhadiahs.tujuan_pub')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_pengumpulan_undian_berhadiahs.id_kelurahan_pub')
                ->select('rekomendasi_pengumpulan_undian_berhadiahs.*', 'b.name_village')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_pengumpulan_undian_berhadiahs.id_kelurahan_pub', $user_wilayah->kelurahan_id)
                        ->where('log_pub.tujuan_pub', '!=', $user_wilayah->role_id)
                        ->where('log_pub.created_by_pub', '=', auth::user()->id)
                        // // ->where('rekomendasi_pengumpulan_undian_berhadiahs.petugas_pub','!=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_pengumpulan_undian_berhadiahs.status_aksi_pub', '=', 'Teruskan')
                                ->orWhere('rekomendasi_pengumpulan_undian_berhadiahs.status_aksi_pub', '=', 'kembalikan');
                        });
                })->distinct();
        }
        if ($user_wilayah->name == 'KepalaDinas') {
            //  dd(auth::user()->id);
            $query = DB::table('rekomendasi_pengumpulan_undian_berhadiahs')
                ->join('users', 'users.id', '=', 'rekomendasi_pengumpulan_undian_berhadiahs.createdby_pub')
                ->join('log_pub', 'log_pub.id_trx_pub', '=', 'rekomendasi_pengumpulan_undian_berhadiahs.id')
                //  ->join('model_has_roles', 'model_has_roles.model_id', '=', 'rekomendasi_pengumpulan_undian_berhadiahs.tujuan_pub')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_pengumpulan_undian_berhadiahs.id_kelurahan_pub')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_pengumpulan_undian_berhadiahs.id_kecamatan_pub')
                ->select('rekomendasi_pengumpulan_undian_berhadiahs.*', 'b.name_village')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_pengumpulan_undian_berhadiahs.id_kelurahan_pub', $user_wilayah->kelurahan_id)
                        ->where('log_pub.tujuan_pub', '!=', $user_wilayah->role_id)
                        ->where('log_pub.created_by_pub', '=', auth::user()->id)
                        // ->where('rekomendasi_pengumpulan_undian_berhadiahs.petugas_pub','!=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_pengumpulan_undian_berhadiahs.status_aksi_pub', '=', 'Teruskan')
                                ->orWhere('rekomendasi_pengumpulan_undian_berhadiahs.status_aksi_pub', '=', 'kembalikan');
                        });
                })->distinct();
        }
        if ($request->has('search') && !empty($request->search['value'])) {
            $search = $request->search['value'];
            $query = DB::table('rekomendasi_pengumpulan_undian_berhadiahs')
                ->join('users', 'users.id', '=', 'rekomendasi_pengumpulan_undian_berhadiahs.createdby_pub')
                ->join('log_pub', 'log_pub.id_trx_pub', '=', 'rekomendasi_pengumpulan_undian_berhadiahs.id')
                // ->join('model_has_roles', 'model_has_roles.model_id', '=', 'rekomendasi_pengumpulan_undian_berhadiahs.tujuan_pub')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_pengumpulan_undian_berhadiahs.id_kelurahan_pub')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_pengumpulan_undian_berhadiahs.id_kecamatan_pub')
                ->select('rekomendasi_pengumpulan_undian_berhadiahs.*', 'b.name_village', 'd.name_districts', 'log_pub.tujuan_pub', 'log_pub.petugas_pub')
                ->where(function ($query) use ($search) {
                    $query->where('rekomendasi_pengumpulan_undian_berhadiahs.no_pendaftaran_pub', 'like', "%$search%");
                });
        }
        $total_filtered_items = $query->count();
        // Add ordering
        if ($request->has('order')) {
            $order_column = $request->order[0]['column'];
            $order_direction = $request->order[0]['dir'];
            $query->orderBy($request->input('columns.' . $order_column . '.data'), $order_direction);
        }
        //Add paginate
        $data = $query->paginate($request->input('length'));

        return response()->json([
            'draw' => $request->input('draw'),
            'recordsTotal' => rekomendasi_pengumpulan_undian_berhadiah::count(),
            'recordsFiltered' => $total_filtered_items,
            'data' => $data,
        ]);
    }
    public function selesai(Request $request)
    {
        $user_name = Auth::user()->name;
        $query = DB::table('rekomendasi_pengumpulan_undian_berhadiahs')
            ->join('users', 'users.id', '=', 'rekomendasi_pengumpulan_undian_berhadiahs.createdby_pub')
            ->join('log_pub', 'log_pub.id_trx_pub', '=', 'rekomendasi_pengumpulan_undian_berhadiahs.id')
            ->join('model_has_roles', 'model_has_roles.model_id', '=', 'rekomendasi_pengumpulan_undian_berhadiahs.tujuan_pub')
            ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_pengumpulan_undian_berhadiahs.id_kelurahan_pub')
            ->select('rekomendasi_pengumpulan_undian_berhadiahs.*', 'b.name_village');
        $user_id = Auth::user()->id;
        $user_wilayah = DB::table('wilayahs')
            ->leftJoin('model_has_roles', 'model_has_roles.model_id', '=', 'wilayahs.createdby')
            ->leftJoin('users', 'users.id', '=', 'model_has_roles.model_id')
            ->leftJoin('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('createdby', $user_id)
            ->where(function ($query) {
                $query->where('status_wilayah', 1);
            })
            ->first();

        if ($user_wilayah->name == 'Front Office Kelurahan') {
            //  dd($user_wilayah->role_id);
            $query = DB::table('rekomendasi_pengumpulan_undian_berhadiahs')
                ->join('users', 'users.id', '=', 'rekomendasi_pengumpulan_undian_berhadiahs.createdby_pub')
                ->join('log_pub', 'log_pub.id_trx_pub', '=', 'rekomendasi_pengumpulan_undian_berhadiahs.id')
                ->join('indonesia_villages', 'indonesia_villages.code', '=', 'rekomendasi_pengumpulan_undian_berhadiahs.id_kelurahan_pub')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_pengumpulan_undian_berhadiahs.id_kecamatan_pub')
                ->select('rekomendasi_pengumpulan_undian_berhadiahs.*', 'd.name_districts', 'indonesia_villages.name_village', 'log_pub.tujuan_pub', 'log_pub.petugas_pub')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_pengumpulan_undian_berhadiahs.id_kelurahan_pub', $user_wilayah->kelurahan_id)
                        ->where('log_pub.tujuan_pub', '=', $user_wilayah->role_id)
                        ->where('log_pub.created_by_pub', '!=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_pengumpulan_undian_berhadiahs.status_aksi_pub', '=', 'Tolak')
                                ->orWhere('rekomendasi_pengumpulan_undian_berhadiahs.status_aksi_pub', '=', 'Selesai');
                        });
                })->distinct();
        } elseif ($user_wilayah->name == 'Front Office kota') {
            //  dd($user_wilayah->role_id);
            $query = DB::table('rekomendasi_pengumpulan_undian_berhadiahs')
                ->join('users', 'users.id', '=', 'rekomendasi_pengumpulan_undian_berhadiahs.createdby_pub')
                ->join('log_pub', 'log_pub.id_trx_pub', '=', 'rekomendasi_pengumpulan_undian_berhadiahs.id')
                ->join('indonesia_villages', 'indonesia_villages.code', '=', 'rekomendasi_pengumpulan_undian_berhadiahs.id_kelurahan_pub')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_pengumpulan_undian_berhadiahs.id_kecamatan_pub')
                ->select('rekomendasi_pengumpulan_undian_berhadiahs.*', 'd.name_districts', 'indonesia_villages.name_village', 'log_pub.tujuan_pub', 'log_pub.petugas_pub')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_pengumpulan_undian_berhadiahs.id_kelurahan_pub', $user_wilayah->kelurahan_id)
                        ->where('log_pub.tujuan_pub', '=', $user_wilayah->role_id)
                        ->where('log_pub.created_by_pub', '!=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_pengumpulan_undian_berhadiahs.status_aksi_pub', '=', 'Tolak')
                                ->orWhere('rekomendasi_pengumpulan_undian_berhadiahs.status_aksi_pub', '=', 'Selesai');
                        });
                })->distinct();
        } elseif ($user_wilayah->name == 'Back Ofiice kota') {
            // dd($user_wilayah);
            $query = DB::table('rekomendasi_pengumpulan_undian_berhadiahs')
                ->join('users', 'users.id', '=', 'rekomendasi_pengumpulan_undian_berhadiahs.createdby_pub')
                ->join('log_pub', 'log_pub.id_trx_pub', '=', 'rekomendasi_pengumpulan_undian_berhadiahs.id')
                // ->join('model_has_roles', 'model_has_roles.model_id', '=', 'rekomendasi_pengumpulan_undian_berhadiahs.tujuan_pub')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_pengumpulan_undian_berhadiahs.id_kecamatan_pub')
                ->select('rekomendasi_pengumpulan_undian_berhadiahs.*', 'd.name_districts', 'indonesia_villages.name_village', 'log_pub.tujuan_pub', 'log_pub.petugas_pub')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_pengumpulan_undian_berhadiahs.id_kelurahan_pub', $user_wilayah->kelurahan_id)
                        ->where('log_pub.tujuan_pub', '!=', $user_wilayah->role_id)
                        ->where('log_pub.created_by_pub', '=', auth::user()->id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_pengumpulan_undian_berhadiahs.status_aksi_pub', '=', 'Tolak')
                                ->orWhere('rekomendasi_pengumpulan_undian_berhadiahs.status_aksi_pub', '=', 'Selesai');
                        });
                })->distinct();
            // dd($query); 
        } elseif ($user_wilayah->name == 'kepala bidang') {
            // dd($user_wilayah);
            $query = DB::table('rekomendasi_pengumpulan_undian_berhadiahs')
                ->join('users', 'users.id', '=', 'rekomendasi_pengumpulan_undian_berhadiahs.createdby_pub')
                ->join('log_pub', 'log_pub.id_trx_pub', '=', 'rekomendasi_pengumpulan_undian_berhadiahs.id')
                // ->join('model_has_roles', 'model_has_roles.model_id', '=', 'rekomendasi_pengumpulan_undian_berhadiahs.tujuan_pub')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_pengumpulan_undian_berhadiahs.id_kecamatan_pub')
                ->select('rekomendasi_pengumpulan_undian_berhadiahs.*', 'd.name_districts', 'indonesia_villages.name_village', 'log_pub.tujuan_pub', 'log_pub.petugas_pub')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_pengumpulan_undian_berhadiahs.id_kelurahan_pub', $user_wilayah->kelurahan_id)
                        ->where('log_pub.tujuan_pub', '!=', $user_wilayah->role_id)
                        ->where('log_pub.created_by_pub', '=', auth::user()->id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_pengumpulan_undian_berhadiahs.status_aksi_pub', '=', 'Tolak')
                                ->orWhere('rekomendasi_pengumpulan_undian_berhadiahs.status_aksi_pub', '=', 'Selesai');
                        });
                })->distinct();
        } elseif ($user_wilayah->name == 'SekertarisDinas') {
            // dd($user_wilayah->role_id);
            $query = DB::table('rekomendasi_pengumpulan_undian_berhadiahs')
                ->join('users', 'users.id', '=', 'rekomendasi_pengumpulan_undian_berhadiahs.createdby_pub')
                ->join('log_pub', 'log_pub.id_trx_pub', '=', 'rekomendasi_pengumpulan_undian_berhadiahs.id')
                // ->join('model_has_roles', 'model_has_roles.model_id', '=', 'rekomendasi_pengumpulan_undian_berhadiahs.tujuan_pub')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_pengumpulan_undian_berhadiahs.id_kecamatan_pub')
                ->select('rekomendasi_pengumpulan_undian_berhadiahs.*', 'd.name_districts', 'indonesia_villages.name_village', 'log_pub.tujuan_pub', 'log_pub.petugas_pub')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_pengumpulan_undian_berhadiahs.id_kelurahan_pub', $user_wilayah->kelurahan_id)
                        ->where('log_pub.tujuan_pub', '!=', $user_wilayah->role_id)
                        ->where('log_pub.created_by_pub', '=', auth::user()->id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_pengumpulan_undian_berhadiahs.status_aksi_pub', '=', 'Tolak')
                                ->orWhere('rekomendasi_pengumpulan_undian_berhadiahs.status_aksi_pub', '=', 'Selesai');
                        });
                })->distinct();
        } elseif ($user_wilayah->name == 'KepalaDinas') {
            // dd($user_wilayah);
            $query = DB::table('rekomendasi_pengumpulan_undian_berhadiahs')
                ->join('users', 'users.id', '=', 'rekomendasi_pengumpulan_undian_berhadiahs.createdby_pub')
                ->join('log_pub', 'log_pub.id_trx_pub', '=', 'rekomendasi_pengumpulan_undian_berhadiahs.id')
                // ->join('model_has_roles', 'model_has_roles.model_id', '=', 'rekomendasi_pengumpulan_undian_berhadiahs.tujuan_pub')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_pengumpulan_undian_berhadiahs.id_kecamatan_pub')
                ->select('rekomendasi_pengumpulan_undian_berhadiahs.*', 'd.name_districts', 'indonesia_villages.name_village', 'log_pub.tujuan_pub', 'log_pub.petugas_pub')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_pengumpulan_undian_berhadiahs.id_kelurahan_pub', $user_wilayah->kelurahan_id)
                        ->where('log_pub.tujuan_pub', '=', $user_wilayah->role_id)
                        ->where('log_pub.petugas_pub', '=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_pengumpulan_undian_berhadiahs.status_aksi_pub', '=', 'Tolak')
                                ->orWhere('rekomendasi_pengumpulan_undian_berhadiahs.status_aksi_pub', '=', 'Selesai');
                        });
                });
        }

        if ($request->has('search') && !empty($request->search['value'])) {
            $search = $request->search['value'];
            $query = DB::table('rekomendasi_pengumpulan_undian_berhadiahs')
                ->join('users', 'users.id', '=', 'rekomendasi_pengumpulan_undian_berhadiahs.createdby_pub')
                ->join('wilayahs', 'wilayahs.createdby', '=', 'users.id')
                ->join('model_has_roles', 'model_has_roles.model_id', '=', 'rekomendasi_pengumpulan_undian_berhadiahs.tujuan_pub')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_pengumpulan_undian_berhadiahs.id_kelurahan_pub')
                ->select('rekomendasi_pengumpulan_undian_berhadiahs.*', 'b.name_village')
                ->where(function ($query) use ($search) {
                    $query->where('rekomendasi_pengumpulan_undian_berhadiahs.no_pendaftaran_pub', 'like', "%$search%");
                });
        }

        // Get total count of filtered items
        $total_filtered_items = $query->count();
        // Add ordering

        // Get paginated data
        $data = $query->paginate($request->input('length'));
        // dd($data);
        // mengubah data JSON menjadi objek PHP

        return response()->json([
            'draw' => $request->input('draw'),
            'recordsTotal' => rekomendasi_pengumpulan_undian_berhadiah::count(),
            'recordsFiltered' => $total_filtered_items,
            'data' => $data,
        ]);
    }
    
}
