@extends('layouts.app')
@section('content')
    <div class="pcoded-inner-content1">
        <!-- Main-body start -->
        <div class="main-body">
            <div class="page-wrapper">
                <!-- Page-body start -->
                <div class="page-body page-body-top">
					<div class = "row">
						<div class="col-md-8 mx-auto">
					      	<div class="card mt-2">
					        	<div class="card-body">
									<div class="mb-3">
					  					<label for="importexcel" class="form-label">Import Excel</label>
					  					<input class="form-control" type="file" id="importexcel">
									</div>
									<center>
						               <button type ="button" id="importexcelbuttons" data-id="importexcelbutton" class="btn btn-primary excelbutton">Submit</button>
						               <button type ="button" id="cancel" class="btn btn-danger  display-none">Cancel</button>
						               <button type ="button" id="confirms" data-id="confirm" class="btn btn-success excelbutton display-none">Confirm</button>
						            </center>
								</div>
							</div>
						</div>
						<div class="card table-top mx-auto display-none" id="tabledump">                                            
                        <div class="card-block table-border-style card-block-padding mx-auto">
                            <div class="table-responsive mx-auto">
                                <table class="table table-custom" id="archivalexceltable">
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
                                    <tbody id="excel">
                                    	
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
					</div>
				</div>
			</div>
		</div>
	</div>


@endsection
@push('scripts')
	<script src="{{ asset('components/importexcel/importexcel.js') }}"></script>
	<script type="text/javascript">
      var importexcel = document.getElementById('importexcel');
        $("body").on("click",".excelbutton",function(){
            
    	   var method = $(this).attr('data-id');
    	    readXlsxFile(importexcel.files[0]).then(function(exceldata) {
                if(method == 'importexcelbutton'){
                	makeTableHTML(exceldata);
               	}else if(method == 'confirm'){
                   	var importexceldata = [];
                    importexceldata.data = {};
                    importexceldata.data['excel_data'] = JSON.stringify(exceldata);
                	importexceldata.url =  '/archival/importarchivalrecord';
        			importexceldata.data['functionName'] = 'ImportExcelDataCallBack';
                    crudAjaxCall(importexceldata);
               	}
    	    });
    	});

 

    function makeTableHTML(exceldataArray) {
        var result = '';
        console.log(exceldataArray);
        for(var i=1; i<exceldataArray.length; i++) {
            result += "<tr>";
            for(var j=0; j<exceldataArray[i].length; j++){
            	exceldataArray[i][5] = JSON.stringify(exceldataArray[i][5])
    			exceldataArray[i][5] = exceldataArray[i][5].slice(1,11)
    			exceldataArray[i][8] = JSON.stringify(exceldataArray[i][8])
    			exceldataArray[i][8] = exceldataArray[i][8].slice(1,11)
                result += "<td>"+exceldataArray[i][j]+"</td>";
            }
            result += "</tr>";
        }
        $('#tabledump').removeClass('display-none');
    	$('#cancel').removeClass('display-none');
        $('#confirms').removeClass('display-none');
        $('#importexcelbuttons').addClass('display-none');
        var table = $('#archivalexceltable').DataTable();
        table.clear().draw().destroy();
        $('#excel').empty();
        makeDataTable(result);
    }

	function ImportExcelDataCallBackFunction(response, object)
	{
		if(response['status'] == 'success' || response['status'] == 'fail'){
            $.growl({message: response['msg']},{type: response['status']});
			location.reload();
		}else if(response['status'] == 'warning'){
            $.growl({message: response['msg']},{type: response['status']});
            console.log(response.data.duplicateRecords);
            var exceldataArray = response.data.duplicateRecords;
            var table = $('#archivalexceltable').DataTable();
            table.clear().draw().destroy();
            $('#excel').empty();
            makeDataTable(exceldataArray);
            $('#confirms').addClass('display-none');
            $('#importexcelbuttons').removeClass('display-none');
            $('#importexcel').val('');
        }
	}

    function makeDataTable(datatable){
        $('#excel').append(datatable);
        $('#archivalexceltable').DataTable({ dom: '<"top"f>rt<"bottom"lip><"clear">'});
        $('.top').css('display','none');
        $('.bottom').css('margin-top','-19px');
        $('#archivalexceltable_length').css('width', '20%').css('display', 'inline');
        $('#archivalexceltable_info').css('display', 'inline').css('width', '30%').css('margin-left', '36%');
        $('#archivalexceltable_paginate').css('width', '30%').css('float', 'right').css('display', 'inline').css('margin-top', '5%');
    }

    $("body").on("click","#cancel",function(){
    	location.reload();
    });

   function getUserDataApplications(){
		var tableRemainingHeight = $(".header-navbar").height()+$(".accountsgrid").height()/*+$(".filtergrid").height()*/+260;
	    var tableObject = [];
	    tableObject.data = {};
	    tableObject.data['table'] = "archivalexceltable";
	    tableObject.url =  '/archival/archivalrecordtemp';
	    datatableAjaxCall(tableObject,tableRemainingHeight, 0,"asc");
	    return false;
	}

</script>
@endpush