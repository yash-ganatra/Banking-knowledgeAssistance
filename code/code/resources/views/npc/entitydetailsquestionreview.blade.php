@php 
    $image_mask_blur = "uploaded-img-ovd";
    $def_blur_image = "style=filter:blur(30px);";
@endphp
 <div class="tabs">
    <ul id="reviewcod-tabs-nav" class="nav nav-tabs tabs tabs-default nav-tabs-tb">
            <li class="nav-item">
                    <a href="#reviewcod-tab"  class="nav-link">Entity Review</a>
            </li>
    </ul>
    <div id="reviewcod-tabs-content-cust" class="reviewcod-tabs-content-cust">
                <div class="card" id="entity_clearance">
                    <div class="card-block">
                        <div class="col-lg-12">
                                @php
                                    $count=0;
                                @endphp
                                @foreach($clearanceDetails as $clearancedetails)
                                <?php
                                $count++;
                                $getClearanceDetails = DB::table('CLEARANCE')
                                                         ->where('FORM_ID',$accountDetails['id'])
                                                         ->where('ACTIVE', 1)
                                                         ->where('CLEARANCE_ID',$clearancedetails->id)
                                                         ->get()->toArray();
                                $getClearanceDetails = (array) current ($getClearanceDetails);
                                $folder = "attachments";
                                $formId = $accountDetails['id'];
                                $hideorshow = '';
                                if(in_array($roleId,[5,6,8])){
                                    $hideorshow = 'display-none';
                                }


                                if(!empty($getClearanceDetails['active'])){
                                    if($getClearanceDetails['active'] == 1){
                                        $clearance_img = $getClearanceDetails['clearance_img'];
                                        $checked = 'checked';
                                        $display = 'display-none';
                                    }
                                }else{
                                    $checked = '';
                                    $clearance_img = '';
                                    $display = '';

                                }
                                    ?>
                                    
                                
                                <!-- Row start -->
                                <div class="proofs-blck">
                                    <!-- <input type="hidden" id="formId" value=""> -->
                                    <div class="row">
                                       <div class="custom-col-review proof-of-identity col-md-8">

                                                <div class="details-custcol">
                                                    <div class="details-custcol-row ">
                                                        <div class="details-custcol-row-top d-flex">
                                                            <div class="detaisl-left d-flex align-items-center">
                                                               <h4>{{$count}}. {{$clearancedetails->description}}</h4>
                                                            </div>
                                                            <div class="detaisl-right">
                                                                <div class=" d-flex flex-row">
                                                                    <div class="switch-blck {{$hideorshow}}">
                                                                        <div class="toggleWrapper">
                                                                            <input type="checkbox" name= "{{$clearancedetails->blade_id}}" data-id="{{$clearancedetails->id}}" class="mobileToggle entityreview  {{$clearancedetails->blade_id}}" id="{{$clearancedetails->blade_id.'_toggle'}}"{{$checked}}>
                                                                            <label for="{{$clearancedetails->blade_id.'_toggle'}}" ></label>
                                                                        </div>
                                                                    </div>
                                                                    <div class="comments-blck {{$display}}" id="{{$clearancedetails->blade_id}}">

                                                                        <input type="text" class="form-control commentsField" id="{{$clearancedetails->blade_id.'_clearance'}}">
                                                                        <i title="save" class="fa fa-floppy-o saveComments {{$clearancedetails->blade_id.'_clearance'}}"></i>
                                                                    </div>
                                                                    <div id="{{$clearancedetails->blade_id.'_reviewComments'}}">
                                                                    <div class="details-custcol-row-bootm d-flex align-items-center {{$clearancedetails->blade_id}}"></div></div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>


                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <div class="form-group" id="pf_type_proof-1">
                                                                <div class="detaisl-left align-content-center ">
                                                                    <label class="">UPLOAD {{strtoupper($clearancedetails->blade_id.' IMAGE')}}</label>
                                                                    <span class="display-none">
                                                                        <i class="fa fa-check"></i>
                                                                    </span>
                                                                </div>
                                                            <div class="add-document d-flex align-items-center justify-content-around" id="{{$clearancedetails->blade_id.'_img'}}">
                                                                @if(isset($clearance_img) && ($clearance_img != ''))
                                                                     <div id="{{$clearancedetails->blade_id.'_clearance_div'}}" data-value="{{$formId}}">



                                        @if($clearance_img != '')
                                                            @if(substr(strtolower($clearance_img),-3) == 'pdf')
                                                            <div class="upload-delete {{$hideorshow}}">
                                                                    <button type="button" class="delete-icon btn btn-danger btn-icon waves-effect waves-light deleteImage pdf" data-id="{{$clearancedetails->blade_id}}">
                                                                        <i class="fa fa-trash" aria-hidden="true"></i>
                                                                    </button>
                                                                </div>
                                                                <i class="fa fa-file-pdf-o" style="font-size:48px;color:red"></i>
                                                                    <a  class="uploaded_image  {{$clearancedetails->blade_id.'_pdf'}}" name="{{$clearancedetails->blade_id}}" id="{{$clearancedetails->blade_id.'_pdf'}}" href="{{URL::to('/images'.$folder.'/'.$formId.'/'.$clearance_img)}}" target="_blank">{{$clearance_img}}</a>
                                                            @else
                                                                <div class="upload-delete {{$hideorshow}}">
                                                                <button type="button" class="delete-icon btn btn-danger btn-icon waves-effect waves-light deleteImage">
                                                                    <i class="fa fa-trash" aria-hidden="true"></i>
                                                                </button>
                                                            </div>
                                                            <div class="{{$image_mask_blur}}" {{$def_blur_image}}>
                                                                <img class="uploaded_image entityreview {{$clearancedetails->blade_id.'_img'}}" name="{{$clearancedetails->blade_id}}" id="{{$clearancedetails->blade_id.'_img'}}" src="{{URL::to('/images'.$folder.'/'.$formId.'/'.$clearance_img)}}">
                                                            </div>
                                                            @endif

                                                    </div>
                                                    @endif
                                        @endif
                                                    @if(isset($clearance_img) && ($clearance_img != ''))
                                                        <div class="add-document-btn adb-btn-inn display-none">
                                                    @else
                                                        <div class="add-document-btn adb-btn-inn">
                                                    @endif

                                                            <button type="button" id="upload_clearance_img" class="btn btn-outline-grey waves-effect upload_document" data-toggle="modal" data-value="{{$formId}}"
                                                            data-id="{{$clearancedetails->blade_id.'_img'}}" data-name="{{$clearancedetails->blade_id.'_img'}}"  data-document="Image" data-doc="pdf" data-target="#upload_proof">
                                                            <span class="adb-icon">
                                                                <i class="fa fa-plus-circle"></i>
                                                            </span>
                                                    Add {{strtoupper($clearancedetails->blade_id.'_Image')}}
                                                            </button>
                                                        </div>
                                                </div>
                                                    <input type="text" style="opacity:0" name="{{$clearancedetails->blade_id.'_img'}}" id="{{$clearancedetails->blade_id.'_img'}}">
                                                </div>

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
`
                                @endforeach
                                                    
                                                    <!-- End of list -->
                            </div>
                        </div>
                    </div>
                </div>

    
@if($roleId != '8')
<div class="modal fade custom-popup" id="upload_proof" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <button type="button" class="btn-close position-absolute top-0 end-0 px-2" data-bs-dismiss="modal" aria-label="Close">
                <!-- <span aria-hidden="true">&times;</span> -->
            </button>
            <div class="modal-body mt-4">
                <div class="custom-popup-heading document_name">
                    <h1>Upload Clearance</h1>
                </div>
                <div class="upload-blck">
                <input type="file" class="" id="inputImage" name="file" accept="image/*,.pdf">
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
            
@endif