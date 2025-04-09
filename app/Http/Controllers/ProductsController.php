<?php

namespace App\Http\Controllers;

use App\Models\ProductAttachements;
use App\Models\ProductAttributes;
use App\Models\ProductCategory;
use App\Models\Products;
use App\Models\ProductVariations;
use App\Models\ProductAttributesValues;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

use Illuminate\Http\Request;

class ProductsController extends Controller
{
    public function index()
    {
        $products = Products::with('category')->get();

        return view('products.index', compact('products'));
    }

    public function create()
    {
        $prodCat = ProductCategory::all();  // Get all product categories
        $attributes = ProductAttributes::with('values')->get();

        return view('products.create', compact('prodCat', 'attributes'));
    }

    public function store(Request $request)
    {
        try {
            // Validate the request
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'category_id' => 'required|exists:product_categories,id',
                'measurement_unit' => 'nullable|string|max:50',
                'width' => '',
                'item_type' => 'nullable|string|max:50',
                'price' => 'required|numeric|min:0',
                'sale_price' => 'required|numeric|min:0',
                'purchase_note' => 'nullable|string',
                'opening_stock' => 'required|numeric|min:0',
                'prod_att' => 'nullable|array',
                'prod_att.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'variations' => 'nullable|array',
                'variations.*.price' => 'required|numeric|min:0',
                'variations.*.stock' => 'required|numeric|min:0',
                'variations.*.attribute_id' => 'required|exists:product_attributes,id',
                'variations.*.attribute_value_id' => 'required|exists:product_attributes_values,id',
            ]);
    
            // Wrap entire logic in DB transaction
            DB::beginTransaction();
    
            // Get category code
            $category = ProductCategory::findOrFail($validatedData['category_id']);
            $categoryCode = $category->cat_code;
    
            // Get latest product in this category
            $lastProduct = Products::where('category_id', $validatedData['category_id'])
                ->orderByDesc('id')
                ->first();
    
            if ($lastProduct && preg_match('/(\d+)$/', $lastProduct->sku, $matches)) {
                $lastNumber = (int) $matches[1];
            } else {
                $lastNumber = 0;
            }
    
            $nextSequence = str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
            $sku = $categoryCode . '-' . $nextSequence;
    
            // Assign SKU
            $validatedData['sku'] = $sku;
    
            // Create the product
            $product = Products::create($validatedData);
    
            // Handle variations
            if ($request->has('variations')) {
                foreach ($request->variations as $variation) {
                    $attrValue = ProductAttributesValues::findOrFail($variation['attribute_value_id']);
                    $variationSlug = Str::slug($attrValue->value);
                    $variationSku = $sku . '-' . strtoupper($variationSlug);
    
                    ProductVariations::create([
                        'product_id' => $product->id,
                        'price' => $variation['price'],
                        'stock' => $variation['stock'],
                        'attribute_id' => $variation['attribute_id'],
                        'attribute_value_id' => $variation['attribute_value_id'],
                        'sku' => $variationSku,
                    ]);
                }
            }
    
            // Handle images
            if ($request->hasFile('prod_att')) {
                foreach ($request->file('prod_att') as $image) {
                    $imagePath = $image->store('products/images', 'public');
                    ProductAttachements::create([
                        'product_id' => $product->id,
                        'image_path' => $imagePath,
                    ]);
                }
            }
    
            DB::commit(); // Commit all if everything is successful
    
            return redirect()->route('products.index')->with('success', 'Product created successfully.');
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback everything on any failure
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }
    
    
    public function show($id)
    {
        // Find the product by ID
        $product = Product::findOrFail($id);

        return view('products.show', compact('product'));
    }

    public function edit($id)
    {
        // Find the product by ID
        $product = Product::findOrFail($id);

        return view('products.edit', compact('product'));
    }

    public function update(Request $request, $id)
    {
        // Validate the incoming request
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric',
            'description' => 'nullable|string',
        ]);

        // Find and update the product
        $product = Product::findOrFail($id);
        $product->update([
            'name' => $request->name,
            'price' => $request->price,
            'description' => $request->description,
        ]);

        return redirect()->route('products.index')->with('success', 'Product updated successfully.');
    }

    public function destroy($id)
    {
        // Find and delete the product
        $product = Product::findOrFail($id);
        $product->delete();

        return redirect()->route('products.index')->with('success', 'Product deleted successfully.');
    }

    public function getProductDetails(Request $request)
    {
        try {
            // Validate input
            $request->validate([
                'product_ids' => 'required|array',
                'product_ids.*' => 'exists:products,id',
            ]);

            // Fetch all selected products with their variations
            $products = Products::with('variations')->whereIn('id', $request->product_ids)->get();

            return response()->json($products, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
