<?php

namespace MicroserviceGenerator\Generator;

use MicroserviceGenerator\File\Blacklist;

class Test
{

    // FIXME: remove duplication with Model
    private $modelName;
    private $namespace;

    public function __construct($modelName, $namespace)
    {
        $this->modelName = $modelName;
        $this->namespace = $namespace;
    }

    public function generate($contract, $targetDir, $value)
    {
        $class = "<?php \n";
        $class .= "use PHPUnit\Framework\TestCase; \n";
        $class .= "use ".str_replace('Test', '', $this->namespace)."\\".$this->modelName."; \n";
        $class .= "class " . $this->modelName . "Test extends TestCase {";
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
        $file = $filePath.'/'.$this->modelName."Test.php";
        $relativeFilepath = str_replace('\\', '/', $this->namespace).'/'.$this->modelName."Test.php";

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
    
            $generatedMethod .= "/**\n";
            for ($i=0; $i < count($parameters); $i++) {
                $generatedMethod .= "* @param " . "$".$parameters[$i]['name'] . " ";
                if (isset($parameters[$i]['description'])) {
                    $generatedMethod .=  $parameters[$i]['description']. "\n";
                }
            }
            $generatedMethod .= "**/\n";
            $generatedMethod .= 'public function ';
            $generatedMethod .= "test".ucfirst($methodName);
            $generatedMethod .="() {";
            $generatedMethod .= $this->testBody($methodName, $parameters);
            $generatedMethod .="}\n\n";
        }
        
        return $generatedMethod;
    }


    private function testBody($methodName, $parameters)
    {
        $generatedMethod = '';
        for ($i=0; $i < count($parameters); $i++) {
            $generatedMethod .= "$".$parameters[$i]['name'] . "='".$parameters[$i]['name']."';\n";
        }
        $generatedMethod .= "\$instance = new ". $this->modelName."();\n";
        $generatedMethod .= "\$result = \$instance->".$methodName."(";
        $parameterList = array();
        for ($i=0; $i < count($parameters); $i++) {
            $parameterList[] = "$".$parameters[$i]['name'];
        }
        $generatedMethod .= implode(', ', $parameterList);
        $generatedMethod .= ");\n";
        $generatedMethod .= "\$expected = 'FIXME'; \n";
        $generatedMethod .= "\$this->assertEquals(\$expected, \$result);";
        
        return $generatedMethod;
    }
}
