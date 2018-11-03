<?php
namespace MicroserviceGenerator\Generator;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;

abstract class Sql
{
    
    protected $configuration;
    protected $structure;


    /**
     * Load the structure segment from the yml file
     *
     * @param String $file
     * @return void
     */
    public function loadfromFile($file)
    {
        if (file_exists($file)) {
            $configuration = Yaml::parseFile($file);
            $this->structure = $configuration['structure'];
            return;
        }

        throw new FileNotFoundException($file);
    }

    public function map($type, $targetDb)
    {
        switch ($targetDb) {
            case 'sqlite':
                return $this->sqlite3Mapper($type);
                break;
            
            case 'oracle':
                return $this->oracleMapper($type);
                break;
            default:
                # code...
                break;
        }
    }


    private function oracleMapper()
    {
    }

    private function sqlite3Mapper($type)
    {

        
        

        $type = strtolower($type);

        switch ($type) {
            case 'int':
            case 'integer':
            case 'tinyint':
            case 'smallint':
            case 'mediumint':
            case 'bigint':
            case 'unsigned big int':
            case 'int2':
            case 'int8':
                return 'integer';
            break;

            case 'character':
            case 'varchar':
            case 'varying':
            case 'character':
            case 'nchar':
            case 'native':
            case 'character':
            case 'nvarchar':
            case 'text':
            case 'clob':
                return 'text';
            break;
            
            case 'real':
            case 'double':
            case 'double precision':
            case 'float':
                return 'float';
            break;
            

            case 'numeric':
            case 'decimal':
            case 'boolean':
            case 'date':
            case 'datetime':
                return 'numeric';
            break;

            
            default:
                # code...
                break;
        }
    }
}
