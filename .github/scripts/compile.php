<?php

declare(strict_types=1);

define("PATH", dirname(__DIR__.DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR, 4).DIRECTORY_SEPARATOR);

if(!mkdir($concurrentDirectory = PATH . "artifact") && !is_dir($concurrentDirectory)){
    throw new RuntimeException("Failed to create directory: ".$concurrentDirectory);
}

$filename = null;
foreach(scandir(PATH."data/") as $file) {
    if(!str_starts_with($file, "matze_")) {
        continue;
    }
    $filename = strtoupper(str_replace("matze_", "", $file));
}
if($filename === null) {
    throw new RuntimeException("Could not find valid file name!");
}

$zip = new ZipArchive();
$zip->open(PATH."artifact".DIRECTORY_SEPARATOR.$filename.".zip", ZipArchive::CREATE | ZipArchive::OVERWRITE);

include_directory($zip, "data");
$zip->addFile(PATH."pack.mcmeta", "pack.mcmeta");
$zip->addFile(PATH."README.md", "README.md");
$zip->close();

function include_directory(ZipArchive $zip, string $directory, ?Closure $filter = null): void {
    /** @var SplFileInfo[] $files */
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(PATH.$directory),
        RecursiveIteratorIterator::LEAVES_ONLY
    );

    foreach ($files as $file){
        if (!$file->isDir()){
            $filePath = $file->getRealPath();
            if(($filter !== null) && !(($filter)($file->getBasename()))){
                continue;
            }
            $zip->addFile($filePath, str_replace(PATH, "", $filePath));
        }
    }
}