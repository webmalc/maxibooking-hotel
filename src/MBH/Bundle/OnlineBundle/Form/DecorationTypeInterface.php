<?php
/**
 * Created by PhpStorm.
 * Date: 31.05.18
 */

namespace MBH\Bundle\OnlineBundle\Form;


use Symfony\Component\Form\FormBuilderInterface;

interface DecorationTypeInterface
{
    function isFullWidth(FormBuilderInterface $builder): FormBuilderInterface;

    function frameWidth(FormBuilderInterface $builder): FormBuilderInterface;

    function frameHeight(FormBuilderInterface $builder): FormBuilderInterface;

    function css(FormBuilderInterface $builder): FormBuilderInterface;

    function isHorizontal(FormBuilderInterface $builder): FormBuilderInterface;

    function cssLibraries(FormBuilderInterface $builder): FormBuilderInterface;

    function theme(FormBuilderInterface $builder): FormBuilderInterface;
}