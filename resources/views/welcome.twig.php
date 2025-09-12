<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Bienvenue sur Tonka</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <style>
    :root {
      --primary-color: #e50914;
      --secondary-color: #6366f1;
      --bg-light: #f9fafb;
      --bg-dark: #111827;
      --text-light: #111827;
      --text-dark: #f9fafb;
      --muted-light: #6b7280;
      --muted-dark: #9ca3af;
    }

    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
      font-family: 'Inter', sans-serif;
      background: var(--bg-light);
      color: var(--text-light);
      min-height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 2rem;
      transition: background 0.5s, color 0.5s;
      position: relative;
      flex-direction: column;
    }

    body.dark {
      background: var(--bg-dark);
      color: var(--text-dark);
    }

    /* Preloader */
    #preloader {
      position: fixed;
      top: 0; left: 0;
      width: 100%; height: 100%;
      background: #fff;
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 1000;
      transition: opacity 0.6s ease, visibility 0.6s;
    }
    #preloader.dark { background: #111827; }
    #preloader.fade-out { opacity: 0; visibility: hidden; }

    .loader {
      border: 6px solid #ddd;
      border-top: 6px solid var(--primary-color);
      border-radius: 50%;
      width: 60px; height: 60px;
      animation: spin 1.5s linear infinite;
    }

    @keyframes spin { to { transform: rotate(360deg); } }

    /* Card */
    .card {
      background: #fff;
      border-radius: 1rem;
      padding: 3rem 2rem;
      max-width: 650px;
      width: 100%;
      text-align: center;
      box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
      opacity: 0;
      transform: translateY(30px) scale(0.98);
      animation: fadeInUp 1.5s ease forwards;
      animation-delay: 2.5s;
      transition: background 0.5s, color 0.5s;
    }

    body.dark .card { background: #1f2937; color: var(--text-dark); }

    .logo { width: 80px; margin-bottom: 1.5rem; }

    h1 {
      font-size: 2.5rem;
      color: var(--primary-color);
      margin-bottom: 1rem;
      font-weight: 700;
    }

    p {
      font-size: 1.1rem;
      color: var(--muted-light);
      margin-bottom: 2rem;
      line-height: 1.6;
      transition: color 0.5s;
    }
    body.dark p { color: var(--muted-dark); }

    .buttons {
      display: flex;
      justify-content: center;
      gap: 1rem;
      flex-wrap: wrap;
      margin-bottom: 1rem;
    }

    .btn {
      background-color: var(--primary-color);
      color: #fff;
      padding: 0.75rem 1.5rem;
      border-radius: 0.75rem;
      font-size: 1rem;
      font-weight: 600;
      text-decoration: none;
      transition: all 0.3s ease;
      display: inline-block;
    }
    .btn:hover { background-color: #bf0810; transform: translateY(-2px); }

    .btn.secondary { background-color: var(--secondary-color); }
    .btn.secondary:hover { background-color: #4f46e5; }

    /* Switch button (top-right corner) */
    .switch {
      position: absolute;
      top: 20px;
      right: 20px;
      display: inline-block;
      width: 60px;
      height: 30px;
      z-index: 2000;
    }
    .switch input { display: none; }
    .slider {
      position: absolute;
      cursor: pointer;
      top: 0; left: 0;
      right: 0; bottom: 0;
      background-color: #ccc;
      transition: 0.4s;
      border-radius: 30px;
    }
    .slider:before {
      position: absolute;
      content: "";
      height: 22px; width: 22px;
      left: 4px; bottom: 4px;
      background-color: white;
      transition: 0.4s;
      border-radius: 50%;
    }
    input:checked + .slider { background-color: var(--primary-color); }
    input:checked + .slider:before { transform: translateX(30px); }

    /* Footer global */
    footer {
      width: 100%;
      text-align: center;
      padding: 1rem 0;
      font-size: 0.9rem;
      color: var(--muted-light);
      transition: color 0.5s;
      margin-top: auto;
    }
    body.dark footer { color: var(--muted-dark); }
    footer .copyright {
      font-size: 0.85rem;
      opacity: 0.8;
      margin-top: 0.3rem;
    }

    /* Animations */
    @keyframes fadeInUp {
      from { opacity: 0; transform: translateY(40px) scale(0.95); }
      to { opacity: 1; transform: translateY(0) scale(1); }
    }
  </style>
</head>
<body>
  <!-- Preloader -->
  <div id="preloader">
    <div class="loader"></div>
  </div>

  <!-- Switch dark/light en haut à droite -->
  <label class="switch">
    <input type="checkbox" id="themeToggle">
    <span class="slider"></span>
  </label>

  <div class="card">
    <img src="fat-logo.png" alt="Logo Tonka" class="logo">
    <h1>Bienvenue sur Tonka</h1>
    <p>
      Un framework moderne, rapide et élégant pour propulser vos applications PHP 
      avec style et performance.
    </p>

    <div class="buttons">
      <a href="https://clicalmani.github.io/tonka" class="btn">Documentation</a>
      <a href="https://github.com/clicalmani/tonka" class="btn secondary">GitHub</a>
    </div>
    <p>Tonka Framework — PHP v<?= PHP_VERSION ?></p>
  </div>

  <!-- Footer global -->
<footer>
  
  <div class="signature">
    © Créé par Moubarack IDOHOU
  </div>
</footer>

<style>
  footer {
    width: 100%;
    text-align: center;
    padding: 1rem 0;
    font-size: 0.9rem;
    color: var(--muted-light);
    transition: color 0.5s;
    margin-top: auto;
  }
  body.dark footer { color: var(--muted-dark); }

  /* copyright placé un peu plus bas */
  .signature {
    margin-top: 1.2rem;
    font-size: 0.85rem;
    opacity: 0.8;
  }
</style>
<script>
    // Preloader
    window.addEventListener("load", () => {
      setTimeout(() => {
        document.getElementById("preloader").classList.add("fade-out");
      }, 2500);
    });

    // Dark/Light toggle
    const toggle = document.getElementById("themeToggle");
    const body = document.body;
    const preloader = document.getElementById("preloader");

    toggle.addEventListener("change", () => {
      body.classList.toggle("dark", toggle.checked);
      preloader.classList.toggle("dark", toggle.checked);
    });
  </script>
</body>
</html>
