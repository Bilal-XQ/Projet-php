# Système de Design UI/UX — Cahier des Charges Visuel & Composants

## 1. Principes Directeurs (Anti "AI Slop")
* **Palette de gris neutres shadcn (`Zinc`) :** Contraste calibré, typographie soignée et bordures de 1px nettes. Zéro dégradés criards, zéro ombres violettes ou néon.
* **Support bimodal complet (Dark / Light) :** L'application commute entre le mode sombre par défaut et un mode clair épuré via l'attribut racine `data-theme="light"` ou `data-theme="dark"`.
* **Densité maîtrisée :** Espacements compacts adaptés à un outil de gestion académique (tableaux denses, inputs précis, headers collants).
* **Code 100% natif :** Aucun framework JS ni CSS (pas de Tailwind, pas de Bootstrap, pas de React). Tout repose sur CSS3 et JavaScript ES6+ pur.
* **Responsive total :**
  * Bureau : Sidebar complète (260px) repliable en bandeau d'icônes (68px).
  * Mobile (< 768px) : Tiroir hors écran (Off-canvas) avec masque sombre tactile.

---

## 2. Tokens de Design & Variables CSS (`public/css/style.css`)

```css
/* Mode Sombre (Par défaut) */
:root,
[data-theme="dark"] {
  --bg-app: #09090b;             /* Zinc 950 */
  --bg-sidebar: #09090b;         /* Zinc 950 */
  --bg-surface: #18181b;         /* Zinc 900 */
  --bg-surface-hover: #27272a;   /* Zinc 800 */
  --bg-input: #09090b;           /* Zinc 950 */
  --bg-table-th: #121215;

  --text-main: #f4f4f5;          /* Zinc 100 */
  --text-muted: #a1a1aa;         /* Zinc 400 */
  --text-subtle: #71717a;        /* Zinc 500 */
  --text-inverse: #09090b;       /* Zinc 950 */

  --border: #27272a;             /* Zinc 800 */
  --border-focus: #52525b;       /* Zinc 600 */

  --primary: #fafafa;            /* Zinc 50 */
  --primary-hover: #e4e4e7;      /* Zinc 200 */

  --destructive: #ef4444;
  --destructive-bg: rgba(239, 68, 68, 0.12);
  --success: #10b981;
  --success-bg: rgba(16, 185, 129, 0.12);
  --warning: #f59e0b;
  --warning-bg: rgba(245, 158, 11, 0.12);

  --shadow-card: 0 1px 3px rgba(0, 0, 0, 0.4);
}

/* Mode Clair (shadcn Zinc Light) */
[data-theme="light"] {
  --bg-app: #fafafa;             /* Zinc 50 */
  --bg-sidebar: #ffffff;         /* Blanc pur */
  --bg-surface: #ffffff;         /* Blanc pur */
  --bg-surface-hover: #f4f4f5;   /* Zinc 100 */
  --bg-input: #ffffff;
  --bg-table-th: #f4f4f5;

  --text-main: #09090b;          /* Zinc 950 */
  --text-muted: #71717a;         /* Zinc 500 */
  --text-subtle: #a1a1aa;        /* Zinc 400 */
  --text-inverse: #ffffff;

  --border: #e4e4e7;             /* Zinc 200 */
  --border-focus: #a1a1aa;       /* Zinc 400 */

  --primary: #18181b;            /* Zinc 900 */
  --primary-hover: #27272a;      /* Zinc 800 */

  --destructive: #dc2626;
  --destructive-bg: rgba(220, 38, 38, 0.08);
  --success: #059669;
  --success-bg: rgba(5, 150, 105, 0.08);
  --warning: #d97706;
  --warning-bg: rgba(217, 119, 6, 0.08);

  --shadow-card: 0 1px 3px rgba(0, 0, 0, 0.05), 0 1px 2px rgba(0, 0, 0, 0.03);
}

/* Variables Géométriques Communes */
:root {
  --radius-sm: 4px;
  --radius-md: 6px;
  --radius-lg: 8px;
  --transition-fast: 150ms cubic-bezier(0.4, 0, 0.2, 1);

  --sidebar-width: 260px;
  --sidebar-collapsed-width: 68px;
  --header-height: 56px;
}

/* Base Reset */
*, *::before, *::after {
  box-sizing: border-box;
  margin: 0;
  padding: 0;
}

body {
  font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", sans-serif;
  background-color: var(--bg-app);
  color: var(--text-main);
  line-height: 1.5;
  -webkit-font-smoothing: antialiased;
  transition: background-color var(--transition-fast), color var(--transition-fast);
  overflow-x: hidden;
}