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
    $url = 'https://www.plantuml.com/plantuml/svg/~1' . $e;
    $ctx = stream_context_create(['http' => ['timeout' => 120, 'header' => "User-Agent: Mozilla/5.0\r\n"]]);
    $b = @file_get_contents($url, false, $ctx);
    $st = trim(explode(' ', $http_response_header[0] ?? '?')[1] ?? '?');
    return [$st, strlen($e), ($b !== false && str_contains($b, '<svg') ? 'VALIDO' : 'ERROR')];
}

$full = file_get_contents('D:\xampp\htdocs\ejemplo\classDiagram');

// 1) URL del diagrama completo
[$st, $len, $res] = ask($full);
printf("Diagrama completo: %d bytes fuente -> URL %d chars -> HTTP %s %s\n", strlen($full), $len, $st, $res);

// 2) Buscar el limite: bloques sinteticos de tamano creciente
echo "\nBuscando el limite de URL del servidor...\n";
$unit = "  class C" . str_pad('X', 3, '0', STR_PAD_LEFT) . " {\n    +campo : int\n  }\n";
foreach ([50, 100, 131, 160, 200, 240, 280] as $n) {
    $s = "@startuml\n";
    for ($i = 0; $i < $n; $i++) {
        $s .= "  class Cl$i {\n    +a$b : int\n    +b() : void\n    +c() : void\n  }\n";
    }
    $s .= "  Cl0 -- Cl1\n@enduml";
    [$st2, $len2, $res2] = ask($s);
    printf("  %3d clases -> fuente %5d, URL %5d -> HTTP %s %s\n", $n, strlen($s), $len2, $st2, $res2);
    if ($res2 === 'ERROR') { printf("  ^^^ primer fallo en %d clases (URL %d chars)\n", $n, $len2); break; }
}
