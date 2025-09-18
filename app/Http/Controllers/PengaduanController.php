<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreatePengaduanRequest;
use App\Http\Requests\UpdatePengaduanRequest;
use App\Http\Controllers\AppBaseController;
use App\Models\logPengaduan;
use App\Models\pelapor;
use App\Repositories\PengaduanRepository;
use Illuminate\Http\Request;
use App\Models\Pengaduan;
use App\Models\Prelist;
use App\Models\wilayah;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Flash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use ImageKit\ImageKit;
use ImageKit\Utils\Response;


class PengaduanController extends AppBaseController
{
    /** @var PengaduanRepository $pengaduanRepository*/
    private $pengaduanRepository;

    public function __construct()
    {   
        $roles = User::orderBy('id','DESC')->paginate(5);

        $this->middleware('auth');
    }

    /**
     * Display a listing of the Pengaduan.
     */

  
     public function getuserbyrole(Request $request)
    {

        $role = $request->input('role');
        $users = DB::table('users')
                                        ->join('model_has_roles', 'model_has_roles.model_id', '=', 'users.id')
                                        ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                                        ->select( 'model_has_roles.*', 'roles.name','users.*')
                                        ->where('model_has_roles.role_id', $role)
                                        ->get();
        // dd($users);
        return response()->json($users);
    }
    public function index(Request $request)
    {


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


        $kecamatan = DB::table('indonesia_districts')->select('id', 'name_districts')->orderBy('name_districts')->get();
        return view('pengaduans.index',compact('kecamatan'));
    }

    /**
     * Show the form for creating a new Pengaduan.
     */
    public function create()
    {
        $v = Pengaduan::latest()->first();
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
        
        $alur = DB::table('alur')
            ->where('name', 'Draft')
            // ->where('name', 'supervisor')
            ->orWhere('name', 'Teruskan')
            ->get();
        $checkuserrole = DB::table('model_has_roles')
            ->leftjoin('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('model_id', '=', $userid)
            ->first();
            // dd($checkuserrole->name);
        if ($checkuserrole->name == 'fasilitator') {
                $roles = DB::table('roles')
                    ->where('name', 'Back Ofiice Kelurahan')
                    // ->where('name', 'supervisor')
                    ->orWhere('name', 'supervisor')
                    ->get();
                // dd($roles);
        }elseif($checkuserrole->name == 'Front Office kota') {
            // dd($checkuserrole->name);
            $roles = DB::table('roles')
                ->where('name', 'Back Ofiice Kota')
                // ->where('name', 'supervisor')
                ->orWhere('name', 'supervisor')
                ->get();
            // dd($roles);
        }elseif ($checkuserrole->name == 'Front Office Kelurahan') {
            $roles = DB::table('roles')
                ->where('name', 'Back Ofiice Kelurahan')
                // ->where('name', 'supervisor')
                ->orWhere('name', 'supervisor')
                ->get();
        }
        // $roles = DB::table('roles')
        //     ->where('name', 'Back Ofiice Kota')
        //     // ->where('name', 'supervisor')
        //     ->orWhere('name', 'supervisor')
        //     ->get();
        // dd($roles);
        return view('pengaduans.create', compact('kecamatans','checkuserrole','wilayah','alur','roles'));
    }

    /**
     * Store a newly created Pengaduan in storage.
     */
    public function store(Request $request)
    {
       // Output: apple,banana,orange
       
        if ($request->get('nik') != null) {
        
            if ($request->get('status_dtks') == 'Terdaftar') {
                //nik dan dtks ada
                // dd($request->all());
                $data = new Pengaduan;
                if($request->file('tl_file')){
                    // dd($request->file('tl_file'));
                   
                    $path = $request->file('tl_file');
                    $filname = 'gambar-baru/'.$path->getClientOriginalName();
                    $return = Storage::disk('imagekit')->put($filname, fopen($path->getRealPath(), 'r') );
                    $data->tl_file =  Storage::disk('imagekit')->url($filname);
                    // dd($data);
                }
                $data->id_alur = $request->get('id_alur');
                $data->no_pendaftaran = mt_rand(100, 1000);
                $data->id_provinsi = $request->get('id_provinsi');
                $data->id_kabkot = $request->get('id_kabkot');
                $data->id_kecamatan = $request->get('id_kecamatan');
                $data->id_kelurahan = $request->get('id_kelurahan');
                $data->jenis_pelapor = $request->get('jenis_pelapor');
                $data->ada_nik = $request->get('memiliki_nik');
                $data->nik = $request->get('nik');
                $data->no_kk = $request->get('no_kk');
                // $data->no_kis = $request->get('no_kis');
                $data->nama = $request->get('nama');
                $data->tgl_lahir = $request->get('tgl_lahir');
                $data->tempat_lahir = $request->get('tempat_lahir');
                // $data->alamat = $request->get('alamat');
                $data->telp = $request->get('telpon');
                $data->email = $request->get('email');
                $data->hubungan_terlapor = $request->get('hubungan_terlapor');
                // $data->id_program_sosial = $request->get('id_program_sosial');
                $data->id_program_sosial = json_encode($request->input('id_program_sosial'));            
                $data->kepesertaan_program = $request->get('kepesertaan_program');
                $data->no_peserta = json_encode($request->input('no_peserta'));
                $data->level_program = $request->get('level_program');
                $data->sektor_program = $request->get('sektor_program');
                $data->no_kartu_program = $request->get('no_kartu_program');
                $data->ringkasan_pengaduan  = $request->get('ringkasan_pengaduan');
                $data->detail_pengaduan  = $request->get('detail_pengaduan');
                // $data->tl_file  = $request->get('detail_pengaduan');
                $data->status_dtks = $request->get('status_dtks');
                $data->tujuan = $request->get('tujuan');
                $data->status_aksi = $request->get('status_aksi'); 
                $data->petugas = $request->get('petugas'); 
                $data->createdby = Auth::user()->id;
                $data->updatedby = Auth::user()->id;
                // dd($data);
                $data->save();
                // dd($data);
                $logpengaduan = new logPengaduan;
                $logpengaduan['id_trx_pengaduan'] = $data->id;
                $logpengaduan['id_alur'] = $request->get('status_aksi');
                $logpengaduan['petugas'] = $request->get('petugas'); 
                $logpengaduan['catatan']  = $request->get('tl_catatan');
                $logpengaduan['file_pendukung'] = $request->get('file_pendukung');
                $logpengaduan['tujuan'] = $request->get('tujuan');
                $logpengaduan['created_by'] = Auth::user()->id;
                $logpengaduan['updated_by'] = Auth::user()->id;
                
                $logpengaduan->save();
                if($request->get('jenis_pelapor') == 'Orang Lain'){
                    $pelapor = new pelapor();
                    
                    $pelapor['id_menu'] = '10   ';
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
                    $pelapor['id_menu'] = '10';
                    $pelapor['id_form'] = $data->id;
                    $pelapor['jenis_peelaporan'] = $request->get('jenis_pelapor');
                    $pelapor['nama_pelapor']  =  $request->get('nama');
                    $pelapor['ada_nik_pelapor'] = $request->get('memiliki_nik');
                    $pelapor['nik_pelapor'] = $request->get('nik');
                    $pelapor['status_dtks_pelapor'] = $request->get('status_dtks');
                    $pelapor['tempat_lahir_pelapor'] = $request->get('tempat_lahir');
                    $pelapor['tanggal_lahir_pelapor'] = $request->get('tgl_lahir');
                    $pelapor['jenis_kelamin'] = $request->get('jenis_kelamin');
                    $pelapor['nama_pelapor']  = $request->get('nama');
                    $pelapor['telepon_pelapor'] = $request->get('telp');
                    $pelapor['alamat_pelapor'] = $request->get('alamat');
                    $pelapor['createdby_pelapor'] = Auth::user()->id;
                    $pelapor['updatedby_pelapor'] = Auth::user()->id;
    
                    $pelapor->save();
                }
                return redirect('pengaduans')->withSuccess('Data Berhasil Disimpan');
            } else {
                //nik ada, dtks tidak
        
                //cek apakah sudah ada
                $cek = Prelist::where('nik', '=', $request->get('nik'))->exists();
                if (!$cek) {
                    $data = new Prelist;
                    $data['id_provinsi'] = $request->get('id_provinsi');
                    $data['id_kabkot'] = $request->get('id_kabkot');
                    $data['id_kecamatan'] = $request->get('id_kecamatan');
                    $data['id_kelurahan'] = $request->get('id_kelurahan');
                    $data['nik'] = $request->get('nik');
                    $data['no_kk'] = $request->get('no_kk');
                    // $data['no_kis'] = $request->get('no_kis');
                    $data['nama'] = $request->get('nama');
                    $data['tgl_lahir'] = $request->get('tgl_lahir');
                    // $data['alamat'] = $request->get('alamat');
                    $data['telp'] = $request->get('telpon');
                    $data['email'] = $request->get('email');
                    $data['status_data'] = 'prelistdtks';
        
                    $data->save();
                }
                $data = new Pengaduan;
                if($request->file('tl_file')){
                    // dd($request->file('tl_file'));
                  
                    $path = $request->file('tl_file');
                    $filname = 'gambar-baru/'.$path->getClientOriginalName();
                    // dd($filname);
                    // $update['filename'] = $filname;
                    $return = Storage::disk('imagekit')->put($filname, fopen($path->getRealPath(), 'r') );
                    $data['tl_file'] =  Storage::disk('imagekit')->url($filname);
                    // dd($data);
                }
                $data['id_alur'] = $request->get('id_alur');
                $data['no_pendaftaran'] = mt_rand(100, 1000);
                $data['id_provinsi'] = $request->get('id_provinsi');
                $data['id_kabkot'] = $request->get('id_kabkot');
                $data['id_kecamatan'] = $request->get('id_kecamatan');
                $data['id_kelurahan'] = $request->get('id_kelurahan');
                $data['jenis_pelapor'] = $request->get('jenis_pelapor');
                $data['ada_nik'] = $request->get('memiliki_nik');
                $data['nik'] = $request->get('nik');
                $data['no_kk'] = $request->get('no_kk');
                // $data['no_kis'] = $request->get('no_kis');
                $data['nama'] = $request->get('nama');
                $data['tgl_lahir'] = $request->get('tgl_lahir');
                $data['tempat_lahir'] = $request->get('tempat_lahir');
                // $data['alamat'] = $request->get('alamat');
                $data['telp'] = $request->get('telpon');
                $data['email'] = $request->get('email');
                $data['hubungan_terlapor'] = $request->get('hubungan_terlapor');
                $data['id_program_sosial'] = $request->get('id_program_sosial');
                $data['kepesertaan_program'] = $request->get('kepesertaan_program');
                $data['no_peserta'] = $request->get('no_peserta');
                $data['level_program'] = $request->get('level_program');
                $data['sektor_program'] = $request->get('sektor_program');
                $data['no_kartu_program'] = $request->get('no_kartu_program');
                $data['ringkasan_pengaduan']  = $request->get('ringkasan_pengaduan');
                $data['detail_pengaduan']  = $request->get('detail_pengaduan');
                $data['kategori_pengaduan'] = $request->get('kategori_pengaduan');
                $data['status_dtks'] = $request->get('status_dtks');
                $data['tujuan'] = $request->get('tujuan');
                $data['status_aksi'] = $request->get('status_aksi'); 
                $data['petugas'] = $request->get('petugas'); 
                $data['createdby'] = Auth::user()->id;
                $data['updatedby'] = Auth::user()->id;
                // dd($data);
                $data->save();
                // dd($data);// dd($data);
                $logpengaduan = new logPengaduan;
                $logpengaduan['id_trx_pengaduan'] = $data->id;
                $logpengaduan['id_alur'] = $request->get('status_aksi');
                $logpengaduan['petugas'] = $request->get('petugas');
                $logpengaduan['catatan']  = $request->get('tl_catatan');
                $logpengaduan['file_pendukung'] = $request->get('file_pendukung');
                $logpengaduan['tujuan'] = $request->get('tujuan');
                $logpengaduan['created_by'] = Auth::user()->id;
                $logpengaduan['updated_by'] = Auth::user()->id;
                
                $logpengaduan->save();
                if($request->get('jenis_pelapor') == 'Orang Lain'){
                    $pelapor = new pelapor();
                    
                    $pelapor['id_menu'] = '10   ';
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
                    $pelapor['id_menu'] = '10';
                    $pelapor['id_form'] = $data->id;
                    $pelapor['jenis_peelaporan'] = $request->get('jenis_pelapor');
                    $pelapor['nama_pelapor']  =  $request->get('nama');
                    $pelapor['ada_nik_pelapor'] = $request->get('memiliki_nik');
                    $pelapor['nik_pelapor'] = $request->get('nik');
                    $pelapor['status_dtks_pelapor'] = $request->get('status_dtks');
                    $pelapor['tempat_lahir_pelapor'] = $request->get('tempat_lahir');
                    $pelapor['tanggal_lahir_pelapor'] = $request->get('tgl_lahir');
                    $pelapor['jenis_kelamin'] = $request->get('jenis_kelamin');
                    $pelapor['nama_pelapor']  = $request->get('nama');
                    $pelapor['telepon_pelapor'] = $request->get('telp');
                    $pelapor['alamat_pelapor'] = $request->get('alamat');
                    $pelapor['createdby_pelapor'] = Auth::user()->id;
                    $pelapor['updatedby_pelapor'] = Auth::user()->id;
    
                    $pelapor->save();
                }
                return redirect('pengaduans')->withSuccess('Data Berhasil Disimpan');
            }
        } else {
            //nik belum ada
            $data = new Pengaduan;
            if($request->file('tl_file')){
                // dd($request->file('tl_file'));
              
                $path = $request->file('tl_file');
                $filname = 'gambar-baru/'.$path->getClientOriginalName();
                // dd($filname);
                // $update['filename'] = $filname;
                $return = Storage::disk('imagekit')->put($filname, fopen($path->getRealPath(), 'r') );
                $data['tl_file'] =  Storage::disk('imagekit')->url($filname);
                // dd($data);
            }
            $data['id_alur'] = $request->get('id_alur');
            $data['no_pendaftaran'] = mt_rand(100, 1000);
            $data['id_provinsi'] = $request->get('id_provinsi');
            $data['id_kabkot'] = $request->get('id_kabkot');
            $data['id_kecamatan'] = $request->get('id_kecamatan');
            $data['id_kelurahan'] = $request->get('id_kelurahan');
            $data['jenis_pelapor'] = $request->get('jenis_pelapor');
            $data['ada_nik'] = $request->get('memiliki_nik');
            $data['nik'] = $request->get('nik');
            $data['no_kk'] = $request->get('no_kk');
            // $data['no_kis'] = $request->get('no_kis');
            $data['nama'] = $request->get('nama');
            $data['tgl_lahir'] = $request->get('tgl_lahir');
            $data['tempat_lahir'] = $request->get('tempat_lahir');
            $data['kategori_pengaduan'] = $request->get('kategori_pengaduan');
            $data['telp'] = $request->get('telpon');
            $data['email'] = $request->get('email');
            $data['hubungan_terlapor'] = $request->get('hubungan_terlapor');
            $data['id_program_sosial'] = $request->get('id_program_sosial');
            $data['kepesertaan_program'] = $request->get('kepesertaan_program');
            $data['no_peserta'] = $request->get('no_peserta');
            $data['level_program'] = $request->get('level_program');
            $data['sektor_program'] = $request->get('sektor_program');
            $data['no_kartu_program'] = $request->get('no_kartu_program');
            $data['ringkasan_pengaduan']  = $request->get('ringkasan_pengaduan');
            $data['detail_pengaduan']  = $request->get('detail_pengaduan');
            // $data['tl_file']  = $request->get('detail_pengaduan');
            $data['status_dtks'] = $request->get('status_dtks');
            $data['tujuan'] = $request->get('tujuan');
            $data['status_aksi'] = $request->get('status_aksi'); 
            $data['petugas'] = $request->get('petugas'); 
            $data['createdby'] = Auth::user()->id;
            $data['updatedby'] = Auth::user()->id;
            // dd($data);
            $data->save();
            // dd($data);
            if($request->get('jenis_pelapor_sudtks') == 'Orang Lain'){
                $pelapor = new pelapor();
                
                $pelapor['id_menu'] = '10   ';
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
                $pelapor['id_menu'] = '10';
                $pelapor['id_form'] = $data->id;
                $pelapor['jenis_peelaporan'] = $request->get('jenis_pelapor');
                $pelapor['nama_pelapor']  =  $request->get('nama');
                $pelapor['ada_nik_pelapor'] = $request->get('memiliki_nik');
                $pelapor['nik_pelapor'] = $request->get('nik');
                $pelapor['status_dtks_pelapor'] = $request->get('status_dtks');
                $pelapor['tempat_lahir_pelapor'] = $request->get('tempat_lahir');
                $pelapor['tanggal_lahir_pelapor'] = $request->get('tgl_lahir');
                $pelapor['jenis_kelamin'] = $request->get('jenis_kelamin');
                $pelapor['nama_pelapor']  = $request->get('nama');
                $pelapor['telepon_pelapor'] = $request->get('telp');
                $pelapor['alamat_pelapor'] = $request->get('alamat');
                $pelapor['createdby_pelapor'] = Auth::user()->id;
                $pelapor['updatedby_pelapor'] = Auth::user()->id;

                $pelapor->save();
            }
            return redirect('pengaduans')->withWarning('NIK Tidak Tersedia Data Disimpan sebagai draft');
        }
        // dd($data); 
    }

    /**
     * Display the specified Pengaduan.
     */
    public function show($id)
    {
        // $userid = Auth::user()->id;
        $logpengaduan = DB::table('log_pengaduan as w')->select(
            'w.*',
            'roles.name as name_roles',
            'users.name',
            // 'w.status_wilayah',
        )
        ->leftjoin('users', 'users.id', '=', 'w.petugas')
        ->leftjoin('roles', 'roles.id', '=', 'w.tujuan')
        ->leftjoin('pengaduans', 'pengaduans.id', '=', 'w.id_trx_pengaduan')
        ->where('w.id', $id)->get();
        // dd($logpengaduan);
        $pengaduan = DB::table('pengaduans as w')->select(
            'w.*',
            'b.name_village',
            'prov.name_prov',
            'kota.name_cities',
            'kecamatan.name_districts',
            'roles.name as name_roles',
            'users.name',
            // 'w.status_wilayah',
        )
        ->leftjoin('users', 'users.id', '=', 'w.petugas')
        ->leftjoin('roles', 'roles.id', '=', 'w.tujuan')
        ->leftjoin('indonesia_provinces as prov', 'prov.code', '=', 'w.id_provinsi')
        ->leftjoin('indonesia_cities as kota', 'kota.code', '=', 'w.id_kabkot')
        ->leftjoin('indonesia_districts as kecamatan', 'kecamatan.code', '=', 'w.id_kecamatan')
        ->leftjoin('indonesia_villages as b', 'b.code', '=', 'w.id_kelurahan')
        ->where('w.id', $id)->first();
        // dd($pengaduan);
        // // $pengaduan = $this->pengaduanRepository->find((int) $id);
        // $wilayah = DB::table('wilayahs as w')->select(
        //     'w.*',
        //     'b.*',
        //     'prov.*',
        //     'kota.*',
        //     'kecamatan.*'
        // )
        //     ->leftjoin('indonesia_provinces as prov', 'prov.code', '=', 'w.province_id')
        //     ->leftjoin('indonesia_cities as kota', 'kota.code', '=', 'w.kota_id')
        //     ->leftjoin('indonesia_districts as kecamatan', 'kecamatan.code', '=', 'w.kecamatan_id')
        //     ->leftjoin('indonesia_villages as b', 'b.code', '=', 'w.kelurahan_id')
        //     ->where('status_wilayah', '1')
        //     ->where('w.createdby', $userid)->get();

        // if (empty($pengaduan)) {
        //     Flash::error('Pengaduan not found');

        //     return redirect(route('pengaduans.index'));
        // }
        // $roleid = DB::table('roles')
        //     ->where('name', 'Back Ofiice kelurahan')
        //     // ->where('name', 'supervisor')
        //     ->orWhere('name', 'supervisor')
        //     ->get();
        //     $checkroles = DB::table('model_has_roles')
        //     ->leftjoin('roles', 'roles.id', '=', 'model_has_roles.role_id')
        //     ->get();
        //     // dd($checkroles);
        return view('pengaduans.show', compact('pengaduan','logpengaduan'));
    }

    /**
     * Show the form for editing the specified Pengaduan.
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


        $checkuserrole = DB::table('model_has_roles')
            ->leftjoin('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('model_id', '=', $userid)
            ->first();
        
        
        $pengaduan = Pengaduan::where('id', $id)->first();
     
        $getdata = DB::table('model_has_roles')
            ->leftjoin('pengaduans as b', 'b.tujuan', '=', 'model_has_roles.role_id')
            ->where('b.id', $id)
            ->get();
        //alur
        if ($checkuserrole->name == 'fasilitator') {
            $alur = DB::table('alur')
                ->where('name', 'Draft')
                // ->where('name', 'supervisor')
                ->orWhere('name', 'Teruskan')
                ->get();
            $roleid = DB::table('model_has_roles')
                ->leftjoin('users', 'users.id', '=', 'model_has_roles.model_id')
                ->where('name', 'Back Ofiice kelurahan')
                // ->where('name', 'supervisor')
                ->orWhere('name', 'supervisor')
                ->get();
            // dd($roleid);
            $createdby = DB::table('pengaduans')
                    ->join('users', 'pengaduans.createdby', '=', 'users.id')
                    ->join('model_has_roles', 'model_has_roles.model_id', '=', 'users.id')
                    ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                    ->select('pengaduans.id', 'pengaduans.createdby', 'model_has_roles.role_id', 'model_has_roles.model_id', 'roles.name','users.name')
                    ->where('pengaduans.id', $id)
                    ->get();
          
        }else if ($checkuserrole->name == 'Back Ofiice Kota') {
            $alur = DB::table('alur')
                ->where('name', 'Kembalikan')
                ->orwhere('name', 'Teruskan')
                ->orWhere('name', 'Tolak')
                ->orWhere('name', 'Selesai')
                ->get();
            // dd($alur);
            $roleid = DB::table('model_has_roles')
                ->leftjoin('users', 'users.id', '=', 'model_has_roles.model_id')
                ->leftjoin('roles', 'roles.id', '=', 'model_has_roles.role_id')
                ->select('roles.*','users.*','model_has_roles.*')
                ->where('roles.name', 'kepala bidang')
                // ->where('name', 'supervisor')
                // ->orWhere('name', 'supervisor')
                ->get();
            // dd($roleid);
            $createdby = DB::table('pengaduans')
                    ->join('users', 'pengaduans.createdby', '=', 'users.id')
                    ->join('model_has_roles', 'model_has_roles.model_id', '=', 'users.id')
                    ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                    ->select('pengaduans.id', 'pengaduans.createdby', 'model_has_roles.role_id', 'model_has_roles.model_id', 'roles.name','users.name')
                    ->where('pengaduans.id', $id)
                    ->get();
        }else if ($checkuserrole->name == 'supervisor') {
            $alur = DB::table('alur')
                ->where('name', 'Kembalikan')
                ->orwhere('name', 'Teruskan')
                ->orWhere('name', 'Tolak')
                ->orWhere('name', 'Selesai')
                ->get();
            // dd($alur);
            $roleid = DB::table('model_has_roles')
                ->leftjoin('users', 'users.id', '=', 'model_has_roles.model_id')
                ->leftjoin('roles', 'roles.id', '=', 'model_has_roles.role_id')
                ->select('roles.*','users.*','model_has_roles.*')
                ->where('roles.name', 'kepala bidang')
                // ->where('name', 'supervisor')
                // ->orWhere('name', 'supervisor')
                ->get();
            // dd($roleid);
            $createdby = DB::table('pengaduans')
                    ->join('users', 'pengaduans.createdby', '=', 'users.id')
                    ->join('model_has_roles', 'model_has_roles.model_id', '=', 'users.id')
                    ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                    ->select('pengaduans.id', 'pengaduans.createdby', 'model_has_roles.role_id', 'model_has_roles.model_id', 'roles.name','users.name')
                    ->where('pengaduans.id', $id)
                    ->get();
        }else if ($checkuserrole->name == 'kepala bidang') {
            $alur = DB::table('alur')
                ->where('name', 'Kembalikan')
                ->orwhere('name', 'Teruskan')
                ->orWhere('name', 'Tolak')
                ->orWhere('name', 'Selesai')
                ->get();
            // dd($alur);
            $roleid = DB::table('model_has_roles')
                ->leftjoin('users', 'users.id', '=', 'model_has_roles.model_id')
                ->leftjoin('roles', 'roles.id', '=', 'model_has_roles.role_id')
                ->select('roles.*','users.*','model_has_roles.*')
                ->where('roles.name', 'kepala bidang')
                // ->where('name', 'supervisor')
                // ->orWhere('name', 'supervisor')
                ->get();
            // dd($roleid);
            $createdby = DB::table('pengaduans')
                    ->join('users', 'pengaduans.createdby', '=', 'users.id')
                    ->join('model_has_roles', 'model_has_roles.model_id', '=', 'users.id')
                    ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                    ->select('pengaduans.id', 'pengaduans.createdby', 'model_has_roles.role_id', 'model_has_roles.model_id', 'roles.name','users.name')
                    ->where('pengaduans.id', $id)
                    ->get();
        } else if ($checkuserrole->name == 'Back Ofiice kelurahan') {

            $alur = DB::table('alur')
                    ->where('name', 'Kembalikan')
                    // ->where('name', 'supervisor')
                    ->orWhere('name', 'Tolak')
                    ->orWhere('name', 'Selesai')
                    ->get();
                // dd($alur);
            $roleid = DB::table('model_has_roles')
                ->leftjoin('users', 'users.id', '=', 'model_has_roles.model_id')
                ->where('name', 'Back Ofiice kelurahan')
                // ->where('name', 'supervisor')
                ->orWhere('name', 'supervisor')
                ->get();
            // dd($roleid);
            $createdby = DB::table('pengaduans')
                    ->join('users', 'pengaduans.createdby', '=', 'users.id')
                    ->join('model_has_roles', 'model_has_roles.model_id', '=', 'users.id')
                    ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                    ->select('pengaduans.id', 'pengaduans.createdby', 'model_has_roles.role_id', 'model_has_roles.model_id', 'roles.name','users.name')
                    ->where('pengaduans.id', $id)
                    ->get();
            // dd($createdby);
          
        } else if ($checkuserrole->name == 'Front Office kota') {
            $alur = DB::table('alur')
                ->where('name', 'Kembalikan')
                // ->where('name', 'supervisor')
                ->orWhere('name', 'Tolak')
                ->orWhere('name', 'Selesai')
                ->get();
            $roleid = DB::table('model_has_roles')
                ->leftjoin('users', 'users.id', '=', 'model_has_roles.model_id')
                ->leftjoin('roles', 'roles.id', '=', 'model_has_roles.role_id')
                ->select('roles.*','users.*','model_has_roles.*')
                ->where('roles.name', 'Back Ofiice Kota')
                // ->where('name', 'supervisor')
                // ->orWhere('name', 'supervisor')
                ->get();
                // dd($roleid);
            $createdby = DB::table('pengaduans')
                ->join('users', 'pengaduans.createdby', '=', 'users.id')
                ->join('model_has_roles', 'model_has_roles.model_id', '=', 'users.id')
                ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                ->select('pengaduans.id', 'pengaduans.createdby', 'model_has_roles.role_id', 'model_has_roles.model_id', 'roles.name','users.name')
                ->where('pengaduans.id', $id)
                ->get();
        } else if ($checkuserrole->name == 'Front Office Kelurahan') {
            $alur = DB::table('alur')
                ->where('name', 'Kembalikan')
                // ->where('name', 'supervisor')
                ->orWhere('name', 'Tolak')
                ->orWhere('name', 'Selesai')
                ->get();
            $roleid = DB::table('model_has_roles')
                ->leftjoin('users', 'users.id', '=', 'model_has_roles.model_id')
                ->where('name', 'Back Ofiice Kota')
                // ->where('name', 'supervisor')
                // ->orWhere('name', 'supervisor')
                ->get();
                // dd($roleid);
            $createdby = DB::table('pengaduans')
                ->join('users', 'pengaduans.createdby', '=', 'users.id')
                ->join('model_has_roles', 'model_has_roles.model_id', '=', 'users.id')
                ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                ->select('pengaduans.id', 'pengaduans.createdby', 'model_has_roles.role_id', 'model_has_roles.model_id', 'roles.name')
                ->where('pengaduans.id', $id)
                ->get();
        }
        $checkroles2 = DB::table('model_has_roles')
            ->leftjoin('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->leftjoin('pengaduans', 'pengaduans.createdby', '=', 'model_has_roles.model_id')
            ->where('pengaduans.id', '=', $id)
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

        return view('pengaduans.edit', compact('wilayah', 'pengaduan', 'roleid', 'getdata', 'alur', 'checkroles', 'rolebackoffice', 'createdby','checkroles2'));
    }
    /**
     * Update the specified Pengaduan in storage.
     */
    public function update(Request $request, $id)
    {
        $userid = Auth::user()->id;
        $datapengaduan = Pengaduan::where('id',$id)->first();
        // dd();
       
        if ($datapengaduan->nik != null) {
         
            if ($datapengaduan->status_dtks == 'Terdaftar') {
               
                if ($datapengaduan->status_aksi == 'Teruskan' || $datapengaduan->status_aksi =='Kembalikan' ){
                    // dd($request->get('status_dtks') );
                    $pengaduan['petugas']  = $request->get('petugas');
                    $pengaduan['tl_catatan'] = $request->get('tl_catatan');
                    $pengaduan['tujuan'] = $request->get('tujuan');
                    $pengaduan['status_aksi'] = $request->get('status_aksi'); 
                    // dd($pengaduan);
                    Pengaduan::where('id',$id)->update($pengaduan);
                }
                if ($datapengaduan->status_aksi == 'Draft'){
                    $pengaduan['id_alur'] = $request->get('id_alur');
                    if($request->file('tl_file')){
                        // dd($request->file('tl_file'));
                      
                        $path = $request->file('tl_file');
                        $filname = 'gambar-baru/'.$path->getClientOriginalName();
                        // dd($filname);
                        // $update['filename'] = $filname;
                        $return = Storage::disk('imagekit')->put($filname, fopen($path->getRealPath(), 'r') );
                        $pengaduan['tl_file'] =  Storage::disk('imagekit')->url($filname);
                        // dd($data);
                    }
                    $pengaduan['id_provinsi'] = $request->get('id_provinsi');
                    $pengaduan['id_kabkot'] = $request->get('id_kabkot');
                    $pengaduan['id_kecamatan'] = $request->get('id_kecamatan');
                    $pengaduan['id_kelurahan'] = $request->get('id_kelurahan');
                    $pengaduan['jenis_pelapor'] = $request->get('jenis_pelapor');
                    $pengaduan['ada_nik'] = $request->get('memiliki_nik');
                    $pengaduan['nik'] = $request->get('nik');
                    $pengaduan['no_kk'] = $request->get('no_kk');
                    $pengaduan['kategori_pengaduan'] = $request->get('kategori_pengaduan');
                    $pengaduan['nama'] = $request->get('nama');
                    $pengaduan['tgl_lahir'] = $request->get('tgl_lahir');
                    $pengaduan['tempat_lahir'] = $request->get('tempat_lahir');
                    $pengaduan['status_dtks'] = $request->get('status_dtks');
                    $pengaduan['telp'] = $request->get('telpon');
                    $pengaduan['email'] = $request->get('email');
                    $pengaduan['hubungan_terlapor'] = $request->get('hubungan_terlapor');
                    $pengaduan['id_program_sosial'] = $request->get('id_program_sosial');
                    $pengaduan['kepesertaan_program'] = $request->get('kepesertaan_program');
                    $pengaduan['no_peserta'] = $request->get('no_peserta');
                    $pengaduan['level_program'] = $request->get('level_program');
                    $pengaduan['sektor_program'] = $request->get('sektor_program');
                    $pengaduan['no_kartu_program'] = $request->get('no_kartu_program');
                    $pengaduan['ringkasan_pengaduan']  = $request->get('ringkasan_pengaduan');
                    $pengaduan['detail_pengaduan']  = $request->get('detail_pengaduan');
                    $pengaduan['petugas']  = $request->get('petugas');
                    $pengaduan['tl_catatan'] = $request->get('tl_catatan');
                    $pengaduan['tujuan'] = $request->get('tujuan');
                    $pengaduan['status_aksi'] = $request->get('status_aksi'); 
                
                    Pengaduan::where('id',$id)->update($pengaduan);
                }
                
                $checkuserrole = DB::table('model_has_roles')
                ->leftjoin('roles', 'roles.id', '=', 'model_has_roles.role_id')
                ->where('model_id', '=', $userid)
                ->first();
                if ($checkuserrole->name == $checkuserrole->name){
                    //   dd($pengaduan);
                    $logpengaduan = new logPengaduan;
                    $logpengaduan['id_trx_pengaduan'] = $id;
                    $logpengaduan['id_alur'] = $request->get('status_aksi');
                    $logpengaduan['petugas'] = $request->get('petugas');
                    $logpengaduan['catatan']  = $request->get('tl_catatan');
                    $logpengaduan['file_pendukung'] = $request->get('file_pendukung');
                    $logpengaduan['tujuan'] = $request->get('tujuan');
                    $logpengaduan['created_by'] = Auth::user()->id;
                    $logpengaduan['updated_by'] = Auth::user()->id;
                    // dd($logpengaduan);
                    $logpengaduan->save();
                }
             
                return redirect('pengaduans')->withSuccess('pengaduan Berhasil Diubah');
            } else {
               
                $cek = Prelist::where('nik', '=', $request->get('nik'))->exists();
                if ($cek) {
                    return redirect('pengaduans')->withWarning('NIK Sudah Terdaftar Di Prelist');
                } else {

                    $pengaduan['id_provinsi'] = $request->get('id_provinsi');
                    $pengaduan['id_kabkot'] = $request->get('id_kabkot');
                    $pengaduan['id_kecamatan'] = $request->get('id_kecamatan');
                    $pengaduan['id_kelurahan'] = $request->get('id_kelurahan');
                    $pengaduan['nik'] = $request->get('nik');
                    $pengaduan['no_kk'] = $request->get('no_kk');
                    // $pengaduan['no_kis'] = $request->get('no_kis');
                    $pengaduan['nama'] = $request->get('nama');
                    $pengaduan['tgl_lahir'] = $request->get('tgl_lahir');
                    // $pengaduan['alamat'] = $request->get('alamat');
                    $pengaduan['telp'] = $request->get('telpon');
                    $pengaduan['email'] = $request->get('email');
                    // $pengaduan['status_pengaduan'] = 'prelistdtks';

                    Prelist::where('id',$id)->update($pengaduan);
                    return redirect('pengaduans')->withSuccess('pengaduan Berhasil Disimpan Di Prelist');
                }
            }
        } else {
           
                // dd($pengaduan);
                $pengaduan['id_alur'] = $request->get('id_alur');
                // $pengaduan['no_pendaftaran'] =  $request->get('no_pendaftaran');
                $pengaduan['id_provinsi'] = $request->get('id_provinsi');
                $pengaduan['id_kabkot'] = $request->get('id_kabkot');
                $pengaduan['id_kecamatan'] = $request->get('id_kecamatan');
                $pengaduan['id_kelurahan'] = $request->get('id_kelurahan');
                $pengaduan['jenis_pelapor'] = $request->get('jenis_pelapor');
                $pengaduan['ada_nik'] = $request->get('memiliki_nik');
                $pengaduan['nik'] = $request->get('nik');
                $pengaduan['no_kk'] = $request->get('no_kk');
                $pengaduan['status_dtks'] = $request->get('status_dtks');
                $pengaduan['nama'] = $request->get('nama');
                $pengaduan['tgl_lahir'] = $request->get('tgl_lahir');
                $pengaduan['tempat_lahir'] = $request->get('tempat_lahir');
                // $pengaduan['alamat'] = $request->get('alamat');
                $pengaduan['telp'] = $request->get('telpon');
                $pengaduan['email'] = $request->get('email');
                $pengaduan['hubungan_terlapor'] = $request->get('hubungan_terlapor');
                $pengaduan['id_program_sosial'] = $request->get('id_program_sosial');
                $pengaduan['kepesertaan_program'] = $request->get('kepesertaan_program');
                $pengaduan['no_peserta'] = $request->get('no_peserta');
                $pengaduan['level_program'] = $request->get('level_program');
                $pengaduan['sektor_program'] = $request->get('sektor_program');
                $pengaduan['no_kartu_program'] = $request->get('no_kartu_program');
                $pengaduan['ringkasan_pengaduan']  = $request->get('ringkasan_pengaduan');
                $pengaduan['detail_pengaduan']  = $request->get('detail_pengaduan');
                // $pengaduan['tl_file']  = $request->get('detail_pengaduan');
                $pengaduan['no_dtks'] = $request->get('no_dtks');
                $pengaduan['tujuan'] = 'Draft';
                $pengaduan['status_aksi'] = $request->get('status_aksi'); 
                $pengaduan['createdby'] = Auth::user()->name;
                $pengaduan['updatedby'] = Auth::user()->name;
                // dd($pengaduan);

                Pengaduan::where('id',$id)->update($pengaduan);

                return redirect('pengaduans')->withSuccess('Data Berhasil Diubah');
        }
        
    }

    /**
     * Remove the specified Pengaduan from storage.
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $pengaduan = $this->pengaduanRepository->find($id);

        if (empty($pengaduan)) {
            Flash::error('Pengaduan not found');

            return redirect('pengaduans')->withSuccess('Data Berhasil Dihapus');
        }

        $this->pengaduanRepository->delete($id);

        Flash::success('Pengaduan deleted successfully.');

        return redirect('pengaduans')->withSuccess('Data Berhasil Dihapus');
    }
    public function draft(Request $request)
    {
        $user_name = Auth::user()->name;
        $query = DB::table('pengaduans')
            ->leftjoin('users', 'users.id', '=', 'pengaduans.createdby')
            ->leftjoin('wilayahs', 'wilayahs.createdby', '=', 'pengaduans.createdby')
            ->leftjoin('model_has_roles', 'model_has_roles.model_id', '=', 'pengaduans.tujuan')
            ->leftjoin('indonesia_villages as b', 'b.code', '=', 'pengaduans.id_kelurahan')
            ->select('pengaduans.*', 'b.name_village')
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
            if ($user_wilayah->name == 'fasilitator' ) {
                 $query->Where(function($query) use ($user_wilayah) {
                            $query->where('pengaduans.id_kelurahan', $user_wilayah->kelurahan_id);
                            $query->where('pengaduans.status_aksi', 'Draft');     
                            $query->where('pengaduans.createdby',  Auth::user()->id);
                });

            }elseif ($user_wilayah->name == 'Front Office kota' ) {
                $query->orWhere(function($query) use ($user_wilayah) {
                    $query->where('pengaduans.id_kelurahan', $user_wilayah->kelurahan_id);
                    $query->where('pengaduans.status_aksi', 'Draft');     
                    $query->where('pengaduans.createdby',  Auth::user()->id);
                    //  dd($va);
                 });

            }elseif ($user_wilayah->name == 'Front Office Kelurahan' ) {
                $query->orWhere(function($query) use ($user_wilayah) {
                    $query->where('pengaduans.id_kelurahan', $user_wilayah->kelurahan_id);
                    $query->where('pengaduans.status_aksi', 'Draft');     
                    $query->where('pengaduans.createdby',  Auth::user()->id);
                    //  dd($va);
                 });

            }
        if ($request->has('search')) {
            // dd($query);
            $search = $request->search['value'];
            $query->where(function ($query) use ($search) {
                $query->where('pengaduans.nama', 'like', "%$search%");
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
            'recordsTotal' => Pengaduan::count(),
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
            if ($user_wilayah->name == 'fasilitator' ) {
                $query = DB::table('pengaduans')
                    ->join('users', 'users.id', '=', 'pengaduans.createdby')
                    ->join('indonesia_districts as d', 'd.code', '=', 'pengaduans.id_kecamatan')
                    ->join('indonesia_villages as b', 'b.code', '=', 'pengaduans.id_kelurahan')
                    ->select('pengaduans.*', 'b.name_village','d.name_districts');
            }elseif ($user_wilayah->name == 'Back Ofiice Kota') {
                $query = DB::table('pengaduans')
                    ->join('users', 'users.id', '=', 'pengaduans.createdby')
                    ->join('indonesia_districts as d', 'd.code', '=', 'pengaduans.id_kecamatan')
                    ->join('indonesia_villages as b', 'b.code', '=', 'pengaduans.id_kelurahan')
                    ->select('pengaduans.*', 'b.name_village','d.name_districts');
            }else {
                $query = DB::table('pengaduans')
                    ->join('users', 'users.id', '=', 'pengaduans.createdby')
                    ->join('indonesia_villages as b', 'b.code', '=', 'pengaduans.id_kelurahan')
                    ->select('pengaduans.*', 'b.name_village');
            }
        // dd($query);
            if ($user_wilayah->name == 'fasilitator' ) {
                // dd($user_wilayah->role_id); 
                $query->orWhere(function($query) use ($user_wilayah) {
                       $query->where('pengaduans.id_kelurahan', '=' , $user_wilayah->kelurahan_id)
                             ->where('pengaduans.tujuan', '=' , $user_wilayah->role_id)
                             ->where('pengaduans.petugas', '=' , auth::user()->id)
                            ->where(function($query){
                                $query->where('pengaduans.status_aksi', '=', 'Teruskan')
                                      ->orWhere('pengaduans.status_aksi', '=', 'kembalikan');
                            }); 
                 });
                // dd($query);
            }
            if ($user_wilayah->name == 'Front Office kota' ) {
                //  dd($user_wilayah->role_id);
                
                $query->orWhere(function($query) use ($user_wilayah) {
                       $query->where('pengaduans.id_kabkot', '=' , $user_wilayah->kota_id)
                                ->where('pengaduans.tujuan', '=' ,$user_wilayah->role_id)
                                ->where('pengaduans.petugas', '=' , auth::user()->id)
                                ->where(function($query){
                                    $query->where('pengaduans.status_aksi', '=', 'Teruskan')
                                          ->orWhere('pengaduans.status_aksi', '=', 'kembalikan');
                                });
                    });

            }
            if ($user_wilayah->name == 'Front Office Kelurahan' ) {
                //  dd($user_wilayah->role_id);
                $query->orWhere(function($query) use ($user_wilayah) {
                    $query->where('pengaduans.id_kelurahan', '=' , $user_wilayah->kelurahan_id)
                          ->where('pengaduans.tujuan', '=' , $user_wilayah->role_id)
                          ->where('pengaduans.petugas', '=' , auth::user()->id)
                         ->where(function($query){
                             $query->where('pengaduans.status_aksi', '=', 'Teruskan')
                                   ->orWhere('pengaduans.status_aksi', '=', 'kembalikan');
                         }); 
                });

            }
            if ($user_wilayah->name == 'Back Ofiice kelurahan' ) {
                // dd(auth::user()->id);
                $query->orWhere(function($query) use ($user_wilayah) {
                    $query->where('pengaduans.id_kelurahan', '=' , $user_wilayah->kelurahan_id)
                    ->where('pengaduans.tujuan', '=' , $user_wilayah->role_id)
                    ->where('pengaduans.petugas', '=' , auth::user()->id)
                    ->where(function($query){
                       $query->where('pengaduans.status_aksi', '=', 'Teruskan')
                             ->orWhere('pengaduans.status_aksi', '=', 'kembalikan');
                   }); 
                    // dd($va);
                });
            }
            if ($user_wilayah->name == 'Back Ofiice Kota' ) {
                
                $query->orWhere(function($query) use ($user_wilayah) {
                    $query->where('pengaduans.id_kabkot', '=' , $user_wilayah->kota_id)
                    ->where('pengaduans.tujuan', '=' , $user_wilayah->role_id)
                    ->where('pengaduans.petugas', '=' , auth::user()->id)
                    ->where(function($query){
                       $query->where('pengaduans.status_aksi', '=', 'Teruskan')
                             ->orWhere('pengaduans.status_aksi', '=', 'kembalikan');
                   }); 
                    // dd($va);
                });
            }
            if ($user_wilayah->name == 'supervisor' ) {
                // dd($user_wilayah);
                $query->orWhere(function($query) use ($user_wilayah) {
                    $query->where('pengaduans.id_kelurahan', '=' , $user_wilayah->kelurahan_id)
                    ->where('pengaduans.tujuan', '=' , $user_wilayah->role_id)
                    ->where('pengaduans.petugas', '=' , $user_wilayah->model_id)
                    ->where(function($query){
                       $query->where('pengaduans.status_aksi', '=', 'Teruskan')
                             ->orWhere('pengaduans.status_aksi', '=', 'kembalikan');
                   }); 
                    // dd($va);
                });
            }
            if ($user_wilayah->name == 'kepala bidang' ) {
                // dd($user_wilayah);
                  $query->orWhere(function($query) use ($user_wilayah) {
                    $query->where('pengaduans.id_kelurahan', '=' , $user_wilayah->kelurahan_id)
                    ->where('pengaduans.tujuan', '=' , $user_wilayah->role_id)
                    ->where('pengaduans.petugas', '=' , $user_wilayah->model_id)
                    ->where(function($query){
                       $query->where('pengaduans.status_aksi', '=', 'Teruskan')
                             ->orWhere('pengaduans.status_aksi', '=', 'kembalikan');
                   }); 
                    // dd($va);
                });
            }
            if ($request->has('search') && !empty($request->search['value'])) {
                $search = $request->search['value'];
                $query = DB::table('pengaduans')
                ->join('users', 'users.id', '=', 'pengaduans.createdby')
                ->join('indonesia_districts as d', 'd.code', '=', 'pengaduans.id_kecamatan')
                ->join('indonesia_villages as b', 'b.code', '=', 'pengaduans.id_kelurahan')
                ->select('pengaduans.*', 'b.name_village','d.name_districts')
                ->where(function($query) use ($search) {
                    $query->where('pengaduans.no_pendaftaran', 'like', "%$search%");
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
        // dd($data);
        // mengubah data JSON menjadi objek PHP

        return response()->json([
            'draw' => $request->input('draw'),
            'recordsTotal' => Pengaduan::count(),
            'recordsFiltered' => $total_filtered_items,
            'data' => $data,
        ]);
       
    }

    public function teruskan(Request $request)
    {
        $user_name = Auth::user()->name;
        // dd($user_name);

        $query = DB::table('pengaduans')
                ->join('users', 'users.id', '=', 'pengaduans.createdby')
                ->join('log_pengaduan', 'log_pengaduan.id_trx_pengaduan', '=', 'pengaduans.id')
                // ->join('model_has_roles', 'model_has_roles.role_id', '=', 'pengaduans.tujuan')
                ->join('indonesia_villages as b', 'b.code', '=', 'pengaduans.id_kelurahan')
                ->select('pengaduans.*', 'b.name_village');
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
            if ($user_wilayah->name == 'fasilitator' ) {
                // dd($user_wilayah->model_id);
                $query = DB::table('pengaduans')
                    ->join('users', 'users.id', '=', 'pengaduans.createdby')
                    ->join('log_pengaduan', 'log_pengaduan.id_trx_pengaduan', '=', 'pengaduans.id')
                    // ->join('model_has_roles', 'model_has_roles.model_id', '=', 'pengaduans.tujuan')
                    ->join('indonesia_villages as b', 'b.code', '=', 'pengaduans.id_kelurahan')
                    ->select('pengaduans.*','b.name_village','log_pengaduan.tujuan','log_pengaduan.petugas' )
                    ->orWhere(function($query) use ($user_wilayah) {
                        $query->where('pengaduans.id_kelurahan', $user_wilayah->kelurahan_id)
                                    ->where('pengaduans.tujuan','!=', $user_wilayah->role_id)
                                    ->where('log_pengaduan.petugas','=', auth::user()->id)
                                    // ->where('pengaduans.petugas','!=', $user_wilayah->model_id)
                                    ->where(function($query){
                                        $query->where('pengaduans.status_aksi', '=', 'Teruskan')
                                            ->orWhere('pengaduans.status_aksi', '=', 'kembalikan');
                                    });
                    })->distinct();
            }
            //front office kota
            if ($user_wilayah->name == 'Front Office kota' ) {
                // dd($user_wilayah->model_id);
                $query = DB::table('pengaduans')
                ->join('users', 'users.id', '=', 'pengaduans.createdby')
                ->join('log_pengaduan', 'log_pengaduan.id_trx_pengaduan', '=', 'pengaduans.id')
                // ->join('model_has_roles', 'model_has_roles.model_id', '=', 'pengaduans.tujuan')
                ->join('indonesia_villages as b', 'b.code', '=', 'pengaduans.id_kelurahan')
                ->select('pengaduans.*','b.name_village')
                ->orWhere(function($query) use ($user_wilayah) {
                    $query->where('pengaduans.id_kelurahan', $user_wilayah->kelurahan_id)
                                ->where('log_pengaduan.tujuan','!=', $user_wilayah->role_id)
                                ->where('log_pengaduan.created_by','=', auth::user()->id)
                                // ->where('pengaduans.petugas','!=', $user_wilayah->model_id)
                                ->where(function($query){
                                    $query->where('pengaduans.status_aksi', '=', 'Teruskan')
                                        ->orWhere('pengaduans.status_aksi', '=', 'kembalikan');
                                });
                });
                // ->get();
                // dd($query);
            }
            //front-office-kelurahan
            if ($user_wilayah->name == 'Front Office Kelurahan' ) {
                // dd($user_wilayah->model_id);
                $query = DB::table('pengaduans')
                ->join('users', 'users.id', '=', 'pengaduans.createdby')
                // ->join('log_pengaduan', 'log_pengaduan.id_trx_pengaduan', '=', 'pengaduans.id')
                // ->join('model_has_roles', 'model_has_roles.model_id', '=', 'pengaduans.tujuan')
                ->join('indonesia_villages as b', 'b.code', '=', 'pengaduans.id_kelurahan')
                ->select('pengaduans.*','b.name_village')
                ->orWhere(function($query) use ($user_wilayah) {
                    $query->where('pengaduans.id_kelurahan', $user_wilayah->kelurahan_id)
                                ->where('pengaduans.tujuan','!=', $user_wilayah->role_id)
                                ->where('pengaduans.createdby','=', auth::user()->id)
                                // ->where('pengaduans.petugas','!=', $user_wilayah->model_id)
                                ->where(function($query){
                                    $query->where('pengaduans.status_aksi', '=', 'Teruskan')
                                        ->orWhere('pengaduans.status_aksi', '=', 'kembalikan');
                                });
                })->distinct();
            }
            if ($user_wilayah->name == 'Back Ofiice kelurahan' ) {
                // dd($user_wilayah->kelurahan_id);
               
                $query = DB::table('pengaduans')
                ->join('users', 'users.id', '=', 'pengaduans.createdby')
                ->join('log_pengaduan', 'log_pengaduan.id_trx_pengaduan', '=', 'pengaduans.id')
                // ->join('model_has_roles', 'model_has_roles.model_id', '=', 'pengaduans.tujuan')
                ->join('indonesia_villages as b', 'b.code', '=', 'pengaduans.id_kelurahan')
                ->select('pengaduans.*','b.name_village')
                ->orWhere(function($query) use ($user_wilayah) {
                    $query->where('pengaduans.id_kelurahan', $user_wilayah->kelurahan_id)
                                ->where('log_pengaduan.tujuan','!=', $user_wilayah->role_id)
                                ->where('log_pengaduan.created_by','=', auth::user()->id)
                                ->where(function($query){
                                    $query->where('pengaduans.status_aksi', '=', 'Teruskan')
                                        ->orWhere('pengaduans.status_aksi', '=', 'kembalikan');
                                });
                })->distinct();
            }
            if ($user_wilayah->name == 'Back Ofiice Kota' ) {
                // dd( $user_wilayah->role_id);
                $query = DB::table('pengaduans')
                ->join('users', 'users.id', '=', 'pengaduans.createdby')
                ->join('log_pengaduan', 'log_pengaduan.id_trx_pengaduan', '=', 'pengaduans.id')
                // ->join('model_has_roles', 'model_has_roles.model_id', '=', 'pengaduans.tujuan')
                ->join('indonesia_villages as b', 'b.code', '=', 'pengaduans.id_kelurahan')
                ->select('pengaduans.*','b.name_village')
                ->orWhere(function($query) use ($user_wilayah) {
                    $query->where('pengaduans.id_kelurahan', $user_wilayah->kelurahan_id)
                                ->where('log_pengaduan.tujuan','!=', $user_wilayah->role_id)
                                ->where('log_pengaduan.created_by','=', auth::user()->id)
                                // // ->where('pengaduans.petugas','!=', $user_wilayah->model_id)
                                ->where(function($query){
                                    $query->where('pengaduans.status_aksi', '=', 'Teruskan')
                                        ->orWhere('pengaduans.status_aksi', '=', 'kembalikan');
                                });
                })->distinct();
            }
            if ($user_wilayah->name == 'supervisor' ) {
                // dd($user_wilayah);
                $query = DB::table('pengaduans')
                ->join('users', 'users.id', '=', 'pengaduans.createdby')
                ->join('log_pengaduan', 'log_pengaduan.id_trx_pengaduan', '=', 'pengaduans.id')
                // ->join('model_has_roles', 'model_has_roles.model_id', '=', 'pengaduans.tujuan')
                ->join('indonesia_villages as b', 'b.code', '=', 'pengaduans.id_kelurahan')
                ->select('pengaduans.*','b.name_village')
                ->orWhere(function($query) use ($user_wilayah) {
                    $query->where('pengaduans.id_kelurahan', $user_wilayah->kelurahan_id)
                                ->where('log_pengaduan.tujuan','!=', $user_wilayah->role_id)
                                ->where('log_pengaduan.created_by','=', auth::user()->id)
                                // // ->where('pengaduans.petugas','!=', $user_wilayah->model_id)
                                ->where(function($query){
                                    $query->where('pengaduans.status_aksi', '=', 'Teruskan')
                                        ->orWhere('pengaduans.status_aksi', '=', 'kembalikan');
                                });
                })->distinct();
            }
            if ($user_wilayah->name == 'kepala bidang' ) {
                //  dd(auth::user()->id);
                 $query = DB::table('pengaduans')
                 ->join('users', 'users.id', '=', 'pengaduans.createdby')
                 ->join('log_pengaduan', 'log_pengaduan.id_trx_pengaduan', '=', 'pengaduans.id')
                //  ->join('model_has_roles', 'model_has_roles.model_id', '=', 'pengaduans.tujuan')
                 ->join('indonesia_villages as b', 'b.code', '=', 'pengaduans.id_kelurahan')
                 ->join('indonesia_districts as d', 'd.code', '=', 'pengaduans.id_kecamatan')
                 ->select('pengaduans.*','b.name_village')
                 ->orWhere(function($query) use ($user_wilayah) {
                     $query->where('pengaduans.id_kabkot', $user_wilayah->kota_id)
                                 ->where('log_pengaduan.tujuan','!=', $user_wilayah->role_id)
                                 ->where('log_pengaduan.created_by','=', auth::user()->id)
                                 // ->where('pengaduans.petugas','!=', $user_wilayah->model_id)
                                 ->where(function($query){
                                     $query->where('pengaduans.status_aksi', '=', 'Teruskan')
                                         ->orWhere('pengaduans.status_aksi', '=', 'kembalikan');
                                 });
                 })->distinct();
       
            }
            if ($request->has('search') && !empty($request->search['value'])) {
                $search = $request->search['value'];
                $query = DB::table('pengaduans')
                ->join('users', 'users.id', '=', 'pengaduans.createdby')
                ->join('log_pengaduan', 'log_pengaduan.id_trx_pengaduan', '=', 'pengaduans.id')
                // ->join('model_has_roles', 'model_has_roles.model_id', '=', 'pengaduans.tujuan')
                ->join('indonesia_villages as b', 'b.code', '=', 'pengaduans.id_kelurahan')
                ->join('indonesia_districts as d', 'd.code', '=', 'pengaduans.id_kecamatan')
                ->select('pengaduans.*','b.name_village','d.name_districts','log_pengaduan.tujuan','log_pengaduan.petugas' )
                ->where(function($query) use ($search) {
                    $query->where('pengaduans.no_pendaftaran', 'like', "%$search%");
                });
            
            }
        $total_filtered_items = $query->count();
        if ($request->has('order')) {
            $order_column = $request->order[0]['column'];
            $order_direction = $request->order[0]['dir'];
            $query->orderBy($request->input('columns.' . $order_column . '.data'), $order_direction);
        }
       $start = $request->start;
        $length = $request->length;
        $query->offset($start)->limit($length);
        $data = $query->get();
        // dd($data);
        return response()->json([
            'draw' => $request->input('draw'),
            'recordsTotal' => Pengaduan::count(),
            'recordsFiltered' => $total_filtered_items,
            'data' => $data,
        ]);
    }

    public function selesai(Request $request)
    {
        $user_name = Auth::user()->name;
        $query = DB::table('pengaduans')
            ->join('users', 'users.id', '=', 'pengaduans.createdby')
            ->join('log_pengaduan', 'log_pengaduan.id_trx_pengaduan', '=', 'pengaduans.id')
            ->join('model_has_roles', 'model_has_roles.model_id', '=', 'pengaduans.tujuan')
            ->join('indonesia_villages as b', 'b.code', '=', 'pengaduans.id_kelurahan')
            ->select('pengaduans.*','b.name_village');
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
            if ($user_wilayah->name == 'fasilitator' ) {
                $query = DB::table('pengaduans')
                        ->join('users', 'users.id', '=', 'pengaduans.createdby')
                        ->join('log_pengaduan', 'log_pengaduan.id_trx_pengaduan', '=', 'pengaduans.id')
                        ->join('indonesia_villages as b', 'b.code', '=', 'pengaduans.id_kelurahan')
                        ->select('pengaduans.*','b.name_village','log_pengaduan.tujuan','log_pengaduan.petugas' )
                        ->orWhere(function($query) use ($user_wilayah) {
                            $query->where('pengaduans.id_kelurahan', $user_wilayah->kelurahan_id)
                                        ->where('log_pengaduan.tujuan','=', $user_wilayah->role_id)
                                        ->where('log_pengaduan.created_by','!=', $user_wilayah->model_id)
                                        ->where(function($query){
                                            $query->where('pengaduans.status_aksi', '=', 'Tolak')
                                                ->orWhere('pengaduans.status_aksi', '=', 'Selesai');
                                        });
                        })->distinct();
            }elseif ($user_wilayah->name == 'Front Office Kelurahan' ) {
                //  dd($user_wilayah->role_id);
                $query = DB::table('pengaduans')
                    ->join('users', 'users.id', '=', 'pengaduans.createdby')
                    ->join('log_pengaduan', 'log_pengaduan.id_trx_pengaduan', '=', 'pengaduans.id')
                    ->join('indonesia_villages', 'indonesia_villages.code', '=', 'pengaduans.id_kelurahan')
                    ->join('indonesia_districts as d', 'd.code', '=', 'pengaduans.id_kecamatan')
                    ->select('pengaduans.*','d.name_districts','indonesia_villages.name_village','log_pengaduan.tujuan','log_pengaduan.petugas' )
                    ->orWhere(function($query) use ($user_wilayah) {
                        $query->where('pengaduans.id_kelurahan', $user_wilayah->kelurahan_id)
                                    ->where('log_pengaduan.tujuan','=', $user_wilayah->role_id)
                                    ->where('log_pengaduan.created_by','!=', $user_wilayah->model_id)
                                    ->where(function($query){
                                        $query->where('pengaduans.status_aksi', '=', 'Tolak')
                                            ->orWhere('pengaduans.status_aksi', '=', 'Selesai');
                                    });
                    });

            }elseif ($user_wilayah->name == 'Front Office kota' ) {
                //  dd($user_wilayah->role_id);
                $query = DB::table('pengaduans')
                    ->join('users', 'users.id', '=', 'pengaduans.createdby')
                    ->join('log_pengaduan', 'log_pengaduan.id_trx_pengaduan', '=', 'pengaduans.id')
                    ->join('indonesia_villages', 'indonesia_villages.code', '=', 'pengaduans.id_kelurahan')
                    ->join('indonesia_districts as d', 'd.code', '=', 'pengaduans.id_kecamatan')
                    ->select('pengaduans.*','d.name_districts','indonesia_villages.name_village','log_pengaduan.tujuan','log_pengaduan.petugas' )
                    ->orWhere(function($query) use ($user_wilayah) {
                        $query->where('pengaduans.id_kelurahan', $user_wilayah->kelurahan_id)
                                    ->where('log_pengaduan.tujuan','=', $user_wilayah->role_id)
                                    ->where('log_pengaduan.created_by','!=', $user_wilayah->model_id)
                                    ->where(function($query){
                                        $query->where('pengaduans.status_aksi', '=', 'Tolak')
                                            ->orWhere('pengaduans.status_aksi', '=', 'Selesai');

                        });
                    })->distinct();
            }elseif ($user_wilayah->name == 'Back Ofiice kelurahan' ) {
                // dd($user_wilayah);
                $query = DB::table('pengaduans')
                        ->join('users', 'users.id', '=', 'pengaduans.createdby')
                        ->join('log_pengaduan', 'log_pengaduan.id_trx_pengaduan', '=', 'pengaduans.id')
                        // ->join('model_has_roles', 'model_has_roles.model_id', '=', 'pengaduans.tujuan')
                        ->join('indonesia_villages as b', 'b.code', '=', 'pengaduans.id_kelurahan')
                        ->select('pengaduans.*','b.name_village','log_pengaduan.tujuan','log_pengaduan.petugas' )
                        ->orWhere(function($query) use ($user_wilayah) {
                            $query->where('pengaduans.id_kelurahan', $user_wilayah->kelurahan_id)
                                         ->where('log_pengaduan.tujuan','!=', $user_wilayah->role_id)
                                         ->where('log_pengaduan.created_by','=', auth::user()->id)
                                         ->where(function($query){
                                             $query->where('pengaduans.status_aksi', '=', 'Tolak')
                                                   ->orWhere('pengaduans.status_aksi', '=', 'Selesai');
                                         });
                        })->distinct();
                // dd($query); 
            }elseif ($user_wilayah->name == 'supervisor' ) {
                // dd($user_wilayah);
                $query = DB::table('pengaduans')
                ->join('users', 'users.id', '=', 'pengaduans.createdby')
                ->join('log_pengaduan', 'log_pengaduan.id_trx_pengaduan', '=', 'pengaduans.id')
                // ->join('model_has_roles', 'model_has_roles.model_id', '=', 'pengaduans.tujuan')
                ->join('indonesia_villages as b', 'b.code', '=', 'pengaduans.id_kelurahan')
                ->select('pengaduans.*','b.name_village','log_pengaduan.tujuan','log_pengaduan.petugas' )
                ->orWhere(function($query) use ($user_wilayah) {
                    $query->where('pengaduans.id_kelurahan', $user_wilayah->kelurahan_id)
                                 ->where('log_pengaduan.tujuan','!=', $user_wilayah->role_id)
                                 ->where('log_pengaduan.created_by','=', auth::user()->id)
                                 ->where(function($query){
                                     $query->where('pengaduans.status_aksi', '=', 'Tolak')
                                           ->orWhere('pengaduans.status_aksi', '=', 'Selesai');
                                 });
                })->distinct();
               
            }elseif ($user_wilayah->name == 'Back Ofiice Kota' ) {
                // dd($user_wilayah->role_id);
                $query = DB::table('pengaduans')
                        ->join('users', 'users.id', '=', 'pengaduans.createdby')
                        ->join('log_pengaduan', 'log_pengaduan.id_trx_pengaduan', '=', 'pengaduans.id')
                        // ->join('model_has_roles', 'model_has_roles.model_id', '=', 'pengaduans.tujuan')
                        ->join('indonesia_villages as b', 'b.code', '=', 'pengaduans.id_kelurahan')
                        ->select('pengaduans.*','b.name_village','log_pengaduan.tujuan','log_pengaduan.petugas' )
                        ->orWhere(function($query) use ($user_wilayah) {
                            $query->where('pengaduans.id_kelurahan', $user_wilayah->kelurahan_id)
                                         ->where('log_pengaduan.tujuan','!=', $user_wilayah->role_id)
                                         ->where('log_pengaduan.created_by','=', auth::user()->id)
                                         ->where(function($query){
                                             $query->where('pengaduans.status_aksi', '=', 'Tolak')
                                                   ->orWhere('pengaduans.status_aksi', '=', 'Selesai');
                                         });
                        })->distinct();
                    }elseif ($user_wilayah->name == 'kepala bidang' ) {
                        // dd($user_wilayah);
                        $query = DB::table('pengaduans')
                                ->join('users', 'users.id', '=', 'pengaduans.createdby')
                                ->join('log_pengaduan', 'log_pengaduan.id_trx_pengaduan', '=', 'pengaduans.id')
                                // ->join('model_has_roles', 'model_has_roles.model_id', '=', 'pengaduans.tujuan')
                                ->join('indonesia_villages as b', 'b.code', '=', 'pengaduans.id_kelurahan')
                                ->select('pengaduans.*','b.name_village','log_pengaduan.tujuan','log_pengaduan.petugas' )
                                ->orWhere(function($query) use ($user_wilayah) {
                                    $query->where('pengaduans.id_kelurahan', $user_wilayah->kelurahan_id)
                                                 ->where('log_pengaduan.tujuan','=', $user_wilayah->role_id)
                                                 ->where('log_pengaduan.petugas','=', $user_wilayah->model_id)
                                                 ->where(function($query){
                                                     $query->where('pengaduans.status_aksi', '=', 'Tolak')
                                                           ->orWhere('pengaduans.status_aksi', '=', 'Selesai');
                                                 });
                                });
                            }
           
        if ($request->has('search') && !empty($request->search['value'])) {
            $search = $request->search['value'];
            $query = DB::table('pengaduans')
            ->join('users', 'users.id', '=', 'pengaduans.createdby')
            ->join('wilayahs', 'wilayahs.createdby', '=', 'users.id')
            ->join('model_has_roles', 'model_has_roles.model_id', '=', 'pengaduans.tujuan')
            ->join('indonesia_villages as b', 'b.code', '=', 'pengaduans.id_kelurahan')
            ->select('pengaduans.*','b.name_village')
            ->where(function($query) use ($search) {
                $query->where('pengaduans.no_pendaftaran', 'like', "%$search%");
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
        // dd($data);
        // mengubah data JSON menjadi objek PHP

        return response()->json([
            'draw' => $request->input('draw'),
            'recordsTotal' => Pengaduan::count(),
            'recordsFiltered' => $total_filtered_items,
            'data' => $data,
        ]);
    }
    public function prelistPage(Request $request){
        return view('prelist.index');
    }
    public function prelistDTKS(Request $request)
    {
        $columns = [
            // daftar kolom yang akan ditampilkan pada tabel
            'id_provinsi',
            'id_kabkot',
            'id_kecamatan',
            'id_kelurahan',
            'nik',
            'no_kk',
            // 'no_kis',
            'nama',
            'tgl_lahir',
            // 'alamat',
            'telp',
            'status_data',
            'email'
        ];

        // $query = Prelist::where('status_data', 'prelistdtks');
        $query = DB::table('prelist')
            ->join('indonesia_districts as a', 'a.code', '=', 'prelist.id_kecamatan')
            ->join('indonesia_villages as b', 'b.code', '=', 'prelist.id_kelurahan')
            ->select('prelist.*','a.name_districts','b.name_village');
        // dd($query);
        // menambahkan kondisi pencarian jika ada
        if ($request->has('search')) {
            $searchValue = $request->search['value'];
            $query->where(function ($query) use ($columns, $searchValue) {
                foreach ($columns as $column) {
                    $query->orWhere($column, 'like', '%' . $searchValue . '%');
                }
            });
        }
        // menambahkan kondisi sortir jika ada
        if ($request->has('order')) {
            $orderColumn = $columns[$request->order[0]['column']];
            $orderDirection = $request->order[0]['dir'];
            $query->orderBy($orderColumn, $orderDirection);
        }

        // mengambil data sesuai dengan paginasi yang diminta
        $perPage = $request->length ?: config('app.pagination.per_page');
        $currentPage = $request->start ? ($request->start / $perPage) + 1 : 1;
        $data = $query->paginate($perPage, ['*'], 'page', $currentPage);

        // memformat data untuk dikirim ke client
        $no = 1;
        $formattedData = [];
        foreach ($data as $item) {
            $formattedData[] = [
                'no' => $no++,
                'id_provinsi' => $item->id_provinsi,
                'id_kabkot' => $item->id_kabkot,
                'id_kecamatan' => $item->name_village,
                'id_kelurahan' => $item->name_districts,
                'nik' => $item->nik,
                'no_kk' => $item->no_kk,
                // 'no_kis' => $item->no_kis,
                'nama' => $item->nama,
                'tgl_lahir' => $item->tgl_lahir,
                // 'alamat' => $item->alamat,
                'telp' => $item->telp,
                'email' => $item->email,
            ];
        }
    // mengembalikan data dalam format JSON
        return response()->json([
            'draw' => $request->draw,
            'recordsTotal' => Pengaduan::count(),
            'recordsFiltered' => $data->total(),
            'data' => $formattedData
        ]);

    }
    public function detail_pengaduan($id)
    {
        $data2 = DB::table('pengaduans as w')->select(
            'w.*',
            'b.name_village',
            'prov.name_prov',
            'kota.name_cities',
            'kecamatan.name_districts',
            // 'w.status_wilayah',
        )
        ->leftjoin('indonesia_provinces as prov', 'prov.code', '=', 'w.id_provinsi')
        ->leftjoin('indonesia_cities as kota', 'kota.code', '=', 'w.id_kabkot')
        ->leftjoin('indonesia_districts as kecamatan', 'kecamatan.code', '=', 'w.id_kecamatan')
        ->leftjoin('indonesia_villages as b', 'b.code', '=', 'w.id_kelurahan')
        ->where('w.id', $id)->first();
        $data = [
            'data' => $data2
            // 'data' => $data2
          ];
        return response()->json($data);
    }
    public function log_detail_pengaduan(Request $request, $id)
    {
        $user_name = Auth::user()->name;
            $query = DB::table('pengaduans')
                // ->join('users', 'users.id', '=', 'pengaduans.createdby')
                ->join('log_pengaduan', 'log_pengaduan.id_trx_pengaduan', '=', 'pengaduans.id')
                ->select('pengaduans.*');
    
            // dd($user_wilayah);
                // Add where conditions based on user's wilayah data

        
        if ($request->has('search') && !empty($request->search['value'])) {
            $search = $request->search['value'];
            $query = DB::table('pengaduans')
            // ->join('users', 'users.id', '=', 'pengaduans.createdby')
            ->join('log_pengaduan', 'log_pengaduan.id_trx_pengaduan', '=', 'pengaduans.id')
            ->select('pengaduans.*')
            ->where(function($query) use ($search) {
                $query->where('pengaduans.no_pendaftaran', 'like', "%$search%");
            });
        
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
        // dd($query->paginate());
        //$start = $request->start;
        $length = $request->length;
        $query->offset($start)->limit($length);
        $data = $query->get();
        // dd($data);
        // mengubah data JSON menjadi objek PHP
        $data = DB::table('log_pengaduan')
        ->join('users as a', 'a.id', '=', 'log_pengaduan.created_by')
        // ->join('users as b', 'b.id', '=', 'pengaduans.createdby')
        ->join('pengaduans', 'pengaduans.id', '=', 'log_pengaduan.id_trx_pengaduan')
        ->select('a.name', 'pengaduans.status_aksi','pengaduans.tl_file','pengaduans.tl_catatan','pengaduans.created_at')
        // ->select('a.name')
        ->where('log_pengaduan.id_trx_pengaduan',$id)->get();
        dd($data);
        return response()->json([
            'draw' => $request->input('draw'),
            'recordsTotal' => Pengaduan::count(),
            'recordsFiltered' => $total_filtered_items,
            'data' => $data,
        ]);
    }
}