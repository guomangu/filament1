<div class="min-h-screen bg-slate-50 font-['Inter'] selection:bg-slate-200 antialiased print:bg-white">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');
        
        @media print {
            .no-print { display: none !important; }
            html, body { 
                background: white !important; 
                margin: 0 !important; 
                padding: 0 !important; 
                min-width: 210mm !important; /* Force Android to use Desktop proportions */
                -webkit-print-color-adjust: exact !important; 
                print-color-adjust: exact !important;
            }
            @page { size: A4; margin: 10mm; }
            .cv-container { 
                box-shadow: none !important; 
                border: none !important; 
                margin: 0 !important; 
                width: 210mm !important; 
                min-width: 210mm !important; 
                max-width: none !important; 
            }
            /* Prevent printing engine from slicing images and crucial sections in half */
            section, img, h2, h3, h4 { break-inside: avoid; page-break-inside: avoid; }
        }

        .cv-container {
            width: 210mm;
            min-height: 297mm;
            margin: 2rem auto;
            background: white;
            box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.1);
            border: 1px solid #e2e8f0;
            display: flex;
            flex-direction: column;
        }

        .qr-small { width: 60px; height: 60px; }
        .qr-large { width: 120px; height: 120px; }
    </style>

    <!-- Controls -->
    <div class="no-print fixed top-6 right-6 flex items-center gap-3 z-50">
        <button onclick="window.print()" class="px-6 py-3 bg-slate-900 text-white rounded-xl font-bold text-sm hover:scale-105 transition-all shadow-lg flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4"/></svg>
            Imprimer / PDF
        </button>
        <a href="{{ $this->type === 'user' ? route('users.show', $user) : ($this->type === 'mission' ? route('mission.show', $skill) : route('projects.show', $project)) }}" class="p-3 bg-white border border-slate-200 text-slate-500 rounded-xl hover:bg-slate-50 transition-all shadow-sm">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
        </a>
    </div>

    <div class="cv-container">
        <!-- Top Bar / Border Accent -->
        <div class="h-2 bg-slate-900"></div>

        <!-- Main Header -->
        <header class="p-10 border-b border-slate-100 flex justify-between items-start">
            <div class="flex gap-8 items-center">
                <div class="relative shrink-0">
                    @php
                        $avatar = match($type) {
                            'user' => $user->avatar,
                            'mission' => 'https://ui-avatars.com/api/?name='.urlencode($skill->name).'&background=0f172a&color=fff&size=200',
                            'project' => $project->owner->avatar ?? 'https://ui-avatars.com/api/?name='.urlencode($project->title).'&background=3b82f6&color=fff&size=200',
                        };
                    @endphp
                    <img src="{{ $avatar }}" class="w-32 h-32 rounded-2xl object-cover border-4 border-slate-50 shadow-sm">
                    <div class="absolute -bottom-3 -right-3 bg-slate-900 text-white px-3 py-1.5 rounded-lg text-xs font-black shadow-lg">
                        {{ match($type) {
                            'user' => $user->trust_score . '%',
                            'mission' => 'Mission',
                            'project' => 'Projet',
                        } }}
                    </div>
                </div>
                <div>
                    <h1 class="text-4xl font-black text-slate-900 tracking-tight uppercase leading-none mb-3">
                        {{ match($type) {
                            'user' => $user->name,
                            'mission' => $skill->name,
                            'project' => $project->title,
                        } }}
                    </h1>
                    <div class="flex flex-wrap items-center gap-4 text-slate-500 font-semibold text-sm">
                        @if($type === 'user')
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-green-500"></span>
                                Membre Vérifié guuu.fr
                            </div>
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                                {{ $user->location ?? 'Global' }}
                            </div>
                        @elseif($type === 'mission')
                            <span class="text-blue-600 uppercase tracking-widest text-xs">Aptitude & Compétence Clef</span>
                        @else
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 bg-slate-100 rounded text-[10px] font-black uppercase text-slate-600">{{ $project->status }}</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                                {{ $project->address ?? 'Global' }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="text-right shrink-0">
                @php
                    $url = match($type) {
                        'user' => route('users.show', $user),
                        'mission' => route('mission.show', $skill),
                        'project' => route('projects.show', $project),
                    };
                    $qr = "https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=" . urlencode(url($url));
                @endphp
                <img src="{{ $qr }}" class="qr-large border-2 border-slate-100 rounded-xl p-1 bg-white inline-block">
                <p class="text-[8px] font-black uppercase text-slate-400 mt-2 tracking-widest">Scanner le Profil</p>
            </div>
        </header>

        <!-- Body -->
        <div class="flex-grow grid grid-cols-[18rem_minmax(0,1fr)]">
            <!-- Sidebar -->
            <aside class="border-r border-slate-50 bg-slate-50/30 p-8 space-y-8">
                <!-- Bio/Desc -->
                <section>
                    <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-3">À Propos</h3>
                    <p class="text-sm text-slate-600 leading-relaxed font-medium">
                        {{ match($type) {
                            'user' => $user->bio ?? 'Aucune biographie.',
                            'mission' => $skill->description ?? 'Description de la mission à venir.',
                            'project' => $project->description,
                        } }}
                    </p>
                </section>

                <!-- Informations / Contact -->
                @php
                    $infos = match($type) {
                        'user' => $user->informations,
                        'project' => $project->informations,
                        'mission' => collect(), // Skills might not have direct infos yet
                    };
                @endphp
                @if($infos->count() > 0)
                <section>
                    <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-4">Informations & Liens</h3>
                    <div class="space-y-6">
                        @foreach($infos as $info)
                            <div class="flex gap-4 items-start">
                                @php
                                    $isLink = preg_match('/https?:\/\/[^\s]+/', $info->label);
                                @endphp
                                @if($isLink)
                                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=80x80&data={{ urlencode($info->label) }}" class="qr-small shadow-sm border border-slate-100 rounded-lg shrink-0">
                                @else
                                    <div class="qr-small flex items-center justify-center bg-white border border-slate-100 rounded-lg shrink-0 text-slate-300">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </div>
                                @endif
                                <div class="min-w-0">
                                    <h4 class="text-[10px] font-black text-slate-900 uppercase truncate mb-0.5">{{ $info->title }}</h4>
                                    <p class="text-[8px] font-bold text-slate-500 break-words leading-tight">{{ $info->label }}</p>
                                    @if($isLink)
                                        <a href="{{ $info->label }}" class="inline-block mt-1 text-[8px] font-black text-blue-600 uppercase no-print underline">Ouvrir le lien</a>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
                @endif

                <!-- Stats -->
                <section>
                    <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-3 text-center">Indicateurs de Confiance</h3>
                    <div class="grid grid-cols-2 gap-2">
                        @php
                            $valsCount = match($type) {
                                'user' => $user->validationsReceived->where('type', 'validate')->count(),
                                'project' => $project->getAllMemberValidations()->where('type', 'validate')->count(),
                                'mission' => 0, // Should we show total validations for this skill across site?
                            };
                            $rejsCount = match($type) {
                                'user' => $user->validationsReceived->where('type', 'reject')->count(),
                                'project' => $project->getAllMemberValidations()->where('type', 'reject')->count(),
                                'mission' => 0,
                            };
                        @endphp
                        <div class="bg-white p-3 rounded-xl border border-slate-100 text-center">
                            <div class="text-[8px] font-black text-slate-400 uppercase mb-1">Validé</div>
                            <div class="text-lg font-black text-green-600">+{{ $valsCount }}</div>
                        </div>
                        <div class="bg-white p-3 rounded-xl border border-slate-100 text-center">
                            <div class="text-[8px] font-black text-slate-400 uppercase mb-1">Alertes</div>
                            <div class="text-lg font-black text-red-600">-{{ $rejsCount }}</div>
                        </div>
                    </div>
                </section>
            </aside>

            <!-- Main Panel -->
            <main class="p-10 space-y-10">
                @if($type === 'user')
                    <!-- User Realisations (Achievements + Projects) -->
                    <section>
                        <h2 class="text-lg font-black text-slate-900 border-b-2 border-slate-900 pb-2 mb-6 uppercase tracking-tight">Timeline des Réalisations</h2>
                        <div class="space-y-8">
                            @php
                                $combined = collect();
                                foreach($user->achievements as $ach) {
                                    $combined->push([
                                        'type' => 'achievement',
                                        'model' => $ach,
                                        'date' => $ach->realized_at ?? $ach->created_at,
                                    ]);
                                }
                                foreach($allUserProjects as $proj) {
                                    $combined->push([
                                        'type' => 'project',
                                        'model' => $proj,
                                        'date' => $proj->realized_at ?? $proj->created_at,
                                    ]);
                                }
                                $combined = $combined->sortByDesc('date');
                            @endphp

                            @foreach($combined as $item)
                                @php 
                                    $type = $item['type'];
                                    $model = $item['model'];
                                @endphp
                                <div class="relative pl-8 border-l-2 border-slate-100 pb-2">
                                    <div class="absolute -left-[9px] top-0 w-4 h-4 rounded-full bg-slate-900 border-4 border-white"></div>
                                    <div class="flex justify-between items-start mb-1">
                                        <div class="flex items-center gap-3">
                                            <h4 class="font-black text-slate-900 uppercase tracking-tight">{{ $model->title }}</h4>
                                            @if($type === 'project')
                                                <span class="text-[8px] font-black text-purple-600 bg-purple-50 px-2 py-0.5 rounded uppercase tracking-tighter">Mission</span>
                                            @endif
                                        </div>
                                        <span class="text-[10px] font-bold text-slate-400 bg-slate-50 px-2 py-0.5 rounded italic">
                                            {{ $model->realized_at ? (\Illuminate\Support\Carbon::parse($model->realized_at)->format('M Y')) : $model->created_at->format('M Y') }}
                                        </span>
                                    </div>
                                    
                                    <div class="flex items-center justify-between mb-2">
                                        <div class="text-[10px] font-black text-blue-600 uppercase tracking-widest">{{ $model->skill->name }}</div>
                                        @if($type === 'project')
                                            <div class="flex -space-x-1.5">
                                                <img src="{{ $model->owner->avatar }}" class="w-5 h-5 rounded-full border border-white shadow-sm" title="Responsable">
                                                @foreach($model->activeMembers->take(3) as $m)
                                                    @if($m->memberable_id !== $model->owner_id)
                                                        <img src="{{ $m->memberable->avatar }}" class="w-5 h-5 rounded-full border border-white shadow-sm">
                                                    @endif
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>

                                    <p class="text-xs text-slate-600 leading-relaxed font-medium mb-4">{{ $model->description }}</p>
                                    
                                    @if($model->informations && $model->informations->count() > 0)
                                        <div class="flex flex-wrap gap-2 mb-4">
                                            @foreach($model->informations as $info)
                                                <div class="flex items-center gap-2 bg-slate-50 border border-slate-100 px-2 py-1.5 rounded-lg max-w-full">
                                                    @if(preg_match('/https?:\/\/[^\s]+/', $info->label))
                                                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=60x60&data={{ urlencode($info->label) }}" class="w-8 h-8 bg-white border border-slate-200 rounded shrink-0 p-0.5">
                                                    @else
                                                        <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                    @endif
                                                    <div class="min-w-0">
                                                        <div class="text-[7px] font-black tracking-widest uppercase text-slate-400 leading-none mb-0.5">{{ $info->title }}</div>
                                                        <div class="text-[9px] font-bold text-slate-700 leading-tight truncate">{{ $info->label }}</div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                    
                                    @if($type === 'achievement')
                                        <!-- Validations with comments -->
                                        <div class="grid grid-cols-2 gap-3">
                                            @foreach($model->validations->where('type', 'validate')->whereNotNull('comment')->take(2) as $val)
                                                <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                                                    <div class="flex items-center gap-2 mb-1.5">
                                                        <img src="{{ $val->user->avatar }}" class="w-4 h-4 rounded-full border border-white">
                                                        <span class="text-[8px] font-black text-slate-900 uppercase">{{ $val->user->name }}</span>
                                                    </div>
                                                    <p class="text-[9px] text-slate-500 font-medium italic leading-snug">"{{ $val->comment }}"</p>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </section>
                @elseif($type === 'mission')
                    <!-- Mission / Skill Overview -->
                    <section>
                        <h2 class="text-lg font-black text-slate-900 border-b-2 border-slate-900 pb-2 mb-6 uppercase tracking-tight">Réalisations de la Mission</h2>
                        <div class="grid grid-cols-1 gap-4">
                            @foreach($skill->projects as $p)
                                <div class="p-6 bg-slate-50 border border-slate-200 rounded-2xl">
                                    <div class="flex items-center justify-between mb-4">
                                        <div class="flex items-center gap-4">
                                            <img src="{{ $p->owner->avatar }}" class="w-10 h-10 rounded-xl border-2 border-white shadow-sm">
                                            <div>
                                                <h4 class="font-black text-slate-900 uppercase tracking-tight">{{ $p->title }}</h4>
                                                <div class="text-[10px] font-black text-slate-400 uppercase">Par {{ $p->owner->name }} • {{ $p->status }}</div>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <span class="text-xs font-black text-blue-600">{{ $p->activeMembers->count() + 1 }} Experts</span>
                                        </div>
                                    </div>
                                    <p class="text-xs text-slate-600 leading-relaxed font-medium line-clamp-2">{{ $p->description }}</p>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @elseif($type === 'project')
                    <!-- Project Realisation Detail -->
                    <section>
                        <h2 class="text-lg font-black text-slate-900 border-b-2 border-slate-900 pb-2 mb-6 uppercase tracking-tight">Équipe & Engagement</h2>
                        <div class="grid grid-cols-2 gap-4 mb-8">
                            <div class="p-4 bg-slate-50 border border-slate-200 rounded-2xl flex items-center gap-4">
                                <img src="{{ $project->owner->avatar }}" class="w-12 h-12 rounded-xl object-cover border-2 border-white shadow-sm">
                                <div>
                                    <div class="text-[10px] font-black text-slate-400 uppercase">Propriétaire</div>
                                    <div class="font-black text-slate-900 uppercase tracking-tight">{{ $project->owner->name }}</div>
                                </div>
                            </div>
                            @foreach($project->activeMembers as $m)
                                <div class="p-4 bg-slate-50 border border-slate-200 rounded-2xl flex items-center gap-4">
                                    <img src="{{ $m->memberable->avatar }}" class="w-12 h-12 rounded-xl object-cover border-2 border-white shadow-sm">
                                    <div>
                                        <div class="text-[10px] font-black text-slate-400 uppercase">Membre Actif</div>
                                        <div class="font-black text-slate-900 uppercase tracking-tight">{{ $m->memberable->name }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <h2 class="text-lg font-black text-slate-900 border-b-2 border-slate-900 pb-2 mb-6 uppercase tracking-tight">Offres de Compétences</h2>
                        <div class="grid grid-cols-1 gap-4">
                            @foreach($project->offers as $offer)
                                <div class="p-5 border-2 border-slate-100 rounded-2xl">
                                    <div class="flex justify-between items-start mb-2">
                                        <h4 class="font-black text-slate-900 uppercase tracking-tight">{{ $offer->title }}</h4>
                                        <span class="text-[10px] font-black text-blue-600 bg-blue-50 px-2 py-1 rounded uppercase tracking-widest">Offre active</span>
                                    </div>
                                    <p class="text-xs text-slate-500 leading-relaxed italic mb-4">"{{ $offer->description }}"</p>
                                    @if($offer->informations->count() > 0)
                                        <div class="flex flex-wrap gap-3">
                                            @foreach($offer->informations as $info)
                                                <div class="flex items-center gap-1.5 px-2 py-1 bg-slate-50 rounded-lg border border-slate-100">
                                                    <span class="text-[8px] font-black text-slate-400 uppercase">{{ $info->title }}:</span>
                                                    <span class="text-[8px] font-bold text-slate-800">{{ $info->label }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endif
            </main>
        </div>

        <!-- Footer -->
        <footer class="p-10 border-t border-slate-100 flex justify-between items-center text-[8px] font-black text-slate-300 uppercase tracking-[0.5em]">
            <div>© guuu.fr • Preuve de Confiance Numérique</div>
            <div class="flex gap-8">
                <span>Généré {{ now()->format('d/m/Y H:i') }}</span>
                <span>Copie Certifiée</span>
            </div>
        </footer>
    </div>
</div>
