@extends('layouts.app')
@section('content')
<style type="text/css">
    body{overflow-y: hidden;}
    #useractivitylogs{width: 100%!important; }
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
                            <div class="with-icon">
                                <input type="text" class="form-control" placeholder="Form Id" name="formId" id="formId">
                            </div>
                        </div>  

                        <div class="col-md-2">
                            <div class="with-icon">
                                <input type="text" class="form-control" placeholder="AOF Number" name="aofNumber" id="aofNumber">
                            </div>
                        </div>

                        <div class="col-md-1">
                            <button class="btn btn-primary">Search</button>
                        </div>                      
                        <div class="col-md-3">
                            {!! Form::select('Service Name',$serviceNames,null,array('class'=>'form-control serviceName',
                                  'id'=>'serviceName','name'=>'serviceName','placeholder'=>'Select Service Name')) !!}
                        </div>
                        <div class="col-md-2">
                            {!! Form::select('user name',$users,null,array('class'=>'form-control userName',
                                  'id'=>'userName','name'=>'userName','placeholder'=>'Select User Name')) !!}
                        </div>
                        <div class="col-md-2">
                          {!! Form::select('log_refresh_timers',$log_refresh_timers,null,array('class'=>'form-control log_refresh_timers','id'=>'log_refresh_timers','name'=>'log_refresh_timers')) !!}
                        </div>   
                    </div>
                    <div class="card table-top">                                            
                        <div class="card-block table-border-style card-block-padding">
                            <div class="table-responsive">
                                <table class="table table-custom" id="useractivitylogs">
                                    <thead>
                                        <tr>
                                            <td>ID</td>
                                            <td>FORM ID</td>
                                            <td>SERVICE NAME</td>
                                            <td>SERVICE URL</td>
                                            <td>SERVICE REQUEST</td>
                                            <td>SERVICE RESPONSE</td>
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
<div class="modal fade" id="service_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content modal-content-etb">
            <div class="modal-header">
                <div class="row">
                    <div class="col-sm-12">
                        <h4 class="modal-title"></h4>
                    </div>
                </div>

                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <!-- <span aria-hidden="true">&times;</span> -->
                </button>
            </div>

            <div class="modal-body">
                
            </div>

            <div class="modal-footer modal-display">
                <button type="button" class="btn btn-default pull-right waves-effect mr-2" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script  src="{{ asset('custom/js/admin.js') }}"></script>
<script type="text/javascript">
$(document).ready(function(){
    addSelect2('serviceName','Service Name');
    addSelect2('userName','User Name');
    var navbarHeight = $(".header-navbar").height();
    var filterHeight = $(".filtergrid").height();
    var paginationHeight = 50;
    if(isNaN(navbarHeight)) navbarHeight = 25;
    if(isNaN(filterHeight)) filterHeight = 25;

    var tableRemainingHeight = navbarHeight+filterHeight+paginationHeight;
    
    getUserApiLogs('/admin/apirequestlogs','useractivitylogs',tableRemainingHeight);

    $("#log_refresh_timers").on("change",function(){
        var timer = $("#log_refresh_timers").val();
        if (typeof(log_refresh_timers) != 'undefined') {
          clearInterval(log_refresh_timers);
        }
        if (timer == 'No Refresh') {
          return false;
        }
        log_refresh_timers = setInterval(function(){ 
            getUserApiLogs('/admin/apirequestlogs','useractivitylogs',tableRemainingHeight);
        }, timer);
    });

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