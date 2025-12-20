@extends('layouts.app')
@section('content')
<style type="text/css">
	#amendapiqueuelogtable{width: 100%!important; }
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
                             <input type="text" class="form-control" placeholder="CRF Number" name="crfNumber" id="amendapiqueuecrfNumber">
                        </div> 
                        <div class="col-md-6 display-none" id="update_amendapi_queue_div">
                            <div class="col-md-3" style="display:none;">
                                 <input type="text" class="form-control" placeholder="Amend Api Queue Id" name="amendapi_queue_id" id="amendapi_queue_id" >
                            </div> 
                            <div class="col-md-3">
                                <button type="button" class="btn btn-primary update_amendapi_queue" id="update_amendapi_queue">Execute Next</button>
	                        </div> 
                        </div> 
                        
                    </div>
                    <div class="card table-top mt-3">                                            
                        <div class="card-block table-border-style card-block-padding">
                            <div class="table-responsive">
                                <table class="table table-custom" id="amendapiqueuelogtable">
                                    <thead>
                                        <tr>
                                            <td>ID</td>
                                            <td>CRF NUMBER</td>
                                            <td>API NAME</td>
                                            <td>SEQUENCE</td>
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
   
    $("body").on("keyup","#amendapiqueuecrfNumber",function(){
        if($('#amendapiqueuecrfNumber').val().length >= 12){
            getamendapiQueueLogs('/admin/amendapiqueuelogtable','amendapiqueuelogtable',tableRemainingHeight);
            $('#update_amendapi_queue_div').addClass('display');
        }
        
    });
    getamendapiQueueLogs('/admin/amendapiqueuelogtable','amendapiqueuelogtable',tableRemainingHeight);

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

    $("body").on("click","#update_amendapi_queue",function(){
    	var updateamendapiqueue = [];
        updateamendapiqueue.data = {};
        updateamendapiqueue.url =  '/admin/updateamendapiqueue';
        updateamendapiqueue.data['crfNumber'] =$('#amendapiqueuecrfNumber').val();
        updateamendapiqueue.data['amednapi_queue_id'] = $('#amendapi_queue_id').val();
        updateamendapiqueue.data['functionName'] = 'UpdateAmendApiQueueCallBack';
        crudAjaxCall(updateamendapiqueue);
        return false;

    });


});

function UpdateAmendApiQueueCallBackFunction(response,object){
    if(response['status'] == "success"){
        $.growl({message: response['message']},{type: response['status']});
        getamendapiQueueLogs('/admin/amendapiqueuelogtable','amendapiqueuelogtable',tableRemainingHeight);
    }else{
        $.growl({message: response['message']},{type: "warning"});
        return false;
    }
}

</script>
@endpush