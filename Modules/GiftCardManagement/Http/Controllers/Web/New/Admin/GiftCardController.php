<?php

namespace Modules\GiftCardManagement\Http\Controllers\Web\New\Admin;

use App\Http\Controllers\BaseController;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\GiftCardManagement\Service\Interface\GiftCardServiceInterface;

class GiftCardController extends BaseController
{
    use AuthorizesRequests;

    protected $giftCardService;

    public function __construct(GiftCardServiceInterface $giftCardService)
    {
        parent::__construct($giftCardService);
        $this->giftCardService = $giftCardService;
    }

    public function index(Request $request): Renderable
    {
        $this->authorize('gift_card_view');
        $giftCards = $this->giftCardService->index(criteria: $request->all(), orderBy: ['created_at' => 'desc'], limit: paginationLimit(), offset: $request->page ?? 1);
        return view('giftcardmanagement::admin.index', compact('giftCards'));
    }

    public function create(): Renderable
    {
        $this->authorize('gift_card_add');
        return view('giftcardmanagement::admin.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('gift_card_add');
        // Add validation logic here
        $this->giftCardService->create(data: $request->all());
        Toastr::success(GIFT_CARD_CREATE_200['message']);
        return redirect()->route('admin.gift-card.index');
    }

    public function edit(string $id): Renderable
    {
        $this->authorize('gift_card_edit');
        $giftCard = $this->giftCardService->findOne(id: $id);
        return view('giftcardmanagement::admin.edit', compact('giftCard'));
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $this->authorize('gift_card_edit');
        // Add validation logic here
        $this->giftCardService->update(id: $id, data: $request->all());
        Toastr::success(GIFT_CARD_UPDATE_200['message']);
        return redirect()->route('admin.gift-card.index');
    }

    public function destroy(string $id): RedirectResponse
    {
        $this->authorize('gift_card_delete');
        $this->giftCardService->delete(id: $id);
        Toastr::success(GIFT_CARD_DELETE_200['message']);
        return back();
    }
}
