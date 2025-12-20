@extends('layouts.app')
@section('content')
@php
    $defaultIndent = config('constants.INDENT_THRESHOLD.DEFAULT');
    $specialIndent = config('constants.INDENT_THRESHOLD.SPECIAL');
    $specialScheme = config('constants.INDENT_THRESHOLD.SPECIAL_SCHEME');
@endphp
    <link rel="stylesheet" type="text/css" href="{{ asset('custom/css/maker-style.css') }}">
    <div class="pcoded-content1">
        <div class="pcoded-inner-content1">
            <!-- Main-body start -->
            <div class="main-body">
                <div class="page-wrapper">
                    <!-- Page-body start -->
                    <div class="page-body page-body-top ">
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
                        <div class="table-responsive" >
                            <table class="table table-striped table-hover indent-table " style="overflow-x:auto;">
                                <thead class="indent-table-head">
                                    <tr>
                                        <th scope="col">Sr No.</th>
                                        <th scope="col">SCHEME</th>
                                        <th scope="col">SCHEME DECRIPTION</th>
                                        <th scope="col">AVAILABLE SALES</th>
                                        <th scope="col">AVAILABLE BRANCH</th>
                                        <th scope="col">TOTAL AVAILABLE</th>
                                        <th scope="col">ACTION</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($delightKitsDetails as $delightKit)
                                        <tr>
                                            <td scope="row">{{$delightKit['id']}}</td>
                                            <td>
                                                <a href="javascript:void(0)" class="kitDetails" id="{{$delightKit['schemeCode']}}">
                                                    {{$delightKit['schemeCode']}}
                                                </a>
                                            </td>
                                            <td scope="row">{{$delightKit['description']}}</td>
                                            <td scope="row">{{$delightKit['availableSalesCount']}}</td>
                                            <td scope="row">{{$delightKit['availableBranchCount']}}</td>
                                            @if($delightKit['availableBranchCount'] <= 10)
                                                <td class="available" style="color: darkred;">{{$delightKit['totalAvailable']}}</td>
                                            @elseif(in_array($delightKit['schemeCode'], $specialScheme) && ($delightKit['availableBranchCount'] < $specialIndent))
                                                <td class="available" style="color: darkorange;">{{$delightKit['totalAvailable']}}</td>
                                            @elseif(!in_array($delightKit['schemeCode'], $specialScheme) && ($delightKit['availableBranchCount'] < $defaultIndent) )
                                                <td class="available" style="color: darkorange;">{{$delightKit['totalAvailable']}}</td>
                                            @else
                                                <td class="available">{{$delightKit['totalAvailable']}}</td>
                                            @endif
                                            <td>
                                                
                                                @if(isset($delightKit['dispatchedKits']) && ($delightKit['dispatchedKits'] > 0))
                                                    <button type="button" class="btn btn-outline-success open-indent-modal disabled" id="addIndent" disabled="">
                                                        Dispatched&nbsp;<span>({{$delightKit['dispatchedKits']}})</span>
                                                    </button>
                                                @elseif(isset($delightKit['requestedKits']) && ($delightKit['requestedKits'] > 0))
                                                    <button type="button" class="btn btn-outline-success open-indent-modal disabled" id="addIndent" disabled="">
                                                        Requested&nbsp;<span>({{$delightKit['requestedKits']}})</span>
                                                    </button>
                                                @elseif(in_array($delightKit['schemeCode'], $specialScheme) && ($delightKit['availableBranchCount'] < $specialIndent))
                                                    <button type="button" class="btn btn-outline-success open-indent-modal" id="addIndent">Indent</button>
                                                @elseif(!in_array($delightKit['schemeCode'], $specialScheme) && ($delightKit['availableBranchCount'] < $defaultIndent) )
                                                    <button type="button" class="btn btn-outline-success open-indent-modal" id="addIndent">Indent</button>
                                                @else
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <!-- Page-body end -->
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="addIndentModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="indent-modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">NEW INDENT FOR SCHEME:
                        <span class="schema-code" id="schemeCode"></span>
                    </h5>
                </div>
                <div class="modal-body indent-modal-body">
                    <form>
                        <div class="form-group row">
                            <label for="indent-available" class="indent-modal-label col-sm-6">Available
                                <span class="indent-colon" >:</span>
                            </label>
                            <div class="col-sm-6">
                                <span id="kitAvailableCount"></span>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="kitRequestCount" class="indent-modal-label col-sm-6">Kit Request Count
                                <span class="indent-colon">:</span>
                            </label>
                            <div class="col-sm-6">
                                <input type="number" id="kitRequestCount" class="form-control" name="kitRequestCount" min="{{config('constants.DELIGHT_KIT_REQUEST_THRESHOLD.DEFAULT_MIN')}}" max="{{config('constants.DELIGHT_KIT_REQUEST_THRESHOLD.DEFAULT_MAX')}}" step="5" value="{{config('constants.DELIGHT_KIT_REQUEST_THRESHOLD.DEFAULT_MIN')}}" oninput="this.value = this.value.replace(/[^0-9]/gi, '').replace(/(\..*)\./g, '$1');">
                            </div>
                        </div>
                    </form>

                </div>
                <div class="modal-footer indent-modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveIndent">Indent</button>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script  src="{{ asset('custom/js/maker.js') }}"></script>
    <script type="text/javascript">
    _configKitRequestThreshold = JSON.parse('<?php echo json_encode($configKitRequestThreshold); ?>');
    </script>
@endpush
