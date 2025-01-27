<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        DB::table('head_of_accounts')->insert([
            ['id' => 1, 'name' => 'Assets'],
            ['id' => 2, 'name' => 'Liabilities'],
            ['id' => 3, 'name' => 'Expenses'],
            ['id' => 4, 'name' => 'Revenue'],
            ['id' => 5, 'name' => 'Equity'],
        ]);

        DB::table('product_categories')->insert([
            ['id' => 1, 'name' => "Men's Fabric"],
            ['id' => 2, 'name' => "Men's Finish Goods"],
            ['id' => 3, 'name' => 'Abaya Fabric'],
            ['id' => 4, 'name' => 'Ladies Finish Goods'],
            ['id' => 5, 'name' => 'Kids Finish Goods'],
            ['id' => 6, 'name' => 'Accessories'],
        ]);
    }
}
