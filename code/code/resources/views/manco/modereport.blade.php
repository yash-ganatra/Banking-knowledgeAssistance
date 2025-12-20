@extends('layouts.app')
@section('content')
<style type="text/css">
    body{overflow-y: hidden;}
    #modeReportTable{width: 100%!important; }
    .table td, .table th{padding: 10px 0px;}
    .export-excel{float: right;margin-right: 1%;color:green;cursor: pointer;}
    .export{font-size: 13px;
    font-family: Arial;color:#364FCC}
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
                            
                            <i class="fa fa-file-excel-o fa-1x export-excel" aria-hidden="true" data-toggle="tooltip" id="export-excel" data-placement="top" title="Export Data"><span class="export ml-2">Export Excel</span></i>
                            <!-- <a class="btn btn-link export-excel" >Export</a> -->
                        </div>
                    </div>

                    <div class="row filter mb-3 drop-down-top filtergrid" style="display: none;">
                        <div class="col-md-3">
                            <input type="text" class="form-control" name="aofnumber" id="aofnumber" placeholder="AOF Number">
                        </div>
                        <div class="col-md-3">
                            @php
                                $customerTypes = ['ETB','NTB','DELIGHT'];
                            @endphp
                            {!! Form::select('customer Types',$customerTypes,null,array('class'=>'form-control customerTypes',
                              'id'=>'customerType','name'=>'customerTypes','placeholder'=>'Select Customer Type')) !!}
                        </div>
                        <div class="col-md-3">
                            <div class="with-icon">
                                <input type="text" class="form-control date-input" placeholder="Submission Date" id="sentDate">
                                <i class="fa fa-times date-clear" aria-hidden="true" id="clear-dates"></i>
                            </div>
                        </div>
                    </div>
                    <div class="card table-top">                                            
                        <div class="card-block table-border-style card-block-padding">
                            <div class="table-responsive">
                                <table class="table table-custom" id="modeReportTable">
                                    <thead>
                                        <tr>
                                            <th>Branch ID</th>
                                            <th>AOF Number</th>
                                            <th>Account Type</th>
                                            <th>Scheme Code</th>
                                            <th>NTB / ETB / DELIGHT</th>
                                            <th>EKYC</th>
                                            <th>Submission Date</th>             
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
<script  src="{{ asset('custom/js/management.js') }}"></script>
<script  src="{{ asset('custom/js/app.js') }}"></script>

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

  //fetch show entries count
            //var datatableLength = $('#modeReportTable_length select').val();
            $('#export-excel').on("click",function(e){
                //$("#export-xls").addClass("display-none");
                e.preventDefault();

                $.growl({message: "Generating Excel file..."},{type: "success",delay:7800});

                $("#modeReportTable").DataTable().page.len( -1 ).draw();

                if($("#modeReportTable").DataTable().page.len() == -1){
                    setTimeout(function(){
                        $("#modeReportTable").DataTable().button('0').trigger();
                        $.growl({message: "Excel file Generated"},{type: "success"});
                        //$("#export-xls").removeClass("display-none");
                    },8000);
                }
                //to revert the show entries count
                // setTimeout(function(){
                //     $("#"+tableObject.data['table']).DataTable().page.len(datatableLength).draw();
                // },600);
            });
});
</script>

<script type="text/javascript">
    $(document).ready(function(){
        addSelect2('applicationStatus','Application Status');
        addSelect2('customerTypes','Customer Type');
        var navbarHeight = $(".header-navbar").height();
          var filterHeight = $(".filtergrid").height();
          var paginationHeight = 170;
          if(isNaN(navbarHeight)) navbarHeight = 25;
          if(isNaN(filterHeight)) filterHeight = 25;

        var tableRemainingHeight = navbarHeight+filterHeight+paginationHeight;

        var startDate = new Date();
    var day = startDate.getDate() - 90;
    var startDate = new Date(startDate.setDate(day));
        $('#sentDate').dateRangePicker({
            startOfWeek: 'monday',
            separator : ' to ',
            format: 'DD-MM-YYYY',
            autoClose: true,
            startDate: startDate,
            endDate: "today",
        }).bind('datepicker-change',function(event,obj){
            getModeReport('/management/getmodereport','modeReportTable',tableRemainingHeight);
        });
        $("body").on("keyup","#aofnumber",function(){
            getModeReport('/management/getmodereport','modeReportTable',tableRemainingHeight);
        });
        $("body").on("change",".applicationStatus, #customerType",function(){
            getModeReport('/management/getmodereport','modeReportTable',tableRemainingHeight);
        });
        getModeReport('/management/getmodereport','modeReportTable',tableRemainingHeight);

        $('body').on('click','#clear-dates',function () {
            $('.date-input').val('');
            getModeReport('/management/getmodereport','modeReportTable',tableRemainingHeight);
        });

        setTimeout(function(){
            $.growl({message: "Last One Month Records"},{type: "success"});
        },2000);
        
    });
</script>
@endpush