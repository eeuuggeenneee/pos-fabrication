<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductStoreRequest;
use App\Http\Requests\ProductUpdateRequest;
use App\Http\Resources\ProductResource;
use App\Models\History;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $products = new Product();
        $sortDirection = $request->dir;

        // dd($sortDirection);
        if ($request->search) {
            $products = $products->where('name', 'LIKE', "%{$request->search}%");
        }


        $products = $products->latest()->paginate(10);


        if (request()->wantsJson()) {
            return ProductResource::collection($products);
        }
        if ($request->dir != null) {
            $products = Product::orderBy('quantity',  $sortDirection)->paginate(10);
        }

        return view('products.index')->with('products', $products)->with('sortDirection', $sortDirection);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('products.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ProductStoreRequest $request)
    {
        $image_path = '';

        if ($request->hasFile('image')) {
            $image_path = $request->file('image')->store('products', 'public');
        }

        $product = Product::create([
            'name' => $request->name,
            'description' => $request->description,
            'image' => $image_path,
            'barcode' => $request->barcode,
            'price' => $request->price,
            'quantity' => $request->quantity,
            'status' => $request->status
        ]);

        $description = "Added {$product->name} with {$product->quantity} quantity";

        History::create([
            'name' => auth()->user()->firstname . ' ' . auth()->user()->lastname,
            'action' => "Add new item",
            'product' => $request->name,
            'image' => $image_path,
            'date' => Carbon::now(),
            'status' => $request->status,
            'description' => $description 
        ]);

        if (!$product) {
            return redirect()->back()->with('error', 'Sorry, there was a problem while creating the product.');
        }

        return redirect()->route('products.index')->with('success', 'Success, your product has been created.');
    }


    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function show(Product $product)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function edit(Product $product)
    {
        return view('products.edit')->with('product', $product);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */

    public function printInventoryHistory(Request $request)
    {
        $fromDate = $request->fromDate;
        $toDate = $request->toDate;

        $history = History::whereBetween('updated_at', [$fromDate, $toDate])->get();

        $pdf = new \FPDF();
        $pdf->AddPage(); 
        $pdf->SetFont('Arial', 'B', 30);

        $pdf->Cell(0, 10, 'Inventory History Report', 0, 1, 'C');
        $pdf->Ln();
        $pdf->SetFont('Arial', 'B', 15);
        $pdf->Cell(190, 5, 'Date Range: ' . $fromDate . ' to ' . $toDate, 0, 1, 'C');
        $pdf->Ln();
        
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(35, 10, 'Action', 1, 0, 'C');
        $pdf->Cell(35, 10, 'Product', 1, 0, 'C');
        $pdf->Cell(120, 10, 'Description', 1, 1, 'C'); 


        $pdf->SetFont('Arial', '', 12);
        foreach ($history as $record) {
            $pdf->Cell(35, 10, $record->action, 1, 0, 'C');
            $pdf->Cell(35, 10, $record->product, 1, 0, 'C');
            $pdf->Cell(120, 10, $record->description, 1, 1, 'C');
        }

        // Output the PDF
        $content = $pdf->Output('inventory_history.pdf', 'I');

        return response($content)->header('Content-Type', 'application/pdf');
    }




    public function update(ProductUpdateRequest $request, Product $product)
    {
        $oldName = $product->name; 
        $oldPrice = $product->price; 
        $oldQuantity = $product->quantity;
        $oldStatus = $product->status;
        $oldDesc = $product->description;
        $oldImage = $product->image; 
    
        $product->name = $request->name;
        $product->description = $request->description;
        $product->barcode = $request->barcode;
        $product->price = $request->price;

        $product->quantity = $request->quantity;
        $product->status = $request->status;

        if ($request->hasFile('image')) {
            // Delete old image
            if ($product->image) {
                Storage::delete($product->image);
            }
            // Store image
            $image_path = $request->file('image')->store('products', 'public');
            // Save to Database
            $product->image = $image_path;
        }

        if (!$product->save()) {
            return redirect()->back()->with('error', 'Sorry, there was a problem while updating the product.');
        }


        $changes = [];

        if ($oldName != $product->name) {
            $changes[] = "Name from {$oldName} to {$product->name}";
        }
    
        if ($oldPrice != $product->price) {
            $changes[] = "Price from {$oldPrice} to {$product->price}";
        }
    
        if ($oldQuantity != $product->quantity) {
            $changes[] = "Quantity from {$oldQuantity} to {$product->quantity}";
        }
    
        if ($product->barcode != $request->barcode) {
            $changes[] = "Barcode to {$request->barcode}";
        }
    
        if ($oldImage != $product->image) {
            $changes[] = "Image Change";
        }
    
        if ($oldDesc != $product->description) {
            $changes[] = "Description Change";
        }
    
        if ($oldStatus != $request->status) {
            $statusLabel = $request->status ? "active" : "inactive";
            $changes[] = "Status to {$statusLabel}";
        }
    
        $description = implode(", ", $changes);


        History::create([
            'name' => auth()->user()->firstname . ' ' . auth()->user()->lastname,
            'action' => "Edit Item",
            'product' => $request->name,
            'image' => $product->image,
            'date' => Carbon::now(),
            'status' => $request->status,
            'description' => $description // Add the description
        ]);

        return redirect()->route('products.index')->with('success', 'Success, your product has been updated.');
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        $productName = $product->name; // Store the product name for the history record
        $description = "Admin deleted the item {$product->name}";

        History::create([
            'name' => auth()->user()->firstname . ' ' . auth()->user()->lastname,
            'action' => "Delete Item",
            'product' => $productName, // Use the stored product name
            'image' => $product->image,
            'date' => Carbon::now(),
            'description' => $description, // Add the description
            'status' => false // Set the status to false or any relevant value
        ]);

        if ($product->image) {
            Storage::delete($product->image);
        }

        $product->delete();

        return redirect()->route('products.index')->with('success', 'You have deleted the product.');
    }
}
