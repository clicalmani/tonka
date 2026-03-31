{% extends "base" %}

{% block title %}403 Forbiden Access{% endblock %}

{% block head %}
    {{ parent() }}
    <style>
        body {
            background-color: #f8fafc; /* slate-50 */
        }
        /* Shake animation to simulate entry refusal */
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-2px); }
            20%, 40%, 60%, 80% { transform: translateX(2px); }
        }
        .shake-animate {
            animation: shake 0.6s ease-in-out;
        }
    </style>
{% endblock %}

{% block header_status %}{% endblock %}

{% block content %}
    <main class="flex-grow flex items-center justify-center px-6 relative overflow-hidden">
        
        <!-- Decorative Background -->
        <div class="absolute inset-0 flex items-center justify-center opacity-[0.03] pointer-events-none select-none" aria-hidden="true">
            <span class="text-[18rem] font-bold text-orange-600 tracking-tighter leading-none">403</span>
        </div>

        <div class="text-center relative z-10 max-w-xl mx-auto">
            
            <!-- Icone "Stop" -->
            <div class="mx-auto w-20 h-20 bg-orange-50 border border-orange-100 rounded-full flex items-center justify-center mb-8 shake-animate">
                <svg class="w-10 h-10 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                </svg>
            </div>

            <!-- Title -->
            <h1 class="text-4xl md:text-5xl font-bold text-slate-900 tracking-tight mb-4">
                Forbiden Access
            </h1>
            
            <!-- Description -->
            <p class="text-lg text-slate-500 mb-8">
                You do not have the necessary permissions to access this resource.
            </p>

            <!-- Bloc Code "Terminal" -->
            <div class="bg-slate-900 rounded-xl shadow-lg p-4 mb-8 text-left font-mono text-sm overflow-x-auto border border-orange-900/30">
                <div class="flex gap-2 mb-3">
                    <span class="w-3 h-3 rounded-full bg-red-500"></span>
                    <span class="w-3 h-3 rounded-full bg-yellow-500"></span>
                    <span class="w-3 h-3 rounded-full bg-green-500"></span>
                    <span class="ml-2 text-orange-400 text-xs font-semibold">SECURITY Token</span>
                </div>
                
                {% verbatim %}
                <code class="text-slate-300">
                    <span class="text-orange-400">AccessDeniedException:</span> Forbidden. <br>
                    <span class="text-slate-500">// The token denied access for the "EDIT" attribute..</span><br>
                    <span class="text-red-400">FAIL:</span> <span class="text-slate-300">User.roles</span> <span class="text-blue-400">!==</span> <span class="text-green-400">'ROLE_ADMIN'</span>
                </code>
                {% endverbatim %}
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                
                <!-- Principal : Go Back -->
                <a href="/" class="inline-flex items-center justify-center gap-2 bg-blue-500 text-white font-semibold px-6 py-3 rounded-lg shadow-sm hover:bg-tonka-600 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12"></path></svg>
                    Go Back
                </a>
                
                <!-- Secondaire : Dashboard -->
                <a href="/dashboard" class="inline-flex items-center justify-center gap-2 bg-white text-slate-700 font-medium px-6 py-3 rounded-lg border border-slate-200 hover:border-slate-300 hover:bg-slate-50 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                    My Dashboard
                </a>

            </div>

        </div>
    </main>
{% endblock %}

{% block footer %}{% endblock %}