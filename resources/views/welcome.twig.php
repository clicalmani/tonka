{% extends "base" %}

{% block title %}Welcome{% endblock %}

{% block head %}
	{{ parent() }}
	<script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'sans': ['Inter', 'sans-serif'],
                        'mono': ['JetBrains Mono', 'monospace'],
                    },
                    colors: {
                        'tonka': {
                            50: '#fffbeb',
                            100: '#fef3c7',
                            500: '#f59e0b', // Amber
                            600: '#d97706',
                            700: '#b45309',
                            900: '#451a03'
                        }
                    }
                }
            }
        }
    </script>
    
    <style>
        /* Animation pour le point vert (statut serveur) */
        @keyframes pulse-green {
            0%, 100% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7); }
            50% { box-shadow: 0 0 0 6px rgba(34, 197, 94, 0); }
        }
        .pulse-dot {
            animation: pulse-green 2s infinite;
        }
        
        /* Animation d'entrée douce */
        .fade-in-up {
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.6s ease-out, transform 0.6s ease-out;
        }
        .fade-in-up.visible {
            opacity: 1;
            transform: translateY(0);
        }
    </style>
{% endblock %}

{% block content %}
    <!-- =========================================
         HERO SECTION
         ========================================= -->
    <main class="relative overflow-hidden">
        <!-- Fond décoratif subtil -->
        <div class="absolute inset-0 -z-10">
            <div class="absolute top-0 left-1/4 w-96 h-96 bg-tonka-100 rounded-full filter blur-3xl opacity-40"></div>
            <div class="absolute bottom-0 right-1/4 w-80 h-80 bg-blue-100 rounded-full filter blur-3xl opacity-40"></div>
        </div>

        <div class="max-w-4xl mx-auto px-6 pt-20 pb-16 text-center">
            
            <div class="inline-flex items-center gap-2 bg-white border border-slate-200 rounded-full px-4 py-1.5 shadow-sm mb-8">
                <span class="w-2 h-2 bg-tonka-500 rounded-full"></span>
                <span class="text-xs font-medium text-slate-600 uppercase tracking-wide">Installation réussie</span>
            </div>

            <h1 class="text-4xl md:text-6xl font-bold text-slate-900 tracking-tight leading-tight mb-6">
                Bienvenue dans l'écosystème <br>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-tonka-600 to-orange-500">Tonka</span>
            </h1>
            
            <p class="text-lg md:text-xl text-slate-600 max-w-2xl mx-auto mb-10 leading-relaxed">
                Le moteur PHP rapide, flexible et élégant. Votre application est prête à être construite avec la puissance de Twig.
            </p>

            <!-- Visuel Central : Structure Dossiers -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xl p-6 md:p-8 max-w-lg mx-auto text-left transition-transform hover:scale-[1.02] duration-300 fade-in-up">
                <div class="flex items-center gap-2 border-b border-slate-100 pb-3 mb-4">
                    <div class="w-3 h-3 rounded-full bg-red-400"></div>
                    <div class="w-3 h-3 rounded-full bg-yellow-400"></div>
                    <div class="w-3 h-3 rounded-full bg-green-400"></div>
                    <span class="ml-4 text-xs text-slate-400 font-mono">~/mon-projet</span>
                </div>
                
                <!-- Arborescence stylisée -->
                <div class="font-mono text-sm space-y-2 text-slate-700">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-tonka-500" fill="currentColor" viewBox="0 0 20 20"><path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"></path></svg>
                        <span class="font-semibold">resources/</span>
                    </div>
                    <div class="pl-6 flex items-center gap-2">
                        <svg class="w-4 h-4 text-slate-400" fill="currentColor" viewBox="0 0 20 20"><path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"></path></svg>
                        <span class="text-slate-600">views/</span>
                        <span class="text-xs text-green-600 ml-auto bg-green-50 px-2 py-0.5 rounded">Twig</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-blue-500" fill="currentColor" viewBox="0 0 20 20"><path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"></path></svg>
                        <span class="font-semibold">config/</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-purple-500" fill="currentColor" viewBox="0 0 20 20"><path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"></path></svg>
                        <span class="font-semibold">public/</span>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- =========================================
         SECTION "THE ENGINE" (TWIG)
         ========================================= -->
    <section class="py-20 bg-white border-y border-slate-100">
        <div class="max-w-6xl mx-auto px-6 grid md:grid-cols-2 gap-12 items-center">
            
            <!-- Colonne Gauche : Texte -->
            <div class="fade-in-up">
                <span class="text-xs font-bold text-tonka-600 uppercase tracking-widest mb-2 block">Templates</span>
                <h2 class="text-3xl md:text-4xl font-bold text-slate-900 mb-6">
                    Twig, Simplifié et Puissant
                </h2>
                <div class="space-y-4 text-slate-600">
                    <div class="flex gap-3">
                        <div class="mt-1 h-2 w-2 rounded-full bg-tonka-500"></div>
                        <p><strong class="text-slate-700">Séparation claire :</strong> Logique métier dans les contrôleurs, présentation dans les vues.</p>
                    </div>
                    <div class="flex gap-3">
                        <div class="mt-1 h-2 w-2 rounded-full bg-tonka-500"></div>
                        <p><strong class="text-slate-700">Héritage natif :</strong> Créez des layouts modulaires sans complexité.</p>
                    </div>
                    <div class="flex gap-3">
                        <div class="mt-1 h-2 w-2 rounded-full bg-tonka-500"></div>
                        <p><strong class="text-slate-700">Sécurité intégrée :</strong> Échappement automatique des variables.</p>
                    </div>
                </div>
            </div>

            <!-- Colonne Droite : Code Snippet -->
            <div class="bg-slate-900 rounded-xl shadow-2xl overflow-hidden fade-in-up">
                <div class="flex items-center justify-between px-4 py-3 bg-slate-800 border-b border-slate-700">
                    <span class="text-slate-400 text-xs font-mono">base.twig.php</span>
                    <span class="text-xs bg-slate-700 text-green-400 px-2 py-0.5 rounded">twig</span>
                </div>

                {% verbatim %}
                <pre class="p-6 text-sm font-mono leading-relaxed overflow-x-auto">
					<code class="text-slate-300"><span class="text-pink-400">&lt;!DOCTYPE html&gt;</span>
					<span class="text-pink-400">&lt;html&gt;</span>
						<span class="text-pink-400">&lt;body&gt;</span>
							<span class="text-purple-400">{%</span> <span class="text-blue-300">block</span> <span class="text-yellow-300">content</span> <span class="text-purple-400">%}</span>
								<span class="text-slate-500">{# Votre contenu ici #}</span>
								<span class="text-teal-300">&lt;h1&gt;</span>Hello Tonka!<span class="text-teal-300">&lt;/h1&gt;</span>
							<span class="text-purple-400">{%</span> <span class="text-blue-300">endblock</span> <span class="text-purple-400">%}</span>
						<span class="text-pink-400">&lt;/body&gt;</span>
					<span class="text-pink-400">&lt;/html&gt;</span></code>
                </pre>
                {% endverbatim %}
                
            </div>
        </div>
    </section>

    <!-- =========================================
         SECTION "NEXT STEPS"
         ========================================= -->
    <section class="py-20 bg-slate-50">
        <div class="max-w-6xl mx-auto px-6 text-center mb-12">
            <h2 class="text-3xl font-bold text-slate-900 mb-4">Prochaines Étapes</h2>
            <p class="text-slate-600">Commencez à construire votre application en suivant ces bases.</p>
        </div>

        <div class="max-w-5xl mx-auto px-6 grid md:grid-cols-3 gap-6">
            
            <!-- Carte 1 : Routes -->
            <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm hover:shadow-lg hover:border-tonka-200 transition-all duration-300 fade-in-up group">
                <div class="w-12 h-12 bg-tonka-50 text-tonka-600 rounded-lg flex items-center justify-center mb-4 group-hover:bg-tonka-500 group-hover:text-white transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
                </div>
                <h3 class="text-lg font-semibold text-slate-900 mb-2">Définir les Routes</h3>
                <p class="text-sm text-slate-500 mb-4">Configurez vos points d'entrée dans le fichier <code class="text-xs bg-slate-100 px-1 rounded">config/routes.php</code>.</p>
                <a href="{{ route_doc_url }}" class="text-sm font-medium text-tonka-600 hover:text-tonka-700 flex items-center gap-1">
                    Lire la doc <span>→</span>
                </a>
            </div>

            <!-- Carte 2 : Controllers -->
            <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm hover:shadow-lg hover:border-tonka-200 transition-all duration-300 fade-in-up group" style="transition-delay: 100ms;">
                <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-lg flex items-center justify-center mb-4 group-hover:bg-blue-500 group-hover:text-white transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path></svg>
                </div>
                <h3 class="text-lg font-semibold text-slate-900 mb-2">Créer un Contrôleur</h3>
                <p class="text-sm text-slate-500 mb-4">Organisez votre logique métier proprement dans le dossier <code class="text-xs bg-slate-100 px-1 rounded">app/Controllers</code>.</p>
                <a href="{{ controller_doc_url }}" class="text-sm font-medium text-blue-600 hover:text-blue-700 flex items-center gap-1">
                    Voir l'exemple <span>→</span>
                </a>
            </div>

            <!-- Carte 3 : Views -->
            <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm hover:shadow-lg hover:border-tonka-200 transition-all duration-300 fade-in-up group" style="transition-delay: 200ms;">
                <div class="w-12 h-12 bg-purple-50 text-purple-600 rounded-lg flex items-center justify-center mb-4 group-hover:bg-purple-500 group-hover:text-white transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                </div>
                <h3 class="text-lg font-semibold text-slate-900 mb-2">Rendre les Vues</h3>
                <p class="text-sm text-slate-500 mb-4">Utilisez Twig pour afficher vos données HTML dans <code class="text-xs bg-slate-100 px-1 rounded">app/views</code>.</p>
                <a href="{{ twig_url }}" class="text-sm font-medium text-purple-600 hover:text-purple-700 flex items-center gap-1">
                    Guide Twig <span>→</span>
                </a>
            </div>

        </div>
    </section>
{% endblock %}

{% block script %}
	<!-- =========================================
         JAVASCRIPT
         ========================================= -->
    <script>
        // 1. Animation d'apparition au scroll (Intersection Observer)
        document.addEventListener('DOMContentLoaded', () => {
            const observerOptions = {
                root: null,
                rootMargin: '0px',
                threshold: 0.1
            };

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('visible');
                    }
                });
            }, observerOptions);

            const elements = document.querySelectorAll('.fade-in-up');
            elements.forEach(el => observer.observe(el));
        });

        // 2. Simulation simple de l'ouverture de dossier (Effet click)
        // Permet d'ajouter une petite interactivité si l'utilisateur clique sur la visualisation des dossiers
        const folderView = document.querySelector('.font-mono.text-sm');
        if(folderView) {
            folderView.addEventListener('click', (e) => {
                // Trouve le parent 'div' le plus proche si on clique sur un span ou svg
                const targetRow = e.target.closest('.flex.items-center');
                if(targetRow && targetRow.querySelector('svg')) {
                    targetRow.classList.toggle('text-tonka-600');
                    targetRow.classList.toggle('font-bold');
                }
            });
        }
    </script>
{% endblock %}