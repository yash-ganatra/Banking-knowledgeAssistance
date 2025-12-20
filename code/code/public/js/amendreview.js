$('document').ready(function(){
    setTimeout(function(){updateLastSavedComments(_savedComments);}, 3000);	

        $('.imageToggle').on('click',function(){
            var id = $(this).attr("id");
            if($(this).hasClass('minusBtn') == true){
                $(this).removeClass('minusBtn');
                $(this).text('+');
                $('#'+id).next().next().addClass('display-none');
            }else{
                $(this).addClass('minusBtn');
                $(this).text('-');
                $('#'+id).next().next().removeClass('display-none');
            }
        });
        
    
        $('.toggle_field').on('change',function(){
            var id = $(this).attr("id");
            var idlastindex = $(this).attr("id").split('_')[2];
    
            var active = $('#'+id+':checked').val();
            if(active == undefined){
                $(this).parent().parent().next().removeClass('display-none');
                if($(this).parent().parent().parent().next().find('.saveField')){
                    $(this).parent().parent().parent().next().removeClass('display-none');
                }
    
            }else{
                $(this).parent().parent().next().addClass('display-none');
                if($(this).parent().parent().next().next().find('p')){
                    $(this).parent().parent().next().next().find('p').addClass('display-none');
                }
                if($(this).parent().parent().parent().next().find('.saveField')){
                    $(this).parent().parent().parent().next().addClass('display-none');
                }
            }
    
            var id = $(this).closest('#verify-checkbox').attr('id');
            $('#'+$(this).closest('#verify-checkbox').attr('id')).find('input:checkbox').each(function() {
                if($("input[name='"+$(this).attr("name")+"']").is(":checked") == false){
                    valid = false;
                    return false;
                }else{
                    valid = true;
                }
            });
            if((valid)){
                $(".clear").removeClass('display-none');
                $(".discrepent").addClass('display-none');
            }else{
                $(".clear").addClass('display-none');
                $(".discrepent").removeClass('display-none');
            }
            return false;
        });	
    
    
            $(".commonBtn").on("click", function(){
                var is_empty = false;
                var id = $(this).attr('id');
                var is_hold_reject = false;

                if(id == 'hold' || id == 'reject'){
                    var is_hold_reject = true;
                    if(id == 'hold'){
                        var hold_reject_id = 'hold_comment';
                        var hold_comment_display = 'Hold Comment';
                    }else{
                        var hold_reject_id = 'reject_comment';
                        var hold_comment_display = 'Reject Comment';
                    }
                } 



                if(!is_hold_reject){
                    $(".commentsField").each(function(){
                        if(!$(this).is(":hidden")){
                            if($(this).val() == '')
                            {
                                var name = $(this).attr('id').split('_')[0];
                                is_empty = true;
                                return false;
                            }
                        }
                    });

                    if((is_empty) && ($(this).hasClass("noValidation") == false))
                    {
                        $.growl({message: "Please add comments"},{type: "warning"});
                        return false;
                    }

                    var allow_forward = false;
                    // $('.comments-blck').each(function(index){
                    $(".commentsField").each(function(){
                        if($(this).is(":visible")){
                            if($(this).val() != '') {
                                allow_forward = true;
                                return false;
                            }
                        }
                    });

                    if((allow_forward) && ($(this).hasClass("noValidation") == false))
                    {
                        $.growl({message: "Please save Comments"},{type: "warning"});
                        return false;
                    }
                    if(typeof($(this).attr('id')) == "undefined")
                    {
                        $.growl({message: "Please update responses"},{type: "warning"});
                        return false;
                    }
                }else{
                    if($('#'+hold_reject_id).val() == ''){
                        $.growl({message: "Please Add "+hold_comment_display},{type: "warning"});
                        return false;
                    }
                }
                var discrepent = {};

                $(".comments-blck").each(function(index){
                    var id = $(this).attr('id');
                    if($('#'+id).next().find('p').text() != ''){
                        discrepent[index] = {};
                        discrepent[index]['comments'] = $('#'+id).next().find('p').text().replace('Edit','').trim();
                        discrepent[index]['column_name'] = id;
                        discrepent[index]['amend_queue_id'] = $('#'+id).find('input').attr('data-id')
                        discrepent[index]['crf_number'] = $('#crfNumber').val();
                        discrepent[index]['iteration'] = $('#iteration').text();
                    }
                });

                var amendbuttonObject = [];
                amendbuttonObject.data = {};
                amendbuttonObject.url =  '/amendnpc/amendnpcsubmit';
                amendbuttonObject.data['method'] = id;
                amendbuttonObject.data['discrepent'] = discrepent;
                amendbuttonObject.data['crf_number'] = $('#crfNumber').val();
                amendbuttonObject.data['role'] = $('#role').val();
                if(id == 'reject'){
                    amendbuttonObject.data['reject_comment'] = $('#reject_comment').val();
                }else if(id == 'hold'){
                    amendbuttonObject.data['hold_comment'] = $('#hold_comment').val();
                }
    
                amendbuttonObject.data['functionName'] = 'SaveAmendNpcSubmitCallBack';
                crudAjaxCall(amendbuttonObject);
                return false;
    
                alert(crfNumber);
            });
    
    
        
    
        $(".saveAmendComments").on("click",function(){
            if($(this).prev().val().trim() == ''){
                $.growl({message: "Please enter comments"},{type: "warning"});
                return false;
            }
             var regexMatch= $(this).prev().val().match(/[^a-zA-Z0-9 .,%?\/\\()_\n\r]/gi);
            if (regexMatch != null && regexMatch.length > 0) {
                $.growl({message: "Error! Invalid comment (Special characters are not allowed)"},{type: "warning"});
                return false;
            }
            var column_name = $(this).prev().attr("id");
            var reviewId = $(this).parent().attr("id");
            var comments = $(this).prev().val();

            $("#"+column_name).parent().addClass('display-none');
                var buildHtml = '<p>'+
                                    comments+
                                    ' <a href="javascript:void(0);" class="editComments" id="'+reviewId+'">'+
                                        'Edit'+
                                    '</a>'+
                                '</p>';
            $("#"+column_name).parent().next().html(buildHtml).removeClass('display-none');
    
            return false;
        });

        $("body").on("click",".editComments",function(){
            existingValue = $(this).parent().text();        
            existingValue = existingValue.slice(0,-4);
            existingId = $(this).attr("id");
            fieldName = $(this).attr("data-field");
            
            $(this).parent().parent().prev().removeClass('display-none').attr('id',fieldName);
            $(this).parent().addClass('display-none');
    
            if(fieldName != '' && existingValue != ''){
                $('#'+fieldName).val(existingValue);
            }       
            return false;
        });


    });
        function submitAmendL3Update(updateType, serial){
            var note = ''; 
            var imageName = '';

            if (updateType == 'note') {
                note = $('#note_decription-'+serial).val();
                if(note.trim() == ''){
                    $.growl({message: 'Please update the Notes section before submitting'}, {type: 'warning'});
                    return false;
                }
            }else{ 
                imageName = $("#level3tempimage_note_card-"+serial).attr('src');
                if(typeof(imageName) == 'undefined' || imageName.trim() == ''){  
                    $.growl({message: 'Please update image section before submitting'}, {type: 'warning'});
                    return false;
                }
                imageName = imageName.split('/');
                imageName = imageName[imageName.length - 1];
            }

            var uploadImageObject = [];
            uploadImageObject.data = {};
            uploadImageObject.url =  '/amendnpc/amendL3update';
            uploadImageObject.data['image'] = imageName;
            uploadImageObject.data['crf_number'] = $('#crfNumber').val();
            uploadImageObject.data['updateType'] = updateType;
            uploadImageObject.data['note'] = note;
            uploadImageObject.data['serial'] = serial;
            uploadImageObject.data['functionName'] = 'AmendL3UpdateCallback';
            //console.log(uploadImageObject);
            crudAjaxCall(uploadImageObject);
            return false;
        }
    
    
    
    
        // function SaveAmendCommentsCallBackFunction(response,object){
        //     if(response['status'] == "success"){
        //         $("#"+object.data['column_name']).parent().addClass('display-none');
        //         var buildHtml = '<p>'+
        //                             object.data['comments']+
        //                             ' <a href="javascript:void(0);" class="editComments" id="'+response.data['reviewId']+'">'+
        //                                 'Edit'+
        //                             '</a>'+
        //                         '</p>';
        //         $("#"+object.data['column_name']).parent().next().html(buildHtml).removeClass('display-none');
        //     }
        //     $.growl({message: response['msg']},{type: response['status']});
        //     return false;
        // }
    
    function updateLastSavedComments(savedObject){ 
        if(typeof(savedObject) == 'undefined' || savedObject == null || savedObject == '' || savedObject.length == 0) return false;
        var currRole = $('#roleId').val();
        for(var fld = 0; fld < savedObject.length; fld++){
            var fieldName = savedObject[fld].column_name;		
            if(currRole == 'L3'){
                var fieldValue = savedObject[fld].response;
            }else{
                var fieldValue = savedObject[fld].comments;
            }
            $("#"+fieldName).addClass('display-none');		
            var buildHtml = '<p>'+
                    fieldValue+
                    ' <a href="javascript:void(0);" class="editComments" data-field="'+fieldName+'" id="'+savedObject[fld].id+'">'+
                        'Edit'+
                    '</a>'+
                '</p>';
           $("#"+fieldName).next().html(buildHtml).removeClass('display-none');                
        }	
        $.growl({message: 'Last '+savedObject.length+' saved comments retreived'},{type: 'success'});
        return false;
    }
    
    function SaveAmendNpcSubmitCallBackFunction(response, object){
        if(response['status'] == "success"){        
            $.growl({message: response['msg']},{type: response['status']});
            redirectUrl(response['data'],'/amendnpc/dashboard');
                
        }else{        
            $.growl({message: response['msg']},{type: "warning"});
        }
    }

    function AmendL3UpdateCallbackFunction(response,object)  {    
        if(response['status'] == "success"){        
            $.growl({message: response['msg']},{type: response['status']});
        }else{        
            $.growl({message: response['msg']},{type: "warning"});
        }
        return true;
    }
    
    
    