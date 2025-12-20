$(document).ready(function(){
    disableCifPhysicalForm();
    _isReview = "<?php echo Session::get('is_review'); ?>";
    $('#sentDate').datepicker({
        format: 'dd-mm-yyyy',
        autoClose: true,
        endDate: new Date(),         
    }).bind('datepicker-change',function(event,obj){
       //getUserActivityLogs('/admin/activitylogs','useractivitylogs',tableRemainingHeight);
    });
    if (!_isReview) {
        $('#sentDate').datepicker("setDate", new Date());
        $('#sentDate').datepicker("setEndDate", new Date());
    }
   
    var submissionFormObj = $('#submissionForm').validate({     
        // ignore: ":hidden",    // initialize the plugin
        rules: {
            reason_for_accnt_opening: {
               required: true,
            },
            lead_generated: {
                required: true,
            },
            dist_from_branch: {
                required: true,
            },
            meeting_date: {
                required: true,
            },
            customer_meeting_location: {
                required: true,
            },
            segment_list: {
                required:function(){
                    return _flow_Type != 'ETB';
                }                
            }
        },
        messages: {
            reason_for_accnt_opening: {
                required: "Please Select Reason for account opening."
            },            
            lead_generated: {
                required: "Please Select Generated Lead"
            },
            dist_from_branch: {
                required: "Please Select Distance from Branch"
            },
            meeting_date: {
                required: "Please Select Customer meeting date"
            },
            customer_meeting_location: {
                required: "Please Select customer meeting location"
            },
            segment_list: {
                required: "Please Select segment list"
            },
        },
        errorPlacement: function( error, element ) {
            if($(element).attr('type') == 'text'){
                error.insertAfter( element );
                // error.insertAfter( element.parent().parent() );
            }else if($(element).attr('type') == 'radio'){
                error.insertAfter( element.parent().parent() );
            }else if($(element).attr('type') == 'hidden'){
                error.insertAfter( element );
            }else{
                error.insertAfter( element.parent() );
            }
        },
    });

    $("body").on("click","#submission_modal_button",function(){
         if (!submissionFormObj.form()) { // Not Valid
            return false;
        }else{
 
            $('#Username-blck').modal('toggle');
        }

    });
    
    $("body").on("click",".submit_to_npc",function(){

        if(($('#submission_user_name').val() == '') || ($('#submission_user_password').val() == '')){
             
             $.growl({message: "Blank / Invalid Credentials"},{type: "warning"});
           return false;
        }


        $('.br_submit_loader').removeClass('display-none-br-submit-loader');
        if ($('.declaration:checked').length != $('.declaration').length) {
           $.growl({message: "Please Select Checkbox"},{type: "warning"});
           return false;
        }
        var val = [];
        // var gpa_required = 0;
        // var two_way_sweep = 0;
        $('.declaration:checked').each(function(i){
          val[i] = $(this).val();
        });
        // if($("#gpa_required").prop('checked') == true)
        // {
        //     gpa_required = 1;   
        // }
        // if($("#two_way_sweep").prop('checked') == true)
        // {
        //     two_way_sweep = 1;   
        // }

       if(_flow_Type != 'ETB'){
        if($('#segment_list').val() == ''){
            $('.close').click();  
            $.growl({message: "Please Select Segment!"},{type: "warning"}); 
            return false;  
        }
    }
        
        var password = $("#submission_user_password").val();
        password = encrypt(password,btoa($('meta[name="cookie"]').attr('content')).substr(0,6));
        password += '='+btoa($('meta[name="cookie"]').attr('content')).substr(0,6);
        password += paddingsalt($('meta[name="cookie"]').attr('content'));

        var submitToNpcObject = [];
        submitToNpcObject.data = {};
        submitToNpcObject.url =  '/bank/submittonpc';

         $('.submissionDeclarationField').each(function(i){
          $declarationId = $(this).attr('name');
          submitToNpcObject.data[$declarationId] = $(this).val();
        });


        submitToNpcObject.data['formId'] = $(this).attr('id');
        submitToNpcObject.data['Declarations'] = val;
        //submitToNpcObject.data['gpa_required'] = gpa_required;
        // submitToNpcObject.data['two_way_sweep'] = two_way_sweep;
        submitToNpcObject.data['branch_id'] = $('#branch_id').val();
        submitToNpcObject.data['segment_code'] = $('#segment_list').val();
        submitToNpcObject.data['user_id'] = $('#submission_user_name').val();
        submitToNpcObject.data['password'] = password;
        submitToNpcObject.data['functionName'] = 'SubmitToNpcCallBack';

        disableSaveAndContinue(this);
        disableWorkAreaDuringBranchSubmit();
        crudAjaxCall(submitToNpcObject);
        return false;
    });
});

function disableCifPhysicalForm() {
    $("#customer_meeting_location option[value='9']").prop('disabled',true);
    $("#reason_for_accnt_opening option[value='9']").prop('disabled',true);
    $("#lead_generated option[value='P']").prop('disabled',true);
    $("#dist_from_branch option[value='9']").prop('disabled',true);
    $("#customer_meeting_location").select2();    
    $("#reason_for_accnt_opening").select2();    
    $("#lead_generated").select2();    
    $("#dist_from_branch").select2();    
}