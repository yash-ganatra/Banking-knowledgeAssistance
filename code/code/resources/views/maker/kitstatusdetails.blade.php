@extends('layouts.app')
@section('content')
<link rel="stylesheet" type="text/css" href="{{ asset('custom/css/maker-style.css') }}">
    <div class="pcoded-content1">
        <div class="pcoded-inner-content1">
            <!-- Main-body start -->
            <div class="main-body">
                <div class="page-wrapper">
                    <!-- Page-body start -->
                    <div class="page-body page-body-top">
                        <div class="row accountsgrid top-blcks">
                            <!-- order-card start -->
                            <div class="col-md-4 col-xl-4">
                                <div class="card">
                                    <div class="card-block bdr-l-bluec card-blue">
                                        <div class="card-block-inn d-flex align-items-center">
                                            <div class="card-block-img">
                                                <img src="{{ asset('assets/images/saving-acoount-icon.svg') }}">
                                            </div>
                                            <div class="card-block-con">
                                                <h6 class="m-b-20">Available Sales</h6>
                                            </div>
                                            <div class="card-block-count">
                                                <h2 class="count">{{$availableSalesCount}}</h2>
                                            </div>
                                            <div class="circle-img">
                                                <img src="{{ asset('assets/images/circle-img.png') }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4 col-xl-4">
                                <div class="card">
                                    <div class="card-block bdr-l-green card-green">
                                        <div class="card-block-inn d-flex align-items-center">
                                            <div class="card-block-img">
                                                <img src="{{ asset('assets/images/term-deposits-icon.svg') }}">
                                            </div>
                                            <div class="card-block-con">
                                                <h6 class="m-b-20">Available Branch</h6>
                                            </div>
                                            <div class="card-block-count">
                                                <h2 class="count">{{$availableBranchCount}}</h2>
                                            </div>
                                            <div class="circle-img">
                                                <img src="{{ asset('assets/images/circle-img.png') }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4 col-xl-4">
                                <div class="card">
                                    <div class="card-block bdr-l-orange card-orange">
                                        <div class="card-block-inn d-flex align-items-center">
                                            <div class="card-block-img">
                                                <img src="{{ asset('assets/images/current-acoount-icon.svg') }}">
                                            </div>
                                            <div class="card-block-con">
                                                <h6 class="m-b-20">Utilized</h6>
                                            </div>
                                            <div class="card-block-count">
                                                <h2 class="count">{{$utilizedCount}}</h2>
                                            </div>
                                            <div class="circle-img">
                                                <img src="{{ asset('assets/images/circle-img.png') }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- order-card end -->
                        </div>
                        <hr>
                        <div class="accordion" id="delight-dashboard-accordion-auto">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <input type="hidden" id="schemeCode" value="{{$schemeCode.'-'.$schemeDetails[$schemeCode]}}">
                                        <button class="btn btn-link schema-button" type="button">
                                            {{$schemeCode}} - {{$schemeDetails[$schemeCode]}}
                                            <span class="kit-status-count">
                                                <label class="badge schema badge-sm badge-default">{{$delightKitStatusDetails['totalkitCount']}}</label>
                                            </span>
                                        </button>
                                    </h5>
                                </div>

                                <div class="card-body">
                                    @foreach($delightKitStatusDetails['kitCount'] as $status => $statusCount)
                                        <div class="well well-sm">
                                            <span id="kitStatus-{{$status}}" class="kit-status">
                                                {{$status}}
                                            </span>
                                            <span class="kit-status-count">
                                                @if($status == 'RECEIVED BY BRANCH')
                                                    @if($statusCount < 10)
                                                        <label class="badge badge-sm badge-danger badge-branch">{{$statusCount}}</label>
                                                    @else
                                                        <label class="badge badge-sm badge-success">{{$statusCount}}</label>
                                                    @endif
                                                @elseif($status == 'UTILIZED')
                                                    <label class="badge badge-sm badge-success">{{$statusCount}}</label>
                                                @elseif($status == 'MISSING' || $status ==  'DAMAGED')
                                                    <label class="badge badge-sm badge-danger">{{$statusCount}}</label>
                                                @else
                                                    <label class="badge badge-sm badge-primary">{{$statusCount}}</label>
                                                @endif
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <!-- Page-body end -->
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script  src="{{ asset('custom/js/maker.js') }}"></script>
@endpush
