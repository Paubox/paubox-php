<?php

class CsvFileIterator implements Iterator
{
    protected $file;
    protected $key = 0;
    protected $current;
    
    public function __construct($file)
    {
        $handle = @fopen($file, 'r');
        if ($handle === false) {
            // Fail loudly. A false handle used to surface as a TypeError from
            // fclose() inside a data provider, which PHPUnit reports only as
            // "the data provider is invalid" with no mention of the file.
            throw new RuntimeException("Unable to open CSV fixture: {$file}");
        }
        $this->file = $handle;
    }

    public function __destruct()
    {
        if (is_resource($this->file)) {
            fclose($this->file);
        }
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