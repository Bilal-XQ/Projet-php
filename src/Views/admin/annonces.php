<?php
use App\Utils\Icon;
?>
<div style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem;">
  <div>
    <h1 style="font-size: 1.5rem; font-weight: 700; letter-spacing: -0.025em; color: var(--text-main); margin-bottom: 0.25rem;">
      Communication & Annonces Pédagogiques
    </h1>
    <p style="color: var(--text-muted); font-size: 0.875rem;">
      Publication des avis officiels, dates d'examens, et communications départementales.
    </p>
  </div>
  <button class="btn btn-primary" data-modal-open="createAnnonceModal">
    <?= Icon::get('plus', 16) ?>
    <span>Publier une Annonce</span>
  </button>
</div>

<!-- Liste des Annonces -->
<div style="display: flex; flex-direction: column; gap: 1.25rem;">
  <?php if (empty($annonces)): ?>
    <div class="card" style="text-align: center; padding: 3rem 1rem; color: var(--text-muted); font-size: 0.875rem;">
      <div style="margin-bottom: 0.5rem; color: var(--text-subtle);"><?= Icon::get('megaphone', 32) ?></div>
      Aucune annonce n'a été publiée pour le moment.
    </div>
  <?php else: ?>
    <?php foreach ($annonces as $a): ?>
      <div class="card" style="position: relative;">
        <div class="card-header" style="margin-bottom: 0.75rem; padding-bottom: 0.5rem;">
          <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
            <span style="font-size: 1.05rem; font-weight: 700; color: var(--text-main);">
              <?= htmlspecialchars($a['titre']) ?>
            </span>
            <?php if ($a['portee'] === 'GLOBALE'): ?>
              <span class="badge badge-warning">Avis Global Département</span>
            <?php else: ?>
              <span class="badge badge-neutral">Module : <?= htmlspecialchars($a['module_code'] ?? 'Matière') ?></span>
            <?php endif; ?>
          </div>
          <div style="display: flex; align-items: center; gap: 0.5rem;">
            <span style="font-size: 0.75rem; color: var(--text-muted); margin-right: 0.25rem;">
              <?= date('d/m/Y à H:i', strtotime($a['datePublication'])) ?>
            </span>
            <button 
              type="button" 
              class="btn btn-outline btn-sm edit-annonce-btn"
              data-id="<?= $a['idAnnonce'] ?>"
              data-titre="<?= htmlspecialchars($a['titre']) ?>"
              data-portee="<?= htmlspecialchars($a['portee']) ?>"
              data-module="<?= (int)($a['idModule'] ?? 0) ?>"
              data-contenu="<?= htmlspecialchars($a['contenu']) ?>"
              title="Modifier l'annonce"
            >
              <?= Icon::get('pencil', 13) ?>
              <span>Modifier</span>
            </button>
            <form action="/admin/annonces/delete" method="POST" onsubmit="return confirm('Supprimer définitivement cette annonce ?');" style="display: inline;">
              <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
              <input type="hidden" name="idAnnonce" value="<?= $a['idAnnonce'] ?>">
              <button type="submit" class="btn btn-destructive btn-sm btn-icon" title="Supprimer l'annonce">
                <?= Icon::get('trash-2', 13) ?>
              </button>
            </form>
          </div>
        </div>

        <div style="font-size: 0.875rem; color: var(--text-main); line-height: 1.6; white-space: pre-line; margin-bottom: 1rem;">
          <?= htmlspecialchars($a['contenu']) ?>
        </div>

        <div style="font-size: 0.75rem; color: var(--text-muted); display: flex; align-items: center; gap: 0.5rem; padding-top: 0.5rem; border-top: 1px solid var(--border);">
          <span>Publié par : <strong><?= htmlspecialchars($a['auteur_prenom'] . ' ' . $a['auteur_nom']) ?></strong> (<?= htmlspecialchars($a['auteur_role']) ?>)</span>
          <span>•</span>
          <code><?= htmlspecialchars($a['auteur_email']) ?></code>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

<!-- Modal Publication Annonce -->
<div id="createAnnonceModal" class="modal">
  <div class="modal-backdrop"></div>
  <div class="modal-dialog">
    <div class="modal-header">
      <h3 class="modal-title">
        <?= Icon::get('megaphone', 18) ?>
        <span>Publier une Annonce</span>
      </h3>
      <button type="button" class="modal-close-btn" data-modal-close aria-label="Fermer">
        <?= Icon::get('x', 18) ?>
      </button>
    </div>
    <form action="/admin/annonces/create" method="POST">
      <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
      
      <div class="form-group">
        <label for="titre" class="form-label">Titre de l'Avis</label>
        <input type="text" id="titre" name="titre" class="form-control" placeholder="Calendrier des examens de mi-session S1" required>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="portee" class="form-label">Portée de Diffusion</label>
          <select id="portee" name="portee" class="form-control" required>
            <option value="GLOBALE">Globale (Tout le Département)</option>
            <option value="MODULE">Ciblée sur un Module Spécifique</option>
          </select>
        </div>
        <div class="form-group" id="moduleSelectGroup" style="display: none;">
          <label for="idModule" class="form-label">Module Concerné</label>
          <select id="idModule" name="idModule" class="form-control">
            <?php foreach ($modules as $m): ?>
              <option value="<?= $m['idModule'] ?>">
                <?= htmlspecialchars($m['code']) ?> — <?= htmlspecialchars($m['intitule']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="form-group">
        <label for="contenu" class="form-label">Corps du Message</label>
        <textarea id="contenu" name="contenu" class="form-control" style="min-height: 120px;" placeholder="Détaillez les informations pédagogiques, consignes ou plannings..." required></textarea>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-outline" data-modal-close>Annuler</button>
        <button type="submit" class="btn btn-primary">Diffuser l'Annonce</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Modification Annonce -->
<div id="editAnnonceModal" class="modal">
  <div class="modal-backdrop"></div>
  <div class="modal-dialog">
    <div class="modal-header">
      <h3 class="modal-title">
        <?= Icon::get('pencil', 18) ?>
        <span>Modifier l'Annonce</span>
      </h3>
      <button type="button" class="modal-close-btn" data-modal-close aria-label="Fermer">
        <?= Icon::get('x', 18) ?>
      </button>
    </div>
    <form action="/admin/annonces/edit" method="POST">
      <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
      <input type="hidden" id="edit_idAnnonce" name="idAnnonce" value="">
      
      <div class="form-group">
        <label for="edit_titre" class="form-label">Titre de l'Avis</label>
        <input type="text" id="edit_titre" name="titre" class="form-control" required>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="edit_portee" class="form-label">Portée de Diffusion</label>
          <select id="edit_portee" name="portee" class="form-control" required>
            <option value="GLOBALE">Globale (Tout le Département)</option>
            <option value="MODULE">Ciblée sur un Module Spécifique</option>
          </select>
        </div>
        <div class="form-group" id="edit_moduleSelectGroup" style="display: none;">
          <label for="edit_idModule" class="form-label">Module Concerné</label>
          <select id="edit_idModule" name="idModule" class="form-control">
            <?php foreach ($modules as $m): ?>
              <option value="<?= $m['idModule'] ?>">
                <?= htmlspecialchars($m['code']) ?> — <?= htmlspecialchars($m['intitule']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="form-group">
        <label for="edit_contenu" class="form-label">Corps du Message</label>
        <textarea id="edit_contenu" name="contenu" class="form-control" style="min-height: 120px;" required></textarea>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-outline" data-modal-close>Annuler</button>
        <button type="submit" class="btn btn-primary">Enregistrer les Modifications</button>
      </div>
    </form>
  </div>
</div>

<script>
  const porteeSelect = document.getElementById('portee');
  const moduleGroup = document.getElementById('moduleSelectGroup');

  if (porteeSelect) {
    porteeSelect.addEventListener('change', () => {
      moduleGroup.style.display = porteeSelect.value === 'MODULE' ? 'block' : 'none';
    });
  }

  const editPorteeSelect = document.getElementById('edit_portee');
  const editModuleGroup = document.getElementById('edit_moduleSelectGroup');

  if (editPorteeSelect) {
    editPorteeSelect.addEventListener('change', () => {
      editModuleGroup.style.display = editPorteeSelect.value === 'MODULE' ? 'block' : 'none';
    });
  }

  document.querySelectorAll('.edit-annonce-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      const id = btn.getAttribute('data-id');
      const titre = btn.getAttribute('data-titre');
      const portee = btn.getAttribute('data-portee');
      const mod = btn.getAttribute('data-module');
      const contenu = btn.getAttribute('data-contenu');

      document.getElementById('edit_idAnnonce').value = id;
      document.getElementById('edit_titre').value = titre;
      document.getElementById('edit_portee').value = portee;
      document.getElementById('edit_contenu').value = contenu;

      if (portee === 'MODULE') {
        editModuleGroup.style.display = 'block';
        document.getElementById('edit_idModule').value = mod;
      } else {
        editModuleGroup.style.display = 'none';
      }

      const modal = document.getElementById('editAnnonceModal');
      if (modal) modal.classList.add('active');
    });
  });
</script>
