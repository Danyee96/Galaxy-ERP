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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
  
    <title>Estimate Calculations</title>
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
                <a style="font-weight:200; color: black;" class="nav-link" href="{{route('productEstimates')}}">Estimates</a>
            </li>
            <li class="nav-item">
                <a style="font-weight:200; color: black;" class="nav-link active" href="{{route('productEstimatesCalculation')}}">Estimate Calculations</a>
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

        <!-- Title Section -->
        <div class="header-section">
        <h2>Estimate Calculation</h2>

        <div class="btn-group">
          <!-- Simple button -->
          <a href="{{route('downloadProductEstimatesCalculation')}}"><button class="btn new-button">
            Download PDF
          </button></a>
          <button id="btnExport" onclick="fnExcelReport();" class="btn new-button" style="margin-left: 10px;">Export to Excel</button>
        </div>
        <!-- Bootstrap JS, Popper.js, and jQuery -->
        <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
      </div>

      <!-- Search Section -->
      <!-- <div class="search-section">
        <input type="text" style="width: 300px" id="searchInput" class="form-control" placeholder="Find Estimates" onkeyup="filterTable()" />
      </div> -->

      <!-- Table Section -->
      <div class="table-section">
        <table class="table table-hover" id="example">
          <thead>
            <tr>
              <th>Sr. #</th>
              <th>Item Name</th>
              <th>Estimate Numbers</th>
              <th>Item Category</th>
              <th>SKU</th>
              <th>Total Quantity Needed<br>to Order</th>
              <th>Total Quantity Used</th>
              <th>Remaining after Use</th>
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
    <script>
      function fnExcelReport() {
          // Get table element
          var table = document.getElementById("example");

          // Convert HTML table to a worksheet
          var workbook = XLSX.utils.table_to_book(table, {sheet:"Estimate Calculations"});

          // Export the workbook as .xlsx
          XLSX.writeFile(workbook, "Estimate_Calculations.xlsx");
      }
    </script>
</body>
</html>