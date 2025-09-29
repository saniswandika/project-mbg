@extends('layouts.masterTemplate')

@section('content')

<style>
    /* Menambah CSS untuk gambar preview */
.img-preview {
    max-width: 100%;       /* Pastikan gambar tidak melebihi lebar container */
    max-height: 300px;     /* Batasi tinggi gambar */
    object-fit: contain;   /* Pastikan gambar tetap proporsional */
    display: none;         /* Sembunyikan gambar preview sampai file dipilih */
    border: 1px solid #ddd; /* Border ringan di sekitar gambar */
    border-radius: 4px;    /* Radius border agar lebih halus */
    margin-top: 10px;      /* Spasi di atas gambar preview */
}

</style>
    <div class="row">
        <div class="col-lg-12 margin-tb">
            <div class="pull-left">
                <h2>Add Master Barang</h2>
            </div>
            <div class="pull-right">
                <a class="btn btn-primary" href="{{ route('logistik.master_barang') }}"> Back</a>
            </div>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Whoops!</strong> There were some problems with your input.<br><br>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('bahan_olahan.proses_tambah_bahan_master') }}" method="POST" enctype="multipart/form-data" id="masterBarangForm">
        @csrf

        <div class="row">
            <div class="col-xs-12 col-sm-12 col-md-12">
                <div class="form-group">
                    <strong>Nama Bahan:</strong>
                    <div class="input-group input-group-outline mb-3">
                        {!! Form::text('nama', null, ['placeholder' => 'Nama Barang', 'class' => 'form-control']) !!}
                    </div>
                </div>
            </div>

            <div class="col-xs-12 col-sm-12 col-md-12">
                <div class="form-group">
                    <strong>Merk Bahan:</strong>
                    <div class="input-group input-group-outline mb-3">
                        {!! Form::text('merk', null, ['placeholder' => 'Merk Barang', 'class' => 'form-control']) !!}
                    </div>
                </div>
            </div>

            <div class="col-xs-12 col-sm-12 col-md-12">
                <div class="form-group">
                    <strong>Foto Bahan:</strong>
                    <div class="input-group input-group-outline mb-3">
                        {!! Form::file('foto', ['class' => 'form-control', 'id' => 'foto', 'onchange' => 'previewImage()', 'required' => 'required']) !!}
                    </div>
                    <div class="form-group">
                        <strong>Preview Foto:</strong><br>
                        <!-- Gambar akan muncul di sini -->
                        <img id="fotoPreview" class="img-preview" src="#" alt="Image Preview" />
                    </div>
                </div>
            </div>

            <div class="col-xs-12 col-sm-12 col-md-12">
                <div class="form-group">
                    <strong>Deskripsi:</strong>
                    <div class="input-group input-group-outline mb-3">
                        {!! Form::textarea('deskripsi', null, ['placeholder' => 'Deskripsi', 'class' => 'form-control', 'style' => 'height:150px']) !!}
                    </div>
                </div>
            </div>

            <div class="col-xs-12 col-sm-12 col-md-12 text-center">
                <button type="submit" class="btn btn-primary">Submit</button>
            </div>
        </div>

    </form>

@endsection

    <script>
        function previewImage() {
            const image = document.querySelector('#foto'); // Ambil elemen input file
            const imgPreview = document.querySelector('#fotoPreview'); // Ambil elemen img untuk preview

            // Debugging: Cek apakah file dipilih
            console.log(image.files.length);
            if (image.files.length > 0) {
                imgPreview.style.display = 'block'; // Tampilkan gambar preview
            } else {
                imgPreview.style.display = 'none'; // Sembunyikan gambar preview jika tidak ada file
            }

            const oFReader = new FileReader(); // Membaca file gambar
            oFReader.readAsDataURL(image.files[0]);
            oFReader.onload = function(oFREvent) {
                imgPreview.src = oFREvent.target.result; // Set source untuk gambar preview
            }
        }


        // Optional: Form validation on submit
        document.getElementById('masterBarangForm').addEventListener('submit', function(event) {
            const fileInput = document.getElementById('foto');
            if (!fileInput.files.length) {
                alert('Please select an image file.');
                event.preventDefault(); // Cegah form submit jika file belum dipilih
            }
        });
    </script>