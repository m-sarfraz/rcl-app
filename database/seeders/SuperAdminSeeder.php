<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $role = Role::where('name', 'super_admin')->firstOrFail();

        User::updateOrCreate(
            ['email' => 'superadmin@rcl.com'],
            [
                'name'              => 'RCL Super Admin',
                'password'          => Hash::make('RCL@SuperAdmin#2024'),
                'role_id'           => $role->id,
                'is_active'         => true,
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('✅ Super Admin created:');
        $this->command->line('   Email    : superadmin@rcl.com');
        $this->command->line('   Password : RCL@SuperAdmin#2024');
        $this->command->warn('   ⚠  Change this password immediately after first login!');
    }
}
