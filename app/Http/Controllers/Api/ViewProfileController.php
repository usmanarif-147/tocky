<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Profile\ViewProfileRequest;
use App\Models\Card;
use App\Models\User;
use App\Services\CategoryService;
use Illuminate\Support\Facades\DB;

class ViewProfileController extends Controller
{

    public function viewUserProfile(ViewProfileRequest $request)
    {
        $card = null;
        $checkCard = null;
        if ($request->has('card_uuid')) {
            $card = Card::where('uuid', $request->card_uuid)->first();
            if (!$card) {
                return response()->json(['message' => 'Card not found']);
            }
            // check card status
            if (!$card->status) {
                return response()->json(['message' => "Card is not activated"]);
            }
            // check user card status is active or not
            $checkCard = DB::table('user_cards')
                ->select('user_cards.user_id')
                ->where('card_id', $card->id)
                ->where('status', 1)
                ->first();
            if (!$checkCard) {
                return response()->json(['message' => "User profile not accessible"]);
            }
        }

        if ($checkCard) {
            $res['user'] = User::where('id', $checkCard->user_id)->first();
        } else {
            $res['user'] = User::where('username', $request->username)->first();
        }
        if (!$res['user']) {
            return response()->json(['message' => "User profile not found"]);
        }

        $res['user']->connected = 0;
        if ($res['user']->id != auth()->id()) {
            $connected = DB::table('connects')->where('connecting_id', auth()->id())
                ->where('connected_id', $res['user']->id)
                ->first();
            if ($connected) {
                $res['user']->connected = 1;
            }
        }

        $categoryService = new CategoryService();

        if ($res['user']->id != auth()->id() || $res['user']->username != auth()->user()->username) {
            User::where('id', $res['user']->id)->increment('tiks');
        }


        return response()->json([
            'message' => 'User profile',
            'user' => $res['user'],
            'categories' => $categoryService->categoryWithPlatorms($res['user']->id)
        ]);
    }
}
