<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\laporan_tamu;
use Illuminate\Support\Facades\DB;
class LaporanTamuController extends Controller
{
    public function index()
    {
        $laporan_tamu = laporan_tamu::all();
        // dd($laporan_tamu);
        return view('laporanTamu.index',compact('laporan_tamu'));
    }
    
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('laporan_tamu.create');
    }
    
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        request()->validate([
            'nama_tamu' => 'required',
            'telepon' => 'required',
            'kantor_instansi' => 'required',
            'bidang' => 'required',
            'pegawai' => 'required',
            'keperluan' => 'required',
        ]);
    
        laporan_tamu::create($request->all());
    
        return redirect()->route('formulir')
                        ->with('success','laporan_tamu created successfully.');
    }
    
    /**
     * Display the specified resource.
     *
     * @param  \App\laporan_tamu  $laporan_tamu
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // dd($laporan_tamu);
        DB::table("laporan_tamus")->where('id',$id)->delete();
        // return view('laporanTamu.index',compact('laporan_tamu'));
    }
    
    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\laporan_tamu  $laporan_tamu
     * @return \Illuminate\Http\Response
     */
    public function edit(laporan_tamu $laporan_tamu)
    {
        return view('laporan_tamu.edit',compact('laporan_tamu'));
    }
    
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\laporan_tamu  $laporan_tamu
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // dd($);
        request()->validate([
            'nama_tamu' => 'required',
            'telepon' => 'required',
            'kantor_instansi' => 'required',
            'bidang' => 'required',
            'pegawai' => 'required',
            'keperluan' => 'required',
        ]);
        $update['nama_tamu'] = $request->get('nama_tamu');
        $update['telepon'] = $request->get('telepon');
        $update['kantor_instansi'] = $request->get('kantor_instansi');
        $update['bidang'] = $request->get('bidang');
        $update['pegawai'] = $request->get('pegawai');
        $update['keperluan'] = $request->get('keperluan');
     
        laporan_tamu::where('id',$id)->update($update);
        // $laporan_tamu->update($request->all());
    
        return redirect()->back()
                        ->with('success','laporan_tamu updated successfully');
    }
    
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\laporan_tamu  $laporan_tamu
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // $laporan_tamu->delete();
        dd($id);
        DB::table("laporan_tamus")->where('id',$id)->delete();
        return redirect()->back()
                        ->with('success','Role deleted successfully');
        // return redirect()->route('laporan_tamus.index')
        //                 ->with('success','laporan_tamu deleted successfully');
    }
}
