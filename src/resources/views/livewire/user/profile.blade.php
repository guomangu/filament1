<div class="min-h-screen pb-12 pt-6">
    <!-- User Header Portfolio -->
    <div class="max-w-7xl mx-auto px-6 mb-12">
        <div class="bg-white/40 backdrop-blur-3xl border border-white/60 rounded-[4rem] p-8 md:p-12 shadow-2xl shadow-blue-500/5 relative overflow-hidden group">
            <div class="relative z-10">
                <div class="flex flex-col md:flex-row items-center gap-8 md:gap-12">
                    <div class="relative flex-shrink-0">
                        <div class="absolute inset-0 bg-blue-600/20 rounded-[3rem] md:rounded-[3.5rem] blur-2xl group-hover:blur-3xl transition-all duration-700"></div>
                        <img src="{{ $user->avatar }}" class="relative w-40 h-40 md:w-64 md:h-64 rounded-[3rem] md:rounded-[3.5rem] object-cover border-4 border-white shadow-2xl transition-transform duration-700 group-hover:scale-[1.02]">
                        <div class="absolute -bottom-2 -right-2 md:-bottom-4 md:-right-4 w-16 h-16 md:w-20 md:h-20 bg-slate-900 rounded-2xl md:rounded-3xl flex flex-col items-center justify-center shadow-2xl border-4 border-white">
                            <span class="text-[8px] md:text-xs font-black text-blue-400 uppercase tracking-widest leading-none mb-1">Score</span>
                            <span class="text-2xl md:text-3xl font-black text-white leading-none">{{ $user->trust_score }}</span>
                        </div>
                    </div>

                    <div class="flex-grow text-center md:text-left">
                        <div class="inline-flex items-center gap-2 px-4 py-1.5 bg-blue-50 text-blue-600 rounded-full text-[10px] font-black uppercase tracking-[0.2em] mb-4 border border-blue-100">
                            Profil Vérifié
                        </div>
                        <div class="flex flex-col md:flex-row items-center justify-center md:justify-start gap-4 mb-4">
                            <h1 class="text-3xl md:text-7xl font-black text-slate-900 tracking-tighter leading-none">{{ $user->name }}</h1>
                            <x-project-transporter :project="$activeProject" />
                            <livewire:information.manager :model="$user" :key="'user-info-'.$user->id" />
                        </div>
                        <p class="text-slate-500 font-medium text-base md:text-xl max-w-2xl leading-relaxed italic px-4 md:px-0 mb-6">
                            "{{ $user->bio ?? 'Ce bâtisseur de confiance n\'a pas encore rédigé sa bio.' }}"
                        </p>
                        @auth
                            @php $trustPath = auth()->user()->getTrustPathTo($user); @endphp
                            @if(count($trustPath) > 0)
                                <div class="mt-4 mb-2">
                                    <x-user-trust-chain :path="$trustPath" />
                                </div>
                            @endif
                        @endauth
                        
                        <div class="mt-8">
                            @auth
                                @if(auth()->id() !== $user->id)
                                    <div class="flex gap-4">
                                        <button wire:click="startConversation" class="flex-grow px-6 py-4 bg-slate-900 text-white rounded-[2rem] font-black text-sm tracking-widest uppercase hover:bg-blue-600 transition-all shadow-xl">
                                            Message
                                        </button>
                                    </div>
                                @else
                                    <div class="space-y-4">
                                        <!-- Primary Platform Actions -->
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                            <button wire:click="openProcheModal" class="px-6 py-4 bg-slate-900 text-white rounded-[2rem] font-black text-xs tracking-widest uppercase hover:bg-blue-600 transition-all shadow-xl flex items-center justify-center gap-3">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                                                Nouveau Proche
                                            </button>
                                            
                                            <a href="{{ route('profile.edit') }}" class="px-6 py-4 bg-white border-2 border-slate-100 text-slate-400 rounded-[2rem] font-black text-[10px] tracking-widest uppercase hover:border-slate-300 hover:text-slate-900 transition-all flex items-center justify-center gap-3">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                                Paramètres
                                            </a>
                                        </div>
                                    </div>
                                @endif
                            @endauth
                        </div>

                    </div>
                </div>
            </div>
            <div class="absolute top-0 right-0 w-1/3 h-full bg-gradient-to-l from-blue-400/10 to-transparent"></div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-12 gap-8 md:gap-12">
        

        <!-- Portfolio: Skill-Centric View -->
        <div class="lg:col-span-6 space-y-12">
            
            {{-- ===== MES PROJETS / OFFRES ===== --}}
            @if($userProjects->count() > 0 || ($userOffers->count() > 0 && auth()->id() !== $user->id))
                <div class="bg-white/60 backdrop-blur-3xl border border-white/60 rounded-[3.5rem] p-8 shadow-2xl shadow-blue-500/5 mb-12">
                    <div class="flex items-center justify-between mb-8">
                        <div>
                            <h3 class="text-xl font-black text-slate-900 uppercase tracking-tight flex items-center gap-3">
                                <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                @if(auth()->id() === $user->id)
                                    Réalisations
                                    <span class="text-[10px] font-black text-slate-400 border border-slate-200 px-2 py-0.5 rounded-full">{{ $userProjects->count() }}</span>
                                @else
                                    Missions & Réalisations
                                    <span class="text-[10px] font-black text-slate-400 border border-slate-200 px-2 py-0.5 rounded-full">{{ $userProjects->count() }}</span>
                                @endif
                            </h3>
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] mt-1">
                                @if(auth()->id() === $user->id)
                                    Mes participations et contrats
                                @else
                                    Ses collaborations et projets actifs
                                @endif
                            </p>
                        </div>
                    </div>

                    @if($isCreatingProject)
                        {{-- Project Creation Form --}}
                        <div class="mb-8 bg-white/80 backdrop-blur-xl border-2 border-blue-500 p-6 rounded-[3.5rem] shadow-2xl animate-in fade-in slide-in-from-top-4 duration-300">
                            <div class="flex items-center justify-between mb-4 px-2">
                                <span class="text-[12px] font-black text-blue-600 uppercase tracking-widest">
                                    Proposer une Offre
                                </span>
                                <button wire:click="cancelProjectCreation" class="text-slate-400 hover:text-red-500 transition-colors">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                            <div class="relative">
                                <input wire:model="projectTitle" 
                                       wire:keydown.enter="confirmProjectCreation"
                                       type="text" 
                                       placeholder="Titre de votre offre..." 
                                       autofocus
                                       class="w-full bg-white border-2 border-slate-100 focus:border-blue-500 focus:ring-0 rounded-3xl p-6 text-xl font-black tracking-tight placeholder:text-slate-300 shadow-inner">
                                <button wire:click="confirmProjectCreation" 
                                        wire:loading.attr="disabled"
                                        class="absolute right-3 top-3 bottom-3 px-2 bg-blue-600 text-white rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-blue-700 transition-all shadow-lg shadow-blue-500/20 disabled:opacity-50">
                                    <span wire:loading.remove wire:target="confirmProjectCreation">Lancer le Projet</span>
                                    <span wire:loading wire:target="confirmProjectCreation">Lancement...</span>
                                </button>
                            </div>
                        </div>
                        @error('projectTitle') <span class="text-red-500 text-[10px] font-black uppercase mt-3 block pl-6">{{ $message }}</span> @enderror

                        @if($draftProject)
                            <div class="mt-6 border border-slate-100 rounded-[2.5rem] p-8 bg-slate-50/50">
                                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-4">Fichiers & Liens de preuve (Optionnel)</span>
                                <livewire:information.manager :model="$draftProject" :key="'info-manager-draft-profile-'.$draftProject->id" />
                            </div>
                        @endif
                    @endif

                    {{-- Projects Grid --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($userProjects as $project)
                            <div class="relative group h-full" wire:key="user-project-{{ $project->id }}">
                                <div class="flex flex-col relative h-full bg-gradient-to-br from-white/80 to-white/40 backdrop-blur-xl border border-white/60 p-5 rounded-3xl hover:shadow-2xl hover:shadow-blue-500/10 hover:scale-[1.02] transition-all duration-300">
                                    <a href="{{ route('projects.show', $project) }}" class="absolute inset-0 z-10 rounded-3xl" title="Voir la réalisation"></a>
                                    
                                    <div class="flex flex-col relative h-full z-20 pointer-events-none">
                                        <div class="flex items-start justify-between mb-4">
                                            <div class="flex-1 min-w-0">
                                                <h4 class="text-base font-black text-slate-900 uppercase tracking-tight truncate group-hover:text-blue-600 transition-colors">
                                                    {{ $project->title }}
                                                </h4>
                                                @if($project->skill)
                                                    <a href="{{ route('mission.show', $project->skill) }}" class="inline-block px-2 py-0.5 bg-slate-900 text-white text-[7px] font-black uppercase rounded-md mb-1 hover:bg-blue-600 transition-colors pointer-events-auto relative z-30">{{ $project->skill->name }}</a>
                                                @endif
                                            </div>
                                            <div class="ml-3 flex-shrink-0">
                                                @php
                                                    $statusColors = [
                                                        'actuelle' => 'bg-blue-50 text-blue-700 border-blue-200',
                                                        'verrouillée' => 'bg-amber-50 text-amber-700 border-amber-200',
                                                        'terminée' => 'bg-green-50 text-green-700 border-green-200',
                                                        'annulée' => 'bg-red-50 text-red-700 border-red-200',
                                                    ];
                                                @endphp
                                                <span class="inline-flex items-center gap-1 px-2 py-1 {{ $statusColors[$project->status] ?? 'bg-slate-50 text-slate-700' }} border rounded-lg">
                                                    @if($project->status === 'actuelle')
                                                        <span class="w-1.5 h-1.5 bg-blue-500 rounded-full animate-pulse"></span>
                                                    @endif
                                                    <span class="text-[8px] font-black uppercase">{{ $project->status }}</span>
                                                </span>
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-2 gap-2 mb-4">
                                            <div class="bg-white/60 rounded-xl p-2 text-center border border-slate-100">
                                                <div class="text-xs font-black text-slate-900">{{ $project->activeMembers->count() + 1 }}</div>
                                                <div class="text-[7px] font-black text-slate-400 uppercase">Participants</div>
                                            </div>
                                            <div class="bg-blue-50/60 rounded-xl p-2 text-center border border-blue-100">
                                                <div class="text-xs font-black text-blue-600">{{ $project->messages->count() }}</div>
                                                <div class="text-[7px] font-black text-blue-400 uppercase">Messages</div>
                                            </div>
                                        </div>

                                        {{-- Join Button for Visitors --}}
                                        @auth
                                            @if(auth()->id() !== $user->id && $project->status === 'actuelle' && !$project->isMember(auth()->user()) && !$project->isOwner(auth()->user()))
                                                <div class="mb-4 pointer-events-auto relative z-30">
                                                    <button wire:click="joinProject({{ $project->id }})" class="w-full py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-[9px] font-black uppercase tracking-widest transition-all shadow-lg shadow-blue-500/20">
                                                        Rejoindre cette mission
                                                    </button>
                                                </div>
                                            @elseif(auth()->id() !== $user->id && ($project->isMember(auth()->user()) || $project->isOwner(auth()->user())))
                                                <div class="mb-4 pointer-events-auto relative z-30">
                                                    <div class="w-full py-2 bg-green-50 text-green-600 border border-green-100 rounded-xl text-[9px] font-black uppercase tracking-widest text-center">
                                                        Vous participez
                                                    </div>
                                                </div>
                                            @endif
                                        @endauth

                                        <div class="flex items-center gap-2 pt-3 border-t border-slate-100 mt-auto">
                                            <a href="{{ route('users.show', $project->owner) }}" class="flex items-center gap-2 group/owner pointer-events-auto relative z-30">
                                                <img src="{{ $project->owner->avatar_url }}" class="w-6 h-6 rounded-lg object-cover border border-slate-200 group-hover/owner:border-blue-500 transition-all">
                                                <div class="flex-1 min-w-0">
                                                    <div class="text-[9px] font-black text-slate-900 uppercase truncate group-hover/owner:text-blue-600 transition-colors">{{ $project->owner->name }}</div>
                                                    <div class="text-[7px] font-black text-slate-400 uppercase">Responsable</div>
                                                </div>
                                            </a>
                                            <svg class="w-4 h-4 text-slate-300 ml-auto group-hover:text-blue-500 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                        </div>
                                    </div>
                                </div>

                                {{-- Dropdown Menu for owner/admin --}}
                                @auth
                                    @if($project->canManage(auth()->user()))
                                        <div x-data="{ open: false }" class="absolute top-4 right-4 z-40">
                                            <button @click="open = !open" 
                                                    class="w-8 h-8 rounded-xl bg-white shadow-xl flex items-center justify-center text-slate-400 hover:text-blue-600 transition-all opacity-0 group-hover:opacity-100 pointer-events-auto">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/></svg>
                                            </button>
                                            <div x-show="open" @click.away="open = false" 
                                                x-transition:enter="transition ease-out duration-100"
                                                x-transition:enter-start="transform opacity-0 scale-95"
                                                x-transition:enter-end="transform opacity-100 scale-100"
                                                class="absolute right-0 mt-2 w-48 bg-white rounded-2xl shadow-2xl border border-slate-100 py-2 z-50 pointer-events-auto">
                                                <button wire:click="editItem('project', {{ $project->id }})" @click="open = false" class="w-full text-left px-4 py-2 text-[10px] font-black uppercase tracking-widest text-slate-600 hover:bg-slate-50 hover:text-blue-600 transition-colors">
                                                    Modifier
                                                </button>
                                                <button wire:click="toggleProjectStatus({{ $project->id }})" @click="open = false" class="w-full text-left px-4 py-2 text-[10px] font-black uppercase tracking-widest text-slate-600 hover:bg-slate-50 hover:text-blue-600 transition-colors">
                                                    Changer le statut
                                                </button>
                                                <div class="border-t border-slate-50 my-1"></div>
                                                <button wire:click="deleteProject({{ $project->id }})" 
                                                        wire:confirm="Êtes-vous sûr de vouloir supprimer cette réalisation ?"
                                                        @click="open = false" class="w-full text-left px-4 py-2 text-[10px] font-black uppercase tracking-widest text-red-600 hover:bg-red-50 transition-colors">
                                                    Supprimer
                                                </button>
                                            </div>
                                        </div>
                                    @endif
                                @endauth
                            </div>
                        @endforeach
                    </div>
                </div>
            @elseif(auth()->check() && auth()->id() === $user->id)
                <div class="bg-white/40 backdrop-blur-3xl border-2 border-dashed border-slate-200 rounded-[3.5rem] p-10 text-center mb-12">
                    <div class="w-16 h-16 bg-blue-50 rounded-3xl flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    </div>
                    <p class="text-slate-400 font-black uppercase tracking-[0.3em] text-xs mb-4">Aucune réalisation en cours</p>
                    <a href="{{ route('search') }}" class="inline-flex items-center gap-2 px-8 py-4 bg-blue-600 text-white rounded-[2rem] font-black text-xs uppercase tracking-widest hover:bg-blue-700 transition-all shadow-lg shadow-blue-500/20">
                        Explorer les Missions
                    </a>
                </div>
            @endif
            <div class="flex items-center justify-between mb-10">
                <h2 class="text-4xl font-black text-slate-900 tracking-tighter">Expertises & Réalisations</h2>
                @if($user->id === auth()->id())
                    <button type="button" wire:click="openCreateModal" class="px-6 py-3 bg-blue-600 text-white rounded-2xl font-black text-xs tracking-widest uppercase hover:bg-blue-700 transition-all shadow-lg shadow-blue-500/20 flex items-center gap-3">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"/></svg>
                        Créer une mission
                    </button>
                @endif
            </div>
            
            <div class="space-y-16">
                @forelse($groupedAchievements as $sName => $achievements)
                    <div class="relative" wire:key="skill-group-{{ \Illuminate\Support\Str::slug($sName) }}">
                        <x-user.skill-header 
                            :skillName="$sName" 
                            :achievements="$achievements" 
                            :canEdit="$canEdit" 
                        />

                        <div class="grid grid-cols-1 gap-6 md:gap-8 pl-6 md:pl-20 relative">
                            {{-- Timeline Connector --}}
                            <div class="absolute left-4 md:left-[4.5rem] top-0 bottom-0 w-px bg-gradient-to-b from-slate-200 via-slate-100 to-transparent"></div>
                            
                            @foreach($achievements as $item)
                                @if($item['type'] === 'achievement' && $item['model']->title === '__SKELETON__') @continue @endif
                                
                                <x-user.achievement-card 
                                    :type="$item['type']" 
                                    :model="$item['model']" 
                                    :user="$user" 
                                    :canEdit="$canEdit" 
                                    :availableSkills="$availableSkills" 
                                />
                            @endforeach
                        </div>
                    </div>
                @empty
                    <div class="py-20 text-center bg-white/40 border-2 border-dashed border-slate-200 rounded-[3rem]">
                        <p class="text-slate-400 font-black uppercase tracking-[0.3em] text-sm italic">Aucune expertise encore certifiée.</p>
                    </div>
                @endforelse

                {{-- Portfolio / CV Footer --}}
                <div class="mt-12 flex justify-center">
                    <a href="{{ route('cv.user', $user) }}" target="_blank" class="inline-flex items-center gap-6 px-12 py-8 bg-slate-900 text-white rounded-[3rem] group hover:bg-blue-600 transition-all shadow-2xl shadow-slate-900/10 hover:-translate-y-1">
                        <div class="w-16 h-16 bg-white/10 rounded-[1.5rem] flex items-center justify-center group-hover:bg-white group-hover:text-blue-600 transition-all">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4"/></svg>
                        </div>
                        <div class="text-left">
                            <div class="text-xs font-black uppercase tracking-[0.3em] opacity-60 mb-1">Portfolio Complet</div>
                            <div class="text-2xl font-black uppercase tracking-tight">Générer mon CV</div>
                        </div>
                        <svg class="w-6 h-6 opacity-40 group-hover:opacity-100 group-hover:translate-x-2 transition-all ml-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="lg:col-span-6 space-y-8">


            <!-- Reseaux (Discovery) -->
            @auth
                @if(auth()->id() === $user->id)
                    <div class="space-y-6">
                        <livewire:network.explorer :origin="$user" />
                    </div>
                @endif
            @endauth
            <!-- Mes Proches (Managed Proches) -->
            @if($user->id === auth()->id() && $user->proches->count() > 0)
                <div class="bg-blue-600 rounded-[3.5rem] p-10 text-white shadow-2xl shadow-blue-500/20 relative overflow-hidden mt-10">
                    <div class="absolute top-0 right-0 w-1/2 h-full bg-gradient-to-l from-white/10 to-transparent"></div>
                    <div class="flex items-center justify-between mb-8 relative z-10">
                        <h3 class="text-2xl font-black tracking-tight">Mes Proches</h3>
                        <button wire:click="openProcheModal" class="w-8 h-8 rounded-full bg-white/20 hover:bg-white text-white hover:text-blue-600 flex items-center justify-center transition-all">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"/></svg>
                        </button>
                    </div>
                    <div class="space-y-4 relative z-10">
                        @foreach($user->proches as $proche)
                            <div class="p-5 bg-white/10 border border-white/10 rounded-3xl hover:bg-white/20 transition-all group/p" wire:key="proche-item-{{ $proche->id }}">
                                <div class="flex items-center justify-between gap-4">
                                    <div class="flex items-center gap-4 flex-grow min-w-0">
                                        <div class="w-12 h-12 rounded-2xl bg-white/20 flex items-center justify-center font-black text-xl border-2 border-white/20">
                                            {{ substr($proche->name, 0, 1) }}
                                        </div>
                                        <div class="min-w-0">
                                            <div class="text-sm font-black uppercase tracking-tight truncate">{{ $proche->name }}</div>
                                            <div class="text-[9px] font-bold text-blue-200 uppercase tracking-widest">{{ $proche->achievements->count() }} compétence(s)</div>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <button wire:click.stop="openCreateModal({{ $proche->id }})" class="p-3 bg-white/10 hover:bg-white text-white hover:text-blue-600 rounded-2xl transition-all shadow-sm" title="Créer une mission">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"/></svg>
                                        </button>
                                        
                                        @if($proche->transfer_token)
                                            <div class="bg-white/90 px-3 py-1.5 rounded-xl flex flex-col items-center">
                                                <span class="text-[7px] font-black text-slate-500 uppercase leading-none mb-1">Code</span>
                                                <span class="text-xs font-black text-blue-600 leading-none">{{ $proche->transfer_code }}</span>
                                            </div>
                                        @else
                                            <button wire:click="generateTransfer({{ $proche->id }})" class="p-3 bg-white/10 hover:bg-white text-white hover:text-blue-600 rounded-2xl transition-all shadow-sm" title="Transférer le compte">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                                @if($proche->transfer_token)
                                    <div class="mt-4 pt-4 border-t border-white/10">
                                        <p class="text-[8px] font-bold text-blue-100 uppercase tracking-widest leading-relaxed break-all">
                                            Transfert : <span class="select-all bg-blue-700/50 px-1 rounded">{{ url('/proches/claim/'.$proche->transfer_token) }}</span>
                                        </p>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>


    <!-- Create Success Modal (Two-Step) -->
    @if($showCreateModal)
        <div class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6 overflow-hidden bg-red-500/10">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-xl" wire:click.self="cancelCreate"></div>
            <div class="absolute top-4 left-4 bg-red-600 text-white px-2 py-1 text-[8px] font-bold z-[110]">DEBUG: CREATE MODAL ACTIVE</div>
            
            <div class="relative bg-white rounded-[4rem] shadow-2xl w-full max-w-2xl overflow-hidden max-h-[90vh] flex flex-col">
                <!-- Progress Header -->
                <div class="bg-blue-600 p-8 md:p-10 text-white relative shrink-0">
                    <button wire:click="cancelCreate" class="absolute top-8 right-8 text-white/70 hover:text-white transition-colors bg-white/10 hover:bg-white/20 rounded-full p-2 z-50">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                    <div class="absolute top-0 right-0 w-1/3 h-full bg-gradient-to-l from-blue-600/20 to-transparent pointer-events-none z-0"></div>
                    <div class="relative z-10">
                        <div class="flex items-center gap-4 mb-6">
                            <span class="w-10 h-10 rounded-2xl bg-blue-600 flex items-center justify-center text-lg font-black">{{ $step }}</span>
                            <div>
                                <h3 class="text-xl md:text-2xl font-black tracking-tight leading-none uppercase pr-10">
                                    {{ $step === 1 ? 'Quelle est votre expertise ?' : 'Certifier cette expertise' }}
                                </h3>
                                <p class="text-slate-400 text-[10px] font-bold mt-1 uppercase tracking-widest">
                                    {{ $step === 1 ? 'Ajout d\'une nouvelle compétence' : 'Dépôt de preuve factuelle' }}
                                </p>
                            </div>
                        </div>
                        <!-- Progress bar -->
                        <div class="w-full h-1 bg-white/10 rounded-full overflow-hidden">
                            <div class="h-full bg-blue-500 rounded-full transition-all duration-500" style="width: {{ $step === 1 ? '50%' : '100%' }}"></div>
                        </div>
                    </div>
                </div>

                <div class="p-6 md:p-8 overflow-y-auto">
                    @if($step === 1)
                        <!-- Step 1 Form -->
                        <div class="space-y-8">
                            <div>
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4 block">Nom de la compétence</label>
                                <input wire:model="skillName" type="text" placeholder="ex: Cuisine Japonaise, Design UI, Charpente..." 
                                    class="w-full bg-slate-50 border-white focus:ring-blue-500 focus:border-blue-500 rounded-3xl p-6 text-xl font-black tracking-tight placeholder:text-slate-300">
                                @error('skillName') <span class="text-red-500 text-[10px] font-black uppercase mt-2 block">{{ $message }}</span> @enderror
                            </div>

                            <button wire:click="submitSkillOnly" class="w-full py-6 bg-slate-900 text-white rounded-[2.5rem] font-black text-sm tracking-[0.3em] uppercase hover:bg-blue-600 transition-all shadow-2xl shadow-slate-900/20">
                                Ajouter cette compétence
                            </button>
                        </div>
                    @else
                        <!-- Step 2 Form -->
                        <div class="space-y-6">
                            <div class="flex items-center gap-4 p-4 bg-blue-50 rounded-2xl border border-blue-100 mb-4">
                                <span class="text-[10px] font-black text-blue-600 uppercase tracking-widest">Compétence :</span>
                                <span class="text-sm font-black text-slate-900 italic">"{{ $skillName }}"</span>
                                {{-- No back button here as we are adding proof to an existing skill context --}}
                            </div>

                            <div>
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 block">Titre de la réussite</label>
                                <input wire:model="proofTitle" type="text" placeholder="ex: Chef de partie au restaurant X..." 
                                    class="w-full bg-slate-50 border-white focus:ring-blue-500 rounded-2xl p-4 text-sm font-bold italic">
                                @error('proofTitle') <span class="text-red-500 text-[10px] font-black uppercase mt-2 block">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 block">Description / Contexte</label>
                                <textarea wire:model="proofDescription" rows="3" placeholder="Expliquez ce que vous avez accompli, le résultat concret..." 
                                    class="w-full bg-slate-50 border-white focus:ring-blue-500 rounded-2xl p-4 text-sm font-bold italic"></textarea>
                                @error('proofDescription') <span class="text-red-500 text-[10px] font-black uppercase mt-2 block">{{ $message }}</span> @enderror
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 block">État actuel</label>
                                    <div class="flex flex-col space-y-2">
                                        <label class="flex items-center gap-3 p-3 rounded-xl border cursor-pointer transition-all" :class="$wire.proofState === 'actuelle' ? 'bg-blue-50 border-blue-500 shadow-sm' : 'bg-white border-slate-200 hover:border-blue-300'">
                                            <input type="radio" wire:model.live="proofState" value="actuelle" class="w-4 h-4 text-blue-600 focus:ring-blue-500">
                                            <span class="text-xs font-bold text-slate-700">⏳ Actuelle (En cours)</span>
                                        </label>
                                        <label class="flex items-center gap-3 p-3 rounded-xl border cursor-pointer transition-all" :class="$wire.proofState === 'verrouillée' ? 'bg-blue-50 border-blue-500 shadow-sm' : 'bg-white border-slate-200 hover:border-blue-300'">
                                            <input type="radio" wire:model.live="proofState" value="verrouillée" class="w-4 h-4 text-blue-600 focus:ring-blue-500">
                                            <span class="text-xs font-bold text-slate-700">🔒 Verrouillée (Suspendue)</span>
                                        </label>
                                        <label class="flex items-center gap-3 p-3 rounded-xl border cursor-pointer transition-all" :class="$wire.proofState === 'terminée' ? 'bg-blue-50 border-blue-500 shadow-sm' : 'bg-white border-slate-200 hover:border-blue-300'">
                                            <input type="radio" wire:model.live="proofState" value="terminée" class="w-4 h-4 text-blue-600 focus:ring-blue-500">
                                            <span class="text-xs font-bold text-slate-700">✅ Terminée (Achevée)</span>
                                        </label>
                                    </div>
                                    @error('proofState') <span class="text-red-500 text-[10px] font-black uppercase mt-2 block">{{ $message }}</span> @enderror
                                </div>

                                @if($proofState === 'terminée')
                                <div>
                                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 block">Date de réalisation</label>
                                    <input wire:model="realizedAt" type="date"
                                        class="w-full bg-slate-50 border-white focus:ring-blue-500 rounded-2xl p-4 text-sm font-bold uppercase tracking-widest">
                                    @error('realizedAt') <span class="text-red-500 text-[10px] font-black uppercase mt-2 block">{{ $message }}</span> @enderror
                                </div>
                                @endif
                            </div>
                            
                            @if($user->proches->count() > 0)
                            <div>
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 block">Lié à un proche ? (Optionnel)</label>
                                <select wire:model="selectedProcheId" class="w-full bg-slate-50 border-white focus:ring-blue-500 rounded-2xl p-4 text-sm font-bold">
                                    <option value="">-- Mon propre profil --</option>
                                    @foreach($user->proches as $proche)
                                        <option value="{{ $proche->id }}">{{ $proche->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @endif

                            <div>
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 block">Compétences techniques additionnelles (Tags)</label>
                                <div class="flex flex-wrap gap-2 p-4 bg-slate-50 rounded-2xl border border-white min-h-[100px]">
                                    @foreach($availableSkills as $skill)
                                        <label class="cursor-pointer group">
                                            <input type="checkbox" wire:model.live="selectedSkillIds" value="{{ $skill->id }}" class="hidden">
                                            <span @class([
                                                'px-3 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-tight border transition-all',
                                                'bg-blue-600 border-blue-600 text-white shadow-lg shadow-blue-500/20' => in_array($skill->id, $selectedSkillIds),
                                                'bg-white border-slate-200 text-slate-400 hover:border-blue-300 hover:text-blue-500' => !in_array($skill->id, $selectedSkillIds)
                                            ])>
                                                {{ $skill->name }}
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                                <p class="text-[9px] text-slate-400 font-medium italic mt-2">Sélectionnez les compétences techniques liées à cette réalisation.</p>
                            </div>

                            {{-- Removed Information & Medias section from Create to avoid Ghost drafts. Users can add media by Editing the achievement later. --}}

                            @if ($errors->any())
                                <div class="bg-red-50 text-red-500 p-4 rounded-xl text-xs font-bold mb-4">
                                    <ul class="list-disc pl-5">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <button wire:click="submitProof" class="w-full py-6 bg-blue-600 text-white rounded-[2.5rem] font-black text-sm tracking-[0.3em] uppercase hover:bg-blue-700 transition-all shadow-2xl shadow-blue-500/20 mt-6">
                                Certifier cette preuve
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
    <!-- Create Proche Modal -->
    @if($showProcheModal)
        <div class="fixed inset-0 z-[100] flex items-center justify-center px-6">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-xl" wire:click="$set('showProcheModal', false)"></div>
            
            <div class="relative bg-white rounded-[4rem] shadow-2xl w-full max-w-lg overflow-hidden">
                <div class="bg-slate-900 p-10 text-white">
                    <h3 class="text-2xl font-black uppercase tracking-tight">Nouveau Proche</h3>
                    <p class="text-slate-400 text-[10px] font-black uppercase tracking-widest mt-2">Créez un réseau pour votre entourage</p>
                </div>
                
                <div class="p-10 space-y-8">
                    <div>
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4 block">Nom complet du proche</label>
                        <input wire:model="procheName" type="text" placeholder="ex: Jean Dupont, Marie Durant..." 
                            class="w-full bg-slate-50 border-white focus:ring-blue-500 rounded-3xl p-6 text-xl font-black tracking-tight">
                        @error('procheName') <span class="text-red-500 text-[10px] font-black uppercase mt-2 block">{{ $message }}</span> @enderror
                    </div>

                    <button wire:click="createProche" class="w-full py-6 bg-blue-600 text-white rounded-[2rem] font-black text-sm tracking-[0.2em] uppercase hover:bg-blue-700 transition-all shadow-xl shadow-blue-500/20">
                        Créer le profil proche
                    </button>
                    
                    <p class="text-center text-[9px] font-medium text-slate-400 italic">
                        Le profil sera géré par vous jusqu'à son transfert définitif.
                    </p>
                </div>
            </div>
        </div>
    @endif

    <!-- Achievement Validation Details Modal -->
    @if($showValidationModal && $selectedAchievement)
        <div class="fixed inset-0 z-[110] flex items-center justify-center px-6">
            <div class="absolute inset-0 bg-slate-900/80 backdrop-blur-xl" wire:click="$set('showValidationModal', false)"></div>
            
            <div class="relative bg-white rounded-[4rem] shadow-2xl w-full max-w-xl overflow-hidden">
                <div class="bg-slate-900 p-10 text-white flex items-center justify-between">
                    <div>
                        <h3 class="text-2xl font-black uppercase tracking-tight italic">"{{ $selectedAchievement->title }}"</h3>
                        <div class="flex items-center gap-4 mt-2">
                            <p class="text-slate-400 text-[10px] font-black uppercase tracking-widest">Score : {{ $selectedAchievement->validations->where('type', 'validate')->count() - $selectedAchievement->validations->where('type', 'reject')->count() }}</p>
                            @if($votingType)
                                <span @class([
                                    'px-3 py-1 rounded-full text-[8px] font-black uppercase',
                                    'bg-green-600 text-white' => $votingType === 'validate',
                                    'bg-red-600 text-white' => $votingType === 'reject',
                                ])>VOTE : {{ $votingType === 'validate' ? 'VALIDER' : 'REJETER' }}</span>
                            @endif
                        </div>
                    </div>
                    <button wire:click="$set('showValidationModal', false)" class="text-slate-400 hover:text-white transition-colors">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                
                    @php 
                        $myV = $selectedAchievement->validations->where('user_id', auth()->id())->first();
                    @endphp

                    @if(($votingType || $myV) && !($myV?->reply))
                        <div class="bg-slate-50 p-8 rounded-[2.5rem] border border-slate-100 shadow-inner">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4 block">
                                {{ $myV ? 'Modifier votre retour' : 'Pourquoi ' . ($votingType === 'validate' ? 'validez' : 'rejetez') . '-vous cette preuve ?' }}
                            </label>
                            <textarea wire:model="validationComment" rows="3" placeholder="Laissez un message (optionnel)..." 
                                class="w-full bg-white border-white focus:ring-blue-500 rounded-2xl p-4 text-sm font-bold italic shadow-sm"></textarea>
                            <button wire:click="confirmValidation" @class([
                                'w-full mt-6 py-4 rounded-2xl font-black text-xs uppercase tracking-widest text-white transition-all shadow-lg',
                                'bg-green-600 hover:bg-green-700 shadow-green-500/20' => ($votingType ?? ($myV?->type)) === 'validate',
                                'bg-red-600 hover:bg-red-700 shadow-red-500/20' => ($votingType ?? ($myV?->type)) === 'reject',
                            ])>
                                {{ $myV ? 'Mettre à jour mon feedback' : 'Confirmer mon vote' }}
                            </button>
                        </div>
                     @endif

                    <div class="space-y-4">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block px-4 italic">Historique des retours</label>
                        @forelse($selectedAchievement->validations as $v)
                            <div @class([
                                'p-6 rounded-[2rem] flex gap-5 border transition-all',
                                'bg-green-50/50 border-green-100 text-green-900 italic' => $v->type === 'validate',
                                'bg-red-50/50 border-red-100 text-red-900 italic' => $v->type === 'reject',
                            ])>
                                <a href="{{ route('users.show', $v->user) }}" class="shrink-0 hover:scale-110 transition-transform">
                                    <img src="{{ $v->user->avatar }}" class="w-12 h-12 rounded-2xl object-cover ring-4 ring-white shadow-md">
                                </a>
                                <div class="flex-grow">
                                    <div class="flex items-center justify-between mb-1">
                                        <a href="{{ route('users.show', $v->user) }}" class="text-sm font-black uppercase hover:text-blue-600 transition-colors">{{ $v->user->name }}</a>
                                        <span @class([
                                            'text-[8px] font-black uppercase px-2 py-0.5 rounded-full',
                                            'bg-green-600 text-white' => $v->type === 'validate',
                                            'bg-red-600 text-white' => $v->type === 'reject',
                                        ])>{{ $v->type === 'validate' ? 'Validé' : 'Rejeté' }}</span>
                                    </div>
                                    <x-user-skills-tags :user="$v->user" limit="3" class="mb-3 scale-90 origin-left" />
                                    @if($v->comment)
                                        <p class="text-xs font-semibold leading-relaxed">"{{ $v->comment }}"</p>
                                    @endif

                                    @if($v->reply)
                                        <div class="mt-4 p-4 bg-white/60 rounded-2xl border border-white/80 shadow-sm relative pr-12">
                                            <div class="flex items-center gap-2 mb-1">
                                                <span class="text-[8px] font-black uppercase text-blue-600 tracking-tighter">Réponse de l'expert</span>
                                            </div>
                                            <p class="text-[10px] font-bold italic text-slate-700">"{{ $v->reply }}"</p>
                                            <div class="absolute top-4 right-4 text-blue-400">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                            </div>
                                        </div>
                                    @elseif(auth()->id() === $selectedAchievement->user_id && auth()->id() !== $v->user_id)
                                        <div class="mt-4 pt-4 border-t border-slate-100/50">
                                            <div class="flex gap-2">
                                                <input wire:model="replyText" type="text" placeholder="Répondre à ce retour..." 
                                                    class="flex-grow bg-white/50 border-none rounded-xl px-4 py-2 text-[10px] font-bold italic focus:ring-2 focus:ring-blue-500/20">
                                                <button wire:click="submitReply({{ $v->id }})" class="bg-blue-600 text-white p-2 rounded-xl hover:bg-blue-700 transition-all shadow-md">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                                </button>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="py-12 text-center">
                                <p class="text-slate-400 font-black uppercase tracking-widest text-[10px]">Aucune validation pour le moment.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
    @endif

    <!-- Métriques de Confiance (Unified Footer) -->
    <div class="max-w-7xl lg:col-span-12 mx-auto px-6 mt-20 mb-12">
        <div class="bg-white/60 backdrop-blur-3xl border border-white/60 rounded-[3.5rem] p-10 md:p-16 shadow-2xl shadow-blue-500/5 relative overflow-hidden group">
            <div class="absolute top-0 right-0 w-1/3 h-full bg-gradient-to-l from-blue-400/5 to-transparent"></div>
            
            <h3 class="text-xl font-black text-slate-900 uppercase tracking-[0.3em] mb-12 flex items-center gap-4">
                <span class="w-2 h-10 bg-blue-600 rounded-full"></span>
                Métriques de Confiance
            </h3>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 relative z-10">
                <div class="space-y-10">
                    <div>
                        <div class="flex justify-between items-end mb-6">
                            <span class="text-sm font-black text-slate-900 uppercase tracking-widest italic">Trust Index Global</span>
                            <span class="text-6xl font-black text-slate-900 tracking-tighter">{{ $user->trust_score }}<span class="text-blue-200">%</span></span>
                        </div>
                        <div class="w-full h-4 bg-slate-100 rounded-full overflow-hidden p-1 border border-slate-50">
                            <div class="h-full bg-gradient-to-r from-blue-500 to-indigo-600 rounded-full shadow-lg shadow-blue-500/20" style="width: {{ $user->trust_score }}%"></div>
                        </div>
                    </div>
                    
                    <p class="text-slate-500 font-medium italic leading-relaxed">
                        Le Trust Index est calculé sur la base des validations reçues par ses pairs et de sa participation active au sein des cercles.
                    </p>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
                    <div class="p-8 bg-green-50/50 rounded-[2.5rem] border border-green-100/50 flex flex-col items-center justify-center text-center group-hover:bg-green-100/50 transition-all duration-500">
                        <div class="text-4xl font-black text-green-600 leading-none mb-3">+{{ $valCount }}</div>
                        <div class="text-[10px] font-black text-green-600/60 uppercase tracking-widest">Validations</div>
                    </div>
                    <div class="p-8 bg-red-50/50 rounded-[2.5rem] border border-red-100/50 flex flex-col items-center justify-center text-center group-hover:bg-red-100/50 transition-all duration-500">
                        <div class="text-4xl font-black text-red-600 leading-none mb-3">-{{ $rejCount }}</div>
                        <div class="text-[10px] font-black text-red-600/60 uppercase tracking-widest">Réfutations</div>
                    </div>
                    <div class="p-8 bg-blue-50/50 rounded-[2.5rem] border border-blue-100/50 flex flex-col items-center justify-center text-center group-hover:bg-blue-100/50 transition-all duration-500">
                        <div class="text-4xl font-black text-blue-600 leading-none mb-3">{{ $ownedProjectsCount }}</div>
                        <div class="text-[10px] font-black text-blue-600/60 uppercase tracking-widest">Projets</div>
                    </div>
                    <div class="p-8 bg-indigo-50/50 rounded-[2.5rem] border border-indigo-100/50 flex flex-col items-center justify-center text-center group-hover:bg-indigo-100/50 transition-all duration-500">
                        <div class="text-4xl font-black text-indigo-600 leading-none mb-3">{{ $ownedOffersCount }}</div>
                        <div class="text-[10px] font-black text-indigo-600/60 uppercase tracking-widest">Missions</div>
                    </div>
                    <div class="p-8 bg-slate-50 rounded-[2.5rem] border border-slate-100 flex flex-col items-center justify-center text-center group-hover:bg-blue-50 transition-all duration-500">
                        <div class="text-4xl font-black text-slate-900 leading-none mb-3">{{ $user->joinedCircles->count() }}</div>
                        <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Cercles</div>
                    </div>
                </div>
            </div>
        </div>
    @include('livewire.offers.modals')

    {{-- Edit Modal --}}
    @if($showEditModal)
        <div class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-md">
            <div class="bg-white w-full max-w-lg rounded-[2.5rem] shadow-2xl overflow-hidden border border-white p-8">
                <div class="flex items-center justify-between mb-8">
                    <h3 class="text-2xl font-black text-slate-900 tracking-tight italic">
                        Modifier {{ $editingType === 'project' ? 'la réalisation' : 'l\'expertise' }}
                    </h3>
                    <button wire:click="cancelEdit" class="w-10 h-10 flex items-center justify-center bg-slate-50 text-slate-400 rounded-2xl hover:bg-slate-100 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="space-y-6">
                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1">Titre</label>
                        <input type="text" wire:model="editTitle" class="w-full bg-slate-50 border-none rounded-2xl p-4 text-sm font-bold placeholder:text-slate-400 focus:ring-2 focus:ring-blue-500 outline-none transition-all shadow-sm">
                        @error('editTitle') <span class="text-red-500 text-[10px] font-bold mt-1 ml-1">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1">Description</label>
                        <textarea wire:model="editDescription" rows="4" class="w-full bg-slate-50 border-none rounded-2xl p-4 text-sm font-bold placeholder:text-slate-400 focus:ring-2 focus:ring-blue-500 outline-none transition-all shadow-sm resize-none"></textarea>
                        @error('editDescription') <span class="text-red-500 text-[10px] font-bold mt-1 ml-1">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1">Date de réalisation</label>
                        <input type="date" wire:model="editDate" class="w-full bg-slate-50 border-none rounded-2xl p-4 text-sm font-bold placeholder:text-slate-400 focus:ring-2 focus:ring-blue-500 outline-none transition-all shadow-sm">
                        @error('editDate') <span class="text-red-500 text-[10px] font-bold mt-1 ml-1">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex gap-3 pt-4">
                        <button wire:click="cancelEdit" class="flex-1 py-4 bg-slate-50 text-slate-400 rounded-2xl font-black uppercase tracking-widest text-[10px] hover:bg-slate-100 transition-all">
                            Annuler
                        </button>
                        <button type="button" wire:click="saveEdit" class="flex-[2] py-4 bg-blue-600 text-white rounded-2xl font-black uppercase tracking-widest text-[10px] hover:bg-blue-700 shadow-lg shadow-blue-500/20 transition-all active:scale-95">
                            Enregistrer
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
