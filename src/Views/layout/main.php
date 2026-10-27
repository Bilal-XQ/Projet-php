<?php
declare(strict_types=1);

use App\Utils\Auth;
use App\Utils\Icon;

$currentUser = Auth::user();
$currentRole = Auth::role();
$activeNav = $activeNav ?? 'dashboard';

$flashSuccess = $_SESSION['flash_success'] ?? null;
unset($_SESSION['flash_success']);

$flashError = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_error']);
?>
<!DOCTYPE html>
<html lang="fr" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($title ?? 'Tableau de bord') ?> — Gestion Pédagogique</title>
  <link rel="stylesheet" href="/css/style.css">
  <script src="/js/chart.min.js"></script>
  <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22currentColor%22 stroke-width=%222%22><path d=%22M22 10v6%22/><path d=%22M6 12.5V16a6 3 0 0 0 12 0v-3.5%22/><path d=%22M21.42 10.922a1 1 0 0 0-.019-1.838L12.83 5.18a2 2 0 0 0-1.66 0L2.6 9.08a1 1 0 0 0 0 1.832l8.57 3.908a2 2 0 0 0 1.66 0z%22/></svg>">
</head>
<body class="app-shell">

  <!-- Mobile Sidebar Backdrop Overlay -->
  <div id="sidebarBackdrop" class="sidebar-backdrop"></div>

  <!-- Sidebar Component -->
  <aside id="sidebar" class="sidebar">
    <!-- Sidebar Header / Logo -->
    <div class="sidebar-header">
      <a href="/" class="sidebar-brand">
        <span class="brand-icon"><?= Icon::get('graduation-cap', 22) ?></span>
        <div class="brand-text">
          <span class="brand-name">Math-Info</span>
          <span class="brand-sub">Gestion Pédagogique</span>
        </div>
      </a>
    </div>

    <!-- Navigation Menu -->
    <nav class="sidebar-nav">
      <div class="nav-section-title">Menu Principal</div>

      <?php if ($currentRole === 'ADMIN'): ?>
        <a href="/admin/dashboard" class="nav-item <?= $activeNav === 'dashboard' ? 'active' : '' ?>">
          <span class="nav-icon"><?= Icon::get('layout-dashboard', 18) ?></span>
          <span class="nav-label">Tableau de bord</span>
        </a>
        <a href="/admin/filieres" class="nav-item <?= $activeNav === 'filieres' ? 'active' : '' ?>">
          <span class="nav-icon"><?= Icon::get('landmark', 18) ?></span>
          <span class="nav-label">Filières</span>
        </a>
        <a href="/admin/modules" class="nav-item <?= $activeNav === 'modules' ? 'active' : '' ?>">
          <span class="nav-icon"><?= Icon::get('book-open', 18) ?></span>
          <span class="nav-label">Modules</span>
        </a>
        <a href="/admin/utilisateurs" class="nav-item <?= $activeNav === 'utilisateurs' ? 'active' : '' ?>">
          <span class="nav-icon"><?= Icon::get('users', 18) ?></span>
          <span class="nav-label">Utilisateurs</span>
        </a>
        <a href="/admin/inscriptions" class="nav-item <?= $activeNav === 'inscriptions' ? 'active' : '' ?>">
          <span class="nav-icon"><?= Icon::get('clipboard-list', 18) ?></span>
          <span class="nav-label">Inscriptions</span>
        </a>
        <a href="/admin/annonces" class="nav-item <?= $activeNav === 'annonces' ? 'active' : '' ?>">
          <span class="nav-icon"><?= Icon::get('megaphone', 18) ?></span>
          <span class="nav-label">Annonces</span>
        </a>
        <a href="/admin/audit" class="nav-item <?= $activeNav === 'audit' ? 'active' : '' ?>">
          <span class="nav-icon"><?= Icon::get('shield-check', 18) ?></span>
          <span class="nav-label">Journal d'Audit</span>
        </a>

      <?php elseif ($currentRole === 'ENSEIGNANT'): ?>
        <a href="/enseignant/dashboard" class="nav-item <?= $activeNav === 'dashboard' ? 'active' : '' ?>">
          <span class="nav-icon"><?= Icon::get('layout-dashboard', 18) ?></span>
          <span class="nav-label">Tableau de bord</span>
        </a>
        <a href="/enseignant/modules" class="nav-item <?= $activeNav === 'modules' ? 'active' : '' ?>">
          <span class="nav-icon"><?= Icon::get('book-open', 18) ?></span>
          <span class="nav-label">Mes Modules</span>
        </a>
        <a href="/enseignant/notes" class="nav-item <?= $activeNav === 'notes' ? 'active' : '' ?>">
          <span class="nav-icon"><?= Icon::get('pencil', 18) ?></span>
          <span class="nav-label">Saisie des Notes</span>
        </a>
        <a href="/enseignant/seances" class="nav-item <?= $activeNav === 'seances' ? 'active' : '' ?>">
          <span class="nav-icon"><?= Icon::get('clock', 18) ?></span>
          <span class="nav-label">Feuilles d'Assiduité</span>
        </a>
        <a href="/enseignant/annonces" class="nav-item <?= $activeNav === 'annonces' ? 'active' : '' ?>">
          <span class="nav-icon"><?= Icon::get('megaphone', 18) ?></span>
          <span class="nav-label">Annonces</span>
        </a>

      <?php elseif ($currentRole === 'ETUDIANT'): ?>
        <a href="/etudiant/dashboard" class="nav-item <?= $activeNav === 'dashboard' ? 'active' : '' ?>">
          <span class="nav-icon"><?= Icon::get('layout-dashboard', 18) ?></span>
          <span class="nav-label">Mon Cursus</span>
        </a>
        <a href="/etudiant/notes" class="nav-item <?= $activeNav === 'notes' ? 'active' : '' ?>">
          <span class="nav-icon"><?= Icon::get('file-text', 18) ?></span>
          <span class="nav-label">Mes Notes & Résultats</span>
        </a>
        <a href="/etudiant/absences" class="nav-item <?= $activeNav === 'absences' ? 'active' : '' ?>">
          <span class="nav-icon"><?= Icon::get('clock', 18) ?></span>
          <span class="nav-label">Bilan d'Assiduité</span>
        </a>
        <a href="/etudiant/annonces" class="nav-item <?= $activeNav === 'annonces' ? 'active' : '' ?>">
          <span class="nav-icon"><?= Icon::get('megaphone', 18) ?></span>
          <span class="nav-label">Avis & Annonces</span>
        </a>
      <?php endif; ?>
    </nav>

    <!-- User Profile Footer in Sidebar -->
    <div class="sidebar-footer">
      <div class="user-profile-widget">
        <div class="user-avatar">
          <?= strtoupper(substr($currentUser ? $currentUser->getPrenom() : 'U', 0, 1) . substr($currentUser ? $currentUser->getNom() : 'S', 0, 1)) ?>
        </div>
        <div class="user-meta">
          <span class="user-name"><?= htmlspecialchars($currentUser ? $currentUser->getNomComplet() : 'Utilisateur') ?></span>
          <span class="user-role-badge badge badge-neutral"><?= htmlspecialchars($currentRole ?? '') ?></span>
        </div>
      </div>
    </div>
  </aside>

  <!-- Main Content Layout Area -->
  <div class="main-wrapper">
    <!-- Topbar Header -->
    <header class="app-topbar">
      <div class="topbar-left">
        <button id="sidebarToggle" class="btn btn-icon sidebar-toggle-btn-topbar" title="Basculer le menu" aria-label="Menu">
          <?= Icon::get('menu', 18) ?>
        </button>
        <div class="breadcrumb">
          <span class="breadcrumb-root">Math-Info</span>
          <span class="breadcrumb-sep">/</span>
          <span class="breadcrumb-current"><?= htmlspecialchars($title ?? 'Dashboard') ?></span>
        </div>
      </div>

      <div class="topbar-right">
        <!-- Academic Year Badge with Calendar SVG Icon -->
        <div class="badge badge-neutral" title="Année Universitaire en cours">
          <?= Icon::get('calendar', 14) ?>
          <span>2026-2027</span>
        </div>

        <!-- Role Badge -->
        <?php if ($currentRole === 'ADMIN'): ?>
          <span class="badge badge-warning">ADMIN</span>
        <?php elseif ($currentRole === 'ENSEIGNANT'): ?>
          <span class="badge badge-neutral">ENSEIGNANT</span>
        <?php else: ?>
          <span class="badge badge-neutral">ÉTUDIANT</span>
        <?php endif; ?>

        <!-- Theme Switcher Button (ICON-ONLY) -->
        <button id="themeToggle" class="btn btn-icon" title="Basculer Mode Clair/Sombre" aria-label="Basculer Thème">
          <span id="themeIcon"><?= Icon::get('sun', 16) ?></span>
        </button>

        <!-- Logout Button (Destructive Red) -->
        <a href="/logout" class="btn btn-destructive" title="Déconnexion">
          <?= Icon::get('log-out', 14) ?>
          <span class="desktop-only">Déconnexion</span>
        </a>
      </div>
    </header>

    <!-- Injected Page Content -->
    <main class="page-content">
      <div class="content-container">
        <!-- Flash Message Alerts -->
        <?php if (!empty($flashSuccess)): ?>
          <div class="alert alert-success" style="display: flex; align-items: center; gap: 0.5rem;">
            <?= Icon::get('check', 16) ?>
            <span><strong>Succès :</strong> <?= htmlspecialchars($flashSuccess) ?></span>
          </div>
        <?php endif; ?>

        <?php if (!empty($flashError)): ?>
          <div class="alert alert-destructive" style="display: flex; align-items: center; gap: 0.5rem;">
            <?= Icon::get('alert-triangle', 16) ?>
            <span><strong>Erreur :</strong> <?= htmlspecialchars($flashError) ?></span>
          </div>
        <?php endif; ?>

        <?= $content ?>
      </div>
    </main>
  </div>

  <script src="/js/app.js"></script>
</body>
</html>
