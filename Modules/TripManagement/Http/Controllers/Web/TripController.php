<?php

namespace Modules\TripManagement\Http\Controllers\Web;

use App\Traits\PdfGenerator;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\TripManagement\Entities\TripRequest;
use Modules\TripManagement\Repositories\TripRequestRepository;
use Modules\ZoneManagement\Entities\Area;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TripController extends Controller
{
    use AuthorizesRequests;
    use PdfGenerator;
    public function __construct(
        protected TripRequestRepository $trip
    )
    {
    }

    /**
     * @param Request $request
     * @param $id
     * @return Application|Factory|View|Response|string|StreamedResponse
     * @throws AuthorizationException
     */
    public function invoice(Request $request, $id)
    {
        $this->authorize('trip_view');
        $type = $request->get('file', 'pdf');
        $data = $this->trip->getBy(column: 'id', value: $id, attributes: ['relations' => ['tripStatus', 'coordinate', 'customer']]);
        if ($type != 'pdf'){
            return exportData($data, $type, 'tripmanagement::admin.trip.invoice');
        }else{
            $mpdf_view = \Illuminate\Support\Facades\View::make('tripmanagement::admin.trip.invoice',
                compact('data')
            );
            $this->generatePdf(view: $mpdf_view, filePrefix: 'trip_invoice_',filePostfix: $data->ref_id.time());
        }
        // return view('tripmanagement::admin.trip.invoice', compact('data'));
    }

    /**
     * @param Request $request
     * @return Application|Factory|View|Response|string|StreamedResponse
     */
    public function export(Request $request): View|Factory|Response|StreamedResponse|string|Application
    {
        $this->authorize('trip_export');

        $trips = TripRequest::query()
            ->with(['tripStatus', 'customer', 'driver', 'fee'])
            ->when($request->has('type') && $request['type'] != 'all', function ($query) use($request){
                $query->where('current_status', $request['type']);
            })
            ->when($request->has('user_type') && $request['user_type'] == 'customer', function ($query) use($request) {
                $query->where('customer_id', $request['id']);
            })
            ->when($request->has('user_type') && $request['user_type'] == 'driver', function ($query) use($request) {
                $query->where('driver_id', $request['id']);
            })
            ->orderBy('created_at', 'desc')
            ->latest()
            ->get();
        $data = $trips->map(fn($item) =>
        [
            'id' => $item['id'],
            'Trip ID' => $item['ref_id'],
            'Date' => date('d F Y', strtotime($item['created_at'])). ' ' .date('h:i a', strtotime($item['created_at'])),
            'Customer' => $item['customer']?->first_name. ' ' . $item['customer']?->first_name,
            'Driver' => $item['driver'] ? $item['driver']?->first_name. ' ' . $item['driver']?->first_name : 'no driver assigned',
            'Trip Cost' => $item['current_status'] == 'completed' ? getCurrencyFormat($item['actual_fare'] ?? 0) : getCurrencyFormat($item['estimated_fare'] ?? 0),
            'Coupon Discount' => getCurrencyFormat($item['coupon_amount'] ?? 0),
            'Delay Fee' => getCurrencyFormat($item['fee'] ? ($item['fee']->delay_fee) : 0),
            'Idle Fee' => getCurrencyFormat($item['fee'] ? ($item['fee']->idle_fee) : 0),
            'Cancellation Fee' => getCurrencyFormat($item['fee'] ? ($item['fee']->cancellation_fee) : 0),
            'Vat/Tax Fee' => getCurrencyFormat($item['fee'] ? ($item['fee']->vat_tax) : 0),
            'Total Additional Fee' => getCurrencyFormat($item['fee'] ? ($item['fee']->waiting_fee + $item['fee']->delay_fee + $item['fee']->idle_fee + $item['fee']->cancellation_fee  + $item['fee']->vat_tax ) : 0),
            'Total Trip Cost' => getCurrencyFormat($item['paid_fare']-$item['tips']),
            'Admin Commission' => getCurrencyFormat($item['fee'] ? $item['fee']->admin_commission : 0),
            'Payment Status' => ucwords($item['payment_status']),
            'Trip Status' => ucwords($item['current_status'])
        ]);
        return exportData($data, $request['file'], 'tripmanagement::admin.trip.print');
    }

    /**
     * @param Request $request
     * @return View
     */
    public function trashed(Request $request): View
    {
        $this->authorize('super-admin');

        $search = $request->has('search') ? $request->search : null;
        $trips = $this->trip->trashed(['search' => $search]);

        return view('tripmanagement::admin.trip.trashed', compact('trips', 'search'));

    }

    /**
     * @param $id
     * @return RedirectResponse
     */
    public function restore($id): RedirectResponse
    {
        $this->authorize('super-admin');

        $this->trip->restore($id);

        Toastr::success(DEFAULT_RESTORE_200['message']);
        return redirect()->route('admin.trip.index', ['all']);

    }
}


