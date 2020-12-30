<?php

namespace Database\Seeders;

use App\Models\TransactionMethod;
use Illuminate\Database\Seeder;

class TransactionMethodSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $titles = [
            "TOPUP",
            "WITHDRAW",
            "TRANSFER"
        ];

        foreach ($titles as $title) {
            TransactionMethod::create([
                "title" => $title
            ]);
        }
    }
}
