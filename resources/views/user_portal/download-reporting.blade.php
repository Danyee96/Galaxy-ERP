<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
        <title>Reporting</title>
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
                background: transparent;
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
                padding:8px;
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
    </head>
    <body>
        <div>
            <header class="row" style="width: 90%;" class="clearfix">
                <div id="logo">
                    <img  height="60px" src="images/logo.png">
                </div>
                <h1 style="color: black; font-weight: bold">All Machines Data</h1>
            </header>

            <table style="margin-top: -20px;width: 85%;">
                <thead>
                    <tr>
                        <th class="service">#</th>
                        <th class="desc">Serial<br>Number</th>
                        <th class="desc">Company<br>Name</th>
                        <th class="desc">Power</th>
                        <th class="desc">Model<br>Name</th>
                        <th class="desc">Total<br>Cost</th>
                        <th class="desc">Occurrence</th>
                        <th class="desc">Errors<br>Codes</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($machines as $key => $machine)
                    <?php
                        $sum = 0;
                        foreach($machine->machineHistory as $hist)
                        {
                            $sum = $sum + $hist->cost;
                        }
                    ?>
                    <tr>
                        <td class="service">{{ ++$key }}</td>
                        <td class="desc">{{$machine->serial_number}}</td>
                        <td class="desc">{{$machine->Company->name}}</td>
                        <td class="desc">{{$machine->Power ? $machine->Power->name : ''}}</td>
                        <td class="desc">{{$machine->model}}</td>
                        <td class="desc">Rs. {{number_format($sum)}}</td>
                        <td class="desc">{{count($machine->machineHistory)}}</td>
                        <td class="desc">
                            @foreach($machine->machineHistory as $histor)
                                {{$histor->Error ? $histor->Error->name : ''}},
                            @endforeach
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <thead>
                    <tr>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th style="color: black; font-weight: bold;">Total<br>Cost</th>
                        <th style="color: black; font-weight: bold;">Rs. {{number_format($machine_cost)}}</th>
                        <th></th>
                        <th></th>
                    </tr>
                </thead>
            </table>
            <footer style="width: 90%;">
                We appreciate your busines and look forward to service you again soon
            </footer> 
        </div>
    </body>
</html>