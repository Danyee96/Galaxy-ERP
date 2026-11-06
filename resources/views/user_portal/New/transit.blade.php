<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="{{asset('New/css/styles.css')}}" />
    <link
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css"
      rel="stylesheet"
    />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&family=Sen:wght@400..800&display=swap"
      rel="stylesheet"
    />
    <link
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css"
      rel="stylesheet"
    />
    <title>In-Transit Inventory | {{ config('brand.name') }}</title>
    @include('branding.head')
    <style>
      .galaxy-workspace .transit-overdue-row > td { --bs-table-bg: #fff5f5; --bs-table-hover-bg: #ffe8e8; }
      .galaxy-workspace .transit-overdue-row > td:first-child { border-left: 3px solid #b42318; }
      .galaxy-workspace .transit-overdue-badge { background: #b42318; color: #fff; }
    </style>
  </head>
  <body class="galaxy-workspace">
    @include('branding.header')
    <div class="container mt-4">
      <h2>In-Transit Inventory</h2>
      @if($overdue_count > 0)
      <div class="alert alert-danger d-flex flex-wrap align-items-center justify-content-between gap-2" role="status">
        <span><strong>{{ $overdue_count }} overdue {{ $overdue_count == 1 ? 'shipment' : 'shipments' }}</strong> awaiting full receipt.</span>
        <a class="alert-link" href="{{ route('inTransitInventory', ['overdue' => 1]) }}">View overdue</a>
      </div>
      @else
      <p class="text-muted">No overdue shipments.</p>
      @endif
      <!-- Tabs Section -->
      <ul class="nav nav-tabs">
        <li class="nav-item">
          <a
            style="font-weight: 200; color: black"
            class="nav-link"
            href="{{route('index')}}"
            >Back to Dashboard</a
          >
        </li>
        <li class="nav-item">
          <a
            style="font-weight: 200; color: black"
            class="nav-link"
            href="{{route('getInvoice')}}"
            >Invoices</a
          >
        </li>
        <li class="nav-item">
          <a
            style="font-weight: 200; color: black"
            class="nav-link"
            href="{{route('productEstimates')}}"
            >Estimates</a
          >
        </li>
        <li class="nav-item">
                <a style="font-weight:200; color: black;" class="nav-link" href="{{route('productEstimatesCalculation')}}">Estimate Calculations</a>
            </li>
        <li class="nav-item">
          <a
            style="font-weight: 200; color: black"
            class="nav-link"
            href="{{route('getCustomer')}}"
            >Customers</a
          >
        </li>
        <li class="nav-item">
          <a
            style="font-weight: 200; color: black"
            class="nav-link"
            href="{{route('productService')}}"
            >Products & Services</a
          >
        </li>
        <li class="nav-item">
          <a
            style="font-weight: 200; color: black"
            class="nav-link active"
            href="{{route('inTransitInventory')}}"
            >In Transit</a
          >
        </li>
        <li class="nav-item">
          <a
            style="font-weight: 200; color: black"
            class="nav-link"
            href="{{route('receivedTransitInventory')}}"
            >Received Transit</a
          >
        </li>
      </ul>

      <!-- Statistics Section -->
      <div class="row text-center">
        <div class="col-md-6">
          <div class="stat-card air-of-stock2">
            <div class="air-stock-img">
            </div>
            <div>
              <h3>{{$air_transit}}</h3>
              <p>By Air Transit</p>
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="stat-card sea-of-stock2">
            <div class="sea-stock-img">
            </div>
            <div>
              <h3>{{$sea_transit}}</h3>
              <p>By Sea Transit</p>
            </div>
          </div>
        </div>
      </div>

      <div class="search-section">
        <input type="text" style="width: 300px" id="searchInput" class="form-control" placeholder="Find products and services" onkeyup="filterTable()" />
        <div class="filter-container">
          <i class="fas fa-solid fa-filter opening-filter" onclick="toggleFilterDropdown()"></i>
          <i class="fas fa-solid fa-angle-down" onclick="toggleFilterDropdown()"></i>
          <div class="filter-dropdown" id="filterDropdown">
            <form class="p-3" method="GET">
              <div class="form-group">
                <label for="transit_method">Transit Method</label>
                <select class="form-select" name="transit_method" id="transit_method">
                  <option value="">All methods</option>
                  <option value="1" @if(request('transit_method') == 1) selected @endif>By Hand</option>
                  <option value="2" @if(request('transit_method') == 2) selected @endif>By Air</option>
                  <option value="3" @if(request('transit_method') == 3) selected @endif>By Sea</option>
                </select>
              </div>
              <div class="form-group mt-2">
                <label for="overdue">Delivery status</label>
                <select class="form-select" name="overdue" id="overdue"><option value="">All pending</option><option value="1" @if(request()->boolean('overdue')) selected @endif>Overdue</option></select>
              </div>
              <div style="justify-content: space-between;" class="d-flex">
                <a class="btn btn-secondary mt-2" href="{{ route('inTransitInventory') }}">Reset</a>
                <button type="submit" class="btn  mt-2 new-button">Apply</button>
              </div>

            </form>
          </div>
        </div>
      </div>

      <!-- Table Section -->
      <div class="table-section">
        <table class="table table-hover">
          <thead>
            <tr>
              <th scope="col">SR. #</th>
              <th scope="col">PRODUCT NAME</th>
              <th scope="col">SKU</th>
              <th scope="col">TRANSIT MODE</th>
              <th scope="col">TRANSIT QUANTITY ORDERED</th>
              <th scope="col">RECEIVED QUANTITY</th>
              <th scope="col">REMAINING QUANTITY</th>
              <th scope="col">EXPECTED PRODUCTION DATE</th>
              <th scope="col">EXPECTED DELIVERY DATE</th>
              <th scope="col">ENTRY BY</th>
              <th scope="col">ENTRY DATE</th>
              <th scope="col">ACTION</th>
            </tr>
          </thead>
          <tbody>
            @foreach($product_inventories as $key => $inventory)
            @php($overdueDays = $inventory->overdue_days)
            <tr class="{{ $overdueDays > 0 ? 'transit-overdue-row' : '' }}">
              <td>{{++$key}}</td>
              <td>{{$inventory->productDetails ? $inventory->productDetails->name : ''}}</td>
              <td>{{$inventory->productDetails ? $inventory->productDetails->sku : ''}}</td>
              <?php
                $transit_method = '';
                if($inventory->transit_id == 1)
                {
                  $transit_method = 'By Hand';
                }
                elseif ($inventory->transit_id == 2)
                {
                  $transit_method = 'By Air';
                }
                elseif ($inventory->transit_id == 3)
                {
                  $transit_method = 'By Sea';
                }
              ?>
              <td>{{$transit_method}}</td>
              <td>{{$inventory->stock_in}}</td>
              <td>{{$inventory->received_stock}}</td>
              <td>{{$inventory->remaining_stock}}</td>
              <td>{{$inventory->production_date}}</td>
              <td>{{ $inventory->expected_date ?: 'Not set' }}
                @if($overdueDays > 0)<br><span class="badge transit-overdue-badge">Overdue</span><small class="d-block text-danger">Overdue by {{ $overdueDays }} {{ $overdueDays == 1 ? 'day' : 'days' }}</small>@endif
              </td>
              <td>{{$inventory->entryBy ? $inventory->entryBy->name : ''}}</td>
              <td>{{date('d-m-Y h:i a', strtotime($inventory->created_at))}}</td>
              <td>
                <button class="btn btn-link"><a class="dropdown-item received-inventory"
                  href="#"
                  data-name="{{optional($inventory->productDetails)->name}}"
                  data-total_qty="{{$inventory->remaining_stock}}"
                  data-product_id="{{$inventory->id}}"
                  data-bs-toggle="modal"
                  data-bs-target="#addReceiveInventoryModal"
                >
                  Add Received Quantity
                </a></button>
                <!-- <button class="btn btn-link"><a href="{{route('markReceivedTransit', $inventory->id)}}">Mark as Recived</a></button></td> -->
            </tr>
            @endforeach
            @if($product_inventories->isEmpty())<tr><td colspan="12" class="text-center text-muted">No matching in-transit inventory.</td></tr>@endif
          </tbody>
        </table>
      </div>
    </div>
    <div style="color: black;" class="modal fade" id="addReceiveInventoryModal" tabindex="-1" role="dialog" aria-labelledby="addReceiveInventoryModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <div style="flex: 4;">
              <h5 class="text-center" style="font-weight: 700;">Transit Inventory</h5>
              <h5 class="modal-title text-center" style="font-weight: 700;" id="productName"></h5>
            </div>
            <button class="close" data-bs-dismiss="modal" style="background: none; border: none;position: absolute; right: 0; top: 0;" >
              <span aria-hidden="true" style="font-size: 32px;">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <form method="post" action="{{route('markReceivedTransitNew')}}">
              @csrf
              <input type="hidden" name="product_id" class="form-control" id="product_id" readonly>
              <div class="form-group">
                <label for="addQuantity" class="label-style mt-2">Remaining Stock to be Received:</label>
                <input type="text" class="form-control" id="addQuantity" readonly>
              </div>
              <div class="form-group">
                <label for="newQuantity" class="label-style mt-2">Add Received Quantity:</label>
                <input type="number" value="1" name="new_quantity" id="newQuantity" class="form-control">
                <small id="quantityError" class="text-danger" style="display: none;">Received quantity cannot be greater than current quantity</small>
              </div>
              <div class="modal-footer" style="justify-content: start;">
                <div class="button-container"style="border-top: 0px;">
                  <button class="save-button" type="submit" id="submitButton">Save and close</button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
    <script>
      document.getElementById('filterDropdown').addEventListener('click', function(event) {
        event.stopPropagation(); // Prevents clicks inside the dropdown from closing it.
      });

      // Target both the filter icon and the angle-down icon for the click event.
      var toggleIcons = document.querySelectorAll('.opening-filter, .fa-angle-down');
      toggleIcons.forEach(function(icon) {
        icon.addEventListener('click', function(event) {
          var dropdown = document.getElementById('filterDropdown');
          var isVisible = dropdown.style.display === 'block';
          if (!isVisible) {
            dropdown.style.display = 'block';
          } else {
            dropdown.style.display = 'none';
          }
          event.stopPropagation(); // Prevents this click from being captured by window listener.
        });
      });

      // Clicking anywhere outside the dropdown closes it.
      window.onclick = function(event) {
        var dropdown = document.getElementById('filterDropdown');
        if (dropdown.style.display === 'block') {
          dropdown.style.display = 'none';
        }
      };
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
      // Handle modal population when "Add Received Quantity" is clicked
      $(document).ready(function() {
        var modalElement = document.getElementById('addReceiveInventoryModal');
        var modal = null;

        // Function to validate quantities
        function validateQuantity() {
          var currentQty = parseFloat($('#addQuantity').val()) || 0;
          var newQty = parseFloat($('#newQuantity').val()) || 0;
          var submitButton = $('#submitButton');
          var errorMessage = $('#quantityError');

          if (newQty > currentQty) {
            submitButton.prop('disabled', true);
            submitButton.addClass('disabled');
            errorMessage.show();
            return false;
          } else {
            submitButton.prop('disabled', false);
            submitButton.removeClass('disabled');
            errorMessage.hide();
            return true;
          }
        }

        // Add event listener for quantity input changes
        $(document).on('input', '#newQuantity', function() {
          validateQuantity();
        });

        $('.received-inventory').on('click', function(e) {
          e.preventDefault();
          
          // Get data from the clicked link
          var totalQty = $(this).data('total_qty');
          var productId = $(this).data('product_id');
          var productName = $(this).data('name');
          
          // Populate the modal fields
          $('#addQuantity').val(totalQty);
          $('#product_id').val(productId);
          $('#productName').text(productName);
          $('#newQuantity').val(1);

          // Validate quantity on modal open
          setTimeout(function() {
            validateQuantity();
          }, 100);

          // Create and show the modal
          modal = new bootstrap.Modal(modalElement, {
            backdrop: true,
            keyboard: true
          });
          modal.show();
        });

        // Handle modal events to prevent backdrop issues
        $(modalElement).on('hidden.bs.modal', function () {
          // Remove any lingering backdrop
          $('.modal-backdrop').remove();
          $('body').removeClass('modal-open');
          $('body').css('padding-right', '');
          
          // Reset form and validation
          $('#newQuantity').val(1);
          $('#quantityError').hide();
          $('#submitButton').prop('disabled', false).removeClass('disabled');
          
          // Dispose of the modal instance
          if (modal) {
            modal.dispose();
            modal = null;
          }
        });

        // Handle form submission
        $(modalElement).find('form').on('submit', function(e) {
          // Validate before submission
          if (!validateQuantity()) {
            e.preventDefault();
            return false;
          }
          
          // Close modal on form submission
          if (modal) {
            modal.hide();
          }
        });
      });
    </script>
  </body>
</html>
