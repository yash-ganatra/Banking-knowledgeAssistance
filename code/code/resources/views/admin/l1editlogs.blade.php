@extends('layouts.app')
@section('content')
<style type="text/css">
	#l1editlogs{width: 100%!important; }
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
                        <div class="col-md-2">
                                <input type="text" class="form-control" placeholder="Form Id" name="formId" id="formId">
                        </div> 
                        <div class="col-md-2">
                                <input type="text" class="form-control" placeholder="Aof Number" name="aofNumber" id="aofNumber">
                        </div>  
                    </div>
                    <div class="card table-top mt-3">                                            
                        <div class="card-block table-border-style card-block-padding">
                            <div class="table-responsive">
                                <table class="table table-custom" id="l1editlogs">
                                    <thead>
                                        <tr>
                                            <td>ID</td>
                                            <td>FORM ID</td>
                                            <td>AOF NUMBER</td>
                                            <td>OLD VALUE</td>
                                            <td>NEW VALUE</td>
                                            <td>FIELD NAME</td>
                                            <td>APPLICANT_SEQUENCE</td>
                                            <td>CREATED BY</td>
                                            <td>CREATED AT</td>
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
    $("body").on("keyup","#formId",function(){
        getl1ReviewLogs('/admin/l1editlogstable','l1editlogs',tableRemainingHeight);
    });
    $("body").on("keyup","#aofNumber",function(){
        getl1ReviewLogs('/admin/l1editlogstable','l1editlogs',tableRemainingHeight);
    });
    getl1ReviewLogs('/admin/l1editlogstable','l1editlogs',tableRemainingHeight);

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
@endpush