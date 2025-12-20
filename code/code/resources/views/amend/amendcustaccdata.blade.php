@extends('layouts.app')
@php
$page = 2;

$acctLbl = '';
$acctNo = '';
$displayAcc = 'none';
if($getAccountNo != ''){
    $acctLbl = 'Account Number';
    $acctNo = $getAccountNo;
    $displayAcc = "";
}

@endphp
<style>
    
.step-4::before{
    display: none;
}
.step-4::after{
    content: '4';
}
.row-line-height{
    line-height: 40px;
}

</style>
@section('content')

<div class="dnone-ryt branch-review">
    <div class="pcoded-content1">
        <div class="pcoded-inner-content1">
            <!-- Main-body start -->
            <div class="main-body">
                <div class="page-wrapper">
                    <div class="">
                        <div class="process-wrap active-step1" style="margin-left:340px">
                        @include('amend.amendbreadcrum',['page'=>$page])
                        </div>
                    </div>
                     <!-- Page-body start -->
                         <div class="page-body mx-auto" style="width:100%;">
                            <div class="card">
                                <div class="card-block" style="padding:20px;">
                                    <div class="row" style="margin-left:10%;line-height: 40px;"> 
                                        <div class="col-md-2">
                                            <span>Customer ID</span>
                                        </div>
                                        <div class="col-md-2" style="display:{{$displayAcc}}">
                                            <span>{{$acctLbl}}</span>
                                        </div>
                                        <div class="col-md-2">
                                            <span>Customer Name</span>
                                        </div>
                                    </div>
                                    <div class="row" style="margin-left:10%">
                                        <div class="col-md-2">
                                            <span id="getcust_acctNo">{{$cust_acctNo}}</span>
                                        </div>
                                        <div class="col-md-2" style="display:{{$displayAcc}}">
                                            <span id="accountNumber">{{$acctNo}}</span>
                                        </div>
                                        <div class="col-md-2">
                                            <span id="customerName">{{$getcustName}}</span>
                                        </div>
                                    </div>
                                </div>
                            </div> 
                            <div class="card">
                                <div class="card-block">
                                    <div class="container" style="margin-bottom: 20px;">
                                        <div class="row">
                                            <div class="col-md-12">
                                             <center><h2>Select the options that you would like to update</h2></center>
                                            </div>
                                        </div>
                                    </div>
                                @include('amend.amenditem')
                                    <div class="d-flex row-12" style="padding-top:50px">
                                        <div class="col-md-12">
                                            <center><a href="javascript:void(0)" class="btn btn-primary mb-3" id="amendItem">Save and Continue </a></center>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
@push('scripts')
<script src="{{ asset('custom/js/amend_rules.js') }}"></script>
@endpush

