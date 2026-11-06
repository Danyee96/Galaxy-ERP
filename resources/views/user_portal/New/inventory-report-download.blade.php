<!DOCTYPE html>
<html lang="en">
    <head>
        <title>{{$data->name}} Report | {{ config('brand.name') }}</title>
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
                color: black;
                border-bottom: 1px solid #C1CED9;
                white-space: nowrap;        
                font-weight: bold;
                font-size: 13px;
            }

            table .service,
            table .desc {
                text-align: left;
            }

            table td {
                /*padding: 20px;*/
                text-align: left;
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
                <h1>{{$data->name}} Ledger Report</h1>
                <div style="float: right;" class="clearfix">
                    @if(config('brand.phone'))<div>{{ config('brand.phone') }}</div>@endif
                    @if(config('brand.email'))<div>{{ config('brand.email') }}</div>@endif
                    <br>
                </div>
                <div style="float: left; margin-left: 10px;">
                    <div>Galaxy Inverter</div>
                    @if(config('brand.address'))<div>{{ config('brand.address') }}</div>@endif
                </div>
            </header>

            <div class="table-section">
                <table class="table table-hover">
                  <thead>
                    <tr>
                      <th>Date</th>
                      <th>Transaction Type</th>
                      <th>Description</th>
                      <th>Quantity</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td>{{$data->created_at->format("d-M-Y")}}</td>
                      <td>Inventory Starting Value</td>
                      <td>{{$data->name}} - Opening Inventory</td>
                      <td>{{$data->initial_qty}}</td>
                    </tr>
                    @foreach($data->productInventory as $dd)
                    <tr>
                      <td>{{$dd->created_at->format("d-M-Y")}}</td>
                      @if($dd->transit_id == 4)
                      <td>Invoice</td>
                      @else
                      <td>Inventory Added</td>
                      @endif
                      <td>{{$dd->description}}</td>
                      <td>{{$dd->stock_in}}</td>
                    </tr>
                    @endforeach
                  </tbody>
                  <tfoot>
                    <tr>
                      <th></th>
                      <th></th>
                      <th>Total Quantity Sum</th>
                      <th>{{$data->total_qty}}</th>
                    </tr>
                  </tfoot>
                </table>
            </div>

        </div>

            @if(config('warehouses.enabled') && Auth::check() && in_array((int)Auth::user()->role_id,[1,3,4,5],true))
            @php($warehouseMovements = \App\Models\Warehouse\Movement::with(['item.source','source.warehouse','destination.warehouse','operation'])->whereHas('item',fn($q)=>$q->where('source_type','product')->where('source_id',$data->id))->orderBy('id')->get())
            @include('warehouse._stock-trace')
            @endif
    </body>
</html>
