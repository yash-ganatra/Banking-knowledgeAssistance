@extends('layouts.app')
@section('content')
<style type="text/css">
    body{overflow-y: hidden;}
    #uamdeletelist{width: 100%!important; }
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

        .page-title-box {
        margin-bottom: -19px;
        }
        .btn-review {
            color: #646c74;
            text-decoration: none; 
        }

        .btn-review:hover {
            color: blue;
        }

        th.column-text-align-center{
            text-align:center!important;
        }
        td.column-text-align-center{
            text-align:center!important;
        }
        td.column-text-align-right{
            text-align: right!important;
        }
        th.column-text-align-right{
            text-align: right!important;
        }

        .card.widget-flat{
            --ct-card-spacer-y:0.5rem;
        }
        button.btn.empstatus {
        padding-left: 1px;
        }
        a.btn.btn-yellow.waves-effect.waves-light {
        background-color: #0d6efd;
        border: 1pxs solid black;
        color: #fff;
        }

        .reportbtn{
            border-radius: 5px !important;
            padding: 3px 8px !important; 
            font-size: 14px !important;
        }



</style>
<div class="pcoded-content1">
    <div class="pcoded-inner-content1">
        <!-- Main-body start -->
        <div class="main-body">
            <div class="page-wrapper">

                <div class="page-body page-body-top mb-3">
                    <div class="row">
                      <div class="col-md-4 filter-icon-main filter-icon-main-2 d-flex align-items-center">
                          <a class="filter-icon"><i class="fa fa-filter"></i> Filters</a>
                          <a class="filter-close" style="display: none;"><i class="fa fa-times"></i> Close Filters</a>

                        <form action="{{ route('uamundeletereport') }}" method="GET">
                            <button type="submit" class="btn btn-outline-primary reportbtn">Report</button>
                        </form>
                        </div>

                      <div class="col-md-4">
                        <div class=" filter drop-down-top filtergrid" style="display: none;">
                             {!! Form::select('users name',$users,null,array('class'=>'form-control deleteusers',
                                    'id'=>'users','name'=>'users','placeholder'=>'Select EMPLDAP UserId')) !!}
                        </div>
                      </div>
                    </div>
                  </div>
                <!-- Page-body start -->
                    <div class="card table-top">                                            
                        <div class="card-block table-border-style card-block-padding">
                            <div class="table-responsive" id="deleteuserdata">
                                <table class="table" id="uamdeletelist">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>HRMSNO</th>
                                            <th>EMPSOL</th>
                                            <th>EMP NAME</th>
                                            <th>EMPLDAPUSERID</th>
                                            <th>EMPBUSINESSUNIT</th>
                                            <th>EMPLOCATION</th>
                                            <th>EMPBRANCH</th>
                                            <th>ROLE</th>
                                            <th>EMPSTATUS</th>
                                        </tr>
                                    </thead>
                                    <tbody class="tabledata"> 
                                        @if(empty($employeeDetails))
                                        <tr>
                                            <td colspan="10" class="text-center">No Data Available.</td>
                                        </tr>
                                       @else                            
                                        @foreach($employeeDetails as $data)
                                        @php
                                        if($data->empstatus == 'D'){
                                            $button = '';
                                            $color = 'blue';
                                            $border = '50%';
                                            $bgcolor = 'red';
                                            $height = '16px';
                                            $width = '16px';
                                        } else {
                                            $color = 'black';
                                            $button = '';
                                            $border = '50%';
                                            $bgcolor = 'gray';
                                            $height = '16px';
                                            $width = '16px';
                                        }
                                    @endphp
                                        <tr>
                                        <td>{{$data->id}}</td>
                                        <td>{{$data->hrmsno}}</td>
                                        <td>{{$data->empsol}}</td>
                                        <td>{{$data->emp_name}}</td>
                                        <td>{{$data->empldapuserid}}</td>
                                        <td>{{$data->empbusinessunit}}</td>
                                        <td>{{$data->emplocation}}</td>
                                        <td>{{$data->empbranch}}</td>
                                        <td>{{$data->role}}</td>
                                        <td> 
                                            <button class="btn empstatus" type="button" data-bs-toggle="modal" data-id="{{$data->id}}" data-bs-target="#uamdashModal" style="color:{{$color}};border-radius:{{$border}};background-color:{{$bgcolor}};height:{{$height}};width:{{$width}};">{{$button}}</button>
                                        </td>  
                                    </tr>
                                        @endforeach 
                                        @endif                                       
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
               
                      <!-- Modal -->
                    <div class="modal fade" id="uamdashModal" tabindex="-1" role="dialog" aria-labelledby="uamModal" aria-hidden="true" style="">
                        <div class="modal-dialog" role="document">
                          <div class="modal-content" style="margin-top: 50%;cursor: pointer;">
                            <div class="modal-header">
                              <h5 class="modal-title" id="uamModal">Un-Delete User</h5>
                              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                                <!-- <span aria-hidden="true">&times;</span> -->
                              </button>
                            </div>
                            <div class="modal-body" id="uamMsgModal">
                                Please Select Appropriate Button.
                            </div>
                            <div class="modal-footer">
                              <button type="button" class="btn btn-primary" id="undelteUser">Un-Delete</button>
                              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
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
<script src="{{ asset('custom/js/uam.js') }}"></script>
<script src="{{ asset('custom/js/app.js') }}"></script>
<script src="{{ asset('custom/js/bank.js') }}"></script>

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

    $(document).ready(function(){

        $('#uamdeletelist').dataTable({
            scrollX: true,
            "dom": 't<"bottom"<"entries"li>p><"clear">',
            paging: true,
            scrollY: '350px',
            aLengthMenu: [
                [10, 25, 50, 100, -1],
                [10, 25, 50, 100, "All"]
            ],
        });
        $('.deleteusers').select2();
    });
</script>
@endpush

