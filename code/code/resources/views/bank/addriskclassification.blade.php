@extends('layouts.app')
@section('content')
@php
    $annual_turnover = '';
    $source_of_funds = array();
    $expected_transactions = '';
    $inward_outward  = '0';
    $approximate_value = '';
    $source_others_comments = '';
    $display = "";
    $enable = "display-none";
    $is_review = 0;
    $country = '';
    $readonly = '';
    $folder = '';
    $disabled = '';
    $basis_categorisation = '';
    $riskDetails = array();
    $AccountIds = array();
    $count = Session::get('no_of_account_holders');
    $accountHoldersCount = Session::get('no_of_account_holders');
    $page = 3;
@endphp
@if(count($userDetails) > 0)
    @php
        if(isset($userDetails['RiskDetails'][1]['annual_turnover']))
        {
            $annual_turnover = $userDetails['RiskDetails'][1]['annual_turnover'];
            if(Session::get('accountType') != 3)
            {
                $source_of_funds = $userDetails['RiskDetails'][1]['source_of_funds'];
                $expected_transactions = $userDetails['RiskDetails'][1]['expected_transactions'];
                if(!is_array($source_of_funds))
                {
                    $source_of_funds = explode(',',$source_of_funds);
                }
                if(in_array('5',$source_of_funds)){
                    $source_others_comments = $userDetails['RiskDetails'][1]['source_others_comments'];
                }            
            }
            $inward_outward = $userDetails['RiskDetails'][1]['inward_outward'];
            $approximate_value = $userDetails['RiskDetails'][1]['approximate_value'];        
        }        
        $display = "display-none";
        $folder = "attachments";
        $riskDetails = $userDetails['RiskDetails'];
        
       // $count = count($userDetails['RiskDetails']);
        $AccountIds = $userDetails['AccountIds'];
    @endphp
@endif
@if(isset($accountCountries))
    @php
        //echo "<pre>";print_r($accountCountries);exit;
        $accountCountries = implode(',',$accountCountries);
    @endphp
@endif
@if(Session::get('is_review') == 1)
    @php
        $is_review = 1;
        $enable = "";
        $readonly = "readonly";
        $folder = "markedattachments";
        $disabled = "disabled";
        if(!is_array($source_of_funds))
                {
        $source_of_funds = explode(',',$source_of_funds);
    }
        //echo "<pre>";print_r($source_of_funds);exit;
    @endphp
@endif
<div class="pcoded-content1 branch-review">
    <div class="pcoded-inner-content1">
        <!-- Main-body start -->
        <div class="main-body">
            <div class="page-wrapper">
                <div class="">
                    <div class="process-wrap active-step3">
                        @include('bank.breadcrumb',['page'=>$page])  
                    </div>
                </div>
            <!-- Page-body start -->
            <div class="page-body">
                <form id="addRsikClassificationForm" method="post" novalidate>
                    <div class="card">
                        <div class="card-block">
                            <div class="row">
                                <div class="col-md-12">
                                    <h4 class="sub-title">CIDD Account Information</h4>
                                </div>
                                <input type="text" id="formId" value="{{$formId}}" style="display: none;">
                            </div>

                            <div class="row">
                                <div class="details-custcol-row col-md-6">
                                    <div class="details-custcol-row-top d-flex editColumnDiv">
                                        <div class="detaisl-left d-flex align-content-center ">
                                            Expected Annual Trans (₹) 
                                            <span role="tooltip" aria-label="Amount of money expected to be Credited in a Year" data-microtip-position="top" data-microtip-size="medium"><i class="fa fa-info-circle" class="tooltip" ></i></span>
                                            <span class="{{$enable}}">
                                                @if(isset($reviewDetails['annual_turnover']))
                                                    <i class="fa fa-times"></i>
                                                    {{$reviewDetails['annual_turnover']}}
                                                    <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                @else
                                                    <i class="fa fa-check"></i>
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                    <div class="details-custcol-row-bootm">
                                        <div class="comments-blck">
                                            @if($accountType == 3)
                                            {{-- {!! Form::select('annual_turnovere',$annualTurnover,$annual_turnover,array('class'=>'form-control  RiskClassificationField',
                                                        'id'=>'annual_turnover','table'=>'risk_classification_details','name'=>'annual_turnover','placeholder'=>'Not applicable for Term Deposit','disabled')) !!} --}}

                                             <input type="text" class="form-control RiskClassificationField annual_turnoverr " table="risk_classification_details" name="annual_turnover" id="annual_turnover"  {{$readonly}} placeholder="Not applicable for Term Deposit" disabled >
                                            @else
                                             {!! Form::select('annual_turnover',$annualTurnover,$annual_turnover,array('class'=>'form-control annual_turnover RiskClassificationField',
                                                        'id'=>'annual_turnover','table'=>'risk_classification_details','name'=>'annual_turnover','placeholder'=>'')) !!}
                                            <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="details-custcol-row col-md-6">
                                    <div class="details-custcol-row-top d-flex editColumnDiv">
                                        <div class="detaisl-left d-flex align-content-center ">
                                            Source of Funds
                                            <span class="{{$enable}}">
                                                @if(isset($reviewDetails['source_of_funds']))
                                                    <i class="fa fa-times"></i>
                                                    {{$reviewDetails['source_of_funds']}}
                                                    <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                @else
                                                    <i class="fa fa-check"></i>
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                    <div class="details-custcol-row-bootm">
                                        <div class="comments-blck">
                                            @if($accountType == 3)
                                             <input type="text" class="form-control RiskClassificationField annual_turnoverr " table="risk_classification_details" name="annual_turnover" id="annual_turnover"  {{$readonly}} placeholder="Not applicable for Term Deposit" disabled >
                                            @else
                                            {!! Form::select('source_of_funds',$sourceOfFunds,$source_of_funds,array('class'=>'form-control source_of_funds RiskClassificationField',
                                                        'id'=>'source_of_funds','table'=>'risk_classification_details','name'=>'source_of_funds','multiple'=>'multiple')) !!}
                                            <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                @if(in_array('5',$source_of_funds))
                                    <div class="details-custcol-row col-md-6" id="source_others_comments_div">
                                @else
                                    <div class="details-custcol-row col-md-6 display-none" id="source_others_comments_div">
                                @endif
                                    <div class="details-custcol-row-top d-flex editColumnDiv">
                                        <div class="detaisl-left d-flex align-content-center ">
                                            Other Comments : 
                                            <span class="{{$enable}}">
                                                @if(isset($reviewDetails['source_others_comments']))
                                                    <i class="fa fa-times"></i>
                                                    {{$reviewDetails['source_others_comments']}}
                                                    <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                @else
                                                    <i class="fa fa-check"></i>
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                    <div class="details-custcol-row-bootm">
                                        <div class="comments-blck">
                                            <input type="text" class="form-control RiskClassificationField" table="risk_classification_details" name="source_others_comments" id="source_others_comments" value="{{$source_others_comments}}" {{$readonly}} oninput="this.value = this.value.replace(/[^a-z0-9 ]/gi, '').replace(/(\..*)\./g, '$1');">
                                            <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                                        </div>
                                    </div>
                                </div>


                                <div class="details-custcol-row col-md-4">
                                    <div class="details-custcol-row-top d-flex editColumnDiv">
                                        <div class="detaisl-left d-flex align-content-center ">
                                            Expected Transactions
                                            <span class="{{$enable}}">
                                                @if(isset($reviewDetails['expected_transactions']))
                                                    <i class="fa fa-times"></i>
                                                    {{$reviewDetails['expected_transactions']}}
                                                    <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                @else
                                                    <i class="fa fa-check"></i>
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                    <div class="details-custcol-row-bootm">
                                        <div class="comments-blck">
                                             @if($accountType == 3)
                                               <input type="text" class="form-control RiskClassificationField annual_turnoverr " table="risk_classification_details" name="annual_turnover" id="annual_turnover"  {{$readonly}} placeholder="Not applicable for Term Deposit" disabled >
                                            @else
                                            {!! Form::select('expected_transactions',$expected_transaction,$expected_transactions,array('class'=>'form-control expected_transactions RiskClassificationField',
                                                        'id'=>'expected_transactions','table'=>'risk_classification_details','name'=>'expected_transactions','placeholder'=>'')) !!}
                                            <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                                            @endif
                                        </div>
                                    </div>
                                </div>



                                <div class="details-custcol-row col-md-4 mt-3">
                                    <div class="details-custcol-row-top mt-1 d-flex editColumnDiv">
                                        <div class="detaisl-left d-flex align-content-center ">
                                            Foreign Inward / Outward Remittence Expected
                                            <span class="{{$enable}}">
                                                @if(isset($reviewDetails['inward_outward']))
                                                    <i class="fa fa-times"></i>
                                                    {{$reviewDetails['inward_outward']}}
                                                    <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                @else
                                                    <i class="fa fa-check"></i>
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                    <div class="details-custcol-row-bootm">
                                        <div class="comments-blck">
                                            @if($accountType == 3)
                                            <label class="radio">
                                                <input class="RiskClassificationField" type="radio" id="inward_outward"  name="inward_outward" value="1" {{ ($inward_outward=="1")?  "checked" : "" }} {{$readonly}} {{$disabled}} disabled>
                                                <span class="lbl padding-8">Yes</span>
                                            </label>
                                            <label class="radio">
                                                <input classs="RiskClassificationField" id="inward_outward"  type="radio" name="inward_outward" value="0" {{ ($inward_outward=="0")? "checked" : "checked" }} {{$readonly}} {{$disabled}} disabled>
                                                <span class="lbl padding-8">No</span>
                                            </label>
                                            @else
                                            <label class="radio">
                                                <input class="RiskClassificationField" type="radio" id="inward_outward"  name="inward_outward" value="1" {{ ($inward_outward=="1")?  "checked" : "" }} {{$readonly}} {{$disabled}}>
                                                <span class="lbl padding-8">Yes</span>
                                            </label>
                                            <label class="radio">
                                                <input classs="RiskClassificationField" id="inward_outward"  type="radio" name="inward_outward" value="0" {{ ($inward_outward=="0")? "checked" : "" }} {{$readonly}} {{$disabled}}>
                                                <span class="lbl padding-8">No</span>
                                            </label>
                                            @endif
                                        </div>
                                    </div>
                                </div>




                    @if($inward_outward == 1)
                        <div class="details-custcol-row col-md-4" id="approximatevalue">
                    @else
                        <div class="details-custcol-row col-md-4 display-none" id="approximatevalue">
                    @endif
                           {{-- <div class="details-custcol-row col-md-6"> --}}
                              <div class="details-custcol-row-top d-flex editColumnDiv">
                                        <div class="detaisl-left d-flex align-content-center ">
                                            Approximate Value  (₹) 
                                            <span class="{{$enable}}">
                                                @if(isset($reviewDetails['approximate_value']))
                                                    <i class="fa fa-times"></i>
                                                    {{$reviewDetails['approximate_value']}}
                                                    <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                @else
                                                    <i class="fa fa-check"></i>
                                                @endif
                                            </span>
                                        </div>                                             
                                    </div>
                                    <div class="details-custcol-row-bootm">
                                        <div class="comments-blck">
                                           {!! Form::select('approximate_value',$approximate_values,$approximate_value,array('class'=>'form-control approximate_value RiskClassificationField',
                                                        'id'=>'approximate_value','table'=>'risk_classification_details','name'=>'approximate_value','placeholder'=>'')) !!}
                                            <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                                        </div>
                                    </div>
                                </div>



                            </div>
                        </div>
                    </div>

                    <div class="tabs">
                        <ul id="tabs-nav" class="nav nav-tabs tabs tabs-default nav-tabs-tb">
                            @for($i = 1; $i <= $count;$i++)
                                @if($i == 1)
                                    <li class="nav-item"  onclick="registerTabEvent({{$i}})">
                                        @if($accountDetails['constitution'] == 'NON_IND_HUF')
                                        <a href="#tab{{$i}}" class="nav-link">Karta/Manager</a>
                                        @else
                                        <a href="#tab{{$i}}" class="nav-link">Primary Account Holder</a>
                                        @endif
                                    </li>
                                @else
                                    <li class="nav-item"  onclick="registerTabEvent({{$i}})">
                                        @if($accountDetails['constitution'] == 'NON_IND_HUF')
                                        <a href="#tab{{$i}}" class="nav-link" class="nav-link" data-id="nextapplicant-{{$i-1}}" data-toggle="tab" role="tab">HUF</a>
                                        @else
                                        <a href="#tab{{$i}}" class="nav-link" class="nav-link" data-id="nextapplicant-{{$i-1}}" data-toggle="tab" role="tab">Applicant{{$i}}</a>
                                    @endif
                                    </li>
                                @endif
                            @endfor
                        </ul> <!-- END tabs-nav -->
                        <div id="tabs-content-cust" class="tabs-content-cust">
                            @for($i = 1 ;$i <= $count;$i++)
                                @include('bank.addriskclassificationapplicant',['riskDetails'=>$riskDetails,
                                            'AccountIds'=>$AccountIds,'i'=>$i])
                            @endfor
                        </div> <!-- END tabs-content -->
                    </div> <!-- END tabs -->
                </form>
            </div>
           {{--  <div class="row">
                <div class="col-md-12 text-center">
                    <a href="{{route('addovddocuments')}}" class="btn btn-outline-grey mr-3">Back</a>
                    <a href="javascript:void(0)" class="btn btn-primary riskClassification" id="{{$formId}}">Save and Continue</a>
                </div>
            </div> --}}
        </div>
    <!-- Page-body end -->
    </div>
</div>               
@endsection
@push('scripts')
<script  src="{{ asset('custom/js/risk_details.js') }}"></script>
<script type="text/javascript">
    
    _is_etb = "<?php echo Session::get('customer_type'); ?>";
    _no_of_account_holders = "<?php echo Session::get('no_of_account_holders'); ?>";
    _is_review = "<?php echo Session::get('is_review'); ?>";
    _customer_type = JSON.parse('<?php echo json_encode($riskaccountDetails['is_new_customer']); ?>');
    _customerdetails = JSON.parse('<?php echo json_encode($customerOvdDetails); ?>');
    _checkNtbEtb = JSON.parse('<?php echo json_encode($checkEtbNtb); ?>'); 

    _globalCountryRisk = {};

    $(document).ready(function(){
        disableRefresh();
        disabledMenuItems();


        var disabled = false;
        if('{{$is_review}}' == 1){
            disabled = true;
        }
        addSelect2('annual_turnover','Annual Turnover',disabled);
        addSelect2('source_of_funds','Source of Funds',disabled);
        addSelect2('expected_transactions','Expected Transactions',disabled);
        addSelect2('approximate_value','Approximate Value',disabled);        
        addSelect2('basis_categorisation','Categorisation',true);
        var applicatSeqId = 1;
        $.each(_checkNtbEtb,function(key,value){
            // if("{{Session::get('customer_type')}}" == "ETB"){
            if(value.is_new_customer == "0"){
                addSelect2('customer_type-'+applicatSeqId,'Customer Type',true);
                addSelect2('country_name-'+applicatSeqId,'Country Name',true);
                addSelect2('residence-'+applicatSeqId,'Residence for Tax Purpose',true);
                addSelect2('country_of_birth-'+applicatSeqId,'Country of Birth',true);
                addSelect2('citizenship-'+applicatSeqId,'Citizenship',true) 
                addSelect2('gross_income-'+applicatSeqId,'Gross Income',true);
                addSelect2('networth-'+applicatSeqId,'Networth',true);
                addSelect2('occupation-'+applicatSeqId,'Occupation',true);
                addSelect2('education-'+applicatSeqId,'Education',true);
             $('.RiskClassificationForm').find('.card-block').css('pointer-events', '');

        }else{
                addSelect2('customer_type-'+applicatSeqId,'Customer Type',disabled);
                addSelect2('country_name-'+applicatSeqId,'Country Name',disabled);
                addSelect2('residence-'+applicatSeqId,'Residence for Tax Purpose',disabled);
                addSelect2('country_of_birth-'+applicatSeqId,'Country of Birth',disabled);
                addSelect2('citizenship-'+applicatSeqId,'Citizenship',disabled) 
                addSelect2('gross_income-'+applicatSeqId,'Gross Income',disabled);
                addSelect2('networth-'+applicatSeqId,'Networth',disabled);
                addSelect2('occupation-'+applicatSeqId,'Occupation',disabled);
                addSelect2('education-'+applicatSeqId,'Education',disabled);
        }
            applicatSeqId++;
        });
        
        // if (_customer_type == '0') {
        //      $('.RiskClassificationForm').find('.card-block').css('pointer-events', '');
        // }
        
    });
    
    function registerTabEvent(id) {
        if (_is_review == "1" ) {// previous screen validation not required for review page
            return true;
        }
        //console.log('registerEvent'+id);
        if(id=="1"){
            return true;
        } 
        if(typeof _riskClassification_form_check != "undefined") {
           if(_riskClassification_form_check[id-2]['riskClassification_account-'+(id-1)]!= true){
              //  console.log(_riskClassification_form_check[id-2]['riskClassification_account-'+(id-1)]);
              $.growl({message: "Please validate previous screen for Applicant " + (id-1)},{type: "warning",allow_dismiss:false});
              return false;
           }           
         
        }
    }

    $("body").on("change","#source_of_funds",function(){
        if(jQuery.inArray( "5", $(this).val() ) != -1){
            $("#source_others_comments_div").show();
        }else{
            $("#source_others_comments_div").hide();
        }
        $.each(_checkNtbEtb,function(key,value){
            // if("{{Session::get('customer_type')}}" != "ETB"){
            if(value.is_new_customer != "0"){
            // validateSourceofFunds($('#source_of_funds').val());
        }
    });
    });
     
    if($(".customer_type").val() == ''){

      $(".customer_type").val(18).trigger('change.select2');
    }
</script>

<script>
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