<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product; // Import the Product model

class ProductController extends Controller
{
    public function index(){
        return view('products.index', ['products' => Product::all()]);
    }

    public function create()
    {
        return view('products.create');
    }

    public function store(Request $request)
    {
        // Validate the request data
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Handle the file upload
        $imageName = time() . '.' . $request->image->extension();
        $request->image->move(public_path('product'), $imageName);

        // Create and save the product
        $product = new Product;
        $product->name = $request->name;
        $product->image = $imageName;
        $product->description = $request->description;
        $product->save();

        // Redirect back with a success message
        return redirect()->route('products.index')->with('success', 'Product Created');
    }

    public function edit($id)
    {
        $product = Product::findOrFail($id);
        return view('products.edit', compact('product'));
    }

    public function update(Request $request, $id)
    {
        // Validate the request data
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Find the product
        $product = Product::findOrFail($id);

        // Update the product data
        $product->name = $request->name;
        $product->description = $request->description;

        // Handle the file upload if present
        if ($request->hasFile('image')) {
            // Delete the old image
            $oldImage = public_path('product/' . $product->image);
            if (file_exists($oldImage)) {
                unlink($oldImage);
            }

            // Upload the new image
            $imageName = time() . '.' . $request->image->extension();
            $request->image->move(public_path('product'), $imageName);
            $product->image = $imageName;
        }

        $product->save();

        // Redirect back with a success message
        return redirect()->route('products.index')->with('success', 'Product Updated');
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);

        // Delete the image file from the server
        $imagePath = public_path('product/' . $product->image);
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }

        // Delete the product record
        $product->delete();

        return redirect()->route('products.index')->with('success', 'Product Deleted');
    }
}
