<?php
use Symfony\Component\Yaml\Yaml;
use MicroserviceGenerator\Generator\Model;
use MicroserviceGenerator\Generator\Test;
use MicroserviceGenerator\Generator\Rest;
use MicroserviceGenerator\File\Metadata;

$loader = require __DIR__ . '/vendor/autoload.php';
$loader->addPsr4('MicroserviceGenerator\\', __DIR__ . "/lib/MicroserviceGenerator");

$config = parse_ini_file('config.ini');

$outputDir = $config['outputDir'];
$modelPath = $outputDir. '/SwaggerServer/src/';
$modelTestPath = $outputDir. '/SwaggerServer/tests/';
$webroot = $outputDir. '/SwaggerServer';
$namespaceRoot  = $config['projectName'];


$contractFile = $config['contractFile'];
$swaggerCodeGen = $config['swaggerCodeGen'];

generateServer($outputDir, $contractFile, $swaggerCodeGen);
generateModels($modelPath, $contractFile, $namespaceRoot);
generateTests($modelTestPath, $contractFile, $namespaceRoot);
generateRestClientFile($outputDir, $contractFile);
startLocalserver($webroot);
cleanup();

function cleanup(){

}

function startLocalserver($webroot) {
    system("php -S localhost:8080 -t " . $webroot);
}


function composerInstall($serverPath)
{

    $command = "cd $serverPath; composer require phpunit/phpunit";
    system($command);
    $composer = json_decode(file_get_contents($serverPath."/composer.json"), true);
    $composer['autoload']['classmap'] = array ('src/');
    file_put_contents($serverPath."/composer.json", json_encode($composer, JSON_PRETTY_PRINT));
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

function generateModels($modelPath, $contractFile, $namespaceRoot)
{

    $contract = Yaml::parseFile($contractFile);

    foreach ($contract['paths'] as $endpoint => $value) {
        $prefix = $namespaceRoot . '\Model';
        $metadata = new Metadata($prefix, $endpoint);
        $classname = $metadata->getClassname();
        $classnamespace = $metadata->getNamespace();
        $generator = new Model($classname, $classnamespace);
        $generator->generate($contract, $modelPath);
    }

    runFormater($modelPath);
}

function generateTests($modelPath, $contractFile, $namespaceRoot) {
    $contract = Yaml::parseFile($contractFile);

    foreach ($contract['paths'] as $endpoint => $value) {
        $prefix = $namespaceRoot . '\ModelTest';
        $metadata = new Metadata($prefix, $endpoint);
        $classname = $metadata->getClassname();
        $classnamespace = $metadata->getNamespace();
        $generator = new Test($classname, $classnamespace);
        $generator->generate($contract, $modelPath);
    }

    runFormater($modelPath);
}



function runFormater($path)
{
    system("phpcbf ". $path);
}

