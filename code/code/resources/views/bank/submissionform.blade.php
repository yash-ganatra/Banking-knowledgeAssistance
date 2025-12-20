@php
$showImages = false;
$showNameOfStaff = false;
if(!function_exists('getInlineImage')){
function getInlineImage($image)
    {   
      try { 
            $imageData =  file_get_contents(storage_path('/uploads/markedattachments/'.$image));

            $dataUri = 'data:image/png;base64,' . base64_encode($imageData);

            return $dataUri;

       }
        catch(\Illuminate\Database\QueryException $e) {
            if (env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            return '';
        }
    }
}


if(!function_exists('getLogoImage')){
function getLogoImage()
    {   
      try { 
            $imageData =  file_get_contents(public_path('assets/images/dcb-logo-blue.png'));

            $dataUri = 'data:image/png;base64,' . base64_encode($imageData);

            return $dataUri;

       }
        catch(\Illuminate\Database\QueryException $e) {
            if (env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            return '';
        }
    }
}

if(isset($cifDeclarationDetails) && $cifDeclarationDetails != ''){
    $meetingdate = Carbon\Carbon::parse($cifDeclarationDetails->meeting_date)->format('d-m-Y');
    $leadgenerated = $cifDeclarationDetails->generated_lead;
    $distfrombranch = $cifDeclarationDetails->dist_from_branch;
    $custmeetinglocations = $cifDeclarationDetails->customer_location;
    $reasonforaccntopening = $cifDeclarationDetails->account_open_reason;
}

$custovd = $userDetails[0]['customerOvdDetails'];

switch ($accountDetails['scheme_code']) {
    case 'SB118':
        $specialCase = array('Label Code'=> $custovd->label_code); 
        break;
    
    case 'SB124':
        $specialCase = array('Elite Account Number'=> $custovd->elite_account_number); 
        break;
    default:
        if ($custovd->customer_account_type == 3) {
            $specialCase = array('HRMS Number'=> $custovd->empno); 
        }else{
            $specialCase = array();
        }
        break;
}

$acc_name = isset($customerOvdDetails['0']->customer_full_name) && $customerOvdDetails['0']->customer_full_name != '' ? $customerOvdDetails['0']->customer_full_name : '';
    

$image_mask_blur = "";
$def_blur_image = "";
$is_review=isset($is_review) && $is_review!=''?$is_review:'';
if(isset($is_review)==1){
    $image_mask_blur = "uploaded-img-ovd";
    $def_blur_image = "style=filter:blur(30px);";
}

$i_ao_trac=false;
if(isset($is_aof_tracker)){
    if($is_aof_tracker){
        $i_ao_trac=true;
    }
}

$is_huf_display = false;
 if($accountDetails['constitution'] == 'NON_IND_HUF'){
    $is_huf_display = true;
 }

@endphp
<html>
<head>

@if($i_ao_trac)
<style>
@media print{
    #maskfields , #unmaskfields{
        display:none !important;
    }
}
</style>
@endif
<style type="text/css" >
    table { page-break-inside:auto }
    tr    { page-break-inside:avoid; page-break-after:auto }
    .page-break-before{page-break-before:always!important;}.page-break-after{page-break-after:always!important;}
    .pdf-table{font-family:Franklin Gothic Book,arial, sans-serif; font-size:12px; margin-top: 20px;width: 100%;padding-left: 15px!important;}
    .pdf-table tr{page-break-inside:avoid; page-break-after:auto}
    p{ font-weight:bold;}

    ol[type="a"] {list-style-type: none;}
    ol[type="a"] li {counter-increment: item;}
    ol[type="a"] li::before {content: "(" counter(item, lower-alpha) ".) ";}

    @page { padding: 1em;margin: 1em; }
</style>
</head>
<body style="size: 8.25in 11.75in;background-color:#F6F7FB">
<table class="pdf-table" align="center" >
    <tbody>
        <tr>
            <td>
                <span style="font-size: 20px; color: #364fcc;">Account Opening Form</span>
                <br>For Individuals<br>
                Form No:
                <span id="application_no">{{strtoupper($accountDetails['aof_number'])}}</span><br>
            </td>
			    <td>   
					<div style="float:right; padding-right: 15px;">
						<img src="{{ getLogoImage() }}" height="50">
					</div>
				</td>	
		</tr>
        <tr>
			<td>
                <div style="float:left;">
					{!!DNS1D::getBarcodeHTML($accountDetails['aof_number'], 'I25')!!}
				</div>						
            </td>
        </tr>			
         <tr>
            <td height="20"></td>
        </tr>	
    </tbody>
</table>
<table class="pdf-table" align="center">
    <tbody>
            <tr>
                <td height="20"></td>
            </tr>
            <tr>
                <td style="color: #364fcc; padding-left: 10px; font-size: 20px; padding-left: 10px!important;" height="30">
                    RELATIONSHIP FORM
                </td>
            </tr>
            <tr>
                <td colspan="2" style="padding-left: 10px!important;">
                    I/We hereby apply for a relationship with your Bank under which I/We wish to open an account(s).
                </td>
            </tr>
            <tr>
                <td height="10"></td>
            </tr>

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
                    <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:24em; height:30px; display: inline-block; color:#0070C0">
                        {{strtoupper($accountDetails['account_type'])}}
                    </span>
                </td>
                @if(!$is_huf_display)
                <td style="line-height: 30px;" width="30%">
                    <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:24em; height:30px; display: inline-block; color:#0070C0">
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
                    <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:24em; height:30px; display: inline-block; color:#0070C0">
                        {{strtoupper($accountDetails['mode_of_operation'])}}
                    </span>
                </td>
                <td style="line-height: 30px;" width="30%">
                    <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:24em; height:30px; display: inline-block; color:#0070C0">
                        @if($accountDetails['account_type_id'] == 3)
                            {{strtoupper($accountDetails['tdscheme_code'])}}
                        @else
                            {{strtoupper($accountDetails['scheme_code'])}}
                        @endif
                    </span>
                </td>
            </tr>
            <tr>
                <td height="8"></td>
            </tr>
            @if(isset($accountDetails['td_scheme_code']))
                <tr>
                    <td style="line-height: 30px;" width="20%">
                        TD Scheme
                    </td>
                </tr>
                <tr>
                    <td style="line-height: 30px;" width="30%">
                        <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:80%; height:30px; display: inline-block; color:#0070C0">
                            {{strtoupper($accountDetails['td_scheme_code'])}}
                        </span>
                    </td>
                </tr>
            @endif
            <tr>
                <td height="20"></td>
            </tr>
    </tbody>
</table>

    @if(count($userDetails) > 0)
        @for($i = 1;$i <= $no_of_account_holders ;$i++)
            @php
                $customerOvdDetails = (array) $userDetails[$i-1]['customerOvdDetails'];
                $customer_type = $customerOvdDetails['is_new_customer'] == '1'? "NTB":"ETB";
                $riskDetails = (array) $userDetails[$i-1]['riskDetails'];
                
                $huf_display = false;
                if($accountDetails['constitution'] == 'NON_IND_HUF' && $i ==2){
                    $huf_display = true;
                }
                
            @endphp
            <table class="pdf-table"  align="center">
             <tbody>
            
            <tr>
                <td colspan="2" style="color: #364fcc; padding-left: 10px; font-size: 20px; padding-left: 10px!important;text-align: left;" height="30">
                    @if($is_huf_display)
                    PERSONAL DETAILS: {{ $i == 1 ? 'KARTA/MANAGER' : 'HUF' }}
                    @else
                    PERSONAL DETAILS: {{ $i == 1 ? 'PRIMARY APPLICANT' : 'APPLICANT ' . $i }}
                    @endif
                </td>
            </tr>
            
            @if($customer_type != "ETB")
                
                <tr>
                    <td style="line-height: 30px;" width="20%">
                        {{ $huf_display ? ' Name' : 'Name (Name as per OVD)' }}
                    </td>
                    @if(!$huf_display)
                    <td style="line-height: 30px;" width="20%">
                        Name on Card (Short Name)
                    </td>
                    @endif
                </tr>
                <tr>
                    <td style="line-height: 30px;margin-bottom:10px;" width="30%">
                        <span style="background:white;padding:0em 1.1em 0.2em 1.1em;  width:24em; height:2.1em; display: inline-block; color:#0070C0">
                            {{strtoupper($customerOvdDetails['title']).' '.($customerOvdDetails['first_name']).' '.($customerOvdDetails['middle_name']).' '.($customerOvdDetails['last_name'])}}
                        </span>
                    </td>
                    @if(!$huf_display)
                    <td style="line-height: 30px;margin-bottom:10px;" width="30%">
                        <span style="background:white;padding:0em 1.1em 0.2em 1.1em;  width:24em; height:2.1em; display: inline-block; color:#0070C0">
                            {{strtoupper($customerOvdDetails['short_name'])}}
                        </span>
                    </td>
                    @endif
                </tr>
                <tr>
                    <td height="8"></td>
                </tr>
                <tr>
                    @if($customerOvdDetails['pf_type'] == 'pancard')
                    <td style="line-height: 30px;" width="20%">
                        PAN Number
                    </td>
                    @else
                    <td style="line-height: 30px;" width="20%">
                        Form 60
                    </td>
                    @endif
                    <td style="line-height: 30px;" width="20%">
                        {{ $huf_display ? 'DOF (Date of Formation)' : 'Date of Birth' }}
                    </td>
                </tr>
                <tr>
                    @if($customerOvdDetails['pf_type'] == 'pancard')
                    <td style="line-height: 30px;" width="30%">
                        <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:24em; height:30px; display: inline-block; color:#0070C0">
                            {{strtoupper($customerOvdDetails['pancard_no'])}}
                        </span>
                    </td>
                    @else
                    <td style="line-height: 30px;" width="30%">
                        <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:24em; height:30px; display: inline-block; color:#0070C0">
                            Submitted
                        </span>
                    </td>
                    @endif
                    <td style="line-height: 30px;" width="30%">
                        <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:24em; height:30px; display: inline-block; color:#0070C0">
                            {{strtoupper(\Carbon\Carbon::parse($customerOvdDetails['dob'])->format('d M Y'))}}
                        </span>
                    </td>
                </tr>
                <tr>
                    <td height="8"></td>
                </tr>
                @if(!$huf_display) {{--  Start non display in huf  --}}
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
                        <span style="background:white;padding:0em 1.1em 0.2em 1.1em;  width:24em; height:2.1em; display: inline-block; color:#0070C0">
                          
                            @if($customerOvdDetails['proof_of_identity'] == "Aadhaar Photocopy")
                                XXXX-XXXX{{substr($customerOvdDetails['id_proof_card_number'],9,11)}}
                            @else
                                {{strtoupper($customerOvdDetails['id_proof_card_number'])}}
                            @endif
                        </span>
                    </td>

                    @if($customerOvdDetails['proof_of_identity'] == 'Passport')
                        <td style="line-height: 30px;margin-bottom:10px;" width="30%">
                                <span style="background:white;padding:0em 1.1em 0.2em 1.1em;  width:24em; height:2.1em; display: inline-block; color:#0070C0">
                                      {{strtoupper(\Carbon\Carbon::parse($customerOvdDetails['passport_driving_expire'])->format('d M Y'))}}

                                </span>
                        </td>
                    @elseif($customerOvdDetails['proof_of_identity'] == 'Driving Licence')
                        <td style="line-height: 30px;margin-bottom:10px;" width="30%">
                                <span style="background:white;padding:0em 1.1em 0.2em 1.1em;  width:24em; height:2.1em; display: inline-block; color:#0070C0">
                                      {{strtoupper(\Carbon\Carbon::parse($customerOvdDetails['passport_driving_expire'])->format('d M Y'))}}

                                </span>
                        </td>
                    @elseif($customerOvdDetails['proof_of_identity'] == 'Aadhaar')
                        <td style="line-height: 30px;margin-bottom:10px;" width="30%">
                            <span style="background:white;padding:0em 1.1em 0.2em 1.1em;  width:24em; height:2.1em; display: inline-block; color:#0070C0">
                                @if($customerOvdDetails['aadhar_link'] == 1)
                                    YES
                                @else
                                    NO
                                @endif
                            </span>
                        </td>
                    @endif
                </tr>

        @if(!empty($customerOvdDetails["id_psprt_dri_issue"]) && in_array($customerOvdDetails["proof_of_address"],["Passport","Driving Licence"]))
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
                                <span style="background:white;padding:0em 1.1em 0.2em 1.1em;  width:24em; height:2.1em; display: inline-block; color:#0070C0">
                                      {{strtoupper(\Carbon\Carbon::parse($customerOvdDetails['id_psprt_dri_issue'])->format('d M Y'))}}

                                </span>
                        </td>
                    @elseif($customerOvdDetails['proof_of_identity'] == 'Driving Licence')
                        <td style="line-height: 30px;margin-bottom:10px;" width="30%">
                                <span style="background:white;padding:0em 1.1em 0.2em 1.1em;  width:24em; height:2.1em; display: inline-block; color:#0070C0">
                                      {{strtoupper(\Carbon\Carbon::parse($customerOvdDetails['id_psprt_dri_issue'])->format('d M Y'))}}

                                </span>
                        </td>
                    @endif

                </tr>
            @endif
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
                        <td style="line-height: 50px;width:16%;padding-left: 16%;padding-bottom: 3em">
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
                        Father/Spouse Name
                    </td>
                    <td style="line-height: 25px;" width="20%">
                        Mother's Full Name
                    </td>
                </tr>
                 <tr>
                    <td style="line-height: 30px;margin-bottom:10px;" width="30%">
                        <span style="background:white;padding:0em 1.1em 0.2em 1.1em;  width:24em; height:2.1em; display: inline-block; color:#0070C0">
                            {{strtoupper($customerOvdDetails['father_name'])}}
                        </span>
                    </td>
                    <td style="line-height: 30px;margin-bottom:10px;" width="30%">
                        <span style="background:white;padding:0em 1.1em 0.2em 1.1em;  width:24em; height:2.1em; display: inline-block; color:#0070C0">
                            {{strtoupper($customerOvdDetails['mother_full_name'])}}
                        </span>
                    </td>
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
                        <span style="background:white;padding:0em 1.1em 0.2em 1.1em;  width:24em; height:2.1em; display: inline-block; color:#0070C0">
                            {{strtoupper($customerOvdDetails['mothers_maiden_name'])}}
                        </span>
                    </td>
                    <td style="line-height: 30px;" width="30%">
                        <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:24em; height:30px; display: inline-block; color:#0070C0">
                            {{strtoupper(config('constants.GENDER.'.$customerOvdDetails['gender']))}}
                        </span>
                    </td>
                </tr>
                @endif {{--  end non display in huf  --}}
               
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
                        <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:24em; height:30px; display: inline-block; color:#0070C0">
                            {{strtoupper($customerOvdDetails['marital_status'])}}
                        </span>
                    </td>
                    <td style="line-height: 30px;" width="30%">
                        <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:24em; height:30px; display: inline-block; color:#0070C0">
                            {{strtoupper($customerOvdDetails['residential_status'])}}
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
                        <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:24em; height:30px; display: inline-block; color:#0070C0">
                            <label {{$is_review==1 || $i_ao_trac ? "style=display:none":""}} class="{{$is_review==1 || $i_ao_trac ?  "unmaskingfield": ""}} enc_label">
                            {{($customerOvdDetails['mobile_number'])}}
                            </label>
                            @if($is_review==1 || $i_ao_trac)
                                <span class="maskingfield">*********</span>
                            @endif
                        </span>
                    </td>
                    <td style="line-height: 30px;" width="30%">
                        <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:24em; height:30px; display: inline-block; color:#0070C0">
                            <label {{$is_review==1 || $i_ao_trac ? "style=display:none":""}} class="{{$is_review==1 || $i_ao_trac ?  "unmaskingfield": ""}} enc_label">
                            {{($customerOvdDetails['email'])}}
                            </label>
                            @if($is_review==1 || $i_ao_trac)
                            <span class="maskingfield">*********</span>
                            @endif
                        </span>
                    </td>
                </tr>
                @if($customerOvdDetails['address_per_flag'] == '' ||$customerOvdDetails['address_per_flag'] == '0')
                <tr>
                    <td style="line-height: 30px;" width="20%">
                        Proof Of Permanent Address Type
                    </td>
                        <td style="line-height: 30px;" width="20%">
                            {{($customerOvdDetails['proof_of_address']) . ' Number' }}
                        </td>
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
                                @else
                                    {{strtoupper($customerOvdDetails['add_proof_card_number'])}}
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
                        <td style="line-height: 30px;" width="20%">{{strtoupper($customerOvdDetails['proof_of_address'])}} Issues Date</td>
                    </tr>
                    <tr>
                        <td style="line-height: 30px;" width="20%">
                            <span style="background:white;padding:0em 1.1em 0.2em 1.1em;  width:80%; /*height:100%;*/ display: inline-block; color:#0070C0">
                            {{date("d-M-Y",strtotime($customerOvdDetails['add_psprt_dri_issue']))}}
                            </span>
                        </td>
                    </tr> 
                    @endif
                    @endif
                <tr>
                    <td style="line-height: 30px;" width="20%">
                        {{ $huf_display ? 'Registered Address' : 'Address (as per OVD)' }}
                    </td>
                    <td style="line-height: 30px;" width="20%">
                            Communication Address
                    </td>
                </tr>
                <tr>
                        <td style="/*line-height: 30px;*/margin-bottom:10px;" width="30%" >
                            <span style="background:white;padding:0em 1.1em 0.2em 1.1em;  width:24em; display: inline-block; color:#0070C0;">
                                {{strtoupper($customerOvdDetails['per_address_line1'])}}<br>
                                {{strtoupper($customerOvdDetails['per_address_line2'])}}<br>
                                {{strtoupper($customerOvdDetails['per_country'])}}<br>
                                {{strtoupper($customerOvdDetails['per_pincode'])}}<br>
                                {{strtoupper($customerOvdDetails['per_state'])}}<br>
                                {{strtoupper($customerOvdDetails['per_city'])}}<br>
                                {{strtoupper($customerOvdDetails['per_landmark'])}}
                            </span>
                        </td>
                        <td style="/*line-height: 30px;*/margin-bottom:10px;" width="30%">
                            <span style="background:white;padding:0em 1.1em 0.2em 1.1em;  width:24em; display: inline-block; color:#0070C0">
                                {{strtoupper($customerOvdDetails['current_address_line1'])}}<br>
                                {{strtoupper($customerOvdDetails['current_address_line2'])}}<br>
                                {{strtoupper($customerOvdDetails['current_country'])}}<br>
                                {{strtoupper($customerOvdDetails['current_pincode'])}}<br>
                                {{strtoupper($customerOvdDetails['current_state'])}}<br>
                                {{strtoupper($customerOvdDetails['current_city'])}}<br>
                                {{strtoupper($customerOvdDetails['current_landmark'])}}
                            </span>
                        </td>
                </tr>
                {{-- FOR KARTA HUF --}}
                @if($huf_display)
                <tr>
                    <td style="line-height: 30px;" width="20%">
                        Relationship Between HUF & Signatory
                    </td>
                </tr>
                <tr>
                    <td style="line-height: 30px;" width="30%">
                        <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:24em; height:2.1em; display: inline-block; color:#0070C0">
                                    {{strtoupper($customerOvdDetails['huf_signatory_relation'])}}
                        </span>
                    </td>
                </tr>

                @if($huf_display)
                @foreach ($huf_cop_row as $key => $co)
                @php
                $co = (array) $co;
                @endphp
                <tr>
                    <td style="line-height: 30px;" width="20%">
                                    Coparcenor Name -{{$key+1}}
                    </td>
                          
                                <td style="line-height: 30px;" width="20%">
                                    Coparcenor Type -{{$key+1}}
                                </td>
                </tr>

                <tr>
                    <td style="line-height: 30px;" width="30%">
                        <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:24em; height:2.1em; display: inline-block; color:#0070C0">
                            {{strtoupper($co['coparcenar_name'])}}
                        </span>
                    </td>

                    <td style="line-height: 30px;" width="30%">
                        <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:24em; height:2.1em; display: inline-block; color:#0070C0">
                            {{strtoupper($co['coparcener_type'])}}
                        </span>
                    </td>
                </tr>

                <tr>
                    <td style="line-height: 30px;" width="20%">
                        Coparcenor Relation -{{$key+1}}
                    </td>

                    <td style="line-height: 30px;" width="20%">
                        Coparcenor DOB -{{$key+1}}
                    </td>
                </tr>
                
                <tr>
                    <td style="line-height: 30px;" width="30%">
                        <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:24em; height:2.1em; display: inline-block; color:#0070C0">
                            {{strtoupper($co['relation'])}}
                        </span>
                    </td>

                    <td style="line-height: 30px;" width="30%">
                        <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:24em; height:2.1em; display: inline-block; color:#0070C0">
                            {{strtoupper($co['dob'])}}
                        </span>
                    </td>
                </tr>
                @endforeach
                @endif

                @endif
                {{-- END --}}
                <tr>
                    <td style="line-height: 30px;" width="20%">
                    @if($accountType != 3)
                        Card Type
                    @endif
                    </td>
                </tr>
                <tr>
                    <td style="line-height: 30px;" width="30%">
                    @if($accountType != 3)
                        <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:24em; height:2.1em; display: inline-block; color:#0070C0">
                            {{$accountDetails['card_description']}}
                        </span>
                    @endif
                    </td>
                </tr>
        @else
             <tr>
                <td style="line-height: 30px;" width="20%">
                    Existing Customer:
                </td>
            </tr>
            <tr>
                <td style="line-height: 30px;margin-bottom:10px;" width="30%">
                    <span style="background:white;padding:0em 1.1em 0.2em 1.1em;  width:24em; height:2.1em; display: inline-block; color:#0070C0">
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
                    <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:24em; height:30px; display: inline-block; color:#0070C0">
                        <label {{$is_review==1 || $i_ao_trac ? "style=display:none":""}} class="{{$is_review==1 || $i_ao_trac ?  "unmaskingfield": ""}} enc_label">
                            {{($customerOvdDetails['mobile_number'])}}
                            </label>
                            @if($is_review==1 || $i_ao_trac)
                                <span class="maskingfield">*********</span>
                            @endif
                    </span>
                </td>
                <td style="line-height: 30px;" width="30%">
                    <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:24em; height:30px; display: inline-block; color:#0070C0">
                        <label {{$is_review==1 || $i_ao_trac ? "style=display:none":""}} class="{{$is_review==1 || $i_ao_trac ?  "unmaskingfield": ""}} enc_label">
                            {{($customerOvdDetails['email'])}}
                            </label>
                            @if($is_review==1 || $i_ao_trac)
                            <span class="maskingfield">*********</span>
                            @endif
                    </span>
                </td>
            </tr>
        @endif
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
                <td style="line-height: 30px;margin-bottom:10px;" width="30%">
                    <span style="background:white;padding:0em 1.1em 0.2em 1.1em;  width:24em; height:2.1em; display: inline-block; color:#0070C0">
                        {{$caseValue}}
                    </span>
                </td>
            </tr>
            @endforeach
        @endif
         <tr>
            <td height="20"></td>
        </tr>  
    </tbody>
</table>

@if($customer_type != "ETB")
<table class="pdf-table" style="page-break-before: always;" align="center">
    <tbody>
                
        <tr>
            <td style="color: #364fcc; padding-left: 10px; font-size: 20px; padding-left: 10px!important;" height="30">
                CIDD
            </td>
        </tr>
        <tr>
            <td height="8"></td>
        </tr>                       
        <tr>
            @if(!$is_huf_display)
            <td style="line-height: 30px;" width="20%">
                Education
            </td>
            @endif
            <td style="line-height: 30px;" width="20%">
                Gross Income
            </td>
        </tr>
        <tr>
            @if(!$is_huf_display)
            <td style="line-height: 30px;" width="30%">
                <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:24em; height:30px; display: inline-block; color:#0070C0">
                    {{strtoupper(config('constants.EDUCATION.'.$riskDetails['education']))}}
                </span>
            </td>
            @endif
            <td style="line-height: 30px;" width="30%">
                <span
                    style="background:white;padding:0em 1.1em 0.2em 1.1em; width:24em; height:30px; display: inline-block; color:#0070C0">
                    <!-- {{ strtoupper(config('constants.GROSS_INCOME.' . $riskDetails['gross_income'])) }} -->
                    {{ strtoupper($riskDetails['gross_income']) }}
                </span>
            </td>

        </tr>
        @if($accountType != 3)
            <tr>
                <td style="line-height: 30px;" width="20%">
                    Expected Turnover
                </td>
                <td style="line-height: 30px;" width="20%">
                    PEP
                </td>
            </tr>
            <tr>
                <td style="line-height: 30px;" width="30%">
                    <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:24em; height:30px; display: inline-block; color:#0070C0">
                        {{htmlspecialchars(config('constants.ANNUAL_TURNOVER.'.$riskDetails['annual_turnover']))}}
                    </span>
                </td>
                <td style="line-height: 30px;" width="30%">
                    <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:24em; height:30px; display: inline-block; color:#0070C0">
                        {{strtoupper($riskDetails['pep'])}}
                    </span>
                </td>
            </tr>
        @endif
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
                <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:24em; height:30px; display: inline-block; color:#0070C0">{{strtoupper($riskDetails['occupation'])}}</span>
            </td>

            @if($riskDetails['occupation'] == 'OTHERS' || $riskDetails['occupation'] == 'OTHER - SELF EMPLOYED PROFESSIONAL' || $riskDetails['occupation'] == 'OTHER - SELF EMPLOYED - NON-PROFESSIONAL' || $riskDetails['occupation'] == 'OTHER - TRADING')
            <td style="line-height: 30px;" width="30%">
                <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:24em; height:30px; display: inline-block; color:#0070C0">
                   {{strtoupper($riskDetails['other_occupation'])}}
                </span>
            </td>
            @endif
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
            <tr>
                <td style="line-height: 30px;" width="30%">
                    <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:24em; height:30px; display: inline-block; color:#0070C0">
                        @php
                            $source_of_funds = explode(',',$riskDetails['source_of_funds']);
                        @endphp
                        @foreach($source_of_funds as $source_of_fund)
                            @if(end($source_of_funds) == $source_of_fund)
                                {{strtoupper($source_of_fund)}}
                            @else
                            {{strtoupper($source_of_fund)}}
                            @endif
                        @endforeach
                    </span>
                </td>
                @if(in_array($source_of_funds,[5]))
                    <td style="line-height: 30px;" width="30%">
                        <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:24em; height:30px; display: inline-block; color:#0070C0">
                            {{strtoupper($riskDetails['source_others_comments'])}}
                        </span>
                    </td>
                @endif
            </tr>
        @endif
         <tr>
            <td height="20"></td>
        </tr>   
    </tbody>
</table>
<table class="pdf-table" align="center">
    <tbody>
               
        <tr>
            <td style="color: #364fcc; padding-left: 10px; font-size: 20px; padding-left: 10px!important;" height="30">
                FATCA
            </td>
        </tr>
        <tr>
            <td height="8"></td>
        </tr>                         
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
                <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:24em; height:30px; display: inline-block; color:#0070C0">
                    {{strtoupper($riskDetails['country_name'])}}
                </span>
            </td>
            <td style="line-height: 30px;" width="30%">
                <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:24em; height:30px; display: inline-block; color:#0070C0">
                    {{strtoupper($riskDetails['country_of_birth'])}}
                </span>
            </td>
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
                <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:24em; height:30px; display: inline-block; color:#0070C0">
                    {{strtoupper($riskDetails['citizenship'])}}
                </span>
            </td>
            <td style="line-height: 30px;" width="30%">
                <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:24em; height:30px; display: inline-block; color:#0070C0">
                    {{strtoupper($riskDetails['place_of_birth'])}}
                </span>
            </td>
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
              <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:24em; height:30px; display: inline-block; color:#0070C0">
                    {{strtoupper($riskDetails['residence'])}}
              </span>
            </td>
            <td style="line-height: 30px;" width="30%">
                <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:24em; height:30px; display: inline-block; color:#0070C0">
                    @if($riskDetails['us_person'] == 1)
                        YES
                    @else
                        NO
                    @endif
                </span>
            </td>
        </tr>
        @if($riskDetails['us_person'] == 1)
            <tr>
                <td style="line-height: 30px;" width="20%">
                    Tin
                </td>
            </tr>
            <tr>
                <td style="line-height: 30px;" width="30%">
                    <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:24em; height:30px; display: inline-block; color:#0070C0">
                        {{strtoupper($riskDetails['tin'])}}
                    </span>
                </td>
            </tr>
        @endif
         <tr>
            <td height="20"></td>
        </tr>   
    </tbody>
</table>
@else
@if($accountDetails['account_type_id'] != 3)
<table class="pdf-table" align="center">
    <tbody>
                   
        <tr>
            <td style="color: #364fcc; padding-left: 10px; font-size: 20px; padding-left: 10px!important;" height="30">
                CIDD
            </td>
        </tr>
        <tr>
            <td height="8"></td>
        </tr>   
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
                <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:24em; height:30px; display: inline-block; color:#0070C0">
                    {{strtoupper(config('constants.EDUCATION.'.$riskDetails['education']))}}
                </span>
            </td>
            <td style="line-height: 30px;" width="30%">
              
                <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:24em; height:30px; display: inline-block; color:#0070C0">
                        <!-- {{strtoupper($riskDetails['occupation'])}} -->
                </span>
                
            </td>
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
                style="background:white;padding:0em 1.1em 0.2em 1.1em; width:24em; height:30px; display: inline-block; color:#0070C0">
                <!-- {{ strtoupper(config('constants.GROSS_INCOME.' . $riskDetails['gross_income'])) }} -->
                {{ strtoupper($riskDetails['gross_income']) }}
                </span>
            </td>
            <td style="line-height: 30px;" width="30%">
                <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:24em; height:30px; display: inline-block; color:#0070C0">
                    {{strtoupper(config('constants.ANNUAL_TURNOVER.'.$riskDetails['annual_turnover']))}}
                </span>
            </td>
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
                <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:22em height:30px; display: inline-block; color:#0070C0">
                        {{strtoupper($riskDetails['source_of_funds'])}}
                </span>
            </td>
            @if(in_array($source_of_fund,[5]))
                <td style="line-height: 30px;" width="30%">
                    <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:24em; height:30px; display: inline-block; color:#0070C0">
                        {{strtoupper($riskDetails['source_others_comments'])}}
                    </span>
                </td>
            @endif
        </tr>
        <tr>
            <td height="20"></td>
        </tr>                            
    </tbody>
</table>
<table class="pdf-table" align="center">
    <tbody>
    
        <tr>
            <td style="color: #364fcc; padding-left: 10px; font-size: 20px; padding-left: 10px!important;" height="30">
                FATCA
            </td>
        </tr>
         <tr>
            <td height="8"></td>
        </tr>   
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
                    <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:24em; height:30px; display: inline-block; color:#0070C0">
                        {{strtoupper($riskDetails['country_name'])}}
                    </span>
                </td>
                <td style="line-height: 30px;" width="30%">
                    <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:24em; height:30px; display: inline-block; color:#0070C0">
                        {{strtoupper($riskDetails['country_of_birth'])}}
                    </span>
                </td>
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
                    <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:24em; height:30px; display: inline-block; color:#0070C0">
                        {{strtoupper($riskDetails['citizenship'])}}
                    </span>
                </td>
                <td style="line-height: 30px;" width="30%">
                    <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:24em; height:30px; display: inline-block; color:#0070C0">
                        @if($riskDetails['citizenship'] == 1)
                            Yes
                        @else
                            No
                        @endif
                    </span>
                </td>
            </tr>
        @if($riskDetails['citizenship'] == 1)
            <tr>
                <td style="line-height: 30px;" width="20%">
                    Tin
                </td>
            </tr>
             <tr>
                <td style="line-height: 30px;" width="30%">
                    <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:24em; height:30px; display: inline-block; color:#0070C0">
                        {{strtoupper($riskDetails['tin'])}}
                    </span>
                </td>
            </tr>
        @endif
        <tr>
            <td height="20"></td>
        </tr>                              
     </tbody>
    </table>
        @endif
    @endif
@endfor
@endif
@if(count($nomineeDetails) > 0)
<table class="pdf-table" style="page-break-before: always;" align="center">
    <tbody>
        @for($k = 0;$k <= count($nomineeDetails) - 1;$k++)
            @php
                $nomineeData = (array) $nomineeDetails[$k];
            @endphp
            
             @if(!$is_huf_display)
            <tr>
                <td style="color: #364fcc; padding-left: 10px; font-size: 20px; padding-left: 10px!important;" height="30">
                    @if($k == 0)
                        NOMINEE DETAILS
                    @else
                        TD NOMINEE DETAILS
                    @endif
                </td>
            </tr>
            @endif
            @if($nomineeData['nominee_exists'] == "yes")
                                    
                <tr>
                    <td style="line-height: 30px;" width="20%">
                        Nominee Name
                    </td>
                    
                </tr>
                <tr>
                    <td style="line-height: 30px;" width="30%">
                        <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:24em; height:30px; display: inline-block; color:#0070C0">
                            {{strtoupper($nomineeData['nominee_name'])}}
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
                        <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:24em; height:30px; display: inline-block; color:#0070C0">
                            {{strtoupper(\Carbon\Carbon::parse($nomineeData['nominee_dob'])->format('d M Y'))}}
                        </span>
                    </td>
                    <td style="line-height: 30px;" width="30%">
                        <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:24em; height:30px; display: inline-block; color:#0070C0">
                            {{strtoupper($nomineeData['relatinship_applicant'])}}
                        </span>
                    </td>
                </tr>
                <tr>
                    <td height="8"></td>
                </tr>
                <tr>
                    <td style="line-height: 30px;" width="20%">
                         Address
                    </td>
                </tr>
                 <tr>
                    <td height="8"></td>
                </tr>
                <tr>
                    <td style="/*line-height: 30px;*/margin-bottom:10px;" width="30%" >
                            <span style="background:white;padding:0em 1.1em 0.2em 1.1em;  width:24em; display: inline-block; color:#0070C0;">
                             {{strtoupper($nomineeData['nominee_address_line1'])}}<br>
                             {{strtoupper($nomineeData['nominee_address_line2'])}}<br>
                            {{strtoupper($nomineeData['nominee_country'])}}<br>
                            {{strtoupper($nomineeData['nominee_pincode'])}}<br>
                            {{strtoupper($nomineeData['nominee_state'])}}<br>
                            {{strtoupper($nomineeData['nominee_city'])}}
                        </span>
                    </td>
                    
                </tr>
                @if($nomineeData['nominee_age'] < 18)
                    <tr>
                        <td style="line-height: 30px;" width="20%">
                            Guardian Name
                        </td>
                         <td style="line-height: 30px;" width="20%">
                           Relationship With Nominee
                        </td>
                        
                    </tr>
                    <tr>
                        <td style="line-height: 30px;" width="30%">
                            <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:24em; height:30px; display: inline-block; color:#0070C0">
                                {{strtoupper($nomineeData['guardian_name'])}}
                            </span>
                        </td>
                        <td style="line-height: 30px;" width="30%">
                            <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:24em; height:30px; display: inline-block; color:#0070C0">
                                {{strtoupper($nomineeData['relatinship_applicant_guardian'])}}
                            </span>
                        </td>
                    
                    </tr>
                    <tr>
                        <td style="line-height: 30px;" width="20%">
                            Guardian Address
                        </td>
                    </tr>
                    <tr>
                        <td style="/*line-height: 30px;*/margin-bottom:10px;" width="30%" >
                            <span style="background:white;padding:0em 1.1em 0.2em 1.1em;  width:24em; display: inline-block; color:#0070C0;">
                            {{strtoupper($nomineeData['guardian_address_line1'])}}<br>
                            {{strtoupper($nomineeData['guardian_address_line2'])}}<br>
                            {{strtoupper($nomineeData['guardian_country'])}}<br>
                            {{strtoupper($nomineeData['guardian_state'])}}<br>
                            {{strtoupper($nomineeData['guardian_pincode'])}}<br>
                            {{strtoupper($nomineeData['guardian_city'])}}
                            </span>
                        </td>
                    </tr>
                @endif
                                                
                @else
                @if(!$is_huf_display)
                    <tr style="background:white;padding:0em 1.1em 0.2em 1.1em; width:100%; height:30px; color:#000">
                        <td>
                           Customer Comment: ( No, I/We do not wish to nominate anyone. )
                        </td>
                    </tr>
                @endif

                @endif
        @endfor
        <tr>
         <td height="20"></td>
        </tr>
         </tbody>
</table>
@else
@if(!$is_huf_display)
    <tr style="background:white;padding:0em 1.1em 0.2em 1.1em; width:100%; height:30px; color:#000">
        <td>
            Customer Comment: ( No, I/We do not wish to nominate anyone. )
        </td>
    </tr>
@endif
@endif
<table class="pdf-table"  align="center">
    <tbody>
        <tr>
            <td style="color: #364fcc; padding-left: 10px; font-size: 20px; padding-left: 10px!important;" height="30">
                INITIAL FUNDING
            </td>
        </tr>
        <tr>
         <td height="8"></td>
        </tr>
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
                @if($customerOvdDetails['amount'] != '')
                <td style="line-height: 30px;" width="20%">
                    Funding Source
                </td>
               
                @endif
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
                <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:24em; height:30px; display: inline-block; color:#0070C0">
                    {{strtoupper(config('constants.INITIAL_FUNDING_TYPE.'.$customerOvdDetails['initial_funding_type']))}}
                </span>
            </td>
            @if($customerOvdDetails['initial_funding_type'] == 5)
                    @if($customerOvdDetails['amount'] != '')
                <td style="line-height: 30px;" width="30%">
                        <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:24em; height:30px; display: inline-block; color:#0070C0">
                            {{$customerOvdDetails['funding_source']}}
                        </span>
                    @if($customerOvdDetails["others_type"] == 0)
                        <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:80%; height:100%; display: inline-block; color:#0070C0">
                            NILL IP
                        </span>
                    @endif
                </td>
                @endif
            @else
                <td style="line-height: 30px;" width="30%">
                    <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:24em; height:30px; display: inline-block; color:#0070C0">
                        {{strtoupper(\Carbon\Carbon::parse($customerOvdDetails['initial_funding_date'])->format('d M Y'))}}
                    </span>
                </td>
            @endif
        </tr>
        <tr>
            <td style="line-height: 30px;" width="20%">
                Amount
            </td>
            @if($customerOvdDetails['initial_funding_type'] != 5 && $customerOvdDetails['initial_funding_type'] != 3)
                <td style="line-height: 30px;" width="20%">
                    Reference
                </td>
            @endif
        </tr>
        <tr>
            <td style="line-height: 30px;" width="30%">
                <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:24em; height:30px; display: inline-block; color:#0070C0">
                    @if($customerOvdDetails['amount'] == '')
                    NILL
                    @else
                    {{strtoupper($customerOvdDetails['amount'])}}
                    @endif
                </span>
            </td>
            @if($customerOvdDetails['initial_funding_type'] != 5 && $customerOvdDetails['initial_funding_type'] != 3)
                <td style="line-height: 30px;" width="30%">
                    <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:24em; height:30px; display: inline-block; color:#0070C0">
                        {{strtoupper($customerOvdDetails['reference'])}}
                    </span>
                </td>
            @endif
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
                    <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:24em; height:30px; display: inline-block; color:#0070C0">
                        @if($customerOvdDetails['initial_funding_type'] == 3)
                            <!-- DCBL0000018 -->
                        {{strtoupper($customerOvdDetails['maturity_ifsc_code'])}}
                        @else
                        {{strtoupper($customerOvdDetails['ifsc_code'])}}
                        @endif
                    </span>
                </td>
                <td style="line-height: 30px;" width="30%">
                    <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:24em; height:30px; display: inline-block; color:#0070C0">
                        {{strtoupper($customerOvdDetails['account_number'])}}
                    </span>
                </td>
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
                <td height="8"></td>
            </tr>
            <tr>
                <td style="line-height: 30px;" width="30%">
                    <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:24em; height:30px; display: inline-block; color:#0070C0">
                        @if($customerOvdDetails['initial_funding_type'] == 3)
                            {{strtoupper($acc_name)}}
                        @else
                            {{strtoupper($customerOvdDetails['account_name'])}}
                        @endif
                    </span>
                </td>
                <td style="line-height: 30px;" width="30%">
                    <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:24em; height:30px; display: inline-block; color:#0070C0">
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
                <td style="line-height: 30px;" width="20%">
                    Type
                </td>
            @if($customerOvdDetails['self_thirdparty'] == 'thirdparty')
                <td style="line-height: 30px;" width="20%">
                    Relationship
                </td>
            @endif
            </tr>
            <tr>
                <td style="line-height: 30px;" width="30%">
                    <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:24em; height:30px; display: inline-block; color:#0070C0">
                    @if($is_huf_display && $customerOvdDetails['self_thirdparty']=='self')
                        HUF
                     @else
                        {{strtoupper($customerOvdDetails['self_thirdparty'])}}
                     @endif
                    </span>
                </td>
            @if($customerOvdDetails['self_thirdparty'] == 'thirdparty')
                <td style="line-height: 30px;" width="30%">
                    <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:24em; height:30px; display: inline-block; color:#0070C0">
                        {{strtoupper($customerOvdDetails['relationship'])}}
                    </span>
                </td>
            @endif
            </tr>
        @endif
         <tr>
         <td height="20"></td>
        </tr>
  </tbody>
</table>
@if(in_array($accountType,[3,4]))
<table class="pdf-table" align="center">
    <tbody>
       
        <tr>
            <td style="color: #364fcc; padding-left: 10px; font-size: 20px; padding-left: 10px!important;" height="30">
                TERM DEPOSIT
            </td>
        </tr>
                          
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
            <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:24em; height:30px; display: inline-block; color:#0070C0">
                {{strtoupper($customerOvdDetails['years'])}}
            </span>
        </td>
        <td style="line-height: 30px;" width="30%">
            <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:24em; height:30px; display: inline-block; color:#0070C0">
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
            <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:24em; height:30px; display: inline-block; color:#0070C0">
                {{strtoupper($customerOvdDetails['days'])}}
            </span>
        </td>
        <td style="line-height: 30px;" width="30%">
            <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:24em; height:30px; display: inline-block; color:#0070C0">
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
            <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:24em; height:30px; display: inline-block; color:#0070C0">

            @if($customerOvdDetails['auto_renew'] == 1)
                    YES
                @else
                    NO
                @endif
            </span>
        </td>
        <td style="line-height: 30px;" width="30%">
            <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:24em; height:30px; display: inline-block; color:#0070C0">
                {{strtoupper(config('constants.INTREST_PAYOUT.'.$customerOvdDetails['interest_payout']))}}
            </span>
        </td>
    </tr>
    <tr>
         <td height="8"></td>
    </tr>
    <tr class="display-none" id="emd_row_submission">
        <td style="line-height: 30px;" width="20%">
            EMD
        </td>
        @if($customerOvdDetails['emd'] == 1)
            <td style="line-height: 30px;" width="20%">
            3<sup>rd</sup> Party Name
        @endif
     </tr>
     <tr class="display-none" id="emd_row_submissions">
        <td style="line-height: 30px;" width="30%">
            <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:24em; height:30px; display: inline-block; color:#0070C0">
            @if($customerOvdDetails['emd'] == 1)
                    YES
                @else
                    NO
                @endif
            </span>
        </td>
        @if($customerOvdDetails['emd'] == 1)
            <td style="line-height: 30px;" width="30%">
            <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:24em; height:30px; display: inline-block; color:#0070C0">
                 {{strtoupper($customerOvdDetails['emd_name'])}}
            </span>
        </td>
        @endif
     </tr>
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
            <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:24em; height:30px; display: inline-block; color:#0070C0">
                 {{strtoupper(config('constants.MATURITY.'.$customerOvdDetails['maturity']))}}
            </span>
        </td>
    </tr>
    <tr>
     <td height="20"></td>
    </tr>                          
    </tbody>
</table>
<table class="pdf-table" align="center">
    <tbody>
        
        <tr>
            <td colspan="2" style="color: #364fcc; padding-left: 10px; font-size: 20px; padding-left: 10px!important;" height="30">
                Account Details for Credit of TD Proceeds
            </td>
        </tr>
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
                <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:24em; height:30px; display: inline-block; color:#0070C0">
                    {{strtoupper($customerOvdDetails['maturity_bank_name'])}}
                </span>
            </td>
            <td style="line-height: 30px;" width="30%">
                <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:24em; height:30px; display: inline-block; color:#0070C0">
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
            <td style="line-height: 30px;" width="20%">
                Account Number
            </td>
            <td style="line-height: 30px;" width="20%">
                Account Name
            </td>
        </tr>
        <tr>
            <td style="line-height: 30px;" width="30%">
                <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:24em; height:30px; display: inline-block; color:#0070C0">
                    {{strtoupper($customerOvdDetails['maturity_account_number'])}}
                </span>
            </td>
            <td style="line-height: 30px;" width="30%">
                <span style="background:white;padding:0em 1.1em 0.2em 1.1em; width:24em; height:30px; display: inline-block; color:#0070C0">
                    {{strtoupper($customerOvdDetails['maturity_account_name'])}}
                </span>
            </td>
        </tr>
    </tbody>
</table>
@endif
@if($showImages)
<table class="pdf-table" align="center">
    <tbody>
    
        <tr>
            <td style="color: #364fcc; padding-left: 10px; font-size: 20px; padding-left: 10px!important;" height="30">
                DOCUMENTS PROVIDED
            </td>
        </tr>
                       
            @if(count($files) > 0)
                @foreach($files as $file)
                    @if($file['type'] != 'declaration')
                    <tr>
                        <td style="line-height: 50px;width:20%;padding-left: 20%;padding-bottom: 3em">
                        <div class="{{$image_mask_blur}}" {{$def_blur_image}}>
                            <img  width="400px" alt="{{$formId.'/'.$file['filename']}}" src="{{ getInlineImage($formId.'/'.$file['filename']) }}"/>
                            </div>
                        </td>
                    </tr>

                   @endif
                @endforeach
                 <tr>
                    <td style="color: #364fcc; padding-left: 10px; font-size: 20px; padding-left: 10px!important;" height="30">
                        DECLARATION DOCUMENTS PROVIDED
                    </td>
                </tr>
                @foreach($files as $file)
                    @if($file['type'] == 'declaration')
                    <tr>
                        <td style="line-height: 50px;width:20%;padding-left: 20%;padding-bottom: 3em">
                        <div class="{{$image_mask_blur}}" {{$def_blur_image}}>
                            <img  width="400px" alt="{{$formId.'/'.$file['filename']}}" src="{{ getInlineImage($formId.'/'.$file['filename']) }}"/>
                            </div>
                        </td>
                    </tr>
                    @endif
                @endforeach
                @php
                    $nriDate = '';
                @endphp
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
                                                    @endphp
                                                    {{$nriDate['nri_date']}}
                                                </span>
                                            </td>
                                        </tr>
            @endif
            @endif
            @if($accountDetails['source'] == 'CC' && $tdSchemeCodecc == '1' && isset($callCenterEmailImage) && count($callCenterEmailImage) == '0')
                <tr>
                    <td height="50">
                        Mode Of Communication: Phone
                                   
                    </td>
                </tr>
            @endif             
        
   </tbody>
</table>    
<table class="pdf-table" style="page-break-before: always;" align="center">
    <tbody>             
        <tr>
            <td colspan="2" style="color: #364fcc; padding-left: 10px; font-size: 20px; padding-left: 10px!important;" height="30">
                @if(($customer_type == "ETB") && ($customerOvdDetails['initial_funding_type'] == 3) && ($accountDetails['source'] == 'CC'))
                    CONFIRMATION (By Call Centre official sourcing the application)
                @else
                    CONFIRMATION (By Branch official sourcing the application)
                @endif
            </td>
        </tr>
         <tr>
            <td height="15"></td>
        </tr> 
        @if(count($declarationsList) > 0)
            @foreach($declarationsList as $declaration)
                @php
                    $declaration = (array) $declaration;
                @endphp
                <tr>
                     <td align="top" style="padding-right: 30px; padding-top: 1.2em;" width="10%">
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
                    </td>
                    <td style="white-space: normal; text-align: left;padding-right: 1em" width="80%">
                        @if(preg_match('/_FIELD_DATE_/', $declaration['declaration']))
                             @php
                                if(preg_match('/_FIELD_DATE_ /', $declaration['declaration'])){
                                    $tokenMdate =  explode('_FIELD_DATE_', $declaration['declaration']);
                                } 
                                if(preg_match('/_FIELD_M_LOCATION_ /', $declaration['declaration'])){
                                    $tokenMlocation = explode('_FIELD_M_LOCATION_', $tokenMdate[1]);
                                } 
                            @endphp
                            {!!$tokenMdate[0] !!}
                            <span style="color: #364fcc; font-weight: bold;">
                                {{$meetingdate}}
                            </span>
                            {!!$tokenMlocation[0] !!}
                            <span style="color: #364fcc; font-weight: bold;">
                                {{strtoupper(config('constants.SPECIAL_SUBMISSION_DECLARATION.CUSTOMER_MEETING_LOCATION')[$custmeetinglocations])}}
                            </span>
                            {!!$tokenMlocation[1] !!}

                        @elseif(preg_match('/_FIELD_REASON_/', $declaration['declaration']))
                            @php
                                if(preg_match('/_FIELD_REASON_/', $declaration['declaration'])){

                                    $tokenReason = explode('_FIELD_REASON_', $declaration['declaration']);

                                } 
                            @endphp
                            {!! $tokenReason[0]  !!}
                            <span style="color: #364fcc; font-weight: bold;">
                                {{strtoupper(config('constants.SPECIAL_SUBMISSION_DECLARATION.REASON_FOR_ACCOUNT_OPEN')[$reasonforaccntopening])}}
                            </span>
                        @elseif(preg_match('/_FIELD_LEAD_/', $declaration['declaration']))
                            @php
                                if(preg_match('/_FIELD_LEAD_/', $declaration['declaration'])){

                                    $tokenlead = explode('_FIELD_LEAD_', $declaration['declaration']);

                                } 
                            @endphp
                            {!! $tokenlead[0]  !!}
                            <span style="color: #364fcc; font-weight: bold;">
                                {{strtoupper(config('constants.SPECIAL_SUBMISSION_DECLARATION.LEAD_GENERATED')[$leadgenerated])}}
                            </span>
                        @elseif(preg_match('/_FIELD_DISTANCE_/', $declaration['declaration']))
                            @php
                                if(preg_match('/_FIELD_DISTANCE_/', $declaration['declaration'])){

                                    $tokendistacne = explode('_FIELD_DISTANCE_', $declaration['declaration']);

                                } 
                            @endphp
                            {!! $tokendistacne[0]  !!}
                            <span style="color: #364fcc; font-weight: bold;">
                                {{strtoupper(config('constants.SPECIAL_SUBMISSION_DECLARATION.DIST_FROM_BRANCH')[$distfrombranch])}}
                            </span>
                        @else
                            {{$declaration['declaration']}}
                        @endif
                    </td>
                </tr>
                <tr>
                    <td height="8"></td>
                </tr>
            @endforeach
        @endif
   </tbody>
</table>    
@endif
<table class="pdf-table" style="page-break-after: always;" align="center">
    <tbody>
            <!-- <tr> -->
                <td style="line-height: 30px;" width="10%">
                @if($showNameOfStaff)
                    Name of Staff
                </td>
                <td style="line-height: 30px;" width="10%">
                @endif
                    @if(($customer_type == "ETB") && ($customerOvdDetails['initial_funding_type'] == 3))
                        Branch / Source
                    @else
                        Branch
                    @endif
                </td>
            <!-- </tr> -->
            <!-- <tr> -->
                <td style="line-height: 30px;" width="40%">
                @if($showNameOfStaff)

                        <span style="background:white;padding:0em 1.1em 0.2em 1.1em;width:24em; height:2.1em; display: inline-block; color:#0070C0">
                            {{ strtoupper($username)}}
                        </span>
                </td>
                <td style="line-height: 30px;" width="40%">
                @endif
                    <span style="background:white;padding:0em 1.1em 0.2em 1.1em;  width:24em; height:2.1em; display: inline-block; color:#0070C0">
                        {{strtoupper($accountDetails['branch_id'])}}
                    </span>
                </td>
            <!-- </tr> -->
            <!-- <tr>
                <td style="line-height: 30px;" width="20%">
                    Date
                </td>
            </tr>
            <tr>
                <td style="line-height: 30px;" width="30%">
                    <span style="background:white;padding:0em 1.1em 0.2em 1.1em;  width:6em; height:2.1em; display: inline-block; color:#0070C0">
                        @if(isset($created_at))
                            {{\Carbon\Carbon::parse($created_at)->format('d')}}
                        @else
                            {{Carbon\Carbon::now()->format('d')}}
                        @endif
                    </span>
                    <span style="background:white;padding:0em 1.1em 0.2em 1.1em;  width:6em; height:2.1em; display: inline-block; color:#0070C0">
                        @if(isset($created_at))
                            {{strtoupper(\Carbon\Carbon::parse($created_at)->format('M'))}}
                        @else
                            {{strtoupper(Carbon\Carbon::now()->format('M'))}}
                        @endif
                    </span>
                    <span style="background:white;padding:0em 1.1em 0.2em 1.1em;width:6em;height:2.1em;display: inline-block;color:#0070C0;margin-left: 3px;">
                        @if(isset($created_at))
                            {{\Carbon\Carbon::parse($created_at)->format('Y')}}
                        @else
                            {{Carbon\Carbon::now()->format('Y')}}
                        @endif
                    </span>
                </td>
                </tr> -->                
    </tbody>
</table>
<table class="pdf-table" align="center">
    <tbody>
            <tr>
                <td style="color: #364fcc;font-size: 20px; padding-left: 5px!important;" height="20">
                   DECLARATION
                </td>
            </tr>
            <tr align="left">
                <span style="justify-content: center; align-content: center; text-align:justify; font-size:7.5px; width:80%; white-space:wrap; padding-right:15px;"> 
                    I/We have read, understood and hereby agree to the “Terms and Conditions and Schedule of charges applicable to my/our account” set forth on DCB Bank Limited (“DCB Bank/the Bank”) website at www.dcbbank.com. I/We understand, agree and confirm that, I/we will access DCB Bank's website at www.dcbbank.com for any changes/updates in terms and conditions of the services and products as applicable and will abide by the same and in the case of Delight kit, the terms and conditions available inside the kit which was applicable at the time of creation of the kit may vary from time to time without notice as per DCB Bank's sole discretion and for the updated terms and conditions applicable to me/us as per account/scheme/product types, I/we shall access and refer to DCB Bank's website at www.dcbbank.com and such revised terms and conditions shall override the earlier terms and conditions and shall be binding on me/us. I/We understand that access to any changes/updates in terms and conditions applicable to this relationship shall be available on the Bank's website only. I/We do hereby declare that information furnished in this form is true and correct to the best of my/our knowledge and belief. I/We hereby authorise issuance of ATM/Debit Card and provision of, Email Statement, Phone Banking, Mobile Banking, WhatsApp Banking Services, Internet Banking. I/We am/are aware of charges applicable for various services offered and I/we affirm, confirm and undertake that I/we have read and understood the “Terms and Conditions” for usage of the Phone Banking, Mobile Banking, WhatsApp Banking, Internet Banking of DCB Bank as set forth in the DCB Bank's website www.dcbbank.com and I/We will adhere to all the terms and conditions as applicable from time to time. I/We authorise the Bank to enable provisions of internet banking, phone banking, mobile banking, WhatsApp Banking, bill repayment and SMS & email alerts services as per the terms and conditions available/set forth on the Bank's website for these banking services and facilities. I/We further authorise the Bank to debit my/our Account(s) towards any applicable charges for any/various service/services provided as applicable from time to time. In the absence of deposit maturity instructions, the deposit will be auto-renewed with the same tenure at the prevailing interest rate with the applicable terms and conditions.
                    <br>
                    I/We understand and agree that the consent given for updating/registration/requests for free mobile alert facility shall be valid till such time I/we withdraw the same in writing. Unless specifically advised, the Bank will continue to send SMS alerts on the number requested by the authorised signatory/ies of the Firm/Company/Trust/Association/Society. The Bank shall not be responsible and liable for any consequences which may arise owing to change in name/s, address, mobile number of individual, authorised signatory/ies or partners or directors or trustees or members of the Firm/Company/Trust/Association/Society.
                    <br>
                    <br>
                    I/We declare, confirm, understand, accept, acknowledge and agree:
                    <ol type="a">
                    <li>That all the particulars and information given in this application form (and all documents referred or provided therewith) are true, correct, complete and up-to-date in all respects and I/We have not withheld any information. I/We understand certain particulars given by me/us are required by the operational guidelines governing banking companies. I/We agree and undertake to provide any further information as and when the Bank may require.</li>
                    <li> That I/we have had no insolvency proceedings initiated against me/us nor I/we have ever been adjudicated insolvent.</li>
                    <li> That I/we have read the application form and brochures and am aware of all the terms and conditions of availing finance or service or products from the Bank.</li>
                    <li> That Bank reserves the right to reject any application without providing any reason and reference to me/us. I/We agree and understand that DCB Bank reserves the right to retain the application forms, and the documents provided therewith, including photographs, and shall not return the same to me/us.</li>
                    <li> To inform the Bank regarding change in my residence/employment and to provide any further information as and when the Bank may require from time to time.</li>
                    <li> That if the Account is under corporate salary scheme: I/We have also read and understood “Terms and Conditions” under which Salary Scheme is offered to my/our organisation and employees. I/We agree that my/our employer has full right to reserve any instruction given by them to credit my account for any amount within a period of three working days and I/we will not dispute or hold the Bank responsible for such debits in my/our account. I/We understand that it is my/our responsibility to inform (in writing) the Bank immediately on termination of my/our employment with my/our current employer, whereupon I/we will cease to enjoy any or all benefits under salary account scheme. I/We understand that the Bank reserves the right to convert my/our account into a regular savings bank account and further ceasing to be categorised as an account under corporate salary scheme. Accordingly, there will be a change in minimum balance requirement and applicable charges per regular savings bank account.</li>
                    <li> That I/we shall not hold DCB Bank liable and responsible for furnishing of the processed information/data/products thereof to other banks/financial institutions/credit providers/users registered as above.</li> 
                    <li> That I/we have to complete further application for specific liability products/services from DCB Bank as prescribed from time to time, and that such further applications shall be regarded as an integral part of this application (and vice versa), and that unless otherwise disclosed in such further forms as prescribed, the particulars and information set forth herein as well as the documents referred or provided herewith are true, correct, complete and up-to-date in all respects.</li>
                    <li>That such further applications will require incorporation of the application form number, and/or such details as DCB Bank may prescribe, to facilitate data management.</li>
                    <li> That I/we authorise DCB Bank to issue a Debit cum ATM Card to me/us.</li>
                    <li> That the issue and usage of the Debit cum ATM Card is governed by the terms and conditions as in force from time to time and I/we agree to be bound by the same.</li>
                    <li> That the terms and conditions of Debit cum ATM Card are liable to be amended by DCB Bank from time to time.</li> 
                    <li> That I/we unconditionally and irrevocably authorise DCB Bank, to debit my/our Account annually with an amount equivalent to the fee and charges for use of the Debit cum ATM Card.</li>
                    <li> I/We, the joint holder(s),agree that in case of death of any one or more of the joint depositor(s), the proceeds may be paid to the survivor(s), on request before due date as per the mode of operation. DCB Bank can levy penal charges, if any, as may be permissible by either regulatory guidelines or provisions of BCSBI code or both, applicable as on the date of request.</li>
                    <li> That continuation of the account with DCB Bank is at the sole discretion of DCB Bank and in case DCB Bank is dissatisfied with the conduct of the account/account holder, DCB Bank has the right to close the account after giving me/us one month's notice or withdraw the concessions in to or any service granted to me/us or charge DCB Bank's applicable rates/charges for such services.</li>
                    <li> That DCB Bank may at its absolute discretion, discontinue any of the services completely or partially without any notice to me/us.</li> 
                    <li> That in case of return of Account Opening Amount (AOA) cheque, for any reason whatsoever, DCB Bank would close the account without any reference to me/us.</li>
                    <li> That on receipt of written application from any of the Authorised Signatory(ies) and/or survivor/s of us, DCB Bank at its sole discretion and subject to such terms and conditions, grant a loan/advance/renew/enhance against the security/collateral issued in joint names.</li> 
                    <li> I/We hereby understand that among all other things, minimum balance requirement for variants of savings bank account under various scheme codes would be applicable and is in line with such updated information as available on DCB Bank's website www.dcbbank.com from time to time.</li>
                    <li> I/We agree that the non- callable deposit/s cannot be closed by me/us before expiry of the term of such deposit/s.</li>
                    <li> I/We agree that the DCB Bank shall deduct applicable TDS (Tax Deducted at Source) as per the Income Tax Provisions.</li>
                    </ol> 
                    I/We understand that savings bank account cannot be used for business transactions and if it is observed that the account is being used for business purpose or does not match with my/our profile, such as, declared Turnover, occupation, etc., DCB Bank shall close the account after sending due intimation to me/us. I/We confirm that any change in my/our profile, such as, turnover, occupation, or demographic information, etc., I/we shall inform DCB Bank immediately in writing. I/We understand that the onus for such an action is on me/us and not on DCB Bank.
                    I/We understand that DCB Bank is relying on this information for the purpose of determining the status of the applicant named above in compliance with FATCA (Foreign Account Tax Compliance Act)/CRS (Common Reporting Standards). DCB Bank is not able to offer any tax advice on CRS or FATCA or its impact on the applicant. I/We shall seek advice from professional tax advisor for any tax questions. I/We agree to submit a new form within 30 days if any information or certification on this form becomes incorrect. I/We agree that as may be required by domestic regulators/tax authorities DCB Bank may also be required to report, reportable details to CBDT (Central Board of Direct Taxes) or close or suspend my/our account. I/We confirm that, I/We will intimate/notify in writing to the Bank and update operating instructions and/or any other change(s) in DCB Bank's record immediately with respect to the account/s held with DCB Bank. I/We hereby agree and authorise DCB Bank to mark freeze in my/our account/s if I/we fail to submit the updated/refresh KYC documents as per DCB Bank's KYC policy and/or operating instructions for my/our account periodically to DCB Bank. I/We certify that I/we provide the information in the electronic form and to the best of my/our knowledge and belief the certification is true, correct, and complete including the taxpayer identification number of the applicant. I/We agree that my/our personal Know Your Customer (KYC) information may be shared with Central KYC (CKYC) registry or any other competent authority. I/We hereby give consent to receive information from DCB Bank/CKYC registry/the Government/Reserve Bank of India or any authority through SMS/email on my/our registered mobile number/email address. I/We also agree that non receipt of any such SMS/email shall not make DCB Bank liable for any nature of loss or damage. I/We have read and understood premature withdrawal penalty charges applicable on DCB Fixed Deposits, details of which are available on DCB Bank website.
                    <br><br>
                    <!-- <b>DCB Suraksha Fixed Deposit - Terms and Conditions:</b><br>
                    I/We hereby have understood, accepted and acknowledged the below mentioned applicable terms and conditions: <br>
                    <ol type="1">
                    <li>DCB Suraksha Fixed Deposit is available for Resident and Non-Resident (NRI) Individuals only.</li>
                    <li>Insurance cover applicable on DCB Suraksha Fixed Deposit would be equivalent to the value of the Deposit, subject to a maximum cover of INR 10,00,000 (Rupees Ten Lakh Only) across DCB Suraksha Fixed 
                    Deposits in the name of the primary account holder.</li> 
                    <li>Applicants aged from 18 years to less than 55 years are allowed to open DCB Suraksha Fixed Deposit. Insurance cover shall cease on account holder attaining the age of 55 years.</li>
                    <li>The insurance cover will be available only to the primary account holder.</li>
                    <li>In the event of premature closure of DCB Suraksha Fixed Deposit, the insurance cover shall cease to exist. For partial withdrawal, the insurance cover amount shall reduce to the extent of the amount 
                    remaining as DCB Suraksha Fixed Deposit. To be read in conjunction with point number 2.</li> 
                    <li>PAN details of the account holder, nomination and email ID are mandatory to open DCB Suraksha Fixed Deposit. The same nomination would be considered both for DCB Suraksha Fixed Deposit and insurance 
                    cover.</li>
                    <li>A waiting period of 45 days shall apply for non-accidental deaths. Suicide exclusion shall apply for a period of one year from the coverage start date.</li> 
                    <li>Insurance cover on the DCB Suraksha Fixed Deposit is provided by Aditya Birla Sun Life Insurance Company Limited ('Insurance Provider'), which is valid for the Deposit period mentioned in this application 
                    form, unless communicated otherwise subject to the customer being within the permissible coverage age of 55 years.</li>
                   <li>Insurance cover provided on and during the renewal of the DCB Suraksha Fixed Deposit (if any) is at the sole discretion of DCB Bank Insurance Provider.</li>
                   <li>Tenure of DCB Suraksha Fixed Deposits is 36 months only.</li>
                   <li>No medical tests are required for the insurance facility. </li>
                   <li>Minimum deposit value for DCB Suraksha Fixed Deposit is INR 10,000 (Rupees Ten Thousand Only).</li>
                   <li>The maximum validity of the insurance coverage is co-terminus with the tenure i.e. up to the maturity date of the DCB Suraksha Fixed Deposit.</li> 
                   <li>For joint accounts, the insurance cover shall be available only to the primary account holder. </li>
                   <li>Applicant/s hereby authorise DCB Bank to share insurance related personal information to the insurance provider for the purpose of insurance. </li>
                   <li>Applicant agrees, accepts and acknowledge that any claim related insurance cover shall be raised to insurance provider and DCB Bank shall act as facilitator for the same.</li> 
                   <li>Applicant agrees, accepts and acknowledges that DCB Bank shall not be liable for payment of any claim related insurance cover under DCB Suraksha Fixed Deposit in the event that the insurance provider 
                    rejects the claim. </li>
                    <li>Applicant hereby understands, accepts and acknowledges that, in the event of any rejection of insurance claim by the insurance provider, DCB Bank shall not be liable for any deficiency/ies of service/s and 
                    shall not be liable for any cost/s, loss/es, charge/s, claim/s, penalty/ies and/or damages in any suit /litigation raised by me/us in any court having local jurisdiction.</li>
                   </ol>-->
                   <b>Declaration where Applicant is Minor:</b>
                   <br>
                   I hereby declare that I am the natural guardian/lawful guardian appointed by the Court order (copy  enclosed) of the Minor (primary applicant).<br>
                   I shall represent the Minor (Primary applicant) in operating the bank Account till he/she attains majority. I agree to indemnify, keep indemnified and hold harmless DCB Bank against any claims for any transactions made in the account(s). I hereby declare that the amount withdrawn from this account by me, will be used for the benefit of the Minor (primary applicant). <br>
                   I undertake and confirm that I shall avail various services of DCB Bank (wherever applicable) such as Phone Banking, Mobile Banking, WhatsApp Banking, Internet Banking, Bill Pay, etc., only for the benefit of the Minor (primary applicant). I shall abide by all terms and conditions governing the various services and shall intimate DCB Bank in writing immediately upon the Minor (primary applicant) attaining majority.
                    <br>
                    <br>
                    <b>Aadhaar Consent for self/on behalf of minor:</b><br>
                    I/We have voluntarily submitted my/our Aadhaar/UID Number mentioned in the electronic application form and consent to:<br>
                    <ul style="list-style-type:disc"> 
                    <li>Seed my/our Aadhaar/UID Number issued by UIDAI, Government of India in my/our name with my/our account to be opened.</li>
                    <li>Map it at NPCI (National Payments Corporation of India) to enable me/us to receive Direct Benefit Transfer (DBT) from Government of India in my/our above mentioned account. I/We understand that if more than one benefit transfer is due to me/us, I/we will receive all benefit transfers in this account.</li> 
                    <li>Use my/our Aadhaar details to authenticate me/us from UIDAI.</li>
                    <li>Use my/our mobile number mentioned in my/our account for sending SMS alerts to me/us.</li>
                    <li>Consent for authentication: I/We, the holder of the above stated Aadhaar number, hereby give my/our consent to the DCB Bank to obtain my/our Aadhaar number, name and fingerprint/iris for authentication with UIDAI. DCB Bank has informed me/us that my/our identity information would only be used for demographic authentication/validation/e- KYC purpose and also informed that my/our biometrics will not be stored/shared and will be submitted to CIDR (Central Identities Data Repository) only for the purpose of authentication.</li>
                    </ul>I/We have been given to understand that my/our information submitted to DCB Bank herewith shall not be used for any purpose other than mentioned above, or as per requirements of law.<br>
                    <br>
                    <b>Terms and conditions for payment of interest and maturity proceeds through/NEFT/RTGS:</b>
                    <br></br>
                    I/We hereby accept, acknowledge and abide by the following terms and conditions (A)I/We hereby authorise DCB Bank to facilitate, remittance, payment of interest and transfer maturity proceeds through NEFT/RTGS. (B) I/We hereby have understood, accept and acknowledge that the remittance is to be sent at my/our own risk, and responsibility. (C) I/We hereby hold entire liability of the transaction and have the distinct understanding, and acceptance that no liability whatsoever will to be attached to DCB Bank in following events:(1) Any loss or damage arising or resulting from delay in transmission, delivery or non- delivery of the message or for any mistake, exchange or error in transmission or delivery thereof. (2) Any message received by me/us hereby deciphering the message for whatsoever cause or form, its misinterpretation when received. (3) Any action or default or inaction of the destination bank with respect to the transaction initiated by me/us. (4) Any action taken by DCB Bank under the notification/s, circular/s instruction/s, restriction/s from regulatory authority/ies for/NEFT/RTGS transactions. (5) NEFT/RTGS system being not available for performance of the transaction. (6) Any error, failure of internal communication system at the recipient bank/branch. (7) Any incorrect information including but not limited to name of the recipient bank, account number, account type of the recipient, amount of the remittance provided by me/us or any incorrect credit accorded by the recipient bank/branch due to incorrect information provided by me/us or any act or event beyond control or from failure to properly identify the beneficiary's/recipient's name to whom the payment is to be transferred. (8) Shall not be liable to verify the name of the beneficiary or any other particulars in effecting the transaction/s or credit amount. (D) Any deficiency of information which is incorrect or incompletely provided by my/us. In the aforesaid event/s, DCB Bank shall not be liable for deficiency/ies of service/s and shall not be liable for any cost/s, loss/es, charges, claim/s, penalty/ies and/or damages in cases of any suit/litigation raised by me/us in any court having local jurisdiction. I I/We understand, accept and acknowledge that the NEFT/RTGS request/s is/are under the governance of regulatory body i.e. RBI and is/are subject to the RBI regulations and guidelines. (F) I/We hereby understand, accept, and acknowledge that the amount to be credited will be effected by DCB Bank solely on the basis of beneficiary's account number only which is informed to DCB Bank and not on the basis of the name of the beneficiary or any other particulars. Furthermore, DCB Bank shall not be liable to verify the name of the beneficiary or any other particulars in effecting the transaction/s or credit amount. (G) Applicable TDS/Tax Collection at Source (TCS) will be deducted on the payment amount initiated for NEFT/RTGS transaction.
                    <br> 
                    <br>
                    <b>Customer ID Merger:</b> 
                    <br>
                    I/We understand and agree that all my/our accounts will now be consolidated under a single DCB Bank Customer ID after merging the multiple Customer IDs. Post such merging, only one Customer ID will remain active. I/We, am/are aware that DCB Bank Personal/Business Internet Banking, if availed, will now be accessible only under the retained Customer ID and all the accounts will be consolidated to this Customer ID. I/We am/are aware that Tax Deducted at Source (TDS) on interest earned on DCB Bank Fixed Deposit Account(s) under erstwhile Customer IDs will also stand consolidated and TDS shall now be applicable on the basis of the unique Customer ID in accordance with the provisions of the Income Tax Act, 1961 and DCB Bank will furnish one TDS Certificate for all my/our accounts. I/We confirm that all the details provided are correct and I/we agree to the terms and conditions of DCB Bank. I/We also understand that all my/our accounts can be accessed from the unique Customer ID post consolidation of multiple Customer ID's if any.
                    <br>
                    <br>
                    <br>
                    <footer class="container">
                        <div class="mt-4" style="display: flex; justify-content: space-between; width: 100%;font-weight:normal !important;">
                            <p style="text-align:left; margin: 0;padding-left: 1px!important;">6334 Ver. 7.0-December 2024</p>
                            <p style="text-align:center; margin: 0;">DCB Bank Limited</p>
                            <p style="text-align:right; margin: 0;padding-right: 1px!important;">M077/Jan 25/1.9</p>
                        </div>
                    </footer>   
                </span>
     </tr>
        </tbody>
    </table>
</body>
</html>
