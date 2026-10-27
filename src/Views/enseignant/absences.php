<?php
use App\Utils\Icon;
?>
<div style="margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem;">
  <div>
    <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
      <a href="/enseignant/modules/<?= $seance['idModule'] ?>/seances" class="btn btn-outline btn-sm" title="Retour aux séances">
        <?= Icon::get('chevron-left', 14) ?>
      </a>
      <h1 style="font-size: 1.5rem; font-weight: 700; letter-spacing: -0.025em; color: var(--text-main);">
        Feuille d'Émargement — Séance de <?= htmlspecialchars($seance['typeSeance']) ?>
      </h1>
    </div>
    <p style="color: var(--text-muted); font-size: 0.875rem;">
      Module : <strong><?= htmlspecialchars($seance['module_code']) ?> — <?= htmlspecialchars($seance['module_intitule']) ?></strong> • 
      Date : <strong><?= date('d/m/Y', strtotime($seance['dateSeance'])) ?></strong> (<?= htmlspecialchars(substr($seance['heureDebut'], 0, 5)) ?> à <?= htmlspecialchars(substr($seance['heureFin'], 0, 5)) ?>)
    </p>
  </div>
</div>

<!-- Guide Visuel Pédagogique d'Assiduité -->
<div class="card" style="margin-bottom: 1.5rem; background: var(--bg-surface-hover); border-color: var(--border);">
  <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
    <div style="display: flex; align-items: center; gap: 0.75rem;">
      <div style="color: var(--primary);"><?= Icon::get('shield-check', 22) ?></div>
      <div>
        <strong style="font-size: 0.875rem; color: var(--text-main);">Guide de Prise d'Assiduité & Statuts Légaux</strong>
        <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 2px;">
          L'assiduité est obligatoire. Toute absence non motivée par certificat sous 48h est comptabilisée comme pénalisante.
        </p>
      </div>
    </div>
    <div style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
      <span class="badge badge-success"><?= Icon::get('check', 12) ?> Présent (En classe)</span>
      <span class="badge badge-destructive"><?= Icon::get('x', 12) ?> Absent (Non présent)</span>
      <button type="button" id="markAllPresentBtn" class="btn btn-outline btn-sm" style="margin-left: 0.5rem;">
        <?= Icon::get('check-check', 14) ?>
        <span>Marquer Tous Présents</span>
      </button>
    </div>
  </div>
</div>

<form action="/enseignant/seances/<?= $seance['idSeance'] ?>/absences/save" method="POST">
  <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

  <div class="card" style="padding: 0; overflow: hidden; margin-bottom: 1.5rem;">
    <div class="card-header" style="padding: 1.25rem 1.25rem 0.75rem 1.25rem; margin-bottom: 0;">
      <div>
        <span class="card-title">
          <?= Icon::get('clipboard-list', 18) ?>
          <span>Registre d'Appel des Étudiants</span>
        </span>
        <span class="card-desc" style="display: block; margin-top: 0.25rem;">
          <?= count($absencesList) ?> étudiant(s) convoqué(s). Cochez le statut pour chaque inscrit.
        </span>
      </div>
      <button type="submit" class="btn btn-primary">
        <?= Icon::get('check', 16) ?>
        <span>Enregistrer la Feuille d'Émargement</span>
      </button>
    </div>

    <?php if (empty($absencesList)): ?>
      <div style="text-align: center; padding: 3rem 1rem; color: var(--text-muted); font-size: 0.875rem;">
        <div style="margin-bottom: 0.5rem; color: var(--text-subtle);"><?= Icon::get('users', 32) ?></div>
        Aucun étudiant n'est inscrit dans la filière rattachée à cette séance.
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
              <th style="min-width: 220px;">Statut de Présence</th>
              <th style="min-width: 90px; text-align: center;">Justifiée ?</th>
              <th style="min-width: 160px;">Motif / Justification</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($absencesList as $etu): 
              $id = (int)$etu['idEtudiant'];
              $statut = $etu['statut'] ?? 'PRESENT';
              $justifiee = (bool)($etu['justifiee'] ?? false);
              $motif = $etu['motif'] ?? '';
            ?>
              <tr>
                <td>
                  <div style="font-weight: 600;"><?= htmlspecialchars($etu['etudiant_prenom'] . ' ' . $etu['etudiant_nom']) ?></div>
                  <div style="font-size: 0.75rem; color: var(--text-muted);"><?= htmlspecialchars($etu['etudiant_email']) ?></div>
                </td>
                <td><code><?= htmlspecialchars($etu['etudiant_cne']) ?></code></td>
                <td>
                  <div style="display: flex; gap: 0.35rem;">
                    <label class="btn btn-sm <?= $statut === 'PRESENT' ? 'btn-primary' : 'btn-outline' ?>" style="cursor: pointer; padding: 0.25rem 0.65rem;">
                      <input type="radio" name="absences[<?= $id ?>][statut]" value="PRESENT" <?= $statut === 'PRESENT' ? 'checked' : '' ?> style="display: none;" onchange="updateRadioSelection(this)">
                      <?= Icon::get('check', 12) ?>
                      <span>Présent</span>
                    </label>
                    <label class="btn btn-sm <?= $statut === 'ABSENT' ? 'btn-destructive' : 'btn-outline' ?>" style="cursor: pointer; padding: 0.25rem 0.65rem;">
                      <input type="radio" name="absences[<?= $id ?>][statut]" value="ABSENT" <?= $statut === 'ABSENT' ? 'checked' : '' ?> style="display: none;" onchange="updateRadioSelection(this)">
                      <?= Icon::get('x', 12) ?>
                      <span>Absent</span>
                    </label>
                  </div>
                </td>
                <td style="text-align: center;">
                  <input type="checkbox" name="absences[<?= $id ?>][justifiee]" value="1" <?= $justifiee ? 'checked' : '' ?> style="width: 16px; height: 16px; cursor: pointer;" title="Cochez si un certificat médical ou justificatif a été validé">
                </td>
                <td>
                  <input type="text" name="absences[<?= $id ?>][motif]" class="form-control" value="<?= htmlspecialchars($motif) ?>" placeholder="Ex: Certificat médical..." style="font-size: 0.8125rem;">
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</form>

<script>
  function updateRadioSelection(radio) {
    const parent = radio.closest('td');
    parent.querySelectorAll('label').forEach(lbl => {
      lbl.classList.remove('btn-primary', 'btn-destructive', 'btn-warning');
      lbl.classList.add('btn-outline');
    });

    const currentLabel = radio.closest('label');
    currentLabel.classList.remove('btn-outline');
    if (radio.value === 'PRESENT') {
      currentLabel.classList.add('btn-primary');
    } else if (radio.value === 'ABSENT') {
      currentLabel.classList.add('btn-destructive');
    }
  }

  // 1-Click: Marquer Tous Présents
  const markAllBtn = document.getElementById('markAllPresentBtn');
  if (markAllBtn) {
    markAllBtn.addEventListener('click', () => {
      document.querySelectorAll('input[type="radio"][value="PRESENT"]').forEach(radio => {
        radio.checked = true;
        updateRadioSelection(radio);
      });
    });
  }
</script>
