@extends('layouts.masterTemplate')

@section('title', 'Pengajuan Barang')

@section('content')
<div class="container mx-auto py-8">
    <div class="row">
        <div class="ms-3">
            <h1 class="text-3xl font-semibold mb-4">Pengajuan Stok Barang</h1>

            <form action="{{ route('logistik.proses_pengajuan_barang') }}" method="POST" class="p-6 bg-white shadow-md rounded-lg border border-gray-200">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                    <!-- Pilih Barang dan Masukkan Jumlah -->
                    <div class="col-span-1">
                        <div class="form-group">
                            <label for="barang" class="block text-sm font-medium text-gray-700 mb-2">Pilih Barang:</label>
                            <select name="barang_ids[]" id="barang" class="form-control mt-1 block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                @foreach ($masterBarang as $item)
                                    <option value="{{ $item->id }}">{{ $item->nama_barang }} ({{ $item->merk_barang }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>


                    <div class="col-span-1">
                        <div class="form-group">
                            <label for="jumlah" class="block text-sm font-medium text-gray-700">Jumlah Stok:</label>
                            <input type="number" name="jumlah_stok" class="form-control mt-1 block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" id="jumlah" min="1" placeholder="Masukkan jumlah stok" />
                        </div>
                    </div>
                </div>

                <!-- Button untuk Menambahkan Barang ke Daftar Pengajuan -->
                <div class="col-span-1 text-center mb-4">
                    <button type="button" class="btn btn-info bg-blue-500 text-white py-2 px-6 rounded-md hover:bg-blue-600" id="addItemButton">Tambah Barang</button>
                </div>

                <!-- Daftar Barang yang Diajukan -->
                <div class="col-span-1">
                    <h4 class="mt-4 text-xl font-semibold">Barang yang Diajukan:</h4>
                    <!-- <table id="logistikTable" class="display table table-striped"> -->
                    <table class="display table table-striped" id="barangTable">
                        <thead>
                            <tr>
                                <th class="border border-gray-300 py-2 px-4">Barang</th>
                                <th class="border border-gray-300 py-2 px-4">Jumlah</th>
                                <th class="border border-gray-300 py-2 px-4">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Items akan ditambahkan secara dinamis menggunakan JavaScript -->
                        </tbody>
                    </table>
                </div>

                <!-- Button Submit -->
                <div class="col-xs-12 col-sm-12 col-md-12 text-center mt-6">
                    <button type="submit" class="btn btn-success">Ajukan Stok</button>
                </div>
            </form>
        </div>
    </div>
</div>


<script>
    // Menambahkan barang yang dipilih ke tabel pengajuan
    document.getElementById('addItemButton').addEventListener('click', function() {
        const barangSelect = document.getElementById('barang');
        const jumlahInput = document.getElementById('jumlah');
        const barangIds = Array.from(barangSelect.selectedOptions).map(option => option.value);
        const barangNames = Array.from(barangSelect.selectedOptions).map(option => option.text);
        const jumlah = jumlahInput.value;

        // Validasi jika tidak ada barang yang dipilih atau jumlah tidak diisi
        if (barangIds.length === 0 || jumlah === '') {
            alert('Pilih barang dan masukkan jumlah stok');
            return;
        }

        const tableBody = document.getElementById('barangTable').getElementsByTagName('tbody')[0];

        // Menambahkan setiap barang yang dipilih
        barangIds.forEach((id, index) => {
            const row = tableBody.insertRow();

            const cell1 = row.insertCell(0);
            const cell2 = row.insertCell(1);
            const cell3 = row.insertCell(2);

            cell1.innerHTML = barangNames[index];
            cell2.innerHTML = jumlah;
            cell3.innerHTML = `<button type="button" class="btn btn-danger" onclick="removeItem(this)">Hapus</button>`;

            // Menambahkan input tersembunyi untuk mengirim data barang dan jumlahnya
            const hiddenInput1 = document.createElement('input');
            hiddenInput1.type = 'hidden';
            hiddenInput1.name = 'barang_ids[]';
            hiddenInput1.value = id;
            row.appendChild(hiddenInput1);

            const hiddenInput2 = document.createElement('input');
            hiddenInput2.type = 'hidden';
            hiddenInput2.name = 'jumlah_stok[]';
            hiddenInput2.value = jumlah;
            row.appendChild(hiddenInput2);
        });

        // Reset form setelah menambah barang
        barangSelect.value = '';
        jumlahInput.value = '';
    });

    // Fungsi untuk menghapus item dari tabel pengajuan
    function removeItem(button) {
        const row = button.parentNode.parentNode;
        row.parentNode.removeChild(row);
    }
</script>
@endsection
