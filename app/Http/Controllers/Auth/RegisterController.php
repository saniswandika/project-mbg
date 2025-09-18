<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Models\User;
use App\Models\wilayah;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
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

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\Models\User
     */
    protected function create(array $data)
    {
    //   dd($data);
        $user = User::create([
                    'name' => $data['name'],
                    // 'role' => $data['role'],
                    'email' => $data['email'],
                    'password' => Hash::make($data['password']),
                ]);
        $user->assignRole($data['roles']);
        $wilayah = wilayah::create([
            'province_id'=>  $data['province_id'],
            'kota_id' => $data['kota_id'],       
            'kecamatan_id' => $data['kecamatan_id'],
            'kelurahan_id'=> $data['kelurahan_id'],
            'status_wilayah'=> 1,
            'createdby'=> $user->id,
        ]);
        // jika wilayah nya ada tidak bisa di simpan by code kelurahan sama id user login
        return $user;
    }
}
