<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        @php
            $pageTitle = $title ?? 'TrustCircle | Réseau de Confiance & Projets de Proximité';
            $metaDesc = $description ?? "Découvrez TrustCircle, le réseau social de confiance pour collaborer sur des projets locaux, valider des compétences et bâtir des cercles d'expertises vérifiés.";
            $metaKeywords = $keywords ?? "réseau de confiance, projets locaux, entraide, expertises, cercles de confiance, collaboration, proximité";
            $ogImage = $og_image ?? asset('images/og-default.jpg');
            $ogType = $og_type ?? 'website';
        @endphp

        <title>{{ $pageTitle }}</title>
        <meta name="description" content="{{ $metaDesc }}">
        <meta name="keywords" content="{{ $metaKeywords }}">

        <script type="application/ld+json">
        {
          "@context": "https://schema.org",
          "@type": "WebSite",
          "name": "TrustCircle",
          "url": "{{ url('/') }}",
          "potentialAction": {
            "@type": "SearchAction",
            "target": "{{ url('/?search={search_term_string}') }}",
            "query-input": "required name=search_term_string"
          }
        }
        </script>

        <!-- Open Graph / Facebook -->
        <meta property="og:type" content="{{ $ogType }}">
        <meta property="og:url" content="{{ url()->current() }}">
        <meta property="og:title" content="{{ $pageTitle }}">
        <meta property="og:description" content="{{ $metaDesc }}">
        <meta property="og:image" content="{{ $ogImage }}">

        <!-- Twitter -->
        <meta property="twitter:card" content="summary_large_image">
        <meta property="twitter:url" content="{{ url()->current() }}">
        <meta property="twitter:title" content="{{ $pageTitle }}">
        <meta property="twitter:description" content="{{ $metaDesc }}">
        <meta property="twitter:image" content="{{ $ogImage }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

        <!-- Tailwind -->
        <script src="https://cdn.tailwindcss.com"></script>
        <style>
            body { font-family: 'Outfit', sans-serif; }
            .bg-gradient-mesh {
                background-color: #f8fafc;
                background-image: 
                    radial-gradient(at 0% 0%, hsla(217,100%,94%,1) 0, transparent 50%), 
                    radial-gradient(at 50% 0%, hsla(225,100%,94%,1) 0, transparent 50%), 
                    radial-gradient(at 100% 0%, hsla(217,100%,94%,1) 0, transparent 50%);
            }
            /* Refined Visibility Patch - allow dark mode overrides */
            .fi-input-wrp, .fi-input, textarea, input, select {
                background-color: white;
                color: #0f172a;
                border: 1px solid #cbd5e1;
            }
            .dark .fi-input-wrp, .dark .fi-input, .dark textarea, .dark input, .dark select {
                background-color: #1e293b;
                color: white;
            }
        </style>
        
        @livewireStyles
        @filamentStyles
    </head>
    <body class="bg-gradient-mesh text-slate-900 antialiased min-h-screen print:bg-white print:min-h-0">
        <nav x-data="{ open: false }" class="bg-white/40 backdrop-blur-2xl sticky top-0 z-50 border-b border-white/20 print:hidden">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-24">
                    <!-- Left: Logo & Messaging Trigger -->
                    <div class="flex items-center flex-shrink-0 gap-4">
                        <a href="{{ route('search') }}" class="flex items-center group">
                            <div class="w-10 h-10 md:w-12 md:h-12 bg-gradient-to-tr from-blue-600 to-indigo-600 rounded-xl md:rounded-2xl flex items-center justify-center shadow-xl shadow-blue-500/20 group-hover:rotate-12 transition-all duration-500">
                                <svg class="w-6 h-6 md:w-7 md:h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            </div>
                        </a>
                        
                        <button @auth @click="Livewire.dispatch('toggleMessaging')" @else @click="window.location.href='/admin/register'" @endauth 
                                x-data="{ unreadCount: {{ auth()->check() ? auth()->user()->receivedMessages()->whereNull('read_at')->count() : 0 }} }"
                                x-on:unread-count-updated.window="unreadCount = $event.detail.count"
                                class="flex items-center gap-3 px-4 py-2 md:py-3 bg-slate-900/5 hover:bg-blue-600 hover:text-white rounded-xl md:rounded-2xl transition-all group border border-slate-100/50 shadow-sm hover:shadow-blue-500/20 hover:-translate-y-0.5 active:translate-y-0 relative">
                            <div class="relative">
                                <svg class="w-5 h-5 md:w-6 md:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                
                                <template x-if="unreadCount > 0">
                                    <span class="absolute -top-1 -right-1 flex h-3 w-3 md:h-4 md:w-4">
                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                                        <span class="relative inline-flex rounded-full h-3 w-3 md:h-4 md:w-4 bg-red-500 border border-white items-center justify-center text-[7px] md:text-[9px] font-black text-white leading-none"
                                              x-text="unreadCount > 9 ? '+' : unreadCount">
                                        </span>
                                    </span>
                                </template>
                            </div>
                            <span class="text-[10px] md:text-xs font-black uppercase tracking-widest hidden sm:inline">Messagerie</span>
                        </button>
                    </div>

                    <!-- Right: Auth & Profile (Universal) -->
                    <div class="flex items-center space-x-3 md:space-x-6">
                        @auth
                            <div class="flex items-center gap-3 md:gap-6">
                                <a href="{{ route('users.show', auth()->user()) }}" class="flex items-center gap-2 md:gap-3 group">
                                    <div class="text-right flex flex-col justify-center">
                                        <div class="text-[8px] md:text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none">{{ auth()->user()->trust_score }}% <span class="hidden sm:inline">Trust</span></div>
                                        <div class="text-[10px] md:text-xs font-black text-slate-900 uppercase group-hover:text-blue-600 transition-colors truncate max-w-[80px] md:max-w-none">{{ auth()->user()->name }}</div>
                                    </div>
                                    <div class="w-10 h-10 md:w-12 md:h-12 rounded-xl md:rounded-2xl border-2 border-white/60 overflow-hidden group-hover:border-blue-500 transition-all shadow-lg">
                                        <img src="{{ auth()->user()->avatar_url ?? 'https://ui-avatars.com/api/?name='.auth()->user()->name }}" class="w-full h-full object-cover">
                                    </div>
                                </a>
                                
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-8 h-8 md:w-10 md:h-10 flex items-center justify-center rounded-lg md:rounded-xl hover:bg-red-50 text-slate-300 hover:text-red-500 transition-all">
                                        <svg class="w-4 h-4 md:w-5 md:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                    </button>
                                </form>
                            </div>
                        @else
                            <a href="/admin/login" class="text-[10px] md:text-xs font-black uppercase tracking-widest text-slate-600 hover:text-blue-600 transition-colors">Login</a>
                            <a href="/admin/register" class="bg-slate-900 text-white px-4 md:px-8 py-2 md:py-3 rounded-xl md:rounded-2xl text-[10px] md:text-xs font-black uppercase tracking-widest shadow-xl shadow-slate-900/20 hover:bg-blue-600 hover:-translate-y-1 transition-all">
                                Rejoindre
                            </a>
                        @endauth
                    </div>
                </div>
            </div>
        </nav>

        <main class="py-12 print:p-0">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 print:max-w-none print:p-0">
                <x-breadcrumb 
                    :circle="$breadcrumbCircle ?? null"
                    :user="$breadcrumbUser ?? null"
                    :skill="$breadcrumbSkill ?? null"
                    :project="$breadcrumbProject ?? null"
                    :achievement="$breadcrumbAchievement ?? null"
                />
                {{ $slot }}
            </div>
        </main>

        <livewire:global-messaging />
        @livewireScripts
        @filamentScripts
    <script>window.addEventListener("livewire:exception", (e) => alert(e.detail.message));</script></body>
</html>
