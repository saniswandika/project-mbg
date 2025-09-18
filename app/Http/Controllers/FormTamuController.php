<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\laporan_tamu;
use Illuminate\Support\Facades\DB;
class FormTamuController extends Controller
{
    public function index()
    {
        // $formulir = formulir::latest()->paginate(5);
        return view('formulir.index');
    }
    public function public()
    {
        // $formulir = formulir::latest()->paginate(5);
        $laporan_tamu = laporan_tamu::all();
        return view('formulir.public',compact('laporan_tamu'));
    }
    public function store(Request $request)
    {
        
        request()->validate([
            'nama_tamu' => 'required',
            'telepon' => 'required',
            'kantor_instansi' => 'required',
            'bidang' => 'required',
            'pegawai' => 'required',
            'keperluan' => 'required',
            'penilaian_tamu' => 'required'
        ]);
        // dd($request);
        laporan_tamu::create($request->all());
    
        return redirect()->route('FormTamu.index')
                        ->with('success','laporan tamu berhasil di buat .');
    }
    public function edit(Request $request, $id)
    {
     
        $laporan_tamu = laporan_tamu::find($id);
        $laporan_tamu->penilaian_tamu = $request->input('penilaian_tamu');
        // dd($laporan_tamu);
        $laporan_tamu->save();
    
    
        return redirect()->back()
                        ->with('success','laporan_tamu updated successfully');
        // dd($laporan_tamu);
        // return redirect()->back()->with('success','Product updated successfully');
    }
    public function show(laporan_tamu $laporan_tamu)
    {
        // dd($laporan_tamu);
        // $laporan_tamu->delete();
    
        return redirect()->route('FormTamu.index')->with('success','laporan tamu berhasil di buat .');
    }
    public function destroy($id)
    {
        // dd($id);
        DB::table("laporan_tamus")->where('id',$id)->delete();
    
        return redirect()->back()->with('success','laporan tamu berhasil di buat .');
    }
}
