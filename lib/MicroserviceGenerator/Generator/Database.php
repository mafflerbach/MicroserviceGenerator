<?php

namespace MicroserviceGenerator\Generator;

use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\Yaml\Yaml;
use MicroserviceGenerator\Generator\Sql\Oracle;
use MicroserviceGenerator\Generator\Sql\Sqlite;

class Database
{


    protected $production = null;
    protected $stage = null;
    protected $test = null;
    protected $development = null;
    protected $configuration = null;
    protected $types = array();
    
    private $file = null;

    public function __construct($file)
    {
        $this->file = $file;
    }


    public function generateSql()
    {
        $this->loadfromFile();
        for ($i=0; $i < count($this->types); $i++) {
            /**
         * @var MicroserviceGenerator\Generator\Sql $sqlGenerator
         */
            $sqlGenerator = null;
        
            switch (strtolower($this->types[$i])) {
                case 'oracle':
                //    $sqlGenerator = new Oracle();
                    break;
                    
                case 'sqlite3':
                    $sqlGenerator = new Sqlite();
                    break;
                
                default:
                    break;
            }

            if ($sqlGenerator !== null) {
                $sqlGenerator->loadfromFile($this->file);
                $sqlGenerator->generate();
            }
        }
    }


    /**
     * Load the structure segment from the yml file
     *
     * @param String $file
     * @return void
     */
    protected function loadfromFile()
    {
        if (file_exists($this->file)) {
            $configuration = Yaml::parseFile($this->file);
        
            foreach ($configuration as $key => $value) {
                if (property_exists($this, $key)) {
                    $this->$key = $value;
                    
                    if (!in_array($value['adapter'], $this->types)) {
                        $this->types[] = $value['adapter'];
                    }
                }
            }

            return;
        }

        throw new FileNotFoundException($this->file);
    }
}
