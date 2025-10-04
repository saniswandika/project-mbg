<!DOCTYPE html>
<html lang="en">


<head>
  <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">

  <!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://cdn.jsdelivr.net/npm/datatables@1.10.21/media/css/jquery.dataTables.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/datatables@1.10.21/media/js/jquery.dataTables.min.js"></script>
  <!-- Include jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Include Popper.js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/2.11.6/umd/popper.min.js"></script>

<!-- Include Bootstrap JS -->
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="apple-touch-icon" sizes="76x76" href="{{ secure_asset('/assets/img/apple-icon.png') }}">
  <link rel="icon" type="image/png" href="{{ secure_asset('/assets/img/favicon.png') }}">
  <title>
    MBG - Indihiang
  </title>
  <!--     Fonts and icons     -->
  <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700,900" />
  <!-- Nucleo Icons -->
  <link href="{{ secure_asset('/assets/css/nucleo-icons.css') }}" rel="stylesheet" />
  <link href="{{ secure_asset('/assets/css/nucleo-svg.css') }}" rel="stylesheet" />
  
  <!-- Font Awesome Icons -->
  <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
  <!-- Material Icons -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
  <!-- CSS Files -->
  <link id="pagestyle" href="{{ secure_asset('/assets/css/material-dashboard.css?v=3.2.0') }}" rel="stylesheet" />
  <link id="pagestyle" href="https://cdn.datatables.net/2.3.4/css/dataTables.dataTables.css" rel="stylesheet" />
</head>

<body class="g-sidenav-show  bg-gray-100">
  <aside class="sidenav navbar navbar-vertical navbar-expand-xs border-radius-lg fixed-start ms-2  bg-white my-2" id="sidenav-main">
    <div class="sidenav-header">
      <i class="fas fa-times p-3 cursor-pointer text-dark opacity-5 position-absolute end-0 top-0 d-none d-xl-none" aria-hidden="true" id="iconSidenav"></i>
      <a class="navbar-brand px-4 py-3 m-0" href="https://demos.creative-tim.com/material-dashboard/pages/dashboard " target="_blank">
        <img src="{{ secure_asset('assets/img/OIP.jpeg') }}" class="navbar-brand-img" width="140" height="120" alt="Creative Tim logo, stylized CT letters in white on a dark background, used as the main logo in the sidebar navigation. The logo appears friendly and modern.">
      </a>
    </div>
    <hr class="horizontal dark mt-0 mb-2">


   
    <div class="collapse navbar-collapse w-auto" id="sidenav-collapse-main">
      <ul class="navbar-nav">
          {{-- file tampilan menu ada di layouts/menu.blade.php --}}
         @include('layouts.menu')
      </ul>
    </div>
  </aside>
  <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
    <!-- Navbar -->
    @include('layouts.navbar')
  
    <!-- End Navbar -->
    @yield('content')

  </main>
  <div class="fixed-plugin">
    <a class="fixed-plugin-button text-dark position-fixed px-3 py-2">
      <i class="material-symbols-rounded py-2">settings</i>
    </a>
    <div class="card shadow-lg">
      <div class="card-header pb-0 pt-3">
        <div class="float-start">
          <h5 class="mt-3 mb-0">Material UI Configurator</h5>
          <p>See our dashboard options.</p>
        </div>
        <div class="float-end mt-4">
          <button class="btn btn-link text-dark p-0 fixed-plugin-close-button">
            <i class="material-symbols-rounded">clear</i>
          </button>
        </div>
        <!-- End Toggle Button -->
      </div>
      <hr class="horizontal dark my-1">
      <div class="card-body pt-sm-3 pt-0">
        <!-- Sidebar Backgrounds -->
        <div>
          <h6 class="mb-0">Sidebar Colors</h6>
        </div>
        <a href="javascript:void(0)" class="switch-trigger background-color">
          <div class="badge-colors my-2 text-start">
            <span class="badge filter bg-gradient-primary" data-color="primary" onclick="sidebarColor(this)"></span>
            <span class="badge filter bg-gradient-dark active" data-color="dark" onclick="sidebarColor(this)"></span>
            <span class="badge filter bg-gradient-info" data-color="info" onclick="sidebarColor(this)"></span>
            <span class="badge filter bg-gradient-success" data-color="success" onclick="sidebarColor(this)"></span>
            <span class="badge filter bg-gradient-warning" data-color="warning" onclick="sidebarColor(this)"></span>
            <span class="badge filter bg-gradient-danger" data-color="danger" onclick="sidebarColor(this)"></span>
          </div>
        </a>
        <!-- Sidenav Type -->
        <div class="mt-3">
          <h6 class="mb-0">Sidenav Type</h6>
          <p class="text-sm">Choose between different sidenav types.</p>
        </div>
        <div class="d-flex">
          <button class="btn bg-gradient-dark px-3 mb-2" data-class="bg-gradient-dark" onclick="sidebarType(this)">Dark</button>
          <button class="btn bg-gradient-dark px-3 mb-2 ms-2" data-class="bg-transparent" onclick="sidebarType(this)">Transparent</button>
          <button class="btn bg-gradient-dark px-3 mb-2  active ms-2" data-class="bg-white" onclick="sidebarType(this)">White</button>
        </div>
        <p class="text-sm d-xl-none d-block mt-2">You can change the sidenav type just on desktop view.</p>
        <!-- Navbar Fixed -->
        <div class="mt-3 d-flex">
          <h6 class="mb-0">Navbar Fixed</h6>
          <div class="form-check form-switch ps-0 ms-auto my-auto">
            <input class="form-check-input mt-1 ms-auto" type="checkbox" id="navbarFixed" onclick="navbarFixed(this)">
          </div>
        </div>
        <hr class="horizontal dark my-3">
        <div class="mt-2 d-flex">
          <h6 class="mb-0">Light / Dark</h6>
          <div class="form-check form-switch ps-0 ms-auto my-auto">
            <input class="form-check-input mt-1 ms-auto" type="checkbox" id="dark-version" onclick="darkMode(this)">
          </div>
        </div>
        <hr class="horizontal dark my-sm-4">
        <a class="btn bg-gradient-info w-100" href="https://www.creative-tim.com/product/material-dashboard-pro">Free Download</a>
        <a class="btn btn-outline-dark w-100" href="https://www.creative-tim.com/learning-lab/bootstrap/overview/material-dashboard">View documentation</a>
        <div class="w-100 text-center">
          <a class="github-button" href="https://github.com/creativetimofficial/material-dashboard" data-icon="octicon-star" data-size="large" data-show-count="true" aria-label="Star creativetimofficial/material-dashboard on GitHub">Star</a>
          <h6 class="mt-3">Thank you for sharing!</h6>
          <a href="https://twitter.com/intent/tweet?text=Check%20Material%20UI%20Dashboard%20made%20by%20%40CreativeTim%20%23webdesign%20%23dashboard%20%23bootstrap5&amp;url=https%3A%2F%2Fwww.creative-tim.com%2Fproduct%2Fsoft-ui-dashboard" class="btn btn-dark mb-0 me-2" target="_blank">
            <i class="fab fa-twitter me-1" aria-hidden="true"></i> Tweet
          </a>
          <a href="https://www.facebook.com/sharer/sharer.php?u=https://www.creative-tim.com/product/material-dashboard" class="btn btn-dark mb-0 me-2" target="_blank">
            <i class="fab fa-facebook-square me-1" aria-hidden="true"></i> Share
          </a>
        </div>
      </div>
    </div>
  </div>
  <!--   Core JS Files   -->
  <script src="{{ secure_asset('assets/js/core/popper.min.js') }}"></script>
  <script src="{{ secure_asset('assets/js/core/bootstrap.min.js') }}"></script>
  <script src="{{ secure_asset('assets/js/plugins/perfect-scrollbar.min.js') }}"></script>
  <script src="{{ secure_asset('assets/js/plugins/smooth-scrollbar.min.js') }}"></script>
  <script src="{{ secure_asset('assets/js/plugins/chartjs.min.js') }}"></script>

  <script>
    var win = navigator.platform.indexOf('Win') > -1;
    if (win && document.querySelector('#sidenav-scrollbar')) {
      var options = {
        damping: '0.5'
      }
      Scrollbar.init(document.querySelector('#sidenav-scrollbar'), options);
    }
  </script>
  <!-- Github buttons -->
  <script async defer src="https://buttons.github.io/buttons.js"></script>
  <!-- Control Center for Material Dashboard: parallax effects, scripts for the example pages etc -->
  <script src="{{ secure_asset('assets/js/material-dashboard.min.js?v=3.2.0') }}"></script>
  <script src="https://cdn.datatables.net/2.3.4/js/dataTables.js"></script>
  {{-- user datatable --}}
    <script>
        // Inisialisasi DataTables
        var table = new DataTable('#userTable', {
            ajax: {
                url: 'http://127.0.0.1:8000/api/users', // Endpoint untuk API users
                dataSrc: 'data' // Ambil data dari key "data"
            },
            processing: true,
            serverSide: true,
            columns: [
                { data: 'id' },        // Menampilkan kolom ID
                { data: 'name' },      // Menampilkan kolom Name
                { data: 'email' },     // Menampilkan kolom Email
                {
                    data: null,
                    render: function(data, type, row) {
                        return `
                            <button class="btn btn-info" data-toggle="modal" data-target="#ShowModal" data-id="${data.id}" data-name="${data.name}" data-email="${data.email}">
                                Show
                            </button>
                            <button class="btn btn-primary" data-toggle="modal" data-target="#editUser${data.id}" data-id="${data.id}">
                                Edit
                            </button>
                        `;
                    }
                } // Tombol Show dan Edit
            ]
        });

        // Menampilkan data modal Show
        $('#userTable').on('click', '[data-target="#ShowModal"]', function() {
            var userId = $(this).data('id');
            var userName = $(this).data('name');
            var userEmail = $(this).data('email');

            // Isi modal dengan data pengguna
            $('#userName').text(userName);
            $('#userEmail').text(userEmail);
        });

        // Menampilkan data modal Edit (sama seperti Show)
        $('#userTable').on('click', '[data-target^="#editUserModal"]', function() {
            var userId = $(this).data('id');
            // Kamu bisa menambahkan AJAX untuk menampilkan data dan mengisi form edit di sini jika diperlukan
        });
    </script>
  {{-- user datatatable --}}
  {{-- Role datatable --}}
    <script>
      // Inisialisasi DataTables
      var table = new DataTable('#RoleTable', {
        ajax: {
          url: 'http://127.0.0.1:8000/api/roles', // Endpoint untuk API roles
          dataSrc: 'data' // Ambil data dari key "data"
        },
        processing: true,
        serverSide: true,
        columns: [
          { data: 'id' },  // Menampilkan kolom ID
          { data: 'name' },  // Menampilkan kolom Name
          { 
            data: 'created_at', // Menampilkan kolom Created At
            render: function(data, type, row) {
              var date = new Date(data); // Convert the date string to a Date object
              return date.toLocaleDateString(); // Format the date to a readable format (e.g., "MM/DD/YYYY")
            }
          },
          { 
            data: 'updated_at', // Menampilkan kolom Updated At
            render: function(data, type, row) {
              var date = new Date(data); // Convert the date string to a Date object
              return date.toLocaleDateString(); // Format the date to a readable format
            }
          },
          {
            data: null,
            render: function(data, type, row) {
              return `
                <a class="btn btn-info" href="/roles/${data.id}">Show</a>
                {{-- @can('role-edit') --}}
                  <a class="btn btn-primary" href="/roles/${data.id}/edit">Edit</a>
                {{-- @endcan --}}
                {{-- @can('role-delete') --}}
                  <form action="/roles/${data.id}" method="POST" style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                  </form>
                {{-- @endcan --}}
              `;
            }
          } // Tombol Show, Edit, Delete
        ]
      });
    </script>
  {{-- Role datatable --}}
  {{-- pegawai datatable --}}
    <script>
      // Inisialisasi DataTables
      var table = new DataTable('#PegawaiTable', {
        ajax: {
          url: 'http://127.0.0.1:8000/api/pegawai', // Endpoint untuk API pegawai
          dataSrc: 'data' // Ambil data dari key "data"
        },
        processing: true,
        serverSide: true,
        columns: [
          { data: 'id' },  // Menampilkan kolom ID
          { data: 'nama_lengkap' },  // Menampilkan kolom Name
          { data: 'nik' },  // Menampilkan kolom NIK
          { data: 'no_kk' },  // Menampilkan kolom No Kartu Keluarga
          { data: 'alamat' },  // Menampilkan kolom Alamat
          {
            data: null,
            render: function(data, type, row) {
              return `
                <a class="btn btn-info" href="/pegawai/${data.id}/show">Show</a>
                  <a class="btn btn-primary" href="/pegawai/${data.id}/edit">Edit</a>
                  <a class="btn btn-primary" href="/pegawai/${data.id}/tambah_akun">Tambah akun</a>
                  <form action="/pegawai/${data.id}" method="POST" style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                  </form>
              `;
            }
          } // Tombol Show, Edit, Delete
        ]
      });
    </script>
  {{-- pegawai datatable --}}
  {{-- Absen Pegawi datatable --}}
    <script>
      function base64ToArrayBuffer(base64) {
          const binaryString = window.atob(base64.replace(/-/g, '+').replace(/_/g, '/'));
          const len = binaryString.length;
          const bytes = new Uint8Array(len);
          for (let i = 0; i < len; i++) {
              bytes[i] = binaryString.charCodeAt(i);
          }
          return bytes.buffer;
      }

      document.getElementById('fingerprintButton').addEventListener('click', async () => {
          if (!window.PublicKeyCredential) {
              alert('Browser tidak mendukung fingerprint WebAuthn');
              return;
          }

          const options = await fetch('/webauthn/generate', {
              method: 'POST',
              headers: {
                  'X-CSRF-TOKEN': '{{ csrf_token() }}',
                  'Content-Type': 'application/json'
              }
          }).then(r => r.json());

          // ✅ Perbaikan di sini
          options.challenge = base64ToArrayBuffer(options.challenge);
          options.user.id = base64ToArrayBuffer(options.user.id);

          const credential = await navigator.credentials.create({ publicKey: options });
          console.log(credential);
          
          const response = await fetch('/webauthn/register', {
              method: 'POST',
              headers: {
                  'X-CSRF-TOKEN': '{{ csrf_token() }}',
                  'Content-Type': 'application/json'
              },
              body: JSON.stringify({ credential })
          });

          const result = await response.json();
          if (result.success) {
              alert('Fingerprint berhasil diverifikasi!');
          } else {
              alert('Gagal verifikasi fingerprint.');
          }
      });
    </script>
  {{-- Absen Pegawi datatable --}}
   {{-- data absen pegawai datatable --}}
    <script>
      // Inisialisasi DataTables
      var table = new DataTable('#AbsenPegawaiTable', {
        ajax: {
          url: 'http://127.0.0.1:8000/api/absen-pegawai', // Endpoint untuk API pegawai
          dataSrc: 'data' // Ambil data dari key "data"
        },
        processing: true,
        serverSide: true,
        columns: [
          { data: 'id' },  // Menampilkan kolom ID
          { data: 'user_id' },  // Menampilkan kolom Name
          { data: 'status' },  // Menampilkan kolom NIK
          { data: 'waktu_absen' },  // Menampilkan kolom No Kartu Keluarga
          {
            data: null,
            render: function(data, type, row) {
              return `
                <a class="btn btn-info" href="/pegawai/${data.id}/show">Show</a>
                  <a class="btn btn-primary" href="/pegawai/${data.id}/edit">Edit</a>
                  <a class="btn btn-primary" href="/pegawai/${data.id}/tambah_akun">Tambah akun</a>
                  <form action="/pegawai/${data.id}" method="POST" style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                  </form>
              `;
            }
          } // Tombol Show, Edit, Delete
        ]
      });
    </script>
  {{-- pegawai datatable --}}

</body>
</html>
