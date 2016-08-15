<?php
/**
 * Created by Zavalyuk Alexandr (Zalex).
 * email: zalex@zalex.com.ua
 * Date: 8/15/16
 * Time: 12:40 PM
 */

namespace MBH\Bundle\BaseBundle\Lib\RuTranslateConverter;
use Symfony\Component\Console\Helper\QuestionHelper;


/**
 * Interface RuTranslateInterface
 * @package MBH\Bundle\BaseBundle\Lib\RuTranslateConverter
 */
interface RuTranslateInterface
{
    /**
     * @return mixed
     */
    public function findEntry();

    /**
     * @return mixed
     */
    public function convert(QuestionHelper $helper);
}