<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
class WebAuthnController extends Controller
{
    public function index(Request $request)
    {
        return view('absensi.index');
    }
    public function absensi(Request $request)
    {
        return view('absensi.absen');
    }
     public function generate(Request $request)
    {
        $challenge = base64_encode(random_bytes(32));

        // Simpan challenge ke session untuk validasi nanti
        session(['webauthn_challenge' => $challenge]);

        return response()->json([
            'challenge' => $challenge,
            'rp' => ['name' => 'Absensi Pegawai'],
            'user' => [
                'id' => base64_encode(Auth::id()),
                'name' => Auth::user()->email,
                'displayName' => Auth::user()->name,
            ],
            'pubKeyCredParams' => [['alg' => -7, 'type' => 'public-key']],
            'timeout' => 60000,
            'attestation' => 'direct',
        ]);
    }

    public function register(Request $request)
    {
        $data = $request->all();

        DB::table('absensis')->insert([
            'user_id' => Auth::id(),
            'waktu_absen' => now(),
            'status' => 'hadir',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['success' => true]);
    }
}
