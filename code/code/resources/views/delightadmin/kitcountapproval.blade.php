@extends('layouts.app')
@section('content')
<link rel="stylesheet" type="text/css" href="{{ asset('custom/css/delightadmin.css') }}">
<style type="text/css">
    #kitCountApprovalTable{width: 100%!important; }
</style>
<div id="kit-detail-table-div">
    <div class="pcoded-inner-content1">
        <!-- Main-body start -->
        <div class="main-body">
            <div class="page-wrapper">
                <!-- Page-body start -->
                <div class="page-body page-body-top">
                    <div class="row filter mb-3 drop-down-top filtergrid" >
                        <div class="col-md-2">
                            <input type="text" name="SolId" class="form-control" placeholder="Sol ID" id="SolId">
                        </div>

                        <div class="col-md-3">
                            {!! Form::select('delightSchemeCode', $delightSchemeCodes, $schemeCodeId, array('class'=>'form-control delightSchemeCode',
                                    'id'=>'delightSchemeCode','name'=>'delightSchemeCode','placeholder'=>'Select Delight Scheme Codes')) !!}
                        </div>

                        <div class="col-md-3">
                            {!! Form::select('drStatus', $drStatusList, $drStatusId, array('class'=>'form-control drStatus',
                                    'id'=>'drStatus','name'=>'delightSchemeCode','placeholder'=>'Select DR Status')) !!}
                        </div>

                        <div class="col-md-2">
                            <div class="with-icon">
                                <input type="text" class="form-control date-input" placeholder="Date range" id="sentDate">
                                <i class="fa fa-times date-clear" aria-hidden="true" id="clear-dates"></i>
                            </div>
                        </div>
                        @if(Session::get('role') == 16)
                            <div class="col-md-2">
                                <button type="button" class="btn btn-primary" id="CheckGenerated"><span><i class="fa fa-refresh"></i></span>Check Generated Kits</button>
                            </div>
                        @endif
                    </div>

{{--                     <div class="row">
                        <div class="col-md-4 mb-2">
                            <button class="btn btn-outline-success kit-approvals-button display-none" id="multiKitApproval">approve selected</button>
                        </div>
                    </div> --}}

                    <div class="card table-top">
                        <div class="card-block table-border-style card-block-padding">
                            <div class="table-responsive">
                                <table class="table table-custom" id="kitCountApprovalTable">
                                    <thead>
                                        <tr>
                                            {{-- <th class="select-all-checkbox" data-orderable="false">
                                                <input type="checkbox" class="approval_checkbox_all" id="request_checkbox_all" name="request-checkbox_all">
                                            </th> --}}
                                            <th>DR_ID</th>
                                            <th>SOL ID</th>
                                            <th>SCHEME CODE</th>
                                            <th>REQUESTED COUNT</th>
                                            <th>REQUESTED DATE</th>
                                            <th>DR STATUS</th>
                                            <th>ACTION</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Page-body end -->
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script  src="{{ asset('custom/js/delightadmin.js') }}"></script>
<script type="text/ecmascript">
$(document).ready(function(){
    $(".filter-icon").click(function(){
        $(".filtergrid").show();
        $(".filter-icon").hide();
        $(".filter-close").show();
    });

    $(".filter-close").click(function(){
        $(".filtergrid").hide();
        $(".filter-close").hide();
        $(".filter-icon").show();
    });

    addSelect2('delightSchemeCode','Delight Scheme Code');
    addSelect2('drStatus','DR Status');
    addSelect2('delightKitStatus','Delight Kit Status');

    // var tableRemainingHeight = $(".header-navbar").height()+$("#menu").height()+200;
    var tableRemainingHeight = $(".header-navbar").height()+$(".filtergrid").height()+70;
    getKitCountApprovalTable('/delightadmin/kitcountapprovaltable','kitCountApprovalTable',tableRemainingHeight);

    $('#sentDate').dateRangePicker({
        startOfWeek: 'monday',
        separator : ' to ',
        format: 'DD-MM-YYYY',
        autoClose: true,
    }).bind('datepicker-change',function(event,obj){
       getKitCountApprovalTable('/delightadmin/kitcountapprovaltable','kitCountApprovalTable',tableRemainingHeight);
    });

    $("body").on("keyup","#SolId",function(){
        getKitCountApprovalTable('/delightadmin/kitcountapprovaltable','kitCountApprovalTable',tableRemainingHeight);
    });

    $("body").on("change","#delightSchemeCode, #drStatus",function(){
        getKitCountApprovalTable('/delightadmin/kitcountapprovaltable','kitCountApprovalTable',tableRemainingHeight);
    });

    $('body').on('click','#clear-dates',function () {
        $('.date-input').val('');
        getKitCountApprovalTable('/delightadmin/kitcountapprovaltable','kitCountApprovalTable',tableRemainingHeight);
    });
});
</script>
@endpush
