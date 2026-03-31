{% extends "base" %}

{% block title %}404 Page Not Found {% endblock %}

{% block head %}
    {{ parent() }}
    <style>
        /* Animated text that slowly blinks */
        @keyframes blink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        .blink-slow {
            animation: blink 2s ease-in-out infinite;
        }
        
        /* Slight "glitch" effect on the title */
        .glitch-text {
            text-shadow: 2px 0px 0px rgba(245, 158, 11, 0.2), -2px 0px 0px rgba(59, 130, 246, 0.2);
        }
    </style>
{% endblock %}

{% block header_status %}{% endblock header_status %}

{% block content %}
    <main class="flex-grow flex items-center justify-center px-6 relative overflow-hidden">
        
        <!-- Decorative Background -->
        <div class="absolute inset-0 flex items-center justify-center opacity-[0.03] pointer-events-none select-none" aria-hidden="true">
            <span class="text-[20rem] font-bold text-slate-900 tracking-tighter leading-none">404</span>
        </div>

        <div class="text-center relative z-10 max-w-xl mx-auto">
            
            <!-- Icone -->
            <div class="mx-auto w-16 h-16 bg-white border border-slate-200 rounded-2xl shadow-sm flex items-center justify-center mb-8">
                <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>

            <!-- Title -->
            <h1 class="text-4xl md:text-5xl font-bold text-slate-900 tracking-tight mb-4 glitch-text">
                Route Not Found
            </h1>
            
            <!-- Description -->
            <p class="text-lg text-slate-500 mb-8">
                Oops! It looks like this route hasn't been declared in your routes file.
            </p>

            <!-- Bloc Code "Terminal" -->
            <div class="bg-slate-900 rounded-xl shadow-lg p-4 mb-8 text-left font-mono text-sm overflow-x-auto">
                <div class="flex gap-2 mb-3">
                    <span class="w-3 h-3 rounded-full bg-red-500"></span>
                    <span class="w-3 h-3 rounded-full bg-yellow-500"></span>
                    <span class="w-3 h-3 rounded-full bg-green-500"></span>
                    <span class="ml-2 text-slate-500 text-xs">terminal</span>
                </div>
                
                {% verbatim %}
                <code class="text-slate-300">
                    <span class="text-red-400">Error:</span> RouteNotFoundException <br>
                    <span class="text-slate-500">// No route matches the requested URL.</span><br>
                    <span class="text-green-400">$</span> router->match('<span class="text-tonka-400 blink-slow">/current-url</span>');
                </code>
                {% endverbatim %}
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="/" class="inline-flex items-center justify-center gap-2 bg-tonka-500 text-white font-semibold px-6 py-3 rounded-lg shadow-sm hover:bg-tonka-600 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                    Back to homepage
                </a>
                
                <a href="{{ route_doc_url }}" class="inline-flex items-center justify-center gap-2 bg-white text-slate-700 font-medium px-6 py-3 rounded-lg border border-slate-200 hover:border-slate-300 hover:bg-slate-50 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" dM10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path></svg>
                    See the documentation
                </a>
            </div>

        </div>
    </main>
{% endblock %}

{% block footer %}{% endblock %}