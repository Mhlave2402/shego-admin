@extends('adminmodule::layouts.master')

@section('title', translate('gift_cards'))

@section('content')
    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-wrap mb-3">
                        <h2 class="page-title">{{ translate('gift_cards') }}</h2>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>{{ translate('SL') }}</th>
                                            <th>{{ translate('code') }}</th>
                                            <th>{{ translate('amount') }}</th>
                                            <th>{{ translate('status') }}</th>
                                            <th>{{ translate('action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($giftCards as $key => $giftCard)
                                            <tr>
                                                <td>{{ $key + 1 }}</td>
                                                <td>{{ $giftCard->code }}</td>
                                                <td>{{ $giftCard->amount }}</td>
                                                <td>
                                                    <label class="switcher">
                                                        <input type="checkbox" class="switcher_input"
                                                            {{ $giftCard->is_active ? 'checked' : '' }}
                                                            onchange="updateStatus('{{ route('admin.gift-card.update', $giftCard->id) }}')">
                                                        <span class="switcher_control"></span>
                                                    </label>
                                                </td>
                                                <td>
                                                    <div class="d-flex gap-2">
                                                        <a href="{{ route('admin.gift-card.edit', $giftCard->id) }}"
                                                            class="btn btn-outline-primary btn-sm">{{ translate('edit') }}</a>
                                                        <form
                                                            action="{{ route('admin.gift-card.destroy', $giftCard->id) }}"
                                                            method="POST">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit"
                                                                class="btn btn-outline-danger btn-sm">{{ translate('delete') }}</button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center">{{ translate('no_data_found') }}</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End Main Content -->
@endsection
