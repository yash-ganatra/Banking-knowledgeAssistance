@php
    $hub_roles = [1,2,3,4];
    $accountType = '';
    $no_of_account_holders = '1';
    $mode_of_operation = '';
    $pf_type = 'pancard';
    $pancard_no = '';
    $dob = '';
    $marital_status = '';
    $residential_status = '';
    $customer_account_type = '';
    $empno = '';
    $label_code = '';
    $elite_account_number = '';
    $mobile_number = '';
    $email = '';
    $scheme_code = '';
    $pan_osv_check = '';
    $class = "inactive";
    $display = "";
    $readonly = "";
    $enable = "display-none";
    $is_review = 0;
    $folder = "";
    $account_id = "";
    $disabled = "";
    $dateDisabled = '';
    $etbreadonly = "";
    $etbdisabled = "";
    $customertype = "";
    $etbSearchDisplay = "";
    $customer_full_name = '';
    $delightKitNumber = '';
    $role = Session::get('role');
    $allowETBbutton = '';
    $huf_signatory_relation = '';
    $huf_dnon = '';
    $normal_dnon = '';
    $is_huf = false;
    $huf_edit = true;
    $huf_all = false;
    $pan_ = '';
@endphp
@if(Session::get('is_review') == 1)
    @php
        $is_review = 1;
        $enable = "";
        $readonly = "readonly";
        $folder = "markedattachments";
        $disabled = "disabled";
        $dateDisabled = "disabled";
        $etbSearchDisplay = 'display-none';
    @endphp
@endif
@if(Session::get('formId') != '')
    @php
        $etbSearchDisplay = '';
    @endphp
@endif
@if(Session::get('role') == "11")
    @php
         $etbdisabled = "disabled";
         $etbreadonly = "readonly";
         $customertype = "etb";
    @endphp
@endif
@if(count($customerOvdDetails) > 0)
    @php
    $huf_edit = false;
       // if(Session::get('customer_type') != "ETB")
        if($customerOvdDetails[$i]['is_new_customer'] == '1'){
            $pf_type = $customerOvdDetails[$i]['pf_type'];
            $marital_status = $customerOvdDetails[$i]['marital_status'];
            $residential_status = $customerOvdDetails[$i]['residential_status'];
            $customer_account_type = $customerOvdDetails[$i]['customer_account_type'];
            $empno = $customerOvdDetails[$i]['empno'];
            $label_code = $customerOvdDetails[$i]['label_code'];
            $elite_account_number = $customerOvdDetails[$i]['elite_account_number'];
            $email = $customerOvdDetails[$i]['email'];
            $huf_signatory_relation = $customerOvdDetails[$i]['huf_signatory_relation'];
            $etbreadonly = "";            
            $etbdisabled = "";
            $customertype = "";
            $customer_full_name = "";
        }else{
            $pf_type = $customerOvdDetails[$i]['pf_type'];
            //$customer_full_name = $userDetails['AccountDetails']['customer_full_name'];
            $customer_full_name = $customerOvdDetails[$i]['first_name']. ' '.$customerOvdDetails[$i]['middle_name'].' '.$customerOvdDetails[$i]['last_name'];
            $email = $customerOvdDetails[$i]['email'];
            $accountType = $userDetails['AccountDetails']['account_type'];
            $scheme_code = $userDetails['AccountDetails']['scheme_code'];
            if (isset($customerOvdDetails[$i]['label_code'])) {
                $label_code = $customerOvdDetails[$i]['label_code'];
            }
            if (isset($customerOvdDetails[$i]['elite_account_number'])) {
                $elite_account_number = $customerOvdDetails[$i]['elite_account_number'];
            }
            $etbreadonly = "readonly";
            $customertype = "etb";
            $etbdisabled = "disabled";
        }
        $pancard_no = $customerOvdDetails[$i]['pancard_no'];
        $dob = Carbon\Carbon::parse($customerOvdDetails[$i]['dob'])->format('d-m-Y');
        if(isset($customerOvdDetails[$i]['pf_type_image'])){
            $pf_type_image = $customerOvdDetails[$i]['pf_type_image'];
        }else{
            $pf_type_image = '';
        }
        $mobile_number = $customerOvdDetails[$i]['mobile_number'];
        if(isset($AccountIds[$i])){
            $account_id = $AccountIds[$i];
        }
        $pan_osv_check = 1;
        $class = "active";
        $display = "display-none";
        $folder  = "attachments";

        
        if(isset($userDetails['AccountDetails']['constitution']) && $userDetails['AccountDetails']['constitution'] == 'NON_IND_HUF'){
            $huf_all = true;
        }
        if(isset($userDetails['AccountDetails']['constitution']) && $userDetails['AccountDetails']['constitution'] == 'NON_IND_HUF' && $i == 2){
            $normal_dnon = '';
            $huf_dnon = 'display-none';
            $is_huf = true;
            $maritalStatus = array_filter($maritalStatus,fn($key)=> $key == 3 , ARRAY_FILTER_USE_KEY);
            $pan_ = 'pan_huf';
        }else{
            $normal_dnon = 'display-none';
            $huf_dnon = '';
            $is_huf = false;

        }
    @endphp
@endif

@if(Session::get('is_review') == 1)
    @php
        $is_review = 1;
        $folder = "markedattachments";
    @endphp
@endif
@php
    if(isset($allowETB) && $allowETB == "ALL"){
        $allowETBbutton = true;
    }else{
        $allowETBbutton = false;
    }

    $def_blur_image = "";
    if($is_review == 1){
        $def_blur_image = "style=filter:blur(30px);";
    }
@endphp
<div id="tab{{$i}}" class="tab-content-cust">
    <div class="card AccountForm" id="{{$i}}">
        <span class="visibility_check" id="visibility_check-{{$i}}"></span>
        <input type="hidden" id="applicantId-{{$i}}" value="{{$account_id}}" customertype="{{$customertype}}">
        <!-- <form id="addAccountForm" method="post" novalidate> -->
            <div class="card-block">
                <div class="row">
                    <div class="col-md-12">
                        <div class="radio-selection mb-2">
                            <label class="radio">
                                <input class="AddPanDetailsField pf_type" type="radio" id="pf_type-{{$i}}" name="pf_type-{{$i}}" value="pancard" {{ ($pf_type=="pancard")? "checked" : "" }} {{$disabled}} {{$etbdisabled}}>
                                <span class="lbl padding-8">Pan card</span>
                            </label>
                            @if(!$huf_all)
                            <label class="radio form60">
                                <input class="AddPanDetailsField pf_type" type="radio" id="pf_type-{{$i}}" name="pf_type-{{$i}}" value="form60" {{ ($pf_type=="form60")? "checked" : "" }} {{$disabled}} {{$etbdisabled}}>
                                <span class="lbl padding-8">Form 60</span>
                            </label>
                            @endif
                            @if((Session::get('role') == "11") || (Session::get('role') == "2") && ($allowETBbutton) && !$is_huf)
                                <div class="d-flex flex-row-reverse adb-btn-inn existing_cust {{$etbSearchDisplay}} ">
                                    <button type="button" class="btn btn-outline-grey waves-effect tooltipp customer_modal"  id="etb_button-{{$i}}" data-id="{{$i}}" data-toggle="modal" data-target="#customer_modal">
                                        <span class="tooltiptext">Fetch Existing Customer</span>
                                        <span class="adb-icon">
                                            <i class="fa fa-search"></i>
                                        </span>
                                        ETB
                                    </button>                                       
                                </div>        
                            @endif              
                        </div>
                        
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-4 col-md-12">
                        <div class="form-group" id="pf_type_proof-{{$i}}">
                            <div class="detaisl-left align-content-center ">
                                <label class="">Upload PAN</label>
                                <span class="{{$enable}}">
                                    @if(isset($reviewDetails['pf_type_image-'.$i]))
                                        <i class="fa fa-times"></i>
                                        {{$reviewDetails['pf_type_image-'.$i]}}
                                        <a href="javascript:void(0)" class="text-link editColumn">Edit</a>
                                    @else
                                        <i class="fa fa-check"></i>
                                    @endif
                                </span>
                            </div>
                            <div class="add-document d-flex align-items-center justify-content-around" id="pf_type_card-{{$i}}">
                                @if(isset($pf_type_image) && ($pf_type_image != ''))
                                    <div id="pf_type_card-{{$i}}_div">
                                        @if($enable == 'display-none')
                                            <div class="upload-delete">
                                                <button type="button" class="delete-icon btn btn-danger btn-icon waves-effect waves-light deleteImage">
                                                    <i class="fa fa-trash" aria-hidden="true"></i>
                                                </button>
                                            </div>
                                        @else
                                            @if(isset($reviewDetails['pf_type_image-'.$i]))
                                                <div class="upload-delete">
                                                    <button type="button" class="delete-icon btn btn-danger btn-icon waves-effect waves-light deleteImage">
                                                        <i class="fa fa-trash" aria-hidden="true"></i>
                                                    </button>
                                                </div>
                                            @else
                                            @endif
                                        @endif
                                        <div class="uploaded-img-ovd" {{$def_blur_image}}>
                                        <img class="uploaded_image" name="pf_type_image" id="document_preview_pf_type" src="{{URL::to('/images'.$folder.'/'.$formId.'/_DONOTSIGN_'.$pf_type_image)}}" onerror="imgNotFound('PAN/ FORM60')">
                                    </div>
                                    </div>
                                @endif
                                @if(isset($pf_type_image) && ($pf_type_image != ''))
                                    <div class="add-document-btn adb-btn-inn display-none">
                                @else
                                    <div class="add-document-btn adb-btn-inn">
                                @endif
                                    @if($customertype != "etb")
                                        <button type="button" id="upload_pan_card" class="btn btn-outline-grey waves-effect upload_document" data-toggle="modal" 
                                        data-id="pf_type_card-{{$i}}" data-name="pf_type_image-{{$i}}"  data-document="Image" data-target="#upload_pan">
                                            
                                                <span class="adb-icon">
                                                    <i class="fa fa-plus-circle"></i>
                                                </span>
                                                Add PAN
                                        </button>
                                    @endif
                                </div>
                            </div>
                            <input type="text" style="opacity:0" name="panImage" id="panImage-{{$i}}">
                        </div>
                    </div>
                    <div class="col-lg-8 col-md-12">
                        <div class="row">
                            @if($pf_type == "pancard")
                                <div class="details-custcol-row col-sm-6" id="pancardnoDiv_{{$i}}">
                                    <div class="details-custcol-row-top d-flex editColumnDiv">
                                        <div class="detaisl-left d-flex align-content-center ">
                                            <p class="lable-cus">PAN Number</p> 
                                            <a href="javascript:void(0)" style="margin-left: 5px;" class="panIsValid" id="panIsValid_{{$i}}">
                                                <i class="fa fa-refresh" aria-hidden="true"></i>
                                            </a>
                                            <span class="{{$enable}}">
                                                @if(isset($reviewDetails['pancard_no-'.$i]))
                                                    <i class="fa fa-times"></i>
                                                    {{$reviewDetails['pancard_no-'.$i]}}
                                                    <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                @else
                                                    <i class="fa fa-check"></i>
                                                @endif
                                            </span>
                                        </div>                                                   
                                    </div>
                                    <div class="details-custcol-row-bootm">
                                        <div class="comments-blck">
                                            <div class="pandiv">
                                            </div>
                                            <input type="text" class="form-control AddPanDetailsField enc_input pan {{$pan_}} {{$is_review==1 ?  "unmaskingfield": ''}}" spellcheck="false" table="customer_ovd_details" id="pancard_no-{{$i}}" {{ $is_review==1 ? 'style=display:none;' : ''}} name="pancard_no" value="{{$pancard_no}}" {{$readonly}} {{$etbreadonly}} onkeyup="this.value = this.value.toUpperCase();">
                                            <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                                            @if($is_review==1)
                                            <input type="password" class="form-control maskingfield" value="**************" {{$readonly}} {{$etbreadonly}}>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @else
                            <div class="details-custcol-row col-sm-6 display-none" id="pancardnoDiv_1">
                                <div class="details-custcol-row-top d-flex editColumnDiv">
                                    <div class="detaisl-left d-flex align-content-center ">
                                        <p class="lable-cus">PAN Number</p> 
                                        <a href="javascript:void(0)" style="margin-left: 5px;" class="panIsValid" id="panIsValid_1">
                                            <i class="fa fa-refresh" aria-hidden="true"></i>
                                        </a>
                                        <span class="display-none">
                                            <i class="fa fa-check"></i>
                                        </span>
                                    </div>                                                   
                                </div>
                                <div class="details-custcol-row-bootm">
                                    <div class="comments-blck">
                                        <div class="pandiv"></div>
                                        <input type="text" class="form-control AddPanDetailsField enc_input pan" spellcheck="false" table="customer_ovd_details" id="pancard_no-1" name="pancard_no" onkeyup="this.value = this.value.toUpperCase();" inputmode="text">
                                        <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                                    </div>
                                </div>
                            </div>
                            @endif
                            
                            <div class="details-custcol-row col-sm-6">
                                <div class="details-custcol-row-top d-flex editColumnDiv">
                                    <div class="detaisl-left d-flex align-content-center ">
                                        <p class="lable-cus">
                                            {{ $huf_dnon ? 'DOF (Date Of Formation)' : 'DOB' }}
                                        </p>
                                        <span class="{{$enable}}">
                                            @if(isset($reviewDetails['dob-'.$i]))
                                                <i class="fa fa-times"></i>
                                                {{$reviewDetails['dob-'.$i]}}
                                                <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                            @else
                                                <i class="fa fa-check"></i>
                                            @endif
                                        </span>
                                    </div>                                                   
                                </div>
                                <div class="details-custcol-row-bootm">
                                    <div class="comments-blck">
                                        <input type="text" class="form-control AddPanDetailsField {{ $is_huf ? "dof" : "dob"}}" table="customer_ovd_details" onfocusout="simulatedatechange(this)" id="dob-{{$i}}" name="dob" value="{{$dob}}" {{$readonly}} {{$etbreadonly}} {{$dateDisabled}}>
                                        <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                                    </div>
                                </div>
                            </div>

                            @if($customertype == "etb")
                                <div class="details-custcol-row col-sm-6">                            
                                    <div class="details-custcol-row-top d-flex editColumnDiv">
                                        <div class="detaisl-left d-flex align-content-center ">
                                            <p class="lable-cus">Customer Name</p> 
                                            <span class="{{$enable}}">
                                                @if(isset($reviewDetails['customerName-'.$i]))
                                                    <i class="fa fa-times"></i>
                                                    {{$reviewDetails['customerName-'.$i]}}
                                                    <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                @else
                                                    <i class="fa fa-check"></i>
                                                @endif
                                            </span>
                                        </div>                                                   
                                    </div>
                                    <div class="details-custcol-row-bootm">
                                        <div class="comments-blck">
                                            <input type="text" class="form-control" id="customer_full_name-{{$i}}" name="customer_full_name" value="{{$customer_full_name}}" {{$etbreadonly}}>
                                            <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                                        </div>
                                    </div>
                                </div>

                                

                                @if($role == '2')
                                <div class="details-custcol-row col-sm-6">
                                    <div class="details-custcol-row-top d-flex editColumnDiv">
                                        <div class="detaisl-left d-flex align-content-center ">
                                            <p class="lable-cus">Mobile Number</p> 
                                            <span class="{{$enable}}">
                                                @if(isset($reviewDetails['mobile_number-'.$i]))
                                                    <i class="fa fa-times"></i>
                                                    {{$reviewDetails['mobile_number-'.$i]}}
                                                    <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                @else
                                                    <i class="fa fa-check"></i>
                                                @endif
                                            </span>
                                        </div>                                                   
                                    </div>
                                    <div class="details-custcol-row-bootm">
                                        <div class="comments-blck">
                                            <input type="text" class="form-control AddPanDetailsField enc_input mobile_number mobile {{$is_review==1 ?  "unmaskingfield": ""}}" {{ $is_review==1 ? 'style=display:none;':''}} table="customer_ovd_details" id="mobile_number-{{$i}}" name="mobile_number" value="{{$mobile_number}}" {{$readonly}} 
                                             oninput="this.value = this.value.replace(/^([6789]\d{10}|[0-5]\d*)$/gi, '').replace(/(\..*)\./g, '$1');">
                                            
                                            <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                                            @if($is_review==1)
                                             <input type="password" class="form-control maskingfield" value="*************" {{$readonly}} {{$etbreadonly}}>
                                             @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="details-custcol-row col-sm-6">
                                    <div class="details-custcol-row-top d-flex editColumnDiv">
                                        <div class="detaisl-left d-flex align-content-center ">
                                            <p class="lable-cus">E-mail ID</p> 
                                            <span class="{{$enable}}">
                                                @if(isset($reviewDetails['email-'.$i]))
                                                    <i class="fa fa-times"></i>
                                                    {{$reviewDetails['email-'.$i]}}
                                                    <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                @else
                                                    <i class="fa fa-check"></i>
                                                @endif
                                            </span>
                                        </div>                                                   
                                    </div>
                                    <div class="details-custcol-row-bootm">
                                        <div class="comments-blck">
                                            <input type="email" class="form-control AddPanDetailsField enc_input email {{$is_review==1 ?  "unmaskingfield": ''}}" {{ $is_review==1 ? 'style=display:none;':""}} table="customer_ovd_details" id="email-{{$i}}" name="email" value="{{$email}}" {{$readonly}}>
                                            <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                                            @if($is_review==1)
                                            <input type="password" class="form-control maskingfield" value="*************" {{$readonly}} {{$etbreadonly}}>
                                            @endif
                                        </div>
                                    </div>
                                </div> 
                                @else
                                <div class="details-custcol-row col-sm-6">
                                    <div class="details-custcol-row-top d-flex editColumnDiv">
                                        <div class="detaisl-left d-flex align-content-center ">
                                            <p class="lable-cus">Mobile Number</p> 
                                            <span class="{{$enable}}">
                                                @if(isset($reviewDetails['mobile_number-'.$i]))
                                                    <i class="fa fa-times"></i>
                                                    {{$reviewDetails['mobile_number-'.$i]}}
                                                    <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                @else
                                                    <i class="fa fa-check"></i>
                                                @endif
                                            </span>
                                        </div>                                                   
                                    </div>
                                    <div class="details-custcol-row-bootm">
                                        <div class="comments-blck">
                                            <input type="text" class="form-control AddPanDetailsField mobile_number enc_input mobile {{$is_review==1 ?  "unmaskingfield": ""}}" {{ $is_review==1 ? 'style=display:none;':''}} table="customer_ovd_details" id="mobile_number-{{$i}}" name="mobile_number" value="{{$mobile_number}}" {{$readonly}} {{$etbreadonly}}
                                             oninput="this.value = this.value.replace(/^([6789]\d{10}|[0-5]\d*)$/gi, '').replace(/(\..*)\./g, '$1');">
                                            <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                                            @if($is_review==1)
                                             <input type="password" class="form-control maskingfield" value="************" {{$readonly}} {{$etbreadonly}}>
                                             @endif
                                        </div>
                                    </div>
                                </div>
                                @endif

                                @if($i == 1)
                                    @if(($accountType == 1) && ($scheme_code == 11))
                                        <div class="details-custcol-row col-sm-6 eliteAccountNumberDiv">
                                            <div class="details-custcol-row-top d-flex editColumnDiv">
                                                <div class="detaisl-left d-flex align-content-center ">
                                                <p class="lable-cus">DCB Elite Account Number (Last 8 Characters)</p> 
                                                    <span class="{{$enable}}">
                                                        @if(isset($reviewDetails['elite_account_number-'.$i]))
                                                            <i class="fa fa-times"></i>
                                                            {{$reviewDetails['elite_account_number-'.$i]}}
                                                            <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                        @else
                                                            <i class="fa fa-check"></i>
                                                        @endif
                                                    </span>
                                                </div>                                                   
                                            </div>
                                            <div class="details-custcol-row-bootm">
                                                <div class="comments-blck">
                                                    <input type="text" class="form-control AddPanDetailsField" table="customer_ovd_details" id="elite_account_number-{{$i}}" name="elite_account_number" value="{{$elite_account_number}}" {{$readonly}} oninput="this.value = this.value.replace(/[^0-9]/gi, '').replace(/(\..*)\./g, '$1');" onkeyup="this.value = this.value.toUpperCase();" minlength="8" maxlength="8">
                                                    <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                    @if(($accountType == 2) && ($scheme_code == 4))
                                        <div class="details-custcol-row col-sm-6 eliteAccountNumberDiv">
                                            <div class="details-custcol-row-top d-flex editColumnDiv">
                                                <div class="detaisl-left d-flex align-content-center ">
                                                <p class="lable-cus">DCB Elite Account Number (Last 8 Characters)</p> 
                                                    <span class="{{$enable}}">
                                                        @if(isset($reviewDetails['elite_account_number-'.$i]))
                                                            <i class="fa fa-times"></i>
                                                            {{$reviewDetails['elite_account_number-'.$i]}}
                                                            <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                        @else
                                                            <i class="fa fa-check"></i>
                                                        @endif
                                                    </span>
                                                </div>                                                   
                                            </div>
                                            <div class="details-custcol-row-bootm">
                                                <div class="comments-blck">
                                                    <input type="text" class="form-control AddPanDetailsField" table="customer_ovd_details" id="elite_account_number-{{$i}}" name="elite_account_number" value="{{$elite_account_number}}" {{$readonly}} oninput="this.value = this.value.replace(/[^0-9]/gi, '').replace(/(\..*)\./g, '$1');" onkeyup="this.value = this.value.toUpperCase();" minlength="8" maxlength="8">
                                                    <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    @if(($accountType == 1) && ($scheme_code == 8))
                                        <div class="details-custcol-row col-sm-6 labelCodeDiv">
                                            <div class="details-custcol-row-top d-flex editColumnDiv">
                                                <div class="detaisl-left d-flex align-content-center ">
                                                    <p class="lable-cus">Label Code</p> 
                                                    <span class="{{$enable}}">
                                                        @if(isset($reviewDetails['label_code-'.$i]))
                                                            <i class="fa fa-times"></i>
                                                            {{$reviewDetails['label_code-'.$i]}}
                                                            <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                        @else
                                                            <i class="fa fa-check"></i>
                                                        @endif
                                                    </span>
                                                </div>                                                   
                                            </div>
                                            <div class="details-custcol-row-bootm">
                                                <div class="comments-blck">
                                                    <input type="text" class="form-control AddPanDetailsField" table="customer_ovd_details" id="label_code-{{$i}}" name="label_code" value="{{$label_code}}" {{$readonly}} oninput="this.value = this.value.replace(/[^a-z0-9]/gi, '').replace(/(\..*)\./g, '$1');" onkeyup="this.value = this.value.toUpperCase();">
                                                    <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endif
                                
                            @else 
                                <div class="details-custcol-row col-sm-6">
                                    <div class="details-custcol-row-top d-flex editColumnDiv">
                                        <div class="detaisl-left d-flex align-content-center ">
                                            <p class="lable-cus">Mobile Number</p> 
                                            <span class="{{$enable}}">
                                                @if(isset($reviewDetails['mobile_number-'.$i]))
                                                    <i class="fa fa-times"></i>
                                                    {{$reviewDetails['mobile_number-'.$i]}}
                                                    <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                @else
                                                    <i class="fa fa-check"></i>
                                                @endif
                                            </span>
                                        </div>                                                   
                                    </div>
                                    <div class="details-custcol-row-bootm">
                                        <div class="comments-blck">
                                            <input type="text" class="form-control AddPanDetailsField enc_input mobile_number mobile {{$is_review==1 ?  "unmaskingfield": ""}}" {{ $is_review==1 ? 'style=display:none;':''}} table="customer_ovd_details" id="mobile_number-{{$i}}" name="mobile_number" value="{{$mobile_number}}" {{$readonly}} {{$etbreadonly}}
                                             oninput="this.value = this.value.replace(/^([6789]\d{10}|[0-5]\d*)$/gi, '').replace(/(\..*)\./g, '$1');">
                                            <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                                            @if($is_review==1)
                                             <input type="password" class="form-control maskingfield" value="**************" {{$readonly}} {{$etbreadonly}}>
                                             @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="details-custcol-row col-sm-6">
                                    <div class="details-custcol-row-top d-flex editColumnDiv">
                                        <div class="detaisl-left d-flex align-content-center ">
                                            <p class="lable-cus">E-mail ID</p> 
                                            <span class="{{$enable}}">
                                                @if(isset($reviewDetails['email-'.$i]))
                                                    <i class="fa fa-times"></i>
                                                    {{$reviewDetails['email-'.$i]}}
                                                    <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                @else
                                                    <i class="fa fa-check"></i>
                                                @endif
                                            </span>
                                        </div>                                                   
                                    </div>
                                    <div class="details-custcol-row-bootm">
                                        <div class="comments-blck">
                                            <input type="email" class="form-control AddPanDetailsField enc_input email {{$is_review==1 ?  "unmaskingfield": ''}}" {{ $is_review==1 ? 'style=display:none;':""}} table="customer_ovd_details" id="email-{{$i}}" name="email" value="{{$email}}" {{$readonly}} {{$etbreadonly}}>
                                            <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                                            @if($is_review==1)
                                            <input type="password" class="form-control maskingfield" value="*************" {{$readonly}} {{$etbreadonly}}>
                                            @endif
                                        </div>
                                    </div>
                                </div> 

                                <div class="details-custcol-row col-sm-6">
                                    <div class="details-custcol-row-top d-flex editColumnDiv">
                                        <div class="detaisl-left d-flex align-content-center ">
                                            <p class="lable-cus">Marital Status</p> 
                                            <span class="{{$enable}}">
                                                @if(isset($reviewDetails['marital_status-'.$i]))
                                                    <i class="fa fa-times"></i>
                                                    {{$reviewDetails['marital_status-'.$i]}}
                                                    <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                @else
                                                    <i class="fa fa-check"></i>
                                                @endif
                                            </span>
                                        </div>                                                   
                                    </div>
                                    <div class="details-custcol-row-bootm">
                                        <div class="comments-blck">
                                            {!! Form::select('marital_status',$maritalStatus,$marital_status,array('class'=>'form-control marital_status AddPanDetailsField',
                                                'table'=>'customer_ovd_details','id'=>'marital_status-'.$i,'name'=>'marital_status','placeholder'=>'')) !!}
                                            <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                                        </div>
                                    </div>
                                </div>

                                <div class="details-custcol-row col-sm-6">
                                    <div class="details-custcol-row-top d-flex editColumnDiv">
                                        <div class="detaisl-left d-flex align-content-center ">
                                            <p class="lable-cus">Residential Status</p> 
                                            <span class="{{$enable}}">
                                                @if(isset($reviewDetails['residential_status-'.$i]))
                                                    <i class="fa fa-times"></i>
                                                    {{$reviewDetails['residential_status-'.$i]}}
                                                    <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                @else
                                                    <i class="fa fa-check"></i>
                                                @endif
                                            </span>
                                        </div>                                                   
                                    </div>
                                    <div class="details-custcol-row-bootm">
                                        <div class="comments-blck">
                                            {!! Form::select('residential_status',$residentialStatus,$residential_status,array('class'=>'form-control residential_status AddPanDetailsField',
                                                'table'=>'customer_ovd_details','id'=>'residential_status-'.$i,'name'=>'residential_status')) !!}
                                            <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                                        </div>
                                    </div>
                                </div>

                                <div class="details-custcol-row col-sm-6 {{$huf_dnon}}">
                                    <div class="details-custcol-row-top d-flex editColumnDiv">
                                        <div class="detaisl-left d-flex align-content-center ">
                                            <p class="lable-cus">Customer Account Type</p> 
                                            <span class="{{$enable}}">
                                                @if(isset($reviewDetails['customer_account_type-'.$i]))
                                                    <i class="fa fa-times"></i>
                                                    {{$reviewDetails['customer_account_type-'.$i]}}
                                                    <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                @else
                                                    <i class="fa fa-check"></i>
                                                @endif
                                            </span>
                                        </div>                                                   
                                    </div>
                                    <div class="details-custcol-row-bootm">
                                        <div class="comments-blck">
                                            {!! Form::select('customer_account_type',$customerAccountTypes,$customer_account_type,array('class'=>'form-control customer_account_type AddPanDetailsField',
                                                'table'=>'customer_ovd_details','id'=>'customer_account_type-'.$i,'name'=>'customer_account_type')) !!}
                                            <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                                        </div>
                                    </div>
                                </div>

                                @php
                                    $huf_rel = ["Karta"=>"Karta","Manager"=>"Manager"];
                                @endphp

                                @if(($is_huf && $i == 2) || ($huf_edit && $i == 2))
                                <div class="details-custcol-row col-sm-6 {{$normal_dnon}}" id="huf_reletionship-{{$i}}">
                                    <div class="details-custcol-row-top d-flex editColumnDiv">
                                        <div class="detaisl-left d-flex align-content-center ">
                                            <p class="lable-cus">Relationship Between HUF & Signatory</p> 
                                            <span class="{{$enable}}">
                                                @if(isset($reviewDetails['huf_signatory_relation-'.$i]))
                                                    <i class="fa fa-times"></i>
                                                    {{$reviewDetails['huf_signatory_relation-'.$i]}}
                                                    <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                @else
                                                    <i class="fa fa-check"></i>
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                    <div class="details-custcol-row-bootm">
                                        <div class="comments-blck">
                                            {!! Form::select('huf_signatory_relation',$huf_rel,$huf_signatory_relation,array('class'=>'form-control huf_signatory_relation AddPanDetailsField',
                                                'table'=>'customer_ovd_details','id'=>'huf_signatory_relation-'.$i,'name'=>'huf_signatory_relation')) !!}
                                            <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                                        </div>
                                    </div>
                                </div>
                                @endif

                                @if($customer_account_type == 3)
                                    <div class="details-custcol-row col-sm-6 empno" id="empnoDiv-{{$i}}">
                                @else
                                    <div class="details-custcol-row col-sm-6 display-none empno" id="empnoDiv-{{$i}}">
                                @endif
                                    <div class="details-custcol-row-top d-flex editColumnDiv">
                                        <div class="detaisl-left d-flex align-content-center ">
                                            <p class="lable-cus">Employee Number</p> 
                                            <span class="{{$enable}}">
                                                @if(isset($reviewDetails['empno-'.$i]))
                                                    <i class="fa fa-times"></i>
                                                    {{$reviewDetails['empno-'.$i]}}
                                                    <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                @else
                                                    <i class="fa fa-check"></i>
                                                @endif
                                            </span>
                                        </div>                                                   
                                    </div>
                                    <div class="details-custcol-row-bootm">
                                        <div class="comments-blck">
                                            <input type="text" class="form-control AddPanDetailsField" table="customer_ovd_details" id="empno-{{$i}}" name="empno" value="{{$empno}}" {{$readonly}} {{$etbreadonly}} oninput="this.value = this.value.replace(/[^0-9]/gi, '').replace(/(\..*)\./g, '$1');" onkeyup="this.value = this.value.toUpperCase();" maxlength="6">
                                            <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                                        </div>
                                    </div>
                                </div>

                                @if($i == 1)
                                    @if(($accountType == 1) && ($scheme_code == 8))
                                        <div class="details-custcol-row col-sm-6 labelCodeDiv">
                                    @else
                                        <div class="details-custcol-row col-sm-6 labelCodeDiv display-none">
                                    @endif
                                        <div class="details-custcol-row-top d-flex editColumnDiv">
                                            <div class="detaisl-left d-flex align-content-center ">
                                                <p class="lable-cus">Label Code</p> 
                                                <span class="{{$enable}}">
                                                    @if(isset($reviewDetails['label_code-'.$i]))
                                                        <i class="fa fa-times"></i>
                                                        {{$reviewDetails['label_code-'.$i]}}
                                                        <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                    @else
                                                        <i class="fa fa-check"></i>
                                                    @endif
                                                </span>
                                            </div>                                                   
                                        </div>
                                        <div class="details-custcol-row-bootm">
                                            <div class="comments-blck">
                                                <input type="text" class="form-control AddPanDetailsField" table="customer_ovd_details" id="label_code-{{$i}}" name="label_code" value="{{$label_code}}" {{$readonly}} {{$etbreadonly}} oninput="this.value = this.value.replace(/[^a-z0-9]/gi, '').replace(/(\..*)\./g, '$1');" onkeyup="this.value = this.value.toUpperCase();">
                                                <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                @if($i == 1)
                                    @if(($accountType == 1) && ($scheme_code == 11))
                                        <div class="details-custcol-row col-sm-6 eliteAccountNumberDiv">
                                    @elseif(($accountType == 2) && ($scheme_code == 4))
                                        <div class="details-custcol-row col-sm-6 eliteAccountNumberDiv">
                                    @else
                                        <div class="details-custcol-row col-sm-6 eliteAccountNumberDiv display-none">
                                    @endif


                                        <div class="details-custcol-row-top d-flex editColumnDiv">
                                            <div class="detaisl-left d-flex align-content-center ">
                                                <p class="lable-cus">DCB Elite Account Number (Last 8 Characters)</p> 
                                                <span class="{{$enable}}">
                                                    @if(isset($reviewDetails['elite_account_number-'.$i]))
                                                        <i class="fa fa-times"></i>
                                                        {{$reviewDetails['elite_account_number-'.$i]}}
                                                        <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                    @else
                                                        <i class="fa fa-check"></i>
                                                    @endif
                                                </span>
                                            </div>                                                   
                                        </div>
                                        <div class="details-custcol-row-bootm">
                                            <div class="comments-blck">
                                                <input type="text" class="form-control AddPanDetailsField" table="customer_ovd_details" id="elite_account_number-{{$i}}" name="elite_account_number" value="{{$elite_account_number}}" {{$readonly}} {{$etbreadonly}} oninput="this.value = this.value.replace(/[^0-9]/gi, '').replace(/(\..*)\./g, '$1');" minlength="8" maxlength="8" onkeyup="this.value = this.value.toUpperCase();">
                                                <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                            @endif
                        </div>
                    </div>
                    <span id="delight-kit-id" class="display-none">{{$delightKitNumber}}</span>
                </div>
              
                <div class="col-md-12 text-center mt-3 mb-3">
                    @if((isset($AccountIds)) && (count($AccountIds) > 0))
                        @if($i == $accountHoldersCount)
                            <a href="javascript:void(0)" class="btn btn-primary mb-3 saveAccountDetails {{ $is_huf ? 'is_huf' : '' }}" id="nextapplicant-{{$i}}" tab="nextapplicant">
                                Save and Continue
                            </a>
                        @else
                            <a href="javascript:void(0)" class="btn btn-primary nextapplicant mb-3 {{ $is_huf ? 'is_huf' : '' }}" id="nextapplicant-{{$i}}" tab="nextapplicant">
                                Next
                            </a>
                        @endif
                    @else
                        <a href="javascript:void(0)" class="btn btn-primary  mb-3 saveAccountDetails {{ $is_huf ? 'is_huf' : '' }}" id="nextapplicant-{{$i}}" tab="nextapplicant">
                            Save and Continue
                        </a>
                    @endif                    
                    
                    {{-- <a href="javascript:void(0)" class="btn btn-primary nexthuftab mb-3" id="hubnexttab" tab="tab-huf">
                        Next 
                    </a>  --}}
                </div>
            </div>
        <!-- </form> -->
    </div>
</div>

