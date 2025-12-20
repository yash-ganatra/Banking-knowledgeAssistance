@php
$image_mask_blur = "";
$def_blur_image = "";
if($is_review==1){
    $image_mask_blur = "uploaded-img-ovd";
    $def_blur_image = "style=filter:blur(30px);";
}

 if($accountDetails['constitution'] == 'NON_IND_HUF'){
    $is_huf_display = true;
 }else{
    $is_huf_display = false;
 }
@endphp
<div class="row">
    <div class="col-lg-12">
        <div class="row">
            <div class="col-lg-12">
                <h4 class="sub-title">Initial Funding</h4>
            </div>
            <div class="col-lg-12">
                <div class="radio-selection mb-2">
                    <div class="">
                        <label class="radio">
                            <input type="radio" name="initial_funding_type" class="AddFinancialinfoField" value="1" {{ ($initial_funding_type == 1) ? "checked" : '' }} {{$disabled}}>
                            <span class="lbl padding-8">Cheque</span>
                        </label>
                        <label class="radio">
                            <input type="radio" name="initial_funding_type" class="AddFinancialinfoField" value="2" {{ ($initial_funding_type == 2) ? "checked" : '' }} {{$disabled}}>
                            <span class="lbl padding-8">NEFT/RTGS</span>
                        </label>
                        @if((Session::get('customer_type') == "ETB") && (!$is_minor) && ($accountType == 3))
                        <label class="radio">
                            @else
                            <label class="radio display-none">
                                @endif
                                @if($msg != '')
                                <input type="radio" name="initial_funding_type" class="AddFinancialinfoField" value="3" {{ ($initial_funding_type == 3) ? "checked" : '' }} {{$disabled}} disabled>
                                @else
                                <input type="radio" name="initial_funding_type" class="AddFinancialinfoField" value="3" {{ ($initial_funding_type == 3) ? "checked" : '' }} {{$disabled}}>
                                @endif
                                <span class="lbl padding-8">DCB Account {{($msg != '')? '('.$msg .')' : ''}}</span>
                            </label>
                            @if((isset($cc_etb_details)) && ($cc_etb_details['etb_cc'] == "CC") && (!$is_minor) && ($accountType == 3))
                            <label class="radio">
                                @else
                                <label class="radio display-none">
                                    @endif
                                    
                                    <input type="radio" name="initial_funding_type" class="AddFinancialinfoField" value="3" {{ ($initial_funding_type == 3) ? "checked" : '' }} {{$disabled}}>
                                    <span class="lbl padding-8">DCB Account</span>
                                </label>
                                @if(!in_array($accountType,[3,4]))
                                <label class="radio">
                                    @else
                                    <label class="radio display-none">
                                        @endif
                                        <input type="radio" name="initial_funding_type" class="AddFinancialinfoField" value="5" {{ ($initial_funding_type == 5) ? "checked" : '' }} {{$disabled}}>
                                        <span class="lbl padding-8">Others</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!---------------Row Start Here ---------------------------------->
                    <div class="row">
                        @if((count($userDetails) > 0) && ($initial_funding_type == 1))
                        <div class="col-md-4" id="upload-cheque-div">
                            @else
                            <div class="col-md-4 display-none" id="upload-cheque-div">
                                @endif
                                <div class="form-group">
                                    <div class="detaisl-left align-content-center ">
                                        <label class="uploadLabel">Upload Cheque</label>
                                        <span class="{{$enable}}">
                                            @if(isset($reviewDetails['cheque_image']))
                                            <i class="fa fa-times"></i>
                                            {{$reviewDetails['cheque_image']}}
                                            <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                            @else
                                            <i class="fa fa-check"></i>
                                            @endif
                                        </span>
                                    </div>
                                    <div class="add-document d-flex align-items-center justify-content-around" id="cheque_image">
                                        @if(isset($cheque_image) && ($cheque_image != ''))
                                        <div id="cheque_div">
                                            @if($enable == 'display-none')
                                            <div class="upload-delete">
                                                <button type="button" class="delete-icon btn btn-danger btn-icon waves-effect waves-light deleteImage">
                                                <i class="fa fa-trash" aria-hidden="true"></i>
                                                </button>
                                            </div>
                                            @else
                                            @if(isset($reviewDetails['cheque_image']))
                                            <div class="upload-delete">
                                                <button type="button" class="delete-icon btn btn-danger btn-icon waves-effect waves-light deleteImage">
                                                <i class="fa fa-trash" aria-hidden="true"></i>
                                                </button>
                                            </div>
                                            @else
                                            @endif
                                            @endif
                                            <div class="{{$image_mask_blur}}" {{$def_blur_image}}>
                                            <img class="uploaded_image" name="cheque_image" id="document_preview_cheque" src="{{URL::to('/images'.$folder.'/'.$formId.'/'.$cheque_image)}}"  onerror="imgNotFound('Cheque')">
                                        </div>
                                        </div>
                                        @endif
                                        @if(isset($cheque_image) && ($cheque_image != ''))
                                        <div class="add-document-btn adb-btn-inn display-none">
                                            @else
                                            <div class="add-document-btn adb-btn-inn">
                                                @endif
                                                <button type="button" id="upload_pan_card" class="btn btn-outline-grey waves-effect upload_document" data-toggle="modal"
                                                data-id="cheque_image" data-class="AddFinancialinfoField" data-name="cheque_image"  data-document="Cheque" data-target="#upload_cheque">
                                                <span class="adb-icon">
                                                    <i class="fa fa-plus-circle"></i>
                                                </span>
                                                Add Cheque
                                                </button>
                                            </div>
                                        </div>
                                        <input type="text" style="opacity:0" name="cheque_image">
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="row">
                                        <div class="details-custcol-row col-md-6 {{$funding_source_class}} {{$direct_class}}">
                                            <div class="details-custcol-row-top d-flex editColumnDiv">
                                                <div class="detaisl-left d-flex align-content-center">
                                                    <p class="lable-cus" id="date_label">
                                                        @if($initial_funding_type == 1)
                                                        Cheque Date
                                                        @else
                                                        Transaction Date
                                                        @endif
                                                    </p>
                                                    <span class="{{$enable}}">
                                                        @if(isset($reviewDetails['initial_funding_date']))
                                                        <i class="fa fa-times"></i>
                                                        {{$reviewDetails['initial_funding_date']}}
                                                        <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                        @else
                                                        <i class="fa fa-check"></i>
                                                        @endif
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="details-custcol-row-bootm">
                                                <div class="comments-blck">
                                                    <input type="text" class="form-control AddFinancialinfoField" table="customer_ovd_details" onfocusout="simulatedatechange(this)" name="initial_funding_date" id="initial_funding_date" value="{{$initial_funding_date}}" {{$readonly}} {{$dateDisabled}}>
                                                    <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                                                </div>
                                            </div>
                                        </div>
                                        @if($initial_funding_type == 5)
                                        <div class="details-custcol-row col-md-12" id="others_radio_div">
                                            @else
                                            <div class="details-custcol-row col-md-12 display-none" id="others_radio_div">
                                                @endif
                                                <h4 class="sub-title"></h4>
                                                <div class="details-custcol-row-top d-flex editColumnDiv">
                                                    <div class="detaisl-left d-flex align-content-center">
                                                        <!-- <p class="lable-cus">Radio</p> -->
                                                        <p class="lable-cus"></p>
                                                        <span class="{{$enable}}">
                                                            @if(isset($reviewDetails['amount']))
                                                            <i class="fa fa-times"></i>
                                                            {{$reviewDetails['amount']}}
                                                            <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                            @else
                                                            <i class="fa fa-check"></i>
                                                            @endif
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="details-custcol-row-bootm">
                                                    <div class="comments-blck">
                                                        <label class="radio">
                                                            <input class="AddFinancialinfoField others_type" type="checkbox" name="others_type" value="zero" {{ ($others_type=="zero")? "checked" : "" }} {{$readonly}} {{$disabled}}>
                                                            <span class="lbl padding-8">Zero Balance </span>
                                                        </label>
                                                        <!-- <label class="radio">
                                                            <input classs="AddFinancialinfoField others_type" type="radio" name="others_type"  value="cash" {{ ($others_type=="cash")? "checked" : "" }} {{$readonly}} {{$disabled}}>
                                                            <span class="lbl padding-8">Cash</span>
                                                        </label>
                                                        -->
                                                        @if($accountType == 3)
                                                        <label class="radio">
                                                            @else
                                                            <label class="radio display-none">
                                                                @endif
                                                                <input classs="AddFinancialinfoField others_type" type="radio" name="others_type" value="callcenter" {{ ($others_type=="callcenter")? "checked" : "" }} {{$readonly}} {{$disabled}}>
                                                                <span class="lbl padding-8">Call Center</span>
                                                            </label>
                                                            <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                                @if($initial_funding_type == 5)
                                                <div class="details-custcol-row col-md-6" id="others_div">
                                                    @else
                                                    <div class="details-custcol-row col-md-6 display-none"id="others_div">
                                                        @endif
                                                        <div class="details-custcol-row-top d-flex editColumnDiv">
                                                            <div class="detaisl-left d-flex align-content-center ">
                                                                <p class="lable-cus">Funding Source</p>
                                                                <span class="{{$enable}}">
                                                                    @if(isset($reviewDetails['funding_source']))
                                                                    <i class="fa fa-times"></i>
                                                                    {{$reviewDetails['funding_source']}}
                                                                    <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                    @else
                                                                    <i class="fa fa-check"></i>
                                                                    @endif
                                                                </span>
                                                            </div>
                                                        </div>
                                                        <div class="details-custcol-row-bootm">
                                                            <div class="comments-blck">
                                                                @if($others_type == "zero")
                                                                <input type="text" class="form-control AddFinancialinfoField input-capitalize" table="customer_ovd_details" name="funding_source" id="funding_source" value="" disabled="" onkeyup="this.value = this.value.toUpperCase();"  >
                                                                @else
                                                                <input type="text" class="form-control AddFinancialinfoField input-capitalize" table="customer_ovd_details" name="funding_source" id="funding_source" value="{{$funding_source}}" {{$readonly}} onkeyup="this.value = this.value.toUpperCase();" oninput="this.value = this.value.replace(/[^a-z0-9 ]/gi, '').replace(/(\..*)\./g, '$1');">
                                                                @endif
                                                                <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="details-custcol-row col-md-6 {{$funding_source_class}} {{$direct_class}}">
                                                        <div class="details-custcol-row-top d-flex editColumnDiv">
                                                            <div class="detaisl-left d-flex align-content-center">
                                                                <p class="lable-cus" id="reference_label">
                                                                    @if($initial_funding_type == 1)
                                                                    Cheque Number
                                                                    @else
                                                                    UTR Number
                                                                    @endif
                                                                </p>
                                                                <span class="{{$enable}}">
                                                                    @if(isset($reviewDetails['reference']))
                                                                    <i class="fa fa-times"></i>
                                                                    {{$reviewDetails['reference']}}
                                                                    <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                    @else
                                                                    <i class="fa fa-check"></i>
                                                                    @endif
                                                                </span>
                                                            </div>
                                                        </div>
                                                        <div class="details-custcol-row-bootm">
                                                            <div class="comments-blck">
                                                                @if($initial_funding_type == 1)
                                                                <input type="text" class="form-control AddFinancialinfoField" table="customer_ovd_details" name="reference" id="reference" value="{{$reference}}" onkeyup="this.value = this.value.toUpperCase();" {{$readonly}} maxlength="6">
                                                                <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                                                                @else
                                                                <input type="text" class="form-control AddFinancialinfoField" table="customer_ovd_details" name="reference" id="reference" value="{{$reference}}" onkeyup="this.value = this.value.toUpperCase();" {{$readonly}} maxlength="22">
                                                                <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="details-custcol-row col-md-6 {{$funding_source_class}} {{$direct_class}}" id="bank_name_div">
                                                        <div class="details-custcol-row-top d-flex editColumnDiv">
                                                            <div class="detaisl-left d-flex align-content-center ">
                                                                <p class="lable-cus">Bank Name</p>
                                                                <span class="{{$enable}}">
                                                                    @if(isset($reviewDetails['bank_name']))
                                                                    <i class="fa fa-times"></i>
                                                                    {{$reviewDetails['bank_name']}}
                                                                    <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                    @else
                                                                    <i class="fa fa-check"></i>
                                                                    @endif
                                                                </span>
                                                            </div>
                                                        </div>
                                                        <div class="details-custcol-row-bootm">
                                                            <div class="comments-blck">
                                                                {!! Form::select('Bank',$banksList,$bank_name,array('class'=>'form-control bank_name AddFinancialinfoField',
                                                                'table'=>'customer_ovd_details','id'=>'bank_name','name'=>'bank_name','placeholder'=>'Select Bank name')) !!}
                                                                <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="details-custcol-row col-md-6 {{$funding_source_class}} {{$direct_class}}" id="ifsc_code_div">
                                                        <div class="details-custcol-row-top d-flex editColumnDiv">
                                                            <div class="detaisl-left d-flex align-content-center ">
                                                                <p class="lable-cus">IFSC Code</p>
                                                                <span class="{{$enable}}">
                                                                    @if(isset($reviewDetails['ifsc_code']))
                                                                    <i class="fa fa-times"></i>
                                                                    {{$reviewDetails['ifsc_code']}}
                                                                    <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                    @else
                                                                    <i class="fa fa-check"></i>
                                                                    @endif
                                                                </span>
                                                            </div>
                                                        </div>
                                                        <div class="details-custcol-row-bootm">
                                                            <div class="comments-blck">
                                                                <input type="text" class="form-control AddFinancialinfoField ifsc_code" table="customer_ovd_details" name="ifsc_code" id="ifsc_code" value="{{$ifsc_code}}" maxlength="11" onkeyup="this.value = this.value.toUpperCase();" {{$readonly}} oninput="this.value = this.value.replace(/[^a-z0-9]/gi, '').replace(/(\..*)\./g, '$1');">
                                                                <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="details-custcol-row col-md-6 {{$funding_source_class}} {{$direct_class}}">
                                                        <div class="details-custcol-row-top d-flex editColumnDiv">
                                                            <div class="detaisl-left d-flex align-content-center ">
                                                                <p class="lable-cus">Account Number</p>
                                                                <span class="{{$enable}}">
                                                                    @if(isset($reviewDetails['account_number']))
                                                                    <i class="fa fa-times"></i>
                                                                    {{$reviewDetails['account_number']}}
                                                                    <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                    @else
                                                                    <i class="fa fa-check"></i>
                                                                    @endif
                                                                </span>
                                                            </div>
                                                        </div>
                                                        <div class="details-custcol-row-bootm">
                                                            <div class="comments-blck">
                                                                <input type="text" class="form-control AddFinancialinfoField" table="customer_ovd_details" name="account_number" id="account_number" value="{{$account_number}}" {{$readonly}} maxlength="20" oninput="this.value = this.value.replace(/[^0-9]/gi, '').replace(/(\..*)\./g, '$1');">
                                                                <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @if($initial_funding_type == 3)
                                                    <div class="details-custcol-row col-md-6" id="etb_others_div">
                                                        @else
                                                        <div class="details-custcol-row col-md-6 display-none"id="etb_others_div">
                                                            @endif
                                                            <div class="details-custcol-row-top d-flex editColumnDiv">
                                                                <div class="detaisl-left d-flex align-content-center ">
                                                                    <p class="lable-cus">Account Number</p>
                                                                    <span class="{{$enable}}">
                                                                        @if(isset($reviewDetails['account_number']))
                                                                        <i class="fa fa-times"></i>
                                                                        {{$reviewDetails['account_number']}}
                                                                        <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                        @else
                                                                        <i class="fa fa-check"></i>
                                                                        @endif
                                                                    </span>
                                                                </div>
                                                            </div>
                                                            <div class="details-custcol-row-bootm">
                                                                <div class="comments-blck">
                                                                    @if(Session::get('customer_type') == "ETB")

                                                                    {!! Form::select('Account Nmuber',$accountNumbers,$account_number,array('class'=>'form-control account_number AddFinancialinfoField','table'=>'customer_ovd_details','id'=>'direct_account_number','name'=>'account_number','placeholder'=>'Select Account Number')) !!}

                                                                    @elseif((isset($cc_etb_details)) && ($cc_etb_details['etb_cc'] == 'CC'))
                                                                    {!! Form::select('Account Nmuber',$accountNumbers,$account_number,array('class'=>'form-control account_number AddFinancialinfoField',
                                                                    'table'=>'customer_ovd_details','id'=>'direct_account_number','name'=>'account_number','placeholder'=>'Select Account Number')) !!}
                                                                    @endif
                                                                    <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="details-custcol-row col-md-6 {{$funding_source_class}} {{$direct_class}}">
                                                            <div class="details-custcol-row-top d-flex editColumnDiv">
                                                                <div class="detaisl-left d-flex align-content-center ">
                                                                    <p class="lable-cus">Account Name</p>
                                                                    <span class="{{$enable}}">
                                                                        @if(isset($reviewDetails['account_name']))
                                                                        <i class="fa fa-times"></i>
                                                                        {{$reviewDetails['account_name']}}
                                                                        <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                        @else
                                                                        <i class="fa fa-check"></i>
                                                                        @endif
                                                                    </span>
                                                                </div>
                                                            </div>
                                                            <div class="details-custcol-row-bootm">
                                                                <div class="comments-blck">
                                                                    <input type="text" class="form-control AddFinancialinfoField input-capitalize" table="customer_ovd_details" name="account_name" id="account_name" value="{{$account_name}}" onkeyup="this.value = this.value.toUpperCase();" {{$readonly}} oninput="this.value = this.value.replace(/[^a-z ]/gi, '').replace(/(\..*)\./g, '$1');" maxlength="20">
                                                                    <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="details-custcol-row col-md-4" id="amount_div">
                                                            <div class="details-custcol-row-top d-flex editColumnDiv">
                                                                <div class="detaisl-left d-flex align-content-center">
                                                                    <p class="lable-cus">Amount</p>
                                                                    <span class="{{$enable}}">
                                                                        @if(isset($reviewDetails['amount']))
                                                                        <i class="fa fa-times"></i>
                                                                        {{$reviewDetails['amount']}}
                                                                        <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                        @else
                                                                        <i class="fa fa-check"></i>
                                                                        @endif
                                                                    </span>
                                                                </div>
                                                            </div>
                                                            <div class="details-custcol-row-bootm">
                                                                <div class="comments-blck">
                                                                    @if($others_type == "zero")
                                                                    <input type="text" class="form-control AddFinancialinfoField" table="customer_ovd_details" min="1" name="amount" id="amount" value="" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');" {{$readonly}} disabled="">
                                                                    @elseif(Session::get('customer_type') == "ETB")
                                                                    <input type="text" class="form-control AddFinancialinfoField direct_amount" table="customer_ovd_details" min="1" name="amount" id="amount" value="{{$amount}}" {{$readonly}} oninput="this.value = this.value.replace(/[^0-9]/g, '').replace(/(\..*)\./g, '$1');">
                                                                    @else
                                                                    <input type="text" class="form-control AddFinancialinfoField" table="customer_ovd_details" min="1" name="amount" id="amount" value="{{$amount}}" {{$readonly}} oninput="this.value = this.value.replace(/[^0-9]/g, '').replace(/(\..*)\./g, '$1');">
                                                                    @endif
                                                                    <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        @if($initial_funding_type != 5)
                                                        <div class="details-custcol-row col-md-3 {{$direct_class}}" id="selfthirdparty">
                                                            @else
                                                            <div class="details-custcol-row col-md-3 display-none" id="selfthirdparty">
                                                                @endif
                                                                <div class="details-custcol-row-top d-flex editColumnDiv">
                                                                    <div class="detaisl-left d-flex align-content-center">
                                                                        <p class="lable-cus">Type: </p>
                                                                        <span class="{{$enable}}">
                                                                            @if(isset($reviewDetails['self_thirdparty']))
                                                                            <i class="fa fa-times"></i>
                                                                            {{$reviewDetails['self_thirdparty']}}
                                                                            <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                            @else
                                                                            <i class="fa fa-check"></i>
                                                                            @endif
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                                <div class="details-custcol-row-bootm">
                                                                    <div class="comments-blck">
                                                                        <label class="radio">
                                                                            <input class="AddFinancialinfoField self_thirdparty" type="radio" name="self_thirdparty" id="self_thirdparty" value="self" {{ ($self_thirdparty=="self")? "checked" : "" }} {{$readonly}} {{$disabled}}>
                                                                            <span class="lbl padding-8">
                                                                                {{ $is_huf_display ? 'HUF' : 'Self' }}
                                                                            </span>
                                                                        </label>
                                                                       
                                                                        <label class="radio">
                                                                            <input classs="AddFinancialinfoField self_thirdparty" type="radio" name="self_thirdparty" id="self_thirdparty" value="thirdparty" {{ ($self_thirdparty=="thirdparty")? "checked" : "" }} {{$readonly}} {{$thirdpartyDisable}} {{$disabled}}>
                                                                            <span class="lbl padding-8">3<sup>rd</sup> Party</span>
                                                                        </label>
                                                                     
                                                                        <!-- <i title="save" class="fa fa-floppy-o display-none updateColumn"></i> -->
                                                                        <i title="save" class="updateColumn"></i>
                                                                        
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            @if($initial_funding_type != 5)
                                                            <div class="details-custcol-row col-md-5 {{$direct_class}}" id="relationship_div">
                                                                @else
                                                                <div class="details-custcol-row col-md-5 display-none" id="relationship_div">
                                                                    @endif
                                                                  
                                                                    <div class="details-custcol-row-top d-flex editColumnDiv">
                                                                        <div class="detaisl-left d-flex align-content-center ">
                                                                            <p class="lable-cus">Relationship</p>
                                                                            <span class="{{$enable}}">
                                                                                @if(isset($reviewDetails['relationship']))
                                                                                <i class="fa fa-times"></i>
                                                                                {{$reviewDetails['relationship']}}
                                                                                <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                                @else
                                                                                <i class="fa fa-check"></i>
                                                                                @endif
                                                                            </span>
                                                                        </div>
                                                                    </div>
                                                                    <div class="details-custcol-row-bootm">
                                                                        <div class="comments-blck">
                                                                            {!! Form::select('Relationship',$relationships,$relationship,array('class'=>'form-control relationship AddFinancialinfoField',
                                                                            'table'=>'customer_ovd_details','id'=>'relationship','name'=>'relationship','placeholder'=>'Select Relationship')) !!}
                                                                            <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                                                                        </div>
                                                                    </div>
                                                                    
                                                                </div>
                                                                <!---------------Row End Here ---------------------------------->
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>