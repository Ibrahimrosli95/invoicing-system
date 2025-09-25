@extends('layouts.app')

@section('title', __('Custom Fields'))

@section('header')
<div class="bg-white border-b border-gray-200 px-6 py-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-xl font-semibold text-gray-800">{{ __('Custom Document Fields') }}</h2>
            <p class="mt-1 text-sm text-gray-500">{{ __('Define additional fields that appear on quotation and invoice forms.') }}</p>
        </div>
        <a href="{{ route('settings.documents.index') }}"
           class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">
            {{ __('Back to Document Settings') }}
        </a>
    </div>
</div>
@endsection

@section('content')
<div class="py-12" x-data="customFieldsManager()">
    <div class="mx-auto max-w-6xl space-y-6 sm:px-6 lg:px-8">
        <div class="rounded-lg bg-white shadow-sm">
            <div class="border-b border-gray-200 px-6 py-4">
                <h3 class="text-lg font-semibold text-gray-900">{{ __('Quotation Fields') }}</h3>
                <p class="mt-1 text-sm text-gray-500">{{ __('These fields appear when creating quotations. They can be optional or required.') }}</p>
            </div>

            <form method="POST" action="{{ route('settings.documents.update-custom-fields') }}" class="space-y-10 px-6 py-6">
                @csrf
                @method('PATCH')

                <div class="space-y-4">
                    <template x-if="!quotationFields.length">
                        <p class="text-sm text-gray-500">{{ __('No custom fields yet. Add one using the button below.') }}</p>
                    </template>

                    <template x-for="(field, index) in quotationFields" :key="`quotation_${index}`">
                        <div class="rounded-lg border border-gray-200 bg-gray-50 p-5 space-y-4">
                            <div class="flex items-start justify-between">
                                <span class="text-sm font-medium text-gray-700">{{ __('Field') }} <span x-text="index + 1"></span></span>
                                <button type="button" class="text-sm text-red-600 hover:text-red-700" @click="removeField('quotation', index)">
                                    <svg class="mr-1 inline h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                    {{ __('Remove') }}
                                </button>
                            </div>

                            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700" :for="`quotation_fields_${index}_label`">{{ __('Label *') }}</label>
                                    <input type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                           :id="`quotation_fields_${index}_label`" :name="`quotation_fields[${index}][label]`" x-model="field.label" required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700" :for="`quotation_fields_${index}_type`">{{ __('Field Type *') }}</label>
                                    <select class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            :id="`quotation_fields_${index}_type`" :name="`quotation_fields[${index}][type]`" x-model="field.type" @change="resetOptions(field)">
                                        <option value="text">{{ __('Text') }}</option>
                                        <option value="textarea">{{ __('Textarea') }}</option>
                                        <option value="number">{{ __('Number') }}</option>
                                        <option value="date">{{ __('Date') }}</option>
                                        <option value="select">{{ __('Dropdown (Select)') }}</option>
                                    </select>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                                <div class="md:col-span-2" x-show="field.type === 'select'">
                                    <label class="block text-sm font-medium text-gray-700" :for="`quotation_fields_${index}_options`">{{ __('Options (comma separated)') }}</label>
                                    <textarea rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                              :id="`quotation_fields_${index}_options`" :name="`quotation_fields[${index}][options]`" x-model="field.options"></textarea>
                                </div>
                                <div class="flex items-center pt-6">
                                    <label class="inline-flex items-center text-sm text-gray-700">
                                        <input type="checkbox" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                               :name="`quotation_fields[${index}][required]`" value="1" x-model="field.required">
                                        <span class="ml-2">{{ __('Required') }}</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </template>

                    <div>
                        <button type="button" @click="addField('quotation')"
                                class="inline-flex items-center rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">
                            <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            {{ __('Add Quotation Field') }}
                        </button>
                    </div>
                </div>

                <div class="border-t border-gray-200 pt-6">
                    <div class="border-b border-gray-200 pb-4">
                        <h3 class="text-lg font-semibold text-gray-900">{{ __('Invoice Fields') }}</h3>
                        <p class="mt-1 text-sm text-gray-500">{{ __('These fields appear on invoice forms.') }}</p>
                    </div>

                    <div class="mt-6 space-y-4">
                        <template x-if="!invoiceFields.length">
                            <p class="text-sm text-gray-500">{{ __('No invoice custom fields yet.') }}</p>
                        </template>

                        <template x-for="(field, index) in invoiceFields" :key="`invoice_${index}`">
                            <div class="rounded-lg border border-gray-200 bg-gray-50 p-5 space-y-4">
                                <div class="flex items-start justify-between">
                                    <span class="text-sm font-medium text-gray-700">{{ __('Field') }} <span x-text="index + 1"></span></span>
                                    <button type="button" class="text-sm text-red-600 hover:text-red-700" @click="removeField('invoice', index)">
                                        <svg class="mr-1 inline h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                        {{ __('Remove') }}
                                    </button>
                                </div>

                                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700" :for="`invoice_fields_${index}_label`">{{ __('Label *') }}</label>
                                        <input type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                               :id="`invoice_fields_${index}_label`" :name="`invoice_fields[${index}][label]`" x-model="field.label" required>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700" :for="`invoice_fields_${index}_type`">{{ __('Field Type *') }}</label>
                                        <select class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                :id="`invoice_fields_${index}_type`" :name="`invoice_fields[${index}][type]`" x-model="field.type" @change="resetOptions(field)">
                                            <option value="text">{{ __('Text') }}</option>
                                            <option value="textarea">{{ __('Textarea') }}</option>
                                            <option value="number">{{ __('Number') }}</option>
                                            <option value="date">{{ __('Date') }}</option>
                                            <option value="select">{{ __('Dropdown (Select)') }}</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                                    <div class="md:col-span-2" x-show="field.type === 'select'">
                                        <label class="block text-sm font-medium text-gray-700" :for="`invoice_fields_${index}_options`">{{ __('Options (comma separated)') }}</label>
                                        <textarea rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                  :id="`invoice_fields_${index}_options`" :name="`invoice_fields[${index}][options]`" x-model="field.options"></textarea>
                                    </div>
                                    <div class="flex items-center pt-6">
                                        <label class="inline-flex items-center text-sm text-gray-700">
                                            <input type="checkbox" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                                   :name="`invoice_fields[${index}][required]`" value="1" x-model="field.required">
                                            <span class="ml-2">{{ __('Required') }}</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <div>
                            <button type="button" @click="addField('invoice')"
                                    class="inline-flex items-center rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">
                                <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                </svg>
                                {{ __('Add Invoice Field') }}
                            </button>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 border-t border-gray-200 pt-6">
                    <a href="{{ route('settings.documents.index') }}"
                       class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">
                        {{ __('Cancel') }}
                    </a>
                    <button type="submit"
                            class="inline-flex items-center rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">
                        {{ __('Save Custom Fields') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function customFieldsManager() {
    return {
        quotationFields: @json(old('quotation_fields', $customFields['quotation_fields'] ?? [])),
        invoiceFields: @json(old('invoice_fields', $customFields['invoice_fields'] ?? [])),
        init() {
            if (!Array.isArray(this.quotationFields)) {
                this.quotationFields = [];
            }
            if (!Array.isArray(this.invoiceFields)) {
                this.invoiceFields = [];
            }
        },
        blankField() {
            return {
                label: '',
                type: 'text',
                required: false,
                options: ''
            };
        },
        addField(type) {
            if (type === 'quotation') {
                this.quotationFields.push(this.blankField());
            } else {
                this.invoiceFields.push(this.blankField());
            }
        },
        removeField(type, index) {
            if (type === 'quotation') {
                this.quotationFields.splice(index, 1);
            } else {
                this.invoiceFields.splice(index, 1);
            }
        },
        resetOptions(field) {
            if (field.type !== 'select') {
                field.options = '';
            }
        }
    };
}
</script>
@endsection
