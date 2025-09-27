<?php

namespace App\Http\Controllers;

use App\Models\Keranjang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    // Menambahkan barang ke keranjang
    public function addToCart(Request $request)
    {
        // Validasi input
        $validated = $request->validate([
            'jumlahAmbil' => 'required|integer|min:1',
        ]);

            $jumlahAmbil = $request->jumlahAmbil;
            $stok = $request->stok;
            // dd($request)->All();
            // var_dump($jumlahAmbil, $stok);die();
            if($jumlahAmbil >= $stok){
                return redirect()->route('logistik.list_barang')
                                ->with('error', 'Jumlah Terlalu banyak woyy');
            }
            $total = $stok - $jumlahAmbil;
        // Menambahkan barang ke keranjang
        $save = Keranjang::create([
            'id_barang' => $request->id_barang,
            'id_master_barang' => $request->id_master_barang,
            'jumlah_barang' => $request->jumlahAmbil,
            'id_user' => Auth::id(), // Mendapatkan ID pengguna yang sedang login
        ]);
        if($save){
                    $data = [
                        'jumlah_barang' => $total,
                    ];
            DB::table('logistiks')->where('id', $request->id_barang)->update($data);
        }

        return redirect()->route('logistik.list_barang')
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

        // Ambil data keranjang berdasarkan role pengguna
        if ($role != 'admin' && $role != 'superadmin') {
            // Jika pengguna adalah kepala dapur, ambil semua keranjang pengguna tersebut dengan status != 2
            $cartItems = Keranjang::where('id_user', Auth::id())
                                ->where('status', '!=', 4)  // Menggunakan where untuk status != 2
                                ->get();
        } elseif ($role == 'admin' || $role == 'superadmin') {
            // Jika pengguna adalah admin atau superadmin, ambil keranjang dengan status yang sesuai
            $cartItems = Keranjang::where('status', '!=', 4)  // Kondisi pertama: status != 2
                                ->get();
        }
        // dd($cartItems)->All();
        // Pastikan ada data keranjang
        if ($cartItems->isNotEmpty()) {
            // Ambil status dari item keranjang pertama
            $status = $cartItems[0]->status;

            // Tentukan status berdasarkan nilai status
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
        return view('keranjang.index', compact('cartItems', 'user', 'admin', 'approve', 'role', 'serahkan', 'terima'));
    }

    public function history_keranjang(Request $request)
    {
        $user = FALSE;
        $admin = FALSE;
        $approve = FALSE;

        // Ambil role pengguna berdasarkan ID
        $role = $this->get_role(Auth::id());

        // Inisialisasi query untuk mengambil data keranjang
        $query = Keranjang::where('status', 4);  // Mengambil keranjang dengan status 4 (sudah disetujui)

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
        return view('keranjang.history', compact('cartItems', 'role', 'startDate', 'endDate', 'old_start', 'old_end'));
    }




    // Menghapus barang dari keranjang
    public function removeFromCart($id)
    {
        $keranjang = Keranjang::findOrFail($id);
        $data_barang = DB::table('logistiks')->where('id', $keranjang->id_barang)->first();
        $jumlah_logistik = $data_barang->jumlah_barang;
        $jumlah_keranjang = $keranjang->jumlah_barang;
        $total = $jumlah_keranjang + $jumlah_logistik;
        $data = [
                    'jumlah_barang' => $total,
                ];
        DB::table('logistiks')->where('id', $keranjang->id_barang)->update($data);
        if ($keranjang->id_user == Auth::id()) {
            $keranjang->delete();
            return redirect()->route('logistik.ambil_barang')
                            ->with('success', 'Keranjang berhasil dihapus!');
        }

            return redirect()->route('logistik.ambil_barang')
                            ->with('error', 'Gagal Menghapus');
    }

    // Menghapus barang dari keranjang
    public function ajukan_pengambilan(Request $request)
    {
        if($request->id_user != Auth::id()){
            return redirect()->route('logistik.ambil_barang')
                            ->with('error', 'Gagal Mengajukan');
        }else{
            $data = [
                    'status' => 1,
            ];
            $save = Keranjang::where('id_user', Auth::id())->update($data);
            if($save){
                return redirect()->route('logistik.ambil_barang')
                                ->with('success', 'Keranjang berhasil diajukan!');
            }else{
                return redirect()->route('logistik.ambil_barang')
                                ->with('error', 'Gagal Mengajukan!');
            }
        }

    }

    public function approve_keranjang(Request $request)
    {
        if($request->id_user != Auth::id()){
            return redirect()->route('logistik.ambil_barang')
                            ->with('error', 'Gagal Mengajukan');
        }else{
            $data = [
                    'status' => 2,
            ];
            $save = Keranjang::where('id', $request->id)->update($data);
            if($save){
                return redirect()->route('logistik.ambil_barang')
                                ->with('success', 'Keranjang berhasil diajukan!');
            }else{
                return redirect()->route('logistik.ambil_barang')
                                ->with('error', 'Gagal Mengajukan!');
            }
        }

    }

    public function status_change(Request $request)
    {
        if($request->id_user != Auth::id()){
            return redirect()->route('logistik.ambil_barang')
                            ->with('error', 'Gagal Mengajukan');
        }else{
            $data = [
                    'status' => $request->status,
            ];
            $save = Keranjang::where('id', $request->id)->update($data);
            if($save){
                return redirect()->route('logistik.ambil_barang')
                                ->with('success', 'Keranjang berhasil diajukan!');
            }else{
                return redirect()->route('logistik.ambil_barang')
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
