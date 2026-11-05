<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Machine Slip</title>
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
    </head>
    <body>
        <div>
            <header class="row" style="width: 100%; margin-top: 20px;" class="clearfix">
                <div id="logo">
                    <img  height="60px" src="{{asset('images/logo.png')}}">
                </div>
                <h1>Estimate Inventory Calculations</h1>
                <div style="float: right;" class="clearfix">
                    <div>+92 303 4288266</div>
                    <div>info@autopower.pk</div>
                    <br>
                </div>
                <div style="float: left; margin-left: 30px;">
                    <div>AutoPower</div>
                    <div>Autopower Factory, M Din,<br> Walton Rd, Walton Railway<br> Officers Colony, Lahore, Punjab</div>
                </div>
            </header>

            <div class="table-section">
                <table class="table table-hover">
                  <thead>
                    <tr>
                      <th>Sr. #</th>
                      <th>Item Name</th>
                      <th>Estimate<br> Numbers</th>
                      <th>Item Category</th>
                      <th>SKU</th>
                      <th>Total Quantity<br> Needed<br>to Order</th>
                      <th>Total<br> Quantity Used</th>
                      <th>Remaining<br> after Use</th>
                      <th>By Air</th>
                      <th>By Sea</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($inventoryWithEstimates as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $item['name'] }}</td>
                        <td>
                            @foreach ($item['estimates'] as $estimate)
                            {{ $estimate['number'] }} - {{ $estimate['title'] }},<br>
                            @endforeach
                        </td>
                        <td>{{ $item['category'] }}</td>
                        <td>{{ $item['sku'] }}</td>
                        <td>{{ $item['total_needed'] * (-1)}}</td>
                        <td>{{ $item['total_in_stock']}}</td>
                        <td>{{ $item['remaining_stock']}}</td>
                        <td>{{ $item['air_transit']}}</td>
                        <td>{{ $item['sea_transit']}}</td>
                    </tr>
                    @endforeach
                  </tbody>
                </table>
            </div>

        </div>
    </body>
</html>

<script type="text/javascript">
$(document).ready(function () {
    window.print();
});
</script>
