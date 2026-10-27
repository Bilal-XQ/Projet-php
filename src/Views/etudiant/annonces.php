<?php
declare(strict_types=1);

use App\Utils\Icon;
?>
<div class="content-container">
  <!-- En-tête -->
  <div style="margin-bottom: 2rem;">
    <h1 style="font-size: 1.5rem; font-weight: 700; letter-spacing: -0.025em; color: var(--text-main); margin-bottom: 0.25rem;">
      Avis & Annonces Pédagogiques
    </h1>
    <p style="color: var(--text-muted); font-size: 0.875rem;">
      Flux des communications officielles du département et des consignes spécifiques à vos modules.
    </p>
  </div>

  <?php if (empty($annonces)): ?>
    <div class="card" style="padding: 3.5rem; text-align: center; color: var(--text-muted);">
      <div style="margin-bottom: 0.75rem; color: var(--text-subtle);"><?= Icon::get('megaphone', 40) ?></div>
      <h3 style="font-size: 1rem; font-weight: 600; color: var(--text-main); margin-bottom: 0.25rem;">Aucune annonce disponible</h3>
      <p style="font-size: 0.875rem; color: var(--text-muted);">
        Vous êtes à jour ! Aucune nouvelle communication n'a été publiée pour votre cursus.
      </p>
    </div>
  <?php else: ?>
    <div style="display: flex; flex-direction: column; gap: 1.25rem;">
      <?php foreach ($annonces as $a): ?>
        <div class="card" style="padding: 1.25rem 1.5rem; transition: transform 0.15s ease, box-shadow 0.15s ease;">
          <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; margin-bottom: 0.75rem; flex-wrap: wrap;">
            <div>
              <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
                <?php if ($a['portee'] === 'GLOBALE'): ?>
                  <span class="badge badge-primary" style="font-size: 0.75rem; font-weight: 600;">
                    <?= Icon::get('megaphone', 12) ?> Avis Général
                  </span>
                <?php else: ?>
                  <span class="badge badge-neutral" style="font-size: 0.75rem; font-weight: 600;">
                    <?= Icon::get('book-open', 12) ?> Module : <?= htmlspecialchars($a['module_code']) ?>
                  </span>
                  <span style="font-size: 0.8125rem; color: var(--text-muted);">
                    (<?= htmlspecialchars($a['module_intitule']) ?>)
                  </span>
                <?php endif; ?>
              </div>

              <h2 style="font-size: 1.15rem; font-weight: 700; color: var(--text-main); letter-spacing: -0.015em; margin: 0.35rem 0 0 0;">
                <?= htmlspecialchars($a['titre']) ?>
              </h2>
            </div>

            <!-- Date & Auteur -->
            <div style="text-align: right; font-size: 0.75rem; color: var(--text-subtle);">
              <div style="font-weight: 600; color: var(--text-main); margin-bottom: 2px;">
                <?= htmlspecialchars($a['auteur_prenom'] . ' ' . $a['auteur_nom']) ?>
              </div>
              <div style="color: var(--text-muted);">
                <?= htmlspecialchars($a['auteur_role'] === 'ADMIN' ? 'Administration Scolarité' : 'Professeur Responsable') ?>
              </div>
              <div style="margin-top: 4px; font-family: ui-monospace, monospace;">
                <?= date('d/m/Y à H:i', strtotime($a['datePublication'])) ?>
              </div>
            </div>
          </div>

          <div style="color: var(--text-main); font-size: 0.875rem; line-height: 1.6; white-space: pre-line; border-top: 1px solid var(--border-color); padding-top: 0.875rem;">
            <?= htmlspecialchars($a['contenu']) ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>
