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
    <!-- Bootstrap Bundle JS (includes Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <title>Invoice</title>
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
        <button
          class="btn btn-create"
          data-bs-toggle="modal"
          data-bs-target="#invoiceModal"
        >
          Create invoice
        </button>
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
                  Invoice no.1001
                </h5>
                <button
                  type="button"
                  class="btn-close"
                  data-bs-dismiss="modal"
                  aria-label="Close"
                ></button>
              </div>
              <div class="modal-body">
                <div class="container mt-4 container-sizing">
                  <form>
                   
                    <!-- Customer Details Section -->
                    <div class="row mb-3">
                      <div class="col-md-3">
                        <label for="customerSelect">Customer</label>
                        <select id="customerSelect" class="form-select">
                          <option>Select a customer</option>
                        </select>
                      </div>
                      <div class="col-md-3 cc-styles">
                        <label for="customerEmail"
                          >Customer email <span>Cc/Bcc</span></label
                        >
                        <input
                          type="email"
                          class="form-control"
                          id="customerEmail"
                          placeholder="Separate emails with a comma"
                        />
                      </div>
                      <div style="margin-left: auto" class="col-md-3">
                        <div style="text-align: right" class="col-md-12">
                          <p style="margin: 0">BALANCE DUE</p>
                          <h4>PRs0.00</h4>
                        </div>
                      </div>
                    </div>

                    <!-- Billing and Dates Section -->
                    <div class="row mb-3">
                      <div class="col-md-2">
                        <label for="billingAddress">Billing address</label>
                        <input
                          type="text"
                          class="form-control"
                          id="billingAddress"
                        />
                      </div>
                      <div class="col-md-2">
                        <label for="terms">Terms</label>
                        <select id="terms" class="form-control">
                          <option>Net 30</option>
                          <option>Net 60</option>
                          <option>Due on receipt</option>
                        </select>
                      </div>
                      <div class="col-md-2">
                        <label for="invoiceDate">Invoice date</label>
                        <input
                          type="date"
                          class="form-control"
                          id="invoiceDate"
                        />
                      </div>
                      <div class="col-md-2">
                        <label for="dueDate">Due date</label>
                        <input type="date" class="form-control" id="dueDate" />
                      </div>
                    </div>

                    <!-- Tags -->
                    <div class="row mb-3">
                      <div class="col-md-6 cc-styles">
                        <label for="tags">Tags <span>Manage tags</span></label>
                        <input
                          type="text"
                          class="form-control"
                          id="tags"
                          placeholder="Start typing to add a tag"
                        />
                      </div>
                    </div>

                    <!-- Balance Due -->

                    <div class="row mb-3">
                      <div class="col-md-12">
                        <label for="invoiceNo">Invoice no.</label>
                        <input
                          type="text"
                          class="form-control"
                          id="invoiceNo"
                          value="1001"
                        />
                      </div>
                    </div>
                    <div class="table-section">
                        <table class="table table-hover">
                          <thead>
                            <tr>
                              <th scope="col"></th>
                              <th scope="col">#</th>
                              <th scope="col">PRODUCT/SERVICE</th>
                              <th scope="col">DESCRIPTION</th>
                              <th scope="col">QTY</th>
                              <th scope="col">RATE</th>
                              <th scope="col">AMOUT</th>
                              <th scope="col"></th>
                            </tr>
                          </thead>
                          <tbody>
                            <tr>
                              <td></td>
                              <td>1</td>
                              <td><input type="form-control"></td>
                              <td><input type="form-control"></td>
                              <td><input type="form-control"></td>
                              <td><input type="form-control"></td>
                              <td><input type="form-control"></td>
                              <td><button class="btn">
                                <i class="fas fa-trash-alt"></i>
                            </button></td>
                            </tr>
                            <tr>
                              <td></td>
                              <td>2</td>
                              <td><input type="form-control"></td>
                              <td><input type="form-control"></td>
                              <td><input type="form-control"></td>
                              <td><input type="form-control"></td>
                              <td><input type="form-control"></td>
                              <td><button class="btn">
                                <i class="fas fa-trash-alt"></i>
                            </button></td>
                            </tr>
                          </tbody>
                        </table>
                      </div>
                      <div class="row justify-content-end">
                        <div class="col-auto">
                          <div class="form-group">
                            <label for="totalAmount">Total</label>
                            <input type="text" class="form-control text-right" id="totalAmount" placeholder="PRs0.00" disabled>
                          </div>
                        </div>
                        <div class="col-auto">
                          <div class="form-group">
                            <label for="balanceDue">Balance due</label>
                            <input type="text" class="form-control text-right" id="balanceDue" placeholder="PRs0.00" disabled>
                          </div>
                        </div>
                      </div>
                      <div class="col-md-6">
                        <div class="form-group">
                          <label for="messageInvoice">Message on invoice</label>
                          <textarea id="messageInvoice" class="form-control" placeholder="This will show up on the invoice."></textarea>
                        </div>
                        <div class="form-group">
                          <label for="messageStatement">Message on statement</label>
                          <textarea id="messageStatement" class="form-control" placeholder="If you send statements to customers, this will show up as the description for this invoice."></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="attachments" class="form-label sizing-text2">Attachments</label>
                            <input type="file" class="form-control" id="attachments" style="display: none;">
                            <label for="attachments" class="btn btn-outline-secondary d-block p-4 text-center attachment" style="border-style: dashed;">
                                Drag/Drop files here or click the icon
                                <small class="text-muted d-block mb-2">Max file size: 20 MB</small>
                            </label>
                            <button class="btn btn-link text-center d-flex m-auto">Show existing</button>
                        </div>
                        <!-- <div class="form-group mt-3">
                          <label>Attachments <span class="text-muted">Maximum size: 20MB</span></label>
                          <div class="custom-file">
                            <input type="file" class="form-control" id="fileAttachment">
                            <label class="form-control-placeholder" for="fileAttachment">Drag/Drop files here or click the icon</label>
                          </div>
                          <button class="btn btn-link">Show existing</button>
                        </div> -->
                      </div>
                  
                      <!-- Total and Balance Due -->
                      
                  </form>
                </div>
              </div>
              <div class="modal-footer">
                <button
                  type="button"
                  class="btn btn-secondary"
                  data-bs-dismiss="modal"
                >
                  Close
                </button>
                <button style="background: #2CA01C; color: white;" type="button" class="btn">
                  Save changes
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </body>
</html>
