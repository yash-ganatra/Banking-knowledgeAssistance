@extends('layouts.app')
@section('content')
<style type="text/css">
/*    body{overflow-x: hidden;}*/
    #TatReport{width: 100%!important;font-size: small}
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
                    <label>Dimension Report - Start Date (Report for {{$disDateRange}} days)</label><br>
                </div>
                <div class="col" style="margin-top:7px; margin-left:15px;">  
                    <div class="col-md-2">
                    <div class="with-icon">
                        <input type="text" class="form-control date-input" placeholder="Select Date" id="sentDate" autocomplete="off">
                        <i class="fa fa-times date-clear" aria-hidden="true" id="clear-dates"></i>
                    </div>                        
                </div>
                    <button id="export-excel" onclick="getTatReportDetails()" style="margin-top:5px; margin-left:240px;">Submit</button>
                    </div>
                    <div class="row" style="display:none;">  
                        <div class="col-md-12 filter-icon-main dis">
                            <i class="fa fa-file-excel-o fa-1x export-excel" aria-hidden="true" data-toggle="tooltip" id="export-excel-old" data-placement="top" title="Export Data"><span class="export ml-2">Export Excel</span></i>
                        </div>
                    </div>
                    <div class="card table-top mt-5" style="display: none;">                              
                        <div class="card-block table-border-style card-block-padding">
                            <div class="table-responsive">
                                <table class="table table-custom" id="TatReport" >
                                    
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
    // getTatReportDetails();
    
    // $('#sentDate').dateRangePicker({
    //     startOfWeek: 'monday',
    //     separator : ' to ',
    //     format: 'DD-MM-YYYY',
    //     autoClose: true,
    //     endDate: new Date(),         
    // }).bind('datepicker-change',function(event,obj){
    //     getTatReportDetails();
    // });

    $("#sentDate").datepicker({
            clearBtn: true,
            format: "dd-mm-yyyy",
            endDate: "today",
            maxDate: "today",            
        }).on('change', function () {
            $(this).datepicker('hide');
           // getTatReportDetails();
        });



    // var endDate = new Date();
    // var startDate = new Date();
    // var day = startDate.getDate() - 14;
    // startDate = new Date(startDate.setDate(day))
    // $('#sentDate').data('dateRangePicker').setDateRange(startDate,endDate);

    $('body').on('click','#clear-dates',function () {
        $('.date-input').val('');
        // getTatReportDetails();
    });
});

function getTatReportDetails(){
    if(typeof($('#sentDate').val()) != 'undefined'){
 
        var startDate = $('#sentDate').val();
        // var startDate =  new Date(formateDate);
    }else{
        $('#sentDate').val('');
        return false;
    }
    
    document.getElementById("export-excel").disabled = true;

    var tatreportObject = [];
    tatreportObject.data = {};
    tatreportObject['url'] = '/management/tatreportdetails'
    tatreportObject.data['startDate'] = startDate;
    tatreportObject.data['functionName'] = 'tatReportCallBack';
    crudAjaxCall(tatreportObject);
    return false;
}

function tatReportCallBackFunction(response,object) {
    document.getElementById("export-excel").disabled = false;
    if(response['status'] == "success"){
        //$('#TatReport').html(response.data);
        $.growl({message: 'Data downloaded. Please wait while we generate the report...'},{type: response['status'],allow_dismiss:true});
        // $('#TatReport').html(arrayToHtml(response.data));
        $('#TatReport').html(arryToCsv(response.data));

    }else{
        $.growl({message: response['msg']},{type: "warning"});
    }        

    return false;
}

function arrayToHtml(data){
    // console.log(data);
    var result = "<table id='TatReport'>";
    for(var i=0; i<data.length; i++) {
        if(data[i][1] != null){
            result += "<tr style='border-top:solid'>"; 
        }else{
            result += "<tr>";
        }
        for(var j=0; j<data[i].length; j++){

            if(data[i][j] == null){
                var displayValue = '';
            }else{
                var displayValue = data[i][j];
            }

            if(i == 0 || j >data[i].length){
                result += "<th class='sticky-header'>"+displayValue+"</th>";
            }else{
                result += "<td style='white-space: inherit;'>"+displayValue+"</td>";
            }

        }
        result += "</tr>";
    }
    result += "</table>";
        $.growl({message:'Report generated'},{type:'success',allow_dismiss:true});
    return result;

}

function arryToCsv(data){
    /*var csv_data = [];
    for(var i=0; i<data.length; i++) {
        var csvrow = [];
        for(var j=0; j<data[i].length; j++){
            csvrow.push(data[i][j]);
        }
        csv_data.push(csvrow.join(","));
    }
    csv_data = csv_data.join('\n');*/

    //CSVFile = new Blob([csv_data], { type: "text/csv" });
    CSVFile = new Blob([data], { type: "text/csv" });
    var temp_link = document.createElement('a');
    temp_link.download = "DimensionReport.csv";
    var url = window.URL.createObjectURL(CSVFile);
    temp_link.href = url;
    temp_link.style.display = "none";
    document.body.appendChild(temp_link);
    temp_link.click();
    document.body.removeChild(temp_link);
}

$('.export-excel').on('click',function(){       
    var csv_data = [];
    var getTableData = document.getElementById('TatReport')
    var rows = getTableData.getElementsByTagName('tr');
    for (var i = 0; i < rows.length; i++) {
        var cols = rows[i].querySelectorAll('td,th');
        var csvrow = [];
        for (var j = 0; j < cols.length; j++) {
            var colHead = cols[j].innerText.replace('\n',' ');
            csvrow.push(colHead);
        }
        csv_data.push(csvrow.join(","));
    }
    csv_data = csv_data.join('\n');

    CSVFile = new Blob([csv_data], { type: "text/csv" });
    var temp_link = document.createElement('a');
    temp_link.download = "DimensionReport.csv";
    var url = window.URL.createObjectURL(CSVFile);
    temp_link.href = url;
    temp_link.style.display = "none";
    document.body.appendChild(temp_link);
    temp_link.click();
    document.body.removeChild(temp_link);
});

</script>

@endpush