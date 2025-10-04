 <!-- Dashboard Menu -->
    <li class="nav-item">
        <a class="nav-link  text-dark {{ request()->is('home') ? 'active bg-gradient-dark text-white' : 'text-dark' }}" href="{{ route('home') }}">
          <i class="material-symbols-rounded opacity-5">dashboard</i>
        <span class="nav-link-text ms-1">Dashboard</span>
      </a>
    </li>

    <hr class="horizontal dark mt-0">

    <li class="nav-item mt-3">
      <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder opacity-6">Barang & Peminjaman</h6>
    </li>

    <!-- Barang Section -->
    <li class="nav-item">
      <a class="nav-link text-dark {{ request()->is('Barang') ? 'active bg-gradient-dark text-white' : 'text-dark' }} " href="#collapseExample" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="collapseExample">
         <i class="material-symbols-rounded opacity-5">table_view</i>
            <span class="nav-link-text ms-1">Barang</span>
      </a>
    </li>    
    <div class="collapse sm-2" id="collapseExample">
      <!-- admin dan superadmin -->
      @can('bahan-olahan-list')
      <li class="nav-item">
        <a class="nav-link text-dark {{ request()->routeIs('logistik.master_barang') ? 'active bg-gradient-dark text-white' : 'text-dark' }}" href="{{ route('logistik.master_barang') }}" style="margin-left: 30px;">
            <i class="material-symbols-rounded opacity-5">table_view</i>
            <span class="nav-link-text ms-1">Master Barang</span>
        </a>
      </li>
      @endcan
    </div>
      <!-- All Role -->
    <div class="collapse sm-2" id="collapseExample">
      @can('bahan-olahan-list')
  
      <li class="nav-item">
        <a href="{{ route('logistik.pengajuan_barang') }}" 
          class="nav-link {{ request()->routeIs('logistik.pengajuan_barang') ? 'active bg-gradient-dark text-white' : 'text-dark' }}" 
          style="margin-left: 30px;">
            <i class="material-symbols-rounded opacity-5">table_view</i>
            <span class="nav-link-text ms-1">Pengajuan Barang</span>
        </a>
      </li>
      @endcan
    </div>

    <div class="collapse sm-2" id="collapseExample">
      <li class="nav-item">
        <a class="nav-link text-dark {{ request()->routeIs('logistik.log_pengajuan_barang') ? 'active bg-gradient-dark text-white' : 'text-dark' }}" href="{{ route('logistik.log_pengajuan_barang') }}" style="margin-left: 30px;">
            <i class="material-symbols-rounded opacity-5">table_view</i>
            <span class="nav-link-text ms-1">Log Pengajuan Barang</span>
        </a>
      </li>
    </div>
     <!-- All Role -->
    <div class="collapse sm-2" id="collapseExample">
      @can('bahan-olahan-list')
      <li class="nav-item">
        <a class="nav-link text-dark {{ request()->routeIs('logistik.list_barang') ? 'active bg-gradient-dark text-white' : 'text-dark' }}" href="{{ route('logistik.list_barang') }}" style="margin-left: 30px;">
            <i class="material-symbols-rounded opacity-5">table_view</i>
            <span class="nav-link-text ms-1">List Barang</span>
        </a>
      </li>
      @endcan
    </div>
        <div class="collapse sm-2" id="collapseExample">
      <li class="nav-item">
        <a class="nav-link text-dark {{ request()->routeIs('logistik.ambil_barang') ? 'active bg-gradient-dark text-white' : 'text-dark' }}" href="{{ route('logistik.ambil_barang') }}" style="margin-left: 30px;">
            <i class="material-symbols-rounded opacity-5">table_view</i>
            <span class="nav-link-text ms-1">Pengambilan Barang</span>
        </a>
      </li>
    </div>
    <div class="collapse sm-2" id="collapseExample">
      <li class="nav-item">
        <a class="nav-link text-dark {{ request()->routeIs('logistik.history_keranjang') ? 'active bg-gradient-dark text-white' : 'text-dark' }}" href="{{ route('logistik.history_keranjang') }}" style="margin-left: 30px;">
            <i class="material-symbols-rounded opacity-5">table_view</i>
            <span class="nav-link-text ms-1">History pengambilan barang</span>
        </a>
      </li>
    </div>
   <!-- All Role -->
    {{-- <div class="collapse sm-2" id="collapseExample">
      @can('bahan-olahan-list')
      <li class="nav-item">
        <a class="nav-link text-dark {{ request()->is('bahan_olahan') ? 'active bg-gradient-dark text-white' : 'text-dark' }}" href="" style="margin-left: 30px;">
            <i class="material-symbols-rounded opacity-5">table_view</i>
            <span class="nav-link-text ms-1">Bahan Olahan</span>
        </a>
      </li>
      @endcan
    </div> --}}
    <li class="nav-item">
      <a class="nav-link text-dark {{ request()->is('Barang') ? 'active bg-gradient-dark text-white' : 'text-dark' }} " href="#collapseExample1" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="collapseExample">
         <i class="material-symbols-rounded opacity-5">table_view</i>
            <span class="nav-link-text ms-1">Bahan Olahan</span>
      </a>
    </li>
    <div class="collapse sm-2" id="collapseExample1">
      <li class="nav-item">
        <a class="nav-link text-dark {{ request()->routeIs('bahan_olahan.bahan_olahan') ? 'active bg-gradient-dark text-white' : 'text-dark' }}" href="{{ route('bahan_olahan.bahan_olahan') }}" style="margin-left: 30px;">
            <i class="material-symbols-rounded opacity-5">table_view</i>
            <span class="nav-link-text ms-1">Bahan Olahan</span>
        </a>
      </li>
    </div>
    <div class="collapse sm-2" id="collapseExample1">
      <li class="nav-item">
        <a class="nav-link text-dark {{ request()->routeIs('bahan_olahan.pengajuan_bahan') ? 'active bg-gradient-dark text-white' : 'text-dark' }}" href="{{ route('bahan_olahan.pengajuan_bahan') }}" style="margin-left: 30px;">
            <i class="material-symbols-rounded opacity-5">table_view</i>
            <span class="nav-link-text ms-1">Pengajuan pembelian</span>
        </a>
      </li>
    </div>
    <div class="collapse sm-2" id="collapseExample1">
      <li class="nav-item">
        <a class="nav-link text-dark {{ request()->routeIs('bahan_olahan.list_bahan') ? 'active bg-gradient-dark text-white' : 'text-dark' }}" href="{{ route('bahan_olahan.list_bahan') }}" style="margin-left: 30px;">
            <i class="material-symbols-rounded opacity-5">table_view</i>
            <span class="nav-link-text ms-1">List Bahan Olahan</span>
        </a>
      </li>
    </div>
    <div class="collapse sm-2" id="collapseExample1">
      <li class="nav-item">
        <a class="nav-link text-dark {{ request()->routeIs('bahan_olahan.ambil_bahan') ? 'active bg-gradient-dark text-white' : 'text-dark' }}" href="{{ route('bahan_olahan.ambil_bahan') }}" style="margin-left: 30px;">
            <i class="material-symbols-rounded opacity-5">table_view</i>
            <span class="nav-link-text ms-1">Pengambilan Bahan</span>
        </a>
      </li>
    </div>
    <div class="collapse sm-2" id="collapseExample1">
      <li class="nav-item">
        <a class="nav-link text-dark {{ request()->routeIs('bahan_olahan.history_keranjang') ? 'active bg-gradient-dark text-white' : 'text-dark' }}" href="{{ route('bahan_olahan.history_keranjang') }}" style="margin-left: 30px;">
            <i class="material-symbols-rounded opacity-5">table_view</i>
            <span class="nav-link-text ms-1">History pengambilan Bahan</span>
        </a>
      </li>
    </div>
    @if(auth('web')->check() && (auth('web')->user()->can('user-list') || auth('web')->user()->can('role-list')))
      <hr class="horizontal dark mt-0">
      <li class="nav-item mt-3">
        <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder opacity-6 text-wrap">Absensi Pegawai</h6>
      </li>
      <hr class="horizontal dark mt-0">
    @endif

     @can('user-list')
      {{-- <li class="nav-item">
        <a class="nav-link text-dark {{ request()->is('webauthn') ? 'active bg-gradient-dark text-white' : 'text-dark' }}" href="{{ route('webauthn.index') }}">
          <i class="material-symbols-rounded opacity-5">person</i>
          <span class="nav-link-text ms-1">Absensi Pegawai</span>
        </a>
      </li> --}}
      <li class="nav-item">
        <a class="nav-link text-dark {{ request()->is('Absensi Pegawai') ? 'active bg-gradient-dark text-white' : 'text-dark' }} " href="#absensipegawai" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="absensipegawai">
          <i class="material-symbols-rounded opacity-5">table_view</i>
              <span class="nav-link-text ms-1">Absensi</span>
        </a>
      </li>
      <div class="collapse sm-2" id="absensipegawai">
        <!-- admin dan superadmin -->
        @can('bahan-olahan-list')
        <li class="nav-item">
          <a class="nav-link text-dark {{ request()->routeIs('webauthn.absensi') ? 'active bg-gradient-dark text-white' : 'text-dark' }}" 
            href="{{ route('webauthn.absensi') }}">
              <i class="material-symbols-rounded opacity-5">table_view</i>
              <span class="nav-link-text ms-1">Absensi Diri</span>
          </a>
        </li>
        @endcan
      </div>
      <div class="collapse sm-2" id="absensipegawai">
        <!-- admin dan superadmin -->
        @can('bahan-olahan-list')
        <li class="nav-item">
          <a class="nav-link text-dark {{ request()->routeIs('logistik.master_barang') ? 'active bg-gradient-dark text-white' : 'text-dark' }}" 
            href="{{ route('webauthn.index') }}">
              <i class="material-symbols-rounded opacity-5">table_view</i>
              <span class="nav-link-text ms-1">Data Absen Pegawai</span>
          </a>
        </li>
        @endcan
      </div>
    @endcan
    
    <!-- Pengaturan Akun dan Hak Akses Section -->
    @if(auth('web')->check() && (auth('web')->user()->can('user-list') || auth('web')->user()->can('role-list')))
      <hr class="horizontal dark mt-0">
      <li class="nav-item mt-3">
        <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder opacity-6 text-wrap">Pengaturan akun</h6>
      </li>
      <hr class="horizontal dark mt-0">
    @endif

    <!-- Management Role User -->
    @can('user-list')
      <li class="nav-item">
        <a class="nav-link text-dark {{ request()->is('roles') ? 'active bg-gradient-dark text-white' : 'text-dark' }}" href="{{ route('roles.index') }}">
          <i class="material-symbols-rounded opacity-5">person</i>
          <span class="nav-link-text ms-1">Management Role User</span>
        </a>
      </li>
    @endcan
    
    <!-- Management Akun -->
    @can('user-list')
      <li class="nav-item">
        <a class="nav-link text-dark {{ request()->is('users') ? 'active bg-gradient-dark text-white' : 'text-dark' }}" href="{{ route('users.index') }}">
          <i class="material-symbols-rounded opacity-5">person</i>
          <span class="nav-link-text ms-1">Management Akun</span>
        </a>
      </li>
    @endcan
      <li class="nav-item">
        <a class="nav-link {{ request()->is('pegawai*') ? 'active bg-gradient-dark text-white' : 'text-dark' }}" href="{{ route('pegawai.index') }}">
          <i class="material-symbols-rounded opacity-5">person</i>
          <span class="nav-link-text ms-1">Management Pegawai</span>
        </a>
      </li>
    <!-- Barang Section -->
   