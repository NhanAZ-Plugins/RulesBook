<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$source = $root . '/virions/libBook';
$target = $root . '/.devtools-virions/libBook';
$manifest = file_get_contents($source . '/virion.yml');
if ($manifest === false || substr_count($manifest, 'php: [ 8.0 ]') !== 1) {
	throw new RuntimeException('The pinned libBook manifest changed. Review its PHP requirement before building.');
}
$manifest = str_replace('php: [ 8.0 ]', 'php: ["8.0"]', $manifest);

$sourceFiles = [];
$sourceRoot = $source . '/src/';
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($sourceRoot, FilesystemIterator::SKIP_DOTS));
foreach ($iterator as $entry) {
	if (!$entry instanceof SplFileInfo || !$entry->isFile() || $entry->isLink()) {
		throw new RuntimeException('libBook source contains an unsupported entry.');
	}
	$relative = substr($entry->getPathname(), strlen($sourceRoot));
	$sourceFiles[$relative] = $entry->getPathname();
}
if ($sourceFiles === [] || !is_file($source . '/LICENSE')) {
	throw new RuntimeException('The pinned libBook source or MIT license is missing.');
}

foreach ($sourceFiles as $relative => $path) {
	$destination = $target . '/src/' . $relative;
	if (!is_dir(dirname($destination)) && !mkdir(dirname($destination), 0777, true) && !is_dir(dirname($destination))) {
		throw new RuntimeException('Could not prepare the libBook source directory.');
	}
	if (!copy($path, $destination)) {
		throw new RuntimeException("Could not copy libBook source {$relative}.");
	}
}
if (!copy($source . '/LICENSE', $target . '/LICENSE') || file_put_contents($target . '/virion.yml', $manifest) === false) {
	throw new RuntimeException('Could not prepare the libBook manifest or license.');
}

echo "Prepared pinned libBook source with a quoted PHP version in virion.yml.\n";
