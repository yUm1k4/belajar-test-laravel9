<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // $products = Product::all();
        // add price_eur to products
        $products = Product::paginate(10); // test factories & pagination

        return view('products.index', compact('products'));
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
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'price' => 'required',
            'photo' => 'image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $product = Product::create($request->all());

        // hande photo
        if ($request->hasFile('photo')) {
            // $photo = $request->file('photo');
            // $photo_name = time() . '_' . $photo->getClientOriginalName();
            // $photo->move(storage_path('public/products'), $photo_name);

            $filename = $request->photo->getClientOriginalName();
            $request->photo->storeAs('products', $filename);
            $product->update(['photo' => $filename]);
        } 

        return redirect()->route('products.index')->with('status', 'Product created successfully.');
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
        return view('products.edit', compact('product'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateProductRequest $request, Product $product)
    {
        $product->update($request->all());

        // update photo
        if ($request->hasFile('photo')) {
            $photo = $request->file('photo');
            $photo_name = time() . '_' . $photo->getClientOriginalName();
            $photo->move(storage_path('public/products'), $photo_name);

            $product->update(['photo' => $photo_name]);
        }

        return redirect()->route('products.index')->with('status', 'Product updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        $product->delete();

        // delete photo from storage
        if ($product->photo) {
            // Storage::delete('products/' . $product->photo);
            $photo_path = storage_path('app/products/' . $product->photo);
            if (file_exists($photo_path)) unlink($photo_path);
        }


        return redirect()->route('products.index')->with('status', 'Product deleted successfully.');
    }
}
