<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{asset('New/css/styles.css')}}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&family=Sen:wght@400..800&display=swap" rel="stylesheet">
    <title>Document</title>
</head>
<body>
    <div class="container mt-4">
        <h2>Sales</h2>
        <!-- Tabs Section -->
        <ul class="nav nav-tabs">
            <li class="nav-item">
                <a style="font-weight: 200; color: black" class="nav-link" href="{{route('index')}}">Back to Dashboard</a>
            </li>
            <li class="nav-item">
                <a style="font-weight:200; color: black;" class="nav-link" href="{{route('getInvoice')}}">Invoices</a>
            </li>
            <li class="nav-item">
                <a style="font-weight:200; color: black;" class="nav-link" href="{{route('productEstimates')}}">Estimates</a>
            </li>
            <li class="nav-item">
                <a style="font-weight:200; color: black;" class="nav-link" href="{{route('productEstimatesCalculation')}}">Estimate Calculations</a>
            </li>
            <li class="nav-item">
                <a style="font-weight:200; color: black;" class="nav-link" href="{{route('getCustomer')}}">Customers</a>
            </li>
            <li class="nav-item">
                <a style="font-weight:200; color: black;" class="nav-link" href="{{route('productService')}}">Products & Services</a>
            </li>
            <li class="nav-item">
                <a style="font-weight:200; color: black;" class="nav-link" href="{{route('inTransitInventory')}}">In Transit</a>
            </li>
            <li class="nav-item">
                <a style="font-weight:200; color: black;" class="nav-link" href="{{route('receivedTransitInventory')}}">Received Transit</a>
            </li>
        </ul>
    
        <div class="d-flex justify-content-between align-items-center">
            <div class="header">Sales transactions</div>
            <a href="#" class="feedback-link">Give feedback</a>
        </div>
    
        <div class="row content-detail">
            <div class="col-md-8">
                <div class="content-card">
                    <h1>Create, send, and track invoices</h1>
                    <ul>
                        <li>View and track your invoices all in one place</li>
                        <li>Customise invoices with your logo, colours, and fonts</li>
                        <li>Schedule automatic reminders and recurring invoices</li>
                    </ul>
                    <button class="btn btn-create mt-3">Create invoice</button>
                </div>
            </div>
            <div class="col-md-4 text-center image">
                <img src="{{asset('New/images/InvoiceWizardImage.min.svg')}}" alt="Illustration" class="illustration">
            </div>
        </div>
        <div class="tips-resources">
            <h5>TIPS & RESOURCES</h5>
            <a href="#">Create and send invoices</a>
            <a href="#">Record a customer payment</a>
            <a href="#">See more</a>
        </div>
    </div>
</body>
</html>