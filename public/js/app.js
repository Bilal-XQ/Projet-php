/**
 * Application de Gestion Pédagogique — Client JS Vanilla (ES6+)
 * Gère le thème bimodal (Dark/Light), le pliage de la sidebar, le tiroir mobile et les fenêtres modales.
 * 100% vectoriel, sans émojis.
 */

(function () {
  'use strict';

  // --- 1. Gestion du Thème Dark / Light (Icon-Only) ---
  const htmlElem = document.documentElement;
  const themeToggle = document.getElementById('themeToggle');
  const themeIcon = document.getElementById('themeIcon');

  const SUN_SVG = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide-icon"><circle cx="12" cy="12" r="4"/><path d="M12 2v2"/><path d="M12 20v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="m17.66 17.66 1.41 1.41"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="m6.34 17.66-1.41 1.41"/><path d="m19.07 4.93-1.41 1.41"/></svg>';
  const MOON_SVG = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide-icon"><path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/></svg>';

  function applyTheme(theme) {
    htmlElem.setAttribute('data-theme', theme);
    try {
      localStorage.setItem('app-theme', theme);
    } catch (e) {}

    if (themeIcon) {
      themeIcon.innerHTML = theme === 'light' ? MOON_SVG : SUN_SVG;
    }
  }

  const savedTheme = localStorage.getItem('app-theme') || 'dark';
  applyTheme(savedTheme);

  if (themeToggle) {
    themeToggle.addEventListener('click', () => {
      const currentTheme = htmlElem.getAttribute('data-theme') || 'dark';
      applyTheme(currentTheme === 'light' ? 'dark' : 'light');
    });
  }

  // --- 2. Pliage / Dépliage de la Sidebar (Desktop & Mobile) ---
  const sidebarToggle = document.getElementById('sidebarToggle') || document.getElementById('sidebarCollapseBtn') || document.getElementById('mobileMenuBtn');
  const sidebarBackdrop = document.getElementById('sidebarBackdrop');
  const body = document.body;

  function setSidebarCollapsed(collapsed) {
    if (collapsed) {
      body.classList.add('sidebar-collapsed');
    } else {
      body.classList.remove('sidebar-collapsed');
    }
    try {
      localStorage.setItem('sidebar-collapsed', collapsed ? 'true' : 'false');
    } catch (e) {}
  }

  // Restore collapsed state on desktop
  const isCollapsed = localStorage.getItem('sidebar-collapsed') === 'true';
  if (window.innerWidth >= 1024 && isCollapsed) {
    setSidebarCollapsed(true);
  }

  if (sidebarToggle) {
    sidebarToggle.addEventListener('click', (e) => {
      e.stopPropagation();
      if (window.innerWidth < 1024) {
        if (body.classList.contains('sidebar-open')) {
          body.classList.remove('sidebar-open');
        } else {
          body.classList.add('sidebar-open');
        }
      } else {
        const willCollapse = !body.classList.contains('sidebar-collapsed');
        setSidebarCollapsed(willCollapse);
      }
    });
  }

  if (sidebarBackdrop) {
    sidebarBackdrop.addEventListener('click', () => {
      body.classList.remove('sidebar-open');
    });
  }

  window.addEventListener('resize', () => {
    if (window.innerWidth >= 1024 && body.classList.contains('sidebar-open')) {
      body.classList.remove('sidebar-open');
    }
  });

  // --- 4. Gestion des Fenêtres Modales ---
  function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
      modal.classList.add('active');
      document.body.style.overflow = 'hidden';
      const firstInput = modal.querySelector('input:not([type=hidden]), select, textarea');
      if (firstInput) {
        setTimeout(() => firstInput.focus(), 100);
      }
    }
  }

  function closeModal(modal) {
    if (modal) {
      modal.classList.remove('active');
      document.body.style.overflow = '';
    }
  }

  window.openModal = openModal;
  window.closeModal = closeModal;

  document.addEventListener('click', (e) => {
    const openTrigger = e.target.closest('[data-modal-open]');
    if (openTrigger) {
      e.preventDefault();
      const targetId = openTrigger.getAttribute('data-modal-open');
      openModal(targetId);
      return;
    }

    const closeTrigger = e.target.closest('[data-modal-close]');
    if (closeTrigger) {
      e.preventDefault();
      const modal = closeTrigger.closest('.modal');
      closeModal(modal);
      return;
    }

    if (e.target.classList.contains('modal-backdrop')) {
      const modal = e.target.closest('.modal');
      closeModal(modal);
    }
  });

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      const activeModal = document.querySelector('.modal.active');
      if (activeModal) {
        closeModal(activeModal);
      }
    }
  });
})();
