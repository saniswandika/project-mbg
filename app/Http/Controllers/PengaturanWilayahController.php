<?php

namespace App\Http\Controllers;

use App\Models\wilayah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
class PengaturanWilayahController extends Controller
{
     /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function listwilayah(Request $request)
    {   
        $user_id =  Auth::user()->id;
        $wilayah = DB::table('wilayahs as w')->select(
            'w.id',
            'b.name_village',
            'prov.name_prov',
            'kota.name_cities',
            'kecamatan.name_districts',
            'w.status_wilayah',
        )
        ->leftjoin('indonesia_provinces as prov', 'prov.code', '=', 'w.province_id')
        ->leftjoin('indonesia_cities as kota', 'kota.code', '=', 'w.kota_id')
        ->leftjoin('indonesia_districts as kecamatan', 'kecamatan.code', '=', 'w.kecamatan_id')
        ->leftjoin('indonesia_villages as b', 'b.code', '=', 'w.kelurahan_id')
        ->where('w.createdby', $user_id)->get();
        // dd($wilayah);
        return view('PengaturanWilayah.index',compact('wilayah'))
            ->with('i', ($request->input('page', 1) - 1) * 5);
    }
    public function updateStatus(Request $request)
    {
        $user_id =  Auth::user()->id;
        $wilayahkeseluruaha = wilayah::where('createdby', $user_id)->get();
        // foreach ($wilayahkeseluruaha as $key => $value) {
        //     if ($value->status_wilayah == '1') {
        //         // $wilayah = wilayah::findOrFail($request->wilayah_Id);
        //         $value->status_wilayah = '0';
        //         $value->save();
        //         return response()->json(['message' => 'User status updated successfully.']);
        //     }else
        //     $wilayah = wilayah::findOrFail($request->wilayah_Id);
        //     $wilayah->status_wilayah = $request->status_wilayah;
        //     $wilayah->save();
        //     return response()->json(['message' => 'User status updated successfully.']);
        // }
        foreach ($wilayahkeseluruaha as $value) {
            if ($value->id == $request->wilayah_Id) {
                // $wilayah = wilayah::findOrFail($request->wilayah_Id);
                $value->status_wilayah = '1';
                $value->save();
            }else
            $value->status_wilayah = '0';
            $value->save();
        }
        return response()->json(['message' => 'User status updated successfully.']);
        
    
        // penambahan if sebelum update
       
        
    
       
    }
    public function create()
    {
        $province = DB::table('indonesia_provinces')->where('code', '32')->get();
        $kota = DB::table('indonesia_cities')->where('code', '3273')->get();
        $kecamatans = DB::table('indonesia_districts')->where('city_code', '3273')->get();
        $kelurahans = DB::table('indonesia_villages')->where('district_code', '327301')->get();
        // dd($kecamatans);
        return view('PengaturanWilayah.create',compact('province','kota','kecamatans','kelurahans'));
    }
    public function store(Request $request)
    {
        $data['province_id'] = $request->get('province_id');
        $data['kota_id'] = $request->get('kota_id');
        // $data['end_date'] = $request->get('end_date');
        $data['kecamatan_id'] = $request->get('kecamatan_id');
        $data['kelurahan_id'] = $request->get('kelurahan_id');
        $data['createdby'] = Auth::user()->id;
        // jika wilayah nya ada tidak bisa di simpan by code kelurahan sama id user login
        $post = wilayah::create($data);
        return redirect()->route('Pengaturan_wilayah')
                        ->with('success','pengaturan wilayah berhasil ditambahkan.');
        // request()->validate([
        //     'province_id' => 'required',
        //     'kota_id' => 'required',
        //     'kecamatan_id' => 'required',
        //     'kelurahan_id' => 'required',
        //     'createdby' => 'required'
        //     // 'status' => 'required',
        // ]);
    
        // wilayah::create($request->all());
    
        // return redirect()->back()
        //                 ->with('success','Product created successfully.');
    }

    public function getKota(Request $request)
    {

        $kota = DB::table('indonesia_cities')->where('province_code', $request->provinsi)->get();
        return response()->json($kota);
    }
    public function getKecamatanByRegency($regencyId)
    {
        $kecamatan =  DB::table('indonesia_districts')->where('city_code', $regencyId)->get();
        return response()->json($kecamatan);
    }
    public function getKelurahanByRegency($kelurahanId)
    {
        $kecamatan =  DB::table('indonesia_villages')->where('district_code', $kelurahanId)->get();
        return response()->json($kecamatan);
    }
}
