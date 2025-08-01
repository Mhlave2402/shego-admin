<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Modules\UserManagement\Entities\User;
use Ramsey\Uuid\Uuid;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $userData = [
            'id' => Uuid::uuid4(),
            'first_name' => 'Super',
            'last_name' => 'Admin',
            'email' => 'admin@admin.com',
            'password' => bcrypt(12345678),
            'user_type' => 'super-admin',
            'is_active' => true
        ];

        // Check if user exists before inserting
        $userExists = DB::table('users')->where('email', $userData['email'])->exists();

        if (!$userExists) {
            DB::table('users')->insert($userData);
        }

        $user = User::where('email', 'admin@admin.com')->first();
        if ($user) {
            $user->assignRole('super-admin');
        }
    }
}
