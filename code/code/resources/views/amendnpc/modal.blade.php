                    <div id="Hold" class="modal-review">
                       <!-- Modal content -->
                       <div class="modal-content-review text-center hold_reject_model">
                          <p>Are you sure to put the form on <span class="hold_reject_title">Hold</span>?<br>If Yes, please comment.</p>
                          <!-- <input type="text" class="form-control commentsField mb-3" id="hold_comment"> -->
                          <textarea type="text" class="form-control commentsField mb-3" id="hold_comment" rows="4"></textarea>
                          <button class="btn btn-danger submit_to_bank mr-4 commonBtn" id="hold" >Confirm</button>
                          <button class="btn btn btn-primary hold-no">Cancel</button>
                       </div>
                    </div>
                    <div id="Reject" class="modal-review">
                       <!-- Modal content -->
                       <div class="modal-content-review text-center hold_reject_model">
                          <p>Are you sure to <span class="hold_reject_title">Reject</span> the form?<br>If Yes, please comment.</p>
                          <!-- <input type="text" class="form-control commentsField mb-3" id="reject_comment"> -->
                          <textarea type="text" class="form-control commentsField mb-3" id="reject_comment" rows="4"></textarea>
                          <button class="btn btn2 btn-danger submit_to_bank mr-4 commonBtn" id="reject" >Confirm</button>
                          <button class="btn btn btn-primary reject-no" >Cancel</button>
                       </div>
                    </div>
                    <!-- Modal large-->
                    <div class="modal fade custom-popup" id="upload_note" tabindex="-1" role="dialog">
                        <div class="modal-dialog modal-xl" role="document">
                            <div class="modal-content">
                                <button type="button" class="btn-close position-absolute top-0 end-0 px-2" data-bs-dismiss="modal" aria-label="Close">
                                    <!-- <span aria-hidden="true">&times;</span> -->
                                </button>
                                <div class="modal-body mt-4">
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