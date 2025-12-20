@extends('layouts.app')
@section('content')
<style type="text/css">
    .export-excel{float: right;color:green;cursor: pointer;}
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

                </div>
                <div class="row">
                    <div class="col-md-3">
                        <input type="text" name="aofNumber" class="form-control" placeholder=" Search Aof Number " id="aofNumber">
                    </div>
                    <div class="col-md-9">
                         <i class="fa fa-file-excel-o fa-1x export-excel" aria-hidden="true" data-toggle="tooltip" id="exportarchival" data-placement="top" title="Export Data"><span class="export ml-2">Export Excel</span></i>
                    </div>
                </div>
                    <div class="card table-top mt-2" id="tabledump">                                            
                        <div class="card-block table-border-style card-block-padding">
                            <div class="table-responsive">
                                <table class="table table-custom mx-auto" id="archivalexceltable" width="100%">
                                    <thead>
                                        <tr>
                                            <th>BOX BARCODE</th>
                                            <th>FILE BARCODE</th>
                                            <th>AOF NUMBER</th>
                                            <th>CUSTOMER NAME</th>
                                            <th>CUSTOMER ID</th>
                                            <th>ACCOUNT OPENING DATE</th>
                                            <th>ACCOUNT ID</th>             
                                            <th>BRANCH ID</th>             
                                            <th>ARCHIVAL DATE</th>             
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
            </div>
        </div>
    </div>
</div>



@endsection
@push('scripts')

<script type="text/javascript">

    var tableRemainingHeight = $(".header-navbar").height()+$(".accountsgrid").height()/*+$(".filtergrid").height()*/+260;
    $(document).ready(function(){
    	getUserDataApplications();
    });

    $("body").on("click",".archivalrecords",function(){
        var aof_number =  $(this).text();
        let currentScreen = window.location.href.substr(window.location.href.lastIndexOf('/') +1 );
        redirectUrl(aof_number +  '.' + currentScreen, '/archival/editarchivalexcelrecord');
        return false;
    });

    $("#aofNumber").on("keyup",function(){
        getUserApplications('/archival/archivalrecords','archivalexceltable',tableRemainingHeight);
    });

	function getUserDataApplications(){
	    var tableObject = [];
	    tableObject.data = {};
        tableObject.data['AOF_NUMBER'] = $("#aofNumber").val();
	    tableObject.data['table'] = "archivalexceltable";
	    tableObject.url =  '/archival/archivalrecords';
	    datatableAjaxCall(tableObject,tableRemainingHeight, 0,"asc");
	    return false;
	}

        $('#exportarchival').on("click",function(e){
            e.preventDefault();
            $.growl({message: "Generating Excel file..."},{type: "success",delay:7800});
            $("#archivalexceltable").DataTable().page.len( -1 ).draw();
            if($("#archivalexceltable").DataTable().page.len() == -1){
                setTimeout(function(){
                    $("#archivalexceltable").DataTable().button('0').trigger();
                    $.growl({message: "Excel file Generated"},{type: "success"});
                    //$("#export-xls").removeClass("display-none");
                },8000);
            }
        });

</script>
@endpush