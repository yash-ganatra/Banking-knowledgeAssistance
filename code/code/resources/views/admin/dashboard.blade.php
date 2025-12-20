@extends('layouts.app')
@section('content')
<style type="text/css">
    body{overflow-y: hidden;}
    #usersTable{width: 100%!important; }
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
                      <div class="col-md-6 add-user">
                        <a href="{{route('adduser')}}" type="button" class="btn btn-yellow waves-effect waves-light">
                          Add User
                        </a>
                      </div>
                    </div>
                  </div>

                    <div class="row filter mb-3 drop-down-top filtergrid" style="display: none;">
                        <div class="col-md-4">
                           {!! Form::select('users name',$users,null,array('class'=>'form-control users',
                                  'id'=>'users','name'=>'users','placeholder'=>'Select Users Name')) !!}
                        </div>
                       <!--  <div class="col-md-4">
                            <div class="with-icon">
                                <input type="text" class="form-control date-input1" placeholder="Sent Date To" id="sentDate">
                                <i class="fa fa-times date-clear" aria-hidden="true" id="clear-dates"></i>
                            </div>
                        </div> -->
                        <!-- <div class="col-md-4">
                            {!! Form::select('application Status',array(),null,array('class'=>'form-control applicationStatus',
                                  'id'=>'applicationStatus','name'=>'applicationStatus','placeholder'=>'Select Application Status')) !!}
                        </div> -->
                    </div>
                    <div class="card table-top">                                            
                        <div class="card-block table-border-style card-block-padding">
                            <div class="table-responsive">
                                <table class="table table-custom" id="usersTable">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>HRMSNO</th>
                                            <th>EMPSOL</th>
                                            <th>EMP NAME</th>
                                            <th>EMPMOBILENO</th>
                                            <th>EMPEMAILID</th>
                                            <th>EMPLDAPUSERID</th>
                                            <th>EMPBUSINESSUNIT</th>
                                           <!--  <th>EMPLOCATION</th>
                                            <th>EMPBRANCH</th> -->
                                            <th>ROLE</th>
                                            <th>EMPSTATUS</th>
                                            <th>ACTION</th>                       
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
    var tableRemainingHeight = $(".header-navbar").height()+$(".filtergrid").height()+200;
   
  
     $("body").on("change",".users",function(){
            getUser('/admin/getuserslist','usersTable',tableRemainingHeight);
        });
      getUser('/admin/getuserslist','usersTable',tableRemainingHeight);
});
</script>
@endpush