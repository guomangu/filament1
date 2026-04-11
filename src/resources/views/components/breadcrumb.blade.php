@props([
    'circle' => null,
    'user' => null,
    'skill' => null,
    'project' => null,
    'achievement' => null,
])

@php
    // Attempt to resolve the full hierarchy backwards from the lowest provided level
    if ($project || $achievement) {
        $realisation = $project ?: $achievement;
        $skill = $skill ?: $realisation->skill;
        $user = $user ?: ($project ? $project->owner : ($achievement->user ?: ($achievement->proche ? $achievement->proche->parent : null)));
    }

    if ($skill && !$user) {
        // Find top expert for this skill to provide context if missing
        $user = \App\Models\User::whereHas('achievements', function($q) use ($skill) {
                $q->where('skill_id', $skill->id);
            })->orderByDesc('trust_score')->first();
    }

    if ($user && !$circle) {
        $circle = $user->activeJoinedCircles->first() ?: $user->ownedCircles->first();
    }
@endphp

<nav class="flex flex-wrap items-center gap-2 mb-12" aria-label="Breadcrumb">
    {{-- Accueil --}}
    <a href="{{ route('search') }}" class="flex items-center gap-2 px-3 py-1.5 bg-white/60 backdrop-blur-md border border-slate-200 rounded-xl text-[10px] font-black uppercase tracking-widest text-slate-500 hover:text-blue-600 hover:border-blue-200 transition-all group shadow-sm">
        <svg class="w-3 h-3 text-slate-400 group-hover:text-blue-500 transition-colors" fill="currentColor" viewBox="0 0 20 20"><path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path></svg>
        <span>Accueil</span>
    </a>

    {{-- Circle --}}
    @if($circle)
        <div class="flex items-center text-slate-300">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
        </div>
        <a href="{{ route('circles.show', $circle) }}" class="flex items-center gap-1.5 px-3 py-1.5 bg-white/60 backdrop-blur-md border border-slate-200 rounded-xl group hover:border-blue-200 transition-all shadow-sm">
            <span class="text-[9px] font-black uppercase tracking-[0.15em] text-slate-400">Cercle :</span>
            <span class="text-[10px] font-black uppercase tracking-widest text-slate-700 group-hover:text-blue-600 transition-colors">{{ $circle->name }}</span>
        </a>
    @endif

    {{-- User --}}
    @if($user)
        <div class="flex items-center text-slate-300">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
        </div>
        <a href="{{ route('users.show', $user) }}" class="flex items-center gap-1.5 px-3 py-1.5 bg-white/60 backdrop-blur-md border border-slate-200 rounded-xl group hover:border-blue-200 transition-all shadow-sm">
            <span class="text-[9px] font-black uppercase tracking-[0.15em] text-slate-400">Utilisateur :</span>
            <span class="text-[10px] font-black uppercase tracking-widest text-slate-700 group-hover:text-blue-600 transition-colors">{{ $user->name }}</span>
        </a>
    @endif

    {{-- Mission (Skill) --}}
    @if($skill)
        <div class="flex items-center text-slate-300">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
        </div>
        <a href="{{ route('mission.show', $skill) }}" class="flex items-center gap-1.5 px-3 py-1.5 bg-white/60 backdrop-blur-md border border-slate-200 rounded-xl group hover:border-blue-200 transition-all shadow-sm">
            <span class="text-[9px] font-black uppercase tracking-[0.15em] text-slate-400">Mission :</span>
            <span class="text-[10px] font-black uppercase tracking-widest text-slate-700 group-hover:text-blue-600 transition-colors">{{ $skill->name }}</span>
        </a>
    @endif

    {{-- Project or Achievement --}}
    @if($project || $achievement)
        @php
            $item = $project ?: $achievement;
            $isProject = (bool)$project;
        @endphp
        <div class="flex items-center text-slate-300">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
        </div>
        <div class="flex items-center gap-1.5 px-3 py-1.5 bg-blue-50/50 backdrop-blur-md border border-blue-200 rounded-xl shadow-sm">
            <span class="text-[9px] font-black uppercase tracking-[0.15em] text-blue-400">{{ $isProject ? 'Réalisation' : 'Réalisation' }} :</span>
            <span class="text-[10px] font-black uppercase tracking-widest text-blue-700">{{ Str::limit($item->title, 30) }}</span>
        </div>
    @endif
</nav>
