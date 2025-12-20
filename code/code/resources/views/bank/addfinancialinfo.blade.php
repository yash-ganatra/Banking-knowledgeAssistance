@extends('layouts.app')
@section('content')
@php
    $initial_funding_type = '';
    $initial_funding_date = '';
    $funding_source = '';
    $amount = '';
    $reference = '';
    $bank_name = '';
    $type_of_account = '';
    //$deposit_type = $depositType;
    $days = 0;
    $months = 0;
    $years = 0;
    //$interest_payout = '';
    $maturity = '';
    $td_amount = '';
    $auto_renew = '';
    $emd = 0;
    $emd_name = '';
    $tenure_amount = '';
    $tenure_month = '';
    $tenure_year = '';
    $frequency = '';
    $ifsc_code = '';
    $account_number = '';
    $account_name = '';
    $relationship = '';
    $maturity_flag = 1;
    $self_thirdparty = 'self';
    $others_type = '';
    if(Session::get('accountType') == 4)
    {
        $maturity_flag = 0;
    }
    $maturity_bank_name = '';
    $maturity_ifsc_code = '';
    $maturity_account_number = '';
    $maturity_account_name = '';
    $reason_for_Account_change = '';
    $cancel_cheque_image = '';
    $enable = "display-none";
    $display = '';
    $folder = '';
    $readonly = '';
    $disabled = '';
    $dateDisabled = '';
    $is_review = 0;
    $funding_source_class = '';
    $direct_class = '';
    $mareadonly = '';
    $thirdpartyDisable='';
    $edit_if_details='';
    if($accountType == 4){
      
      $td_amount_readability = '';
    }else{

      $td_amount_readability = 'readonly';
    }
    if($accountType == 3){
      $edit_if_details="disabled";
      $thirdpartyDisable="disabled";
    }
    $page = 4;
@endphp
@if((isset($schemeDetails) && count($schemeDetails) == 0) || ($accountType == 1) || ($accountType == 2)  || ($accountType == 5))
    @php
        $schemeDetails['allow_auto_renewal'] = '';
        $schemeDetails['validation_days'] = '';
        $schemeDetails['min'] = '';
        $schemeDetails['max'] = '';
        $schemeDetails['bulk_retail'] = '';
        $schemeDetails['min_amount'] = '';
        $schemeDetails['max_amount'] = '';
    @endphp
@endif
@if(count($userDetails) > 0)
    @php
        //echo "<pre>";print_r($userDetails['FinancialDetails']);exit;
        $initial_funding_type = $userDetails['FinancialDetails']['initial_funding_type'];
        if($initial_funding_type == 1){
            $cheque_image = $userDetails['FinancialDetails']['cheque_image'];
            if(substr($cheque_image,0,11) == "_DONOTSIGN_"){
                $cheque_image = $cheque_image;
            }else{
                $cheque_image = '_DONOTSIGN_'.$cheque_image;
            }
        }
        if($initial_funding_type == 5){
            $funding_source_class = "display-none";
            if(isset($userDetails['FinancialDetails']['others_type'])){
                $others_type = $userDetails['FinancialDetails']['others_type'];
            }
            if($others_type != "zero"){
                $funding_source = $userDetails['FinancialDetails']['funding_source'];
                $amount = $userDetails['FinancialDetails']['amount'];
            }
        }elseif($initial_funding_type == 3){
            $direct_class = "display-none";
            $account_number = $userDetails['FinancialDetails']['account_number'];
            $amount = $userDetails['FinancialDetails']['amount'];
        }else{
            $initial_funding_date = Carbon\Carbon::parse($userDetails['FinancialDetails']['initial_funding_date'])->format('d-m-Y');
            $amount = $userDetails['FinancialDetails']['amount'];
            $reference = $userDetails['FinancialDetails']['reference'];
            $bank_name = $userDetails['FinancialDetails']['bank_name'];
            $ifsc_code = $userDetails['FinancialDetails']['ifsc_code'];
            $account_number = $userDetails['FinancialDetails']['account_number'];
            $account_name = $userDetails['FinancialDetails']['account_name'];
        }
         if(isset($userDetails['FinancialDetails']['amount'])){
               $amount = $userDetails['FinancialDetails']['amount'];
            }

        if(isset($userDetails['FinancialDetails']['self_thirdparty'])){
        $self_thirdparty = $userDetails['FinancialDetails']['self_thirdparty'];
            }

        if($self_thirdparty == "thirdparty")
        {
            $relationship = $userDetails['FinancialDetails']['relationship'];
        }
        if(in_array($accountType,[3,4])){
            $days = $userDetails['FinancialDetails']['days'];
            if(isset($userDetails['FinancialDetails']['months'])){
                $months = $userDetails['FinancialDetails']['months'];
            }
            if(isset($userDetails['FinancialDetails']['years'])){
                $years = $userDetails['FinancialDetails']['years'];
            }
            if(isset($userDetails['FinancialDetails']['td_amount'])){
                $td_amount = $userDetails['FinancialDetails']['td_amount'];
            }
            if(isset($userDetails['FinancialDetails']['auto_renew'])){
                $auto_renew = $userDetails['FinancialDetails']['auto_renew'];
            }
            $emd = $userDetails['FinancialDetails']['emd'];
            if(isset($userDetails['FinancialDetails']['emd_name'])){
            if($emd == 1)
            {
                $emd_name = $userDetails['FinancialDetails']['emd_name'];
            }
            }
            //$interest_payout = $userDetails['FinancialDetails']['interest_payout'];
            //$tenure_amount = $userDetails['FinancialDetails']['tenure_amount'];
            if(isset($userDetails['FinancialDetails']['maturity'])){
                $maturity = $userDetails['FinancialDetails']['maturity'];
            }
            if(isset($userDetails['FinancialDetails']['frequency'])){
                $frequency = $userDetails['FinancialDetails']['frequency'];
            }
            if(isset($userDetails['FinancialDetails']['tenure_month'])){
                $tenure_month = $userDetails['FinancialDetails']['tenure_month'];
            }
            if(isset($userDetails['FinancialDetails']['tenure_year'])){
                $tenure_year = $userDetails['FinancialDetails']['tenure_year'];
            }
            if(isset($userDetails['FinancialDetails']['maturity_flag']))
            {
                $maturity_flag = $userDetails['FinancialDetails']['maturity_flag'];
            }

            if(isset($userDetails['FinancialDetails']['maturity_bank_name']))
            {
                $maturity_bank_name = $userDetails['FinancialDetails']['maturity_bank_name'];
            }

            if(isset($userDetails['FinancialDetails']['maturity_ifsc_code']))
            {
                $maturity_ifsc_code = $userDetails['FinancialDetails']['maturity_ifsc_code'];
            }

             if(isset($userDetails['FinancialDetails']['maturity_account_number']))
            {
                $maturity_account_number = $userDetails['FinancialDetails']['maturity_account_number'];
            }

            if(isset($userDetails['FinancialDetails']['maturity_account_name']))
            {
                $maturity_account_name = $userDetails['FinancialDetails']['maturity_account_name'];
            }

            if($maturity_flag == 2)
            {

                if(isset($userDetails['FinancialDetails']['reason_for_Account_change'])){
                    $reason_for_Account_change = $userDetails['FinancialDetails']['reason_for_Account_change'];
                }else{
                    $reason_for_Account_change = $userDetails['FinancialDetails']['reason_for_account_change'];
                }

                $cancel_cheque_image = $userDetails['FinancialDetails']['cancel_cheque_image'];
                if(substr($cancel_cheque_image,0,11) == "_DONOTSIGN_"){
                    $cancel_cheque_image = $cancel_cheque_image;
                }else{
                    $cancel_cheque_image = '_DONOTSIGN_'.$cancel_cheque_image;
                }
            }
        }
        $display = "display-none";
        $folder = "attachments";
    @endphp
@endif
@if(isset($cc_etb_details))
    @php
        $readonly_etb_cc = "readonly";
    @endphp
@endif
@if(Session::get('is_review') == 1)
    @php
        $is_review = 1;
        $enable = "";
        $readonly = "readonly";
        $mareadonly = "readonly";
        $disabled = 'disabled';
        $dateDisabled = 'disabled';
        $folder = "markedattachments";
    @endphp
@endif


@if($maturity_flag == 1)
    @php
        $mareadonly = "readonly";
    @endphp
@endif
@if(Session::get('customer_type') == "ETB")
    @php
        $digits = 3;
    @endphp
@endif
@php
$image_mask_blur = "";
$def_blur_image = "";
if($is_review==1){
    $image_mask_blur = "uploaded-img-ovd";
    $def_blur_image = "style=filter:blur(30px);";
}
@endphp
    <div class="pcoded-content1 branch-review">
        <div class="pcoded-inner-content1">
            <!-- Main-body start -->
            <div class="main-body">
                <div class="page-wrapper">
                @if($is_review==1)
                @include("bank.mask_unmask_btn")
                @endif
                    <div class="">
                       <div class="process-wrap active-step4">
                           @include('bank.breadcrumb',['page'=>$page])
                        <div>
                    </div>
                </div>
                <!-- Page-body start -->
            <div class="tab-pane documentstab" id="termdeposit" role="tabpanel">
                <div class="page-body">
                    <div class="tabs" id="tabs">

                        <ul id="tabs-nav" class="nav nav-tabs tabs tabs-default nav-tabs-tb ovdapplicant">
                            <li class="nav-item firsttab">
                                 <a href="#tab" class="nav-link">Initial Funding</a>
                            </li>
                        @if(in_array($accountType,[3,4]))
                            <li class="nav-item secondTab">
                                <a href="#tab2" class="nav-link" id="termdeposittab" data-id="termdeposit" data-toggle="tab" href="#termdeposit" role="tab">Term Deposit</a>
                            </li>
                        @endif
                        </ul>
                        <form id="addInitialFundingForm" method="post" novalidate>
                            <div id="tab" class="tab-content-cust">
                                <div class="card">
                                <span class="visibility_check" id="visibility_check"></span>
                                    <input type="hidden" name="customer_name" id="customer_name" value="{{$customer_name}}">
                                    <input type="hidden" id="formId" name="formId" value="{{$formId}}">
                                    <div class="card-block">
                                    <!-- Row start -->
                                    @if((isset($cc_etb_details)) && ($cc_etb_details['etb_cc'] == "CC") && ($accountType == 3))
                                        @include('bank.callcenterintialfunding')
                                    @else
                                        @include('bank.intialfunding')
                                    @endif
                                                 
                                    <!--Next Tab -->
                                    @if(in_array($accountType,[3,4]))
                                    <div class="row">
                                        <div class="col-md-12 text-center">
                                            <a href="javascript:void(0)" class="btn btn-primary nexttabtermdeposit" id="idProofNext" tab="termdeposit">Next</a>
                                        </div>
                                    </div>
                                    @endif
                                    <!--Next Tab End-->
                                <!-- Row end -->
                                </div>
                            </div>
                            <!--Term Deposit Option  End-->
                        </div>

                        <div class="page-body" id="typeofaccount">
                            <form id="addInitialFundingForm" method="post" novalidate>
                                <div id="tab2" class="tab-content-cust">
                                    <div class="card">

                                        <input type="hidden" id="formId" name="formId" value="{{$formId}}">
                                        <div class="card-block">
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <div class="row">
                                                        <div class="col-lg-12">
                                                            <h4 class="sub-title">Term Deposit</h4>
                                                        </div>
                                                    </div>

                                                   @if($depositType == 1 || $depositType == 2)
                                                        <div class="details-custcol-row col-md-12" id="deposit_type" data-id={{$depositType}}>
                                                    @else
                                                        <div class="details-custcol-row col-md-12 display-none" id="deposit_type" data-id={{$depositType}}>
                                                    @endif

                                                    <!--Second Section-->
                                                    <form id="addInitialFundingForm" method="post" novalidate>
                                                        <div class="row mt-3">
                                                            <div class="col-lg-12">
                                                                <div class="col">
                                                                    <div class="row">


                                                                        <div class="details-custcol-row col-md-1">
                                                                             <!-- <div class="float-right ml-1"> -->
                                                                             <div class="float-end ml-1">
                                                                            <i class="fa fa-calendar fa-2x date-input tenure_calucate" id="tenureduration" aria-hidden="true"></i>
                                                                            </div>
                                                                        </div>
                                                                        <div class="details-custcol-row col-md-2 tenure_fromto">
                                                                            <div class="d-flex tenure_from">
                                                                                <p>From:</p>
                                                                                <span id="tenure_pick_start" class="tenure_date"></span>
                                                                            </div>
                                                                            <div class="d-flex">
                                                                                <p>To: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</p>
                                                                                <span  id="tenure_pick_end"  class="tenure_date"></span>
                                                                            </div>


                                                                        </div>
                                                                        <!--  // old code of fa-fa-referace- commented below line -->
                                                                     <!--  <div class=" mr-2 mt-2">
                                                                        <i class="fa fa-refresh fa-1x date-input tenure_refresh" id="clear_tenureduration" aria-hidden="true"></i>
                                                                      </div> -->

                                                                        <div class="details-custcol-row col-md-1 y-left   {{$funding_source_class}}">
                                                                            <div class="details-custcol-row-top d-flex editColumnDiv">
                                                                                <div class="detaisl-left d-flex align-content-center ">
                                                                     <!--  // 22May23 - For BS5 - below line i changed 344-346 -->
                                                                                <div class=" mr-2 mt-2">
                                                                        <i class="fa fa-refresh fa-1x date-input tenure_refresh" id="clear_tenureduration" aria-hidden="true"></i>
                                                                      </div>
                                                                                    <p class="lable-cus">Years</p>
                                                                                    <span class="{{$enable}}">
                                                                                        @if(isset($reviewDetails['years']))
                                                                                            <i class="fa fa-times"></i>
                                                                                            {{$reviewDetails['years']}}
                                                                                            <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                                        @else
                                                                                            <i class="fa fa-check"></i>
                                                                                        @endif
                                                                                    </span>
                                                                                </div>
                                                                            </div>
                                                                            <div class="details-custcol-row-bootm">
                                                                                <div class="comments-blck">
                                                                                    <input type="text" class="form-control AddFinancialinfoField" table="customer_ovd_details" name="years" id="years" maxlength="2" value="{{$years}}" oninput="this.value = this.value.replace(/[^0-9]/g, '').replace(/(\..*)\./g, '$1');" {{$readonly}}>
                                                                                     <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                                                                                </div>
                                                                            </div>
                                                                        </div>

                                                                        <div class="details-custcol-row col-md-1 ymd-left {{$funding_source_class}}">
                                                                            <div class="details-custcol-row-top d-flex editColumnDiv">
                                                                                <div class="detaisl-left d-flex align-content-center ">
                                                                                    <p class="lable-cus">Months</p>
                                                                                    <span class="{{$enable}}">
                                                                                        @if(isset($reviewDetails['months']))
                                                                                            <i class="fa fa-times"></i>
                                                                                            {{$reviewDetails['months']}}
                                                                                            <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                                        @else
                                                                                            <i class="fa fa-check"></i>
                                                                                        @endif
                                                                                    </span>
                                                                                </div>
                                                                            </div>
                                                                            <div class="details-custcol-row-bootm">
                                                                                <div class="comments-blck">
                                                                                    <input type="text" class="form-control AddFinancialinfoField" table="customer_ovd_details" name="months" id="months" maxlength="3" value="{{$months}}" {{$readonly}} oninput="this.value = this.value.replace(/[^0-9]/g, '').replace(/(\..*)\./g, '$1');">
                                                                                     <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                                                                                </div>
                                                                            </div>
                                                                        </div>

                                                                        <div class="details-custcol-row col-md-1 ymd-left {{$funding_source_class}}">
                                                                            <div class="details-custcol-row-top d-flex editColumnDiv">
                                                                                <div class="detaisl-left d-flex align-content-center ">
                                                                                    <p class="lable-cus" id="display_days">Days</p>
                                                                                    <span class="{{$enable}}">
                                                                                        @if(isset($reviewDetails['days']))
                                                                                            <i class="fa fa-times"></i>
                                                                                            {{$reviewDetails['days']}}
                                                                                            <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                                        @else
                                                                                            <i class="fa fa-check"></i>
                                                                                        @endif
                                                                                    </span>
                                                                                </div>
                                                                            </div>
                                                                            <div class="details-custcol-row-bootm">
                                                                                <div class="comments-blck">
                                                                                    <input type="text" class="form-control AddFinancialinfoField" table="customer_ovd_details" name="days" id="days" maxlength="3" value="{{$days}}" {{$readonly}} oninput="this.value = this.value.replace(/[^0-9]/g, '').replace(/(\..*)\./g, '$1');">
                                                                                     <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                                                                                </div>
                                                                            </div>
                                                                        </div>


                                                                        <div class="details-custcol-row col-md-2 td_amount {{$funding_source_class}}" id="td_amount_div">
                                                                            <div class="details-custcol-row-top d-flex editColumnDiv">
                                                                                <div class="detaisl-left d-flex align-content-center ">
                                                                                    <p class="lable-cus">Amount</p>
                                                                                    <span class="{{$enable}}">
                                                                                        @if(isset($reviewDetails['td_amount']))
                                                                                            <i class="fa fa-times"></i>
                                                                                            {{$reviewDetails['td_amount']}}
                                                                                            <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                                        @else
                                                                                            <i class="fa fa-check"></i>
                                                                                        @endif
                                                                                    </span>
                                                                                </div>
                                                                            </div>
                                                                            <div class="details-custcol-row-bootm">
                                                                                <div class="comments-blck">
                                                                                    <input type="text" class="form-control AddFinancialinfoField" table="customer_ovd_details" name="td_amount" id="td_amount" value="{{$td_amount}}" {{$readonly}} oninput="this.value = this.value.replace(/[^0-9]/g, '').replace(/(\..*)\./g, '$1');" {{$td_amount_readability}}>
                                                                                     <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                                                                                </div>
                                                                            </div>
                                                                        </div>

                                                                        <div class="details-custcol-row col-md-3 mt-3 {{$funding_source_class}}" id="td_amount_autorenew">
                                                                            <div class="details-custcol-row-top d-flex editColumnDiv">
                                                                                <div class="detaisl-left d-flex align-content-center ">
                                                                                    <p class="lable-cus">Auto Renewal</p>
                                                                                    <span class="{{$enable}}">
                                                                                        @if(isset($reviewDetails['auto_renew']))
                                                                                            <i class="fa fa-times"></i>
                                                                                            {{$reviewDetails['auto_renew']}}
                                                                                            <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                                        @else
                                                                                            <i class="fa fa-check"></i>
                                                                                        @endif
                                                                                    </span>
                                                                                </div>
                                                                            </div>
                                                                            <div class="details-custcol-row-bootm">
                                                                                <div class="comments-blck" >
                                                                                    <label class="radio">
                                                                                    <input class="AddFinancialinfoField" type="radio"  name="auto_renew" id="auto_yes"  value="Y" {{ ($auto_renew=="Y")?  "checked" : "checked" }} {{$readonly}} {{$disabled}}>
                                                                                        <span class="lbl padding-8">Yes</span>
                                                                                    </label>
                                                                                    <label class="radio">
                                                                                        <input classs="AddFinancialinfoField" type="radio" name="auto_renew" id="auto_no" value="N" {{ ($auto_renew=="N")? "checked" : "" }} {{$readonly}} {{$disabled}}>
                                                                                        <span class="lbl padding-8">No</span>
                                                                                    </label>
                                                                                    <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <!--dROP DOWN-->
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <div class="details-custcol-row col-md-12 {{$funding_source_class}}" id="bank_name_div">
                                                                    <div class="details-custcol-row-top d-flex editColumnDiv">
                                                                        <div class="detaisl-left d-flex align-content-center ">
                                                                            <p class="lable-cus">Interest payout</p>
                                                                            <span class="{{$enable}}">
                                                                                @if(isset($reviewDetails['interest_payout']))
                                                                                    <i class="fa fa-times"></i>
                                                                                    {{$reviewDetails['interest_payout']}}
                                                                                    <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                                @else
                                                                                    <i class="fa fa-check"></i>
                                                                                @endif
                                                                            </span>
                                                                        </div>
                                                                    </div>
                                                                    <div class="details-custcol-row-bootm">
                                                                        <div class="comments-blck">
                                                                            {!! Form::select('Intrest',$interestpayout,$interest_payout,array('class'=>'form-control interest_payout AddFinancialinfoField',
                                                                                'table'=>'customer_ovd_details','id'=>'interest_payout','name'=>'interest_payout','placeholder'=>'Select Interest Payout')) !!}
                                                                            <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                                                                        </div>
                                                                    </div>
                                                                 </div>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <div class="details-custcol-row col-md-12 {{$funding_source_class}}" id="bank_name_div">
                                                                    <div class="details-custcol-row-top d-flex editColumnDiv">
                                                                        <div class="detaisl-left d-flex align-content-center ">
                                                                            <p class="lable-cus">Maturity Instructions</p>
                                                                            <span class="{{$enable}}">
                                                                                @if(isset($reviewDetails['maturity']))
                                                                                    <i class="fa fa-times"></i>
                                                                                    {{$reviewDetails['maturity']}}
                                                                                    <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                                @else
                                                                                    <i class="fa fa-check"></i>
                                                                                @endif
                                                                            </span>
                                                                        </div>
                                                                    </div>
                                                                    <div class="details-custcol-row-bootm">
                                                                        <div class="comments-blck">
                                                                            {!! Form::select('Maturity',$maturityList,$maturity,array('class'=>'form-control maturity AddFinancialinfoField',
                                                                                'table'=>'customer_ovd_details','id'=>'maturity','name'=>'maturity','placeholder'=>'Select Maturity')) !!}
                                                                            <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="row display-none" id="emd_row">
                                                            <div class="col-md-6 mt-2">
                                                                <div class="details-custcol-row col-md-12 {{$funding_source_class}}" id="bank_name_div">
                                                                    <div class="details-custcol-row-top d-flex editColumnDiv">
                                                                        <div class="detaisl-left d-flex align-content-center ">
                                                                            <p class="lable-cus">Earnest Money Deposit</p>
                                                                            <span class="{{$enable}}">
                                                                                @if(isset($reviewDetails['emd']))
                                                                                    <i class="fa fa-times"></i>
                                                                                    {{$reviewDetails['emd']}}
                                                                                    <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                                @else
                                                                                    <i class="fa fa-check"></i>
                                                                                @endif
                                                                            </span>
                                                                        </div>
                                                                    </div>
                                                                    <div class="details-custcol-row-bootm">
                                                                        <div class="comments-blck mt-1" style="z-index:1">
                                                                            <label class="radio">
                                                                            <input class="AddFinancialinfoField" type="radio"  name="emd" id="emd_yes"  value="1" {{ ($emd=="1")?  "checked" : "" }} onkeypress="return /[a-z]/i.test(event.key)" onkeyup="this.value = this.value.toUpperCase();"  {{$readonly}} {{$disabled}}>
                                                                                <span class="lbl padding-8">Yes</span>
                                                                            </label>
                                                                            <label class="radio">
                                                                                <input classs="AddFinancialinfoField" type="radio" name="emd" id="emd_no" value="0" {{ ($emd=="0")? "checked" : "" }} {{$readonly}} {{$disabled}}>
                                                                                <span class="lbl padding-8">No</span>
                                                                            </label>
                                                                            <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                                                                        </div>
                                                                    </div>
                                                                 </div>
                                                            </div>

                                                            @if($emd == 1)
                                                                <div class="col-md-6" id="emdname">
                                                            @else
                                                                <div class="col-md-6 display-none" id="emdname">
                                                            @endif
                                                                <div class="details-custcol-row col-md-12 {{$funding_source_class}}">
                                                                    <div class="details-custcol-row-top d-flex editColumnDiv">
                                                                        <div class="detaisl-left d-flex align-content-center mt-2 ">
                                                                            <p class="lable-cus">3rd Party Name</p>
                                                                            <span class="{{$enable}}">
                                                                                @if(isset($reviewDetails['emd_name']))
                                                                                    <i class="fa fa-times"></i>
                                                                                    {{$reviewDetails['emd_name']}}
                                                                                    <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                                @else
                                                                                    <i class="fa fa-check"></i>
                                                                                @endif
                                                                            </span>
                                                                        </div>
                                                                    </div>
                                                                    <div class="details-custcol-row-bootm">
                                                                        <div class="comments-blck">
                                                                        <input type="text" class="form-control AddFinancialinfoField" table="customer_ovd_details" name="emd_name" id="emd_name" value="{{$emd_name}}" {{$readonly}} onkeyup="this.value = this.value.toUpperCase();" onkeypress="return /[a-z]/i.test(event.key)">
                                                                            <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!--dROP DOWN-->
                                                    </form>
                                                </div>
                                                <!-- New Row end -->
                                            </div>
                                        </div>

                                        @if($depositType == 2)
                                            <div class="details-custcol-row col-md-12" id="deposit">
                                        @else
                                            <div class="details-custcol-row col-md-12 display-none" id="deposit">
                                        @endif
                                           <!--dROP DOWN-->
                                                    <!-- @include('bank.rd_view') -->
                                                <!--dROP DOWN END-->
                                            </div>
                                        </div>
                                        <?php //echo $auto_renew;exit; ?>
                                        <div class="card-block">
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <div class="row">
                                                        <div class="col-lg-12">
                                                            <h4 class="sub-title">Account Details for Credit of TD Proceeds</h4>
                                                        </div>
                                                    </div>
                                                    <div class="details-custcol-row col-md-12">
                                                        <!--Second Section-->
                                                        <form id="addInitialFundingForm" method="post" novalidate>
                                                            <div class="comments-blck mt-1 radio-selection mt-2 mb-2" style="z-index:1">
                                                                @if(Session::get('accountType') == 4)
                                                                    <label class="chekbox">
                                                                        <input type="radio" class="AddFinancialinfoField" id="credit_flag" name="maturity_flag" value="0" {{ ($maturity_flag == 0) ? 'checked':''}} {{$disabled}}>
                                                                        <span class="lbl padding-8">Credit to the Newly Opened Account</span>
                                                                    </label>
                                                                @endif

                                                                <label class="chekbox">
                                                                    @if(Session::get('role') == '11')
                                                                      <input type="radio" class="AddFinancialinfoField" id="maturity_flag" name="maturity_flag" value="1" {{ ($maturity_flag == 1) ? 'checked':''}} disabled="disabled">
                                                                    @else
                                                                      <input type="radio" class="AddFinancialinfoField" id="maturity_flag" name="maturity_flag" value="1" {{ ($maturity_flag == 1) ? 'checked':''}} {{$disabled}}>
                                                                    @endif
                                                                      <span class="lbl padding-8">As per Initial Funding Details</span>
                                                                </label>

                                                                 <label class="chekbox">
                                                                    @if(Session::get('role') == '11')
                                                                       <input type="radio" class="AddFinancialinfoField" id="edit_flag" name="maturity_flag" value="2" {{ ($maturity_flag == 2) ? 'checked':''}} disabled="disabled">
                                                                    @else
                                                                       <input type="radio" class="AddFinancialinfoField" id="edit_flag" name="maturity_flag" value="2" {{ ($maturity_flag == 2) ? 'checked':''}} {{$disabled}} {{$edit_if_details}}>
                                                                    @endif
                                                                       <span class="lbl padding-8">Edit initial funding details
                                                                </label>
                                                            </div>
                                                            <div class="row mt-3" id="maturity_account_details">
                                                                <div class="col-lg-12">
                                                                    <div class="col">
                                                                        <div class="row">
                                                                            <div class="details-custcol-row col-md-6">
                                                                                <div class="details-custcol-row-top d-flex editColumnDiv">
                                                                                    <div class="detaisl-left d-flex align-content-center ">
                                                                                        <p class="lable-cus">Bank Name</p>
                                                                                        <span class="{{$enable}}">
                                                                                            @if(isset($reviewDetails['maturity_bank_name']))
                                                                                                <i class="fa fa-times"></i>
                                                                                                {{$reviewDetails['maturity_bank_name']}}
                                                                                                <a href="javascript:void(0)" class="text-link editColumn">Edit</a>
                                                                                            @else
                                                                                                <i class="fa fa-check"></i>
                                                                                            @endif
                                                                                        </span>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="details-custcol-row-bootm">
                                                                                    <div class="comments-blck">
                                                                                        {!! Form::select('Bank',$banksList,$maturity_bank_name,array('class'=>'form-control maturity_bank_name AddFinancialinfoField',
                                                                                                'table'=>'customer_ovd_details','id'=>'maturity_bank_name','name'=>'maturity_bank_name','placeholder'=>'Select Bank name')) !!}
                                                                                        <!-- <input type="text" class="form-control AddFinancialinfoField" table="customer_ovd_details" name="maturity_bank_name" id="maturity_bank_name" value="{{$maturity_bank_name}}" {{$readonly}}> -->
                                                                                        <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                                                                                    </div>
                                                                                </div>
                                                                            </div>

                                                                            <div class="details-custcol-row col-md-6">
                                                                                <div class="details-custcol-row-top d-flex editColumnDiv">
                                                                                    <div class="detaisl-left d-flex align-content-center ">
                                                                                        <p class="lable-cus">IFSC Code</p>
                                                                                        <span class="{{$enable}}">
                                                                                            @if(isset($reviewDetails['maturity_ifsc_code']))
                                                                                                <i class="fa fa-times"></i>
                                                                                                {{$reviewDetails['maturity_ifsc_code']}}
                                                                                                <a href="javascript:void(0)" class="text-link editColumn">Edit</a>
                                                                                            @else
                                                                                                <i class="fa fa-check"></i>
                                                                                            @endif
                                                                                        </span>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="details-custcol-row-bootm">
                                                                                    <div class="comments-blck">
                                                                                        <input type="text" class="form-control AddFinancialinfoField" table="customer_ovd_details" name="maturity_ifsc_code" id="maturity_ifsc_code" value="{{$maturity_ifsc_code}}" maxlength="11" onkeyup="this.value = this.value.toUpperCase();" {{$mareadonly}} oninput="this.value = this.value.replace(/[^a-z0-9]/gi, '').replace(/(\..*)\./g, '$1');">
                                                                                         <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                                                                                    </div>
                                                                                </div>
                                                                            </div>

                                                                            <div class="details-custcol-row col-md-6">
                                                                                <div class="details-custcol-row-top d-flex editColumnDiv">
                                                                                    <div class="detaisl-left d-flex align-content-center ">
                                                                                        <p class="lable-cus">Account Number</p>
                                                                                        <span class="{{$enable}}">
                                                                                            @if(isset($reviewDetails['maturity_account_number']))
                                                                                                <i class="fa fa-times"></i>
                                                                                                {{$reviewDetails['maturity_account_number']}}
                                                                                                <a href="javascript:void(0)" class="text-link editColumn">Edit</a>
                                                                                            @else
                                                                                                <i class="fa fa-check"></i>
                                                                                            @endif
                                                                                        </span>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="details-custcol-row-bootm">
                                                                                    <div class="comments-blck">
                                                                                        <input type="text" class="form-control AddFinancialinfoField" table="customer_ovd_details" name="maturity_account_number" id="maturity_account_number" value="{{$maturity_account_number}}" {{$mareadonly}} oninput="this.value = this.value.replace(/[^0-9]/g, '').replace(/(\..*)\./g, '$1');">
                                                                                         <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                                                                                    </div>
                                                                                </div>
                                                                            </div>

                                                                            <div class="details-custcol-row col-md-6">
                                                                                <div class="details-custcol-row-top d-flex editColumnDiv">
                                                                                    <div class="detaisl-left d-flex align-content-center ">
                                                                                        <p class="lable-cus">Account Name</p>
                                                                                        <span class="{{$enable}}">
                                                                                            @if(isset($reviewDetails['maturity_account_name']))
                                                                                                <i class="fa fa-times"></i>
                                                                                                {{$reviewDetails['maturity_account_name']}}
                                                                                                <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                                            @else
                                                                                                <i class="fa fa-check"></i>
                                                                                            @endif
                                                                                        </span>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="details-custcol-row-bootm">
                                                                                    <div class="comments-blck">
                                                                                        <input type="text" class="form-control AddFinancialinfoField" table="customer_ovd_details" name="maturity_account_name" id="maturity_account_name" value="{{$maturity_account_name}}" {{$mareadonly}} onkeyup="this.value = this.value.toUpperCase();" oninput="this.value = this.value.replace(/[^a-z ]/gi, '').replace(/(\..*)\./g, '$1');">
                                                                                         <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                                                                                    </div>
                                                                                </div>
                                                                            </div>

                                                                            @if($maturity_flag == 2)
                                                                                <div class="details-custcol-row col-md-6" id="reason_for_Account_change_div">
                                                                            @else
                                                                                <div class="details-custcol-row col-md-6 display-none" id="reason_for_Account_change_div">
                                                                            @endif
                                                                                <div class="details-custcol-row-top d-flex editColumnDiv">
                                                                                    <div class="detaisl-left d-flex align-content-center ">
                                                                                        <p class="lable-cus">Reason For Account Change</p>
                                                                                        <span class="{{$enable}}">
                                                                                            @if(isset($reviewDetails['reason_for_Account_change']))
                                                                                                <i class="fa fa-times"></i>
                                                                                                {{$reviewDetails['reason_for_Account_change']}}
                                                                                                <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                                            @else
                                                                                                <i class="fa fa-check"></i>
                                                                                            @endif
                                                                                        </span>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="details-custcol-row-bootm">
                                                                                    <div class="comments-blck">
                                                                                        <input type="text" class="form-control AddFinancialinfoField" table="customer_ovd_details" name="reason_for_Account_change" id="reason_for_Account_change" value="{{$reason_for_Account_change}}" {{$readonly}} onkeyup="this.value = this.value.toUpperCase();" oninput="this.value = this.value.replace(/[^a-z0-9 ]/gi, '').replace(/(\..*)\./g, '$1');">
                                                                                         <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                                                                                    </div>
                                                                                </div>
                                                                            </div>

                                                                            @if($maturity_flag == 2)
                                                                                <div class="col-md-4"  id="cancel_cheque_image_div">
                                                                            @else
                                                                                <div class="col-md-4 display-none"  id="cancel_cheque_image_div">
                                                                            @endif
                                                                                <div class="tab-content">
                                                                                    <div class="form-group" id="cancel_cheque_image">
                                                                                        <label>Upload Cancel Cheque Image</label>
                                                                                        <div class="add-document d-flex align-items-center justify-content-around" id="cancel_cheque_photo">
                                                                                            @if(isset($cancel_cheque_image) && ($cancel_cheque_image != ''))
                                                                                                <div id="pf_type_div">
                                                                                                    @if($enable == 'display-none')
                                                                                                       <div class="upload-delete">
                                                                                                            <button type="button" class="delete-icon btn btn-danger btn-icon waves-effect waves-light deleteImage">
                                                                                                                <i class="fa fa-trash" aria-hidden="true"></i>
                                                                                                            </button>
                                                                                                        </div>
                                                                                                    @else
                                                                                                     @if(isset($reviewDetails['reason_for_Account_change']))
                                                                                                            <div class="upload-delete">
                                                                                                                <button type="button" class="delete-icon btn btn-danger btn-icon waves-effect waves-light deleteImage">
                                                                                                                    <i class="fa fa-trash" aria-hidden="true"></i>
                                                                                                                </button>
                                                                                                            </div>
                                                                                                        @else
                                                                                                        @endif
                                                                                                    @endif
                                                                                                    <div class="{{$image_mask_blur}}" {{$def_blur_image}}>
                                                                                                    <img class="uploaded_image" name="cancel_cheque_image" id="document_preview_cancel_cheque" src="{{URL::to('/images'.$folder.'/'.$formId.'/'.$cancel_cheque_image)}}">
                                                                                                </div>
                                                                                                </div>
                                                                                            @endif
                                                                                            @if(isset($cancel_cheque_image) && ($cancel_cheque_image != ''))
                                                                                                <div class="add-document-btn adb-btn-inn display-none">
                                                                                            @else
                                                                                                <div class="add-document-btn adb-btn-inn">
                                                                                            @endif
                                                                                                <button type="button" id="upload_cancel_cheque" class="btn btn-outline-grey waves-effect upload_document" data-toggle="modal"
                                                                                                data-id="cancel_cheque_photo"  data-name="cancel_cheque_image"  data-document="Customer Photo" data-target="#upload_cheque">
                                                                                                    <span class="adb-icon">
                                                                                                        <i class="fa fa-plus-circle"></i>
                                                                                                    </span>
                                                                                                    Upload File
                                                                                                </button>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>

                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                                <!-- New Row end -->
                                            </div>
                                        </div>

                                    </div>
                                </div>
                                <!-- New Row end -->
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </form>


                 </div>
                </div>
            </div>
                <div class="row">
                @if(isset($cc_etb_details['etb_cc']) && $cc_etb_details['etb_cc'] == 'CC')
                    <div class="col-md-12 text-center display-none" id="saveandcontinue">
                        <a  onclick="firstTabshow()" class="btn btn-outline-grey mr-3">Back</a>
                        <a href="javascript:void(0)" class="btn btn-primary financialinfo" id="{{$formId}}">
                            Save and Continue
                        </a>
                    </div>
                @else
                   @if(in_array($accountType,[3,4]))
                    <div class="col-md-12 text-center display-none" id="saveandcontinue">
                        <a href="{{route('addriskclassification')}}" class="btn btn-outline-grey mr-3">Back</a>
                        <a href="javascript:void(0)" class="btn btn-primary financialinfo" id="{{$formId}}">
                            Save and Continue
                        </a>
                    </div>
                    @else
                    <div class="col-md-12 text-center">
                        <a href="{{route('addriskclassification')}}" class="btn btn-outline-grey mr-3">Back</a>
                        <a href="javascript:void(0)" class="btn btn-primary financialinfo" id="{{$formId}}">
                            Save and Continue
                        </a>
                    </div>
                    @endif
                @endif
                </div>
                <!-- Page-body end -->
            </div>
        </div>
    </div>
</div>

<!-- Modal large-->
<div class="modal fade custom-popup" id="upload_cheque" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <button type="button" class="btn-close position-absolute top-0 end-0 px-2" data-bs-dismiss="modal" aria-label="Close">
                <!-- <span aria-hidden="true">&times;</span> -->
            </button>
            <div class="modal-body mt-4">
                <div class="custom-popup-heading document_name">
                    <h1>Upload Document</h1>
                </div>
                <div class="upload-blck">
                <input type="file" class="" id="inputImage" name="file" accept="image/*">
                    <div class="upload-blck-inn d-flex justify-content-center align-items-center">
                        <div class="upload-icon">
                            <img src="{{ asset('assets/images/browse-icon.svg') }}">
                        </div>
                        <div class="upload-con">
                            <h5>Drag & Drop or <span>Browse</span></h5>
                        </div>
                    </div>
                </div>
                <div class="container img-crop-blck image_preview">
                    <div class="row d-flex justify-content-center">
                        <div class="col-md-6">
                            <div class="img-container" >
                                <img id="image" class="preview_image" src="" alt="Crop Picture">
                            </div>
                            <div class="row">
                                <div class="col-md-12 docs-buttons button-page d-flex justify-content-center align-items-center">
                                    <div class="btn-group">
                                        <button type="button" class="rotate-icons" data-method="rotate" data-option="-45" title="Rotate Left">
                                            <span class="docs-tooltip" data-toggle="tooltip" title="$().cropper(&quot;rotate&quot;, -45)">
                                                <span class="fa fa-rotate-left"></span>
                                            </span>
                                        </button>
                                        <button type="button" class="rotate-icons" data-method="rotate" data-option="45" title="Rotate Right">
                                            <span class="docs-tooltip" data-toggle="tooltip" title="$().cropper(&quot;rotate&quot;, 45)">
                                                <span class="fa fa-rotate-right"></span>
                                            </span>
                                        </button>
                                    </div>
                                    <button class="image_crop btn btn-green"> crop </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 display-none" id="img-preview-div">
                            <div class="docs-preview clearfix">
                                <img class="crop_image_preview" src="">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-12 text-center mt-3">
                    <button type="button" id="uploadImage" class="btn btn-lblue saveDocument" disabled>Save document</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script  src="{{ asset('custom/js/financial_details.js') }}"></script>
<script type="text/javascript">
    function firstTabshow(){
        
        $('#tabs-nav li:first-child').addClass('active');
        $('.tab-content-cust').hide();
        $('.tab-content-cust:first').show();
        $('#saveandcontinue').addClass('display-none');
    }
    _globalSchemeDetails = JSON.parse('<?php echo json_encode($schemeDetails); ?>');
    // _accountType = '<?php echo Session::get('accountType'); ?>';
    _accountType = JSON.parse('<?php echo json_encode($accountType); ?>');
    _messageFlag = false;
    _min_ip_reqmt = JSON.parse('<?php echo json_encode($min_ip_reqmt); ?>');
    _role = '<?php echo Session::get('role'); ?>';
    _is_review = JSON.parse('<?php echo json_encode($is_review); ?>');
    deposit_type = JSON.parse('<?php echo json_encode($depositType); ?>');
    _maturity_flag = JSON.parse('<?php echo json_encode($maturity_flag); ?>');

    _ifsc_cc_enable = JSON.parse('<?php echo json_encode($cc_etb_details); ?>');

    var setAutoRenew = function(){
        var autoRenewFlag = '{{$schemeDetails["allow_auto_renewal"]}}';
        if(autoRenewFlag == 'Y'){
              //$('#auto_no').prop("checked", false).trigger("click");
               $("#auto_yes").prop('disabled',false);
               $('input[name="auto_renew"][value="Y"]').prop("checked", false).trigger("click");
               $("#auto_no").prop('disabled',false);
               if (_is_review == 1) {
                  $("#auto_yes").prop('disabled',true);
               }else{
                  $("#auto_yes").prop('disabled',false);
               }
        }
        else{
               $("#auto_yes").prop('disabled',true);
               $("#auto_no").prop('disabled',false);
               $('input[name="auto_renew"][value="N"]').prop("checked", false).trigger("click");
             //$('#auto_no').prop("checked", false).trigger("click");
               if (_is_review == 1) {
                $("#auto_no").prop('disabled',true);
               }else{
                $("#auto_no").prop('disabled',false);
               }
        }
    }

    //rd field days hinddin
    if(_globalSchemeDetails.td_rd == 'RD'){
        $('#days').css('display','none');
        $('#display_days').css('display','none');
        $('#interest_payout').prop('disabled',true);
    }

    var resetTenureDatePicker = function(){
            $('#tenureduration').data('dateRangePicker').clear();
            $('#tenure_pick_start').text('');
            $('#tenure_pick_end').text('');
            $('#days').val(0);
    }



$(document).ready(function(){
    disableRefresh();
    disabledMenuItems();
    var startDate = new Date();
    var day = startDate.getDate() - 90;
    var startDate = new Date(startDate.setDate(day));


    $("#initial_funding_date").datepicker({
        clearBtn: true,
        format: "dd-mm-yyyy",
        startDate: startDate,
        endDate: "today",
    });


    if(_globalSchemeDetails.td_rd == 'RD'){
        $('#interest_payout').val('1');
    }

    if (_is_review == 1) {
        $('#direct_account_number').prop('disabled',true);
    }

	$('#clear_tenureduration').click(function(){
		resetTenureDatePicker();
	});

    $('#tenureduration').dateRangePicker({
        startOfWeek: 'monday',
        separator : ' to ',
        format: 'DD-MMM-YYYY',
        autoClose: true,
		monthSelect: true,
		yearSelect: true,
		getValue: function()
			{
				return $(this).val();
			},
    }).bind('datepicker-closed',function(start,end)
	{
		var dtRange = $('#tenureduration')[0].value
		if(typeof(dtRange) !== "undefined"){
			dtRange = dtRange.split(' ');

			$('#tenure_pick_start').text(dtRange[0]);
			$('#tenure_pick_end').text(dtRange[2]);

			var st = moment(dtRange[0],'DD-MMM-YYYY');
			var en = moment(dtRange[2],'DD-MMM-YYYY');
			var tenureDays = (en.diff(st, 'days'))+1;
			$('#days').val(tenureDays);
			// console.log(tenureDays+' days selected!');
		}
	});

    if(("{{Session::get('customer_type')}}" == "ETB") || (_is_review == 1)){
        addSelect2('account_number','Account Number');
    }

    if(_is_review == 1){
        addSelect2('bank_name','Bank Name',true);
        addSelect2('maturity_bank_name','Bank Name',true);
        addSelect2('type_of_account','Type Of Account',true);
        addSelect2('maturity','Maturity',true);
        addSelect2('frequency','Frequency',true);
        addSelect2('relationship','Relationship',true);
        if(deposit_type == 2){
            addSelect2('interest_payout','Interest Payout',true);
        }
    }else{
        addSelect2('bank_name','Bank Name');
        addSelect2('maturity_bank_name','Bank Name');
        addSelect2('type_of_account','Type Of Account');
        addSelect2('maturity','Maturity');
        addSelect2('frequency','Frequency');
        if('{{$self_thirdparty}}' == "thirdparty"){
            addSelect2('relationship','Relationship',false);
        }else{
            $(".relationship").val('').trigger('change');
            addSelect2('relationship','Relationship',true);
        }
    }


    if(deposit_type == 1){
    addSelect2('interest_payout','Interest Payout',true);
    }else{
        if(deposit_type == 2){
            addSelect2('interest_payout','Interest Payout');
        }
    }
    imageCropper("document_preview_cheque");

    if(_maturity_flag == 0)
    {
        $("#maturity_account_details").addClass('display-none');
    }
    if(_maturity_flag == 1)
    {
        addSelect2('maturity_bank_name','Maturity',true);
    }

    if(_is_review != 1){
    $("#maturity").val('1').trigger('change');
    }
    // $("#maturity").val('1').trigger('change');
    // if($('input[name="auto_renew"]:checked').val() == 'Y')
    // {
    //     $("#maturity option[value='3']").wrap('<span class="title" style="display: none;" />');
    //     if($("#maturity option[value='1']").parent().find('span').length == 0)
    //     {
    //         $("#maturity option[value='1']").unwrap();
    //         $("#maturity option[value='2']").unwrap();
    //     }
    //     $("#maturity").val(1).trigger('change');
    // }

    $("body").on("focusout","#years,#months,#days",function(){
        	if(!_messageFlag){
			_messageFlag = true;
			validateTenure("{{$schemeDetails['validation_days']}}","{{$schemeDetails['min']}}","{{$schemeDetails['max']}}");
			setTimeout(function(){ _messageFlag = false; }, 3000);
		}
    });

    $("body").on("focusout","#td_amount",function(){
        validateBulkRetail("{{$schemeDetails['bulk_retail']}}");
        validateTDamount($("#td_amount").val(),"{{$schemeDetails['min_amount']}}","{{$schemeDetails['max_amount']}}");
    });

    $("body").on("focusout","#tenure_amount",function(){
        validateBulkRetail("{{$schemeDetails['bulk_retail']}}");
        validateTDamount($("#tenure_amount").val(),"{{$schemeDetails['min_amount']}}","{{$schemeDetails['max_amount']}}");
    });

    $("body").on('change',".account_number",function(){
        $("#account_balance").removeClass('display-none');
    });

    // If In Review need not update td_amount!	
	$("#termdeposittab").click(function () {
		if(_is_review == 0 || ($("#td_amount").val() == 0 && $("#amount").val() != 0)){
			$("#td_amount").val($("#amount").val());
		}	
	});	
	// EndIf

    $('.ifsc_code').inputmask("aaaa[*]{100}", {
        "placeholder": "",
        autoUnmask: true,
    });
    
    if(_is_review != 1){
        setAutoRenew();
    }

    $("#account_number").keypress(function (e) {
        if(String.fromCharCode(e.keyCode).match(/[^0-9]/g)) return false;
    });

    if(_globalSchemeDetails.is_emd == 'Y'){
        $('#emd_row').removeClass('display-none');
    }
    if (_globalSchemeDetails.is_emd == 'N') {
        $('#emd_row').addClass('display-none');
    }

    if(_globalSchemeDetails['min'] == _globalSchemeDetails['max'] ){
                    if(_globalSchemeDetails['validation_days'] == 'Days'){
                      $("#months").val(0);
                      $("#days").val(_globalSchemeDetails['max']);
                      $("#days").prop('disabled',true);
                      $("#months").prop('disabled',true);
                      $("#years").prop('disabled',true);
                    }else{
                      $("#days").val(0);
                      $("#months").val(_globalSchemeDetails['min']);
                      $("#days").prop('disabled',true);
                      $("#years").prop('disabled',true);
                      $("#months").prop('disabled',true);

                    }
                }

});

// Show the first tab and hide the rest
$('#tabs-nav li:first-child').addClass('active');
$('.tab-content-cust').hide();
$('.tab-content-cust:first').show();

// Click function
$('#tabs-nav li').click(function(){
  $('#tabs-nav li').removeClass('active');
  $(this).addClass('active');
  $('.tab-content-cust').hide();

  var activeTab = $(this).find('a').attr('href');
  $(activeTab).fadeIn();
  return false;
});
</script>
@endpush