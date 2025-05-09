<?php

namespace App\Http\Controllers;

use App\Models\ChartOfAccounts;
use App\Models\ProductCategory;
use App\Models\Products;
use App\Models\PurPO;
use App\Models\PurPoAttachment;
use App\Models\PurPosDetail;
use App\Services\myPDF;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurPOController extends Controller
{
    public function index()
    {
        $purpos = PurPo::with(['vendor', 'details.product'])->get();
        return view('purchasing.po.index', compact('purpos'));
    }

    public function create()
    {
        $prodCat = ProductCategory::all();  // Get all product categories
        $coa = ChartOfAccounts::all();  // Get all product categories
        $products = Products::all();  // Get all product categories

        return view('purchasing.po.create', compact('prodCat', 'coa', 'products'));
    }

    public function store(Request $request)
    {
        DB::beginTransaction();  // Begin the transaction
    
        try {
            // Validate the incoming request data
            $validatedData = $request->validate([
                'vendor_id' => 'required|exists:chart_of_accounts,id', // Ensure vendor exists
                'category_id' => 'required|exists:product_categories,id',
                'order_date' => 'required|date',
                'delivery_date' => 'nullable|date',
                'details' => 'required|array',
                'details.*.item_id' => 'required|string|max:255',
                'details.*.width' => 'required|numeric|min:0',
                'details.*.description' => 'nullable|string|max:255',
                'details.*.item_rate' => 'required|numeric|min:0',
                'details.*.item_qty' => 'required|numeric|min:0',
                'other_exp' => 'nullable|numeric|min:0',
                'bill_discount' => 'nullable|numeric|min:0',
                'att.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048', // Image validation
            ]);
    
            // Fetch the category code based on category_id
            $category = ProductCategory::findOrFail($validatedData['category_id']);
            $categoryCode = $category->cat_code; // Assuming 'code' is the column for category code
    
            // Generate PO code in the format: PO-SequenceNo-CategoryCode
            $latestPo = PurPo::latest()->first();
            $sequenceNo = $latestPo ? $latestPo->id + 1 : 1;
            
            // Pad the number with leading zeros to make it 5 digits
            $sequencePadded = str_pad($sequenceNo, 5, '0', STR_PAD_LEFT);
            
            $poCode = "PO-{$categoryCode}-{$sequencePadded}";
    
            // Create the Purchase Order
            $purpo = PurPo::create([
                'vendor_id' => $validatedData['vendor_id'],
                'category_id' => $validatedData['category_id'],
                'po_code' => $poCode,  // Use the generated PO code
                'order_date' => $validatedData['order_date'],
                'delivery_date' => $validatedData['delivery_date'],
                'other_exp' => $validatedData['other_exp'] ?? 0,
                'bill_discount' => $validatedData['bill_discount'] ?? 0,
            ]);
    
            // Store Purchase Order Details
            foreach ($validatedData['details'] as $detail) {
                PurPosDetail::create([
                    'pur_pos_id' => $purpo->id, // Foreign key
                    'item_id' => $detail['item_id'],
                    'width' => $detail['width'],
                    'description' => $detail['description'],
                    'item_rate' => $detail['item_rate'],
                    'item_qty' => $detail['item_qty'],
                ]);
            }
    
            // Handle Images (if provided)
            if ($request->hasFile('att')) {
                foreach ($request->file('att') as $file) {
                    $filePath = $file->store('purchase_orders', 'public'); // Store in storage/app/public/purchase_orders
    
                    PurPoAttachment::create([
                        'pur_po_id' => $purpo->id,
                        'att_path' => $filePath,
                    ]);
                }
            }
    
            // Commit the transaction if all operations are successful
            DB::commit();
    
            return redirect()->route('pur-pos.index')->with('success', 'Purchase Order created successfully!');
    
        } catch (\Exception $e) {
            // Rollback the transaction if any error occurs
            DB::rollBack();
    
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
    
    public function show(PurPos $purpo)
    {
        $purpo->load('details'); // Eager load details

        return view('purpos.show', compact('purpo'));
    }

    public function edit($id)
    {
        $purPo = PurPo::with(['details', 'attachments'])->findOrFail($id);
        $prodCat = ProductCategory::all(); // Fetch all product categories
        $coa = ChartOfAccounts::all(); // Fetch all vendors (Chart of Accounts)
        $products = Products::all(); // Assuming this model fetches products
    
        return view('purchasing.po.edit', compact('purPo', 'prodCat', 'coa', 'products'));
    }

    public function receiving($id)
    {
        $purpo = PurPo::with(['details', 'attachments'])->findOrFail($id);
        return view('purchasing.po.receiving', compact('purpo'));
    }

    public function storeReceiving(Request $request)
    {
        $request->validate([
            'po_id' => 'required|exists:pur_fgpos,id',
            'rec_date' => 'required|date',
            'received_qty' => 'required|array',
            'received_qty.*' => 'nullable|integer|min:1',
        ]);

        // Create a new receiving record
        $receiving = PurFGPORec::create([
            'po_id' => $request->fgpo_id,
            'rec_date' => $request->rec_date,
        ]);

        // Loop through received items and store details
        foreach ($request->received_qty as $poDetailId => $receivedQty) {
            if ($receivedQty > 0) {
                // Fetch the ordered product details
                $orderDetail = PurFGPODetails::find($poDetailId);

                if ($orderDetail) {
                    PurFGPORecDetails::create([
                        'pur_pos_rec_id' => $receiving->id,
                        'product_id' => $orderDetail->product_id,
                        'variation_id' => $orderDetail->variation_id,
                        'sku' => $orderDetail->sku ?? '',
                        'qty' => $receivedQty,
                    ]);
                }
            }
        }

        return redirect()->route('pur-fgpos.index')->with('success', 'Receiving recorded successfully.');
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();
    
        try {
            // Validate the incoming data
            $validatedData = $request->validate([
                'vendor_id' => 'required|exists:chart_of_accounts,id',
                'category_id' => 'required|exists:product_categories,id',
                'order_date' => 'required|date',
                'delivery_date' => 'nullable|date',
                'details' => 'required|array',
                'details.*.item_id' => 'required|string|max:255',
                'details.*.item_rate' => 'required|numeric|min:0',
                'details.*.item_qty' => 'required|numeric|min:0',
                'other_exp' => 'nullable|numeric|min:0',
                'bill_discount' => 'nullable|numeric|min:0',
                'att.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            ]);
    
            // Fetch the existing Purchase Order
            $purPo = PurPo::findOrFail($id);
    
            // Update PO data
            $purPo->update([
                'vendor_id'     => $request->input('vendor_id'),
                'category_id'   => $request->input('category_id'),
                'order_date'    => $request->input('order_date'),
                'delivery_date' => $request->input('delivery_date'),
                'other_exp'     => $request->input('other_exp', 0),
                'bill_discount' => $request->input('bill_discount', 0),
                'net_amount'    => $this->calculateNetAmount($request),
            ]);
    
            // Delete existing details
            $purPo->itemdetails()->delete();
    
            // Recreate details
            foreach ($request->input('details') as $detail) {
                $purPo->itemdetails()->create([
                    'item_id'   => $detail['item_id'],
                    'item_rate' => $detail['item_rate'],
                    'item_qty'  => $detail['item_qty'],
                    'item_total'=> $detail['item_rate'] * $detail['item_qty'],
                ]);
            }
    
            // Handle Attachments (if needed)
            if ($request->hasFile('att')) {
                foreach ($request->file('att') as $file) {
                    // Save files and store their paths if required
                }
            }
    
            DB::commit();
    
            return redirect()->route('pur-pos.show', $purPo->id)->with('success', 'PO updated successfully!');
    
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
    

    public function destroy(PurPo $purpo)
    {
        $purpo->details()->delete(); // Delete associated details
        $purpo->delete(); // Delete the Purchase Order

        return redirect()->route('purpos.index')->with('success', 'Purchase Order deleted successfully!');
    }

    public function indexAPI()
    {
        $purpos = PurPO::paginate(10); // Add pagination for large datasets

        return PurPOResource::collection($purpos);
    }

    public function getPoCodes(Request $request)
    {
        $productId = $request->product_id;

        $poIds = PurPosDetail::where('item_id', $productId)
                    ->pluck('pur_pos_id')
                    ->unique();

        $poCodes = PurPo::whereIn('id', $poIds)
                    ->pluck('po_code');

        return response()->json([
            'po_ids' => $poIds,
            'po_codes' => $poCodes,
        ]);
    }

    public function getWidth(Request $request)
    {
        $productId = $request->product_id;
        $poId = $request->po_id;

        $width = PurPosDetail::where('item_id', $productId)
            ->where('pur_pos_id', $poId)
            ->value('width');

        return response()->json(['width' => $width]);
    }

    public function showAPI(PurPO $purpo)
    {
        return new PurPOResource($purpo);
    }

    public function print($id)
    {
        // Fetch the purchase order with related data
        $purpos = PurPo::with(['vendor', 'details.product', 'attachments', 'details.product'])->findOrFail($id);

        if (! $purpos) {
            abort(404, 'Purchase Order not found.');
        }

        $pdf = new MyPDF;

        // Set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('TGM');
        $pdf->SetTitle('PO-'.$purpos->id);
        $pdf->SetSubject('PO-'.$purpos->id);
        $pdf->SetKeywords('PO, TCPDF, PDF');

        // Add a page
        $pdf->AddPage();
        $pdf->setCellPadding(1.2);

        // Purchase Order Heading
        $heading = '<h1 style="font-size:20px;text-align:center;font-style:italic;text-decoration:underline;color:#17365D">Purchase Order</h1>';
        $pdf->writeHTML($heading, true, false, true, false, '');

        // Purchase Order Details Table
        $html = '<table style="margin-bottom:10px">
            <tr>
                <td style="font-size:10px;font-weight:bold;color:#17365D">PO No: <span style="color:#000">'.$purpos->po_code.'</span></td>
                <td style="font-size:10px;font-weight:bold;color:#17365D">Date: <span style="color:#000">'.\Carbon\Carbon::parse($purpos->order_date)->format('d-m-Y').'</span></td>
                <td style="font-size:10px;font-weight:bold;color:#17365D">Vendor: <span style="text-decoration: underline;color:#000">'.$purpos->vendor->name.'</span></td>
            </tr>
        </table>';

        $pdf->writeHTML($html, true, false, true, false, '');

        // Items Table Header
        $html= '<table border="0.3" style="text-align:center;margin-top:15px">
            <tr>
                <th width="5%" style="font-size:10px;font-weight:bold;color:#17365D">S/N</th>
                <th width="28%" style="font-size:10px;font-weight:bold;color:#17365D">Item ID-Name</th>
                <th width="18%" style="font-size:10px;font-weight:bold;color:#17365D">Description</th>
                <th width="8%" style="font-size:10px;font-weight:bold;color:#17365D">Width</th>
                <th width="15%" style="font-size:10px;font-weight:bold;color:#17365D">Qty</th>
                <th width="12%" style="font-size:10px;font-weight:bold;color:#17365D">Rate</th>
                <th width="15%" style="font-size:10px;font-weight:bold;color:#17365D">Total</th>
            </tr>
       ';
        $total_qty = 0;
        $count = 0;

        foreach ($purpos->details as $item) {
            $count++;
            $product_name = $item->product->name ?? 'N/A'; // Fetch product name safely
            $sku = $item->product->sku ?? 'N/A'; // Fetch product name safely
            $id = $item->product->id ?? 'N/A'; // Fetch product name safely
            $product_m_unit = $item->product->measurement_unit ?? 'N/A'; // Assuming 'measurement_unit' is the column name
            $description = $item->description ?? 'N/A'; // Assuming 'measurement_unit' is the column name

            $total = $item->item_rate * $item->item_qty;
            $total_qty += $item->item_qty;
            $formattedTotal = number_format($total, 2); // 2 decimal places, e.g., 1,234.50

            $html .= '<tr>
                <td width="5%" style="font-size:10px;text-align:center;">'.$count.'</td>
                <td width="28%" style="font-size:10px;text-align:center;">'.$id.'-'.$product_name.'</td>
                <td width="18%" style="font-size:10px;">'.$description.'</td>
                <th width="8%" style="font-size:10px;">'.$item->width.'</th>
                <td width="15%" style="font-size:10px;text-align:center;">'.$item->item_qty.' '.$product_m_unit.'</td>
                <td width="12%" style="font-size:10px;text-align:center;">'.$item->item_rate.'</td>
                <td width="15%" style="font-size:10px;text-align:center;">'.$formattedTotal.'</td>
            </tr>';
        }

        $html .= '</table>';
        $pdf->writeHTML($html, true, false, true, false, '');

        // Summary Table
        $pdf->SetFont('helvetica', 'B', 10);
        $summary = '<table border="0.3" cellpadding="2" width="35%">
            <tr><td><strong>Total Quantity:</strong></td><td>'.$total_qty.'</td></tr>
            <tr><td><strong>Total Item:</strong></td><td>'.$count.'</td></tr>
        </table>';
        $pdf->writeHTML($summary, true, false, true, false, '');

        // Attachments (Images)
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 10, 'Attachments:', 0, 1, 'L');

        $imageWidth = 50;
        $imageHeight = 50;
        $margin = 10;
        $maxX = $pdf->getPageWidth() - $pdf->getMargins()['right'];
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $rowHeight = $imageHeight + 5; // image + spacing
        
        foreach ($purpos->attachments as $attachment) {
            $imagePath = storage_path('app/public/' . $attachment->att_path);
        
            if (file_exists($imagePath)) {
                // Check for page height before drawing
                $availableHeight = $pdf->getPageHeight() - $pdf->GetY() - $pdf->getBreakMargin();
                if ($availableHeight < $rowHeight) {
                    $pdf->AddPage();
                    $x = $pdf->GetX();
                    $y = $pdf->GetY();
                }
        
                // If image goes beyond right margin, wrap to next row
                if ($x + $imageWidth > $maxX) {
                    $x = $pdf->GetMargins()['left'];
                    $y += $rowHeight;
                }
        
                $pdf->Image($imagePath, $x, $y, $imageWidth, $imageHeight, '', '', '', false, 300, '', false, false, 0, false, false, false);
        
                // Update X for next image
                $x += $imageWidth + $margin;
            }
        }
        
        // Move cursor below last image row
        $pdf->SetY($y + $rowHeight);

        // Move to the bottom of the page
        $pdf->SetY(-50); // Adjust value if needed to position correctly

        $lineWidth = 60; // Line width in mm
        $yPosition = $pdf->GetY(); // Get current Y position for alignment

        // Draw lines for signatures
        $pdf->Line(28, $yPosition, 20 + $lineWidth, $yPosition); // Approved By
        $pdf->Line(130, $yPosition, 120 + $lineWidth, $yPosition); // Received By

        $pdf->Ln(5); // Move cursor below the line

        // Text below the lines
        $pdf->SetXY(23, $yPosition);
        $pdf->Cell($lineWidth, 10, 'Approved By', 0, 0, 'C');

        $pdf->SetXY(125, $yPosition);
        $pdf->Cell($lineWidth, 10, 'Received By', 0, 0, 'C');

        // Output the PDF
        $pdf->Output('PO-'.$purpos->id.'.pdf', 'I');
    }
}
