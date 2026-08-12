<?php

class CsvFileIterator implements Iterator
{
    protected $file;
    protected $key = 0;
    protected $current;
    
    public function __construct($file)
    {
        $this->file = fopen($file, 'r');
    }
    
    public function __destruct()
    {
        fclose($this->file);
    }
    
    #[\ReturnTypeWillChange]
    public function rewind()
    {
        rewind($this->file);
        $this->current = fgetcsv($this->file);
        $this->key = 0;
    }
    
    #[\ReturnTypeWillChange]
    public function valid()
    {
        return !feof($this->file);
    }
    
    #[\ReturnTypeWillChange]
    public function key()
    {
        return $this->key;
    }
    
    #[\ReturnTypeWillChange]
    public function current()
    {
        return $this->current;
    }
    
    #[\ReturnTypeWillChange]
    public function next()
    {
        $this->current = fgetcsv($this->file);
        $this->key++;
    }
}