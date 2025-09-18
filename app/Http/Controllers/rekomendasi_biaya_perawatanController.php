<?php

namespace App\Http\Controllers;

use App\Http\Requests\Createrekomendasi_biaya_perawatanRequest;
use App\Http\Requests\Updaterekomendasi_biaya_perawatanRequest;
use App\Http\Controllers\AppBaseController;
use App\Models\log_biper;
use App\Models\pelapor;
use App\Models\Prelist;
use App\Models\rekomendasi_biaya_perawatan;
use App\Models\Roles;
use App\Repositories\rekomendasi_biaya_perawatanRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Flash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class rekomendasi_biaya_perawatanController extends AppBaseController
{
	/** @var rekomendasi_biaya_perawatanRepository $rekomendasiBiayaPerawatanRepository*/
	private $rekomendasiBiayaPerawatanRepository;

	public function __construct(rekomendasi_biaya_perawatanRepository $rekomendasiBiayaPerawatanRepo)
	{
		$this->rekomendasiBiayaPerawatanRepository = $rekomendasiBiayaPerawatanRepo;
	}

	/**
	 * Display a listing of the rekomendasi_biaya_perawatan.
	 */
	public function index(Request $request)
	{
		$rekomendasiBiayaPerawatans = $this->rekomendasiBiayaPerawatanRepository->paginate(10);

		return view('rekomendasi_biaya_perawatans.index')
			->with('rekomendasiBiayaPerawatans', $rekomendasiBiayaPerawatans);
	}

	/**
	 * Show the form for creating a new rekomendasi_biaya_perawatan.
	 */
	public function create()
	{
		$v = rekomendasi_biaya_perawatan::latest()->first();
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
				->whereIn('name', ['Draft', 'Kembalikan', 'Tolak', 'Teruskan'])
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
		return view('rekomendasi_biaya_perawatans.create', compact('kecamatans', 'wilayah', 'roleid', 'checkuserrole', 'alur'));
		// return view('rekomendasi_biper.create');
	}
	public function cekIDBiper(Request $request, $Nik)
	{
		$found = false;
		$table2 = DB::table('dtks')->where('nik_biper', $Nik)->first();
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
	 * Store a newly created rekomendasi_biaya_perawatan in storage.
	 */
	public function store(Request $request)
	{
		
		if ($request->get('status_aksi_biper') != 'Draft') {
			// jika status_aksi_biper sama dengan Draft akan nmasuk kondisi sini
			$data = new rekomendasi_biaya_perawatan();
			
			// dd($data);
			if ($request->file('file_perawatan_biper')) {
				$path = $request->file('file_perawatan_biper');
				$filename = 'biaya_perawatan/perawatan/' . $path->getClientOriginalName();
				$return = Storage::disk('imagekit')->put($filename, fopen($path->getRealPath(), 'r'));
				$data->file_perawatan_biper = Storage::disk('imagekit')->url($filename);
			} else {
				$data->file_perawatan_biper = null;
			}

			if ($request->file('file_lap_sosial_biper')) {
				$path = $request->file('file_lap_sosial_biper');
				$filename = 'biaya_perawatan/lapsos/' . $path->getClientOriginalName();
				$return = Storage::disk('imagekit')->put($filename, fopen($path->getRealPath(), 'r'));
				$data->file_lap_sosial_biper = Storage::disk('imagekit')->url($filename);
			} else {
				$data->file_lap_sosial_biper = null;
			}

			if ($request->file('file_kebutuhan_layanan_biper')) {
				$path = $request->file('file_kebutuhan_layanan_biper');
				$filename = 'biaya_perawatan/layanan/' . $path->getClientOriginalName();
				$return = Storage::disk('imagekit')->put($filename, fopen($path->getRealPath(), 'r'));
				$data->file_kebutuhan_layanan_biper = Storage::disk('imagekit')->url($filename);
			} else {
				$data->file_kebutuhan_layanan_biper = null;
			}

			// print_r($request->get('id_kabkot_biper'));die;
 
			$data->status_dtks_biper = $request->get('status_dtks_biper');
			$data->ada_nik_biper = $request->get('ada_nik_biper');
			$data->no_pendaftaran_biper = mt_rand(100, 1000);
			$data->id_provinsi_biper = $request->get('id_provinsi_biper');
			$data->id_kabkot_biper = $request->get('id_kabkot_biper');
			$data->id_kecamatan_biper = $request->get('id_kecamatan_biper');
			$data->id_kelurahan_biper = $request->get('id_kelurahan_biper');
			$data->jenis_pelapor_biper = $request->get('jenis_pelapor_biper');
			$data->nik_biper = $request->get('nik_biper');
			$data->nama_biper = $request->get('nama_biper');
			$data->tgl_lahir_biper = $request->get('tgl_lahir_biper');
			$data->tempat_lahir_biper = $request->get('tempat_lahir_biper');
			$data->jenis_kelamin_biper = $request->get('jenis_kelamin_biper');
			$data->telp_biper = $request->get('telp_biper');
			$data->alamat_biper = $request->get('alamat_biper');

			$data->no_rm_biper = $request->get('no_rm_biper');
			$data->tgl_masuk_rs_biper = $request->get('tgl_masuk_rs_biper');
			$data->keterangan_biper = $request->get('keterangan_biper');

			$data->tujuan_biper = $request->get('tujuan_biper');
			$data->status_aksi_biper = $request->get('status_aksi_biper');
			$data->petugas_biper = $request->get('petugas_biper');
			$data->createdby_biper = Auth::user()->id;
			// dd($data); die;
			$data->validasi_surat = $request->get('validasi_surat');
			$data->Nomor_Surat = $request->get('Nomor_Surat');
			$data->nama_rumah_sakit = $request->get('nama_rumah_sakit');
			$data->yth_biper = $request->get('yth_biper');
			$data->kab_kota_rumah_sakit = $request->get('kab_kota_rumah_sakit');
			// dd($data);
			$data->save();
			$logbiper = new log_biper();
			$logbiper['id_trx_biper'] = $data->id;
			$logbiper['id_alur_biper'] = $request->get('status_aksi_biper');
			$logbiper['tujuan_biper'] = $data->tujuan_biper;
			$logbiper['petugas_biper'] = $request->get('petugas_biper');
			$logbiper['catatan_biper']  = $request->get('keterangan_biper');
			$logbiper['created_by_biper'] = Auth::user()->id;
			$logbiper['updated_by_biper'] = Auth::user()->id;

			$logbiper->save();
			if ($request->get('jenis_pelapor_biper')== "Orang Lain") {
				$pelapor = new pelapor();
                    $pelapor['id_menu'] = '05';
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
				$pelapor['id_menu'] = '05';
				$pelapor['id_form'] = $data->id;
				$pelapor['jenis_peelaporan'] = $request->get('jenis_pelapor_biper');
				$pelapor['nama_pelapor']  =  $request->get('nama_biper');
				$pelapor['ada_nik_pelapor'] = $request->get('ada_nik_biper');
				$pelapor['nik_pelapor'] = $request->get('nik_biper');
				$pelapor['status_dtks_pelapor'] = $request->get('status_dtks_biper');
				$pelapor['tempat_lahir_pelapor'] = $request->get('tempat_lahir_biper');
				$pelapor['tanggal_lahir_pelapor'] = $request->get('tgl_lahir_biper');
				$pelapor['jenis_kelamin'] = $request->get('jenis_kelamin_biper');
				// $pelapor['nama_pelapor']  = $request->get('nama_biper');
				$pelapor['telepon_pelapor'] = $request->get('telp_biper');
				$pelapor['alamat_pelapor'] = $request->get('alamat_biper');
				$pelapor['createdby_pelapor'] = Auth::user()->id;
				$pelapor['updatedby_pelapor'] = Auth::user()->id;

				$pelapor->save();
			} 
			return redirect('rekomendasi_biaya_perawatans')->withSuccess('Data Rekomendasi Berhasil Ditambahkan');
		} else {
			//jika status draft adalah ini akan masuk ke sini
			$data = new rekomendasi_biaya_perawatan();
			if ($request->file('file_perawatan_biper')) {
				$path = $request->file('file_perawatan_biper');
				$filename = 'biaya_perawatan/perawatan/' . $path->getClientOriginalName();
				$return = Storage::disk('imagekit')->put($filename, fopen($path->getRealPath(), 'r'));
				$data->file_perawatan_biper = Storage::disk('imagekit')->url($filename);
			} else {
				$data->file_perawatan_biper = null;
			}

			if ($request->file('file_lap_sosial_biper')) {
				$path = $request->file('file_lap_sosial_biper');
				$filename = 'biaya_perawatan/lapsos/' . $path->getClientOriginalName();
				$return = Storage::disk('imagekit')->put($filename, fopen($path->getRealPath(), 'r'));
				$data->file_lap_sosial_biper = Storage::disk('imagekit')->url($filename);
			} else {
				$data->file_lap_sosial_biper = null;
			}

			if ($request->file('file_kebutuhan_layanan_biper')) {
				$path = $request->file('file_kebutuhan_layanan_biper');
				$filename = 'biaya_perawatan/layanan/' . $path->getClientOriginalName();
				$return = Storage::disk('imagekit')->put($filename, fopen($path->getRealPath(), 'r'));
				$data->file_kebutuhan_layanan_biper = Storage::disk('imagekit')->url($filename);
			} else {
				$data->file_kebutuhan_layanan_biper = null;
			}

			$data->no_pendaftaran_biper = mt_rand(100, 1000);
			$data->id_provinsi_biper = $request->get('id_provinsi_biper');
			$data->id_kabkot_biper = $request->get('id_kabkot_biper');
			$data->id_kecamatan_biper = $request->get('id_kecamatan_biper');
			$data->id_kelurahan_biper = $request->get('id_kelurahan_biper');
			$data->jenis_pelapor_biper = $request->get('jenis_pelapor_biper');
			$data->nik_biper = $request->get('nik_biper');
			$data->nama_biper = $request->get('nama_biper');
			$data->tgl_lahir_biper = $request->get('tgl_lahir_biper');
			$data->tempat_lahir_biper = $request->get('tempat_lahir_biper');
			$data->jenis_kelamin_biper = $request->get('jenis_kelamin_biper');
			$data->telp_biper = $request->get('telp_biper');
			$data->alamat_biper = $request->get('alamat_biper');
			$data->ada_nik_biper = $request->get('ada_nik_biper');
			$data->no_rm_biper = $request->get('no_rm_biper');
			$data->tgl_masuk_rs_biper = $request->get('tgl_masuk_rs_biper');
			$data->keterangan_biper = $request->get('keterangan_biper');
			$data->status_dtks_biper = $request->get('status_dtks_biper');
			$data->tujuan_biper = $request->get('tujuan_biper');
			$data->status_aksi_biper = $request->get('status_aksi_biper');
			$data->petugas_biper = $request->get('petugas_biper');
			$data->createdby_biper = Auth::user()->id;
			// $data->updatedby_biper = Auth::user()->id;
			// dd($data); die;
			$data->validasi_surat = $request->get('validasi_surat');
			$data->Nomor_Surat = $request->get('Nomor_Surat');
			$data->nama_rumah_sakit = $request->get('nama_rumah_sakit');
			$data->yth_biper = $request->get('yth_biper');
			$data->kab_kota_rumah_sakit = $request->get('kab_kota_rumah_sakit');
			$data->save();
			if($request->get('jenis_pelapor_biper' == 'Orang Lain')){
				$pelapor = new pelapor();
				$pelapor['id_menu'] = '05';
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
				$pelapor['id_menu'] = '05';
				$pelapor['id_form'] = $data->id;
				$pelapor['jenis_peelaporan'] = $request->get('jenis_pelapor_biper');
				$pelapor['nama_pelapor']  =  $request->get('nama_biper');
				$pelapor['ada_nik_pelapor'] = $request->get('ada_nik_biper');
				$pelapor['nik_pelapor'] = $request->get('nik_biper');
				$pelapor['status_dtks_pelapor'] = $request->get('status_dtks_biper');
				$pelapor['tempat_lahir_pelapor'] = $request->get('tempat_lahir_biper');
				$pelapor['tanggal_lahir_pelapor'] = $request->get('tgl_lahir_biper');
				$pelapor['jenis_kelamin'] = $request->get('jenis_kelamin_biper');
				// $pelapor['nama_pelapor']  = $request->get('nama_biper');
				$pelapor['telepon_pelapor'] = $request->get('telp_biper');
				$pelapor['alamat_pelapor'] = $request->get('alamat_biper');
				$pelapor['createdby_pelapor'] = Auth::user()->id;
				$pelapor['updatedby_pelapor'] = Auth::user()->id;

				$pelapor->save();
			} 
			return redirect('rekomendasi_biaya_perawatans')->withWarning('Data disi,pan sebagai draft');
		}
	}

	public function show($id)
	{
		$userid = Auth::user()->id;
		// $rekomendasiBiayaPerawatan = $this->rekomendasiBiayaPerawatanRepository->find((int) $id);
		$rekomendasiBiayaPerawatanPelapor = DB::table('rekomendasi_biaya_perawatans')
            ->join('pelapor', function ($join) use ($id) {
                $join->on('rekomendasi_biaya_perawatans.id', '=', 'pelapor.id_form')
                    ->where('rekomendasi_biaya_perawatans.id', '=', $id);
            })
            ->select('rekomendasi_biaya_perawatans.*', 'pelapor.*')
            ->where('pelapor.id_menu', '05')
            ->where('pelapor.id_form', $id)
            ->first();
        // dd($rekomendasiBiayaPerawatanPelapor);

        $rekomendasiBiayaPerawatan = DB::table('rekomendasi_biaya_perawatans as w')->select(
            'w.*',
            'rls.name as name_roles',
            'usr.name',
            'prov.name_prov',
            'kota.name_cities',
            'kecamatan.name_districts',
            'b.name_village',
        )
            ->leftjoin('roles as rls', 'rls.id', '=', 'w.tujuan_biper')
            ->leftjoin('users as usr', 'usr.id', '=', 'w.petugas_biper')
            ->leftjoin('indonesia_provinces as prov', 'prov.code', '=', 'w.id_provinsi_biper')
            ->leftjoin('indonesia_cities as kota', 'kota.code', '=', 'w.id_kabkot_biper')
            ->leftjoin('indonesia_districts as kecamatan', 'kecamatan.code', '=', 'w.id_kecamatan_biper')
            ->leftjoin('indonesia_villages as b', 'b.code', '=', 'w.id_kelurahan_biper')
            ->where('w.id', $id)->first();

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

		if (empty($rekomendasiBiayaPerawatan)) {
			Flash::error('Rekomendasi not found');

			return redirect(route('rekomendasi_biaya_perawatans.index'));
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

		// $log_biper = log_biper::where('id_trx_biper', $id)->get();
        // $log_biper = DB::table('log_biper as w')->select(
        //     'w.*',
        //     'rls.name as name_roles',
        //     'usr.name',
        // )
        //     ->leftjoin('roles as rls', 'rls.id', '=', 'w.tujuan_biper')
        //     ->leftjoin('users as usr', 'usr.id', '=', 'w.petugas_biper')
        //     ->where('w.id_trx_biper', $id)->get();
		$log_biper = DB::table('log_biper as w')->select(
            'w.*',
            'rls.name as name_update',
            'usr.name',
            'roles.name as name_roles',

        )
            ->leftjoin('roles', 'roles.id', '=', 'w.tujuan_biper')
            ->leftjoin('users as rls', 'rls.id', '=', 'w.updated_by_biper')
            ->leftjoin('users as usr', 'usr.id', '=', 'w.updated_by_biper')
            ->where('w.id_trx_biper', $id)->get();
		return view('rekomendasi_biaya_perawatans.show', compact('rekomendasiBiayaPerawatanPelapor','rekomendasiBiayaPerawatan', 'roleid', 'wilayah', 'checkroles', 'log_biper'));
	}
	// public function show($id)
    // {
    //     // dd($rekomendasi_bantuan_pendidikans);
    //     $rekomendasiBiayaPerawatan = DB::table('rekomendasi_biaya_perawatans as w')->select(
    //         'w.*',
    //         'b.name_village',
    //         'prov.name_prov',
    //         'kota.name_cities',
    //         'kecamatan.name_districts',
    //         'roles.name as name_roles',
    //         'users.name'
    //         // 'w.status_wilayah',
    //     )
    //     ->leftjoin('users', 'users.id', '=', 'w.petugas_biper')
    //     ->leftjoin('roles', 'roles.id', '=', 'w.tujuan_biper')
    //     ->leftJoin('pelapor','pelapor.id_form','=','w.id')
    //     ->leftjoin('indonesia_provinces as prov', 'prov.code', '=', 'w.id_provinsi_biper')
    //     ->leftjoin('indonesia_cities as kota', 'kota.code', '=', 'w.id_kabkot_biper')
    //     ->leftjoin('indonesia_districts as kecamatan', 'kecamatan.code', '=', 'w.id_kecamatan_biper')
    //     ->leftjoin('indonesia_villages as b', 'b.code', '=', 'w.id_kelurahan_biper')
    //     ->where('pelapor.id_menu', '07')
    //     ->where('w.id', $id)->first();
    //     // dd($DetailRekomendasiBantuanPendidikan);
    //     $DetailLogBiayaPerawatan = DB::table('log_biper as w')->select(
    //         'w.*',
    //         'rls.name as name_update',
    //         'usr.name',
    //         'roles.name as name_roles',

    //     )
    //         ->leftjoin('roles', 'roles.id', '=', 'w.tujuan_biper')
    //         ->leftjoin('users as rls', 'rls.id', '=', 'w.updated_by_biper')
    //         ->leftjoin('users as usr', 'usr.id', '=', 'w.updated_by_biper')
    //         ->where('w.id_trx_biper', $id)->get();
    //     // dd($DetailLogBantuanPendidikan);
       


    // 	return view('rekomendasi_biaya_perawatans.show', compact('rekomendasiBiayaPerawatan','DetailLogBiayaPerawatan',''));
    // }
	public function FileBiayaPerawatan($id)
    {
        $adminduk = rekomendasi_biaya_perawatan::find($id);
        // dd($rehabsos);
        $getIdDtks = DB::table('rekomendasi_biaya_perawatans as w')->select(
            'w.*',
            'dtks.Id_DTKS'
        )
            ->leftjoin('dtks', 'dtks.Nik', '=', 'w.nik_biper')
            // ->where('status_wilayah', '1')
            ->where('dtks.Nik', $adminduk->nik_biper)->first();
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
        $pdf = PDF::loadHtml(view('rekomendasi_biaya_perawatans.file_permohonan',compact('adminduk','tanggal','data_dtks')));
        $pdf->setPaper('F4', 'portrait');
        $filename = 'File Permohonan' . $adminduk->nama . '.pdf';
        return $pdf->stream($filename);
    }

	/**
	 * Show the form for editing the specified rekomendasi_biaya_perawatan.
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
			->leftjoin('rekomendasi_biaya_perawatans', 'rekomendasi_biaya_perawatans.createdby_biper', '=', 'model_has_roles.model_id')
			->where('rekomendasi_biaya_perawatans.id', '=', $id)
			// ->where('status_aksi', '=', 'Draft')
			// ->orwhere('status_aksi', '=', 'Teruskan')
			->get();
		// dd($checkroles2);
		//Tujuan
		$createdby = DB::table('rekomendasi_biaya_perawatans')
			->join('users', 'rekomendasi_biaya_perawatans.createdby_biper', '=', 'users.name')
			->join('model_has_roles', 'model_has_roles.model_id', '=', 'users.id')
			->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
			->select('rekomendasi_biaya_perawatans.id', 'rekomendasi_biaya_perawatans.createdby_biper', 'roles.name')
			->get();

		// $rekomendasiBiayaPerawatan = rekomendasi_biaya_perawatan::where('createdby_biper', $userid)->get();
		$getdata = DB::table('model_has_roles')
			->leftjoin('rekomendasi_biaya_perawatans as b', 'b.tujuan_biper', '=', 'model_has_roles.role_id')
			->where('b.id', $id)
			->get();
		//alur
		$user = Auth::user();
		$roles = $user->roles()->pluck('name');
		$alur = [];

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


		// $user = Auth::user();
		// $roles = $user->roles()->pluck('name');

	
        // $roles = $user->roles()->pluck('name');
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
		    $rekomendasiBiayaPerawatan = DB::table('rekomendasi_biaya_perawatans as w')->select(
				'w.*',
				'roles.name as name_roles',
				'users.name as name_pembuat',
				'prov.name_prov',
				'kota.name_cities',
				'kecamatan.name_districts',
				'b.name_village',
				'p.*'
			)
				->leftjoin('model_has_roles', 'model_has_roles.model_id', '=', 'w.createdby_biper')
				->leftjoin('roles', 'roles.id', '=', 'model_has_roles.role_id')
				->leftjoin('users', 'users.id', '=', 'w.createdby_biper')
				->leftjoin('pelapor as p', 'p.id_form', 'w.id')
				->leftjoin('indonesia_provinces as prov', 'prov.code', '=', 'w.id_provinsi_biper')
				->leftjoin('indonesia_cities as kota', 'kota.code', '=', 'w.id_kabkot_biper')
				->leftjoin('indonesia_districts as kecamatan', 'kecamatan.code', '=', 'w.id_kecamatan_biper')
				->leftjoin('indonesia_villages as b', 'b.code', '=', 'w.id_kelurahan_biper')
				->where('p.id_menu', '05')
				->where('p.id_form', $id)
				->where('w.id', $id)->first();
        $rekomendasiBiperPelapor = DB::table('rekomendasi_biaya_perawatans')
            ->join('pelapor', function ($join) use ($id) {
                $join->on('rekomendasi_biaya_perawatans.id', '=', 'pelapor.id_form')
                    ->where('rekomendasi_biaya_perawatans.id', '=', $id);
            })
            ->select('rekomendasi_biaya_perawatans.*', 'pelapor.*')
            ->where('pelapor.id_menu', '05')
            ->where('pelapor.id_form', $id)
            ->first();
		// dd($rekomendasiBiperPelapor);   


		// $rekomendasiBiayaPerawatan = $this->rekomendasiBiayaPerawatanRepository->find($id);


		return view('rekomendasi_biaya_perawatans.edit', compact('rekomendasiBiperPelapor','getAuth','wilayah', 'rekomendasiBiayaPerawatan', 'roleid', 'getdata', 'alur', 'createdby', 'getUsers'));
	}

	/**
	 * Update the specified rekomendasi_biaya_perawatan in storage.
	 */
	public function update($id, Request $request)
	{
		// $validated = $request->validate([
		// 	'validasi_surat' => 'required',
		// ]);
		$userid = Auth::user()->id;
		// $databiper = rekomendasi_biaya_perawatan::where('id', $id)->first();
		$databiper = rekomendasi_biaya_perawatan::find($id);
		$rekomendasiBiayaPerawatan = DB::table('rekomendasi_biaya_perawatans as w')
		->join('model_has_roles', 'model_has_roles.model_id', '=', 'w.createdby_biper')
		->join('roles as rls', 'rls.id', '=', 'model_has_roles.role_id')

		->leftjoin('users', 'users.id', '=', 'w.createdby_biper')
		->select(
					'w.*',
					'rls.name as name_roles',
					// 'usr.name',
					'model_has_roles.*')
		->where('w.id', $id)->first();
		if ($request->file('file_perawatan_biper')) {
			$path = $request->file('file_perawatan_biper');
			$filename = 'biaya_perawatan/perawatan/' . $path->getClientOriginalName();
			$return = Storage::disk('imagekit')->put($filename, fopen($path->getRealPath(), 'r'));
			$databiper->file_perawatan_biper = Storage::disk('imagekit')->url($filename);
		} else {
			$databiper->file_perawatan_biper = $databiper->file_perawatan_biper;
		}

		if ($request->file('file_lap_sosial_biper')) {
			$path = $request->file('file_lap_sosial_biper');
			$filename = 'biaya_perawatan/lapsos/' . $path->getClientOriginalName();
			$return = Storage::disk('imagekit')->put($filename, fopen($path->getRealPath(), 'r'));
			$databiper->file_lap_sosial_biper = Storage::disk('imagekit')->url($filename);
		} else {
			$databiper->file_lap_sosial_biper = $databiper->file_lap_sosial_biper ;
		}

		if ($request->file('file_kebutuhan_layanan_biper')) {
			$path = $request->file('file_kebutuhan_layanan_biper');
			$filename = 'biaya_perawatan/layanan/' . $path->getClientOriginalName();
			$return = Storage::disk('imagekit')->put($filename, fopen($path->getRealPath(), 'r'));
			$databiper->file_kebutuhan_layanan_biper = Storage::disk('imagekit')->url($filename);
		} else {
			$databiper->file_kebutuhan_layanan_biper = $databiper->file_kebutuhan_layanan_biper;
		}

		$databiper->validasi_surat = $request->get('validasi_surat');
		$databiper->Nomor_Surat = $request->get('Nomor_Surat');
		$databiper->nama_rumah_sakit = $request->get('nama_rumah_sakit');
		$databiper->yth_biper = $request->get('yth_biper');
		$databiper->kab_kota_rumah_sakit = $request->get('kab_kota_rumah_sakit');


		$databiper->jenis_pelapor_biper = $request->get('jenis_pelapor_biper');
		$databiper->nik_biper = $request->get('nik_biper');
		$databiper->nama_biper = $request->get('nama_biper');
		$databiper->tgl_lahir_biper = $request->get('tgl_lahir_biper');
		$databiper->tempat_lahir_biper = $request->get('tempat_lahir_biper');
		$databiper->jenis_kelamin_biper = $request->get('jenis_kelamin_biper');
		$databiper->telp_biper = $request->get('telpon_biper');
		$databiper->alamat_biper = $request->get('alamat_biper');

		$databiper->no_rm_biper = $request->get('no_rm_biper');
		$databiper->tgl_masuk_rs_biper = $request->get('tgl_masuk_rs_biper');
		$databiper->keterangan_biper = $request->get('keterangan_biper');

		
		$databiper->status_aksi_biper = $request->get('status_aksi_biper');
		if($request->get('status_aksi_biper') == 'Kembalikan' || $request->get('status_aksi_biper') == 'Selesai'){
			$databiper->petugas_biper = $rekomendasiBiayaPerawatan->model_id;
			$databiper->tujuan_biper = $rekomendasiBiayaPerawatan->role_id;
			// $databiper->updatedby_biper = ;
		}else{
			$databiper->petugas_biper = $request->get('petugas');
			$databiper->tujuan_biper = $request->get('tujuan_biper');
		}
		$databiper->updatedby_biper = Auth::user()->id;
		$databiper->updated_at = date('Y-m-d H:i:s');
		$databiper->save();
		// print_r($databiper);die;
		// rekomendasi_biaya_perawatan::where('id', $id)->update($biper);
		$log = new log_biper();
		$log->id_trx_biper = $databiper->id;
		$log->id_alur_biper = $request->get('status_aksi_biper');
		$log->catatan_biper = $request->get('keterangan_biper');
		$log->file_pendukung_biper = $request->get('file_pendukung_biper');
		if($request->get('status_aksi_biper') == 'Kembalikan' || $request->get('status_aksi_biper') == 'Selesai'){
			$log->petugas_biper = $rekomendasiBiayaPerawatan->model_id;
			$log->tujuan_biper = $rekomendasiBiayaPerawatan->role_id;
			// $databiper->updatedby_biper = ;
		}else{
			$databiper->petugas_biper = $request->get('petugas');
			$databiper->tujuan_biper = $request->get('tujuan_biper');
		}
		$log->created_by_biper = Auth::user()->id;
		$log->updated_by_biper = Auth::user()->id;
		// dd($log);
		$log->save();
		
		return redirect('rekomendasi_biaya_perawatans')->withSuccess('Rekomendasi Berhasil Diubah');
	}


	// public function update($id, Request $request)
	// {
	// 	$userid = Auth::user()->id;
	// 	$databiper = rekomendasi_biaya_perawatan::where('id', $id)->first();
	// 	// dd();
	// 	// dd($request->get('status_dtks'));

	// 	if ($databiper->nik != null) {

	// 		if ($databiper->status_dtks == 'Terdaftar') {

	// 			if ($databiper->status_aksi == 'Teruskan' || $databiper->status_aksi == 'Kembalikan') {
	// 				// dd($request->get('status_dtks') );
	// 				$biper['petugas_biper']  = $request->get('petugas_bipera');
	// 				$biper['tujuan_biper'] = $request->get('tujuan_biper');
	// 				$biper['status_aksi_biper'] = $request->get('status_aksi_biper');
	// 				// dd($biper);
	// 				rekomendasi_biaya_perawatan::where('id', $id)->update($biper);
	// 			}
	// 			if ($databiper->status_aksi == 'Draft') {
	// 				// $files = [
	// 				// 	'file_ktp_terlapor_biper' => 'rekativasi/ktp/',
	// 				// 	'file_kk_terlapor_biper' => 'rekativasi/kk/',
	// 				// 	'file_keterangan_dtks_biper' => 'rekativasi/strukturorganisasi/',
	// 				// 	'file_pendukung_biper' => 'rekativasi/wajibpajak/',
	// 				// ];

	// 				// foreach ($files as $field => $path) {
	// 				// 	if ($request->file($field)) {
	// 				// 		$file = $request->file($field);
	// 				// 		$nama_file = $path . $file->getClientOriginalName();
	// 				// 		$return = Storage::disk('imagekit')->put($nama_file, fopen($file->getRealPath(), 'r'));
	// 				// 		$databiper->{$field} = Storage::disk('imagekit')->url($nama_file);
	// 				// 	} else {
	// 				// 		$databiper->{$field} = null;
	// 				// 	}
	// 				// }

	// 			}



	// 			if ($request->file('file_perawatan_biper')) {
	// 				$path = $request->file('file_perawatan_biper');
	// 				$filename = 'biaya_perawatan/perawatan/' . $path->getClientOriginalName();
	// 				$return = Storage::disk('imagekit')->put($filename, fopen($path->getRealPath(), 'r'));
	// 				$biper['file_perawatan_biper'] = Storage::disk('imagekit')->url($filename);
	// 			} else {
	// 				$biper['file_perawatan_biper'] = null;
	// 			}

	// 			if ($request->file('file_lap_sosial_biper')) {
	// 				$path = $request->file('file_lap_sosial_biper');
	// 				$filename = 'biaya_perawatan/lapsos/' . $path->getClientOriginalName();
	// 				$return = Storage::disk('imagekit')->put($filename, fopen($path->getRealPath(), 'r'));
	// 				$biper['file_lap_sosial_biper'] = Storage::disk('imagekit')->url($filename);
	// 			} else {
	// 				$biper['file_lap_sosial_biper'] = null;
	// 			}

	// 			if ($request->file('file_kebutuhan_layanan_biper')) {
	// 				$path = $request->file('file_kebutuhan_layanan_biper');
	// 				$filename = 'biaya_perawatan/layanan/' . $path->getClientOriginalName();
	// 				$return = Storage::disk('imagekit')->put($filename, fopen($path->getRealPath(), 'r'));
	// 				$biper['file_kebutuhan_layanan_biper'] = Storage::disk('imagekit')->url($filename);
	// 			} else {
	// 				$biper['file_kebutuhan_layanan_biper'] = null;
	// 			}


	// 			// $biper['id_provinsi_biper'] = $request->get('id_provinsi_biper');
	// 			// $biper['id_kabkot_biper'] = $request->get('id_kabkot_biper');
	// 			// $biper['id_kecamatan_biper'] = $request->get('id_kecamatan_biper');
	// 			// $biper['id_kelurahan_biper'] = $request->get('id_kelurahan_biper');
	// 			$biper['jenis_pelapor_biper'] = $request->get('jenis_pelapor_biper');
	// 			$biper['nik_biper'] = $request->get('nik_biper');
	// 			$biper['nama_biper'] = $request->get('nama_biper');
	// 			$biper['tgl_lahir_biper'] = $request->get('tgl_lahir_biper');
	// 			$biper['tempat_lahir_biper'] = $request->get('tempat_lahir_biper');
	// 			$biper['jenis_kelamin_biper'] = $request->get('jenis_kelamin_biper');
	// 			$biper['telp_biper'] = $request->get('telpon_biper');
	// 			$biper['alamat_biper'] = $request->get('alamat_biper');

	// 			$biper['no_rm_biper'] = $request->get('no_rm_biper');
	// 			$biper['tgl_masuk_rs_biper'] = $request->get('tgl_masuk_rs_biper');
	// 			$biper['keterangan_biper'] = $request->get('keterangan_biper');

	// 			$biper['petugas_biper']  = $request->get('petugas_biper');
	// 			$biper['tujuan_biper'] = $request->get('tujuan_biper');
	// 			$biper['status_aksi_biper'] = $request->get('status_aksi_biper');
	// 			$biper['updatedby_biper'] = Auth::user()->id;
	// 			$biper['updated_at'] = date('Y-m-d H:i:s');

	// 			rekomendasi_biaya_perawatan::where('id', $id)->update($biper);
	// 		}

	// 		$checkuserrole = DB::table('model_has_roles')
	// 			->leftjoin('roles', 'roles.id', '=', 'model_has_roles.role_id')
	// 			->where('model_id', '=', $userid)
	// 			->first();
	// 		if ($checkuserrole->name == $checkuserrole->name) {
	// 			//   dd($pengaduan);
	// 			$logpengaduan = new log_biper();
	// 			$logpengaduan['id_trx_biper'] = $databiper->id;
	// 			$logpengaduan['id_alur_biper'] = $request->get('status_aksi_biper');
	// 			$logpengaduan['petugas_biper'] = $request->get('petugas_biper');
	// 			$logpengaduan['catatan_biper']  = $request->get('catatan_biper');
	// 			$logpengaduan['file_pendukung_biper'] = $request->get('file_pendukung_biper');
	// 			$logpengaduan['tujuan_biper'] = $request->get('tujuan');
	// 			$logpengaduan['created_by_biper'] = Auth::user()->id;
	// 			$logpengaduan['updated_by_biper'] = Auth::user()->id;
	// 			// dd($logpengaduan);
	// 			$logpengaduan->save();


	// 			return redirect('rekomendasi_biaya_perawatans')->withSuccess('Rekomendasi Berhasil Diubah');
	// 		} else {

	// 			$cek = Prelist::where('nik', '=', $request->get('nik'))->exists();
	// 			if ($cek) {
	// 				return redirect('rekomendasi_biaya_perawatans')->withWarning('NIK Sudah Terdaftar Di Prelist');
	// 			} else {

	// 				$biper['id_provinsi'] = $request->get('id_provinsi_biper');
	// 				$biper['id_kabkot'] = $request->get('id_kabkot_biper');
	// 				$biper['id_kecamatan'] = $request->get('id_kecamatan_biper');
	// 				$biper['id_kelurahan'] = $request->get('id_kelurahan_biper');
	// 				$biper['nik'] = $request->get('nik_biper');
	// 				$biper['no_kk'] = $request->get('no_kk_biper');
	// 				$biper['nama'] = $request->get('nama_biper');
	// 				$biper['tgl_lahir'] = $request->get('tgl_lahir_biper');;
	// 				$biper['telp'] = $request->get('telpon_biper');
	// 				$biper['email'] = $request->get('email_biper');
	// 				// $biper['status_data'] = 'prelistdtks';
	// 				Prelist::where('id', $id)->update($biper);
	// 				return redirect('bipers')->withSuccess('Data  Berhasil Disimpan Di Prelist');
	// 			}
	// 		}
	// 	} else {

	// 		$biper['id_kabkot_biper'] = $request->get('id_kabkot_biper');
	// 		$biper['id_kecamatan_biper'] = $request->get('id_kecamatan_biper');
	// 		$biper['id_kelurahan_biper'] = $request->get('id_kelurahan_biper');
	// 		$biper['jenis_pelapor_biper'] = $request->get('jenis_pelapor_biper');
	// 		$biper['ada_nik_biper'] = $request->get('ada_nik_biper');
	// 		$biper['nik_biper'] = $request->get('nik_biper');
	// 		$biper['no_kk_biper'] = $request->get('no_kk_biper');
	// 		$biper['nama_biper'] = $request->get('nama_biper');
	// 		$biper['tgl_lahir_biper'] = $request->get('tgl_lahir_biper');
	// 		$biper['tempat_lahir_biper'] = $request->get('tempat_lahir_biper');
	// 		$biper['status_dtks_biper'] = $request->get('status_dtks_biper');
	// 		$biper['telp_biper'] = $request->get('telpon_biper');
	// 		$biper['email_biper'] = $request->get('email_biper');
	// 		$biper['petugas_biper']  = $request->get('petugas_biper');
	// 		$biper['tujuan_biper'] = $request->get('tujuan_biper');
	// 		$biper['status_aksi_biper'] = $request->get('status_aksi_biper');
	// 		// $biper['tl_file']  = $request->get('detail_biper');  
	// 		$biper['createdby_biper'] = Auth::user()->id;
	// 		$biper['updatedby_biper'] = Auth::user()->id;
	// 		// dd($biper);

	// 		rekomendasi_biaya_perawatan::where('id', $id)->update($biper);

	// 		return redirect('rekomendasi_biaya_perawatans')->withSuccess('Data Berhasil Diubah');
	// 	}
	// }

	/**
	 * Remove the specified rekomendasi_biaya_perawatan from storage.
	 *
	 * @throws \Exception
	 */
	public function destroy($id)
	{
		$rekomendasiBiayaPerawatan = $this->rekomendasiBiayaPerawatanRepository->find($id);

		if (empty($rekomendasiBiayaPerawatan)) {
			Flash::error('Rekomendasi Biaya Perawatan not found');

			return redirect(route('rekomendasi_biaya_perawatans.index'));
		}

		$this->rekomendasiBiayaPerawatanRepository->delete($id);

		Flash::success('Rekomendasi Biaya Perawatan deleted successfully.');

		return redirect(route('rekomendasi_biaya_perawatans.index'));
	}

	public function draft(Request $request)
	{
		$user_name = Auth::user()->name;
		$query = DB::table('rekomendasi_biaya_perawatans')
			->leftjoin('users', 'users.id', '=', 'rekomendasi_biaya_perawatans.createdby_biper')
			->leftjoin('wilayahs', 'wilayahs.createdby', '=', 'rekomendasi_biaya_perawatans.createdby_biper')
			->leftjoin('model_has_roles', 'model_has_roles.model_id', '=', 'rekomendasi_biaya_perawatans.tujuan_biper')
			->leftjoin('indonesia_districts as d', 'd.code', '=', 'rekomendasi_biaya_perawatans.id_kecamatan_biper')
			->leftjoin('indonesia_villages as b', 'b.code', '=', 'rekomendasi_biaya_perawatans.id_kelurahan_biper')
			->select('rekomendasi_biaya_perawatans.*', 'd.name_districts', 'b.name_village','users.name')
			->where('status_aksi_biper', 'Draft')
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
			if ($user_wilayah->name == 'Front Office Kelurahan'|| $user_wilayah == 'fasilitator') {
				$query->orWhere(function ($query) use ($user_wilayah) {
					$query->where('rekomendasi_biaya_perawatans.id_kelurahan_biper', $user_wilayah->kelurahan_id);
					$query->where('rekomendasi_biaya_perawatans.status_aksi_biper', 'Draft');
					$query->where('rekomendasi_biaya_perawatans.createdby_biper',  Auth::user()->id);
				});
			}else{
				$query->orWhere(function ($query) use ($user_wilayah) {
					$query->where('rekomendasi_biaya_perawatans.id_kabkot_biper', $user_wilayah->kota_id);
					$query->where('rekomendasi_biaya_perawatans.status_aksi_biper', 'Draft');
					$query->where('rekomendasi_biaya_perawatans.createdby_biper',  Auth::user()->id);
				});
			}

		if ($user_wilayah->name == 'Front Office Kelurahan' || $user_wilayah->name == 'Back Ofiice kelurahan'|| $user_wilayah->name == 'fasilitator') {
            if ($request->has('search') && !empty($request->search['value'])) {
                $search = $request->search['value'];
				$query = DB::table('rekomendasi_biaya_perawatans')
				->leftjoin('users', 'users.id', '=', 'rekomendasi_biaya_perawatans.createdby_biper')
				->leftjoin('wilayahs', 'wilayahs.createdby', '=', 'rekomendasi_biaya_perawatans.createdby_biper')
				->leftjoin('model_has_roles', 'model_has_roles.model_id', '=', 'rekomendasi_biaya_perawatans.tujuan_biper')
				->leftjoin('indonesia_districts as d', 'd.code', '=', 'rekomendasi_biaya_perawatans.id_kecamatan_biper')
				->leftjoin('indonesia_villages as b', 'b.code', '=', 'rekomendasi_biaya_perawatans.id_kelurahan_biper')
				->select('rekomendasi_biaya_perawatans.*', 'd.name_districts', 'b.name_village','users.name');
				// ->where('status_aksi_biper', 'Draft');
				$query->Where(function ($query) use ($user_wilayah) {
					$query->where('rekomendasi_biaya_perawatans.id_kelurahan_biper', '=', $user_wilayah->kelurahan_id)
						->where('rekomendasi_biaya_perawatans.createdby_biper',  Auth::user()->id)
						->where('rekomendasi_biaya_perawatans.status_aksi_biper', 'Draft');
					// dd($va);
				})->where(function ($query) use ($search) {
					$query->where('rekomendasi_biaya_perawatans.no_pendaftaran_biper', 'like', "%$search%");
						// ->orwhere('indonesia_villages.name_village', 'like', "%$search%")
						// ->orwhere('d.name_districts', 'like', "%$search%")
						// ->orwhere('indonesia_villages.name_village', 'like', "%$search%")
						// ->orwhere('rekomendasi_biaya_perawatans.alamat_biper', 'like', "%$search%");
				});
				// dd($query);
            }
        }elseif($user_wilayah->name == 'Front Office Kota' || $user_wilayah->name == 'Back Ofiice Kota'|| $user_wilayah->name == 'KepalaDinas'||$user_wilayah->name == 'SekertarisDinas'||$user_wilayah->name == 'Supervisor') {
			if ($request->has('search') && !empty($request->search['value'])) {
                $search = $request->search['value'];
				$query = DB::table('rekomendasi_biaya_perawatans')
				->leftjoin('users', 'users.id', '=', 'rekomendasi_biaya_perawatans.createdby_biper')
				->leftjoin('wilayahs', 'wilayahs.createdby', '=', 'rekomendasi_biaya_perawatans.createdby_biper')
				->leftjoin('model_has_roles', 'model_has_roles.model_id', '=', 'rekomendasi_biaya_perawatans.tujuan_biper')
				->leftjoin('indonesia_districts as d', 'd.code', '=', 'rekomendasi_biaya_perawatans.id_kecamatan_biper')
				->leftjoin('indonesia_villages as b', 'b.code', '=', 'rekomendasi_biaya_perawatans.id_kelurahan_biper')
				->select('rekomendasi_biaya_perawatans.*', 'd.name_districts', 'b.name_village','users.name');
				// ->where('status_aksi_biper', 'Draft');
				$query->Where(function ($query) use ($user_wilayah) {
					$query->where('rekomendasi_biaya_perawatans.id_kabkota_biper', '=', $user_wilayah->kota_id)
						->where('rekomendasi_biaya_perawatans.createdby_biper',  Auth::user()->id)
						->where('rekomendasi_biaya_perawatans.status_aksi_biper', 'Draft');
					// dd($va);
				})->where(function ($query) use ($search) {
					$query->where('rekomendasi_biaya_perawatans.no_pendaftaran_biper', 'like', "%$search%");
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
		//Add paginate
		$start = $request->start;
        $length = $request->length;
        $query->offset($start)->limit($length);
        $data = $query->get();
		// mengubah data JSON menjadi objek PHP

		// print_r($total_filtered_items);die; 
		return response()->json([
			'draw' => $request->input('draw'),
			'recordsTotal' => DB::table('rekomendasi_biaya_perawatans')->count(),
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
			$query = DB::table('rekomendasi_biaya_perawatans')
				->join('users', 'users.id', '=', 'rekomendasi_biaya_perawatans.tujuan_biper')
				->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_biaya_perawatans.id_kecamatan_biper')
				->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_biaya_perawatans.id_kelurahan_biper')
				->select('rekomendasi_biaya_perawatans.*', 'b.name_village', 'd.name_districts','users.name');
		} elseif ($user_wilayah->name == 'Front Office kota') {
			$query = DB::table('rekomendasi_biaya_perawatans')
				->join('users', 'users.id', '=', 'rekomendasi_biaya_perawatans.tujuan_biper')
				->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_biaya_perawatans.id_kecamatan_biper')
				->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_biaya_perawatans.id_kelurahan_biper')
				->select('rekomendasi_biaya_perawatans.*', 'b.name_village', 'd.name_districts','users.name');
		} elseif ($user_wilayah->name == 'Back Ofiice Kota') {
			$query = DB::table('rekomendasi_biaya_perawatans')
				->join('users', 'users.id', '=', 'rekomendasi_biaya_perawatans.tujuan_biper')
				->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_biaya_perawatans.id_kecamatan_biper')
				->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_biaya_perawatans.id_kelurahan_biper')
				->select('rekomendasi_biaya_perawatans.*', 'b.name_village', 'd.name_districts','users.name');
		} else {
			$query = DB::table('rekomendasi_biaya_perawatans')
				->join('users', 'users.id', '=', 'rekomendasi_biaya_perawatans.tujuan_biper')
				->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_biaya_perawatans.id_kecamatan_biper')
				->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_biaya_perawatans.id_kelurahan_biper')
				->select('rekomendasi_biaya_perawatans.*', 'b.name_village', 'd.name_districts','users.name');
		}
		if ($user_wilayah->name == 'Front Office Kelurahan') {
			//  dd($user_wilayah->role_id);

			$query->orWhere(function ($query) use ($user_wilayah) {
				$query->where('rekomendasi_biaya_perawatans.id_kelurahan_biper', '=', $user_wilayah->kelurahan_id)
					->where('rekomendasi_biaya_perawatans.tujuan_biper', '=', $user_wilayah->role_id)
					->where(function ($query) {
						$query->where('rekomendasi_biaya_perawatans.status_aksi_biper', '=', 'Teruskan')
							->orWhere('rekomendasi_biaya_perawatans.status_aksi_biper', '=', 'kembalikan');
					});
			});
		}
		if ($user_wilayah->name == 'Front Office kota') {
			//  dd($user_wilayah->role_id);

			$query->orWhere(function ($query) use ($user_wilayah) {
				$query->where('rekomendasi_biaya_perawatans.id_kelurahan_biper', '=', $user_wilayah->kelurahan_id)
					->where('rekomendasi_biaya_perawatans.tujuan_biper', '=', $user_wilayah->role_id)
					->where(function ($query) {
						$query->where('rekomendasi_biaya_perawatans.status_aksi_biper', '=', 'Teruskan')
							->orWhere('rekomendasi_biaya_perawatans.status_aksi_biper', '=', 'kembalikan');
					});
			});
		}
		if ($user_wilayah->name == 'Back Ofiice kelurahan') {
			//  dd($user_wilayah->role_id);
			$query->orWhere(function ($query) use ($user_wilayah) {
				$query->where('rekomendasi_biaya_perawatans.id_kelurahan_biper', '=', $user_wilayah->kelurahan_id)
					->where('rekomendasi_biaya_perawatans.tujuan_biper', '=', $user_wilayah->role_id)
					->where(function ($query) {
						$query->where('rekomendasi_biaya_perawatans.status_aksi_biper', '=', 'Teruskan')
							->orWhere('rekomendasi_biaya_perawatans.status_aksi_biper', '=', 'kembalikan');
					});
			});
		}
		if ($user_wilayah->name == 'Back Ofiice Kota') {
			$query->orWhere(function ($query) use ($user_wilayah) {
				$query->where('rekomendasi_biaya_perawatans.id_kelurahan_biper', '=', $user_wilayah->kelurahan_id)
					->where('rekomendasi_biaya_perawatans.tujuan_biper', '=', $user_wilayah->role_id)
					->where(function ($query) {
						$query->where('rekomendasi_biaya_perawatans.status_aksi_biper', '=', 'Teruskan')
							->orWhere('rekomendasi_biaya_perawatans.status_aksi_biper', '=', 'kembalikan');
					});
			});
		}
		if ($user_wilayah->name == 'kepala bidang') {

			$query->orWhere(function ($query) use ($user_wilayah) {
				$query->where('rekomendasi_biaya_perawatans.id_kabkot_biper', '=', $user_wilayah->kota_id)
					->where('rekomendasi_biaya_perawatans.tujuan_biper', '=', $user_wilayah->role_id)
					->where('rekomendasi_biaya_perawatans.petugas_biper', '=', $user_wilayah->model_id)
					->where(function ($query) {
						$query->where('rekomendasi_biaya_perawatans.status_aksi_biper', '=', 'Teruskan')
							->orWhere('rekomendasi_biaya_perawatans.status_aksi_biper', '=', 'kembalikan');
					});
				// dd($va);
			});
		}
		if ($user_wilayah->name == 'supervisor') {
			// dd($user_wilayah);
			$query->orWhere(function ($query) use ($user_wilayah) {
				$query->where('rekomendasi_biaya_perawatans.id_kecamatan_biper', '=', $user_wilayah->kecamatan_id)
					->where('rekomendasi_biaya_perawatans.tujuan_biper', '=', $user_wilayah->role_id)
					->where('rekomendasi_biaya_perawatans.petugas_biper', '=', $user_wilayah->model_id)
					->where(function ($query) {
						$query->where('rekomendasi_biaya_perawatans.status_aksi_biper', '=', 'Teruskan')
							->orWhere('rekomendasi_biaya_perawatans.status_aksi_biper', '=', 'kembalikan');
					});
				// dd($va);
			});
		}
		if ($user_wilayah->name == 'KepalaDinas') {
			// dd($user_wilayah);
			$query->orWhere(function ($query) use ($user_wilayah) {
				$query->where('rekomendasi_biaya_perawatans.id_kelurahan_biper', '=', $user_wilayah->kelurahan_id)
					->where('rekomendasi_biaya_perawatans.tujuan_biper', '=', $user_wilayah->role_id)
					->where('rekomendasi_biaya_perawatans.petugas_biper', '=', $user_wilayah->model_id)
					->where(function ($query) {
						$query->where('rekomendasi_biaya_perawatans.status_aksi_biper', '=', 'Teruskan')
							->orWhere('rekomendasi_biaya_perawatans.status_aksi_biper', '=', 'kembalikan');
					});
				// dd($va);
			});
		}
		if ($user_wilayah->name == 'Front Office Kelurahan' || $user_wilayah->name == 'Back Ofiice kelurahan'|| $user_wilayah->name == 'fasilitator') {
            if ($request->has('search') && !empty($request->search['value'])) {
                $search = $request->search['value'];
				$query->orWhere(function ($query) use ($user_wilayah) {
					$query->where('rekomendasi_biaya_perawatans.id_kelurahan_biper', '=', $user_wilayah->kelurahan_id)
						->where('rekomendasi_biaya_perawatans.tujuan_biper', '=', $user_wilayah->role_id)
						->where('rekomendasi_biaya_perawatans.petugas_biper', '=', $user_wilayah->model_id)
						->where(function ($query) {
							$query->where('rekomendasi_biaya_perawatans.status_aksi_biper', '=', 'Teruskan')
								->orWhere('rekomendasi_biaya_perawatans.status_aksi_biper', '=', 'kembalikan');
						});
					// dd($va);
				})->where(function ($query) use ($search) {
					$query->where('rekomendasi_biaya_perawatans.no_pendaftaran_biper', 'like', "%$search%");
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
					$query->where('rekomendasi_biaya_perawatans.id_kelurahan_biper', '=', $user_wilayah->kelurahan_id)
						->where('rekomendasi_biaya_perawatans.tujuan_biper', '=', $user_wilayah->role_id)
						->where('rekomendasi_biaya_perawatans.petugas_biper', '=', $user_wilayah->model_id)
						->where(function ($query) {
							$query->where('rekomendasi_biaya_perawatans.status_aksi_biper', '=', 'Teruskan')
								->orWhere('rekomendasi_biaya_perawatans.status_aksi_biper', '=', 'kembalikan');
						});
					// dd($va);
				})->where(function ($query) use ($search) {
					$query->where('rekomendasi_biaya_perawatans.no_pendaftaran_biper', 'like', "%$search%");
						// ->orwhere('indonesia_villages.name_village', 'like', "%$search%")
						// ->orwhere('d.name_districts', 'like', "%$search%")
						// ->orwhere('indonesia_villages.name_village', 'like', "%$search%")
						// ->orwhere('rekomendasi_biaya_perawatans.alamat_biper', 'like', "%$search%");
				});
				

				// ->orWhere('rekomendasi_biaya_perawatans.name_districts', 'like', "%$search%")
				// ->orWhere('rekomendasi_biaya_perawatans.nik_biper', 'like', "%$search%")
				// ->orWhere('rekomendasi_biaya_perawatans.tujuan_biper', 'like', "%$search%");
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
			'recordsTotal' => rekomendasi_biaya_perawatan::count(),
			'recordsFiltered' => $total_filtered_items,
			'data' => $data,
		]);
	}
	public function teruskan(Request $request)
	{
		$user_name = Auth::user()->name;
		// dd($user_name);

		$query = DB::table('rekomendasi_biaya_perawatans')
			->join('users', 'users.id', '=', 'rekomendasi_biaya_perawatans.petugas_biper')
			->leftjoin('indonesia_villages as b', 'b.code', '=', 'rekomendasi_biaya_perawatans.id_kelurahan_biper')
			->leftjoin('indonesia_districts as d', 'd.code', '=', 'rekomendasi_biaya_perawatans.id_kecamatan_biper')
			->select('rekomendasi_biaya_perawatans.*', 'b.name_village', 'd.name_districts', 'users.name');

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
		//Front Office Kota
		if ($user_wilayah->name == 'Front Office Kelurahan' || $user_wilayah->name == 'fasilitator') {
			$query = DB::table('rekomendasi_biaya_perawatans')
			->leftjoin('indonesia_districts as d', 'd.code', '=', 'rekomendasi_biaya_perawatans.id_kecamatan_biper')
			->leftjoin('indonesia_villages as b', 'b.code', '=', 'rekomendasi_biaya_perawatans.id_kelurahan_biper')
			// ->leftjoin('roles as r', 'r.id', '=', 'rekomendasi_biaya_perawatans.petugas_biper')
			->leftjoin('users', 'users.id', '=', 'rekomendasi_biaya_perawatans.petugas_biper')
			->select('rekomendasi_biaya_perawatans.*', 'b.name_village', 'd.name_districts','users.name')
			// ->selectRaw('IFNULL(r.name,"") name')
			->where('rekomendasi_biaya_perawatans.id_kelurahan_biper', '=', $user_wilayah->kelurahan_id)
			->whereIn('rekomendasi_biaya_perawatans.status_aksi_biper', ['Teruskan','Kembalikan'])
			->where('rekomendasi_biaya_perawatans.petugas_biper', '<>', $user_id)
			->whereRaw("(SELECT COUNT(l.id) FROM log_biper as l WHERE l.id_trx_biper = rekomendasi_biaya_perawatans.id AND l.created_by_biper = '".$user_id."') > 0 ");
			// dd($query);

		}
		if ($user_wilayah->name == 'Front Office kota') {

			$query = DB::table('rekomendasi_biaya_perawatans')
				->leftjoin('indonesia_districts as d', 'd.code', '=', 'rekomendasi_biaya_perawatans.id_kecamatan_biper')
				->leftjoin('indonesia_villages as b', 'b.code', '=', 'rekomendasi_biaya_perawatans.id_kelurahan_biper')
				// ->leftjoin('roles as r', 'r.id', '=', 'rekomendasi_biaya_perawatans.petugas_biper')
				->leftjoin('users', 'users.id', '=', 'rekomendasi_biaya_perawatans.petugas_biper')
				->select('rekomendasi_biaya_perawatans.*', 'b.name_village', 'd.name_districts','users.name')
				// ->selectRaw('IFNULL(r.name,"") name')
				->where('rekomendasi_biaya_perawatans.id_kabkot_biper', '=', $user_wilayah->kota_id)
				->whereIn('rekomendasi_biaya_perawatans.status_aksi_biper', ['Teruskan','Kembalikan'])
				->where('rekomendasi_biaya_perawatans.petugas_biper', '<>', $user_id)
				->whereRaw("(SELECT COUNT(l.id) FROM log_biper as l WHERE l.id_trx_biper = rekomendasi_biaya_perawatans.id AND l.updated_by_biper = '".$user_id."') > 0 ");
		}
		//Back office kota 
		if ($user_wilayah->name == 'Back Ofiice Kota') {
			// dd($user_wilayah->role_id);
			$query = DB::table('rekomendasi_biaya_perawatans')
			->leftjoin('indonesia_districts as d', 'd.code', '=', 'rekomendasi_biaya_perawatans.id_kecamatan_biper')
			->leftjoin('indonesia_villages as b', 'b.code', '=', 'rekomendasi_biaya_perawatans.id_kelurahan_biper')
			// ->leftjoin('roles as r', 'r.id', '=', 'rekomendasi_biaya_perawatans.petugas_biper')
			->leftjoin('users', 'users.id', '=', 'rekomendasi_biaya_perawatans.petugas_biper')
			->select('rekomendasi_biaya_perawatans.*', 'b.name_village', 'd.name_districts','users.name')
			// ->selectRaw('IFNULL(r.name,"") name')
			->where('rekomendasi_biaya_perawatans.id_kabkot_biper', '=', $user_wilayah->kota_id)
			->whereIn('rekomendasi_biaya_perawatans.status_aksi_biper', ['Teruskan','Kembalikan'])
			->where('rekomendasi_biaya_perawatans.petugas_biper', '<>', $user_id)
			->whereRaw("(SELECT COUNT(l.id) FROM log_biper as l WHERE l.id_trx_biper = rekomendasi_biaya_perawatans.id AND l.updated_by_biper = '".$user_id."') > 0 ");
		}
		//Back office kota 
		if ($user_wilayah->name == 'supervisor') {
			// dd($user_wilayah->model_id);
			$query = DB::table('rekomendasi_biaya_perawatans')
			->leftjoin('indonesia_districts as d', 'd.code', '=', 'rekomendasi_biaya_perawatans.id_kecamatan_biper')
			->leftjoin('indonesia_villages as b', 'b.code', '=', 'rekomendasi_biaya_perawatans.id_kelurahan_biper')
			// ->leftjoin('roles as r', 'r.id', '=', 'rekomendasi_biaya_perawatans.petugas_biper')
			->leftjoin('users', 'users.id', '=', 'rekomendasi_biaya_perawatans.petugas_biper')
			->select('rekomendasi_biaya_perawatans.*', 'b.name_village', 'd.name_districts','users.name')
			// ->selectRaw('IFNULL(r.name,"") name')
			->where('rekomendasi_biaya_perawatans.id_kecamatan_biper', '=', $user_wilayah->kecamatan_id)
			->whereIn('rekomendasi_biaya_perawatans.status_aksi_biper', ['Teruskan','Kembalikan'])
			->where('rekomendasi_biaya_perawatans.petugas_biper', '<>', $user_id)
			->whereRaw("(SELECT COUNT(l.id) FROM log_biper as l WHERE l.id_trx_biper = rekomendasi_biaya_perawatans.id AND l.updated_by_biper = '".$user_id."') > 0 ");
		}
		if ($user_wilayah->name == 'Back Ofiice kelurahan') {
			// dd($user_wilayah->model_id);
			$query = DB::table('rekomendasi_biaya_perawatans')
			->leftjoin('indonesia_districts as d', 'd.code', '=', 'rekomendasi_biaya_perawatans.id_kecamatan_biper')
			->leftjoin('indonesia_villages as b', 'b.code', '=', 'rekomendasi_biaya_perawatans.id_kelurahan_biper')
			// ->leftjoin('roles as r', 'r.id', '=', 'rekomendasi_biaya_perawatans.petugas_biper')
			->leftjoin('users', 'users.id', '=', 'rekomendasi_biaya_perawatans.petugas_biper')
			->select('rekomendasi_biaya_perawatans.*', 'b.name_village', 'd.name_districts','users.name')
			// ->selectRaw('IFNULL(r.name,"") name')
			->where('rekomendasi_biaya_perawatans.id_kelurahan_biper', '=', $user_wilayah->kelurahan_id)
			->whereIn('rekomendasi_biaya_perawatans.status_aksi_biper', ['Teruskan','Kembalikan'])
			->where('rekomendasi_biaya_perawatans.petugas_biper', '<>', $user_id)
			->whereRaw("(SELECT COUNT(l.id) FROM log_biper as l WHERE l.id_trx_biper = rekomendasi_biaya_perawatans.id AND l.updated_by_biper = '".$user_id."') > 0 ");
		}

		if ($user_wilayah->name == 'kepala bidang') {
			// dd( $user_wilayah->role_id);
			$query = DB::table('rekomendasi_biaya_perawatans')
			->leftjoin('indonesia_districts as d', 'd.code', '=', 'rekomendasi_biaya_perawatans.id_kecamatan_biper')
			->leftjoin('indonesia_villages as b', 'b.code', '=', 'rekomendasi_biaya_perawatans.id_kelurahan_biper')
			// ->leftjoin('roles as r', 'r.id', '=', 'rekomendasi_biaya_perawatans.petugas_biper')
			->leftjoin('users', 'users.id', '=', 'rekomendasi_biaya_perawatans.petugas_biper')
			->select('rekomendasi_biaya_perawatans.*', 'b.name_village', 'd.name_districts','users.name')
			// ->selectRaw('IFNULL(r.name,"") name')
			->where('rekomendasi_biaya_perawatans.id_kabkot_biper', '=', $user_wilayah->kota_id)
			->whereIn('rekomendasi_biaya_perawatans.status_aksi_biper', ['Teruskan','Kembalikan'])
			->where('rekomendasi_biaya_perawatans.petugas_biper', '<>', $user_id)
			->whereRaw("(SELECT COUNT(l.id) FROM log_biper as l WHERE l.id_trx_biper = rekomendasi_biaya_perawatans.id AND l.updated_by_biper = '".$user_id."') > 0 ");
		}
		

		if ($user_wilayah->name == 'Front Office Kelurahan' || $user_wilayah->name  == 'Back Ofiice kelurahan'|| $user_wilayah->name  == 'fasilitator') {
            if ($request->has('search') && !empty($request->search['value'])) {
                $search = $request->search['value'];
				$query = DB::table('rekomendasi_biaya_perawatans')
				->leftjoin('indonesia_districts as d', 'd.code', '=', 'rekomendasi_biaya_perawatans.id_kecamatan_biper')
				->leftjoin('indonesia_villages as b', 'b.code', '=', 'rekomendasi_biaya_perawatans.id_kelurahan_biper')
				// ->leftjoin('roles as r', 'r.id', '=', 'rekomendasi_biaya_perawatans.petugas_biper')
				->leftjoin('users', 'users.id', '=', 'rekomendasi_biaya_perawatans.petugas_biper')
				->select('rekomendasi_biaya_perawatans.*', 'b.name_village', 'd.name_districts','users.name')
				// ->selectRaw('IFNULL(r.name,"") name')
				->where('rekomendasi_biaya_perawatans.id_kelurahan_biper', '=', $user_wilayah->kelurahan_id)
				->whereIn('rekomendasi_biaya_perawatans.status_aksi_biper', ['Teruskan','Kembalikan'])
				->where('rekomendasi_biaya_perawatans.petugas_biper', '<>', $user_id)
				->whereRaw("(SELECT COUNT(l.id) FROM log_biper as l WHERE l.id_trx_biper = rekomendasi_biaya_perawatans.id AND l.created_by_biper = '".$user_id."') > 0 ")
				->where(function ($query) use ($search) {
					$query->where('rekomendasi_biaya_perawatans.no_pendaftaran_biper', 'like', "%$search%");
					// ->orwhere('indonesia_villages.name_village', 'like', "%$search%")
					// ->orwhere('d.name_districts', 'like', "%$search%")
					// ->orwhere('indonesia_villages.name_village', 'like', "%$search%")
					// ->orwhere('rekomendasi_biaya_perawatans.alamat_biper', 'like', "%$search%");
				});
				

				// ->orWhere('rekomendasi_biaya_perawatans.name_districts', 'like', "%$search%")
				// ->orWhere('rekomendasi_biaya_perawatans.nik_biper', 'like', "%$search%")
				// ->orWhere('rekomendasi_biaya_perawatans.tujuan_biper', 'like', "%$search%");
            }
        }elseif($user_wilayah->name == 'Front Office Kota' || $user_wilayah->name == 'Back Ofiice Kota'|| $user_wilayah->name == 'KepalaDinas'||$user_wilayah->name == 'SekertarisDinas'||$user_wilayah->name == 'Supervisor') {
			if ($request->has('search') && !empty($request->search['value'])) {
                $search = $request->search['value'];
				$query = DB::table('rekomendasi_biaya_perawatans')
				->leftjoin('indonesia_districts as d', 'd.code', '=', 'rekomendasi_biaya_perawatans.id_kecamatan_biper')
				->leftjoin('indonesia_villages as b', 'b.code', '=', 'rekomendasi_biaya_perawatans.id_kelurahan_biper')
				// ->leftjoin('roles as r', 'r.id', '=', 'rekomendasi_biaya_perawatans.petugas_biper')
				->leftjoin('users', 'users.id', '=', 'rekomendasi_biaya_perawatans.petugas_biper')
				->select('rekomendasi_biaya_perawatans.*', 'b.name_village', 'd.name_districts','users.name')
				// ->selectRaw('IFNULL(r.name,"") name')
				->where('rekomendasi_biaya_perawatans.id_kabkot_biper', '=', $user_wilayah->kota_id)
				->whereIn('rekomendasi_biaya_perawatans.status_aksi_biper', ['Teruskan','Kembalikan'])
				->where('rekomendasi_biaya_perawatans.petugas_biper', '<>', $user_id)
				->whereRaw("(SELECT COUNT(l.id) FROM log_biper as l WHERE l.id_trx_biper = rekomendasi_biaya_perawatans.id AND l.created_by_biper = '".$user_id."') > 0 ")
				->where(function ($query) use ($search) {
					$query->where('rekomendasi_biaya_perawatans.no_pendaftaran_biper', 'like', "%$search%");
					// ->orwhere('indonesia_villages.name_village', 'like', "%$search%")
					// ->orwhere('d.name_districts', 'like', "%$search%")
					// ->orwhere('indonesia_villages.name_village', 'like', "%$search%")
					// ->orwhere('rekomendasi_biaya_perawatans.alamat_biper', 'like', "%$search%");
				});
				

				// ->orWhere('rekomendasi_biaya_perawatans.name_districts', 'like', "%$search%")
				// ->orWhere('rekomendasi_biaya_perawatans.nik_biper', 'like', "%$search%")
				// ->orWhere('rekomendasi_biaya_perawatans.tujuan_biper', 'like', "%$search%");
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
			'recordsTotal' => rekomendasi_biaya_perawatan::count(),
			'recordsFiltered' => $total_filtered_items,
			'data' => $data,
		]);
	}

	public function selesai(Request $request)
	{
		$user_name = Auth::user()->name;
		$query = DB::table('rekomendasi_biaya_perawatans')
			->join('users', 'users.id', '=', 'rekomendasi_biaya_perawatans.createdby_biper')
			->join('log_biper', 'log_biper.id_trx_biper', '=', 'rekomendasi_biaya_perawatans.id')
			->join('model_has_roles', 'model_has_roles.model_id', '=', 'rekomendasi_biaya_perawatans.tujuan_biper')
			->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_biaya_perawatans.id_kelurahan_biper')
			->select('rekomendasi_biaya_perawatans.*', 'b.name_village');
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
			$query = DB::table('rekomendasi_biaya_perawatans')
				->join('users', 'users.id', '=', 'rekomendasi_biaya_perawatans.tujuan_biper')
				// ->join('log_biper', 'log_biper.id_trx_biper', '=', 'rekomendasi_biaya_perawatans.id')
				->join('indonesia_villages', 'indonesia_villages.code', '=', 'rekomendasi_biaya_perawatans.id_kelurahan_biper')
				->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_biaya_perawatans.id_kecamatan_biper')
				->select('rekomendasi_biaya_perawatans.*', 'd.name_districts', 'indonesia_villages.name_village','users.name')
				->orWhere(function ($query) use ($user_wilayah) {
					$query->where('rekomendasi_biaya_perawatans.id_kelurahan_biper', $user_wilayah->kelurahan_id)
						// ->where('log_biper.tujuan_biper', '=', $user_wilayah->role_id)
						// ->where('log_biper.created_by_biper', '!=', $user_wilayah->model_id)
						->where(function ($query) {
							$query->where('rekomendasi_biaya_perawatans.status_aksi_biper', '=', 'Tolak')
								->orWhere('rekomendasi_biaya_perawatans.status_aksi_biper', '=', 'Selesai');
						});
				})->distinct();
		} elseif ($user_wilayah->name == 'Front Office kota') {
			//  dd($user_wilayah->role_id);
			$query = DB::table('rekomendasi_biaya_perawatans')
				->join('users', 'users.id', '=', 'rekomendasi_biaya_perawatans.tujuan_biper')
				// ->join('log_biper', 'log_biper.id_trx_biper', '=', 'rekomendasi_biaya_perawatans.id')
				->join('indonesia_villages', 'indonesia_villages.code', '=', 'rekomendasi_biaya_perawatans.id_kelurahan_biper')
				->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_biaya_perawatans.id_kecamatan_biper')
				->select('rekomendasi_biaya_perawatans.*', 'd.name_districts', 'indonesia_villages.name_village','users.name')
				->orWhere(function ($query) use ($user_wilayah) {
					$query->where('rekomendasi_biaya_perawatans.id_kabkot_biper', $user_wilayah->kota_id)
						// ->where('log_biper.tujuan_biper', '=', $user_wilayah->role_id)
						// ->where('log_biper.created_by_biper', '!=', $user_wilayah->model_id)
						->where(function ($query) {
							$query->where('rekomendasi_biaya_perawatans.status_aksi_biper', '=', 'Tolak')
								->orWhere('rekomendasi_biaya_perawatans.status_aksi_biper', '=', 'Selesai');
						});
				})->distinct();
		} elseif ($user_wilayah->name == 'Back Ofiice kelurahan') {
			// dd($user_wilayah);
			$query = DB::table('rekomendasi_biaya_perawatans')
				->join('users', 'users.id', '=', 'rekomendasi_biaya_perawatans.tujuan_biper')
				// ->join('log_biper', 'log_biper.id_trx_biper', '=', 'rekomendasi_biaya_perawatans.id')
				->join('indonesia_villages', 'indonesia_villages.code', '=', 'rekomendasi_biaya_perawatans.id_kelurahan_biper')
				// ->join('model_has_roles', 'model_has_roles.model_id', '=', 'rekomendasi_biaya_perawatans.tujuan_biper')
				->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_biaya_perawatans.id_kecamatan_biper')
				->select('rekomendasi_biaya_perawatans.*', 'd.name_districts', 'indonesia_villages.name_village','users.name')
				->orWhere(function ($query) use ($user_wilayah) {
					$query->where('rekomendasi_biaya_perawatans.id_kelurahan_biper', $user_wilayah->kelurahan_id)
						// ->where('log_biper.tujuan_biper', '!=', $user_wilayah->role_id)
						// ->where('log_biper.created_by_biper', '=', auth::user()->id)
						->where(function ($query) {
							$query->where('rekomendasi_biaya_perawatans.status_aksi_biper', '=', 'Tolak')
								->orWhere('rekomendasi_biaya_perawatans.status_aksi_biper', '=', 'Selesai');
						});
				})->distinct();
			// dd($query); 
		} elseif ($user_wilayah->name == 'Back Ofiice Kota') {
			// dd($user_wilayah);
			$query = DB::table('rekomendasi_biaya_perawatans')
				->join('users', 'users.id', '=', 'rekomendasi_biaya_perawatans.tujuan_biper')
				// ->join('log_biper', 'log_biper.id_trx_biper', '=', 'rekomendasi_biaya_perawatans.id')
				->join('indonesia_villages', 'indonesia_villages.code', '=', 'rekomendasi_biaya_perawatans.id_kelurahan_biper')
				// ->join('model_has_roles', 'model_has_roles.model_id', '=', 'rekomendasi_biaya_perawatans.tujuan_biper')
				->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_biaya_perawatans.id_kecamatan_biper')
				->select('rekomendasi_biaya_perawatans.*', 'd.name_districts', 'indonesia_villages.name_village','users.name')
				->orWhere(function ($query) use ($user_wilayah) {
					$query->where('rekomendasi_biaya_perawatans.id_kabkot_biper', $user_wilayah->kota_id)
						// ->where('log_biper.tujuan_biper', '!=', $user_wilayah->role_id)
						// ->where('log_biper.created_by_biper', '=', auth::user()->id)
						->where(function ($query) {
							$query->where('rekomendasi_biaya_perawatans.status_aksi_biper', '=', 'Tolak')
								->orWhere('rekomendasi_biaya_perawatans.status_aksi_biper', '=', 'Selesai');
						});
				})->distinct();
			// dd($query); 
		} elseif ($user_wilayah->name == 'supervisor') {
			// dd($user_wilayah);
			$query = DB::table('rekomendasi_biaya_perawatans')
				->join('users', 'users.id', '=', 'rekomendasi_biaya_perawatans.tujuan_biper')
				// ->join('log_biper', 'log_biper.id_trx_biper', '=', 'rekomendasi_biaya_perawatans.id')
				->join('indonesia_villages', 'indonesia_villages.code', '=', 'rekomendasi_biaya_perawatans.id_kelurahan_biper')
				// ->join('model_has_roles', 'model_has_roles.model_id', '=', 'rekomendasi_biaya_perawatans.tujuan_biper')
				->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_biaya_perawatans.id_kecamatan_biper')
				->select('rekomendasi_biaya_perawatans.*', 'd.name_districts', 'indonesia_villages.name_village','users.name')
				->orWhere(function ($query) use ($user_wilayah) {
					$query->where('rekomendasi_biaya_perawatans.id_kecamatan_biper', $user_wilayah->kecamatan_id)
						// ->where('log_biper.tujuan_biper', '!=', $user_wilayah->role_id)
						// ->where('log_biper.created_by_biper', '=', auth::user()->id)
						->where(function ($query) {
							$query->where('rekomendasi_biaya_perawatans.status_aksi_biper', '=', 'Tolak')
								->orWhere('rekomendasi_biaya_perawatans.status_aksi_biper', '=', 'Selesai');
						});
				})->distinct();
		} elseif ($user_wilayah->name == 'kepala bidang') {
			// dd($user_wilayah->role_id);
			$query = DB::table('rekomendasi_biaya_perawatans')
				->join('users', 'users.id', '=', 'rekomendasi_biaya_perawatans.tujuan_biper')
				->join('log_biper', 'log_biper.id_trx_biper', '=', 'rekomendasi_biaya_perawatans.id')
				->join('indonesia_villages', 'indonesia_villages.code', '=', 'rekomendasi_biaya_perawatans.id_kelurahan_biper')
				->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_biaya_perawatans.id_kecamatan_biper')
				->select('rekomendasi_biaya_perawatans.*', 'd.name_districts', 'indonesia_villages.name_village','users.name')
				->orWhere(function ($query) use ($user_wilayah) {
					$query->where('rekomendasi_biaya_perawatans.id_kabkot_biper', $user_wilayah->kota_id)
						// ->where('log_biper.tujuan_biper', '!=', $user_wilayah->role_id)
						// ->where('log_biper.created_by_biper', '=', auth::user()->id)
						->where(function ($query) {
							$query->where('rekomendasi_biaya_perawatans.status_aksi_biper', '=', 'Tolak')
								->orWhere('rekomendasi_biaya_perawatans.status_aksi_biper', '=', 'Selesai');
						});
				})->distinct();
		} elseif ($user_wilayah->name == 'KepalaDinas') {
			// dd($user_wilayah);
			$query = DB::table('rekomendasi_biaya_perawatans')
				->join('users', 'users.id', '=', 'rekomendasi_biaya_perawatans.createdby_biper')
				->join('log_biper', 'log_biper.id_trx_biper', '=', 'rekomendasi_biaya_perawatans.id')
				// ->join('model_has_roles', 'model_has_roles.model_id', '=', 'rekomendasi_biaya_perawatans.tujuan_biper')
				->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_biaya_perawatans.id_kecamatan_biper')
				->select('rekomendasi_biaya_perawatans.*', 'd.name_districts', 'indonesia_villages.name_village','users.name')
				->orWhere(function ($query) use ($user_wilayah) {
					$query->where('rekomendasi_biaya_perawatans.id_kelurahan_biper', $user_wilayah->kelurahan_id)
						->where('log_biper.tujuan_biper', '=', $user_wilayah->role_id)
						->where('log_biper.petugas_biper', '=', $user_wilayah->model_id)
						->where(function ($query) {
							$query->where('rekomendasi_biaya_perawatans.status_aksi_biper', '=', 'Tolak')
								->orWhere('rekomendasi_biaya_perawatans.status_aksi_biper', '=', 'Selesai');
						});
				});
		}

		if ($user_wilayah->name == 'Front Office Kelurahan' || $user_wilayah->name == 'Back Ofiice kelurahan'|| $user_wilayah->name == 'fasilitator') {
            if ($request->has('search') && !empty($request->search['value'])) {
                $search = $request->search['value'];
				$query = DB::table('rekomendasi_biaya_perawatans')
				->join('users', 'users.id', '=', 'rekomendasi_biaya_perawatans.tujuan_biper')
				->join('indonesia_villages', 'indonesia_villages.code', '=', 'rekomendasi_biaya_perawatans.id_kelurahan_biper')
				->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_biaya_perawatans.id_kecamatan_biper')
				->select('rekomendasi_biaya_perawatans.*', 'd.name_districts', 'indonesia_villages.name_village', 'users.name')
				->where(function ($query) use ($user_wilayah) {
					$query->where('rekomendasi_biaya_perawatans.id_kelurahan_biper', $user_wilayah->kelurahan_id)
						->where(function ($query) {
							$query->where('rekomendasi_biaya_perawatans.status_aksi_biper', '=', 'Tolak')
								->orWhere('rekomendasi_biaya_perawatans.status_aksi_biper', '=', 'Selesai');
						});
				})->where(function ($query) use ($search) {
					$query->where('rekomendasi_biaya_perawatans.no_pendaftaran_biper', 'like', "%$search%");
						// ->orwhere('indonesia_villages.name_village', 'like', "%$search%")
						// ->orwhere('d.name_districts', 'like', "%$search%")
						// ->orwhere('indonesia_villages.name_village', 'like', "%$search%")
						// ->orwhere('rekomendasi_biaya_perawatans.alamat_biper', 'like', "%$search%");
				});
            }
        }elseif($user_wilayah->name == 'Front Office Kota' || $user_wilayah->name == 'Back Ofiice Kota'|| $user_wilayah->name == 'KepalaDinas'||$user_wilayah->name == 'SekertarisDinas'||$user_wilayah->name == 'Supervisor') {
            if ($request->has('search') && !empty($request->search['value'])) {
                $search = $request->search['value'];
				$query = DB::table('rekomendasi_biaya_perawatans')
				->join('users', 'users.id', '=', 'rekomendasi_biaya_perawatans.tujuan_biper')
				->join('indonesia_villages', 'indonesia_villages.code', '=', 'rekomendasi_biaya_perawatans.id_kelurahan_biper')
				->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_biaya_perawatans.id_kecamatan_biper')
				->select('rekomendasi_biaya_perawatans.*', 'd.name_districts', 'indonesia_villages.name_village', 'users.name')
				->where(function ($query) use ($user_wilayah) {
					$query->where('rekomendasi_biaya_perawatans.id_kelurahan_biper', $user_wilayah->kelurahan_id)
						->where(function ($query) {
							$query->where('rekomendasi_biaya_perawatans.status_aksi_biper', '=', 'Tolak')
								->orWhere('rekomendasi_biaya_perawatans.status_aksi_biper', '=', 'Selesai');
						});
					})->where(function ($query) use ($search) {
						$query->where('rekomendasi_biaya_perawatans.no_pendaftaran_biper', 'like', "%$search%");
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
			'recordsTotal' => rekomendasi_biaya_perawatan::count(),
			'recordsFiltered' => $total_filtered_items,
			'data' => $data,
		]);
	}
	public function getPetugasBiayaPerawatan($id)
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
                    ->leftJoin('rekomendasi_biaya_perawatans','rekomendasi_biaya_perawatans.createdby_biper','=','u.id')
                    ->where('mhr.model_type', '=', 'App\Models\User')
                    ->where('wilayahs.kota_id', '=',$wilayah->kota_id)
                    ->where('mhr.role_id', '=', $id)
                    // ->whereRaw("(SELECT COUNT(l.id) FROM users as l WHERE l.id = '".$id."') > 0 ")
                    ->whereRaw("(SELECT COUNT(l.id) FROM users as l WHERE l.id = rekomendasi_biaya_perawatans.id AND l.id = '".$id."') > 0 ")
                    ->select('u.id as user_id', 'u.name', 'u.email', 'r.name as role')
                    ->get();
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
