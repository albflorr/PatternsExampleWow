<?php

declare(strict_types=1);

/**
 * Genera classDiagram: un unico diagrama de clases con las 131 clases del proyecto.
 *
 * Verificacion: el servidor web de PlantUML no admite URLs mayores de ~7000 chars,
 * asi que el fichero completo se valida partido en 2 mitades brace-balanced.
 * Cada mitad se envia por separado; si ambas son validas, el conjunto lo es.
 */

const CHARS = [
    '0','1','2','3','4','5','6','7','8','9',
    'A','B','C','D','E','F','G','H','I','J','K','L','M','N','O',
    'P','Q','R','S','T','U','V','W','X','Y','Z','a','b','c','d',
    'e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t',
    'u','v','w','x','y','z','-','_',
];

function enc(string $t): string
{
    $raw = gzcompress($t, 9);
    $o = '';
    $n = strlen($raw);
    for ($i = 0; $i < $n; $i += 3) {
        $b1 = ord($raw[$i]);
        $b2 = $i + 1 < $n ? ord($raw[$i + 1]) : 0;
        $b3 = $i + 2 < $n ? ord($raw[$i + 2]) : 0;
        $o .= CHARS[($b1 >> 2) & 0x3F];
        $o .= CHARS[(($b1 << 4) & 0x30) | (($b2 >> 4) & 0x0F)];
        $o .= CHARS[(($b2 << 2) & 0x3C) | (($b3 >> 6) & 0x03)];
        $o .= CHARS[$b3 & 0x3F];
    }
    return $o;
}

function plantumlOk(string $src): array
{
    $e = enc($src);
    $ctx = stream_context_create(['http' => ['timeout' => 120, 'header' => "User-Agent: Mozilla/5.0\r\n"]]);
    $b = @file_get_contents('https://www.plantuml.com/plantuml/svg/~1' . $e, false, $ctx);
    return [
        'url'   => strlen($e),
        'status' => trim(explode(' ', $http_response_header[0] ?? '?')[1] ?? '?'),
        'ok'    => $b !== false && str_contains($b, '<svg'),
        'nodes' => $b === false ? 0 : preg_match_all('/<text[^>]*>/', $b),
    ];
}

$root = 'D:\xampp\htdocs\ejemplo';
$md = file_get_contents("$root\\Design.md");
preg_match_all('/```plantuml(.*?)```/s', $md, $m);
$all = $m[1];

// El bloque 0 es el resumen de arquitectura (no es un diagrama de clases) -> se excluye
$blocks = array_slice($all, 1);

$head = <<<'PUML'
@startuml

title Azeroth Engine - Diagrama de clases completo (PHP 8.2+)

skinparam classAttributeIconSize 0
skinparam classBackgroundColor #FEFEFE
skinparam classBorderColor #2E5C8A
skinparam classArrowColor #2E5C8A
skinparam shadowing false
skinparam linetype ortho
hide empty members

PUML;

$seen    = [];
$body    = '';
$packages = [];

foreach ($blocks as $blk) {
    $inNote = false;
    foreach (preg_split('/\R/', $blk) as $line) {
        $t = trim($line);
        if ($t === '' || str_starts_with($t, '@startuml') || str_starts_with($t, '@enduml')
            || str_starts_with($t, 'title ') || str_starts_with($t, 'skinparam') || $t === 'hide empty members') {
            continue;
        }
        // Los "note" de los bloques originales chocarian entre si al fusionar
        if (preg_match('/^note\b/', $t)) { $inNote = !str_contains($t, 'end note'); continue; }
        if ($inNote) { if (str_contains($t, 'end note')) { $inNote = false; } continue; }

        // Registrar paquetes declarados (para partir el fichero de forma segura)
        if (preg_match('/^\s*package\s+("?)(.+?)\1\s*(#\w+)?\s*\{\s*$/', $line)) {
            $packages[] = 'PAQUETE:' . trim($line);
        }

        if (preg_match('/^\s*(?:abstract\s+)?(?:class|interface|enum)\s+(\w+)/', $line, $cm)) {
            if (isset($seen[$cm[1]])) { continue; }
            $seen[$cm[1]] = true;
        }
        $body .= $line . "\n";
    }
}

$full = $head . $body . "\n@enduml\n";
file_put_contents("$root\\classDiagram", $full);

printf("classDiagram generado\n");
printf("  clases      : %d\n", count($seen));
printf("  bytes       : %d\n", strlen($full));
printf("  lineas      : %d\n", substr_count($full, "\n"));
printf("  llaves      : %d abiertas / %d cerradas\n", substr_count($full, '{'), substr_count($full, '}'));

if (substr_count($full, '{') !== substr_count($full, '}')) {
    echo "  DESBALANCEADO\n";
    exit(1);
}

// ---- Verificacion en 2 mitades por el limite de URL del servidor ----
// Se parte justo antes de un "package" de nivel superior para que
// ambas mitades sean diagramas completos y balanceados
$lines  = preg_split('/\R/', $full);
$cutIdx = null;
$depth  = 0;
foreach ($lines as $i => $l) {
    // La profundidad se comprueba ANTES de aplicar las llaves de esta linea:
    // una linea "package X {" abre un nivel, asi que aqui depth sigue siendo 0.
    if ($i > 10 && $depth === 0 && preg_match('/^\s*package\s/', $l)) {
        $cutIdx = $i;
        break;
    }
    $depth += substr_count($l, '{') - substr_count($l, '}');
}

if ($cutIdx === null) { echo "  no se pudo partir\n"; exit(1); }

printf("\n  corte en la linea %d: %s\n", $cutIdx, trim($lines[$cutIdx]));

$partA = implode("\n", array_slice($lines, 0, $cutIdx)) . "\n@enduml\n";
$rest  = array_slice($lines, $cutIdx, -1);
$partB = $head . implode("\n", $rest) . "\n@enduml\n";

printf("\nVerificacion (2 mitades, por el limite de ~7000 chars de URL):\n");
$allOk = true;
foreach (['A' => $partA, 'B' => $partB] as $k => $src) {
    $r = plantumlOk($src);
    printf("  mitad %s: %6d bytes  URL %5d  HTTP %s  %s  (%d textos)\n",
        $k, strlen($src), $r['url'], $r['status'], $r['ok'] ? 'OK' : 'FALLA', $r['nodes']);
    if (!$r['ok']) { $allOk = false; }
}
printf("\n%s\n", $allOk ? 'Ambas mitades validas -> el fichero completo es sintacticamente correcto'
                        : 'ERROR: alguna mitad no valida');
exit($allOk ? 0 : 1);
