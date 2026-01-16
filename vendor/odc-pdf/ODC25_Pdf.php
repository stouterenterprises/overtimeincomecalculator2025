<?php

if (!defined('ABSPATH')) {
    exit;
}

class ODC25_Pdf
{
    private array $pages = [[]];

    public function add_title(string $title): void
    {
        $this->pages[$this->get_page_index()][] = strtoupper($title);
    }

    public function add_text(string $text): void
    {
        $this->pages[$this->get_page_index()][] = $text;
    }

    public function add_page(): void
    {
        $this->pages[] = [];
    }

    private function get_page_index(): int
    {
        return count($this->pages) - 1;
    }

    public function output(): string
    {
        $objects = [];
        $objects[] = "1 0 obj << /Type /Catalog /Pages 2 0 R >> endobj";
        $objects[] = "3 0 obj << /Type /Font /Subtype /Type1 /BaseFont /Helvetica >> endobj";

        $content_objects = [];
        $page_count = count($this->pages);
        $kids = [];
        $object_index = 4;

        foreach ($this->pages as $page) {
            $content = "";
            $y = 800;
            foreach ($page as $line) {
                $escaped = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $line);
                $content .= sprintf("BT /F1 12 Tf 40 %d Td (%s) Tj ET\n", $y, $escaped);
                $y -= 16;
            }
            $content_objects[] = $content;
            $kids[] = $object_index . " 0 R";
            $object_index += 2;
        }

        $objects[] = "2 0 obj << /Type /Pages /Kids [" . implode(' ', $kids) . "] /Count " . $page_count . " >> endobj";

        $object_id = 4;
        $content_id = 5;
        foreach ($content_objects as $content) {
            $objects[] = $object_id . " 0 obj << /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents " . $content_id . " 0 R /Resources << /Font << /F1 3 0 R >> >> >> endobj";
            $objects[] = $content_id . " 0 obj << /Length " . strlen($content) . " >> stream\n" . $content . "endstream endobj";
            $object_id += 2;
            $content_id += 2;
        }

        $pdf = "%PDF-1.4\n";
        $offsets = [0];
        foreach ($objects as $object) {
            $offsets[] = strlen($pdf);
            $pdf .= $object . "\n";
        }

        $xref = strlen($pdf);
        $pdf .= "xref\n0 " . count($offsets) . "\n";
        foreach ($offsets as $offset) {
            $pdf .= sprintf("%010d 00000 n \n", $offset);
        }

        $pdf .= "trailer << /Size " . count($offsets) . " /Root 1 0 R >>\nstartxref\n" . $xref . "\n%%EOF";

        return $pdf;
    }
}
