<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;

use App\Models\Categorie;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoriesController extends Controller
{
    public function index()
    {
        $categories = Categorie::orderBy('sort')->paginate(10);
        return view('admin.pages.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.pages.categories.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'thumbnail' => 'nullable|image',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sort' => 'required|integer',
        ]);

        $path = $request->file('thumbnail')?->store('categories', 'public');

        Categorie::create([
            'thumbnail' => "app/public/$path",
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'sort' => $request->sort,
        ]);

        return back()->with('success', 'Category created.');
    }

    public function edit(Categorie $category)
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function update(Request $request, Categorie $category)
    {
        $request->validate([
            'thumbnail' => 'nullable|image',
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'sort' => 'required|integer',
        ]);

        $path = $request->file('thumbnail')?->store('categories', 'public');

        $category->update([
            'thumbnail' => $path ?? $category->thumbnail,
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'sort' => $request->sort,
        ]);

        return back()->with('success', 'Category updated.');
    }

    public function destroy(Categorie $category)
    {
        $category->delete();
        return back()->with('success', 'Category deleted.');
    }
}
