<?php

namespace Sixt\File;

class Blacklist
{
    private $blacklistFile;
    private $blacklistedFiles;
    public function __construct($blacklistFile)
    {
        $this->blacklistFile = $blacklistFile;
    }
    
    public function load()
    {
        if(file_exists($this->blacklistFile)) {
            $this->blacklistedFiles = json_decode(file_get_contents($this->blacklistFile), true);
            return $this->blacklistedFiles;
        }
        
        return '';
    }
    
    public function add($file)
    {
        $blacklistedFiles = $this->load();
        $blacklistedFiles[] = $file;
        $this->save($blacklistedFiles);
    }
    
    public function remove()
    {
        // FIXME: Not implemented yet
    }
    
    public function exist($file)
    {
        $blacklistedFiles = $this->load();
        if (is_array($blacklistedFiles)) {
            return in_array($file, $blacklistedFiles);
        }
        return false;
    }

    private function save($blacklistedFiles) {
        file_put_contents($this->blacklistFile, json_encode($blacklistedFiles));
    }
}
