<?php
require '../vendor/autoload.php';

use PhpOffice\PhpWord\TemplateProcessor;

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_errors.log');

header('Content-Type: application/json');

try {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        throw new Exception("Invalid input JSON");
    }

    $templatePath = __DIR__ . '/PaySlipTemplate_custom.docx';
    if (!file_exists($templatePath)) {
        throw new Exception("Template file not found: " . $templatePath);
    }

    $template = new TemplateProcessor($templatePath);

    foreach ($data as $key => $value) {
        $template->setValue('{' . strtoupper($key) . '}', $value ?? '');
    }

    // Save to temp DOCX
    $tempDir = __DIR__ . '/temp';
    if (!is_dir($tempDir)) {
        mkdir($tempDir, 0777, true);
    }
    $docxFile = $tempDir . '/preview_' . uniqid() . '.docx';
    $pdfFile = $tempDir . '/preview_' . uniqid() . '.pdf';
    $template->saveAs($docxFile);

    // Convert DOCX to PDF using LibreOffice (must be installed on your server)
    $command = "soffice --headless --convert-to pdf --outdir " . escapeshellarg($tempDir) . " " . escapeshellarg($docxFile);
    exec($command, $output, $returnCode);

    if ($returnCode !== 0) {
        throw new Exception("DOCX to PDF conversion failed. Ensure LibreOffice is installed and in PATH.");
    }

    // Find generated PDF
    $generatedPdf = preg_replace('/\.docx$/', '.pdf', $docxFile);
    if (!file_exists($generatedPdf)) {
        throw new Exception("PDF not created from DOCX.");
    }

    // Return PDF URL for iframe
    $pdfUrl = 'temp/' . basename($generatedPdf);

    echo json_encode([
        'success' => true,
        'url' => $pdfUrl,
        'file' => basename($generatedPdf)  // <-- add this
    ]);

} catch (Exception $e) {
    error_log("Preview generation failed: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
exit;
