<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{asset('New/css/styles.css')}}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&family=Sen:wght@400..800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css" rel="stylesheet"/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  
    <title>Estimates</title>
</head>
<body>
    
    <div class="container mt-4">
        <h2>Estimates</h2>
        <!-- Tabs Section -->
        <ul class="nav nav-tabs">
            <li class="nav-item">
                <a style="font-weight: 200; color: black" class="nav-link" href="{{route('index')}}">Back to Dashboard</a>
            </li>
            <li class="nav-item">
                <a style="font-weight:200; color: black;" class="nav-link" href="{{route('getInvoice')}}">Invoices</a>
            </li>
            <li class="nav-item">
                <a style="font-weight:200; color: black;" class="nav-link active" href="{{route('productEstimates')}}">Estimates</a>
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

        @if(count($datas) == 0)
        <div class="row content-detail2">
            <div class="col-md-8">
                <div class="content-card">
                    <h1>Request approvals and <br> more with estimates</h1>
                    <ul>
                        <li>Ask for approvals directly on your estimate</li>
                        <li>Enjoy new ways to customise the look and feel</li>
                        <li>Track your estimate’s status from the moment you send it</li>
                    </ul>
                    <a href="{{route('createProductEstimates')}}"><button class="btn btn-create mt-3 estimate-button">Create an estimate</button></a>
                </div>
            </div>
            <div class="col-md-4 text-center image2">
                <img src="{{asset('New/images/709d22889bdee8948402d6b73c330214.svg')}}" alt="Illustration" class="illustration">
            </div>
        </div>
        @else
        <!-- Title Section -->
        <div class="header-section">
        <h2>Estimates</h2>

        <div class="btn-group">
          <!-- Simple button -->
          <a href="{{route('createProductEstimates')}}"><button class="btn new-button">
            New Estimate
          </button></a>
        </div>
        <!-- Bootstrap JS, Popper.js, and jQuery -->
        <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
      </div>

      <!-- Search Section -->
      <div class="search-section">
        <input type="text" style="width: 300px" id="searchInput" class="form-control" placeholder="Find Estimates" onkeyup="filterTable()" />
      </div>

      <!-- Table Section -->
      <div class="table-section">
        <table class="table table-hover">
          <thead>
            <tr>
              <th scope="col">ESTIMATION NUMBER</th>
              <th scope="col">TITLE</th>
              <th scope="col">NUMBER OF QUANTITY TO ESTIMATE</th>
              <th scope="col">BUNDLE</th>
              <th scope="col">ITEMS NEEDED</th>
              <th scope="col">REMARKS</th>
              <th scope="col">ACTION</th>
            </tr>
          </thead>
          <tbody>
            @foreach($datas as $key => $data)
            <tr>
              <td>{{$data->number}}</td>
              <td>{{$data->title}}</td>
              <td>{{$data->quantity}}</td>
              <td>{{$data->bundleDetails ? $data->bundleDetails->name : ''}}</td>
              <td>
                <ul>
                    @foreach($data->estimateDetails as $dd)
                    <li><span style="font-weight: bold;">{{$dd->quantity_in_stock}}</span> X {{$dd->productDetails->name}} items will be used in this estimate</li>
                    @endforeach
                </ul>
              </td>
              <td>
                <ul>
                    @foreach($data->estimateDetails as $dd)
                    <li><span style="font-weight: bold;">{{$dd->quantity_needed * (-1)}}</span> X {{$dd->productDetails->name}} more items needs to order</li>
                    @endforeach
                </ul>
              </td>
              <td>
                <a href="{{route('deleteEstimate', $data->id)}}"><button class="btn btn-link">Delete</button></a>
                <a href="{{route('SingleEstimateDownload', $data->id)}}" target="_blank"><button class="btn btn-link">Download</button></a>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      @endif
    </div>
    <script src="{{asset('New/customerScript.js')}}"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Bundle JS (includes Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script type="text/javascript">
        function filterTable() {
        const input = document.getElementById("searchInput");
        const filter = input.value.toLowerCase();
        const table = document.querySelector(".table tbody");
        const rows = table.getElementsByTagName("tr");

        for (let i = 0; i < rows.length; i++) {
          const cells = rows[i].getElementsByTagName("td");
          let match = false;

          // Check if any cell in the row contains the search term
          for (let j = 0; j < cells.length; j++) {
            if (cells[j]) {
              const cellValue = cells[j].textContent || cells[j].innerText;
              if (cellValue.toLowerCase().indexOf(filter) > -1) {
                match = true;
                break;
              }
            }
          }

          // Show or hide the row based on the search match
          rows[i].style.display = match ? "" : "none";
        }
      }
    </script>
</body>
</html>