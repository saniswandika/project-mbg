<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Arr;
use App\Exports\UsersExport;
use App\Imports\DtkssImport;
use App\Models\wilayah;
use Illuminate\Support\Facades\Validator;

use Maatwebsite\Excel\HeadingRowImport;

class RegisterController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // $jadwals = Event::all();
        $data = User::orderBy('id', 'DESC')->paginate(5);

        $roles = DB::table('roles')->get();
        // dd($roles);
        // $roles = Role::pluck('name','name')->all();
        return view('users.index', compact('data', 'roles'))
            ->with('i', ($request->input('page', 1) - 1) * 5);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //= Event::all();
        $roles = DB::table('roles')->where('name','=','warga')->first();
        $province = DB::table('indonesia_provinces')->where('code', '32')->get();
        $kota = DB::table('indonesia_cities')->where('code', '3273')->get();
        $kecamatans = DB::table('indonesia_districts')->where('city_code', '3273')->get();
        $kelurahans = DB::table('indonesia_villages')->where('district_code', '327301')->get();
        return view('auth.register',compact('roles','province','kota','kecamatans','kelurahans'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'roles' => ['required', 'string', 'max:255'],
            'kota_id'=>['required', 'string', 'max:255'],
            'province_id' => ['required', 'string', 'max:255'],
            'kecamatan_id' => ['required', 'string', 'max:255'],
            'kelurahan_id' => ['required', 'string', 'max:255'],
            'roles' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'roles' => ['required', 'string', 'max:255'],
            'kota_id' => ['required', 'string', 'max:255'],
            'province_id' => ['required', 'string', 'max:255'],
            'kecamatan_id' => ['required', 'string', 'max:255'],
            'kelurahan_id' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
    
        $user = User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
        ]);
    
        $user->assignRole($request->input('roles'));
    
        $wilayah = Wilayah::create([
            'province_id' => $request->input('province_id'),
            'kota_id' => $request->input('kota_id'),       
            'kecamatan_id' => $request->input('kecamatan_id'),
            'kelurahan_id' => $request->input('kelurahan_id'),
            'status_wilayah' => 1,
            'createdby' => $user->id,
        ]);
    
        return view('auth.login');
    }
    

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
    
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {

    }
}
