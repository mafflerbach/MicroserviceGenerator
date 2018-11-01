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

    public function generate($contract, $targetDir)
    {
        foreach ($contract['paths'] as $key => $value) {
            $class = "<?php \n";
            $class .= "namespace " . $this->namespace .";\n";
            $class .= "class " . $this->modelName . " {";
            $class .= $this->getMethodSkeleton($value);
            $class .= "}\n";
        }

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

        $blacklist = new Blacklist($targetDir."ignoreFiles.json");
        if (!$blacklist->exist($relativeFilepath)) {
            file_put_contents($file, $content);
            $blacklist->add($relativeFilepath);
        }
    }

    private function getMethodSkeleton($value)
    {
        $generatedMethod = '';
        foreach ($value as $methodName => $method) {
            $parameters = $method['parameters'];
            $parameters = $method['parameters'];
    
            $generatedMethod .= "/**\n";
            for ($i=0; $i < count($parameters); $i++) {
                $generatedMethod .= "* " . "$".$parameters[$i]['name'] . " ";
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
