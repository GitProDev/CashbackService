<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\LocaleTrait;

class MerchantController extends Controller
{
    use LocaleTrait;

    public function __invoke(Request $request)
    {
        $merchants = User::all()->map(function ($user) {
            return [
                'name'  => $user->localeName($this->getLocale())?->name ?? $user->name,
                'email' => $user->email,
            ];
        });

        return response()->json(['merchants' => $merchants]);
    }
}