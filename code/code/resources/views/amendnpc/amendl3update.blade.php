@php
use Carbon\Carbon;
if($role != 22){
        $readOnlyView = true;
    }else{
        $readOnlyView = false; 
    }
@endphp 

        <div class="card" id="notes-details">
            <div class="card-block">
                <h4 class="sub-title">L3 Updates</h4>
                    <div class="row">
                        @for($i = 1; $i <= 5; $i++)
                             @php
                                if(isset($amendL3Images[$i-1]) && $amendL3Images[$i-1]->comments != ''){
                                    $readOnly = 'readonly';
                                    $displayType = 'display-none';
                                    $disabled = 'disabled';
                                    $noteValue = trim($amendL3Images[$i-1]->comments);
                                    $noteClass = 'readOnlyNote';
                                    $noteDate = Carbon::parse($amendL3Images[$i-1]->created_at)->format('d-M-Y H:m');
                                }else{
                                    $readOnly = '';
                                    $noteValue = '';
                                    $disabled = '';
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
                                <div class="form-group" id="note_proof">
                                    <label for="note_decription-" class="pr-3">NOTE {{$i}}</label>
                                    <textarea rows="3"  type="textarea" name="note_decription-{{$i}}" {{$readOnly}} {{$disabled}} id="note_decription-{{$i}}"   class=" form-control-textarea-note AddNoteField mb-1 w-100">{{$noteValue}}</textarea>{{$noteDate}}
                                    <button onclick="submitAmendL3Update('note',{{$i}})" class= "btn pr-3 {{$displayType}}">Submit</button>
                                </div>
                            </div>
                        @endfor
                    </div>
                        <hr/>
                            <div class="row mt-2">
                                 @for($i = 1;$i <= 5;$i++)
                                    @php
                                        if(isset($amendL3Images[$i-1]) && $amendL3Images[$i-1]->amend_proof_image != ''){
                                            $readOnly = 'readonly';
                                            $imageExist = true;                     
                                            $displayType = 'display-none';
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
                                    <div class="form-group" id="note_card_proof-">
                                        <div class="detaisl-left align-content-center mt-1 w-100">
                                            <label for="note_card-{{$i}}">Attachment {{$i}}</label>
                                        </div>
                                        @if($imageExist)
                                            <div class="add-document d-flex align-items-center justify-content-around do-not-crop" id="note_card-{{$i}}">
                                                <div id="note_div">
                                                    <img class="uploaded_image imagetoenlarge" name="note_image" id="document_preview_note" src="{{URL::to('/showamendimage/'.$amendL3Images[$i-1]->amend_proof_image)}}">
                                                </div>
                                            </div>  
                                        @else                        
                                            <div class="add-document d-flex align-items-center justify-content-around do-not-crop" id="note_card-{{$i}}">
                                                <div class=" {{$displayType}}  add-document-btn adb-btn-inn">
                                                    <button type="button" id="upload_note_card" class="btn btn-outline-grey waves-effect upload_document" data-toggle="modal" 
                                                    data-id="note_card-{{$i}}" data-name="note_image-{{$i}}"  data-document="Image" data-target="#upload_note">
                                                        <span class="adb-icon">
                                                            <i class="fa fa-plus-circle"></i>
                                                        </span>
                                                        Add NOTE
                                                    </button>
                                                </div>
                                            </div>  
                                            <button class="{{$displayType}}" onclick="submitAmendL3Update('image',{{$i}})" class="btn mt-2">Submit</button>
                                            @endif                          
                                        <input type="text" style="opacity:0" name="noteImage" id="noteImage-{{$i}}">
                                    </div>
                                </div> 
                             @endfor
                        </div>
                    </div>
                </div>