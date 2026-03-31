{% extends "base" %}

{% block title %}500 Internal Server Error{% endblock %}

{% block head %}
    {{ parent() }}
    <style>
        body {
            background-color: #f8fafc; /* slate-50 */
        }
        
        /* Glitch effect on the title */
        .glitch {
            position: relative;
            animation: glitch-skew 1s infinite linear alternate-reverse;
        }
        .glitch::before,
        .glitch::after {
            content: attr(data-text);
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }
        .glitch::before {
            left: 2px;
            text-shadow: -2px 0 #ef4444; /* Rouge */
            clip: rect(44px, 450px, 56px, 0);
            animation: glitch-anim 5s infinite linear alternate-reverse;
        }
        .glitch::after {
            left: -2px;
            text-shadow: -2px 0 #3b82f6; /* Bleu */
            clip: rect(44px, 450px, 56px, 0);
            animation: glitch-anim2 5s infinite linear alternate-reverse;
        }

        @keyframes glitch-anim {
            0% { clip: rect(31px, 9999px, 94px, 0); transform: skew(0.15deg); }
            25% { clip: rect(62px, 9999px, 34px, 0); transform: skew(0.40deg); }
            50% { clip: rect(16px, 9999px, 82px, 0); transform: skew(0.10deg); }
            75% { clip: rect(54px, 9999px, 49px, 0); transform: skew(0.55deg); }
            100% { clip: rect(78px, 9999px, 11px, 0); transform: skew(0.25deg); }
        }
        @keyframes glitch-anim2 {
            0% { clip: rect(65px, 9999px, 22px, 0); transform: skew(0.20deg); }
            25% { clip: rect(12px, 9999px, 88px, 0); transform: skew(0.35deg); }
            50% { clip: rect(47px, 9999px, 63px, 0); transform: skew(0.15deg); }
            75% { clip: rect(89px, 9999px, 10px, 0); transform: skew(0.60deg); }
            100% { clip: rect(33px, 9999px, 74px, 0); transform: skew(0.05deg); }
        }
        @keyframes glitch-skew {
            0% { transform: skew(1deg); }
            10% { transform: skew(-2deg); }
            20% { transform: skew(1deg); }
            30% { transform: skew(0deg); }
            100% { transform: skew(0deg); }
        }

        /* Pulsation effect on the exclamation point */
        @keyframes pulse-ring {
            0% { transform: scale(0.9); opacity: 0.7; }
            50% { transform: scale(1.1); opacity: 0; }
            100% { transform: scale(0.9); opacity: 0; }
        }
        .pulse-error::before {
            content: '';
            position: absolute;
            inset: -8px;
            border-radius: 50%;
            border: 2px solid #ef4444;
            animation: pulse-ring 1.5s ease-out infinite;
        }
    </style>
{% endblock %}

{% block header_status %}{% endblock %}

{% block content %}
    <main class="flex-grow flex items-center justify-center px-6 relative">
        
        <!-- Decorative Background -->
        <div class="absolute inset-0 flex items-center justify-center opacity-[0.03] pointer-events-none select-none" aria-hidden="true">
            <span class="text-[20rem] font-bold text-red-600 tracking-tighter leading-none">500</span>
        </div>

        <div class="text-center w-full relative z-10">
            
            <!-- Error Icone -->
            <div class="mx-auto w-20 h-20 bg-red-50 border border-red-100 rounded-full flex items-center justify-center mb-8 relative pulse-error">
                <svg class="w-10 h-10 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
            </div>

            <!-- Title -->
            <h1 class="text-4xl md:text-5xl font-bold text-slate-900 tracking-tight mb-4 glitch" data-text="Internal Server Error">
                Internal Server Error
            </h1>
            
            <!-- Description -->
            <p class="text-lg text-slate-500 mb-8">
                The server encountered an unexpected error and was unable to complete your request.
            </p>

            <div class="flex max-w-6xl mx-auto gap-4 mb-10">
                <!-- Section Stack Trace -->
                <div class="space-y-2 w-1/2">
                    <div class="px-4 py-3 bg-white/50 border-b border-slate-200 rounded-t-xl flex items-center justify-between mb-2">
                        <h3 class="text-sm font-semibold text-slate-700 flex items-center gap-2">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path>
                            </svg>
                            Stack Trace
                        </h3>
                    </div>
                    {% for frame in error.trace %}
                        <details class="bg-white border border-slate-200 rounded-lg group" {% if loop.index == 0 %}open{% endif %}>
                            <summary class="px-4 py-3 cursor-pointer hover:bg-slate-50 flex flex-col items-start font-mono text-sm">
                                
                                <!-- Clickable Link -->
                                <span class="text-slate-600 flex flex-col items-start {% if frame.editorLink %}hover:text-blue-600{% endif %}">
                                    <span class="text-slate-400">#{{ frame.id }}</span> 
                                    
                                    {% if frame.editorLink %}
                                        <a href="{{ frame.editorLink }}" onclick="event.stopPropagation()" title="Ouvrir dans l'éditeur">
                                            <span class="text-blue-600 break-all hover:underline">{{ frame.file }}</span>:<span class="text-green-600">{{ frame.line }}</span>
                                        </a>
                                    {% else %}
                                        <span class="text-blue-600">{{ frame.file }}</span>:<span class="text-green-600">{{ frame.line }}</span>
                                    {% endif %}
                                </span>
                                
                                <span class="text-purple-500 text-xs hidden sm:inline">
                                    {{ frame.class ~ frame.type ~ frame.func }}()
                                </span>
                            </summary>
                            
                            <!-- Snippet -->
                        </details>
                    {% endfor %}
                </div>

                <div class="flex flex-col w-1/2">
                    <!-- Code Block -->
                    <div class="bg-slate-900 rounded-xl shadow-lg p-4 mb-8 text-left font-mono text-sm overflow-x-auto border-t-4 border-red-500 mb-5">
                        
                        <!-- Editor Header -->
                        <div class="flex items-center justify-between mb-4 border-b border-slate-700 pb-2">
                            <div class="flex gap-2">
                                <span class="w-3 h-3 rounded-full bg-red-500"></span>
                                <span class="w-3 h-3 rounded-full bg-yellow-500"></span>
                                <span class="w-3 h-3 rounded-full bg-green-500"></span>
                            </div>
                            
                            {% if editorLink %}
                                <a href="{{ editorLink }}" class="text-xs text-blue-400 hover:text-blue-300 hover:underline transition-colors flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                    {{ error.file }}:{{ error.line }} (Open in VS Code)
                                </a>
                            {% else %}
                                <span class="text-slate-400 text-xs">{{ error.file }}:{{ error.line }}</span>
                            {% endif %}
                        </div>

                        <!-- Editor -->
                        <div class="overflow-x-auto">
                            <table class="w-full border-collapse">
                                <tbody>
                                    {% for lineNum, lineCode in error.snippet %}
                                        <tr class="{% if lineNum == error.line %}bg-red-900/20{% endif %}">
                                            <!-- Numéros de ligne -->
                                            <td class="text-right pr-4 text-slate-500 select-none border-r border-slate-700 w-12 align-top {% if lineNum == error.line %}text-red-400{% endif %}">
                                                {{ lineNum }}
                                            </td>
                                            <td class="pl-4 whitespace-nowrap {% if lineNum == error.line %}font-bold text-white{% else %}text-slate-300{% endif %}">
                                                {% if lineNum == error.line %}
                                                    <span class="text-red-500 mr-2">✗</span> 
                                                {% endif %}
                                                {{ lineCode | raw }}
                                            </td>
                                        </tr>
                                    {% endfor %}
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="mt-4 p-3 bg-red-900/20 border-l-4 border-red-500 text-red-300">
                            <strong>{{ error.title }}:</strong> {{ error.message }}
                        </div>
                    </div>

                    <!-- Request Context -->
                    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden mb-8">
                        
                        <!-- Header -->
                        <div class="px-4 py-3 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                            <h3 class="text-sm font-semibold text-slate-700 flex items-center gap-2">
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path>
                                </svg>
                                Request Context
                            </h3>
                            
                            <!-- Badge HTTP Method -->
                            {% set methodColor = 'bg-gray-100 text-gray-800' %}
                            {% if request.method == 'POST' %}
                                {% set methodColor = 'bg-green-100 text-green-800' %}
                            {% elseif request.method == 'PUT' or request.method == 'PATCH' %}
                                {% set methodColor = 'bg-orange-100 text-orange-800' %}
                            {% elseif request.method == 'DELETE' %}
                                {% set methodColor = 'bg-red-100 text-red-800' %}
                            {% endif %}

                            <span class="px-2.5 py-1 text-xs font-bold rounded {{ methodColor }}">
                                {{ request.method }}
                            </span>
                        </div>

                        <!-- Content -->
                        <div class="p-4 text-sm space-y-3">
                            
                            <!-- URL -->
                            <div class="flex items-center gap-2 font-mono">
                                <span class="text-slate-500 w-16 shrink-0">URL</span>
                                <span class="text-blue-600 bg-blue-50 px-2 py-0.5 rounded break-all">{{ request.url }}</span>
                            </div>

                            <!-- Route Handler -->
                            {% if request.routeName is defined %}
                            <div class="flex items-center gap-2 font-mono">
                                <span class="text-slate-500 w-16 shrink-0">Route</span>
                                <span class="text-purple-600 bg-purple-50 px-2 py-0.5 rounded">{{ request.routeName }}</span>
                            </div>
                            {% endif %}

                            {% if request.handler is defined %}
                            <div class="flex items-center gap-2 font-mono">
                                <span class="text-slate-500 w-16 shrink-0">Handler</span>
                                <span class="text-slate-700">{{ request.handler }}</span>
                            </div>
                            {% endif %}

                            <!-- Query Params -->
                            {% if request.query is defined and request.query is not empty %}
                            <div class="flex items-start gap-2 font-mono">
                                <span class="text-slate-500 w-16 shrink-0 pt-0.5">Query</span>
                                <div class="bg-slate-100 p-2 rounded text-xs overflow-x-auto">
                                    {{ request.query | json_encode(constant('JSON_PRETTY_PRINT')) }}
                                </div>
                            </div>
                            {% endif %}

                        </div>
                    </div>

                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center mb-10">
                
                <!-- Principal -->
                <a href="/" class="inline-flex items-center justify-center gap-2 bg-red-600 text-white font-semibold px-6 py-3 rounded-lg shadow-sm hover:bg-red-700 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    Reload the Page
                </a>
                
                <!-- Secondary -->
                <a href="#" class="inline-flex items-center justify-center gap-2 bg-white text-slate-700 font-medium px-6 py-3 rounded-lg border border-slate-200 hover:border-slate-300 hover:bg-slate-50 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path></svg>
                    View Logs
                </a>

            </div>

        </div>
    </main>
{% endblock %}

{% block footer %}{% endblock %}