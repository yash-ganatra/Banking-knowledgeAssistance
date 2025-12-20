$(document).ready(function(){
    var messageTypeObj = $('#addtemplateDocumentForm').validate({
        // ignore: "",    // initialize the plugin
        rules: {
            message_type: {
                required: true,
            },
            role: {
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
            role: {
                required: "Select Role"
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

    $("body").on("click",".edit_template",function(){
        // var url = document.URL;
        // var baseUrl = url.substr(0, url.lastIndexOf("/"));
        var Id = $(this).attr("id");
        setTimeout(function(){
            redirectUrl(Id,'/admin/edittemplate');
        }, 1000);
        // var encodedParams =  $.base64.encode($(this).attr("id"));
        // var key = $('meta[name="cookie"]').attr('content').split('.')[2];
        // var encryptedData = encrypt(encodedParams,key);
        // var form = $('<form action="' + baseUrl + '/edittemplate" method="post">' +
        //                 '<input type="text" name="encodedString" value="' + encryptedData + '" />' +
        //             '</form>');
        // $('body').append(form);
        // form.submit();
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
        templateObject['url'] = '/admin/savetemplate';
        templateObject.data['is_active'] = $("#active").val();
        
        // var inputRegex=/^[a-zA-Z0-9 .,\@\-\'\/\\()_{}/\[\].\n\r]*$/;
        var inputRegex=/^[a-zA-Z0-9 .,!\@&\-:\'\/\\()_{}/\[\].\n\r//\//\/]*$/;
        if (!$("#templatemessage").val().match(inputRegex)) {
            $.growl({message: "Comments with following [ /:&.,(){}[]_ ] characters are allowed. <br>Other special characters are not permitted due to Security reasons."},{type: "warning"});
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
        templateObject.data['functionName'] = 'saveTemplateDetailsCallBack';
        //getting the data from here
        crudAjaxCall(templateObject);
    });

});


/*function gettemplates(tableName)
{    
    var tableObject = [];
    tableObject.data = {};
    var url = document.URL;
    var baseUrl = url.substr(0, url.lastIndexOf("/"));
    tableObject.data['table'] = tableName;
    tableObject.url =  baseUrl+'/gettemplates';
    
    datatableAjaxCall(tableObject);
    return false;
}*/


/*function datatableAjaxCall(tableObject,sort_idx=0,sort_type='asc')
{
    if ($.fn.DataTable.isDataTable( '#'+tableObject.data['table'] ) ) {
      $('#'+tableObject.data['table']).dataTable().fnDestroy();
    }
    var tableRemainingHeight = $(".header-navbar").height()+208;
    var documentHeight = $(document).height();
    $("#"+tableObject.data['table']).DataTable({
        processing: true,
        serverSide: true,
        "order":[[sort_idx,sort_type]],
        "scrollX": true,
        "scrollY": documentHeight-tableRemainingHeight,
        "iDisplayLength": 10,
        "language": { search: "", searchPlaceholder: "Search"  },
        "dom": '<"#datatable_search"f>t<"bottom"<"entries"li>p><"clear">',      
        "ajax":{
            "url": tableObject.url,
            "dataType": "json",
            "type": "POST",
            "data":{data: tableObject.data}
        },
        'columnDefs': [{
                'targets': [-1],
                'searchable': false,
                'orderable': false,
        }],
        drawCallback: function () {
            $('#'+tableObject.data['table']+'_filter input').unbind();
            $('#'+tableObject.data['table']+'_filter input').bind('keyup', function(e) {
                if(e.keyCode == 13) {
                    $("#"+tableObject.data['table']).DataTable().search(this.value).draw();
                    $(".dataTables_filter:eq(0)").find('input').after("<button type=button id=remove_search>x</button>");
                }
            });

            if($('#'+tableObject.data['table']+'_filter input').val() == ''){
                $("#datatable_search").css("display", "none");    
            }

            $('#'+tableObject.data['table']+'_length select').removeClass();
            $('#'+tableObject.data['table']+'_length select').addClass("select-css");

            $("#remove_search").click(function(e){
                $(".dataTables_filter").val('');
                $("#"+tableObject.data['table']).DataTable().search('').draw();
                $(this).hide();
            });

            $('body').keydown(function(e) {
                if (e.keyCode == 27) {
                    $("#datatable_search").find('input').val('');
                    $("#"+tableObject.data['table']).DataTable().search('').draw();
                    $("#datatable_search").css("display", "none");
                }
            });
            
            //adding slimScroll
            var tableHeight = documentHeight-tableRemainingHeight;
            $('.dataTables_scrollBody').slimScroll({
                height: tableHeight,
                position: 'left',
                opacity: 1,
                color: '#67aefe',
                wheelStep: 1500,
                axis: 'both',
            });
            $('[data-toggle="tooltip"]').tooltip();
        },
    });
}*/