<?php
use App\Utils\Icon;
use App\Utils\Auth;
?>
<div style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem;">
  <div>
    <h1 style="font-size: 1.5rem; font-weight: 700; letter-spacing: -0.025em; color: var(--text-main); margin-bottom: 0.25rem;">
      Gestion des Comptes Utilisateurs
    </h1>
    <p style="color: var(--text-muted); font-size: 0.875rem;">
      Répertoire du corps professoral, des promotions étudiantes et des administrateurs.
    </p>
  </div>
  <button class="btn btn-primary" data-modal-open="createUserModal">
    <?= Icon::get('plus', 16) ?>
    <span>Nouvel Utilisateur</span>
  </button>
</div>

<!-- Tabs de Filtrage des Rôles -->
<div class="tabs-container">
  <a href="/admin/utilisateurs" class="tab-btn <?= empty($currentFilter) ? 'active' : '' ?>">
    <?= Icon::get('users', 14) ?>
    <span>Tous (<?= count($allUsers) ?>)</span>
  </a>
  <a href="/admin/utilisateurs?role=ENSEIGNANT" class="tab-btn <?= $currentFilter === 'ENSEIGNANT' ? 'active' : '' ?>">
    <?= Icon::get('user-check', 14) ?>
    <span>Enseignants (<?= count($enseignantsList) ?>)</span>
  </a>
  <a href="/admin/utilisateurs?role=ETUDIANT" class="tab-btn <?= $currentFilter === 'ETUDIANT' ? 'active' : '' ?>">
    <?= Icon::get('graduation-cap', 14) ?>
    <span>Étudiants (<?= count($etudiantsList) ?>)</span>
  </a>
  <a href="/admin/utilisateurs?role=ADMIN" class="tab-btn <?= $currentFilter === 'ADMIN' ? 'active' : '' ?>">
    <?= Icon::get('shield-check', 14) ?>
    <span>Administrateurs (<?= count($adminsList) ?>)</span>
  </a>
</div>

<!-- Table Card -->
<div class="card">
  <div class="card-header">
    <div>
      <span class="card-title">
        <?= Icon::get('users', 18) ?>
        <span>Utilisateurs Enregistrés</span>
      </span>
      <span class="card-desc" style="display: block; margin-top: 0.25rem;">
        Affichage de <?= count($filteredUsers) ?> utilisateur(s)
      </span>
    </div>
    <span class="badge badge-neutral"><?= count($filteredUsers) ?> Actifs</span>
  </div>

  <div class="table-scroll-hint">
    <?= Icon::get('arrow-right', 12) ?>
    <span>Faites glisser horizontalement pour voir toutes les colonnes</span>
  </div>
  <div class="table-wrapper">
    <table>
      <thead>
        <tr>
          <th style="min-width: 60px;">ID</th>
          <th style="min-width: 140px;">Nom Complet</th>
          <th style="min-width: 170px;">Email Académique</th>
          <th style="min-width: 100px;">Rôle</th>
          <th style="min-width: 200px;">Identifiant / Attributs Spécifiques</th>
          <th style="min-width: 120px;">Date de Création</th>
          <th style="min-width: 140px; text-align: right;">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($filteredUsers as $u): ?>
          <tr>
            <td><code>#<?= htmlspecialchars((string)$u['idUtilisateur']) ?></code></td>
            <td style="font-weight: 600;">
              <?= htmlspecialchars($u['prenom'] . ' ' . $u['nom']) ?>
            </td>
            <td><code><?= htmlspecialchars($u['email']) ?></code></td>
            <td>
              <?php if ($u['role'] === 'ADMIN'): ?>
                <span class="badge badge-warning">ADMINISTRATEUR</span>
              <?php elseif ($u['role'] === 'ENSEIGNANT'): ?>
                <span class="badge badge-neutral">ENSEIGNANT</span>
              <?php else: ?>
                <span class="badge badge-neutral">ÉTUDIANT</span>
              <?php endif; ?>
            </td>
            <td>
              <?php if ($u['role'] === 'ENSEIGNANT'): ?>
                <span>Matricule : <code><?= htmlspecialchars($u['matricule'] ?? 'N/A') ?></code></span>
                <span style="display: block; font-size: 0.75rem; color: var(--text-muted);"><?= htmlspecialchars($u['specialite'] ?? '') ?></span>
              <?php elseif ($u['role'] === 'ETUDIANT'): ?>
                <span>CNE : <code><?= htmlspecialchars($u['cne'] ?? 'N/A') ?></code></span>
                <span style="display: block; font-size: 0.75rem; color: var(--text-muted);">Né(e) le : <?= htmlspecialchars($u['dateNaissance'] ?? '') ?></span>
              <?php else: ?>
                <span style="color: var(--text-muted); font-size: 0.8125rem;">Accès Intégral Système</span>
              <?php endif; ?>
            </td>
            <td style="color: var(--text-muted); font-size: 0.8125rem;">
              <?= date('d/m/Y H:i', strtotime($u['dateCreation'])) ?>
            </td>
            <td style="text-align: right;">
              <div style="display: inline-flex; gap: 0.35rem; align-items: center;">
                <button 
                  type="button" 
                  class="btn btn-outline btn-sm edit-user-btn"
                  data-id="<?= $u['idUtilisateur'] ?>"
                  data-nom="<?= htmlspecialchars($u['nom']) ?>"
                  data-prenom="<?= htmlspecialchars($u['prenom']) ?>"
                  data-email="<?= htmlspecialchars($u['email']) ?>"
                  data-role="<?= htmlspecialchars($u['role']) ?>"
                  data-matricule="<?= htmlspecialchars($u['matricule'] ?? '') ?>"
                  data-specialite="<?= htmlspecialchars($u['specialite'] ?? '') ?>"
                  data-cne="<?= htmlspecialchars($u['cne'] ?? '') ?>"
                  data-datenaissance="<?= htmlspecialchars($u['dateNaissance'] ?? '') ?>"
                  title="Modifier l'utilisateur"
                >
                  <?= Icon::get('pencil', 13) ?>
                  <span>Modifier</span>
                </button>
                <?php if ($u['idUtilisateur'] !== Auth::id()): ?>
                  <form action="/admin/utilisateurs/delete" method="POST" onsubmit="return confirm('Confirmer la suppression définitive de cet utilisateur ?');" style="display: inline;">
                    <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                    <input type="hidden" name="idUtilisateur" value="<?= $u['idUtilisateur'] ?>">
                    <button type="submit" class="btn btn-destructive btn-sm btn-icon" title="Supprimer l'utilisateur">
                      <?= Icon::get('trash-2', 13) ?>
                    </button>
                  </form>
                <?php endif; ?>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal Création Utilisateur Polymorphe -->
<div id="createUserModal" class="modal">
  <div class="modal-backdrop"></div>
  <div class="modal-dialog">
    <div class="modal-header">
      <h3 class="modal-title">
        <?= Icon::get('plus', 18) ?>
        <span>Créer un Nouveau Compte</span>
      </h3>
      <button type="button" class="modal-close-btn" data-modal-close aria-label="Fermer">
        <?= Icon::get('x', 18) ?>
      </button>
    </div>
    <form action="/admin/utilisateurs/create" method="POST">
      <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
      
      <!-- Sélection du Rôle -->
      <div class="form-group">
        <label for="roleSelect" class="form-label">Type d'Utilisateur / Rôle</label>
        <select id="roleSelect" name="role" class="form-control" required>
          <option value="ENSEIGNANT">Enseignant (Professeur)</option>
          <option value="ETUDIANT">Étudiant (Inscrit au département)</option>
          <option value="ADMIN">Administrateur (Chef / Scolarité)</option>
        </select>
      </div>

      <!-- Informations Générales -->
      <div class="form-row">
        <div class="form-group">
          <label for="nom" class="form-label">Nom de Famille</label>
          <input type="text" id="nom" name="nom" class="form-control" placeholder="El Amrani" required>
        </div>
        <div class="form-group">
          <label for="prenom" class="form-label">Prénom</label>
          <input type="text" id="prenom" name="prenom" class="form-control" placeholder="Kenza" required>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="user_email" class="form-label">Adresse Email</label>
          <input type="email" id="user_email" name="email" class="form-control" placeholder="k.elamrani@departement.ma" required>
        </div>
        <div class="form-group">
          <label for="user_password" class="form-label">Mot de Passe Initial</label>
          <input type="password" id="user_password" name="password" class="form-control" placeholder="••••••••••••" required>
        </div>
      </div>

      <!-- Section Champs Enseignant (Spécifique) -->
      <div id="enseignantFields" style="padding-top: 0.5rem; border-top: 1px dashed var(--border); margin-top: 0.5rem;">
        <div class="form-row">
          <div class="form-group">
            <label for="matricule" class="form-label">Matricule Enseignant</label>
            <input type="text" id="matricule" name="matricule" class="form-control" placeholder="ENS-2026-002">
          </div>
          <div class="form-group">
            <label for="specialite" class="form-label">Discipline / Spécialité</label>
            <input type="text" id="specialite" name="specialite" class="form-control" placeholder="Algorithmique & Graphes">
          </div>
        </div>
      </div>

      <!-- Section Champs Étudiant (Spécifique) -->
      <div id="etudiantFields" style="display: none; padding-top: 0.5rem; border-top: 1px dashed var(--border); margin-top: 0.5rem;">
        <div class="form-row">
          <div class="form-group">
            <label for="cne" class="form-label">CNE (Code National Étudiant)</label>
            <input type="text" id="cne" name="cne" class="form-control" placeholder="R130987654">
          </div>
          <div class="form-group">
            <label for="dateNaissance" class="form-label">Date de Naissance</label>
            <input type="date" id="dateNaissance" name="dateNaissance" class="form-control" value="2004-01-01">
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-outline" data-modal-close>Annuler</button>
        <button type="submit" class="btn btn-primary">Créer le Compte</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Modification Utilisateur -->
<div id="editUserModal" class="modal">
  <div class="modal-backdrop"></div>
  <div class="modal-dialog">
    <div class="modal-header">
      <h3 class="modal-title">
        <?= Icon::get('pencil', 18) ?>
        <span>Modifier le Compte Utilisateur</span>
      </h3>
      <button type="button" class="modal-close-btn" data-modal-close aria-label="Fermer">
        <?= Icon::get('x', 18) ?>
      </button>
    </div>
    <form action="/admin/utilisateurs/edit" method="POST">
      <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
      <input type="hidden" id="edit_idUtilisateur" name="idUtilisateur" value="">
      <input type="hidden" id="edit_role" name="role" value="">

      <!-- Informations Générales -->
      <div class="form-row">
        <div class="form-group">
          <label for="edit_nom" class="form-label">Nom de Famille</label>
          <input type="text" id="edit_nom" name="nom" class="form-control" required>
        </div>
        <div class="form-group">
          <label for="edit_prenom" class="form-label">Prénom</label>
          <input type="text" id="edit_prenom" name="prenom" class="form-control" required>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="edit_email" class="form-label">Adresse Email</label>
          <input type="email" id="edit_email" name="email" class="form-control" required>
        </div>
        <div class="form-group">
          <label for="edit_password" class="form-label">Nouveau Mot de Passe (optionnel)</label>
          <input type="password" id="edit_password" name="motDePasse" class="form-control" placeholder="Laisser vide si inchangé">
        </div>
      </div>

      <!-- Section Champs Enseignant (Spécifique) -->
      <div id="editEnseignantFields" style="display: none; padding-top: 0.5rem; border-top: 1px dashed var(--border); margin-top: 0.5rem;">
        <div class="form-row">
          <div class="form-group">
            <label for="edit_matricule" class="form-label">Matricule Enseignant</label>
            <input type="text" id="edit_matricule" name="matricule" class="form-control">
          </div>
          <div class="form-group">
            <label for="edit_specialite" class="form-label">Discipline / Spécialité</label>
            <input type="text" id="edit_specialite" name="specialite" class="form-control">
          </div>
        </div>
      </div>

      <!-- Section Champs Étudiant (Spécifique) -->
      <div id="editEtudiantFields" style="display: none; padding-top: 0.5rem; border-top: 1px dashed var(--border); margin-top: 0.5rem;">
        <div class="form-row">
          <div class="form-group">
            <label for="edit_cne" class="form-label">CNE (Code National Étudiant)</label>
            <input type="text" id="edit_cne" name="cne" class="form-control">
          </div>
          <div class="form-group">
            <label for="edit_dateNaissance" class="form-label">Date de Naissance</label>
            <input type="date" id="edit_dateNaissance" name="dateNaissance" class="form-control">
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-outline" data-modal-close>Annuler</button>
        <button type="submit" class="btn btn-primary">Enregistrer les Modifications</button>
      </div>
    </form>
  </div>
</div>

<script>
  const roleSelect = document.getElementById('roleSelect');
  const enseignantFields = document.getElementById('enseignantFields');
  const etudiantFields = document.getElementById('etudiantFields');

  roleSelect.addEventListener('change', () => {
    const role = roleSelect.value;
    if (role === 'ENSEIGNANT') {
      enseignantFields.style.display = 'block';
      etudiantFields.style.display = 'none';
      document.getElementById('matricule').required = true;
      document.getElementById('specialite').required = true;
      document.getElementById('cne').required = false;
      document.getElementById('dateNaissance').required = false;
    } else if (role === 'ETUDIANT') {
      enseignantFields.style.display = 'none';
      etudiantFields.style.display = 'block';
      document.getElementById('matricule').required = false;
      document.getElementById('specialite').required = false;
      document.getElementById('cne').required = true;
      document.getElementById('dateNaissance').required = true;
    } else {
      enseignantFields.style.display = 'none';
      etudiantFields.style.display = 'none';
      document.getElementById('matricule').required = false;
      document.getElementById('specialite').required = false;
      document.getElementById('cne').required = false;
      document.getElementById('dateNaissance').required = false;
    }
  });

  // Modal Modification Utilisateur
  document.querySelectorAll('.edit-user-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      const id = btn.getAttribute('data-id');
      const nom = btn.getAttribute('data-nom');
      const prenom = btn.getAttribute('data-prenom');
      const email = btn.getAttribute('data-email');
      const role = btn.getAttribute('data-role');
      const matricule = btn.getAttribute('data-matricule');
      const specialite = btn.getAttribute('data-specialite');
      const cne = btn.getAttribute('data-cne');
      const dateNaissance = btn.getAttribute('data-datenaissance');

      document.getElementById('edit_idUtilisateur').value = id;
      document.getElementById('edit_role').value = role;
      document.getElementById('edit_nom').value = nom;
      document.getElementById('edit_prenom').value = prenom;
      document.getElementById('edit_email').value = email;
      document.getElementById('edit_password').value = '';

      const editEns = document.getElementById('editEnseignantFields');
      const editEtu = document.getElementById('editEtudiantFields');

      if (role === 'ENSEIGNANT') {
        editEns.style.display = 'block';
        editEtu.style.display = 'none';
        document.getElementById('edit_matricule').value = matricule;
        document.getElementById('edit_specialite').value = specialite;
      } else if (role === 'ETUDIANT') {
        editEns.style.display = 'none';
        editEtu.style.display = 'block';
        document.getElementById('edit_cne').value = cne;
        document.getElementById('edit_dateNaissance').value = dateNaissance;
      } else {
        editEns.style.display = 'none';
        editEtu.style.display = 'none';
      }

      const modal = document.getElementById('editUserModal');
      if (modal) modal.classList.add('active');
    });
  });
</script>
