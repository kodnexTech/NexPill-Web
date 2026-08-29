<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Medicine;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminMedicinesController extends Controller
{
    public function index(Request $request): View
    {
        $query = Medicine::with('user')->withTrashed()->latest();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('form')) {
            $query->where('form', $request->form);
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_paused', false)->whereNull('deleted_at');
            } elseif ($request->status === 'paused') {
                $query->where('is_paused', true);
            } elseif ($request->status === 'deleted') {
                $query->whereNotNull('deleted_at');
            }
        }

        $medicines = $query->paginate(20)->withQueryString();

        return view('admin.medicines.index', compact('medicines'));
    }

    public function show(string $id): View
    {
        $medicine = Medicine::withTrashed()->with(['user', 'schedules'])->findOrFail($id);
        $doseLogs = $medicine->doseLogs()->latest()->limit(20)->get();

        return view('admin.medicines.show', compact('medicine', 'doseLogs'));
    }
}
