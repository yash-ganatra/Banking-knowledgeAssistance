@extends('layouts.app')
@section('content')
<link rel="stylesheet" type="text/css" href="{{ asset('icon/icofont/css/icofont.css') }}">
<style type="text/css">
    body{overflow-y: hidden;}
    #templatesTable{width: 100%!important; }
    .table td, .table th{padding: 10px 0px;}
     a{text-decoration: none!important;}
</style>
<div class="pcoded-content1">
    <div class="pcoded-inner-content1">
        <!-- Main-body start -->
        <div class="main-body">
            <div class="page-wrapper">
                <!-- Page-body start -->
                <div class="page-body page-body-top mb-3">
                    <div class="row">
                          <!-- 22May23 - For BS5 - commented below line -->
                        <!-- <div class="col-md-6 add-template text-left"> -->
                            <div class="col-md-6 text-left">
                            <h4>Templates List</h4>
                        </div>
                        <div class="col-md-6">
                             <!-- 22May23 - For BS5 - commented below line -->
                            <!-- <a href="{{route('addtemplate')}}" type="button" class="btn btn-yellow waves-effect waves-light float-right"> -->
                            <a href="{{route('addtemplate')}}" type="button" class="btn btn-yellow waves-effect waves-light float-end">
                                Add Template
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card table-top">
                    <div class="card-block table-border-style card-block-padding">
                        <div class="table-responsive">
                            <table class="table table-custom" id="templatesTable">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>ACTIVITY CODE</th>
                                        <th>ACTIVITY</th>
                                        <th>ROLE</th>
                                        <th>MESSAGE_TYPE</th>
                                        <th>MESSAGE</th>
                                        <th>FUNCTION NAME</th>
                                        <th>ACTIVE</th>
                                        <th>EDIT</th>
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
@endsection
@push('scripts')
<script  src="{{ asset('custom/js/emailsms.js') }}"></script>
<script type="text/javascript">
$(document).ready(function(){
  var navbarHeight = $(".header-navbar").height();
  var filterHeight = $(".filtergrid").height();
  var paginationHeight = 200;
  if(isNaN(navbarHeight)) navbarHeight = 25;
  if(isNaN(filterHeight)) filterHeight = 25;  
  var tableRemainingHeight = navbarHeight+filterHeight+paginationHeight;
  //var tableRemainingHeight = $(".header-navbar").height()+$(".filtergrid").height()+150;
  getUserApplications('/admin/gettemplates','templatesTable',tableRemainingHeight);
});
</script>
@endpush
