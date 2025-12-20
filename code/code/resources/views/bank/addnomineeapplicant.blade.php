@php
    $nominee_exists = '';
    $nominee_name = '';
    $nominee_address = '';
    $relatinship_applicant = '';
    $relatinship_applicant_guardian = '';
    $nominee_dob = '';
    $nominee_age = '';
    $guardian_name = '';
    $guardian_address = '';
    $name_as_per_passbook = '';
    $display = "";
    $enable = "display-none";
    $is_review = 0;
    $readonly = '';
    $folder = '';
    $disabled = "";
    $dateDisabled = '';
    $account_id = '';
    $nominee_id = '';
    $witness1_signature_image = '';
    $lti_declaration_image = '';
    $address_type = '';
    $nominee_address_line1 = '';
    $nominee_address_line2 = '';
    $nominee_country = '';
    $nominee_state = '';
    $nominee_city = '';
    $nominee_pincode = '';
    $guardian_address_line1 = '';
    $guardian_address_line2 = '';
    $guardian_country = '';
    $guardian_state = '';
    $guardian_city = '';
    $guardian_pincode = '';
    $showWitnessDeclaration = false;
    @endphp
    @if(count($nomineeDetails) > 0)

    @php
        $nominee_exists = $nomineeDetails[$i]['nominee_exists'];
        if($nominee_exists == "yes")
        {
            $nominee_name = $nomineeDetails[$i]['nominee_name'];
            $relatinship_applicant = $nomineeDetails[$i]['relatinship_applicant'];
            $relatinship_applicant_guardian = $nomineeDetails[$i]['relatinship_applicant_guardian'];
            $nominee_dob = Carbon\Carbon::parse($nomineeDetails[$i]['nominee_dob'])->format('d-m-Y');
            $nominee_age = $nomineeDetails[$i]['nominee_age'];
            $nominee_address_line1 = $nomineeDetails[$i]['nominee_address_line1'];
            $nominee_address_line2 = $nomineeDetails[$i]['nominee_address_line2'];
            $nominee_country = $nomineeDetails[$i]['nominee_country'];
            $nominee_state = $nomineeDetails[$i]['nominee_state'];
            $nominee_city = $nomineeDetails[$i]['nominee_city'];
            $nominee_pincode = $nomineeDetails[$i]['nominee_pincode'];
            $name_as_per_passbook = $nomineeDetails[$i]['name_as_per_passbook'];
            if($nominee_age < 18)
            {
                $guardian_name = $nomineeDetails[$i]['guardian_name'];
                $guardian_address_line1 = $nomineeDetails[$i]['guardian_address_line1'];
                $guardian_address_line2 = $nomineeDetails[$i]['guardian_address_line2'];
                $guardian_country = $nomineeDetails[$i]['guardian_country'];
                $guardian_state = $nomineeDetails[$i]['guardian_state'];
                $guardian_city = $nomineeDetails[$i]['guardian_city'];
                $guardian_pincode = $nomineeDetails[$i]['guardian_pincode'];
                $witness1_signature_image = $nomineeDetails[$i]['witness1_signature_image'];
            }
        }
        if(isset($nomineeDetails[$i]['lti_declaration_image']))
        {
            $lti_declaration_image = $nomineeDetails[$i]['lti_declaration_image'];
        }
        $display = "display-none";
        $folder = "attachments";
    @endphp
@endif
@if(Session::get('is_review') == 1)
    @php
        $nominee_id = $nomineeDetails[$i]['id'];
        $is_review = 1;
        $enable = "";
        $readonly = "readonly";
        $folder = "markedattachments";
        $disabled = "disabled";
        $dateDisabled = "disabled";
    @endphp
@endif
@if((Session::get('in_progress') == 1) && (Session::get('max_screen') > 5))
    @php
        $nominee_id = $nomineeDetails[$i]['id'];
    @endphp
@elseif(isset(Session::get('nomineeIds')[$i-1]))
    @php
        $nominee_id = Session::get('nomineeIds')[$i-1];
    @endphp
@endif
@php
$image_mask_blur = "";
$def_blur_image = "";
if($is_review==1){
    $image_mask_blur = "uploaded-img-ovd";
    $def_blur_image = "style=filter:blur(30px);";
}

$is_huf_display = false;
$huf_disabled = "";
if($accountDetails['constitution'] == 'NON_IND_HUF'){
    $is_huf_display = true;
    $huf_disabled = "disabled";

 }
@endphp
<div class="nomineeDetailsForm" id="{{$i}}">
    <input type="hidden" id="applicantId-{{$i}}" value="{{$nominee_id}}">
    <div class="card">
        <input type="hidden" id="formId" name="formId" value="{{$formId}}">
        <div class="card-block">
            <div class="row">
                <div class="col-md-12">
                    @if($i === 1)
                        <h4 class="sub-title">Nomination Details</h4>
                    @else
                        <h4 class="sub-title">Nomination Details For Term Deposit</h4>
                    @endif
                </div>
                <div class="col-md-12">
                    <div class="radio-selectioninn-blck">
                        <label class="radio">
                            <input type="checkbox" name="nominee_exists" value='yes' id="nominee_exists-{{$i}}" class="NomineeDetailsField nominee_exists" onclick="show_nominee_form('{{$i}}')" {{($nominee_exists == "yes")? "checked" : "" }} {{$readonly}} {{$disabled}} {{$huf_disabled}}>
                            @if(!$is_huf_display)
                            @if($i === 1)
                                <span class="lbl padding-8">Yes, I want to nominate the following person</span>
                            @else
                                <span class="lbl padding-8">Yes, I want to nominate the following person for Term Deposit</span>
                            @endif
                            @endif
                        </label>
                        
                        @if($is_huf_display)
                            <span class="lbl padding-8" style='color:#ae2217'>Nomination not applicable for HUF Account.</span>
                        @endif
                    </div>
                </div>
            </div>
            @if($i == 2)
                <div class="col-md-6 display-none nominee_form_td">
                    <div class="radio-selectioninn-blck">
                        <label class="radio">
                            <input type="checkbox" id="same_nominee" {{$readonly}} {{$disabled}}>
                            <span class="lbl padding-8">Same as Above Nomination Details</span>
                        </label>
                    </div>
                </div>
            @endif   

            @if((count($userDetails) > 0) && ($nominee_exists == "yes"))
                <div class="row nominee_form nominee_form-{{$i}}">
            @else
                <div class="row display-none nominee_form nominee_form-{{$i}}">
            @endif  
                      
                <div class="details-custcol-row col-md-4">
                    <div class="details-custcol-row-top d-flex editColumnDiv">
                        <div class="detaisl-left d-flex align-content-center ">
                            Nominee Name 
                            <span class="{{$enable}}">
                                @if(isset($reviewDetails['nominee_name-'.$i]))
                                    <i class="fa fa-times"></i>
                                    {{$reviewDetails['nominee_name-'.$i]}}
                                    <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                @else
                                    <i class="fa fa-check"></i>
                                @endif
                            </span>
                        </div>
                    </div>
                    <div class="details-custcol-row-bootm">
                        <div class="comments-blck">
                            <input type="text" class="form-control NomineeDetailsField input-capitalize" table="nominee_details" name="nominee_name" id="nominee_name-{{$i}}" value="{{$nominee_name}}" onkeyup="this.value = this.value.toUpperCase();" {{$readonly}} oninput="this.value = this.value.replace(/[^a-z ]/gi, '').replace(/(\..*)\./g, '$1');" maxlength="40">
                            <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                        </div>
                    </div>
                </div>

                <div class="details-custcol-row col-md-4">
                    <div class="details-custcol-row-top d-flex editColumnDiv">
                        <div class="detaisl-left d-flex align-content-center ">
                            Relationship with Primary Applicant, if any 
                            <span class="{{$enable}}">
                                @if(isset($reviewDetails['relationship-'.$i]))
                                    <i class="fa fa-times"></i>
                                    {{$reviewDetails['relationship-'.$i]}}
                                    <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                @else
                                    <i class="fa fa-check"></i>
                                @endif
                            </span>
                        </div>
                    </div>
                    <div class="details-custcol-row-bootm">
                        <div class="comments-blck">
                            {!! Form::select('relatinship_applicant',$relations,$relatinship_applicant,array('class'=>'form-control relatinship_applicant NomineeDetailsField',
                                            'id'=>'relatinship_applicant-'.$i,'table'=>'nominee_details','name'=>'relatinship_applicant','placeholder'=>'')) !!}
                            <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                        </div>
                    </div>
                </div>

                <div class="details-custcol-row col-md-4 nomine-addressText">
                    <div class="details-custcol-row-top editColumnDiv nomine-addinput">
                        <div class="detaisl-left align-content-center nominee-addmain">
                            Address 
                            <span class="{{$enable}}">
                                @if(isset($reviewDetails['nominee_address-'.$i]))
                                    <i class="fa fa-times"></i>
                                    {{$reviewDetails['nominee_address-'.$i]}}
                                    <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                @else
                                    <i class="fa fa-check"></i>
                                @endif
                            </span>
                            <div class="radio-selection mb-2 ml-2">
                                @if($globalCCData == '')
                                <label class="radio">
                                    <input class="AddPanDetailsField address_type" type="radio" id="address_type-{{$i}}" name="address_type" value="permanent" {{ ($address_type=="permanent")? "checked" : "" }} {{$disabled}}>
                                    <span class="lbl padding-8">Address (Same as applicant)</span>
                                </label>
                                <label class="radio">
                                    <input class="AddPanDetailsField address_type" type="radio" id="address_type-{{$i}}" name="address_type" value="communication" {{ ($address_type=="communication")? "checked" : "" }} {{$disabled}}>
                                    <span class="lbl padding-8">Communication</span>
                                </label>
                                @else
                                    @if($globalCCData['etb_cc'] == 'CC')
                                        <label class="radio">
                                            <input class="AddPanDetailsField address_type" type="radio" id="address_type-{{$i}}" name="address_type" value="permanent" {{ ($address_type=="permanent")? "checked" : "" }} {{$disabled}}>
                                            <span class="lbl padding-8">Address (Same as applicant)</span>
                                        </label>
                                        <label class="radio">
                                            <input class="AddPanDetailsField address_type" type="radio" id="address_type-{{$i}}" name="address_type" value="communication" {{ ($address_type=="communication")? "checked" : "" }} {{$disabled}}>
                                            <span class="lbl padding-8">Communication</span>
                                        </label>
                                        <label class="radio">
                                            <input class="nominee_Details" type="radio" id="nominee_Details-{{$i}}" name="nominee_Details" value="savingnomineeDetails">
                                            <span class="lbl padding-8">Same as Saving</span>
                                        </label>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="details-custcol-row col-md-4">
                    <div class="details-custcol-row-top d-flex editColumnDiv">
                        <div class="detaisl-left d-flex align-content-center ">
                            <p class="lable-cus">Address Line1</p>
                            <span class="{{$enable}}">
                                @if(isset($reviewDetails['nominee_address_line1-'.$i]))
                                    <i class="fa fa-times"></i>
                                    {{$reviewDetails['nominee_address_line1-'.$i]}}
                                    <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                @else
                                    <i class="fa fa-check"></i>
                                @endif
                            </span>
                        </div>
                    </div>
                    <div class="details-custcol-row-bootm">
                        <div class="comments-blck">
                            <input type="text" class="form-control input-capitalize NomineeDetailsField" table="nominee_details" id="nominee_address_line1-{{$i}}" name="nominee_address_line1" value="{{$nominee_address_line1}}" onkeyup="this.value = this.value.toUpperCase();" oninput="this.value = addressFieldValidation(this);" {{$readonly}} maxlength="45">
                            <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                        </div>
                    </div>
                </div>

                <div class="details-custcol-row col-md-4">
                    <div class="details-custcol-row-top d-flex editColumnDiv">
                        <div class="detaisl-left d-flex align-content-center ">
                            <p class="lable-cus">Address Line2</p>
                            <span class="{{$enable}}">
                                @if(isset($reviewDetails['nominee_address_line2-'.$i]))
                                    <i class="fa fa-times"></i>
                                    {{$reviewDetails['nominee_address_line2-'.$i]}}
                                    <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                @else
                                    <i class="fa fa-check"></i>
                                @endif
                            </span>
                        </div>
                    </div>
                    <div class="details-custcol-row-bootm">
                        <div class="comments-blck">
                            <input type="text" class="form-control input-capitalize NomineeDetailsField" table="nominee_details" id="nominee_address_line2-{{$i}}" name="nominee_address_line2" value="{{$nominee_address_line2}}" onkeyup="this.value = this.value.toUpperCase();" oninput="this.value = addressFieldValidation(this);" {{$readonly}}  maxlength="45">
                            <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                        </div>
                    </div>
                </div>               

                <div class="details-custcol-row col-md-4">
                    <div class="details-custcol-row-top d-flex editColumnDiv">
                        <div class="detaisl-left d-flex align-content-center ">
                            <p class="lable-cus">Pincode </p>
                            <span class="{{$enable}}">
                                @if(isset($reviewDetails['nominee_pincode-'.$i]))
                                    <i class="fa fa-times"></i>
                                    {{$reviewDetails['nominee_pincode-'.$i]}}
                                    <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                @else
                                    <i class="fa fa-check"></i>
                                @endif
                            </span>
                        </div>
                    </div>
                    <div class="details-custcol-row-bootm">
                        <div class="comments-blck">
                            <input type="text" class="form-control NomineeDetailsField nominee_pincode" table="nominee_details" id="nominee_pincode-{{$i}}" name="nominee_pincode" value="{{$nominee_pincode}}" oninput="this.value = this.value.replace(/[^0-9]/g, '').replace(/(\..*)\./g, '$1');" minlength="6" maxlength="6"  {{$readonly}}>
                            <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                        </div>
                    </div>
                </div>  

                <div class="details-custcol-row col-md-4">
                    <div class="details-custcol-row-top d-flex editColumnDiv">
                        <div class="detaisl-left d-flex align-content-center ">
                            <p class="lable-cus">City</p>
                            <span class="{{$enable}}">
                                @if(isset($reviewDetails['nominee_city-'.$i]))
                                    <i class="fa fa-times"></i>
                                    {{$reviewDetails['nominee_city-'.$i]}}
                                    <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                @else
                                    <i class="fa fa-check"></i>
                                @endif
                            </span>
                        </div>
                    </div>
                    <div class="details-custcol-row-bootm">
                        <div class="comments-blck">
                            <input type="text" class="form-control NomineeDetailsField city" table="nominee_details" id="nominee_city-{{$i}}" name="nominee_city" value="{{$nominee_city}}" oninput="this.value = this.value.replace(/[^a-z0-9]/gi, '').replace(/(\..*)\./g, '$1');" {{$readonly}}  style="text-transform: uppercase;" readonly>
                            <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                        </div>
                    </div>
                </div> 

                <div class="details-custcol-row col-md-4">
                    <div class="details-custcol-row-top d-flex editColumnDiv">
                        <div class="detaisl-left d-flex align-content-center ">
                            <p class="lable-cus">State</p>
                            <span class="{{$enable}}">
                                @if(isset($reviewDetails['nominee_state-'.$i]))
                                    <i class="fa fa-times"></i>
                                    {{$reviewDetails['nominee_state-'.$i]}}
                                    <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                @else
                                    <i class="fa fa-check"></i>
                                @endif
                            </span>
                        </div>
                    </div>
                    <div class="details-custcol-row-bootm">
                        <div class="comments-blck">
                            <input type="text" class="form-control NomineeDetailsField state" table="nominee_details" id="nominee_state-{{$i}}" name="nominee_state" value="{{$nominee_state}}" oninput="this.value = this.value.replace(/[^a-z]/gi, '').replace(/(\..*)\./g, '$1');" {{$readonly}}  style="text-transform: uppercase;" readonly>
                            <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                        </div>
                    </div>
                </div>

                <div class="details-custcol-row col-md-4">
                    <div class="details-custcol-row-top d-flex editColumnDiv">
                        <div class="detaisl-left d-flex align-content-center">
                            <p class="lable-cus">Country</p>
                            <span class="{{$enable}}">
                                @if(isset($reviewDetails['nominee_country-'.$i]))
                                    <i class="fa fa-times"></i>
                                    {{$reviewDetails['nominee_country-'.$i]}}
                                    <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                @else
                                    <i class="fa fa-check"></i>
                                @endif
                            </span>
                        </div>
                    </div>
                    <div class="details-custcol-row-bootm">
                        <div class="comments-blck">
                            {!! Form::select('country',$countries,$nominee_country,array('class'=>'form-control country nominee_country NomineeDetailsField',
                                'table'=>'nominee_details','id'=>'nominee_country-'.$i,'name'=>'nominee_country')) !!}
                            <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                        </div>
                    </div>
                </div> 

                <div class="details-custcol-row col-md-4">
                    <div class="details-custcol-row-top d-flex editColumnDiv">
                        <div class="detaisl-left d-flex align-content-center ">
                            Date of Birth 
                            <span class="{{$enable}}">
                                @if(isset($reviewDetails['nominee_dob-'.$i]))
                                    <i class="fa fa-times"></i>
                                    {{$reviewDetails['nominee_dob-'.$i]}}
                                    <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                @else
                                    <i class="fa fa-check"></i>
                                @endif
                            </span>
                        </div>
                    </div>
                    <div class="details-custcol-row-bootm">
                        <div class="comments-blck">
                            <input type="text" class="form-control NomineeDetailsField nominee_dob" table="nominee_details" onfocusout="simulatedatechange(this)" name="nominee_dob" id="nominee_dob-{{$i}}" value="{{$nominee_dob}}" {{$readonly}} {{$dateDisabled}}>
                            <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                        </div>
                    </div>
                </div>

                <div class="details-custcol-row col-md-2">
                    <div class="details-custcol-row-top d-flex editColumnDiv">
                        <div class="detaisl-left d-flex align-content-center ">
                            Age 
                            <span class="{{$enable}}">
                                @if(isset($reviewDetails['nominee_age-'.$i]))
                                    <i class="fa fa-times"></i>
                                    {{$reviewDetails['nominee_age-'.$i]}}
                                    <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                @else
                                    <i class="fa fa-check"></i>
                                @endif
                            </span>
                        </div>
                    </div>
                    <div class="details-custcol-row-bootm">
                        <div class="comments-blck">
                            <?php //echo $nominee_age;exit; ?>
                            <input type="text" class="form-control NomineeDetailsField" table="nominee_details" name="nominee_age" id="nominee_age-{{$i}}" value="{{$nominee_age}}" readonly>
                            <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                        </div>
                    </div>
                </div>
            </div>

            @if((count($userDetails) > 0) && ($nominee_exists == "yes") && ($nominee_age < 18))
                @php
                    $class = "";
                @endphp
            @else
                @php
                    $class = "display-none";
                @endphp
            @endif
            <div class="col-md-12 {{$class}} minor_guardian-{{$i}}">
                <div class="sub-title mt-3">Guardian / Appointee Details</div>
                <div class="radio-selection mb-2 ml-2">
                                <label class="radio">
                                    <input class="same_as_nominee_address GuardianDetailsField" type="radio" id="same_as_nominee_address-{{$i}}" name="same_as_nominee_address"  "checked" : "" }} {{$disabled}}>
                                    <span class="lbl padding-8">Address (Same as nominee)</span>
                                </label>
                            </div>
                 <!-- 22May23 - For BS5 - commented below line -->
                    <!-- <div class="d-flex"> -->
                        <div class="d-flex me-md-5 gap-4">
                        <div class="details-custcol-row col-md-4 minor_guardian">
                            <div class="details-custcol-row-top d-flex editColumnDiv">
                                <div class="detaisl-left d-flex align-content-center ">
                                    Guardian Name : 
                                    <span class="{{$enable}}">
                                        @if(isset($reviewDetails['guardian_name-'.$i]))
                                            <i class="fa fa-times"></i>
                                            {{$reviewDetails['guardian_name-'.$i]}}
                                            <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                        @else
                                            <i class="fa fa-check"></i>
                                        @endif
                                    </span>
                                </div>
                            </div>
                            <div class="details-custcol-row-bootm">
                                <div class="comments-blck">
                                    <input type="text" class="form-control NomineeDetailsField input-capitalize guardian_name GuardianDetailsField" table="nominee_details" name="guardian_name" id="guardian_name-{{$i}}" value="{{$guardian_name}}" onkeyup="this.value = this.value.toUpperCase();" {{$readonly}} oninput="this.value = this.value.replace(/[^a-z ]/gi, '').replace(/(\..*)\./g, '$1');" maxlength="40">
                                    <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                                </div>
                            </div>
                        </div>


                           <div class="details-custcol-row col-md-4">
                            <div class="details-custcol-row-top d-flex editColumnDiv">
                                <div class="detaisl-left d-flex align-content-center ">
                                    <p class="lable-cus">Relationship with Nominee</p>
                                    <span class="{{$enable}}">
                                        @if(isset($reviewDetails['relatinship_applicant_guardian-'.$i]))
                                            <i class="fa fa-times"></i>
                                            {{$reviewDetails['relatinship_applicant_guardian-'.$i]}}
                                            <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                        @else
                                            <i class="fa fa-check"></i>
                                        @endif
                                    </span>
                                </div>
                            </div>
                            <div class="details-custcol-row-bootm">
                                <div class="comments-blck">
                                   {!! Form::select('relatinship_applicant_guardian',$relations,$relatinship_applicant_guardian,array('class'=>'form-control relatinship_applicant_guardian NomineeDetailsField',
                                            'id'=>'relatinship_applicant_guardian-'.$i,'table'=>'nominee_details','name'=>'relatinship_applicant_guardian','placeholder'=>'')) !!}
                                    <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                                </div>
                            </div>
                        </div>

                        <div class="details-custcol-row col-md-4">
                            <div class="details-custcol-row-top d-flex editColumnDiv">
                                <div class="detaisl-left d-flex align-content-center ">
                                    <p class="lable-cus">Address Line1</p>
                                    <span class="{{$enable}}">
                                        @if(isset($reviewDetails['guardian_address_line1-'.$i]))
                                            <i class="fa fa-times"></i>
                                            {{$reviewDetails['guardian_address_line1-'.$i]}}
                                            <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                        @else
                                            <i class="fa fa-check"></i>
                                        @endif
                                    </span>
                                </div>
                            </div>
                            <div class="details-custcol-row-bootm"> 
                                <div class="comments-blck">
                                    <input type="text" class="form-control input-capitalize NomineeDetailsField GuardianDetailsField" table="nominee_details" id="guardian_address_line1-{{$i}}" name="guardian_address_line1" value="{{$guardian_address_line1}}" onkeyup="this.value = this.value.toUpperCase();" oninput="this.value = addressFieldValidation(this);" {{$readonly}}  maxlength="45">
                                    <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                                </div>
                            </div>
                        </div>
                    
                     
                    </div>

            <!-- 22May23 - For BS5 - commented below line -->
                    <!-- <div class="d-flex"> -->
                <div class="d-flex me-md-5 gap-4">

                          <div class="details-custcol-row col-md-4">
                            <div class="details-custcol-row-top d-flex editColumnDiv">
                                <div class="detaisl-left d-flex align-content-center ">
                                    <p class="lable-cus">Address Line2</p>
                                    <span class="{{$enable}}">
                                        @if(isset($reviewDetails['guardian_address_line2-'.$i]))
                                            <i class="fa fa-times"></i>
                                            {{$reviewDetails['guardian_address_line2-'.$i]}}
                                            <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                        @else
                                            <i class="fa fa-check"></i>
                                        @endif
                                    </span>
                                </div>
                            </div>
                            <div class="details-custcol-row-bootm">
                                <div class="comments-blck">
                                    <input type="text" class="form-control input-capitalize NomineeDetailsField GuardianDetailsField" table="nominee_details" id="guardian_address_line2-{{$i}}" name="guardian_address_line2" value="{{$guardian_address_line2}}" onkeyup="this.value = this.value.toUpperCase();" oninput="this.value = addressFieldValidation(this);" {{$readonly}}  maxlength="45">
                                    <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                                </div>
                            </div>
                        </div>

                        <div class="details-custcol-row col-md-4">
                            <div class="details-custcol-row-top d-flex editColumnDiv">
                                <div class="detaisl-left d-flex align-content-center ">
                                    <p class="lable-cus">Pincode </p>
                                    <span class="{{$enable}}">
                                        @if(isset($reviewDetails['guardian_pincode-'.$i]))
                                            <i class="fa fa-times"></i>
                                            {{$reviewDetails['guardian_pincode-'.$i]}}
                                            <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                        @else
                                            <i class="fa fa-check"></i>
                                        @endif
                                    </span>
                                </div>
                            </div>
                            <div class="details-custcol-row-bootm">
                                <div class="comments-blck">
                                    <input type="text" class="form-control NomineeDetailsField guardian_pincode GuardianDetailsField" table="nominee_details" id="guardian_pincode-{{$i}}" name="guardian_pincode" value="{{$guardian_pincode}}" minlength="6" maxlength="6" oninput="this.value = this.value.replace(/[^0-9]/g, '').replace(/(\..*)\./g, '$1');" {{$readonly}}>
                                    <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                                </div>
                            </div>
                        </div>

                        <div class="details-custcol-row col-md-4">
                            <div class="details-custcol-row-top d-flex editColumnDiv">
                                <div class="detaisl-left d-flex align-content-center ">
                                    <p class="lable-cus">City</p>
                                    <span class="{{$enable}}">
                                        @if(isset($reviewDetails['guardian_city-'.$i]))
                                            <i class="fa fa-times"></i>
                                            {{$reviewDetails['guardian_city-'.$i]}}
                                            <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                        @else
                                            <i class="fa fa-check"></i>
                                        @endif
                                    </span>
                                </div>
                            </div>
                            <div class="details-custcol-row-bootm">
                                <div class="comments-blck">
                                    <input type="text" class="form-control NomineeDetailsField city GuardianDetailsField" table="nominee_details " id="guardian_city-{{$i}}" name="guardian_city" value="{{$guardian_city}}" oninput="this.value = this.value.replace(/[^a-z0-9]/gi, '').replace(/(\..*)\./g, '$1');"  {{$readonly}}  style="text-transform: uppercase;" readonly>
                                    <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                                </div>
                            </div>
                        </div>
                   
                        
                    </div>

             <!-- 22May23 - For BS5 - commented below line -->
                    <!-- <div class="d-flex"> -->
                  <div class="d-flex me-md-5 gap-4">

                        <div class="details-custcol-row col-md-4">
                            <div class="details-custcol-row-top d-flex editColumnDiv">
                                <div class="detaisl-left d-flex align-content-center ">
                                    <p class="lable-cus">State</p>
                                    <span class="{{$enable}}">
                                        @if(isset($reviewDetails['guardian_state-'.$i]))
                                            <i class="fa fa-times"></i>
                                            {{$reviewDetails['guardian_state-'.$i]}}
                                            <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                        @else
                                            <i class="fa fa-check"></i>
                                        @endif
                                    </span>
                                </div>
                            </div>
                            <div class="details-custcol-row-bootm">
                                <div class="comments-blck">
                                    <input type="text" class="form-control NomineeDetailsField state GuardianDetailsField" table="nominee_details " id="guardian_state-{{$i}}" name="guardian_state" value="{{$guardian_state}}" {{$readonly}}  style="text-transform: uppercase;" oninput="this.value = this.value.replace(/[^a-z]/gi, '').replace(/(\..*)\./g, '$1');" readonly>
                                    <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                                </div>
                            </div>
                        </div>   


                        <div class="details-custcol-row col-md-4">
                            <div class="details-custcol-row-top d-flex editColumnDiv">
                                <div class="detaisl-left d-flex align-content-center">
                                    <p class="lable-cus">Country</p>
                                    <span class="{{$enable}}">
                                        @if(isset($reviewDetails['guardian_country-'.$i]))
                                            <i class="fa fa-times"></i>
                                            {{$reviewDetails['guardian_country-'.$i]}}
                                            <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                        @else
                                            <i class="fa fa-check"></i>
                                        @endif
                                    </span>
                                </div>
                            </div>
                            <div class="details-custcol-row-bootm">
                                <div class="comments-blck">
                                    {!! Form::select('country',$countries,$guardian_country,array('class'=>'form-control country guardian_country NomineeDetailsField',
                                        'table'=>'nominee_details','id'=>'guardian_country-'.$i,'name'=>'guardian_country')) !!}
                                    <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                @if((count($userDetails) > 0) && ($nominee_exists == "yes"))
                    <div class="row nominee_form nominee_form-{{$i}}">
                @else
                    <div class="row nominee_form display-none nominee_form-{{$i}}">
                @endif
                    <div class="col-md-12">
                        <!-- <p class="text-muted m-0">In case you have specified a nominee above, please indicate if you wish to make mention of the nominee name on the passbook, statement &amp; DCA issued in respect of your account and / or the passbook issued to you</p> -->
                        <h9>Nominee name to be printed on passbook, statement and DCA ?</h9>
                        <div class="radio-selection mt-2">
                            <div class="radio-selectioninn-blck">
                                <label class="radio">
                                    <input type="radio" name="name_as_per_passbook_{{$i}}" value="yes" class="NomineeDetailsField GuardianDetailsField" id="name_as_per_passbook-{{$i}}" {{ ($name_as_per_passbook=="yes")? "checked" : "" }} {{$readonly}} {{$disabled}}>
                                    <span class="lbl padding-8">Yes</span>
                                </label>
                                <label class="radio">
                                    <input type="radio" name="name_as_per_passbook_{{$i}}" value="no" class="NomineeDetailsField GuardianDetailsField" id="name_as_per_passbook-{{$i}}" {{ ($name_as_per_passbook=="no")? "checked" : "" }} {{$readonly}} {{$disabled}}>
                                    <span class="lbl padding-8">No</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
    
                @if((count($userDetails) > 0) && ($nominee_exists == "yes") && ($nominee_age < 18))
                    @php
                        $class = "";
                    @endphp
                @else
                    @php
                        $class = "display-none";
                    @endphp
                @endif
                @if($showWitnessDeclaration)
                <div class="row minor_guardian {{$class}} minor_guardian-{{$i}}">
                    <div class="col-md-4">
                        <div class="form-group" id="witness1_signature_image">
                           {{--  <label>Witness Declarations</label> --}}
                            <div class="detaisl-left align-content-center ">
                               <label class="uploadLabel">Witness Declarations</label>
                               <span class="{{$enable}}">
                               @if(isset($reviewDetails['witness1_signature']))
                               <i class="fa fa-times"></i>
                               {{$reviewDetails['witness1_signature']}}
                               <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                               @else
                               <i class="fa fa-check"></i>
                               @endif
                               </span>
                            </div>
                            <div class="add-document d-flex align-items-center justify-content-around" id="witness1_signature-{{$i}}">
                                @if(isset($witness1_signature_image) && ($witness1_signature_image != ''))
                                    <div id="pf_type_div">
                                        @if($enable == 'display-none')
                                            <div class="upload-delete">
                                                    <button type="button" class="delete-icon btn btn-danger btn-icon waves-effect waves-light deleteImage">
                                                        <i class="fa fa-trash" aria-hidden="true"></i>
                                                    </button>
                                            </div>                                                           
                                        @else
                                            @if(isset($reviewDetails['witness1_signature']))
                                                <div class="upload-delete">
                                                    <button type="button" class="delete-icon btn btn-danger btn-icon waves-effect waves-light deleteImage">
                                                        <i class="fa fa-trash" aria-hidden="true"></i>
                                                    </button>
                                                </div>
                                            @else
                                            @endif
                                        @endif
                                        <div class="{{$image_mask_blur}}" {{$def_blur_image}}>
                                        <img class="uploaded_image" name="witness1_signature_image-{{$i}}" id="document_preview_witness1_signaturee-{{$i}}" src="{{URL::to('/images'.$folder.'/'.$formId.'/'.$witness1_signature_image)}}">
                                    </div>
                                    </div>
                                @endif
                                @if(isset($witness1_signature_image) && ($witness1_signature_image != ''))
                                    <div class="add-document-btn adb-btn-inn display-none">
                                @else
                                    <div class="add-document-btn adb-btn-inn">
                                @endif
                                    <button type="button" class="btn btn-outline-grey waves-effect upload_document" data-toggle="modal" 
                                    data-id="witness1_signature-{{$i}}"  data-name="witness1_signature_image-{{$i}}"  data-document="Witness Signature1" data-target="#upload_proof">
                                        <span class="adb-icon">
                                            <i class="fa fa-plus-circle"></i>
                                        </span>
                                        Add Witness Signature1
                                    </button>
                                </div>                                                                     
                            </div>
                            <input type="text" style="opacity:0" name="witness_signature" id="witness_signature-{{$i}}">
                        </div>
                    </div>
                </div>
                @endif

                @if(($signature_type == "LTI") || ($lti_declaration_image != ''))
                    <div class="row">                    
                        <div class="col-md-4">
                            <div class="form-group" id="lti_declaration_image-{{$i}}">
                                {{-- <label>LTI Declarations</label> --}}
                                <div class="detaisl-left align-content-center ">
                                   <label class="uploadLabel">LTI/RTI Declarations</label>
                                   <span class="{{$enable}}">
                                   @if(isset($reviewDetails['lti_declaration-'.$i]))
                                   <i class="fa fa-times"></i>
                                   {{$reviewDetails['lti_declaration-'.$i]}}
                                   <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                   @else
                                   <i class="fa fa-check"></i>
                                   @endif
                                   </span>
                                </div>
                                <div class="add-document d-flex align-items-center justify-content-around" id="lti_declaration-{{$i}}">
                                    @if(isset($lti_declaration_image) && ($lti_declaration_image != ''))
                                        <div id="pf_type_div">
                                            @if($enable == 'display-none')
                                            <div class="upload-delete">
                                                <button type="button" class="delete-icon btn btn-danger btn-icon waves-effect waves-light deleteImage">
                                                <i class="fa fa-trash" aria-hidden="true"></i>
                                                </button>
                                            </div>                                                        
                                            @else
                                                @if(isset($reviewDetails['lti_declaration-'.$i]))
                                                    <div class="upload-delete">
                                                        <button type="button" class="delete-icon btn btn-danger btn-icon waves-effect waves-light deleteImage">
                                                            <i class="fa fa-trash" aria-hidden="true"></i>
                                                        </button>
                                                    </div>
                                                @else
                                                @endif
                                            @endif
                                            <div class="{{$image_mask_blur}}" {{$def_blur_image}}>
                                            <img class="uploaded_image" name="lti_declaration_image-{{$i}}" id="document_preview_lti_declaration"-{{$i}} src="{{URL::to('/images'.$folder.'/'.$formId.'/'.$lti_declaration_image)}}">
                                        </div>
                                        </div>
                                    @endif
                                    @if(isset($lti_declaration_image) && ($lti_declaration_image != ''))
                                        <div class="add-document-btn adb-btn-inn display-none">
                                    @else
                                        <div class="add-document-btn adb-btn-inn">
                                    @endif
                                        <button type="button" class="btn btn-outline-grey waves-effect upload_document" data-toggle="modal" 
                                        data-id="lti_declaration-{{$i}}"  data-name="lti_declaration_image-{{$i}}"  data-document="LTI Declarations" data-target="#upload_proof">
                                            <span class="adb-icon">
                                                <i class="fa fa-plus-circle"></i>
                                            </span>
                                            Add LTI/RTI Declarations
                                        </button>
                                    </div>                                        
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>