@extends('layouts.app')
@section('content')

<link rel="stylesheet" type="text/css" href="{{ asset('icon/icofont/css/icofont.css') }}">
<style type="text/css">
    #templatesTable{width: 100%!important; }
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
                        <!-- <div class="col-md-6 add-template text-left"> -->
                            <div class="col-md-6 text-left"> 
                            <h4>Templates List</h4>
                        </div>
                        <div class="col-md-6">
                            <a href="{{route('channelid.addtemplate')}}" type="button" class="btn btn-yellow waves-effect waves-light float-end">
                                Add Template
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card table-top">
                    <div class="card-block table-border-style card-block-padding">
                        <div class="table-responsive">
                            <table class="table table-custom" id="templatesTable">
                                <thead class="thead">
                                    <tr>
                                        <th>ID</th>
                                        <th>ACTIVITY CODE</th>
                                        <th>MESSAGE_TYPE</th>
                                        <th>MESSAGE</th>
                                        <th>IS ACTIVITY</th>
                                        <th>FUNCTION NAME</th>
                                        <th>ACTIVITY</th>
                                        <th>EDIT</th>
                                    </tr>
                                </thead>
                                @php
                                    echo $html;
                                @endphp
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
<script  src="{{ asset('custom/js/channelid_emailsms.js') }}"></script>
<script type="text/javascript">
$(document).ready(function(){
    $('#templatesTable').DataTable({"dom": '<"top"f>rt<"bottom"lip><"clear">'});
    $('#templatesTable_length').css('width', '20%').css('display', 'inline');
    $('#templatesTable_info').css('display', 'inline').css('width', '30%').css('margin-left', '20%');
    $('#templatesTable_paginate').css('width', '30%').css('float', 'right').css('display', 'inline').css('margin-top', '5%');
});
</script>
@endpush