<div>
    @if ($this->isActive)
        <div class="px-4 py-2.5 bg-yellow-100 border-b border-yellow-300">
            <div class="flex items-center justify-between gap-3">

                <div class="flex items-center gap-2 flex-1 min-w-0">
                    <svg class="w-4 h-4 text-yellow-600 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                    </svg>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-semibold text-yellow-900">{{ __('kerberos-auth::kerberos.simulation_banner.mode') }}</p>
                        <p class="text-xs font-mono text-yellow-700 truncate" title="{{ $currentSimulation }}">{{ $currentSimulation }}</p>
                    </div>
                </div>

                <button
                    wire:click="disable"
                    wire:loading.attr="disabled"
                    title="{{ __('kerberos-auth::kerberos.simulation_banner.disable_title') }}"
                    class="inline-flex items-center gap-1 px-2.5 py-1 text-xs bg-red-500 text-white rounded hover:bg-red-600 disabled:opacity-50 transition-colors flex-shrink-0 font-medium"
                >
                    <svg wire:loading.remove wire:target="disable" class="w-3 h-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                    <svg wire:loading wire:target="disable" class="animate-spin w-3 h-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 5.373 0 0 12h4z"></path>
                    </svg>
                    {{ __('kerberos-auth::kerberos.simulation_banner.quit') }}
                </button>

            </div>

            <div class="mt-1.5 flex items-start gap-1.5">
                <svg class="w-3 h-3 text-yellow-500 flex-shrink-0 mt-0.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
                </svg>
                <p class="text-[10px] text-yellow-700 leading-tight">
                    {{ __('kerberos-auth::kerberos.simulation_banner.description', ['env' => app()->environment()]) }}
                </p>
            </div>
        </div>
    @endif
</div>
