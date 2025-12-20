@extends('layouts.app')
@section('content')
@php
$is_review = 0;
$disabled = '';
$segment_list = '';

@endphp
@if(Session::get('is_review') == 1)
    @php
        $is_review = 1; 
        $disabled = 'disabled';
        $status = $accountDetails['application_status'];
    @endphp
@endif
<style type="text/css">
    .toggleWrapper input.mobileToggle:disabled + label:before{
      background-color: #e2e2e2;
    }
    /*.toggleWrapper input.mobileToggle:disabled + label:after{
      background-color: #f20000;
    }*/
    .toggleWrapper input.mobileToggle:disabled{
      background-color: #f20000;
    }
    .display-none-br-submit-loader{
        display: none;
    }
     a{text-decoration: none!important;}
</style>

@include('bank.form')
    
<div class="modal fade" id="Username-blck" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">User Authentication</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                </button>
            </div>
             <div class="br_submit_loader display-none-br-submit-loader">
                  <div class="br_submit_loader__element"></div>
                </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>User Name</label>
                    <input type="text" id="submission_user_name" class="form-control" value="{{ ucfirst( Session::get('username'))}}" readonly>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" id="submission_user_password" class="form-control" id="password" name="password" value="" autocomplete="false" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default waves-effect" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary waves-effect waves-light submit_to_npc" id="{{$formId}}">Submit</button>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
    <script  src="{{ asset('custom/js/submission.js') }}"></script>
    <script  src="{{ asset('assets/js/crypto-js.js') }}"></script>
    <script src="{{ asset('custom/js/util.js') }}"></script>
    <script type="text/javascript">

        var type = '';       
        var _customerType = JSON.parse('<?php echo json_encode($customer_type); ?>'); 
        var _flow_Type = JSON.parse('<?php echo json_encode($accountDetails['flow_type']); ?>');
        if('{{$is_review}}' == 1){
            type = true;
            addSelect2('segment_list','Segment List',type);
            addSelect2('lead_generated','Source',type);
            addSelect2('dist_from_branch','Distance from Branch',type);
            addSelect2('customer_meeting_location','Customer Meeting Location',type);
            addSelect2('reason_for_accnt_opening','Reason for account opening',type);
            $('.meeting_date').prop('disabled',true);

        }else{
            $(".lead_generated").select2({placeholder: "Source"});
            $(".dist_from_branch").select2({placeholder: "Distance from Branch"});
            $(".customer_meeting_location").select2({placeholder: "Customer Meeting Location"});
            $(".reason_for_accnt_opening").select2({placeholder: "Reason for account opening"});
            $(".segment_list").select2({placeholder: "Segment List"});
        }

        if(_flow_Type == 'ETB'){
        
            $('#segment_list').prop('disabled',true); 
        }else{
            $('#segment_list').prop('disabled',false); 
        }
        

        _globalSchemeDetails = JSON.parse('<?php echo json_encode($schemeDetails); ?>');
        $(document).ready(function(){
            disableRefresh();
            disabledMenuItems();
            
            $(".declaration").change(function(event){
                if ($('.declaration:checked').length == $('.declaration').length) {
                    $('#submission_modal_button').removeClass('disabled');
                }else{
                    $('#submission_modal_button').addClass('disabled');
                }
            }); 

            // if(_globalSchemeDetails.is_emd == 'Y'){
            //     $('#emd_row_submission').removeClass('display-none');
            // }else{
            //     $('#emd_row_submission').addClass('display-none');
            // }         
        });
        
         $(document).on("keypress", "input", function(e){
        if(e.which == 13){
            $('.submit_to_npc').click();
        }
    });
    </script>
@endpush