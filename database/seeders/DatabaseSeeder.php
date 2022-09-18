<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        Role::create(['name' => 'administrator']);
        Role::create(['name' => 'user/guest']);
        // \App\Models\User::factory(10)->create();
        DB::table('users')->insert([
            'name' => 'Admin',
            'first_name' => 'Admin',
            'last_name' => '',
            'username' => 'admin',
            'email' => 'admin@rpmhire.com',
            'password' => bcrypt('rpmhire@2022'),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
            'role_id' => 1,
            'mobile' => '9876543210',
            'dob' => '01-01-1971',
            'address' => 'Keilor Park, VIC 3042',
            'validity_date' => date('Y-m-d H:i:s', strtotime('01-12-2050')),
            'created_by' => 0,
            'is_verified' => 1,
            'api_token' => '',
            'status' => 1,
        ]);
        DB::table('users')->insert([
            'name' => 'RPM User',
            'first_name' => 'RPM',
            'last_name' => 'User',
            'username' => 'rpmhire',
            'email' => 'user@rpmhire.com',
            'password' => bcrypt('rpmhire@2022'),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
            'role_id' => 2,
            'mobile' => '9876543210',
            'dob' => '01-01-1971',
            'address' => 'Keilor Park, VIC 3042',
            'validity_date' => date('Y-m-d H:i:s', strtotime('01-12-2050')),
            'created_by' => 1,
            'is_verified' => 1,
            'api_token' => '',
            'status' => 1,
        ]);
        DB::table('configurations')->insert([
            'key' => 'CONTACT_PAGE_NO',
            'value' => '1',
        ]);
        DB::table('configurations')->insert([
            'key' => 'CONTRACT_PAGE_NO',
            'value' => '1',
        ]);
    }
}
