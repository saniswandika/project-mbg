<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DtksSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        try {
            //TODO::download action for dtks dump file
            $cmd = "mysql -u " . env('DB_USERNAME') . " -p" . env('DB_PASSWORD') . " " . env('DB_DATABASE') . " < " . base_path() . '/database/seeders/dtks.sql' . "";
            exec($cmd);
        } catch (\Exception $e) {
            echo 'mysqldump-php error: ' . $e->getMessage();
        }
    }
}
