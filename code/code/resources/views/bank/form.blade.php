@inject('provider','App\Helpers\labelCode')
@php
$enc_fields = ['Aadhaar Photocopy','Passport','Voter ID','Driving Licence'];
$role = Session::get('role');
$ccDefaultSolId = config('constants.CC_ACCOUNT_OPENING_DEFAULT_SOL_ID');
$is_review = 0;
$disabled = '';
$segment_list = '';
$reason_for_account_open = '';
$leadGenerated = config('constants.SPECIAL_SUBMISSION_DECLARATION.LEAD_GENERATED');
$distFromBranch = config('constants.SPECIAL_SUBMISSION_DECLARATION.DIST_FROM_BRANCH');
$custMeetingLocations = config('constants.SPECIAL_SUBMISSION_DECLARATION.CUSTOMER_MEETING_LOCATION');
$reasonForAccntOpening = config('constants.SPECIAL_SUBMISSION_DECLARATION.REASON_FOR_ACCOUNT_OPEN');
$meetingdate = '';
$leadgenerated = '';
$distfrombranch = '';
$custmeetinglocations = '';
$reasonforaccntopening = '';
$token = '';
$tokenReason = '';
$tokenlead = '';
$tokendistance = '';
$specialDeaclarationIds = array();


$get_schemeCode = $accountDetails['scheme_code'];
$label_entity_details = $provider::getLabel($get_schemeCode,'label_entity_details');
$label_entity_name = $provider::getLabel($get_schemeCode,'label_entity_name');
$label_proof_of_entity_address = $provider::getLabel($get_schemeCode,'label_proof_of_entity_address');
$label_entity_address = $provider::getLabel($get_schemeCode,'label_entity_address');

if(isset($entityDetails) && isset($entityDetails['proof_of_entity_address'])){
	$label_proof_of_entity_address_name = $provider::getKYClistItem($get_schemeCode,$entityDetails['proof_of_entity_address']);	
}
	
$schemeCodeName = strtoupper($accountDetails['scheme_code']) == 'SB129' ? 'CA229' : strtoupper($accountDetails['scheme_code']);

    if($accountDetails['account_type'] == 'Current'){
        $custMeetingLocations[6] = 'Registered Address';
    }


 $schemeDetails = Session::get('td_schemeData');
    if(!isset($schemeDetails['id']))
    {
        $schemeDetails = Session::get('schemeData');
    }
    $page = 7;
    //echo "<pre>";print_r($userDetails);exit;
    $custovd = $userDetails[0]['customerOvdDetails'];
    switch ($accountDetails['scheme_code']) {
        case 'SB118':
            $specialCase = array('Label Code'=> $custovd->label_code); 
            break;
        
        case 'SB124':
            $specialCase = array('Elite Account Number (Last 8 digits)'=> $custovd->elite_account_number); 
            break;
         case 'CA224':
            $specialCase = array('Elite Account Number (Last 8 digits)'=> $custovd->elite_account_number); 
            break;
        default:
            if ($custovd->customer_account_type == 3) {
                $specialCase = array('HRMS Number'=> $custovd->empno); 
            }else{
                $specialCase = array();
            }
            break;
    }
    if(isset($cifDeclarationDetails) && $cifDeclarationDetails != ''){
        $meetingdate = Carbon\Carbon::parse($cifDeclarationDetails->meeting_date)->format('d-m-Y');
        $leadgenerated = $cifDeclarationDetails->generated_lead;
        $distfrombranch = $cifDeclarationDetails->dist_from_branch;
        $custmeetinglocations = $cifDeclarationDetails->customer_location;
        $reasonforaccntopening = $cifDeclarationDetails->account_open_reason;
    }
    
    $acc_name = isset($customerOvdDetails[0]->customer_full_name) && $customerOvdDetails[0]->customer_full_name != '' ? $customerOvdDetails[0]->customer_full_name : '';
    
@endphp
@if(Session::get('is_review') == 1)
    @php
        $is_review = 1;
        $disabled = 'disabled';
        $status = $accountDetails['application_status'];
    @endphp
@endif
@php
$i_ao_trac=false;
if(isset($is_aof_tracker)){
    if($is_aof_tracker){
    $i_ao_trac=true;
    }
}
@endphp
@php
$image_mask_blur = "";
$def_blur_image = "";
if($is_review==1 || $i_ao_trac){
    $image_mask_blur = "uploaded-img-ovd";
    $def_blur_image = "style=filter:blur(30px);";
}

$is_huf_display = false;
 if($accountDetails['constitution'] == 'NON_IND_HUF'){
    $is_huf_display = true;
 }
@endphp

@if($i_ao_trac)
<style>
@media print{
    #maskfields , #unmaskfields{
        display:none !important;
    }
}
</style>
@endif

<div class="pcoded-content1">
     <form method="post" id="submissionForm" action="javascript:void(0)">
    <div class="pcoded-inner-content1">
        <!-- Main-body start -->
        <div class="main-body">
            <div class="page-wrapper">
                @if($is_review==1 || $i_ao_trac)
                @include("bank.mask_unmask_btn")
                @endif
                @if(!$is_aof_tracker)
                    <div class="">
                        <div class="process-wrap active-step7">
                            @include('bank.breadcrumb',['page'=>$page])
                    </div>
                @endif
                <body bgcolor="#ffffff">
                    <table class="pdf-table" style="font-family:Franklin Gothic Book,arial, sans-serif; font-size:14px; margin-top: 20px;" width="1000px" align="center">
                        <tbody>
                            <tr>
                                <td>
                                    <table style="margin-bottom:10px;" width="100%">
                                        <tbody>
                                            <tr>
                                                <td>
                                                    <font style="font-size: 25px; color: #364fcc;">Account Opening Form</font>
                                                    <br>For Individuals<br>
                                                    Form No:
                                                    <span id="application_no">{{strtoupper($accountDetails['aof_number'])}}</span><br>
                                                </td>
                                                <td style="text-align: right!important;" align="right">
                                                    <img src="{{ asset('assets/images/dcb-logo-blue.png') }}">
                                                    <br>
                                                    <div style="margin-top: 5px;margin-left: 89px;" align="right">{!!DNS1D::getBarcodeHTML($accountDetails['aof_number'], 'I25')!!}</div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <span id="cidd">
                                        <table style=" border: 1px solid #f1f1f1;" width="100%" cellspacing="0" cellpadding="0" bgcolor="#F2F2F2">
                                            <tbody>
                                                <tr>
                                                    <td style="color: #364fcc; padding-left: 10px; font-size: 20px; padding-left: 10px!important;" height="30">
                                                        RELATIONSHIP FORM
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td style="padding-left: 10px!important;">
                                                        I/We hereby apply for a relationship with your Bank under which I/We wish to open an account(s).
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td height="10"></td>
                                                </tr>
                                                <tr>
                                                    <td style="padding-left: 10px!important;">
                                                        <table style="padding-left: 10px; padding-right: 10px;" width="100%">
                                                            <tbody>
                                                                <tr>
                                                                    <td style="line-height: 30px;" width="20%">
                                                                        Account Type
                                                                    </td>
                                                                    @if(!$is_huf_display)
                                                                    <td style="line-height: 30px;" width="20%">
                                                                       Number of applicants
                                                                    </td>
                                                                    @endif
                                                                </tr>
                                                                <tr>
                                                                    <td style="line-height: 30px;" width="30%">
                                                                        <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:80%; height:100%; display: inline-block; color:#0070C0">
                                                                            {{strtoupper($accountDetails['account_type'])}}
                                                                        </span>
                                                                    </td>
                                                                    @if(!$is_huf_display)
                                                                    <td style="line-height: 30px;" width="30%">
                                                                        <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:80%; height:100%; display: inline-block; color:#0070C0">
                                                                            {{strtoupper($accountDetails['no_of_account_holders'])}}
                                                                        </span>
                                                                    </td>
                                                                    @endif
                                                                </tr>
                                                                <tr>
                                                                    <td height="8"></td>
                                                                </tr>
                                                                <tr>
                                                                    <td style="line-height: 30px;" width="20%">
                                                                        Mode of Operation
                                                                    </td>
                                                                    <td style="line-height: 30px;" width="20%">
                                                                        Scheme
                                                                    </td>
                                                                </tr>
                                                                 <tr>
                                                                    <td style="line-height: 30px;" width="30%">
                                                                        <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:80%; height:100%; display: inline-block; color:#0070C0">
                                                                            {{strtoupper($accountDetails['mode_of_operation'])}}
                                                                        </span>
                                                                    </td>
                                                                    <td style="line-height: 30px;" width="30%">
                                                                        <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:80%; height:100%; display: inline-block; color:#0070C0">
                                                                            @if($accountDetails['account_type_id'] == 3)
                                                                                {{strtoupper($accountDetails['tdscheme_code'])}}
                                                                            @else
                                                                                {{$schemeCodeName}}
                                                                            @endif
                                                                        </span>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td height="8"></td>
                                                                </tr>
                                                                @if($accountDetails['account_type'] == 'Current')
                                                                    @php
                                                                        if($accountDetails['flow_tag_1'] == 'INDI'){
                                                                            $labelPropInd = 'Individual';
                                                                        }else{
                                                                            $labelPropInd = 'Proprietorship';
                                                                        }
                                                                    @endphp
                                                                    <tr>
                                                                        <td style="line-height: 30px;" width="20%">
                                                                            Proprietorship/Individual
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td style="line-height: 30px;" width="30%">
                                                                            <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:80%; height:100%; display: inline-block; color:#0070C0">
                                                                                {{strtoupper($labelPropInd)}}
                                                                            </span>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td height="8"></td>
                                                                    </tr>
                                                                @endif
                                                                @if(isset($accountDetails['td_scheme_code']))
                                                                    <tr>
                                                                        <td style="line-height: 30px;" width="20%">
                                                                            TD Scheme
                                                                        </td>
                                                                    </tr>
                                                                     <tr>
                                                                        <td style="line-height: 30px;" width="30%">
                                                                            <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:80%; height:100%; display: inline-block; color:#0070C0">
                                                                                {{strtoupper($accountDetails['td_scheme_code'])}}
                                                                            </span>
                                                                        </td>
                                                                    </tr>
                                                                @endif
                                                                <tr>
                                                                    <td height="8"></td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td height="20"></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </span>
                                </td>
                            </tr>
                            @if(count($userDetails) > 0)
                                @for($i = 1;$i <= $no_of_account_holders ;$i++)
                                    @php
                                        $customerOvdDetails = (array) $userDetails[$i-1]['customerOvdDetails'];
                                        $customer_type = $customerOvdDetails['is_new_customer'] == '1'? 'NTB':'ETB';
                                        $riskDetails = (array) $userDetails[$i-1]['riskDetails'];

                                        $huf_display = false;
                                        if($accountDetails['constitution'] == 'NON_IND_HUF' && $i ==2){
                                            $huf_display = true;
                                        }

                                    @endphp
                                    @if($is_huf_display)
                                    @include('bank.huf_ovd_submission')
                                    @else
                                    <tr>
                                        <td>
                                            <span id="ovd_details">
                                                <table style=" border: 1px solid #f1f1f1;" width="100%" cellspacing="0" cellpadding="0" bgcolor="#F2F2F2">
                                                    <tbody>
                                                        <tr>
                                                            <td style="color: #364fcc; padding-left: 10px; font-size: 20px; padding-left: 10px!important;" height="30">
                                                                @if($i == 1)
                                                                    PERSONAL DETAILS: PRIMARY APPLICANT
                                                                @else
                                                                    PERSONAL DETAILS: APPLICANT {{$i}}
                                                                @endif
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td height="15"></td>
                                                        </tr>
                                                        @if($customer_type != "ETB")
                                                            <tr>
                                                                <td style="padding-left: 10px!important;">
                                                                    <table style="padding-left: 10px; padding-right: 10px;" width="100%">
                                                                        <tbody>
                                                                            <tr>
                                                                                <td style="line-height: 30px;" width="20%">
                                                                                    Name (Name as per OVD)
                                                                                </td>
                                                                                <td style="line-height: 30px;" width="20%">
                                                                                    Name on Card (Short Name)
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td style="line-height: 30px;margin-bottom:10px;" width="30%">
                                                                                    <span style="background:white;padding:0em 1.1em 0.2em 1.1em;  width:80%; height:100%; display: inline-block; color:#0070C0">
                                                                                        {{strtoupper($customerOvdDetails['title']).' '.($customerOvdDetails['first_name']).' '.($customerOvdDetails['middle_name']).' '.($customerOvdDetails['last_name'])}}
                                                                                    </span>
                                                                                </td>
                                                                                <td style="line-height: 30px;margin-bottom:10px;" width="30%">
                                                                                    <span style="background:white;padding:0em 1.1em 0.2em 1.1em;  width:80%; height:100%; display: inline-block; color:#0070C0">
                                                                                        {{strtoupper($customerOvdDetails['short_name'])}}
                                                                                    </span>
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td height="8"></td>
                                                                            </tr>
                                                                            <tr>
                                                                                @if($customerOvdDetails['pf_type'] == 'pancard')
                                                                                    <td style="line-height: 30px;" width="20%">
                                                                                        PAN Number
                                                                                    </td>
                                                                                @endif
                                                                                <td style="line-height: 30px;" width="20%">
                                                                                    Date of Birth
                                                                                </td>
                                                                            </tr>
                                                                             <tr>
                                                                                @if($customerOvdDetails['pf_type'] == 'pancard')
                                                                                    <td style="line-height: 30px;" width="30%">
                                                                                    
                                                                                        <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:80%; height:100%; display: inline-block; color:#0070C0;">
                                                                                        @if($is_review==1 || $i_ao_trac)
                                                                                        <span class="maskingfield">************</span>
                                                                                        @endif

                                                                                        <span {{$is_review==1 || $i_ao_trac ? "style=display:none":""}} class="{{$is_review==1 || $i_ao_trac ?  "unmaskingfield": ""}}" >
                                                                                        <label class="{{$is_review==1 || $i_ao_trac ?  "": "enc_label"}}">
                                                                                            {{($customerOvdDetails['pancard_no'])}}
                                                                                        </label>
                                                                                        </span>
                                                                                        </span>
                                                                                        
                                                                                    </td>
                                                                                @endif
                                                                                <td style="line-height: 30px;" width="30%">
                                                                                    <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:80%; height:100%; display: inline-block; color:#0070C0">
                                                                                        {{strtoupper(\Carbon\Carbon::parse($customerOvdDetails['dob'])->format('d M Y'))}}
                                                                                    </span>
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td height="8"></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td style="line-height: 30px;" width="20%">
                                                                                    {{$customerOvdDetails['proof_of_identity']}} Number
                                                                                </td>
                                                                                @if($customerOvdDetails['proof_of_identity'] == 'Passport')
                                                                                    <td style="line-height: 25px;" width="20%">
                                                                                            {{$customerOvdDetails['proof_of_identity']}} Expire
                                                                                        </td>

                                                                                    @elseif($customerOvdDetails['proof_of_identity'] == 'Driving Licence')
                                                                                    <td style="line-height: 25px;" width="20%">
                                                                                            {{$customerOvdDetails['proof_of_identity']}} Expire
                                                                                        </td>
                                                                                @elseif($customerOvdDetails['proof_of_identity'] == 'Aadhaar')
                                                                                    <td style="line-height: 25px;" width="20%">
                                                                                        Link Aadhar to Account
                                                                                    </td>
                                                                                @endif
                                                                            </tr>
                                                                            
                                                                         

                                                                            <tr>
                                                                                <td style="line-height: 30px;margin-bottom:10px;" width="30%">
                                                                                    <span style="background:white;padding:0em 1.1em 0.2em 1.1em;  width:80%; height:100%; display: inline-block; color:#0070C0">
                                                                                    
                                                                                    @if($customerOvdDetails['proof_of_identity'] == "Aadhaar Photocopy")
                                                                                            XXXX-XXXX{{substr($customerOvdDetails['id_proof_card_number'],9,11)}}
                                                                                        @elseif(in_array($customerOvdDetails['proof_of_identity'],$enc_fields))
                                                                                            @if($is_review==1 || $i_ao_trac)
                                                                                            <span class="maskingfield">************</span>
                                                                                            @endif
                                                                                        <span {{$is_review==1 || $i_ao_trac ? "style=display:none":""}} class="{{$is_review==1 || $i_ao_trac ?  "unmaskingfield": ""}} enc_label" >
                                                                                            {{($customerOvdDetails['id_proof_card_number'])}}
                                                                                            </span>
                                                                                        @else
                                                                                            <span>
                                                                                            {{($customerOvdDetails['id_proof_card_number'])}}
                                                                                            </span>
                                                                                        @endif
                                                                                    </span>
                                                                                </td>

                                                                                @if($customerOvdDetails['proof_of_identity'] == 'Passport')
                                                                                        <td style="line-height: 30px;margin-bottom:10px;" width="30%">
                                                                                            <span style="background:white;padding:0em 1.1em 0.2em 1.1em;  width:80%; height:100%; display: inline-block; color:#0070C0">
                                                                                                  {{strtoupper(\Carbon\Carbon::parse($customerOvdDetails['passport_driving_expire'])->format('d M Y'))}}

                                                                                            </span>
                                                                                    </td>
                                                                                    @elseif($customerOvdDetails['proof_of_identity'] == 'Driving Licence')
                                                                                        <td style="line-height: 30px;margin-bottom:10px;" width="30%">
                                                                                            <span style="background:white;padding:0em 1.1em 0.2em 1.1em;  width:80%; height:100%; display: inline-block; color:#0070C0">
                                                                                                  {{strtoupper(\Carbon\Carbon::parse($customerOvdDetails['passport_driving_expire'])->format('d M Y'))}}

                                                                                            </span>
                                                                                    </td>
                                                                                @elseif($customerOvdDetails['proof_of_identity'] == 'Aadhaar')
                                                                                            <td style="line-height: 30px;margin-bottom:10px;" width="30%">
                                                                                        <span style="background:white;padding:0em 1.1em 0.2em 1.1em;  width:80%; height:100%; display: inline-block; color:#0070C0">
                                                                                            @if($customerOvdDetails['aadhar_link'] == 1)
                                                                                                YES
                                                                                            @else
                                                                                                NO
                                                                                            @endif
                                                                                        </span>
                                                                                    </td>
                                                                                @endif
                                                                            </tr>
                                                                            <tr>
                                                                                <td height="8"></td>
                                                                            </tr>
                                                                            <tr>
                                                                                
                                                                                @if($customerOvdDetails['proof_of_identity'] == 'Passport')
                                                                                    <td style="line-height: 25px;" width="20%">
                                                                                            {{$customerOvdDetails['proof_of_identity']}} Issue
                                                                                    </td>

                                                                                @elseif($customerOvdDetails['proof_of_identity'] == 'Driving Licence')
                                                                                    <td style="line-height: 25px;" width="20%">
                                                                                        {{$customerOvdDetails['proof_of_identity']}} Issue
                                                                                    </td>
                                                                                @endif
                                                                            </tr>

                                                                            <tr>
                                                                                @if($customerOvdDetails['proof_of_identity'] == 'Passport')
                                                                                        <td style="line-height: 30px;margin-bottom:10px;" width="30%">
                                                                                            <span style="background:white;padding:0em 1.1em 0.2em 1.1em;  width:80%; height:100%; display: inline-block; color:#0070C0">
                                                                                                  {{strtoupper(\Carbon\Carbon::parse($customerOvdDetails['id_psprt_dri_issue'])->format('d M Y'))}}

                                                                                            </span>
                                                                                        </td>
                                                                                
                                                                                @elseif($customerOvdDetails['proof_of_identity'] == 'Driving Licence')
                                                                                        <td style="line-height: 30px;margin-bottom:10px;" width="30%">
                                                                                            <span style="background:white;padding:0em 1.1em 0.2em 1.1em;  width:80%; height:100%; display: inline-block; color:#0070C0">
                                                                                                  {{strtoupper(\Carbon\Carbon::parse($customerOvdDetails['id_psprt_dri_issue'])->format('d M Y'))}}

                                                                                            </span>
                                                                                    </td>
                                                                                 @endif
                                                                            </tr>

                                                                            <tr>
                                                                                <td height="8"></td>
                                                                            </tr>

                                                                            @if(isset($customerOvdDetails['ekyc_photo']) && $customerOvdDetails['ekyc_photo'] != '')
                                                                            <tr>
                                                                                    <td style="line-height: 25px;" width="20%">
                                                                                        E-KYC Photo
                                                                                    </td>
                                                                                </tr>

                                                                                <tr>
                                                                                    <td style="line-height: 50px;width:16%;padding-bottom: 3em">
                                                                                    <div class="{{$image_mask_blur}}" {{$def_blur_image}}>
                                                                                        <img  width="160px" alt="" src="{{ 'data: image/jpeg;base64,' .$customerOvdDetails['ekyc_photo'] }}"/>
                                                                                         </div>
                                                                                    </td>
                                                                                </tr>

                                                                                <tr>
                                                                                    <td height="8"></td>
                                                                                </tr>
                                                                            @endif


                                                                            <tr>
                                                                                <td style="line-height: 30px;" width="20%">
                                                                                    @if($customerOvdDetails['father_spouse'] == 1)
                                                                                        Father Name
                                                                                    @else
                                                                                        Spouse Name
                                                                                    @endif
                                                                                </td>
                                                                                <td style="line-height: 25px;" width="20%">
                                                                                    Mother's Full Name
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td style="line-height: 30px;margin-bottom:10px;" width="30%">
                                                                                    <span style="background:white;padding:0em 1.1em 0.2em 1.1em;  width:80%; height:100%; display: inline-block; color:#0070C0">
                                                                                        {{strtoupper($customerOvdDetails['father_name'])}}
                                                                                    </span>
                                                                                </td>
                                                                                <td style="line-height: 30px;margin-bottom:10px;" width="30%">
                                                                                    <span style="background:white;padding:0em 1.1em 0.2em 1.1em;  width:80%; height:100%; display: inline-block; color:#0070C0">
                                                                                        {{strtoupper($customerOvdDetails['mother_full_name'])}}
                                                                                    </span>
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td height="8"></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td style="line-height: 30px;" width="20%">
                                                                                    Mother’s Maiden Name
                                                                                </td>
                                                                                <td style="line-height: 30px;" width="20%">
                                                                                    Gender
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td style="line-height: 30px;margin-bottom:10px;" width="30%">
                                                                                    <span style="background:white;padding:0em 1.1em 0.2em 1.1em;  width:80%; height:100%; display: inline-block; color:#0070C0">
                                                                                        {{strtoupper($customerOvdDetails['mothers_maiden_name'])}}
                                                                                    </span>
                                                                                </td>
                                                                                <td style="line-height: 30px;" width="30%">
                                                                                    <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:80%; height:100%; display: inline-block; color:#0070C0">
                                                                                        {{strtoupper(config('constants.GENDER.'.$customerOvdDetails['gender']))}}
                                                                                    </span>
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td height="8"></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td style="line-height: 30px;" width="20%">
                                                                                    Martial Status
                                                                                </td>
                                                                                <td style="line-height: 30px;" width="20%">
                                                                                   Residential Status
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td style="line-height: 30px;" width="30%">
                                                                                    <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:80%; height:100%; display: inline-block; color:#0070C0">
                                                                                        {{strtoupper($customerOvdDetails['marital_status'])}}
                                                                                    </span>
                                                                                </td>
                                                                                <td style="line-height: 30px;" width="30%">
                                                                                    <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:80%; height:100%; display: inline-block; color:#0070C0">
                                                                                        {{strtoupper($customerOvdDetails['residential_status'])}}
                                                                                    </span>
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td height="8"></td>
                                                                            </tr>

                                                                             <tr>
                                                                                <td height="8"></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td style="line-height: 30px;" width="20%">
                                                                                    Mobile Number
                                                                                </td>
                                                                                <td style="line-height: 30px;" width="20%">
                                                                                    Email
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td style="line-height: 30px;" width="30%">
                                                                                    <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:80%; height:100%; display: inline-block; color:#0070C0">
                                                                                     @if($is_review==1 || $i_ao_trac)
                                                                                        <span class="maskingfield">*********</span>
                                                                                    @endif
                                                                                        <span {{$is_review==1 || $i_ao_trac ? "style=display:none":""}} class="{{$is_review==1 || $i_ao_trac ?  "unmaskingfield": ""}} enc_label" >
                                                                                        {{($customerOvdDetails['mobile_number'])}}
                                                                                    </span>
                                                                                    </span>
                                                                                </td>
                                                                                <td style="line-height: 30px;" width="30%">
                                                                                    <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:80%; height:100%; display: inline-block; color:#0070C0">
                                                                                     @if($is_review==1 || $i_ao_trac)
                                                                                        <span class="maskingfield">************</span>
                                                                                        @endif
                                                                                        <span {{$is_review==1 || $i_ao_trac ? "style=display:none":""}} class="{{$is_review==1 || $i_ao_trac ?  "unmaskingfield": ""}} enc_label" >
                                                                                        {{($customerOvdDetails['email'])}}
                                                                                    </span>
                                                                                    </span>
                                                                                </td>
                                                                            </tr>
                                                                                    <tr>
                                                                                <td height="8"></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td style="line-height: 30px;" width="20%">Proof Of Permanent Address Type</td>
                                                                                <td style="line-height: 30px;" width="20%">{{strtoupper($customerOvdDetails['proof_of_address'])}} Number</td>
                                                                            </tr>
                                                                             <tr>
                                                                                <td style="line-height: 30px;" width="20%">
                                                                                    <span style="background:white;padding:0em 1.1em 0.2em 1.1em;  width:80%; /*height:100%;*/ display: inline-block; color:#0070C0">
                                                                                    {{strtoupper($customerOvdDetails['proof_of_address'])}}
                                                                                    </span>
                                                                                </td>
                                                                                <td style="line-height: 30px;" width="20%">
                                                                                    <span style="background:white;padding:0em 1.1em 0.2em 1.1em;  width:80%; /*height:100%;*/ display: inline-block; color:#0070C0">
                                                                                    @if($customerOvdDetails['proof_of_address'] == "Aadhaar Photocopy")
                                                                                            XXXX-XXXX{{substr($customerOvdDetails['add_proof_card_number'],9,11)}}
                                                                                    @elseif(in_array($customerOvdDetails['proof_of_address'],$enc_fields))
                                                                                    @if($is_review==1 || $i_ao_trac)
                                                                                        <span class="maskingfield">************</span>
                                                                                    @endif
                                                                                        <span {{$is_review==1 || $i_ao_trac ? "style=display:none":""}} class="{{$is_review==1 || $i_ao_trac ?  "unmaskingfield": ""}}" >
                                                                                        <label class="{{$is_review==1 || $i_ao_trac ?  "": "enc_label"}}">
                                                                                            {{$customerOvdDetails['add_proof_card_number']}}
                                                                                        </label>
                                                                                        </span>
                                                                                        @else
                                                                                        <span >
                                                                                        <label class="enc_label">
                                                                                            {{$customerOvdDetails['add_proof_card_number']}}
                                                                                        </label>
                                                                                        </span>
                                                                                        @endif
                                                                                    </span>
                                                                                </td>
                                                                            </tr>
                                                                            @if(!empty($customerOvdDetails["passport_driving_expire_permanent"]) &&in_array($customerOvdDetails["proof_of_address"],["Passport","Driving Licence"]))
                                                                            <tr>
                                                                                <td style="line-height: 30px;" width="20%">{{strtoupper($customerOvdDetails['proof_of_address'])}} Expiry Date</td>
                                                                            </tr>
                                                                             <tr>
                                                                                <td style="line-height: 30px;" width="20%">
                                                                                    <span style="background:white;padding:0em 1.1em 0.2em 1.1em;  width:80%; /*height:100%;*/ display: inline-block; color:#0070C0">
                                                                                    {{date("d-M-Y",strtotime($customerOvdDetails['passport_driving_expire_permanent']))}}
                                                                                    </span>
                                                                                </td>
                                                                            </tr> 
                                                                            @endif

                                                                            @if(!empty($customerOvdDetails["add_psprt_dri_issue"]) &&in_array($customerOvdDetails["proof_of_address"],["Passport","Driving Licence"]))
                                                                            <tr>
                                                                                <td style="line-height: 30px;" width="20%">{{strtoupper($customerOvdDetails['proof_of_address'])}} Issue Date</td>
                                                                            </tr>
                                                                             <tr>
                                                                                <td style="line-height: 30px;" width="20%">
                                                                                    <span style="background:white;padding:0em 1.1em 0.2em 1.1em;  width:80%; /*height:100%;*/ display: inline-block; color:#0070C0">
                                                                                    {{date("d-M-Y",strtotime($customerOvdDetails['add_psprt_dri_issue']))}}
                                                                                    </span>
                                                                                </td>
                                                                            </tr> 
                                                                            @endif

                                                                            <tr>
                                                                                <td style="line-height: 30px;" width="20%">
                                                                                        Address (as per OVD)
                                                                                    </td>
                                                                                    <td style="line-height: 30px;" width="20%">
                                                                                        Communication Address
                                                                                    </td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td style="/*line-height: 30px;*/margin-bottom:10px;" width="30%">
                                                                                        <span style="background:white;padding:0em 1.1em 0.2em 1.1em;  width:80%; /*height:100%;*/ display: inline-block; color:#0070C0">
                                                                                            {{strtoupper($customerOvdDetails['per_address_line1'])}}</br>
                                                                                            {{strtoupper($customerOvdDetails['per_address_line2'])}}</br>
                                                                                            {{strtoupper($customerOvdDetails['per_country'])}}</br>
                                                                                            {{strtoupper($customerOvdDetails['per_pincode'])}}</br>
                                                                                            {{strtoupper($customerOvdDetails['per_state'])}}</br>
                                                                                            {{strtoupper($customerOvdDetails['per_city'])}}</br>
                                                                                            {{strtoupper($customerOvdDetails['per_landmark'])}}
                                                                                        </span>
                                                                                    </td>
                                                                                    <td style="/*line-height: 30px;*/margin-bottom:10px;" width="30%">
                                                                                        <span style="background:white;padding:0em 1.1em 0.2em 1.1em;  width:80%; /*height:100%; */display: inline-block; color:#0070C0">
                                                                                            {{strtoupper($customerOvdDetails['current_address_line1'])}}</br>
                                                                                            {{strtoupper($customerOvdDetails['current_address_line2'])}}</br>
                                                                                            {{strtoupper($customerOvdDetails['current_country'])}}</br>
                                                                                            {{strtoupper($customerOvdDetails['current_pincode'])}}</br>
                                                                                            {{strtoupper($customerOvdDetails['current_state'])}}</br>
                                                                                            {{strtoupper($customerOvdDetails['current_city'])}}</br>
                                                                                            {{strtoupper($customerOvdDetails['current_landmark'])}}
                                                                                        </span>
                                                                                    </td>
                                                                                </tr>

                                                                                <tr>
                                                                                <td height="8"></td>
                                                                            </tr>
                                                                            @if(isset($specialCase) && count($specialCase) > 0)
                                                                                <tr>
                                                                                    <td height="8"></td>
                                                                                </tr>
                                                                                @foreach($specialCase as $caseName => $caseValue)
                                                                                <tr>
                                                                                    <td style="line-height: 30px;" width="20%">
                                                                                       {{$caseName}}
                                                                                    </td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td style="line-height: 30px;" width="30%">
                                                                                        <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:80%; height:100%; display: inline-block; color:#0070C0">
                                                                                            {{$caseValue}}
                                                                                        </span>
                                                                                    </td>
                                                                                </tr>
                                                                                @endforeach
                                                                            @endif
                                                                            <tr>
                                                                                <td height="8"></td>
                                                                            </tr>
                                                                        </tbody>
                                                                    </table>
                                                                </td>
                                                            </tr>
                                                        @else
                                                        @if(isset($specialCase) && count($specialCase) > 0)
																	<tr>
																		<td height="8"></td>
																	</tr>
																	@foreach($specialCase as $caseName => $caseValue)
																	<tr>
																		<td style="line-height: 30px;" width="20%">
																		   <label style="margin-left: 20px">{{$caseName}}</label>
																		</td>
																	</tr>
																	<tr>
																		<td style="line-height: 30px;">
																			<span style="background:white;padding:0em 2.1em 0.2em 1.1em; width:20%;
																			margin-left: 20px; height:100%; display: inline-block; color:#0070C0">
																				{{$caseValue}}
																			</span>
																		</td>
																	</tr>
																	@endforeach
                                                            @endif
                                                            <tr>
                                                                <td style="padding-left: 10px!important;">
                                                                    <table style="padding-left: 10px; padding-right: 10px;" width="100%">
                                                                        <tbody>
                                                                            <tr>
                                                                                <td style="line-height: 30px;" width="20%">
                                                                                    Existing Customer:
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td style="line-height: 30px;margin-bottom:10px;" width="30%">
                                                                                    <span style="background:white;padding:0em 1.1em 0.2em 1.1em;  width:80%; height:100%; display: inline-block; color:#0070C0">
                                                                                        {{strtoupper($customerOvdDetails['customer_full_name'])}} [CUSTID: {{$customerOvdDetails['customer_id']}}]
                                                                                    </span>
                                                                                </td>
                                                                            </tr>

                                                                            <tr>
                                                                                <td style="line-height: 30px;" width="20%">
                                                                                    Mobile Number
                                                                                </td>
                                                                                <td style="line-height: 30px;" width="20%">
                                                                                    Email
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td style="line-height: 30px;" width="30%">
                                                                                    <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:80%; height:100%; display: inline-block; color:#0070C0"  >
                                                                                    <label {{$is_review==1 || $i_ao_trac ? "style=display:none":""}} class="{{$is_review==1 || $i_ao_trac ?  "unmaskingfield": ""}} enc_label">
                                                                                        {{($customerOvdDetails['mobile_number'])}}
                                                                                        </label>
                                                                                        @if($is_review==1 || $i_ao_trac)
                                                                                        <span class="maskingfield">*********</span>
                                                                                    @endif
                                                                                    </span>
                                                                                </td>
                                                                                <td style="line-height: 30px;" width="30%">
                                                                                    <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:80%; height:100%; display: inline-block; color:#0070C0">
                                                                                        <label {{$is_review==1 || $i_ao_trac ? "style=display:none":""}} class="{{$is_review==1 || $i_ao_trac ?  "unmaskingfield": ""}} enc_label">
                                                                                        {{($customerOvdDetails['email'])}}
                                                                                        </label>
                                                                                        @if($is_review==1 || $i_ao_trac)
                                                                                        <span class="maskingfield">*********</span>
                                                                                    @endif
                                                                                    </span>
                                                                                </td>
                                                                            </tr>

                                                                            <tr>
                                                                                <td height="8"></td>
                                                                            </tr>
                                                                        </tbody>
                                                                    </table>
                                                                </td>
                                                            </tr>
                                                        @endif
                                                       
                                                    </tbody>
                                                </table>
                                            </span>
                                        </td>
                                    </tr>
                                    @endif
                                    @if(($accountType == 1) && ($accountDetails['delight_scheme'] == 5) && ($customer_type == 'NTB'))
                                        <tr>
                                            <td>
                                                <span>
                                                    <table style=" border: 1px solid #f1f1f1;" width="100%" cellspacing="0" cellpadding="0" bgcolor="#F2F2F2">
                                                        <tbody>
                                                            <tr>
                                                                <td style="color: #364fcc; padding-left: 10px; font-size: 20px; padding-left: 10px!important;" height="30">
                                                                    Delight Kit Details
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td height="10"></td>
                                                            </tr>
                                                            <tr>
                                                                <td style="padding-left: 10px!important;">
                                                                    <table style="padding-left: 10px; padding-right: 10px;" width="100%">
                                                                        <tbody>
                                                                            <tr>
                                                                                <td style="line-height: 30px;" width="20%">
                                                                                    Kit Number
                                                                                </td>
                                                                                <td style="line-height: 30px;" width="20%">
                                                                                    Customer Id
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td style="line-height: 30px;" width="30%">
                                                                                    <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:80%; height:100%; display: inline-block; color:#0070C0">
                                                                                        {{strtoupper($delightDetails['kit_number'])}}
                                                                                    </span>
                                                                                </td>
                                                                                <td style="line-height: 30px;" width="30%">
                                                                                    <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:80%; height:100%; display: inline-block; color:#0070C0">
                                                                                        {{strtoupper($delightDetails['customer_id'])}}
                                                                                    </span>
                                                                                </td>

                                                                            </tr>
                                                                            <tr>
                                                                                <td height="8"></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td style="line-height: 30px;" width="20%">
                                                                                    Account Number
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td style="line-height: 30px;" width="30%">
                                                                                    <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:80%; height:100%; display: inline-block; color:#0070C0">
                                                                                        {{strtoupper($delightDetails['account_number'])}}
                                                                                    </span>
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td height="8"></td>
                                                                            </tr>
                                                                        </tbody>
                                                                    </table>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td height="20"></td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </span>
                                            </td>
                                        </tr>
                                    @endif
                                 
                                    @if(!$is_huf_display)
                                    @if($accountDetails['account_type'] == 'Current' && $accountDetails['flow_tag_1'] != 'INDI')
                                        @include('bank.entityform')
                                    @endif
                                    @endif

                                    @if($huf_display)
                                    @if($accountDetails['account_type'] == 'Current' && $accountDetails['flow_tag_1'] != 'INDI')
                                        @include('bank.entityform')
                                    @endif
                                    @endif

                                    @if($customer_type != "ETB")
                                        <tr>
                                            <td>
                                                <span id="cidd">
                                                    <table style=" border: 1px solid #f1f1f1;" width="100%" cellspacing="0" cellpadding="0" bgcolor="#F2F2F2">
                                                        <tbody>
                                                            <tr>
                                                                <td style="color: #364fcc; padding-left: 10px; font-size: 20px; padding-left: 10px!important;" height="30">
                                                                    CIDD
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td height="10"></td>
                                                            </tr>
                                                            <tr>
                                                                <td style="padding-left: 10px!important;">
                                                                    <table style="padding-left: 10px; padding-right: 10px;" width="100%">
                                                                        <tbody>
                                                                            <tr>
                                                                                @if(!$huf_display)
                                                                                <td style="line-height: 30px;" width="20%">
                                                                                    Education
                                                                                </td>
                                                                                @endif
                                                                                <td style="line-height: 30px;" width="20%">
                                                                                    Gross Income
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                @if(!$huf_display)
                                                                                <td style="line-height: 30px;" width="30%">
                                                                                    <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:80%; height:100%; display: inline-block; color:#0070C0">
                                                                                        {{strtoupper(config('constants.EDUCATION.'.$riskDetails['education']))}}
                                                                                    </span>
                                                                                </td>
                                                                                @endif
                                                                                <td style="line-height: 30px;" width="30%">
                                                                                <span
                                                                                    style="background:white;padding:0em 1.1em 0.2em 1.1em; width:80%; height:100%; display: inline-block; color:#0070C0">
                                                                                            <!-- {{ strtoupper(config('constants.GROSS_INCOME.' . $riskDetails['gross_income'])) }} -->
                                                                                            {{ strtoupper($riskDetails['gross_income']) }}
                                                                                    </span>
                                                                                </td>

                                                                            </tr>
                                                                            <tr>
                                                                                <td height="8"></td>
                                                                            </tr>
                                                                            <tr>
                                                                            @if($accountType != 3)
                                                                                <td style="line-height: 30px;" width="20%">
                                                                                    Expected Turnover
                                                                                </td>
                                                                                 <td style="line-height: 30px;" width="20%">
                                                                                    PEP
                                                                                </td>
                                                                            @endif
                                                                            </tr>
                                                                            <tr>
                                                                            @if($accountType != 3)
                                                                                <td style="line-height: 30px;" width="30%">
                                                                                    <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:80%; height:100%; display: inline-block; color:#0070C0">
                                                                                        {{strtoupper(config('constants.ANNUAL_TURNOVER.'.$riskDetails['annual_turnover']))}}
                                                                                    </span>
                                                                                </td>
                                                                                <td style="line-height: 30px;" width="30%">
                                                                                    <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:80%; height:100%; display: inline-block; color:#0070C0">
                                                                                        {{strtoupper($riskDetails['pep'])}}
                                                                                    </span>
                                                                                </td>
                                                                            @endif
                                                                            </tr>
                                                                            <tr>
                                                                                <td height="8"></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td style="line-height: 30px;" width="20%">
                                                                                    {{ $huf_display ? 'Nature of Business' : 'Occupation' }}
                                                                                </td>
                                                                            @if($riskDetails['occupation'] == 'OTHERS' || $riskDetails['occupation'] == 'OTHER - SELF EMPLOYED PROFESSIONAL' || $riskDetails['occupation'] == 'OTHER - SELF EMPLOYED - NON-PROFESSIONAL' || $riskDetails['occupation'] == 'OTHER - TRADING')
                                                                                <td style="line-height: 30px;" width="20%">
                                                                                    {{ $huf_display ? 'Others Business' : 'Others Occupation' }}
                                                                                </td>
                                                                            @endif
                                                                            </tr>
                                                                            <tr>
                                                                                <td style="line-height: 30px;" width="30%">
                                                                                    <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:80%; height:100%; display: inline-block; color:#0070C0">
                                                                                        {{strtoupper($riskDetails['occupation'])}}
                                                                                    </span>
                                                                                </td>
                                                                            @if($riskDetails['occupation'] == 'OTHERS' || $riskDetails['occupation'] == 'OTHER - SELF EMPLOYED PROFESSIONAL' || $riskDetails['occupation'] == 'OTHER - SELF EMPLOYED - NON-PROFESSIONAL' || $riskDetails['occupation'] == 'OTHER - TRADING')
                                                                                <td style="line-height: 30px;" width="30%">
                                                                                    <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:80%; height:100%; display: inline-block; color:#0070C0">
                                                                                       {{strtoupper($riskDetails['other_occupation'])}}
                                                                                    </span>
                                                                                </td>
                                                                            @endif
                                                                            </tr>
                                                                            <tr>
                                                                                <td height="8"></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td style="line-height: 30px;" width="20%">
                                                                                        Networth
                                                                                </td>
                                                                            </td>

                                                                            </tr>
                                                                            <tr>
                                                                                <td style="line-height: 30px;" width="30%">
                                                                                        <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:80%; height:100%; display: inline-block; color:#0070C0">
                                                                                            {{strtoupper(config('constants.NETWORTH.'.$riskDetails['networth']))}}
                                                                                        </span>
                                                                                </td>

                                                                            </tr>
                                                                            <tr>
                                                                                <td height="8"></td>
                                                                            </tr>


                                                                    @if($accountType != 3)
                                                                            <tr>
                                                                                <td style="line-height: 30px;" width="20%">
                                                                                    Source of funds
                                                                                </td>
                                                                                @php
                                                                                    $source_of_funds = explode(',',$riskDetails['source_of_funds']);
                                                                                @endphp
                                                                    @if(in_array($source_of_funds,[5]))
                                                                                <td style="line-height: 30px;" width="20%">
                                                                                    Source of fund Others
                                                                                </td>
                                                                    @endif

                                                                            </tr>
                                                                    @endif
                                                                    @if($accountType != 3)
                                                                            <tr>
                                                                                <td style="line-height: 30px;" width="30%">
                                                                                    <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:80%; height:100%; display: inline-block; color:#0070C0">
                                                                                                {{strtoupper($riskDetails['source_of_funds'])}}
                                                                                      
                                                                                    </span>
                                                                                </td>
                                                                                @if(in_array($source_of_funds,[5]))
                                                                                <td style="line-height: 30px;" width="30%">
                                                                                    <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:80%; height:100%; display: inline-block; color:#0070C0">
                                                                                        {{strtoupper($riskDetails['source_others_comments'])}}
                                                                                    </span>
                                                                                </td>
                                                                                @endif
                                                                            </tr>
                                                                    @endif
                                                                            <tr>
                                                                                <td height="8"></td>
                                                                            </tr>
                                                                        </tbody>
                                                                    </table>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td height="20"></td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <span id="FATCA">
                                                    <table style=" border: 1px solid #f1f1f1;" width="100%" cellspacing="0" cellpadding="0" bgcolor="#F2F2F2">
                                                        <tbody>
                                                            <tr>
                                                                <td style="color: #364fcc; padding-left: 10px; font-size: 20px; padding-left: 10px!important;" height="30">
                                                                    FATCA
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td height="10"></td>
                                                            </tr>
                                                            <tr>
                                                                <td style="padding-left: 10px!important;">
                                                                    <table style="padding-left: 10px; padding-right: 10px;" width="100%">
                                                                        <tbody>
                                                                            <tr>
                                                                                <td style="line-height: 30px;" width="20%">
                                                                                    Country of Residence
                                                                                </td>
                                                                                <td style="line-height: 30px;" width="20%">
                                                                                    Country of Birth
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td style="line-height: 30px;" width="30%">
                                                                                    <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:80%; height:100%; display: inline-block; color:#0070C0">
                                                                                        {{strtoupper($riskDetails['country_name'])}}
                                                                                    </span>
                                                                                </td>
                                                                                <td style="line-height: 30px;" width="30%">
                                                                                    <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:80%; height:100%; display: inline-block; color:#0070C0">
                                                                                        {{strtoupper($riskDetails['country_of_birth'])}}
                                                                                    </span>
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td height="8"></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td style="line-height: 30px;" width="20%">
                                                                                    Citizenship
                                                                                </td>
                                                                                <td style="line-height: 30px;" width="20%">
                                                                                    Place of Birth
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td style="line-height: 30px;" width="30%">
                                                                                    <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:80%; height:100%; display: inline-block; color:#0070C0">
                                                                                        {{strtoupper($riskDetails['citizenship'])}}
                                                                                    </span>
                                                                                </td>
                                                                                <td style="line-height: 30px;" width="30%">
                                                                                    <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:80%; height:100%; display: inline-block; color:#0070C0">
                                                                                        {{strtoupper($riskDetails['place_of_birth'])}}
                                                                                    </span>
                                                                                </td>
                                                                            </tr>
                                                                             <tr>
                                                                                <td height="8"></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td style="line-height: 30px;" width="20%">
                                                                                    Residence for Tax Purpose
                                                                                </td>
                                                                               <td style="line-height: 30px;" width="20%">
                                                                                    US Person
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td style="line-height: 30px;" width="30%">
                                                                                  <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:80%; height:100%; display: inline-block; color:#0070C0">
                                                                                        {{strtoupper($riskDetails['residence'])}}
                                                                                    </span>
                                                                                </td>
                                                                                <td style="line-height: 30px;" width="30%">
                                                                                    <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:80%; height:100%; display: inline-block; color:#0070C0">
                                                                                        @if($riskDetails['us_person'] == 1)
                                                                                            YES
                                                                                        @else
                                                                                            NO
                                                                                        @endif
                                                                                    </span>
                                                                                </td>
                                                                            </tr>
                                                                             <tr>
                                                                                <td height="8"></td>
                                                                            </tr>
                                                                            @if($riskDetails['us_person'] == 1)
                                                                                <tr>
                                                                                    <td style="line-height: 30px;" width="20%">
                                                                                        Tin
                                                                                    </td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td style="line-height: 30px;" width="30%">
                                                                                        <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:80%; height:100%; display: inline-block; color:#0070C0">
                                                                                            {{strtoupper($riskDetails['tin'])}}
                                                                                        </span>
                                                                                    </td>
                                                                                </tr>
                                                                            @endif
                                                                            <tr>
                                                                                <td height="8"></td>
                                                                            </tr>
                                                                        </tbody>
                                                                    </table>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td height="20"></td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </span>
                                            </td>
                                        </tr>
                                    @else
                                        @if($accountDetails['account_type_id'] != 3)
                                            <tr>
                                                <td>
                                                    <span id="cidd">
                                                        <table style=" border: 1px solid #f1f1f1;" width="100%" cellspacing="0" cellpadding="0" bgcolor="#F2F2F2">
                                                            <tbody>
                                                                <tr>
                                                                    <td style="color: #364fcc; padding-left: 10px; font-size: 20px; padding-left: 10px!important;" height="30">
                                                                        CIDD
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td height="10"></td>
                                                                </tr>
                                                                <tr>
                                                                    <td style="padding-left: 10px!important;">
                                                                        <table style="padding-left: 10px; padding-right: 10px;" width="100%">
                                                                            <tbody>
                                                                                <tr>
                                                                                    <td style="line-height: 30px;" width="20%">
                                                                                        Education
                                                                                    </td>
                                                                                    <td style="line-height: 30px;" width="20%">
                                                                                        Occupation
                                                                                    </td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td style="line-height: 30px;" width="30%">
                                                                                        <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:80%; height:100%; display: inline-block; color:#0070C0">
                                                                                            {{strtoupper(config('constants.EDUCATION.'.$riskDetails['education']))}}
                                                                                        </span>
                                                                                    </td>
                                                                                    <td style="line-height: 30px;" width="30%">
                                                                                      
                                                                                        <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:80%; height:100%; display: inline-block; color:#0070C0">
                                                                                                <!-- {{strtoupper($riskDetails['occupation'])}} -->
                                                                                        </span>
                                                                                        
                                                                                    </td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td height="8"></td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td style="line-height: 30px;" width="20%">
                                                                                        Gross Income
                                                                                    </td>
                                                                                    <td style="line-height: 30px;" width="20%">
                                                                                        Expected Turnover
                                                                                    </td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td style="line-height: 30px;" width="30%">
                                                                                    <span
                                                                                        style="background:white;padding:0em 1.1em 0.2em 1.1em; width:80%; height:100%; display: inline-block; color:#0070C0">
                                                                                        <!-- {{ strtoupper(config('constants.GROSS_INCOME.' . $riskDetails['gross_income'])) }} -->
                                                                                        {{ strtoupper($riskDetails['gross_income']) }}
                                                                                        </span>
                                                                                    </td>
                                                                                    <td style="line-height: 30px;" width="30%">
                                                                                        <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:80%; height:100%; display: inline-block; color:#0070C0">
                                                                                            {{strtoupper(config('constants.ANNUAL_TURNOVER.'.$riskDetails['annual_turnover']))}}
                                                                                        </span>
                                                                                    </td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td height="8"></td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td style="line-height: 30px;" width="20%">
                                                                                        Source of funds
                                                                                    </td>
                                                                                @php
                                                                                       $source_of_fund = explode(',',$riskDetails['source_of_funds']);
                                                                                @endphp
                                                                                @if(in_array($source_of_fund,[5]))
                                                                                    <td style="line-height: 30px;" width="20%">
                                                                                        Source of fund Others
                                                                                    </td>
                                                                                @endif
                                                                                </tr>
                                                                                <tr>
                                                                                    <td style="line-height: 30px;" width="30%">
                                                                                        <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:100%; height:100%; display: inline-block; color:#0070C0">
                                                                                        {{strtoupper($riskDetails['source_of_funds'])}}
                                                                                        </span>
                                                                                    </td>
                                                                                @if(in_array($source_of_fund,[5]))
                                                                                    <td style="line-height: 30px;" width="30%">
                                                                                        <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:80%; height:100%; display: inline-block; color:#0070C0">
                                                                                            {{strtoupper($riskDetails['source_others_comments'])}}
                                                                                        </span>
                                                                                    </td>
                                                                                @endif
                                                                                </tr>

                                                                                    <td height="8"></td>
                                                                                </tr>
                                                                            </tbody>
                                                                        </table>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td height="20"></td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <span id="FATCA">
                                                        <table style=" border: 1px solid #f1f1f1;" width="100%" cellspacing="0" cellpadding="0" bgcolor="#F2F2F2">
                                                            <tbody>
                                                                <tr>
                                                                    <td style="color: #364fcc; padding-left: 10px; font-size: 20px; padding-left: 10px!important;" height="30">
                                                                        FATCA
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td height="10"></td>
                                                                </tr>
                                                                <tr>
                                                                    <td style="padding-left: 10px!important;">
                                                                        <table style="padding-left: 10px; padding-right: 10px;" width="100%">
                                                                            <tbody>
                                                                                <tr>
                                                                                    <td style="line-height: 30px;" width="20%">
                                                                                        Country of Residence
                                                                                    </td>
                                                                                    <td style="line-height: 30px;" width="20%">
                                                                                        Country of Birth
                                                                                    </td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td style="line-height: 30px;" width="30%">
                                                                                        <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:80%; height:100%; display: inline-block; color:#0070C0">
                                                                                            {{strtoupper($riskDetails['country_name'])}}
                                                                                        </span>
                                                                                    </td>
                                                                                    <td style="line-height: 30px;" width="30%">
                                                                                        <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:80%; height:100%; display: inline-block; color:#0070C0">
                                                                                            {{strtoupper($riskDetails['country_of_birth'])}}
                                                                                        </span>
                                                                                    </td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td height="8"></td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td style="line-height: 30px;" width="20%">
                                                                                        Citizenship
                                                                                    </td>
                                                                                    <td style="line-height: 30px;" width="20%">
                                                                                        US Person
                                                                                    </td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td style="line-height: 30px;" width="30%">
                                                                                        <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:80%; height:100%; display: inline-block; color:#0070C0">
                                                                                            {{strtoupper($riskDetails['citizenship'])}}
                                                                                        </span>
                                                                                    </td>
                                                                                    <td style="line-height: 30px;" width="30%">
                                                                                        <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:80%; height:100%; display: inline-block; color:#0070C0">
                                                                                            @if($riskDetails['citizenship'] == 1)
                                                                                                Yes
                                                                                            @else
                                                                                                No
                                                                                            @endif
                                                                                        </span>
                                                                                    </td>
                                                                                </tr>
                                                                                 <tr>
                                                                                    <td height="8"></td>
                                                                                </tr>
                                                                            @if($riskDetails['citizenship'] == 1)
                                                                                <tr>
                                                                                    <td style="line-height: 30px;" width="20%">
                                                                                        Tin
                                                                                    </td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td style="line-height: 30px;" width="30%">
                                                                                        <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:80%; height:100%; display: inline-block; color:#0070C0">
                                                                                            {{strtoupper($riskDetails['tin'])}}
                                                                                        </span>
                                                                                    </td>
                                                                                </tr>
                                                                            @endif
                                                                                <tr>
                                                                                    <td height="8"></td>
                                                                                </tr>
                                                                            </tbody>
                                                                        </table>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td height="20"></td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </span>
                                                </td>
                                            </tr>
                                        @endif
                                    @endif
                                @endfor
                            @endif
                            @if(count($nomineeDetails) > 0)
                                @for($k = 0;$k <= count($nomineeDetails) - 1;$k++)
                                    @php
                                        //echo "<pre>";print_r($nomineeDetails);exit;
                                        $nomineeData = (array) $nomineeDetails[$k];
                                        //echo "<pre>";print_r($nomineeData);exit;
                                    @endphp
                                    @if(!$is_huf_display)
                                        <tr>
                                            <td>
                                                <span id="nominee_details">
                                                    <table style=" border: 1px solid #f1f1f1;" width="100%" cellspacing="0" cellpadding="0" bgcolor="#F2F2F2">
                                                        <tbody>
                                                            <tr>
                                                                <td style="color: #364fcc; padding-left: 10px; font-size: 20px; padding-left: 10px!important;" height="30">
                                                                    @if($k == 0)
                                                                        NOMINEE DETAILS
                                                                    @else
                                                                        TD NOMINEE DETAILS
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td height="10"></td>
                                                            </tr>
                                                                @if($nomineeData['nominee_exists'] == "yes")
                                                                    <tr>
                                                                        <td style="padding-left: 10px!important;">
                                                                            <table style="padding-left: 10px; padding-right: 10px;" width="100%">
                                                                                <tbody>
                                                                                    <tr>
                                                                                        <td style="line-height: 30px;" width="20%">
                                                                                            Nominee Name
                                                                                        </td>

                                                                                    </tr>
                                                                                    <tr>
                                                                                        <td style="line-height: 30px;" width="30%">
                                                                                            <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:80%; height:100%; display: inline-block; color:#0070C0">
                                                                                                {{strtoupper($nomineeData['nominee_name'])}}
                                                                                            </span>
                                                                                        </td>

                                                                                    </tr>
                                                                                    <tr>
                                                                                        <td height="8"></td>
                                                                                    </tr>

                                                                                    <tr>
                                                                                        <td style="line-height: 30px;" width="20%">
                                                                                             Address Line1
                                                                                        </td>
                                                                                        <td style="line-height: 30px;" width="20%">
                                                                                            Address Line2
                                                                                        </td>
                                                                                    </tr>
                                                                                    <tr>
                                                                                        <td style="line-height: 30px;" width="30%">
                                                                                            <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:80%; height:100%; display: inline-block; color:#0070C0">
                                                                                                {{strtoupper($nomineeData['nominee_address_line1'])}}
                                                                                            </span>
                                                                                        </td>
                                                                                        <td style="line-height: 30px;" width="30%">
                                                                                            <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:80%; height:100%; display: inline-block; color:#0070C0">
                                                                                                {{strtoupper($nomineeData['nominee_address_line2'])}}
                                                                                            </span>
                                                                                        </td>
                                                                                    </tr>

                                                                                    <tr>
                                                                                        <td height="8"></td>
                                                                                    </tr>

                                                                                    <tr>
                                                                                        <td style="line-height: 30px;" width="20%">
                                                                                             City
                                                                                        </td>
                                                                                         <td style="line-height: 30px;" width="20%">
                                                                                            State
                                                                                        </td>
                                                                                    </tr>
                                                                                    <tr>

                                                                                        <td style="line-height: 30px;" width="30%">
                                                                                            <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:80%; height:100%; display: inline-block; color:#0070C0">
                                                                                                {{strtoupper($nomineeData['nominee_city'])}}
                                                                                            </span>
                                                                                        </td>
                                                                                        <td style="line-height: 30px;" width="30%">
                                                                                            <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:80%; height:100%; display: inline-block; color:#0070C0">
                                                                                                {{strtoupper($nomineeData['nominee_state'])}}
                                                                                            </span>
                                                                                        </td>
                                                                                    </tr>
                                                                                    <tr>
                                                                                        <td height="8"></td>
                                                                                    </tr>

                                                                                    <tr>

                                                                                        <td style="line-height: 30px;" width="20%">
                                                                                             Country
                                                                                        </td>
                                                                                        <td style="line-height: 30px;" width="20%">
                                                                                            Pincode
                                                                                        </td>
                                                                                    </tr>
                                                                                    <tr>

                                                                                        <td style="line-height: 30px;" width="30%">
                                                                                            <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:80%; height:100%; display: inline-block; color:#0070C0">
                                                                                                {{strtoupper($nomineeData['nominee_country'])}}
                                                                                            </span>
                                                                                        </td>
                                                                                        <td style="line-height: 30px;" width="30%">
                                                                                            <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:80%; height:100%; display:   inline-block; color:#0070C0">
                                                                                                {{strtoupper($nomineeData['nominee_pincode'])}}
                                                                                            </span>
                                                                                        </td>
                                                                                    </tr>
                                                                                    <tr>
                                                                                        <td height="8"></td>
                                                                                    </tr>
                                                                                    <tr>

                                                                                        <td style="line-height: 30px;" width="20%">
                                                                                            Nominee Date of Birth
                                                                                        </td>
                                                                                         <td style="line-height: 30px;" width="20%">
                                                                                            Nominee Relationship
                                                                                        </td>
                                                                                    </tr>
                                                                                    <tr>
                                                                                        <td style="line-height: 30px;" width="30%">
                                                                                            <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:80%; height:100%; display: inline-block; color:#0070C0">
                                                                                                {{strtoupper(\Carbon\Carbon::parse($nomineeData['nominee_dob'])->format('d M Y'))}}
                                                                                            </span>
                                                                                        </td>
                                                                                        <td style="line-height: 30px;" width="30%">
                                                                                            <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:80%; height:100%; display: inline-block; color:#0070C0">
                                                                                                {{strtoupper($nomineeData['relatinship_applicant'])}}
                                                                                            </span>
                                                                                        </td>
                                                                                    </tr>
                                                                                    <tr>
                                                                                        <td height="8"></td>
                                                                                    </tr>

                                                                                    <tr>

                                                                                        <td style="line-height: 30px;" width="20%">
                                                                                            Nominee name to be printed on passbook, statement and DCA ?
                                                                                        </td>
                                                                                    </tr>
                                                                                    <tr>

                                                                                        <td style="line-height: 30px;" width="30%">
                                                                                            <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:80%; height:100%; display: inline-block; color:#0070C0">
                                                                                                {{strtoupper($nomineeData['name_as_per_passbook'])}}
                                                                                            </span>
                                                                                        </td>
                                                                                    </tr>

                                                                                    <tr>
                                                                                        <td height="8"></td>
                                                                                    </tr>

                                                                                    @if($nomineeData['nominee_age'] < 18)
                                                                                        <tr>
                                                                                             <td style="color: #364fcc; padding-top: 10px; font-size: 20px; " height="30">
                                                                                                Guardian / Appointee Details
                                                                                            </tr>
                                                                                        <tr>
                                                                                        <tr>
                                                                                            <td style="line-height: 30px;" width="20%">
                                                                                                Guardian Name
                                                                                            </td>
                                                                                            <td style="line-height: 30px;" width="20%">
                                                                                                Guardian Address1
                                                                                            </td>
                                                                                        </tr>
                                                                                        <tr>
                                                                                            <td style="line-height: 30px;" width="30%">
                                                                                                <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:80%; height:100%; display: inline-block; color:#0070C0">
                                                                                                    {{strtoupper($nomineeData['guardian_name'])}}
                                                                                                </span>
                                                                                            </td>
                                                                                            <td style="line-height: 30px;" width="30%">
                                                                                                <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:80%; height:100%; display: inline-block; color:#0070C0">
                                                                                                    {{strtoupper($nomineeData['guardian_address_line1'])}}
                                                                                                </span>
                                                                                            </td>
                                                                                        </tr>
                                                                                        <tr>
                                                                                            <td height="8"></td>
                                                                                        </tr>

                                                                                        <tr>
                                                                                            <td style="line-height: 30px;" width="20%">
                                                                                                Guardian Address2
                                                                                            </td>
                                                                                            <td style="line-height: 30px;" width="20%">
                                                                                               City
                                                                                            </td>
                                                                                        </tr>
                                                                                        <tr>
                                                                                            <td style="line-height: 30px;" width="30%">
                                                                                                <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:80%; height:100%; display: inline-block; color:#0070C0">
                                                                                                    {{strtoupper($nomineeData['guardian_address_line2'])}}
                                                                                                </span>
                                                                                            </td>
                                                                                            <td style="line-height: 30px;" width="30%">
                                                                                                <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:80%; height:100%; display: inline-block; color:#0070C0">
                                                                                                    {{strtoupper($nomineeData['guardian_city'])}}
                                                                                                </span>
                                                                                            </td>
                                                                                        </tr>
                                                                                        <tr>
                                                                                            <td height="8"></td>
                                                                                        </tr>

                                                                                        <tr>
                                                                                            <td style="line-height: 30px;" width="20%">
                                                                                                State
                                                                                            </td>
                                                                                            <td style="line-height: 30px;" width="20%">
                                                                                               Country
                                                                                            </td>
                                                                                        </tr>
                                                                                        <tr>
                                                                                            <td style="line-height: 30px;" width="30%">
                                                                                                <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:80%; height:100%; display: inline-block; color:#0070C0">
                                                                                                    {{strtoupper($nomineeData['guardian_state'])}}
                                                                                                </span>
                                                                                            </td>
                                                                                            <td style="line-height: 30px;" width="30%">
                                                                                                <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:80%; height:100%; display: inline-block; color:#0070C0">
                                                                                                    {{strtoupper($nomineeData['guardian_country'])}}
                                                                                                </span>
                                                                                            </td>
                                                                                        </tr>
                                                                                        <tr>
                                                                                            <td height="8"></td>
                                                                                        </tr>
                                                                                        <tr>
                                                                                            <td style="line-height: 30px;" width="20%">
                                                                                               Pincode
                                                                                            </td>
                                                                                            <td style="line-height: 30px;" width="20%">
                                                                                               Relationship With Nominee
                                                                                            </td>
                                                                                        </tr>
                                                                                        <tr>
                                                                                            <td style="line-height: 30px;" width="30%">
                                                                                                <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:80%; height:100%; display: inline-block; color:#0070C0">
                                                                                                    {{strtoupper($nomineeData['guardian_pincode'])}}
                                                                                                </span>
                                                                                            </td>
                                                                                            <td style="line-height: 30px;" width="30%">
                                                                                                <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:80%; height:100%; display: inline-block; color:#0070C0">
                                                                                                    {{strtoupper($nomineeData['relatinship_applicant_guardian'])}}
                                                                                                </span>
                                                                                            </td>
                                                                                        </tr>

                                                                                    @endif
                                                                                </tbody>
                                                                            </table>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td height="20"></td>
                                                                    </tr>
                                                                @else
                                                                @if(!$is_huf_display)
                                                                    <tr style="background:white;padding:0em 1.1em 0.2em 1.1em; width:100%; height:100%; display: inline-block; color:#000">
                                                                        <td>
                                                                             Customer Comment: ( No, I/We do not wish to nominate anyone. )
                                                                        </td>
                                                                    </tr>
                                                                    @endif
                                                                    <tr>
                                                                        <td height="20"></td>
                                                                    </tr>
                                                                @endif
                                                        </tbody>
                                                    </table>
                                                </span>
                                            </td>
                                        </tr>
                                    @endif
                                @endfor
                            @else
                            @if(!$is_huf_display)
                                <tr style="background:white;padding:0em 1.1em 0.2em 1.1em; width:100%; height:100%; display: inline-block; color:#000">
                                    <td>
                                            Customer Comment: ( No, I/We do not wish to nominate anyone. )
                                    </td>
                                </tr>
                            @endif
                            <tr>
                                    <td height="20"></td>
                                </tr>
                            @endif
                            <tr>
                                <td>
                                    <span id="initial_funding_details">
                                        <table style=" border: 1px solid #f1f1f1;" width="100%" cellspacing="0" cellpadding="0" bgcolor="#F2F2F2">
                                            <tbody>
                                                <tr>
                                                    <td style="color: #364fcc; padding-left: 10px; font-size: 20px; padding-left: 10px!important;" height="30">
                                                        INITIAL FUNDING
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td height="10"></td>
                                                </tr>
                                                <tr>
                                                    <td style="padding-left: 10px!important;">
                                                        <table style="padding-left: 10px; padding-right: 10px;" width="100%">
                                                            <tbody>
                                                                <tr>
                                                                    <td style="line-height: 30px;" width="20%">
                                                                        Initial Funding Type
                                                                    </td>
                                                                    @if(!isset($customerOvdDetails['initial_funding_type']))
                                                                        @php
                                                                            $customerOvdDetails = (array) current($customerOvdDetails);
                                                                        @endphp
                                                                    @endif
                                                                    @if($customerOvdDetails['initial_funding_type'] == 5)
                                                                            <td style="line-height: 30px;" width="20%">
                                                                        @if($customerOvdDetails['amount'] != '')
                                                                                Funding Source
                                                                        @endif
                                                                            </td>
                                                                    @else
                                                                        <td style="line-height: 30px;" width="20%">
                                                                            Initial Funding Date
                                                                        </td>
                                                                    @endif
                                                                </tr>
                                                                <tr>
                                                                    @if(!isset($customerOvdDetails['initial_funding_type']))
                                                                        @php
                                                                            $customerOvdDetails = (array) current($customerOvdDetails);
                                                                        @endphp
                                                                    @endif
                                                                    <td style="line-height: 30px;" width="30%">
                                                                        <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:80%; height:100%; display: inline-block; color:#0070C0">
                                                                            {{strtoupper(config('constants.INITIAL_FUNDING_TYPE.'.$customerOvdDetails['initial_funding_type']))}}
                                                                        </span>
                                                                    </td>
                                                                    @if($customerOvdDetails['initial_funding_type'] == 5)
                                                                        <td style="line-height: 30px;" width="30%">
                                                                        @if($customerOvdDetails['amount'] != '')
                                                                            <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:80%; height:100%; display: inline-block; color:#0070C0">
                                                                                {{$customerOvdDetails['funding_source']}}
                                                                            </span>
                                                                        @endif

                                                                        @if($customerOvdDetails['others_type'] == 0)
                                                                            <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:80%; height:100%; display: inline-block; color:#0070C0">
                                                                                NILL IP
                                                                            </span>
                                                                        @endif

                                                                        </td>
                                                                    @else
                                                                        <td style="line-height: 30px;" width="30%">
                                                                            <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:80%; height:100%; display: inline-block; color:#0070C0">
                                                                                {{strtoupper(\Carbon\Carbon::parse($customerOvdDetails['initial_funding_date'])->format('d M Y'))}}
                                                                            </span>
                                                                        </td>
                                                                    @endif
                                                                </tr>
                                                                <tr>
                                                                    <td height="8"></td>
                                                                </tr>

                                                                <tr>
                                                                    <td style="line-height: 30px;" width="20%">
                                                                        Amount
                                                                    </td>

                                                                    @if($customerOvdDetails['initial_funding_type'] != 5 && $customerOvdDetails['initial_funding_type'] != 3 )
                                                                        <td style="line-height: 30px;" width="20%">
                                                                            Reference
                                                                        </td>
                                                                    @endif
                                                                </tr>
                                                                <tr>
                                                                    <td style="line-height: 30px;" width="30%">
                                                                        <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:80%; height:100%; display: inline-block; color:#0070C0">
                                                                            @if($customerOvdDetails['amount'] == '')
                                                                            NILL
                                                                            @else
                                                                            {{strtoupper($customerOvdDetails['amount'])}}
                                                                            @endif
                                                                        </span>
                                                                    </td>
                                                                    @if($customerOvdDetails['initial_funding_type'] != 5 && $customerOvdDetails['initial_funding_type'] != 3)
                                                                        <td style="line-height: 30px;" width="30%">
                                                                            <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:80%; height:100%; display: inline-block; color:#0070C0">
                                                                                {{strtoupper($customerOvdDetails['reference'])}}
                                                                            </span>
                                                                        </td>
                                                                    @endif
                                                                </tr>
                                                                <tr>
                                                                    <td height="8"></td>
                                                                </tr>
                                                                @if($customerOvdDetails['initial_funding_type'] != 5)
                                                                    <tr>
                                                                        <td style="line-height: 30px;" width="20%">
                                                                            IFSC Code
                                                                        </td>
                                                                        <td style="line-height: 30px;" width="20%">
                                                                            Account Number
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td style="line-height: 30px;" width="30%">
                                                                            <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:80%; height:100%; display: inline-block; color:#0070C0">
                                                                            @if($customerOvdDetails['initial_funding_type'] == 3)
                                                                                <!-- DCBL0000018 -->
                                                                                {{strtoupper($customerOvdDetails['maturity_ifsc_code'])}}
                                                                            @else
                                                                                {{strtoupper($customerOvdDetails['ifsc_code'])}}
                                                                            @endif
                                                                            </span>
                                                                        </td>
                                                                        <td style="line-height: 30px;" width="30%">
                                                                            <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:80%; height:100%; display: inline-block; color:#0070C0">
                                                                                {{strtoupper($customerOvdDetails['account_number'])}}
                                                                            </span>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td height="8"></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td style="line-height: 30px;" width="20%">
                                                                            Account Name
                                                                        </td>
                                                                        <td style="line-height: 30px;" width="20%">
                                                                            Bank Name
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td style="line-height: 30px;" width="30%">
                                                                            <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:80%; height:100%; display: inline-block; color:#0070C0">
                                                                                @if($customerOvdDetails['initial_funding_type'] == 3)
                                                                                    {{strtoupper($acc_name)}}
                                                                                @else
                                                                                    {{strtoupper($customerOvdDetails['account_name'])}}
                                                                                @endif
                                                                            </span>
                                                                        </td>
                                                                        <td style="line-height: 30px;" width="30%">
                                                                            <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:80%; height:100%; display: inline-block; color:#0070C0">
                                                                                @if($customerOvdDetails['initial_funding_type'] == 3)
                                                                                    DCB Bank
                                                                                @else
                                                                                    {{strtoupper($customerOvdDetails['bank_name'])}}
                                                                                @endif
                                                                            </span>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td height="8"></td>
                                                                    </tr>
                                                                    <tr>
                                                                    @if($customerOvdDetails['self_thirdparty'] != '')
                                                                        <td style="line-height: 30px;" width="20%">
                                                                            Type
                                                                        </td>
                                                                    @endif
                                                                    @if($customerOvdDetails['self_thirdparty'] == 'thirdparty')
                                                                        <td style="line-height: 30px;" width="20%">
                                                                            Relationship
                                                                        </td>
                                                                    </tr>
                                                                    @endif
                                                                    <tr>
                                                                    @if($customerOvdDetails['self_thirdparty'] != '')
                                                                        <td style="line-height: 30px;" width="30%">
                                                                            <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:80%; height:100%; display: inline-block; color:#0070C0">
                                                                                @if($is_huf_display && $customerOvdDetails['self_thirdparty']=='self')
                                                                                   HUF
                                                                                @else
                                                                                {{strtoupper($customerOvdDetails['self_thirdparty'])}}
                                                                                @endif
                                                                            </span>
                                                                        </td>
                                                                    @endif
                                                                    @if($customerOvdDetails['self_thirdparty'] == 'thirdparty')
                                                                        <td style="line-height: 30px;" width="30%">
                                                                            <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:80%; height:100%; display: inline-block; color:#0070C0">
                                                                                {{strtoupper($customerOvdDetails['relationship'])}}
                                                                            </span>
                                                                        </td>
                                                                    </tr>
                                                                    @endif
                                                                @endif
                                                            </tbody>
                                                        </table>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td height="20"></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </span>
                                </td>
                            </tr>
                           
                            @if(in_array($accountType,[3,4]) || $accountDetails['source'] == 'CC')
                                <tr>
                                    <td>
                                        <span id="initial_funding_details">
                                            <table style=" border: 1px solid #f1f1f1;" width="100%" cellspacing="0" cellpadding="0" bgcolor="#F2F2F2">
                                                <tbody>
                                                    <tr>
                                                        <td style="color: #364fcc; padding-left: 10px; font-size: 20px; padding-left: 10px!important;" height="30">
                                                            TERM DEPOSIT
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td height="10"></td>
                                                    </tr>
                                                    <tr>
                                                        <td style="padding-left: 10px!important;">
                                                            <table style="padding-left: 10px; padding-right: 10px;" width="100%">
                                                                <tbody>
                                                                    <tr>
                                                                        <td style="line-height: 30px;" width="20%">
                                                                            Years
                                                                        </td>
                                                                        <td style="line-height: 30px;" width="20%">
                                                                           Months
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td style="line-height: 30px;" width="30%">
                                                                            <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:80%; height:100%; display: inline-block; color:#0070C0">
                                                                                {{strtoupper($customerOvdDetails['years'])}}
                                                                            </span>
                                                                        </td>
                                                                        <td style="line-height: 30px;" width="30%">
                                                                            <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:80%; height:100%; display: inline-block; color:#0070C0">
                                                                                {{strtoupper($customerOvdDetails['months'])}}
                                                                            </span>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td height="8"></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td style="line-height: 30px;" width="20%">
                                                                            Days
                                                                        </td>
                                                                        <td style="line-height: 30px;" width="20%">
                                                                            Amount
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td style="line-height: 30px;" width="30%">
                                                                            <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:80%; height:100%; display: inline-block; color:#0070C0">
                                                                                {{strtoupper($customerOvdDetails['days'])}}
                                                                            </span>
                                                                        </td>
                                                                        <td style="line-height: 30px;" width="30%">
                                                                            <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:80%; height:100%; display: inline-block; color:#0070C0">
                                                                                {{strtoupper($customerOvdDetails['td_amount'])}}
                                                                            </span>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td height="8"></td>
                                                                    </tr>
                                                                        <tr>
                                                                            <td style="line-height: 30px;" width="20%">
                                                                                Auto Renew
                                                                            </td>
                                                                            <td style="line-height: 30px;" width="20%">
                                                                                Interest Payout
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td style="line-height: 30px;" width="30%">
                                                                                <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:80%; height:30px; display: inline-block; color:#0070C0">

                                                                                @if($customerOvdDetails['auto_renew'] == 'Y')
                                                                                    YES
                                                                                @else
                                                                                    NO
                                                                                 @endif
                                                                                </span>
                                                                            </td>
                                                                            <td style="line-height: 30px;" width="30%">
                                                                                <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:80%; height:30px; display: inline-block; color:#0070C0">
                                                                                    {{strtoupper(config('constants.INTREST_PAYOUT.'.$customerOvdDetails['interest_payout']))}}
                                                                                </span>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td height="8"></td>
                                                                        </tr>
                                                                    @if($customerOvdDetails['emd'] == 1)
                                                                     <tr id="emd_row_submission">

                                                                        <td style="line-height: 30px;" width="20%">
                                                                            EMD
                                                                        </td>

                                                                        <td style="line-height: 30px;" width="20%">
                                                                            3<sup>rd</sup> Party Name
                                                                        </td>
                                                                    </tr>
                                                                    <tr id="emd_row_submission_value">
                                                                            <td style="line-height: 30px;" width="30%">
                                                                                <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:80%; height:30px; display: inline-block; color:#0070C0">      YES
                                                                                </span>
                                                                            </td>
                                                                            <td style="line-height: 30px;" width="30%">
                                                                                <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:80%; height:30px; display: inline-block; color:#0070C0">
                                                                                     {{strtoupper($customerOvdDetails['emd_name'])}}
                                                                                </span>
                                                                            </td>

                                                                    </tr>
                                                                    @else
                                                                    <tr id="emd_row_submission">
                                                                        <td style="line-height: 30px;" width="20%">
                                                                            EMD
                                                                        </td>
                                                                    </tr>
                                                                    <tr id="emd_row_submission_value">
                                                                            <td style="line-height: 30px;" width="30%">
                                                                                <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:80%; height:30px; display: inline-block; color:#0070C0">
                                                                                    NO
                                                                                </span>
                                                                            </td>
                                                                    </tr>
                                                                    @endif
                                                                    <tr>
                                                                        <td height="8"></td>
                                                                    </tr>
                                                                     <tr>
                                                                        <td style="line-height: 30px;" width="20%">
                                                                            Maturity Instructions
                                                                        </td>

                                                                    </tr>
                                                                    <tr>
                                                                        <td style="line-height: 30px;" width="30%">
                                                                            <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:80%; height:30px; display: inline-block; color:#0070C0">
                                                                                 {{strtoupper(config('constants.MATURITY.'.$customerOvdDetails['maturity']))}}
                                                                            </span>
                                                                        </td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td height="20"></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <span id="initial_funding_details">
                                            <table style=" border: 1px solid #f1f1f1;" width="100%" cellspacing="0" cellpadding="0" bgcolor="#F2F2F2">
                                                <tbody>
                                                    <tr>
                                                        <td style="color: #364fcc; padding-left: 10px; font-size: 20px; padding-left: 10px!important;" height="30">
                                                            Account Details for Credit of TD Proceeds
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td height="10"></td>
                                                    </tr>
                                                    <tr>
                                                        <td style="padding-left: 10px!important;">
                                                            <table style="padding-left: 10px; padding-right: 10px;" width="100%">
                                                                <tbody>
                                                                    <tr>
                                                                        <td style="line-height: 30px;" width="20%">
                                                                            Bank Name
                                                                        </td>
                                                                        <td style="line-height: 30px;" width="20%">
                                                                           IFSC Code
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td style="line-height: 30px;" width="30%">
                                                                            <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:80%; height:100%; display: inline-block; color:#0070C0">
                                                                                {{strtoupper($customerOvdDetails['maturity_bank_name'])}}
                                                                            </span>
                                                                        </td>
                                                                        <td style="line-height: 30px;" width="30%">
                                                                            <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:80%; height:100%; display: inline-block; color:#0070C0">
                                                                                @if($customerOvdDetails['initial_funding_type'] == 3)
                                                                                <!-- DCBL0000018 -->
                                                                                {{strtoupper($customerOvdDetails['maturity_ifsc_code'])}}
                                                                                @else
                                                                                {{strtoupper($customerOvdDetails['maturity_ifsc_code'])}}
                                                                                @endif
                                                                            </span>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td height="8"></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td style="line-height: 30px;" width="20%">
                                                                            Account Number
                                                                        </td>
                                                                        <td style="line-height: 30px;" width="20%">
                                                                            Account Name
                                                                        </td>
                                                                    </tr>
                                                                     <tr>
                                                                        <td style="line-height: 30px;" width="30%">
                                                                            <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:80%; height:100%; display: inline-block; color:#0070C0">
                                                                                {{strtoupper($customerOvdDetails['maturity_account_number'])}}
                                                                            </span>
                                                                        </td>
                                                                        <td style="line-height: 30px;" width="30%">
                                                                            <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:80%; height:100%; display: inline-block; color:#0070C0">
                                                                                {{strtoupper($customerOvdDetails['maturity_account_name'])}}
                                                                            </span>
                                                                        </td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td height="20"></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </span>
                                    </td>
                                </tr>
                            @endif
                          
                            @if(in_array($accountType,[1,2,4]))
                                <tr>
                                    <td>
                                        <span id="initial_funding_details">
                                            <table style=" border: 1px solid #f1f1f1;" width="100%" cellspacing="0" cellpadding="0" bgcolor="#F2F2F2">
                                                <tbody>
                                             
                                                    <tr>
                                                        <td style="color: #364fcc; padding-left: 10px; font-size: 20px; padding-left: 10px!important;" height="30">
                                                        {{ $is_huf_display ? 'Services' : 'GPA' }}
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td height="10"></td>
                                                    </tr>
                                               
                                                    <tr>
                                                        <td style="padding-left: 10px!important;">
                                                            <table style="padding-left: 10px; padding-right: 10px;" width="100%">
                                                                <tbody>
                                                                @if(!$is_huf_display)
                                                                    <tr>
                                                                        <td style="line-height: 30px;" width="20%">
                                                                            GPA Required
                                                                        </td>
                                                                        @if(isset($sweeps_availability) && $sweeps_availability)
                                                                            <td style="line-height: 30px;" width="20%">
                                                                               Two way sweep
                                                                            </td>
                                                                        @else
                                                                            <td style="line-height: 30px;" width="20%">
                                                                            </td>
                                                                        @endif
                                                                    </tr>
                                                                    <tr>
                                                                        <td style="line-height: 30px;" width="30%">
                                                                            <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:80%; height:100%; display: inline-block; color:#0070C0">
                                                                                @if($accountDetails['gpa_required'] == 1)
                                                                                    YES
                                                                                @else
                                                                                    NO
                                                                                @endif
                                                                            </span>
                                                                        </td>
                                                                        @if(isset($sweeps_availability) && $sweeps_availability)
                                                                            <td style="line-height: 30px;" width="30%">
                                                                                <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:80%; height:100%; display: inline-block; color:#0070C0">
                                                                                @if($accountDetails['two_way_sweep'] == 1)
                                                                                    YES
                                                                                @else
                                                                                    NO
                                                                                @endif
                                                                                </span>
                                                                            </td>
                                                                        @else
                                                                            <td style="line-height: 30px;" width="30%">
                                                                            </td>
                                                                        @endif
                                                                    </tr>
                                                                    <tr>
                                                                        <td height="8"></td>
                                                                    </tr>
                                                                    @if($accountDetails['gpa_required'] == 1)
                                                                        <tr>
                                                                            <td style="line-height: 30px;" width="20%">
                                                                                Plan Name
                                                                            </td>
                                                                            <td style="line-height: 30px;" width="20%">
                                                                                Auto Renewal
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td style="line-height: 30px;" width="30%">
                                                                                <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:80%; height:100%; display: inline-block; color:#0070C0">
                                                                                    {{strtoupper($accountDetails['gpaplan'])}}
                                                                                </span>
                                                                            </td>
                                                                            <td style="line-height: 30px;" width="30%">
                                                                                <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:80%; height:100%; display: inline-block; color:#0070C0">
                                                                                @if($accountDetails['auto_renew_gpa'] == 1)
                                                                                    YES
                                                                                @else
                                                                                    NO
                                                                                @endif
                                                                                </span>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td height="8"></td>
                                                                        </tr>
                                                                        @if($accountDetails['auto_renew_gpa'] == 1)
                                                                            <tr>
                                                                                <td style="line-height: 30px;" width="20%">
                                                                                   Term for Auto Renew
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td style="line-height: 30px;" width="30%">
                                                                                    <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:80%; height:100%; display: inline-block; color:#0070C0">
                                                                                        {{strtoupper(config('constants.TERM_FOR_AUTO_RENEWAL.'.$accountDetails['termautorenewal']))}}
                                                                                    </span>
                                                                                </td>
                                                                            </tr>
                                                                        @endif
                                                                    @endif
                                                                    <tr>
                                                                        <td height="8"></td>
                                                                    </tr>
                                                                @endif
                                                                    <tr>
                                                                       @if($accountType != 3)
                                                                              
                                                                                    <td>
                                                                                        Card Type
                                                                                    </td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td style="line-height: 30px;" width="30%">
                                                                                        <span style="background:white;padding:0em 1.1em 0.2em 1.1em;  width:30%; height:100%; display: inline-block; color:#0070C0">

                                                                                            {{$accountDetails['card_description']}}

                                                                                        </span>
                                                                                    </td>

                                                                                </tr>
                                                                        @endif
                                                                    </tr>

                                                                </tbody>
                                                            </table>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td height="20"></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </span>
                                    </td>
                                </tr>
                            @endif
                            
                            <tr>
                                <td>


            @if($accountDetails['source'] == 'CC' && $callCenterDeclaration)
                <span class="display-none" id="documents">
            @elseif(($customer_type == "ETB") && ($customerOvdDetails['initial_funding_type'] == 3) && ($accountDetails['source'] == 'CC'))
                <span id="documents">
            @else
                <span id="documents">
            @endif
                    <table style=" border: 1px solid #f1f1f1;" width="100%" cellspacing="0" cellpadding="0" bgcolor="#F2F2F2">
                        <tbody>
                            <tr>
                                <td style="color: #364fcc; padding-left: 10px; font-size: 20px; padding-left: 10px!important;" height="30">
                                    DOCUMENTS PROVIDED
                                </td>
                            </tr>
                            <tr>
                                <td height="10"></td>
                            </tr>
                            <tr>
                                <td style="padding-left: 10px!important;">
                <table style="padding-left: 10px; padding-right: 10px;" width="100%">
                    <tbody>
                        @if(count($files) > 0)
                            @foreach($files as $file)
                                @if($file['type']!='declaration')
                                    <tr>
                                        <td style="line-height: 50px;" width="20%">
                                        <div class="{{$image_mask_blur}}" {{$def_blur_image}}>
                                            <?php $imgPublicPath = asset('/imagesmarkedattachments/'.$formId.'/'.$file['filename']); ?>
                                            <img width="400px" src="{{ $imgPublicPath }}"/>
                                        </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td height="100"></td>
                                    </tr>
                                @endif
                            @endforeach
                            <tr>
                                <td style="color: #364fcc; padding-left: 10px; font-size: 20px; padding-left: 10px!important;" height="30">
                                    DECLARATION DOCUMENTS PROVIDED
                                </td>
                            </tr>
                            <tr>
                                <td height="10"></td>
                            </tr>
                            @foreach($files as $file)
                                @if($file['type']=='declaration')
                                    <tr>
                                        @if(substr(strtolower($file['filename']),-3) != 'pdf')
                                        <td style="line-height: 50px;" width="20%">
                                        <div class="{{$image_mask_blur}}" {{$def_blur_image}}>
                                            <img width="400px" src="{{ asset('/imagesmarkedattachments/'.$formId.'/'.$file['filename']) }}"/>
                                            </div>
                                        </td>
                                        @else
                                            <td style="line-height: 50px;" width="20%">
                                                <i class="fa fa-file-pdf-o" style="font-size:48px;color:red"></i>
                                                <a href="{{ asset('/imagesmarkedattachments/'.$formId.'/'.$file['filename']) }}" target="_blank" style="font-size:14px;text-decoration:none;margin-top:14px;">{{$file['filename']}}</a>
                                            </td>
                                        @endif
                                    </tr>
                                
                                @endif

                            @endforeach
                                @if(isset($callCenterEmailImage) && count($callCenterEmailImage) > 0)
                                    <tr>
                                            <td height="50">
                                                NRI Value Date
                                                
                                            </td>
                                    </tr>
                                        <tr>
                                            <td style="line-height: 30px;" >
                                                <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:41%; height:100%; display: inline-block; color:#0070C0">
                                                    @php
                                                        $nriDate = json_decode($callCenterEmailImage['dyna_text'],true);
                                                        // echo "<pre>"; print_r($dyna_text);exit;
                                                    @endphp
                                                    {{$nriDate['nri_date']}}
                                                </span>
                                            </td>
                                        </tr>
                                
                               
                                @endif
                                @endif
                              
                                @if($accountDetails['source'] == 'CC' && $tdSchemeCodecc == '1' && isset($callCenterEmailImage) && count($callCenterEmailImage)==0)
                                <tr>
                                            <td height="50">
                                               Mode Of Communication: Phone
                                                
                                            </td>
                                    </tr>
                                @endif
                        @if(count($l3DeclarationsImage) > 0)
                        <tr>
                                <td style="color: #364fcc; padding-left: 10px; font-size: 20px; padding-left: 10px!important;" height="30">
                                   L3 DECLARATION DOCUMENTS PROVIDED
                                </td>
                            </tr>
                            <tr>
                                <td height="10"></td>
                            </tr>
                            @foreach($l3DeclarationsImage as $l3DeclarationsImages)
                                    <tr>
                                        <td style="line-height: 50px;" width="20%">
                                        <div class="{{$image_mask_blur}}" {{$def_blur_image}}>
                                            <img width="400px" src="{{ asset('/imageslevelthree/'.$formId.'/'.$l3DeclarationsImages->attachment) }}"/>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td height="100"></td>
                                    </tr>
                            @endforeach
                        @endif  
                        @if(count($entityL1Images) > 0)
                        <tr>
                                <td style="color: #364fcc; padding-left: 10px; font-size: 20px; padding-left: 10px!important;" height="30">
                                   ENTITY NPC DECLARATION DOCUMENTS PROVIDED
                                </td>
                            </tr>
                            <tr>
                                <td height="10"></td>
                            </tr>
                            @foreach($entityL1Images as $entityL1Image)
                                    <tr>
                                 
                                    @if(substr(strtolower($entityL1Image->clearance_img),-3) != 'pdf')
                                        <td style="line-height: 50px;" width="20%">
                                        <div class="{{$image_mask_blur}}" {{$def_blur_image}}>
                                            <img width="400px" src="{{ asset('/imagesattachments/'.$formId.'/'.$entityL1Image->clearance_img) }}"/>
                                            </div>
                                        </td>
                                    @else
                                            <td style="line-height: 50px;" width="20%">
                                                <i class="fa fa-file-pdf-o" style="font-size:48px;color:red"></i>
                                                <a href="{{ asset('/imagesattachments/'.$formId.'/'.$entityL1Image->clearance_img) }}" target="_blank" style="font-size:14px;text-decoration:none;margin-top:14px;">{{$entityL1Image->clearance_img}}</a>
                                            </td>
                                        @endif
                                    </tr>
                                    <tr>
                                        <td height="100"></td>
                                    </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
                                </td>
                            </tr>
                            <tr>
                                <td height="10"></td>
                            </tr>
                            <tr>
                                <td height="1" bgcolor="#d4d4d4"></td>
                            </tr>
                            <tr>
                                <td height="10"></td>
                            </tr>
                        </tbody>
                    </table>
                </span>


                                </td>
                            </tr>
                            
                            <tr>
                                <td>
                                    <span id="confirmation">
                                        <table style=" border: 1px solid #f1f1f1;" width="100%" cellspacing="0" cellpadding="0" bgcolor="#F2F2F2">
                                            <tbody>
                                               
                                                <tr>
                                                    <td style="color: #364fcc; padding-left: 10px; font-size: 20px; padding-left: 10px!important;" height="30">
                                                        @if(($customer_type == "ETB") && ($customerOvdDetails['initial_funding_type'] == 3) && ($accountDetails['source'] == 'CC'))
                                                            CONFIRMATION (By Call Centre official sourcing the application)
                                                        @else
                                                            CONFIRMATION (By Branch official sourcing the application)
                                                        @endif
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td height="10"></td>
                                                </tr>
                                                <tr>
                                                    <td style="padding-left: 10px!important;">
                                                        <table style="padding-left: 10px; padding-right: 10px;" width="100%">
                                                            <tbody>
                                                                @if(count($declarationsList) > 0)
                                                                    @foreach($declarationsList as $declaration)
                                                                        @php
                                                                            $declaration = (array) $declaration;
                                                                            
                                                                        @endphp
                                                                        <tr>
                                                                            
                                                                             
                                                                             @if($declaration['checkbox'] != 'Y')
                                                                             <td valign="center" style="padding-top: 1.2em;">
                                                                                 
                                                                             </td>
                                                                                @else
                                                                            <td valign="center" style="padding-top: 1.2em;">
                                                                                @if($is_aof_tracker)
                                                                                    <input type="checkbox" class="declaration" name="declaration_{{$declaration['id']}}" checked disabled>
                                                                                @else
                                                                                    @if(count($declarations) > 0)
                                                                                        @php
                                                                                            $declarationsList = explode(',',$declarations['declarations']);
                                                                                           
                                                                                        @endphp
                                                                                        @if(in_array($declaration['id'],$declarationsList))
                                                                                            <input type="checkbox" class="declaration" name="declaration_{{$declaration['id']}}" value="{{$declaration['id']}}" checked {{$disabled}}>
                                                                                        @else
                                                                                            <input type="checkbox" class="declaration" name="declaration_{{$declaration['id']}}" value="{{$declaration['id']}}">
                                                                                        @endif
                                                                                    @else
                                                                                        <input type="checkbox" class="declaration" name="declaration_{{$declaration['id']}}" value="{{$declaration['id']}}">
                                                                                    @endif
                                                                                @endif
                                                                                &nbsp;
                                                                                @endif
                                                                            </td>
                                                                            @if($declaration['input_field'] == 'Y')
                                                                            <td style="">
                                                                                    <span style="white-space: initial;">
                                                                                    @if(preg_match('/_FIELD_DATE_/', $declaration['declaration']))
                                                                                    @php
                                                                                        if($is_review != 1 && $meetingdate == ''){
                                                                                        $dateHtml = '<span style="white-space: initial;">
                                                                                                        <span class="with-icon mr-1 ml-1" style="width: 12em;display: inline-block;">
                                                                                                         <input  type="text" class="form-control date-input meeting_date submissionDeclarationField" placeholder="Meeting Date" id="sentDate" name="meeting_date" style="position: inherit;" value="'.$meetingdate.'">
                                                                                                        </span>
                                                                                                     </span>';
                                                                                                }else{
                                                                                            $dateHtml =  '<span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:10em; height:100%; display: inline-block; color:#0070C0">'.$meetingdate.'</span>';

                                                                                            }
                                                                                        $output = $declaration['declaration'];
                                                                                        $datedeclaration = str_replace('_FIELD_DATE_',$dateHtml,$output);
                                                                                        $declaration['declaration'] = $datedeclaration; 


                                                                                        if(preg_match('/_FIELD_M_LOCATION_ /', $declaration['declaration'])){

                                                                                            $token = explode('_FIELD_M_LOCATION_', $declaration['declaration']);
                                                                                        
                                                                                           
                                                                                        } 
                                                                                    @endphp
                                                                                       
                                                                                    {!!$token[0] !!}
                                                                                    @if($is_review != 1 && $custmeetinglocations == '')
                                                                                    <span class="ml-1 mr-1" style="width: 20em;display: inline-block;">
                                                                                        {!! Form::select('customer_meeting_location',$custMeetingLocations,$custmeetinglocations,array('class'=>'form-control submissionDeclarationField customer_meeting_location','table'=>'customer_meeting_location','id'=>'customer_meeting_location','name'=>'customer_meeting_location','placeholder'=>'')) !!}
                                                                                    </span>
                                                                                    @else
                                                                                     @php
                                                                                        if($custmeetinglocations == 6){
                                                                                            $custMeeting = 'REGISTERED ADDRESS';
                                                                                        }else{
                                                                                            $custMeeting = strtoupper(config('constants.SPECIAL_SUBMISSION_DECLARATION.CUSTOMER_MEETING_LOCATION')[$custmeetinglocations]);
                                                                                        }
                                                                                    @endphp
                                                                                        <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:10em; height:100%; display: inline-block; color:#0070C0">
                                                                                        {{$custMeeting}}

                                                                                        </span>
                                                                                    @endif
                                                                                    {{$token[1]}}
                                                                                       
                                                                                    @endif
                                                                                    </span>
                                                                                    @if(preg_match('/_FIELD_REASON_/', $declaration['declaration']))
                                                                                            
                                                                                        @php

                                                                                            if(preg_match('/_FIELD_REASON_/', $declaration['declaration'])){

                                                                                                $tokenReason = explode('_FIELD_REASON_', $declaration['declaration']);

                                                                                            } 
                                                                                        @endphp
                                                                                       
                                                                                        {!! $tokenReason[0]  !!}
                                                                                        @if($is_review != 1 && $reasonforaccntopening == '')
                                                                                        <span class="ml-1 mr-1" style="width: 20em;display: inline-block;">
                                                                                        {!! Form::select('reason_for_accnt_opening',$reasonForAccntOpening,$reasonforaccntopening,array('class'=>'form-control submissionDeclarationField reason_for_accnt_opening','table'=>'lead_generated','id'=>'reason_for_accnt_opening','name'=>'reason_for_accnt_opening','placeholder'=>'')) !!}
                                                                                        </span>
                                                                                        @else
                                                                                            <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:20em; height:100%; display: inline-block; color:#0070C0">    
                                                                                        {{strtoupper(config('constants.SPECIAL_SUBMISSION_DECLARATION.REASON_FOR_ACCOUNT_OPEN')[$reasonforaccntopening])}}
                                                                                            </span>
                                                                                        @endif 
                                                                                    @endif


                                                                                    @if(preg_match('/_FIELD_LEAD_/', $declaration['declaration']))
                                                                                            
                                                                                        @php

                                                                                            if(preg_match('/_FIELD_LEAD_/', $declaration['declaration'])){

                                                                                                $tokenlead = explode('_FIELD_LEAD_', $declaration['declaration']);

                                                                                            } 
                                                                                        @endphp
                                                                                       
                                                                                        {!! $tokenlead[0]  !!}
                                                                                        @if($is_review != 1 && $leadgenerated == '')
                                                                                        <span class="ml-1 mr-1" style="width: 20em;display: inline-block;">
                                                                                        {!! Form::select('lead_generated',$leadGenerated,$leadgenerated,array('class'=>'form-control submissionDeclarationField lead_generated','table'=>'lead_generated','id'=>'lead_generated','name'=>'lead_generated','placeholder'=>'')) !!}
                                                                                         </span>
                                                                                        @else
                                                                                            <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:15em; height:100%; display: inline-block; color:#0070C0">
                                                                                                {{strtoupper(config('constants.SPECIAL_SUBMISSION_DECLARATION.LEAD_GENERATED')[$leadgenerated])}}
                                                                                            </span>
                                                                                        @endif  
                                                                                       
                                                                                    @endif

                                                                                    @if(preg_match('/_FIELD_DISTANCE_/', $declaration['declaration']))
                                                                                            
                                                                                        @php

                                                                                            if(preg_match('/_FIELD_DISTANCE_/', $declaration['declaration'])){

                                                                                                $tokendistacne = explode('_FIELD_DISTANCE_', $declaration['declaration']);

                                                                                            } 
                                                                                        @endphp
                                                                                       
                                                                                        {!! $tokendistacne[0]  !!}
                                                                                        @if($is_review != 1 && $distfrombranch == '')
                                                                                        <span class="ml-1 mr-1" style="width: 20em;display: inline-block;">
                                                                                         {!! Form::select('dist_from_branch',$distFromBranch,$distfrombranch,array('class'=>'form-control submissionDeclarationField dist_from_branch','table'=>'dist_from_branch','id'=>'dist_from_branch','name'=>'dist_from_branch','placeholder'=>'')) !!}
                                                                                         </span>
                                                                                         @else
                                                                                            <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:15em; height:100%; display: inline-block; color:#0070C0">
                                                                                                {{strtoupper(config('constants.SPECIAL_SUBMISSION_DECLARATION.DIST_FROM_BRANCH')[$distfrombranch])}}
                                                                                            </span>
                                                                                        @endif                                                                  
                                                                                    @endif
                                                                                    
                                                                               
                                                                            </td>

                                                                            @else
                                                                            <td style="white-space: normal; margin-left: 10px;">
                                                                                {{$declaration['declaration']}}
                                                                            </td>
                                                                            @endif
                                                                        </tr>
                                                                        <tr>
                                                                            <td height="10"></td>
                                                                        </tr>
                                                                    @endforeach
                                                                @endif
                                                            </tbody>
                                                        </table>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td height="20"></td>
                                                </tr>
                                            
                                            </tbody>
                                        </table>
                                    </span>
                                </td>
                            </tr>
                            
                            <tr>
                                <td>
                                    <table style=" border: 1px solid #f1f1f1;" width="100%" cellspacing="0" cellpadding="0" bgcolor="#F2F2F2">
                                        <tr>
                                            <td height="10"></td>
                                        </tr>
                                        <tr>
                                            <td style="padding-left: 10px!important;">
                                                <table style="padding-left: 10px; padding-right: 10px;" width="80%">
                                                    <tbody>
                                                        <tr>
                                                            <td style="line-height: 30px;" width="10%">
                                                                Name of Staff
                                                            </td>
                                                            <td style="line-height: 30px;" width="40%">
                                                                <span style="background:white;padding:0em 1.1em 0.2em 1.1em;width:80%; height:100%; display: inline-block; color:#0070C0">
                                                                    {{ strtoupper($username)}}
                                                                </span>
                                                            </td>
                                                            <td style="line-height: 30px;" width="10%">
                                                                @if(($customer_type == "ETB") && ($customerOvdDetails['initial_funding_type'] == 3))
                                                                    Branch / Source
                                                                @else
                                                                    Branch
                                                                @endif
                                                            </td>
                                                            @if($is_review != 1 && !$is_aof_tracker)
                                                            <td style="line-height: 30px;" width="40%">
                                                                <span style="background:white;padding:0em 1.1em 0.2em 1.1em;  width:80%; height:100%; display: inline-block; color:#0070C0">
                                                                    <input type="text" maxlength="3" class="branch_id" name="branch_id" id="branch_id" value="{{strtoupper($accountDetails['branch_id'])}}" onkeyup="this.value = this.value.toUpperCase();" oninput="this.value = this.value.replace(/[^0-9]/gi, '').replace(/(\..*)\./g, '$1');">

                                                                </span>
                                                            </td>
                                                            @else
                                                                <td style="line-height: 30px;" width="30%">
                                                                    <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:80%; height:100%; display: inline-block; color:#0070C0">
                                                                            {{strtoupper($accountDetails['branch_id'])}}
                                                                    </span>
                                                                </td>
                                                            @endif
                                                        </tr>
                                                        <tr>
                                                            <td height="8"></td>
                                                        </tr>
                                                        @if($accountDetails['source'] != 'CC')
                                                        <tr>
                                                            <td style="line-height: 30px;" width="10%">
                                                               Segment
                                                            </td>

                                                        @if(($accountDetails['segment_code'] == '' || $is_review != 1) && !$is_aof_tracker )
                                                            <td style="line-height: 25px;" width="100%">
                                                                <div class="comments-blck segment-comments-blck" style="margin-left: 0px; width: 100%;">
                                                                   {!! Form::select('segment_list',$segmentList,$segment_list,array('class'=>'form-control segment_list AddAccountDetailsField',
                                                                    'table'=>'account_details','id'=>'segment_list','name'=>'segment_list','placeholder'=>'')) !!}

                                                                </div>
                                                            </td>
                                                        @else
                                                            <td style="line-height: 30px;" width="30%">
                                                                <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:80%; height:100%; display: inline-block; color:#0070C0">
                                                                    {{strtoupper($accountDetails['segment_code'])}}
                                                                </span>
                                                            </td>
                                                        @endif
                                                        </tr>
                                                        @endif
                                                        <tr>
                                                            <td height="8"></td>
                                                        </tr>
                                                        <tr>
                                                            <td style="line-height: 30px;" width="20%">
                                                                Date
                                                            </td>
                                                            <td style="line-height: 30px;" width="30%">
                                                                <span style="background:white;padding:0em 1.1em 0.2em 1.1em;  width:20%; height:100%; display: inline-block; color:#0070C0">
                                                                    @if(isset($created_at))
                                                                        {{\Carbon\Carbon::parse($created_at)->format('d')}}
                                                                    @else
                                                                        {{Carbon\Carbon::now()->format('d')}}
                                                                    @endif
                                                                </span>
                                                                <span style="background:white;padding:0em 1.1em 0.2em 1.1em;  width:20%; height:100%; display: inline-block; color:#0070C0">
                                                                    @if(isset($created_at))
                                                                        {{strtoupper(\Carbon\Carbon::parse($created_at)->format('M'))}}
                                                                    @else
                                                                        {{strtoupper(Carbon\Carbon::now()->format('M'))}}
                                                                    @endif
                                                                </span>
                                                                <span style="background:white;padding:0em 1.1em 0.2em 1.1em;width:20%;height:100%;display: inline-block;color:#0070C0;margin-left: 3px;">
                                                                    @if(isset($created_at))
                                                                        {{\Carbon\Carbon::parse($created_at)->format('Y')}}
                                                                    @else
                                                                        {{Carbon\Carbon::now()->format('Y')}}
                                                                    @endif
                                                                </span>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td height="8"></td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td height="20"></td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                                   
                        </tbody>
                    </table>
                </body>
                <div class="row">
                    <div class="col-md-12 text-center mt-3 mb-3">
                        @if($is_aof_tracker)
                            <!-- <a href="{{route('aoftracking')}}" class="btn btn-outline-grey mr-3">Back</a> -->
                        @else
                            @if(($is_review == 1) && ($status == 2))
                                <a href="{{route('bankdashboard')}}" class="btn btn-outline-grey mr-3">Back</a>
                            @else
                                <a class="btn btn-outline-grey mr-3"  onclick="redirectUrl('{{$formId}}','/bank/declaration')">Back</a>
                            @endif
                            @if($is_review == 1)
                                <!-- @if(!($status == 1) || ($status == 10))
                                    <a href="javascript:void(0)" class="btn btn-primary submit_to_npc display-none" id="{{$formId}}">Save and Continue</a>
                                @else
                                    <a href="javascript:void(0)" class="btn btn-primary" data-toggle="modal" data-target="#Username-blck" data-id="{{$formId}}" >Save and Continue</a>
                                @endif -->
                                @if(($status == 1) || ($status == 10) || ($status == 22) || ($status == 23))
                                    <a href="javascript:void(0)" class="btn btn-primary" data-bs-toggle="modal" id="submission_modal_button"  data-id="{{$formId}}" >Save and Continue</a>
                                @else
                                    <a href="javascript:void(0)" class="btn btn-primary submit_to_npc display-none" id="{{$formId}}">Save and Continue</a>
                                @endif
                            @else
                                <a href="javascript:void(0)" class="btn btn-primary disabled" id="submission_modal_button" data-bs-toggle="modal" data-id="{{$formId}}" >Save and Continue</a>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    </form>
</div>
@if($i_ao_trac)
<script  src="{{ asset('components/jquery/js/jquery.min.js') }}"></script>
<script  src="{{ asset('custom/js/app.js') }}"></script>
<script>
    masking_time_count = "{{Session::get('maks_timer') ?? ''}}";
    if(masking_time_count == ''){
        masking_time_count = 120000;
    }
    $(document).on("keydown",function(e){
    if(e.ctrlKey && e.keyCode == "80"){
        unmaskingfield();
    }
});
</script>

@endif
@push('scripts')
    <script>
        _userrole = "<?php echo Session::get('role'); ?>";

        if (_userrole == "11" ){
            _ccDefaultSolId = JSON.parse('<?php echo json_encode($ccDefaultSolId); ?>');
            $("#branch_id").val(_ccDefaultSolId);
        }
    </script>
@endpush