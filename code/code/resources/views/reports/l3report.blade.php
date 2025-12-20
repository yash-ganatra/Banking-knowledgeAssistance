@extends('layouts.app')
@section('content')
<style type="text/css">
    body{overflow-x: hidden;}
    #l3Report{width: 100%!important;}
    .table th{background-color: #364FCC!important;}
    .table td, .table th{padding: 10px; word-wrap:break-word;}
    .sticky-header{background-color: #364FCC!important; color: white!important; position:sticky!important; top:0!important;}
    /*.table-responsive{overflow-x: initial!important;}*/
    .export-excel{float: right;margin-right: 1%;color:green;cursor: pointer;}
    .export{font-size: 13px;font-family: Arial;color:#364FCC}
    .month-wrapper{width: 420px!important;}
    div#excelrowid {
    position: relative;
  
    left: -61%;
    margin-top: -39px;
    font-size: 27px;
    padding-right: -18px;
}
.export {
    font-size: 19px;
    font-family: Arial;
    color: #ffffff;
    border: 1px solo;
}
.export-excel {
    padding-right: 13px;
    float: right;
    margin-right: 1%;
    color: green;
    cursor: pointer;
    border: 1px solid black;
    padding-top: 3px;
    padding-bottom: 6px;
    padding-left: 13px;
    background-color: #4099ff;
    color: white;
}
</style>
<div class="pcoded-content1">
    <div class="pcoded-inner-content1">
        <!-- Main-body start -->
        <div class="main-body">
            <div class="page-wrapper">
                <!-- Page-body start -->
                <div class="page-body page-body-top">
                   
                    <div class="col-md-3">
                        <div class="with-icon">
                            <input type="text" class="form-control startDate" id="startDate" name="startDate">
                            <i class="fa fa-times date-clear" aria-hidden="true" id="clear-dates"></i>
                        </div>
                    </div>
                    <div class="row excelrow display-none" id="excelrowid">
                        <div class="col-md-12 filter-icon-main dis">
                            <i class="fa fa-file-excel-o fa-1x export-excel" aria-hidden="true" data-toggle="tooltip" id="export-excel" data-placement="top" title="Export Data"><span class="export ml-2">Export Excel</span></i>
                        </div>
                    </div>
                    <div class="card table-top mt-5 l3reportData display-none" id="l3reportDataid">                              
                        <div class="card-block table-border-style card-block-padding">
                            <div class="table-responsive">
                                <table class="table table-custom" id="l3Report" >
                                    
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
        getl3ReportDetails();
    });

    $('#startDate').datepicker("setDate",'now');

    $('body').on('click','#clear-dates',function () {
        $('.startDate').val('');
        getl3ReportDetails();
    });

    $('#export-excel').on("click",function(e){
        e.preventDefault();
        
        $.growl({message: "Generating Excel file..."},{type: "success",delay:7800});
        $("#l3ReportDataTable").DataTable().page.len( -1 ).draw();

        if($("#l3ReportDataTable").DataTable().page.len() == -1){
                $("#l3ReportDataTable").DataTable().button('0').trigger();
                $.growl({message: "Excel file Generated"},{type: "success"});
        }
    });
});

function getl3ReportDetails(){
    if(typeof($('#startDate').val()) != 'undefined'){
        var sentDateRange = $('#startDate').val();
    }

    var l3reportObject = [];
    l3reportObject.data = {};
    l3reportObject['url'] = '/report/l3ReportDetails'

    if(typeof($('#startDate').val()) != 'undefined'){
        l3reportObject.data['startDate'] = $('#startDate').val();

    }
        l3reportObject.data['functionName'] = 'l3ReportCallBack';
        console.log(l3reportObject);

    crudAjaxCall(l3reportObject);
    return false;
}

function l3ReportCallBackFunction(response,object) {
    
    if(response['status'] == "success"){
        // $('.l3reportData').removeClass('display-none');
        $('.excelrow').removeClass('display-none');
        $('#l3Report').html(response.data);
        $.growl({message: response['msg']},{type: response['status'],allow_dismiss:false});
    }else{
        if(!($('#l3reportData').hasClass('display-none'))){
            $('.l3reportData').addClass('display-none');
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