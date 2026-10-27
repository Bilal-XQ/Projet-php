<?php
declare(strict_types=1);

use App\Utils\Icon;

$moyenneVal = $gpa['moyenne'] !== null ? number_format((float)$gpa['moyenne'], 2) : '—';
?>
<div class="content-container">
  <!-- En-tête de page -->
  <div style="margin-bottom: 2rem;">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem;">
      <div>
        <h1 style="font-size: 1.5rem; font-weight: 700; letter-spacing: -0.025em; color: var(--text-main); margin-bottom: 0.25rem;">
          Mes Notes & Résultats d'Évaluation
        </h1>
        <p style="color: var(--text-muted); font-size: 0.875rem;">
          Consultez le détail de vos évaluations continues, épreuves d'examen et remarques pédagogiques.
        </p>
      </div>

      <div style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
        <!-- Sélecteur de session -->
        <div style="display: flex; background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-sm); padding: 3px;">
          <a href="/etudiant/notes?session=NORMALE" class="btn btn-sm <?= $currentSession === 'NORMALE' ? 'btn-primary' : 'btn-ghost' ?>" style="padding: 4px 10px; font-size: 0.75rem;">
            Session Normale
          </a>
          <a href="/etudiant/notes?session=RATTRAPAGE" class="btn btn-sm <?= $currentSession === 'RATTRAPAGE' ? 'btn-primary' : 'btn-ghost' ?>" style="padding: 4px 10px; font-size: 0.75rem;">
            Rattrapage
          </a>
        </div>

        <!-- Bouton Télécharger Relevé PDF -->
        <a href="/etudiant/releve/pdf" target="_blank" class="btn btn-outline btn-sm">
          <?= Icon::get('file-text', 14) ?>
          <span>Relevé Officiel PDF</span>
        </a>
      </div>
    </div>
  </div>

  <!-- Bloc Synthèse Académique & Décision du Jury -->
  <div class="card" style="margin-bottom: 1.5rem; background: linear-gradient(to right, var(--bg-card), var(--bg-hover));">
    <div style="padding: 1.25rem 1.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1.5rem;">
      <div style="display: flex; align-items: center; gap: 1.25rem;">
        <div style="width: 52px; height: 52px; border-radius: var(--radius); background: var(--border-color); display: flex; align-items: center; justify-content: center; color: var(--text-main);">
          <?= Icon::get('graduation-cap', 26) ?>
        </div>
        <div>
          <div style="font-size: 0.8125rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; font-weight: 600;">
            Moyenne Générale
          </div>
          <div style="display: flex; align-items: baseline; gap: 0.5rem; margin-top: 2px;">
            <span style="font-size: 1.75rem; font-weight: 800; font-family: ui-monospace, monospace; color: var(--text-main);">
              <?= $moyenneVal ?>
            </span>
            <span style="font-size: 0.875rem; color: var(--text-muted);">/ 20.00</span>
          </div>
        </div>
      </div>

      <!-- Détails métriques -->
      <div style="display: flex; gap: 2rem; align-items: center; flex-wrap: wrap;">
        <div>
          <div style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 2px;">Progression</div>
          <div style="font-size: 1.1rem; font-weight: 700; font-family: ui-monospace, monospace;">
            <?= $gpa['modulesEvalues'] ?> / <?= $gpa['totalModules'] ?> <span style="font-size: 0.75rem; font-weight: 400; color: var(--text-muted);">évalués</span>
          </div>
        </div>

        <div>
          <div style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 2px;">Décision du Jury</div>
          <div>
            <?php if ($gpa['decision'] === 'Admis'): ?>
              <span class="badge badge-success" style="font-size: 0.8125rem; font-weight: 700; padding: 4px 10px;">
                <span class="status-dot"></span> Admis
              </span>
            <?php elseif ($gpa['decision'] === 'Session de Rattrapage'): ?>
              <span class="badge badge-warning" style="font-size: 0.8125rem; font-weight: 700; padding: 4px 10px;">
                <span class="status-dot"></span> Session de Rattrapage
              </span>
            <?php elseif ($gpa['decision'] === 'Ajourné'): ?>
              <span class="badge badge-destructive" style="font-size: 0.8125rem; font-weight: 700; padding: 4px 10px;">
                <span class="status-dot"></span> Ajourné
              </span>
            <?php else: ?>
              <span class="badge badge-neutral" style="font-size: 0.8125rem; padding: 4px 10px;">
                En cours d'évaluation
              </span>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Tableau Matriciel des Évaluations -->
  <div class="card">
    <div class="card-header">
      <div>
        <span class="card-title">
          <?= Icon::get('book-open', 18) ?>
          <span>Détail des Évaluations par Module (<?= htmlspecialchars($currentSession) ?>)</span>
        </span>
        <span class="card-desc" style="display: block; margin-top: 0.25rem;">
          Règle officielle : Seuil de validation du module : 10.00 / 20.
        </span>
      </div>
    </div>

    <?php if (empty($notes)): ?>
      <div style="padding: 3rem; text-align: center; color: var(--text-muted); font-size: 0.875rem;">
        <div style="margin-bottom: 0.5rem; color: var(--text-subtle);"><?= Icon::get('book-open', 36) ?></div>
        Aucune donnée d'évaluation n'est actuellement disponible pour votre filière.
      </div>
    <?php else: ?>
      <div class="table-scroll-hint">
        <?= Icon::get('arrow-right', 12) ?>
        <span>Faites glisser horizontalement pour voir toutes les colonnes</span>
      </div>
      <div class="table-wrapper">
        <table class="data-table">
          <thead>
            <tr>
              <th style="min-width: 90px;">Code</th>
              <th style="min-width: 180px;">Intitulé du Module</th>
              <th style="min-width: 80px; text-align: center;">Semestre</th>
              <th style="min-width: 150px;">Enseignant</th>
              <th style="min-width: 110px; text-align: right;">Note (/20)</th>
              <th style="min-width: 110px; text-align: center;">Statut</th>
              <th style="min-width: 180px;">Observations / Remarques</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($notes as $n): ?>
              <tr>
                <td>
                  <span class="badge-code"><?= htmlspecialchars($n['module_code']) ?></span>
                </td>
                <td>
                  <div style="font-weight: 600; color: var(--text-main); font-size: 0.875rem;">
                    <?= htmlspecialchars($n['module_intitule']) ?>
                  </div>
                </td>
                <td style="text-align: center; font-family: ui-monospace, monospace; font-size: 0.8125rem;">
                  <?= htmlspecialchars($n['semestre'] ?? 'S1') ?>
                </td>
                <td style="color: var(--text-muted); font-size: 0.8125rem;">
                  <?= !empty($n['enseignant_nom']) ? htmlspecialchars($n['enseignant_prenom'] . ' ' . $n['enseignant_nom']) : '<span style="color: var(--text-subtle);">Non assigné</span>' ?>
                </td>
                <td style="text-align: right; font-weight: 700; font-family: ui-monospace, monospace; font-size: 1rem; color: var(--text-main);">
                  <?= $n['noteFinale'] !== null ? htmlspecialchars(number_format((float)$n['noteFinale'], 2)) : '<span style="color: var(--text-subtle);">—</span>' ?>
                </td>
                <td style="text-align: center;">
                  <?php if ($n['statutValidation'] === 'V'): ?>
                    <span class="badge badge-success">Validé</span>
                  <?php elseif ($n['statutValidation'] === 'RATT'): ?>
                    <span class="badge badge-warning">Rattrapage</span>
                  <?php elseif ($n['statutValidation'] === 'NV'): ?>
                    <span class="badge badge-destructive">Non Validé</span>
                  <?php else: ?>
                    <span class="badge badge-neutral">En attente</span>
                  <?php endif; ?>
                </td>
                <td style="font-size: 0.8125rem; color: var(--text-muted); max-width: 240px;">
                  <?php if (!empty($n['remarque'])): ?>
                    <span title="<?= htmlspecialchars($n['remarque']) ?>">
                      <?= htmlspecialchars($n['remarque']) ?>
                    </span>
                  <?php else: ?>
                    <span style="color: var(--text-subtle); font-style: italic;">Aucune observation</span>
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
