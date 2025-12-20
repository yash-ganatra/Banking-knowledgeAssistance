@extends('layouts.app')
@section('content')
<style type="text/css">
    body{overflow-y: hidden;}
    #emailsmsmessagesTable{width: 100%!important; }
    .table td, .table th{padding: 10px 0px;text-align: center;}
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
                        <div class="col-md-2">
                            <div class="with-icon">
                                <input type="text" class="form-control" placeholder="AOF Number" name="aofNumber" id="aofNumber">
                            </div>
                        </div>
                        <div class="col-md-3">
                            {!! Form::select('Activity Codes',$activityCodes,null,array('class'=>'form-control activityCode',
                              'id'=>'activityCodes','name'=>'activityCodes','placeholder'=>'Select Activity Code')) !!}
                        </div>
                        <div class="col-md-2">
                          {!! Form::select('log_refresh_timers',$log_refresh_timers,null,array('class'=>'form-control log_refresh_timers','id'=>'log_refresh_timers','name'=>'log_refresh_timers')) !!}
                        </div>   
                    </div>
                    <div class="card table-top">                                            
                        <div class="card-block table-border-style card-block-padding">
                            <div class="table-responsive">
                                <table class="table table-custom" id="emailsmsmessagesTable">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>AOF NUMBER</th>
                                            <th>ACTIVITY CODE</th>
                                            <th>MESSAGE TYPE</th>
                                            <th>EMAIL ID</th>
                                            <th>ATTACHMENT</th>
                                            <th>MOBILE</th>
                                            <th>SENT DATE</th>
                                            <th>IS SENT</th>
                                            <th>CREATED AT</th>                                           
                                            <th>SMS RESPONSE CODE</th>
                                            <th>EMAIL RESPONSE</th>
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
<div class="modal fade" id="email_sms_message_modal" tabindex="-1" role="dialog">
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
    //addSelect2('users','Users Name');
    addSelect2('activityCode','Activity Codes');
    var tableRemainingHeight = $(".header-navbar").height()+$(".filtergrid").height()+230;
    getEmailSmsMessagesLogs('/admin/getemailsmsmessageslist','emailsmsmessagesTable',tableRemainingHeight);

    $("#log_refresh_timers").on("change",function(){
        var timer = $("#log_refresh_timers").val();
        if (typeof(log_refresh_timers) != 'undefined') {
          clearInterval(log_refresh_timers);
        }
        if (timer == 'No Refresh') {
          return false;
        }
        log_refresh_timers = setInterval(function(){ 
            getEmailSmsMessagesLogs('/admin/getemailsmsmessageslist','emailsmsmessagesTable',tableRemainingHeight);
        }, timer);
    });
    
    $("#aofNumber").on("keyup", function() {
        var value = $(this).val().toLowerCase();
        $("#emailsmsmessagesTable tbody > tr").filter(function() {
          $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });
    $("body").on("change",".activityCode",function(){
        getEmailSmsMessagesLogs('/admin/getemailsmsmessageslist','emailsmsmessagesTable',tableRemainingHeight);
    });
     //  getUser('/admin/getexceptionlist','exceptionTable',tableRemainingHeight);
});
</script>
@endpush