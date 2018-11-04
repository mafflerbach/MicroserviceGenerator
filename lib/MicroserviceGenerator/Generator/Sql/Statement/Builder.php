<?php 
namespace MicroserviceGenerator\Generator\Sql\Statement;

use MicroserviceGenerator\Generator\Sql\Statement;




class Builder {
    private $createStatement = null;
    private $inserStatement = null;
    public function withCreateStatment($createStatement)
    {
        $this->createStatement = $createStatement;
    }
    
    public function withInsertStatement($insertStatement)
    {
        $this->inserStatement = $insertStatement;
    }

    public function build() {
        
        return new Statement($this->createStatement, $this->inserStatement);
        
    }

}