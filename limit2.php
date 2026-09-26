<?php

declare(strict_types=1);

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

function ask(string $src): array
{
    $e = enc($src);
    $ctx = stream_context_create(['http' => ['timeout' => 120, 'header' => "User-Agent: Mozilla/5.0\r\n"]]);
    $b = @file_get_contents('https://www.plantuml.com/plantuml/svg/~1' . $e, false, $ctx);
    $st = trim(explode(' ', $http_response_header[0] ?? '?')[1] ?? '?');
    return [$st, strlen($e), ($b !== false && str_contains($b, '<svg'))];
}

$md = file_get_contents('D:\xampp\htdocs\ejemplo\Design.md');
preg_match_all('/```plantuml(.*?)```/s', $md, $m);
$blocks = $m[1];

// Solo los 7 diagramas de clases (el bloque 0 es el resumen de arquitectura)
$classBlocks = array_slice($blocks, 1);

$names = ['Domain', 'Repository', 'Creational', 'Structural', 'Strategy', 'Observer+Command+TM+State', 'Contexts'];

$seen = [];
$acc = '';
echo "Acumulando diagramas de clases completos ( siempre balanceados ):\n";
foreach ($classBlocks as $i => $blk) {
    // limpiar envoltorios y deduplicar
    $inNote = false;
    foreach (preg_split('/\R/', $blk) as $line) {
        $t = trim($line);
        if ($t === '' || str_starts_with($t, '@startuml') || str_starts_with($t, '@enduml')
            || str_starts_with($t, 'title ') || str_starts_with($t, 'skinparam') || $t === 'hide empty members') { continue; }
        if (preg_match('/^note\s/', $t)) { $inNote = !str_contains($t, 'end note'); continue; }
        if ($inNote) { if (str_contains($t, 'end note')) { $inNote = false; } continue; }
        if (preg_match('/^\s*(?:abstract\s+)?(?:class|interface|enum)\s+(\w+)/', $line, $cm)) {
            if (isset($seen[$cm[1]])) { continue; }
            $seen[$cm[1]] = true;
        }
        $acc .= $line . "\n";
    }
    $src = "@startuml\nskinparam shadowing false\nhide empty members\n" . $acc . "\n@enduml\n";
    [$st, $len, $ok] = ask($src);
    printf("  + %-32s fuente %6d  URL %5d  HTTP %s  %s\n", $names[$i], strlen($src), $len, $st, $ok ? 'OK' : 'FALLA');
    if (!$ok) { break; }
}
printf("\n  Total de clases acumuladas: %d\n", count($seen));
