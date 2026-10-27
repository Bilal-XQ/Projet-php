<?php
use App\Utils\Icon;
?>
<div style="margin-bottom: 2rem;">
  <h1 style="font-size: 1.5rem; font-weight: 700; letter-spacing: -0.025em; color: var(--text-main); margin-bottom: 0.25rem;">
    Mes Modules d'Enseignement
  </h1>
  <p style="color: var(--text-muted); font-size: 0.875rem;">
    Unités d'enseignement sous votre responsabilité pédagogique pour l'année universitaire 2026-2027.
  </p>
</div>

<div class="card">
  <div class="card-header">
    <div>
      <span class="card-title">
        <?= Icon::get('book-open', 18) ?>
        <span>Modules Affectés</span>
      </span>
      <span class="card-desc" style="display: block; margin-top: 0.25rem;">
        Total : <?= count($modules) ?> module(s)
      </span>
    </div>
    <span class="badge badge-neutral"><?= count($modules) ?> Modules</span>
  </div>

  <?php if (empty($modules)): ?>
    <div style="text-align: center; padding: 3rem 1rem; color: var(--text-muted); font-size: 0.875rem;">
      <div style="margin-bottom: 0.5rem; color: var(--text-subtle);"><?= Icon::get('book-open', 32) ?></div>
      Aucun module ne vous est actuellement attribué. Contactez le secrétariat pédagogique.
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
            <th style="min-width: 90px;">Code</th>
            <th style="min-width: 180px;">Intitulé du Module</th>
            <th style="min-width: 120px;">Filière</th>
            <th style="min-width: 80px;">Semestre</th>
            <th style="min-width: 110px;">Étudiants Inscrits</th>
            <th style="min-width: 160px; text-align: right;">Actions Pédagogiques</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($modules as $m): ?>
            <tr>
              <td><code><?= htmlspecialchars($m['code']) ?></code></td>
              <td style="font-weight: 600;"><?= htmlspecialchars($m['intitule']) ?></td>
              <td>
                <span class="badge badge-neutral"><?= htmlspecialchars($m['filiere_code']) ?></span>
                <span style="font-size: 0.75rem; color: var(--text-muted); display: block;"><?= htmlspecialchars($m['filiere_intitule']) ?></span>
              </td>
              <td><span class="badge badge-neutral"><?= htmlspecialchars($m['semestre']) ?></span></td>
              <td>
                <span class="badge badge-neutral">
                  <?= Icon::get('users', 12) ?>
                  <span><?= (int)$m['total_etudiants'] ?> étudiants</span>
                </span>
              </td>
              <td style="text-align: right;">
                <div style="display: inline-flex; gap: 0.35rem;">
                  <a href="/enseignant/modules/<?= $m['idModule'] ?>/notes" class="btn btn-primary btn-sm" title="Saisie des notes">
                    <?= Icon::get('pencil', 13) ?>
                    <span>Notes</span>
                  </a>
                  <a href="/enseignant/modules/<?= $m['idModule'] ?>/seances" class="btn btn-outline btn-sm" title="Gestion des séances">
                    <?= Icon::get('clock', 13) ?>
                    <span>Séances</span>
                  </a>
                  <a href="/enseignant/modules/<?= $m['idModule'] ?>/etudiants" class="btn btn-outline btn-sm" title="Liste des étudiants">
                    <?= Icon::get('users', 13) ?>
                    <span>Effectif</span>
                  </a>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>
