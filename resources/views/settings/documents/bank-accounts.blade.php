@extends('layouts.app')

@section('title', __('Bank Accounts'))

@section('header')
<div class="bg-white border-b border-gray-200 px-6 py-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-xl font-semibold text-gray-800">{{ __('Bank Accounts') }}</h2>
            <p class="mt-1 text-sm text-gray-500">{{ __('Configure the bank accounts that appear on customer invoices and payment instructions.') }}</p>
        </div>
        <a href="{{ route('settings.documents.index') }}"
           class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">
            {{ __('Back to Document Settings') }}
        </a>
    </div>
</div>
@endsection

@section('content')
<div class="py-12" x-data="bankAccountsManager()">
    <div class="mx-auto max-w-6xl space-y-6 sm:px-6 lg:px-8">
        <div class="rounded-lg bg-white shadow-sm">
            <div class="border-b border-gray-200 px-6 py-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">{{ __('Company Bank Accounts') }}</h3>
                        <p class="mt-1 text-sm text-gray-500">{{ __('Add one or more bank accounts. Mark a single account as primary to highlight it to customers.') }}</p>
                    </div>
                    <button type="button" @click="addAccount()"
                            class="inline-flex items-center rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">
                        <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        {{ __('Add Account') }}
                    </button>
                </div>
            </div>

            <form method="POST" action="{{ route('settings.documents.update-bank-accounts') }}" class="px-6 py-6 space-y-6">
                @csrf
                @method('PATCH')

                <template x-if="!accounts.length">
                    <p class="text-sm text-gray-500">{{ __('No bank accounts added yet. Use "Add Account" to create one.') }}</p>
                </template>

                <template x-for="(account, index) in accounts" :key="index">
                    <div class="rounded-lg border border-gray-200 bg-gray-50 p-5 space-y-4">
                        <div class="flex items-start justify-between">
                            <div class="text-sm font-medium text-gray-700">
                                {{ __('Account') }} <span x-text="index + 1"></span>
                            </div>
                            <button type="button" @click="removeAccount(index)" x-show="accounts.length > 1"
                                    class="inline-flex items-center text-sm text-red-600 hover:text-red-700">
                                <svg class="mr-1 h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                {{ __('Remove') }}
                            </button>
                        </div>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div>
                                <label class="block text-sm font-medium text-gray-700" :for="`bank_accounts_${index}_bank_name`">{{ __('Bank Name *') }}</label>
                                <input class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                       type="text" :id="`bank_accounts_${index}_bank_name`" :name="`bank_accounts[${index}][bank_name]`"
                                       x-model="account.bank_name" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700" :for="`bank_accounts_${index}_account_name`">{{ __('Account Name *') }}</label>
                                <input class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                       type="text" :id="`bank_accounts_${index}_account_name`" :name="`bank_accounts[${index}][account_name]`"
                                       x-model="account.account_name" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700" :for="`bank_accounts_${index}_account_number`">{{ __('Account Number *') }}</label>
                                <input class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                       type="text" :id="`bank_accounts_${index}_account_number`" :name="`bank_accounts[${index}][account_number]`"
                                       x-model="account.account_number" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700" :for="`bank_accounts_${index}_branch`">{{ __('Branch') }}</label>
                                <input class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                       type="text" :id="`bank_accounts_${index}_branch`" :name="`bank_accounts[${index}][branch]`"
                                       x-model="account.branch">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700" :for="`bank_accounts_${index}_swift_code`">{{ __('SWIFT / BIC') }}</label>
                                <input class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                       type="text" :id="`bank_accounts_${index}_swift_code`" :name="`bank_accounts[${index}][swift_code]`"
                                       x-model="account.swift_code">
                            </div>
                            <div class="flex items-center space-x-2 pt-6">
                                <input class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" type="radio"
                                       :name="`primary_account`" :value="index" @change="setPrimary(index)" :checked="account.is_primary">
                                <span class="text-sm text-gray-700">{{ __('Mark as primary account') }}</span>
                                <input type="hidden" :name="`bank_accounts[${index}][is_primary]`" :value="account.is_primary ? 1 : 0">
                            </div>
                        </div>
                    </div>
                </template>

                @error('bank_accounts')
                    <p class="text-sm text-red-600">{{ $message }}</p>
                @enderror

                <div class="flex items-center justify-end gap-3 border-t border-gray-200 pt-6">
                    <a href="{{ route('settings.documents.index') }}"
                       class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">
                        {{ __('Cancel') }}
                    </a>
                    <button type="submit"
                            class="inline-flex items-center rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">
                        {{ __('Save Accounts') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function bankAccountsManager() {
    return {
        accounts: @json(old('bank_accounts', $bankAccounts)),
        init() {
            if (!Array.isArray(this.accounts) || !this.accounts.length) {
                this.accounts = [this.blankAccount()];
            } else {
                this.accounts = this.accounts.map(account => ({
                    bank_name: account.bank_name ?? '',
                    account_name: account.account_name ?? '',
                    account_number: account.account_number ?? '',
                    branch: account.branch ?? '',
                    swift_code: account.swift_code ?? '',
                    is_primary: Boolean(account.is_primary)
                }));
                if (!this.accounts.some(account => account.is_primary)) {
                    this.accounts[0].is_primary = true;
                }
            }
        },
        blankAccount() {
            return {
                bank_name: '',
                account_name: '',
                account_number: '',
                branch: '',
                swift_code: '',
                is_primary: this.accounts?.length ? false : true
            };
        },
        addAccount() {
            this.accounts.push(this.blankAccount());
        },
        removeAccount(index) {
            this.accounts.splice(index, 1);
            if (!this.accounts.some(account => account.is_primary) && this.accounts[0]) {
                this.accounts[0].is_primary = true;
            }
        },
        setPrimary(index) {
            this.accounts = this.accounts.map((account, i) => ({
                ...account,
                is_primary: i === index
            }));
        }
    };
}
</script>
@endsection
