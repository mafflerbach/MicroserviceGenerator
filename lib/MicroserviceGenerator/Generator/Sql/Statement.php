<?php 

namespace MicroserviceGenerator\Generator\Sql;

class Statement {
    private $insertStatement; 
    private $createStatement; 
    
    public function __construct(array $createStatement, array $insertStatement)
    {
        $this->createStatement = $createStatement;
        $this->insertStatement = $insertStatement;
    }

    public function getCreateStatement(){
        return $this->createStatement;
    }
    public function getInsertStatement(){
        return $this->insertStatement;
    }

}