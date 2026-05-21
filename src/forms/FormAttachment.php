<?php
namespace Paubox\Forms;

class FormAttachment
{
    private $name;
    private $content;

    public function getName() { return $this->name; }
    public function setName($name) { $this->name = $name; }

    public function getContent() { return $this->content; }
    public function setContent($content) { $this->content = $content; }
}
?>
