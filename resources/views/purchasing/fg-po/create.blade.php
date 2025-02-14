@extends('layouts.app')

@section('title', 'Purchasing | New PO')

@section('content')
  <div class="row">
    <form action="{{ route('pur-fgpos.store') }}" method="POST" enctype="multipart/form-data">
      @csrf
      @if ($errors->has('error'))
        <strong class="text-danger">{{ $errors->first('error') }}</strong>
      @endif
      <div class="row">
        <div class="col-12 col-md-12 mb-3">
          <section class="card">
            <header class="card-header">
              <div style="display: flex;justify-content: space-between;">
                <h2 class="card-title">New PO</h2>
              </div>
            </header>
            <div class="card-body">
              <div class="row">
                <div class="col-12 col-md-1 mb-3">
                  <label>PO #</label>
                  <input type="number" class="form-control"  placeholder="PO #" disabled/>
                </div>
                <div class="col-12 col-md-2 mb-3">
                  <label>Vendor Name</label>
                  <select data-plugin-selecttwo class="form-control select2-js" name="vendor_name" id="vendor_name" required>  <!-- Added name attribute for form submission -->
                    <option value="" selected disabled>Select Vendor</option>
                    @foreach ($coa as $item)
                      <option value="{{ $item->id }}">{{ $item->name }}</option> 
                    @endforeach
                  </select>
                </div>
                <div class="col-12 col-md-2 mb-3">
                  <label>Order Date</label>
                  <input type="date" name="order_date" class="form-control" id="order_date" value="<?php echo date('Y-m-d'); ?>"   placeholder="Order Date" required/>
                </div>

                <div class="col-12 col-md-2 mb-3">
                  <label>Item Name <a href="#" ><i class="fa fa-plus"></i></a></label>
                  <select data-plugin-selecttwo class="form-control select2-js" id="item_name" required>  <!-- Added name attribute for form submission -->
                    <option value="" selected disabled>Select Item</option>
                    @foreach ($articles as $item)
                      <option value="{{ $item->id }}"> {{ $item->sku }} - {{ $item->name }}</option> 
                    @endforeach
                  </select>
                </div>

                <div class="col-12 col-md-2 mb-3">
                  <label>Hijab</label>
                  <select data-plugin-selecttwo class="form-control select2-js" required>  <!-- Added name attribute for form submission -->
                    <option value="" selected disabled>Select Hijab</option>
                    <option> Hijab Included </option> 
                    <option> Hijab Not Included </option>
                  </select>
                </div>

               
                <div class="col-12 col-md-1 mb-3">
                  <label>Width</label>
                  <input type="number" class="form-control" name="width" id="width" value=0 placeholder="Width"/>
                </div>
                <div class="col-12 col-md-1 mb-3" >
                  <label>Consumption</label>
                  <input type="number" class="form-control" name="consumption" id="consumption" value=0 placeholder="Consumption"/>
                </div>
                <div class="col-12 col-md-1">
                  <label>Item Variations</label>
                  <button type="button" class="d-block btn btn-success" id="generate-variations-btn" >Generate</button>
                </div>
              </div>
              <div class="col-12 col-md-5 mt-3 ">
                <table class="table table-bordered" id="variationsTable">
                  <thead>
                    <tr>
                      <th>S.No</th>
                      <th>SKU</th>
                      <th>Quantity</th>
                      <th>Action</th>
                    </tr>
                  </thead>
                  <tbody id="variationsTableBody">
                  </tbody>
                </table>
              </div>
            </div>
          </section>
        </div>

        <div class="col-12 col-md-7 mb-3">
          <section class="card">
            <header class="card-header" style="display: flex;justify-content: space-between;">
              <h2 class="card-title">Fabric Details</h2>
            </header>
            <div class="card-body">
              <table class="table table-bordered" id="myTable">
                <thead>
                  <tr>
                    <th width="25%">Fabric</th>
                    <th width="25%">Description</th>
                    <th>Rate</th>
                    <th>Qty</th>
                    <th>Unit</th>
                    <th>Total</th>
                    <th width="10%"></th>
                  </tr>
                </thead>
                <tbody id="PurPOTbleBody">
                  <tr>
                    <td width="25%">
                      <select data-plugin-selecttwo class="form-control select2-js" name="details[0][item_id]" required>  <!-- Added name attribute for form submission -->
                        <option value="" selected disabled>Select Item</option>
                        @foreach ($fabrics as $item)
                          <option value="{{ $item->id }}">{{$item->sku }} - {{$item->name }}</option> 
                        @endforeach
                      </select>  
                    </td>
                    <td width="25%"><input type="text" name="details[0][for]" class="form-control" placeholder="Description" required/></td>
                    <td><input type="number" name="details[0][item_rate]"  id="item_rate_0" onchange="rowTotal(0)" step="any" value="0" class="form-control" placeholder="Rate" required/></td>
                    <td><input type="number" name="details[0][item_qty]"   id="item_qty_0" onchange="rowTotal(0)" step="any" value="0" class="form-control" placeholder="Quantity" required/></td>
                    <td></td>
                    <td><input type="number" id="item_total_0" class="form-control" placeholder="Total" disabled/></td>
                    <td width="10%">
                      <button type="button" onclick="removeRow(this)" class="btn btn-danger btn-xs" tabindex="1"><i class="fas fa-times"></i></button>
                      <button type="button" class="btn btn-primary btn-xs"  onclick="addNewRow()" ><i class="fa fa-plus"></i></button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </section>
        </div>

        <div class="col-12 col-md-5 mb-3">
          <section class="card">
            <header class="card-header" style="display: flex;justify-content: space-between;">
              <h2 class="card-title">Voucher (Challan #)</h2>
              <div>
                <a class="btn btn-danger text-end" aria-expanded="false" onclick="generateVoucher()">Generate Challan</a>
              </div>
            </header>
            <div class="card-body">
              <div class="row pb-4">
                <div class="col-12 mt-3" id="voucher-container">
                  
                </div>
              </div>
            </div>
          </section>
        </div>

        <div class="col-12 col-md-12">
          <section class="card">
            <header class="card-header" style="display: flex;justify-content: space-between;">
              <h2 class="card-title">Summary</h2>
            </header>
            <div class="card-body">
              <div class="row pb-4">
                <div class="col-12 col-md-2">
                  <label>Total Fabric Quantity</label>
                  <input type="number" class="form-control" id="total_fab" placeholder="Total Fabric" disabled/>
                </div>

                <div class="col-12 col-md-2">
                  <label>Total Fabric Amount</label>
                  <input type="number" class="form-control" id="total_fab_amt" placeholder="Total Fabric Amount" disabled />
                </div>

                <div class="col-12 col-md-2">
                  <label>Total Units (Estimated)</label>
                  <input type="number" class="form-control" id="total_units" placeholder="Total Units" disabled/>
                </div>
                
                <div class="col-12 col-md-3">
                  <label>Attachement </label>
                  <input type="file" class="form-control" name="att[]" multiple accept="image/png, image/jpeg, image/jpg, image/webp">
                </div>

                <div class="col-12 pb-sm-3 pb-md-0 text-end">
                  <h3 class="font-weight-bold mb-0 text-5 text-primary">Net Amount</h3>
                  <span>
                    <strong class="text-4 text-primary">PKR <span id="netTotal" class="text-4 text-danger">0.00 </span></strong>
                  </span>
                </div>
              </div>
            </div>

            <footer class="card-footer text-end">
              <a class="btn btn-danger" href="{{ route('pur-fgpos.index') }}" >Discard</a>
              <button type="submit" class="btn btn-primary">Create</button>
            </footer>
          </section>
        </div>
      </div>
    </form>
  </div>
  <script>

    var index=1;

    function removeRow(button) {
      var tableRows = $("#PurPOTbleBody tr").length;
      if(tableRows>1){
        var row = button.parentNode.parentNode;
        row.parentNode.removeChild(row);
        index--;	
      } 
      tableTotal();
    }

    function addNewRow(){
      var lastRow =  $('#PurPOTbleBody tr:last');
      latestValue=lastRow[0].cells[0].querySelector('select').value;

      if(latestValue!=""){
        var table = document.getElementById('myTable').getElementsByTagName('tbody')[0];
        var newRow = table.insertRow(table.rows.length);

        var cell1 = newRow.insertCell(0);
        var cell2 = newRow.insertCell(1);
        var cell3 = newRow.insertCell(2);
        var cell4 = newRow.insertCell(3);
        var cell5 = newRow.insertCell(4);
        var cell6 = newRow.insertCell(5);
        var cell7 = newRow.insertCell(6);

        cell1.innerHTML  = '<select data-plugin-selecttwo class="form-control select2-js" name="details['+index+'][item_id]">'+
                            '<option value="" disabled selected>Select Category</option>'+
                            @foreach ($fabrics as $item)
                              '<option value="{{ $item->id }}">{{$item->sku }} - {{$item->name }}</option>'+
                            @endforeach
                          '</select>';
        cell2.innerHTML  = '<input type="text" name="details['+index+'][for]" class="form-control" placeholder="Description" required/>';
        cell3.innerHTML  = '<input type="number" name="details['+index+'][item_rate]" step="any" id="item_rate_'+index+'" value="0" onchange="rowTotal('+index+')" class="form-control" placeholder="Rate" required/>';
        cell4.innerHTML  = '<input type="number" name="details['+index+'][item_qty]" step="any" id="item_qty_'+index+'" value="0" onchange="rowTotal('+index+')" class="form-control" placeholder="Quantity" required/>';
        cell5.innerHTML  = '';
        cell6.innerHTML  = '<input type="number" id="item_total_'+index+'" class="form-control" placeholder="Total" disabled/>';
        cell7.innerHTML  = '<button type="button" onclick="removeRow(this)" class="btn btn-danger btn-xs" tabindex="1"><i class="fas fa-times"></i></button> '+
                          '<button type="button" class="btn btn-primary btn-xs" onclick="addNewRow()" ><i class="fa fa-plus"></i></button>';
        index++;
        
        tableTotal();
      }
      $('#myTable select[data-plugin-selecttwo]').select2();

    }

    function rowTotal(index){
      var item_rate = parseFloat($('#item_rate_'+index+'').val());
      var item_qty = parseFloat($('#item_qty_'+index+'').val());   
      var item_total = item_rate * item_qty;

      $('#item_total_'+index+'').val(item_total.toFixed());
      
      tableTotal();
    }

    function tableTotal(){
      var totalQuantity=0;
      var totalAmount=0;
      var totalUnits=0;

      var tableRows = $("#PurPOTbleBody tr").length;
      var table = document.getElementById('myTable').getElementsByTagName('tbody')[0];

      var variationTableRows = $("#variationsTableBody tr").length;
      var variationTable = document.getElementById('variationsTable').getElementsByTagName('tbody')[0];

      for (var i = 0; i < tableRows; i++) {
        var currentRow =  table.rows[i];
        totalQuantity = totalQuantity + Number(currentRow.cells[3].querySelector('input').value);
        totalAmount = totalAmount + Number(currentRow.cells[5].querySelector('input').value);
      }

      for (var j = 0; j < variationTableRows; j++) {
        var currtRow =  variationTable.rows[j];
        totalUnits = totalUnits + Number(currtRow.cells[2].querySelector('input').value);
      }

      $('#total_fab').val(totalQuantity);
      $('#total_fab_amt').val(totalAmount.toFixed());
      $('#total_units').val(totalUnits.toFixed());

      netTotal();
    }

    function netTotal(){
      var netTotal = 0;
      var total = Number($('#total_fab_amt').val());

      netTotal = total;
      netTotal = netTotal.toFixed(0);
      FormattednetTotal = formatNumberWithCommas(netTotal);
      document.getElementById("netTotal").innerHTML = '<span class="text-4 text-danger">'+FormattednetTotal+'</span>';
      $('#net_amount').val(netTotal);
    }
    function formatNumberWithCommas(number) {
      // Convert number to string and add commas
      return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }

    function generateVoucher() {
      document.getElementById("voucher-container").innerHTML = "";

      // Sample Data (Replace with dynamic values if needed)
      let vendorName = document.querySelector("#vendor_name option:checked").textContent;
      let date = $('#order_date').val();
      let purchaseOrderNo = "PO-";
      let designName = document.querySelector("#item_name option:checked").textContent;

      let items = [];
      let rows = document.querySelectorAll("#PurPOTbleBody tr");

      rows.forEach((row, key) => {
        let fabric = row.querySelector("select").options[row.querySelector("select").selectedIndex].text;
        let description = row.querySelector("input[name='details["+key+"][for]']").value;
        let rate = row.querySelector("input[name='details["+key+"][item_rate]']").value;
        let quantity = row.querySelector("input[name='details["+key+"][item_qty]']").value;
        let total = row.querySelector("#item_total_"+(key)).value;

        if (fabric && quantity && rate) { // Ensure required fields are filled
          items.push({ fabric, description, rate, quantity, total });
        }
      });
      
      let totalAmount = items.reduce((sum, item) => sum + parseFloat(item.total || 0), 0);

      // Construct the HTML for the voucher
      let voucherHTML = `
        <div class="border p-3 mt-3">
          <h3 class="text-center text-dark">Payment Voucher</h3>
          <hr>
          
          <!-- Vendor, PO No, Item, and Date in One Line -->
          <div class="d-flex justify-content-between text-dark">
            <p class="text-dark"><strong>Vendor:</strong> ${vendorName}</p>
            <p class="text-dark"><strong>PO No:</strong> ${purchaseOrderNo}</p>
            <p class="text-dark"><strong>Item:</strong> ${designName}</p>
            <p class="text-dark"><strong>Date:</strong> ${date}</p>
          </div>

          <table class="table table-bordered mt-3">
            <thead>
                <tr>
                    <th>Fabric Name</th>
                    <th>Description</th>
                    <th>Quantity</th>
                    <th>Rate</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                ${items.map(item => `
                    <tr>
                        <td>${item.fabric}</td>
                        <td>${item.description}</td>
                        <td>${item.quantity}</td>
                        <td>${item.rate}</td>
                        <td>${item.total}</td>
                    </tr>`).join('')}
            </tbody>
          </table>
          
          <h4 class="text-end text-dark"><strong>Total Amount:</strong> ${totalAmount} PKR</h4>

          <div class="d-flex justify-content-between mt-4">
            <div>
              <p class="text-dark"><strong>Authorized By:</strong></p>
              <p>________________________</p>
            </div> 
          </div>
        </div>
      `;

      // Insert the voucher into the voucher-container div
      document.getElementById("voucher-container").innerHTML = voucherHTML;
    }

    $("#generate-variations-btn").click(function () {
      let tableBody = $("#variationsTable tbody");
      tableBody.empty();

      let productId = $("#item_name").val(); // Get selected product ID

      if (!productId) {
        alert("Please select an item first.");
        return;
      }

      $.ajax({
        url: `/productDetails/${productId}`, // Laravel route
        type: "GET",
        dataType: "json",
        success: function (response) {
            let tableBody = $("#variationsTable tbody");
            tableBody.empty(); // Clear previous rows

            if (response.variations.length > 0) {
                response.variations.forEach((variation, index) => {
                    let row = `<tr>
                        <td>${index + 1}</td>
                        <td>${variation.sku}
                          <input type="hidden" name="item_variation_value_id[]" class="form-control" placeholder="Quantity" />
                          <input type="hidden" name="item_sku[]" class="form-control" placeholder="Quantity" />
                        </td>
                        <td><input type="number" onchange="tableTotal()" name="item_qty[]" class="form-control" placeholder="Quantity" /></td>
                        <td><button class="btn btn-danger btn-sm delete-row">Delete</button></td>
                    </tr>`;
                    tableBody.append(row);
                });
            } else {
                tableBody.append(`<tr><td colspan="5" class="text-center">No variations found.</td></tr>`);
            }
        },
        error: function (error) {
            console.error("Error fetching product details:", error);
            alert("Failed to fetch product details.");
        }
      });
    });

    $(document).on("click", ".delete-row", function () {
      $(this).closest("tr").remove();
    });
  </script>
@endsection