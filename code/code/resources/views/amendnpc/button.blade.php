@if(!in_array($role, [21,22,23]))
    <button type="button" class="btn btn-primary mr-4  clear display-none mt-5 commonBtn" id="approved">Clear</button>
    <button type="button" class="btn btn-info mr-4 commonBtn  discrepent mt-5" id="discrepent">In Review</button>
    <button type="button" class="btn btn-warning mr-4 mt-5" id="hold_modal">Hold</button>
    <button type="button" class="btn btn-danger mt-5" id="reject_modal">Reject</button>
@endif

@if($role == 22)
    <button type="button" class="btn btn-primary mr-4  clear commonBtn" id="approved">L3 Cleared</button>
    <button type="button" class="btn btn-warning mr-4" id="hold_modal">L3 Hold</button>
@endif

@if($role == 21)
    <button type="button" class="btn btn-info mr-4 commonBtn  discrepent mt-5" id="discrepent">QC In Review</button>
    <button type="button" class="btn btn-primary mr-4  clear display-none mt-5 commonBtn" id="approved">QC Cleared</button>
    <button type="button" class="btn btn-warning mr-4 mt-5" id="hold_modal">QC Hold</button>
@endif

@if($role == 23)
    <button type="button" class="btn btn-info mr-4 commonBtn  discrepent mt-5" id="discrepent">Audit In Review</button>
    <button type="button" class="btn btn-primary mr-4  clear  mt-5 commonBtn" id="approved">Audit Cleared</button>
@endif