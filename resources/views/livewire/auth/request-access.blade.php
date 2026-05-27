<div>
    @if ($submitted)
        <div class="flex flex-col gap-6">

            <div>
                <h1 class="text-xl font-semibold text-gray-900">{{ __("Demande d'accès envoyée") }}</h1>
                <p class="text-sm text-gray-500 mt-1">{{ __('Votre demande a été transmise aux administrateurs') }}</p>
            </div>

            <div class="rounded-xl border border-green-300 bg-green-50 p-6">
                <div class="flex flex-col gap-4 text-center">
                    <div class="flex justify-center">
                        <svg class="w-16 h-16 text-green-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    </div>
                    <div class="flex flex-col gap-2">
                        <p class="text-gray-700">Votre demande d'accès a bien été envoyée aux administrateurs.</p>
                        <p class="text-sm text-gray-500">Vous serez notifié par email dès que votre demande aura été traitée.</p>
                    </div>
                    <div class="mt-2">
                        <a href="{{ route('login') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
                            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                            </svg>
                            Retour à la connexion
                        </a>
                    </div>
                </div>
            </div>

        </div>
    @else
        <div class="flex flex-col gap-6">

            <div>
                <h1 class="text-xl font-semibold text-gray-900">{{ __("Demande d'accès") }}</h1>
                <p class="text-sm text-gray-500 mt-1">{{ __("Votre compte n'a pas encore de rôle attribué. Veuillez remplir ce formulaire.") }}</p>
            </div>

            <div class="rounded-xl border border-yellow-300 bg-yellow-50 p-4">
                <div class="flex items-start gap-3">
                    <svg class="w-6 h-6 text-yellow-500 flex-shrink-0 mt-0.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                    </svg>
                    <div class="flex flex-col gap-1">
                        <p class="font-medium text-gray-900">Compte sans rôle</p>
                        <p class="text-sm text-gray-600">
                            Votre identifiant Kerberos <strong>{{ $kerberos }}</strong> est reconnu, mais votre compte n'a aucun rôle attribué. Veuillez justifier votre demande d'accès ci-dessous.
                        </p>
                    </div>
                </div>
            </div>

            <form wire:submit="submit" class="flex flex-col gap-5">

                <div class="flex flex-col gap-1">
                    <label class="text-sm font-medium text-gray-700">Identifiant Kerberos</label>
                    <input
                        wire:model="kerberos"
                        type="text"
                        readonly
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-100 text-gray-500 text-sm cursor-not-allowed"
                    />
                    <p class="text-xs text-gray-400">Votre identifiant Kerberos détecté automatiquement</p>
                </div>

                <div class="flex flex-col gap-1">
                    <label class="text-sm font-medium text-gray-700">
                        Justification de votre demande
                        <span class="text-red-500">*</span>
                    </label>
                    <textarea
                        wire:model="justification"
                        rows="5"
                        required
                        placeholder="Expliquez pourquoi vous avez besoin d'accéder à l'application (minimum 20 caractères)..."
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
                    ></textarea>
                    <p class="text-xs text-gray-400">Minimum 20 caractères, maximum 500 caractères</p>
                    @error('justification')
                        <p class="text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                    <div class="flex items-start gap-2">
                        <svg class="w-5 h-5 text-blue-500 flex-shrink-0 mt-0.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
                        </svg>
                        <p class="text-sm text-gray-600">
                            Les administrateurs recevront votre demande par email et vous serez notifié une fois celle-ci traitée.
                        </p>
                    </div>
                </div>

                <div class="flex flex-col gap-3">
                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center justify-center gap-2 w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 transition-colors font-medium"
                    >
                        <svg wire:loading.remove wire:target="submit" class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
                        </svg>
                        <svg wire:loading wire:target="submit" class="animate-spin w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 5.373 0 0 12h4z"></path>
                        </svg>
                        <span wire:loading.remove wire:target="submit">Envoyer la demande d'accès</span>
                        <span wire:loading wire:target="submit">Envoi en cours...</span>
                    </button>

                    <a
                        href="{{ route('login') }}"
                        class="inline-flex items-center justify-center gap-2 w-full px-4 py-2 text-gray-600 rounded-lg hover:bg-gray-100 transition-colors font-medium"
                    >
                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                        </svg>
                        Retour à la connexion
                    </a>
                </div>

            </form>
        </div>
    @endif
</div>
