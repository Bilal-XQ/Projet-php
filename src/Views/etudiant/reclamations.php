<?php
declare(strict_types=1);

use App\Utils\Icon;
?>

<div class="content-header">
  <div class="header-titles">
    <h1 class="page-title">Mes Réclamations</h1>
    <p class="page-subtitle">Déposez une demande ou contestation et suivez l'état de traitement par le département.</p>
  </div>
  <div class="header-actions">
    <button type="button" class="btn btn-primary" data-modal-open="createReclamationModal">
      <?= Icon::get('plus', 16) ?>
      <span>Nouvelle Réclamation</span>
    </button>
  </div>
</div>

<!-- Modal Déposer une Réclamation -->
<div id="createReclamationModal" class="modal">
  <div class="modal-backdrop" data-modal-close></div>
  <div class="modal-dialog">
    <div class="modal-header">
      <h3 class="modal-title">
        <?= Icon::get('message-square', 18) ?>
        <span>Déposer une Réclamation</span>
      </h3>
      <button type="button" class="modal-close-btn" data-modal-close aria-label="Fermer">
        <?= Icon::get('x', 18) ?>
      </button>
    </div>
    <form action="/etudiant/reclamations/create" method="POST">
      <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
      
      <div class="form-group" style="margin-bottom: 1rem;">
        <label for="motif_type" class="form-label">Motif de la Réclamation</label>
        <select id="motif_type" name="motif_type" class="form-control" required onchange="toggleMotifCustom(this.value)">
          <option value="" disabled selected>-- Sélectionnez un motif --</option>
          <option value="Contestation de note ou d'évaluation">Contestation de note ou d'évaluation</option>
          <option value="Justification ou rectification d'absence">Justification ou rectification d'absence</option>
          <option value="Conflit d'emploi du temps ou de séance">Conflit d'emploi du temps ou de séance</option>
          <option value="Demande de document académique / attestation">Demande de document académique / attestation</option>
          <option value="Problème technique ou identifiants">Problème technique ou identifiants</option>
          <option value="Autre">Autre motif personnalisé...</option>
        </select>
      </div>

      <div id="customMotifWrapper" class="form-group" style="display: none; margin-bottom: 1rem;">
        <label for="motif_custom" class="form-label">Précisez votre motif personnalisé</label>
        <input type="text" id="motif_custom" name="motif_custom" class="form-control" placeholder="Ex: Changement de groupe de TD, problème de stage...">
      </div>

      <div class="form-group" style="margin-bottom: 1rem;">
        <label for="objet" class="form-label">Objet / Titre résumé</label>
        <input type="text" id="objet" name="objet" class="form-control" placeholder="Ex: Erreur de saisie note TP Noté Backend" required>
      </div>

      <div class="form-group" style="margin-bottom: 1.5rem;">
        <label for="message" class="form-label">Détails de la Réclamation</label>
        <textarea id="message" name="message" class="form-control" rows="5" placeholder="Décrivez clairement votre situation, les circonstances et toute précision utile..." required></textarea>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-outline" data-modal-close>Annuler</button>
        <button type="submit" class="btn btn-primary">Transmettre la Réclamation</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Visualisation Détails & Réponse -->
<div id="viewReclamationModal" class="modal">
  <div class="modal-backdrop" data-modal-close></div>
  <div class="modal-dialog">
    <div class="modal-header">
      <h3 class="modal-title">
        <?= Icon::get('file-text', 18) ?>
        <span id="viewModalTitle">Détails de la Réclamation</span>
      </h3>
      <button type="button" class="modal-close-btn" data-modal-close aria-label="Fermer">
        <?= Icon::get('x', 18) ?>
      </button>
    </div>
    <div style="padding: 1.25rem; display: flex; flex-direction: column; gap: 1rem;">
      <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem;">
        <div>
          <span style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; font-weight: 600;">Motif</span>
          <div id="viewModalMotif" style="font-weight: 600; color: var(--text-main);"></div>
        </div>
        <div id="viewModalBadge"></div>
      </div>

      <div>
        <span style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; font-weight: 600;">Votre Message</span>
        <div id="viewModalMessage" style="margin-top: 0.35rem; padding: 0.75rem; background: var(--bg-hover); border: 1px solid var(--border-color); border-radius: var(--radius-sm); font-size: 0.875rem; line-height: 1.5; white-space: pre-wrap;"></div>
      </div>

      <div id="viewModalReponseSection">
        <span style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; font-weight: 600;">Réponse du Département</span>
        <div id="viewModalReponse" style="margin-top: 0.35rem; padding: 0.75rem; background: var(--bg-surface-hover); border: 1px solid var(--border-color); border-radius: var(--radius-sm); font-size: 0.875rem; line-height: 1.5; white-space: pre-wrap;"></div>
        <div id="viewModalReponseMeta" style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.35rem;"></div>
      </div>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-outline" data-modal-close>Fermer</button>
    </div>
  </div>
</div>

<!-- Liste des Réclamations -->
<div class="card">
  <div class="card-header">
    <div style="display: flex; align-items: center; gap: 0.5rem;">
      <?= Icon::get('message-square', 18) ?>
      <span class="card-title">Historique de vos Demandes</span>
    </div>
    <span class="badge badge-neutral"><?= count($reclamations) ?> réclamation(s)</span>
  </div>

  <?php if (empty($reclamations)): ?>
    <div style="text-align: center; padding: 3rem 1rem; color: var(--text-muted); font-size: 0.875rem;">
      <div style="margin-bottom: 0.75rem; color: var(--text-subtle);"><?= Icon::get('message-square', 32) ?></div>
      Vous n'avez déposé aucune réclamation pour le moment.
    </div>
  <?php else: ?>
    <div class="table-wrapper">
      <table>
        <thead>
          <tr>
            <th>Date</th>
            <th>Objet</th>
            <th>Motif</th>
            <th>Statut</th>
            <th>Traitement</th>
            <th style="text-align: right;">Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($reclamations as $rec): ?>
            <tr>
              <td style="font-family: ui-monospace, monospace; font-size: 0.8125rem; color: var(--text-muted); white-space: nowrap;">
                <?= date('d/m/Y H:i', strtotime($rec['dateCreation'])) ?>
              </td>
              <td style="max-width: 280px;">
                <span style="font-weight: 600; color: var(--text-main); display: block; word-break: break-word;">
                  <?= htmlspecialchars($rec['objet']) ?>
                </span>
                <span style="font-size: 0.75rem; color: var(--text-muted); display: block; word-break: break-word;">
                  <?= htmlspecialchars($rec['message']) ?>
                </span>
              </td>
              <td>
                <span class="badge badge-neutral" style="font-size: 0.75rem; white-space: normal; line-height: 1.3;">
                  <?= htmlspecialchars($rec['motif']) ?>
                </span>
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
              <td style="max-width: 220px;">
                <?php if (!empty($rec['reponse'])): ?>
                  <span style="font-size: 0.8125rem; color: var(--color-success); font-weight: 500;">
                    <?= Icon::get('check', 13) ?> Répondu
                  </span>
                  <div style="font-size: 0.75rem; color: var(--text-muted);">
                    Par Pr. <?= htmlspecialchars($rec['repondeur_prenom'] . ' ' . $rec['repondeur_nom']) ?>
                  </div>
                <?php else: ?>
                  <span style="font-size: 0.8125rem; color: var(--text-muted);">En cours d'examen</span>
                <?php endif; ?>
              </td>
              <td style="text-align: right; white-space: nowrap;">
                <button type="button" 
                        class="btn btn-outline btn-sm"
                        onclick="showReclamationDetails(<?= htmlspecialchars(json_encode($rec), ENT_QUOTES, 'UTF-8') ?>)">
                  <?= Icon::get('file-text', 13) ?>
                  <span>Voir</span>
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
function toggleMotifCustom(val) {
  const customWrapper = document.getElementById('customMotifWrapper');
  const customInput = document.getElementById('motif_custom');
  if (val === 'Autre') {
    customWrapper.style.display = 'block';
    customInput.required = true;
    customInput.focus();
  } else {
    customWrapper.style.display = 'none';
    customInput.required = false;
  }
}

function showReclamationDetails(rec) {
  document.getElementById('viewModalTitle').textContent = rec.objet;
  document.getElementById('viewModalMotif').textContent = rec.motif;
  document.getElementById('viewModalMessage').textContent = rec.message;

  const badgeEl = document.getElementById('viewModalBadge');
  if (rec.statut === 'EN_ATTENTE') {
    badgeEl.innerHTML = '<span class="badge badge-warning">En Attente</span>';
  } else if (rec.statut === 'EN_COURS') {
    badgeEl.innerHTML = '<span class="badge badge-info">En Cours</span>';
  } else if (rec.statut === 'RESOLUE') {
    badgeEl.innerHTML = '<span class="badge badge-success">Résolue</span>';
  } else if (rec.statut === 'REJETEE') {
    badgeEl.innerHTML = '<span class="badge badge-danger">Rejetée</span>';
  }

  const reponseSection = document.getElementById('viewModalReponseSection');
  if (rec.reponse && rec.reponse.trim() !== '') {
    reponseSection.style.display = 'block';
    document.getElementById('viewModalReponse').textContent = rec.reponse;
    let meta = '';
    if (rec.repondeur_nom) {
      meta += 'Répondu par ' + (rec.repondeur_role === 'ENSEIGNANT' ? 'Pr. ' : '') + rec.repondeur_prenom + ' ' + rec.repondeur_nom;
    }
    if (rec.dateReponse) {
      meta += ' le ' + rec.dateReponse;
    }
    document.getElementById('viewModalReponseMeta').textContent = meta;
  } else {
    reponseSection.style.display = 'none';
  }

  const modal = document.getElementById('viewReclamationModal');
  modal.classList.add('active');
}
</script>

