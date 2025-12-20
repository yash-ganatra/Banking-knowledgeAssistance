<style type="text/css">
    body{overflow-y: hidden;}
    #emailsmsmessagespendingTable{width: 100%!important; }
    .table td, .table th{padding: 10px 0px;}
</style>

@extends('layouts.app')
@section('content')
<div class="pcoded-content1">
    <div class="pcoded-inner-content1">
        <!-- Main-body start -->
        <div class="main-body">
            <div class="page-wrapper">
                <!-- Page-body start -->
                  <div class="page-body page-body-top mb-3">
                    <div class="row">
                      <!-- <div class="col-md-6 filter-icon-main filter-icon-main-2 d-flex align-items-center">
                          <a class="filter-icon"><i class="fa fa-filter"></i> Filters</a>
                          <a class="filter-close" style="display: none;"><i class="fa fa-times"></i> Close Filters</a>
                      </div> -->
                    </div>
                  </div>

                    
                    <div class="card table-top">                                            
                        <div class="card-block table-border-style card-block-padding">
                            <div class="table-responsive">
                                <table class="table table-custom" id="emailsmsmessagespendingTable">
                                    <thead>
                                        <tr>
                                            <th>DATE</th>
                                            <th>COUNT</th>
                                        </tr>
                                    </thead>
                                        @foreach($sqlQuery as $sqlQueryData)
                                        <tr>
                                            <td>{{$sqlQueryData->emaildate}}</td>
                                            <td>{{$sqlQueryData->forms}}</td>
                                        </tr>
                                        @endforeach
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
<script>
    var documentHeight = $(document).height();
    var tableRemainingHeight = $(".header-navbar").height()+$(".accountsgrid").height()/*+$(".filtergrid").height()*/+260;

    $(document).ready(function(){
        setTimeout(function(){
            $('#emailsmsmessagespendingTable').DataTable({ dom: '<"top"f>rt<"bottom"lip><"clear">'});
        $('.top').css('display','none');
        $('.bottom').css('margin-top','-19px');
        $('#emailsmsmessagespendingTable_length').css('width', '20%').css('display', 'inline');
        $('#emailsmsmessagespendingTable_info').css('display', 'inline').css('width', '30%').css('margin-left', '36%');
        $('#emailsmsmessagespendingTable_paginate').css('width', '30%').css('float', 'right').css('display', 'inline').css('margin-top', '5%');
        },1000);
    });
</script>
@endpush('scripts')
