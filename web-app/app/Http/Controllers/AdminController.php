<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AllowedPublicKey;

class AdminController extends Controller
{
    public function index()
    {
        $allowedKeys = AllowedPublicKey::with('users')->orderBy('created_at', 'desc')->paginate(20);
        return view('admin.keys.index', compact('allowedKeys'));
    }

    public function create()
    {
        return view('admin.keys.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'public_key' => 'required|string|size:66|unique:allowed_public_keys,public_key',
            'label' => 'nullable|string|max:255',
            'is_active' => 'boolean'
        ]);

        // Validate public key format (compressed secp256k1)
        if (!preg_match('/^(02|03)[a-f0-9]{64}$/i', $request->public_key)) {
            return back()->withErrors(['public_key' => 'Invalid secp256k1 public key format']);
        }

        AllowedPublicKey::create([
            'public_key' => strtolower($request->public_key),
            'label' => $request->label,
            'is_active' => $request->boolean('is_active', true)
        ]);

        return redirect()->route('admin.keys.index')->with('success', 'Public key added successfully');
    }

    public function edit(AllowedPublicKey $allowedKey)
    {
        return view('admin.keys.edit', compact('allowedKey'));
    }

    public function update(Request $request, AllowedPublicKey $allowedKey)
    {
        $request->validate([
            'label' => 'nullable|string|max:255',
            'is_active' => 'boolean'
        ]);

        $allowedKey->update([
            'label' => $request->label,
            'is_active' => $request->boolean('is_active')
        ]);

        return redirect()->route('admin.keys.index')->with('success', 'Public key updated successfully');
    }

    public function destroy(AllowedPublicKey $allowedKey)
    {
        $allowedKey->delete();
        return redirect()->route('admin.keys.index')->with('success', 'Public key removed successfully');
    }
}