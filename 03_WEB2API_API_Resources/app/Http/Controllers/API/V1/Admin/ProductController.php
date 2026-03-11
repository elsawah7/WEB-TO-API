<?php

namespace App\Http\Controllers\API\V1\Admin;

use App\Http\Controllers\API\BaseApiController;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends BaseApiController
{
    public function index()
    {


        $products = Product::with(['category', 'images'])->orderBy('created_at', 'desc')->get();
        $categories = Category::all();
        return $this->sendResponse(ProductResource::collection($products), 'Products retrieved successfully.');
    }

    public function store(Request $request)
    {


        $request->validate([
            'name' => 'required|string|max:255|unique:products,name',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|gt:0',
            'stock' => 'required|numeric|gt:0',
            'active' => 'nullable|in:on,off',
            'featured' => 'nullable|in:on,off',
            'images.*' => 'required|image|mimes:jpg,png,jpeg,gif|max:2048',
        ]);

        $product = Product::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'category_id' => $request->category_id,
            'price' => $request->price,
            'stock' => $request->stock,
            'active' => $request->active ? true : false,
            'featured' => $request->featured ? true : false
        ]);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $image) {
                $imagePath = $image->store('products', 'public');
                $product->images()->create(['path' => $imagePath, 'is_primary' => $index === 0]);
            }
        }

        return $this->sendResponse($product, 'Product created successfully.', 201);
    }

    public function show(Product $product)
    {


        $product->load(['category', 'images']);
        $simillarProducts = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->inRandomOrder()->limit(6)->get();
        $categories = Category::all();

        return $this->sendResponse($product);
    }

    public function similarProducts(Product $product)
    {

        $product->load(['category', 'images']);
        $simillarProducts = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->inRandomOrder()->limit(6)->get();
        $categories = Category::all();

        return $this->sendResponse($simillarProducts);
    }

    public function update(Request $request, Product $product)
    {


        $request->validate([
            'name' => 'sometimes|string|max:255|unique:products,name,' . $product->id,
            'description' => 'nullable|string',
            'category_id' => 'sometimes|exists:categories,id',
            'price' => 'sometimes|numeric|gt:0',
            'stock' => 'sometimes|numeric|gt:0',
            'active' => 'nullable|in:on,off',
            'featured' => 'nullable|in:on,off',
        ]);


        $name = $request->name ?? $product->name;

        $product->update([
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => $request->description ?? $product->description,
            'category_id' => $request->category_id ?? $product->category_id,
            'price' => $request->price ?? $product->price,
            'stock' => $request->stock ?? $product->stock,
            'active' => $request->has('active') ? ($request->active ? true : false) : $product->active,
            'featured' => $request->has('featured') ? ($request->featured ? true : false) : $product->featured,
        ]);

        return $this->sendResponse($product, 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {

        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();
        return $this->sendResponse(null, 'Product deleted successfully.');
    }

    public function uploadImages(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'images.*' => 'required|image|mimes:jpg,png,jpeg,gif|max:2048',
        ]);

        $product = Product::findOrFail($request->product_id);



        if ($product->images->count() >= 5) {
            return back()->with('error', 'You can only upload 5 images.');
        }

        foreach ($request->file('images') as $index => $image) {
            $imagePath = $image->store('products', 'public');
            $product->images()->create(['path' => $imagePath, 'is_primary' => false]);
        }

        return back()->with('success', 'Images uploaded successfully.');
    }

    public function setPrimary(ProductImage $image)
    {
        $product = $image->product;



        $product->images()->update(['is_primary' => false]);
        $image->update(['is_primary' => true]);

        return back()->with('success', 'Primary image updated successfully.');
    }

    public function deleteImage(ProductImage $image)
    {
        $product = $image->product;


        Storage::disk('public')->delete($image->path);

        if ($image->isPrimary()) {
            $product->images->where('id', '!=', $product->id)->first()->update(['is_primary' => true]);
        }

        $image->delete();

        return redirect()->back()->with('success', 'Image deleted successfully.');
    }
}
