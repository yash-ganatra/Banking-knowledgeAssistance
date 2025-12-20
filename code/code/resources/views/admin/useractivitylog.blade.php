@extends('layouts.app')
@section('content')
<style type="text/css">
    body{overflow-y: hidden;}
    #useractivitylogs{width: 100%!important; }
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
                        <div class="col-md-3">
                          {!! Form::select('users name',$users,null,array('class'=>'form-control users',
                                  'id'=>'users','name'=>'users','placeholder'=>'Select Users Name')) !!}
                        </div>
                        <div class="col-md-3">
                            <div class="with-icon">
                                <input type="text" class="form-control date-input_useractivity" placeholder="Sent Date To" id="sentDate">
                                <i class="fa fa-times date-clear" aria-hidden="true" id="clear-dates" autocomplete="off"></i>
                            </div>
                        </div>
                       <div class="col-md-3">
                            <input type="text" class="form-control" placeholder="Enter Module" name="module" id="module">
                        </div>
                        <div class="col-md-3">
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
                                              <td>ACTIVITY DATE</td>
                                              <td>USER</td>
                                              <td>URL</td>
                                              <td>MODULE</td>
                                              <td>CONTROLLER</td>
                                              <td>ACTION</td>
                                              <td>IP ADDRESS</td>
                                              <td>COMMENTS</td>
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

<script>
$(document).ready(function(){
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

<script type="text/javascript">
$(document).ready(function(){
     addSelect2('users','Users Name');
    //addSelect2('applicationStatus','Application Status');
        var navbarHeight = $(".header-navbar").height();
        var filterHeight = $(".filtergrid").height();
        var paginationHeight = 200;
        if(isNaN(navbarHeight)) navbarHeight = 25;
        if(isNaN(filterHeight)) filterHeight = 25;

        var tableRemainingHeight = navbarHeight+filterHeight+paginationHeight;
        $('#sentDate').dateRangePicker({
            startOfWeek: 'monday',
            separator : ' to ',
            format: 'DD-MM-YYYY',
            autoClose: true,
        }).bind('datepicker-change',function(event,obj){
           getUserActivityLogs('/admin/activitylogs','useractivitylogs',tableRemainingHeight);
        });

        $("body").on("change",".users",function(){
            getUserActivityLogs('/admin/activitylogs','useractivitylogs',tableRemainingHeight);
        });

        $("body").on("keyup","#module",function(){
            getUserActivityLogs('/admin/activitylogs','useractivitylogs',tableRemainingHeight);
        });

        $("#log_refresh_timers").on("change",function(){
            var timer = $("#log_refresh_timers").val();
            if (typeof(log_refresh_timers) != 'undefined') {
              clearInterval(log_refresh_timers);
            }
            if (timer == 'No Refresh') {
              return false;
            }
            log_refresh_timers = setInterval(function(){ 
              getUserActivityLogs('/admin/activitylogs','useractivitylogs',tableRemainingHeight);
            }, timer);
        });

        getUserActivityLogs('/admin/activitylogs','useractivitylogs',tableRemainingHeight);

        $('body').on('click','#clear-dates',function () {
            $('.date-input_useractivity').val('');
            getUserActivityLogs('/admin/activitylogs','useractivitylogs',tableRemainingHeight);
        });

    //var tableRemainingHeight = $(".header-navbar").height()+$(".filtergrid").height()+150;
    
    
});
</script>
@endpush