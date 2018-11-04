<?php
namespace MicroserviceGenerator\Generator\Sql;

use MicroserviceGenerator\Generator\Sql;

class Sqlite extends Sql
{
    
    const DATABASE_TYPE='sqlite';

    public function generate()
    {
        $this->buildInitialisationStatements();
    }

    private function buildCreateStatement($tablename, $fields)
    {

        $sql = 'CREATE TABLE ';
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

    private function buildInitialisationStatements()
    {
        $createStatements = '';
        $insertStatements = '';
        foreach ($this->structure as $tablename => $fields) {
            $insertStatements .= $this->buildInsertStatements($tablename);
            $createStatements .= $this->buildCreateStatement($tablename, $fields);
        }
        var_dump($insertStatements);
        return array(
            'create' => $createStatements,
            'insert' => $insertStatements
        );
    }
 

    private function getColumnAttributes($attributes)
    {
        $sql = $this->map($attributes['type'], self::DATABASE_TYPE);
        if (isset($attributes['size'])) {
            $sql .= '('.$attributes['size'].')';
        }
        if (isset($attributes['primary']) && $attributes['primary'] == true) {
            $sql .= ' PRIMARY KEY NOT NULL ';
        }
        if (isset($attributes['null']) && $attributes['null'] == false && !isset($attributes['primary'])) {
            $sql .= ' NOT NULL ';
        }
        return $sql;
    }

    private function buildInsertStatements($tableName)
    {
        file_get_contents($this->fixturesDataPath.'/'.$tableName. '.csv'));
    }
}
