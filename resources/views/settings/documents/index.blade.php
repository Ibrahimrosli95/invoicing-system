@extends('layouts.app')

@section('title', __('Document Settings'))

@section('header')
<div class="bg-white border-b border-gray-200 px-6 py-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-xl font-semibold text-gray-800">{{ __('Document Settings') }}</h2>
            <p class="mt-1 text-sm text-gray-500">{{ __('Manage default terms, notes, banking details, and signatures for quotations and invoices.') }}</p>
        </div>
        <a href="{{ route('settings.documents.bank-accounts') }}"
           class="inline-flex items-center rounded-md border border-indigo-200 bg-indigo-50 px-4 py-2 text-sm font-medium text-indigo-700 shadow-sm transition hover:bg-indigo-100">
            {{ __('Manage Bank Accounts') }}
        </a>
    </div>
</div>
@endsection

@section('content')
<div class="py-12">
    <div class="mx-auto max-w-6xl space-y-6 sm:px-6 lg:px-8">
        <div class="rounded-lg bg-white shadow-sm">
            <div class="border-b border-gray-200 px-6 py-4">
                <h3 class="text-lg font-semibold text-gray-900">{{ __('Company Document Defaults') }}</h3>
                <p class="mt-1 text-sm text-gray-500">{{ __('These settings populate new quotations and invoices by default. Team members can override them per document when needed.') }}</p>
            </div>

            <div class="px-6 py-6">
                <form method="POST" action="{{ route('settings.documents.update') }}" enctype="multipart/form-data" class="space-y-10">
                    @csrf
                    @method('PATCH')

                    {{-- Terms & Conditions --}}
                    <section>
                        <div class="mb-4">
                            <h4 class="text-base font-semibold text-gray-900">{{ __('Terms & Conditions') }}</h4>
                            <p class="mt-1 text-sm text-gray-500">{{ __('Provide the standard legal terms that should appear on customer-facing documents.') }}</p>
                        </div>
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div>
                                <label for="quotation_terms" class="block text-sm font-medium text-gray-700">{{ __('Quotation Terms') }}</label>
                                <textarea id="quotation_terms" name="quotation_terms" rows="6"
                                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('quotation_terms', $documentSettings['quotation_terms'] ?? '') }}</textarea>
                                @error('quotation_terms')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="invoice_terms" class="block text-sm font-medium text-gray-700">{{ __('Invoice Terms') }}</label>
                                <textarea id="invoice_terms" name="invoice_terms" rows="6"
                                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('invoice_terms', $documentSettings['invoice_terms'] ?? '') }}</textarea>
                                @error('invoice_terms')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        <div class="mt-6">
                            <label for="payment_terms" class="block text-sm font-medium text-gray-700">{{ __('Payment Terms') }}</label>
                            <textarea id="payment_terms" name="payment_terms" rows="4"
                                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('payment_terms', $documentSettings['payment_terms'] ?? '') }}</textarea>
                            @error('payment_terms')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </section>

                    {{-- Default Notes --}}
                    <section>
                        <div class="mb-4">
                            <h4 class="text-base font-semibold text-gray-900">{{ __('Default Notes') }}</h4>
                            <p class="mt-1 text-sm text-gray-500">{{ __('Preset messages displayed in the notes sections of each document.') }}</p>
                        </div>
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div>
                                <label for="quotation_notes" class="block text-sm font-medium text-gray-700">{{ __('Quotation Notes') }}</label>
                                <textarea id="quotation_notes" name="quotation_notes" rows="4"
                                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('quotation_notes', $documentSettings['quotation_notes'] ?? '') }}</textarea>
                                @error('quotation_notes')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="invoice_notes" class="block text-sm font-medium text-gray-700">{{ __('Invoice Notes') }}</label>
                                <textarea id="invoice_notes" name="invoice_notes" rows="4"
                                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('invoice_notes', $documentSettings['invoice_notes'] ?? '') }}</textarea>
                                @error('invoice_notes')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        <div class="mt-6">
                            <label for="payment_notes" class="block text-sm font-medium text-gray-700">{{ __('Payment Notes') }}</label>
                            <textarea id="payment_notes" name="payment_notes" rows="4"
                                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('payment_notes', $documentSettings['payment_notes'] ?? '') }}</textarea>
                            @error('payment_notes')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </section>

                    {{-- Payment Instructions --}}
                    <section>
                        <div class="mb-4">
                            <h4 class="text-base font-semibold text-gray-900">{{ __('Payment Instructions') }}</h4>
                            <p class="mt-1 text-sm text-gray-500">{{ __('Define the default payment instructions and bank details shown to customers.') }}</p>
                        </div>
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div>
                                <label for="payment_instructions" class="block text-sm font-medium text-gray-700">{{ __('Payment Instructions') }}</label>
                                <textarea id="payment_instructions" name="payment_instructions" rows="6"
                                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('payment_instructions', $documentSettings['payment_instructions'] ?? '') }}</textarea>
                                @error('payment_instructions')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="bank_details" class="block text-sm font-medium text-gray-700">{{ __('Bank Details (inline)') }}</label>
                                <textarea id="bank_details" name="bank_details" rows="6"
                                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('bank_details', $documentSettings['bank_details'] ?? '') }}</textarea>
                                @error('bank_details')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-2 text-xs text-gray-500">{{ __('Use the Bank Accounts manager for structured account lists.') }}</p>
                            </div>
                        </div>
                    </section>

                    {{-- Signature Block --}}
                    <section>
                        <div class="mb-4">
                            <h4 class="text-base font-semibold text-gray-900">{{ __('Company Signature') }}</h4>
                            <p class="mt-1 text-sm text-gray-500">{{ __('Upload an optional company signature and provide the default signatory details.') }}</p>
                        </div>
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div>
                                <label for="signature_name" class="block text-sm font-medium text-gray-700">{{ __('Signature Name') }}</label>
                                <input id="signature_name" name="signature_name" type="text" value="{{ old('signature_name', $documentSettings['signature_name'] ?? '') }}"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @error('signature_name')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="signature_title" class="block text-sm font-medium text-gray-700">{{ __('Signature Title / Role') }}</label>
                                <input id="signature_title" name="signature_title" type="text" value="{{ old('signature_title', $documentSettings['signature_title'] ?? '') }}"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @error('signature_title')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div>
                                <label for="signature_image" class="block text-sm font-medium text-gray-700">{{ __('Signature Image') }}</label>
                                <input id="signature_image" name="signature_image" type="file" accept="image/*"
                                       class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:rounded-md file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-indigo-700 hover:file:bg-indigo-100">
                                @error('signature_image')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                @if(!empty($documentSettings['signature_path']))
                                    <div class="mt-4 flex items-center space-x-4">
                                        <img src="{{ Storage::url($documentSettings['signature_path']) }}" alt="{{ __('Signature') }}"
                                             class="h-20 w-auto rounded border border-gray-200 bg-white p-2">
                                        <label class="inline-flex items-center">
                                            <input type="checkbox" name="remove_signature" value="1" class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                                            <span class="ml-2 text-sm text-gray-600">{{ __('Remove signature') }}</span>
                                        </label>
                                    </div>
                                @endif
                            </div>
                            <div class="rounded border border-dashed border-gray-300 bg-gray-50 p-4">
                                <p class="text-sm font-medium text-gray-700">{{ __('Preview') }}</p>
                                <div class="mt-3 space-y-1 text-sm text-gray-600">
                                    <p>{{ old('signature_name', $documentSettings['signature_name'] ?? 'Your Name') }}</p>
                                    <p>{{ old('signature_title', $documentSettings['signature_title'] ?? 'Title / Role') }}</p>
                                    <p class="text-xs text-gray-500">{{ __('Signature image displays above when uploaded.') }}</p>
                                </div>
                            </div>
                        </div>
                    </section>

                    {{-- Document Timing & Fees --}}
                    <section>
                        <div class="mb-4">
                            <h4 class="text-base font-semibold text-gray-900">{{ __('Document Timing & Late Fees') }}</h4>
                            <p class="mt-1 text-sm text-gray-500">{{ __('Control validity periods, default due dates, and late fee behaviour.') }}</p>
                        </div>
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                            <div>
                                <label for="quotation_validity_days" class="block text-sm font-medium text-gray-700">{{ __('Quotation Validity (days)') }}</label>
                                <input id="quotation_validity_days" name="quotation_validity_days" type="number" min="1" max="365"
                                       value="{{ old('quotation_validity_days', $documentSettings['quotation_validity_days'] ?? 30) }}"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @error('quotation_validity_days')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="invoice_due_days" class="block text-sm font-medium text-gray-700">{{ __('Invoice Due (days)') }}</label>
                                <input id="invoice_due_days" name="invoice_due_days" type="number" min="1" max="365"
                                       value="{{ old('invoice_due_days', $documentSettings['invoice_due_days'] ?? 30) }}"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @error('invoice_due_days')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="late_fee_percentage" class="block text-sm font-medium text-gray-700">{{ __('Late Fee (%)') }}</label>
                                <input id="late_fee_percentage" name="late_fee_percentage" type="number" min="0" max="100" step="0.01"
                                       value="{{ old('late_fee_percentage', $documentSettings['late_fee_percentage'] ?? 0) }}"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @error('late_fee_percentage')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        <div class="mt-6 grid grid-cols-1 gap-6 md:grid-cols-3">
                            <div>
                                <label for="late_fee_grace_days" class="block text-sm font-medium text-gray-700">{{ __('Grace Period (days)') }}</label>
                                <input id="late_fee_grace_days" name="late_fee_grace_days" type="number" min="0" max="90"
                                       value="{{ old('late_fee_grace_days', $documentSettings['late_fee_grace_days'] ?? 7) }}"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @error('late_fee_grace_days')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="md:col-span-2">
                                <label for="footer_text" class="block text-sm font-medium text-gray-700">{{ __('Document Footer Text') }}</label>
                                <textarea id="footer_text" name="footer_text" rows="3"
                                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('footer_text', $documentSettings['footer_text'] ?? '') }}</textarea>
                                @error('footer_text')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        <div class="mt-6 grid grid-cols-1 gap-4 md:grid-cols-3">
                            <label class="inline-flex items-center rounded-md border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700">
                                <input type="checkbox" name="show_company_registration" value="1" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                       {{ old('show_company_registration', $documentSettings['show_company_registration'] ?? true) ? 'checked' : '' }}>
                                <span class="ml-2">{{ __('Show company registration number') }}</span>
                            </label>
                            <label class="inline-flex items-center rounded-md border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700">
                                <input type="checkbox" name="show_tax_number" value="1" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                       {{ old('show_tax_number', $documentSettings['show_tax_number'] ?? true) ? 'checked' : '' }}>
                                <span class="ml-2">{{ __('Show tax / SST number') }}</span>
                            </label>
                            <label class="inline-flex items-center rounded-md border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700">
                                <input type="checkbox" name="show_website" value="1" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                       {{ old('show_website', $documentSettings['show_website'] ?? true) ? 'checked' : '' }}>
                                <span class="ml-2">{{ __('Show website URL in footer') }}</span>
                            </label>
                        </div>
                    </section>

                    {{-- Actions --}}
                    <div class="flex items-center justify-end gap-3 border-t border-gray-200 pt-6">
                        <a href="{{ route('dashboard') }}"
                           class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">
                            {{ __('Cancel') }}
                        </a>
                        <button type="submit"
                                class="inline-flex items-center rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">
                            {{ __('Save Changes') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
