@extends('layouts.app')
@section('content')
<style type="text/css">
    body{overflow-y: hidden;}
    #exceptionTable{width: 100%!important; }
    .table td, .table th{padding: 10px 0px;}
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
                  </div>

                    <div class="row filter mb-3 drop-down-top filtergrid" style="display: none;">
                        <div class="col-md-4">
                           {!! Form::select('users name',$users,null,array('class'=>'form-control users',
                                  'id'=>'users','name'=>'users','placeholder'=>'Select Users Name')) !!}
                        </div>
                        <div class="col-md-2">
                          {!! Form::select('log_refresh_timers',$log_refresh_timers,null,array('class'=>'form-control log_refresh_timers','id'=>'log_refresh_timers','name'=>'log_refresh_timers')) !!}
                        </div>   
                    </div>
                    <div class="card table-top">                                            
                        <div class="card-block table-border-style card-block-padding">
                            <div class="table-responsive">
                                <table class="table table-custom" id="exceptionTable">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>MODULE</th>
                                            <th>FUNCTION NAME</th>
                                            <th>USER ID</th>
                                            <th>AOF NUMBER</th>
                                            <th>FORM ID</th>
                                            <th>MESSAGE</th>
                                            <th>CREATED AT</th>                                           
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
</script>

<script type="text/javascript">
$(document).ready(function(){
    addSelect2('users','Users Name');
    //addSelect2('applicationStatus','Application Status');
    var tableRemainingHeight = $(".header-navbar").height()+$(".filtergrid").height()+230;
   
  
     $("body").on("change",".users",function(){
            getUser('/admin/getexceptionlist','exceptionTable',tableRemainingHeight);
        });
      getUser('/admin/getexceptionlist','exceptionTable',tableRemainingHeight);

      $("#log_refresh_timers").on("change",function(){
        var timer = $("#log_refresh_timers").val();
        if (typeof(log_refresh_timers) != 'undefined') {
          clearInterval(log_refresh_timers);
        }
        if (timer == 'No Refresh') {
          return false;
        }
        log_refresh_timers = setInterval(function(){ 
          getUser('/admin/getexceptionlist','exceptionTable',tableRemainingHeight);
        }, timer);
    });
});
</script>
@endpush