<?php

namespace Database\Seeders;

use App\Models\Roles;
use App\Models\User;
use App\Models\wilayah;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;


class CreateAdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // fasilitator
        $fasilitator = User::create([
            'name' => 'fasilitator',
            'email' => 'fasilitator@dinsos.co.id',
            'password' => bcrypt('123456')
        ]);
        $fasilitatorrole = Roles::create(['name' => 'fasilitator',
            'guard_name' => 'web']);
        $user_id = $fasilitator->id;
        $role_id = $fasilitatorrole->id;
        DB::insert('insert into model_has_roles (role_id, model_type , model_id) values (?,?,?)', [$role_id, 'App\Models\User', $user_id,]);

        //backOfiiceKota
        $backOfiiceKota = User::create([
            'name' => 'Back Ofiice Kota',
            'email' => 'BackOfficeKota@dinsos.co.id',
            'password' => bcrypt('123456')
        ]);
        $backOfiiceKotaRole = Roles::create(['name' => 'Back Ofiice Kota',
            'guard_name' => 'web']);
        $user_id = $backOfiiceKota->id;
        $role_id = $backOfiiceKotaRole->id;
        DB::insert('insert into model_has_roles (role_id, model_type , model_id) values (?,?,?)', [$role_id, 'App\Models\User', $user_id,]);
        $kelurahans = DB::table('indonesia_villages')
        ->select('r.code as code_provinces', 'c.code as code_cities','d.code as code_district','indonesia_villages.code as code_villages', 'indonesia_villages.name_village')
        ->join('indonesia_districts as d', 'indonesia_villages.district_code', '=', 'd.code')
        ->join('indonesia_cities as c', 'd.city_code', '=', 'c.code')
        ->join('indonesia_provinces as r', 'c.province_code', '=', 'r.code')
        ->where('c.code', '3273')
        ->limit(1) // L imit the number of kelurahans
        ->get();
        foreach ($kelurahans as $key => $kelurahan) {
            DB::table('wilayahs')->insert([
                'province_id' => $kelurahan->code_provinces,
                'kota_id' => $kelurahan->code_cities,
                'kecamatan_id' => $kelurahan->code_district,
                'kelurahan_id' => $kelurahan->code_villages,
                'status_wilayah' => 1,
                'createdby' => $user_id
            ]);
        }
        //frontOfficekota
        $frontOfficekota = User::create([
            'name' => 'Front Office kota',
            'email' => 'FrontOfficekota@dinsos.co.id',
            'password' => bcrypt('123456')
        ]);
        $frontOfficekotarole = Roles::create(['name' => 'Front Office kota',
            'guard_name' => 'web']);
        $user_id = $frontOfficekota->id;
        $role_id = $frontOfficekotarole->id;
        DB::insert('insert into model_has_roles (role_id, model_type , model_id) values (?,?,?)', [$role_id, 'App\Models\User', $user_id,]);
        $kelurahans = DB::table('indonesia_villages')
            ->select('r.code as code_provinces', 'c.code as code_cities','d.code as code_district','indonesia_villages.code as code_villages', 'indonesia_villages.name_village')
            ->join('indonesia_districts as d', 'indonesia_villages.district_code', '=', 'd.code')
            ->join('indonesia_cities as c', 'd.city_code', '=', 'c.code')
            ->join('indonesia_provinces as r', 'c.province_code', '=', 'r.code')
            ->where('c.code', '3273')
            ->limit(1) // L imit the number of kelurahans
            ->get();
        foreach ($kelurahans as $key => $kelurahan) {
            DB::table('wilayahs')->insert([
                'province_id' => $kelurahan->code_provinces,
                'kota_id' => $kelurahan->code_cities,
                'kecamatan_id' => $kelurahan->code_district,
                'kelurahan_id' => $kelurahan->code_villages,
                'status_wilayah' => 1,
                'createdby' => $user_id
            ]);
        }
        //supervisor
        $supervisor = User::create([
            'name' => 'supervisor',
            'email' => 'supervisor@dinsos.co.id',
            'password' => bcrypt('123456')
        ]);
        $supervisorrole = Roles::create(['name' => 'supervisor',
            'guard_name' => 'web']);
        $user_id = $supervisor->id;
        $role_id = $supervisorrole->id;
        DB::insert('insert into model_has_roles (role_id, model_type , model_id) values (?,?,?)', [$role_id, 'App\Models\User', $user_id,]);
        $kelurahans = DB::table('indonesia_villages')
            ->select('r.code as code_provinces', 'c.code as code_cities','d.code as code_district','indonesia_villages.code as code_villages', 'indonesia_villages.name_village')
            ->join('indonesia_districts as d', 'indonesia_villages.district_code', '=', 'd.code')
            ->join('indonesia_cities as c', 'd.city_code', '=', 'c.code')
            ->join('indonesia_provinces as r', 'c.province_code', '=', 'r.code')
            ->where('c.code', '3273')
            ->limit(1) // L imit the number of kelurahans
            ->get();
        foreach ($kelurahans as $key => $kelurahan) {
            DB::table('wilayahs')->insert([
                'province_id' => $kelurahan->code_provinces,
                'kota_id' => $kelurahan->code_cities,
                'kecamatan_id' => $kelurahan->code_district,
                'kelurahan_id' => $kelurahan->code_villages,
                'status_wilayah' => 1,
                'createdby' => $supervisor->id
            ]);
        }
        //kepala bidang
        $kepalabidang = User::create([
            'name' => 'kepala bidang',
            'email' => 'kepalabidang@dinsos.co.id',
            'password' => bcrypt('123456')
        ]);
        $kepalabidangrole = Roles::create(['name' => 'kepala bidang',
            'guard_name' => 'web']);
        $user_id = $kepalabidang->id;
        $role_id = $kepalabidangrole->id;
        DB::insert('insert into model_has_roles (role_id, model_type , model_id) values (?,?,?)', [$role_id, 'App\Models\User', $user_id,]);
        $kelurahans = DB::table('indonesia_villages')
            ->select('r.code as code_provinces', 'c.code as code_cities','d.code as code_district','indonesia_villages.code as code_villages', 'indonesia_villages.name_village')
            ->join('indonesia_districts as d', 'indonesia_villages.district_code', '=', 'd.code')
            ->join('indonesia_cities as c', 'd.city_code', '=', 'c.code')
            ->join('indonesia_provinces as r', 'c.province_code', '=', 'r.code')
            ->where('c.code', '3273')
            ->limit(1) // L imit the number of kelurahans
            ->get();
        foreach ($kelurahans as $key => $kelurahan) {
            DB::table('wilayahs')->insert([
                'province_id' => $kelurahan->code_provinces,
                'kota_id' => $kelurahan->code_cities,
                'kecamatan_id' => $kelurahan->code_district,
                'kelurahan_id' => $kelurahan->code_villages,
                'status_wilayah' => 1,
                'createdby' => $kepalabidang->id
            ]);
        }
        //sekretarisDinas
        $sekertarisDinas = User::create([
            'name' => 'SekertarisDinas',
            'email' => 'SekertarisDinas@dinsos.co.id',
            'password' => bcrypt('123456')
        ]);
        $sekertarisDinasrole = Roles::create(['name' => 'SekertarisDinas',
            'guard_name' => 'web']);
        $user_id = $sekertarisDinas->id;
        $role_id = $sekertarisDinasrole->id;
        DB::insert('insert into model_has_roles (role_id, model_type , model_id) values (?,?,?)', [$role_id, 'App\Models\User', $user_id,]);
        $kelurahans = DB::table('indonesia_villages')
            ->select('r.code as code_provinces', 'c.code as code_cities','d.code as code_district','indonesia_villages.code as code_villages', 'indonesia_villages.name_village')
            ->join('indonesia_districts as d', 'indonesia_villages.district_code', '=', 'd.code')
            ->join('indonesia_cities as c', 'd.city_code', '=', 'c.code')
            ->join('indonesia_provinces as r', 'c.province_code', '=', 'r.code')
            ->where('c.code', '3273')
            ->limit(1) // L imit the number of kelurahans
            ->get();
        foreach ($kelurahans as $key => $kelurahan) {
            DB::table('wilayahs')->insert([
                'province_id' => $kelurahan->code_provinces,
                'kota_id' => $kelurahan->code_cities,
                'kecamatan_id' => $kelurahan->code_district,
                'kelurahan_id' => $kelurahan->code_villages,
                'status_wilayah' => 1,
                'createdby' => $sekertarisDinas->id
            ]);
        }
        //kepala dinas
        $kepalaDinas = User::create([
            'name' => 'KepalaDinas',
            'email' => 'KepalaDinas@dinsos.co.id',
            'password' => bcrypt('123456')
        ]);
        $kepalaDinasrole = Roles::create(['name' => 'KepalaDinas',
            'guard_name' => 'web']);
        $user_id = $kepalaDinas->id;
        $role_id = $kepalaDinasrole->id;
        DB::insert('insert into model_has_roles (role_id, model_type , model_id) values (?,?,?)', [$role_id, 'App\Models\User', $user_id,]);
        $kelurahans = DB::table('indonesia_villages')
                ->select('r.code as code_provinces', 'c.code as code_cities','d.code as code_district','indonesia_villages.code as code_villages', 'indonesia_villages.name_village')
                ->join('indonesia_districts as d', 'indonesia_villages.district_code', '=', 'd.code')
                ->join('indonesia_cities as c', 'd.city_code', '=', 'c.code')
                ->join('indonesia_provinces as r', 'c.province_code', '=', 'r.code')
                ->where('c.code', '3273')
                ->limit(3) // Limit the number of kelurahans
                ->get();
        foreach ($kelurahans as $key => $kelurahan) {
            DB::table('wilayahs')->insert([
                'province_id' => $kelurahan->code_provinces,
                'kota_id' => $kelurahan->code_cities,
                'kecamatan_id' => $kelurahan->code_district,
                'kelurahan_id' => $kelurahan->code_villages,
                'status_wilayah' => 1,
                'createdby' => $kepalaDinas->id
            ]);
        }
        //warga
        
        $warga = User::create([
            'name' => 'warga',
            'email' => 'warga@dinsos.co.id',
            'password' => bcrypt('123456')
        ]);
        $wargarole = Roles::create(['name' => 'warga',
            'guard_name' => 'web']);
        $user_id = $warga->id;
        $role_id = $wargarole->id;
        DB::insert('insert into model_has_roles (role_id, model_type , model_id) values (?,?,?)', [$role_id, 'App\Models\User', $user_id,]);
        $kelurahans = DB::table('indonesia_villages')
            ->select('r.code as code_provinces', 'c.code as code_cities','d.code as code_district','indonesia_villages.code as code_villages', 'indonesia_villages.name_village')
            ->join('indonesia_districts as d', 'indonesia_villages.district_code', '=', 'd.code')
            ->join('indonesia_cities as c', 'd.city_code', '=', 'c.code')
            ->join('indonesia_provinces as r', 'c.province_code', '=', 'r.code')
            ->where('c.code', '3273')
            ->limit(1) // Limit the number of kelurahans
            ->get();
        DB::table('wilayahs')->insert([
            'province_id' => $kelurahan->code_provinces,
            'kota_id' => $kelurahan->code_cities,
            'kecamatan_id' => $kelurahan->code_district,
            'kelurahan_id' => $kelurahan->code_villages,
            'status_wilayah' => 1,
            'createdby' => $warga->id
        ]);
        $kelurahans = DB::table('indonesia_villages')
        ->select('r.code as code_provinces', 'c.code as code_cities','d.code as code_district','indonesia_villages.code as code_villages', 'indonesia_villages.name_village')
        ->join('indonesia_districts as d', 'indonesia_villages.district_code', '=', 'd.code')
        ->join('indonesia_cities as c', 'd.city_code', '=', 'c.code')
        ->join('indonesia_provinces as r', 'c.province_code', '=', 'r.code')
        ->where('c.code', '3273')
        ->limit(3) // Limit the number of kelurahans
        ->get();
        
      
        $rolesToCreate = ['Front Office Kelurahan', 'Back Ofiice kelurahan'];
        
        foreach ($kelurahans as $kelurahan) {
            foreach ($rolesToCreate as $kelurahanName) {
                // Check if the role already exists
                $existingRole = Roles::where('name', $kelurahanName)
                    ->where('guard_name', 'web')
                    ->first();
        
                if (!$existingRole) {
                    // Create the role only if it doesn't already exist
                    $kelurahanRole = Roles::create([
                        'name' => $kelurahanName,
                        'guard_name' => 'web'
                    ]);
                } else {
                    // Use the existing role
                    $kelurahanRole = $existingRole;
                }
        
                // Create a user for each role and assign the role
                for ($i = 0; $i < 1; $i++) {
                    $user = User::create([
                        'name' => $kelurahan->name_village,
                        'email' => $kelurahan->name_village . '_' . strtolower(str_replace(' ', '', $kelurahanName)) . '_' . $i . '@dinsos.co.id',
                        'password' => bcrypt('123456')
                    ]);
        
                    DB::table('model_has_roles')->insert([
                        'role_id' => $kelurahanRole->id,
                        'model_type' => 'App\Models\User',
                        'model_id' => $user->id
                    ]);

                    DB::table('wilayahs')->insert([
                        'province_id' => $kelurahan->code_provinces,
                        'kota_id' => $kelurahan->code_cities,
                        'kecamatan_id' => $kelurahan->code_district,
                        'kelurahan_id' => $kelurahan->code_villages,
                        'status_wilayah' => 1,
                        'createdby' => $user->id
                    ]);
                }
            }
            // Assign user IDs to the current Kelurahan
        }
        $wargarole = Roles::where('name', 'warga')->first();
    if ($wargarole) {
        $permissions = [
            'rekomendasi-pengangangkatan-anak' => Permission::where('name', 'LIKE', 'rekomendasi-pengangangkatan-anak-%')->get(),
            'rekomendasi-daftar-ulang-yayasan' => Permission::where('name', 'LIKE', 'rekomendasi-daftar-ulang-yayasan%')->get(),
            'rekomendasi-terdaftar-yayasan' => Permission::where('name', 'LIKE', 'rekomendasi-terdaftar-yayasan-%')->get(),
            'rekomendasi-yayasan-provinsi' => Permission::where('name', 'LIKE', 'rekomendasi-yayasan-provinsi-%')->get(),
            'rekomendasi-pelaporan-pub' => Permission::where('name', 'LIKE', 'rekomendasi-pelaporan-pub-%')->get()
        ];

        foreach ($permissions as $permissionKey => $permissionsData) {
            foreach ($permissionsData as $permission) {
                $permissionId = $permission->id;
                DB::insert('insert into role_has_permissions (permission_id, role_id) values (?, ?)', [$permissionId, $wargarole->id]);
            }
        }
    }

    // Assign permissions to other roles (add more conditions as needed)
        $otherRoles = [
            'fasilitator',
            'Back Ofiice Kota',
            'Front Office kota',
            'supervisor',
            'Front Office Kelurahan',
            'Back Ofiice kelurahan',
            'kepala bidang',
            'SekertarisDinas',
            'KepalaDinas',
        ];

        foreach ($otherRoles as $roleName) {
            $role = Roles::where('name', $roleName)->first();
            if ($role) {
                // Adjust the permissions array based on the role's requirements
                // Example:
                $permissions = [
                    // 'pengaduan' => Permission::where('name', 'LIKE', 'pengaduan-%')->get(),
                    'rekomendasi-dtks' => Permission::where('name', 'LIKE', 'rekomendasi-dtks-%')->get(),
                    // 'rekomendasi-biaya-perawatans' => Permission::where('name', 'LIKE', 'rekomendasi-biaya-perawatans-%')->get(),
                    // 'role' => Permission::where('name', 'LIKE', 'role-%')->get(),
                    // 'rekomendasi-admin-kependudukan-list' => Permission::where('name', 'LIKE', 'rekomendasi-admin-kependudukan-list-%')->get(),
                    'rekomendasi-bantuan-pendidikan' => Permission::where('name', 'LIKE', 'rekomendasi-bantuan-pendidikan-%')->get(),
                    // 'rekomendasi-rehabilitasi-sosial' => Permission::where('name', 'LIKE', 'rekomendasi-rehabilitasi-sosial-%')->get(),
                    // 'rekomendasi-reaktivasi-pbijk' => Permission::where('name', 'LIKE', 'rekomendasi-reaktivasi-pbijk-%')->get(),
                    // 'rekomendasikeringanan-pbbs' => Permission::where('name', 'LIKE', 'rekomendasi-keringanan-pbbs-%')->get(),
                    // 'rekomendasi-pengangangkatan-anak' => Permission::where('name', 'LIKE', 'rekomendasi-pengangangkatan-anak-%')->get(),
                    // 'rekomendasi-daftar-ulang-yayasan' => Permission::where('name', 'LIKE', 'rekomendasi-daftar-ulang-yayasan%')->get(),
                    // 'rekomendasi-terdaftar-yayasan' => Permission::where('name', 'LIKE', 'rekomendasi-terdaftar-yayasan-%')->get(),
                    // 'rekomendasi-yayasan-provinsi' => Permission::where('name', 'LIKE', 'rekomendasi-yayasan-provinsi-%')->get(),
                    // 'rekomendasi-pelaporan-pub' => Permission::where('name', 'LIKE', 'rekomendasi-pelaporan-pub-%')->get()
                ];

                foreach ($permissions as $permissionKey => $permissionsData) {
                    foreach ($permissionsData as $permission) {
                        $permissionId = $permission->id;
                        DB::insert('insert into role_has_permissions (permission_id, role_id) values (?, ?)', [$permissionId, $role->id]);
                    }
                }
            }
        }
        $otherRoles = [
            // 'fasilitator',
            'Back Ofiice Kota',
            'Front Office kota',
            // 'supervisor',
            // 'Front Office Kelurahan',
            // 'Back Ofiice kelurahan',
            // 'kepala bidang',
            // 'SekertarisDinas',
            // 'KepalaDinas',
        ];

        foreach ($otherRoles as $roleName) {
            $role = Roles::where('name', $roleName)->first();
            if ($role) {
                // Adjust the permissions array based on the role's requirements
                // Example:
                $permissions = [
                    // 'pengaduan' => Permission::where('name', 'LIKE', 'pengaduan-%')->get(),
                    // 'rekomendasi-dtks' => Permission::where('name', 'LIKE', 'rekomendasi-dtks-%')->get(),
                    // 'rekomendasi-biaya-perawatans' => Permission::where('name', 'LIKE', 'rekomendasi-biaya-perawatans-%')->get(),
                    'role' => Permission::where('name', 'LIKE', 'role-%')->get(),
                    'user' => Permission::where('name', 'LIKE', 'user-%')->get(),
                    // 'rekomendasi-bantuan-pendidikan' => Permission::where('name', 'LIKE', 'rekomendasi-bantuan-pendidikan-%')->get(),
                    // 'rekomendasi-rehabilitasi-sosial' => Permission::where('name', 'LIKE', 'rekomendasi-rehabilitasi-sosial-%')->get(),
                    // 'rekomendasi-reaktivasi-pbijk' => Permission::where('name', 'LIKE', 'rekomendasi-reaktivasi-pbijk-%')->get(),
                    // 'rekomendasikeringanan-pbbs' => Permission::where('name', 'LIKE', 'rekomendasi-keringanan-pbbs-%')->get(),
                    // 'rekomendasi-pengangangkatan-anak' => Permission::where('name', 'LIKE', 'rekomendasi-pengangangkatan-anak-%')->get(),
                    // 'rekomendasi-daftar-ulang-yayasan' => Permission::where('name', 'LIKE', 'rekomendasi-daftar-ulang-yayasan%')->get(),
                    // 'rekomendasi-terdaftar-yayasan' => Permission::where('name', 'LIKE', 'rekomendasi-terdaftar-yayasan-%')->get(),
                    // 'rekomendasi-yayasan-provinsi' => Permission::where('name', 'LIKE', 'rekomendasi-yayasan-provinsi-%')->get(),
                    // 'rekomendasi-pelaporan-pub' => Permission::where('name', 'LIKE', 'rekomendasi-pelaporan-pub-%')->get()
                ];

                foreach ($permissions as $permissionKey => $permissionsData) {
                    foreach ($permissionsData as $permission) {
                        $permissionId = $permission->id;
                        DB::insert('insert into role_has_permissions (permission_id, role_id) values (?, ?)', [$permissionId, $role->id]);
                    }
                }
            }
        }
    }
}
