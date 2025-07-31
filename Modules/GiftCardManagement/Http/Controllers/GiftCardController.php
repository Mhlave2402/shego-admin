<?php

namespace Modules\GiftCardManagement\Http\Controllers;

use App\Models\GiftCard;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;

class GiftCardController extends Controller
{
    public function index()
    {
        $giftCards = GiftCard::all();
        return response()->json($giftCards);
    }

    public function store(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:1',
            'expires_at' => 'nullable|date',
        ]);

        $giftCards = [];
        for ($i = 0; $i < $request->quantity; $i++) {
            $giftCards[] = GiftCard::create([
                'code' => Str::random(16),
                'amount' => $request->amount,
                'expires_at' => $request->expires_at,
            ]);
        }

        return response()->json($giftCards, 201);
    }

    public function show(GiftCard $giftCard)
    {
        return response()->json($giftCard);
    }

    public function update(Request $request, GiftCard $giftCard)
    {
        $request->validate([
            'status' => 'required|in:active,used,expired',
        ]);

        $giftCard->update($request->only('status'));

        return response()->json($giftCard);
    }

    public function destroy(GiftCard $giftCard)
    {
        $giftCard->delete();

        return response()->json(null, 204);
    }

    public function redeem(Request $request)
    {
        $request->validate([
            'code' => 'required|exists:gift_cards,code',
        ]);

        $giftCard = GiftCard::where('code', $request->code)->first();

        if ($giftCard->status !== 'active') {
            return response()->json(['message' => 'This gift card is not active.'], 422);
        }

        if ($giftCard->expires_at && $giftCard->expires_at->isPast()) {
            $giftCard->update(['status' => 'expired']);
            return response()->json(['message' => 'This gift card has expired.'], 422);
        }

        $giftCard->update([
            'status' => 'used',
            'user_id' => auth()->id(),
        ]);

        // Here you would typically credit the user's account with the gift card amount.
        // For this example, we'll just return the updated gift card.

        return response()->json($giftCard);
    }
}
