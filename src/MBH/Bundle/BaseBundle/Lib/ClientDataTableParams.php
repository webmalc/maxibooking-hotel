<?php


namespace MBH\Bundle\BaseBundle\Lib;

use Symfony\Component\HttpFoundation\Request;

/**
 * Class ClientDataTableParams
 * @package MBH\Bundle\BaseBundle\Lib
 * @author Aleksandr Arofikin <sasaharo@gmail.com>
 */
class ClientDataTableParams
{
    const DEFAULT_START = 0;
    const DEFAULT_LENGTH = 50;

    protected $columns = [];
    protected $order = [];
    /**
     * @var int
     */
    protected $start;
    /**
     * @var int
     */
    protected $length;
    /**
     * @var string
     */
    protected $search;

    /**
     * @var bool
     */
    protected $isSearchRegex;

    /**
     * array(
     *  columnName => columnName
     * ...
     * )
     * @var array
     */
    protected $sortColumnFields = [];


    /**
     * @param Request $request
     * @return ClientDataTableParams
     */
    public static function createFromRequest(Request $request)
    {
        $params = new self;
        $params->columns = $request->get('columns');
        $params->start = $request->get('start');
        if (!is_numeric($params->start)) {
            $params->start = self::DEFAULT_START;
        }
        $params->length = $request->get('length');
        if (!is_numeric($params->length)) {
            $params->length = self::DEFAULT_LENGTH;
        }
        $searchParams = $request->get('search');
        $params->search = $searchParams['value'] ? (string)$searchParams['value'] : null;
        $params->isSearchRegex = $searchParams['regex'];
        $params->order = (array)$request->get('order');

        $sortColumnFields = [];

        foreach($params->columns as $column) {
            $columnNumber = $column['data'];
            $columnName = $column['name'];
            if($column['orderable'] === 'true' && $columnName) {
                $sortColumnFields[$columnNumber] = $columnName;
            }
        }

        if($sortColumnFields) {
            $params->setSortColumnFields($sortColumnFields);
        }

        return $params;
    }

    /**
     * @param array $sortColumnFields
     */
    public function setSortColumnFields($sortColumnFields)
    {
        $this->sortColumnFields = $sortColumnFields;
    }

    /**
     * @return array|null
     */
    public function getFirstSort()
    {
        $sorts = $this->getSorts();
        return $sorts ? reset($sorts) : null;
    }

    /**
     * @return array[]
     */
    public function getSorts()
    {
        $sorts = [];
        foreach($this->order as $order){
            $columnNumber = $order['column'];
            $sortOrder = $order['dir'] == 'asc' ? 1 : -1; //value of 1 or -1 to specify an ascending or descending sort respectively
            if(array_key_exists($columnNumber, $this->sortColumnFields) && $fieldName = $this->sortColumnFields[$columnNumber]) {
                $sorts[] = [$fieldName, $sortOrder];
            }
        }

        return $sorts;
    }

    /**
     * @return string
     */
    public function getSearch()
    {
        return $this->search;
    }

    /**
     * @return int
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * @return int
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * @return boolean
     */
    public function isSearchRegex()
    {
        return $this->isSearchRegex;
    }
}