@extends('layouts.app')
@section('content')
<style type="text/css">
    body{overflow-y: hidden;}
    #userApplicationsTable{width: 100%!important; }
    .table td, .table th{padding: 10px 0px;}
</style>
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
                                            <h6 class="m-b-20">Saving Accounts</h6>
                                        </div>
                                        <div class="card-block-count">
                                            <h2 class="count">{{$accountsCount['savings']}}</h2>
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
                                            <h6 class="m-b-20">Term Deposits</h6>
                                        </div>
                                        <div class="card-block-count">
                                            <h2 class="count">{{$accountsCount['termDeposit']}}</h2>
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
                                            <h6 class="m-b-20">Current Accounts</h6>
                                        </div>
                                        <div class="card-block-count">
                                            <h2 class="count">{{$accountsCount['current']}}</h2>
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
                    <div class="row">
                        <div class="col-md-12 filter-icon-main">
                            <a class="filter-icon"><i class="fa fa-filter"></i> Filters</a>
                            <a class="filter-close" style="display: none;"><i class="fa fa-times"></i> Close Filters</a>
                        </div>
                    </div>

                    <div class="row filter mb-3 drop-down-top filtergrid" style="display: none;">
                        <div class="col-md-3">
                            {!! Form::select('customer name',$customerNames,null,array('class'=>'form-control customerName',
                                  'id'=>'customerName','name'=>'customerName','placeholder'=>'Select Customer Name')) !!}
                        </div>
                        <div class="col-md-3">
                            <div class="with-icon">
                                <input type="text" class="form-control date-input" placeholder="Sent Date To" id="sentDate">
                                <i class="fa fa-times date-clear" aria-hidden="true" id="clear-dates"></i>
                            </div>
                        </div>
                        <div class="col-md-3">
                            {!! Form::select('application Status',$applicationStatus,null,array('class'=>'form-control applicationStatus',
                                  'id'=>'applicationStatus','name'=>'applicationStatus','placeholder'=>'Select Application Status')) !!}
                        </div>
                        <div class="col-md-3">
                            <div class="switch-toggle switch-3 switch-candy" id="customerType">
                                <input id="on" name="customer_type" class="customer-type" type="radio" checked="" value="1">
                                <label for="on" onclick="">ETB</label>
                            
                                <input id="na" name="customer_type" class="customer-type" type="radio" checked="checked" value="all">
                                <label for="na" onclick="">ALL</label>
                            
                                <input id="off" name="customer_type" class="customer-type" type="radio" value="2">
                                <label for="off" onclick="">NTB</label>
                                <a></a>
                            </div>
                        </div>
                    </div>
                    <div class="card table-top">                                            
                        <div class="card-block table-border-style card-block-padding">
                            <div class="table-responsive">
                                <table class="table table-custom" id="userApplicationsTable">
                                    <thead>
                                        <tr>
                                            <th>AOF Number</th>
                                            <th>Name</th>
                                            <th>Account Type</th>
                                            <th>Sent On</th>
                                            <th>Status</th>
                                            <th>NTB / ETB</th>
                                            <th>Sourcing Staff</th>
                                            <th>Action</th>             
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
<script  src="{{ asset('custom/js/bank.js') }}"></script>

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
});
</script>

<script type="text/javascript">
    $(document).ready(function(){
        addSelect2('customerName','Customer Name');
        addSelect2('applicationStatus','Application Status');
        var tableRemainingHeight = $(".header-navbar").height()+$(".accountsgrid").height()/*+$(".filtergrid").height()*/+190;
        $('#sentDate').dateRangePicker({
            startOfWeek: 'monday',
            separator : ' to ',
            format: 'DD-MM-YYYY',
            autoClose: true,
        }).bind('datepicker-change',function(event,obj){
            getUserApplications('/bank/userapplications','userApplicationsTable',tableRemainingHeight);
        });
        $("body").on("change",".customerName,.applicationStatus",function(){
            getUserApplications('/bank/userapplications','userApplicationsTable',tableRemainingHeight);
        });
        getUserApplications('/bank/userapplications','userApplicationsTable',tableRemainingHeight);

        $('body').on('click','#clear-dates',function () {
            $('.date-input').val('');
            getUserApplications('/bank/userapplications','userApplicationsTable',tableRemainingHeight);
        });
    });
</script>
@endpush