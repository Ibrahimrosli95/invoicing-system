<?php

namespace App\Http\Controllers;

use App\Models\CompanyBrand;
use App\Http\Requests\StoreCompanyBrandRequest;
use App\Http\Requests\UpdateCompanyBrandRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CompanyBrandController extends Controller
{
    /**
     * Display a listing of company brands.
     */
    public function index()
    {
        $this->authorize('viewAny', CompanyBrand::class);

        $brands = CompanyBrand::forCompany(auth()->user()->company_id)
            ->withCount(['quotations', 'invoices'])
            ->orderBy('is_default', 'desc')
            ->orderBy('name')
            ->get();

        return view('company-brands.index', compact('brands'));
    }

    /**
     * Show the form for creating a new brand.
     */
    public function create()
    {
        $this->authorize('create', CompanyBrand::class);

        return view('company-brands.create');
    }

    /**
     * Store a newly created brand in storage.
     */
    public function store(StoreCompanyBrandRequest $request)
    {
        $validated = $request->validated();

        // Handle logo upload
        if ($request->hasFile('logo')) {
            $validated['logo_path'] = $request->file('logo')->store('brands/logos', 'public');
        }

        // Add company_id from authenticated user
        $validated['company_id'] = auth()->user()->company_id;

        $brand = CompanyBrand::create($validated);

        return redirect()->route('brands.index')
            ->with('success', 'Brand created successfully.');
    }

    /**
     * Display the specified brand.
     */
    public function show(CompanyBrand $companyBrand)
    {
        $this->authorize('view', $companyBrand);

        $companyBrand->loadCount(['quotations', 'invoices']);

        return view('company-brands.show', compact('companyBrand'));
    }

    /**
     * Show the form for editing the specified brand.
     */
    public function edit(CompanyBrand $companyBrand)
    {
        $this->authorize('update', $companyBrand);

        return view('company-brands.edit', compact('companyBrand'));
    }

    /**
     * Update the specified brand in storage.
     */
    public function update(UpdateCompanyBrandRequest $request, CompanyBrand $companyBrand)
    {
        $validated = $request->validated();

        // Handle logo upload
        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($companyBrand->logo_path) {
                Storage::disk('public')->delete($companyBrand->logo_path);
            }

            $validated['logo_path'] = $request->file('logo')->store('brands/logos', 'public');
        }

        $companyBrand->update($validated);

        return redirect()->route('brands.index')
            ->with('success', 'Brand updated successfully.');
    }

    /**
     * Remove the specified brand from storage.
     */
    public function destroy(CompanyBrand $companyBrand)
    {
        $this->authorize('delete', $companyBrand);

        // Check if brand is used in documents
        if ($companyBrand->isUsedInDocuments()) {
            return back()->withErrors([
                'brand' => 'Cannot delete brand that is used in quotations or invoices. Please archive it instead.'
            ]);
        }

        // Delete logo if exists
        if ($companyBrand->logo_path) {
            Storage::disk('public')->delete($companyBrand->logo_path);
        }

        $companyBrand->delete();

        return redirect()->route('brands.index')
            ->with('success', 'Brand deleted successfully.');
    }

    /**
     * Set the specified brand as default.
     */
    public function setDefault(CompanyBrand $companyBrand)
    {
        $this->authorize('setDefault', $companyBrand);

        $companyBrand->setAsDefault();

        return back()->with('success', 'Default brand updated successfully.');
    }

    /**
     * Toggle the active status of the specified brand.
     */
    public function toggleStatus(CompanyBrand $companyBrand)
    {
        $this->authorize('toggleStatus', $companyBrand);

        $companyBrand->update([
            'is_active' => !$companyBrand->is_active
        ]);

        $status = $companyBrand->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "Brand {$status} successfully.");
    }
}
