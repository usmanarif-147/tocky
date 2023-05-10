<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Card\CardRequest;
use App\Models\Card;
use App\Models\User;
use App\Services\CategoryService;
use Exception;
use Illuminate\Support\Facades\DB;

class CardController extends Controller
{
    public function index()
    {
        $cards = DB::table('user_cards')
            ->select(
                'cards.id',
                'cards.uuid',
                'cards.description',
                'user_cards.status',
                'user_cards.created_at'
            )
            ->join('cards', 'cards.id', 'user_cards.card_id')
            ->where('user_id', auth()->id())
            ->get();

        return response()->json(['data' => $cards]);
    }

    public function cardProfileDetail(CardRequest $request)
    {
        $card = Card::where('uuid', $request->card_uuid)->first();
        if (!$card) {
            return  response()->json(['message' => 'Card not found']);
        }

        // check card status
        if (!$card->status) {
            return  response()->json(['message' => "Card is not activated"]);
        }

        // check user card status is active or not
        $checkCard = DB::table('user_cards')
            ->select('user_cards.user_id')
            ->where('card_id', $card->id)
            ->where('status', 1)
            ->first();
        if (!$checkCard) {
            return  response()->json(['message' => "User profile not accessible"]);
        }

        $res['user'] = User::where('id', $checkCard->user_id)->first();
        if (!$res['user']) {
            return  response()->error("Profile not found");
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

        User::where('id', $res['user']->id)->increment('tiks');

        return response()->json([
            'message' => 'User profile',
            'user' => $res['user'],
            'categories' => $categoryService->categoryWithPlatorms($res['user']->id)
        ]);
    }

    public function activateCard(CardRequest $request)
    {

        // check card exist
        $card = Card::where('uuid', $request->card_uuid)->first();
        if (!$card) {
            return response()->json(["message" => "Card not found"]);
        }

        // check card is already activated
        if ($card->status) {
            return response()->json(["message" => "Card is already activated"]);
        }

        try {
            // insert card in user cards table
            DB::table('user_cards')->insert([
                'card_id' => $card->id,
                'user_id' => auth()->id(),
                'status' => 1
            ]);

            // update card status to activated
            DB::table('cards')->where('id', $card->id)->update([
                'status' => 1
            ]);

            return response()->json(["message" => "Card activated successfully"]);
        } catch (Exception $ex) {
            return response()->json(["message" => $ex->getMessage()]);
        }
    }

    public function changeCardStatus(CardRequest $request)
    {

        // check card exist
        $card = Card::where('uuid', $request->card_uuid)->first();
        if (!$card) {
            return response()->json(["message" => "Card not found"]);
        }

        // check is card belongs to the user
        $checkCard = DB::table('user_cards')
            ->where('user_id', auth()->id())
            ->where('card_id', $card->id)
            ->get()
            ->first();
        if (!$checkCard) {
            return response()->json(['message' => 'Not authenticated user']);
        }

        // update user_card status
        try {
            DB::table('user_cards')
                ->where('user_id', auth()->id())
                ->where('card_id', $card->id)
                ->update(['status' => $checkCard->status ? 0 : 1]);

            if ($checkCard->status) {
                return response()->json(['message' => 'Card dectivated successfully']);
            }
            return response()->json(['message' => 'Card activated successfully']);
        } catch (Exception $ex) {
            return response()->json(['message' => $ex->getMessage()]);
        }
    }
}
