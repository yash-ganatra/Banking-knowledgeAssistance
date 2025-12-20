@extends('layouts.app')
@section('content')
<style type="text/css">
    body{overflow-y: hidden;}
    #callCenteruserApplicationsTable{width: 100%!important; }
    .table td, .table th{padding: 10px 0px;text-align: center;}
</style>
<div class="pcoded-content1">
    <div class="pcoded-inner-content1">
        <!-- Main-body start -->
        <div class="main-body">
            <div class="page-wrapper">
                <!-- Page-body start -->
                <div class="page-body page-body-top">
                  
                    <div class="row">
                        <div class="col-md-12 filter-icon-main">
                            <a class="filter-icon"><i class="fa fa-filter"></i> Filters</a>
                            <a class="filter-close" style="display: none;"><i class="fa fa-times"></i> Close Filters</a>
                        </div>
                    </div>

                    <div class="row filter mb-3 drop-down-top filtergrid" style="display: none;">
                        <div class="col-md-3">
                        <input type="text" name="aofNumber" class="form-control" placeholder=" Aof Number " id="aofNumber">
                        </div>
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
                    </div>
                    <div class="card table-top">                                            
                        <div class="card-block table-border-style card-block-padding">
                            <div class="table-responsive">
                                <table class="table table-custom" id="callCenteruserApplicationsTable">
                                    <thead>
                                        <tr>
                                            <th>AOF Number</th>
                                            <th>Name</th>
                                            <th>TYPE</th>
                                            <th>Account Type</th>
                                            <th>Sent On</th>
                                            <th>Status</th>
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

<script type="text/javascript">
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
        addSelect2('aofnumber','aofNumber')
        addSelect2('customerName','Customer Name');
        addSelect2('customerTypes','Customer Type');

          var navbarHeight = $(".header-navbar").height();
          var filterHeight = $(".filtergrid").height();
          var paginationHeight = 200;
          if(isNaN(navbarHeight)) navbarHeight = 25;
          if(isNaN(filterHeight)) filterHeight = 25;

        var tableRemainingHeight = navbarHeight+filterHeight+paginationHeight;
        
        $('#sentDate').dateRangePicker({
            startOfWeek: 'monday',
            separator : ' to ',
            format: 'DD-MM-YYYY',
            autoClose: true,
        }).bind('datepicker-change',function(event,obj){
            getCallCenterUserApplications('/callcenter/callcenteruserapplications','callCenteruserApplicationsTable',tableRemainingHeight);
        });
        $("body").on("keyup"," #aofNumber",function(){
            getCallCenterUserApplications('/callcenter/callcenteruserapplications','callCenteruserApplicationsTable',tableRemainingHeight);
        });
        $("body").on("change",".customerName,#customerType",function(){
            getCallCenterUserApplications('/callcenter/callcenteruserapplications','callCenteruserApplicationsTable',tableRemainingHeight);
        });
        getCallCenterUserApplications('/callcenter/callcenteruserapplications','callCenteruserApplicationsTable',tableRemainingHeight);

        $('body').on('click','#clear-dates',function () {
            $('.date-input').val('');
            getCallCenterUserApplications('/callcenter/callcenteruserapplications','callCenteruserApplicationsTable',tableRemainingHeight);
        });
    });
</script>
@endpush