<?php
use App\Utils\Icon;
?>
<div style="margin-bottom: 2rem;">
  <h1 style="font-size: 1.5rem; font-weight: 700; letter-spacing: -0.025em; color: var(--text-main); margin-bottom: 0.25rem;">
    Journal d'Audit & Traçabilité des Notes
  </h1>
  <p style="color: var(--text-muted); font-size: 0.875rem;">
    Historique immuable des créations, modifications et rectifications des évaluations académiques.
  </p>
</div>

<!-- Tabs de Filtrage par Type d'Action -->
<div class="tabs-container">
  <a href="/admin/audit" class="tab-btn <?= empty($currentFilter) ? 'active' : '' ?>">
    <?= Icon::get('shield-check', 14) ?>
    <span>Toutes les actions</span>
  </a>
  <a href="/admin/audit?type=CREATION" class="tab-btn <?= $currentFilter === 'CREATION' ? 'active' : '' ?>">
    <?= Icon::get('plus', 14) ?>
    <span>Créations</span>
  </a>
  <a href="/admin/audit?type=MODIFICATION" class="tab-btn <?= $currentFilter === 'MODIFICATION' ? 'active' : '' ?>">
    <?= Icon::get('pencil', 14) ?>
    <span>Modifications</span>
  </a>
  <a href="/admin/audit?type=SUPPRESSION" class="tab-btn <?= $currentFilter === 'SUPPRESSION' ? 'active' : '' ?>">
    <?= Icon::get('trash-2', 14) ?>
    <span>Suppressions</span>
  </a>
</div>

<!-- Table Card -->
<div class="card">
  <div class="card-header">
    <div>
      <span class="card-title">
        <?= Icon::get('shield-check', 18) ?>
        <span>Piste d'Audit Réglementaire</span>
      </span>
      <span class="card-desc" style="display: block; margin-top: 0.25rem;">
        <?= count($logs) ?> événement(s) consigné(s)
      </span>
    </div>
    <span class="badge badge-success">
      <span class="status-dot"></span> Surveillance Active
    </span>
  </div>

  <?php if (empty($logs)): ?>
    <div style="text-align: center; padding: 3rem 1rem; color: var(--text-muted); font-size: 0.875rem;">
      <div style="margin-bottom: 0.5rem; color: var(--text-subtle);"><?= Icon::get('shield-check', 32) ?></div>
      Aucune modification de note enregistrée dans le journal pour l'instant.
    </div>
  <?php else: ?>
    <div class="table-scroll-hint">
      <?= Icon::get('arrow-right', 12) ?>
      <span>Faites glisser horizontalement pour voir toutes les colonnes</span>
    </div>
    <div class="table-wrapper">
      <table>
        <thead>
          <tr>
            <th style="min-width: 140px;">Horodatage</th>
            <th style="min-width: 160px;">Intervenant</th>
            <th style="min-width: 130px;">Type d'Action</th>
            <th style="min-width: 120px;">Module</th>
            <th style="min-width: 150px;">Étudiant Noté</th>
            <th style="min-width: 160px;">Évolution Note (/20)</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($logs as $l): ?>
            <tr>
              <td style="color: var(--text-muted); font-size: 0.8125rem;">
                <?= date('d/m/Y H:i:s', strtotime($l['dateAction'])) ?>
              </td>
              <td>
                <div style="font-weight: 500;"><?= htmlspecialchars($l['auteur_prenom'] . ' ' . $l['auteur_nom']) ?></div>
                <div style="font-size: 0.75rem; color: var(--text-muted);"><?= htmlspecialchars($l['auteur_email']) ?></div>
              </td>
              <td>
                <?php if ($l['typeAction'] === 'CREATION'): ?>
                  <span class="badge badge-success">Création</span>
                <?php elseif ($l['typeAction'] === 'MODIFICATION'): ?>
                  <span class="badge badge-warning">Modification</span>
                <?php else: ?>
                  <span class="badge badge-destructive">Suppression</span>
                <?php endif; ?>
              </td>
              <td>
                <code><?= htmlspecialchars($l['module_code']) ?></code>
                <span style="font-size: 0.75rem; color: var(--text-muted); display: block;"><?= htmlspecialchars($l['module_intitule']) ?></span>
              </td>
              <td>
                <div><?= htmlspecialchars($l['etudiant_prenom'] . ' ' . $l['etudiant_nom']) ?></div>
                <div style="font-size: 0.75rem; color: var(--text-muted);">CNE: <code><?= htmlspecialchars($l['etudiant_cne']) ?></code></div>
              </td>
              <td>
                <div style="display: flex; align-items: center; gap: 0.5rem; font-weight: 600; font-family: ui-monospace, monospace;">
                  <span style="color: var(--text-muted); text-decoration: line-through;">
                    <?= $l['ancienneValeur'] !== null ? htmlspecialchars(number_format((float)$l['ancienneValeur'], 2)) : '—' ?>
                  </span>
                  <span style="color: var(--text-subtle);">&rarr;</span>
                  <span style="color: var(--primary);">
                    <?= $l['nouvelleValeur'] !== null ? htmlspecialchars(number_format((float)$l['nouvelleValeur'], 2)) : '—' ?>
                  </span>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>
