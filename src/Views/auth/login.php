<?php
use App\Utils\Icon;
?>
<!DOCTYPE html>
<html lang="fr" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Connexion — Gestion Pédagogique</title>
  <link rel="stylesheet" href="/css/style.css">
  <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22currentColor%22 stroke-width=%222%22><path d=%22M22 10v6%22/><path d=%22M6 12.5V16a6 3 0 0 0 12 0v-3.5%22/><path d=%22M21.42 10.922a1 1 0 0 0-.019-1.838L12.83 5.18a2 2 0 0 0-1.66 0L2.6 9.08a1 1 0 0 0 0 1.832l8.57 3.908a2 2 0 0 0 1.66 0z%22/></svg>">
</head>
<body style="min-height: 100vh; display: flex; flex-direction: column; justify-content: center; align-items: center; padding: 1.5rem;">

  <!-- Theme Toggle in Corner (ICON-ONLY) -->
  <div style="position: absolute; top: 1.5rem; right: 1.5rem;">
    <button id="themeToggle" class="btn btn-icon" title="Basculer Mode Clair/Sombre" aria-label="Basculer Thème">
      <span id="themeIcon"><?= Icon::get('sun', 16) ?></span>
    </button>
  </div>

  <div style="width: 100%; max-width: 440px;">
    <!-- Brand Title -->
    <div style="text-align: center; margin-bottom: 2rem;">
      <div style="display: inline-flex; align-items: center; justify-content: center; width: 48px; height: 48px; border-radius: var(--radius-lg); background: var(--bg-surface); border: 1px solid var(--border); margin-bottom: 1rem; box-shadow: var(--shadow-card); color: var(--text-main);">
        <?= Icon::get('graduation-cap', 26) ?>
      </div>
      <h1 style="font-size: 1.5rem; font-weight: 700; letter-spacing: -0.025em; color: var(--text-main); margin-bottom: 0.25rem;">
        Gestion Pédagogique
      </h1>
      <p style="font-size: 0.875rem; color: var(--text-muted);">
        Département Mathématiques & Informatique
      </p>
    </div>

    <!-- Login Card -->
    <div class="card" style="padding: 2rem;">
      <div style="margin-bottom: 1.5rem;">
        <h2 style="font-size: 1.125rem; font-weight: 600; color: var(--text-main); margin-bottom: 0.25rem;">
          Authentification
        </h2>
        <p style="font-size: 0.8125rem; color: var(--text-muted);">
          Connectez-vous avec vos identifiants académiques.
        </p>
      </div>

      <!-- Messages Flash -->
      <?php if (!empty($error)): ?>
        <div class="alert alert-destructive" style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1.25rem;">
          <?= Icon::get('alert-triangle', 16) ?>
          <span><?= htmlspecialchars($error) ?></span>
        </div>
      <?php endif; ?>

      <?php if (!empty($success)): ?>
        <div class="alert alert-success" style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1.25rem;">
          <?= Icon::get('check', 16) ?>
          <span><?= htmlspecialchars($success) ?></span>
        </div>
      <?php endif; ?>

      <!-- Login Form -->
      <form action="/login" method="POST" id="loginForm">
        <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

        <div class="form-group" style="margin-bottom: 1.125rem;">
          <label for="email" class="form-label">Adresse Email Académique</label>
          <input 
            type="email" 
            id="email" 
            name="email" 
            class="form-control" 
            placeholder="nom.prenom@departement.ma" 
            required 
            autocomplete="email"
            autofocus
          >
        </div>

        <div class="form-group" style="margin-bottom: 1.5rem;">
          <label for="password" class="form-label">Mot de Passe</label>
          <input 
            type="password" 
            id="password" 
            name="password" 
            class="form-control" 
            placeholder="••••••••••••" 
            required 
            autocomplete="current-password"
          >
        </div>

        <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.65rem 1rem;">
          Se Connecter
        </button>
      </form>

      <!-- Quick Test Accounts -->
      <div style="margin-top: 2rem; border-top: 1px solid var(--border); padding-top: 1.25rem;">
        <div style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-muted); margin-bottom: 0.75rem;">
          Comptes de test pré-remplis
        </div>
        <div style="display: flex; flex-direction: column; gap: 0.5rem;">
          <button type="button" class="btn btn-outline quick-fill-btn" data-email="admin@departement.ma" data-pass="admin123" style="justify-content: space-between; font-size: 0.8125rem; padding: 0.45rem 0.75rem;">
            <span><span class="badge badge-warning" style="margin-right: 0.4rem;">ADMIN</span> Karim Benjelloun</span>
            <code style="color: var(--text-muted); font-size: 0.75rem;">admin123</code>
          </button>
          <button type="button" class="btn btn-outline quick-fill-btn" data-email="h.elbahi@departement.ma" data-pass="teacher123" style="justify-content: space-between; font-size: 0.8125rem; padding: 0.45rem 0.75rem;">
            <span><span class="badge badge-neutral" style="margin-right: 0.4rem;">ENSEIGNANT</span> Pr. Hassan El Bahi</span>
            <code style="color: var(--text-muted); font-size: 0.75rem;">teacher123</code>
          </button>
          <button type="button" class="btn btn-outline quick-fill-btn" data-email="b.elazzam@etudiant.ma" data-pass="student123" style="justify-content: space-between; font-size: 0.8125rem; padding: 0.45rem 0.75rem;">
            <span><span class="badge badge-neutral" style="margin-right: 0.4rem;">ÉTUDIANT</span> Bilal EL AZZAM</span>
            <code style="color: var(--text-muted); font-size: 0.75rem;">student123</code>
          </button>
        </div>
      </div>
    </div>

    <!-- Security notice -->
    <div style="text-align: center; margin-top: 1.5rem; font-size: 0.75rem; color: var(--text-subtle);">
      Application académique sécurisée • Session protégée par jeton CSRF & Cookie HTTP-Only
    </div>
  </div>

  <script src="/js/app.js"></script>
  <script>
    document.querySelectorAll('.quick-fill-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        document.getElementById('email').value = btn.getAttribute('data-email');
        document.getElementById('password').value = btn.getAttribute('data-pass');
      });
    });
  </script>
</body>
</html>
