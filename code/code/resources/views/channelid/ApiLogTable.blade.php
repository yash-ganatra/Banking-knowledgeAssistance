@extends('layouts.app')
@section('content')
<style type="text/css">
    #branchTable{width: 100%!important; }
    .table td, .table th{padding: 10px 0px;}
</style>
<div class="pcoded-content1">
    <div class="pcoded-inner-content1">
        <!-- Main-body start -->
        <div class="main-body">
            <div class="page-wrapper">
                <!-- Page-body start -->
                  <div class="page-body page-body-top mb-3">
                    <div class="card">
                                <div class="card-block">
                                    <div class="row" id="tableDiv">
                                        <div class="col-sm-6">
                                            <div class="form-group row">
                                                <label class="col-sm-4 col-form-label">
                                                    <h4>Select DSA API Log</h4>
                                                  </label>
                                                 <div class="col-sm-6">
                                                    {!! Form::select('Table',$tables,null, array('class'=>'select-css table_name','id'=>'api_table_name',
                                                            'name'=>'table_name','placeholder'=>'Select Table')) !!}
                                                </div>
                                            </div>
                                        </div>
                                                                           </div>
                                    <div class="card table-top">                                            
                                      <div class="card-block table-border-style card-block-padding">
                                          <div class="table-responsive" id="a">
                                              <table class="table table-custom" id="oaoMasterTableDiv">
                                              </table>

                                              
                                 </div>
                               </div>
                             </div>
                                </div>
                            </div>
                        </div>
                
                <!-- Page-body end -->
                <div class="modal fade" id="oao_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content modal-content-etb">
            <div class="modal-header">
                <div class="row">
                    <div class="col-sm-12">
                        <h4 class="modal-title"></h4>
                    </div>
                </div>

                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <!-- <span aria-hidden="true">&times;</span> -->
                </button>
            </div>

            <div class="modal-body">
                
            </div>

            <div class="modal-footer modal-display">
                <button type="button" class="btn btn-default pull-right waves-effect mr-2" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
            </div>  
        </div>                          
    </div>
</div>
@endsection
@push('scripts')
<script  src="{{ asset('custom/js/admin.js') }}"></script>
<script  src="{{ asset('custom/js/dsa.js') }}"></script>
@endpush