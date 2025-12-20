@extends('layouts.app')
@section('content')
    <div class = "row mt-4">
        
        <!-- Debug Info -->
        <div class="col-md-5 mx-auto">
            <div class="card mt-2">
                <div class="card-body">
                    <strong><p style="font-size: 16px;">Upload DebugInfo Text File</p></strong>
                    <input type="file" accept=".txt" id="debugInfo" />
                    <button id="debugInfoImport" class="btn btn-primary btn-md float-end">Import</button>
                </div>
            </div>
            <div class="card mt-2" id="displayContent" style="display:none;">
                <div class="card-body">
                    <p id="formId" class="pt-2"></p>
                    <p id="aof_number"></p>
                    <p id="acct_details"></p>
                    <p id="ovd_details"></p>
                </div>
            </div>
        </div>

        <!-- Amend Debug Info -->
        <div class="col-md-5 mx-auto">
            <div class="card mt-2">
                <div class="card-body">
                    <strong><p style="font-size: 16px;">Upload Amend DebugInfo Text File</p></strong>
                    <input type="file" accept=".txt" id="amendDebugInfo" />
                    <button id="amendDebugInfoImport" class="btn btn-primary btn-md float-end">Import</button>
    </div>
            </div>
            <div class="card mt-2" id="displayAmendContent" style="display:none;">
                <div class="card-body">
                    <p id="crfId" class="pt-2"></p>
                    <p id="crf_number"></p>
                    <p id="amend_master"></p>
                    <p id="amend_queue"></p>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
      
        var debugInfoFileContent;
        var amendDebugInfoFileContent;

        $("body").on("change","#debugInfo",function(){

            var fileReader=new FileReader();

            fileReader.onload=function(){
                debugInfoFileContent = fileReader.result;
            }
            fileReader.readAsText(this.files[0]);

        });

        $('body').on('click','#debugInfoImport',function(){
            var debuginfo = [];
            debuginfo.data = {};
            debuginfo.url =  '/admin/debugTest';
            debuginfo.data['debugInfo'] = debugInfoFileContent;
            debuginfo.data['functionName'] = 'DebugInfoCallback';
            
            crudAjaxCall(debuginfo);
            return false;
        });



        $("body").on("change","#amendDebugInfo",function(){

            var fileReader=new FileReader();

            fileReader.onload=function(){
                amendDebugInfoFileContent = fileReader.result;
            }
            fileReader.readAsText(this.files[0]);

        });

        $('body').on('click','#amendDebugInfoImport',function(){
            var debuginfo = [];
            debuginfo.data = {};
            debuginfo.url =  '/admin/amenddebugTest';
            debuginfo.data['amendDebugInfo'] = amendDebugInfoFileContent;
            debuginfo.data['functionName'] = 'AmendDebugInfoCallback';

            crudAjaxCall(debuginfo);
            return false;
        });


    </script>
@endpush
