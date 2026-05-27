<div>
    @if ($this->simulationEnabled)
        <div class="mb-6">
            <div class="rounded-xl border-2 border-yellow-400 bg-yellow-50 p-4 flex flex-col gap-4">

                <div class="flex items-center gap-3">
                    <svg class="w-6 h-6 text-yellow-500 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                    </svg>
                    <div>
                        <h3 class="font-semibold text-gray-900">⚠️ Mode Développement</h3>
                        <p class="text-sm text-gray-600">Simulation Kerberos active (environnement {{ app()->environment() }})</p>
                    </div>
                </div>

                @if ($currentSimulation)
                    <div class="bg-green-50 border border-green-300 rounded-lg p-3">
                        <div class="flex items-center justify-between gap-3">
                            <div class="flex items-center gap-2 flex-1 min-w-0">
                                <svg class="w-5 h-5 text-green-500 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900">Simulation en cours</p>
                                    <p class="text-xs font-mono text-gray-600 truncate">{{ $currentSimulation }}</p>
                                </div>
                            </div>
                            <button
                                wire:click="disable"
                                wire:loading.attr="disabled"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs bg-red-500 text-white rounded-lg hover:bg-red-600 disabled:opacity-50 transition-colors font-medium flex-shrink-0"
                            >
                                <svg wire:loading.remove wire:target="disable" class="w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                </svg>
                                <svg wire:loading wire:target="disable" class="animate-spin w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 5.373 0 0 12h4z"></path>
                                </svg>
                                Désactiver
                            </button>
                        </div>
                    </div>
                @else
                    <div class="flex flex-col gap-3">

                        <p class="text-xs text-center text-gray-400 font-medium uppercase tracking-wide">Activer la simulation</p>

                        <div class="flex flex-col gap-1">
                            <label class="text-sm font-medium text-gray-700">Identifiant Kerberos personnalisé</label>
                            <input
                                wire:model="customKerberos"
                                type="text"
                                placeholder="prenom.nom@exemple.fr"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-transparent"
                            />
                            <p class="text-xs text-gray-400">Saisissez n'importe quel identifiant Kerberos</p>
                        </div>

                        <p class="text-xs text-center text-gray-400">ou</p>

                        <div class="flex flex-col gap-1">
                            <label class="text-sm font-medium text-gray-700">Sélectionner un utilisateur existant</label>
                            <select
                                wire:model="selectedKerberos"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-transparent bg-white"
                            >
                                <option value="">Choisir un identifiant existant...</option>
                                @foreach($this->availableKerberos as $user)
                                    <option value="{{ $user->kerberos }}">{{ $user->kerberos }}</option>
                                @endforeach
                            </select>
                            <p class="text-xs text-gray-400">Les 10 premiers identifiants de la base de données</p>
                        </div>

                        <button
                            wire:click="simulate"
                            wire:loading.attr="disabled"
                            class="inline-flex items-center justify-center gap-2 w-full px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 disabled:opacity-50 transition-colors font-medium"
                        >
                            <svg wire:loading.remove wire:target="simulate" class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 0 1 0 1.972l-11.54 6.347a1.125 1.125 0 0 1-1.667-.986V5.653Z" />
                            </svg>
                            <svg wire:loading wire:target="simulate" class="animate-spin w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 5.373 0 0 12h4z"></path>
                            </svg>
                            <span wire:loading.remove wire:target="simulate">Simuler la connexion</span>
                            <span wire:loading wire:target="simulate">Connexion en cours...</span>
                        </button>

                    </div>
                @endif

                <div class="bg-red-50 border border-red-200 rounded-lg p-3">
                    <div class="flex items-start gap-2">
                        <svg class="w-5 h-5 text-red-400 flex-shrink-0 mt-0.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0-10.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.75c0 5.592 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.57-.598-3.75h-.152c-3.196 0-6.1-1.25-8.25-3.286Zm0 13.036h.008v.008H12v-.008Z" />
                        </svg>
                        <p class="text-xs text-gray-600">
                            <strong>Attention :</strong> Ce mode de simulation est <strong>strictement réservé aux environnements de développement et de pré-production</strong>. Il est automatiquement désactivé en production.
                        </p>
                    </div>
                </div>

            </div>
        </div>
    @endif
</div>
