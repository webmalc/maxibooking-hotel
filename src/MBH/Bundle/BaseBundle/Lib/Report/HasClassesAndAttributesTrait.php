<?php

namespace MBH\Bundle\BaseBundle\Lib\Report;


trait HasClassesAndAttributesTrait
{
    private $classes = [];
    private $attributes = [];
    private $callbacks = [];
    
    /**
     * @param string $class
     * @return static
     */
    public function addClass(string $class)
    {
        if(($key = array_search($class, $this->classes)) === false) {
            $this->classes[] = $class;
        }

        return $this;
    }

    /**
     * @param string $class
     * @return static
     */
    public function removeClass(string $class)
    {
        if(($key = array_search($class, $this->classes)) !== false) {
            unset($this->classes[$class]);
        }

        return $this;
    }

    /**
     * @param $callbacks
     * @return static
     */
    public function setCallbacks($callbacks)
    {
        $this->callbacks = $callbacks;

        return $this;
    }

    /**
     * @param $name
     * @param $value
     * @return static
     * @throws \Exception
     */
    public function addAttribute($name, $value)
    {
        if ($name === 'class') {
            throw new \Exception('To add the class, use method "addClass"');
        }
        $this->attributes[$name] = $value;

        return $this;
    }

    /**
     * @return string
     */
    public function getAttributesAsString()
    {
        $attributesAsString = '';
        foreach ($this->getAttributes() as $name => $value) {
            $attributesAsString .= $name . '="' . $value . '" ';
        }

        return $attributesAsString;
    }
}