<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    // GET /api/categories
    public function index()
    {
        return response()->json(Category::all());
    }

    // POST /api/categories
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'icon' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = 'category_' . time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
            
            // Store the file in the public disk
            $path = $image->storeAs('categories', $filename, 'public');
            
            // Set the public URL path
            $data['image_path'] = 'storage/' . $path;
        }

        $category = Category::create($data);
        return response()->json($category, 201);
    }

    // GET /api/categories/{category}
    public function show(Category $category)
    {
        return response()->json($category);
    }

    // PUT/PATCH /api/categories/{category}
    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'name' => 'sometimes|string',
            'icon' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($category->image_path && Storage::exists(str_replace('storage/', 'public/', $category->image_path))) {
                Storage::delete(str_replace('storage/', 'public/', $category->image_path));
            }
            
            $image = $request->file('image');
            $filename = 'category_' . time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
            $path = $image->storeAs('public/categories', $filename);
            $data['image_path'] = 'storage/categories/' . $filename;
        }

        $category->update($data);
        return response()->json($category);
    }

    // DELETE /api/categories/{category}
    public function destroy(Category $category)
    {
        $category->delete();
        return response()->json(null, 204);
    }
}
