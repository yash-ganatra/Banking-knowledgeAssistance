@extends('layouts.app')
@section('content')
@php
    $nominee_exists = '';
    $nominee_exists_td = '';
    $nominee_name = '';
    $nominee_name_td = '';
    $nominee_address = '';
    $relatinship_applicant = '';
    $relatinship_applicant_td = '';
    $nominee_dob = '';
    $nominee_dob_td = '';
    $nominee_age = '';
    $nominee_age_td = '';
    $guardian_name = '';
    $guardian_name_td = '';
    $guardian_address = '';
    $name_as_per_passbook = '';
    $display = "";
    $enable = "display-none";
    $is_review = 0;
    $readonly = '';
    $folder = '';
    $disabled = "";
    $account_id = '';
    $nomineeDetails = array();
    
    $page = 5;
@endphp
@if(count($userDetails) > 0)
    @php
        $nomineeDetails = $userDetails['NomineeDetails'];
        $display = "display-none";
        $folder = "attachments";
    @endphp
@endif
@if(Session::get('is_review') == 1)
    @php
        $is_review = 1;
        $enable = "";
        $readonly = "readonly";
        $folder = "markedattachments";
        $disabled = "disabled";
    @endphp
@endif
<div class="pcoded-content1 branch-review">
    <div class="pcoded-inner-content1">
        <!-- Main-body start -->
        <div class="main-body">
            <div class="page-wrapper">
                <div class="">
                    <div class="process-wrap active-step5">
                        @include('bank.breadcrumb',['page'=>$page])
                   </div>
            </div>
            <!-- Page-body start -->
            

            <!-- Term Deposit Nominee -->           
            <div class="page-body">
                @if($accountType == 4)
                    @php
                        $nomineecount = 2;
                    @endphp
                @else
                    @php
                        $nomineecount = 1;
                    @endphp
                @endif
               
                    @for($i = 1; $i <= $nomineecount;$i++)
                       <form method="post" id="addNomineeDetailsForm-{{$i}}">
                        @include('bank.addnomineeapplicant',['nomineeDetails' => $nomineeDetails,'i'=>$i])
                        </form>
                    @endfor 
                
            </div>
            <!-- tERM Deposit Nominee -->

            <div class="row">
                <div class="col-md-12 text-center">
                    <!-- <a href="{{route('addfinancialinfo')}}" class="btn btn-outline-grey mr-3">Back</a> -->
                    @if(Session::get('role') == "11")
                        <a class="btn btn-light btn-outline-grey mr-3"  disabled>Back</a>
                    @else
                        <a onclick="redirectUrl('{{$formId}}','/bank/addfinancialinfo')" class="btn btn-outline-grey mr-3">Back</a>
                    @endif
                    <a href="javascript:void(0)" class="btn btn-primary nomineeDetails" data-id="nomineedetailsnew-{{$i}}" id="{{$formId}}">Save and Continue</a>
                </div>
            </div>
        </div>
    <!-- Page-body end -->
    </div>
</div>

<!-- Modal large-->
<div class="modal fade custom-popup" id="upload_proof" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                <!-- <span aria-hidden="true">&times;</span> -->
            </button>
            <div class="modal-body">
                <div class="custom-popup-heading document_name">
                    <h1>Upload Document</h1>
                </div>
                <div class="upload-blck">
                <input type="file" class="" id="inputImage" name="file" accept="image/*">
                    <div class="upload-blck-inn d-flex justify-content-center align-items-center">
                        <div class="upload-icon">
                            <img src="{{ asset('assets/images/browse-icon.svg') }}">
                        </div>
                        <div class="upload-con">                            
                            <h5>Drag & Drop or <span>Browse</span></h5>  
                        </div>
                    </div>
                </div>                
                <div class="container img-crop-blck image_preview">
                    <div class="row d-flex justify-content-center">
                        <div class="col-md-6">
                            <div class="img-container" >
                                <img id="image" class="preview_image" src="" alt="Crop Picture">
                            </div>
                            <div class="row">
                                <div class="col-md-12 docs-buttons button-page d-flex justify-content-center align-items-center">
                                    <div class="btn-group">
                                        <button type="button" class="rotate-icons" data-method="rotate" data-option="-45" title="Rotate Left">
                                            <span class="docs-tooltip" data-toggle="tooltip" title="$().cropper(&quot;rotate&quot;, -45)">
                                                <span class="fa fa-rotate-left"></span>
                                            </span>
                                        </button>
                                        <button type="button" class="rotate-icons" data-method="rotate" data-option="45" title="Rotate Right">
                                            <span class="docs-tooltip" data-toggle="tooltip" title="$().cropper(&quot;rotate&quot;, 45)">
                                                <span class="fa fa-rotate-right"></span>
                                            </span>
                                        </button>
                                    </div>
                                    <button class="image_crop btn btn-green"> crop </button>
                                </div>      
                            </div>
                        </div>
                        <div class="col-md-6 display-none" id="img-preview-div">
                            <div class="docs-preview clearfix">
                                <img class="crop_image_preview" src="">
                                <!-- <div class="img-preview preview-lg"></div> -->
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-12 text-center mt-3">
                    <button type="button" id="uploadImage" class="btn btn-lblue saveDocument" disabled>Save document</button>
                </div>
            </div>              
        </div>
    </div>
</div>                
@endsection
@push('scripts')
<script  src="{{ asset('custom/js/nominee_details.js') }}"></script>
<script type="text/javascript">
    _globalSchemeDetails = JSON.parse('<?php echo json_encode($schemeDetails); ?>');
    _accountType = '<?php echo Session::get('accountType'); ?>';
    _globaluser_nominee_dob = JSON.parse('<?php echo json_encode($applicantDob); ?>');
    
    $(document).ready(function(){
        disableRefresh();
        disabledMenuItems();

        if ($('.nominee_exists').is(':checked')) {
            $('.nominee_form').removeClass('display-none');
        }

        
        if((_globalSchemeDetails.scheme_code == 'TD276') || (_globalSchemeDetails.scheme_code == 'TD277')){
            
            if(_globaluser_nominee_dob < 18){
                $(".nominee_exists").prop('disabled',true);
            }else{
                $(".nominee_exists").prop('disabled',false);
            }
        }
        // relatinship_applicant
        var disabled = false;
        if('{{$is_review}}' == '1'){
            disabled = true;
        }
        addSelect2('relatinship_applicant','Relationship',disabled);
        addSelect2('relatinship_applicant_guardian','Relationship',disabled);
        addSelect2('relatinship_applicant_td','Relationship',disabled);
        addSelect2('nominee_country','Country',disabled);
        addSelect2('guardian_country','Country',disabled);
        var disabled = false;
        if('{{$is_review}}' == 1){
            disabled = true;
        }
        imageCropper("document_preview_witness1_signaturee");
        imageCropper("document_preview_witness2_signaturee");

        $(".nominee_dob").datepicker({
            clearBtn: true,
            format: "dd-mm-yyyy",
            endDate: "today",
            maxDate: "today",
        }).on('change', function () {
            var date_string = moment(this.value, "DD.MM.YYYY").format("YYYY-MM-DD");
            selectedDate = new Date(date_string);
            today = new Date();
            years = new Date(today - selectedDate)/(24 * 60 * 60 * 1000 * 365.25 );
            var id = $(this).attr("id").split('-')[1];
            if(Math.floor(years) < 18){
                // $('#minor_guardian-'+id).show();
                $('.minor_guardian-'+id).show();
            }else{
                // $('#minor_guardian-'+id).hide();
                $('.minor_guardian-'+id).hide();
                
                $(".GuardianDetailsField").each(function(){
                 var currId = $(this).attr('id').split('-')[1];
                 if (currId == id) {
                    if($(this).attr("type") == "radio")
                    {
                        $(this).prop('checked', false);
                    }

                    if($(this).attr("type") == "text")
                    {
                        $(this).val('');
                        $('#relatinship_applicant_guardian-'+currId).val('').trigger('change')
                        $('#guardian_country-'+currId).val('').trigger('change');

                    }            
                }
                })
            }
            $("#nominee_age-"+id).val(Math.floor(years));
            return false;
        });
        /*$("#nominee_dob_td").datepicker({
            clearBtn: true,
            format: "dd-mm-yyyy",
            endDate: "today",
            maxDate: "today",
        }).on('change', function () {
            var date_string = moment(this.value, "DD.MM.YYYY").format("YYYY-MM-DD");
            selectedDate = new Date(date_string);
            today = new Date();
            years = new Date(today - selectedDate)/(24 * 60 * 60 * 1000 * 365.25 );
            if(Math.floor(years) < 18){
                $('.minor_guardian').show();
            }else{
                $('.minor_guardian').hide();
            }
            $("#nominee_age_td").val(Math.floor(years));
            return false;
        });*/
    });
    function show_nominee_form(id)
    {
        if($("#nominee_exists-"+id).prop('checked') == true){
            // var id = $(this).attr("id").split('-')[1];
            $(".nominee_form-"+id).removeClass('display-none');

            if(id == 2){                
                $(".nominee_form_td").removeClass('display-none');
            }
        }else{
            $(".nominee_form-"+id).addClass('display-none');
            $(".minor_guardian-"+id).hide();
            if(id == 2){
                $(".nominee_form_td").removeClass('display-none');
            }
        }
    }

       /*function show_nominee_form_td()
    {
        if($("#nominee_exists_td").prop('checked') == true){
             $(".nominee_form_td").removeClass('display-none');
        }else{
            $(".nominee_form_td").addClass('display-none');
        }
    }*/
</script>
@endpush