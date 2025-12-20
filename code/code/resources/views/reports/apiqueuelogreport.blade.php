@extends('layouts.app')
@section('content')
    <div class="container">
        @if(count($apiqueuedata)>0)

            <div class="row p-5">
                <div class="col-4 mx-auto">
                    <button type="submit" class="btn-primary form-control" id="excel-apireport">EXPORT API-QUEUE REPORT</button>
                </div>
            </div>
        @else
            <div class="row p-5">
                <div class="card">
                    <div class="card-body mx-auto">
                        <h2><label>No Record Found To Export Data</label></h2>
                    </div>  
                </div>
            </div>
        @endif
    </div>
@endsection
@push('scripts')
    <script type="text/javascript">
        // var _getapiqueuedata  = '';
        var _getapiqueuedata = JSON.parse('<?php echo json_encode($apiqueuedata); ?>');
        for(i=1;_getapiqueuedata.length > i ;i++){
            _getapiqueuedata[i].forEach(function(data,key1){
                if(key1 == 6 || key1 == 15){
                    _getapiqueuedata[i][key1] = atob(data).replaceAll(',',';');
                }else{
                    _getapiqueuedata[i][key1] = data;
                }
            });
        }
        $('#excel-apireport').on('click',function(){
            var currdate = moment().format("YMMDDHHmmss")
            let csvContent = "";
            _getapiqueuedata.forEach(function(rowArray,key){
                let row = rowArray.join(",");
                csvContent += row+ "\r\n";
            });
            const link = document.createElement("a");
            if(link.download !== undefined){
                const blob = new Blob([csvContent],{type:'text/csv;charset=utf-8;'});
                const url = URL.createObjectURL(blob);
                link.setAttribute("href",url);
                link.setAttribute("download","apiqueuelogreport-"+currdate+".csv");
                link.style.visibility = 'hidden';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                $.growl({message:'Succefully generated API queue log report.'},{type:'success'});
            }
        })
    </script>
@endpush