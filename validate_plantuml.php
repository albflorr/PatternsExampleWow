<?php
declare(strict_types=1);

/**
 * Validador de sintaxis PlantUML usando el servidor publico de PlantUML.
 * Codifica el diagrama con el algoritmo oficial: deflate + alfabeto URL-safe de 6 bits.
 *
 * Uso:  php validate_plantuml.php <fichero.md> [indice_bloque]
 */

const CHARS = [
    '0','1','2','3','4','5','6','7','8','9',
    'A','B','C','D','E','F','G','H','I','J','K','L','M','N','O',
    'P','Q','R','S','T','U','V','W','X','Y','Z','a','b','c','d',
    'e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t',
    'u','v','w','x','y','z','-','_',
];

/**
 * Codifica 3 bytes en 4 caracteres usando el alfabeto de 6 bits de PlantUML.
 * Algoritmo oficial (TranscoderUtil.appendEncoded3Bytes).
 */
function encode3(int $b1, int $b2, int $b3): string
{
    return CHARS[($b1 >> 2) & 0x3F]
         . CHARS[(($b1 << 4) & 0x30) | (($b2 >> 4) & 0x0F)]
         . CHARS[(($b2 << 2) & 0x3C) | (($b3 >> 6) & 0x03)]
         . CHARS[$b3 & 0x3F];
}

function plantumlEncode(string $text): string
{
    // Prefijo ~1 = DEFLATE con cabecera zlib (gzcompress, NO gzdeflate)
    $raw = gzcompress($text, 9);
    $out = '';
    $len = strlen($raw);
    for ($i = 0; $i < $len; $i += 3) {
        $out .= encode3(
            ord($raw[$i]),
            $i + 1 < $len ? ord($raw[$i + 1]) : 0,
            $i + 2 < $len ? ord($raw[$i + 2]) : 0
        );
    }
    return $out;
}

function fetchSvg(string $diagram): array
{
    $enc = plantumlEncode($diagram);
    $url = 'https://www.plantuml.com/plantuml/svg/~1' . $enc;
    $ctx = stream_context_create(['http' => ['timeout' => 90, 'header' => "User-Agent: Mozilla/5.0\r\n"]]);
    $body = @file_get_contents($url, false, $ctx);
    // file_get_contents devuelve false cuando el servidor responde 400
    return [$body === false ? null : $body, $url];
}

function isSyntaxError(?string $body): bool
{
    if ($body === null || !str_contains($body, '<svg')) {
        return true;
    }
    return stripos($body, 'Syntax Error') !== false
        || stripos($body, 'bad URL') !== false
        || stripos($body, 'HUFFMAN') !== false;
}

// --- Autotest: demuestra que el detector FUNCIONA de verdad ---
if (($argv[1] ?? '') === '--selftest') {
    [$okBody] = fetchSvg("@startuml\nclass Foo\nclass Bar\nFoo -- Bar\n@enduml");
    $ok = !isSyntaxError($okBody);

    [$badBody] = fetchSvg("@startuml\nclass Foo\n%%%\n@enduml");
    $bad = isSyntaxError($badBody);

    printf("Autotest del detector\n");
    printf("  diagrama valido aceptado ....... %s\n", $ok ? 'SI' : 'NO  <-- detector roto');
    printf("  diagrama roto rechazado ........ %s\n", $bad ? 'SI' : 'NO  <-- detector no ve errores');
    printf("  deteccion de fallos ............ %s\n", ($ok && $bad) ? 'FIABLE' : 'NO FIABLE');
    exit(($ok && $bad) ? 0 : 1);
}

$file = $argv[1] ?? 'Design.md';
$content = file_get_contents($file);

// Un .md contiene los diagramas entre vallas ```plantuml ... ```
// Un .puml / .plantuml / classDiagram es un unico diagrama completo
if (str_contains($content, '```plantuml')) {
    preg_match_all('/```plantuml(.*?)```/s', $content, $m);
    $diagrams = $m[1];
} else {
    $diagrams = [$content];
}

printf("Fichero: %s  (%d diagrama%s)\n\n", $file, count($diagrams), count($diagrams) === 1 ? '' : 's');

$errors = 0;
foreach ($diagrams as $i => $diagram) {
    $diagram = trim($diagram);
    [$body, $url] = fetchSvg($diagram);

    $title = preg_match('/title\s+(.*)/', $diagram, $t)
        ? trim($t[1])
        : ('Diagrama ' . ($i + 1));
    $title = preg_replace('/\s+/', ' ', (string) $title);

    if (!isSyntaxError($body)) {
        // Cuenta los nodos renderizados para confirmar el diagrama completo
        $nodes = preg_match_all('/class="[^"]*(?:node|class)/', (string) $body);
        printf("[%d] %-44s VALIDO  (%d elementos, URL %d chars)\n", $i + 1, mb_substr($title, 0, 44), $nodes, strlen($url));
        continue;
    }

    printf("[%d] %-44s ERROR DE SINTAXIS\n", $i + 1, mb_substr($title, 0, 44));
    if ($body !== null && preg_match('/Syntax Error[^<]{0,120}/', strip_tags($body), $e)) {
        printf("      -> %s\n", trim($e[0]));
    } else {
        printf("      -> el servidor no devolvio un SVG\n");
    }
    $errors++;
}

printf("\n%s\n", $errors === 0
    ? 'RESULTADO: los ' . count($diagrams) . ' diagramas son validos para PlantUML'
    : "RESULTADO: $errors de " . count($diagrams) . " diagramas con errores");
exit($errors === 0 ? 0 : 1);
