@php
    $statePath = $getStatePath();
    $id = 'addr_' . str_replace('.', '_', $statePath);
@endphp

<div x-data="{ 
    query: @entangle($statePath),
    raw: null,
    isAutoDetected: false,
    suggestions: [],
    loading: false,
    isOpen: false,
    async fetchSuggestions() {
        if (!this.query || this.query.length < 3) {
            this.suggestions = [];
            return;
        }
        this.loading = true;
        try {
            const response = await fetch(`https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(this.query)}&format=json&limit=8&addressdetails=1`);
            const rawData = await response.json();
            
            this.suggestions = rawData.map(s => {
                const addr = s.address || {};
                const city = addr.city || addr.town || addr.village || addr.municipality;
                const region = addr.state || addr.region || addr.province;
                const country = addr.country;
                
                if (city && region && country) {
                    return { ...s, parsed: { city, region, country } };
                }
                return null;
            }).filter(s => s !== null);
            
            this.isOpen = this.suggestions.length > 0;
        } catch (e) {
            console.error(e);
        } finally {
            this.loading = false;
        }
    },
    select(suggestion, isAuto = false) {
        this.query = suggestion.display_name;
        this.raw = suggestion;
        this.isAutoDetected = isAuto;
        this.isOpen = false;
        $wire.handleAddressSelected({ query: this.query, raw: this.raw });
    },
    async detectLocation() {
        if (!navigator.geolocation) return;
        this.loading = true;
        navigator.geolocation.getCurrentPosition(async (position) => {
            try {
                const response = await fetch(`https://nominatim.openstreetmap.org/reverse?lat=${position.coords.latitude}&lon=${position.coords.longitude}&format=json&addressdetails=1`);
                const data = await response.json();
                const addr = data.address || {};
                const city = addr.city || addr.town || addr.village || addr.municipality;
                const region = addr.state || addr.region || addr.province;
                const country = addr.country;
                
                if (city && region && country) {
                    this.suggestions = [{ ...data, parsed: { city, region, country } }];
                    this.isOpen = true;
                    if (!this.query) this.query = city;
                } else {
                    this.select(data, true);
                }
            } catch (e) {
                console.error(e);
            } finally {
                this.loading = false;
            }
        }, (error) => {
            console.error(error);
            this.loading = false;
        });
    }
}" 
x-init="setTimeout(() => { if (!query) detectLocation() }, 1500)"
x-on:clear-address.window="query = ''; raw = null; isAutoDetected = false;"
class="w-full space-y-2"
id="{{ $id }}">
    
    <style>
        #{{ $id }} .addr-input-wrp {
            background-color: white !important;
            color: #0f172a !important;
        }
        .dark #{{ $id }} .addr-input-wrp {
            background-color: #0f172a !important;
            color: white !important;
        }
        #{{ $id }} input.addr-input-field {
            background-color: transparent !important;
            color: inherit !important;
            border: none !important;
            box-shadow: none !important;
        }
        #{{ $id }} input.addr-input-field::placeholder {
            color: #94a3b8 !important;
        }
        .dark #{{ $id }} input.addr-input-field::placeholder {
            color: #475569 !important;
        }
        #{{ $id }} .addr-dropdown {
            background-color: white !important;
            color: #0f172a !important;
        }
        .dark #{{ $id }} .addr-dropdown {
            background-color: #0f172a !important;
            color: white !important;
        }
    </style>

    <label class="block text-xs font-bold uppercase tracking-widest text-slate-700 dark:text-slate-300 px-1">
        Localisation du Cercle (Ville/Région/Pays) <span class="text-red-500">*</span>
    </label>

    <div class="relative" :class="isOpen ? 'z-[100]' : 'z-10'">
        <div class="addr-input-wrp flex rounded-xl shadow-sm ring-1 transition duration-75 focus-within:ring-2 ring-gray-950/20 dark:ring-white/20 focus-within:ring-blue-600 dark:focus-within:ring-blue-500 overflow-hidden border border-gray-200 dark:border-white/10">
            <div class="min-w-0 flex-1">
                <input 
                    x-model="query" 
                    @input.debounce.400ms="fetchSuggestions(); isAutoDetected = false;"
                    @click.away="isOpen = false"
                    @focus="if(query.length >= 3) fetchSuggestions()"
                    type="text" 
                    autocomplete="off"
                    class="addr-input-field block w-full py-3.5 px-4 text-base focus:outline-none focus:ring-0 sm:text-sm sm:leading-6" 
                    placeholder="Tapez pour rechercher (ex: Paris)...">
            </div>
            
            <div class="flex items-center gap-x-1 pe-3">
                <button 
                    @click="detectLocation()" 
                    type="button"
                    class="p-2 text-blue-500 dark:text-blue-400 hover:text-blue-600 transition-colors"
                    title="Détecter ma position">
                    <svg x-show="!loading" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                    </svg>
                    <div x-show="loading" style="display: none;" class="w-4 h-4 border-2 border-blue-600/30 border-t-blue-600 rounded-full animate-spin"></div>
                </button>
            </div>
        </div>

        <!-- Suggestions Dropdown -->
        <div x-show="isOpen" x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            style="z-index: 100;"
            class="addr-dropdown absolute z-[100] w-full mt-2 border border-slate-200 dark:border-white/10 rounded-xl shadow-2xl overflow-hidden divide-y divide-gray-100 dark:divide-white/5">
            <template x-for="suggestion in suggestions" :key="suggestion.place_id">
                <button @click="select(suggestion)" type="button" class="w-full text-left px-3 sm:px-4 py-3 hover:bg-gray-50 dark:hover:bg-white/5 transition-all group focus:bg-blue-50 outline-none">
                    <div class="flex flex-wrap items-center gap-1 sm:gap-2 py-1">
                        <!-- Ville -->
                        <div class="flex flex-col items-center shrink-0 w-auto bg-blue-600/10 border border-blue-200/50 px-2 sm:px-3 py-1 rounded-xl">
                            <span class="text-[7px] font-black uppercase tracking-widest text-blue-600 leading-none mb-0.5">Ville</span>
                            <span class="text-[8px] sm:text-[9px] font-bold text-blue-900 group-hover:text-blue-600 transition-colors" x-text="suggestion.parsed.city"></span>
                        </div>
                        <div class="text-slate-300 shrink-0">
                            <svg class="w-2 h-2 sm:w-3 sm:h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"/></svg>
                        </div>
                        <!-- Région -->
                        <div class="flex flex-col items-center shrink-0 bg-orange-600/10 border border-orange-200/50 px-2 sm:px-3 py-1 rounded-xl">
                            <span class="text-[7px] font-black uppercase tracking-widest text-orange-500 leading-none mb-0.5">Région</span>
                            <span class="text-[8px] sm:text-[9px] font-bold text-orange-900" x-text="suggestion.parsed.region"></span>
                        </div>
                        <div class="text-slate-300 shrink-0">
                            <svg class="w-2 h-2 sm:w-3 sm:h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"/></svg>
                        </div>
                        <!-- Pays -->
                        <div class="flex flex-col items-center shrink-0 bg-slate-900/10 border border-slate-900/20 px-2 sm:px-3 py-1 rounded-xl">
                            <span class="text-[7px] font-black uppercase tracking-widest text-slate-500 leading-none mb-0.5">Pays</span>
                            <span class="text-[8px] sm:text-[9px] font-bold text-slate-900 uppercase" x-text="suggestion.parsed.country"></span>
                        </div>
                    </div>
                </button>
            </template>
        </div>
    </div>
    
    <div class="flex items-center justify-between px-1">
        <div>
            @error($statePath)
                <p class="text-[10px] text-red-600 dark:text-red-400 font-extrabold uppercase tracking-widest">{{ $message }}</p>
            @else
                <p class="text-[10px] text-gray-500 dark:text-gray-400 italic flex items-center gap-1.5 font-bold uppercase tracking-tight">
                    <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Une adresse précise est requise.
                </p>
            @enderror
        </div>
        
        <template x-if="isAutoDetected">
            <div class="flex items-center gap-1.5 bg-blue-50 dark:bg-blue-950/30 px-3 py-1.5 rounded-full border border-blue-100 dark:border-blue-900/50 shadow-sm animate-pulse">
                <span class="w-2 h-2 bg-blue-500 rounded-full"></span>
                <span class="text-[10px] font-black uppercase tracking-widest text-blue-700 dark:text-blue-400">Position OK</span>
            </div>
        </template>
    </div>
</div>
