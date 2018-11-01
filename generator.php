<?php
use Symfony\Component\Yaml\Yaml;
use MicroserviceGenerator\Generator\Model;
use MicroserviceGenerator\Generator\Rest;
use MicroserviceGenerator\File\Metadata;

$loader = require __DIR__ . '/vendor/autoload.php';
$loader->addPsr4('MicroserviceGenerator\\', __DIR__ . "/lib/MicroserviceGenerator");

$config = parse_ini_file('config.ini');

$outputDir = $config['outputDir'];
$modelPath = $outputDir. '/SwaggerServer/src/';
$webroot = $outputDir. '/SwaggerServer';

$contractFile = $config['contractFile'];
$swaggerCodeGen = $config['swaggerCodeGen'];

generateServer($outputDir, $contractFile, $swaggerCodeGen);
generateModels($modelPath, $contractFile);
generateRestClientFile($outputDir, $contractFile);
startLocalserver($webroot);

function startLocalserver($webroot) {
    system("php -S localhost:8080 -t " . $webroot);
}


function composerInstall($serverPath)
{
    $command = "cd $serverPath; composer install";
    system($command);
}

function generateRestClientFile($outputDir, $contractFile)
{
    $rest = new Rest($contractFile);
    $rest->generate($outputDir, $contractFile);
}

function generateServer($outputDir, $contractFile, $swaggerCodeGen)
{

    $command = "java -jar ".$swaggerCodeGen." \
    generate -i ".$contractFile." \
    -l php-silex \
    -o " . $outputDir;
    system($command);

    composerInstall($outputDir.'/SwaggerServer');

}

function generateModels($modelPath, $contractFile)
{

    $contract = Yaml::parseFile($contractFile);

    foreach ($contract['paths'] as $endpoint => $value) {
        $prefix = 'Example\Model';
        $metadata = new Metadata($prefix, $endpoint);
        $classname = $metadata->getClassname();
        $classnamespace = $metadata->getNamespace();
        $generator = new Model($classname, $classnamespace);
        $generator->generate($contract, $modelPath);
    }

    runFormater($modelPath);
}



function runFormater($path)
{
    system("phpcbf ". $path);
}

