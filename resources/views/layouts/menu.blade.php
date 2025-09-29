 <!-- Dashboard Menu -->
    <li class="nav-item">
        <a class="nav-link  text-dark {{ request()->is('home') ? 'active bg-gradient-dark text-white' : '' }}" href="{{ route('home') }}">
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
      <a class="nav-link text-dark {{ request()->is('Barang') ? 'active bg-gradient-dark text-white' : '' }} " href="#collapseExample" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="collapseExample">
         <i class="material-symbols-rounded opacity-5">table_view</i>
            <span class="nav-link-text ms-1">Logistik</span>
      </a>
    </li>
    <div class="collapse sm-2" id="collapseExample">
      <!-- admin dan superadmin -->
      @can('bahan-olahan-list')
      <li class="nav-item">
        <a class="nav-link text-dark {{ request()->routeIs('logistik.master_barang') ? 'active bg-gradient-dark text-white' : '' }}" href="{{ route('logistik.master_barang') }}" style="margin-left: 30px;">
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
        <a class="nav-link text-dark {{ request()->routeIs('logistik.pengajuan_barang') ? 'active bg-gradient-dark text-white' : '' }}" href="{{ route('logistik.pengajuan_barang') }}" style="margin-left: 30px;">
            <i class="material-symbols-rounded opacity-5">table_view</i>
            <span class="nav-link-text ms-1">Pengajuan Barang</span>
        </a>
      </li>
      @endcan
    </div>
     <!-- All Role -->
    <div class="collapse sm-2" id="collapseExample">
      @can('bahan-olahan-list')
      <li class="nav-item">
        <a class="nav-link text-dark {{ request()->routeIs('logistik.list_barang') ? 'active bg-gradient-dark text-white' : '' }}" href="{{ route('logistik.list_barang') }}" style="margin-left: 30px;">
            <i class="material-symbols-rounded opacity-5">table_view</i>
            <span class="nav-link-text ms-1">List Barang</span>
        </a>
      </li>
      @endcan
    </div>
   <!-- All Role -->
    <div class="collapse sm-2" id="collapseExample">
      @can('bahan-olahan-list')
      <li class="nav-item">
        <a class="nav-link text-dark {{ request()->is('bahan_olahan') ? 'active bg-gradient-dark text-white' : '' }}" href="" style="margin-left: 30px;">
            <i class="material-symbols-rounded opacity-5">table_view</i>
            <span class="nav-link-text ms-1">Bahan Olahan</span>
        </a>
      </li>
      @endcan
    </div>

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
        <a class="nav-link text-dark {{ request()->is('roles') ? 'active bg-gradient-dark text-white' : '' }}" href="{{ route('roles.index') }}">
          <i class="material-symbols-rounded opacity-5">person</i>
          <span class="nav-link-text ms-1">Management Role User</span>
        </a>
      </li>
    @endcan
    
    <!-- Management Akun -->
    @can('user-list')
      <li class="nav-item">
        <a class="nav-link text-dark {{ request()->is('users') ? 'active bg-gradient-dark text-white' : '' }}" href="{{ route('users.index') }}">
          <i class="material-symbols-rounded opacity-5">person</i>
          <span class="nav-link-text ms-1">Management Akun</span>
        </a>
      </li>
    @endcan

    <!-- Barang Section -->
   