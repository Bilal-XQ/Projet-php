<?php
use App\Utils\Icon;
?>
<div style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem;">
  <div>
    <h1 style="font-size: 1.5rem; font-weight: 700; letter-spacing: -0.025em; color: var(--text-main); margin-bottom: 0.25rem;">
      Gestion des Filières Pédagogiques
    </h1>
    <p style="color: var(--text-muted); font-size: 0.875rem;">
      Programmes de formation, cursus du département et volumes d'inscriptions.
    </p>
  </div>
  <button class="btn btn-primary" data-modal-open="createFiliereModal">
    <?= Icon::get('plus', 16) ?>
    <span>Nouvelle Filière</span>
  </button>
</div>

<!-- Table Card -->
<div class="card">
  <div class="card-header">
    <div>
      <span class="card-title">
        <?= Icon::get('landmark', 18) ?>
        <span>Cursus Accrédités</span>
      </span>
      <span class="card-desc" style="display: block; margin-top: 0.25rem;">
        Total : <?= count($filieres) ?> filière(s) enregistrée(s)
      </span>
    </div>
    <span class="badge badge-neutral"><?= count($filieres) ?> Filières</span>
  </div>

  <?php if (empty($filieres)): ?>
    <div style="text-align: center; padding: 3rem 1rem; color: var(--text-muted); font-size: 0.875rem;">
      <div style="margin-bottom: 0.5rem; color: var(--text-subtle);"><?= Icon::get('landmark', 32) ?></div>
      Aucune filière configurée pour le moment.
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
            <th style="min-width: 170px;">Intitulé du Cursus</th>
            <th style="min-width: 200px;">Description</th>
            <th style="min-width: 90px;">Modules</th>
            <th style="min-width: 110px;">Étudiants Inscrits</th>
            <th style="min-width: 120px; text-align: right;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($filieres as $f): ?>
            <tr>
              <td><code><?= htmlspecialchars($f['code']) ?></code></td>
              <td style="font-weight: 600;"><?= htmlspecialchars($f['intitule']) ?></td>
              <td style="color: var(--text-muted); font-size: 0.8125rem; max-width: 320px;">
                <?= htmlspecialchars($f['description'] ?? 'Aucune description spécifiée.') ?>
              </td>
              <td>
                <span class="badge badge-neutral">
                  <?= Icon::get('book-open', 13) ?>
                  <span><?= (int)$f['total_modules'] ?> modules</span>
                </span>
              </td>
              <td>
                <span class="badge badge-neutral">
                  <?= Icon::get('users', 13) ?>
                  <span><?= (int)$f['total_etudiants'] ?> étudiants</span>
                </span>
              </td>
              <td style="text-align: right;">
                <div style="display: inline-flex; gap: 0.35rem;">
                  <button 
                    type="button" 
                    class="btn btn-outline btn-sm edit-filiere-btn"
                    data-id="<?= $f['idFiliere'] ?>"
                    data-code="<?= htmlspecialchars($f['code']) ?>"
                    data-intitule="<?= htmlspecialchars($f['intitule']) ?>"
                    data-description="<?= htmlspecialchars($f['description'] ?? '') ?>"
                    title="Modifier la filière"
                  >
                    <?= Icon::get('pencil', 13) ?>
                    <span>Modifier</span>
                  </button>
                  <form action="/admin/filieres/delete" method="POST" onsubmit="return confirm('Confirmer la suppression définitive de cette filière et de ses modules associés ?');" style="display: inline;">
                    <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                    <input type="hidden" name="idFiliere" value="<?= $f['idFiliere'] ?>">
                    <button type="submit" class="btn btn-destructive btn-sm btn-icon" title="Supprimer la filière">
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

<!-- Modal Création Filière -->
<div id="createFiliereModal" class="modal">
  <div class="modal-backdrop"></div>
  <div class="modal-dialog">
    <div class="modal-header">
      <h3 class="modal-title">
        <?= Icon::get('plus', 18) ?>
        <span>Ajouter une Filière</span>
      </h3>
      <button type="button" class="modal-close-btn" data-modal-close aria-label="Fermer">
        <?= Icon::get('x', 18) ?>
      </button>
    </div>
    <form action="/admin/filieres/create" method="POST">
      <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
      
      <div class="form-group">
        <label for="code" class="form-label">Code Filière (Ex: INFO-L3, DATA-M1)</label>
        <input type="text" id="code" name="code" class="form-control" placeholder="INFO-L3" required>
      </div>

      <div class="form-group">
        <label for="intitule" class="form-label">Intitulé Officiel</label>
        <input type="text" id="intitule" name="intitule" class="form-control" placeholder="Licence Informatique et Ingénierie..." required>
      </div>

      <div class="form-group">
        <label for="description" class="form-label">Description / Objectifs Pédagogiques</label>
        <textarea id="description" name="description" class="form-control" placeholder="Description des compétences visées et des semestres..."></textarea>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-outline" data-modal-close>Annuler</button>
        <button type="submit" class="btn btn-primary">Enregistrer la Filière</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Édition Filière -->
<div id="editFiliereModal" class="modal">
  <div class="modal-backdrop"></div>
  <div class="modal-dialog">
    <div class="modal-header">
      <h3 class="modal-title">
        <?= Icon::get('pencil', 18) ?>
        <span>Modifier la Filière</span>
      </h3>
      <button type="button" class="modal-close-btn" data-modal-close aria-label="Fermer">
        <?= Icon::get('x', 18) ?>
      </button>
    </div>
    <form action="/admin/filieres/edit" method="POST">
      <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
      <input type="hidden" id="edit_idFiliere" name="idFiliere" value="">
      
      <div class="form-group">
        <label for="edit_code" class="form-label">Code Filière</label>
        <input type="text" id="edit_code" name="code" class="form-control" required>
      </div>

      <div class="form-group">
        <label for="edit_intitule" class="form-label">Intitulé Officiel</label>
        <input type="text" id="edit_intitule" name="intitule" class="form-control" required>
      </div>

      <div class="form-group">
        <label for="edit_description" class="form-label">Description / Objectifs</label>
        <textarea id="edit_description" name="description" class="form-control"></textarea>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-outline" data-modal-close>Annuler</button>
        <button type="submit" class="btn btn-primary">Enregistrer les Modifications</button>
      </div>
    </form>
  </div>
</div>

<script>
  document.querySelectorAll('.edit-filiere-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      document.getElementById('edit_idFiliere').value = btn.getAttribute('data-id');
      document.getElementById('edit_code').value = btn.getAttribute('data-code');
      document.getElementById('edit_intitule').value = btn.getAttribute('data-intitule');
      document.getElementById('edit_description').value = btn.getAttribute('data-description');
      window.openModal('editFiliereModal');
    });
  });
</script>
