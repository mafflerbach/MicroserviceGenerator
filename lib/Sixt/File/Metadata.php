<?php 
namespace Sixt\File;

class Metadata 
{
    private $prefix;
    private $classname;
    private $namespace;
    private $modelpath;
    
    public function __construct($prefix, $modelpath)
    {
        $this->prefix = $prefix;    
        $this->modelpath = explode('/', $modelpath);    
        $this->generate();
    }
    
    public function getClassname()
    {
        return $this->classname;
    }

    public function getNamespace()
    {
        return $this->namespace;
    }

    private function generate() {
        $namespace = '';
        $modelNamePath = $this->modelpath;
        array_shift($modelNamePath);
        
        $namespace = array();
        for ($i=0; $i < count($modelNamePath); $i++) {
            if (strpos($modelNamePath[$i], '{') === false) {
                $namespace[] = ucfirst($modelNamePath[$i]);
            }
        }

        if (count($namespace) == 1) {
            $this->classname = $namespace[0];
            $this->namespace =  $this->prefix;
            
            return null;
        }
        $this->classname = $namespace[count($namespace[0]-1)];
        array_pop($namespace);
        $this->namespace = $this->prefix.'\\'.implode('\\', $namespace);
    
        return null;
    }
}
