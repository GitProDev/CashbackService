<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Carbon;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $cashbackCompaignId = DB::table('campaigns')->insertGetId([
                'name'          => 'Cashback',
                'start_date'    => Carbon::today(),
                'end_date'      => Carbon::tomorrow()->endOfDay(),
                'total_pool'    => 100,
                'per_day_pool'  => 50,
                'reward_amount' => 10.00,
                'created_at'    => now(),
                'updated_at'    => now(),
        ]);

        foreach ([Carbon::today(), Carbon::tomorrow()] as $day) {
            DB::table('daily_limits')->insert([
                    'campaign_id' => $cashbackCompaignId,
                    'day'         => $day,
                    'remaining'   => 50,
                    'created_at'  => now(),
                    'updated_at'  => now(),
            ]);
        };
    }
}
