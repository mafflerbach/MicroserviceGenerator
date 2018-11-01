<?php
namespace MicroserviceGenerator\Generator;

use Symfony\Component\Yaml\Yaml;
use MicroserviceGenerator\File\Blacklist;

class Rest
{
    private $contractFile;
    private $contract;

    public function __construct($contractFile)
    {
        $this->contractFile = $contractFile;
    }

    public function generate($targetDir, $contract)
    {
        
        $contract = $this->contract = Yaml::parseFile($contract);
        $class = '';
        $class .= "@endpoint = \n";
        $class .= $this->buildVariableList();

        foreach ($contract['paths'] as $key => $value) {
            $path = str_replace('{', '{{', $key);
            $path = str_replace('}', '}}', $path);
            $class .= $this->getMethodSkeleton($value, $path);
        }

        
        $this->save($class, $targetDir);
    }

    private function buildVariableList()
    {
        
        $fileContent = file_get_contents($this->contractFile);
        preg_match_all('/{.*}/', $fileContent, $matches);
        $matches = array_unique($matches[0]);
        $list = '';
        
        $matches = array_values($matches);
        
        for ($i = 0; $i < count($matches); $i++) {
            $item = str_replace('{', '@', $matches[$i]);
            $item =  str_replace('}', ' = ', $item);
            $list .= $item ."\n";
        }
        $list .= "\n";
        return $list;
    }

    private function save($content, $targetDir)
    {

        $filePath = $targetDir;
        
        if (!file_exists($filePath)) {
            mkdir($filePath, 0777, true);
        }
        $file = $filePath.'SwaggerServer/resource.http';

        $blacklist = new Blacklist($targetDir."ignoreFiles.json");
        // if (!$blacklist->exist($filePath)) {
             file_put_contents($file, $content);
        //     $blacklist->add($filePath);
        // }
    }

    private function getMethodSkeleton($value, $path)
    {
        $generatedMethod = '';
        foreach ($value as $methodName => $method) {
            $parameters = $method['parameters'];
            $generatedMethod .= "\n\n###\n".strtoupper($methodName) . " {{endpoint}}" . $path ."\n";
            
            if (isset($method['consumes'])) {
                $generatedMethod .= 'Content-Type: ' . $method['consumes'][0] . "\n\n";
            }
            if (!isset($method['consumes'])) {
                $generatedMethod .= 'Content-Type: application/json'."\n\n";
            }
            for ($i=0; $i < count($parameters); $i++) {
                switch ($parameters[$i]['in']) {
                    case 'formData':
                    case 'query':
                        if ($i > 1) {
                            $generatedMethod .= '&';
                        }
                        $generatedMethod .= $parameters[$i]['name'] . '=' . $parameters[$i]['type']. "\n";
                        break;
                    case 'body':
                        $generatedMethod .= json_encode($this->getSchema($parameters[$i]['schema']), JSON_PRETTY_PRINT);
                        break;
                    
                    default:
                        # code...
                        break;
                }
            }
        }

        return $generatedMethod;
    }

    private function getSchema($schemaRef)
    {
        
        if (isset($schemaRef['$ref'])) {
            $element = $this->getReference($schemaRef);
            return $this->foo($element['properties']);
        }
    }


    function getReference($schemaRef)
    {
        $ref = str_replace('#', '', $schemaRef['$ref']);
        $ref = explode('/', $ref);
        array_shift($ref);
        array_shift($ref);
        
        $arryPath = "['definitions']['".$ref['0']."']";
        $contract = $this->contract;

        $element = eval("return \$this->contract{$arryPath};");
        return $element;
    }

/*
array (
  'id' => 0,
  'category' =>
  array (
    'id' => 0,
    'name' => 'string',
  ),
  'name' => 'doggie',
  'photoUrls' =>
  array (
    0 => 'string',
  ),
  'tags' =>
  array (
    0 =>
    array (
      'id' => 0,
      'name' => 'string',
    ),
  ),
  'status' => 'available',
)

*/




    function foo($properties)
    {
        $return = array();
        foreach ($properties as $item => $value) {
            if (isset($value['type'])) {
                switch ($value['type']) {
                    case 'string':
                    case 'integer':
                    case 'boolean':
                        $return[$item] = $value['type'];        # code...
                        break;
                    case 'array':
                        if (isset($value['items']['$ref'])) {
                            $reference = $this->getReference($value['items']);
                            $return[$item] = $this->foo($reference);
                        } else {
                            $return[$item] = $this->foo($value);
                        }
                        break;
                    default:
                        break;
                }
            } elseif (isset($value['$ref'])) {
                $reference = $this->getReference($value);
                $return[$item] = $this->foo($reference);
            } else {
                if ($item == 'properties') {
                    $mee = array();
                    foreach ($value as $key1 => $value1) {
                        $return[$key1] = $value1['type'];
                    }
                }
            }
        }
        return $return;
    }
}
