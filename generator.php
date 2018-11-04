<?php
use Symfony\Component\Yaml\Yaml;
use MicroserviceGenerator\Generator\Model;
use MicroserviceGenerator\Generator\Test;
use MicroserviceGenerator\Generator\Rest;
use MicroserviceGenerator\File\Metadata;
use MicroserviceGenerator\Generator\Database;

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
$databaseFile = $config['databaseFile'];

//generateServer($outputDir, $contractFile, $swaggerCodeGen);
generateModels($modelPath, $contractFile, $namespaceRoot);
//generateTests($modelTestPath, $contractFile, $namespaceRoot);
//generateRestClientFile($outputDir, $contractFile);
//startLocalserver($webroot);
// cleanup();
//generateDatabase($databaseFile);

function cleanup()
{
}

function startLocalserver($webroot)
{
    system("php -S localhost:8080 -t " . $webroot);
}


function composerInstall($serverPath)
{

    $command = "cd $serverPath; composer require phpunit/phpunit";
    system($command);
    $command = "cd $serverPath; composer require symfony/orm-pack";
    system($command);
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
    $prefix = $namespaceRoot . '\Model';
    generateSrc($prefix, $contractFile, $modelPath,'model');
    runFormater($modelPath);
}

function generateTests($modelPath, $contractFile, $namespaceRoot)
{
    $prefix = $namespaceRoot . '\ModelTest';
    generateSrc($prefix, $contractFile, $modelPath, 'test');
    runFormater($modelPath);
}

function generateSrc($prefix, $contractFile, $modelPath, $type)
{
    $contract = Yaml::parseFile($contractFile);

    foreach ($contract['paths'] as $endpoint => $value) {
        $metadata = new Metadata($prefix, $endpoint);
        $classname = $metadata->getClassname();
        $classnamespace = $metadata->getNamespace();
        switch ($type) {
            case 'test':
            $generator = new Test($classname, $classnamespace);
            break;
            
            default:
            $generator = new Model($classname, $classnamespace);
                break;
            }
            $generator->generate($contract, $modelPath, $value);
    }
}




function runFormater($path)
{
    system("phpcbf ". $path);
}

function generateDatabase($file)
{
    $db = new Database($file);
    $db->generateSql();
}


$array = array ('structure' =>
    array(
        'fixture' =>'filepath.csv',
        'fields'=> array (
            'User' => array(
                array (
                    'password' => array(
                        'size' => 255,
                        'type' => 'varchar',
                        'null' => false,
                        'primary' => true
                    )
                ),
                array (
                    'id' => array(
                        'size' => 9,
                        'type' => 'int',
                        'null' => false,
                        'primary' => true
                    )
                ),
                array( 'email' =>
                    array(
                        'size' => 255,
                        'type' => 'varchar',
                        'null' => true
                    )
                )
            )
        )
    )
);
