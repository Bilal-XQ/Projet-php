<?php
declare(strict_types=1);

use App\Utils\Icon;

$tauxReussite = number_format((float)($deptStats['tauxReussite'] ?? 0.0), 1);
$moyenneGen = number_format((float)($deptStats['moyenneGenerale'] ?? 0.0), 2);
$tauxAssiduite = number_format((float)($attendanceStats['tauxPresence'] ?? 100.0), 1);
?>
<div class="content-container">
  <!-- En-tête de section -->
  <div style="margin-bottom: 2rem;">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem;">
      <div>
        <h1 style="font-size: 1.5rem; font-weight: 700; letter-spacing: -0.025em; color: var(--text-main); margin-bottom: 0.25rem;">
          Tableau de bord — Direction & Scolarité
        </h1>
        <p style="color: var(--text-muted); font-size: 0.875rem;">
          Bienvenue, <strong><?= htmlspecialchars($user->getNomComplet()) ?></strong>. Vue consolidée et indicateurs de performance académique.
        </p>
      </div>
      <div style="display: flex; gap: 0.5rem; align-items: center;">
        <span class="badge badge-success">
          <span class="status-dot"></span> Système Opérationnel
        </span>
        <span class="badge badge-neutral" style="font-family: ui-monospace, monospace;">
          <?= Icon::get('calendar', 13) ?>
          <span>2026-2027</span>
        </span>
      </div>
    </div>
  </div>

  <!-- Métriques Générales (Comptes, Cursus, Performances) -->
  <div class="grid grid-cols-4" style="margin-bottom: 1.5rem;">
    <div class="card stat-card">
      <div class="stat-header">
        <span class="stat-label">Comptes Utilisateurs</span>
        <span class="stat-icon"><?= Icon::get('users', 16) ?></span>
      </div>
      <div class="stat-value"><?= (int)($stats['utilisateurs'] ?? 0) ?></div>
      <div class="stat-meta">
        <?= (int)($stats['enseignants'] ?? 0) ?> professeurs • <?= (int)($stats['etudiants'] ?? 0) ?> étudiants
      </div>
    </div>

    <div class="card stat-card">
      <div class="stat-header">
        <span class="stat-label">Structure Pédagogique</span>
        <span class="stat-icon"><?= Icon::get('book-open', 16) ?></span>
      </div>
      <div class="stat-value"><?= (int)($stats['filieres'] ?? 0) ?> filières</div>
      <div class="stat-meta"><?= (int)($stats['modules'] ?? 0) ?> modules d'enseignement</div>
    </div>

    <div class="card stat-card">
      <div class="stat-header">
        <span class="stat-label">Taux de Réussite Global</span>
        <span class="stat-icon"><?= Icon::get('graduation-cap', 16) ?></span>
      </div>
      <div class="stat-value" style="color: var(--color-success); font-family: ui-monospace, monospace;">
        <?= $tauxReussite ?> %
      </div>
      <div class="stat-meta">Moyenne dép. : <?= $moyenneGen ?> / 20</div>
    </div>

    <div class="card stat-card">
      <div class="stat-header">
        <span class="stat-label">Assiduité Départementale</span>
        <span class="stat-icon"><?= Icon::get('clock', 16) ?></span>
      </div>
      <div class="stat-value" style="font-family: ui-monospace, monospace; color: <?= (float)($attendanceStats['tauxPresence'] ?? 100) >= 80 ? 'var(--color-success)' : 'var(--color-warning)' ?>;">
        <?= $tauxAssiduite ?> %
      </div>
      <div class="stat-meta">
        <?= (int)($attendanceStats['absents'] ?? 0) ?> absence(s) • <?= (int)($attendanceStats['retards'] ?? 0) ?> retard(s)
      </div>
    </div>
  </div>

  <!-- Visualisations Dynamiques Chart.js -->
  <div class="grid grid-cols-2" style="gap: 1.5rem; margin-bottom: 1.5rem;">
    <!-- Graphique 1 : Histogramme de distribution des notes -->
    <div class="card">
      <div class="card-header">
        <div>
          <span class="card-title">
            <?= Icon::get('bar-chart', 18) ?>
            <span>Distribution des Notes du Département</span>
          </span>
          <span class="card-desc" style="display: block; margin-top: 0.25rem;">
            Histogramme des mentions et validations en Session Normale
          </span>
        </div>
        <span class="badge badge-neutral" style="font-family: ui-monospace, monospace;"><?= (int)($deptStats['totalNotes'] ?? 0) ?> notes</span>
      </div>
      <div style="padding: 1rem 1.25rem; height: 260px; position: relative;">
        <canvas id="deptNotesChart"></canvas>
      </div>
    </div>

    <!-- Graphique 2 : Bilan d'Assiduité -->
    <div class="card">
      <div class="card-header">
        <div>
          <span class="card-title">
            <?= Icon::get('pie-chart', 18) ?>
            <span>Bilan Global de l'Assiduité aux Séances</span>
          </span>
          <span class="card-desc" style="display: block; margin-top: 0.25rem;">
            Ventilation des présences, retards et absences justifiées / non justifiées
          </span>
        </div>
        <span class="badge badge-neutral" style="font-family: ui-monospace, monospace;"><?= (int)($attendanceStats['totalEnregistrements'] ?? 0) ?> émargements</span>
      </div>
      <div style="padding: 1rem 1.25rem; height: 260px; position: relative; display: flex; align-items: center; justify-content: center;">
        <canvas id="deptAttendanceChart"></canvas>
      </div>
    </div>
  </div>

  <!-- Grille 2 Colonnes : Raccourcis Administratifs & Dernières Inscriptions -->
  <div class="grid grid-cols-2" style="gap: 1.5rem; margin-bottom: 1.5rem;">
    <!-- Raccourcis de Gestion -->
    <div class="card">
      <div class="card-header">
        <span class="card-title">
          <?= Icon::get('layout-dashboard', 18) ?>
          <span>Actions & Raccourcis de Gestion</span>
        </span>
        <span class="badge badge-neutral">Scolarité</span>
      </div>
      <div style="display: flex; flex-direction: column; gap: 0.75rem; padding: 1rem 1.25rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: var(--radius-sm); background: var(--bg-hover);">
          <div>
            <div style="font-weight: 600; font-size: 0.875rem; color: var(--text-main);">Filières & Cursus</div>
            <div style="font-size: 0.75rem; color: var(--text-muted);">Paramétrage des filières et accréditations</div>
          </div>
          <a href="/admin/filieres" class="btn btn-outline btn-sm">Gérer les Filières</a>
        </div>

        <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: var(--radius-sm); background: var(--bg-hover);">
          <div>
            <div style="font-weight: 600; font-size: 0.875rem; color: var(--text-main);">Modules & Enseignants</div>
            <div style="font-size: 0.75rem; color: var(--text-muted);">Attribution des modules aux professeurs</div>
          </div>
          <a href="/admin/modules" class="btn btn-outline btn-sm">Gérer les Modules</a>
        </div>

        <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: var(--radius-sm); background: var(--bg-hover);">
          <div>
            <div style="font-weight: 600; font-size: 0.875rem; color: var(--text-main);">Inscriptions & Effectifs</div>
            <div style="font-size: 0.75rem; color: var(--text-muted);">Enrôlement des étudiants pour 2026-2027</div>
          </div>
          <a href="/admin/inscriptions" class="btn btn-primary btn-sm">Inscrire un Étudiant</a>
        </div>

        <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: var(--radius-sm); background: var(--bg-hover);">
          <div>
            <div style="font-weight: 600; font-size: 0.875rem; color: var(--text-main);">Journal d'Audit des Notes</div>
            <div style="font-size: 0.75rem; color: var(--text-muted);">Contrôle réglementaire et traçabilité</div>
          </div>
          <a href="/admin/audit" class="btn btn-outline btn-sm">Consulter l'Audit</a>
        </div>
      </div>
    </div>

    <!-- Dernières Inscriptions -->
    <div class="card">
      <div class="card-header">
        <div>
          <span class="card-title">
            <?= Icon::get('user-plus', 18) ?>
            <span>Dernières Inscriptions Administratives</span>
          </span>
          <span class="card-desc" style="display: block; margin-top: 0.25rem;">
            Étudiants récemment affectés aux filières
          </span>
        </div>
        <a href="/admin/inscriptions" class="btn btn-ghost btn-sm">Toutes</a>
      </div>

      <?php if (empty($recentInscriptions)): ?>
        <div style="padding: 2.5rem; text-align: center; color: var(--text-muted); font-size: 0.875rem;">
          Aucune inscription enregistrée récemment.
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
                <th style="min-width: 150px;">Étudiant</th>
                <th style="min-width: 90px;">Filière</th>
                <th style="min-width: 90px;">Session</th>
                <th style="min-width: 100px;">Statut</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($recentInscriptions as $ri): ?>
                <tr>
                  <td style="font-weight: 600; font-size: 0.875rem;">
                    <?= htmlspecialchars($ri['prenom'] . ' ' . $ri['nom']) ?>
                  </td>
                  <td>
                    <span class="badge-code"><?= htmlspecialchars($ri['filiere_code']) ?></span>
                  </td>
                  <td style="font-family: ui-monospace, monospace; font-size: 0.8125rem;">
                    <?= htmlspecialchars($ri['anneeUniversitaire']) ?>
                  </td>
                  <td>
                    <?php if ($ri['statut'] === 'EN_COURS'): ?>
                      <span class="badge badge-success">En règle</span>
                    <?php else: ?>
                      <span class="badge badge-neutral"><?= htmlspecialchars($ri['statut']) ?></span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Initialisation des Graphiques Chart.js -->
<script>
document.addEventListener('DOMContentLoaded', function() {
  if (typeof Chart === 'undefined') return;

  const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
  const textColor = isDark ? '#a1a1aa' : '#71717a';
  const gridColor = isDark ? 'rgba(255, 255, 255, 0.08)' : 'rgba(0, 0, 0, 0.06)';

  // 1. Graphique Notes (Histogramme)
  const notesCtx = document.getElementById('deptNotesChart');
  if (notesCtx) {
    new Chart(notesCtx, {
      type: 'bar',
      data: {
        labels: ['< 10 (Ajourné)', '10 - 12 (Passable)', '12 - 14 (Assez Bien)', '14 - 16 (Bien)', '≥ 16 (Très Bien)'],
        datasets: [{
          label: 'Nombre d\'évaluations',
          data: [
            <?= (int)($deptStats['distribution']['ajourne'] ?? 0) ?>,
            <?= (int)($deptStats['distribution']['passable'] ?? 0) ?>,
            <?= (int)($deptStats['distribution']['assez_bien'] ?? 0) ?>,
            <?= (int)($deptStats['distribution']['bien'] ?? 0) ?>,
            <?= (int)($deptStats['distribution']['tres_bien'] ?? 0) ?>
          ],
          backgroundColor: [
            'rgba(239, 68, 68, 0.8)',
            'rgba(245, 158, 11, 0.8)',
            'rgba(59, 130, 246, 0.8)',
            'rgba(16, 185, 129, 0.8)',
            'rgba(139, 92, 246, 0.8)'
          ],
          borderRadius: 4
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false }
        },
        scales: {
          x: {
            ticks: { color: textColor, font: { size: 11 } },
            grid: { display: false }
          },
          y: {
            ticks: { color: textColor, stepSize: 1, font: { size: 11 } },
            grid: { color: gridColor },
            beginAtZero: true
          }
        }
      }
    });
  }

  // 2. Graphique Assiduité (Doughnut)
  const attCtx = document.getElementById('deptAttendanceChart');
  if (attCtx) {
    new Chart(attCtx, {
      type: 'doughnut',
      data: {
        labels: ['Présents', 'Absents Non Justifiés', 'Absents Justifiés', 'Retards'],
        datasets: [{
          data: [
            <?= (int)($attendanceStats['presents'] ?? 0) ?>,
            <?= (int)($attendanceStats['absentsNonJustifies'] ?? 0) ?>,
            <?= (int)($attendanceStats['absentsJustifies'] ?? 0) ?>,
            <?= (int)($attendanceStats['retards'] ?? 0) ?>
          ],
          backgroundColor: [
            '#10b981',
            '#ef4444',
            '#f59e0b',
            '#6366f1'
          ],
          borderWidth: 0
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'bottom',
            labels: { color: textColor, font: { size: 11 } }
          }
        },
        cutout: '68%'
      }
    });
  }
});
</script>
