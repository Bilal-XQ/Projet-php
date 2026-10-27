<?php
declare(strict_types=1);

use App\Utils\Icon;

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
  <div class="grid grid-cols-3" style="margin-bottom: 2rem;">
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

