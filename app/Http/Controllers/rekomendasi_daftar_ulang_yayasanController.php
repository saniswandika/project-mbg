<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\log_ulangYayasan;
use App\Models\rekomendasi_daftar_ulang_yayasan;
use App\Models\rekomendasi_terdaftar_yayasan;
use App\Models\RekomendasiYayasansProvinsi;
use App\Models\Roles;
use App\Repositories\rekomendasi_daftar_ulang_yayasanRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use stdClass;

class rekomendasi_daftar_ulang_yayasanController extends Controller
{
    /** @var rekomendasi_daftar_ulang_yayasanRepository $rekomendasiDaftarUlangYayasanRepository*/
    private $rekomendasiDaftarUlangYayasanRepository;

    public function __construct(rekomendasi_daftar_ulang_yayasanRepository $rekomendasiDaftarUlangYayasanRepo)
    {
        $this->rekomendasiDaftarUlangYayasanRepository = $rekomendasiDaftarUlangYayasanRepo;
    }

    /**
     * Display a listing of the rekomendasi_daftar_ulang_yayasan.
     */



    public function index(Request $request)
    {
        $rekomendasiDaftarUlangYayasan = $this->rekomendasiDaftarUlangYayasanRepository->paginate(10);
        // dd()
        return view('rekomendasi_daftar_ulang_yayasans.index')
            ->with('re$rekomendasiDaftarUlangYayasans', $rekomendasiDaftarUlangYayasan);
    }
    public function FileRekomDaftarUlangYayasan($id)
    {
        $rekomendasiTerdaftaryayasan = rekomendasi_terdaftar_yayasan::find($id);
        // dd($rekomendasiTerdaftaryayasan);
       $date = Carbon::parse($rekomendasiTerdaftaryayasan->updated_at)->locale('id');

       $date->settings(['formatFunction' => 'translatedFormat']);

       $tanggal = $date->format('j F Y ');


       // dd($tanggal);
        $pdf = PDF::loadHtml(view('rekomendasi_daftar_ulang_yayasans.file_permohonan',compact('rekomendasiTerdaftaryayasan','tanggal')));
        $filename = 'File Permohonan' . $rekomendasiTerdaftaryayasan->nama . '.pdf';
        return $pdf->stream($filename);
    }

    /**
     * Show the form for creating a new rekomendasi_daftar_ulang_yayasan.
     */

    public function create()
    {
    }

    /**
     * Store a newly created rekomendasi_daftar_ulang_yayasan in storage.
     */
    public function store()
    {
    }
    

    /**
     * Display the specified rekomendasi_daftar_ulang_yayasan.
     */
    public function show($id)
    {
        $userid = Auth::user()->id;
        $rekomendasiDaftarUlangYayasan =  DB::table('rekomendasi_terdaftar_yayasans as w')->select(
            'w.*',
            'rls.name as name_roles',
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

        if (empty($rekomendasiDaftarUlangYayasan)) {
            Flash::error('Rekomendasi not found');

            return redirect(route('rekomendasi_daftar_ulang_yayasans.index'));
        }
        $roleid = DB::table('roles')
            ->where('name', 'Back Ofiice kelurahan')
            // ->where('name', 'supervisor')
            ->orWhere('name', 'supervisor')
            ->get();
        $checkroles = DB::table('model_has_roles')
            ->leftjoin('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->get();

        $log_ulangYayasan = DB::table('log_ulangyayasan as w')->select(
            'w.*',
            'roles.name',
            'users.name',
            // 'alur.name'

        )
        ->leftjoin('users', 'users.id', '=', 'w.petugas_ulangYayasan')
        ->leftjoin('roles', 'roles.id', '=', 'w.tujuan_ulangYayasan')
        ->where('w.id_trx_ulangYayasan', $id)->get();
        // dd($log_ulangYayasan);   
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

        return view('rekomendasi_daftar_ulang_yayasans.show', compact('rekomendasiDaftarUlangYayasan','wilayah','roleid','checkroles', 'log_ulangYayasan'));
    }


    /**
     * Show the form for editing the specified rekomendasi_daftar_ulang_yayasan.
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

        $rekomendasiDaftarUlangYayasan=DB::table('rekomendasi_terdaftar_yayasans as w')->select(
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

        // $rekomendasiDaftarUlangYayasan = $this->rekomendasiDaftarUlangYayasanRepository->find($id);


        return view('rekomendasi_daftar_ulang_yayasans.edit', compact('wilayah','getAuth','rekomendasiDaftarUlangYayasan', 'roleid', 'getdata', 'alur', 'created_by', 'getUsers', 'getAuth'));
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
     * Update the specified rekomendasi_daftar_ulang_yayasan in storage.
     */
    public function sertifikat_akreditasi (Request $request){
        // $getdata = rekomendasi_terdaftar_yayasan::where('id', $id)->first();
        // dd($getdata);
        // $data = $request->all();
        // dd($data);
        $files = [
            'sertifikat_akreditasi'
        ];
        foreach ($files as $file) {
            if ($request->file($file)) {
                $path = $request->file($file);
                $nama_file = 'yayasan/' . $file . '/' . $path->getClientOriginalName();
                Storage::disk('imagekit')->put($nama_file, fopen($path->getRealPath(), 'r'));
                $data[$file] = Storage::disk('imagekit')->url($nama_file);
            } else {
                $data[$file] = null;
            }
        }
        return response()->json([
            'data' => $data,
        ]);
        // $data['updated_by'] = Auth::user()->name;
        // $getdata->update($data);
    }
    public function update($id, Request $request)
    {
        $getdata = rekomendasi_terdaftar_yayasan::where('id', $id)->first();
        // dd($getdata);
        $data = $request->all();
        // dd($data);
        $files = [
            'sertifikat_akreditasi'
        ];
        foreach ($files as $file) {
            if ($request->file($file)) {
                $path = $request->file($file);
                $nama_file = 'yayasan/' . $file . '/' . $path->getClientOriginalName();
                Storage::disk('imagekit')->put($nama_file, fopen($path->getRealPath(), 'r'));
                $data[$file] = Storage::disk('imagekit')->url($nama_file);
            } else {
                $data[$file] = $getdata->$file;;
            }
        }

        $data['updated_by'] = Auth::user()->id;
        $getdata->update($data);
        // dd($getdata->id);
        $logpengaduan = new log_ulangYayasan();
        $logpengaduan['id_trx_ulangyayasan'] = $getdata->id;
        $logpengaduan['id_alur_ulangYayasan'] = $request->get('keterangan_daftar_ulang');
        $logpengaduan['petugas_ulangYayasan'] = $request->get('petugas');
        $logpengaduan['catatan_ulangYayasan']  = $request->get('catatan');
        $logpengaduan['validasi_surat']  = $request->get('validasi_surat');
        $logpengaduan['file_permohonan_ulangYayasan'] = $request->get('file_permohonan');
        $logpengaduan['tujuan_ulangYayasan'] = $request->get('tujuan');
        $logpengaduan['updated_at'] = $request->get('updated_at');
        $logpengaduan['created_by_ulangYayasan'] = Auth::user()->id;

        $logpengaduan->save();
        return redirect()->route('rekomendasi_daftar_ulang_yayasans.index')->with('success', 'Data berhasil diupdate.');
    }

    /**
     * Remove the specified rekomendasi_daftar_ulang_yayasan from storage.
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $rekomendasi = rekomendasi_daftar_ulang_yayasan::findOrFail($id);

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
    public function listTerdatarYayasan(Request $request)
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
        
            $query = DB::table('rekomendasi_terdaftar_yayasans')
                ->join('users', 'users.id', '=', 'rekomendasi_terdaftar_yayasans.petugas')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_terdaftar_yayasans.id_kecamatan')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_terdaftar_yayasans.id_kelurahan')
                ->select('rekomendasi_terdaftar_yayasans.*', 'b.name_village', 'd.name_districts', 'users.name')
                ->orWhere(function ($query) use ($user_wilayah) {
                $query->where('rekomendasi_terdaftar_yayasans.id_kelurahan', '=', $user_wilayah->kelurahan_id)
                    // ->where('rekomendasi_terdaftar_yayasans.tujuan', '=', $user_wilayah->role_id)
                    // ->where('rekomendasi_terdaftar_yayasans.petugas', '=', auth::user()->id)

                    ->where(function ($query) {
                        $query->where('rekomendasi_terdaftar_yayasans.status_alur', '=', 'Selesai');
                            // ->orWhere('rekomendasi_terdaftar_yayasans.status_alur', '=', 'Daftar Ulang Dikembalikan');
                    });
            });

        if ($request->has('search') && !empty($request->search['value'])) {
            $search = $request->search['value'];
            $query = DB::table('rekomendasi_terdaftar_yayasans')
                ->join('users', 'users.id', '=', 'rekomendasi_terdaftar_yayasans.petugas')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_terdaftar_yayasans.id_kecamatan')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_terdaftar_yayasans.id_kelurahan')
                ->select('rekomendasi_terdaftar_yayasans.*', 'b.name_village', 'd.name_districts', 'users.name')
                ->where(function ($query) use ($search) {
                    $query->where('rekomendasi_terdaftar_yayasans.status_alur', '=', 'Selesai');
                    $query->where('rekomendasi_terdaftar_yayasans.no_pendaftaran', 'like', "%$search%");
                });
        }
        // Get total count of filtered items
        $total_filtered_items = $query->count();
        // dd($total_filtered_items);
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
            $query = DB::table('rekomendasi_terdaftar_yayasans')
                ->join('users', 'users.id', '=', 'rekomendasi_terdaftar_yayasans.petugas')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_terdaftar_yayasans.id_kecamatan')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_terdaftar_yayasans.id_kelurahan')
                ->select('rekomendasi_terdaftar_yayasans.*', 'b.name_village', 'd.name_districts', 'users.name');
      
        } elseif ($user_wilayah->name == 'Front Office kota') {
            $query = DB::table('rekomendasi_terdaftar_yayasans')
                ->join('users', 'users.id', '=', 'rekomendasi_terdaftar_yayasans.petugas')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_terdaftar_yayasans.id_kecamatan')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_terdaftar_yayasans.id_kelurahan')
                ->select('rekomendasi_terdaftar_yayasans.*', 'b.name_village', 'd.name_districts', 'users.name');
        } elseif ($user_wilayah->name == 'Front Office Kelurahan') {
            $query = DB::table('rekomendasi_terdaftar_yayasans')
                ->join('users', 'users.id', '=', 'rekomendasi_terdaftar_yayasans.petugas')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_terdaftar_yayasans.id_kecamatan')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_terdaftar_yayasans.id_kelurahan')
                ->select('rekomendasi_terdaftar_yayasans.*', 'b.name_village', 'd.name_districts', 'users.name');
                // dd($query);
        } elseif ($user_wilayah->name == 'supervisor') {
            $query = DB::table('rekomendasi_terdaftar_yayasans')
                ->join('users', 'users.id', '=', 'rekomendasi_terdaftar_yayasans.petugas')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_terdaftar_yayasans.id_kecamatan')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_terdaftar_yayasans.id_kelurahan')
                ->select('rekomendasi_terdaftar_yayasans.*', 'b.name_village', 'd.name_districts', 'users.name');
        } elseif ($user_wilayah->name == 'Back Ofiice kelurahan') {
            $query = DB::table('rekomendasi_terdaftar_yayasans')
                ->join('users', 'users.id', '=', 'rekomendasi_terdaftar_yayasans.petugas')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_terdaftar_yayasans.id_kecamatan')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_terdaftar_yayasans.id_kelurahan')
                ->select('rekomendasi_terdaftar_yayasans.*', 'b.name_village', 'd.name_districts', 'users.name');
        } elseif ($user_wilayah->name == 'Back Ofiice Kota') {
            $query = DB::table('rekomendasi_terdaftar_yayasans')
                ->join('users', 'users.id', '=', 'rekomendasi_terdaftar_yayasans.petugas')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_terdaftar_yayasans.id_kecamatan')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_terdaftar_yayasans.id_kelurahan')
                ->select('rekomendasi_terdaftar_yayasans.*', 'b.name_village', 'd.name_districts', 'users.name');
                // dd($query);
        } elseif ($user_wilayah->name == 'kepala bidang') {
            $query = DB::table('rekomendasi_terdaftar_yayasans')
                ->join('users', 'users.id', '=', 'rekomendasi_terdaftar_yayasans.petugas')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_terdaftar_yayasans.id_kecamatan')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_terdaftar_yayasans.id_kelurahan')
                ->select('rekomendasi_terdaftar_yayasans.*', 'b.name_village', 'd.name_districts', 'users.name');
        } elseif ($user_wilayah->name == 'SekertarisDinas') {
            $query = DB::table('rekomendasi_terdaftar_yayasans')
                ->join('users', 'users.id', '=', 'rekomendasi_terdaftar_yayasans.petugas')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_terdaftar_yayasans.id_kecamatan')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_terdaftar_yayasans.id_kelurahan')
                ->select('rekomendasi_terdaftar_yayasans.*', 'b.name_village', 'd.name_districts', 'users.name');
        } elseif ($user_wilayah->name == 'KepalaDinas') {
            $query = DB::table('rekomendasi_terdaftar_yayasans')
                ->join('users', 'users.id', '=', 'rekomendasi_terdaftar_yayasans.petugas')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_terdaftar_yayasans.id_kecamatan')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_terdaftar_yayasans.id_kelurahan')
                ->select('rekomendasi_terdaftar_yayasans.*', 'b.name_village', 'd.name_districts', 'users.name');
        } else {
            $query = DB::table('rekomendasi_terdaftar_yayasans')
                ->join('users', 'users.id', '=', 'rekomendasi_terdaftar_yayasans.createdby')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_terdaftar_yayasans.id_kelurahan')
                ->select('rekomendasi_terdaftar_yayasans.*', 'b.name_village');
        }
        if ($user_wilayah->name == 'fasilitator') {
            $query->orWhere(function ($query) use ($user_wilayah) {
                $query->where('rekomendasi_terdaftar_yayasans.id_kelurahan', '=', $user_wilayah->kelurahan_id)
                    ->where('rekomendasi_terdaftar_yayasans.tujuan', '=', $user_wilayah->role_id)
                    ->where('rekomendasi_terdaftar_yayasans.petugas', '=', auth::user()->id)

                    ->where(function ($query) {
                        $query->where('rekomendasi_terdaftar_yayasans.keterangan_daftar_ulang', '=', 'Daftar Ulang Diteruskan')
                            ->orWhere('rekomendasi_terdaftar_yayasans.keterangan_daftar_ulang', '=', 'Daftar Ulang Dikembalikan');
                    });
            });
        }
        if ($user_wilayah->name == 'Front Office Kelurahan') {
            $query->orWhere(function ($query) use ($user_wilayah) {
                $query->where('rekomendasi_terdaftar_yayasans.id_kelurahan', '=', $user_wilayah->kelurahan_id)
                    ->where('rekomendasi_terdaftar_yayasans.tujuan', '=', $user_wilayah->role_id)
                    ->where('rekomendasi_terdaftar_yayasans.petugas', '=', auth::user()->id)

                    ->where(function ($query) {
                        $query->where('rekomendasi_terdaftar_yayasans.keterangan_daftar_ulang', '=', 'Daftar Ulang Diteruskan')
                            ->orWhere('rekomendasi_terdaftar_yayasans.keterangan_daftar_ulang', '=', 'Daftar Ulang Dikembalikan');
                    });
            });
        }
        if ($user_wilayah->name == 'Front Office kota') {
            $query->orWhere(function ($query) use ($user_wilayah) {
                $query->where('rekomendasi_terdaftar_yayasans.id_kabkot', '=', $user_wilayah->kota_id)
                    ->where('rekomendasi_terdaftar_yayasans.tujuan', '=', $user_wilayah->role_id)
                    ->where('rekomendasi_terdaftar_yayasans.petugas', '=', auth::user()->id)

                    ->where(function ($query) {
                        $query->where('rekomendasi_terdaftar_yayasans.keterangan_daftar_ulang', '=', 'Daftar Ulang Diteruskan')
                            ->orWhere('rekomendasi_terdaftar_yayasans.keterangan_daftar_ulang', '=', 'Daftar Ulang Dikembalikan');
                    });
            });
        }
        if ($user_wilayah->name == 'supervisor') {
            $query->orWhere(function ($query) use ($user_wilayah) {
                $query->where('rekomendasi_terdaftar_yayasans.id_kabkot', '=', $user_wilayah->kota_id)
                    ->where('rekomendasi_terdaftar_yayasans.tujuan', '=', $user_wilayah->role_id)
                    ->where('rekomendasi_terdaftar_yayasans.petugas', '=', auth::user()->id)
                    ->where(function ($query) {
                        $query->where('rekomendasi_terdaftar_yayasans.keterangan_daftar_ulang', '=', 'Daftar Ulang Diteruskan')
                            ->orWhere('rekomendasi_terdaftar_yayasans.keterangan_daftar_ulang', '=', 'Daftar Ulang Dikembalikan');
                    });
                // dd($va);
            });
        }



        if ($user_wilayah->name == 'Back Ofiice Kota') {
            $query->orWhere(function ($query) use ($user_wilayah) {
                $query->where('rekomendasi_terdaftar_yayasans.id_kabkot', '=', $user_wilayah->kota_id)
                    ->where('rekomendasi_terdaftar_yayasans.tujuan', '=', $user_wilayah->role_id)
                    ->where('rekomendasi_terdaftar_yayasans.petugas', '=', auth::user()->id)
                    ->where(function ($query) {
                        $query->where('rekomendasi_terdaftar_yayasans.keterangan_daftar_ulang', '=', 'Daftar Ulang Diteruskan')
                            ->orWhere('rekomendasi_terdaftar_yayasans.keterangan_daftar_ulang', '=', 'Daftar Ulang Dikembalikan');
                    });
                // dd($va);
            });
            // dd($query->count());
        }
        if ($user_wilayah->name == 'Back Ofiice kelurahan') {
            $query->orWhere(function ($query) use ($user_wilayah) {
                $query->where('rekomendasi_terdaftar_yayasans.id_kelurahan', '=', $user_wilayah->kelurahan_id)
                    ->where('rekomendasi_terdaftar_yayasans.tujuan', '=', $user_wilayah->role_id)
                    ->where('rekomendasi_terdaftar_yayasans.petugas', '=', auth::user()->id)
                    ->where(function ($query) {
                        $query->where('rekomendasi_terdaftar_yayasans.keterangan_daftar_ulang', '=', 'Daftar Ulang Diteruskan')
                            ->orWhere('rekomendasi_terdaftar_yayasans.keterangan_daftar_ulang', '=', 'Daftar Ulang Dikembalikan');
                    });
                // dd($va);
            });
            // dd($query->count());
        }
        if ($user_wilayah->name == 'kepala bidang') {
            $query->orWhere(function ($query) use ($user_wilayah) {
                $query->where('rekomendasi_terdaftar_yayasans.id_kabkot', '=', $user_wilayah->kota_id)
                    ->where('rekomendasi_terdaftar_yayasans.tujuan', '=', $user_wilayah->role_id)
                    ->where('rekomendasi_terdaftar_yayasans.petugas', '=', auth::user()->id)
                    ->where(function ($query) {
                        $query->where('rekomendasi_terdaftar_yayasans.keterangan_daftar_ulang', '=', 'Daftar Ulang Diteruskan')
                            ->orWhere('rekomendasi_terdaftar_yayasans.keterangan_daftar_ulang', '=', 'Daftar Ulang Dikembalikan');
                    });
                // dd($va);
            });
        }
        if ($user_wilayah->name == 'SekertarisDinas') {
            $query->orWhere(function ($query) use ($user_wilayah) {
                $query->where('rekomendasi_terdaftar_yayasans.id_kabkot', '=', $user_wilayah->kota_id)
                    ->where('rekomendasi_terdaftar_yayasans.tujuan', '=', $user_wilayah->role_id)
                    ->where('rekomendasi_terdaftar_yayasans.petugas', '=', auth::user()->id)
                    ->where(function ($query) {
                        $query->where('rekomendasi_terdaftar_yayasans.keterangan_daftar_ulang', '=', 'Daftar Ulang Diteruskan')
                            ->orWhere('rekomendasi_terdaftar_yayasans.keterangan_daftar_ulang', '=', 'Daftar Ulang Dikembalikan');
                    });
                // dd($va);
            });
        }
        if ($user_wilayah->name == 'KepalaDinas') {
            $query->orWhere(function ($query) use ($user_wilayah) {
                $query->where('rekomendasi_terdaftar_yayasans.id_kabkot', '=', $user_wilayah->kota_id)
                    ->where('rekomendasi_terdaftar_yayasans.tujuan', '=', $user_wilayah->role_id)
                    ->where('rekomendasi_terdaftar_yayasans.petugas', '=', auth::user()->id)
                    ->where(function ($query) {
                        $query->where('rekomendasi_terdaftar_yayasans.keterangan_daftar_ulang', '=', 'Daftar Ulang Diteruskan')
                            ->orWhere('rekomendasi_terdaftar_yayasans.keterangan_daftar_ulang', '=', 'Daftar Ulang Dikembalikan');
                    });
                // dd($va);
            });
        }
        if ($user_wilayah->name == 'Front Office Kelurahan' || $user_wilayah->name == 'Back Ofiice kelurahan'|| $user_wilayah->name == 'fasilitator') {
            if ($request->has('search') && !empty($request->search['value'])) {
                $search = $request->search['value'];
                            $query->orWhere(function ($query) use ($user_wilayah) {
                                $query->where('rekomendasi_terdaftar_yayasans.id_kelurahan', '=', $user_wilayah->kelurahan_id)
                                    ->where('rekomendasi_terdaftar_yayasans.tujuan', '=', $user_wilayah->role_id)
                                    ->where('rekomendasi_terdaftar_yayasans.petugas', '=', auth::user()->id)
                                    ->where(function ($query) {
                                        $query->where('rekomendasi_terdaftar_yayasans.keterangan_daftar_ulang', '=', 'Daftar Ulang Diteruskan')
                                            ->orWhere('rekomendasi_terdaftar_yayasans.keterangan_daftar_ulang', '=', 'Daftar Ulang Dikembalikan');
                                    });
                                // dd($va);
                            })->where(function ($query) use ($search) {
                                $query->where('rekomendasi_terdaftar_yayasans.no_pendaftaran', 'like', "%$search%");
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
                $query->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_terdaftar_yayasans.id_kabkot', '=', $user_wilayah->kota_id)
                        ->where('rekomendasi_terdaftar_yayasans.tujuan', '=', $user_wilayah->role_id)
                        ->where('rekomendasi_terdaftar_yayasans.petugas', '=', auth::user()->id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_terdaftar_yayasans.keterangan_daftar_ulang', '=', 'Daftar Ulang Diteruskan')
                                ->orWhere('rekomendasi_terdaftar_yayasans.keterangan_daftar_ulang', '=', 'Daftar Ulang Dikembalikan');
                        });
                    // dd($va);
                })->where(function ($query) use ($search) {
                    $query->where('rekomendasi_terdaftar_yayasans.no_pendaftaran', 'like', "%$search%");
                        // ->orwhere('indonesia_villages.name_village', 'like', "%$search%")
                        // ->orwhere('d.name_districts', 'like', "%$search%")
                        // ->orwhere('indonesia_villages.name_village', 'like', "%$search%")
                        // ->orwhere('rekomendasi_biaya_perawatans.alamat_biper', 'like', "%$search%");
                });
            }
        }
        // Get total count of filtered items
        $total_filtered_items = $query->count();
        // dd($total_filtered_items);
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

    public function teruskan(Request $request)
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
            ->select('wilayahs.*','roles.name','model_has_roles.*')
            ->where('wilayahs.createdby', $user_id)
            ->where(function ($query) {
                $query->where('status_wilayah', 1);
            })
            ->first();
        if ($user_wilayah->name == 'fasilitator') {
            $query = DB::table('rekomendasi_terdaftar_yayasans')
                ->join('users', 'users.id', '=', 'rekomendasi_terdaftar_yayasans.petugas')
                // ->join('log_ulangyayasan', 'log_ulangyayasan.id_trx_ulangYayasan_ulangYayasan', '=', 'rekomendasi_terdaftar_yayasans.id')
                // ->join('model_has_roles', 'model_has_roles.model_id', '=', 'rekomendasi_terdaftar_yayasans.tujuan')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_terdaftar_yayasans.id_kelurahan')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_terdaftar_yayasans.id_kecamatan')
                ->select('rekomendasi_terdaftar_yayasans.*', 'b.name_village', 'd.name_districts','users.name')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_terdaftar_yayasans.id_kelurahan', $user_wilayah->kelurahan_id)
                        ->where('rekomendasi_terdaftar_yayasans.tujuan', '!=', $user_wilayah->role_id)
                        // ->where('log_ulangyayasan.created_by_ulangYayasan', '=', auth::user()->id)
                        // ->where('rekomendasi_terdaftar_yayasans.petugas','!=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_terdaftar_yayasans.keterangan_daftar_ulang', '=', 'Daftar Ulang Diteruskan')
                                ->orWhere('rekomendasi_terdaftar_yayasans.keterangan_daftar_ulang', '=', 'Daftar Ulang Dikembalikan');
                        });
                })->distinct();
            // dd($query);

        }
        if ($user_wilayah->name == 'Front Office Kelurahan') {
            $query = DB::table('rekomendasi_terdaftar_yayasans')
                ->join('users', 'users.id', '=', 'rekomendasi_terdaftar_yayasans.petugas')
                // ->join('log_ulangyayasan', 'log_ulangyayasan.id_trx_ulangYayasan', '=', 'rekomendasi_terdaftar_yayasans.id')
                // ->join('model_has_roles', 'model_has_roles.model_id', '=', 'rekomendasi_terdaftar_yayasans.tujuan')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_terdaftar_yayasans.id_kelurahan')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_terdaftar_yayasans.id_kecamatan')
                ->select('rekomendasi_terdaftar_yayasans.*', 'b.name_village', 'd.name_districts', 'users.name')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_terdaftar_yayasans.id_kelurahan', $user_wilayah->kelurahan_id)
                        ->where('rekomendasi_terdaftar_yayasans.tujuan', '!=', $user_wilayah->role_id)
                        // ->where('log_ulangyayasan.created_by_ulangYayasan', '=', auth::user()->id)
                        // ->where('rekomendasi_terdaftar_yayasans.petugas','!=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_terdaftar_yayasans.keterangan_daftar_ulang', '=', 'Daftar Ulang Diteruskan')
                                ->orWhere('rekomendasi_terdaftar_yayasans.keterangan_daftar_ulang', '=', 'Daftar Ulang Dikembalikan');
                        });
                })->distinct();
            // dd($query);

        }
        if ($user_wilayah->name == 'Front Office kota') {
            // dd($user_wilayah);
            $query = DB::table('rekomendasi_terdaftar_yayasans')
                ->join('users', 'users.id', '=', 'rekomendasi_terdaftar_yayasans.petugas')
                // ->join('log_ulangyayasan', 'log_ulangyayasan.id_trx_ulangYayasan', '=', 'rekomendasi_terdaftar_yayasans.id')
                // ->join('model_has_roles', 'model_has_roles.model_id', '=', 'rekomendasi_terdaftar_yayasans.tujuan')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_terdaftar_yayasans.id_kelurahan')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_terdaftar_yayasans.id_kecamatan')
                ->select('rekomendasi_terdaftar_yayasans.*', 'b.name_village', 'd.name_districts','users.name')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_terdaftar_yayasans.id_kabkot', $user_wilayah->kota_id)
                        ->where('rekomendasi_terdaftar_yayasans.tujuan', '!=', $user_wilayah->role_id)
                        // ->where('log_ulangyayasan.created_by_ulangYayasan', '=', auth::user()->id);
                        // ->where('rekomendasi_terdaftar_yayasans.petugas','!=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_terdaftar_yayasans.keterangan_daftar_ulang', '=', 'Daftar Ulang Diteruskan')
                                ->orWhere('rekomendasi_terdaftar_yayasans.keterangan_daftar_ulang', '=', 'Daftar Ulang Dikembalikan');
                        });
                })->distinct();
            // dd($query);

        }

        //Back office kota 
        if ($user_wilayah->name == 'Back Ofiice Kota') {
            // dd($user_wilayah->model_id);
            $query = DB::table('rekomendasi_terdaftar_yayasans')
                ->join('users', 'users.id', '=', 'rekomendasi_terdaftar_yayasans.petugas')
                // ->join('log_ulangyayasan', 'log_ulangyayasan.id_trx_ulangYayasan', '=', 'rekomendasi_terdaftar_yayasans.id')
                // ->join('model_has_roles', 'model_has_roles.model_id', '=', 'rekomendasi_terdaftar_yayasans.tujuan')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_terdaftar_yayasans.id_kelurahan')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_terdaftar_yayasans.id_kecamatan')
                ->select('rekomendasi_terdaftar_yayasans.*', 'b.name_village', 'd.name_districts', 'users.name')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_terdaftar_yayasans.id_kabkot', $user_wilayah->kota_id)
                        ->where('rekomendasi_terdaftar_yayasans.tujuan', '!=', $user_wilayah->role_id)
                        // ->where('log_ulangyayasan.created_by_ulangYayasan', '=', auth::user()->id)
                        // ->where('rekomendasi_terdaftar_yayasans.petugas','!=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_terdaftar_yayasans.keterangan_daftar_ulang', '=', 'Daftar Ulang Diteruskan')
                                ->orWhere('rekomendasi_terdaftar_yayasans.keterangan_daftar_ulang', '=', 'Daftar Ulang Dikembalikan');
                        });
                })->distinct();
        }
        if ($user_wilayah->name == 'Back Ofiice kelurahan') {
            // dd($user_wilayah->model_id);
            $query = DB::table('rekomendasi_terdaftar_yayasans')
                ->join('users', 'users.id', '=', 'rekomendasi_terdaftar_yayasans.petugas')
                // ->join('log_ulangyayasan', 'log_ulangyayasan.id_trx_ulangYayasan', '=', 'rekomendasi_terdaftar_yayasans.id')
                // ->join('model_has_roles', 'model_has_roles.model_id', '=', 'rekomendasi_terdaftar_yayasans.tujuan')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_terdaftar_yayasans.id_kelurahan')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_terdaftar_yayasans.id_kecamatan')
                ->select('rekomendasi_terdaftar_yayasans.*', 'b.name_village', 'd.name_districts', 'users.name')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_terdaftar_yayasans.id_kelurahan', $user_wilayah->kelurahan_id)
                        ->where('log_ulangyayasan.tujuan_ulangYayasan', '!=', $user_wilayah->role_id)
                        // ->where('log_ulangyayasan.created_by_ulangYayasan', '=', auth::user()->id)
                        // ->where('rekomendasi_terdaftar_yayasans.petugas','!=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_terdaftar_yayasans.keterangan_daftar_ulang', '=', 'Daftar Ulang Diteruskan')
                                ->orWhere('rekomendasi_terdaftar_yayasans.keterangan_daftar_ulang', '=', 'Daftar Ulang Dikembalikan');
                        });
                })->distinct();
        }

        //Back office kota 
        if ($user_wilayah->name == 'supervisor') {
            // dd($user_wilayah->model_id);
            $query = DB::table('rekomendasi_terdaftar_yayasans')
            ->join('users', 'users.id', '=', 'rekomendasi_terdaftar_yayasans.petugas')
            // ->join('log_ulangyayasan', 'log_ulangyayasan.id_trx_ulangYayasan', '=', 'rekomendasi_terdaftar_yayasans.id')
            // ->join('model_has_roles', 'model_has_roles.model_id', '=', 'rekomendasi_terdaftar_yayasans.tujuan')
            ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_terdaftar_yayasans.id_kelurahan')
            ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_terdaftar_yayasans.id_kecamatan')
            ->select('rekomendasi_terdaftar_yayasans.*', 'b.name_village', 'd.name_districts', 'users.name')
            ->orWhere(function ($query) use ($user_wilayah) {
                $query->where('rekomendasi_terdaftar_yayasans.id_kabkot', $user_wilayah->kota_id)
                    ->where('rekomendasi_terdaftar_yayasans.tujuan', '!=', $user_wilayah->role_id)
                    // ->where('log_ulangyayasan.created_by_ulangYayasan', '=', auth::user()->id)
                    // ->where('rekomendasi_terdaftar_yayasans.petugas','!=', $user_wilayah->model_id)
                    ->where(function ($query) {
                        $query->where('rekomendasi_terdaftar_yayasans.keterangan_daftar_ulang', '=', 'Daftar Ulang Diteruskan')
                            ->orWhere('rekomendasi_terdaftar_yayasans.keterangan_daftar_ulang', '=', 'Daftar Ulang Dikembalikan');
                    });
            })->distinct();
        }

        if ($user_wilayah->name == 'kepala bidang') {
            // dd( $user_wilayah->role_id);
            $query = DB::table('rekomendasi_terdaftar_yayasans')
            ->join('users', 'users.id', '=', 'rekomendasi_terdaftar_yayasans.petugas')
            // ->join('log_ulangyayasan', 'log_ulangyayasan.id_trx_ulangYayasan', '=', 'rekomendasi_terdaftar_yayasans.id')
            // ->join('model_has_roles', 'model_has_roles.model_id', '=', 'rekomendasi_terdaftar_yayasans.tujuan')
            ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_terdaftar_yayasans.id_kelurahan')
            ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_terdaftar_yayasans.id_kecamatan')
            ->select('rekomendasi_terdaftar_yayasans.*', 'b.name_village', 'd.name_districts', 'users.name')
            ->orWhere(function ($query) use ($user_wilayah) {
                $query->where('rekomendasi_terdaftar_yayasans.id_kabkot', $user_wilayah->kota_id)
                    ->where('rekomendasi_terdaftar_yayasans.tujuan', '!=', $user_wilayah->role_id)
                    // ->where('log_ulangyayasan.created_by_ulangYayasan', '=', auth::user()->id)
                    // ->where('rekomendasi_terdaftar_yayasans.petugas','!=', $user_wilayah->model_id)
                    ->where(function ($query) {
                        $query->where('rekomendasi_terdaftar_yayasans.keterangan_daftar_ulang', '=', 'Daftar Ulang Diteruskan')
                            ->orWhere('rekomendasi_terdaftar_yayasans.keterangan_daftar_ulang', '=', 'Daftar Ulang Dikembalikan');
                    });
            })->distinct();
        }
        if ($user_wilayah->name == 'SekertarisDinas') {
            // dd($user_wilayah);
            $query = DB::table('rekomendasi_terdaftar_yayasans')
                ->join('users', 'users.id', '=', 'rekomendasi_terdaftar_yayasans.petugas')
                // ->join('log_ulangyayasan', 'log_ulangyayasan.id_trx_ulangYayasan', '=', 'rekomendasi_terdaftar_yayasans.id')
                // ->join('model_has_roles', 'model_has_roles.model_id', '=', 'rekomendasi_terdaftar_yayasans.tujuan')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_terdaftar_yayasans.id_kelurahan')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_terdaftar_yayasans.id_kecamatan')
                ->select('rekomendasi_terdaftar_yayasans.*', 'b.name_village', 'd.name_districts', 'users.name')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_terdaftar_yayasans.id_kabkot', $user_wilayah->kota_id)
                        ->where('rekomendasi_terdaftar_yayasans.tujuan', '!=', $user_wilayah->role_id)
                        // ->where('log_ulangyayasan.created_by_ulangYayasan', '=', auth::user()->id)
                        // ->where('rekomendasi_terdaftar_yayasans.petugas','!=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_terdaftar_yayasans.keterangan_daftar_ulang', '=', 'Daftar Ulang Diteruskan')
                                ->orWhere('rekomendasi_terdaftar_yayasans.keterangan_daftar_ulang', '=', 'Daftar Ulang Dikembalikan');
                        });
                })->distinct();
        }
        if ($user_wilayah->name == 'KepalaDinas') {
            //  dd(auth::user()->id);
            $query = DB::table('rekomendasi_terdaftar_yayasans')
            ->join('users', 'users.id', '=', 'rekomendasi_terdaftar_yayasans.petugas')
            // ->join('log_ulangyayasan', 'log_ulangyayasan.id_trx_ulangYayasan', '=', 'rekomendasi_terdaftar_yayasans.id')
            // ->join('model_has_roles', 'model_has_roles.model_id', '=', 'rekomendasi_terdaftar_yayasans.tujuan')
            ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_terdaftar_yayasans.id_kelurahan')
            ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_terdaftar_yayasans.id_kecamatan')
            ->select('rekomendasi_terdaftar_yayasans.*', 'b.name_village', 'd.name_districts', 'users.name')
            ->orWhere(function ($query) use ($user_wilayah) {
                $query->where('rekomendasi_terdaftar_yayasans.id_kabkot', $user_wilayah->kota_id)
                    ->where('rekomendasi_terdaftar_yayasans.tujuan', '!=', $user_wilayah->role_id)
                    // ->where('log_ulangyayasan.created_by_ulangYayasan', '=', auth::user()->id)
                    // ->where('rekomendasi_terdaftar_yayasans.petugas','!=', $user_wilayah->model_id)
                    ->where(function ($query) {
                        $query->where('rekomendasi_terdaftar_yayasans.keterangan_daftar_ulang', '=', 'Daftar Ulang Diteruskan')
                            ->orWhere('rekomendasi_terdaftar_yayasans.keterangan_daftar_ulang', '=', 'Daftar Ulang Dikembalikan');
                    });
            })->distinct();
        }
        if ($user_wilayah->name == 'Front Office Kelurahan' || $user_wilayah->name == 'Back Ofiice kelurahan'|| $user_wilayah->name == 'fasilitator') {
            if ($request->has('search') && !empty($request->search['value'])) {
                $search = $request->search['value'];
                $query = DB::table('rekomendasi_terdaftar_yayasans')
                ->join('users', 'users.id', '=', 'rekomendasi_terdaftar_yayasans.petugas')
                ->join('log_ulangyayasan', 'log_ulangyayasan.id_trx_ulangYayasan', '=', 'rekomendasi_terdaftar_yayasans.id')
                // ->join('model_has_roles', 'model_has_roles.model_id', '=', 'rekomendasi_terdaftar_yayasans.tujuan')
                ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_terdaftar_yayasans.id_kelurahan')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_terdaftar_yayasans.id_kecamatan')
                ->select('rekomendasi_terdaftar_yayasans.*', 'b.name_village', 'd.name_districts', 'log_ulangyayasan.tujuan_ulangYayasan', 'log_ulangyayasan.petugas_ulangYayasan', 'users.name')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_terdaftar_yayasans.id_kelurahan', $user_wilayah->kelurahan_id)
                        ->where('rekomendasi_terdaftar_yayasans.tujuan', '!=', $user_wilayah->role_id)
                        // ->where('log_ulangyayasan.created_by_ulangYayasan', '=', auth::user()->id)
                        // ->where('rekomendasi_terdaftar_yayasans.petugas','!=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_terdaftar_yayasans.keterangan_daftar_ulang', '=', 'Daftar Ulang Diteruskan')
                                ->orWhere('rekomendasi_terdaftar_yayasans.keterangan_daftar_ulang', '=', 'Daftar Ulang Dikembalikan');
                        });
                    })->where(function ($query) use ($search) {
                        $query->where('rekomendasi_terdaftar_yayasans.no_pendaftaran', 'like', "%$search%");
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
                $query = DB::table('rekomendasi_terdaftar_yayasans')
                ->join('users', 'users.id', 'rekomendasi_terdaftar_yayasans.petugas')
                // ->join('log_ulangyayasan', 'log_ulangyayasan.id_trx_ulangYayasan', '=', 'rekomendasi_terdaftar_yayasans.id')
                ->join('indonesia_villages', 'indonesia_villages.code', '=', 'rekomendasi_terdaftar_yayasans.id_kelurahan')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_terdaftar_yayasans.id_kecamatan')
                ->select('rekomendasi_terdaftar_yayasans.*', 'users.name', 'd.name_districts', 'indonesia_villages.name_village')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_terdaftar_yayasans.id_kabkot', $user_wilayah->kota_id)
                        // ->where('log_ulangyayasan.tujuan_ulangYayasan','=', $user_wilayah->role_id)
                        // ->where('log_ulangyayasan.created_by_ulangYayasan','!=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_terdaftar_yayasans.keterangan_daftar_ulang', '=', 'Daftar Ulang Ditolak')
                               ->orWhere('rekomendasi_terdaftar_yayasans.keterangan_daftar_ulang', '=', 'Daftar Ulang Selesai');                        // 
                        });
                    })->where(function ($query) use ($search) {
                        $query->where('rekomendasi_terdaftar_yayasans.no_pendaftaran', 'like', "%$search%");
                            // ->orwhere('indonesia_villages.name_village', 'like', "%$search%")
                            // ->orwhere('d.name_districts', 'like', "%$search%")
                            // ->orwhere('indonesia_villages.name_village', 'like', "%$search%")
                            // ->orwhere('rekomendasi_biaya_perawatans.alamat_biper', 'like', "%$search%");
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
            'recordsTotal' => rekomendasi_terdaftar_yayasan::count(),
            'recordsFiltered' => $total_filtered_items,
            'data' => $data,
        ]);
    }

    public function selesai(Request $request)
    {
        $user_name = Auth::user()->name;
        // dd($user_name);
        $query = DB::table('rekomendasi_terdaftar_yayasans')
            ->join('users', 'users.id', 'rekomendasi_terdaftar_yayasans.petugas')
            ->join('log_ulangyayasan', 'log_ulangyayasan.id_trx_ulangYayasan', '=', 'rekomendasi_terdaftar_yayasans.id')
            ->join('roles', 'roles.id', '=', 'rekomendasi_terdaftar_yayasans.tujuan')
            ->join('indonesia_villages as b', 'b.code', '=', 'rekomendasi_terdaftar_yayasans.id_kelurahan')
            ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_terdaftar_yayasans.id_kecamatan')
            ->select('rekomendasi_terdaftar_yayasans.*', 'roles.name', 'users.name', 'b.name_village', 'd.name_districts');
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
            $query = DB::table('rekomendasi_terdaftar_yayasans')
                ->join('users', 'users.id', 'rekomendasi_terdaftar_yayasans.petugas')
                // ->join('log_ulangyayasan', 'log_ulangyayasan.id_trx_ulangYayasan', '=', 'rekomendasi_terdaftar_yayasans.id')
                ->join('indonesia_villages', 'indonesia_villages.code', '=', 'rekomendasi_terdaftar_yayasans.id_kelurahan')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_terdaftar_yayasans.id_kecamatan')
                ->select('rekomendasi_terdaftar_yayasans.*', 'users.name', 'd.name_districts', 'indonesia_villages.name_village')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_terdaftar_yayasans.id_kelurahan', $user_wilayah->kelurahan_id)
                        // ->where('log_ulangyayasan.tujuan_ulangYayasan','=', $user_wilayah->role_id);
                        // ->where('log_ulangyayasan.created_by_ulangYayasan','!=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_terdaftar_yayasans.keterangan_daftar_ulang', '=', 'Daftar Ulang Ditolak')
                                ->orWhere('rekomendasi_terdaftar_yayasans.keterangan_daftar_ulang', '=', 'Daftar Ulang Selesai');
                                
                        });
                })->distinct();
        } elseif ($user_wilayah->name == 'Front Office Kelurahan') {
            //  dd($user_wilayah->role_id);
            $query = DB::table('rekomendasi_terdaftar_yayasans')
                ->join('users', 'users.id', 'rekomendasi_terdaftar_yayasans.petugas')
                // ->join('log_ulangyayasan', 'log_ulangyayasan.id_trx_ulangYayasan', '=', 'rekomendasi_terdaftar_yayasans.id')
                ->join('indonesia_villages', 'indonesia_villages.code', '=', 'rekomendasi_terdaftar_yayasans.id_kelurahan')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_terdaftar_yayasans.id_kecamatan')
                ->select('rekomendasi_terdaftar_yayasans.*', 'users.name', 'd.name_districts', 'indonesia_villages.name_village')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_terdaftar_yayasans.id_kelurahan', $user_wilayah->kelurahan_id)
                        // ->where('log_ulangyayasan.tujuan_ulangYayasan','=', $user_wilayah->role_id);
                        // ->where('log_ulangyayasan.created_by_ulangYayasan','!=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_terdaftar_yayasans.keterangan_daftar_ulang', '=', 'Daftar Ulang Ditolak')
                                ->orWhere('rekomendasi_terdaftar_yayasans.keterangan_daftar_ulang', '=', 'Daftar Ulang Selesai');
                                
                        });
                })->distinct();
            // dd($query); 
        } elseif ($user_wilayah->name == 'Front Office kota') {
            //  dd($user_wilayah->role_id);
            $query = DB::table('rekomendasi_terdaftar_yayasans')
                ->join('users', 'users.id', 'rekomendasi_terdaftar_yayasans.petugas')
                // ->join('log_ulangyayasan', 'log_ulangyayasan.id_trx_ulangYayasan', '=', 'rekomendasi_terdaftar_yayasans.id')
                ->join('indonesia_villages', 'indonesia_villages.code', '=', 'rekomendasi_terdaftar_yayasans.id_kelurahan')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_terdaftar_yayasans.id_kecamatan')
                ->select('rekomendasi_terdaftar_yayasans.*', 'users.name', 'd.name_districts', 'indonesia_villages.name_village')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_terdaftar_yayasans.id_kabkot', $user_wilayah->kota_id)
                            ->Where('rekomendasi_terdaftar_yayasans.status_alur', '=', 'Selesai')
                            ->orwhere('rekomendasi_terdaftar_yayasans.keterangan_daftar_ulang', '=', 'Daftar Ulang Ditolak')
                            ->orWhere('rekomendasi_terdaftar_yayasans.keterangan_daftar_ulang', '=', 'Daftar Ulang Selesai');
                })->distinct();
            // dd($query);
        } elseif ($user_wilayah->name == 'Back Ofiice kelurahan') {
            // dd($user_wilayah);
            $query = DB::table('rekomendasi_terdaftar_yayasans')
                ->join('users', 'users.id', 'rekomendasi_terdaftar_yayasans.petugas')
                ->join('indonesia_villages', 'indonesia_villages.code', '=', 'rekomendasi_terdaftar_yayasans.id_kelurahan')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_terdaftar_yayasans.id_kecamatan')
                ->select('rekomendasi_terdaftar_yayasans.*', 'users.name', 'd.name_districts', 'indonesia_villages.name_village')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_terdaftar_yayasans.id_kelurahan', $user_wilayah->kelurahan_id)
                        // ->where('log_ulangyayasan.tujuan_ulangYayasan','=', $user_wilayah->role_id)
                        // ->where('log_ulangyayasan.created_by_ulangYayasan','!=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_terdaftar_yayasans.keterangan_daftar_ulang', '=', 'Daftar Ulang Ditolak')
                               ->orWhere('rekomendasi_terdaftar_yayasans.keterangan_daftar_ulang', '=', 'Daftar Ulang Selesai');
                        });
                })->distinct();
            // dd($query); 
        } elseif ($user_wilayah->name == 'kepala bidang') {
            // dd($user_wilayah);
            $query = DB::table('rekomendasi_terdaftar_yayasans')
                ->join('users', 'users.id', 'rekomendasi_terdaftar_yayasans.petugas')
                ->join('indonesia_villages', 'indonesia_villages.code', '=', 'rekomendasi_terdaftar_yayasans.id_kelurahan')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_terdaftar_yayasans.id_kecamatan')
                ->select('rekomendasi_terdaftar_yayasans.*', 'users.name', 'd.name_districts', 'indonesia_villages.name_village')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_terdaftar_yayasans.id_kabkot', $user_wilayah->kota_id)
                        // ->where('log_ulangyayasan.tujuan_ulangYayasan','=', $user_wilayah->role_id)
                        // ->where('log_ulangyayasan.created_by_ulangYayasan','!=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_terdaftar_yayasans.keterangan_daftar_ulang', '=', 'Daftar Ulang Ditolak')
                               ->orWhere('rekomendasi_terdaftar_yayasans.keterangan_daftar_ulang', '=', 'Daftar Ulang Selesai');
                        });
                })->distinct();
        } elseif ($user_wilayah->name == 'Back Ofiice Kota') {
            // dd($user_wilayah->role_id);
            $query = DB::table('rekomendasi_terdaftar_yayasans')
                ->join('users', 'users.id', 'rekomendasi_terdaftar_yayasans.petugas')
                ->join('indonesia_villages', 'indonesia_villages.code', '=', 'rekomendasi_terdaftar_yayasans.id_kelurahan')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_terdaftar_yayasans.id_kecamatan')
                ->select('rekomendasi_terdaftar_yayasans.*', 'users.name', 'd.name_districts', 'indonesia_villages.name_village')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_terdaftar_yayasans.id_kabkot', $user_wilayah->kota_id)
                        // ->where('log_ulangyayasan.tujuan_ulangYayasan','=', $user_wilayah->role_id)
                        // ->where('log_ulangyayasan.created_by_ulangYayasan','!=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_terdaftar_yayasans.keterangan_daftar_ulang', '=', 'Daftar Ulang Ditolak')
                               ->orWhere('rekomendasi_terdaftar_yayasans.keterangan_daftar_ulang', '=', 'Daftar Ulang Selesai');
                        });
                })->distinct();
        } elseif ($user_wilayah->name == 'SekertarisDinas') {
            // dd($user_wilayah);
            $query = DB::table('rekomendasi_terdaftar_yayasans')
                ->join('users', 'users.id', 'rekomendasi_terdaftar_yayasans.petugas')
                
                ->join('indonesia_villages', 'indonesia_villages.code', '=', 'rekomendasi_terdaftar_yayasans.id_kelurahan')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_terdaftar_yayasans.id_kecamatan')
                ->select('rekomendasi_terdaftar_yayasans.*', 'users.name', 'd.name_districts', 'indonesia_villages.name_village')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_terdaftar_yayasans.id_kabkot', $user_wilayah->kota_id)
                        // ->where('log_ulangyayasan.tujuan_ulangYayasan','=', $user_wilayah->role_id)
                        // ->where('log_ulangyayasan.created_by_ulangYayasan','!=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_terdaftar_yayasans.keterangan_daftar_ulang', '=', 'Daftar Ulang Ditolak')
                               ->orWhere('rekomendasi_terdaftar_yayasans.keterangan_daftar_ulang', '=', 'Daftar Ulang Selesai');
                        });
                })->distinct();
        } elseif ($user_wilayah->name == 'supervisor') {
            // dd($user_wilayah);
            $query = DB::table('rekomendasi_terdaftar_yayasans')
                ->join('users', 'users.id', 'rekomendasi_terdaftar_yayasans.petugas')
                // ->join('log_ulangyayasan', 'log_ulangyayasan.id_trx_ulangYayasan', '=', 'rekomendasi_terdaftar_yayasans.id')
                ->join('indonesia_villages', 'indonesia_villages.code', '=', 'rekomendasi_terdaftar_yayasans.id_kelurahan')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_terdaftar_yayasans.id_kecamatan')
                ->select('rekomendasi_terdaftar_yayasans.*', 'users.name', 'd.name_districts', 'indonesia_villages.name_village')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_terdaftar_yayasans.id_kabkot', $user_wilayah->kota_id)
                        // ->where('log_ulangyayasan.tujuan_ulangYayasan','=', $user_wilayah->role_id)
                        // ->where('log_ulangyayasan.created_by_ulangYayasan','!=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_terdaftar_yayasans.keterangan_daftar_ulang', '=', 'Daftar Ulang Ditolak')
                               ->orWhere('rekomendasi_terdaftar_yayasans.keterangan_daftar_ulang', '=', 'Daftar Ulang Selesai');
                        });
                })->distinct();
        }
        elseif ($user_wilayah->name == 'KepalaDinas') {
            // dd($user_wilayah);
            $query = DB::table('rekomendasi_terdaftar_yayasans')
                ->join('users', 'users.id', 'rekomendasi_terdaftar_yayasans.petugas')
                // ->join('log_ulangyayasan', 'log_ulangyayasan.id_trx_ulangYayasan', '=', 'rekomendasi_terdaftar_yayasans.id')
                ->join('indonesia_villages', 'indonesia_villages.code', '=', 'rekomendasi_terdaftar_yayasans.id_kelurahan')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_terdaftar_yayasans.id_kecamatan')
                ->select('rekomendasi_terdaftar_yayasans.*', 'users.name', 'd.name_districts', 'indonesia_villages.name_village')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_terdaftar_yayasans.id_kabkot', $user_wilayah->kota_id)
                        // ->where('log_ulangyayasan.tujuan_ulangYayasan','=', $user_wilayah->role_id)
                        // ->where('log_ulangyayasan.created_by_ulangYayasan','!=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_terdaftar_yayasans.keterangan_daftar_ulang', '=', 'Daftar Ulang Ditolak')
                               ->orWhere('rekomendasi_terdaftar_yayasans.keterangan_daftar_ulang', '=', 'Daftar Ulang Selesai');                        // 
                        });
                })->distinct();
        }
        if ($user_wilayah->name == 'Front Office Kelurahan' || $user_wilayah->name == 'Back Ofiice kelurahan'|| $user_wilayah->name == 'fasilitator') {
            if ($request->has('search') && !empty($request->search['value'])) {
                $search = $request->search['value'];
                $query = DB::table('rekomendasi_terdaftar_yayasans')
                ->join('users', 'users.id', 'rekomendasi_terdaftar_yayasans.petugas')
                // ->join('log_ulangyayasan', 'log_ulangyayasan.id_trx_ulangYayasan', '=', 'rekomendasi_terdaftar_yayasans.id')
                ->join('indonesia_villages', 'indonesia_villages.code', '=', 'rekomendasi_terdaftar_yayasans.id_kelurahan')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_terdaftar_yayasans.id_kecamatan')
                ->select('rekomendasi_terdaftar_yayasans.*', 'users.name', 'd.name_districts', 'indonesia_villages.name_village')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_terdaftar_yayasans.id_kelurahan', $user_wilayah->kelurahan_id)
                        // ->where('log_ulangyayasan.tujuan_ulangYayasan','=', $user_wilayah->role_id)
                        // ->where('log_ulangyayasan.created_by_ulangYayasan','!=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_terdaftar_yayasans.keterangan_daftar_ulang', '=', 'Daftar Ulang Ditolak')
                               ->orWhere('rekomendasi_terdaftar_yayasans.keterangan_daftar_ulang', '=', 'Daftar Ulang Selesai');                        // 
                        });
                    })->where(function ($query) use ($search) {
                        $query->where('rekomendasi_terdaftar_yayasans.no_pendaftaran', 'like', "%$search%");
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
                $query = DB::table('rekomendasi_terdaftar_yayasans')
                ->join('users', 'users.id', 'rekomendasi_terdaftar_yayasans.petugas')
                // ->join('log_ulangyayasan', 'log_ulangyayasan.id_trx_ulangYayasan', '=', 'rekomendasi_terdaftar_yayasans.id')
                ->join('indonesia_villages', 'indonesia_villages.code', '=', 'rekomendasi_terdaftar_yayasans.id_kelurahan')
                ->join('indonesia_districts as d', 'd.code', '=', 'rekomendasi_terdaftar_yayasans.id_kecamatan')
                ->select('rekomendasi_terdaftar_yayasans.*', 'users.name', 'd.name_districts', 'indonesia_villages.name_village')
                ->orWhere(function ($query) use ($user_wilayah) {
                    $query->where('rekomendasi_terdaftar_yayasans.id_kabkot', $user_wilayah->kota_id)
                        // ->where('log_ulangyayasan.tujuan_ulangYayasan','=', $user_wilayah->role_id)
                        // ->where('log_ulangyayasan.created_by_ulangYayasan','!=', $user_wilayah->model_id)
                        ->where(function ($query) {
                            $query->where('rekomendasi_terdaftar_yayasans.keterangan_daftar_ulang', '=', 'Daftar Ulang Ditolak')
                               ->orWhere('rekomendasi_terdaftar_yayasans.keterangan_daftar_ulang', '=', 'Daftar Ulang Selesai');                        // 
                        });
                })
                ->where('rekomendasi_terdaftar_yayasans.no_pendaftaran', 'like', "%$search%");
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
        // // Get paginated data
       
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
}
