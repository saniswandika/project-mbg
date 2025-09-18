<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MasterDataIndonesia extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

       $path = base_path() . '/database/seeders/indonesia_provinces.sql';

       $sql = file_get_contents($path);

       DB::unprepared($sql);

        $path = base_path() . '/database/seeders/indonesia_cities.sql';
       $sql = file_get_contents($path);
       DB::unprepared($sql);

       $path = base_path() . '/database/seeders/indonesia_districts.sql';
       $sql = file_get_contents($path);
       DB::unprepared($sql);

       $path = base_path() . '/database/seeders/indonesia_villages.sql';
       $sql = file_get_contents($path);
       DB::unprepared($sql);
       $path = base_path() . '/database/seeders/indonesia_villages2.sql';
       $sql = file_get_contents($path);
       DB::unprepared($sql);
       $path = base_path() . '/database/seeders/indonesia_villages3.sql';
       $sql = file_get_contents($path);
       DB::unprepared($sql);
       $path = base_path() . '/database/seeders/indonesia_villages4.sql';
       $sql = file_get_contents($path);
       DB::unprepared($sql);
       $path = base_path() . '/database/seeders/indonesia_villages5.sql';
       $sql = file_get_contents($path);
       DB::unprepared($sql);
       $path = base_path() . '/database/seeders/indonesia_villages6.sql';
       $sql = file_get_contents($path);
       DB::unprepared($sql);
       $path = base_path() . '/database/seeders/indonesia_villages7.sql';
       $sql = file_get_contents($path);
       DB::unprepared($sql);
       $path = base_path() . '/database/seeders/indonesia_villages8.sql';
       $sql = file_get_contents($path);
       DB::unprepared($sql);
       $path = base_path() . '/database/seeders/indonesia_villages9.sql';
       $sql = file_get_contents($path);
       DB::unprepared($sql);
       $path = base_path() . '/database/seeders/indonesia_villages10.sql';
       $sql = file_get_contents($path);
       DB::unprepared($sql);
         $path = base_path() . '/database/seeders/indonesia_villages11.sql';
       $sql = file_get_contents($path);
       DB::unprepared($sql);
       $path = base_path() . '/database/seeders/indonesia_villages12.sql';
       $sql = file_get_contents($path);
       DB::unprepared($sql);
       $path = base_path() . '/database/seeders/indonesia_villages13.sql';
       $sql = file_get_contents($path);
       DB::unprepared($sql);
       $path = base_path() . '/database/seeders/indonesia_villages14.sql';
       $sql = file_get_contents($path);
       DB::unprepared($sql);

    }
}
