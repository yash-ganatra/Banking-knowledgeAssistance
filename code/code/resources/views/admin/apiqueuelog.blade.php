@extends('layouts.app')
@section('content')
<style type="text/css">
	#apiqueuelogtable{width: 100%!important; }
	.table td, .table th{padding: 10px 0px;}
	pre {
		white-space: pre-wrap;       
		white-space: -moz-pre-wrap;  
		white-space: -pre-wrap;      
		white-space: -o-pre-wrap;    
		word-wrap: break-word;       
	}
</style>
<div class="pcoded-content1">
    <div class="pcoded-inner-content1">
        <!-- Main-body start -->
        <div class="main-body">
            <div class="page-wrapper">
                <!-- Page-body start -->
                <div class="page-body page-body-top mb-3">
                    <div class="row">
                        <div class="col-md-6 filter-icon-main filter-icon-main-2 d-flex align-items-center">
                            <a class="filter-icon"><i class="fa fa-filter"></i> Filters</a>
                            <a class="filter-close" style="display: none;"><i class="fa fa-times"></i> Close Filters</a>
                        </div>
                    </div>
                    <div class="row filter mb-3 drop-down-top filtergrid" style="display: none;">
                        <!-- <div class="col-md-2">
                                <input type="text" class="form-control" placeholder="Form Id" name="formId" id="formId">
                        </div>  -->
                        <div class="col-md-2">
                             <input type="text" class="form-control" placeholder="Aof Number" name="aofNumber" id="apiqueueaofNumber">
                        </div> 
                        <div class="col-md-6 display-none" id="update_api_queue_div">
	                        <div class="col-md-3">
	                             <input type="text" class="form-control" placeholder="Api Queue Id" name="api_queue_id" id="api_queue_id">
	                        </div> 
	                        <div class="col-md-3">
	                            <button type="button" class="btn btn-primary update_api_queue" id="update_api_queue" style="margin-left: 198px; margin-top: -67px;">Update</button>
	                        </div> 
                        </div> 

                    </div>
                    <div class="card table-top mt-3">                                            
                        <div class="card-block table-border-style card-block-padding">
                            <div class="table-responsive">
                                <table class="table table-custom" id="apiqueuelogtable">
                                    <thead>
                                        <tr>
                                            <td>ID</td>
                                            <td>FORM ID</td>
                                            <td>AOF NUMBER</td>
                                            <td>API NAME</td>
                                            <td>APPLICANT</td>
                                            <td>STATUS</td>
                                            <td>CREATED BY</td>
                                            <td>RETRY</td>
                                            <td>NEXT RUN</td>
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
<script  src="{{ asset('custom/js/admin.js') }}"></script>
<script type="text/javascript">
$(document).ready(function(){
    tableRemainingHeight = 308.75;
    // $("body").on("keyup","#formId",function(){
    //     getapiQueueLogs('/admin/apiqueuelogtable','l1editlogs',tableRemainingHeight);
    // });
    $("body").on("keyup","#apiqueueaofNumber",function(){
        if($('#apiqueueaofNumber').val().length >= 9){
           	getapiQueueLogs('/admin/apiqueuelogtable','apiqueuelogtable',tableRemainingHeight);
            $('#update_api_queue_div').addClass('display');
        }
        
    });
    getapiQueueLogs('/admin/apiqueuelogtable','apiqueuelogtable',tableRemainingHeight);

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

    $("body").on("click","#update_api_queue",function(){
    	var updateapiqueue = [];
        updateapiqueue.data = {};
        updateapiqueue.url =  '/admin/updateapiqueue';
        updateapiqueue.data['api_queue_id'] = $('#api_queue_id').val();
        updateapiqueue.data['functionName'] = 'UpdateApiQueueCallBack';
        crudAjaxCall(updateapiqueue);
        return false;

    });


});
    function UpdateApiQueueCallBackFunction(response,object){
    	if(response['status'] == "success"){
            $.growl({message: response['message']},{type: response['status']});
       		getapiQueueLogs('/admin/apiqueuelogtable','apiqueuelogtable',tableRemainingHeight);
        }else{
            $.growl({message: response['message']},{type: "warning"});
            return false;
        }
    }

</script>
@endpush