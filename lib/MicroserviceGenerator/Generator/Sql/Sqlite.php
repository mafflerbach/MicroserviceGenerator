<?php
namespace MicroserviceGenerator\Generator\Sql;

use MicroserviceGenerator\Generator\Sql;
use PhpParser\PrettyPrinter\Standard;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use MicroserviceGenerator\Generator\Sql\Statement\Builder;

class Sqlite extends Sql
{
    
    const DATABASE_TYPE='sqlite';

    /**
     * Undocumented function
     *
     * @return Statement
     */
    public function generate()
    {
        return $this->buildInitialisationStatements();
    }

    private function buildCreateStatement($tablename, $fields)
    {

        $sql = 'CREATE TABLE IF NOT EXISTS ';
        $sql .= $tablename . ' ('."\n";
        for ($i=0; $i < count($fields); $i++) {
            foreach ($fields[$i] as $columnName => $attributes) {
                $sql .= "\t". $columnName . ' ';
                $sql .= $this->getColumnAttributes($attributes);
            }
            if ($i < count($fields)-1) {
                $sql .= ','."\n";
            }
        }
        $sql .= "\n". ');'."\n";
        return $sql;
    }

    /**
     * Undocumented function
     *
     * @return Statement
     */
    private function buildInitialisationStatements()
    {
        $createStatements = array();
        $insertStatements = array();
        foreach ($this->structure as $tablename => $fields) {
            $insertStatements[]= $this->buildInsertStatements($tablename);
            $createStatements[]= $this->buildCreateStatement($tablename, $fields);
        }
        
        $statements = new Builder();
        $statements->withCreateStatment($createStatements);
        $statements->withInsertStatement($insertStatements);
     
        return $statements->build();
    }

    private function getColumnAttributes($attributes)
    {
        $sql = $this->map($attributes['type'], self::DATABASE_TYPE);
        if (isset($attributes['autoincrement']) && $attributes['autoincrement'] == true) {
            return ' INTEGER PRIMARY KEY ';
        }
        if (isset($attributes['size'])) {
            $sql .= '('.$attributes['size'].')';
        }
        if (isset($attributes['primary']) && $attributes['primary'] == true) {
            $sql .= ' PRIMARY KEY NOT NULL';
        }
        if (isset($attributes['null']) && $attributes['null'] == false && !isset($attributes['primary'])) {
            $sql .= ' NOT NULL ';
        }
        return $sql;
    }

    private function buildInsertStatements($tableName)
    {
        $file = $this->fixturesDataPath.'/'.$tableName. '.csv';

        if (($handle = fopen($file, "r")) !== false) {
            $i = 0;
            $sql = '';
            $header = 0;
            $sqlContainer = array();
            while (($data = fgetcsv($handle, null, ";")) !== false) {
                
                if ($i == 0) {
                    $header = $data;
                    $i++;
                    continue;
                }
                $sql .= "insert into " .$tableName ."\n";
                
                $content = array();
                for ($k = 0; $k < count($data); $k++) {
                    if (is_numeric($data[$k])) {
                        $content[] = $data[$k];
                    } else {
                        $content[] = "'".$data[$k]."'";
                    }
                }
            
                $sqlContainer[]= $sql .= "(".implode(',', $header).") values (".implode(',', $content)."); \n";
                $sql ='';
            }
            fclose($handle);
            return $sqlContainer;
        }
        
        throw new FileNotFoundException($file);
    }
}
