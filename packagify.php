<?php

$pluginDir = realpath("src/");
$distDir = realpath("dist/");


$pluginsToComposerify = findPrivatePlugins($pluginDir);
$composerifiedPlugins = [];
foreach ($pluginsToComposerify as $pluginName => $pluginPath) {
    $composerifiedPlugins[] = "{$pluginName}/{$pluginName}";
    composerifyPlugin($distDir, $pluginName, $pluginPath);
}

print "\n\nComposer Configuration:\n";
print "composer config repositories.private-plugins artifact {$distDir}\n";
$requires = [];
foreach ($composerifiedPlugins as $p) {
	$requires[] = "\"{$p}:1.0.0\"";
}
print "composer require " . join(" ", $requires) . "\n";
exit(0);

function findPrivatePlugins($pluginSrcDir) {
    $privatePlugins = [];

    chdir($pluginSrcDir);
    foreach (new DirectoryIterator('.') as $plugin) {
        if ($plugin->isDot()) continue;
        if ($plugin->isFile()) continue;
        //  TODO: any more appropriate way to detect real plugins?

        $pluginName = $plugin->getFilename();
        $pluginPath = $plugin->getRealPath();
        print "Found wordpress plugin: {$pluginName}\n";
        $privatePlugins[$pluginName] = $pluginPath;
    }
    return $privatePlugins;
}

function composerifyPlugin($distDir, $pluginName, $pluginPath) {
    $composerJSON = <<<JSON
{
"name": "{$pluginName}/{$pluginName}",
"type": "wordpress-plugin",
"version": "1.0.0"
}
JSON;

    # Create the composer json
    $pkgComposerJsonFile = "{$pluginPath}/composer.json";
    print "Creating {$pkgComposerJsonFile}\n";

    # Create the dist/zip
    $pkgFileFile = "{$distDir}/{$pluginName}-1.0.0.zip";
    $zip = new ZipArchive();
    $ret = $zip->open($pkgFileFile, ZipArchive::CREATE | ZipArchive::OVERWRITE);
    if ($ret !== TRUE) {
        printf('zip::open() Failed with code %d', $ret);
        exit(1);
    }

    // add our composer.json
    $zip->addFromString("composer.json", $composerJSON);

    // create recursive directory iterator
    $pluginDirCWD = "{$pluginDir}/{$pluginPath}";
    chdir($pluginDirCWD);
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('.'), RecursiveIteratorIterator::LEAVES_ONLY);
    foreach ($files as $name => $file) {
        if (!$file->isFile()) continue;

        $localPath = $file->getPathName();
        $fullPath = $file->getRealPath();
        $ret = $zip->addFile($fullPath, $localPath);
        if ($ret !== TRUE) {
            printf('zip::addFile() Failed with code %d', $ret);
            exit(1);
        }
    }

    $ret = $zip->close();
    if ($ret !== TRUE) {
        printf('zip::close() Failed with code %d', $ret);
        exit(1);
    }
}

