<?php

namespace App\Http\Controllers;

use App\Models\laporan_tamu;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\User;
use Spatie\Permission\Models\Role;
class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {   
        $roles = User::orderBy('id','DESC')->paginate(5);

        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        
        $userid = Auth::user()->id;
        $checkuserrole = DB::table('model_has_roles')
        ->leftjoin('roles', 'roles.id', '=', 'model_has_roles.role_id')
        ->where('model_id', '=', $userid)
        ->get();
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
              return view('home',compact('checkuserrole','user_wilayah'));
    }
}
