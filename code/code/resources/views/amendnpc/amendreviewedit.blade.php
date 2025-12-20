@php    
    $editFields = $amendL1Rules;
@endphp
<style type="text/css">
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
</style>
<script type="text/javascript">
    var _L1_editFields = JSON.parse('<?php echo addslashes(json_encode($editFields,JSON_HEX_APOS | JSON_HEX_QUOT)) ?>');    
    var stringHtml = '<div class="comments-blck eloneedit" style="margin-top:5px;"><input type="text" class="form-control  saveField" maxlength="_MAXLEN_" _INPUT_VALIDATION_ data-func="_VALIDATION_FUNC_"><i title="edit" class="fa fa-pencil-square-o editComment"></i></div>';
   	var dateHtml = '<div class="comments-blck eloneedit" style="margin-top:5px;"><input type="text" class="form-control saveField futuredate" ><i title="edit" class="fa fa-pencil-square-o editComment"></i></div>';
    var numberHtml = '<div class="comments-blck eloneedit" style="margin-top:5px;"><input type="text" class="form-control mobile  saveField" maxlength="_MAXLEN_" _INPUT_VALIDATION_ data-func="_VALIDATION_FUNC_"><i title="edit" class="fa fa-pencil-square-o editComment"></i></div>';
     var pincodeHtml = '<div class="comments-blck eloneedit" style="margin-top:5px;"><input type="text" class="form-control pincode  saveField" maxlength="_MAXLEN_" _INPUT_VALIDATION_ data-func="_VALIDATION_FUNC_"><i title="edit" class="fa fa-pencil-square-o editComment"></i></div>';
	

    function addL1editFields(){
            for(var flds=0; flds<_L1_editFields.length; flds++){  
            	var currField = _L1_editFields[flds]; 
                var inputHtml = stringHtml;
                switch(currField.data_type){
                 case 'string':
                        inputHtml = stringHtml; break;
                case 'date':
                    inputHtml = dateHtml; break;
                case 'future_date':
                    inputHtml = futuredateHtml; break;
                case 'pincode':
                    inputHtml = pincodeHtml; break;
                 case 'dropdown':
                    var array = currField.dropdown_array;
                    // console.log(array);
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
                currId = $('#'+currField.field_name).parent().parent().parent();
                currId.append(inputHtml);
                 currId.find('.saveField').attr({'config_id':flds,'field_name':currField.field_name,'id':currField.field_name+
                 	'_edit','allow_empty':currField.allow_empty});

            }
	}
	 setTimeout(() => {
        addL1editFields();
         $(".futuredate").datepicker({
             clearBtn: true,
            format: "dd-mm-yyyy",
            endDate: "today",
            maxDate: "today", 
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
        var updateFieldValue = [];
        updateFieldValue.data = {};
        var config_id = $(this).prev('.saveField').attr('config_id');
        var old_value = $(this).parent().prev().parent().prev().find('td p:last').text().trim();
        var table_id = field_name.slice(0,-5);
       if($(this).prev('.saveField').hasClass('dropdown')){
            var new_value = $('#'+field_name).find('option:selected').val();
            var content_value = $('#'+field_name).find('option:selected').text().toUpperCase();
        }else if($(this).prev('.saveField').hasClass('pincode')){
            if(field_name.match('per') != ''){
                var content_value = $(this).prev('.saveField').val() + '(' + $(this).prev('.saveField').attr('new_state_value') + ', ' + $(this).prev('.saveField').attr('new_city_value') + ')';
                var new_value = $(this).prev('.saveField').val();
                     updateFieldValue.data['state_id'] = $('#cust_perm_state_code').attr('data-id');
                     updateFieldValue.data['city_id'] = $('#cust_perm_city_code').attr('data-id');
            }
        }else{
             var new_value = $(this).prev('.saveField').val().toUpperCase();
             var content_value = $(this).prev('.saveField').val().toUpperCase();
        }
        if(new_value == '' && $(this).prev('.saveField').attr('allow_empty') == 'No'){
            $.growl({message: "Please Enter Field."},{type: "warning"});
            return false;
        }
         $.confirm({
                content: "Field Value changed <br>FROM : "+ old_value + " <br> TO : "+ content_value +" <br> Please confirm the changes",
                title: 'Update Change for '+field_name.replace('_edit'," -").toUpperCase(),
                confirmButton:'Yes',
                cancelButton: 'No',
                confirm: function(button) {
                     updateFieldValue['url'] = '/amendnpc/updateamendfieldvalue';
                     updateFieldValue.data['new_value'] = new_value;
                     updateFieldValue.data['new_value_text'] = content_value;
                     updateFieldValue.data['old_value'] = old_value;
                     updateFieldValue.data['table_id'] = $('#'+table_id).attr('data-id');
                     updateFieldValue.data['config_id'] = config_id;
                     updateFieldValue.data['functionName'] = 'UpdateAmendFieldValueCallBack';
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
            pincodeObject.url =  '/amendnpc/getamendaddressdatabypincode';
            // pincodeObject.data['id'] = $(this).attr('name');
            pincodeObject.data['id'] = $(this).attr('id');
            pincodeObject.data['pincode'] = $(this).val();
            pincodeObject.data['functionName'] = 'AmendStateCityDataCallBack';
        
            crudAjaxCall(pincodeObject);
            return false;
        }
    });

    },2500);
        function AmendStateCityDataCallBackFunction(response,object){
            if(response['status'] == "success"){
                $('#'+response.data[0]).attr('new_state_value',response.data.statedesc);
                $('#'+response.data[0]).attr('new_city_value',response.data.citydesc);
            }
        }

        function UpdateAmendFieldValueCallBackFunction(response, object){
            if(response['status'] == "success"){        
                $.growl({message: response['msg']},{type: response['status']});
                location.reload();
            }else{        
                $.growl({message: response['msg']},{type: "warning"});
            }
        }
</script>