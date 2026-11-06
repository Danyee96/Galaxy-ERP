<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Invoice | {{ config('brand.name') }}</title>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
        <style type="text/css">
            .clearfix:after {
                content: "";
                display: table;
                clear: both;
            }

            a {
                color: #5D6975;
                text-decoration: underline;
            }

            body {
                position: relative;
                width: 21cm;  
                height: 29.7cm; 
                margin: 0 auto; 
                color: #001028;
                background: #FFFFFF; 
                font-family: Arial, sans-serif; 
                font-size: 12px; 
                font-family: Arial;
            }

            header {
                padding: 10px 0;
                margin-bottom: 30px;
            }

            #logo {
                text-align: center;
                margin-bottom: 10px;
            }

            #logo img {
                width: 90px;
            }

            h1 {
                border-top: 1px solid  #5D6975;
                border-bottom: 1px solid  #5D6975;
                color: #5D6975;
                font-size: 2.4em;
                line-height: 1.4em;
                font-weight: normal;
                text-align: center;
                margin: 0 0 20px 0;
                background: url(dimension.png);
            }

            #project {
                float: left;
            }

            #project span {
                color: #5D6975;
                text-align: right;
                width: 52px;
                margin-right: 10px;
                display: inline-block;
                font-size: 0.8em;
            }

            #company {
                float: right;
                text-align: right;
            }

            #project div,
            #company div {
                white-space: nowrap;        
            }

            table {
                width: 100%;
                border-collapse: collapse;
                border-spacing: 0;
                margin-bottom: 20px;
            }

            table tr:nth-child(2n-1) td {
                background: #F5F5F5;
            }

            table th,
            table td {
                text-align: center;
            }

            table th {
                padding: 5px 20px;
                color: #5D6975;
                border-bottom: 1px solid #C1CED9;
                white-space: nowrap;        
                font-weight: normal;
            }

            table .service,
            table .desc {
                text-align: left;
            }

            table td {
                padding: 20px;
                text-align: right;
            }

            table td.service,
            table td.desc {
                vertical-align: top;
            }

            table td.unit,
            table td.qty,
            table td.total {
                font-size: 1.2em;
            }

            table td.grand {
                border-top: 1px solid #5D6975;;
            }

            #notices .notice {
                color: #5D6975;
                font-size: 1.2em;
            }

            footer {
                color: #5D6975;
                width: 100%;
                height: 30px;
                position: absolute;
                bottom: 0;
                border-top: 1px solid #C1CED9;
                padding: 8px 0;
                text-align: center;
            }


        </style>
        @include('branding.print-head')
    </head>
    <body>
        <div>
            <header class="row" style="width: 100%; margin-top: 20px;" class="clearfix">
                <div id="logo">
                    <img  height="60px" src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('brand/galaxy-logo.png'))) }}">
                </div>
                <h1>Invoice Number # {{$data->invoice_number}}</h1>
                <div style="float: right;" class="clearfix">
                    @include('branding.contact')
                    <br>
                </div>
                <div style="float: left; margin-left: 40px;">
                    <div><span>Invoice Number #: </span> {{ $data->invoice_number }}</div>
                    <div><span>Invoice Issued To: </span> {{ $data->invoice_name }}</div>
                    <br>
                    <div><span>Issued By: </span> {{$data->createdBy ? $data->createdBy->name : ''}}</div>
                    <div><span>Total Number of Products: </span> {{count($data->invoiceDetails)}}</div>
                    <div><span>Total Number of Quantity: </span> {{$data->total_count}}</div>
                </div>
            </header>

            <table style="width: 100%;">
                <thead>
                    <tr>
                        <th class="service">DATE</th>
                        <th class="desc">INVENTORY ITEM NAME</th>
                        <th class="desc">ISSUED QUANTITY</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data->invoiceDetails as $dd)
                    <tr>
                        <td class="service">{{ date('d M Y', strtoTime($data->created_at)) }}</td>
                        <td class="desc">{{$dd->productDetails->name}}</td>
                        <td class="desc">{{$dd->product_qty}}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

            @if(config('warehouses.enabled') && Auth::check() && in_array((int)Auth::user()->role_id,[1,3,4,5],true))
            @php($warehouseMovements = \App\Models\Warehouse\Movement::with(['item.source','source.warehouse','destination.warehouse','operation'])->whereHas('operation',fn($q)=>$q->where('reference','invoice:'.$data->invoice_number))->orderBy('id')->get())
            @include('warehouse._stock-trace')
            @endif
    </body>
</html>

<script type="text/javascript">
$(document).ready(function () {
    window.print();
});
</script>
