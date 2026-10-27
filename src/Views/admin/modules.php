<?php
use App\Utils\Icon;
?>
<div style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem;">
  <div>
    <h1 style="font-size: 1.5rem; font-weight: 700; letter-spacing: -0.025em; color: var(--text-main); margin-bottom: 0.25rem;">
      Gestion des Modules & Enseignements
    </h1>
    <p style="color: var(--text-muted); font-size: 0.875rem;">
      Unités d'enseignement, semestres (S1-S4), coefficients et affectations professorales.
    </p>
  </div>
  <button class="btn btn-primary" data-modal-open="createModuleModal">
    <?= Icon::get('plus', 16) ?>
    <span>Nouveau Module</span>
  </button>
</div>

<!-- Table Card -->
<div class="card">
  <div class="card-header">
    <div>
      <span class="card-title">
        <?= Icon::get('book-open', 18) ?>
        <span>Catalogue des Modules</span>
      </span>
      <span class="card-desc" style="display: block; margin-top: 0.25rem;">
        Total : <?= count($modules) ?> module(s) déployé(s)
      </span>
    </div>
    <span class="badge badge-neutral"><?= count($modules) ?> Modules</span>
  </div>

  <?php if (empty($modules)): ?>
    <div style="text-align: center; padding: 3rem 1rem; color: var(--text-muted); font-size: 0.875rem;">
      <div style="margin-bottom: 0.5rem; color: var(--text-subtle);"><?= Icon::get('book-open', 32) ?></div>
      Aucun module répertorié. Créez un premier module pour commencer.
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
            <th style="min-width: 90px;">Filière</th>
            <th style="min-width: 80px;">Semestre</th>
            <th style="min-width: 200px;">Enseignant Responsable</th>
            <th style="min-width: 140px; text-align: right;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($modules as $m): ?>
            <tr>
              <td><code><?= htmlspecialchars($m['code']) ?></code></td>
              <td style="font-weight: 600;"><?= htmlspecialchars($m['intitule']) ?></td>
              <td>
                <span class="badge badge-neutral"><?= htmlspecialchars($m['filiere_code']) ?></span>
              </td>
              <td>
                <span class="badge badge-neutral"><?= htmlspecialchars($m['semestre']) ?></span>
              </td>
              <td>
                <?php if (!empty($m['enseignant_nom'])): ?>
                  <div style="display: flex; flex-direction: column;">
                    <span style="font-weight: 500;"><?= htmlspecialchars($m['enseignant_prenom'] . ' ' . $m['enseignant_nom']) ?></span>
                    <span style="font-size: 0.75rem; color: var(--text-muted);">
                      <code><?= htmlspecialchars($m['enseignant_matricule'] ?? '') ?></code> • <?= htmlspecialchars($m['enseignant_specialite'] ?? '') ?>
                    </span>
                  </div>
                <?php else: ?>
                  <span class="badge badge-warning">
                    <?= Icon::get('alert-triangle', 12) ?>
                    <span>Non assigné</span>
                  </span>
                <?php endif; ?>
              </td>
              <td style="text-align: right;">
                <div style="display: inline-flex; gap: 0.35rem;">
                  <button 
                    type="button" 
                    class="btn btn-outline btn-sm assign-teacher-btn"
                    data-id="<?= $m['idModule'] ?>"
                    data-code="<?= htmlspecialchars($m['code']) ?>"
                    data-intitule="<?= htmlspecialchars($m['intitule']) ?>"
                    data-teacher-id="<?= (int)($m['idEnseignant'] ?? 0) ?>"
                    title="Changer l'enseignant responsable"
                  >
                    <?= Icon::get('user-check', 13) ?>
                    <span>Affecter</span>
                  </button>
                  <button 
                    type="button" 
                    class="btn btn-outline btn-sm btn-icon edit-module-btn"
                    data-id="<?= $m['idModule'] ?>"
                    data-code="<?= htmlspecialchars($m['code']) ?>"
                    data-intitule="<?= htmlspecialchars($m['intitule']) ?>"
                    data-semestre="<?= htmlspecialchars($m['semestre']) ?>"
                    data-coefficient="<?= htmlspecialchars((string)$m['coefficient']) ?>"
                    data-filiere-id="<?= $m['idFiliere'] ?>"
                    data-teacher-id="<?= (int)($m['idEnseignant'] ?? 0) ?>"
                    title="Modifier le module"
                  >
                    <?= Icon::get('pencil', 13) ?>
                  </button>
                  <form action="/admin/modules/delete" method="POST" onsubmit="return confirm('Confirmer la suppression de ce module ?');" style="display: inline;">
                    <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                    <input type="hidden" name="idModule" value="<?= $m['idModule'] ?>">
                    <button type="submit" class="btn btn-destructive btn-sm btn-icon" title="Supprimer">
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

<!-- Modal Création Module -->
<div id="createModuleModal" class="modal">
  <div class="modal-backdrop"></div>
  <div class="modal-dialog">
    <div class="modal-header">
      <h3 class="modal-title">
        <?= Icon::get('plus', 18) ?>
        <span>Créer un Nouveau Module</span>
      </h3>
      <button type="button" class="modal-close-btn" data-modal-close aria-label="Fermer">
        <?= Icon::get('x', 18) ?>
      </button>
    </div>
    <form action="/admin/modules/create" method="POST">
      <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
      
      <div class="form-row">
        <div class="form-group">
          <label for="code" class="form-label">Code Module (Ex: ALGO-S1, BDD-S2)</label>
          <input type="text" id="code" name="code" class="form-control" placeholder="BDD-S2" required>
        </div>
        <div class="form-group">
          <label for="semestre" class="form-label">Semestre</label>
          <select id="semestre" name="semestre" class="form-control" required>
            <option value="S1">S1</option>
            <option value="S2">S2</option>
            <option value="S3">S3</option>
            <option value="S4">S4</option>
          </select>
        </div>
      </div>

      <div class="form-group">
        <label for="intitule" class="form-label">Intitulé du Module</label>
        <input type="text" id="intitule" name="intitule" class="form-control" placeholder="Bases de Données & Modélisation Relationnelle" required>
      </div>

      <input type="hidden" id="coefficient" name="coefficient" value="1.0">
      <div class="form-group">
        <label for="idFiliere" class="form-label">Filière de Rattachement</label>
        <select id="idFiliere" name="idFiliere" class="form-control" required>
          <?php foreach ($filieres as $f): ?>
            <option value="<?= $f['idFiliere'] ?>">
              <?= htmlspecialchars($f['code']) ?> — <?= htmlspecialchars($f['intitule']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group">
        <label for="idEnseignant" class="form-label">Enseignant Responsable (Optionnel)</label>
        <select id="idEnseignant" name="idEnseignant" class="form-control">
          <option value="0">-- Aucun (Non assigné) --</option>
          <?php foreach ($enseignants as $ens): ?>
            <option value="<?= $ens['idUtilisateur'] ?>">
              Pr. <?= htmlspecialchars($ens['prenom'] . ' ' . $ens['nom']) ?> (<?= htmlspecialchars($ens['specialite'] ?? 'Math-Info') ?>)
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-outline" data-modal-close>Annuler</button>
        <button type="submit" class="btn btn-primary">Créer le Module</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Modifier Module -->
<div id="editModuleModal" class="modal">
  <div class="modal-backdrop"></div>
  <div class="modal-dialog">
    <div class="modal-header">
      <h3 class="modal-title">
        <?= Icon::get('pencil', 18) ?>
        <span>Modifier le Module</span>
      </h3>
      <button type="button" class="modal-close-btn" data-modal-close aria-label="Fermer">
        <?= Icon::get('x', 18) ?>
      </button>
    </div>
    <form action="/admin/modules/edit" method="POST">
      <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
      <input type="hidden" id="edit_idModule" name="idModule" value="">
      
      <div class="form-row">
        <div class="form-group">
          <label for="edit_code" class="form-label">Code Module</label>
          <input type="text" id="edit_code" name="code" class="form-control" required>
        </div>
        <div class="form-group">
          <label for="edit_semestre" class="form-label">Semestre</label>
          <select id="edit_semestre" name="semestre" class="form-control" required>
            <option value="S1">S1</option>
            <option value="S2">S2</option>
            <option value="S3">S3</option>
            <option value="S4">S4</option>
          </select>
        </div>
      </div>

      <div class="form-group">
        <label for="edit_intitule" class="form-label">Intitulé du Module</label>
        <input type="text" id="edit_intitule" name="intitule" class="form-control" required>
      </div>

      <input type="hidden" id="edit_coefficient" name="coefficient" value="1.0">
      <div class="form-group">
        <label for="edit_filiereId" class="form-label">Filière</label>
        <select id="edit_filiereId" name="idFiliere" class="form-control" required>
          <?php foreach ($filieres as $f): ?>
            <option value="<?= $f['idFiliere'] ?>">
              <?= htmlspecialchars($f['code']) ?> — <?= htmlspecialchars($f['intitule']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group">
        <label for="edit_teacherId" class="form-label">Enseignant Responsable</label>
        <select id="edit_teacherId" name="idEnseignant" class="form-control">
          <option value="0">-- Aucun (Non assigné) --</option>
          <?php foreach ($enseignants as $ens): ?>
            <option value="<?= $ens['idUtilisateur'] ?>">
              Pr. <?= htmlspecialchars($ens['prenom'] . ' ' . $ens['nom']) ?> (<?= htmlspecialchars($ens['specialite'] ?? 'Math-Info') ?>)
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-outline" data-modal-close>Annuler</button>
        <button type="submit" class="btn btn-primary">Enregistrer les Modifications</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Affectation Rapide Enseignant -->
<div id="assignTeacherModal" class="modal">
  <div class="modal-backdrop"></div>
  <div class="modal-dialog">
    <div class="modal-header">
      <h3 class="modal-title">
        <?= Icon::get('user-check', 18) ?>
        <span>Affecter un Enseignant</span>
      </h3>
      <button type="button" class="modal-close-btn" data-modal-close aria-label="Fermer">
        <?= Icon::get('x', 18) ?>
      </button>
    </div>
    <form action="/admin/modules/assign-teacher" method="POST">
      <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
      <input type="hidden" id="assign_idModule" name="idModule" value="">
      
      <p style="font-size: 0.875rem; color: var(--text-muted); margin-bottom: 1rem;">
        Module sélectionné : <strong id="assign_moduleLabel" style="color: var(--text-main);"></strong>
      </p>

      <div class="form-group">
        <label for="assign_idEnseignant" class="form-label">Professeur Responsable</label>
        <select id="assign_idEnseignant" name="idEnseignant" class="form-control" required>
          <option value="0">-- Retirer l'affectation (Non assigné) --</option>
          <?php foreach ($enseignants as $ens): ?>
            <option value="<?= $ens['idUtilisateur'] ?>">
              Pr. <?= htmlspecialchars($ens['prenom'] . ' ' . $ens['nom']) ?> — <?= htmlspecialchars($ens['matricule'] ?? '') ?> (<?= htmlspecialchars($ens['specialite'] ?? 'Math-Info') ?>)
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-outline" data-modal-close>Annuler</button>
        <button type="submit" class="btn btn-primary">Confirmer l'Affectation</button>
      </div>
    </form>
  </div>
</div>

<script>
  document.querySelectorAll('.edit-module-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      document.getElementById('edit_idModule').value = btn.getAttribute('data-id');
      document.getElementById('edit_code').value = btn.getAttribute('data-code');
      document.getElementById('edit_intitule').value = btn.getAttribute('data-intitule');
      document.getElementById('edit_semestre').value = btn.getAttribute('data-semestre');
      document.getElementById('edit_coefficient').value = btn.getAttribute('data-coefficient');
      document.getElementById('edit_filiereId').value = btn.getAttribute('data-filiere-id');
      document.getElementById('edit_teacherId').value = btn.getAttribute('data-teacher-id') || '0';
      window.openModal('editModuleModal');
    });
  });

  document.querySelectorAll('.assign-teacher-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      document.getElementById('assign_idModule').value = btn.getAttribute('data-id');
      document.getElementById('assign_moduleLabel').textContent = `${btn.getAttribute('data-code')} — ${btn.getAttribute('data-intitule')}`;
      document.getElementById('assign_idEnseignant').value = btn.getAttribute('data-teacher-id') || '0';
      window.openModal('assignTeacherModal');
    });
  });
</script>
