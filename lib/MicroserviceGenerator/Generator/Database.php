<?php

namespace MicroserviceGenerator\Generator;

use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\Yaml\Yaml;
use MicroserviceGenerator\Generator\Sql\Oracle;
use MicroserviceGenerator\Generator\Sql\Sqlite;
use MicroserviceGenerator\Generator\Sql\Statement;

class Database
{


    protected $production = null;
    protected $stage = null;
    protected $test = null;
    protected $development = null;
    protected $configuration = null;
    protected $types = array();
    private $basepath = '';

    private $file = null;

    public function __construct($file)
    {
        $this->file = $file;
        $this->basepath = dirname($this->file);
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

                $statements = $sqlGenerator->generate();

                $this->executeSql($statements);
            }
        }
    }

    private function executeSql(Statement $statements)
    {
        
        $create = $statements->getCreateStatement();
        $insert = $statements->getInsertStatement();
        
        $connection = $this->getDatabaseConnection($this->development['adapter']);
        
        for ($i=0; $i < count($create); $i++) {
            $statement = $connection->prepare($create[$i]);
            $statement->execute();
        }
        
        for ($i=0; $i < count($insert); $i++) {
            for ($k=0; $k < count($insert[$i]); $k++) {
                $statement = $connection->prepare($insert[$i][$k]);
                $statement->execute();
            }
        }
    }

    /**
    * Undocumented function
    *
    * @param String $adapter
    * @return \PDO
    **/
    private function getDatabaseConnection($adapter)
    {
        $path = $this->basepath."/database";
        
        switch ($adapter) {
            case 'sqlite3':
                if (!file_exists($path)) {
                    mkdir($path, 0777);
                }

                return new \PDO('sqlite:'.$path.'/'. $this->development['database']);
                break;
            default:
                throw new \Exception("Adapter nit found:" . $adapter);
                break;
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
