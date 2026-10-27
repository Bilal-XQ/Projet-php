<?php
use App\Utils\Icon;
?>
<div style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem;">
  <div>
    <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
      <a href="/enseignant/modules" class="btn btn-outline btn-sm" title="Retour aux modules">
        <?= Icon::get('chevron-left', 14) ?>
      </a>
      <h1 style="font-size: 1.5rem; font-weight: 700; letter-spacing: -0.025em; color: var(--text-main);">
        Effectif Étudiants — <?= htmlspecialchars($module['code']) ?>
      </h1>
    </div>
    <p style="color: var(--text-muted); font-size: 0.875rem;">
      Module : <strong><?= htmlspecialchars($module['intitule']) ?></strong> • 
      Filière : <strong><?= htmlspecialchars($module['filiere_code'] ?? 'Filière') ?></strong>
    </p>
  </div>

  <div style="display: flex; gap: 0.5rem;">
    <a href="/enseignant/modules/<?= $module['idModule'] ?>/notes" class="btn btn-primary btn-sm">
      <?= Icon::get('pencil', 13) ?>
      <span>Saisie des Notes</span>
    </a>
    <a href="/enseignant/modules/<?= $module['idModule'] ?>/seances" class="btn btn-outline btn-sm">
      <?= Icon::get('clock', 13) ?>
      <span>Séances</span>
    </a>
  </div>
</div>

<div class="card">
  <div class="card-header">
    <div>
      <span class="card-title">
        <?= Icon::get('users', 18) ?>
        <span>Liste des Étudiants Inscrits</span>
      </span>
      <span class="card-desc" style="display: block; margin-top: 0.25rem;">
        Total : <?= count($etudiants) ?> étudiant(s) matriculé(s)
      </span>
    </div>
    <span class="badge badge-neutral"><?= count($etudiants) ?> Inscrits</span>
  </div>

  <?php if (empty($etudiants)): ?>
    <div style="text-align: center; padding: 3rem 1rem; color: var(--text-muted); font-size: 0.875rem;">
      <div style="margin-bottom: 0.5rem; color: var(--text-subtle);"><?= Icon::get('users', 32) ?></div>
      Aucun étudiant n'est inscrit dans la filière rattachée à ce module.
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
            <th style="min-width: 160px;">Étudiant</th>
            <th style="min-width: 100px;">CNE</th>
            <th style="min-width: 100px;">Né(e) le</th>
            <th style="min-width: 160px;">Note Finale (Session Normale)</th>
            <th style="min-width: 120px;">Statut Validation</th>
            <th style="min-width: 160px;">Bilan d'Assiduité</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($etudiants as $etu): 
            $abs = $etu['absences_summary'] ?? ['total_absences' => 0, 'non_justifiees' => 0, 'justifiees' => 0];
          ?>
            <tr>
              <td>
                <div style="font-weight: 600;"><?= htmlspecialchars($etu['prenom'] . ' ' . $etu['nom']) ?></div>
                <div style="font-size: 0.75rem; color: var(--text-muted);"><?= htmlspecialchars($etu['email']) ?></div>
              </td>
              <td><code><?= htmlspecialchars($etu['cne']) ?></code></td>
              <td style="color: var(--text-muted); font-size: 0.8125rem;">
                <?= !empty($etu['dateNaissance']) ? date('d/m/Y', strtotime($etu['dateNaissance'])) : '—' ?>
              </td>
              <td>
                <?php if ($etu['noteFinale'] !== null): ?>
                  <span style="font-weight: 700; font-family: ui-monospace, monospace;">
                    <?= number_format((float)$etu['noteFinale'], 2) ?> / 20
                  </span>
                <?php else: ?>
                  <span style="color: var(--text-muted);">En attente</span>
                <?php endif; ?>
              </td>
              <td>
                <?php if ($etu['statutValidation'] === 'V'): ?>
                  <span class="badge badge-success">Validé</span>
                <?php elseif ($etu['statutValidation'] === 'RATT'): ?>
                  <span class="badge badge-warning">Rattrapage</span>
                <?php else: ?>
                  <span class="badge badge-neutral">Non évalué</span>
                <?php endif; ?>
              </td>
              <td>
                <div style="display: flex; gap: 0.35rem; font-size: 0.75rem;">
                  <?php if ((int)$abs['total_absences'] === 0): ?>
                    <span class="badge badge-success">Assidu (0 abs)</span>
                  <?php else: ?>
                    <?php if ((int)$abs['non_justifiees'] > 0): ?>
                      <span class="badge badge-destructive" title="Absences non justifiées">
                        <?= (int)$abs['non_justifiees'] ?> non justifiée(s)
                      </span>
                    <?php endif; ?>
                    <?php if ((int)$abs['justifiees'] > 0): ?>
                      <span class="badge badge-neutral" title="Absences justifiées">
                        <?= (int)$abs['justifiees'] ?> justifiée(s)
                      </span>
                    <?php endif; ?>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>
