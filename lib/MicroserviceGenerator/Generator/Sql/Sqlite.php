<?php
namespace MicroserviceGenerator\Generator\Sql;

use MicroserviceGenerator\Generator\Sql;

class Sqlite extends Sql
{
    
    const DATABASE_TYPE='sqlite';

    public function generate()
    {
        $this->buildCreateStatement();
    }


    private function buildCreateStatement()
    {
        $sql = 'CREATE TABLE ';

        foreach ($this->structure as $tablename => $fields) {
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
            $sql .= $tablename ."\n". ')'."\n";
        }
        return $sql;
    }

    private function getColumnAttributes($attributes) {
        $sql = $this->map($attributes['type'], self::DATABASE_TYPE);
        if (isset($attributes['size'])) {
            $sql .= '('.$attributes['size'].')';
        }
        if (isset($attributes['primary'])) {
            $sql .= ' PRIMARY KEY NOT NULL ';
        }
        if (isset($attributes['null'])) {
            $sql .= ' NOT NULL ';
        }
        return $sql;
    }

    private function buildInsertStatement()
    {
    }
}
