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

        $description = "Admin added a new item with {$product->quantity} qty";

        History::create([
            'name' => auth()->user()->firstname . ' ' . auth()->user()->lastname,
            'action' => "Add new item",
            'product' => $request->name,
            'image' => $image_path,
            'date' => Carbon::now(),
            'status' => $request->status,
            'description' => $description // Add the description
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
    public function update(ProductUpdateRequest $request, Product $product)
    {
        $product->name = $request->name;
        $product->description = $request->description;
        $product->barcode = $request->barcode;
        $product->price = $request->price;

        $oldQuantity = $product->quantity; // Store the old quantity for comparison
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

        $description = "Admin edited the item from {$oldQuantity} qty to {$product->quantity} qty";

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
