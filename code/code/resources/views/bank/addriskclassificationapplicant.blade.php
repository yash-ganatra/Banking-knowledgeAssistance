@php
    $basis_categorisation = array('8');
    $customer_type = '';
    $country_name = '';
    $country_of_birth = '';
    $place_of_birth = '';
    $citizenship = '';
    $education = '';
    $gross_income = '';
    $networth = '';
    $occupation = '';
    $pep = 'No';
    $other_occupation = '';
    $categorisation_others_comments = '';
    $source_others_comments = '';
    $residence = '';
    $us_person = '0';
    $tin = '';
    $risk_classification_rating = '';
    $lcr_category = 'Individual';
    $display = "";
    $enable = "display-none";
    $is_review = 0;
    $readonly = '';
    $folder = '';
    $account_id = '';
    $disabled = '';
    $etbreadonly = "";
    $etbdisabled = "";
    $customertype = "";
    $per_country  = "";
    if(isset($customerOvdDetails[$i]['per_country'])){
    $per_country = $customerOvdDetails[$i]['per_country'];
    }
@endphp
@if(count($userDetails) > 0)
    @php
    //  if($riskaccountDetails['is_new_customer'] == '1')
        //$customeretbntb = isset($checkEtbNtb[$i]->is_new_customer) && $checkEtbNtb[$i]->is_new_customer == '0'? $checkEtbNtb[$i]->is_new_customer:'1';
    
       if($checkEtbNtb[$i]->is_new_customer == 1)
        {
            if(isset($riskDetails[$i]['basis_categorisation']))
            {
                $basis_categorisation = $riskDetails[$i]['basis_categorisation'];
            }
            if($basis_categorisation == 6){
                $categorisation_others_comments = $riskDetails[$i]['categorisation_others_comments'];
            }  
            if(!is_array($basis_categorisation))
            {
                $basis_categorisation = explode(',',$basis_categorisation);
            }          
            $risk_classification_rating = isset($riskDetails[$i]['risk_classification_rating']) && $riskDetails[$i]['risk_classification_rating'] != ''?$riskDetails[$i]['risk_classification_rating']:'';
            $customer_type = isset($riskDetails[$i]['customer_type']) && $riskDetails[$i]['customer_type'] !=''?$riskDetails[$i]['customer_type']:'';
            $country_name = isset($riskDetails[$i]['country_name']) && $riskDetails[$i]['country_name'] !=''?$riskDetails[$i]['country_name']:'';
            $country_of_birth = isset($riskDetails[$i]['country_of_birth']) && $riskDetails[$i]['country_of_birth'] !=''?$riskDetails[$i]['country_of_birth']:'';
            $place_of_birth = isset($riskDetails[$i]['place_of_birth']) && $riskDetails[$i]['place_of_birth'] !=''?$riskDetails[$i]['place_of_birth']:'';
            $citizenship = isset($riskDetails[$i]['citizenship']) && $riskDetails[$i]['citizenship'] !=''?$riskDetails[$i]['citizenship']:'';  
            $education = isset($riskDetails[$i]['education']) && $riskDetails[$i]['education'] !=''?$riskDetails[$i]['education']:'';
            $gross_income = isset($riskDetails[$i]['gross_income']) && $riskDetails[$i]['gross_income'] !=''?$riskDetails[$i]['gross_income']:'';
            $networth = isset($riskDetails[$i]['networth']) && $riskDetails[$i]['networth'] !=''?$riskDetails[$i]['networth']:'';
            $residence = isset($riskDetails[$i]['residence']) && $riskDetails[$i]['residence'] !=''?$riskDetails[$i]['residence']:'';
            $us_person = isset($riskDetails[$i]['us_person']) && $riskDetails[$i]['us_person'] !=''?$riskDetails[$i]['us_person']:'0';
            $pep = isset($riskDetails[$i]['pep']) && $riskDetails[$i]['pep'] !=''?$riskDetails[$i]['pep']:'No';
            $tin = isset($riskDetails[$i]['tin']) && $riskDetails[$i]['tin'] !=''?$riskDetails[$i]['tin']:'';
            //$lcr_category = isset($riskDetails[$i]['lcr_category']) && $riskDetails[$i]['lcr_category'] !=''?$riskDetails[$i]['lcr_category']:'';
            $etbreadonly = "";
            $etbdisabled = "";
            $customertype = "";
        }else{
            $etbreadonly = "readonly";
            $etbdisabled = "disabled";
            $customertype = "etb";
        }
      
        $occupation = isset($riskDetails[$i]['occupation']) && $riskDetails[$i]['occupation'] !=''?$riskDetails[$i]['occupation']:'';
        if($occupation == 28 || $occupation == 14 || $occupation == 6 || $occupation == 23){
            if(isset($riskDetails[$i]['other_occupation']))
            {
                $other_occupation = $riskDetails[$i]['other_occupation'];
            }
        }
        $account_id = $AccountIds[$i];
        $display = "display-none";
        $folder = "attachments";        
    @endphp
@endif
@if(Session::get('is_review') == 1)
    @php
      if(!is_array($basis_categorisation))
            {
        $basis_categorisation = explode(',',$basis_categorisation);
    }
        $is_review = 1;
        $enable = "";
        $readonly = "readonly";
        $folder = "markedattachments";
        $disabled = "disabled";
    @endphp
@endif
@if((Session::get('in_progress') == 1) && (Session::get('is_review') == 1) && (Session::get('screen') == 3))
    @php
        $basis_categorisation = explode(',',$basis_categorisation);
    @endphp
@endif
@php
$is_huf = false;

if($accountDetails['constitution'] == 'NON_IND_HUF'&& $i == 2){
   $is_huf_display = true;
   $is_huf = true;
   $customerTypeList[$i] = array_filter($customerTypeList[$i], fn($key) => $key == 'Joint Families (HUF)');
   $lcr_category = 'Joint Families (HUF)';
}else{
    if($accountDetails['constitution'] == 'NON_IND_HUF'){
        $customerTypeList[$i] = array_filter($customerTypeList[$i], fn($key) => $key != 'Joint Families (HUF)');
    }else{
        $customerTypeList[$i] = array_filter($customerTypeList[$i], fn($key) => $key != 'Joint Families (HUF)');
    }
   $is_huf_display = false;
}
@endphp
<div id="tab{{$i}}" class="tab-content-cust">
    <div class="card RiskClassificationForm"  id="{{$i}}" is_new_custtype="{{$checkEtbNtb[$i]->is_new_customer}}">
        <span class="visibility_check" id="visibility_check-{{$i}}"></span>
        
        <input type="hidden" id="applicantId-{{$i}}" value="{{$account_id}}" customertype="{{$customertype}}">
        <input type="hidden"  name="h_per_country" id="h_per_country-{{$i}}" value="{{$per_country}}">
        <div class="card-block">
            <div class="row">
                <div class="col-md-12">
                    <h4 class="sub-title">CIDD Customer Information</h4>
                </div>
            </div>

            <div class="row">
                @if(!$is_huf_display)
                <div class="details-custcol-row col-md-6">
                    <div class="details-custcol-row-top d-flex editColumnDiv">
                        <div class="detaisl-left d-flex align-content-center ">
                            <p class="lable-cus">Education</p> 
                            <span class="{{$enable}}">
                                @if(isset($reviewDetails['education']))
                                    <i class="fa fa-times"></i>
                                    {{$reviewDetails['education']}}
                                    <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                @else
                                    <i class="fa fa-check"></i>
                                @endif
                            </span>
                        </div>                                                   
                    </div>
                    <div class="details-custcol-row-bootm">
                        <div class="comments-blck">
                            {!! Form::select('education',$educationList,$education,array('class'=>'form-control education RiskClassificationField',
                                'table'=>'risk_classification_details','name'=>'education','id'=>'education-'.$i,'placeholder'=>'',$etbdisabled,$disabled)) !!}
                            <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                        </div>
                    </div>
                </div>
                @endif

                <div class="details-custcol-row col-md-6">
                    <div class="details-custcol-row-top d-flex editColumnDiv">
                        <div class="detaisl-left d-flex align-content-center ">
                            Customer Type
                            <span class="{{$enable}}">
                                @if(isset($reviewDetails['customer_type-'.$i]))
                                    <i class="fa fa-times"></i>
                                    {{$reviewDetails['customer_type-'.$i]}}
                                    <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                @else
                                    <i class="fa fa-check"></i>
                                @endif
                            </span>
                        </div>
                    </div>
                    <div class="details-custcol-row-bootm">
                        <div class="comments-blck">

                            {!! Form::select('customer_type',$customerTypeList[$i],$customer_type,array('class'=>'form-control customer_type customerRiskType RiskClassificationField',
                                        'id'=>'customer_type-'.$i,'table'=>'risk_classification_details','name'=>'customer_type','placeholder'=>'',$etbdisabled,$disabled)) !!}
                            <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                        </div>
                    </div>
                </div>
                               
                <div class="details-custcol-row col-md-6">
                    <div class="details-custcol-row-top d-flex editColumnDiv">
                        <div class="detaisl-left d-flex align-content-center ">
                            <p class="lable-cus">Gross Annual Income</p> 
                            <span class="{{$enable}}">
                                @if(isset($reviewDetails['gross_income-'.$i]))
                                    <i class="fa fa-times"></i>
                                    {{$reviewDetails['gross_income-'.$i]}}
                                    <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                @else
                                    <i class="fa fa-check"></i>
                                @endif
                            </span>
                        </div>                                                   
                    </div>
                    <div class="details-custcol-row-bootm">
                        <div class="comments-blck">
                            @php
                                $testArray = array();
                         
                                //form60 annual gross income
                                    if($checkEtbNtb[$i]->pf_type == 'pancard'){
                                        $grossIncome = $grossIncome;
                                    }else{

                                        foreach($grossIncome as $key => $value){
                                            if(in_array($key,[1,2,3])){
                                                $testArray[$key] = $grossIncome[$key];
                                            }
                                        }
                                        $grossIncome = $testArray;
                                    }
                            @endphp
                            {!! Form::select('gross_income',$grossIncome,$gross_income,array('class'=>'form-control gross_income RiskClassificationField',
                                'table'=>'risk_classification_details','name'=>'gross_income','id'=>'gross_income-'.$i,'placeholder'=>'',$etbdisabled,$disabled)) !!}
                            <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                        </div>
                    </div>
                </div>


                <div class="details-custcol-row col-md-6">
                    <div class="details-custcol-row-top d-flex editColumnDiv">
                        <div class="detaisl-left d-flex align-content-center ">
                            <p class="lable-cus"> Networth</p> 
                            <span class="{{$enable}}">
                                @if(isset($reviewDetails['networth']))
                                    <i class="fa fa-times"></i>
                                    {{$reviewDetails['networth']}}
                                    <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                @else
                                    <i class="fa fa-check"></i>
                                @endif
                            </span>
                        </div>                                                   
                    </div>
                    <div class="details-custcol-row-bootm">
                        <div class="comments-blck">
                            {!! Form::select('networth',$networthList,$networth,array('class'=>'form-control networth RiskClassificationField',
                                'table'=>'risk_classification_details','name'=>'networth','id'=>'networth-'.$i,'placeholder'=>'',$etbdisabled,$disabled)) !!}
                            <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                        </div>
                    </div>
                </div>

                <div class="details-custcol-row col-md-6">
                    <div class="details-custcol-row-top d-flex editColumnDiv">
                        <div class="detaisl-left d-flex align-content-center ">
                            {{ $is_huf_display ? 'Nature of Business*' : 'Occupation*' }}
                            <span class="{{$enable}}">
                                @if(isset($reviewDetails['occupation-'.$i]))
                                    <i class="fa fa-times"></i>
                                    {{$reviewDetails['occupation-'.$i]}}
                                    <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                @else
                                    <i class="fa fa-check"></i>
                                @endif
                            </span>
                        </div>
                    </div>
                    <div class="details-custcol-row-bootm">
                        <div class="comments-blck">
                            {!! Form::select('occupation',$occupationList[$i],$occupation,array('class'=>'form-control occupation RiskClassificationField',
                                        'id'=>'occupation-'.$i,'table'=>'risk_classification_details','name'=>'occupation','placeholder'=>'',$etbdisabled,$disabled)) !!}
                            <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                        </div>
                    </div>
                </div>

                <div class="details-custcol-row col-md-6 mt-4">
                    <div class="details-custcol-row-top d-flex editColumnDiv">
                        <div class="detaisl-left d-flex align-content-center ">
                            PEP
                            <span class="{{$enable}}">
                                @if(isset($reviewDetails['pep-'.$i]))
                                    <i class="fa fa-times"></i>
                                    {{$reviewDetails['pep-'.$i]}}
                                    <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                @else
                                    <i class="fa fa-check"></i>
                                @endif
                            </span>
                        </div>
                    </div>
                    <div class="details-custcol-row-bootm">
                        <div class="comments-blck">
                            <div class="radio-selection">
                                <label class="radio">
                                    <input class="RiskClassificationField pep" type="radio" name="pep-{{$i}}" id="pep-{{$i}}" value="Yes" {{ ($pep=="Yes")? "checked" : "" }} {{$disabled}} {{$etbdisabled}}>
                                    <span class="lbl padding-8">Yes</span>
                                </label>
                                <label class="radio">
                                    <input classs="RiskClassificationField pep" type="radio" name="pep-{{$i}}" id="pep-{{$i}}" data-attr="pepno-{{$i}}" value="No" {{ ($pep=="No")? "checked" : "" }} {{$disabled}} {{$etbdisabled}}>
                                    <span class="lbl padding-8">No</span>
                                </label>
                            </div>
                            <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                        </div>
                    </div>
                </div>

                @if(($occupation == 'OTHERS') || ($other_occupation != ''))
                    <div class="details-custcol-row col-md-6" id="other_occupation_div-{{$i}}">
                @else
                    <div class="details-custcol-row col-md-6 display-none" id="other_occupation_div-{{$i}}">
                @endif
                    <div class="details-custcol-row-top d-flex editColumnDiv">
                        <div class="detaisl-left d-flex align-content-center ">
                            {{ $is_huf_display ? 'Other Business :' : 'Other Occupation :' }}
                            <span class="{{$enable}}">
                                @if(isset($reviewDetails['other_occupation-'.$i]))
                                    <i class="fa fa-times"></i>
                                    {{$reviewDetails['other_occupation-'.$i]}}
                                    <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                @else
                                    <i class="fa fa-check"></i>
                                @endif
                            </span>
                        </div>
                    </div>
                    <div class="details-custcol-row-bootm">
                        <div class="comments-blck">
                            <input type="text" class="form-control RiskClassificationField" table="risk_classification_details" name="other_occupation" id="other_occupation-{{$i}}" value="{{$other_occupation}}" {{$readonly}} {{$etbreadonly}} oninput="this.value = this.value.replace(/[^a-z0-9 ]/gi, '').replace(/(\..*)\./g, '$1');">
                            <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                        </div>
                    </div>
                </div>

                @if($categorisation_others_comments != '')
                    <div class="details-custcol-row col-md-6" id="categorisation_others_comments-{{$i}}">
                @else
                    <div class="details-custcol-row col-md-6 display-none" id="categorisation_others_comments-{{$i}}">
                @endif
                    <div class="details-custcol-row-top d-flex editColumnDiv">
                        <div class="detaisl-left d-flex align-content-center ">
                            Categorisation Comments : 
                            <span class="{{$enable}}">
                                @if(isset($reviewDetails['categorisation_others_comments-'.$i]))
                                    <i class="fa fa-times"></i>
                                    {{$reviewDetails['categorisation_others_comments-'.$i]}}
                                    <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                @else
                                    <i class="fa fa-check"></i>
                                @endif
                            </span>
                        </div>
                    </div>
                    <div class="details-custcol-row-bootm">
                        <div class="comments-blck">
                            <input type="text" class="form-control RiskClassificationField" table="risk_classification_details" name="categorisation_others_comments" id="categorisation_others_comments-{{$i}}" value="{{$categorisation_others_comments}}" {{$readonly}} {{$etbreadonly}}>
                            <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                        </div>
                    </div>
                </div>                
            </div>
            
            <div class="row">
                <div class="col-md-12">
                    <div class="border-bottomrc"> <span>Information Related to FATCA Compliance</span></div>
                </div>
            </div>

            <div class="row">
                <div class="details-custcol-row col-md-6">
                    <div class="details-custcol-row-top d-flex editColumnDiv">
                        <div class="detaisl-left d-flex align-content-center ">
                            Current Residency*
                            <span class="{{$enable}}">
                                @if(isset($reviewDetails['country_name-'.$i]))
                                    <i class="fa fa-times"></i>
                                    {{$reviewDetails['country_name-'.$i]}}
                                    <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                @else
                                    <i class="fa fa-check"></i>
                                @endif
                            </span>
                        </div>
                    </div>
                    <div class="details-custcol-row-bootm">
                        <div class="comments-blck">
                            {!! Form::select('country_name',$countries,$country_name,array('class'=>'form-control country_name country RiskClassificationField',
                                'id'=>'country_name-'.$i,'table'=>'risk_classification_details','name'=>'country_name',$etbdisabled,$disabled)) !!}
                            <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                        </div>
                    </div>
                </div>

                 <div class="details-custcol-row col-md-6">
                    <div class="details-custcol-row-top d-flex editColumnDiv">
                        <div class="detaisl-left d-flex align-content-center ">
                            <p class="lable-cus">Country of Birth</p> 
                            <span class="{{$enable}}">
                                @if(isset($reviewDetails['country_of_birth']))
                                    <i class="fa fa-times"></i>
                                    {{$reviewDetails['country_of_birth']}}
                                    <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                @else
                                    <i class="fa fa-check"></i>
                                @endif
                            </span>
                        </div>                                                   
                    </div>
                    <div class="details-custcol-row-bootm">
                        <div class="comments-blck">
                            {!! Form::select('country_of_birth',$placeOfBirthList,$country_of_birth,array('class'=>'form-control country_of_birth RiskClassificationField',
                            'id'=>'country_of_birth-'.$i,'table'=>'risk_classification_details','name'=>'country_of_birth',$etbdisabled,$disabled)) !!}
                            <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                        </div>
                    </div>
                </div>

                <div class="details-custcol-row col-md-6">
                    <div class="details-custcol-row-top d-flex editColumnDiv">
                        <div class="detaisl-left d-flex align-content-center ">
                            <p class="lable-cus">Country of Citizenship</p> 
                            <span class="{{$enable}}">
                                @if(isset($reviewDetails['citizenship']))
                                    <i class="fa fa-times"></i>
                                    {{$reviewDetails['citizenship']}}
                                    <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                @else
                                    <i class="fa fa-check"></i>
                                @endif
                            </span>
                        </div>                                                   
                    </div>
                    <div class="details-custcol-row-bootm">
                        <div class="comments-blck">
                            {!! Form::select('citizenship',$citizenshipList,$citizenship,array('class'=>'form-control citizenship RiskClassificationField',
                            'id'=>'citizenship-'.$i,'table'=>'risk_classification_details','name'=>'citizenship',$etbdisabled,$disabled)) !!}
                            <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                        </div>
                    </div>
                </div>

                <div class="details-custcol-row col-md-6">
                    <div class="details-custcol-row-top d-flex editColumnDiv">
                        <div class="detaisl-left d-flex align-content-center ">
                            <p class="lable-cus">Place of Birth</p> 
                            <span class="{{$enable}}">
                                @if(isset($reviewDetails['place_of_birth-'.$i]))
                                    <i class="fa fa-times"></i>
                                    {{$reviewDetails['place_of_birth-'.$i]}}
                                    <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                @else
                                    <i class="fa fa-check"></i>
                                @endif
                            </span>
                        </div>                                                   
                    </div>
                    <div class="details-custcol-row-bootm">
                        <div class="comments-blck">
                            <input type="text" class="form-control RiskClassificationField input-capitalize" table="risk_classification_details" id="place_of_birth-{{$i}}" name="place_of_birth" value="{{$place_of_birth}}" {{$readonly}} {{$etbreadonly}} {{$etbdisabled}} {{$disabled}} onkeyup="this.value = this.value.toUpperCase();" oninput="this.value = this.value.replace(/[^a-z0-9 ]/gi, '').replace(/(\..*)\./g, '$1');"  >
                            <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                        </div>
                    </div>
                </div>
                
                <div class="details-custcol-row col-md-6">
                    <div class="details-custcol-row-top d-flex editColumnDiv">
                        <div class="detaisl-left d-flex align-content-center ">
                            Residence for Tax Purpose
                            <span class="{{$enable}}">
                                @if(isset($reviewDetails['residence-'.$i]))
                                    <i class="fa fa-times"></i>
                                    {{$reviewDetails['residence-'.$i]}}
                                    <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                @else
                                    <i class="fa fa-check"></i>
                                @endif
                            </span>
                        </div>
                    </div>
                    <div class="details-custcol-row-bootm">
                        <div class="comments-blck">
                            {!! Form::select('residence',$residenceList,$residence,array('class'=>'form-control residence RiskClassificationField',
                                        'id'=>'residence-'.$i,'table'=>'risk_classification_details','name'=>'residence',$etbdisabled,$disabled)) !!}
                            <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                        </div>
                    </div>
                </div> 

                <div class="details-custcol-row col-md-2 mt-4">
                    <div class="details-custcol-row-top d-flex editColumnDiv">
                        <div class="detaisl-left d-flex align-content-center ">
                            <p class="lable-cus">US Person</p>
                            <span class="{{$enable}}">
                                @if(isset($reviewDetails['us_person-'.$i]))
                                    <i class="fa fa-times"></i>
                                    {{$reviewDetails['us_person-'.$i]}}
                                    <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                @else
                                    <i class="fa fa-check"></i>
                                @endif
                            </span>
                        </div>
                    </div>
                    <div class="details-custcol-row-bootm">
                        <div class="comments-blck">
                        @if(isset($riskaccountDetails['delight_scheme']) && ($riskaccountDetails['delight_scheme'] == 5))
                            <div class="radio-selection">
                                <label class="radio">
                                    <input class="RiskClassificationField us_person" type="radio" name="us_person-{{$i}}" id="us_person-{{$i}}" value="1" {{ ($us_person=="1")? "checked" : "" }} {{$disabled}} {{$etbdisabled}} disabled="disabled">
                                    <span class="lbl padding-8">Yes</span>
                                </label>
                                <label class="radio">
                                    <input classs="RiskClassificationField us_person" type="radio" name="us_person-{{$i}}" id="us_person-{{$i}}" value="0" {{ ($us_person=="0")? "checked" : "" }} {{$disabled}} {{$etbdisabled}}  disabled="disabled">
                                    <span class="lbl padding-8">No</span>
                                </label>
                            </div>
                        @else
                           <div class="radio-selection">
                                <label class="radio">
                                    <input class="RiskClassificationField us_person" type="radio" name="us_person-{{$i}}" id="us_person-{{$i}}" value="1" {{ ($us_person=="1")? "checked" : "" }} {{$disabled}} {{$etbdisabled}}>
                                    <span class="lbl padding-8">Yes</span>
                                </label>
                                <label class="radio">
                                    <input classs="RiskClassificationField us_person" type="radio" name="us_person-{{$i}}" id="us_person-{{$i}}" value="0" {{ ($us_person=="0")? "checked" : "" }} {{$disabled}} {{$etbdisabled}}>
                                    <span class="lbl padding-8">No</span>
                                </label>
                            </div>
                        @endif
                            <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                        </div>
                    </div>
                </div>

              @if($us_person == 1)
                  <div class="details-custcol-row col-md-4 mt-1" id="tinDiv-{{$i}}">
                @else
                  <div class="details-custcol-row col-md-4 mt-1 display-none" id="tinDiv-{{$i}}">
                 @endif
                    <div class="details-custcol-row-top d-flex editColumnDiv">
                        <div class="detaisl-left d-flex align-content-center ">
                            {{-- <p class="lable-cus display-none">TIN</p>  --}}
                            <span class="{{$enable}}">
                                @if(isset($reviewDetails['tin-'.$i]))
                                    <i class="fa fa-times"></i>
                                    {{$reviewDetails['tin-'.$i]}}
                                    <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                @else
                                    <i class="fa fa-check"></i>
                                @endif
                            </span>
                        </div>                                                   
                    </div>
                    <div class="details-custcol-row-bootm mt-4">
                        <div class="comments-blck">
                            <input type="text" class="form-control RiskClassificationField input-capitalize" table="risk_classification_details" id="tin-{{$i}}" name="tin" value="{{$tin}}" onkeyup="this.value = this.value.toUpperCase();" {{$readonly}} {{$etbreadonly}} {{$etbdisabled}} placeholder="Please Enter TIN Number" oninput="this.value = this.value.replace(/[^a-z0-9 ]/gi, '').replace(/(\..*)\./g, '$1');" >
                            <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                        </div>
                    </div>
                </div>
               

            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="border-bottomrc"></div>
                </div>
            </div>

            <div class="row">
                <div class="details-custcol-row col-md-6">
                    <div class="details-custcol-row-top d-flex editColumnDiv">
                        <div class="detaisl-left d-flex align-content-center ">
                            Categorization
                            <span class="{{$enable}}">
                                @if(isset($reviewDetails['basis_categorisation-'.$i]))
                                    <i class="fa fa-times"></i>
                                    {{$reviewDetails['basis_categorisation-'.$i]}}
                                    <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                @else
                                    <i class="fa fa-check"></i>
                                @endif
                            </span>
                        </div>
                    </div>
                    <div class="details-custcol-row-bootm">
                        <div class="comments-blck">
                            {!! Form::select('basis_categorisation',$basisCategorisation,$basis_categorisation,array('class'=>'form-control basis_categorisation RiskClassificationField',
                                        'id'=>'basis_categorisation-'.$i,'table'=>'risk_classification_details','name'=>'basis_categorisation','multiple'=>'multiple','disabled'=>'disabled')) !!}
                            <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                        </div>
                    </div>
                </div>

                <div class="details-custcol-row col-md-4">
                    <div class="details-custcol-row-top d-flex editColumnDiv">
                        <div class="detaisl-left d-flex align-content-center ">
                        LCR Customer Type
                            <span class="{{$enable}}">
                                @if(isset($reviewDetails['lcr_category-'.$i]))
                                    <i class="fa fa-times"></i>
                                    {{$reviewDetails['lcr_category-'.$i]}}
                                    <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                @else
                                    <i class="fa fa-check"></i>
                                @endif
                            </span>
                        </div>
                    </div>
                    <div class="details-custcol-row-bootm">
                        <div class="comments-blck">
                            <input type="text" class="form-control form-control-sm RiskClassificationField" name="lcr_category" id="lcr_category-{{$i}}" value="{{$lcr_category}}" readonly {{$etbdisabled}}>
                            <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                        </div>
                    </div>
                </div> 

                <div class="details-custcol-row col-md-2">
                    <div class="details-custcol-row-top d-flex editColumnDiv">
                        <div class="detaisl-left d-flex align-content-center ">
                            Risk Classification
                            <span class="{{$enable}}">
                                @if(isset($reviewDetails['risk_classification_rating-'.$i]))
                                    <i class="fa fa-times"></i>
                                    {{$reviewDetails['risk_classification_rating-'.$i]}}
                                    <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                @else
                                    <i class="fa fa-check"></i>
                                @endif
                            </span>
                        </div>
                    </div>
                    <div class="details-custcol-row-bootm">
                        <div class="comments-blck">
                            <input type="text" class="form-control form-control-sm RiskClassificationField" id="risk_classification_rating-{{$i}}" name="risk_classification_rating" value="{{$risk_classification_rating}}" readonly {{$etbdisabled}}>
                            <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer text-muted">
            <div class="col-md-12 text-center mt-3 mb-3">
                @if($i == $accountHoldersCount)
                       {{--  <a href="javascript:void(0)" class="btn btn-primary nextapplicant mb-3 " id="nextapplicant-{{$i}}" tab="nextapplicant">Save and Continue</a>  --}}
                        <a href="{{route('addovddocuments')}}" class="btn btn-outline-grey mr-3">Back</a>
                <a href="javascript:void(0)" class="btn btn-primary riskClassification" id="{{$formId}}">Save and Continue</a>

                        {{-- <a href="javascript:void(0)" class="btn btn-primary nextapplicant mb-3 riskClassification" id="{{$formId}}" tab="nextapplicant">Save and Continue</a> --}}
   
                @else
                        <a href="javascript:void(0)" class="btn btn-primary nextriskclassifiaction mb-3" id="nextapplicant-{{$i}}" tab="nextapplicant">Next</a>
                @endif
            </div>

        </div>
    </div>
</div>