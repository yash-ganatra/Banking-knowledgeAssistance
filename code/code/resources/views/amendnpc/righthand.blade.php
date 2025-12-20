<?php
use App\Helpers\CommonFunctions;
$masking_fields = ["Registered Email Id","Registered Mobile Number (RMN)","Aadhaar Number","PAN Number Updation","voter_id_number","Alternate Contact Number","passport_number","aadhaar_photocopy_number","driving_licence_number","pan_number"];
$tokenParams = Cookie::get('token');
$encrypt_key = substr($tokenParams, -5);
?>
@for($i=0;$i<count($getCrfDetails);$i++)
    <?php
        $lableShow = isset($getCrfDetails[$i]->dataLableshow) && $getCrfDetails[$i]->dataLableshow != ''? $getCrfDetails[$i]->dataLableshow :'';
        $displayData = isset($getCrfDetails[$i]->display) && $getCrfDetails[$i]->display != ''? $getCrfDetails[$i]->display :'';

       
        if($lableShow == 'Y'){
            $displayRaw = 'none';
        }else{
            $displayRaw = 'show';
        }
    ?>

@if($displayData != 'none')
    <div class="details-custcol mt-5">
        <div class="details-custcol-row-top d-flex">
            <div class="detaisl-left d-flex align-items-center">
            @php
                    if(isset($getCrfDetails[$i]->section) && $getCrfDetails[$i]->section = 'ovd'){
                        $getDisplay = $getCrfDetails[$i]->display_name;
                        $getCrfDetails[$i]->old_value = $getCrfDetails[$i]->value;
                        $ovdSection = true;
                    }else{
                    

                            $display = (isset($amendValidation[$getCrfDetails[$i]->field_name]) && $amendValidation[$getCrfDetails[$i]->field_name] != '') ? $amendValidation[$getCrfDetails[$i]->field_name] : '';
                            $getDisplay = isset($display['display_name']) && $display['display_name'] != '' ? $display['display_name'] : '';
                            $ovdSection = false;
	}
                    if(count($qcReviewDetails) == 0){
                        $displayClass = '';
                        $checked = '';
                    }else{
                        if(isset($qcReviewDetails['amendreview_input_'.$i]) && $qcReviewDetails['amendreview_input_'.$i] != ''){
                            $displayClass = '';
                            $checked = '';

                        }else{
                            $displayClass = 'display-none';
                            $checked = 'checked';
                        }
                    }

                    if(in_array($masterDetails['crf_status'],[35,45,85,38,48])){
                         $displayClass = 'display-none';
                    }
                    if(!isset($getCrfDetails[$i]->amend_item)){
                        $getCrfDetails[$i]->amend_item = $getCrfDetails[$i]->field_name;
                    }
                @endphp
                <table> 
                   @if(isset($getCrfDetails[$i]->account_no) && ($getCrfDetails[$i]->account_no != '') && ($getCrfDetails[$i]->soft_del != 'Y'))
                        @if($getCrfDetails[$i]->account_no != '')
                    <tr>
                        <td>
                            <label>Account No :</label><span>{{$getCrfDetails[$i]->account_no}}</span>
                        </td>
                    </tr>
                    @endif
                    @endif
                            
                    <tr>
                        @if($displayRaw != 'none')   
                            <td><p style="text-align:left;line-height:0.4px;">{{strtoupper($getDisplay)}} : </p></td>
                        <td>
                        @if(in_array($getCrfDetails[$i]->amend_item,$masking_fields) && $getCrfDetails[$i]->old_value != "")
                                <p style="text-align:left;line-height:0.4px; display:none;" class='enc_label unmaskingfield'>
                                    {{CommonFunctions::encrypt256($getCrfDetails[$i]->old_value,$encrypt_key)}}
                                </p>
                                <p style="text-align:left;line-height:0.4px;" class="maskingfield">
                                    *************
                                </p>
                            @else
                                <p style="text-align:left;line-height:0.4px;">
                                    {{strtoupper($getCrfDetails[$i]->old_value)}}
                                </p>
                            @endif
                            {{-- <p style="text-align:left;line-height:0.4px;">{{strtoupper($getCrfDetails[$i]->old_value)}}</p> --}}
                        </td>
                        @endif
                    </tr>
                    @if(!$ovdSection)
                        <tr>                                                           
                            @if($displayRaw != 'none')                                                        
                           <td><p style="text-align:left;line-height:0.4px;">NEW VALUE : </p></td>
                            @else
                                <td><p style="text-align:left;line-height:0.4px;">{{strtoupper($getDisplay)}} :</p></td>
                            @endif
                            <td>
                            @if(in_array($getCrfDetails[$i]->amend_item,$masking_fields) && $getCrfDetails[$i]->new_value_display != "")
                                <p style="text-align:left;line-height:0.4px; display:none;" class='enc_label unmaskingfield'>
                                    {{CommonFunctions::encrypt256($getCrfDetails[$i]->new_value_display,$encrypt_key)}}
                                </p>
                                <p style="text-align:left;line-height:0.4px;" class="maskingfield">
                                    *************
                                </p>
                            @else
                                <p style="text-align:left;line-height:0.4px;">@if(strtoupper($getCrfDetails[$i]->new_value_display) != 'INITIATED')
                                        {{strtoupper($getCrfDetails[$i]->new_value_display)}}
                                    @endif </p>
                            @endif
                            </td>
                        </tr>
                    @endif
                </table>
            </div>
            <div class="detaisl-right ">
                <div class=" d-flex flex-row mt-3">
                     @if($role == '19' || $role == '20' || $role == '21' || $role == '23')
                        <div class="switch-blck {{$displayClass}}">
                            <div class="toggleWrapper">
                                <input type="checkbox" name="amend_toggle_{{$i}}" class="mobileToggle toggle_field" id="amend_toggle_{{$i}}" {{$checked}} >
                                <label for="amend_toggle_{{$i}}"></label>
                            </div>
                        </div>
                    @endif
                    <div class="comments-blck {{$displayClass}}" id="amendreview_input_{{$i}}">
                        @if($role == '19' || $role == '20' || $role == '21' || $role == '23')
                            <input type="text" class="form-control commentsField " id='{{strtolower($getCrfDetails[$i]->field_name)}}' data-id='{{$getCrfDetails[$i]->id}}' >
                            <i title="save" class="fa fa-floppy-o saveAmendComments"></i>
                        @endif

                        @if(isset($qcReviewDetails['amendreview_input_'.$i]) && $qcReviewDetails['amendreview_input_'.$i] != '')
                            <span class="review-comment">
                            <i class="fa fa-exclamation review-exclamation"></i>
                          {{$qcReviewDetails['amendreview_input_'.$i]}}
                            </span>
                        @endif
                    </div>
                        <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                </div>
            </div>
        </div>
    </div>
@endif
@endfor