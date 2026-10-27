<?php
declare(strict_types=1);

use App\Utils\Icon;

$gpaVal = $gpa['moyenne'] !== null ? number_format((float)$gpa['moyenne'], 2) : '—';
$tauxAssiduite = number_format((float)$attendance['taux_assiduite'], 1);
?>
<div class="content-container">
  <!-- En-tête de section -->
  <div style="margin-bottom: 2rem;">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem;">
      <div>
        <h1 style="font-size: 1.5rem; font-weight: 700; letter-spacing: -0.025em; color: var(--text-main); margin-bottom: 0.25rem;">
          Portail Étudiant — Tableau de bord
        </h1>
        <p style="color: var(--text-muted); font-size: 0.875rem;">
          Bienvenue, <strong><?= htmlspecialchars($user->getNomComplet()) ?></strong>. Consultez votre progression académique et votre bilan d'assiduité.
        </p>
      </div>
      <div style="display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: center;">
        <span class="badge badge-neutral" style="font-family: ui-monospace, monospace;">
          <?= Icon::get('shield-check', 13) ?>
          <span>CNE : <?= htmlspecialchars($user->getCne() ?: 'N/A') ?></span>
        </span>
        <a href="/etudiant/releve/pdf" target="_blank" class="btn btn-outline btn-sm">
          <?= Icon::get('file-text', 14) ?>
          <span>Télécharger Relevé PDF</span>
        </a>
      </div>
    </div>
  </div>

  <!-- KPI Cards Grid -->
  <div class="grid grid-cols-4" style="margin-bottom: 1.5rem;">
    <!-- Filière & Cursus -->
    <div class="card stat-card">
      <div class="stat-header">
        <span class="stat-label">Filière d'Étude</span>
        <span class="stat-icon"><?= Icon::get('landmark', 16) ?></span>
      </div>
      <div class="stat-value" style="font-size: 1.15rem; font-weight: 700;">
        <?= htmlspecialchars($inscription['filiere_code'] ?? 'Non assignée') ?>
      </div>
      <div class="stat-meta">
        <?= htmlspecialchars($inscription['filiere_intitule'] ?? 'Année 2026-2027') ?>
      </div>
    </div>

    <!-- Moyenne Générale (GPA) -->
    <div class="card stat-card">
      <div class="stat-header">
        <span class="stat-label">Moyenne Générale</span>
        <span class="stat-icon"><?= Icon::get('graduation-cap', 16) ?></span>
      </div>
      <div class="stat-value" style="font-size: 1.35rem; font-weight: 800; font-family: ui-monospace, monospace;">
        <?= $gpaVal ?> <span style="font-size: 0.875rem; font-weight: 500; color: var(--text-muted);">/ 20</span>
      </div>
      <div class="stat-meta">
        <?php if ($gpa['decision'] === 'Admis'): ?>
          <span class="badge badge-success" style="font-size: 0.7rem;">Admis</span>
        <?php elseif ($gpa['decision'] === 'Session de Rattrapage'): ?>
          <span class="badge badge-warning" style="font-size: 0.7rem;">Rattrapage</span>
        <?php elseif ($gpa['decision'] === 'Ajourné'): ?>
          <span class="badge badge-destructive" style="font-size: 0.7rem;">Ajourné</span>
        <?php else: ?>
          <span class="badge badge-neutral" style="font-size: 0.7rem;">Évaluation en cours</span>
        <?php endif; ?>
        <span style="margin-left: 0.25rem;"><?= $gpa['modulesEvalues'] ?> / <?= $gpa['totalModules'] ?> modules</span>
      </div>
    </div>

    <!-- Assiduité Globale -->
    <div class="card stat-card">
      <div class="stat-header">
        <span class="stat-label">Taux d'Assiduité</span>
        <span class="stat-icon"><?= Icon::get('clock', 16) ?></span>
      </div>
      <div class="stat-value" style="font-size: 1.35rem; font-weight: 800; font-family: ui-monospace, monospace; color: <?= (float)$attendance['taux_assiduite'] >= 80 ? 'var(--color-success)' : 'var(--color-danger)' ?>;">
        <?= $tauxAssiduite ?> %
      </div>
      <div class="stat-meta">
        <?= $attendance['total_presences'] ?> présences sur <?= $attendance['total_seances'] ?> séances
      </div>
    </div>

    <!-- Absences & Retards -->
    <div class="card stat-card">
      <div class="stat-header">
        <span class="stat-label">Absences Enregistrées</span>
        <span class="stat-icon"><?= Icon::get('users', 16) ?></span>
      </div>
      <div class="stat-value" style="font-size: 1.35rem; font-weight: 800; font-family: ui-monospace, monospace;">
        <?= $attendance['total_absences'] ?>
      </div>
      <div class="stat-meta">
        <span style="color: var(--color-danger);"><?= $attendance['non_justifiees'] ?> non justifiée(s)</span> • <?= $attendance['total_retards'] ?> retard(s)
      </div>
    </div>
  </div>

  <!-- Grille 2 Colonnes : Notes Récentes & Annonces Pédagogiques -->
  <div class="grid grid-cols-2" style="gap: 1.5rem; margin-bottom: 1.5rem;">
    <!-- Bloc Relevé de Notes Sommaire -->
    <div class="card">
      <div class="card-header">
        <div>
          <span class="card-title">
            <?= Icon::get('file-text', 18) ?>
            <span>Résultats par Module (Session Normale)</span>
          </span>
          <span class="card-desc" style="display: block; margin-top: 0.25rem;">
            Dernières évaluations transmises par vos enseignants
          </span>
        </div>
        <a href="/etudiant/notes" class="btn btn-ghost btn-sm">
          <span>Voir tout</span>
          <?= Icon::get('chevron-right', 14) ?>
        </a>
      </div>

      <?php if (empty($notes)): ?>
        <div style="padding: 2.5rem; text-align: center; color: var(--text-muted); font-size: 0.875rem;">
          <div style="margin-bottom: 0.5rem; color: var(--text-subtle);"><?= Icon::get('book-open', 32) ?></div>
          Aucun module n'est rattaché pour l'instant à votre cursus.
        </div>
      <?php else: ?>
        <div class="table-wrapper">
          <div class="table-scroll-hint">
            <?= Icon::get('arrow-right', 13) ?>
            <span>Faites glisser horizontalement pour voir toutes les colonnes</span>
          </div>
          <table class="data-table">
            <thead>
              <tr>
                <th style="min-width: 150px;">Module</th>
                <th style="text-align: right; min-width: 90px;">Note (/20)</th>
                <th style="text-align: center; min-width: 100px;">Statut</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($notes as $n): ?>
                <tr>
                  <td>
                    <div style="font-weight: 600; color: var(--text-main); font-size: 0.875rem;">
                      <span class="badge-code"><?= htmlspecialchars($n['module_code']) ?></span>
                    </div>
                    <div style="color: var(--text-muted); font-size: 0.75rem; margin-top: 2px;">
                      <?= htmlspecialchars($n['module_intitule']) ?>
                    </div>
                  </td>
                  <td style="text-align: right; font-weight: 700; font-family: ui-monospace, monospace;">
                    <?= $n['noteFinale'] !== null ? htmlspecialchars(number_format((float)$n['noteFinale'], 2)) : '—' ?>
                  </td>
                  <td style="text-align: center;">
                    <?php if ($n['statutValidation'] === 'V'): ?>
                      <span class="badge badge-success">Validé</span>
                    <?php elseif ($n['statutValidation'] === 'RATT'): ?>
                      <span class="badge badge-warning">Rattrapage</span>
                    <?php elseif ($n['statutValidation'] === 'NV'): ?>
                      <span class="badge badge-destructive">Non Validé</span>
                    <?php else: ?>
                      <span class="badge badge-neutral">En cours</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>

    <!-- Bloc Annonces Ciblées -->
    <div class="card">
      <div class="card-header">
        <div>
          <span class="card-title">
            <?= Icon::get('megaphone', 18) ?>
            <span>Avis & Annonces Récentes</span>
          </span>
          <span class="card-desc" style="display: block; margin-top: 0.25rem;">
            Informations du département et de vos enseignants
          </span>
        </div>
        <a href="/etudiant/annonces" class="btn btn-ghost btn-sm">
          <span>Tous les avis</span>
          <?= Icon::get('chevron-right', 14) ?>
        </a>
      </div>

      <div style="padding: 1rem 1.25rem;">
        <?php if (empty($annonces)): ?>
          <div style="padding: 2rem; text-align: center; color: var(--text-muted); font-size: 0.875rem;">
            <div style="margin-bottom: 0.5rem; color: var(--text-subtle);"><?= Icon::get('megaphone', 28) ?></div>
            Aucune annonce récente pour le moment.
          </div>
        <?php else: ?>
          <div style="display: flex; flex-direction: column; gap: 1rem;">
            <?php foreach ($annonces as $a): ?>
              <div style="border: 1px solid var(--border-color); border-radius: var(--radius-sm); padding: 0.875rem; background-color: var(--bg-hover);">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 0.5rem; margin-bottom: 0.35rem;">
                  <span style="font-weight: 600; font-size: 0.875rem; color: var(--text-main);">
                    <?= htmlspecialchars($a['titre']) ?>
                  </span>
                  <?php if (!empty($a['module_code'])): ?>
                    <span class="badge-code" style="font-size: 0.7rem;"><?= htmlspecialchars($a['module_code']) ?></span>
                  <?php else: ?>
                    <span class="badge badge-neutral" style="font-size: 0.7rem;">Globale</span>
                  <?php endif; ?>
                </div>
                <p style="font-size: 0.8125rem; color: var(--text-muted); margin-bottom: 0.5rem; line-height: 1.4;">
                  <?= nl2br(htmlspecialchars(substr($a['contenu'], 0, 140))) ?><?= strlen($a['contenu']) > 140 ? '...' : '' ?>
                </p>
                <div style="display: flex; justify-content: space-between; font-size: 0.75rem; color: var(--text-subtle);">
                  <span>Par <?= htmlspecialchars($a['auteur_prenom'] . ' ' . $a['auteur_nom']) ?></span>
                  <span><?= date('d/m/Y', strtotime($a['datePublication'])) ?></span>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
