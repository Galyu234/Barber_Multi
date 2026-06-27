<?php

namespace Database\Seeders;

use App\Models\Barbershop;
use App\Models\Branch;
use App\Models\Queue;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ─── Super Admin ────────────────────────────────────────
        User::create([
            'name'     => 'Super Admin',
            'email'    => 'superadmin@barberq.com',
            'password' => Hash::make('password'),
            'role'     => 'super_admin',
        ]);

        // ─── Barbershop 1 ────────────────────────────────────────
        $bs1 = Barbershop::create([
            'name'       => 'Rapih Barbershop',
            'slug'       => 'rapih-barbershop',
            'owner_name' => 'Budi Santoso',
            'phone'      => '0812-3456-7890',
            'address'    => 'Jl. Merdeka No. 10, Jakarta',
            'is_active'  => true,
        ]);

        // No general admin for barbershop anymore

        $branches1 = [
            ['name' => 'Rapih - Pusat',       'code' => 'RP001', 'address' => 'Jl. Merdeka No. 10, Jakarta Pusat'],
            ['name' => 'Rapih - Selatan',     'code' => 'RP002', 'address' => 'Jl. Sudirman No. 45, Jakarta Selatan'],
            ['name' => 'Rapih - Timur',       'code' => 'RP003', 'address' => 'Jl. Kalimalang No. 7, Jakarta Timur'],
        ];

        foreach ($branches1 as $i => $b) {
            $branch = Branch::create(array_merge($b, [
                'barbershop_id'         => $bs1->id,
                'phone'                 => '021-' . rand(10000000, 99999999),
                'open_time'             => '08:00:00',
                'close_time'            => '21:00:00',
                'is_active'             => true,
                'queue_timeout_minutes' => 60,
                'avg_service_minutes'   => 15,
            ]));

            // Seed admin per branch
            User::create([
                'name'      => 'Admin ' . $branch->code,
                'email'     => 'admin_' . strtolower($branch->code) . '@rapih.com',
                'password'  => Hash::make('password'),
                'role'      => 'admin',
                'branch_id' => $branch->id,
            ]);

            $this->seedQueues($branch);
        }

        // ─── Barbershop 2 ────────────────────────────────────────
        $bs2 = Barbershop::create([
            'name'       => 'Raja Cukur',
            'slug'       => 'raja-cukur',
            'owner_name' => 'Andi Wijaya',
            'phone'      => '0856-9876-5432',
            'address'    => 'Jl. Pahlawan No. 22, Bandung',
            'is_active'  => true,
        ]);

        // No general admin for barbershop anymore

        $branches2 = [
            ['name' => 'Raja Cukur - Dago',   'code' => 'RC001', 'address' => 'Jl. Dago No. 88, Bandung'],
            ['name' => 'Raja Cukur - Buah Batu','code' => 'RC002', 'address' => 'Jl. Buah Batu No. 34, Bandung'],
        ];

        foreach ($branches2 as $b) {
            $branch = Branch::create(array_merge($b, [
                'barbershop_id'         => $bs2->id,
                'phone'                 => '022-' . rand(10000000, 99999999),
                'open_time'             => '09:00:00',
                'close_time'            => '20:00:00',
                'is_active'             => true,
                'queue_timeout_minutes' => 60,
                'avg_service_minutes'   => 20,
            ]));

            // Seed admin per branch
            User::create([
                'name'      => 'Admin ' . $branch->code,
                'email'     => 'admin_' . strtolower($branch->code) . '@rajacukur.com',
                'password'  => Hash::make('password'),
                'role'      => 'admin',
                'branch_id' => $branch->id,
            ]);

            $this->seedQueues($branch);
        }
    }

    private function seedQueues(Branch $branch): void
    {
        $count = rand(3, 8);
        for ($i = 1; $i <= $count; $i++) {
            Queue::create([
                'branch_id'        => $branch->id,
                'queue_number'     => $i,
                'customer_session' => Str::random(40),
                'queue_qr'         => Str::random(40),
                'status'           => $i === 1 ? 'serving' : 'waiting',
                'joined_at'        => now()->subMinutes(($count - $i) * 15),
            ]);
        }
    }
}
