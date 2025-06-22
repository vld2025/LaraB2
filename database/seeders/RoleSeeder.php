<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\User;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Crea i ruoli solo se non esistono
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $managerRole = Role::firstOrCreate(['name' => 'manager']);
        $userRole = Role::firstOrCreate(['name' => 'user']);

        // Assegna il ruolo admin al primo utente
        $user = User::where('email', 'vlad@vldservice.ch')->first();
        if ($user) {
            // Rimuovi tutti i ruoli esistenti e assegna admin
            $user->syncRoles(['admin']);
            $this->command->info('Admin role assigned to ' . $user->email);
        } else {
            $this->command->error('User not found!');
        }
    }
}
