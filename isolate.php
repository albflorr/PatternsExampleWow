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

function ask(string $src): string
{
    $ctx = stream_context_create(['http' => ['timeout' => 120, 'header' => "User-Agent: Mozilla/5.0\r\n"]]);
    $b = @file_get_contents('https://www.plantuml.com/plantuml/svg/~1' . enc($src), false, $ctx);
    return ($b !== false && str_contains($b, '<svg')) ? 'OK' : 'FALLA';
}

$md = file_get_contents('D:\xampp\htdocs\ejemplo\Design.md');
preg_match_all('/```plantuml(.*?)```/s', $md, $m);
$blocks = $m[1];

$head = "@startuml\nskinparam shadowing false\nhide empty members\n";

function bodyOf(string $block, array &$seen): string
{
    $inNote = false;
    $out = '';
    foreach (preg_split('/\R/', $block) as $line) {
        $t = trim($line);
        if ($t === '' || str_starts_with($t, '@startuml') || str_starts_with($t, '@enduml')
            || str_starts_with($t, 'title ') || str_starts_with($t, 'skinparam') || $t === 'hide empty members') { continue; }
        if (preg_match('/^note\s/', $t)) { $inNote = !str_contains($t, 'end note'); continue; }
        if ($inNote) { if (str_contains($t, 'end note')) { $inNote = false; } continue; }
        if (preg_match('/^\s*(?:abstract\s+)?(?:class|interface|enum)\s+(\w+)/', $line, $cm)) {
            if (isset($seen[$cm[1]])) { continue; }
            $seen[$cm[1]] = true;
        }
        $out .= $line . "\n";
    }
    return $out;
}

$build = function (array $blocks) use ($head, $bodyOf): string {
    $seen = [];
    $b = '';
    foreach ($blocks as $blk) { $b .= bodyOf($blk, $seen); }
    return $head . $b . "\n@enduml\n";
};

echo "A) Bloque 2 solo (Domain)\n";
printf("   %s\n", ask($build([$blocks[1]])));

echo "B) Bloque 1 (resumen) solo\n";
printf("   %s\n", ask($build([$blocks[0]])));

echo "C) Bloques 2..8 (sin el resumen)\n";
$s = $build(array_slice($blocks, 1));
printf("   %s  (%d bytes)\n", ask($s), strlen($s));
file_put_contents('D:\xampp\htdocs\ejemplo\_norResumen.puml', $s);

echo "D) Bloques 1..8 (todo)\n";
printf("   %s\n", ask($build($blocks)));

echo "\nE) Anade el resumen al FINAL en vez de al principio\n";
$s2 = $head . bodyOf($blocks[1], $GLOBALS['sv'] ?? $sv) . "\n@enduml\n";
// caso real: resumen despues
$seen2 = [];
$b2 = bodyOf($blocks[1], $seen2) . "\n" . bodyOf($blocks[0], $seen2);
$s3 = $head . $b2 . "\n@enduml\n";
printf("   %s  (%d bytes)\n", ask($s3), strlen($s3));
file_put_contents('D:\xampp\htdocs\ejemplo\_resumenFinal.puml', $s3);
