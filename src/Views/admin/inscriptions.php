<?php
use App\Utils\Icon;
?>
<div style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem;">
  <div>
    <h1 style="font-size: 1.5rem; font-weight: 700; letter-spacing: -0.025em; color: var(--text-main); margin-bottom: 0.25rem;">
      Inscriptions Pédagogiques Annuelles
    </h1>
    <p style="color: var(--text-muted); font-size: 0.875rem;">
      Affectation des étudiants aux filières par année académique et suivi des progressions.
    </p>
  </div>
  <button class="btn btn-primary" data-modal-open="createInscriptionModal">
    <?= Icon::get('plus', 16) ?>
    <span>Inscrire un Étudiant</span>
  </button>
</div>

<!-- Table Card -->
<div class="card">
  <div class="card-header">
    <div>
      <span class="card-title">
        <?= Icon::get('clipboard-list', 18) ?>
        <span>Registre des Inscriptions</span>
      </span>
      <span class="card-desc" style="display: block; margin-top: 0.25rem;">
        Total : <?= count($inscriptions) ?> inscription(s)
      </span>
    </div>
    <span class="badge badge-neutral"><?= count($inscriptions) ?> Enregistrements</span>
  </div>

  <?php if (empty($inscriptions)): ?>
    <div style="text-align: center; padding: 3rem 1rem; color: var(--text-muted); font-size: 0.875rem;">
      <div style="margin-bottom: 0.5rem; color: var(--text-subtle);"><?= Icon::get('clipboard-list', 32) ?></div>
      Aucune inscription enregistrée pour l'instant.
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
            <th style="min-width: 150px;">Étudiant</th>
            <th style="min-width: 100px;">CNE</th>
            <th style="min-width: 120px;">Filière d'Étude</th>
            <th style="min-width: 110px;">Année Universitaire</th>
            <th style="min-width: 110px;">Date Inscription</th>
            <th style="min-width: 110px;">Statut Cursus</th>
            <th style="min-width: 140px; text-align: right;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($inscriptions as $ins): ?>
            <tr>
              <td>
                <div style="font-weight: 600;"><?= htmlspecialchars($ins['etudiant_prenom'] . ' ' . $ins['etudiant_nom']) ?></div>
                <div style="font-size: 0.75rem; color: var(--text-muted);"><?= htmlspecialchars($ins['etudiant_email']) ?></div>
              </td>
              <td><code><?= htmlspecialchars($ins['etudiant_cne']) ?></code></td>
              <td>
                <span class="badge badge-neutral"><?= htmlspecialchars($ins['filiere_code']) ?></span>
                <span style="font-size: 0.75rem; color: var(--text-muted); display: block;"><?= htmlspecialchars($ins['filiere_intitule']) ?></span>
              </td>
              <td>
                <span class="badge badge-neutral">
                  <?= Icon::get('calendar', 12) ?>
                  <span><?= htmlspecialchars($ins['anneeUniversitaire']) ?></span>
                </span>
              </td>
              <td style="color: var(--text-muted); font-size: 0.8125rem;">
                <?= date('d/m/Y', strtotime($ins['dateInscription'])) ?>
              </td>
              <td>
                <?php if ($ins['statut'] === 'VALIDE'): ?>
                  <span class="badge badge-success">Validé</span>
                <?php elseif ($ins['statut'] === 'AJOURNE'): ?>
                  <span class="badge badge-destructive">Ajourné</span>
                <?php else: ?>
                  <span class="badge badge-warning">En cours</span>
                <?php endif; ?>
              </td>
              <td style="text-align: right;">
                <div style="display: inline-flex; gap: 0.35rem; align-items: center;">
                  <button 
                    type="button" 
                    class="btn btn-outline btn-sm edit-inscription-btn"
                    data-id="<?= $ins['idInscription'] ?>"
                    data-etudiant="<?= htmlspecialchars($ins['etudiant_prenom'] . ' ' . $ins['etudiant_nom']) ?>"
                    data-filiere="<?= $ins['idFiliere'] ?>"
                    data-annee="<?= htmlspecialchars($ins['anneeUniversitaire']) ?>"
                    data-statut="<?= htmlspecialchars($ins['statut']) ?>"
                    title="Modifier l'inscription"
                  >
                    <?= Icon::get('pencil', 13) ?>
                    <span>Modifier</span>
                  </button>
                  <form action="/admin/inscriptions/delete" method="POST" onsubmit="return confirm('Confirmer la suppression de cette inscription ?');" style="display: inline;">
                    <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                    <input type="hidden" name="idInscription" value="<?= $ins['idInscription'] ?>">
                    <button type="submit" class="btn btn-destructive btn-sm btn-icon" title="Supprimer l'inscription">
                      <?= Icon::get('trash-2', 13) ?>
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<!-- Modal Inscription Étudiant -->
<div id="createInscriptionModal" class="modal">
  <div class="modal-backdrop"></div>
  <div class="modal-dialog">
    <div class="modal-header">
      <h3 class="modal-title">
        <?= Icon::get('plus', 18) ?>
        <span>Inscrire un Étudiant</span>
      </h3>
      <button type="button" class="modal-close-btn" data-modal-close aria-label="Fermer">
        <?= Icon::get('x', 18) ?>
      </button>
    </div>
    <form action="/admin/inscriptions/create" method="POST">
      <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
      
      <div class="form-group">
        <label for="idEtudiant" class="form-label">Sélectionner l'Étudiant</label>
        <select id="idEtudiant" name="idEtudiant" class="form-control" required>
          <?php foreach ($etudiants as $etu): ?>
            <option value="<?= $etu['idUtilisateur'] ?>">
              <?= htmlspecialchars($etu['prenom'] . ' ' . $etu['nom']) ?> (CNE: <?= htmlspecialchars($etu['cne'] ?? '') ?>) — <?= htmlspecialchars($etu['email']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group">
        <label for="ins_idFiliere" class="form-label">Filière d'Affectation</label>
        <select id="ins_idFiliere" name="idFiliere" class="form-control" required>
          <?php foreach ($filieres as $f): ?>
            <option value="<?= $f['idFiliere'] ?>">
              <?= htmlspecialchars($f['code']) ?> — <?= htmlspecialchars($f['intitule']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="anneeUniversitaire" class="form-label">Année Universitaire</label>
          <input type="text" id="anneeUniversitaire" name="anneeUniversitaire" class="form-control" value="2026-2027" placeholder="2026-2027" required>
        </div>
        <div class="form-group">
          <label for="dateInscription" class="form-label">Date d'Inscription</label>
          <input type="date" id="dateInscription" name="dateInscription" class="form-control" value="<?= date('Y-m-d') ?>" required>
        </div>
      </div>

      <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.5rem;">
        * La contrainte d'unicité vérifie qu'un étudiant n'est pas déjà inscrit pour cette même année académique.
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-outline" data-modal-close>Annuler</button>
        <button type="submit" class="btn btn-primary">Valider l'Inscription</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Modification Inscription -->
<div id="editInscriptionModal" class="modal">
  <div class="modal-backdrop"></div>
  <div class="modal-dialog">
    <div class="modal-header">
      <h3 class="modal-title">
        <?= Icon::get('pencil', 18) ?>
        <span>Modifier l'Inscription Universitaire</span>
      </h3>
      <button type="button" class="modal-close-btn" data-modal-close aria-label="Fermer">
        <?= Icon::get('x', 18) ?>
      </button>
    </div>
    <form action="/admin/inscriptions/edit" method="POST">
      <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
      <input type="hidden" id="edit_idInscription" name="idInscription" value="">
      
      <div class="form-group">
        <label class="form-label">Étudiant</label>
        <input type="text" id="edit_etudiant_nom" class="form-control" readonly style="opacity: 0.8; cursor: not-allowed;">
      </div>

      <div class="form-group">
        <label for="edit_ins_idFiliere" class="form-label">Filière d'Étude</label>
        <select id="edit_ins_idFiliere" name="idFiliere" class="form-control" required>
          <?php foreach ($filieres as $f): ?>
            <option value="<?= $f['idFiliere'] ?>">
              <?= htmlspecialchars($f['code']) ?> — <?= htmlspecialchars($f['intitule']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="edit_anneeUniversitaire" class="form-label">Année Universitaire</label>
          <input type="text" id="edit_anneeUniversitaire" name="anneeUniversitaire" class="form-control" required>
        </div>
        <div class="form-group">
          <label for="edit_statut" class="form-label">Statut du Cursus</label>
          <select id="edit_statut" name="statut" class="form-control" required>
            <option value="EN_COURS">En cours</option>
            <option value="VALIDE">Validé</option>
            <option value="AJOURNE">Ajourné</option>
          </select>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-outline" data-modal-close>Annuler</button>
        <button type="submit" class="btn btn-primary">Enregistrer les Modifications</button>
      </div>
    </form>
  </div>
</div>

<script>
  document.querySelectorAll('.edit-inscription-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      const id = btn.getAttribute('data-id');
      const etudiant = btn.getAttribute('data-etudiant');
      const filiere = btn.getAttribute('data-filiere');
      const annee = btn.getAttribute('data-annee');
      const statut = btn.getAttribute('data-statut');

      document.getElementById('edit_idInscription').value = id;
      document.getElementById('edit_etudiant_nom').value = etudiant;
      document.getElementById('edit_ins_idFiliere').value = filiere;
      document.getElementById('edit_anneeUniversitaire').value = annee;
      document.getElementById('edit_statut').value = statut;

      const modal = document.getElementById('editInscriptionModal');
      if (modal) modal.classList.add('active');
    });
  });
</script>
