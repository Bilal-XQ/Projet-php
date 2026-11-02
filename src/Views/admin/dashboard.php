<?php
declare(strict_types=1);

use App\Utils\Icon;
?>

<div class="content-header" style="margin-bottom: 2rem;">
  <div class="header-titles">
    <h1 class="page-title">Tableau de bord — Direction & Scolarité</h1>
    <p class="page-subtitle">
      Bienvenue, <strong>Pr. <?= htmlspecialchars($user->getNomComplet()) ?></strong>. Vue d'ensemble et pilotage pédagogique du département.
    </p>
  </div>
  <div class="header-actions">
    <div class="badge badge-neutral" style="font-family: ui-monospace, monospace; padding: 0.5rem 0.85rem; font-size: 0.8125rem;">
      <?= Icon::get('calendar', 14) ?>
      <span>Année Universitaire 2026-2027</span>
    </div>
  </div>
</div>

<!-- Métriques Clés du Département (4 Cartes Modernes) -->
<div class="grid grid-cols-4" style="gap: 1.25rem; margin-bottom: 2rem;">
  <!-- Utilisateurs & Équipe -->
  <div class="card stat-card" style="position: relative; overflow: hidden;">
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
  <div class="card stat-card" style="position: relative; overflow: hidden;">
    <div class="stat-header">
      <span class="stat-label">Filières Accréditées</span>
      <span class="stat-icon"><?= Icon::get('landmark', 16) ?></span>
    </div>
    <div class="stat-value"><?= (int)($stats['filieres'] ?? 0) ?></div>
    <div class="stat-meta">Cursus Licence & Master</div>
  </div>

  <!-- Modules Pédagogiques -->
  <div class="card stat-card" style="position: relative; overflow: hidden;">
    <div class="stat-header">
      <span class="stat-label">Modules d'Enseignement</span>
      <span class="stat-icon"><?= Icon::get('book-open', 16) ?></span>
    </div>
    <div class="stat-value"><?= (int)($stats['modules'] ?? 0) ?></div>
    <div class="stat-meta">Unités d'enseignement actives</div>
  </div>

  <!-- Inscriptions / Réclamations -->
  <div class="card stat-card" style="position: relative; overflow: hidden;">
    <div class="stat-header">
      <span class="stat-label">Inscriptions Actives</span>
      <span class="stat-icon"><?= Icon::get('clipboard-list', 16) ?></span>
    </div>
    <div class="stat-value"><?= (int)($stats['inscriptions'] ?? 0) ?></div>
    <div class="stat-meta" style="<?= ((int)($stats['reclamations'] ?? 0) > 0) ? 'color: var(--color-warning); font-weight: 500;' : '' ?>">
      <?= (int)($stats['reclamations'] ?? 0) ?> réclamation(s) en attente
    </div>
  </div>
</div>

<!-- Section 2 Colonnes : Actions Rapides & Dernières Inscriptions -->
<div class="grid grid-cols-2" style="gap: 1.5rem; margin-bottom: 2rem;">
  
  <!-- Colonne 1 : Actions & Raccourcis de Gestion (Interactive Modern Cards) -->
  <div class="card" style="display: flex; flex-direction: column;">
    <div class="card-header">
      <div style="display: flex; align-items: center; gap: 0.5rem;">
        <?= Icon::get('layout-dashboard', 18) ?>
        <span class="card-title">Actions & Raccourcis de Gestion</span>
      </div>
      <span class="badge badge-neutral">Scolarité</span>
    </div>
    
    <div style="display: flex; flex-direction: column; gap: 0.75rem; flex: 1;">
      
      <!-- Filières -->
      <a href="/admin/filieres" style="text-decoration: none; display: flex; align-items: center; justify-content: space-between; padding: 0.85rem 1rem; border: 1px solid var(--border); border-radius: var(--radius-md); background: var(--bg-surface-hover); transition: transform 0.15s ease, border-color 0.15s ease, background 0.15s ease;" onmouseover="this.style.borderColor='var(--border-focus)'; this.style.transform='translateY(-1px)';" onmouseout="this.style.borderColor='var(--border)'; this.style.transform='none';">
        <div style="display: flex; align-items: center; gap: 0.85rem;">
          <div style="width: 36px; height: 36px; border-radius: var(--radius-md); background: rgba(59, 130, 246, 0.1); color: #3b82f6; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <?= Icon::get('landmark', 18) ?>
          </div>
          <div>
            <div style="font-weight: 600; font-size: 0.875rem; color: var(--text-main);">Filières & Cursus</div>
            <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.15rem;">Paramétrage des filières, départements et accréditations</div>
          </div>
        </div>
        <span class="btn btn-outline btn-sm" style="pointer-events: none;">Gérer</span>
      </a>

      <!-- Modules -->
      <a href="/admin/modules" style="text-decoration: none; display: flex; align-items: center; justify-content: space-between; padding: 0.85rem 1rem; border: 1px solid var(--border); border-radius: var(--radius-md); background: var(--bg-surface-hover); transition: transform 0.15s ease, border-color 0.15s ease, background 0.15s ease;" onmouseover="this.style.borderColor='var(--border-focus)'; this.style.transform='translateY(-1px)';" onmouseout="this.style.borderColor='var(--border)'; this.style.transform='none';">
        <div style="display: flex; align-items: center; gap: 0.85rem;">
          <div style="width: 36px; height: 36px; border-radius: var(--radius-md); background: rgba(16, 185, 129, 0.1); color: #10b981; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <?= Icon::get('book-open', 18) ?>
          </div>
          <div>
            <div style="font-weight: 600; font-size: 0.875rem; color: var(--text-main);">Modules & Enseignants</div>
            <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.15rem;">Attribution et affectation des modules aux professeurs</div>
          </div>
        </div>
        <span class="btn btn-outline btn-sm" style="pointer-events: none;">Gérer</span>
      </a>

      <!-- Inscriptions -->
      <a href="/admin/inscriptions" style="text-decoration: none; display: flex; align-items: center; justify-content: space-between; padding: 0.85rem 1rem; border: 1px solid var(--border); border-radius: var(--radius-md); background: var(--bg-surface-hover); transition: transform 0.15s ease, border-color 0.15s ease, background 0.15s ease;" onmouseover="this.style.borderColor='var(--border-focus)'; this.style.transform='translateY(-1px)';" onmouseout="this.style.borderColor='var(--border)'; this.style.transform='none';">
        <div style="display: flex; align-items: center; gap: 0.85rem;">
          <div style="width: 36px; height: 36px; border-radius: var(--radius-md); background: rgba(245, 158, 11, 0.1); color: #f59e0b; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <?= Icon::get('clipboard-list', 18) ?>
          </div>
          <div>
            <div style="font-weight: 600; font-size: 0.875rem; color: var(--text-main);">Inscriptions & Effectifs</div>
            <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.15rem;">Enrôlement des étudiants pour l'année 2026-2027</div>
          </div>
        </div>
        <span class="btn btn-primary btn-sm" style="pointer-events: none;">Inscrire</span>
      </a>

      <!-- Journal d'Audit -->
      <a href="/admin/audit" style="text-decoration: none; display: flex; align-items: center; justify-content: space-between; padding: 0.85rem 1rem; border: 1px solid var(--border); border-radius: var(--radius-md); background: var(--bg-surface-hover); transition: transform 0.15s ease, border-color 0.15s ease, background 0.15s ease;" onmouseover="this.style.borderColor='var(--border-focus)'; this.style.transform='translateY(-1px)';" onmouseout="this.style.borderColor='var(--border)'; this.style.transform='none';">
        <div style="display: flex; align-items: center; gap: 0.85rem;">
          <div style="width: 36px; height: 36px; border-radius: var(--radius-md); background: rgba(139, 92, 246, 0.1); color: #8b5cf6; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <?= Icon::get('shield-check', 18) ?>
          </div>
          <div>
            <div style="font-weight: 600; font-size: 0.875rem; color: var(--text-main);">Journal d'Audit des Notes</div>
            <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.15rem;">Contrôle réglementaire et traçabilité des modifications</div>
          </div>
        </div>
        <span class="btn btn-outline btn-sm" style="pointer-events: none;">Consulter</span>
      </a>

      <!-- Réclamations -->
      <a href="/admin/reclamations" style="text-decoration: none; display: flex; align-items: center; justify-content: space-between; padding: 0.85rem 1rem; border: 1px solid var(--border); border-radius: var(--radius-md); background: var(--bg-surface-hover); transition: transform 0.15s ease, border-color 0.15s ease, background 0.15s ease;" onmouseover="this.style.borderColor='var(--border-focus)'; this.style.transform='translateY(-1px)';" onmouseout="this.style.borderColor='var(--border)'; this.style.transform='none';">
        <div style="display: flex; align-items: center; gap: 0.85rem;">
          <div style="width: 36px; height: 36px; border-radius: var(--radius-md); background: rgba(239, 68, 68, 0.1); color: #ef4444; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <?= Icon::get('message-square', 18) ?>
          </div>
          <div>
            <div style="font-weight: 600; font-size: 0.875rem; color: var(--text-main);">Réclamations Étudiants</div>
            <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.15rem;">Traitement des demandes et contestations des étudiants</div>
          </div>
        </div>
        <span class="btn btn-outline btn-sm" style="pointer-events: none;">Traiter</span>
      </a>

    </div>
  </div>

  <!-- Colonne 2 : Dernières Inscriptions Administratives -->
  <div class="card" style="display: flex; flex-direction: column;">
    <div class="card-header">
      <div>
        <div style="display: flex; align-items: center; gap: 0.5rem;">
          <?= Icon::get('users', 18) ?>
          <span class="card-title">Dernières Inscriptions Administratives</span>
        </div>
        <span class="card-desc" style="display: block; margin-top: 0.25rem;">
          Étudiants récemment affectés aux filières
        </span>
      </div>
      <a href="/admin/inscriptions" class="btn btn-ghost btn-sm">Voir tout</a>
    </div>

    <?php if (empty($recentInscriptions)): ?>
      <div style="padding: 3rem 1rem; text-align: center; color: var(--text-muted); font-size: 0.875rem; margin: auto;">
        <div style="margin-bottom: 0.5rem; color: var(--text-subtle);"><?= Icon::get('clipboard-list', 32) ?></div>
        Aucune inscription enregistrée récemment.
      </div>
    <?php else: ?>
      <div style="display: flex; flex-direction: column; gap: 0.5rem; flex: 1;">
        <?php foreach ($recentInscriptions as $ri): ?>
          <div style="display: flex; align-items: center; justify-content: space-between; padding: 0.75rem 0.85rem; border: 1px solid var(--border); border-radius: var(--radius-md); background: var(--bg-surface-hover); transition: background 0.15s ease;">
            <div style="display: flex; align-items: center; gap: 0.75rem;">
              <div style="width: 32px; height: 32px; border-radius: var(--radius-full, 9999px); background: var(--bg-hover); border: 1px solid var(--border); display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 700; color: var(--text-main);">
                <?= strtoupper(substr($ri['prenom'], 0, 1) . substr($ri['nom'], 0, 1)) ?>
              </div>
              <div>
                <div style="font-weight: 600; font-size: 0.875rem; color: var(--text-main);">
                  <?= htmlspecialchars($ri['prenom'] . ' ' . $ri['nom']) ?>
                </div>
                <div style="font-size: 0.75rem; color: var(--text-muted); font-family: ui-monospace, monospace;">
                  <?= htmlspecialchars($ri['anneeUniversitaire']) ?>
                </div>
              </div>
            </div>
            
            <div style="display: flex; align-items: center; gap: 0.65rem;">
              <span class="badge badge-neutral" style="font-family: ui-monospace, monospace; font-size: 0.75rem;">
                <?= htmlspecialchars($ri['filiere_code']) ?>
              </span>
              <?php if ($ri['statut'] === 'EN_COURS'): ?>
                <span class="badge badge-success" style="font-size: 0.75rem;">En règle</span>
              <?php else: ?>
                <span class="badge badge-neutral" style="font-size: 0.75rem;"><?= htmlspecialchars($ri['statut']) ?></span>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

</div>
