@php
use App\Helpers\CommonFunctions;
$masking_fields = ["Registered Email Id","Registered Mobile Number (RMN)","Aadhaar Number","PAN Number Updation","voter_id_number","Alternate Contact Number","passport_number","aadhaar_photocopy_number","driving_licence_number","pan_number","Passport","Voter ID","Driving Licence"];
$tokenParams = Cookie::get('token');
$encrypt_key = substr($tokenParams, -5);
$crf_number = '';
$created_at = '';
$disabled = '';
if(count($getAmendData)>0){
    $crf_number = $getAmendData[0]->crf;
    $created_at = date('d-m-Y',strtotime(substr($getAmendData[0]->created_at,0,10)));
    $disabled = 'disabled';
}
$displaySelected = '';
$displayAccNo = '';
function displayInlineImage($imageName){
 
    $getDataImage = file_get_contents(storage_path('uploads/amend/'.$imageName));
    $imagedata =  'data:image/png;base64,'.base64_encode($getDataImage);

    return $imagedata;
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


if($linkNotShow != '' && Cache::get('amendUrl'.$crf_number) != ''){
    $showApproval = "true";
}else{
    $showApproval = "false";
}

@endphp
<html lang="en">
<head>
<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="Expires" content="0">
<meta charset="utf-8" name="base_url" content="{{ URL::to('/') }}">
<meta name="cookie" content="{{Cookie::get('token')}}">
<style type="text/css">
    table { page-break-inside:auto }
    tr    { page-break-inside:avoid; page-break-after:auto }
    /*.page-break-before{page-break-before:always!important;}.page-break-after{page-break-after:always!important;}*/
    .pdf-table{font-family:Franklin Gothic Book,arial, sans-serif; font-size:12px;margin-top: 20px;width: 85%;}
    /*.pdf-table tr{page-break-inside:avoid; page-break-after:auto}*/

    @page { size: A4 portrait;}
   
</style>
</head>
<body>
<div>
<center>
<section id='printSectionFor' style="/*size: 8.25in 11.75in; */ width:188mm
  /*  height: 250mm*/;background-color:#F6F7FB;">
  @include("amend.mask_unmask_btn")
<table align="center" class="pdf-table">
    <tbody>
        <tr>
            <td>
                <!-- <img class="img-fluid" src="{{ asset('assets/images/dcb-logo.svg') }}" alt="DCB Logo" /> -->
                <img src="{{ getLogoImage() }}" height="50">
            </td>
            <td>
                <h3 id="centerWord" style="text-align:right; margin-top:6%;">Customer Request Form</h3>
            </td>
        </tr>
    </tbody>
</table>
<table border="1" class="crfInfo pdf-table" align="center">
    <tbody>
        <tr>
            <td>
             <center> <img src="data:image/png;base64,{{DNS2D::getBarcodePNG($crf_number,"QRCODE")}}" alt="barcode" style="height:90px;"></center>
            </td>
                <td>
                <center>
                    <table align="center">
                        <tbody>
                            <tr>
                                <td>
                                   <p> Request Number :</p>
                                </td>
                                <td>
                                   <p>{{$crf_number}}</p>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                   <p> Customer Name :</p>
                                </td>
                                <td>
                                   <p>
                                    @php
                                        $wordCtn = strlen($getCustomerName);
                                        $eqlWord = $wordCtn/2;  
                                    @endphp
                                    @if(strlen($getCustomerName) < 50)
                                        {{$getCustomerName}}
                                    @else
                                        <p>
                                        {{substr($getCustomerName,0,$eqlWord)}}<br>
                                        {{substr($getCustomerName,$eqlWord,$wordCtn)}}
                                        </p>
                                    @endif
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <p>Customer ID :</p>
                                </td>
                                <td>
                                  <p>{{$getAmendMasterData['customer_id']}}</p>
                                </td>
                            </tr>
                            @if($getAmendMasterData['account_no'] != '')
                                <tr>
                                    <td>
                                        <p>Account Number :</p>
                                    </td>
                                    <td>
                                        <p>{{$getAmendMasterData['account_no']}}</p>
                                    </td>
                                </tr>
                            @endif
                            
                            @if($kycNumber != '')
                            <tr>
                                <td>
                                    <p>E-KYC Number :</p>
                                </td>
                                <td>
                                    <p>{{$kycNumber}}</p>
                                </td>
                            </tr>
                            @endif

                            <tr>
                                <td>
                                   <p>Branch Official :</p>
                                </td>
                                <td>
                                    <?php 
                                        $getBranchOfficalName['emp_name'] = isset($getBranchOfficalName['emp_name']) && $getBranchOfficalName['emp_name'] != ''?$getBranchOfficalName['emp_name']:'';
                                    ?>
                                   <p>{{strtoupper($getBranchOfficalName['emp_name'])}}</p>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <p>Date :</p>
                                </td>
                                <td>
                                  <p>{{$created_at}}</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </center>   
                </td>
        </tr>
    </tbody>
</table>
        <br>
        @if($comment)
        <table>
            <tbody>
                <tr>
                    <p><h4>{{$message}}</h4></p>
                </tr>
            </tbody>
        </table>
        @endif
        <input id="printCall" value="{{$printCall}}" hidden>
        <table align="center" class="pdf-table">
            <tbody>
                <tr>
                    <td>
                        <h4>Amend Selected Information</h4>
                    </td>
                </tr>
            </tbody>
        </table>
        <table align="center" class="pdf-table">
            <tbody>
                <tr>
                    <td>
                        <b>Selected Change</b>
                    </td>
                    <td>
                        <b>Existing Information</b>
                    </td>
                    <td>
                        <b style="color: #0070C0;">New Information</b>
                    </td>
                </tr>
            </tbody>
        </table>
         <br>   
        <table id="addDetails" class="pdf-table" align="center">
            @for($i=0;$i<count($getAmendData);$i++)
                @php
                    if($getAmendData[$i]->amend_item != $displaySelected){

                        $displaySelected = $getAmendData[$i]->amend_item;
                        $showSelected =  $getAmendData[$i]->amend_item;

                    }else{

                        $showSelected = '';
                    }

                    if($getAmendData[$i]->account_no != $displayAccNo){

                        $displayAccNo = $getAmendData[$i]->account_no;
                        $showAccNo =  $getAmendData[$i]->account_no;
                    }else{
                        $showAccNo = '';
                    }
                @endphp
            <tbody>
                <tr>
                    <td>
                        <span style="font-size:12px;">{{$showSelected}}  {{$showAccNo}}</span>  
                    </td>
                    <td>
                        <span style="background:white;padding:0.4em 1.1em 0.2em 1.1em;width:18em; height:2.1em;color:black; word-wrap: break-word;display: block;">
                        @if(in_array($getAmendData[$i]->amend_item,$masking_fields) && $getAmendData[$i]->old_value != "")
                            <label class="enc_label unmaskingfield" style="display:none;">
                                {{CommonFunctions::encrypt256($getAmendData[$i]->old_value,$encrypt_key)}}
                            </label>
                            <label class="maskingfield">
                                **************
                            </label>
                        @else
                            {{strtoupper($getAmendData[$i]->old_value)}}
                        @endif
                        </span>
                    </td>
                    <td>
                        <span  style="background:white;padding:0.4em 1.1em 0.2em 1.1em;width:18em; height:2.1em;  color:#0070C0; word-wrap: break-word;display: block;">
                            {{-- {{strtoupper($getAmendData[$i]->new_value_display)}} --}}
                            @if($getAmendData[$i]->field_name == '_AADHAR_NUMBER')
                                {{'XXXX-XXXX-'.substr($getAmendData[$i]->new_value_display,8)}}
                            @elseif(in_array($getAmendData[$i]->amend_item,$masking_fields) && $getAmendData[$i]->new_value_display != "")
                                <label class="enc_label unmaskingfield" style="display:none;">
                                    {{CommonFunctions::encrypt256($getAmendData[$i]->new_value_display,$encrypt_key)}}
                                </label>
                                <label class="maskingfield">
                                    **************
                                </label>
                            @else
                                {{strtoupper($getAmendData[$i]->new_value_display)}}
                            @endif
                        </span>
                    </td>
                </tr>
            </tbody>
            @endfor
        </table>
     
        @if((isset($additionalData['proofIdData']['proof_id']) && $additionalData['proofIdData']['proof_id'] != '') || (isset($additionalData['comuproofAddData']['addproof_id']) && $additionalData['comuproofAddData']['addproof_id'] != ''))
                  
            <h5>Additional details :</h5> 
            <br>
            <table align="center">
                    @if(isset($additionalData['proofIdData']['proof_id']) && $additionalData['proofIdData']['proof_id'] != '')
                    <tr>
                        <td>
                            <input type="text" class="input_field" {{$disabled}} value="{{strtoupper($additionalData['proofIdData']['proof_id'])}}" style="height:30px;background-color:white;color: #0070C0;font-size: 12px;margin-left: 15px;">
                        </td>
                        <td>
                            @if(in_array($additionalData['proofIdData']['proof_id'],$masking_fields))
                                <input type="text" class="input_field enc_input unmaskingfield" {{$disabled}} value="{{($additionalData['proofIdData']['id_code'])}}" style="height:30px;background-color:white;color: #0070C0;font-size: 12px;margin-left: 15px; display:none;">
                                <input type="text" class="input_field maskingfield" {{$disabled}} value="**************" style="height:30px;background-color:white;color: #0070C0;font-size: 12px;margin-left: 15px;">
                            @else
                                <input type="text" class="input_field" {{$disabled}} value="{{($additionalData['proofIdData']['id_code'])}}" style="height:30px;background-color:white;color: #0070C0;font-size: 12px;margin-left: 15px;">
                            @endif
                        </td>
                        @if(isset($additionalData['proofIdData']['id_date']) && $additionalData['proofIdData']['id_date'] != '')
                            <td>
                                <input type="text" class="input_field" {{$disabled}} value="{{($additionalData['proofIdData']['id_date'])}}" style="height:30px;background-color:white;color: #0070C0;font-size: 12px;margin-left: 15px;">
                            </td>
                        @endif
                        @if(isset($additionalData['proofIdData']['issues_id_date']) && $additionalData['proofIdData']['issues_id_date'] != '')
                            <td>
                                <input type="text" class="input_field" {{$disabled}} value="{{($additionalData['proofIdData']['issues_id_date'])}}" style="height:30px;background-color:white;color: #0070C0;font-size: 12px;margin-left: 15px;">
                            </td>
                        @endif
                    </tr>

                            @endif
                    @if(isset($additionalData['comuproofAddData']['addproof_id']) && $additionalData['comuproofAddData']['addproof_id'] != '')
                    <tr>
                        <td>
                            <input type="text" class="input_field" {{$disabled}} value="{{strtoupper($additionalData['comuproofAddData']['addproof_id'])}}" style="height:30px;background-color:white;color: #0070C0;font-size: 12px;margin-left: 15px;">
                        </td>
                        <td>
                            <input type="text" class="input_field" {{$disabled}} value="{{strtoupper($additionalData['comuproofAddData']['addproof_no'])}}" style="height:30px;background-color:white;color: #0070C0;font-size: 12px;margin-left: 15px;">
                        </td>
                    </tr>
                    @endif
            </table>
        @endif
    
        @if(count($getImageData)>0)
        <table align="center" class="pdf-table">
            <tbody>
                <tr>
                    <td>
                        <h5>CRF proof document list</h5>
                    </td>
                </tr>
            </tbody>
        </table>
            <table align="center" class="pdf-table">
                <tbody>
                    @foreach($getImageData as $evid => $imageName)
                        @php
                            $imagePathCount = explode('/',$imageName);
                            $nameDoc = (isset($evidenceNameList[$evid]) && $evidenceNameList[$evid] != '') ? $evidenceNameList[$evid]->evidence : '';
                            $extensionImage = explode('.',$imageName)[1];
                        @endphp
                        
                        @if(($evid == 2 || $evid == 3) && ((isset($additionalData['proofIdData']['proof_id']) && $additionalData['proofIdData']['proof_id'] == 'Aadhaar Photocopy')))

                        @else
                        <tr>
                            <td style="margin-right:800px;">
                         
                                <h5><b>{{$nameDoc}}</b></h5>
                            
                                @if(isset($imageCheckCache) && $imageCheckCache == 'N')
                                    @if($extensionImage != 'pdf')
                                  {{--   <img src="{{URL::to('/showamendimage/'.$imageName)}}" style="height:240px;width:100%;border: 2px;"> --}}
                                    <div class="uploaded-img-ovd" style='filter:blur(30px);'>
                                   <img alt="imageName" src="{{ displayInlineImage($imageName) }}" style="height:200px;width:400px;" />
                                    </div>
                                    @else
                                        @php
                                            $pdfImageName =  base64_encode($imageName);
                                        @endphp
                                        <p style="margin-top:40px;">
                                            <i class="fa fa-file-pdf-o" style="font-size:48px;color:red"></i>
                                            <a href="{{URL::to('/showamendimage/'.$pdfImageName)}}" target="_blank" style="vertical-align: 10px;color: blue">{{explode('/',$imageName)[3]}}
                                            </a>
                                           {{--  <img alt="imageName" src="{{ getInlineImage($imageName) }}" /> --}}
                                        </p>
                                    @endif
                                @else
                                    @php
                                        $get_Path =  base64_encode($currentYear.'/'.$crfId.'/'.$customerReqId.'/'.$imageName);
                                    @endphp
                                    @if($extensionImage != 'pdf')
                                        <div class="uploaded-img-ovd" style='filter:blur(30px);'>
                                        @if(isset($osv_check->$evid) && $osv_check->$evid == 'Y')
                                            <img src="{{URL::to('/showamendimage/'.$currentYear.'/'.$crfId.'/'.$customerReqId.'/'.'OSV_DONE_'.$imageName)}}" style="height:240px;width:100%;border: 2px;">
                                        @else
                                            <img src="{{URL::to('/showamendimage/'.$get_Path)}}" style="height:240px;width:100%;border: 2px;">
                                        @endif
                                        </div>
                                    @else
                                        <p style="margin-top:40px;">
                                                <i class="fa fa-file-pdf-o" style="font-size:48px;color:red"></i>
                                                <a href="{{URL::to('/showamendimage/'.$get_Path)}}" target="_blank" style="vertical-align: 10px;color: blue">{{$imageName}}
                                                </a>
                                            </p>
                                    @endif
                                @endif
                            </td>
                    </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        @endif

        @if($printCall == 'Y')
        
        <table align="center" class="pdf-table">
            <tr>
                <td>
        <p align="justify">
        I/We have read, understood and hereby agree to the "Terms and Conditions" as applicable to my/our account set forth on DCB Bank Limited's ("DCB Bank") website at www.dcbbank.com. I/We understand, agree and confirm that, I/we will access DCB Bank's website www.dcbbank.com for any changes/updates in terms and conditions of the services and products as applicable. I/We authorise a representative of DCB Bank to enter the required details on my/our behalf and as per the instruction given by me/us in the electronic application. The Bank's officials have explained in detail about the contents of this electronic application form in English/my/our vernacular language. I/We have reviewed and verified the details entered in the electronic application form and declare same to be true, correct and updated.
       </p>
       <br>
       <br>

       <p style="text-align:right">
         ----------------------------------
       </p>
        <p style="text-align:right">
        Customer Signature
       </p>
       </td>
            </tr>
            
        </table>
        @endif

<script>
    var callPrint = document.getElementById('printCall').value;
    if(callPrint == 'Y'){
        setTimeout(function () {
            window.print();
        }, 1500);
    }   
</script>

</section>
    @if($showApproval != 'false')
    <section style="background-color: #fff0d3;">
            <table class="pdf-table" align="center">
                <tbody>
                    <tr>
                        <td>
                            <div style="text-align:center;font-size:18px;">
                               <p> Click the below button to approve the request </p>
                            </div>
                            <div style="width: 100px; margin: 0 auto;">
                                    <a href="{{Cache::get('amendUrl'.$crf_number)}}" target="_blank" style="padding: 2mm 6mm 2mm 6mm;text-decoration: none;color:black;background-color: yellow;display: inline-block;text-align: center;">APPROVE</a>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
            </section>
        @endif
</center>
</div>
<script src="{{asset("components/jquery/js/jquery.min.js")}}"></script>
<script src="{{ asset('assets/js/crypto-js.js') }}"></script>
<script src="{{asset("custom/js/app.js")}}"></script>
<script>
    masking_time_count = "{{Session::get('maks_timer') ?? ''}}";
    if(masking_time_count == ''){
        masking_time_count = 120000;
    }
    decrypt_filds();
    $("input.maskingfield").prop("disabled","true");
</script>
</body>
</html>