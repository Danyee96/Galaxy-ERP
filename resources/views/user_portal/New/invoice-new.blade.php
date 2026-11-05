<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="{{asset('New/css/styles.css')}}" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <!-- Bootstrap Bundle JS (includes Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <title>Invoice New</title>
  </head>
  <body>
    <div class="container mt-4">
      <h2>Sales</h2>
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
            class="nav-link active"
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
            class="nav-link"
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

      <div class="main-section">
        @if(count($datas) == 0)
        <h1>Do work. Get paid. It’s that easy.</h1>
        <div class="row justify-content-center">
          <div class="col-md-3 feature">
            <img
              src="{{asset('New/images/Zero_State_Create_Invoice.min.svg')}}"
              alt="Fast Invoicing"
            />
            <h5>Fast, simple invoicing</h5>
            <p>
              Easily create and send <br />
              professional-style invoices.
            </p>
          </div>
          <div class="col-md-3 feature">
            <img
              src="{{asset('New/images/Zero_State_Open_Invoice.min.svg')}}"
              alt="Track Invoices"
            />
            <h5>Track your open invoices</h5>
            <p>
              Know the moment your <br />
              invoices are viewed and paid.
            </p>
          </div>
          <div class="col-md-3 feature">
            <img
              src="{{asset('New/images/Zero_State_Mobile_Invoice.min.svg')}}"
              alt="Mobile Invoices"
            />
            <h5>Invoice on your mobile</h5>
            <p>
              Create invoices wherever you <br />
              are with the mobile app.
            </p>
          </div>
        </div>
        <button class="btn btn-create" data-bs-toggle="modal" data-bs-target="#invoiceModal">
          Create invoice
        </button>
        @else
        <!-- Title Section -->
        <div class="header-section">
          <h2>Invoices</h2>

          <div class="btn-group">
            <!-- Simple button -->
            <button class="btn btn-create" data-bs-toggle="modal" data-bs-target="#invoiceModal">
              Create invoice
            </button>
          </div>
          <!-- Bootstrap JS, Popper.js, and jQuery -->
          <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
          <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
          <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
        </div>

        <!-- Search Section -->
        <div class="search-section mb-3">
          <form method="GET" action="">
            <div class="input-group" style="width: 300px;">
              <input type="text" name="search" id="searchInput" class="form-control" placeholder="Find Invoices" value="{{ isset($search) ? $search : '' }}" />
              <button class="btn btn-primary" type="submit">Search</button>
              @if(request('search'))
                <a href="{{ route('getInvoice') }}" class="btn btn-outline-secondary">Clear</a>
              @endif
            </div>
          </form>
        </div>

        <!-- Table Section -->
        <div class="table-section">
          <table class="table table-hover">
            <thead>
              <tr>
                <th scope="col">DATE</th>
                <th scope="col">INVOICE NUMBER</th>
                <th scope="col">INVOICE TITLE</th>
                <th scope="col">INVOICE TOTAL ITEMS COUNT</th>
                <th scope="col">DETAILS</th>
                <th scope="col">ACTION</th>
              </tr>
            </thead>
            <tbody>
              @foreach($datas as $key => $data)
              <tr>
                <td>{{$data->created_at->format("d-M-Y")}}</td>
                <td>{{$data->invoice_number}}</td>
                <td>{{$data->invoice_name}}</td>
                <td>{{$data->total_count}}</td>
                <td>
                  <div class="modal fade text-left" id="backdrop{{$data->id}}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel4" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
                      <div class="modal-content">
                        <div class="modal-header">
                          <h4 class="modal-title" id="myModalLabel4">Invoice Detail</h4>
                        </div>
                        <div class="modal-body">
                          <b>Invoice Number: {{$data->invoice_number}}</b>
                          <br>
                          <b>Total Quantity Issued : {{$data->total_count}}</b>
                          <hr>
                          <ul>
                            @foreach($data->invoiceDetails as $dd)
                            <li>{{$dd->product_qty}} X {{$dd->productDetails->name ?? ''}}</li>
                            @endforeach
                          </ul>
                        </div>
                        <div class="modal-footer">
                          <button type="button" class="btn btn-light-secondary" data-dismiss="modal">
                            <i class="bx bx-x d-block d-sm-none"></i>
                            <span class="d-none d-sm-block">Close</span>
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>
                  <button class="btn" data-toggle="modal" data-backdrop="false" data-target="#backdrop{{$data->id}}"><i class="fas fa-eye" title="Click to View Making Details"></i></button>
                </td>
                <td>
                  <a href="{{route('SingleInvoiceDownload', $data->id)}}" target="_blank"><button class="btn btn-link">Print</button></a>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
          <div class="d-flex justify-content-center mt-3">
            {{ $datas->links('pagination::bootstrap-4') }}
          </div>
        </div>
        @endif
          <div
            class="modal fade"
            id="invoiceModal"
            tabindex="-1"
            aria-labelledby="invoiceModalLabel"
            aria-hidden="true"
          >
          <div class="modal-dialog modal-fullscreen">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="invoiceModalLabel">
                  Invoice no. {{$invoice_number}}
                </h5>
                <button
                  type="button"
                  class="btn-close"
                  data-bs-dismiss="modal"
                  aria-label="Close"
                ></button>
              </div>
              <form method="post" action="{{route('saveInvoice')}}">
              @csrf
              <div class="modal-body">
                <div class="container mt-4 container-sizing">
                    <!-- Customer Details Section -->
                    <div class="row mb-3">
                      <div class="col-md-6">
                        <label for="customerSelect">Customer</label>
                        <input type="text" class="form-control" id="invoiceName" name="invoice_name" placeholder="Enter Invoice Issuance Name"/ required>
                      </div>
                      <div class="col-md-6">
                        <label for="invoiceNo">Invoice no.</label>
                        <input type="number" name="invoice_number" readonly class="form-control" id="invoiceNo" value="{{$invoice_number}}"/>
                      </div>
                  </div>
                  <label for="customerSelect">Choose Products</label>
                  <div class="row">
                    <div class="col-md-12">
                      <div class="form-group">
                        <button type="button" onclick="newAddon();" class="save-button"> Add New Product </button>
                      </div>
                    </div>
                  </div>
                  <div style="max-height: 500px; min-height: 80px; overflow-y: auto; border: 1px solid #e0e0e0; border-radius: 10px; margin: 18px 0 18px 0; background: #fafbfc; padding: 18px 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.03);" id="addon-scrollable-container">
                    <div id="addon-container"></div>
                  </div>
                  <br><hr>
                  <div class="row">
                    <div class="col-md-5">
                    </div>
                    <div class="col-md-5">
                      <div class="form-group">
                        <label for="totalAmount">Total Count</label>
                        <input type="text" class="form-control text-right" name="total_count" id="totalAmount" placeholder="0" readonly>
                      </div>
                    </div>
                  </div>                  
                  <!-- Total and Balance Due -->
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                  Close
                </button>
                <button style="background: #2CA01C; color: white;" type="submit" class="btn">
                  Save changes
                </button>
              </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </body>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <!-- Select2 JS -->
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  <script type="text/javascript">
    var addonId = 0;
    function newAddon()
    {
      addonId++;
      var newAddon = '<div style="margin-top: 10px" id="row-' + addonId + '" class="row">' +
      '<div class="col-md-4">' +
      '<label>Product/Service</label>' +
      '<select class="form-select" name="product[]" id="productDropdown-' + addonId + '" onchange="fetchProductQuantity(' + addonId + ')"></select>' +
      '</div>' +
      '<div class="col-md-3">' +
      '<label>Current Qty in System</label>' +
      '<input type="text" class="form-control" id="currentQty-' + addonId + '" readonly placeholder="Select a product" style="background-color: #f8f9fa; font-weight: 500;">' +
      '</div>' +
      '<div class="col-md-3">' +
      '<label>Quantity</label>' +
      '<input type="number" class="form-control quantity" required name="quantity[]" max="5000" value="1" placeholder="Quantity" oninput="updateTotalCount()">' +
      '</div>' +
      '<div class="col-md-1">' +
      '<br>' +
      '<div class="input-group-btn">' +
      '<button class="btn" onclick="removeAddon(' + addonId + ');"><i class="fas fa-trash-alt"></i></button>' +
      '</div>' +
      '</div>' +
      '</div>';

      $("#addon-container").append(newAddon);

      fetchProductAndPopulateDropdown("productDropdown-" + addonId);
      updateTotalCount();
    }

    function removeAddon(rowId) {
      $("#row-" + rowId).remove();
    }

    function fetchProductAndPopulateDropdown(dropdownId) {
      fetch('/get-invoice-product-service')
        .then(response => response.json())
        .then(data => {
          const dropdown = document.getElementById(dropdownId);
          dropdown.innerHTML = '<option selected value="">Choose Product</option>';
          data.forEach(category => {
            const option = document.createElement('option');
            option.value = category.id;
            option.textContent = category.name + ' - ' + category.sku;
            dropdown.appendChild(option);
          });

          // Initialize Select2 on the dropdown after populating it
          $('#' + dropdownId).select2({
            placeholder: "Choose Product",
            allowClear: true,
            dropdownParent: $('#invoiceModal') // IMPORTANT: makes it work inside modal
          });
        })
        .catch(error => console.error('Error fetching categories:', error));
    }

    // Fetch and display current quantity for selected product
    function fetchProductQuantity(rowId) {
      const dropdown = document.getElementById('productDropdown-' + rowId);
      const currentQtyInput = document.getElementById('currentQty-' + rowId);
      const productId = dropdown.value;

      if (!productId) {
        currentQtyInput.value = '';
        currentQtyInput.placeholder = 'Select a product';
        return;
      }

      currentQtyInput.value = 'Loading...';
      
      fetch('/get-product-quantity/' + productId)
        .then(response => response.json())
        .then(data => {
          if (data.quantity !== null && data.quantity !== undefined && data.quantity !== '') {
            currentQtyInput.value = data.quantity;
            currentQtyInput.style.color = data.quantity > 0 ? '#28a745' : '#dc3545';
          } else {
            currentQtyInput.value = 'No Quantity added in system';
            currentQtyInput.style.color = '#6c757d';
          }
        })
        .catch(error => {
          console.error('Error fetching product quantity:', error);
          currentQtyInput.value = 'Error loading quantity';
          currentQtyInput.style.color = '#dc3545';
        });
    }

    // Update the total count
    function updateTotalCount() {
      let total = 0;

      // Select all quantity inputs and calculate the total
      document.querySelectorAll(".quantity").forEach((input) => {
        const value = parseFloat(input.value) || 0; // Ensure the value is numeric
        total += value;
      });

      // Update the total amount field
      document.getElementById("totalAmount").value = total;
    }

    // Removed filterTable() as search is now server-side
  </script>
</html>
