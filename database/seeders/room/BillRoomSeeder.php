<?php

namespace Database\Seeders\room;

use App\Models\room\BillRoom;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BillRoomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        BillRoom::factory(20)->create();
    }
}
