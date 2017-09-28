<?php

namespace MBH\Bundle\BaseBundle\Lib\Report;

class ReportRow
{
    private $cells = [];
    private $rowOption;

    use HasClassesAndAttributesTrait;

    /**
     * @return string
     */
    public function getRowOption()
    {
        return $this->rowOption;
    }

    /**
     * @param string $rowOption
     * @return ReportRow
     */
    public function setRowOption($rowOption)
    {
        $this->rowOption = $rowOption;

        return $this;
    }

    /**
     * @param $value
     * @param int $colSpan
     * @param int $rowSpan
     * @param array $classes
     * @param array $attributes
     * @return ReportCell
     */
    public function createAndAddCell($value, $colSpan = 1, $rowSpan = 1, $classes = [], $attributes = [])
    {
        $cell = (new ReportCell())->setInitData($value, $colSpan, $rowSpan, $classes, $attributes);
        $this->cells[] = $cell;

        return $cell;
    }

    /**
     * @return ReportCell[]
     */
    public function getCells()
    {
        return $this->cells;
    }

    /**
     * @return array
     */
    public function getClasses()
    {
        if (isset($this->callbacks['classes'])) {
            $this->classes = array_merge($this->callbacks['classes']($this), $this->classes);
        }
        return $this->classes;
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        if (isset($this->callbacks['attributes'])) {
            $this->attributes = array_merge($this->callbacks['attributes']($this), $this->attributes);
        }

        return $this->attributes;
    }
}