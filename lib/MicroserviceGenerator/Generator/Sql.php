<?php
namespace MicroserviceGenerator\Generator;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;

abstract class Sql {
    
    protected $configuration;
    protected $structure;


    /**
     * Load the structure segment from the yml file
     *
     * @param String $file
     * @return void
     */
    public function loadfromFile($file) {
        if (file_exists($file)) {
            $configuration = Yaml::parseFile($file);
            $this->structure = $configuration['structure'];
            return;
        }

        throw new FileNotFoundException($file);
    }

    public function map(){

    }

} 