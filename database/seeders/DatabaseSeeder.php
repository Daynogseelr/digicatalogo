<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void{
        DB::table('users')->insert([
            'name' => 'DYOSER',
            'last_name' => 'GONZALEZ',
            'nationality' => 'V',
            'ci' => '2462228',
            'phone' => '04168835169',
            'email' => 'DYO123SER123@GMAIL.COM',
            'password' => Hash::make('24625228'),
            'type' => 'ADMINISTRADOR',
            'direction' => 'CARUPANO',
            'status' => '1',
        ]);
    }
}
