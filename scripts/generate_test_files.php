<?php

$baseDir = dirname(__DIR__).'/test-files';

if (! is_dir($baseDir)) {
    mkdir($baseDir, 0777, true);
}

function esc(string $value): string
{
    return str_replace(['\\', '(', ')'], ['\\\\', '\(', '\)'], $value);
}

function createNativePdf(string $path, array $lines): void
{
    $content = "BT\n/F1 14 Tf\n50 780 Td\n";

    foreach ($lines as $index => $line) {
        if ($index > 0) {
            $content .= "0 -22 Td\n";
        }
        $content .= '('.esc($line).") Tj\n";
    }

    $content .= "ET\n";

    $objects = [
        '<< /Type /Catalog /Pages 2 0 R >>',
        '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
        '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>',
        '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
        "<< /Length ".strlen($content)." >>\nstream\n".$content.'endstream',
    ];

    $pdf = "%PDF-1.4\n";
    $offsets = [0];

    foreach ($objects as $i => $obj) {
        $objNum = $i + 1;
        $offsets[$objNum] = strlen($pdf);
        $pdf .= $objNum." 0 obj\n".$obj."\nendobj\n";
    }

    $xrefPos = strlen($pdf);
    $pdf .= "xref\n0 ".(count($objects) + 1)."\n";
    $pdf .= "0000000000 65535 f \n";

    for ($i = 1; $i <= count($objects); $i++) {
        $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
    }

    $pdf .= "trailer\n<< /Size ".(count($objects) + 1)." /Root 1 0 R >>\nstartxref\n".$xrefPos."\n%%EOF";

    file_put_contents($path, $pdf);
}

function createImageWithText(string $path, string $format, array $lines): void
{
    $width = 1400;
    $height = 950;
    $image = imagecreatetruecolor($width, $height);

    $bg = imagecolorallocate($image, 250, 252, 255);
    $titleColor = imagecolorallocate($image, 17, 24, 39);
    $textColor = imagecolorallocate($image, 31, 41, 55);
    $lineColor = imagecolorallocate($image, 203, 213, 225);

    imagefilledrectangle($image, 0, 0, $width, $height, $bg);
    imageline($image, 40, 120, $width - 40, 120, $lineColor);

    $fontPath = 'C:/Windows/Fonts/arial.ttf';

    if (is_file($fontPath) && function_exists('imagettftext')) {
        imagettftext($image, 40, 0, 45, 85, $titleColor, $fontPath, 'ARQUIVO DE TESTE OCR');

        $y = 190;
        foreach ($lines as $line) {
            imagettftext($image, 30, 0, 50, $y, $textColor, $fontPath, $line);
            $y += 70;
        }
    } else {
        imagestring($image, 5, 45, 60, 'ARQUIVO DE TESTE OCR', $titleColor);
        $y = 150;
        foreach ($lines as $line) {
            imagestring($image, 5, 50, $y, $line, $textColor);
            $y += 45;
        }
    }

    switch ($format) {
        case 'png':
            imagepng($image, $path);
            break;
        case 'jpg':
        case 'jpeg':
            imagejpeg($image, $path, 92);
            break;
        case 'webp':
            imagewebp($image, $path, 90);
            break;
        default:
            throw new RuntimeException('Formato nao suportado: '.$format);
    }

    imagedestroy($image);
}

createNativePdf($baseDir.'/01-documento-nativo.pdf', [
    'Documento OCR de Teste 01',
    'Cliente: Maria da Silva',
    'CPF: 123.456.789-10',
    'Total: R$ 598,42',
    'Data: 24/03/2026',
]);

createNativePdf($baseDir.'/02-contrato-nativo.pdf', [
    'Contrato Simples de Prestacao de Servico',
    'Contratante: Empresa ABC LTDA',
    'Prestador: Joao Santos',
    'Valor mensal: R$ 2.300,00',
    'Vigencia: 01/04/2026 ate 31/12/2026',
]);

$imgLinesA = [
    'Nota Fiscal 009821',
    'Emitente: Mercado Central',
    'Chave: 3512 9988 7766 5544 3322 1100 9988 7766 5544 3322',
    'Valor final: R$ 147,90',
    'Data de emissao: 24/03/2026',
];

$imgLinesB = [
    'Comprovante de Pagamento',
    'Banco: Exemplo S.A.',
    'Agencia: 1234  Conta: 56789-0',
    'Valor pago: R$ 1.250,75',
    'Autenticacao: ZXCV-7788-QWER',
];

createImageWithText($baseDir.'/03-nota-fiscal.png', 'png', $imgLinesA);
createImageWithText($baseDir.'/04-nota-fiscal.jpg', 'jpg', $imgLinesA);
createImageWithText($baseDir.'/05-nota-fiscal.jpeg', 'jpeg', $imgLinesA);
createImageWithText($baseDir.'/06-comprovante.webp', 'webp', $imgLinesB);
createImageWithText($baseDir.'/07-rg-simulado.png', 'png', [
    'Registro Geral Simulado',
    'Nome: Carlos Eduardo Teste',
    'RG: 44.555.666-7',
    'Nascimento: 11/08/1991',
    'Orgao emissor: SSP',
]);
createImageWithText($baseDir.'/08-conta-luz.webp', 'webp', [
    'Conta de Energia - Referencia 03/2026',
    'Unidade consumidora: 00012345',
    'Consumo: 278 kWh',
    'Valor a pagar: R$ 214,33',
    'Vencimento: 10/04/2026',
]);

file_put_contents(
    $baseDir.'/README.txt',
    "Arquivos de teste para importacao no Laravel OCR System Simples.\n\n".
    "Tipos incluidos: PDF, PNG, JPG, JPEG e WEBP.\n".
    "Todos os arquivos foram gerados localmente em 24/03/2026.\n"
);

echo "Arquivos gerados em: {$baseDir}\n";
