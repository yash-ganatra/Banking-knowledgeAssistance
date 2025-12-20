@extends('layouts.app')
@php
$page = 1;
$cust_name = '';
$cust_acct = '';
$active_status = '';
$hidden = 'hidden';
$user_status_active = '';
$messages = '';
$height = '25%';

   // customer details fetch
    // echo "<pre>";print_r($getCustomerDetails);exit;
    if(isset($getCustomerDetails) && count($getCustomerDetails)>0){
        $cust_name = $getCustomerDetails['CUST_NAME'];
        $cust_Id = $getCustomerDetails['CUST_ID'];
        $cust_acct = $cust_Id;
        $user_status_active = 'user_status_gray';
    }
    //account details fetch
    // echo "<pre>";print_r($getAccountDetails);exit;
    $messages = "NA";
    if(isset($getAccountDetails) && count($getAccountDetails)>0){
        $cust_name = $getAccountDetails['CUSTOMER_NAME'];
        $active_status = $getAccountDetails['ACCT_STATUS'];
            if($active_status == 'ACTIVE'){
                $user_status_active = 'user_status_active';
                $messages = 'ok';
                $hidden = 'display';
            }else{
                $user_status_active = 'user_status_inactive';
                $messages = 'Not ok';
                $hidden = 'display';
            }
    }

    if($cust_acct != ''){
        $hidden ='visible';
        $height = '';
        $getCustSess = '';
    }else{
        $hidden ='hidden';
        $getCustSess = Session::get('currCustomerNo');
    }   

    if($getCustSess != ''){
        $hidden ='visible';
        $height = '55%';
    }

//check minortomajor
    if(isset($getMinorData) && $getMinorData == ''){
        $minorAcDeactive = 'user_status_inactive';
        $minorMessasge = 'Applicable';
    }else{
        $minorAcDeactive = 'user_status_active';
        $minorMessasge = 'Not Applicable';
    }
//ekyc 
    if(isset($getEycData) && $getEycData == ''){
        $ekycstatus = 'user_status_active';
        $ekycmessage = 'Good to proceed';
        $getCustRefnumber =  $getCustRefnumber;
    }else{
        $ekycstatus = 'user_status_inactive';
        $ekycmessage = 'Over Due';
        $getCustRefnumber = '';
    }

    
@endphp
<style>
    
.step-4::before{
    display: none;
}
.step-4::after{
    content: '4';
}


  /* Switch ekyc  css start */ 
.switch-ekyc {
  position: relative;
  display: inline-block;
  width: 130px;
  height: 34px; text-transform: uppercase;
}

.switch-ekyc b span{
    margin-left: 9px;
}

.switch-ekyc b { font-weight: normal;  }

.switch-ekyc input {display:none;}

.slider-ekyc {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
   background-color: #2ecc71;
  
  -webkit-transition: .4s;
  transition: .4s;
}

.slider-ekyc:before {
  position: absolute;
  content: "";
  height: 26px;
  width: 26px;
  left: 4px;
  bottom: 4px;
  background-color: white;
  -webkit-transition: .4s;
  transition: .4s;
}

input:checked + .slider-ekyc {
   background-color: whitesmoke;
}

input:focus + .slider-ekyc {
  box-shadow: 0 0 1px #2196F3;
}

input:checked + .slider-ekyc:before {
  -webkit-transform: translateX(96px);
  -ms-transform: translateX(96px);
  transform: translateX(96px);
}

.on
{
  display: none;
}

.off
{
  color: white;
  position: absolute;
  transform: translate(-50%,-50%);
  top: 50%;
  left: 47%;
  font-size: 13px;
  
}

.on
{
  color: black;
  position: absolute;
  transform: translate(-50%,-50%);
  top: 50%;
  /*left: 40%;*/ width: 100%; left: 76px;
 font-size: 13px;

}

input:checked+ .slider-ekyc .on
{display: block;
}

input:checked + .slider-ekyc .off
{display: none;}


.slider-ekyc.round {
  border-radius: 34px;
}

.slider-ekyc.round:before {
  border-radius: 50%;}  

</style>
@section('content')
<style>
    .user_status_inactive{
        background-color: red;
    }
    .user_status_gray{
    background-color: darkgray;
    height: 16px;
    width: 16px;
    border: none;
    border-radius: 50%;
    cursor: pointer;
 }
</style>
<div class="dnone-ryt ekyc-review">
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
                    <div class="page-body mx-auto" style="width:90%;"> 
                        <div class="card" style="height:{{$height}}">
                            <div class="card-block">
                                <div class="row-12">
                                    <h5 style="text-align:center;">Please provide Customer ID or Account Number</h5>
                                </div>
                            </div>
                            <div class="card-block mx-auto" style="padding:0px;">
                                <div class="row">
                                    <div class="col-12 d-flex">
                                        <input type="text" class="form-control" id="customer_id" name="customer_id" placeholder="" 
                                        oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');" maxlength="14" style="height:70%;width:300px;">
                                    &nbsp;&nbsp;&nbsp;
                                        <a href="javascript:void(0)" class="btn btn-primary mb-3" id="cust_acct" style="padding: 8px;">Search</a>
                                    </div>
                                </div>
                            </div>
                            <div class="container" id="userDetailsTab">
                                <div class="card-block" style="padding:1px;visibility:{{$hidden}};">
                                    <hr>
                                    <div class="row" style="margin-left:10%;line-height: 40px;"> 
                                        <div class="col-md-2">
                                            <span>Customer ID</span>
                                        </div>
                                        <div class="col-md-3">
                                            <span>Customer Name</span>
                                        </div>
                                        <div class="col-md-2">
                                            <span>KYC Refresh</span>
                                        </div>
                                         <div class="col-md-2">
                                            <span>Account Active</span>
                                        </div>
                                        <div class="col-md-2">
                                            <span>Minor to Major</span>
                                        </div>
                                    </div>
                                    <div class="row" style="margin-left:10%">
                                        <div class="col-md-2">
                                            @if($cust_acct != '')
                                                <span id="getcust_acctNo">{{$cust_acct}}</span>
                                            @else
                                                <span id="getcust_acctNo">{{$getCustSess}}</span>
                                            @endif
                                        </div>
                                        <div class="col-md-3">
                                            <span>{{$cust_name}}</span>
                                        </div>
                                        <div class="col-md-2 d-flex">
                                            @if($cust_acct != '')
                                                <button class="{{$ekycstatus}}" style="margin-left:1px;margin-top: 4px;"></button>&nbsp;&nbsp;
                                                <span id="getekycStatus">{{$ekycmessage}}</span>
                                                <span  id="getReferenceNo" hidden>{{$getCustRefnumber}}</span>
                                            @endif

                                        </div>
                                            <div class="col-md-2" {{$hidden}}>
                                                <button class="{{$user_status_active}}" style="margin-left:1px;margin-top: 4px;"></button>&nbsp;&nbsp;
                                               <span id="accActStatus">{{$messages}}</span>

                                            </div>
                                        <div class="col-md-2 d-flex">
                                            @if($cust_acct != '')
                                               <button class="{{$minorAcDeactive}}" style="margin-left:1px;margin-top: 4px;"></button>&nbsp;&nbsp;
                                             <span id="getminorStatus"> {{$minorMessasge}}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="card-block" style="visibility:{{$hidden}};">
                                    <hr><br>
                                    <div class="row">
                                       <div class="col-md-2" id="amendekyc" style="margin-left:23%">
                                            <label class="switch-ekyc">
                                                <input type="checkbox" id="risk_order">
                                                <div class="slider-ekyc round">
                                                    <b class="off">E-KYC</b>
                                                    <b class="on" style="padding-right: 30px;">No E-KYC</b>
                                                </div>
                                            </label>
                                        </div>

                                        <div class="col-md-3 withEyc">
                                             <input type="text" class="form-control" id="ekyc_number" name="ekyc_number" placeholder="Enter E-KYC Number" oninput="this.value = this.value.replace(/[^0-9a-zA-Z]/g, '').replace(/(\..*)\./g, '$1');" maxlength="20">
                                        </div>
                                        <div class="col-md-1 withEyc">
                                            <a href="javascript:void(0)" class="btn btn-primary mb-3" id="getekycNo" style="padding:8px;">Continue</a>
                                        </div>
                                        <div class="col-md-3 withoutEkyc">
                                        </div>
                                        <div class="col-md-1 withoutEkyc" style="visibility:hidden;">
                                           <center> <a href="javascript:void(0)" class="btn btn-primary mb-3" id="selectcustId" style="padding:8px;">Continue
                                        </a></center>
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
<script>
 $(document).ready(function(){
    $('#risk_order').on('change',function(){
        var ekycChecked = $('#risk_order:checked').val();
        if(ekycChecked == undefined){

            $('.withoutEkyc').css('display','none');
            $('.withEyc').css('display','block');

        }else{
            $('.withEyc').css('display','none');
            $('.withoutEkyc').css('display','block');


        }
    })
 });
</script>

@endpush