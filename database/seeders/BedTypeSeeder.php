<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BedTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $sqlFile = file_get_contents(__DIR__ . '/../bedType.sql');
        DB::unprepared($sqlFile);
    }
}
