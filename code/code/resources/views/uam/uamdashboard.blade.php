@extends('layouts.app')
@section('content')
<style type="text/css">
    body{overflow-y: hidden;}
    #usersTable{width: 100%!important; }
    .table td, .table th{padding: 10px 0px;}
    input.check 
        {
            opacity: 1;
        }
        a.saveuser {
        background-color: #4099ff;
        padding-left: 10%;
        padding-right: 10%;
        padding-top: 7%;
        padding-bottom: 7%;
        color: white;
        }
        
       
        /* div#uamlistl1_length {
        position: relative;
        top: 700%!important;
        }
       .dataTables_scroll {
        margin-top:-3%;
        }
        .uamlistl1{
        background-color: white;
        } */

</style>
<div class="pcoded-content1">
    <div class="pcoded-inner-content1">
        <!-- Main-body start -->
        <div class="main-body">
            <div class="page-wrapper">

            <div class="page-body page-body-top">
                    <div id="nonedisplay" style="display: none;">
                        <div class="row accountsgrid top-blcks">
                            <!-- order-card start -->                        
                            
                                
                            <!-- order-card end -->                                  
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12 filter-icon-main">
                            <a class="filter-icon"><i class="fa fa-filter"></i> Filters</a>
                            <a class="filter-close" style="display: none;"><i class="fa fa-times"></i> Close Filters</a>
                        </div>
                    </div>

                    <div class="row filter mb-3 drop-down-top filtergrid" style="display: none;">
                       
                       
                        <div class="col-md-3">
                        {!! Form::select('users name',$users,null,array('class'=>'form-control users',
                                    'id'=>'users','name'=>'users','placeholder'=>'Select EMPLDAP User')) !!}
                        </div> 
                                               
                    </div>
                  
                <!-- Page-body start -->
                    <div class="card table-top">                                            
                        <div class="card-block table-border-style card-block-padding">
                            <div class="table-responsive">
                                <table class="table" id="uamlistl1">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>HRMSNO</th>                                           
                                            <th>EMP NAME</th>
                                            <th>EMPLDAPUSERID</th>
                                            <th>NORMAL FLAG</th>
                                            <th>PRIORITY FLAG</th>
                                            <th>NR FLAG</th>                                           
                                            <th>ACTION</th>                       
                                        </tr>
                                    </thead>
                                                                        
                                    <tbody class="tabledata">                                        
                                        @foreach($employeeDetails as $data)
                                        <tr>
                                        <td>{{$data->id}}</td>
                                        <td>{{$data->hrmsno}}</td>
                                        <td>{{$data->emp_name}}</td>
                                        <td>{{$data->empldapuserid}}</td>
                                        <td><input type="checkbox" class="form-cotrol check " name="NORMAL_FLAG" id="normal_flag-{{$data->id}}" value="{{$data->normal_flag}}" {{($data->normal_flag == 'Y')? "checked" : ""}} ></td>      
                                        <td><input type="checkbox" class="form-cotrol check" id="priority_flag-{{$data->id}}" name="PRIORITY_FLAG" value="{{ $data->priority_flag }}" {{($data->priority_flag == 'Y')? "checked" : ""}}></td>
                                        <td><input type="checkbox" class="form-cotrol check" id="nr_flag-{{$data->id}}" name="NR_FLAG" value="{{ $data->nr_flag }}" {{($data->nr_flag == 'Y')? "checked" : ""}}></td>
                                        <td><a href="#" class="saveuser" data-id="{{ $data->id }}"  data-value="{{$data->emp_name}}" data-route="{{ route('saveuser', ['id' => $data->id]) }}">Save</a></td>
                                    </tr>
                                        @endforeach                                        
                                    </tbody>
                                    
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
               
                    <!-- Modal -->
                    <div class="modal fade" id="uamdashModal" tabindex="-1" role="dialog" aria-labelledby="uamModal" aria-hidden="true" style="">
                      <div class="modal-dialog" role="document">
                        <div class="modal-content" style="margin-top: 50%;cursor: pointer;">
                          <div class="modal-header">
                            <h5 class="modal-title" id="uamModal"></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                              <!-- <span aria-hidden="true">&times;</span> -->
                            </button>
                          </div>
                          <div class="modal-body" id="uamMsgModal">
                              Please Select Appropriate Button.
                          </div>
                          <div class="modal-footer">
                            <button type="button" class="btn btn-primary" id="activatedUser">Activated</button>
                            <button type="button" class="btn btn-danger" id="permDeactivated" style="margin-right:40%">Delete</button>
                            <button type="button" class="btn btn-primary" id="tempDeactivated">Disabled</button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                          </div>
                        </div>
                      </div>
                    </div>
                <!-- Page-body end -->
            </div>  
        </div>                          
    </div>
</div>
@endsection
@push('scripts')
<script  src="{{ asset('custom/js/uam.js') }}"></script>

<script type="text/javascript">
  $(".filter-icon").click(function(){
      $(".filtergrid").show();
      $(".filter-icon").hide();
      $(".filter-close").show();
    });
    $(".filter-close").click(function(){
      $(".filtergrid").hide();
      $(".filter-close").hide();
      $(".filter-icon").show();
    });
</script>


<script type="text/javascript">
    $(document).ready(function(){
        $('#uamlistl1').DataTable({
    "lengthMenu": [[10, 50, 100, -1], [10, 50, 100, "All"]],
    "scrollCollapse": true,
    "scroller": true,
    "scrollY": 500,
    "dom": '<"#datatable_search"f>t<"bottom"<"entries"li>p><"clear">',
    // "dom": 'Rfrtlip', // Note the use of "dom" instead of "sDom"    
     });
    $('#uamlistl1_filter').remove();
       
     addSelect2('users','EMPLDAP User Id');
    //addSelect2('applicationStatus','Application Status');
    var tableRemainingHeight = $(".header-navbar").height()+$(".filtergrid").height()+200;
   
  
     $("body").on("change",".users",function(){
            getUser('/uam/getuamuserslist','usersTable',tableRemainingHeight);
        });
      getUser('/uam/getuamuserslist','usersTable',tableRemainingHeight);
         
});


/*filter by username*/
$('.username').on('change', function(e) {
    e.preventDefault();        
      
    var selectedvalue=$('.username').val();
            
    $.ajax({
        type: 'POST',
        url: '{{ route("L1Dashboard") }}', 
        data: {
            value: selectedvalue
        },
        success: function(response) {
            var trHTML = '';
            $.each(response, function(i, item) {
            var saveUserRoute = '/saveuser/' + item.id; 
            trHTML += '<tr><td>' +
                item.id + '</td><td>' +
                item.hrmsno + '</td><td>' +
                item.emp_name + '</td><td>' +
                item.empldapuserid + '</td><td>' +
                '<input type="checkbox" class="form-cotrol check" name="NORMAL_FLAG" id="normal_flag-' + item.id + '" value="' + item.normal_flag + '" ' + (item.normal_flag == 'Y' ? 'checked' : '') + '></td><td>' +
                '<input type="checkbox" class="form-cotrol check" id="priority_flag-' + item.id + '" name="PRIORITY_FLAG" value="' + item.priority_flag + '" ' + (item.priority_flag == 'Y' ? 'checked' : '') + '></td><td>' +
                '<input type="checkbox" class="form-cotrol check" id="nr_flag-' + item.id + '" name="NR_FLAG" value="' + item.nr_flag + '" ' + (item.nr_flag == 'Y' ? 'checked' : '') + '></td><td>' +
                '<a href="#" class="saveuser" data-id="' + item.id + '" data-route="' + saveUserRoute + '">Save</a></td></tr>';
             });   
            $('#uamlistl1 tbody').html(trHTML);
            $('.bottom').css('display','none');          
            
        }              
    });
});

/*Filter by userId*/
$('.users').on('change', function(e) {
    e.preventDefault();     
    var selectedvalue=$('.users').val();
            
    $.ajax({
        type: 'POST',
        url: '{{ route("L1Dashboard") }}', 
        data: {
            value: selectedvalue
        },
        success: function(response) {
        var trHTML = '';
        $.each(response, function(i, item) {
        var saveUserRoute = '/saveuser/' + item.id; 
        trHTML += '<tr><td>' +
        item.id + '</td><td>' +
        item.hrmsno + '</td><td>' +
        item.emp_name + '</td><td>' +
        item.empldapuserid + '</td><td>' +
        '<input type="checkbox" class="form-cotrol check" name="NORMAL_FLAG" id="normal_flag-' + item.id + '" value="' + item.normal_flag + '" ' + (item.normal_flag == 'Y' ? 'checked' : '') + '></td><td>' +
        '<input type="checkbox" class="form-cotrol check" id="priority_flag-' + item.id + '" name="PRIORITY_FLAG" value="' + item.priority_flag + '" ' + (item.priority_flag == 'Y' ? 'checked' : '') + '></td><td>' +
        '<input type="checkbox" class="form-cotrol check" id="nr_flag-' + item.id + '" name="NR_FLAG" value="' + item.nr_flag + '" ' + (item.nr_flag == 'Y' ? 'checked' : '') + '></td><td>' +
        '<a href="#" class="saveuser"  data-value="'+item.emp_name+'" data-id="' + item.id + '" data-route="' + saveUserRoute + '">Save</a></td></tr>';
        });

        $('#uamlistl1 tbody').html(trHTML);
        }               
    });
});

/*save & update flag*/
 $(document).on('click', '.saveuser', function(event) {            

event.preventDefault();
var id = $(this).data('id');       
var nor = 'N';
if ($('#normal_flag-' + id).prop('checked')) {
    nor = 'Y';
}
var priority = 'N';
if ($('#priority_flag-' + id).prop('checked')) {
    priority = 'Y';
}
var nr = 'N';
if ($('#nr_flag-' + id).prop('checked')) {
    nr = 'Y';
}
var name=$(this).attr('data-value');

if(nor=='N'&& priority=='N' && nr=='N')
{
    $.growl({message: "Please check any priority Flag."},{type: "warning"});
    return false;
}

$.ajax({
    type: 'POST',
    url: '{{ route("saveuser") }}',
    data: {
        id: id,
        username:name,
        normal_flag: nor,
        priority_flag: priority,
        nr_flag: nr,
    },
    success: function (response) {
        $.growl({ message: name + ' have been updated successfully.' },{type: 'success' });

    },
});
 });

</script>

@endpush

