
function getModeReport(url,table,tableRemainingHeight)
{
    if(typeof($('#sentDate').val()) != 'undefined'){
        var sentDateRange = $('#sentDate').val();
        var sentDates = sentDateRange.split(" to ");
    }

    var tableObject = [];
    tableObject.data = {};
    tableObject.data['aofnumber'] = $("#aofnumber").val();
    if($("#applicationStatus").val() != 'undefined')
    {
        tableObject.data['status'] = $("#applicationStatus").val();
    }
    tableObject.data['customer_type'] = $('#customerType').val();
    if(typeof($('#sentDate').val()) != 'undefined'){
        tableObject.data['startDate'] = sentDates[0];
        tableObject.data['endDate'] = sentDates[1];
    }
    tableObject.data['table'] = table;
    tableObject.url =  url;

    dataTableAjaxCall(tableObject,tableRemainingHeight);
    return false;
}


function getDiscrepancyReport(url,table,tableRemainingHeight)
{
    
    if(typeof($('#sentDate').val()) != 'undefined'){
        var sentDateRange = $('#sentDate').val();
        var sentDates = sentDateRange.split(" to ");
    }
    var tableObject = [];
    tableObject.data = {};
    tableObject.data['aofnumber'] = $("#aofnumber").val();
    if($("#applicationStatus").val() != 'undefined')
    {
        tableObject.data['status'] = $("#applicationStatus").val();
    }
    tableObject.data['customer_type'] = $('#customerType').val();
    if(typeof($('#sentDate').val()) != 'undefined'){
        tableObject.data['startDate'] = sentDates[0];
        tableObject.data['endDate'] = sentDates[1];
    }
    tableObject.data['table'] = table;
    tableObject.url =  url;

    dataTableAjaxCall(tableObject,tableRemainingHeight);
    return false;
}

function dataTableAjaxCall(tableObject,tableRemainingHeight,sort_idx=0,sort_type='desc', iDisplayLength=10)
{
    sort_idx = sortDashboardByRole(tableObject.url);
    
    var baseUrl = $('meta[name="base_url"]').attr('content');
    if ($.fn.DataTable.isDataTable( '#'+tableObject.data['table'] ) ) {
      $('#'+tableObject.data['table']).dataTable().fnDestroy();
    }
    var documentHeight = $(document).height();
    $("#"+tableObject.data['table']).DataTable({
        processing: true,
        // serverSide: true,
        "order":[],
        "scrollX": true,
        "scrollY": true,
        "lengthMenu": [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]],
        "iDisplayLength": iDisplayLength,
        "language": { search: "", searchPlaceholder: "Search"  },
        "dom": '<"#datatable_search"f>t<"bottom"<"entries"li>p><"clear">',
        // columnDefs: [
        //     {
        //         targets: [13],  
        //         visible: false   
        //     }
        // ],
        buttons: [{
            extend : 'excel',
            text : 'Export to Excel',
            exportOptions : {
                modifier : {
                    // DataTables core,
                    selected: true,
                    //  columns: ':visible', 
                    //order : 'index',  // 'current', 'applied', 'index',  'original'
                    page : 'all',      // 'all',     'current'
                    search : 'none'     // 'none',    'applied', 'removed'
                }
            }
        }],
        "ajax":{
            "url": baseUrl+tableObject.url,
            "dataType": "json",
            "type": "POST",
            "data":{data: tableObject.data}
        },
        'columnDefs': [{
                'targets': [-1],
                'searchable': false,
                'orderable': false,
        }],
        //Code for Fixing the additional row getting in data table
        "initComplete": function(settings, json) {
            $('.dataTables_scrollBody thead tr').css({visibility:'collapse'});

            if(json.status == 'fail'){
                $.growl({message:json.msg},{type:'warning'});
                return false;
            }
        },
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
            addSlimScroll('dataTables_scrollBody',documentHeight-tableRemainingHeight);
            $.fn.dataTable.tables( { visible: true, api: true } ).columns.adjust();
            $('[data-toggle="tooltip"]').tooltip();
        },
    });
}


