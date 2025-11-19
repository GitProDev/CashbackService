<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Carbon;

class RewardStatusController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();

        $reward = DB::table('rewards')
                ->join('campaigns', 'campaigns.id', '=', 'rewards.campaign_id')
                ->where('user_id', $user->id)
                ->where('campaigns.name', 'Cashback')
                ->where('awarded_at', Carbon::today()->toDateString())
                ->select(
                    DB::raw("CONCAT(CAST(amount AS UNSIGNED), '€') as amount"),
                    DB::raw("DATE_FORMAT(rewards.awarded_at, '%d-%M-%Y') as awarded_at"))
                ->first();

        if ($reward) {
            return response()->json(['won' => true, 'reword_details' => $reward]);
        }

        return response()->json(['won' => false]);          
    }
}