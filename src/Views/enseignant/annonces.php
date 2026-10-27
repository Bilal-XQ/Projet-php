<?php
use App\Utils\Icon;
?>
<div style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem;">
  <div>
    <h1 style="font-size: 1.5rem; font-weight: 700; letter-spacing: -0.025em; color: var(--text-main); margin-bottom: 0.25rem;">
      Annonces aux Étudiants
    </h1>
    <p style="color: var(--text-muted); font-size: 0.875rem;">
      Communications ciblées, consignes de travaux pratiques et calendrier des évaluations pour vos modules.
    </p>
  </div>
  <button class="btn btn-primary" data-modal-open="createAnnonceModal">
    <?= Icon::get('plus', 16) ?>
    <span>Publier un Avis</span>
  </button>
</div>

<div style="display: flex; flex-direction: column; gap: 1.25rem;">
  <?php if (empty($annonces)): ?>
    <div class="card" style="text-align: center; padding: 3rem 1rem; color: var(--text-muted); font-size: 0.875rem;">
      <div style="margin-bottom: 0.5rem; color: var(--text-subtle);"><?= Icon::get('megaphone', 32) ?></div>
      Aucune annonce n'est active actuellement pour vos modules.
    </div>
  <?php else: ?>
    <?php foreach ($annonces as $a): ?>
      <div class="card">
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
            <?php if ((int)$a['idAuteur'] === (int)\App\Utils\Auth::id()): ?>
              <button 
                type="button" 
                class="btn btn-outline btn-sm edit-annonce-btn"
                data-id="<?= $a['idAnnonce'] ?>"
                data-titre="<?= htmlspecialchars($a['titre']) ?>"
                data-module="<?= (int)($a['idModule'] ?? 0) ?>"
                data-contenu="<?= htmlspecialchars($a['contenu']) ?>"
                title="Modifier l'annonce"
              >
                <?= Icon::get('pencil', 13) ?>
                <span>Modifier</span>
              </button>
              <form action="/enseignant/annonces/delete" method="POST" onsubmit="return confirm('Confirmer la suppression définitive de cette annonce ?');" style="display: inline;">
                <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                <input type="hidden" name="idAnnonce" value="<?= $a['idAnnonce'] ?>">
                <button type="submit" class="btn btn-destructive btn-sm btn-icon" title="Supprimer l'annonce">
                  <?= Icon::get('trash-2', 13) ?>
                </button>
              </form>
            <?php endif; ?>
          </div>
        </div>

        <div style="font-size: 0.875rem; color: var(--text-main); line-height: 1.6; white-space: pre-line; margin-bottom: 1rem;">
          <?= htmlspecialchars($a['contenu']) ?>
        </div>

        <div style="font-size: 0.75rem; color: var(--text-muted); display: flex; align-items: center; gap: 0.5rem; padding-top: 0.5rem; border-top: 1px solid var(--border);">
          <span>Auteur : <strong><?= htmlspecialchars($a['auteur_prenom'] . ' ' . $a['auteur_nom']) ?></strong></span>
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
        <span>Publier une Annonce de Module</span>
      </h3>
      <button type="button" class="modal-close-btn" data-modal-close aria-label="Fermer">
        <?= Icon::get('x', 18) ?>
      </button>
    </div>
    <form action="/enseignant/annonces/create" method="POST">
      <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
      
      <div class="form-group">
        <label for="titre" class="form-label">Titre de l'Annonce</label>
        <input type="text" id="titre" name="titre" class="form-control" placeholder="Rendu du TP Noté — Date Limite" required>
      </div>

      <div class="form-group">
        <label for="idModule" class="form-label">Module Ciblé</label>
        <select id="idModule" name="idModule" class="form-control" required>
          <?php foreach ($modules as $m): ?>
            <option value="<?= $m['idModule'] ?>">
              <?= htmlspecialchars($m['code']) ?> — <?= htmlspecialchars($m['intitule']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group">
        <label for="contenu" class="form-label">Contenu du Message</label>
        <textarea id="contenu" name="contenu" class="form-control" style="min-height: 120px;" placeholder="Détaillez les consignes et modalités..." required></textarea>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-outline" data-modal-close>Annuler</button>
        <button type="submit" class="btn btn-primary">Diffuser l'Avis</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Modification Annonce Enseignant -->
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
    <form action="/enseignant/annonces/edit" method="POST">
      <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
      <input type="hidden" id="edit_idAnnonce" name="idAnnonce" value="">
      
      <div class="form-group">
        <label for="edit_titre" class="form-label">Titre de l'Annonce</label>
        <input type="text" id="edit_titre" name="titre" class="form-control" required>
      </div>

      <div class="form-group">
        <label for="edit_idModule" class="form-label">Module Ciblé</label>
        <select id="edit_idModule" name="idModule" class="form-control" required>
          <?php foreach ($modules as $m): ?>
            <option value="<?= $m['idModule'] ?>">
              <?= htmlspecialchars($m['code']) ?> — <?= htmlspecialchars($m['intitule']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group">
        <label for="edit_contenu" class="form-label">Contenu du Message</label>
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
  document.querySelectorAll('.edit-annonce-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      const id = btn.getAttribute('data-id');
      const titre = btn.getAttribute('data-titre');
      const mod = btn.getAttribute('data-module');
      const contenu = btn.getAttribute('data-contenu');

      document.getElementById('edit_idAnnonce').value = id;
      document.getElementById('edit_titre').value = titre;
      document.getElementById('edit_idModule').value = mod;
      document.getElementById('edit_contenu').value = contenu;

      const modal = document.getElementById('editAnnonceModal');
      if (modal) modal.classList.add('active');
    });
  });
</script>
