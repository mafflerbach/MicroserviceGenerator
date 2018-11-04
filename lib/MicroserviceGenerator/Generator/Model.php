<?php

namespace MicroserviceGenerator\Generator;

use MicroserviceGenerator\File\Blacklist;

class Model 
{
    private $modelName;
    private $namespace;

    public function __construct($modelName, $namespace)
    {
        $this->modelName = $modelName;
        $this->namespace = $namespace;
    }

    public function generate($contract, $targetDir, $value)
    {       $class = "<?php \n";
            $class .= "namespace " . $this->namespace .";\n";
            $class .= "class " . $this->modelName . " {";
            $class .= $this->getMethodSkeleton($value);
            $class .= "}\n";
            $this->save($class, $targetDir);
    }

    private function save($content, $targetDir)
    {

        $filePath = $targetDir. str_replace('\\', '/', $this->namespace);
        
        if (!file_exists($filePath)) {
            mkdir($filePath, 0777, true);
        }
        $file = $filePath.'/'.$this->modelName.".php";
        $relativeFilepath = str_replace('\\', '/', $this->namespace).'/'.$this->modelName.".php";

            file_put_contents($file, $content);
        
    }

    private function getMethodSkeleton($value)
    {
        $generatedMethod = '';
        foreach ($value as $methodName => $method) {
            $parameters = $method['parameters'];
    
            $generatedMethod .= "/**\n";
            for ($i=0; $i < count($parameters); $i++) {
                $generatedMethod .= "* @param " . "$".$parameters[$i]['name'] . " ";
                if (isset($parameters[$i]['description'])) {
                    $generatedMethod .=  $parameters[$i]['description']. "\n";
                }
            }
            $generatedMethod .= "**/\n";
            $generatedMethod .= 'public function ';
            $generatedMethod .= $methodName;
            $generatedMethod .="(";
            $parameterList = array();
            for ($i=0; $i < count($parameters); $i++) {
                $parameterList[] = "$".$parameters[$i]['name'];
            }
            $generatedMethod .= implode(', ', $parameterList);
            $generatedMethod .=") {\n";
            $generatedMethod .="// TODO: Implementig \n";
            $generatedMethod .="}\n\n";
        }
        return $generatedMethod;
    }
}
