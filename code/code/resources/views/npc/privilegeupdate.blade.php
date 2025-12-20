@extends('layouts.app')
@section('content')
<div class="pcoded-content1">
    <div class="pcoded-inner-content1">
        <!-- Main-body start -->
        <div class="main-body">
            <div class="page-wrapper">
                <!-- Page-body start -->
                <div class="page-body">                
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-block table-border-style">
                                    <div class="row mb-3">
                                        <div class="col-md-12">                                            
											<h5>L3 Update 
													&nbsp;&nbsp;&nbsp;
													<span id='aof_number_for_copy'></span>
													&nbsp;
													<img id="copy_to_clip_img" target="_blank" src="{{ asset('assets/images/copy_to_clip.png') }}" style="width: 16px !important; margin-bottom:9px; display:none;" 
													title='Copy to clipboard'>
											</h5>
                                        </div>
                                    </div>
                                    <div class="row filter mb-4 mt-0">
                                        <div class="col-md-3">
                                            <input type="text" class="form-control" id="aof_tracking_no" name="aof_tracking_no" placeholder="AOF Number">
                                        </div>
                                        <div class="col-md-4">
                                            {!! Form::select('customer name',$customerNames,null,array('class'=>'form-control customerName',
                                                    'id'=>'customerName','name'=>'customerName','placeholder'=>'Select Customer Name')) !!}
                                        </div>
                                        <div class="col-md-3">
                                            <button type="submit" class="btn btn-primary" id="account_details_update_search">Search</button>
                                            <img id="viewform" target="_blank" src="{{ asset('assets/images/report-blue.svg') }}" style="width: 39px !important;margin-left: 10px;">
                                        </div>
                                    </div>
                                    <div class="privilegeaccessRedirect_background display-none"></div>
                                    <div id="account_details_update">
                                        
                                    </div>                                 
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Page-body end -->
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<!-- <script  src="{{ asset('custom/js/npc.js') }}"></script> -->
<script  src="{{ asset('custom/js/tracking.js') }}"></script>  
<script  src="{{ asset('custom/js/privilegesupdate.js') }}"></script>  
<script type="text/javascript">
    $(document).ready(function(){
       addSelect2('customerName','Customer Name'); 
    });
</script>
@endpush