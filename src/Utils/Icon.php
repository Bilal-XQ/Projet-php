<?php
declare(strict_types=1);

namespace App\Utils;

/**
 * Générateur d'Icônes SVG Lucide (Sans Emojis)
 * Génère des icônes vectorielles nettes, légères et stylées selon la charte shadcn Zinc.
 */
class Icon {
    public static function get(string $name, int $size = 16, string $extraClass = ''): string {
        $classAttr = 'lucide-icon' . ($extraClass !== '' ? ' ' . $extraClass : '');
        $svgAttrs = sprintf(
            'xmlns="http://www.w3.org/2000/svg" width="%d" height="%d" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="%s"',
            $size,
            $size,
            htmlspecialchars($classAttr)
        );

        switch ($name) {
            case 'graduation-cap':
                return "<svg {$svgAttrs}><path d=\"M21.42 10.922a1 1 0 0 0-.019-1.838L12.83 5.18a2 2 0 0 0-1.66 0L2.6 9.08a1 1 0 0 0 0 1.832l8.57 3.908a2 2 0 0 0 1.66 0z\"/><path d=\"M22 10v6\"/><path d=\"M6 12.5V16a6 3 0 0 0 12 0v-3.5\"/></svg>";

            case 'layout-dashboard':
                return "<svg {$svgAttrs}><rect width=\"7\" height=\"9\" x=\"3\" y=\"3\" rx=\"1\"/><rect width=\"7\" height=\"5\" x=\"14\" y=\"3\" rx=\"1\"/><rect width=\"7\" height=\"9\" x=\"14\" y=\"12\" rx=\"1\"/><rect width=\"7\" height=\"5\" x=\"3\" y=\"16\" rx=\"1\"/></svg>";

            case 'landmark':
                return "<svg {$svgAttrs}><line x1=\"3\" x2=\"21\" y1=\"22\" y2=\"22\"/><line x1=\"6\" x2=\"6\" y1=\"18\" y2=\"11\"/><line x1=\"10\" x2=\"10\" y1=\"18\" y2=\"11\"/><line x1=\"14\" x2=\"14\" y1=\"18\" y2=\"11\"/><line x1=\"18\" x2=\"18\" y1=\"18\" y2=\"11\"/><polygon points=\"12 2 20 7 4 7\"/></svg>";

            case 'book-open':
                return "<svg {$svgAttrs}><path d=\"M12 7v14\"/><path d=\"M3 18a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h5a4 4 0 0 1 4 4 4 4 0 0 1 4-4h5a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1h-6a3 3 0 0 0-3 3 3 3 0 0 0-3-3z\"/></svg>";

            case 'users':
                return "<svg {$svgAttrs}><path d=\"M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2\"/><circle cx=\"9\" cy=\"7\" r=\"4\"/><path d=\"M22 21v-2a4 4 0 0 0-3-3.87\"/><path d=\"M16 3.13a4 4 0 0 1 0 7.75\"/></svg>";

            case 'user':
                return "<svg {$svgAttrs}><path d=\"M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2\"/><circle cx=\"12\" cy=\"7\" r=\"4\"/></svg>";

            case 'user-check':
                return "<svg {$svgAttrs}><path d=\"M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2\"/><circle cx=\"9\" cy=\"7\" r=\"4\"/><polyline points=\"16 11 18 13 22 9\"/></svg>";

            case 'clipboard-list':
                return "<svg {$svgAttrs}><rect width=\"8\" height=\"4\" x=\"8\" y=\"2\" rx=\"1\" ry=\"1\"/><path d=\"M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2\"/><path d=\"M12 11h4\"/><path d=\"M12 16h4\"/><path d=\"M8 11h.01\"/><path d=\"M8 16h.01\"/></svg>";

            case 'file-text':
                return "<svg {$svgAttrs}><path d=\"M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z\"/><path d=\"M14 2v4a2 2 0 0 0 2 2h4\"/><path d=\"M10 9H8\"/><path d=\"M16 13H8\"/><path d=\"M16 17H8\"/></svg>";

            case 'clock':
                return "<svg {$svgAttrs}><circle cx=\"12\" cy=\"12\" r=\"10\"/><polyline points=\"12 6 12 12 16 14\"/></svg>";

            case 'megaphone':
                return "<svg {$svgAttrs}><path d=\"m3 11 18-5v12L3 14v-3z\"/><path d=\"M11.6 16.8a3 3 0 1 1-5.8-1.6\"/></svg>";

            case 'shield-check':
                return "<svg {$svgAttrs}><path d=\"M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z\"/><path d=\"m9 12 2 2 4-4\"/></svg>";

            case 'calendar':
                return "<svg {$svgAttrs}><path d=\"M8 2v4\"/><path d=\"M16 2v4\"/><rect width=\"18\" height=\"18\" x=\"3\" y=\"4\" rx=\"2\"/><path d=\"M3 10h18\"/></svg>";

            case 'sun':
                return "<svg {$svgAttrs}><circle cx=\"12\" cy=\"12\" r=\"4\"/><path d=\"M12 2v2\"/><path d=\"M12 20v2\"/><path d=\"m4.93 4.93 1.41 1.41\"/><path d=\"m17.66 17.66 1.41 1.41\"/><path d=\"M2 12h2\"/><path d=\"M20 12h2\"/><path d=\"m6.34 17.66-1.41 1.41\"/><path d=\"m19.07 4.93-1.41 1.41\"/></svg>";

            case 'moon':
                return "<svg {$svgAttrs}><path d=\"M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z\"/></svg>";

            case 'log-out':
                return "<svg {$svgAttrs}><path d=\"M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4\"/><polyline points=\"16 17 21 12 16 7\"/><line x1=\"21\" x2=\"9\" y1=\"12\" y2=\"12\"/></svg>";

            case 'pencil':
                return "<svg {$svgAttrs}><path d=\"M21.174 6.812a1 1 0 0 0-3.986-3.987L3.842 16.174a2 2 0 0 0-.5.83l-1.321 4.352a.5.5 0 0 0 .623.622l4.353-1.32a2 2 0 0 0 .83-.497z\"/><path d=\"m15 5 4 4\"/></svg>";

            case 'trash-2':
                return "<svg {$svgAttrs}><path d=\"M3 6h18\"/><path d=\"M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6\"/><path d=\"M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2\"/><line x1=\"10\" x2=\"10\" y1=\"11\" y2=\"17\"/><line x1=\"14\" x2=\"14\" y1=\"11\" y2=\"17\"/></svg>";

            case 'plus':
                return "<svg {$svgAttrs}><path d=\"M5 12h14\"/><path d=\"M12 5v14\"/></svg>";

            case 'check':
                return "<svg {$svgAttrs}><polyline points=\"20 6 9 17 4 12\"/></svg>";

            case 'x':
                return "<svg {$svgAttrs}><path d=\"M18 6 6 18\"/><path d=\"m6 6 12 12\"/></svg>";

            case 'search':
                return "<svg {$svgAttrs}><circle cx=\"11\" cy=\"11\" r=\"8\"/><path d=\"m21 21-4.3-4.3\"/></svg>";

            case 'chevron-left':
                return "<svg {$svgAttrs}><path d=\"m15 18-6-6 6-6\"/></svg>";

            case 'chevron-right':
                return "<svg {$svgAttrs}><path d=\"m9 18 6-6-6-6\"/></svg>";

            case 'menu':
                return "<svg {$svgAttrs}><line x1=\"4\" x2=\"20\" y1=\"12\" y2=\"12\"/><line x1=\"4\" x2=\"20\" y1=\"6\" y2=\"6\"/><line x1=\"4\" x2=\"20\" y1=\"18\" y2=\"18\"/></svg>";

            case 'award':
                return "<svg {$svgAttrs}><circle cx=\"12\" cy=\"8\" r=\"6\"/><path d=\"M15.477 12.89 17 22l-5-3-5 3 1.523-9.11\"/></svg>";

            case 'arrow-right':
                return "<svg {$svgAttrs}><path d=\"M5 12h14\"/><path d=\"m12 5 7 7-7 7\"/></svg>";

            case 'arrow-left-right':
                return "<svg {$svgAttrs}><path d=\"m16 3 4 4-4 4\"/><path d=\"M20 7H4\"/><path d=\"m8 21-4-4 4-4\"/><path d=\"M4 17h16\"/></svg>";

            default:
                return "<svg {$svgAttrs}><circle cx=\"12\" cy=\"12\" r=\"10\"/></svg>";
        }
    }
}
