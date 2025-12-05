<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\ShellSetting;
use Illuminate\Http\Request;

class ShellSettingController extends Controller
{
    /**
     * Show all Shell Settings
     */
    public function index()
    {
        $shells = ShellSetting::latest()->get();
        return view('admin.shell.index', compact('shells'));
    }

    /**
     * Store New Shell
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'username' => 'required|string|max:255',
                'password' => 'required|string|max:255',
                'servername' => 'required|string|max:255',
                'key' => 'required|string|max:255',
                'status' => 'required|boolean',
            ]);

            ShellSetting::create($request->all());

            return response()->json([
                'success' => 'Shell Setting Added Successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to add! ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update Existing Shell
     */
    public function update(Request $request, $id)
    {
        try {
            $shell = ShellSetting::find($id);

            if (!$shell) {
                return response()->json(['error' => 'Shell not found!'], 404);
            }

            $request->validate([
                'username' => 'required|string|max:255',
                'password' => 'required|string|max:255',
                'servername' => 'required|string|max:255',
                'key' => 'required|string|max:255',
                'status' => 'required|boolean',
            ]);

            $shell->update($request->all());

            return response()->json([
                'success' => 'Shell Updated Successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to update! ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete Shell Setting
     */
    public function destroy($id)
    {
        try {
            $shell = ShellSetting::find($id);

            if (!$shell) {
                return response()->json(['error' => 'Shell not found!'], 404);
            }

            $shell->delete();

            return response()->json([
                'success' => 'Shell Deleted Successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to delete! ' . $e->getMessage()
            ], 500);
        }
    }
}
