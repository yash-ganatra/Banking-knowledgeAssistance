@php    
    $editFields = $l1Rules;
    $applicants = $accountDetails['no_of_account_holders'];
    $proofOfentityaddress =  isset($entityDetails['proof_of_entity_address']) && $entityDetails['proof_of_entity_address'] !=''?$entityDetails['proof_of_entity_address']:'';
@endphp
<style>
    .editComment{
        position: absolute;
        right: 10px;
        top: 12px;
        color: #ff5370;
        cursor: pointer;
    }
    
    select {
      -webkit-appearance: none;
      -moz-appearance: none;
      text-indent: 1px;
      text-overflow: '';
    }
    

    .saveField{
        max-width:  380px;
        margin-left: 86px;
        border: 0px;
        border-radius: 7px;
        border-right: 0px solid rgb(206, 212, 218) ;
        border-bottom: 2px solid rgb(206, 212, 218) ;
        max-width: -webkit-fill-available;
    }

    .mobile{
        max-width:  265px;
        margin-left: 86px;
    }

    .select2-selection__arrow{
        display: none;
    }
</style>

<script>
    var _L1_editFields = JSON.parse('<?php echo addslashes(json_encode($editFields,JSON_HEX_APOS | JSON_HEX_QUOT)) ?>');    
    var banksList = JSON.parse('<?php echo json_encode($banksList); ?>');
    var titleList = JSON.parse('<?php echo json_encode($titleList); ?>');
    var nomineeRelations = JSON.parse('<?php echo json_encode($nomineeRelations); ?>');
	var proofOfentityaddress = JSON.parse('<?php echo json_encode($proofOfentityaddress); ?>');
    var _L1_applicants = <?php echo $applicants; ?>;

    var dateHtml = '<div class="comments-blck eloneedit" style="margin-top:5px;"><input type="text" class="form-control saveField dateField" ><i title="edit" class="fa fa-pencil-square-o editComment"></i></div>';
    var futuredateHtml = '<div class="comments-blck eloneedit" style="margin-top:5px;"><input type="text" class="form-control saveField futuredate" ><i title="edit" class="fa fa-pencil-square-o editComment"></i></div>';
    var stringHtml = '<div class="comments-blck eloneedit" style="margin-top:5px;"><input type="text" class="form-control  saveField" maxlength="_MAXLEN_" _INPUT_VALIDATION_ data-func="_VALIDATION_FUNC_"><i title="edit" class="fa fa-pencil-square-o editComment"></i></div>';
    var pincodeHtml = '<div class="comments-blck eloneedit" style="margin-top:5px;"><input type="text" class="form-control pincode  saveField" maxlength="_MAXLEN_" _INPUT_VALIDATION_ data-func="_VALIDATION_FUNC_"><i title="edit" class="fa fa-pencil-square-o editComment"></i></div>';
    var numberHtml = '<div class="comments-blck eloneedit" style="margin-top:5px;"><input type="text" class="form-control mobile  saveField" maxlength="_MAXLEN_" _INPUT_VALIDATION_ data-func="_VALIDATION_FUNC_"><i title="edit" class="fa fa-pencil-square-o editComment"></i></div>';

    function addL1editFields(){
        for(var appl=1; appl<=_L1_applicants; appl++){   
            for(var flds=0; flds<_L1_editFields.length; flds++){  
                var currField = _L1_editFields[flds]; 
                var inputHtml = '';
                switch(currField.data_type){
                    case 'string':
                        inputHtml = stringHtml; break;
                    case 'date':
                        inputHtml = dateHtml; break;
                    case 'future_date':
                        inputHtml = futuredateHtml; break;
                    case 'pincode':
                        inputHtml = pincodeHtml; break;
                    case 'mobile':
                        inputHtml = numberHtml; break;
                    case 'dropdown':
                    var array = currField.dropdown_array;
                    if(array == 'banksList'){
                        array = banksList;
                    }else if(array == 'nomineeRelations'){
                        array = nomineeRelations;
                    }else if(array == 'titleList'){
                        if(appl == 1){
                            array = titleList[1- appl];
                        }else{
                            array = titleList[appl - 1];
                        }
                    }
                    let selectHtml = "<div class='comments-blck eloneedit' style='margin-top:5px;'><select class='form-control dropdown saveField' data-func='_VALIDATION_FUNC_'>";
                    $.each(array, function(index, arrayValue){
                        selectHtml += "<option value='"+index+"'>" + arrayValue + "</option>";
                    });
                    selectHtml += "</select><i title='edit' class='fa fa-pencil-square-o editComment'></i></div>";
                    inputHtml = selectHtml; break;
                    default:
                        break;
                 }

                 switch(currField.allowed_input){
                    case 'alpha':
                        inputHtml = inputHtml.replace('_INPUT_VALIDATION_','onkeypress="return /[a-z ]/i.test(event.key)"');
                        break;
                    case 'numeric':
                        inputHtml = inputHtml.replace('_INPUT_VALIDATION_','onkeypress="return /[0-9]/i.test(event.key)"');
                        break;
                    case 'alphanumeric':
                        inputHtml = inputHtml.replace('_INPUT_VALIDATION_','onkeypress="return /[a-z 0-9]/i.test(event.key)"');
                        break;
                    case 'email':
                        inputHtml = inputHtml.replace('_INPUT_VALIDATION_','onkeypress="return /[a-zA-Z\@\.\_0-9]/i.test(event.key)"');
                        break; 
                    case 'address':
                        inputHtml = inputHtml.replace('_INPUT_VALIDATION_','onkeypress="return /[/& a-z 0-9 .()@_,-]/i.test(event.key)"');
                        break;                       
                    default:
                        break;
                 }

                 
                inputHtml = inputHtml.replace('_VALIDATION_FUNC_', currField.validation_type);
                 
                     inputHtml = currField.max_length != '' ? inputHtml.replace('_MAXLEN_',currField.max_length) : inputHtml;
	if(proofOfentityaddress == '5' && currField.field_name == 'entity_add_proof_card_number'){
                        currField.is_hypen = 'Yes';
                    }
                    
                     if(appl >= 1 && currField.is_hypen == 'Yes'){
                        if(currField.field_name == 'id_proof_card_number' && ($('#'+currField.field_name+'-'+appl).hasClass('idtype_1') || $('#'+currField.field_name+'-'+appl).hasClass('idtype_2') || $('#'+currField.field_name+'-'+appl).hasClass('idtype_9'))){
                            continue;
                        }else if(currField.field_name == 'add_proof_card_number' && ($('#'+currField.field_name+'-'+appl).hasClass('idtype_1') || $('#'+currField.field_name+'-'+appl).hasClass('idtype_2') || $('#'+currField.field_name+'-'+appl).hasClass('idtype_9'))){
                            continue;
                        }else if(currField.field_name == 'current_add_proof_card_number' && ($('#'+currField.field_name+'-'+appl).hasClass('idtype_1') || $('#'+currField.field_name+'-'+appl).hasClass('idtype_2') || $('#'+currField.field_name+'-'+appl).hasClass('idtype_9'))){
                            continue;
                        }
                             currId = $('#'+currField.field_name+'-'+appl).parent().parent().parent();
                             currId.append(inputHtml);

                     }else if(appl == 1 && currField.is_hypen == 'No'){
                         currId = $('#'+currField.field_name).parent().parent().parent();
                         currId.append(inputHtml);
                     }
                        if(currId != undefined){
                            currId.find('.saveField').attr({'config_id':flds,'applicant-id':appl,'field_name':currField.field_name+'_edit-'+appl,'id':currField.field_name+appl,'allow_empty':currField.allow_empty});
                             $('#email'+appl).css('max-width','265px');
                            if($('#'+currField.field_name+'-'+appl).parent().hasClass('display-none') == true && currField.is_hypen == 'Yes'){
                                $('#'+currField.field_name+appl).parent().addClass('display-none');
                            }else if($('#'+currField.field_name).parent().hasClass('display-none') == true && currField.is_hypen == 'No'){
                                $('#'+currField.field_name+appl).parent().addClass('display-none');
                            }
                        }
            }
        }
    }     


    setTimeout(() => {
        addL1editFields();
        $(".futuredate").datepicker({
            clearBtn: true,
            format: "dd-mm-yyyy",
            startDate: new Date(),
        }).on('change', function () {
            var curr = $(this);
            var idSequence = 1;
            if(curr[0].id != null){
                idSequence = curr[0].id.split('-')[1];
            }                        
        }); 

    $(".editComment").on("click",function(){

        var field_name = $(this).prev('.saveField').attr('id');
        var validfunc = $(this).prev('.saveField').data('func');
       

        if(window[validfunc] != undefined){
            if(validfunc !='' && !window[validfunc].apply(window,[field_name])){
                $.growl({message:'Validation failed. Please recheck'},{type:'warning'});
                return false;
            }
        }

        var updateFieldValue = [];
        updateFieldValue.data = {};
       var applicant_sequence = $(this).prev('.saveField').attr('applicant-id');
       var config_id = $(this).prev('.saveField').attr('config_id');
       var old_value = $(this).parent().prev().parent().prev().find('span').text().trim();
            if($(this).prev('.saveField').hasClass('dropdown')){
                var new_value = $('#'+field_name).find('option:selected').val();
                var content_value = $('#'+field_name).find('option:selected').text().toUpperCase();
            }else if($(this).prev('.saveField').hasClass('pincode')){
                if(field_name.match('per') != ''){
                    var content_value = $(this).prev('.saveField').val() + '(' + $(this).prev('.saveField').attr('new_state_value') + ', ' + $(this).prev('.saveField').attr('new_city_value') + ')';
                    var new_value = $(this).prev('.saveField').val();
                }
            }else{
             var new_value = $(this).prev('.saveField').val().toUpperCase();
             var content_value = $(this).prev('.saveField').val().toUpperCase();
            }

            if(new_value == '' && $(this).prev('.saveField').attr('allow_empty') == 'No'){
                $.growl({message:'Field Empty. Please recheck'},{type:'warning'});
                return false;
            }

        $.confirm({
                content: "Field Value changed <br>FROM : "+ old_value + " <br> TO : "+ content_value +". <br> Please confirm the changes",
                title: 'Update Change for '+field_name.replace('_edit'," -").toUpperCase(),
                confirmButton:'Yes',
                cancelButton: 'No',
                confirm: function(button) {
                     updateFieldValue['url'] = '/npc/updateFieldValue';
                     updateFieldValue.data['new_value'] = new_value;
                     updateFieldValue.data['new_value_text'] = content_value;
                     updateFieldValue.data['old_value'] = old_value;
                     updateFieldValue.data['form_id'] = $('#formId').val();
                     updateFieldValue.data['config_id'] = config_id;
                     updateFieldValue.data['applicant_seq'] = applicant_sequence;
                     updateFieldValue.data['functionName'] = 'UpdateFieldValueCallBack';
                     crudAjaxCall(updateFieldValue);
                },
                cancel: function(button) {
                }
            });
        });

        $("body").on("keyup",".pincode",function(){
        if($(this).val().length >= 6){
            var pincodeObject = [];
            pincodeObject.data = {};
            pincodeObject.url =  '/npc/getaddressdatabypincode';
            // pincodeObject.data['id'] = $(this).attr('name');
            pincodeObject.data['id'] = $(this).attr('id');
            pincodeObject.data['pincode'] = $(this).val();
            pincodeObject.data['functionName'] = 'StateCityDataCallBack';
        
            crudAjaxCall(pincodeObject);
            return false;
        }
    });

    //     $("body").on("change","#ifsc_code1",function(){
    //     var bankObject = [];
    //     bankObject.data = {};
    //     bankObject.url =  '/bank/getifsccode';
    //     bankObject.data['id'] = $(this).val();
    //     bankObject.data['functionName'] = 'NpcBankCodeCallBack';
    //     crudAjaxCall(bankObject);
    //     return false;
    // });

    }, 8000);

    // function NpcBankCodeCallBackFunction(response,object){
    //     if(response['status'] == "success"){
    //         $('#ifsc_code1').parent().append('<input type="text" class="form-control  saveField" id="ifsc_code-1" config_id="14" applicant-id="1" field_name="ifsc_code_edit-1" maxlength="11"><i title="edit" class="fa fa-pencil-square-o editComment">')
    //         $('#ifsc_code1').remove();
    //         $("#ifsc_code-1").val(response.data['ifsc_code_prefix']);
    //     }else{
    //         $.growl({message: response['msg']},{type: "warning"});
    //     }
    //     return false;
    // }

    
     function StateCityDataCallBackFunction(response,object){
        if(response['status'] == "success"){
            $('#'+response.data[0]).attr('new_state_value',response.data.statedesc);
            $('#'+response.data[0]).attr('new_city_value',response.data.citydesc);
        }
     }
    function UpdateFieldValueCallBackFunction(response,object){
        if(response['status'] == "success"){        
            $.growl({message: response['msg']},{type: response['status']});
            location.reload();
        }else{        
            $.growl({message: response['msg']},{type: "warning"});
        }

    }

    function ValidateEmail(inputText)    {
        var mailformat = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/;
        if(inputText.value.match(mailformat)){
            return true;
        }else{
            return false;
        }
    }

/*    
    //<div class="comments-blck eloneedit commentsField" style="margin-top:5px;"><input type="text" class="form-control dob saveField"><i title="edit" class="fa fa-pencil-square-o editComment"></i></div>
    //<div class="comments-blck eloneedit commentsField" style="margin-top:5px;"><input type="text" class="form-control _name saveField"><i title="edit" class="fa fa-pencil-square-o editComment"></i></div>
    var stringHtml = '';
*/

</script>
<script src="{{ asset('custom/js/eloneedit.js') }}"></script>