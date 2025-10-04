<?php

namespace App\Http\Controllers;

use App\Models\KeranjangBahan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CheckoutBahanController extends Controller
{
    public function addToCart(Request $request)
    {
        // Validasi input
        $validated = $request->validate([
            'jumlahAmbil' => 'required|integer|min:1',
        ]);

            $jumlahAmbil = $request->jumlahAmbil;
            $stok = $request->stok;
            // var_dump($jumlahAmbil, $stok);die();
            if($jumlahAmbil >= $stok){
                return redirect()->route('bahan_olahan.list_bahan')
                                ->with('error', 'Jumlah Terlalu banyak woyy');
            }
            $total = $stok - $jumlahAmbil;
        $save = KeranjangBahan::create([
            'id_bahan' => $request->id_bahan,
            'id_master_bahan' => $request->id_master_bahan,
            'jumlah_bahan' => $request->jumlahAmbil,
            'status' => 0,
            'id_user' => Auth::id(),
        ]);
        if($save){
                    $data = [
                        'jumlah_bahan' => $total,
                    ];
            DB::table('olahan_dapurs')->where('id', $request->id_bahan)->update($data);
        }

        return redirect()->route('bahan_olahan.list_bahan')
                         ->with('success', 'Keranjang berhasil diperbarui!');
    }

    public function showCart()
    {
        $user = FALSE;
        $admin = FALSE;
        $approve = FALSE;
        $serahkan = FALSE;
        $terima = FALSE;

        // Ambil role pengguna berdasarkan ID
        $role = $this->get_role(Auth::id());

        // Ambil data keranjang berdasarkan role pengguna menggunakan Eloquent
        if ($role != 'admin' && $role != 'superadmin') {
            $cartItems = KeranjangBahan::where('id_user', Auth::id())  // Menggunakan Eloquent Model KeranjangBahan
                                ->where(function($query) {
                                    $query->where('status', '!=', 4)  // status bukan 4
                                            ->orWhereNull('status');  // status bisa null
                                })
                                ->get();  // Mengambil data menggunakan Eloquent
        } else {
            $cartItems = KeranjangBahan::where(function($query) {
                                    $query->where('status', '!=', 4)  // status bukan 4
                                            ->orWhereNull('status');  // status bisa null
                                })
                                ->get();  // Mengambil data menggunakan Eloquent
        }

        // Pastikan ada data keranjang
        if ($cartItems->isNotEmpty()) {
            $status = $cartItems->first()->status;  // Ambil status item pertama

            if (is_null($status)) {
                $user = TRUE;
            } 
            if ($status == 1) {
                $admin = TRUE;
            }
            if ($status == 2) {
                $serahkan = TRUE;
            }
            if ($status == 3) {
                $terima = TRUE;
            }
        }

        // Kirim data ke view
        return view('keranjang.bahan_olahan.index', compact('cartItems', 'user', 'admin', 'approve', 'role', 'serahkan', 'terima'));
    }

    public function history_keranjang(Request $request)
    {
        $user = FALSE;
        $admin = FALSE;
        $approve = FALSE;

        // Ambil role pengguna berdasarkan ID
        $role = $this->get_role(Auth::id());

        // Inisialisasi query untuk mengambil data keranjang
        $query = KeranjangBahan::where('status', 4);  // Mengambil keranjang dengan status 4 (sudah disetujui)

        // Menambahkan filter berdasarkan role pengguna
        if ($role != 'admin' && $role != 'superadmin') {
            // Jika pengguna adalah kepala dapur, ambil semua keranjang pengguna tersebut
            $query->where('id_user', Auth::id());
        } elseif ($role == 'admin' || $role == 'superadmin') {
            // Jika pengguna adalah admin atau superadmin, ambil semua keranjang dengan status 4
            // Tidak ada filter tambahan
        }

        // Menentukan tanggal default jika tidak ada filter tanggal
        $startDate = $request->has('start_date') ? $request->input('start_date') . ' 00:00:00' : now()->subMonth()->format('Y-m-d') . ' 00:00:00';
        $endDate = $request->has('end_date') ? $request->input('end_date') . ' 23:59:59' : now()->format('Y-m-d') . ' 23:59:59';
        
        // Filter berdasarkan rentang tanggal
        $query->whereBetween('updated_at', [$startDate, $endDate]);

        // Ambil data keranjang berdasarkan query yang sudah difilter
        $cartItems = $query->get();
        $old_start = $request->has('start_date') ? $request->input('start_date') : now()->subMonth()->format('Y-m-d');
        $old_end = $request->has('end_date') ? $request->input('end_date') : now()->format('Y-m-d');
        // Kirim data ke view
        return view('keranjang.bahan_olahan.history', compact('cartItems', 'role', 'startDate', 'endDate', 'old_start', 'old_end'));
    }


    public function ajukan_pengambilan(Request $request)
    {
        if($request->id_user != Auth::id()){
            return redirect()->route('bahan_olahan.ambil_bahan')
                            ->with('error', 'Gagal Mengajukan');
        }else{
            $data = [
                    'status' => 1,
            ];
            $save = KeranjangBahan::where('id_user', Auth::id())->update($data);
            if($save){
                return redirect()->route('bahan_olahan.ambil_bahan')
                                ->with('success', 'Keranjang berhasil diajukan!');
            }else{
                return redirect()->route('bahan_olahan.ambil_bahan')
                                ->with('error', 'Gagal Mengajukan!');
            }
        }

    }

    public function status_change(Request $request)
    {
        if($request->id_user != Auth::id()){
            return redirect()->route('bahan_olahan.ambil_bahan')
                            ->with('error', 'Gagal Mengajukan');
        }else{
            $data = [
                    'status' => $request->status,
            ];
            $save = KeranjangBahan::where('id', $request->id)->update($data);
            if($save){
                return redirect()->route('bahan_olahan.ambil_bahan')
                                ->with('success', 'Keranjang berhasil diajukan!');
            }else{
                return redirect()->route('bahan_olahan.ambil_bahan')
                                ->with('error', 'Gagal Mengajukan!');
            }
        }

    }

    private function get_role(int $userid){
        $collection = DB::table('model_has_roles')
            ->leftJoin('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('model_id', $userid)
            ->get();

        // Ambil role_id dari koleksi
        $role_id = $collection->pluck('name')->first();
        return $role_id;
    }
}
