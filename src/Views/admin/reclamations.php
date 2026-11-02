<?php
declare(strict_types=1);

use App\Utils\Icon;
?>

<div class="content-header">
  <div class="header-titles">
    <h1 class="page-title">Gestion des Réclamations</h1>
    <p class="page-subtitle">Examinez les requêtes des étudiants du département et apportez des réponses officielles.</p>
  </div>
</div>

<!-- Statistiques Rapides -->
<div class="grid grid-cols-4" style="gap: 1rem; margin-bottom: 1.5rem;">
  <div class="card stat-card">
    <div class="stat-header">
      <span class="stat-label">Total Déposées</span>
      <span class="stat-icon"><?= Icon::get('message-square', 16) ?></span>
    </div>
    <div class="stat-value"><?= (int)($stats['total'] ?? 0) ?></div>
  </div>

  <div class="card stat-card">
    <div class="stat-header">
      <span class="stat-label">En Attente</span>
      <span class="stat-icon"><?= Icon::get('clock', 16) ?></span>
    </div>
    <div class="stat-value" style="color: var(--color-warning);"><?= (int)($stats['en_attente'] ?? 0) ?></div>
  </div>

  <div class="card stat-card">
    <div class="stat-header">
      <span class="stat-label">Résolues</span>
      <span class="stat-icon"><?= Icon::get('check', 16) ?></span>
    </div>
    <div class="stat-value" style="color: var(--color-success);"><?= (int)($stats['resolues'] ?? 0) ?></div>
  </div>

  <div class="card stat-card">
    <div class="stat-header">
      <span class="stat-label">Rejetées</span>
      <span class="stat-icon"><?= Icon::get('x', 16) ?></span>
    </div>
    <div class="stat-value" style="color: var(--color-danger);"><?= (int)($stats['rejetees'] ?? 0) ?></div>
  </div>
</div>

<!-- Filtres de Statut -->
<div style="display: flex; gap: 0.5rem; margin-bottom: 1.25rem; flex-wrap: wrap;">
  <a href="/admin/reclamations" class="btn <?= empty($statutActif) ? 'btn-primary' : 'btn-outline' ?> btn-sm">
    Toutes (<?= (int)($stats['total'] ?? 0) ?>)
  </a>
  <a href="/admin/reclamations?statut=EN_ATTENTE" class="btn <?= $statutActif === 'EN_ATTENTE' ? 'btn-primary' : 'btn-outline' ?> btn-sm">
    En Attente (<?= (int)($stats['en_attente'] ?? 0) ?>)
  </a>
  <a href="/admin/reclamations?statut=EN_COURS" class="btn <?= $statutActif === 'EN_COURS' ? 'btn-primary' : 'btn-outline' ?> btn-sm">
    En Cours (<?= (int)($stats['en_cours'] ?? 0) ?>)
  </a>
  <a href="/admin/reclamations?statut=RESOLUE" class="btn <?= $statutActif === 'RESOLUE' ? 'btn-primary' : 'btn-outline' ?> btn-sm">
    Résolues (<?= (int)($stats['resolues'] ?? 0) ?>)
  </a>
  <a href="/admin/reclamations?statut=REJETEE" class="btn <?= $statutActif === 'REJETEE' ? 'btn-primary' : 'btn-outline' ?> btn-sm">
    Rejetées (<?= (int)($stats['rejetees'] ?? 0) ?>)
  </a>
</div>

<!-- Modal Traiter / Répondre à une Réclamation -->
<div id="traiterModal" class="modal">
  <div class="modal-backdrop" data-modal-close></div>
  <div class="modal-dialog">
    <div class="modal-header">
      <h3 class="modal-title">
        <?= Icon::get('message-square', 18) ?>
        <span>Traiter la Réclamation</span>
      </h3>
      <button type="button" class="modal-close-btn" data-modal-close aria-label="Fermer">
        <?= Icon::get('x', 18) ?>
      </button>
    </div>
    <form action="/admin/reclamations/repondre" method="POST">
      <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
      <input type="hidden" id="traiter_idReclamation" name="idReclamation" value="">

      <div style="padding: 1.25rem; display: flex; flex-direction: column; gap: 1rem;">
        <div style="background: var(--bg-hover); padding: 0.875rem; border-radius: var(--radius-sm); border: 1px solid var(--border-color);">
          <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; font-weight: 600;">Étudiant Dépositaire</div>
          <div id="traiter_etudiant" style="font-weight: 600; color: var(--text-main); margin-top: 0.15rem;"></div>
          <div id="traiter_objet" style="font-size: 0.875rem; color: var(--text-muted); margin-top: 0.35rem;"></div>
          <div id="traiter_message" style="margin-top: 0.5rem; font-size: 0.8125rem; line-height: 1.4; color: var(--text-main); white-space: pre-wrap; border-top: 1px dashed var(--border-color); padding-top: 0.5rem;"></div>
        </div>

        <div class="form-group">
          <label for="traiter_statut" class="form-label">Statut de la Décision</label>
          <select id="traiter_statut" name="statut" class="form-control" required>
            <option value="EN_COURS">En Cours de Traitement</option>
            <option value="RESOLUE" selected>Résolue (Favorable)</option>
            <option value="REJETEE">Rejetée (Défavorable)</option>
          </select>
        </div>

        <div class="form-group">
          <label for="traiter_reponse" class="form-label">Réponse officielle à l'étudiant</label>
          <textarea id="traiter_reponse" name="reponse" class="form-control" rows="4" placeholder="Indiquez ici la justification, les actions correctives ou les consignes transmises à l'étudiant..." required></textarea>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-outline" data-modal-close>Annuler</button>
        <button type="submit" class="btn btn-primary">Enregistrer la Réponse</button>
      </div>
    </form>
  </div>
</div>

<!-- Table des Réclamations -->
<div class="card">
  <div class="card-header">
    <div style="display: flex; align-items: center; gap: 0.5rem;">
      <?= Icon::get('message-square', 18) ?>
      <span class="card-title">Liste des Réclamations</span>
    </div>
    <span class="badge badge-neutral"><?= count($reclamations) ?> requête(s)</span>
  </div>

  <?php if (empty($reclamations)): ?>
    <div style="text-align: center; padding: 3rem 1rem; color: var(--text-muted); font-size: 0.875rem;">
      <div style="margin-bottom: 0.75rem; color: var(--text-subtle);"><?= Icon::get('check', 32) ?></div>
      Aucune réclamation trouvée pour ce critère.
    </div>
  <?php else: ?>
    <div class="table-responsive">
      <table class="data-table">
        <thead>
          <tr>
            <th>Date</th>
            <th>Étudiant</th>
            <th>Filière</th>
            <th>Motif & Objet</th>
            <th>Statut</th>
            <th>Dernière Réponse</th>
            <th style="text-align: right;">Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($reclamations as $rec): ?>
            <tr>
              <td style="font-family: ui-monospace, monospace; font-size: 0.8125rem; color: var(--text-muted); white-space: nowrap;">
                <?= date('d/m/Y H:i', strtotime($rec['dateCreation'])) ?>
              </td>
              <td>
                <span style="font-weight: 600; color: var(--text-main); display: block;">
                  <?= htmlspecialchars($rec['etudiant_prenom'] . ' ' . $rec['etudiant_nom']) ?>
                </span>
                <span style="font-size: 0.75rem; color: var(--text-muted); font-family: ui-monospace, monospace;">
                  <?= htmlspecialchars($rec['etudiant_cne'] ?? $rec['etudiant_email']) ?>
                </span>
              </td>
              <td>
                <span class="badge badge-neutral">
                  <?= htmlspecialchars($rec['filiere_code'] ?? 'Tronc Commun') ?>
                </span>
              </td>
              <td>
                <div style="font-weight: 600; color: var(--text-main);">
                  <?= htmlspecialchars($rec['objet']) ?>
                </div>
                <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.2rem;">
                  Motif : <span class="badge badge-neutral" style="font-size: 0.7rem;"><?= htmlspecialchars($rec['motif']) ?></span>
                </div>
              </td>
              <td>
                <?php if ($rec['statut'] === 'EN_ATTENTE'): ?>
                  <span class="badge badge-warning">En Attente</span>
                <?php elseif ($rec['statut'] === 'EN_COURS'): ?>
                  <span class="badge badge-info">En Cours</span>
                <?php elseif ($rec['statut'] === 'RESOLUE'): ?>
                  <span class="badge badge-success">Résolue</span>
                <?php elseif ($rec['statut'] === 'REJETEE'): ?>
                  <span class="badge badge-danger">Rejetée</span>
                <?php endif; ?>
              </td>
              <td>
                <?php if (!empty($rec['reponse'])): ?>
                  <span style="font-size: 0.75rem; color: var(--color-success); font-weight: 600;">Traité</span>
                  <div style="font-size: 0.75rem; color: var(--text-muted); max-width: 220px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                    <?= htmlspecialchars($rec['reponse']) ?>
                  </div>
                <?php else: ?>
                  <span style="font-size: 0.75rem; color: var(--color-warning);">À traiter</span>
                <?php endif; ?>
              </td>
              <td style="text-align: right;">
                <button type="button" 
                        class="btn btn-outline btn-sm"
                        onclick="openTraiterModal(<?= htmlspecialchars(json_encode($rec), ENT_QUOTES, 'UTF-8') ?>)">
                  <?= Icon::get('pencil', 13) ?>
                  <span><?= empty($rec['reponse']) ? 'Traiter' : 'Modifier' ?></span>
                </button>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<script>
function openTraiterModal(rec) {
  document.getElementById('traiter_idReclamation').value = rec.idReclamation;
  document.getElementById('traiter_etudiant').textContent = rec.etudiant_prenom + ' ' + rec.etudiant_nom + ' (' + (rec.etudiant_cne || rec.etudiant_email) + ')';
  document.getElementById('traiter_objet').textContent = 'Objet : ' + rec.objet + ' [' + rec.motif + ']';
  document.getElementById('traiter_message').textContent = rec.message;
  document.getElementById('traiter_statut').value = rec.statut === 'EN_ATTENTE' ? 'RESOLUE' : rec.statut;
  document.getElementById('traiter_reponse').value = rec.reponse || '';

  const modal = document.getElementById('traiterModal');
  modal.classList.add('active');
}
</script>
