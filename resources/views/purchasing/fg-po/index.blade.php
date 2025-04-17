@extends('layouts.app')

@section('title', 'Purchasing | Finish Good PO')

@section('content')
  <div class="row">
    <div class="col">
      <section class="card">
        <header class="card-header" style="display: flex;justify-content: space-between;">
          <h2 class="card-title">All PO</h2>
          <div>
            <a class="btn btn-danger text-end" href="{{ route('pur-fgpos.new-challan') }}"  aria-expanded="false" > <i class="fa fa-plus"></i> New Challan</a>
            <a class="btn btn-primary text-end" href="{{ route('pur-fgpos.create') }}"  aria-expanded="false" > <i class="fa fa-plus"></i> New PO</a>
          </div>
        </header>
        <div class="card-body">
          <div>
            <div class="col-md-5" style="display:flex;">
              <select class="form-control" style="margin-right:10px" id="columnSelect">
                <option selected disabled>Search by</option>
                <option value="1">by Delivery Date</option>
                <option value="2">by Order Date</option>
                <option value="3">by Vendor</option>
              </select>
              <input type="text" class="form-control" id="columnSearch" placeholder="Search By Column"/>

            </div>
          </div>

          <div class="modal-wrapper table-scroll">
            <table class="table table-bordered table-striped mb-0" id="cust-datatable-default">
              <thead>
                <tr>
                  <th>S.No</th>
                  <th>Vendor</th>
                  <th>Job ID</th>
                  <th>Order Date</th>
                  <th>Items</th>
                  <th>status</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($purpos as $key => $row)
                  <tr>
                    <td>{{$key+1}}</td>
                    <td>{{ $row->vendor->name ?? 'N/A' }}</td>
                    <td>{{ $row->doc_code }}-{{ $row->id }}</td>
                    <td>{{ \Carbon\Carbon::parse($row->order_date)->format('d-m-y') }}</td>
                    <td>
                      @foreach($row->details as $value)
                        <span class="badge bg-primary">
                          {{ $value->product->name ?? 'N/A'  }}
                          @if($value->attribute_values)
                            - {{ $value->attribute_values->value }}
                          @endif
                        </span>
                      @endforeach
                    </td>
                    <td></td>
                    <td>
                      <a href="{{ route('pur-fgpos.print', $row->id) }}" class="btn btn-primary btn-xs">
                        <i class="fa fa-print"></i>
                      </a>
                      <a href="{{ route('pur-fgpos.rec', $row->id) }}" class="btn btn-success btn-xs">
                        <i class="fa fa-download"></i>
                      </a>
                      <a href="{{ route('pur-fgpos.edit', $row->id) }}" class="btn btn-warning btn-xs">
                        <i class="fa fa-edit"></i>
                      </a>
                      <!-- Delete Link (with Confirmation) -->
                      <form action="{{ route('pur-fgpos.destroy', $row->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this purchase order?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-xs">
                          <i class="fa fa-trash"></i>
                        </button>
                      </form>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </section>
    </div>
  </div>
@endsection