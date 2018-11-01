<?php
use Symfony\Component\Yaml\Yaml;
use Sixt\Generator\Model;
use Sixt\Generator\Rest;
use Sixt\File\Metadata;

$loader = require __DIR__ . '/vendor/autoload.php';
$loader->addPsr4('Sixt\\', __DIR__ . "/lib/Sixt");


$outputDir = '/home/maren/development/swagger/sample/';
$modelPath = $outputDir.'SwaggerServer/src/';
$contractFile = '/home/maren/development/swagger/sample/swaggerResorces/contract.yml';

#generateServer($outputDir, $contractFile);
#generateModels($modelPath, $contractFile);
generateRestClientFile($outputDir, $contractFile);

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

function generateServer($outputDir, $contractFile)
{

    $command = "java -jar /home/maren/development/swagger/swagger-codegen-cli.jar \
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

