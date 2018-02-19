<?php

namespace MBH\Bundle\BaseBundle\Lib\Report;

class ReportCell
{
    private $rowSpan = 1;
    private $colSpan = 1;
    private $value;
    private $rowOption;
    private $columnOption;
    protected $isForMail = false;

    use HasClassesAndAttributesTrait;

    /**
     * @param $isForMail
     * @return ReportCell
     */
    public function setIsForMail($isForMail)
    {
        $this->isForMail = $isForMail;
        if ($isForMail) {
            $this->addStyle('border: 1px solid black');
            $this->addStyle('padding: 5px');
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function isForMail()
    {
        return $this->isForMail;
    }

    /**
     * @param $value
     * @param int $colSpan
     * @param int $rowSpan
     * @param array $classes
     * @param array $attributes
     * @return ReportCell
     */
    public function setInitData($value, $colSpan = 1, $rowSpan = 1, $classes = [], $attributes = [])
    {
        $this->value = $value;
        $this->rowSpan = $rowSpan;
        $this->colSpan = $colSpan;
        $this->classes = $classes;
        $this->attributes = $attributes;

        return $this;
    }

    /**
     * @param mixed $rowOption
     * @return ReportCell
     */
    public function setRowOption($rowOption)
    {
        $this->rowOption = $rowOption;

        return $this;
    }

    /**
     * @return string
     */
    public function getRowOption()
    {
        return $this->rowOption;
    }

    /**
     * @param mixed $columnOption
     * @return ReportCell
     */
    public function setColumnOption($columnOption)
    {
        $this->columnOption = $columnOption;

        return $this;
    }

    /**
     * @return string
     */
    public function getColumnOption()
    {
        return $this->columnOption;
    }

    /**
     * @return int
     */
    public function getRowSpan(): ?int
    {
        return $this->rowSpan;
    }

    /**
     * @return int
     */
    public function getColSpan(): ?int
    {
        return $this->colSpan;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return mixed
     */
    public function getDisplayedValue()
    {
        if (isset($this->callbacks['value'])) {
            return $this->callbacks['value']($this);
        }

        return $this->value;
    }

    /**
     * @param int $rowSpan
     * @return ReportCell
     */
    public function setRowSpan(int $rowSpan): ReportCell
    {
        $this->rowSpan = $rowSpan;

        return $this;
    }

    /**
     * @param int $colSpan
     * @return ReportCell
     */
    public function setColSpan(int $colSpan): ReportCell
    {
        $this->colSpan = $colSpan;

        return $this;
    }

    /**
     * @param mixed $value
     * @return ReportCell
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
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