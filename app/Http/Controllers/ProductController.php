<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index()
    {
        // Return a view with all products
        $products = Product::all();
        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        // Return the view to create a new product
        return view('admin.products.create');
    }

    public function store(Request $request)
    {
        // Validate form data
        $validated = $request->validate([
            'title' => 'required|max:255',
            'price' => 'required|numeric',
            'image' => 'required|image|mimes:jpg,jpeg,png|max:2048', // validate image
        ]);

        // Generate slug
        $slug = Str::slug($request->title);
        // Upload image
        if ($request->hasFile('image')) {
            $file = $request->file('image');

            $destinationPath = public_path('admin/products/images');

            // Create unique file name
            $fileNameOnly = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $file->getClientOriginalExtension();
            $imageName = $fileNameOnly . '_' . uniqid() . '.' . $extension;

            // Move file to destination folder
            $file->move($destinationPath, $imageName);

            // Store relative path for database
            $imagePath = 'admin/products/images/' . $imageName;
        } else {
            $imagePath = null; // or handle no image case
        }

        // Create product
        Product::create([
            'title' => $request->title,
            'slug' => $slug,
            'price' => $request->price,
            'image' => $imageName,
        ]);
        return redirect()->route('admin.product.create')->withInput()->with('success', 'Product created successfully.');

    }

    public function show(Product $product)
    {
        // Show the details of a single product
        return view('admin.products.show', compact('product'));
    }

    public function edit(Request $request, $id)
    {
        $product = Product::find($id);
    
        if (!$product) {
            return redirect()->route('admin.product.index')->with('error', 'Product not found');
        }

        return view('admin.products.edit', compact('product'));
    }

    public function update(Request $request, $id)
    {
        // Validate form data
        $validated = $request->validate([
            'title' => 'required|max:255',
            'price' => 'required|numeric',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // Find existing product
        $product = Product::findOrFail($id);

        // Generate new slug
        $slug = Str::slug($request->title);

        // Check if a new image is uploaded
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $destinationPath = public_path('admin/products/images');

            // Delete old image if it exists
            if ($product->image && file_exists($destinationPath . '/' . $product->image)) {
                unlink($destinationPath . '/' . $product->image);
            }

            // Create unique file name
            $fileNameOnly = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $file->getClientOriginalExtension();
            $imageName = $fileNameOnly . '_' . uniqid() . '.' . $extension;

            // Move new image to destination
            $file->move($destinationPath, $imageName);
        } else {
            // Keep the old image if no new one is uploaded
            $imageName = $product->image;
        }

        // Update product fields
        $product->update([
            'title' => $request->title,
            'slug' => $slug,
            'price' => $request->price,
            'image' => $imageName,
        ]);
        return redirect()->route('admin.product.index')->with('success', 'Product updated successfully.');
    }


    public function destroy($id)
    {
       
        $product = Product::find($id);

        if (!$product) {
            return redirect()->route('product.index')->with('error', 'Product not found.');
        }

        if (!empty($product->image)) {
            $imagePath = public_path('admin/products/images/' . $product->image);
            if (file_exists($imagePath) && is_file($imagePath)) {
                unlink($imagePath);
            }
        }
        $product->delete();
        return redirect()->route('admin.product.index')->with('success', 'Product deleted successfully.');
    }


}
