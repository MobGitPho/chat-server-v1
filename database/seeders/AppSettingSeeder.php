<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AppSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('app_settings')->insert([
            [
                'key'    => 'disableInstaller',
                'value' => '1',
                'created_at' => now(),
                'updated_at' => now(),
            ], [
                'key'    => 'allowRegistration',
                'value' => '1',
                'created_at' => now(),
                'updated_at' => now(),
            ], [
                'key'    => 'adminProfileTypeLabel',
                'value' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ], [
                'key'    => 'displayAdminProfileType',
                'value' => '1',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}
