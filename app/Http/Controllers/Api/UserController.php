<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $columns = ['id', 'name', 'email']; // Kolom yang ingin ditampilkan

        $totalData = User::count(); // Total data tanpa filter
        $totalFiltered = $totalData; // Total data yang difilter (bisa berbeda dengan totalData)

        $users = User::orderBy('id', 'ASC');

        // Filter berdasarkan pencarian jika ada
        if ($request->has('search') && $request->input('search.value') != '') {
            $search = $request->input('search.value');
            $users = $users->where(function($query) use ($search) {
                $query->where('name', 'like', "%$search%")
                    ->orWhere('email', 'like', "%$search%");
            });
        }

        // Ambil data berdasarkan limit dan offset (pagination)
        $users = $users->offset($request->input('start'))
                    ->limit($request->input('length'))
                    ->get();

        $totalFiltered = $users->count(); // Filtered count (setelah pencarian)

        // Kembalikan data dalam format yang sesuai dengan DataTables
        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $totalData,
            'recordsFiltered' => $totalFiltered,
            'data' => $users
        ]);
    }

}
