@extends('layouts.app')
@section('content')
<style type="text/css">
    body{overflow-y: hidden;}
    #usersTable{width: 100%!important; }
    .table td, .table th{padding: 10px 0px;}

    .modal-footer{
      flex-wrap: nowrap !important;
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
                      <div class="col-md-4 filter-icon-main filter-icon-main-2 d-flex align-items-center">
                          <a class="filter-icon"><i class="fa fa-filter"></i> Filters</a>
                          <a class="filter-close" style="display: none;"><i class="fa fa-times"></i> Close Filters</a>
                      </div>
                      <div class="col-md-4">
                        <div class=" filter drop-down-top filtergrid" style="display: none;">
                             {!! Form::select('users name',$users,null,array('class'=>'form-control users',
                                    'id'=>'users','name'=>'users','placeholder'=>'Select EMPLDAP User')) !!}
                        </div>
                      </div>
                      <div class="col-md-4 add-user">
                        <a href="{{route('uamadduser')}}" type="button" class="btn btn-yellow waves-effect waves-light">
                          Add Users
                        </a>
                      </div>
                    </div>
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
                                            <th>EMPLDAPUSERID</th>
                                            <th>EMPBUSINESSUNIT</th>
                                            <th>EMPLOCATION</th>
                                            <th>EMPBRANCH</th>
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
                    <!-- Modal -->
                    <div class="modal fade" id="uamdashModal" tabindex="-1" role="dialog" aria-labelledby="uamModal" aria-hidden="true" style="">
                      <div class="modal-dialog" role="document">
                        <div class="modal-content" style="margin-top: 50%;cursor: pointer;">
                          <div class="modal-header">
                            <h5 class="modal-title" id="uamModal"></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                              <!-- <span aria-hidden="true">&times;</span> -->
                            </button>
                          </div>
                          <div class="modal-body" id="uamMsgModal">
                              Please Select Appropriate Button.
                          </div>
                          <div class="modal-footer">
                            <button type="button" class="btn btn-danger" id="permDeactivated" style="margin-right:40%">Delete</button>
                            <button type="button" class="btn btn-primary" id="activatedUser">Activated</button>
                            <button type="button" class="btn btn-primary" id="tempDeactivated">Disabled</button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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
<script  src="{{ asset('custom/js/uam.js') }}"></script>

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
            getUser('/uam/getuamuserslist','usersTable',tableRemainingHeight);
        });
      getUser('/uam/getuamuserslist','usersTable',tableRemainingHeight);
});
</script>
@endpush