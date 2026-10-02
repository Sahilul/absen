<?php
/**
 * File: app/core/AiDocumentGenerator.php
 * Service untuk generate dokumen (PDF, Excel) dari AI response.
 * PDF: menggunakan Dompdf yang sudah ada di app/core/dompdf/
 * Excel: menggunakan native PHP (CSV-based XLSX via ZipArchive)
 */

use Dompdf\Dompdf;
use Dompdf\Options;

class AiDocumentGenerator
{
    public function __construct()
    {
        // Load Dompdf
        require_once APPROOT . '/app/core/dompdf/autoload.inc.php';
    }

    /**
     * Generate PDF dari HTML content.
     *
     * @param string $html     HTML content (bisa full HTML atau fragment)
     * @param string $filePath Path absolut untuk menyimpan file
     * @return string|null Path file yang berhasil dibuat, atau null jika gagal
     */
    public function generatePdf(string $html, string $filePath): ?string
    {
        try {
            // Wrap HTML jika belum ada tag <html>
            if (stripos($html, '<html') === false) {
                $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: "Times New Roman", Times, serif; font-size: 12pt; line-height: 1.5; margin: 2cm; color: #333; }
        h1 { font-size: 18pt; margin-bottom: 10pt; }
        h2 { font-size: 14pt; margin-bottom: 8pt; }
        h3 { font-size: 12pt; margin-bottom: 6pt; }
        table { width: 100%; border-collapse: collapse; margin: 10pt 0; }
        th, td { border: 1px solid #333; padding: 6pt 8pt; text-align: left; font-size: 10pt; }
        th { background-color: #f0f0f0; font-weight: bold; }
        p { margin: 0 0 6pt; }
        ul, ol { margin: 6pt 0; padding-left: 20pt; }
    </style>
</head>
<body>' . $html . '</body>
</html>';
            }

            $options = new Options();
            $options->set('isRemoteEnabled', false);
            $options->set('defaultFont', 'Times-Roman');
            $options->set('isPhpEnabled', false);
            $options->set('isJavascriptEnabled', false);

            $dompdf = new Dompdf($options);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            $output = $dompdf->output();
            file_put_contents($filePath, $output);

            return file_exists($filePath) ? $filePath : null;
        } catch (\Exception $e) {
            error_log("[AiDocumentGenerator] PDF generation error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Generate Excel (XLSX) dari array data.
     * Menggunakan native PHP + ZipArchive (tanpa PhpSpreadsheet).
     *
     * @param array  $data     Array of arrays (baris pertama = header)
     * @param string $filePath Path absolut untuk menyimpan file
     * @return string|null Path file yang berhasil dibuat, atau null jika gagal
     */
    public function generateExcel(array $data, string $filePath): ?string
    {
        try {
            if (empty($data)) {
                return null;
            }

            // Buat XLSX menggunakan ZipArchive (XLSX = ZIP of XML files)
            $zip = new \ZipArchive();
            if ($zip->open($filePath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
                return null;
            }

            // [Content_Types].xml
            $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
    <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
    <Default Extension="xml" ContentType="application/xml"/>
    <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
    <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
    <Override PartName="/xl/sharedStrings.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml"/>
    <Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>
</Types>');

            // _rels/.rels
            $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
    <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
</Relationships>');

            // xl/_rels/workbook.xml.rels
            $zip->addFromString('xl/_rels/workbook.xml.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
    <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
    <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings" Target="sharedStrings.xml"/>
    <Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>
</Relationships>');

            // xl/workbook.xml
            $zip->addFromString('xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
    <sheets>
        <sheet name="Sheet1" sheetId="1" r:id="rId1"/>
    </sheets>
</workbook>');

            // xl/styles.xml (basic styles with bold header)
            $zip->addFromString('xl/styles.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
    <fonts count="2">
        <font><sz val="11"/><name val="Calibri"/></font>
        <font><b/><sz val="11"/><name val="Calibri"/></font>
    </fonts>
    <fills count="3">
        <fill><patternFill patternType="none"/></fill>
        <fill><patternFill patternType="gray125"/></fill>
        <fill><patternFill patternType="solid"><fgColor rgb="FFE0F2FE"/></patternFill></fill>
    </fills>
    <borders count="2">
        <border><left/><right/><top/><bottom/></border>
        <border>
            <left style="thin"><color auto="1"/></left>
            <right style="thin"><color auto="1"/></right>
            <top style="thin"><color auto="1"/></top>
            <bottom style="thin"><color auto="1"/></bottom>
        </border>
    </borders>
    <cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>
    <cellXfs count="3">
        <xf numFmtId="0" fontId="0" fillId="0" borderId="0"/>
        <xf numFmtId="0" fontId="1" fillId="2" borderId="1" applyFont="1" applyFill="1" applyBorder="1"/>
        <xf numFmtId="0" fontId="0" fillId="0" borderId="1" applyBorder="1"/>
    </cellXfs>
</styleSheet>');

            // Build shared strings and sheet data
            $sharedStrings = [];
            $ssIndex = [];
            $sheetRows = '';

            foreach ($data as $rowIdx => $row) {
                if (!is_array($row)) continue;
                $sheetRows .= '<row r="' . ($rowIdx + 1) . '">';
                foreach ($row as $colIdx => $cellValue) {
                    $cellValue = (string) $cellValue;
                    $colLetter = $this->columnLetter($colIdx);
                    $cellRef = $colLetter . ($rowIdx + 1);

                    // Style: row 0 = header (bold + bg), rest = bordered
                    $styleIdx = $rowIdx === 0 ? 1 : 2;

                    // Check if numeric
                    if (is_numeric($cellValue) && $cellValue !== '') {
                        $sheetRows .= '<c r="' . $cellRef . '" s="' . $styleIdx . '"><v>' . htmlspecialchars($cellValue) . '</v></c>';
                    } else {
                        // Shared string
                        if (!isset($ssIndex[$cellValue])) {
                            $ssIndex[$cellValue] = count($sharedStrings);
                            $sharedStrings[] = $cellValue;
                        }
                        $sheetRows .= '<c r="' . $cellRef . '" t="s" s="' . $styleIdx . '"><v>' . $ssIndex[$cellValue] . '</v></c>';
                    }
                }
                $sheetRows .= '</row>';
            }

            // xl/sharedStrings.xml
            $ssCount = count($sharedStrings);
            $ssXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
            $ssXml .= '<sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" count="' . $ssCount . '" uniqueCount="' . $ssCount . '">';
            foreach ($sharedStrings as $str) {
                $ssXml .= '<si><t>' . htmlspecialchars($str) . '</t></si>';
            }
            $ssXml .= '</sst>';
            $zip->addFromString('xl/sharedStrings.xml', $ssXml);

            // xl/worksheets/sheet1.xml
            $sheetXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
            $sheetXml .= '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">';
            $sheetXml .= '<sheetData>' . $sheetRows . '</sheetData>';
            $sheetXml .= '</worksheet>';
            $zip->addFromString('xl/worksheets/sheet1.xml', $sheetXml);

            $zip->close();

            return file_exists($filePath) ? $filePath : null;
        } catch (\Exception $e) {
            error_log("[AiDocumentGenerator] Excel generation error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Convert column index (0-based) ke letter (A, B, ..., Z, AA, AB, ...).
     */
    private function columnLetter(int $index): string
    {
        $letter = '';
        while ($index >= 0) {
            $letter = chr(65 + ($index % 26)) . $letter;
            $index = intdiv($index, 26) - 1;
        }
        return $letter;
    }
}
