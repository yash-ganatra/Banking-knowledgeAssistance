@php
	use Carbon\Carbon;
	$currUrlPath = parse_url(\Request::getRequestUri(), PHP_URL_PATH);
	$pathComponents = explode("/", trim($currUrlPath, "/"));                                                  				
	$appName = $pathComponents[0];
	if($roleId != 8){
		$readOnlyView = true;
	}else{
		$readOnlyView = false; 
	}
    
@endphp
<style>
	.readOnlyNote {
		border-width: 0px;
		background-color: #d3d3d396;
	}
     a{text-decoration: none!important;}
</style>


<div class="card" id="notes-details">
    <div class="card-block">
        <h4 class="sub-title">L3 Updates</h4>
        <!-- Row start -->
        <div class="row">
            @for($i = 1; $i <= 5; $i++)
				@php
					if(isset($level3notesimages[$i-1]) && $level3notesimages[$i-1]->dyna_text != ''){
						$readOnly = 'readonly';
						$displayType = 'display-none';
						$noteValue = trim($level3notesimages[$i-1]->dyna_text);
						$noteClass = 'readOnlyNote';
						$noteDate = Carbon::parse($level3notesimages[$i-1]->created_at)->format('d-M-Y H:m');
					}else{
						$readOnly = '';
						$noteValue = '';
						$displayType = '';
						$noteClass = '';
						$noteDate = '';
					}
					if($readOnlyView){
						$noteClass = 'readOnlyNote';
						$displayType = 'display-none';
					}	
				@endphp  
            <div class="col">
                <div class="form-group" id="note_proof-{{$i}}">
                    <label for="note_decription-{{$i}}" class="pr-3">NOTE {{$i}}</label>
                    <textarea rows="3" {{$readOnly}} type="textarea" name="note_decription-{{$i}}" id="note_decription-{{$i}}"   class="{{$noteClass}} form-control-textarea-note AddNoteField mb-1 w-100">{{$noteValue}}</textarea>
                    {{$noteDate}}<button onclick="submitL3Update('note',{{$i}})" class="btn btn-primary {{$displayType}}  pr-3">Submit</button>
                </div>
            </div>
            @endfor
        </div>
		<hr/>
        <div class="row mt-2">
            @for($i = 1;$i <= 5;$i++)
				@php
					if(isset($level3notesimages[$i-1]) && $level3notesimages[$i-1]->attachment != ''){
						$readOnly = 'readonly';
						$displayType = 'display-none';
						$imageName = trim($level3notesimages[$i-1]->attachment);
						$imageExist = true;						
						$imageSrc = '/imageslevelthree/'.$level3notesimages[$i-1]->form_id.'/'.$imageName;
					}else{
						$readOnly = '';
						$noteValue = '';
						$displayType = '';
						$imageExist = false;						
					}					
					if($readOnlyView){					
						$displayType = 'display-none';
					}	
				@endphp  
            <div class="col">
                <div class="form-group" id="note_card_proof-{{$i}}">
						<div class="detaisl-left align-content-center mt-1 w-100">
							<label for="note_card-{{$i}}">Attachment {{$i}}</label>
						</div>
                        @if($imageExist)
                        <div class="add-document d-flex align-items-center justify-content-around do-not-crop" id="note_card-{{$i}}">
							<div id="note_div">
                                <img class="uploaded_image imagetoenlarge" name="note_image" id="document_preview_note" src="{{$imageSrc}}">
                            </div>
						</div>	
                        @else                        
                        <div class="add-document d-flex align-items-center justify-content-around do-not-crop" id="note_card-{{$i}}">
							<div class=" {{$displayType}} add-document-btn adb-btn-inn">
								<button type="button" id="upload_note_card" class="btn btn-outline-grey waves-effect upload_document" data-toggle="modal" 
								data-id="note_card-{{$i}}" data-name="note_image-{{$i}}"  data-document="Image" data-target="#upload_note">
									<span class="adb-icon">
										<i class="fa fa-plus-circle"></i>
									</span>
									Add NOTE
								</button>
							</div>
						</div>	
						<button onclick="submitL3Update('image',{{$i}})" class="btn btn-primary {{$displayType}} mt-2">Submit</button>
						@endif							
                    <input type="text" style="opacity:0" name="noteImage" id="noteImage-{{$i}}">
                </div>
            </div>
            @endfor
        </div>

        </div>

        </div>
        <!-- Row end -->
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


