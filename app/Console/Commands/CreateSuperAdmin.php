<?php

namespace App\Console\Commands;

use App\Models\Roles;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CreateSuperAdmin extends Command
{
    protected $signature = 'app:create-super-admin';

    protected $description = 'Create a Super Admin account';

    public function handle()
    {
        $name = $this->ask('Nama Super Admin', 'Super Admin');
        $username = $this->ask('Username untuk login');

        if (!$name || strlen($name) > 100) {
            $this->error('Nama wajib diisi dan maksimal 100 karakter.');

            return 1;
        }

        if (!$username || strlen($username) > 50) {
            $this->error('Username wajib diisi dan maksimal 50 karakter.');

            return 1;
        }

        if (User::where('username', $username)->exists()) {
            $this->error('Username sudah digunakan. Pilih username yang lain.');

            return 1;
        }

        $password = $this->secret('Password (minimal 8 karakter)');
        $passwordConfirmation = $this->secret('Konfirmasi password');

        if (!$password || strlen($password) < 8) {
            $this->error('Password harus memiliki minimal 8 karakter.');

            return 1;
        }

        if ($password !== $passwordConfirmation) {
            $this->error('Konfirmasi password tidak cocok.');

            return 1;
        }

        DB::transaction(function () use ($name, $username, $password) {
            $role = Roles::firstOrNew([
                'name' => 'Super Admin',
                'guard_name' => 'web',
            ]);
            $role->is_delete = 0;
            $role->save();

            $user = User::create([
                'name' => $name,
                'username' => $username,
                'password' => Hash::make($password),
                'id_role' => $role->id,
                'is_delete' => 0,
            ]);

            $user->assignRole($role);
        });

        $this->info('Akun Super Admin berhasil dibuat. Silakan login dengan username yang dipilih.');

        return 0;
    }
}
