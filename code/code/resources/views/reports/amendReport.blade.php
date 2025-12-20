@extends('layouts.app')
@section('content')
<style type="text/css">
    body{overflow-x: hidden;}
    #amendReport{width: 100%!important;}
    .table th{background-color: #364FCC!important;}
    .table td, .table th{padding: 10px; word-wrap:break-word;}
    .sticky-header{background-color: #364FCC!important; color: white!important; position:sticky!important; top:0!important;}
    /*.table-responsive{overflow-x: initial!important;}*/
    .export-excel{float: right;margin-right: 1%;color:green;cursor: pointer;}
    .export{font-size: 13px;font-family: Arial;color:#364FCC}
    .month-wrapper{width: 420px!important;}
</style>
<div class="pcoded-content1">
    <div class="pcoded-inner-content1">
        <!-- Main-body start -->
        <div class="main-body">
            <div class="page-wrapper">
                <!-- Page-body start -->
                <div class="page-body page-body-top">
                    <div class="row">
                        <div class="col-8 d-flex">
                            <span style="margin-top:10px;">Start Date </span> &nbsp;
                            <div class="with-icon">
                                <input type="text" class="form-control startDate" id="startDate" name="startDate">
                                <i class="fa fa-times date-clear" aria-hidden="true" id="clear-dates"></i>
                            </div>
                        </div>
                        <div class="col-4 filter-icon-main" id="excelrowid">
                            <i class="fa fa-file-excel-o fa-1x export-excel" aria-hidden="true" data-toggle="tooltip" id="amedexport-excel" data-placement="top" title="Export Data"><span class="export ml-2">Export Excel</span></i>
                        </div>
                    </div>
                    <!-- <div class="row excelrow display-none" id="excelrowid"> -->
                    <!-- </div> -->
                    <div class="card table-top mt-5 amendreportData display-none" id="amendreportDataid">                              
                        <div class="card-block table-border-style card-block-padding">
                            <div class="table-responsive">
                                <table class="table table-custom" id="amendReport" >
                                    
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
<script  src="{{ asset('custom/js/npc.js') }}"></script>
<script  src="{{ asset('custom/js/app.js') }}"></script>

<script>
$(document).ready(function(){
    
    $('#startDate').datepicker({
         clearBtn: true,
        format: "dd-mm-yyyy",
        endDate: "today",
        maxDate: "today",    
    }).on('change', function () {
       amendReports();
    });

    $('#startDate').datepicker("setDate",'now');

    $('body').on('click','#clear-dates',function () {
        $('.startDate').val('');
        amendReports();
    });

    $('#amedexport-excel').on('click',function(){
        var tableExcelExport =  document.getElementById('exportTableAmend');
        var rowCtn =  tableExcelExport.rows.length;
        var rowData = tableExcelExport.getElementsByTagName('tr');
        var newArr = [];
        var demoTs = '';

        if(typeof(tableExcelExport) == 'null'){
            return false;
        }

        for(var i=0;rowCtn>i;i++){
            demoTs += rowData[i].outerText.replaceAll("\t",",");
            demoTs += '\n';
        }
        newArr.push(demoTs);
        
        CSVFile = new Blob([newArr], { type: "text/csv" });
        var tmp_link = document.createElement('a');
        tmp_link.download = "AmendServiceRequest.csv";
        var url = window.URL.createObjectURL(CSVFile);
        tmp_link.href = url;
        tmp_link.style.display = "none";
        document.body.appendChild(tmp_link);
        tmp_link.click();

    });
});

function amendReports(){
    if(typeof($('#startDate').val()) != 'undefined'){
        var sentDateRange = $('#startDate').val();
    }

    var amendReportObj = [];
    amendReportObj.data = {};
    amendReportObj['url'] = '/amendnpc/getservicerequestdata'

    if(typeof($('#startDate').val()) != 'undefined'){
        amendReportObj.data['startDate'] = $('#startDate').val();

    }
    amendReportObj.data['functionName'] = 'amendReportCallBack';

    crudAjaxCall(amendReportObj);
    return false;
}

function amendReportCallBackFunction(response,object){
        if(response['status'] == "success"){
        $('.amendreportData').removeClass('display-none');
        $('.excelrow').removeClass('display-none');
        $('#amendReport').html(response.data);
        $.growl({message: response['msg']},{type: response['status'],allow_dismiss:false});
    }else{
        if(!($('#amendreportData').hasClass('display-none'))){
            $('.amendreportData').addClass('display-none');
        }
        if(!($('#excelrowid').hasClass('display-none'))){
            $('.excelrow').addClass('display-none');
        }
        $.growl({message: response['msg']},{type: "warning"});
    }        

    return false;
}

 </script>

@endpush