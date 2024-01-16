<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$action of method Doctrine\\\\DBAL\\\\Platforms\\\\AbstractPlatform\\:\\:getForeignKeyReferentialActionSQL\\(\\) expects string, mixed given\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/src/Platforms/CockroachPlatform.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
