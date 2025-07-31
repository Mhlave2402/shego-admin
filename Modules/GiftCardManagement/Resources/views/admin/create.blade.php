@extends('adminmodule::layouts.master')

@section('title', translate('add_gift_card'))

@section('content')
    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-wrap mb-3">
                        <h2 class="page-title">{{ translate('add_gift_card') }}</h2>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <form action="{{ route('admin.gift-card.store') }}" method="POST">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="code">{{ translate('code') }}</label>
                                            <input type="text" class="form-control" name="code" id="code"
                                                placeholder="{{ translate('ex') }}: ABC-123" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="amount">{{ translate('amount') }}</label>
                                            <input type="number" class="form-control" name="amount" id="amount"
                                                placeholder="{{ translate('ex') }}: 100" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-end gap-3">
                                    <button type="reset" class="btn btn-secondary">{{ translate('reset') }}</button>
                                    <button type="submit" class="btn btn-primary">{{ translate('submit') }}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End Main Content -->
@endsection
