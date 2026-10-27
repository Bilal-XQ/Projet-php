<?php
declare(strict_types=1);

use App\Utils\Icon;

$tauxGlobal = number_format((float)$overall['taux_assiduite'], 1);
?>
<div class="content-container">
  <!-- En-tête -->
  <div style="margin-bottom: 1.5rem;">
    <h1 style="font-size: 1.5rem; font-weight: 700; letter-spacing: -0.025em; color: var(--text-main); margin-bottom: 0.25rem;">
      Bilan d'Assiduité & Suivi des Présences
    </h1>
    <p style="color: var(--text-muted); font-size: 0.875rem;">
      Consultez en temps réel vos émargements officiels, vos présences en cours et vos justificatifs d'absence.
    </p>
  </div>

  <!-- KPI Cards Grid -->
  <div class="grid grid-cols-3" style="margin-bottom: 1.5rem;">
    <div class="card stat-card">
      <div class="stat-header">
        <span class="stat-label">Assiduité Globale</span>
        <span class="stat-icon"><?= Icon::get('clock', 16) ?></span>
      </div>
      <div class="stat-value" style="font-size: 1.45rem; font-weight: 800; font-family: ui-monospace, monospace; color: <?= (float)$overall['taux_assiduite'] >= 80 ? 'var(--color-success)' : 'var(--color-danger)' ?>;">
        <?= $tauxGlobal ?> %
      </div>
      <div class="stat-meta">Seuil d'alerte : < 75%</div>
    </div>

    <div class="card stat-card">
      <div class="stat-header">
        <span class="stat-label">Séances Effectuées</span>
        <span class="stat-icon"><?= Icon::get('calendar', 16) ?></span>
      </div>
      <div class="stat-value" style="font-size: 1.45rem; font-weight: 800; font-family: ui-monospace, monospace;">
        <?= $overall['total_seances'] ?>
      </div>
      <div class="stat-meta"><?= $overall['total_presences'] ?> présences confirmées</div>
    </div>

    <div class="card stat-card">
      <div class="stat-header">
        <span class="stat-label">Absences Non Justifiées</span>
        <span class="stat-icon"><?= Icon::get('x', 16) ?></span>
      </div>
      <div class="stat-value" style="font-size: 1.45rem; font-weight: 800; font-family: ui-monospace, monospace; color: var(--color-danger);">
        <?= $overall['non_justifiees'] ?>
      </div>
      <div class="stat-meta">
        <?= $overall['justifiees'] ?> absence(s) justifiée(s)
      </div>
    </div>
  </div>

  <!-- Tableau 1 : Synthèse par Module -->
  <div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-header">
      <div>
        <span class="card-title">
          <?= Icon::get('book-open', 18) ?>
          <span>Taux d'Assiduité par Module d'Enseignement</span>
        </span>
        <span class="card-desc" style="display: block; margin-top: 0.25rem;">
          Ventilation des présences et absences par matière
        </span>
      </div>
    </div>

    <?php if (empty($byModule)): ?>
      <div style="padding: 2.5rem; text-align: center; color: var(--text-muted); font-size: 0.875rem;">
        Aucun module rattaché à votre compte.
      </div>
    <?php else: ?>
      <div class="table-scroll-hint">
        <?= Icon::get('arrow-right', 12) ?>
        <span>Faites glisser horizontalement pour voir toutes les colonnes</span>
      </div>
      <div class="table-wrapper">
        <table class="data-table">
          <thead>
            <tr>
              <th style="min-width: 90px;">Code</th>
              <th style="min-width: 170px;">Intitulé du Module</th>
              <th style="min-width: 70px; text-align: center;">Sem.</th>
              <th style="min-width: 150px;">Enseignant Responsable</th>
              <th style="min-width: 90px; text-align: center;">Total Séances</th>
              <th style="min-width: 80px; text-align: center;">Absences</th>
              <th style="min-width: 120px; text-align: right;">Taux d'Assiduité</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($byModule as $m): ?>
              <?php 
                $s = $m['summary']; 
                $taux = (float)$m['taux_assiduite'];
              ?>
              <tr>
                <td><span class="badge-code"><?= htmlspecialchars($m['module_code']) ?></span></td>
                <td>
                  <div style="font-weight: 600; color: var(--text-main); font-size: 0.875rem;">
                    <?= htmlspecialchars($m['module_intitule']) ?>
                  </div>
                </td>
                <td style="text-align: center; font-family: ui-monospace, monospace;"><?= htmlspecialchars($m['semestre']) ?></td>
                <td style="font-size: 0.8125rem; color: var(--text-muted);">
                  <?= !empty($m['enseignant_nom']) ? htmlspecialchars($m['enseignant_prenom'] . ' ' . $m['enseignant_nom']) : '<span style="color: var(--text-subtle);">Non assigné</span>' ?>
                </td>
                <td style="text-align: center; font-family: ui-monospace, monospace; font-weight: 600;">
                  <?= $s['total_seances'] ?>
                </td>
                <td style="text-align: center; font-family: ui-monospace, monospace; color: <?= $s['total_absences'] > 0 ? 'var(--color-danger)' : 'var(--text-muted)' ?>;">
                  <?= $s['total_absences'] ?>
                </td>
                <td style="text-align: right; font-family: ui-monospace, monospace; font-weight: 700; color: <?= $taux >= 80 ? 'var(--color-success)' : ($taux >= 60 ? 'var(--color-warning)' : 'var(--color-danger)') ?>;">
                  <?= number_format($taux, 1) ?> %
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>

  <!-- Tableau 2 : Journal Détaillé des Absences -->
  <div class="card">
    <div class="card-header">
      <div>
        <span class="card-title">
          <?= Icon::get('clock', 18) ?>
          <span>Journal Historique des Absences</span>
        </span>
        <span class="card-desc" style="display: block; margin-top: 0.25rem;">
          Liste chronologique de tous les incidents d'émargement enregistrés
        </span>
      </div>
    </div>

    <?php if (empty($detailed)): ?>
      <div style="padding: 2.5rem; text-align: center; color: var(--color-success); font-size: 0.875rem;">
        <div style="margin-bottom: 0.5rem;"><?= Icon::get('check', 32) ?></div>
        Félicitations ! Vous n'avez aucune absence enregistrée à ce jour.
      </div>
    <?php else: ?>
      <div class="table-scroll-hint">
        <?= Icon::get('arrow-right', 12) ?>
        <span>Faites glisser horizontalement pour voir toutes les colonnes</span>
      </div>
      <div class="table-wrapper">
        <table class="data-table">
          <thead>
            <tr>
              <th style="min-width: 120px;">Date de la Séance</th>
              <th style="min-width: 110px;">Horaires</th>
              <th style="min-width: 80px;">Type</th>
              <th style="min-width: 100px;">Module</th>
              <th style="min-width: 120px; text-align: center;">Statut</th>
              <th style="min-width: 110px; text-align: center;">Justification</th>
              <th style="min-width: 150px;">Motif Déclaré</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($detailed as $d): ?>
              <tr>
                <td style="font-weight: 600; font-size: 0.875rem;">
                  <?= date('d/m/Y', strtotime($d['dateSeance'])) ?>
                </td>
                <td style="font-family: ui-monospace, monospace; font-size: 0.8125rem; color: var(--text-muted);">
                  <?= substr($d['heureDebut'], 0, 5) ?> - <?= substr($d['heureFin'], 0, 5) ?>
                </td>
                <td>
                  <span class="badge badge-neutral" style="font-size: 0.75rem;"><?= htmlspecialchars($d['typeSeance']) ?></span>
                </td>
                <td>
                  <span class="badge-code"><?= htmlspecialchars($d['module_code']) ?></span>
                  <span style="font-size: 0.8125rem; color: var(--text-muted); margin-left: 0.25rem;"><?= htmlspecialchars($d['module_intitule']) ?></span>
                </td>
                <td style="text-align: center;">
                  <span class="badge badge-destructive">Absent</span>
                </td>
                <td style="text-align: center;">
                  <?php if ((int)$d['justifiee'] === 1): ?>
                    <span class="badge badge-success"><?= Icon::get('check', 11) ?> Justifiée</span>
                  <?php else: ?>
                    <span class="badge badge-neutral"><?= Icon::get('x', 11) ?> Non justifiée</span>
                  <?php endif; ?>
                </td>
                <td style="font-size: 0.8125rem; color: var(--text-muted);">
                  <?= !empty($d['motif']) ? htmlspecialchars($d['motif']) : '<span style="color: var(--text-subtle); font-style: italic;">Aucun motif fourni</span>' ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>
