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
    return [$st, strlen($e), ($b !== false && str_contains($b, '<svg') ? 'VALIDO' : 'ERROR')];
}

echo "1) Los diagramas ya validados de Design.md (URLs cortas):\n";
$md = file_get_contents('D:\xampp\htdocs\ejemplo\Design.md');
preg_match_all('/```plantuml(.*?)```/s', $md, $m);
foreach ($m[1] as $i => $d) {
    [$st, $len, $res] = ask(trim($d));
    printf("   diag %d: fuente %5d  URL %5d  HTTP %s  %s\n", $i + 1, strlen(trim($d)), $len, $st, $res);
}

echo "\n2) Fichero classDiagram fusionado completo:\n";
$full = file_get_contents('D:\xampp\htdocs\ejemplo\classDiagram');
[$st, $len, $res] = ask($full);
printf("   fuente %d  URL %d  HTTP %s  %s\n", strlen($full), $len, $st, $res);

echo "\n3) Corte de longitud de URL (diagramas reales, recortados por clases):\n";
$lines = preg_split('/\R/', $full);
$head = array_slice($lines, 0, 12);
$body = array_slice($lines, 12, -1);
foreach ([100, 200, 300, 400, 500, 600, 700] as $n) {
    if ($n > count($body)) { break; }
    $src = implode("\n", $head) . "\n" . implode("\n", array_slice($body, 0, $n)) . "\n@enduml\n";
    [$st2, $len2, $res2] = ask($src);
    printf("   %4d lineas  URL %5d  HTTP %s  %s\n", $n, $len2, $st2, $res2);
    if ($res2 === 'ERROR') { printf("   >>> CORTE: falla en %d lineas (URL %d chars)\n", $n, $len2); break; }
}
