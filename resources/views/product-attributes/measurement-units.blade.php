@extends('layouts.app')

@section('title', 'Product Attributes | Measurement Units')

@section('content')
  <div class="row">
    <div class="col">
        <section class="card">
            <header class="card-header">
                <div style="display: flex;justify-content: space-between;">
                    <h2 class="card-title">Measurement Units</h2>
                    <button type="button" class="modal-with-form btn btn-primary" href="#addModal"> <i class="fas fa-plus"></i> Add Account</button>
                </div>
                @if ($errors->has('error'))
                    <strong class="text-danger">{{ $errors->first('error') }}</strong>
                @endif
            </header>
            <div class="card-body">
            <div class="modal-wrapper table-scroll">
                <table class="table table-bordered table-striped mb-0" id="cust-datatable-default">
                <thead>
                    <tr>
                    <th>S.No</th>
                    <th>Name</th>
                    <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($items as $item)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $item->name }}</td> <!-- Adjusted based on the entity -->
                        <td>
                        <!-- Edit button with dynamic data attributes -->
                        <button class="btn btn-warning btn-sm edit-entity-button">
                            Edit
                        </button>
                        <!-- Delete form -->
                        <form action="{{ route('product-measurement-units.destroy', $item->id) }}" method="POST" style="display:inline-block;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</button>
                        </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                </table>
            </div>
            </div>
        </section>

        <div id="addModal" class="modal-block modal-block-primary mfp-hide">
            <section class="card">
                <form method="post" action="{{ route('product-measurement-units.store') }}" enctype="multipart/form-data" onkeydown="return event.key != 'Enter';">
                    @csrf
                    <header class="card-header">
                        <h2 class="card-title">New Measurement Unit</h2>
                    </header>
                    <div class="card-body">
                        <div class="form-group mb-3">
                            <label>Name<span style="color: red;"><strong>*</strong></span></label>
                            <input type="text" class="form-control" placeholder="Name" name="name" required>
                        </div>
                    </div>
                    <footer class="card-footer">
                        <div class="row">
                            <div class="col-md-12 text-end">
                                <button type="submit" class="btn btn-primary">Create</button>
                                <button class="btn btn-default modal-dismiss">Cancel</button>
                            </div>
                        </div>
                    </footer>
                </form>
            </section>
        </div>
    </div>
  </div>



@endsection
