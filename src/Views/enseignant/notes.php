<?php
use App\Utils\Icon;
?>
<div style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem;">
  <div>
    <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
      <a href="/enseignant/modules" class="btn btn-outline btn-sm" title="Retour aux modules">
        <?= Icon::get('chevron-left', 14) ?>
      </a>
      <h1 style="font-size: 1.35rem; font-weight: 700; letter-spacing: -0.025em; color: var(--text-main); word-break: break-word;">
        Évaluation des Notes — <?= htmlspecialchars($module['code']) ?>
      </h1>
    </div>
    <p style="color: var(--text-muted); font-size: 0.875rem;">
      Module : <strong><?= htmlspecialchars($module['intitule']) ?></strong> • 
      Semestre : <strong><?= htmlspecialchars($module['semestre']) ?></strong> •
      Évaluation directe sur 20
    </p>
  </div>
</div>

<!-- Tabs Session Normale vs Rattrapage -->
<div class="tabs-container">
  <a href="/enseignant/modules/<?= $module['idModule'] ?>/notes?session=NORMALE" class="tab-btn <?= $currentSession === 'NORMALE' ? 'active' : '' ?>">
    <?= Icon::get('award', 14) ?>
    <span>Session Normale</span>
  </a>
  <a href="/enseignant/modules/<?= $module['idModule'] ?>/notes?session=RATTRAPAGE" class="tab-btn <?= $currentSession === 'RATTRAPAGE' ? 'active' : '' ?>">
    <?= Icon::get('clock', 14) ?>
    <span>Session de Rattrapage</span>
  </a>
</div>

<!-- Formulaire Matrice de Notes -->
<form action="/enseignant/modules/<?= $module['idModule'] ?>/notes/save" method="POST" id="gradesMatrixForm">
  <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
  <input type="hidden" name="session" value="<?= htmlspecialchars($currentSession) ?>">

  <div class="card" style="padding: 0; overflow: hidden; margin-bottom: 1.5rem;">
    <div class="card-header" style="padding: 1.25rem 1.25rem 0.75rem 1.25rem; margin-bottom: 0;">
      <div>
        <span class="card-title">
          <?= Icon::get('pencil', 18) ?>
          <span>Matrice des Évaluations (Session <?= htmlspecialchars($currentSession) ?>)</span>
        </span>
        <span class="card-desc" style="display: block; margin-top: 0.25rem;">
          <?= count($notesList) ?> étudiant(s) inscrit(s) à évaluer.
        </span>
      </div>
      <button type="submit" class="btn btn-primary">
        <?= Icon::get('check', 16) ?>
        <span>Enregistrer</span>
      </button>
    </div>

    <?php if (empty($notesList)): ?>
      <div style="text-align: center; padding: 3rem 1rem; color: var(--text-muted); font-size: 0.875rem;">
        <div style="margin-bottom: 0.5rem; color: var(--text-subtle);"><?= Icon::get('users', 32) ?></div>
        Aucun étudiant n'est inscrit dans la filière rattachée à ce module.
      </div>
    <?php else: ?>
      <div class="table-scroll-hint" style="margin: 0.5rem 1rem 0.75rem 1rem;">
        <?= Icon::get('arrow-right', 13) ?>
        <span>Faites glisser horizontalement pour voir toutes les colonnes</span>
      </div>
      <div class="table-wrapper" style="border: none; border-radius: 0;">
        <table>
          <thead>
            <tr>
              <th style="min-width: 160px;">Étudiant</th>
              <th style="min-width: 100px;">CNE</th>
              <th style="min-width: 110px; text-align: center;">Note (/20)</th>
              <th style="min-width: 110px; text-align: center;">Statut</th>
              <th style="min-width: 180px;">Remarque Pédagogique</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($notesList as $etu): 
              $id = (int)$etu['idEtudiant'];
              $val = $etu['noteFinale'] !== null ? number_format((float)$etu['noteFinale'], 2, '.', '') : '';
              $statut = $etu['statutValidation'] ?? 'NV';
            ?>
              <tr class="grade-row" data-etudiant-id="<?= $id ?>">
                <td>
                  <div style="font-weight: 600;"><?= htmlspecialchars($etu['etudiant_prenom'] . ' ' . $etu['etudiant_nom']) ?></div>
                  <div style="font-size: 0.75rem; color: var(--text-muted);"><?= htmlspecialchars($etu['etudiant_email']) ?></div>
                </td>
                <td><code><?= htmlspecialchars($etu['etudiant_cne']) ?></code></td>
                <td style="text-align: center;">
                  <input 
                    type="number" 
                    step="0.25" 
                    min="0" 
                    max="20" 
                    name="notes[<?= $id ?>][note]" 
                    class="form-control input-note" 
                    value="<?= htmlspecialchars($val) ?>" 
                    placeholder="0.00"
                    style="font-family: ui-monospace, monospace; font-size: 0.9375rem; font-weight: 700; text-align: center; max-width: 130px; margin: 0 auto;"
                  >
                </td>
                <td style="text-align: center;">
                  <div class="cell-statut-badge">
                    <?php if ($val === ''): ?>
                      <span class="badge badge-neutral">—</span>
                    <?php elseif ((float)$val >= 10.00): ?>
                      <span class="badge badge-success">Validé</span>
                    <?php elseif ($currentSession === 'NORMALE'): ?>
                      <span class="badge badge-warning">Rattrapage</span>
                    <?php else: ?>
                      <span class="badge badge-destructive">Non Validé</span>
                    <?php endif; ?>
                  </div>
                </td>
                <td>
                  <input 
                    type="text" 
                    name="notes[<?= $id ?>][remarque]" 
                    class="form-control" 
                    value="<?= htmlspecialchars($etu['remarque'] ?? '') ?>" 
                    placeholder="Observation pédagogique..."
                    style="font-size: 0.8125rem;"
                  >
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>

    <div style="padding: 0.875rem 1.5rem; background-color: var(--bg-surface); border-top: 1px solid var(--border); display: flex; align-items: center; gap: 0.5rem;">
      <span style="color: var(--text-muted);"><?= Icon::get('shield-check', 14) ?></span>
      <span style="font-size: 0.8125rem; color: var(--text-muted);">
        Toute modification de note est automatiquement consignée dans le journal d'audit avec horodatage et identifiant enseignant.
      </span>
    </div>
  </div>
</form>

<script>
  (function () {
    const currentSession = <?= json_encode($currentSession) ?>;

    function updateRowStatus(row) {
      const noteInput = row.querySelector('.input-note');
      const badgeCell = row.querySelector('.cell-statut-badge');

      const val = noteInput && noteInput.value.trim() !== '' ? parseFloat(noteInput.value) : null;

      if (val === null || isNaN(val)) {
        badgeCell.innerHTML = '<span class="badge badge-neutral">—</span>';
        return;
      }

      if (val >= 10.00) {
        badgeCell.innerHTML = '<span class="badge badge-success">Validé</span>';
      } else {
        if (currentSession === 'NORMALE') {
          badgeCell.innerHTML = '<span class="badge badge-warning">Rattrapage</span>';
        } else {
          badgeCell.innerHTML = '<span class="badge badge-destructive">Non Validé</span>';
        }
      }
    }

    document.querySelectorAll('.grade-row').forEach(row => {
      row.addEventListener('input', (e) => {
        if (e.target.classList.contains('input-note')) {
          updateRowStatus(row);
        }
      });
    });
  })();
</script>
