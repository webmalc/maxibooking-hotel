<?php

namespace MBH\Bundle\VegaBundle\Service;

use Symfony\Component\DependencyInjection\Container;
/**
 * Class FriendlyFormatter
 *

 */
class FriendlyFormatter
{
    const CHARSET = "UTF-8";

    private static $container;

    private static $translator;

    public function __construct(Container $container)
    {
        self::$container = $container;
        self::$translator = self::$container->get('translator');
    }

    private static function specialCountryTitles()
    {
        return [
            self::$container->get('translator')->trans('vegabundle.service.sha'),
            self::$container->get('translator')->trans('vegabundle.service.sssr'),
            self::$container->get('translator')->trans('vegabundle.service.kndr'),
            self::$container->get('translator')->trans('vegabundle.service.uar'),
            self::$container->get('translator')->trans('vegabundle.service.gdr'),
            self::$container->get('translator')->trans('vegabundle.service.chessr')
        ];
    }

    public static function convertCountry($country)
    {
        if (in_array($country, self::specialCountryTitles())) {
            return $country;
        }

        $result = mb_convert_case($country, MB_CASE_TITLE, self::CHARSET);
        $wrongCountryTitles = [];
        foreach(self::specialCountryTitles() as $title) {
            $wrongCountryTitles[] = mb_convert_case($title, MB_CASE_TITLE, self::CHARSET);
        }

        $search = array_merge([
            ' '.self::$translator->trans('vegabundle.service.BBez').' ',
            ' '.self::$translator->trans('vegabundle.service.II').' ',
            ' '.self::$translator->trans('vegabundle.service.VV').' '
        ], $wrongCountryTitles);
        $replace = array_merge([
            ' '.self::$translator->trans('vegabundle.service.bez').' ',
            ' '.self::$translator->trans('vegabundle.service.i').' ',
            ' '.self::$translator->trans('vegabundle.service.v').' '
        ], self::specialCountryTitles());
        $result = str_replace($search, $replace, $result);

        return $result;
    }

    /**
     * @return array
     */
    public static function specialNames()
    {
        return [
            self::$translator->trans('vegabundle.service.krasnodarsky') => self::$translator->trans('vegabundle.service.kry'),
            self::$translator->trans('vegabundle.service.hanty-manciysky_avtonomny_okrug'),
            self::$translator->trans('vegabundle.service.karelia') => self::$translator->trans('vegabundle.service.resp'),
            self::$translator->trans('vegabundle.service.primorsky') => self::$translator->trans('vegabundle.service.kry'),
            self::$translator->trans('vegabundle.service.severnaya-osetia_alania'),
            self::$translator->trans('vegabundle.service.bashkorkostan') => self::$translator->trans('vegabundle.service.resp'),
            self::$translator->trans('vegabundle.service.komy') => self::$translator->trans('vegabundle.service.resp'),
            self::$translator->trans('vegabundle.service.kalmikia') => self::$translator->trans('vegabundle.service.resp'),
            self::$translator->trans('vegabundle.service.tatarstan') => self::$translator->trans('vegabundle.service.resp'),
            self::$translator->trans('vegabundle.service.yamalo-neneckiy') => self::$translator->trans('vegabundle.service.avt-okrug'),
            self::$translator->trans('vegabundle.service.moskwa'),
            self::$translator->trans('vegabundle.service.permsky') => self::$translator->trans('vegabundle.service.kry'),
            self::$translator->trans('vegabundle.service.dagestan') => self::$translator->trans('vegabundle.service.resp'),
            self::$translator->trans('vegabundle.service.baykonur') => '',
            self::$translator->trans('vegabundle.service.saha-yakutia') => self::$translator->trans('vegabundle.service.resp'),
            self::$translator->trans('vegabundle.service.buryatia') => self::$translator->trans('vegabundle.service.resp'),
            self::$translator->trans('vegabundle.service.ingushetia') => self::$translator->trans('vegabundle.service.resp'),
            self::$translator->trans('vegabundle.service.adygeya') => self::$translator->trans('vegabundle.service.resp'),
            self::$translator->trans('vegabundle.service.hakasia') => self::$translator->trans('vegabundle.service.resp'),
            self::$translator->trans('vegabundle.service.habarovsky') => self::$translator->trans('vegabundle.service.kry'),
            self::$translator->trans('vegabundle.service.zabaykalsky') => self::$translator->trans('vegabundle.service.kry'),
            self::$translator->trans('vegabundle.service.stavropolsky') => self::$translator->trans('vegabundle.service.kry'),
            self::$translator->trans('vegabundle.service.mariy-el') => self::$translator->trans('vegabundle.service.resp'),
            self::$translator->trans('vegabundle.service.nenecky') => self::$translator->trans('vegabundle.service.avt-okrug'),
            self::$translator->trans('vegabundle.service.tiva') => self::$translator->trans('vegabundle.service.resp'),
            self::$translator->trans('vegabundle.service.mordovia') => self::$translator->trans('vegabundle.service.resp'),
            self::$translator->trans('vegabundle.service.altaisky') => self::$translator->trans('vegabundle.service.kry'),
            self::$translator->trans('vegabundle.service.chuvashskaya-respublika'),
            self::$translator->trans('vegabundle.service.chukotsky') => self::$translator->trans('vegabundle.service.avt-okrug'),
            self::$translator->trans('vegabundle.service.krasnodarsky') => self::$translator->trans('vegabundle.service.kry'),
            self::$translator->trans('vegabundle.service.sankt-peterburg'),
            self::$translator->trans('vegabundle.service.sevastopol'),
            self::$translator->trans('vegabundle.service.altay') => self::$translator->trans('vegabundle.service.resp'),
            self::$translator->trans('vegabundle.service.krym') => self::$translator->trans('vegabundle.service.resp'),
        ];
    }

    public static function convertRegion($region)
    {
        $result = mb_strtolower($region);
        $result = mb_convert_case($result, MB_CASE_TITLE, self::CHARSET);
        $ends = mb_substr($result, -2);

        if ($ends == self::$container->get('translator')->trans('vegabundle.service.aya')) {
            $result .= ' '.self::$container->get('translator')->trans('vegabundle.service.obl').'.';
        } elseif (!in_array($result, self::specialNames())) {
            $type = self::specialNames()[$result];
            if($type == self::$container->get('translator')->trans('vegabundle.service.resp')) {
                $result = $type.' '.$result;
            }else {
                $result .= ' '.$type;
            }
        }

        return $result;
    }

    /**
     * @param string $fms
     * @return string
     */
    public static function convertFMS($fms)
    {
        $result = mb_convert_case($fms, MB_CASE_TITLE, self::CHARSET);

        $search = [
            self::$translator->trans('vegabundle.service.OOvd'),
            self::$translator->trans('vegabundle.service.UUfms'),
            self::$translator->trans('vegabundle.service.OOfms'),
            self::$translator->trans('vegabundle.service.OOufms'),
            self::$translator->trans('vegabundle.service.RRovd'),
            self::$translator->trans('vegabundle.service.GGovd'),
            self::$translator->trans('vegabundle.service.OOm'),
            self::$translator->trans('vegabundle.service.GGom'),
            self::$translator->trans('vegabundle.service.OOik'),
            self::$translator->trans('vegabundle.service.TTp'),
            self::$translator->trans('vegabundle.service.UUvd'),
            self::$translator->trans('vegabundle.service.UUao'),
            self::$translator->trans('vegabundle.service.SSao'),
            self::$translator->trans('vegabundle.service.ZZao'),
            self::$translator->trans('vegabundle.service.SSzao'),
            self::$translator->trans('vegabundle.service.UUao'),
            self::$translator->trans('vegabundle.service.VVao'),
            self::$translator->trans('vegabundle.service.SSvao'),
            self::$translator->trans('vegabundle.service.UUzao'),
            self::$translator->trans('vegabundle.service.UUvao'),
        ];
        $replace = array_map('mb_strtoupper', $search);

        $search = array_merge($search, [
            self::$translator->trans('vegabundle.service.GG'),
            self::$translator->trans('vegabundle.service.OObl'),
            self::$translator->trans('vegabundle.service.RRna'),
            self::$translator->trans('vegabundle.service.RRna'),
            self::$translator->trans('vegabundle.service.RRne'),
            self::$translator->trans('vegabundle.service.RRne'),
            self::$translator->trans('vegabundle.service.PPo'),
            self::$translator->trans('vegabundle.service.VV'),
            self::$translator->trans('vegabundle.service.NNa'),
            self::$translator->trans('vegabundle.service.GGor'),
        ]);
        $replace = array_merge($replace, [
            self::$translator->trans('vegabundle.service.g'),
            self::$translator->trans('vegabundle.service.obl'),
            self::$translator->trans('vegabundle.service.rna'),
            self::$translator->trans('vegabundle.service.rna'),
            self::$translator->trans('vegabundle.service.rne'),
            self::$translator->trans('vegabundle.service.rne'),
            self::$translator->trans('vegabundle.service.po'),
            self::$translator->trans('vegabundle.service.v'),
            self::$translator->trans('vegabundle.service.na'),
            self::$translator->trans('vegabundle.service.gor'),
        ]);

        //var_dump(array_combine($search, $replace));die();
        $result = str_replace($search, $replace, $result);

        return $result;
    }

    /**
     * @param string $name
     * @return string
     */
    public static function convertDocumentType($name)
    {
        $result = mb_convert_case($name, MB_CASE_TITLE, self::CHARSET);

        $search = array_merge([
            self::$translator->trans('vegabundle.service.PPo'),
            self::$translator->trans('vegabundle.service.OO'),
            self::$translator->trans('vegabundle.service.VV'),
            self::$translator->trans('vegabundle.service.NNa'),
            self::$translator->trans('vegabundle.service.RRf'),
            self::$translator->trans('vegabundle.service.IIg'),
            self::$translator->trans('vegabundle.service.SSvo-vo'),
            self::$translator->trans('vegabundle.service.LLbg'),
            self::$translator->trans('vegabundle.service.LLbg'),
            self::$translator->trans('vegabundle.service.SSSr'),
            self::$translator->trans('vegabundle.service.CC'),
        ]);
        $replace = array_merge([
            self::$translator->trans('vegabundle.service.po'),
            self::$translator->trans('vegabundle.service.o'),
            self::$translator->trans('vegabundle.service.v'),
            self::$translator->trans('vegabundle.service.na'),
            self::$translator->trans('vegabundle.service.rf'),
            self::$translator->trans('vegabundle.service.ig'),
            self::$translator->trans('vegabundle.service.svo-vo'),
            self::$translator->trans('vegabundle.service.lbg'),
            self::$translator->trans('vegabundle.service.lbg'),
            self::$translator->trans('vegabundle.service.sssr'),
            self::$translator->trans('vegabundle.service.c'),
        ]);

        $result = str_replace($search, $replace, $result);
        return $result;
    }
}