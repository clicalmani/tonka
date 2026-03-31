{% extends "base" %}

{% block title %}401 Unauthorized Access{% endblock %}

{% block head %}
    {{ parent() }}
    <style>
        body {
            background-color: #f8fafc; /* slate-50 */
        }
        /* Lock Animation */
        @keyframes lock-pulse {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.05); opacity: 0.8; }
        }
        .lock-animate {
            animation: lock-pulse 2s ease-in-out infinite;
        }
    </style>
{% endblock %}

{% block header_status %}{% endblock header_status %}

{% block content %}
    <main class="flex-grow flex items-center justify-center px-6 relative overflow-hidden">
        
        <!-- Decorative Background -->
        <div class="absolute inset-0 flex items-center justify-center opacity-[0.03] pointer-events-none select-none" aria-hidden="true">
            <span class="text-[20rem] font-bold text-red-600 tracking-tighter leading-none">401</span>
        </div>

        <div class="text-center relative z-10 max-w-xl mx-auto">
            
            <!-- Padlock Icon -->
            <div class="mx-auto w-20 h-20 bg-red-50 border border-red-100 rounded-2xl flex items-center justify-center mb-8 lock-animate">
                <svg class="w-10 h-10 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                </svg>
            </div>

            <!-- Title -->
            <h1 class="text-4xl md:text-5xl font-bold text-slate-900 tracking-tight mb-4">
                Unauthorized Access
            </h1>
            
            <!-- Description -->
            <p class="text-lg text-slate-500 mb-8">
                This resource is protected. You must be authenticated to access it.
            </p>

            <!-- Bloc Code "Terminal" -->
            <div class="bg-slate-900 rounded-xl shadow-lg p-4 mb-8 text-left font-mono text-sm overflow-x-auto border border-red-900/30">
                <div class="flex gap-2 mb-3">
                    <span class="w-3 h-3 rounded-full bg-red-500"></span>
                    <span class="w-3 h-3 rounded-full bg-yellow-500"></span>
                    <span class="w-3 h-3 rounded-full bg-green-500"></span>
                    <span class="ml-2 text-red-400 text-xs font-semibold">AUTH MIDDLEWARE</span>
                </div>
                
                {% verbatim %}
                <code class="text-slate-300">
                    <span class="text-red-400">AuthenticationException:</span> Unauthenticated. <br>
                    <span class="text-slate-500">// Please check your session or API token.</span><br>
                    <span class="text-slate-500">// Hint: Redirecting to</span> <span class="text-blue-400">/login</span>
                </code>
                {% endverbatim %}
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                
                <!-- Principal: Log In -->
                <a href="/login" class="inline-flex items-center justify-center gap-2 bg-blue-500 text-white font-semibold px-6 py-3 rounded-lg shadow-sm hover:bg-tonka-600 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
                    Log In
                </a>
                
                <!-- Secondary: Home -->
                <a href="/" class="inline-flex items-center justify-center gap-2 bg-white text-slate-700 font-medium px-6 py-3 rounded-lg border border-slate-200 hover:border-slate-300 hover:bg-slate-50 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                    Back to Home
                </a>

            </div>

        </div>
    </main>
{% endblock %}

{% block footer %}{% endblock %}