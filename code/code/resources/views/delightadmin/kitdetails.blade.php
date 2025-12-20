@extends('layouts.app')
@section('content')
<link rel="stylesheet" type="text/css" href="{{ asset('custom/css/maker-style.css') }}">
<style type="text/css">
    #kitDetailsTable{width: 100%!important; }
    .export-excel{float: right;margin-right: 1%;color:green;cursor: pointer;margin-top: 12px;}
</style>
<div id="kit-detail-table-div">
    <div class="pcoded-inner-content1">
        <!-- Main-body start -->
        <div class="main-body">
            <div class="page-wrapper">
                <!-- Page-body start -->
                <div class="page-body page-body-top">
                    <div class="row filter mb-3 drop-down-top filtergrid">
                         <div id="export-excel" class="col-md-1">
                            <i class="fa fa-file-excel-o fa-1x export-excel" aria-hidden="true" data-toggle="tooltip" id="export-excel" data-placement="top" title="Export Kit Details"><span class="export ml-2">Export Excel</span></i>
                       </div>

                        <div class="col-md-2">
                            {!! Form::select('delightSchemeCodes', $delightSchemeCodes, $schemeCodeId, array('class'=>'form-control delightSchemeCodes',
                                    'id'=>'delightSchemeCode','name'=>'delightSchemeCode','placeholder'=>'Select Delight Scheme Codes')) !!}
                        </div>

                        <div class="col-md-1">
                            <input type="text" name="branchID" class="form-control" placeholder="Branch ID" id="branchID" value="{{$branchId}}">
                        </div>
                        <div class="col-md-2">
                            <input type="text" name="kitNumber" class="form-control" placeholder="Kit No." id="kitNumber">
                        </div>
                        <div class="col-md-1">
                            <input type="text" name="customerID" class="form-control" placeholder="Cust ID" id="customerID">
                        </div>
                        <div class="col-md-1">
                            <input type="text" name="accountID" class="form-control" placeholder="Accnt No." id="accountID">
                        </div>
                        <div class="col-md-2">
                            {!! Form::select('delightKitStatus',$delightKitStatus,$kitStatusId,array('class'=>'form-control delightKitStatus',
                                        'id'=>'delightKitStatus','name'=>'delightKitStatus','placeholder'=>'Delight Kit Status')) !!}
                        </div>
                        <div class="col-md-2">
                            <div class="with-icon">
                                <input type="text" class="form-control date-input" placeholder="Date range" id="sentDate">
                                <i class="fa fa-times date-clear" aria-hidden="true" id="clear-dates"></i>
                            </div>

                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-primary display-none" id="updateKitStatus">
                                Update Status
                            </button>
                        </div>
                    </div>
                    <div class="card table-top">
                        <div class="card-block table-border-style card-block-padding">
                            <div class="table-responsive">
                                <table class="table table-custom" id="kitDetailsTable">
                                    <thead>
                                        <tr>
                                            <th>Request ID</th>
                                            <th>Branch ID</th>
                                            <th>Scheme Code</th>
                                            <th>Kit Number</th>
                                            <th>Customer ID</th>
                                            <th>Account Number</th>
                                            <th>Created At</th>
                                            <th>Status</th>
                                            <!--<th class="select_all_checkbox_th" data-orderable="false">
                                                <span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" class="select_all_checkbox " name="select_all_checkbox">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
                                            </th>-->
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
<script  src="{{ asset('custom/js/maker.js') }}"></script>
<script>
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
    $(".filter-icon").click();

    $('#export-excel').on("click",function(e){
        e.preventDefault();
        $.growl({message: "Generating Excel file..."},{type: "success",delay:7800});
        $("#kitDetailsTable").DataTable().page.len( -1 ).draw();
        if($("#kitDetailsTable").DataTable().page.len() == -1){
            setTimeout(function(){
                $("#kitDetailsTable").DataTable().button('0').trigger();
                $.growl({message: "Excel file Generated"},{type: "success"});
            },8000);
        }
        });


    addSelect2('delightSchemeCodes','Delight Scheme Code');
    addSelect2('delightKitStatus','Delight Kit Status');
    var tableRemainingHeight = $(".header-navbar").height()+$(".filtergrid").height()+210;
    getDKitTable('/delightadmin/kitdetailstable','kitDetailsTable',tableRemainingHeight);
    $('#sentDate').dateRangePicker({
        startOfWeek: 'monday',
        separator : ' to ',
        format: 'DD-MM-YYYY',
        autoClose: true,
    }).bind('datepicker-change',function(event,obj){
        getDKitTable('/delightadmin/kitdetailstable','kitDetailsTable',tableRemainingHeight);
    });

    $("body").on("change",".delightSchemeCodes, .delightKitStatus",function(){
        getDKitTable('/delightadmin/kitdetailstable','kitDetailsTable',tableRemainingHeight);
    });

    $("body").on("keyup","#branchID, #kitNumber, #customerID, #accountID",function(){
        getDKitTable('/delightadmin/kitdetailstable','kitDetailsTable',tableRemainingHeight);
    });

    $('body').on('click','#clear-dates',function () {
        $('.date-input').val('');
        getDKitTable('/delightadmin/kitdetailstable','kitDetailsTable',tableRemainingHeight);
    });
});
</script>
@endpush
