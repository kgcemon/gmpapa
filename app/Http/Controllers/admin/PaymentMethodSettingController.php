<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;

class PaymentMethodSettingController extends Controller
{
    // ✅ All Payment Methods List
    public function index()
    {
        $methods = PaymentMethod::latest()->paginate(10);
        return view('admin.payment.payment', compact('methods'));
    }

    // ✅ Store New Payment Method
    public function store(Request $request)
    {
        $request->validate([
            'icon' => 'required|url',
            'method' => 'required|string|max:100',
            'description' => 'nullable|string',
            'number' => 'required|string|max:50',
        ]);

        PaymentMethod::create([
            'icon' => $request->icon,
            'method' => $request->input('method'),
            'description' => $request->description,
            'number' => $request->number,
            'status' => 1,
        ]);

        return back()->with('success', 'Payment method added successfully!');
    }

    // ✅ Update Payment Method
    public function update(Request $request, $id)
    {
        $request->validate([
            'icon' => 'required|url',
            'method' => 'required|string|max:100',
            'description' => 'nullable|string',
            'number' => 'required|string|max:50',
        ]);

        $method = PaymentMethod::findOrFail($id);
        $method->update($request->only('icon','method','description','number','status'));

        return back()->with('success', 'Payment method updated successfully!');
    }

    // ✅ Delete Payment Method
    public function destroy($id)
    {
        $method = PaymentMethod::findOrFail($id);
        $method->delete();

        return back()->with('success', 'Payment method deleted successfully!');
    }

    // ✅ Toggle Status
    public function toggleStatus($id)
    {
        $method = PaymentMethod::findOrFail($id);
        $method->status = $method->status ? 0 : 1;
        $method->save();

        return back()->with('success', 'Status updated successfully!');
    }

    // ✅ Copy Number (Ajax based)
    public function copyNumber($id)
    {
        $method = PaymentMethod::findOrFail($id);
        return response()->json([
            'number' => $method->number
        ]);
    }
}
