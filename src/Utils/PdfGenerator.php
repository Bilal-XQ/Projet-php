<?php
declare(strict_types=1);

namespace App\Utils;

/**
 * Générateur PDF Natif et Autonome (PHP 8.3 Pur, Zéro Dépendance Tierce)
 * Conforme aux spécifications PDF 1.4 pour l'édition des documents académiques officiels.
 */
class PdfGenerator {
    private int $pageWidth = 595;   // A4 Portrait en points typographiques (72 dpi)
    private int $pageHeight = 842;
    private string $buffer = '';
    private array $offsets = [];
    private int $objectCount = 0;
    private string $contentStream = '';
    private float $currentY = 40.0;
    private float $margin = 40.0;

    public function __construct() {
        // Initialisation du document
    }

    /**
     * Échappe une chaîne de caractères pour les flux de texte PDF standard.
     */
    private function escapeString(string $text): string {
        // Conversion UTF-8 vers ISO-8859-1 (Latin1) pour les polices standards Type 1
        $latin1 = @iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $text) ?: $text;
        $latin1 = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $latin1);
        return $latin1;
    }

    /**
     * Convertit une coordonnée Y utilisateur (0 en haut) vers l'origine PDF (0 en bas).
     */
    private function y(float $userY): float {
        return $this->pageHeight - $userY;
    }

    /**
     * Dessine un rectangle plein ou avec bordure.
     */
    public function rect(
        float $x,
        float $y,
        float $w,
        float $h,
        string $style = 'D',
        ?array $fillColor = null,
        ?array $strokeColor = null,
        float $lineWidth = 1.0
    ): void {
        $pdfY = $this->y($y + $h);
        $cmd = '';

        if ($strokeColor !== null) {
            $cmd .= sprintf("%.3f %.3f %.3f RG\n", $strokeColor[0]/255, $strokeColor[1]/255, $strokeColor[2]/255);
        }
        if ($fillColor !== null) {
            $cmd .= sprintf("%.3f %.3f %.3f rg\n", $fillColor[0]/255, $fillColor[1]/255, $fillColor[2]/255);
        }
        $cmd .= sprintf("%.2f w\n", $lineWidth);
        $cmd .= sprintf("%.2f %.2f %.2f %.2f re\n", $x, $pdfY, $w, $h);

        if ($style === 'F') {
            $cmd .= "f\n";
        } elseif ($style === 'B') {
            $cmd .= "B\n";
        } else {
            $cmd .= "S\n";
        }

        $this->contentStream .= $cmd;
    }

    /**
     * Trace une ligne droite entre deux points.
     */
    public function line(float $x1, float $y1, float $x2, float $y2, array $color = [200, 200, 200], float $width = 0.75): void {
        $cmd = sprintf(
            "%.3f %.3f %.3f RG\n%.2f w\n%.2f %.2f m\n%.2f %.2f l\nS\n",
            $color[0]/255, $color[1]/255, $color[2]/255,
            $width,
            $x1, $this->y($y1),
            $x2, $this->y($y2)
        );
        $this->contentStream .= $cmd;
    }

    /**
     * Imprime un texte positionné.
     */
    public function text(
        float $x,
        float $y,
        string $text,
        string $font = 'F1',
        float $size = 10.0,
        array $color = [24, 24, 27]
    ): void {
        $escaped = $this->escapeString($text);
        $cmd = sprintf(
            "BT\n/%.2s %.2f Tf\n%.3f %.3f %.3f rg\n%.2f %.2f Td\n(%s) Tj\nET\n",
            $font, $size,
            $color[0]/255, $color[1]/255, $color[2]/255,
            $x, $this->y($y),
            $escaped
        );
        $this->contentStream .= $cmd;
    }

    /**
     * Imprime une cellule tabulaire avec fond optionnel et bordures fines.
     */
    public function cell(
        float $x,
        float $y,
        float $w,
        float $h,
        string $text,
        string $align = 'L',
        bool $border = true,
        ?array $fillColor = null,
        string $font = 'F1',
        float $fontSize = 8.5,
        array $textColor = [24, 24, 27]
    ): void {
        if ($fillColor !== null || $border) {
            $style = $fillColor !== null && $border ? 'B' : ($fillColor !== null ? 'F' : 'D');
            $this->rect($x, $y, $w, $h, $style, $fillColor, [228, 228, 231], 0.5);
        }

        // Alignement approximatif du texte
        $textY = $y + ($h / 2) + ($fontSize / 3);
        $charWidth = $fontSize * 0.52;
        $textLen = strlen($text);
        $textWidth = $textLen * $charWidth;

        $textX = $x + 4.0;
        if ($align === 'C') {
            $textX = $x + max(2.0, ($w - $textWidth) / 2);
        } elseif ($align === 'R') {
            $textX = $x + max(2.0, $w - $textWidth - 4.0);
        }

        $this->text($textX, $textY, $text, $font, $fontSize, $textColor);
    }

    /**
     * Rendu d'un badge de statut compact.
     */
    public function badge(float $x, float $y, float $w, float $h, string $text, string $status): void {
        $bgColor = [244, 244, 245];
        $textColor = [113, 113, 122];

        if ($status === 'V') {
            $bgColor = [220, 252, 231];
            $textColor = [22, 101, 52];
            $text = 'Valide';
        } elseif ($status === 'RATT') {
            $bgColor = [254, 243, 199];
            $textColor = [146, 64, 14];
            $text = 'Rattrapage';
        } elseif ($status === 'NV') {
            $bgColor = [254, 226, 226];
            $textColor = [153, 27, 27];
            $text = 'Non Valide';
        }

        $this->rect($x, $y, $w, $h, 'F', $bgColor, null, 0);
        $this->cell($x, $y, $w, $h, $text, 'C', false, null, 'F2', 7.5, $textColor);
    }

    /**
     * Construit le flux binaire PDF 1.4 complet.
     */
    public function render(): string {
        $this->buffer = "%PDF-1.4\n";
        $this->offsets = [];
        $this->objectCount = 0;

        // 1. Catalog
        $this->newObject();
        $this->buffer .= "<< /Type /Catalog /Pages 2 0 R >>\nendobj\n";

        // 2. Pages
        $this->newObject();
        $this->buffer .= "<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n";

        // 3. Page
        $this->newObject();
        $this->buffer .= sprintf(
            "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 %d %d] /Resources << /Font << /F1 5 0 R /F2 6 0 R >> >> /Contents 4 0 R >>\nendobj\n",
            $this->pageWidth,
            $this->pageHeight
        );

        // 4. Stream de contenu
        $streamLen = strlen($this->contentStream);
        $this->newObject();
        $this->buffer .= sprintf("<< /Length %d >>\nstream\n%sendstream\nendobj\n", $streamLen, $this->contentStream);

        // 5. Font F1 (Helvetica normal)
        $this->newObject();
        $this->buffer .= "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>\nendobj\n";

        // 6. Font F2 (Helvetica-Bold)
        $this->newObject();
        $this->buffer .= "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>\nendobj\n";

        // Table des références croisées (xref)
        $xrefOffset = strlen($this->buffer);
        $this->buffer .= sprintf("xref\n0 %d\n0000000000 65535 f \n", $this->objectCount + 1);

        for ($i = 1; $i <= $this->objectCount; $i++) {
            $this->buffer .= sprintf("%010d 00000 n \n", $this->offsets[$i]);
        }

        // Trailer
        $this->buffer .= sprintf(
            "trailer\n<< /Size %d /Root 1 0 R >>\nstartxref\n%d\n%%%%EOF\n",
            $this->objectCount + 1,
            $xrefOffset
        );

        return $this->buffer;
    }

    private function newObject(): void {
        $this->objectCount++;
        $this->offsets[$this->objectCount] = strlen($this->buffer);
        $this->buffer .= "{$this->objectCount} 0 obj\n";
    }

    /**
     * Génère le relevé officiel de notes et résultats pour un étudiant.
     */
    public static function generateStudentReleve(
        array $student,
        array $filiere,
        array $notes,
        array $gpa
    ): string {
        $pdf = new self();

        // 1. En-tête Institutionnel
        $pdf->rect(40, 30, 515, 65, 'B', [248, 250, 252], [226, 232, 240], 1.0);
        $pdf->text(55, 48, "ROYAUME DU MAROC — ENSEIGNEMENT SUPERIEUR", 'F2', 8.5, [71, 85, 105]);
        $pdf->text(55, 62, "UNIVERSITE — DEPARTEMENT MATHEMATIQUES & INFORMATIQUE", 'F2', 10.0, [15, 23, 42]);
        $pdf->text(55, 78, "RELEVE DE NOTES ET RESULTATS ACADEMIQUES OFFICIEL", 'F2', 12.0, [30, 41, 59]);

        // Badge Année Universitaire
        $pdf->rect(440, 42, 100, 20, 'F', [238, 242, 255]);
        $pdf->cell(440, 42, 100, 20, $filiere['anneeUniversitaire'] ?? '2026-2027', 'C', false, null, 'F2', 9.0, [67, 56, 202]);

        // 2. Bloc Métadonnées Étudiant
        $pdf->rect(40, 105, 515, 60, 'B', [255, 255, 255], [228, 228, 231], 0.75);

        // Colonne 1
        $pdf->text(52, 122, "Nom & Prenom :", 'F2', 8.5, [113, 113, 122]);
        $pdf->text(130, 122, strtoupper((string)$student['nom']) . ' ' . (string)$student['prenom'], 'F2', 9.5, [24, 24, 27]);

        $pdf->text(52, 138, "Code CNE :", 'F2', 8.5, [113, 113, 122]);
        $pdf->text(130, 138, (string)$student['cne'], 'F1', 9.0, [24, 24, 27]);

        $pdf->text(52, 154, "Date de Naissance :", 'F2', 8.5, [113, 113, 122]);
        $pdf->text(130, 154, (string)($student['dateNaissance'] ?? 'Non renseignee'), 'F1', 9.0, [24, 24, 27]);

        // Colonne 2
        $pdf->text(310, 122, "Filiere :", 'F2', 8.5, [113, 113, 122]);
        $filText = (string)($filiere['code'] ?? 'INFO-L3');
        if (!empty($filiere['intitule'])) {
            $filText .= ' - ' . substr((string)$filiere['intitule'], 0, 28);
        }
        $pdf->text(360, 122, $filText, 'F2', 8.0, [24, 24, 27]);

        $pdf->text(310, 138, "Statut :", 'F2', 8.5, [113, 113, 122]);
        $pdf->text(360, 138, "Inscrit en regle (Session Principale)", 'F1', 8.5, [22, 101, 52]);

        $pdf->text(310, 154, "Date d'edition :", 'F2', 8.5, [113, 113, 122]);
        $pdf->text(360, 154, date('d/m/Y - H:i'), 'F1', 8.5, [113, 113, 122]);

        // 3. Tableau des Modules et Évaluations (Observation retirée)
        $y = 180;
        $cols = [
            ['title' => 'Code',               'w' => 80,  'align' => 'L'],
            ['title' => 'Intitule du Module', 'w' => 250, 'align' => 'L'],
            ['title' => 'Semestre',           'w' => 60,  'align' => 'C'],
            ['title' => 'Note /20',           'w' => 65,  'align' => 'C'],
            ['title' => 'Statut',             'w' => 60,  'align' => 'C'],
        ];

        // En-tête du tableau
        $curX = 40;
        foreach ($cols as $col) {
            $pdf->cell($curX, $y, $col['w'], 22, $col['title'], $col['align'], true, [241, 245, 249], 'F2', 8.0, [51, 65, 85]);
            $curX += $col['w'];
        }
        $y += 22;

        // Lignes de données
        if (empty($notes)) {
            $pdf->cell(40, $y, 515, 30, "Aucune note disponible pour le moment.", 'C', true, [255, 255, 255], 'F1', 9.0, [113, 113, 122]);
            $y += 30;
        } else {
            $i = 0;
            foreach ($notes as $n) {
                $bg = $i % 2 === 0 ? [255, 255, 255] : [250, 250, 250];
                $curX = 40;

                $finale = $n['noteFinale'] !== null ? number_format((float)$n['noteFinale'], 2) : '-';

                $pdf->cell($curX, $y, 80, 20, (string)$n['module_code'], 'L', true, $bg, 'F2', 8.0); $curX += 80;
                $pdf->cell($curX, $y, 250, 20, substr((string)$n['module_intitule'], 0, 50), 'L', true, $bg, 'F1', 8.0); $curX += 250;
                $pdf->cell($curX, $y, 60, 20, (string)($n['semestre'] ?? 'S1'), 'C', true, $bg, 'F1', 8.0); $curX += 60;
                $pdf->cell($curX, $y, 65, 20, $finale, 'C', true, $bg, 'F2', 8.5); $curX += 65;

                // Badge de validation
                $statut = $n['statutValidation'] ?? 'NV';
                $pdf->badge($curX + 5, $y + 3, 50, 14, (string)$statut, (string)$statut);
                $curX += 60;

                $y += 20;
                $i++;
            }
        }

        // 4. Synthèse et Décision du Jury
        $y += 15;
        $pdf->rect(40, $y, 515, 60, 'B', [248, 250, 252], [203, 213, 225], 1.0);

        $moyenne = $gpa['moyenne'] !== null ? number_format((float)$gpa['moyenne'], 2) . " / 20.00" : "En cours";
        $decision = $gpa['decision'] ?? 'En cours';

        $pdf->text(55, $y + 20, "Modules Evalues :", 'F2', 9.0, [71, 85, 105]);
        $pdf->text(160, $y + 20, "{$gpa['modulesEvalues']} / {$gpa['totalModules']}", 'F2', 9.5, [15, 23, 42]);

        $pdf->text(55, $y + 40, "Moyenne Generale :", 'F2', 10.0, [71, 85, 105]);
        $pdf->text(160, $y + 40, $moyenne, 'F2', 12.0, [30, 41, 59]);

        $pdf->text(310, $y + 20, "Modules Valides :", 'F2', 9.0, [71, 85, 105]);
        $pdf->text(410, $y + 20, "{$gpa['modulesValides']} / {$gpa['totalModules']}", 'F2', 9.5, [22, 101, 52]);

        $pdf->text(310, $y + 42, "Decision du Jury :", 'F2', 10.0, [71, 85, 105]);

        $decColor = [30, 41, 59];
        if ($decision === 'Admis') {
            $decColor = [22, 101, 52];
        } elseif ($decision === 'Ajourne') {
            $decColor = [153, 27, 27];
        }
        $pdf->text(410, $y + 42, strtoupper($decision), 'F2', 11.5, $decColor);

        // Pied de page
        $pdf->line(40, 790, 555, 790, [228, 228, 231], 0.5);
        $pdf->text(40, 804, "Gestion Pedagogique Departement Math-Info — Ce document est authentique et certifie conforme.", 'F1', 7.0, [161, 161, 170]);
        $pdf->text(495, 804, "Page 1 sur 1", 'F1', 7.0, [161, 161, 170]);

        return $pdf->render();
    }

    /**
     * Génère le Procès-Verbal officiel de notes d'un module pour l'enseignant.
     */
    public static function generateModuleRoster(
        array $module,
        array $students,
        array $stats
    ): string {
        $pdf = new self();

        // 1. En-tête
        $pdf->rect(40, 30, 515, 65, 'B', [248, 250, 252], [226, 232, 240], 1.0);
        $pdf->text(55, 48, "DEPARTEMENT MATHEMATIQUES & INFORMATIQUE", 'F2', 8.5, [71, 85, 105]);
        $pdf->text(55, 63, "PROCES-VERBAL D'EVALUATION DU MODULE", 'F2', 12.0, [15, 23, 42]);
        $pdf->text(55, 80, (string)$module['code'] . ' — ' . (string)$module['intitule'], 'F2', 9.5, [30, 41, 59]);

        // Badge Filière & Semestre
        $pdf->rect(430, 42, 110, 22, 'F', [238, 242, 255]);
        $pdf->cell(430, 42, 110, 22, (string)($module['filiere_code'] ?? '') . ' | ' . (string)$module['semestre'], 'C', false, null, 'F2', 9.0, [67, 56, 202]);

        // 2. Métadonnées du Module
        $pdf->rect(40, 105, 515, 45, 'B', [255, 255, 255], [228, 228, 231], 0.75);
        $pdf->text(52, 122, "Enseignant :", 'F2', 8.5, [113, 113, 122]);
        $pdf->text(120, 122, (string)($module['enseignant_prenom'] ?? '') . ' ' . (string)($module['enseignant_nom'] ?? ''), 'F2', 9.0, [24, 24, 27]);

        $pdf->text(52, 138, "Evaluation :", 'F2', 8.5, [113, 113, 122]);
        $pdf->text(120, 138, "Directe sur 20", 'F1', 9.0, [24, 24, 27]);

        $pdf->text(310, 122, "Taux de Reussite :", 'F2', 8.5, [113, 113, 122]);
        $pdf->text(400, 122, number_format((float)($stats['tauxReussite'] ?? 0.0), 1) . " %", 'F2', 9.0, [22, 101, 52]);

        $pdf->text(310, 138, "Moyenne Generale :", 'F2', 8.5, [113, 113, 122]);
        $pdf->text(400, 138, number_format((float)($stats['moyenneFinale'] ?? 0.0), 2) . " / 20.00", 'F2', 9.0, [24, 24, 27]);

        // 3. Tableau d'évaluation des étudiants (Observation retirée)
        $y = 165;
        $cols = [
            ['title' => 'CNE',          'w' => 100, 'align' => 'L'],
            ['title' => 'Nom & Prenom', 'w' => 255, 'align' => 'L'],
            ['title' => 'Note /20',     'w' => 80,  'align' => 'C'],
            ['title' => 'Statut',       'w' => 80,  'align' => 'C'],
        ];

        $curX = 40;
        foreach ($cols as $col) {
            $pdf->cell($curX, $y, $col['w'], 20, $col['title'], $col['align'], true, [241, 245, 249], 'F2', 8.0, [51, 65, 85]);
            $curX += $col['w'];
        }
        $y += 20;

        $i = 0;
        foreach ($students as $s) {
            $bg = $i % 2 === 0 ? [255, 255, 255] : [250, 250, 250];
            $curX = 40;

            $finale = $s['noteFinale'] !== null ? number_format((float)$s['noteFinale'], 2) : '-';
            $nomComplet = strtoupper((string)($s['etudiant_nom'] ?? '')) . ' ' . (string)($s['etudiant_prenom'] ?? '');

            $pdf->cell($curX, $y, 100, 18, (string)($s['etudiant_cne'] ?? ''), 'L', true, $bg, 'F1', 7.5); $curX += 100;
            $pdf->cell($curX, $y, 255, 18, substr($nomComplet, 0, 40), 'L', true, $bg, 'F2', 8.0); $curX += 255;
            $pdf->cell($curX, $y, 80, 18, $finale, 'C', true, $bg, 'F2', 8.5); $curX += 80;

            $statut = $s['statutValidation'] ?? 'NV';
            $pdf->badge($curX + 15, $y + 2, 50, 14, (string)$statut, (string)$statut);
            $curX += 80;

            $y += 18;
            $i++;
        }

        // 4. Signature
        $y += 30;
        $pdf->rect(325, $y, 230, 60, 'D', null, [228, 228, 231], 0.5);
        $pdf->text(335, $y + 16, "Signature de l'Enseignant Responsable", 'F2', 8.5, [71, 85, 105]);
        $pdf->text(335, $y + 50, "Fait a Rabat, le " . date('d/m/Y'), 'F1', 7.5, [113, 113, 122]);

        // Pied de page
        $pdf->line(40, 790, 555, 790, [228, 228, 231], 0.5);
        $pdf->text(40, 804, "Departement Mathematiques & Informatique — Proces-Verbal Officiel des Evaluations", 'F1', 7.0, [161, 161, 170]);
        $pdf->text(495, 804, "Page 1 sur 1", 'F1', 7.0, [161, 161, 170]);

        return $pdf->render();
    }

    /**
     * Envoie le document PDF au navigateur client.
     */
    public static function stream(string $pdfContent, string $filename, bool $attachment = false): void {
        if (ob_get_level()) {
            ob_end_clean();
        }

        header('Content-Type: application/pdf');
        header('Content-Length: ' . strlen($pdfContent));
        $disposition = $attachment ? 'attachment' : 'inline';
        header("Content-Disposition: {$disposition}; filename=\"{$filename}\"");
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');

        echo $pdfContent;
        exit;
    }
}
