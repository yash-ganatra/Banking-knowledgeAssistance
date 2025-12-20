$(document).ready(function(){
    var messageTypeObj = $('#addtemplateDocumentForm').validate({
        // ignore: "",    // initialize the plugin
        rules: {
            message_type: {
                required: true,
            },           
            activity_code: {
                required: true,
            },
            activity: {
                // required: true
               required: true,
              
            },
            function_name: {
                required: true,
            },
            is_active: {
                // required: true
               required: true,

            },
            subject:{
               required: true,

            },
            message: {
                required: true,
                /*required: function(element){
                    return $("#applicantId-"+element.id.split('-')[1]).attr('customertype') != "etb";
                },*/
            },

        },
        messages: {

            message_type: {
                required: "Select Message Type"
            },            
            activity_code: {
                required: "Please Enter Activity Code"
            },
            activity: {
                // required: true
              required: "Please Enter Activity"
              
            },
            function_name: {
               required: "Please Enter Function Name"
            },
            is_active: {
                required: "Select Active or Not"
            },
            subject:{
               required: "Please Enter Subject"

            },
            message: {
               required: "Please Enter Message"
            },
        },
        errorPlacement: function( error, element ) {
            if($(element).attr('type') == 'text'){
                error.insertAfter( element );
            }else if($(element).attr('type') == 'textarea'){
               error.insertAfter( element );
            }else if($(element).attr('type') == 'hidden'){
                error.insertAfter( element );
            }else{
                error.insertAfter( element.next() );
            }
        },

    });
    $("body").on("change","#message_type", function() {
        if($(this).val() == "email")
        {
            $(".subject").removeClass("d-none");
        }else{
            $(".subject").addClass("d-none");
        }
        return false;
    });

    $('.edit_template').on('click', function(){
        var url = document.URL;
        var baseUrl = url.substr(0, url.lastIndexOf("/"));
        var encodedParams =  $.base64.encode($(this).attr("id"));
        var key = $('meta[name="cookie"]').attr('content').split('.')[2];
        var encryptedData = encrypt(encodedParams,key);
        var form = $('<form action="' + baseUrl + '/edittemplate" method="post">' +
                        '<input type="text" name="encodedString" value="' + encryptedData + '" />' +
                    '</form>');
        $('body').append(form);
        form.submit();
        return false;
    });

    $('body').on('click','#saveTemplate', function() {

        if($('#message_type').val() == "sms")
        {
            $("#subject").val('sms');
        }

        if($('#templatemessage').val() == ' '){
            $.growl({message: "Please Enter Message"},{type: "warning"});
            return false;
        }
        
        if ((!messageTypeObj.form()) || ($('#templatemessage').val().length > 2000 )) { // Not Valid
            $.growl({message: "Please Update the form with Message not more than 2000 characters"},{type: "warning"});
            return false;
        }else{

        }
        var templateObject = [];
        templateObject.data = {};
        templateObject['url'] = '/channelid/savetemplate';
        templateObject.data['is_active'] = $("#active").val();
        
        var inputRegex=/^[a-zA-Z0-9 .,\/\\()_{}/\[\].\n\r]*$/;
        if (!$("#templatemessage").val().match(inputRegex)) {
            $.growl({message: "Comments with following [ .,(){}[]_ ] characters are allowed. <br>Other special characters are not permitted due to Security reasons."},{type: "warning"});
            return false;
        }

        //var messageText = $("#message").val().replace('_NL_','<br>');
        $('#role').val('1');
        //$('#activity').val($('#activity_code').val());

        $(".templateDetailsAddField").each(function() {
            if($(this).val() != ''){
                templateObject.data[$(this).attr('name').toUpperCase()] = $(this).val();    
            }else{
                return false;
            }
        });
        if(typeof($(this).attr("templateid")) != "undefined"){
            templateObject.data['id'] = $(this).attr("templateid");
        }
        templateObject.data['functionName'] = 'saveChannelIDTemplateDetailsCallBack';
        //getting the data from here
        crudAjaxCall(templateObject);
    });

});