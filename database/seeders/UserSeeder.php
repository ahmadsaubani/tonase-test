<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $names = [
            'Ahmad Saubani',
            'Chanelle Goodman',
            'Jarvis Mcintyre',
            'Tiya Duarte',
            'Haidar Farmer',
            'Danny Farrington',
            'Shamas Ingram',
            'Nathan Finley',
            'Zuzanna Bruce',
            'Sarah Kennedy'
        ];

        for ($i = 1; $i < 9; $i++) {
            $name = strtolower(str_replace(' ', '.', $names[$i-1]));
            $user = User::create([
                'id'            => $i,
                'name'          => $name,
                'email'         => $name ."@tonase.com",
                'password'      => Hash::make('password')
            ]);

            $user->wallet()->create([
                "wallet_id" => Str::random(30),
                "balance"   => 0
            ]);
        }
    }
}
