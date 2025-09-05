<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\HelpLine;
use Illuminate\Http\Request;

class SocialLinkController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $links = HelpLine::latest()->get();
        return view('admin.social.index', compact('links'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'  => 'required|string|max:255',
            'url'   => 'required|url',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        // upload image
        $path = $request->file('image')->store('uploads/helplines', 'public');

        HelpLine::create([
            'name'  => $request->name,
            'url'   => $request->url,
            'image' => 'storage/' . $path,
        ]);

        return redirect()->back()->with('success', 'HelpLine added successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, HelpLine $helpline)
    {
        $request->validate([
            'name'  => 'required|string|max:255',
            'url'   => 'required|url',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($request->hasFile('image')) {
            if ($helpline->image && file_exists(public_path($helpline->image))) {
                unlink(public_path($helpline->image));
            }
            $path = $request->file('image')->store('uploads/helplines', 'public');
            $helpline->image = 'storage/' . $path;
        }

        $helpline->name = $request->name;
        $helpline->url  = $request->url;
        $helpline->save();

        return redirect()->back()->with('success', 'HelpLine updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(HelpLine $helpline)
    {
        if ($helpline->image && file_exists(public_path($helpline->image))) {
            unlink(public_path($helpline->image));
        }
        $helpline->delete();

        return redirect()->back()->with('success', 'HelpLine deleted successfully.');
    }
}
