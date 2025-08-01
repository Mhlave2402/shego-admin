@extends('adminmodule::layouts.master')

@section('title', translate('expense_reports'))

@section('content')

    <div class="main-content">
        <div class="container-fluid">
            <h4 class="text-capitalize mb-3">{{ translate('Report Analytics') }}</h4>
            <div class="d-flex mb-3">
                <ul class="nav nav--tabs p-1 rounded bg-white" role="tablist">
                    <li class="nav-item" role="presentation">
                        <a href="{{route('admin.report.earning')}}" class="nav-link">Earning</a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a href="{{route('admin.report.expense')}}" class="nav-link active">Expense</a>
                    </li>
                </ul>
            </div>
            <div class="mb-4 row g-4">
                <div class="col-lg-6 col-xl-6 col-xxl-5">
                    <div class="card h-100">
                        <div class="card-header d-flex flex-wrap justify-content-between gap-10 border-0 align-items-center pb-0">
                            <h5 class="text-capitalize m-0">{{translate('Expense Statistics')}}</h5>
                            <div class="d-flex flex-wrap flex-sm-nowrap gap-2 align-items-center">
                                <select class="js-select" id="dateRangeForExpenseStatistics">
                                    <option value="{{ALL_TIME}}" selected>{{translate(ALL_TIME)}}</option>
                                    <option value="{{TODAY}}">{{translate(TODAY)}}</option>
                                    <option value="{{PREVIOUS_DAY}}">{{translate(PREVIOUS_DAY)}}</option>
                                    <option value="{{LAST_7_DAYS}}">{{translate(LAST_7_DAYS)}}</option>
                                    <option value="{{THIS_WEEK}}">{{translate(THIS_WEEK)}}</option>
                                    <option value="{{THIS_MONTH}}">{{translate(THIS_MONTH)}}</option>
                                    <option value="{{LAST_MONTH}}">{{translate(LAST_MONTH)}}</option>
                                    <option value="{{THIS_YEAR}}">{{translate(THIS_YEAR)}}</option>
                                </select>
                            </div>
                            <div class="w-100 border-bottom pt-3"></div>
                        </div>
                        <div class="card-body">
                            <div class="d-flex flex-lg-nowrap flex-wrap justify-content-lg-start justify-content-center">
                                <div class="d-flex justify-content-center">
                                    <div class="position-relative pie-chart">
                                        <div class="pie-placeholder"></div>
                                        <div id="dognut-pie" class="pie-chart-inner"></div>
                                        <div class="total--orders">
                                            <h4 class="text-uppercase mb-xxl-2">{{getSession('currency_symbol')}}<span id="totalExpense"></span></h4>
                                            <span class="text-capitalize">{{translate("Expenses")}}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex flex-xl-column flex-wrap gap-2 w-100">
                                    <div class="expense-info bg-F6F6F6 px-xl-3 px-2">
                                        <div class="icon bg-info">
                                            <img src="{{asset('public/assets/admin-module/img/svg/ride-sharing.svg')}}" alt="">
                                        </div>
                                        <div class="w-0 flex-grow-1">
                                            <span>{{translate("Ride Request")}}</span>
                                            <h5 class="m-0">{{getSession('currency_symbol')}}<span id="rideExpense"></span></h5>
                                        </div>
                                    </div>
                                    <div class="expense-info bg-F6F6F6 px-xl-3 px-2">
                                        <div class="icon bg-danger">
                                            <img src="{{asset('public/assets/admin-module/img/svg/ride-sharing.svg')}}" alt="">
                                        </div>
                                        <div class="w-0 flex-grow-1">
                                            <span class="fs-12">{{translate("Schedule Ride")}}</span>
                                            <h6 class="mt-1">{{getSession('currency_symbol')}}<span id="scheduleRide"></span></h6>
                                        </div>
                                    </div>
                                    <div class="expense-info bg-F6F6F6 px-xl-3 px-2">
                                        <div class="icon bg-warning">
                                            <img src="{{asset('public/assets/admin-module/img/svg/parcel.svg')}}" alt="">
                                        </div>
                                        <div class="w-0 flex-grow-1">
                                            <span>{{translate("Parcel")}}</span>
                                            <h5 class="m-0">{{getSession('currency_symbol')}}<span id="parcelExpense"></span></h5>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 col-xl-6 col-xxl-7">
                    <div class="card h-100">
                        <div class="card-header d-flex flex-wrap justify-content-between gap-10 pb-0 border-0 align-items-center">
                            <h5 class="text-capitalize m-0">{{translate('Zone Wise  Statistics')}}</h5>
                            <div class="d-flex flex-wrap flex-sm-nowrap gap-2 align-items-center">
                                <select class="js-select" id="dateRange">
                                    <option disabled>{{translate('Select_Duration')}}</option>
                                    <option value="{{ALL_TIME}}" selected>{{translate(ALL_TIME)}}</option>
                                    <option value="{{TODAY}}">{{translate(TODAY)}}</option>
                                    <option value="{{PREVIOUS_DAY}}">{{translate(PREVIOUS_DAY)}}</option>
                                    <option value="{{LAST_7_DAYS}}">{{translate(LAST_7_DAYS)}}</option>
                                    <option value="{{THIS_WEEK}}">{{translate(THIS_WEEK)}}</option>
                                    <option value="{{THIS_MONTH}}">{{translate(THIS_MONTH)}}</option>
                                    <option value="{{LAST_MONTH}}">{{translate(LAST_MONTH)}}</option>
                                    <option value="{{THIS_YEAR}}">{{translate(THIS_YEAR)}}</option>
                                </select>
                            </div>
                        </div>
                        <div class="card-body hide-apexcharts-tooltip-title hide-1st-line-of-chart" id="updating_line_chart">
                            <div id="apex_line-chart"></div>
                        </div>
                    </div>
                </div>
            </div>
            <h4 class="text-capitalize pt-2 mb-4">{{ translate('Trip Wise Expense') }}</h4>
            <div class="card">
                <div class="card-body">
                    <div class="table-top d-flex flex-wrap gap-10 justify-content-between">
                        <form action="{{url()->current()}}" class="search-form search-form_style-two">
                            <div class="input-group search-form__input_group">
                                <span class="search-form__icon">
                                    <i class="bi bi-search"></i>
                                </span>
                                <input type="search" value="{{request()->search}}" id="search" name="search" class="theme-input-style search-form__input"
                                       placeholder="{{translate('Search')}}">
                            </div>
                            <button type="submit" class="btn btn-primary search-submit">{{translate('Search')}}</button>
                        </form>

                        <div class="d-flex flex-wrap gap-md-3 gap-2">
                            <div class="dropdown">
                                <button type="button" class="btn btn-outline-primary" data-bs-toggle="dropdown">
                                    <i class="bi bi-download"></i>
                                    {{translate("download")}}
                                    <i class="bi bi-caret-down-fill"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                                    <li>
                                        <a class="dropdown-item" target="_blank" href="{{route('admin.report.expenseReportExport',['file' => 'excel', request()->getQueryString()])}}">
                                            {{translate("Excel")}}
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive mt-3">
                        <table class="table table-borderless align-middle table-hover text-nowrap trip-table">
                            <thead class="table-light align-middle text-capitalize">
                            <tr>
                                <th class="sl">{{translate("SL")}}</th>
                                <th>{{translate("Trip ID")}}</th>
                                <th>{{translate("Date")}}</th>
                                <th>{{translate("Zone")}}</th>
                                <th class="text-center">{{translate("Trip Type")}}</th>
                                <th class="text-end">{{translate("Total Trip Cost")}} ({{getSession('currency_symbol')}}) </th>
                                <th class="text-end">{{translate("Trip Discount")}}({{getSession('currency_symbol')}})</th>
                                <th class="text-end">{{translate("Coupon Discount")}}({{getSession('currency_symbol')}})</th>
                                <th class="text-end">{{translate("Expense")}}({{getSession('currency_symbol')}})</th>
                                <th class="text-center">{{translate("Action")}}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($trips as $key => $trip)
                                <tr>
                                    <td>{{$trips->firstItem() + $key}}</td>
                                    <td><a href="{{route('admin.trip.show', ['id' => $trip->id, 'page' => 'summary'])}}">#{{$trip->ref_id}}</a></td>
                                    <td>
                                        {{date('d F Y', strtotime($trip->created_at))}}, <br /> {{date('h:i a', strtotime($trip->created_at))}}
                                    </td>
                                    <td>{{$trip?->zone?->name}}</td>
                                    <td>
                                        @if($trip->type=="parcel")
                                            <div class="text-center">
                                                <span class="badge badge-warning">{{translate($trip->type)}}</span>
                                            </div>
                                            @else
                                            <div class="text-center">
                                                <span class="badge badge-info">{{translate($trip->type)}}</span>
                                                @if($trip->ride_request_type == SCHEDULED)
                                                    <span class="text-info fw-semibold d-block mt-1">{{ translate(SCHEDULED) }}</span>
                                                @endif
                                            </div>
                                        @endif
                                    </td>
                                    <td class="text-end">{{ getCurrencyFormat($trip->paid_fare) }}</td>
                                    <td class="text-end">{{ getCurrencyFormat($trip->discount_amount??0) }}</td>
                                    <td class="text-end">{{ getCurrencyFormat($trip->coupon_amount??0) }}</td>
                                    <td class="text-end">{{ getCurrencyFormat($trip->discount_amount+$trip->coupon_amount) }}</td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-2 align-items-center">
                                            <a target="_blank" href="{{route('admin.report.singleExpenseReportExport',$trip->id)}}" class="btn btn-outline-primary btn-action">
                                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M12.6667 6H10V2H6V6H3.33333L8 11.3333L12.6667 6ZM2.66666 12.6667H13.3333V14H2.66666V12.6667Z" fill="currentColor"/>
                                                </svg>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="14">
                                        <div class="d-flex flex-column justify-content-center align-items-center gap-2 py-3">
                                            <img src="{{ asset('public/assets/admin-module/img/empty-icons/no-data-found.svg') }}" alt="" width="100">
                                            <p class="text-center">{{translate('no_data_available')}}</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="table-bottom d-flex flex-column flex-sm-row justify-content-sm-between align-items-center gap-2">
                        <p class="mb-0"></p>
                        {{$trips->render()}}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script src="{{asset('public/assets/admin-module/plugins/apex/apexcharts.min.js')}}"></script>
    <script>
        "use strict";
        let point = {{(int)getSession('currency_decimal_point') ?? 0}};
        $("#dateRange").on('change', function () {
            let date = $("#dateRange").val();
            dateZoneWiseExpenseStatistics(date)
        })
        function abbreviateNumber(num) {
            if (num >= 1_000_000_000_000) {
                return (num / 1_000_000_000_000).toFixed(point) + 'T';
            } else if (num >= 1_000_000_000) {
                return (num / 1_000_000_000).toFixed(point) + 'B';
            } else if (num >= 1_000_000) {
                return (num / 1_000_000).toFixed(point) + 'M';
            } else if (num >= 1_000) {
                return (num / 1_000).toFixed(point) + 'K';
            } else {
                return num.toString();
            }
        }
        function dateZoneWiseExpenseStatistics(date) {
            $.get({
                url: '{{route('admin.report.dateZoneWiseExpenseStatistics')}}',
                dataType: 'json',
                data: {date: date},
                beforeSend: function () {
                    $('#resource-loader').show();
                },
                success: function (response) {
                    console.log(response)
                    let hours = response.label;
                    // Remove double quotes from each string value
                    hours = hours.map(function (hour) {
                        return hour.replace(/"/g, '');
                    });
                    document.getElementById('apex_line-chart').remove();
                    let graph = document.createElement('div');
                    graph.setAttribute("id", "apex_line-chart");
                    document.getElementById("updating_line_chart").appendChild(graph);
                    let options = {
                        series: [
                            {
                                name: '{{translate("Total Expense")}}($)',
                                data: [0].concat(Object.values(response.totalExpense??0))
                            },
                            {
                                name: '{{translate("Total Trips")}}',
                                data: [0].concat(Object.values(response.totalTripRequest??0))
                            }
                        ],
                        chart: {
                            height: 200,
                            type: 'line',
                            dropShadow: {
                                enabled: true,
                                color: '#000',
                                top: 18,
                                left: 0,
                                blur: 10,
                                opacity: 0.1
                            },
                            toolbar: {
                                show: false
                            },
                        },
                        colors: [ '#14B19E','#F4A164'],
                        dataLabels: {
                            enabled: false,
                        },
                        stroke: {
                            curve: 'smooth',
                            width: 2,
                        },
                        grid: {
                            yaxis: {
                                lines: {
                                    show: true
                                }
                            },
                            borderColor: '#ddd',
                        },
                        markers: {
                            size: 2,
                            strokeColors: [ '#14B19E','#F4A164'],
                            strokeWidth: 1,
                            fillOpacity: 0,
                            hover: {
                                sizeOffset: 2
                            }
                        },
                        theme: {
                            mode: 'light',
                        },
                        xaxis: {
                            categories: ['00'].concat(hours),
                            labels: {
                                offsetX: 0,
                            },
                        },
                        legend: {
                            show: false,
                            position: 'bottom',
                            horizontalAlign: 'left',
                            floating: false,
                            offsetY: -10,
                            itemMargin: {
                                vertical: 10
                            },
                        },
                        yaxis: {
                            tickAmount: 5,
                            labels: {
                                offsetX: 0,
                                formatter: function (value) {
                                    return abbreviateNumber(value);
                                }
                            },
                        }
                    };

                    if (localStorage.getItem('dir') === 'rtl') {
                        options.yaxis.labels.offsetX = -20;
                    }

                    let chart = new ApexCharts(document.querySelector("#apex_line-chart"), options);
                    chart.render();
                },
                complete: function () {
                    $('#resource-loader').hide();
                },
                error: function (xhr, status, error) {
                    let err = eval("(" + xhr.responseText + ")");
                    // alert(err.Message);
                    $('#resource-loader').hide();
                    toastr.error('{{translate('failed_to_load_data')}}')
                },
            });
        }
        dateZoneWiseExpenseStatistics("{{ALL_TIME}}")
        $("#dateRangeForExpenseStatistics").on('change', function () {
            let date = $("#dateRangeForExpenseStatistics").val();
            dateRideTypeWiseExpenseStatistics(date)
        })
        function dateRideTypeWiseExpenseStatistics(date) {
            $.get({
                url: '{{route('admin.report.dateRideTypeWiseExpenseStatistics')}}',
                dataType: 'json',
                data: {date: date},
                beforeSend: function () {
                    $('#resource-loader').show();
                },
                success: function (response) {
                    let parcelExpense = parseFloat(response.totalExpense.parcel);
                    let rideExpense = parseFloat(response.totalExpense.ride_request);
                    let scheduleRide = parseFloat(response.totalExpense.scheduled);
                    $("#parcelExpense").html(parcelExpense.toFixed(point))
                    $("#rideExpense").html(rideExpense.toFixed(point))
                    $("#scheduleRide").html(scheduleRide.toFixed(point))
                    $("#totalExpense").html(abbreviateNumber((parcelExpense+rideExpense).toFixed(point)))
                    let options;
                    let chart;
                    if(parcelExpense > 0 || rideExpense > 0 || scheduleRide > 0) {
                        $('.pie-placeholder').hide()
                        $('.pie-chart-inner').css('opacity', '1');
                    } else {
                        $('.pie-placeholder').show();
                        $('.pie-chart-inner').css('opacity', '0');
                    }
                    options = {
                        series: [parcelExpense, rideExpense, scheduleRide],
                        chart: {
                            width: 200,
                            type: 'donut',
                        },
                        labels: ['{{ translate('Parcel') }}', '{{ translate('Ride Request') }}', '{{ translate('Schedule Ride') }}'],
                        dataLabels: {
                            enabled: false,
                            style: {
                                colors: ['#FFA84A', '#0177CD', '#EF5B5B']
                            }
                        },
                        responsive: [{
                            breakpoint: 1650,
                            options: {
                                chart: {
                                    width: 200
                                },
                            }
                        }],
                        colors: ['#FFA84A', '#0177CD', '#EF5B5B'],
                        fill: {
                            colors: ['#FFA84A', '#0177CD', '#EF5B5B']
                        },
                        stroke:{
                            colors: ['#FFA84A00', '#0177CD00']
                        },
                        legend: {
                            show: false
                        },
                        plotOptions: {
                            pie: {
                                donut: {
                                    size: '80%'
                                }
                            }
                        }
                    };

                    chart = new ApexCharts(document.querySelector("#dognut-pie"), options);
                    chart.render();
                },
                complete: function () {
                    $('#resource-loader').hide();
                },
                error: function (xhr, status, error) {
                    let err = eval("(" + xhr.responseText + ")");
                    // alert(err.Message);
                    $('#resource-loader').hide();
                    toastr.error('{{translate('failed_to_load_data')}}')
                },
            });
        }
        dateRideTypeWiseExpenseStatistics("{{ALL_TIME}}")
    </script>
@endpush
