<?php
declare(strict_types=1);

use App\Utils\Icon;
?>

<div class="content-header" style="margin-bottom: 2rem;">
  <div class="header-titles">
    <h1 class="page-title">Tableau de bord — Direction & Scolarité</h1>
    <p class="page-subtitle">
      Bienvenue, <strong>Pr. <?= htmlspecialchars($user->getNomComplet()) ?></strong>. Vue d'ensemble et gestion du département.
    </p>
  </div>
  <div class="header-actions">
    <div class="badge badge-neutral" style="font-family: ui-monospace, monospace; padding: 0.45rem 0.75rem;">
      <?= Icon::get('calendar', 14) ?>
      <span>Année Académique 2026-2027</span>
    </div>
  </div>
</div>

<!-- Métriques Clés du Département (4 Cartes Professionnelles) -->
<div class="grid grid-cols-4" style="gap: 1.25rem; margin-bottom: 2rem;">
  <!-- Utilisateurs & Équipe -->
  <div class="card stat-card">
    <div class="stat-header">
      <span class="stat-label">Comptes Utilisateurs</span>
      <span class="stat-icon"><?= Icon::get('users', 16) ?></span>
    </div>
    <div class="stat-value"><?= (int)($stats['utilisateurs'] ?? 0) ?></div>
    <div class="stat-meta">
      <strong><?= (int)($stats['enseignants'] ?? 0) ?></strong> professeurs • <strong><?= (int)($stats['etudiants'] ?? 0) ?></strong> étudiants
    </div>
  </div>

  <!-- Offre de Formation -->
  <div class="card stat-card">
    <div class="stat-header">
      <span class="stat-label">Filières Accréditées</span>
      <span class="stat-icon"><?= Icon::get('landmark', 16) ?></span>
    </div>
    <div class="stat-value"><?= (int)($stats['filieres'] ?? 0) ?></div>
    <div class="stat-meta">Cursus Licence & Master</div>
  </div>

  <!-- Modules Pédagogiques -->
  <div class="card stat-card">
    <div class="stat-header">
      <span class="stat-label">Modules d'Enseignement</span>
      <span class="stat-icon"><?= Icon::get('book-open', 16) ?></span>
    </div>
    <div class="stat-value"><?= (int)($stats['modules'] ?? 0) ?></div>
    <div class="stat-meta">Unités d'enseignement actives</div>
  </div>

  <!-- Inscriptions / Réclamations -->
  <div class="card stat-card">
    <div class="stat-header">
      <span class="stat-label">Inscriptions Actives</span>
      <span class="stat-icon"><?= Icon::get('clipboard-list', 16) ?></span>
    </div>
    <div class="stat-value"><?= (int)($stats['inscriptions'] ?? 0) ?></div>
    <div class="stat-meta" style="<?= ((int)($stats['reclamations'] ?? 0) > 0) ? 'color: var(--color-warning);' : '' ?>">
      <?= (int)($stats['reclamations'] ?? 0) ?> réclamation(s) en attente
    </div>
  </div>
</div>

<!-- Section 2 Colonnes : Actions Rapides & Dernières Inscriptions -->
<div class="grid grid-cols-2" style="gap: 1.5rem; margin-bottom: 2rem;">
  <!-- Raccourcis de Gestion -->
  <div class="card">
    <div class="card-header">
      <span class="card-title">
        <?= Icon::get('layout-dashboard', 18) ?>
        <span>Actions & Raccourcis de Gestion</span>
      </span>
      <span class="badge badge-neutral">Scolarité</span>
    </div>
    <div style="display: flex; flex-direction: column; gap: 0.75rem;">
      <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.85rem 1rem; border: 1px solid var(--border); border-radius: var(--radius-md); background: var(--bg-hover);">
        <div>
          <div style="font-weight: 600; font-size: 0.875rem; color: var(--text-main);">Filières & Cursus</div>
          <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.15rem;">Paramétrage des filières, départements et accréditations</div>
        </div>
        <a href="/admin/filieres" class="btn btn-outline btn-sm">Gérer les Filières</a>
      </div>

      <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.85rem 1rem; border: 1px solid var(--border); border-radius: var(--radius-md); background: var(--bg-hover);">
        <div>
          <div style="font-weight: 600; font-size: 0.875rem; color: var(--text-main);">Modules & Enseignants</div>
          <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.15rem;">Attribution et affectation des modules aux professeurs</div>
        </div>
        <a href="/admin/modules" class="btn btn-outline btn-sm">Gérer les Modules</a>
      </div>

      <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.85rem 1rem; border: 1px solid var(--border); border-radius: var(--radius-md); background: var(--bg-hover);">
        <div>
          <div style="font-weight: 600; font-size: 0.875rem; color: var(--text-main);">Inscriptions & Effectifs</div>
          <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.15rem;">Enrôlement des étudiants pour l'année 2026-2027</div>
        </div>
        <a href="/admin/inscriptions" class="btn btn-primary btn-sm">Inscrire un Étudiant</a>
      </div>

      <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.85rem 1rem; border: 1px solid var(--border); border-radius: var(--radius-md); background: var(--bg-hover);">
        <div>
          <div style="font-weight: 600; font-size: 0.875rem; color: var(--text-main);">Journal d'Audit des Notes</div>
          <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.15rem;">Contrôle réglementaire et traçabilité des modifications</div>
        </div>
        <a href="/admin/audit" class="btn btn-outline btn-sm">Consulter l'Audit</a>
      </div>

      <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.85rem 1rem; border: 1px solid var(--border); border-radius: var(--radius-md); background: var(--bg-hover);">
        <div>
          <div style="font-weight: 600; font-size: 0.875rem; color: var(--text-main);">Réclamations Étudiants</div>
          <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.15rem;">Traitement des demandes et contestations des étudiants</div>
        </div>
        <a href="/admin/reclamations" class="btn btn-outline btn-sm">Voir les Réclamations</a>
      </div>
    </div>
  </div>

  <!-- Dernières Inscriptions Administratives -->
  <div class="card" style="display: flex; flex-direction: column;">
    <div class="card-header">
      <div>
        <span class="card-title">
          <?= Icon::get('users', 18) ?>
          <span>Dernières Inscriptions Administratives</span>
        </span>
        <span class="card-desc" style="display: block; margin-top: 0.25rem;">
          Étudiants récemment affectés aux filières
        </span>
      </div>
      <a href="/admin/inscriptions" class="btn btn-ghost btn-sm">Toutes</a>
    </div>

    <?php if (empty($recentInscriptions)): ?>
      <div style="padding: 2.5rem; text-align: center; color: var(--text-muted); font-size: 0.875rem;">
        Aucune inscription enregistrée récemment.
      </div>
    <?php else: ?>
      <div class="table-wrapper" style="border: none; border-radius: 0; margin-bottom: 0;">
        <table>
          <thead>
            <tr>
              <th>Étudiant</th>
              <th>Filière</th>
              <th>Session</th>
              <th style="text-align: right;">Statut</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($recentInscriptions as $ri): ?>
              <tr>
                <td>
                  <span style="font-weight: 600; color: var(--text-main); display: block;">
                    <?= htmlspecialchars($ri['prenom'] . ' ' . $ri['nom']) ?>
                  </span>
                </td>
                <td>
                  <span class="badge badge-neutral"><?= htmlspecialchars($ri['filiere_code']) ?></span>
                </td>
                <td style="font-family: ui-monospace, monospace; font-size: 0.8125rem; color: var(--text-muted);">
                  <?= htmlspecialchars($ri['anneeUniversitaire']) ?>
                </td>
                <td style="text-align: right;">
                  <?php if ($ri['statut'] === 'EN_COURS'): ?>
                    <span class="badge badge-success">En règle</span>
                  <?php else: ?>
                    <span class="badge badge-neutral"><?= htmlspecialchars($ri['statut']) ?></span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>
