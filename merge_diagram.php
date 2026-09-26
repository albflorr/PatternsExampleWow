<?php

declare(strict_types=1);

/**
 * Fusiona los N diagramas de un .md con vallas ```plantuml en un unico
 * fichero .puml, eliminando los envoltorios @startuml/@enduml/title de cada bloque.
 */

$mdPath = 'D:\xampp\htdocs\ejemplo\Design.md';
$outPath = 'D:\xampp\htdocs\ejemplo\classDiagram';

$content = file_get_contents($mdPath);
preg_match_all('/```plantuml(.*?)```/s', $content, $m);
$blocks = $m[1];

// Encabezado comun del diagrama unico
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

$seenClasses = [];
$seenPkgs    = [];
$body        = '';
$skipped     = [];

foreach ($blocks as $idx => $block) {
    $lines = preg_split('/\R/', $block);
    $inNote = false;

    foreach ($lines as $line) {
        $trim = trim($line);

        // Descartar envoltorios de cada bloque
        if ($trim === '' ) { continue; }
        if (str_starts_with($trim, '@startuml') || str_starts_with($trim, '@enduml')) { continue; }
        if (str_starts_with($trim, 'title ')) { continue; }
        if (str_starts_with($trim, 'skinparam')) { continue; }
        if ($trim === 'hide empty members') { continue; }

        // Los "note" del original chocan entre bloques: se omiten
        if (preg_match('/^note\s/', $trim)) { $inNote = !str_contains($trim, 'end note') ? true : false; $skipped[] = 'note'; continue; }
        if ($inNote) {
            if (str_contains($trim, 'end note')) { $inNote = false; }
            continue;
        }

        // Evitar declaraciones duplicadas de la misma clase
        if (preg_match('/^\s*(?:abstract\s+)?(?:class|interface|enum)\s+(\w+)/', $line, $cm)) {
            $name = $cm[1];
            if (isset($seenClasses[$name])) { $skipped[] = "clase duplicada: $name"; continue; }
            $seenClasses[$name] = true;
        }

        $body .= $line . "\n";
    }
}

$out = $head . $body . "\n@enduml\n";
file_put_contents($outPath, $out);

printf("Bloques fusionados : %d\n", count($blocks));
printf("Clases unicas      : %d\n", count($seenClasses));
printf("Omitidos           : %d (%s)\n", count($skipped), implode(', ', array_unique($skipped)) ?: 'ninguno');
printf("Tamano salida      : %d bytes\n", strlen($out));
printf("Lineas             : %d\n", substr_count($out, "\n"));
