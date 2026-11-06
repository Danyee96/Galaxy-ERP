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
            @media print {
                @page {
                    size: 3in 2in;
                    /*size: 60mm 10mm;*/
                    word-wrap: break-word;
                }

                body{
                    /*width: 50mm !important;
                    height: 20mm !important;
                    margin: 50mm 0mm 50mm mm; */
                    /* change the margins as you want them to be. */
               } 
            }
            body {
                position: fixed;
                color: #001028;
                background: #FFFFFF;
                font-family: Arial, sans-serif;
                font-size: 11px;
                font-family: Arial;
            }
        </style>
        @include('branding.print-head')
    </head>
    <body>
        <div style="page-break-before: always;">
            <header class="row">
                <div style="float: left; margin-left: 85px; margin-top: 0px;word-wrap: break-word;">
                    <div style="font-weight: bold; font-size: 9px; line-height: 10px;"><span style="font-weight: 100; font-size: 8px;">Serial #: </span> {{ $machine->Item->serial_number }}</div>
                    <div style="font-weight: bold; font-size: 9px; line-height: 10px;"><span style="font-weight: 100; font-size: 8px;">Cust. Name: </span> {{ $machine->name}}</div>
                    <div style="font-weight: bold; font-size: 9px; line-height: 10px;"><span style="font-weight: 100; font-size: 8px;">Cust. Phone: </span> {{ $machine->phone}}</div>
                </div>
            </header>
        </div>
    </body>
</html>

<script type="text/javascript">
$(document).ready(function () {
    window.print();
});
</script>
