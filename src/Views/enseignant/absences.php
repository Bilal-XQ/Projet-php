<?php
use App\Utils\Icon;
?>
<div style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem;">
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

<form action="/enseignant/seances/<?= $seance['idSeance'] ?>/absences/save" method="POST">
  <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

  <div class="card" style="padding: 0; overflow: hidden; margin-bottom: 1.5rem;">
    <div class="card-header" style="padding: 1.25rem 1.5rem; margin-bottom: 0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
      <div>
        <span class="card-title" style="display: flex; align-items: center; gap: 0.5rem;">
          <?= Icon::get('clipboard-list', 18) ?>
          <span>Registre d'Appel des Étudiants</span>
        </span>
        <span class="card-desc" style="display: block; margin-top: 0.25rem;">
          <?= count($absencesList) ?> étudiant(s) convoqué(s). Cochez le statut de présence pour chaque inscrit.
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
      <div class="table-wrapper" style="border: none; border-radius: 0; margin-bottom: 0;">
        <table>
          <thead>
            <tr>
              <th style="min-width: 180px;">Étudiant</th>
              <th style="min-width: 110px;">CNE</th>
              <th style="min-width: 200px; text-align: center;">Statut de Présence</th>
              <th style="min-width: 110px; text-align: center;">Justifiée ?</th>
              <th style="min-width: 220px;">Motif / Justification</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($absencesList as $etu): 
              $id = (int)$etu['idEtudiant'];
              $statut = $etu['statut'] ?? 'PRESENT';
              $justifiee = (bool)($etu['justifiee'] ?? false);
              $motif = $etu['motif'] ?? '';
            ?>
              <tr class="attendance-row" id="row_<?= $id ?>">
                <td>
                  <div style="font-weight: 600; color: var(--text-main);"><?= htmlspecialchars($etu['etudiant_prenom'] . ' ' . $etu['etudiant_nom']) ?></div>
                  <div style="font-size: 0.75rem; color: var(--text-muted);"><?= htmlspecialchars($etu['etudiant_email']) ?></div>
                </td>
                <td><code><?= htmlspecialchars($etu['etudiant_cne']) ?></code></td>
                <td style="text-align: center;">
                  <div class="attendance-toggle-group" style="display: inline-flex; border: 1px solid var(--border); border-radius: var(--radius-md); padding: 3px; background: var(--bg-surface); gap: 3px;">
                    <button type="button" 
                            class="attendance-btn <?= $statut === 'PRESENT' ? 'is-present' : '' ?>"
                            onclick="setAttendanceStatus(<?= $id ?>, 'PRESENT', this)"
                            style="display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.35rem 0.85rem; border-radius: var(--radius-sm); border: none; font-size: 0.8125rem; font-weight: 600; cursor: pointer; transition: all 0.15s ease;">
                      <?= Icon::get('check', 13) ?>
                      <span>Présent</span>
                    </button>
                    <button type="button" 
                            class="attendance-btn <?= $statut === 'ABSENT' ? 'is-absent' : '' ?>"
                            onclick="setAttendanceStatus(<?= $id ?>, 'ABSENT', this)"
                            style="display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.35rem 0.85rem; border-radius: var(--radius-sm); border: none; font-size: 0.8125rem; font-weight: 600; cursor: pointer; transition: all 0.15s ease;">
                      <?= Icon::get('x', 13) ?>
                      <span>Absent</span>
                    </button>
                    <input type="hidden" id="statut_input_<?= $id ?>" name="absences[<?= $id ?>][statut]" value="<?= htmlspecialchars($statut) ?>">
                  </div>
                </td>
                <td style="text-align: center;">
                  <input type="checkbox" 
                         id="justifiee_<?= $id ?>"
                         name="absences[<?= $id ?>][justifiee]" 
                         value="1" 
                         <?= $justifiee ? 'checked' : '' ?> 
                         <?= $statut === 'PRESENT' ? 'disabled' : '' ?>
                         onchange="toggleJustificationInput(<?= $id ?>)"
                         style="width: 18px; height: 18px; cursor: pointer; accent-color: var(--success);" 
                         title="Cochez si un certificat ou justificatif a été validé">
                </td>
                <td>
                  <input type="text" 
                         id="motif_<?= $id ?>"
                         name="absences[<?= $id ?>][motif]" 
                         class="form-control" 
                         value="<?= htmlspecialchars($motif) ?>" 
                         placeholder="Ex: Certificat médical validé..." 
                         <?= (!$justifiee || $statut === 'PRESENT') ? 'disabled' : '' ?>
                         style="font-size: 0.8125rem; padding: 0.45rem 0.75rem; border-radius: var(--radius-md); background: var(--bg-hover); transition: opacity 0.2s ease; <?= (!$justifiee || $statut === 'PRESENT') ? 'opacity: 0.4; cursor: not-allowed;' : '' ?>">
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</form>

<style>
  .attendance-btn {
    background: transparent;
    color: var(--text-muted);
  }
  .attendance-btn:hover {
    color: var(--text-main);
    background: var(--bg-surface-hover);
  }
  .attendance-btn.is-present {
    background-color: #10b981 !important;
    color: #ffffff !important;
    box-shadow: 0 1px 3px rgba(16, 185, 129, 0.4);
  }
  .attendance-btn.is-absent {
    background-color: #ef4444 !important;
    color: #ffffff !important;
    box-shadow: 0 1px 3px rgba(239, 68, 68, 0.4);
  }
</style>

<script>
  function toggleJustificationInput(id) {
    const chk = document.getElementById('justifiee_' + id);
    const motifInput = document.getElementById('motif_' + id);
    if (!chk || !motifInput) return;

    if (chk.checked && !chk.disabled) {
      motifInput.disabled = false;
      motifInput.style.opacity = '1';
      motifInput.style.cursor = 'text';
      motifInput.focus();
    } else {
      motifInput.disabled = true;
      motifInput.style.opacity = '0.4';
      motifInput.style.cursor = 'not-allowed';
      motifInput.value = '';
    }
  }

  function setAttendanceStatus(id, newStatus, clickedBtn) {
    const hiddenInput = document.getElementById('statut_input_<?= $id ?? '' ?>' ? document.getElementById('statut_input_' + id) : null);
    if (hiddenInput) {
      hiddenInput.value = newStatus;
    }

    const group = clickedBtn.closest('.attendance-toggle-group');
    group.querySelectorAll('.attendance-btn').forEach(btn => {
      btn.classList.remove('is-present', 'is-absent');
    });

    const chk = document.getElementById('justifiee_' + id);
    const motifInput = document.getElementById('motif_' + id);

    if (newStatus === 'PRESENT') {
      clickedBtn.classList.add('is-present');
      if (chk) {
        chk.checked = false;
        chk.disabled = true;
      }
      if (motifInput) {
        motifInput.disabled = true;
        motifInput.style.opacity = '0.4';
        motifInput.style.cursor = 'not-allowed';
        motifInput.value = '';
      }
    } else if (newStatus === 'ABSENT') {
      clickedBtn.classList.add('is-absent');
      if (chk) {
        chk.disabled = false;
      }
      toggleJustificationInput(id);
    }
  }
</script>
