<?php
use App\Utils\Icon;
?>
<div style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem;">
  <div>
    <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
      <a href="/enseignant/modules" class="btn btn-outline btn-sm" title="Retour aux modules">
        <?= Icon::get('chevron-left', 14) ?>
      </a>
      <h1 style="font-size: 1.5rem; font-weight: 700; letter-spacing: -0.025em; color: var(--text-main);">
        Séances & Feuille d'Assiduité — <?= htmlspecialchars($module['code']) ?>
      </h1>
    </div>
    <p style="color: var(--text-muted); font-size: 0.875rem;">
      Module : <strong><?= htmlspecialchars($module['intitule']) ?></strong> • 
      Planification des séances de cours, TD et TP.
    </p>
  </div>

  <button class="btn btn-primary" data-modal-open="createSeanceModal">
    <?= Icon::get('plus', 16) ?>
    <span>Planifier une Séance</span>
  </button>
</div>

<!-- Table Card -->
<div class="card">
  <div class="card-header">
    <div>
      <span class="card-title">
        <?= Icon::get('clock', 18) ?>
        <span>Historique & Calendrier des Séances</span>
      </span>
      <span class="card-desc" style="display: block; margin-top: 0.25rem;">
        Total : <?= count($seances) ?> séance(s) enregistrée(s)
      </span>
    </div>
    <span class="badge badge-neutral"><?= count($seances) ?> Séances</span>
  </div>

  <?php if (empty($seances)): ?>
    <div style="text-align: center; padding: 3rem 1rem; color: var(--text-muted); font-size: 0.875rem;">
      <div style="margin-bottom: 0.5rem; color: var(--text-subtle);"><?= Icon::get('clock', 32) ?></div>
      Aucune séance n'est encore programmée pour ce module.
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
            <th style="min-width: 120px;">Date Séance</th>
            <th style="min-width: 140px;">Créneau Horaire</th>
            <th style="min-width: 90px;">Type</th>
            <th style="min-width: 180px;">Bilan d'Émargement</th>
            <th style="min-width: 140px; text-align: right;">Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($seances as $s): ?>
            <tr>
              <td style="font-weight: 600;">
                <?= date('d/m/Y', strtotime($s['dateSeance'])) ?>
              </td>
              <td>
                <span class="badge badge-neutral">
                  <?= Icon::get('clock', 12) ?>
                  <span><?= htmlspecialchars(substr($s['heureDebut'], 0, 5)) ?> — <?= htmlspecialchars(substr($s['heureFin'], 0, 5)) ?></span>
                </span>
              </td>
              <td>
                <?php if ($s['typeSeance'] === 'COURS'): ?>
                  <span class="badge badge-neutral">COURS</span>
                <?php elseif ($s['typeSeance'] === 'TD'): ?>
                  <span class="badge badge-warning">TD</span>
                <?php else: ?>
                  <span class="badge badge-success">TP</span>
                <?php endif; ?>
              </td>
              <td>
                <div style="display: flex; gap: 0.4rem; font-size: 0.75rem;">
                  <span class="badge badge-success" title="Présents"><?= (int)$s['total_presents'] ?> Présents</span>
                  <span class="badge badge-destructive" title="Absents"><?= (int)$s['total_absents'] ?> Absents</span>
                  <?php if ((int)$s['total_retards'] > 0): ?>
                    <span class="badge badge-warning" title="Retards"><?= (int)$s['total_retards'] ?> Retards</span>
                  <?php endif; ?>
                </div>
              </td>
              <td style="text-align: right;">
                <a href="/enseignant/seances/<?= $s['idSeance'] ?>/absences" class="btn btn-outline btn-sm">
                  <?= Icon::get('clipboard-list', 13) ?>
                  <span>Feuille d'Assiduité</span>
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<!-- Modal Déclaration Séance -->
<div id="createSeanceModal" class="modal">
  <div class="modal-backdrop"></div>
  <div class="modal-dialog">
    <div class="modal-header">
      <h3 class="modal-title">
        <?= Icon::get('plus', 18) ?>
        <span>Planifier une Séance</span>
      </h3>
      <button type="button" class="modal-close-btn" data-modal-close aria-label="Fermer">
        <?= Icon::get('x', 18) ?>
      </button>
    </div>
    <form action="/enseignant/modules/<?= $module['idModule'] ?>/seances/create" method="POST">
      <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
      
      <div class="form-row">
        <div class="form-group">
          <label for="dateSeance" class="form-label">Date de la Séance</label>
          <input type="date" id="dateSeance" name="dateSeance" class="form-control" value="<?= date('Y-m-d') ?>" required>
        </div>
        <div class="form-group">
          <label for="typeSeance" class="form-label">Type d'Enseignement</label>
          <select id="typeSeance" name="typeSeance" class="form-control" required>
            <option value="COURS">Cours Magistral</option>
            <option value="TD">Travaux Dirigés (TD)</option>
            <option value="TP">Travaux Pratiques (TP)</option>
          </select>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="heureDebut" class="form-label">Heure de Début</label>
          <input type="time" id="heureDebut" name="heureDebut" class="form-control" value="08:30" required>
        </div>
        <div class="form-group">
          <label for="heureFin" class="form-label">Heure de Fin</label>
          <input type="time" id="heureFin" name="heureFin" class="form-control" value="10:30" required>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-outline" data-modal-close>Annuler</button>
        <button type="submit" class="btn btn-primary">Valider la Séance</button>
      </div>
    </form>
  </div>
</div>
