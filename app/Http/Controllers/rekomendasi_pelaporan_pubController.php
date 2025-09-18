<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\log_pelaporanPub;
use App\Models\rekomendasi_pelaporan_pub;
use App\Models\Roles;
use App\Repositories\rekomendasi_pelaporan_pubRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use stdClass;
use Barryvdh\DomPDF\Facade\Pdf;

class rekomendasi_pelaporan_pubController extends Controller
{
    /** @var rekomendasi_pelaporan_pubRepository $rekomendasiPelaporanPUBRepository*/
    private $rekomendasiPelaporanPubRepository;

    public function __construct(rekomendasi_pelaporan_pubRepository $rekomendasiPelaporanPubRepo)
    {
        $this->rekomendasiPelaporanPubRepository = $rekomendasiPelaporanPubRepo;
    }

    /**
     * Display a listing of the rekomendasi_pelaporan_pub.
     */



    public function index(Request $request)
    {
        $rekomendasiPelaporanPUB = $this->rekomendasiPelaporanPubRepository->paginate(10);
        // dd()
        return view('rekomendasi_pelaporan_pubs.index')
            ->with('rekomendasi_pelaporan_pubs', $rekomendasiPelaporanPUB);
    }

    /**
     * Show the form for creating a new rekomendasi_pelaporan_pub.
     */
    public function FileRekomPelaporanPub($id)
    {
        $queryRekomendasiPelaporanPub = rekomendasi_pelaporan_pub::find($id);
       //  dd($rekomendasiTerdaftaryayasan);
        $pdf = PDF::loadHtml(view('rekomendasi_pelaporan_pubs.file_permohonan',compact('queryRekomendasiPelaporanPub')));
        $filename = 'File Permohonan' . $queryRekomendasiPelaporanPub->nama_ubar . '.pdf';
        return $pdf->stream($filename);
    }
    public function create()
    {
        $v = rekomendasi_pelaporan_pub::latest()->first();
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

        if ($roles->contains('Front Office Kelurahan')|| $roles->contains('Front Office kota')) {
            // Jika user memiliki role 'FO-Kota', maka tampilkan alur dengan nama 'Draft' dan 'Teruskan'
            $alur = DB::table('alur')
                ->whereIn('name', ['Draft', 'Teruskan'])
                ->get();
        } else if ($roles->contains('Back Ofiice Kota') || $roles->contains('SekertarisDinas') || $roles->contains('kepala bidang') ) {
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
                ->whereIn('name', ['Back Ofiice kelurahan', 'Back Ofiice Kota'])
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
        $checkroles = Roles::where('name', 'Front Office kota')
            ->orWhere('name', 'SekertarisDinas')
            ->orWhere('name', 'KepalaDinas')
            ->get();
        return view('rekomendasi_pelaporan_pubs.create', compact('wilayah', 'roleid', 'checkroles', 'alur', 'kecamatans'));
    }

    /**
     * Store a newly created rekomendasi_pelaporan_pub in storage.
     */
    public function store(Request $request)
    {
        // dd($request->all());
        // $getdata = rekomendasi_pelaporan_pub::where('id', Auth::user()->id)->first();
        $data = new rekomendasi_pelaporan_pub();
        $files = [
            'surat_permohonan_pub',
            'surat_izin_terdaftar',
            'surat_keterangan_domisili',
            'no_pokok_wajib_pajak',
            'bukti_setor_pajak',
            'norek_penampung_pub',
            'ktp_direktur',
            'super_keabsahan_dokumen',
            'super_bermaterai_cukup',
            'proposal_pub',
            'file_permohonan'

        ];

        foreach ($files as $file) {
            if ($request->file($file)) {
                $path = $request->file($file);
                $nama_file = 'pelaporanpub/' . $file . '/' . $path->getClientOriginalName();
                Storage::disk('imagekit')->put($nama_file, fopen($path->getRealPath(), 'r'));
                $data[$file] = Storage::disk('imagekit')->url($nama_file);
            } else {
                $data[$file] = null;
            }
        }

        $data['no_pendaftaran_ubar'] = mt_rand(100, 1000);
        $data['Sistem_Pengumpulan'] = $request->get('Sistem_Pengumpulan');
        $data['id_provinsi_ubar'] = $request->get('id_provinsi_ubar');
        $data['id_kabkot_ubar'] = $request->get('id_kabkot_ubar');
        $data['id_kecamatan_ubar'] = $request->get('id_kecamatan_ubar');
        $data['id_kelurahan_ubar'] = $request->get('id_kelurahan_ubar');
        $data['nik_ubar'] = $request->get('nik_ubar');
        $data['nama_ubar'] = $request->get('nama_ubar');
        $data['telp_ubar'] = $request->get('telp_ubar');
        $data['alamat_ubar'] = $request->get('alamat_ubar');
        $data['catatan_ubar'] = $request->get('catatan_ubar');
        $data['tujuan_ubar'] = $request->get('tujuan_ubar');
        $data['status_aksi_ubar'] = $request->get('status_aksi_ubar');
        $data['petugas_ubar'] = $request->get('petugas_ubar');
        $data['createdby_ubar'] = Auth::user()->id;
        //  dd($data);
        $data->save();


        $logpengaduan = new log_pelaporanPub();
        $logpengaduan['id_trx_ubar'] = $data->id;
        $logpengaduan['id_alur_ubar'] = $request->get('status_aksi_ubar');
        $logpengaduan['petugas_ubar'] = $request->get('petugas_ubar');
        $logpengaduan['catatan_ubar']  = $request->get('catatan_ubar');
        $logpengaduan['file_permohonan_ubar'] = $request->get('file_permohonan');
        $logpengaduan['tujuan_ubar'] = $request->get('tujuan_ubar');
        $logpengaduan['created_by_ubar'] = Auth::user()->id;
        $logpengaduan['updated_by_ubar'] = Auth::user()->id;
        if ($data['status_aksi_ubar'] !== 'Draft') {
            $logpengaduan = new log_pelaporanPub();
            $logpengaduan['id_trx_ubar'] = $data->id;
            $logpengaduan['id_alur_ubar'] = $request->get('status_aksi_ubar');
            $logpengaduan['petugas_ubar'] = $request->get('petugas_ubar');
            $logpengaduan['catatan_ubar']  = $request->get('catatan_ubar');
            $logpengaduan['file_permohonan_ubar'] = $request->get('file_permohonan');
            $logpengaduan['tujuan_ubar'] = $request->get('tujuan_ubar');
            $logpengaduan['created_by_ubar'] = Auth::user()->id;
            $logpengaduan['updated_by_ubar'] = Auth::user()->id;

            $logpengaduan->save();
        } else {
            return redirect('rekomendasi_pelaporan_pubs')->withSuccess('Data Disimpan Kedalam Draft');
        }
        // dd($logpengaduan);

        return redirect('rekomendasi_pelaporan_pubs')->withSuccess('Data Berhasil Disimpan');
    }

    /**
     * Display the specified rekomendasi_pelaporan_pub.
     */
    public function show($id)
    {
        $userid = Auth::user()->id;
        $rekomendasiPelaporanPUB =  DB::table('rekomendasi_pelaporan_pubs as w')->select(
            'w.*',
            'rls.name as name_roles',
            'usr.name',
            'prov.name_prov',
            'kota.name_cities',
            'kecamatan.name_districts',
            'b.name_village',
        )
            ->leftjoin('roles as rls', 'rls.id', '=', 'w.tujuan_ubar')
            ->leftjoin('users as usr', 'usr.id', '=', 'w.petugas_ubar')
            ->leftjoin('indonesia_provinces as prov', 'prov.code', '=', 'w.id_provinsi_ubar')
            ->leftjoin('indonesia_cities as kota', 'kota.code', '=', 'w.id_kabkot_ubar')
            ->leftjoin('indonesia_districts as kecamatan', 'kecamatan.code', '=', 'w.id_kecamatan_ubar')
            ->leftjoin('indonesia_villages as b', 'b.code', '=', 'w.id_kelurahan_ubar')
            ->where('w.id', $id)->first();
        if (empty($rekomendasiPelaporanPUB)) {
            Flash::error('Rekomendasi not found');

            return redirect(route('rekomendasi_pelaporan_pubs.index'));
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

     
        // $log_pelaporanPUB = log_pelaporanPUB::join('roles', 'roles.name', '=', 'log_pelaporanPUB.tujuan_ubar')
        // ->join('users', 'users.name', '=', 'log_pelaporanPUB.created_by_ubar')
        // ->where('log_pelaporanPUB.id_trx_ubar', $id)
        // ->select('log_pelaporanPUB.*', 'roles.name', 'users.name')
        // ->get();
        // dd($log_pelaporanPUB);
        
        $log_pelaporanPUB =  DB::table('log_pelaporanpub as w')->select(
            'w.*',
            'usr.name'
        )
            ->leftjoin('users as usr', 'usr.id', '=', 'w.petugas_ubar')
            ->where('w.id_trx_ubar', $id)->get();
        // dd($log_pelaporanPUB);
        return view('rekomendasi_pelaporan_pubs.show', compact('rekomendasiPelaporanPUB', 'roleid', 'checkroles', 'log_pelaporanPUB'));
    }


    /**
     * Show the form for editing the specified rekomendasi_pelaporan_pub.
     */
    public function edit($id)
    {
        $getUsers = DB::table('model_has_roles')
            ->leftjoin('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->leftjoin('rekomendasi_pelaporan_pubs', 'rekomendasi_pelaporan_pubs.createdby_ubar', '=', 'model_has_roles.model_id')
            ->where('rekomendasi_pelaporan_pubs.id', '=', $id)
            // ->where('status_aksi', '=', 'Draft')
            // ->orwhere('status_aksi', '=', 'Teruskan')
            ->get();

        $users =  Auth::user()->id;
        $getAuth = DB::table('model_has_roles')
            ->leftJoin('users', 'users.id', '=', 'model_has_roles.model_id')
            ->leftJoin('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('model_id', $users)
            ->get();
        
        $createdby = DB::table('rekomendasi_pelaporan_pubs')
            ->join('users', 'rekomendasi_pelaporan_pubs.createdby_ubar', '=', 'users.name')
            ->join('model_has_roles', 'model_has_roles.model_id', '=', 'users.id')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->select('rekomendasi_pelaporan_pubs.id', 'rekomendasi_pelaporan_pubs.createdby_ubar', 'roles.name')
            ->get();
        

        $rekomendasiPelaporanPUB =  DB::table('rekomendasi_pelaporan_pubs as w')->select(
            'w.*',
            'rls.name',
            'usr.name',
            'prov.name_prov',
            'kota.name_cities',
            'kecamatan.name_districts',
            'b.name_village',
        )
            ->leftjoin('roles as rls', 'rls.id', '=', 'w.tujuan_ubar')
            ->leftjoin('users as usr', 'usr.id', '=', 'w.petugas_ubar')
            ->leftjoin('indonesia_provinces as prov', 'prov.code', '=', 'w.id_provinsi_ubar')
            ->leftjoin('indonesia_cities as kota', 'kota.code', '=', 'w.id_kabkot_ubar')
            ->leftjoin('indonesia_districts as kecamatan', 'kecamatan.code', '=', 'w.id_kecamatan_ubar')
            ->leftjoin('indonesia_villages as b', 'b.code', '=', 'w.id_kelurahan_ubar')
            ->where('w.id', $id)->first();
        // dd($rekomendasiPelaporanPUB);

        $getdata = DB::table('model_has_roles')
            ->leftjoin('rekomendasi_pelaporan_pubs as b', 'b.tujuan_ubar', '=', 'model_has_roles.role_id')
            ->where('b.id', $id)
            ->get();
        //alur
        $user = Auth::user();
        $roles = $user->roles()->pluck('name');

        if ($roles->contains('Front Office kelurahan') || $roles->contains('Front Office kota') ) {
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
            // $alur = collect();
            $alur = DB::table('alur')
            ->whereIn('name', ['Kembalikan', 'Tolak', 'Teruskan'])
            ->get();
        }


        $user = Auth::user();
        $roles = $user->roles()->pluck('name');

        if ($roles->contains('Front Office Kelurahan')) {
            $roleid = DB::table('roles')
                ->where('name', 'Front Office kota')
                ->get();
                
        } elseif ($roles->contains('Back Ofiice kelurahan')) {
            $roleid = DB::table('roles')
            ->whereIn('name', ['Back Ofiice Kota', 'Front Office kelurahan'])
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



        return view('rekomendasi_pelaporan_pubs.edit', compact( 'rekomendasiPelaporanPUB', 'roleid', 'getdata', 'alur', 'createdby', 'getUsers', 'getAuth'));
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
     * Update the specified rekomendasi_pelaporan_pub in storage.
     */
    public function update($id, Request $request)
    {
        $getdata = rekomendasi_pelaporan_pub::where('id', $id)->first();
       
        $data = $request->all();
        $files = [
            'surat_permohonan_pub',
            'surat_izin_terdaftar',
            'surat_keterangan_domisili',
            'no_pokok_wajib_pajak',
            'bukti_setor_pajak',
            'norek_penampung_pub',
            'ktp_direktur',
            'super_keabsahan_dokumen',
            'super_bermaterai_cukup',
            'proposal_pub',
            'file_permohonan'

        ];
        $sudahUpload = [
            'surat_permohonan_pub',
            'surat_izin_terdaftar',
            'surat_keterangan_domisili',
            'no_pokok_wajib_pajak',
            'bukti_setor_pajak',
            'norek_penampung_pub',
            'ktp_direktur',
            'super_keabsahan_dokumen',
            'super_bermaterai_cukup',
            'proposal_pub',
            'file_permohonan'

        ];
        foreach ($files as $file) {
            if ($request->file($file)) {
                $path = $request->file($file);
                $nama_file = 'pelaporanpub/' . $file . '/' . $path->getClientOriginalName();
                Storage::disk('imagekit')->put($nama_file, fopen($path->getRealPath(), 'r'));
                $data[$file] = Storage::disk('imagekit')->url($nama_file);
            } elseif($request->input($sudahUpload)) {
                $data[$file] = $getdata->$file;
            }
        }
        $data['updatedby_ubar'] = Auth::user()->id;
        $getdata->update($data);
   
        

        $logpengaduan = new log_pelaporanPub();
        $logpengaduan['id_trx_ubar'] = $getdata->id;
        $logpengaduan['id_alur_ubar'] = $request->get('status_aksi_ubar');
        $logpengaduan['petugas_ubar'] = $request->get('petugas_ubar');
        $logpengaduan['catatan_ubar']  = $request->get('catatan_ubar');
        $logpengaduan['validasi_surat']  = $request->get('validasi_surat');
        $logpengaduan['file_permohonan_ubar'] = $request->get('file_permohonan');
        $logpengaduan['tujuan_ubar'] = $request->get('tujuan_ubar');
        $logpengaduan['created_by_ubar'] = Auth::user()->id;
        $logpengaduan['updated_by_ubar'] = Auth::user()->id;
        $logpengaduan->save();
        return redirect()->route('rekomendasi_pelaporan_pubs.index')->with('success', 'Data berhasil diupdate.');
    }

    /**
     * Remove the specified rekomendasi_pelaporan_pub from storage.
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $rekomendasi = rekomendasi_pelaporan_pub::findOrFail($id);

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
            ->with('success', 'Rekomendasi terdaftar pub berhasil dihapus.');
    }


    public function draft(Request $request)
    {
        $user_name = Auth::user()->name;
        $query = DB::table('rekomendasi_pelaporan_pubs')
            ->leftjoin('users', 'users.id', '=', 'rekomendasi_pelaporan_pubs.petugas_ubar')
            // ->leftjoin('wilayahs', 'wilayahs.createdby', '=', 'rekomendasi_pelaporan_pubs.createdby_ubar')
            // ->leftjoin('model_has_roles', 'model_has_roles.model_id', '=', 'rekomendasi_pelaporan_pubs.tujuan_ubar_bantuan_pendidikans')
            ->leftjoin('indonesia_villages as b', 'b.code', '=', 'rekomendasi_pelaporan_pubs.id_kelurahan_ubar')
            ->leftjoin('indonesia_districts as d', 'd.code', '=', 'rekomendasi_pelaporan_pubs.id_kecamatan_ubar')
            ->select('rekomendasi_pelaporan_pubs.*', 'b.name_village', 'd.name_districts', 'users.name')
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
        if ($user_wilayah->name == 'Front Office kota') {
            $query->Where(function ($query) use ($user_wilayah) {
                $query->where('rekomendasi_pelaporan_pubs.id_kabkot_ubar', $user_wilayah->kota_id);
                $query->where('rekomendasi_pelaporan_pubs.status_aksi_ubar', 'Draft');
                $query->where('rekomendasi_pelaporan_pubs.createdby_ubar',  Auth::user()->id);
            });
        }
        if ($user_wilayah->name == 'Front Office Kelurahan') {
            $query->Where(function ($query) use ($user_wilayah) {
                $query->where('rekomendasi_pelaporan_pubs.id_kelurahan_ubar', $user_wilayah->kelurahan_id);
                $query->where('rekomendasi_pelaporan_pubs.status_aksi_ubar', 'Draft');
                // $query->where('rekomendasi_pelaporan_pubs.createdby_ubar',  Auth::user()->id);
            });
        }
        if ($user_wilayah->name == 'fasilitator') {
            $query->Where(function ($query) use ($user_wilayah) {
                $query->where('rekomendasi_pelaporan_pubs.id_kelurahan_ubar', $user_wilayah->kelurahan_id);
                $query->where('rekomendasi_pelaporan_pubs.status_aksi_ubar', 'Draft');
                $query->where('rekomendasi_pelaporan_pubs.createdby_ubar',  Auth::user()->id);
            });
        }
        if ($request->has('search')) {
            // dd($query);
            $search = $request->search['value'];
            $query->where(function ($query) use ($search) {
                $query->where('rekomendasi_pelaporan_pubs.no_pendaftaran_ubar', 'like', "%$search%");
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
        // Get paginated data
        $start = $request->start;
        $length = $request->length;
        $query->offset($start)->limit($length);
        $data = $query->get();
        // mengubah data JSON menjadi objek PHP

        return response()->json([
            'draw' => $request->input('draw'),
            'recordsTotal' => rekomendasi_pelaporan_pub::count(),
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
        if ($user_wilayah->name == 'fasilitator') {
            $query = DB::table('rekomendasi_pelaporan_pubs')
                ->join('users', 'users.id', '=', 'rekomendasi_pelaporan_pubs.petugas_ubar')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_pelaporan_pubs.id_kecamatan_ubar')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_pelaporan_pubs.id_kelurahan_ubar')
                ->select('rekomendasi_pelaporan_pubs.*', 'b.name_village', 'd.name_districts', 'users.name')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_pelaporan_pubs.id_kelurahan_ubar', '=', $user_wilayah->kelurahan_id)
                        ->where('rekomendasi_pelaporan_pubs.tujuan_ubar', '=', $user_wilayah->role_id)
                        ->where('rekomendasi_pelaporan_pubs.petugas_ubar', '=', auth::user()->id)
    
                        ->where(function ($query) {
                            $query->where('rekomendasi_pelaporan_pubs.status_aksi_ubar', '=', 'Teruskan')
                                ->orWhere('rekomendasi_pelaporan_pubs.status_aksi_ubar', '=', 'kembalikan');
                        });
                });
        } elseif ($user_wilayah->name == 'Front Office kota') {
            $query = DB::table('rekomendasi_pelaporan_pubs')
                ->join('users', 'users.id', '=', 'rekomendasi_pelaporan_pubs.petugas_ubar')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_pelaporan_pubs.id_kecamatan_ubar')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_pelaporan_pubs.id_kelurahan_ubar')
                ->select('rekomendasi_pelaporan_pubs.*', 'b.name_village', 'd.name_districts', 'users.name')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_pelaporan_pubs.id_kabkot_ubar', '=', $user_wilayah->kota_id)
                        ->where('rekomendasi_pelaporan_pubs.tujuan_ubar', '=', $user_wilayah->role_id)
                        ->where('rekomendasi_pelaporan_pubs.petugas_ubar', '=', auth::user()->id)
    
                        ->where(function ($query) {
                            $query->where('rekomendasi_pelaporan_pubs.status_aksi_ubar', '=', 'Teruskan')
                                ->orWhere('rekomendasi_pelaporan_pubs.status_aksi_ubar', '=', 'kembalikan');
                        });
                });
        } elseif ($user_wilayah->name == 'Front Office Kelurahan') {
            $query = DB::table('rekomendasi_pelaporan_pubs')
                ->join('users', 'users.id', '=', 'rekomendasi_pelaporan_pubs.petugas_ubar')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_pelaporan_pubs.id_kecamatan_ubar')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_pelaporan_pubs.id_kelurahan_ubar')
                ->select('rekomendasi_pelaporan_pubs.*', 'b.name_village', 'd.name_districts', 'users.name')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_pelaporan_pubs.id_kelurahan_ubar', '=', $user_wilayah->kelurahan_id)
                        ->where('rekomendasi_pelaporan_pubs.tujuan_ubar', '=', $user_wilayah->role_id)
                        ->where('rekomendasi_pelaporan_pubs.petugas_ubar', '=', auth::user()->id)
    
                        ->where(function ($query) {
                            $query->where('rekomendasi_pelaporan_pubs.status_aksi_ubar', '=', 'Teruskan')
                                ->orWhere('rekomendasi_pelaporan_pubs.status_aksi_ubar', '=', 'kembalikan');
                        });
                });
        } elseif ($user_wilayah->name == 'supervisor') {
            $query = DB::table('rekomendasi_pelaporan_pubs')
                ->join('users', 'users.id', '=', 'rekomendasi_pelaporan_pubs.petugas_ubar')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_pelaporan_pubs.id_kecamatan_ubar')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_pelaporan_pubs.id_kelurahan_ubar')
                ->select('rekomendasi_pelaporan_pubs.*', 'b.name_village', 'd.name_districts', 'users.name')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_pelaporan_pubs.id_kabkot_ubar', '=', $user_wilayah->kota_id)
                        ->where('rekomendasi_pelaporan_pubs.tujuan_ubar', '=', $user_wilayah->role_id)
                        ->where('rekomendasi_pelaporan_pubs.petugas_ubar', '=', auth::user()->id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_pelaporan_pubs.status_aksi_ubar', '=', 'Teruskan')
                                ->orWhere('rekomendasi_pelaporan_pubs.status_aksi_ubar', '=', 'kembalikan');
                        });
                    // dd($va);
                });
        } elseif ($user_wilayah->name == 'Back Ofiice kelurahan') {
            $query = DB::table('rekomendasi_pelaporan_pubs')
                ->join('users', 'users.id', '=', 'rekomendasi_pelaporan_pubs.petugas_ubar')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_pelaporan_pubs.id_kecamatan_ubar')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_pelaporan_pubs.id_kelurahan_ubar')
                ->select('rekomendasi_pelaporan_pubs.*', 'b.name_village', 'd.name_districts', 'users.name')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_pelaporan_pubs.id_kelurahan_ubar', '=', $user_wilayah->kelurahan_id)
                        ->where('rekomendasi_pelaporan_pubs.tujuan_ubar', '=', $user_wilayah->role_id)
                        ->where('rekomendasi_pelaporan_pubs.petugas_ubar', '=', auth::user()->id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_pelaporan_pubs.status_aksi_ubar', '=', 'Teruskan')
                                ->orWhere('rekomendasi_pelaporan_pubs.status_aksi_ubar', '=', 'kembalikan');
                        });
                    // dd($va);
                });
        } elseif ($user_wilayah->name == 'Back Ofiice Kota') {
            $query = DB::table('rekomendasi_pelaporan_pubs')
                ->join('users', 'users.id', '=', 'rekomendasi_pelaporan_pubs.petugas_ubar')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_pelaporan_pubs.id_kecamatan_ubar')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_pelaporan_pubs.id_kelurahan_ubar')
                ->select('rekomendasi_pelaporan_pubs.*', 'b.name_village', 'd.name_districts', 'users.name')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_pelaporan_pubs.id_kabkot_ubar', '=', $user_wilayah->kota_id)
                        ->where('rekomendasi_pelaporan_pubs.tujuan_ubar', '=', $user_wilayah->role_id)
                        ->where('rekomendasi_pelaporan_pubs.petugas_ubar', '=', auth::user()->id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_pelaporan_pubs.status_aksi_ubar', '=', 'Teruskan')
                                ->orWhere('rekomendasi_pelaporan_pubs.status_aksi_ubar', '=', 'kembalikan');
                        });
                    // dd($va);
                });
        } elseif ($user_wilayah->name == 'kepala bidang') {
            $query = DB::table('rekomendasi_pelaporan_pubs')
                ->join('users', 'users.id', '=', 'rekomendasi_pelaporan_pubs.petugas_ubar')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_pelaporan_pubs.id_kecamatan_ubar')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_pelaporan_pubs.id_kelurahan_ubar')
                ->select('rekomendasi_pelaporan_pubs.*', 'b.name_village', 'd.name_districts', 'users.name')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_pelaporan_pubs.id_kabkot_ubar', '=', $user_wilayah->kota_id)
                        ->where('rekomendasi_pelaporan_pubs.tujuan_ubar', '=', $user_wilayah->role_id)
                        ->where('rekomendasi_pelaporan_pubs.petugas_ubar', '=', auth::user()->id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_pelaporan_pubs.status_aksi_ubar', '=', 'Teruskan')
                                ->orWhere('rekomendasi_pelaporan_pubs.status_aksi_ubar', '=', 'kembalikan');
                        });
                    // dd($va);
                });
        } elseif ($user_wilayah->name == 'SekertarisDinas') {
            $query = DB::table('rekomendasi_pelaporan_pubs')
                ->join('users', 'users.id', '=', 'rekomendasi_pelaporan_pubs.petugas_ubar')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_pelaporan_pubs.id_kecamatan_ubar')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_pelaporan_pubs.id_kelurahan_ubar')
                ->select('rekomendasi_pelaporan_pubs.*', 'b.name_village', 'd.name_districts', 'users.name')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_pelaporan_pubs.id_kabkot_ubar', '=', $user_wilayah->kota_id)
                        ->where('rekomendasi_pelaporan_pubs.tujuan_ubar', '=', $user_wilayah->role_id)
                        ->where('rekomendasi_pelaporan_pubs.petugas_ubar', '=', auth::user()->id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_pelaporan_pubs.status_aksi_ubar', '=', 'Teruskan')
                                ->orWhere('rekomendasi_pelaporan_pubs.status_aksi_ubar', '=', 'kembalikan');
                        });
                    // dd($va);
                });
        } elseif ($user_wilayah->name == 'KepalaDinas') {
            $query = DB::table('rekomendasi_pelaporan_pubs')
                ->join('users', 'users.id', '=', 'rekomendasi_pelaporan_pubs.petugas_ubar')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_pelaporan_pubs.id_kecamatan_ubar')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_pelaporan_pubs.id_kelurahan_ubar')
                ->select('rekomendasi_pelaporan_pubs.*', 'b.name_village', 'd.name_districts', 'users.name')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_pelaporan_pubs.id_kabkot_ubar', '=', $user_wilayah->kota_id)
                        ->where('rekomendasi_pelaporan_pubs.tujuan_ubar', '=', $user_wilayah->role_id)
                        ->where('rekomendasi_pelaporan_pubs.petugas_ubar', '=', auth::user()->id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_pelaporan_pubs.status_aksi_ubar', '=', 'Teruskan')
                                ->orWhere('rekomendasi_pelaporan_pubs.status_aksi_ubar', '=', 'kembalikan');
                        });
                    // dd($va);
                });
        } else {
            // $query = DB::table('pengaduans')
            //     ->join('users', 'users.id', '=', 'pengaduans.createdby')
            //     ->join('indonesia_villages as b', 'b.code', '=', 'pengaduans.id_kelurahan')
            //     ->select('pengaduans.*', 'b.name_village');
        }

      
        if ($request->has('search') && !empty($request->search['value'])) {
            $search = $request->search['value'];
            $query = DB::table('rekomendasi_pelaporan_pubs')
                ->join('users', 'users.id', '=', 'rekomendasi_pelaporan_pubs.petugas_ubar')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_pelaporan_pubs.id_kecamatan_ubar')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_pelaporan_pubs.id_kelurahan_ubar')
                ->select('rekomendasi_pelaporan_pubs.*', 'b.name_village', 'd.name_districts', 'users.name')
                ->where(function ($query) use ($search) {
                    $query->where('rekomendasi_pelaporan_pubs.no_pendaftaran_ubar', 'like', "%$search%");
                });
        }
        // Get total count of filtered items
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
        $gabunganData->dataRekomPub=$tmp;
           array_push($result, $gabunganData);
        }
        // dd($result);
        return response()->json([
            'draw' => $request->input('draw'),
            'recordsTotal' => rekomendasi_pelaporan_pub::count(),
            'recordsFiltered' => $total_filtered_items,
            'data' => $result,
        ]);
    }

    public function teruskan(Request $request)
    {

        $query = DB::table('rekomendasi_pelaporan_pubs')
            ->join('users', 'users.id', '=', 'rekomendasi_pelaporan_pubs.petugas_ubar')
            ->leftjoin('indonesia_villages as b', 'b.code', '=', 'rekomendasi_pelaporan_pubs.id_kelurahan_ubar')
            ->leftjoin('indonesia_districts as d', 'd.code', '=', 'rekomendasi_pelaporan_pubs.id_kecamatan_ubar')
            ->select('rekomendasi_pelaporan_pubs.*', 'b.name_village', 'd.name_districts', 'users.name');

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
        if ($user_wilayah->name == 'fasilitator') {
            $query = DB::table('rekomendasi_pelaporan_pubs')
                    ->join('users', 'users.id', '=', 'rekomendasi_pelaporan_pubs.petugas_ubar')
                    // ->join('log_pelaporanpub', 'log_pelaporanpub.id_trx_ubar', '=', 'rekomendasi_pelaporan_pubs.id')
                    // ->join('model_has_roles', 'model_has_roles.model_id', '=', 'rekomendasi_pelaporan_pubs.tujuan_ubar')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_pelaporan_pubs.id_kelurahan_ubar')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_pelaporan_pubs.id_kecamatan_ubar')
                ->select('rekomendasi_pelaporan_pubs.*', 'b.name_village', 'd.name_districts','users.name')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_pelaporan_pubs.id_kelurahan_ubar', $user_wilayah->kelurahan_id)
                        ->where('rekomendasi_pelaporan_pubs.tujuan_ubar', '!=', $user_wilayah->role_id)
                        ->where('rekomendasi_pelaporan_pubs.petugas_ubar', '!=', auth::user()->id)
                        // ->where('rekomendasi_pelaporan_pubs.petugas_ubar','!=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_pelaporan_pubs.status_aksi_ubar', '=', 'Teruskan')
                                ->orWhere('rekomendasi_pelaporan_pubs.status_aksi_ubar', '=', 'kembalikan');
                        });
                })->distinct();
            // dd($query);

        }
        if ($user_wilayah->name == 'Front Office Kelurahan') {
            $query = DB::table('rekomendasi_pelaporan_pubs')
                ->join('users', 'users.id', '=', 'rekomendasi_pelaporan_pubs.petugas_ubar')
                // ->join('model_has_roles', 'model_has_roles.model_id', '=', 'rekomendasi_pelaporan_pubs.tujuan_ubar')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_pelaporan_pubs.id_kelurahan_ubar')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_pelaporan_pubs.id_kecamatan_ubar')
                ->select('rekomendasi_pelaporan_pubs.*', 'b.name_village', 'd.name_districts','users.name')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_pelaporan_pubs.id_kelurahan_ubar', $user_wilayah->kelurahan_id)
                        ->where('rekomendasi_pelaporan_pubs.tujuan_ubar', '!=', $user_wilayah->role_id)
                        ->where('rekomendasi_pelaporan_pubs.petugas_ubar', '!=', auth::user()->id)
                        // ->where('rekomendasi_pelaporan_pubs.petugas_ubar','!=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_pelaporan_pubs.status_aksi_ubar', '=', 'Teruskan')
                                ->orWhere('rekomendasi_pelaporan_pubs.status_aksi_ubar', '=', 'kembalikan');
                        });
                })->distinct();
            // dd($query);

        }
        if ($user_wilayah->name == 'Front Office kota') {
            $query = DB::table('rekomendasi_pelaporan_pubs')
                ->join('users', 'users.id', '=', 'rekomendasi_pelaporan_pubs.petugas_ubar')
                // ->join('model_has_roles', 'model_has_roles.model_id', '=', 'rekomendasi_pelaporan_pubs.tujuan_ubar')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_pelaporan_pubs.id_kelurahan_ubar')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_pelaporan_pubs.id_kecamatan_ubar')
                ->select('rekomendasi_pelaporan_pubs.*', 'b.name_village', 'd.name_districts','users.name')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_pelaporan_pubs.id_kabkot_ubar', $user_wilayah->kota_id)
                        ->where('rekomendasi_pelaporan_pubs.tujuan_ubar', '!=', $user_wilayah->role_id)
                        ->where('rekomendasi_pelaporan_pubs.petugas_ubar', '!=', auth::user()->id)
                        // ->where('rekomendasi_pelaporan_pubs.petugas_ubar','!=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_pelaporan_pubs.status_aksi_ubar', '=', 'Teruskan')
                                ->orWhere('rekomendasi_pelaporan_pubs.status_aksi_ubar', '=', 'kembalikan');
                        });
                })->distinct();
            // dd($query);

        }

        //Back office kota 
        if ($user_wilayah->name == 'Back Ofiice Kota') {
            // dd($user_wilayah->model_id);
            $query = DB::table('rekomendasi_pelaporan_pubs')
                ->join('users', 'users.id', '=', 'rekomendasi_pelaporan_pubs.petugas_ubar')
                // ->join('model_has_roles', 'model_has_roles.model_id', '=', 'rekomendasi_pelaporan_pubs.tujuan_ubar')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_pelaporan_pubs.id_kelurahan_ubar')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_pelaporan_pubs.id_kecamatan_ubar')
                ->select('rekomendasi_pelaporan_pubs.*', 'b.name_village', 'd.name_districts','users.name')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_pelaporan_pubs.id_kabkot_ubar', $user_wilayah->kota_id)
                        ->where('rekomendasi_pelaporan_pubs.tujuan_ubar', '!=', $user_wilayah->role_id)
                        ->where('rekomendasi_pelaporan_pubs.petugas_ubar', '!=', auth::user()->id)
                        // ->where('rekomendasi_pelaporan_pubs.petugas_ubar','!=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_pelaporan_pubs.status_aksi_ubar', '=', 'Teruskan')
                                ->orWhere('rekomendasi_pelaporan_pubs.status_aksi_ubar', '=', 'kembalikan');
                        });
                })->distinct();
        }
        if ($user_wilayah->name == 'Back Ofiice kelurahan') {
            // dd($user_wilayah->model_id);
            $query = DB::table('rekomendasi_pelaporan_pubs')
                ->join('users', 'users.id', '=', 'rekomendasi_pelaporan_pubs.petugas_ubar')
                // ->join('model_has_roles', 'model_has_roles.model_id', '=', 'rekomendasi_pelaporan_pubs.tujuan_ubar')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_pelaporan_pubs.id_kelurahan_ubar')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_pelaporan_pubs.id_kecamatan_ubar')
                ->select('rekomendasi_pelaporan_pubs.*', 'b.name_village', 'd.name_districts','users.name')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_pelaporan_pubs.id_kelurahan_ubar', $user_wilayah->kelurahan_id)
                        ->where('rekomendasi_pelaporan_pubs.tujuan_ubar', '!=', $user_wilayah->role_id)
                        ->where('rekomendasi_pelaporan_pubs.petugas_ubar', '!=', auth::user()->id)
                        // ->where('rekomendasi_pelaporan_pubs.petugas_ubar','!=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_pelaporan_pubs.status_aksi_ubar', '=', 'Teruskan')
                                ->orWhere('rekomendasi_pelaporan_pubs.status_aksi_ubar', '=', 'kembalikan');
                        });
                })->distinct();
        }

        //Back office kota 
        if ($user_wilayah->name == 'supervisor') {
            // dd($user_wilayah->model_id);
            $query = DB::table('rekomendasi_pelaporan_pubs')
                ->join('users', 'users.id', '=', 'rekomendasi_pelaporan_pubs.petugas_ubar')
                ->join('log_pelaporanpub', 'log_pelaporanpub.id_trx_ubar', '=', 'rekomendasi_pelaporan_pubs.id')
                // ->join('model_has_roles', 'model_has_roles.model_id', '=', 'rekomendasi_pelaporan_pubs.tujuan_ubar')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_pelaporan_pubs.id_kelurahan_ubar')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_pelaporan_pubs.id_kecamatan_ubar')
                ->select('rekomendasi_pelaporan_pubs.*', 'b.name_village', 'd.name_districts','users.name')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_pelaporan_pubs.id_kabkot_ubar', $user_wilayah->kota_id)
                        ->where('log_pelaporanpub.tujuan_ubar', '!=', $user_wilayah->role_id)
                        ->where('rekomendasi_pelaporan_pubs.petugas_ubar', '!=', auth::user()->id)
                        // ->where('rekomendasi_pelaporan_pubs.petugas_ubar','!=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_pelaporan_pubs.status_aksi_ubar', '=', 'Teruskan')
                                ->orWhere('rekomendasi_pelaporan_pubs.status_aksi_ubar', '=', 'kembalikan');
                        });
                })->distinct();
        }

        if ($user_wilayah->name == 'kepala bidang') {
            // dd( $user_wilayah->role_id);
            $query = DB::table('rekomendasi_pelaporan_pubs')
                ->join('users', 'users.id', '=', 'rekomendasi_pelaporan_pubs.petugas_ubar')
                ->join('log_pelaporanpub', 'log_pelaporanpub.id_trx_ubar', '=', 'rekomendasi_pelaporan_pubs.id')
                // ->join('model_has_roles', 'model_has_roles.model_id', '=', 'rekomendasi_pelaporan_pubs.tujuan_ubar')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_pelaporan_pubs.id_kelurahan_ubar')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_pelaporan_pubs.id_kecamatan_ubar')
                ->select('rekomendasi_pelaporan_pubs.*', 'b.name_village', 'd.name_districts','users.name')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_pelaporan_pubs.id_kabkot_ubar', $user_wilayah->kota_id)
                        ->where('log_pelaporanpub.tujuan_ubar', '!=', $user_wilayah->role_id)
                        ->where('rekomendasi_pelaporan_pubs.petugas_ubar', '!=', auth::user()->id)
                        // ->where('rekomendasi_pelaporan_pubs.petugas_ubar','!=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_pelaporan_pubs.status_aksi_ubar', '=', 'Teruskan')
                                ->orWhere('rekomendasi_pelaporan_pubs.status_aksi_ubar', '=', 'kembalikan');
                        });
                })->distinct();
        }
        if ($user_wilayah->name == 'SekertarisDinas') {
            // dd($user_wilayah);
            $query = DB::table('rekomendasi_pelaporan_pubs')
                ->join('users', 'users.id', '=', 'rekomendasi_pelaporan_pubs.petugas_ubar')
                ->join('log_pelaporanpub', 'log_pelaporanpub.id_trx_ubar', '=', 'rekomendasi_pelaporan_pubs.id')
                // ->join('model_has_roles', 'model_has_roles.model_id', '=', 'rekomendasi_pelaporan_pubs.tujuan_ubar')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_pelaporan_pubs.id_kelurahan_ubar')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_pelaporan_pubs.id_kecamatan_ubar')
                ->select('rekomendasi_pelaporan_pubs.*', 'b.name_village', 'd.name_districts','users.name')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_pelaporan_pubs.id_kabkot_ubar', $user_wilayah->kota_id)
                        ->where('log_pelaporanpub.tujuan_ubar', '!=', $user_wilayah->role_id)
                        ->where('rekomendasi_pelaporan_pubs.petugas_ubar', '!=', auth::user()->id)
                        // ->where('rekomendasi_pelaporan_pubs.petugas_ubar','!=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_pelaporan_pubs.status_aksi_ubar', '=', 'Teruskan')
                                ->orWhere('rekomendasi_pelaporan_pubs.status_aksi_ubar', '=', 'kembalikan');
                        });
                })->distinct();
        }
        if ($user_wilayah->name == 'KepalaDinas') {
            //  dd(auth::user()->id);
            $query = DB::table('rekomendasi_pelaporan_pubs')
                ->join('users', 'users.id', '=', 'rekomendasi_pelaporan_pubs.petugas_ubar')
                ->join('log_pelaporanpub', 'log_pelaporanpub.id_trx_ubar', '=', 'rekomendasi_pelaporan_pubs.id')
                // ->join('model_has_roles', 'model_has_roles.model_id', '=', 'rekomendasi_pelaporan_pubs.tujuan_ubar')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_pelaporan_pubs.id_kelurahan_ubar')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_pelaporan_pubs.id_kecamatan_ubar')
                ->select('rekomendasi_pelaporan_pubs.*', 'b.name_village', 'd.name_districts','users.name')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_pelaporan_pubs.id_kabkot_ubar', $user_wilayah->kota_id)
                        ->where('log_pelaporanpub.tujuan_ubar', '!=', $user_wilayah->role_id)
                        ->where('rekomendasi_pelaporan_pubs.petugas_ubar', '!=', auth::user()->id)
                        // ->where('rekomendasi_pelaporan_pubs.petugas_ubar','!=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_pelaporan_pubs.status_aksi_ubar', '=', 'Teruskan')
                                ->orWhere('rekomendasi_pelaporan_pubs.status_aksi_ubar', '=', 'kembalikan');
                        });
                })->distinct();
        }
        if ($request->has('search') && !empty($request->search['value'])) {
            $search = $request->search['value'];
            $query = DB::table('rekomendasi_pelaporan_pubs')
                ->join('users', 'users.id', '=', 'rekomendasi_pelaporan_pubs.petugas_ubar')
                ->join('log_pelaporanpub', 'log_pelaporanpub.id_trx_ubar', '=', 'rekomendasi_pelaporan_pubs.id')
                // ->join('model_has_roles', 'model_has_roles.model_id', '=', 'rekomendasi_pelaporan_pubs.tujuan_ubar')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_pelaporan_pubs.id_kelurahan_ubar')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_pelaporan_pubs.id_kecamatan_ubar')
                ->select('rekomendasi_pelaporan_pubs.*', 'b.name_village', 'd.name_districts', 'log_pelaporanpub.tujuan_ubar', 'log_pelaporanpub.petugas_ubar')
                ->where(function ($query) use ($search) {
                    $query->where('rekomendasi_pelaporan_pubs.no_pendaftaran_ubar_bantuan_pendidikans', 'like', "%$search%");
                });
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

        $result = array();

        foreach ($data as $tmp) {
            $gabunganData = new stdClass;
            $gabunganData->userLogin=$userLogin;
            $gabunganData->dataRekomPub=$tmp;
            array_push($result, $gabunganData);
        }
        return response()->json([
            'draw' => $request->input('draw'),
            'recordsTotal' => rekomendasi_pelaporan_pub::count(),
            'recordsFiltered' => $total_filtered_items,
            'data' => $result,
        ]);
    }

    public function selesai(Request $request)
    {
        $user_name = Auth::user()->name;
        $query = DB::table('rekomendasi_pelaporan_pubs')
            ->join('users', 'users.id', 'rekomendasi_pelaporan_pubs.petugas_ubar')
            ->join('log_pelaporanpub', 'log_pelaporanpub.id_trx_ubar', '=', 'rekomendasi_pelaporan_pubs.id')
            ->join('roles', 'roles.id', '=', 'rekomendasi_pelaporan_pubs.tujuan_ubar')
            ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_pelaporan_pubs.id_kelurahan_ubar')
            ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_pelaporan_pubs.id_kecamatan_ubar')
            ->select('rekomendasi_pelaporan_pubs.*', 'roles.name', 'users.name', 'b.name_village', 'd.name_districts');
        // dd($query);
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
        // Add where conditions based on user's wilayah data
        if ($user_wilayah->name == 'fasilitator') {
            $query = DB::table('rekomendasi_pelaporan_pubs')
                ->join('users', 'users.id', 'rekomendasi_pelaporan_pubs.petugas_ubar')
                // ->join('log_pelaporanpub', 'log_pelaporanpub.id_trx_ubar', '=', 'rekomendasi_pelaporan_pubs.id')
                ->join('indonesia_villages', 'indonesia_villages.code', '=', 'rekomendasi_pelaporan_pubs.id_kelurahan_ubar')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_pelaporan_pubs.id_kecamatan_ubar')
                ->select('rekomendasi_pelaporan_pubs.*', 'users.name', 'd.name_districts', 'indonesia_villages.name_village')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_pelaporan_pubs.id_kelurahan_ubar', $user_wilayah->kelurahan_id)
                        // ->where('log_pelaporanpub.tujuan_ubar','=', $user_wilayah->role_id);
                        // ->where('log_pelaporanpub.created_by_ubar','!=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_pelaporan_pubs.status_aksi_ubar', '=', 'Tolak')
                                ->orWhere('rekomendasi_pelaporan_pubs.status_aksi_ubar', '=', 'Selesai');
                        });
                })->distinct();
        } elseif ($user_wilayah->name == 'Front Office Kelurahan') {
            //  dd($user_wilayah->role_id);
            $query = DB::table('rekomendasi_pelaporan_pubs')
                ->join('users', 'users.id', 'rekomendasi_pelaporan_pubs.petugas_ubar')
                // ->join('log_pelaporanpub', 'log_pelaporanpub.id_trx_ubar', '=', 'rekomendasi_pelaporan_pubs.id')
                ->join('indonesia_villages', 'indonesia_villages.code', '=', 'rekomendasi_pelaporan_pubs.id_kelurahan_ubar')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_pelaporan_pubs.id_kecamatan_ubar')
                ->select('rekomendasi_pelaporan_pubs.*', 'users.name', 'd.name_districts', 'indonesia_villages.name_village')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_pelaporan_pubs.id_kelurahan_ubar', $user_wilayah->kelurahan_id)
                        // ->where('log_pelaporanpub.tujuan_ubar','=', $user_wilayah->role_id);
                        // ->where('log_pelaporanpub.created_by_ubar','!=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_pelaporan_pubs.status_aksi_ubar', '=', 'Tolak')
                                ->orWhere('rekomendasi_pelaporan_pubs.status_aksi_ubar', '=', 'Selesai');
                        });
                })->distinct();
        } elseif ($user_wilayah->name == 'Front Office kota') {
            //  dd($user_wilayah->role_id);
            $query = DB::table('rekomendasi_pelaporan_pubs')
                ->join('users', 'users.id', 'rekomendasi_pelaporan_pubs.petugas_ubar')
                // ->join('log_pelaporanpub', 'log_pelaporanpub.id_trx_ubar', '=', 'rekomendasi_pelaporan_pubs.id')
                ->join('indonesia_villages', 'indonesia_villages.code', '=', 'rekomendasi_pelaporan_pubs.id_kelurahan_ubar')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_pelaporan_pubs.id_kecamatan_ubar')
                ->select('rekomendasi_pelaporan_pubs.*', 'users.name', 'd.name_districts', 'indonesia_villages.name_village')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_pelaporan_pubs.id_kabkot_ubar', $user_wilayah->kota_id)
                        // ->where('log_pelaporanpub.tujuan_ubar','=', $user_wilayah->role_id)
                        // ->where('log_pelaporanpub.created_by_ubar','!=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_pelaporan_pubs.status_aksi_ubar', '=', 'Tolak')
                                ->orWhere('rekomendasi_pelaporan_pubs.status_aksi_ubar', '=', 'Selesai');
                        });
                })->distinct();
        } elseif ($user_wilayah->name == 'Back Ofiice kelurahan') {
            // dd($user_wilayah);
            $query = DB::table('rekomendasi_pelaporan_pubs')
                ->join('users', 'users.id', 'rekomendasi_pelaporan_pubs.petugas_ubar')
                // ->join('log_pelaporanpub', 'log_pelaporanpub.id_trx_ubar', '=', 'rekomendasi_pelaporan_pubs.id')
                ->join('indonesia_villages', 'indonesia_villages.code', '=', 'rekomendasi_pelaporan_pubs.id_kelurahan_ubar')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_pelaporan_pubs.id_kecamatan_ubar')
                ->select('rekomendasi_pelaporan_pubs.*', 'users.name', 'd.name_districts', 'indonesia_villages.name_village')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_pelaporan_pubs.id_kabkot_ubar', $user_wilayah->kota_id)
                        // ->where('log_pelaporanpub.tujuan_ubar','=', $user_wilayah->role_id)
                        // ->where('log_pelaporanpub.created_by_ubar','!=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_pelaporan_pubs.status_aksi_ubar', '=', 'Tolak')
                                ->orWhere('rekomendasi_pelaporan_pubs.status_aksi_ubar', '=', 'Selesai');
                        });
                })->distinct();
            // dd($query); 
        } elseif ($user_wilayah->name == 'kepala bidang') {
            // dd($user_wilayah);
            $query = DB::table('rekomendasi_pelaporan_pubs')
                ->join('users', 'users.id', 'rekomendasi_pelaporan_pubs.petugas_ubar')
                // ->join('log_pelaporanpub', 'log_pelaporanpub.id_trx_ubar', '=', 'rekomendasi_pelaporan_pubs.id')
                ->join('indonesia_villages', 'indonesia_villages.code', '=', 'rekomendasi_pelaporan_pubs.id_kelurahan_ubar')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_pelaporan_pubs.id_kecamatan_ubar')
                ->select('rekomendasi_pelaporan_pubs.*', 'users.name', 'd.name_districts', 'indonesia_villages.name_village')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_pelaporan_pubs.id_kabkot_ubar', $user_wilayah->kota_id)
                        // ->where('log_pelaporanpub.tujuan_ubar','=', $user_wilayah->role_id)
                        // ->where('log_pelaporanpub.created_by_ubar','!=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_pelaporan_pubs.status_aksi_ubar', '=', 'Tolak')
                                ->orWhere('rekomendasi_pelaporan_pubs.status_aksi_ubar', '=', 'Selesai');
                        });
                })->distinct();
        } elseif ($user_wilayah->name == 'Back Ofiice Kota') {
            // dd($user_wilayah->role_id);
            $query = DB::table('rekomendasi_pelaporan_pubs')
                ->join('users', 'users.id', 'rekomendasi_pelaporan_pubs.petugas_ubar')
                // ->join('log_pelaporanpub', 'log_pelaporanpub.id_trx_ubar', '=', 'rekomendasi_pelaporan_pubs.id')
                ->join('indonesia_villages', 'indonesia_villages.code', '=', 'rekomendasi_pelaporan_pubs.id_kelurahan_ubar')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_pelaporan_pubs.id_kecamatan_ubar')
                ->select('rekomendasi_pelaporan_pubs.*', 'users.name', 'd.name_districts', 'indonesia_villages.name_village')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_pelaporan_pubs.id_kabkot_ubar', $user_wilayah->kota_id)
                        // ->where('log_pelaporanpub.tujuan_ubar','=', $user_wilayah->role_id)
                        // ->where('log_pelaporanpub.created_by_ubar','!=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_pelaporan_pubs.status_aksi_ubar', '=', 'Tolak')
                                ->orWhere('rekomendasi_pelaporan_pubs.status_aksi_ubar', '=', 'Selesai');
                        });
                })->distinct();
        } elseif ($user_wilayah->name == 'KepalaDinas') {
            // dd($user_wilayah);
            $query = DB::table('rekomendasi_pelaporan_pubs')
                ->join('users', 'users.id', 'rekomendasi_pelaporan_pubs.petugas_ubar')
                // ->join('log_pelaporanpub', 'log_pelaporanpub.id_trx_ubar', '=', 'rekomendasi_pelaporan_pubs.id')
                ->join('indonesia_villages', 'indonesia_villages.code', '=', 'rekomendasi_pelaporan_pubs.id_kelurahan_ubar')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_pelaporan_pubs.id_kecamatan_ubar')
                ->select('rekomendasi_pelaporan_pubs.*', 'users.name', 'd.name_districts', 'indonesia_villages.name_village')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_pelaporan_pubs.id_kabkot_ubar', $user_wilayah->kota_id)
                        // ->where('log_pelaporanpub.tujuan_ubar','=', $user_wilayah->role_id)
                        // ->where('log_pelaporanpub.created_by_ubar','!=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_pelaporan_pubs.status_aksi_ubar', '=', 'Tolak')
                                ->orWhere('rekomendasi_pelaporan_pubs.status_aksi_ubar', '=', 'Selesai');
                        });
                })->distinct();
        } elseif ($user_wilayah->name == 'SekertarisDinas') {
            // dd($user_wilayah);
            $query = DB::table('rekomendasi_pelaporan_pubs')
                ->join('users', 'users.id', 'rekomendasi_pelaporan_pubs.petugas_ubar')
                // ->join('log_pelaporanpub', 'log_pelaporanpub.id_trx_ubar', '=', 'rekomendasi_pelaporan_pubs.id')
                ->join('indonesia_villages', 'indonesia_villages.code', '=', 'rekomendasi_pelaporan_pubs.id_kelurahan_ubar')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_pelaporan_pubs.id_kecamatan_ubar')
                ->select('rekomendasi_pelaporan_pubs.*', 'users.name', 'd.name_districts', 'indonesia_villages.name_village')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_pelaporan_pubs.id_kabkot_ubar', $user_wilayah->kota_id)
                        // ->where('log_pelaporanpub.tujuan_ubar','=', $user_wilayah->role_id)
                        // ->where('log_pelaporanpub.created_by_ubar','!=', $user_wilayah->model_id)
                       
                            ->where('rekomendasi_pelaporan_pubs.status_aksi_ubar', '=', 'Tolak')
                            ->orWhere('rekomendasi_pelaporan_pubs.status_aksi_ubar', '=', 'Selesai');
                       
                })->distinct();
        }
        if ($request->has('search') && !empty($request->search['value'])) {
            $search = $request->search['value'];
            $query = DB::table('rekomendasi_pelaporan_pubs')
                ->join('users', 'users.id', 'rekomendasi_pelaporan_pubs.petugas_ubar')
                ->join('log_pelaporanpub', 'log_pelaporanpub.id_trx_ubar', '=', 'rekomendasi_pelaporan_pubs.id')
                ->join('roles', 'roles.id', '=', 'rekomendasi_pelaporan_pubs.tujuan_ubar')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_pelaporan_pubs.id_kelurahan_ubar')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_pelaporan_pubs.id_kecamatan_ubar')
                ->select('rekomendasi_pelaporan_pubs.*', 'roles.name', 'users.name', 'b.name_village', 'd.name_districts')
                ->where(function ($query) use ($search) {
                    $query->where('rekomendasi_pelaporan_pubs.no_pendaftaran_ubar_bantuan_pendidikans', 'like', "%$search%");
                });
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

        $result = array();

        foreach ($data as $tmp) {
            $gabunganData = new stdClass;
            $gabunganData->userLogin=$userLogin;
            $gabunganData->dataRekomPub=$tmp;
            array_push($result, $gabunganData);
        }
        return response()->json([
            'draw' => $request->input('draw'),
            'recordsTotal' => rekomendasi_pelaporan_pub::count(),
            'recordsFiltered' => $total_filtered_items,
            'data' => $result,
        ]);
    }
    // public function prelistPage(Request $request)
    // {
    //     return view('pubs.index');
    // }
    // public function prelistDTKS(Request $request)
    // {
    //     $columns = [
    //         // daftar kolom yang akan ditampilkan pada tabel
    //         'id_provinsi',
    //         'id_kabkot_ubar',
    //         'id_kecamatan_ubar',
    //         'id_kelurahan_ubar',
    //         'nik',
    //         'no_kk',
    //         'no_kis',
    //         'nama',
    //         'tgl_lahir',
    //         'alamat',
    //         'telp',
    //         'status_data',
    //         'email'
    //     ];

    //     // $query = Prelist::where('status_data', 'prelistdtks');
    //     $query = DB::table('prelist')
    //         ->join('indonesia_districts as a', 'a.code', '=', 'prelist.id_kecamatan_ubar')
    //         ->join('indonesia_villages as b', 'b.code', '=', 'prelist.id_kelurahan_ubar')
    //         ->select('prelist.*', 'a.name_districts', 'b.name_village');
    //     // dd($query);
    //     // menambahkan kondisi pencarian jika ada
    //     if ($request->has('search')) {
    //         $searchValue = $request->search['value'];
    //         $query->where(function ($query) use ($columns, $searchValue) {
    //             foreach ($columns as $column) {
    //                 $query->orWhere($column, 'like', '%' . $searchValue . '%');
    //             }
    //         });
    //     }
    //     // menambahkan kondisi sortir jika ada
    //     if ($request->has('order')) {
    //         $orderColumn = $columns[$request->order[0]['column']];
    //         $orderDirection = $request->order[0]['dir'];
    //         $query->orderBy($orderColumn, $orderDirection);
    //     }

    //     // mengambil data sesuai dengan paginasi yang diminta
    //     $perPage = $request->length ?: config('app.pagination.per_page');
    //     $currentPage = $request->start ? ($request->start / $perPage) + 1 : 1;
    //     $data = $query->paginate($perPage, ['*'], 'page', $currentPage);

    //     // memformat data untuk dikirim ke client
    //     $no = 1;
    //     $formattedData = [];
    //     foreach ($data as $item) {
    //         $formattedData[] = [
    //             'no' => $no++,
    //             'id_provinsi' => $item->id_provinsi,
    //             'id_kabkot_ubar' => $item->id_kabkot_ubar,
    //             'id_kecamatan_ubar' => $item->name_village,
    //             'id_kelurahan_ubar' => $item->name_districts,
    //             'nik' => $item->nik,
    //             'no_kk' => $item->no_kk,
    //             'no_kis' => $item->no_kis,
    //             'nama' => $item->nama,
    //             'tgl_lahir' => $item->tgl_lahir,
    //             'alamat' => $item->alamat,
    //             'telp' => $item->telp,
    //             'email' => $item->email,
    //         ];
    //     }
    //     // mengembalikan data dalam format JSON
    //     return response()->json([
    //         'draw' => $request->draw,
    //         'recordsTotal' => Pengaduan::count(),
    //         'recordsFiltered' => $data->total(),
    //         'data' => $formattedData
    //     ]);
    // }
    // public function detail_pengaduan($id)
    // {
    //     $data2 = DB::table('pengaduans as w')->select(
    //         'w.*',
    //         'b.name_village',
    //         'prov.name_prov',
    //         'kota.name_cities',
    //         'kecamatan.name_districts',
    //         // 'w.status_wilayah',
    //     )
    //         ->leftjoin('indonesia_provinces as prov', 'prov.code', '=', 'w.id_provinsi')
    //         ->leftjoin('indonesia_cities as kota', 'kota.code', '=', 'w.id_kabkot_ubar')
    //         ->leftjoin('indonesia_districts as kecamatan', 'kecamatan.code', '=', 'w.id_kecamatan_ubar')
    //         ->leftjoin('indonesia_villages as b', 'b.code', '=', 'w.id_kelurahan_ubar')
    //         ->where('w.id', $id)->first();
    //     $data = [
    //         'data' => $data2
    //         // 'data' => $data2
    //     ];
    //     return response()->json($data);
    // }
    // public function detail_log_pelaporanPub(Request $request, $id)
    // {
    //     // dd($request);
    //     $user_name = Auth::user()->name;
    //     $query = DB::table('rekomendasi_bantuan_pendidikans')
    //         // ->join('users', 'users.id', '=', 'rekomendasi_bantuan_pendidikans.createdby')
    //         ->join('log_pelaporanpub', 'log_pelaporanpub.id_trx_ubar', '=', 'rekomendasi_bantuan_pendidikans.id')
    //         ->select('log_pelaporanpub.*')->get();

    //     // dd($query);
    //     // Add where conditions based on user's wilayah data


    //     if ($request->has('search') && !empty($request->search['value'])) {
    //         $search = $request->search['value'];
    //         $query = DB::table('rekomendasi_bantuan_pendidikans')
    //             // ->join('users', 'users.id', '=', 'rekomendasi_bantuan_pendidikans.createdby')
    //             ->join('log_pelaporanPub', 'log_pelaporanPub.id_trx_ubar', '=', 'rekomendasi_bantuan_pendidikans.id')
    //             ->select('rekomendasi_bantuan_pendidikans.*')
    //             ->where(function ($query) use ($search) {
    //                 $query->where('rekomendasi_bantuan_pendidikans.no_pendaftaran', 'like', "%$search%");
    //             });
    //     }

    //     // Get total count of filtered items
    //     $total_filtered_items = $query->count();
    //     // dd($total_filtered_items);
    //     // Add ordering
    //     if ($request->has('order')) {
    //         $order_column = $request->order[0]['column'];
    //         $order_direction = $request->order[0]['dir'];
    //         $query->orderBy($request->input('columns.' . $order_column . '.data'), $order_direction);
    //     }
    //     // Get paginated data
    //     // dd($query->paginate());
    //     // $data = $query->paginate($request->input('length'));
    //     // dd($data);
    //     // mengubah data JSON menjadi objek PHP
    //     $data = DB::table('log_pelaporanPub')
    //         ->join('users as a', 'a.id', '=', 'log_pelaporanPub.created_by_ubar')
    //         // ->join('users as b', 'b.id', '=', 'rekomendasi_bantuan_pendidikans.createdby')
    //         ->join('rekomendasi_bantuan_pendidikans', 'rekomendasi_bantuan_pendidikans.id', '=', 'log_pelaporanPub.id_trx_ubar')
    //         ->select('a.name', 'rekomendasi_bantuan_pendidikans.status_alur_bantuan_pendidikans', 'rekomendasi_bantuan_pendidikans.id', 'rekomendasi_bantuan_pendidikans.file_pendukung_bantuan_pendidikans', 'rekomendasi_bantuan_pendidikans.catatan_bantuan_pendidikans', 'rekomendasi_bantuan_pendidikans.created_at')
    //         // ->select('a.name')
    //         ->where('log_pelaporanPub.id_trx_ubar', $id)->get();
    //     // dd($data);
    //     return response()->json([
    //         'draw' => $request->input('draw'),
    //         'recordsTotal' => logpendidikan::count(),
    //         'recordsFiltered' => $total_filtered_items,
    //         'data' => $data,
    //     ]);
    // }
}
