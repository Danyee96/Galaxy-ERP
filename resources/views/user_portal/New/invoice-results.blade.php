<div class="invoice-filter-summary row g-2 mb-3" role="status" aria-live="polite">
  <div class="col-6 col-lg"><small class="text-muted">Total invoices</small><strong class="d-block">{{ number_format($summary->invoice_count) }}</strong></div>
  <div class="col-6 col-lg"><small class="text-muted">Total items issued</small><strong class="d-block">{{ number_format($summary->item_count) }}</strong></div>
  @foreach(['Total amount', 'Paid amount', 'Outstanding amount'] as $label)<div class="col-6 col-lg"><small class="text-muted">{{ $label }}</small><span class="d-block text-muted">Not recorded</span></div>@endforeach
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
              @if($datas->isEmpty())<tr><td colspan="6" class="text-center text-muted">No invoices match these filters.</td></tr>@endif
            </tbody>
          </table>
          <div class="d-flex justify-content-center mt-3">
            {{ $datas->links('pagination::bootstrap-4') }}
          </div>
        </div>
