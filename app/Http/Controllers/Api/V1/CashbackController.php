<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Carbon;
use App\Models\DailyLimit;
use App\Models\User;
use App\Traits\LocaleTrait;

class CashbackController extends Controller
{
    use LocaleTrait;

    /**
     * Handle the incoming request to simulate a transaction and attempt to award cashback.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    // This method simulates a transaction and attempts to award cashback based on the current campaign.
    public function __invoke(Request $request)
    {
        $user = $request->user();

        $cashbackCampaign = DB::table('campaigns')->where('name', 'Cashback')
                                                ->where('start_date', '<=', now())
                                                ->where('end_date', '>=', now())
                                                ->first();

        $now = Carbon::now();

        if (!$cashbackCampaign || !$this->isWithinPeakHours($now)) {
            return response()->json(['success' => true, 'data' => ['won' => false, 'reason' => 'promotion_not_active']]);
        }

        $today = $now->toDateString();

        try {
            DB::beginTransaction();
            // Pessimistic lock on the daily limits row
            $daily = DailyLimit::where('campaign_id', $cashbackCampaign->id)
                                ->where('day', $today)
                                ->lockForUpdate()
                                ->first();

            if (! $daily || $daily->remaining <= 0) {
                DB::commit();
                return response()->json(['success' => true, 'data' => ['won' => false, 'reason' => 'no_remaining']], 200);
            }

            // check user hasn't already won today
            $exists = DB::table('rewards')->where('user_id', $user->id)
                                        ->where('campaign_id', $cashbackCampaign->id)
                                        ->where('awarded_at', $today)
                                        ->exists();

            if ($exists) {
                DB::commit();
                return response()->json(['success' => true, 'data' => ['won' => false, 'reason' => 'already_won']], 200);
            }

            // Award reward
            $reward = DB::table('rewards')->insert([
                'user_id' => $user->id,
                'campaign_id' => $cashbackCampaign->id,
                'amount' => $cashbackCampaign->reward_amount,
                'awarded_at' => $today,
            ]);

            $daily->decrement('remaining');

            DB::commit();

            return response()->json(['success' => true, 'data' => [ 'won' => true,
                                                                    'winner_name' => $user->localeName($this->getLocale())?->name ?? $user->name,
                                                                    'reward' => $reward]], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success'=> false, 'error' => 'internal_error'], 500);
        }
    }

    private function isWithinPeakHours(Carbon $now): bool
    {
        // Ensure we're using server timezone
        $now = $now->copy()->timezone(config('app.timezone'));

        $hour = (int) $now->format('H');
        $minute = (int) $now->format('i');
        $time = $hour * 60 + $minute; // convert to minutes since midnight

        $start = 9 * 60;      // 09:00 = 540
        $end   = 19 * 60 + 59; // 19:59 = 1199

        return $time >= $start && $time <= $end;
    }
}