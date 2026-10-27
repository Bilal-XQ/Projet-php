<?php
declare(strict_types=1);

use App\Utils\Icon;

$primaryCode = $primaryModuleStats['module_code'] ?? 'DEV-WEB';
$primaryPassRate = number_format((float)($primaryModuleStats['tauxReussite'] ?? 0.0), 1);
$primaryAvgFinal = number_format((float)($primaryModuleStats['moyenneFinale'] ?? 0.0), 2);

// Calcul du total des étudiants supervisés
$totalStudents = 0;
foreach ($modules as $m) {
    $totalStudents += (int)($m['total_etudiants'] ?? 0);
}
?>
<div class="content-container">
  <!-- 1. En-tête Exécutif Professionnel -->
  <div style="margin-bottom: 2rem;">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1.25rem;">
      <div>
        <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.35rem;">
          <span class="badge badge-warning">Espace Pédagogique Enseignant</span>
          <span class="badge badge-neutral" style="font-family: ui-monospace, monospace;">
            <?= Icon::get('shield-check', 13) ?>
            <span><?= htmlspecialchars($user->getMatricule()) ?></span>
          </span>
        </div>
        <h1 style="font-size: 1.75rem; font-weight: 700; letter-spacing: -0.025em; color: var(--text-main); margin-bottom: 0.25rem;">
          Pr. <?= htmlspecialchars($user->getNomComplet()) ?>
        </h1>
        <p style="color: var(--text-muted); font-size: 0.875rem;">
          Département Mathématiques & Informatique • Spécialité : <strong><?= htmlspecialchars($user->getSpecialite()) ?></strong>
        </p>
      </div>

      <div style="display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: center;">
        <a href="/enseignant/notes" class="btn btn-primary btn-sm">
          <?= Icon::get('pencil', 14) ?>
          <span>Saisie des Notes</span>
        </a>
        <a href="/enseignant/seances" class="btn btn-outline btn-sm">
          <?= Icon::get('clock', 14) ?>
          <span>Feuilles d'Assiduité</span>
        </a>
        <a href="/enseignant/annonces" class="btn btn-outline btn-sm">
          <?= Icon::get('megaphone', 14) ?>
          <span>Publier un Avis</span>
        </a>
      </div>
    </div>
  </div>

  <!-- 2. Grille des Indicateurs Clés (KPI Stats) -->
  <div class="grid grid-cols-4" style="margin-bottom: 2rem;">
    <div class="card stat-card">
      <div class="stat-header">
        <span class="stat-label">Modules Attribués</span>
        <span class="stat-icon"><?= Icon::get('book-open', 18) ?></span>
      </div>
      <div class="stat-value"><?= count($modules) ?></div>
      <div class="stat-meta">Responsabilités pédagogiques S1/S2</div>
    </div>

    <div class="card stat-card">
      <div class="stat-header">
        <span class="stat-label">Étudiants Supervisés</span>
        <span class="stat-icon"><?= Icon::get('users', 18) ?></span>
      </div>
      <div class="stat-value"><?= $totalStudents ?></div>
      <div class="stat-meta">Inscrits dans vos cours</div>
    </div>

    <div class="card stat-card">
      <div class="stat-header">
        <span class="stat-label">Séances Dispensées</span>
        <span class="stat-icon"><?= Icon::get('clock', 18) ?></span>
      </div>
      <div class="stat-value"><?= count($recentSeances) ?></div>
      <div class="stat-meta">Séances enregistrées au planning</div>
    </div>

    <div class="card stat-card">
      <div class="stat-header">
        <span class="stat-label">Taux Réussite Moyen</span>
        <span class="stat-icon"><?= Icon::get('award', 18) ?></span>
      </div>
      <div class="stat-value" style="color: var(--success);"><?= $primaryPassRate ?> %</div>
      <div class="stat-meta">Moyenne module : <?= $primaryAvgFinal ?>/20</div>
    </div>
  </div>

  <!-- 3. Catalogue des Modules Sous Votre Responsabilité -->
  <div style="margin-bottom: 2rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
      <div>
        <h2 style="font-size: 1.15rem; font-weight: 700; color: var(--text-main); display: flex; align-items: center; gap: 0.5rem;">
          <?= Icon::get('book-open', 18) ?>
          <span>Unités d'Enseignement & Modules Pris en Charge</span>
        </h2>
        <p style="font-size: 0.8125rem; color: var(--text-muted); margin-top: 0.15rem;">
          Accédez directement à la notation directe sur 20, aux feuilles d'émargement et aux PV officiels
        </p>
      </div>
      <a href="/enseignant/modules" class="btn btn-outline btn-sm">Vue Détaillée</a>
    </div>

    <?php if (empty($modules)): ?>
      <div class="card" style="text-align: center; padding: 3rem 1rem; color: var(--text-muted); font-size: 0.875rem;">
        <div style="margin-bottom: 0.5rem; color: var(--text-subtle);"><?= Icon::get('book-open', 32) ?></div>
        Aucun module ne vous est actuellement attribué pour cette année universitaire.
      </div>
    <?php else: ?>
      <div class="grid grid-cols-2" style="gap: 1.25rem;">
        <?php foreach ($modules as $m): ?>
          <div class="card" style="display: flex; flex-direction: column; justify-content: space-between;">
            <div>
              <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.75rem;">
                <div>
                  <div style="display: flex; gap: 0.4rem; align-items: center; margin-bottom: 0.35rem;">
                    <code><?= htmlspecialchars($m['code']) ?></code>
                    <span class="badge badge-neutral"><?= htmlspecialchars($m['filiere_code']) ?></span>
                  </div>
                  <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--text-main); line-height: 1.3;">
                    <?= htmlspecialchars($m['intitule']) ?>
                  </h3>
                </div>
                <span class="badge badge-warning"><?= htmlspecialchars($m['semestre']) ?></span>
              </div>

              <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.65rem 0.75rem; background: var(--bg-surface-hover); border-radius: var(--radius-sm); margin: 0.75rem 0;">
                <span style="font-size: 0.8125rem; color: var(--text-muted);">
                  Inscrits : <strong style="color: var(--text-main);"><?= (int)$m['total_etudiants'] ?> étudiants</strong>
                </span>
                <span style="font-size: 0.8125rem; color: var(--text-muted);">
                  Réussite : <strong style="color: var(--success);"><?= number_format((float)($m['stats']['tauxReussite'] ?? 0), 1) ?> %</strong>
                </span>
              </div>
            </div>

            <div style="display: flex; gap: 0.5rem; flex-wrap: wrap; border-top: 1px solid var(--border); padding-top: 0.875rem; margin-top: 0.5rem;">
              <a href="/enseignant/modules/<?= $m['idModule'] ?>/notes" class="btn btn-primary btn-sm">
                <?= Icon::get('pencil', 13) ?>
                <span>Saisie Notes</span>
              </a>
              <a href="/enseignant/modules/<?= $m['idModule'] ?>/seances" class="btn btn-outline btn-sm">
                <?= Icon::get('clock', 13) ?>
                <span>Assiduité</span>
              </a>
              <a href="/enseignant/modules/<?= $m['idModule'] ?>/etudiants" class="btn btn-outline btn-sm">
                <?= Icon::get('users', 13) ?>
                <span>Inscrits (<?= (int)$m['total_etudiants'] ?>)</span>
              </a>
              <a href="/enseignant/modules/<?= $m['idModule'] ?>/pdf" target="_blank" class="btn btn-ghost btn-sm" title="Télécharger le procès-verbal officiel en PDF">
                <?= Icon::get('file-text', 13) ?>
                <span>PV PDF</span>
              </a>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

  <!-- 4. Bloc Graphique & Indicateurs Statistiques -->
  <?php if ($primaryModuleStats): ?>
    <div class="card" style="margin-bottom: 2rem;">
      <div class="card-header">
        <div>
          <span class="card-title">
            <?= Icon::get('award', 18) ?>
            <span>Distribution des Résultats Académiques — Module <?= htmlspecialchars($primaryCode) ?></span>
          </span>
          <span class="card-desc" style="display: block; margin-top: 0.25rem;">
            Répartition des notes pour <?= (int)$primaryModuleStats['totalEvalues'] ?> étudiant(s) évalué(s) en Session Normale
          </span>
        </div>
        <div style="display: flex; gap: 0.75rem; align-items: center;">
          <span class="badge badge-success">
            Réussite : <?= $primaryPassRate ?> %
          </span>
          <span class="badge badge-neutral" style="font-family: ui-monospace, monospace;">
            Moyenne : <?= $primaryAvgFinal ?> / 20
          </span>
        </div>
      </div>
      <div style="padding: 1.25rem; height: 260px; position: relative;">
        <canvas id="teacherModuleChart"></canvas>
      </div>
    </div>
  <?php endif; ?>

  <!-- 5. Séances Récentes & Communications Pédagogiques -->
  <div class="grid grid-cols-2" style="gap: 1.5rem;">
    <!-- Dernières Séances Programmées -->
    <div class="card">
      <div class="card-header">
        <span class="card-title">
          <?= Icon::get('clock', 18) ?>
          <span>Dernières Séances & Assiduité</span>
        </span>
        <a href="/enseignant/seances" class="btn btn-outline btn-sm">Toutes les séances</a>
      </div>

      <?php if (empty($recentSeances)): ?>
        <p style="color: var(--text-muted); font-size: 0.875rem; padding: 2rem 1rem; text-align: center;">
          Aucune séance programmée récemment.
        </p>
      <?php else: ?>
        <div style="display: flex; flex-direction: column; gap: 0.65rem;">
          <?php foreach ($recentSeances as $s): ?>
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 1rem; border: 1px solid var(--border); border-radius: var(--radius-md); background: var(--bg-surface-hover);">
              <div>
                <div style="font-weight: 600; font-size: 0.875rem; color: var(--text-main); display: flex; align-items: center; gap: 0.4rem;">
                  <code><?= htmlspecialchars($s['module_code']) ?></code>
                  <span>Séance de <?= htmlspecialchars($s['typeSeance']) ?></span>
                </div>
                <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 2px;">
                  <?= date('d/m/Y', strtotime($s['dateSeance'])) ?> • <?= htmlspecialchars(substr($s['heureDebut'], 0, 5)) ?> à <?= htmlspecialchars(substr($s['heureFin'], 0, 5)) ?>
                </div>
              </div>
              <a href="/enseignant/seances/<?= $s['idSeance'] ?>/absences" class="btn btn-outline btn-sm">
                <?= Icon::get('check', 13) ?>
                <span>Émarger</span>
              </a>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <!-- Communications Pédagogiques -->
    <div class="card">
      <div class="card-header">
        <span class="card-title">
          <?= Icon::get('megaphone', 18) ?>
          <span>Communications Pédagogiques</span>
        </span>
        <a href="/enseignant/annonces" class="btn btn-outline btn-sm">Gérer les avis</a>
      </div>

      <?php if (empty($recentAnnonces)): ?>
        <p style="color: var(--text-muted); font-size: 0.875rem; padding: 2rem 1rem; text-align: center;">
          Aucune annonce publiée pour le moment.
        </p>
      <?php else: ?>
        <div style="display: flex; flex-direction: column; gap: 0.65rem;">
          <?php foreach ($recentAnnonces as $a): ?>
            <div style="padding: 0.75rem 1rem; border: 1px solid var(--border); border-radius: var(--radius-md); background: var(--bg-surface-hover);">
              <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.25rem;">
                <span style="font-weight: 600; font-size: 0.875rem; color: var(--text-main);"><?= htmlspecialchars($a['titre']) ?></span>
                <span style="font-size: 0.7rem; color: var(--text-muted);"><?= date('d/m/Y', strtotime($a['datePublication'])) ?></span>
              </div>
              <p style="font-size: 0.8125rem; color: var(--text-muted); line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                <?= htmlspecialchars($a['contenu']) ?>
              </p>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php if ($primaryModuleStats): ?>
<script>
function renderTeacherChart() {
  const ctx = document.getElementById('teacherModuleChart');
  if (!ctx || typeof Chart === 'undefined') return;
  if (Chart.getChart(ctx)) return;

  const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
  const textColor = isDark ? '#a1a1aa' : '#71717a';
  const gridColor = isDark ? '#27272a' : '#e4e4e7';

    new Chart(ctx, {
      type: 'bar',
      data: {
        labels: ['[0 - 8[ Non Valide', '[8 - 10[ Rattrapage', '[10 - 12[ Passable', '[12 - 14[ Assez Bien', '[14 - 16[ Bien', '[16 - 20] Tres Bien'],
        datasets: [{
          label: 'Effectif Etudiants',
        data: [
          <?= (int)($primaryModuleStats['tranche_0_8'] ?? 0) ?>,
          <?= (int)($primaryModuleStats['tranche_8_10'] ?? 0) ?>,
          <?= (int)($primaryModuleStats['tranche_10_12'] ?? 0) ?>,
          <?= (int)($primaryModuleStats['tranche_12_14'] ?? 0) ?>,
          <?= (int)($primaryModuleStats['tranche_14_16'] ?? 0) ?>,
          <?= (int)($primaryModuleStats['tranche_16_20'] ?? 0) ?>
        ],
        backgroundColor: [
          'rgba(239, 68, 68, 0.65)',
          'rgba(245, 158, 11, 0.65)',
          'rgba(59, 130, 246, 0.65)',
          'rgba(16, 185, 129, 0.65)',
          'rgba(16, 185, 129, 0.8)',
          'rgba(16, 185, 129, 0.95)'
        ],
        borderColor: [
          '#ef4444',
          '#f59e0b',
          '#3b82f6',
          '#10b981',
          '#10b981',
          '#10b981'
        ],
        borderWidth: 1,
        borderRadius: 4
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false },
        tooltip: {
          backgroundColor: isDark ? '#18181b' : '#ffffff',
          titleColor: isDark ? '#f4f4f5' : '#09090b',
          bodyColor: isDark ? '#a1a1aa' : '#71717a',
          borderColor: isDark ? '#27272a' : '#e4e4e7',
          borderWidth: 1
        }
      },
      scales: {
        x: {
          grid: { color: gridColor },
          ticks: { color: textColor, font: { size: 11 } }
        },
        y: {
          beginAtZero: true,
          grid: { color: gridColor },
          ticks: { stepSize: 1, color: textColor, font: { size: 11 } }
        }
      }
    }
  });
}

document.addEventListener('DOMContentLoaded', renderTeacherChart);
window.addEventListener('load', renderTeacherChart);
renderTeacherChart();
setTimeout(renderTeacherChart, 150);
setTimeout(renderTeacherChart, 500);
</script>
<?php endif; ?>
