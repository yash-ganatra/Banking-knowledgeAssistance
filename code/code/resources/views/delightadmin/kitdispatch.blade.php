@extends('layouts.app')
@section('content')
<link rel="stylesheet" type="text/css" href="{{ asset('custom/css/delightadmin.css') }}">
<style type="text/css">
    body{overflow-y: hidden;}
    #kitDetailsTable{width: 100%!important; }
    .table td, .table th{padding: 10px 0px;}
</style>
<div id="kit-detail-table-div">
    <div class="pcoded-inner-content1">
        <!-- Main-body start -->
        <div class="main-body">
            <div class="page-wrapper">
                <!-- Page-body start -->
                <div class="page-body page-body-top">
                    
                    <input type="hidden" name="dr_no" id="dr_no" value="{{$dr_no}}">
                    <div class="row filter mb-3 drop-down-top filtergrid">
                        <div class="col-md-3">
                            <input type="text" name="kitNumber" class="form-control" placeholder="Kit Number" id="kitNumber">
                        </div>
                        <div class="col-md-3">
                            <input type="text" name="customerId" class="form-control" placeholder="Customer ID" id="customerId">
                        </div>
                        <div class="col-md-3">
                            <input type="text" name="accountNumber" class="form-control" placeholder="Account Number" id="accountNumber">
                        </div>
                        <div class="col-md-3">
                            <button type="button" class="btn btn-primary" id="kitDispatch">Dispatch</button>
                        </div>
                    </div>

                    <div class="card table-top">
                        <div class="card-block table-border-style card-block-padding">
                            <div class="table-responsive">
                                <table class="table table-custom" id="kitDetailsTable">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>SOL ID</th>
                                            <th>Schema Code</th>
                                            <th>Kit Number</th>
                                            <th>Customer Id</th>
                                            <th>Account Number</th>
                                            <th class="select_all_checkbox_th" data-orderable="false">
                                               <span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                    <input type="checkbox" class="select_all_checkbox " name="select_all_checkbox">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                </span>
                                            </th>
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

    var tableRemainingHeight = $(".header-navbar").height()+$("#menu").height()+200;
    getKitDispatchTable('/delightadmin/kitdispatchtable','kitDetailsTable',tableRemainingHeight);

    $("body").on("keyup","#kitNumber, #customerId, #accountNumber",function(){
        getKitDispatchTable('/delightadmin/kitdispatchtable','kitDetailsTable',tableRemainingHeight);
    });

    

});
</script>
@endpush
