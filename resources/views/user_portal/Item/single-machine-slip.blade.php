<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Machine Slip | {{ config('brand.name') }}</title>
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
                <h1>Galaxy Inverter Warranty Slip</h1>
                <div style="float: right;" class="clearfix">
                    @include('branding.contact')
                    <br>
                </div>
                <div style="float: left; margin-left: 40px;">
                    <div><span>Serial Number #: </span> {{ $machine->Item->serial_number }}</div>
                    <div><span>Company Name: </span> {{ $machine->Company->name }}</div>
                    <div><span>Machine Power: </span> {{ $machine->Power ? $machine->Power->name : '' }}</div>
                    <div><span>Machine Model: </span> {{ $machine->model}}</div>
                    <div><span>Production Date: </span> {{ $machine->Item->production_date}}</div>
                    <div><span>Warranty Year: </span> {{ $machine->Item->warranty_years}}</div>
                    @if($machine->warranty_status == 'out')
                    <div style="font-weight: bold;"><span>Warranty Status: </span> {{ $machine->warranty_status == 'in' ? 'In-Warranty' : 'Over-Warranty'}}</div>
                    @endif
                    <br>
                    <div><span>Customer Name: </span> {{ $machine->name}}</div>
                    <div><span>Customer Phone: </span> {{ $machine->phone}}</div>
                    <div><span>Term: </span> Due On Receipt</div>
                </div>
            </header>

            <table style="width: 100%;">
                <thead>
                    <tr>
                        <th class="service">DATE</th>
                        <th class="desc">ERROR CODE</th>
                        <th class="desc">ACCESSORIES</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="service">{{ date('d M Y', strtoTime($machine->created_at)) }}</td>
                        <td class="desc">{{$machine->Error ? $machine->Error->name : ''}}</td>
                        <td class="desc">
                            @foreach($machine->machineAccessories as $key => $machine_accessories)
                                <input type="checkbox"/> {{$machine_accessories->Accessory->name}}<br>
                            @endforeach
                        </td>
                    </tr>
                </tbody>
            </table>
            <h3 style="margin-top: 60px">Disclaimer</h3>
            <p><span style="font-weight: bold">{{ $machine->name }}</span> is to ensure that the items being delivered to Galaxy Inverter as per this sheet.</p>
            <p><span style="font-weight: bold">Galaxy Inverter</span> is not responsible for the content packed inside the Machine.</p>
            <p>Booked weight may vary with invoice/billing weight as our manifested weight will be treated as final weight.</p>
            <br>
            <table style="width: 100%;">
                <tbody>
                    <tr>
                        <th class="service" style="border: 1px solid black; color: black; font-weight: bold; font-size: 13px">Customer</th>
                        <th class="service" colspan="2" style="text-align: center; border: 1px solid black; color: black; font-weight: bold; font-size: 13px">Galaxy Inverter</th>
                    </tr>
                    <tr>
                        <td class="service" style="text-align: left; padding: 8px; border: 1px solid black;">Name: {{ $machine->name }}</td>
                        <td class="service" style="text-align: left; padding: 8px; border: 1px solid black;">Machine Received By:</td>
                        <td class="service" style="text-align: left; padding: 8px; border: 1px solid black;">{{$machine->EntryBy->name}}</td>
                    </tr>
                    <tr>
                        <td class="service" style="text-align: left; padding: 8px; border: 1px solid black;">Phone: {{$machine->phone}}</td>
                        <td class="service" style="text-align: left; padding: 8px; border: 1px solid black;">Sign:</td>
                        <td class="service" style="text-align: left; padding: 8px; border: 1px solid black;"></td>
                    </tr>
                    <tr>
                        <td class="service" style="text-align: left; padding: 8px; border: 1px solid black;">Sign:</td>
                        <td class="service" style="text-align: left; padding: 8px; border: 1px solid black;">Date & Time:</td>
                        <td class="service" style="text-align: left; padding: 8px; border: 1px solid black;">{{$machine->entry_date}}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </body>
</html>

<script type="text/javascript">
$(document).ready(function () {
    window.print();
});
</script>
