<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tonka Framework - {% block title %}{% endblock %}</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    
    {% block head %}
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
    {% endblock %}
</head>
<body class="bg-slate-50 text-slate-800 font-sans antialiased selection:bg-tonka-200 selection:text-tonka-900">

  	{% set doc_url = 'https://clicalmani.github.io/tonka' %}
	{% set github_url = 'https://github.com/clicalmani/tonka' %}
	{% set license_url = 'https://opensource.org/license/mit' %}
	{% set route_doc_url = 'https://clicalmani.github.io/tonka/#/routing' %}
	{% set controller_doc_url = 'https://clicalmani.github.io/tonka/#/controllers' %}
	{% set twig_url = 'https://twig.symfony.com/' %}

	<!-- =========================================
			HEADER / STATUS BAR
			========================================= -->
    {% block header %}
	<header class="bg-white/70 backdrop-blur-md border-b border-slate-200 sticky top-0 z-50">
		<div class="max-w-6xl mx-auto px-6 py-4 flex justify-between items-center">
			<!-- Logo -->
			<div class="flex items-center gap-3">
				<img src="/logo.svg" alt="Logo" class="w-8 h-8">
				<span class="text-xl font-semibold tracking-tight text-slate-900">Tonka</span>
				<span class="text-xs font-mono bg-slate-100 text-slate-500 px-2 py-1 rounded hidden sm:inline">v2.0</span>
			</div>

			<!-- Server Status -->
			{% block header_status %}
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-2 bg-green-50 border border-green-200 text-green-700 text-xs font-medium px-3 py-1.5 rounded-full">
                        <span class="w-2 h-2 bg-green-500 rounded-full pulse-dot"></span>
                        Server Active
                    </div>
                    <a href="{{ doc_url }}" class="text-sm font-medium text-slate-600 hover:text-tonka-600 transition-colors hidden md:block">Documentation</a>
                </div>
            {% endblock header_status %}
		</div>
	</header>
    {% endblock header %}

    <!-- =========================================
         MAIN CONTENT
         ========================================= -->
    {% block content %}
    {% endblock %}

    <!-- =========================================
         FOOTER
         ========================================= -->
    {% block footer %}
    <footer class="bg-white border-t border-slate-200 py-10">
        <div class="max-w-6xl mx-auto px-6 flex flex-col md:flex-row justify-between items-center gap-6">
            <div class="flex items-center gap-2 text-slate-500">
                <img src="/logo.svg" alt="Logo" class="w-8 h-8">
                <span class="text-sm font-medium">Tonka Framework</span>
            </div>

            <div class="flex items-center gap-6 text-sm text-slate-500">
                <a href="{{ doc_url }}" class="hover:text-slate-900 transition-colors">Documentation</a>
                <a href="{{ github_url }}" class="hover:text-slate-900 transition-colors">GitHub</a>
                <a href="{{ license_url }}" class="hover:text-slate-900 transition-colors">License MIT</a>
            </div>
        </div>
    </footer>
    {% endblock %}

    {% block script %}
    {% endblock %}

    {% block footer2 %}
        <footer class="py-6 text-center text-sm text-slate-400 border-t border-slate-100 bg-white">
            Tonka Framework &copy; 2023 - Made with passion.
        </footer>
    {% endblock %}

</body>
</html>