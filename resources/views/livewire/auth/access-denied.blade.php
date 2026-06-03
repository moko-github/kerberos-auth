<div>
    <div class="flex flex-col gap-6">

        <div>
            <h1 class="text-xl font-semibold text-gray-900">{{ __('kerberos-auth::kerberos.access_denied.title') }}</h1>
            <p class="text-sm text-gray-500 mt-1">{{ __('kerberos-auth::kerberos.access_denied.subtitle') }}</p>
        </div>

        <div class="rounded-xl border border-red-300 bg-red-50 p-5">
            <div class="flex flex-col gap-6">

                <div class="flex items-start gap-3">
                    <svg class="w-8 h-8 text-red-500 flex-shrink-0 mt-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0-10.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.75c0 5.592 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.57-.598-3.75h-.152c-3.196 0-6.1-1.25-8.25-3.286Zm0 13.036h.008v.008H12v-.008Z" />
                    </svg>
                    <div class="flex flex-col gap-3">
                        <div>
                            <p class="font-semibold text-lg text-gray-900">{{ __('kerberos-auth::kerberos.access_denied.unknown_id') }}</p>
                            <p class="text-sm text-gray-600 mt-1">{{ __('kerberos-auth::kerberos.access_denied.not_registered') }}</p>
                        </div>
                        <div class="bg-white rounded-lg p-4 border border-gray-200">
                            <p class="font-mono text-sm text-red-600 font-medium">{{ $kerberos }}</p>
                        </div>
                        <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                            <div class="flex items-start gap-2">
                                <svg class="w-5 h-5 text-blue-500 flex-shrink-0 mt-0.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
                                </svg>
                                <p class="text-sm text-gray-600">{{ __('kerberos-auth::kerberos.access_denied.admins_notified') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <hr class="border-red-200" />

                <div class="flex flex-col gap-2">
                    <p class="text-sm font-medium text-gray-900">{{ __('kerberos-auth::kerberos.access_denied.what_to_do') }}</p>
                    <ul class="list-disc list-inside text-sm text-gray-600 space-y-1 ml-2">
                        <li>{{ __('kerberos-auth::kerberos.access_denied.tip_network') }}</li>
                        <li>{{ __('kerberos-auth::kerberos.access_denied.tip_it') }}</li>
                        <li>{{ __('kerberos-auth::kerberos.access_denied.tip_local') }}</li>
                    </ul>
                </div>

            </div>
        </div>

        <div class="flex flex-col gap-3">
            <button
                wire:click="backToLogin"
                wire:loading.attr="disabled"
                class="inline-flex items-center justify-center gap-2 w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 transition-colors font-medium"
            >
                <svg wire:loading.remove wire:target="backToLogin" class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                </svg>
                <svg wire:loading wire:target="backToLogin" class="animate-spin w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 5.373 0 0 12h4z"></path>
                </svg>
                {{ __('kerberos-auth::kerberos.access_denied.back_button') }}
            </button>

            <div class="text-center">
                <p class="text-xs text-gray-400">{{ __('kerberos-auth::kerberos.access_denied.attempt_time', ['datetime' => now()->format('d/m/Y à H:i:s')]) }}</p>
            </div>
        </div>

    </div>
</div>
