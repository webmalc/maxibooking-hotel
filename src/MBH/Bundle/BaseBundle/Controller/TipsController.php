<?php

namespace MBH\Bundle\BaseBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class TipsController extends BaseController
{
    /**
     * @Route("/add_tip", name="add_tip", options={"expose"=true})
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function addOrChangeTipAction(Request $request)
    {
        if ($this->get('kernel')->getEnvironment() !== 'dev') {
            throw new \Exception('Addition of tips can be run only in dev environment');
        }
        $formName = $request->get('formName');
        $fieldId = $request->get('fieldId');
        $tipText = $request->get('tipText');
        $lang = $request->get('lang');

        $tipsFileAddress = $this->get('kernel')->getBundle('MBHBaseBundle')->getPath() . '/Resources/public/js/app/tips_' . $lang . '.js';
        if (file_exists($tipsFileAddress)) {
            $fileOldContent = file_get_contents($tipsFileAddress);
            $jsonBegin = strpos($fileOldContent, '{');
            $jsonEnd = strrpos($fileOldContent, '}');
            $asd = substr($fileOldContent, $jsonBegin, $jsonEnd - $jsonBegin + 1);
            $json = json_decode($asd, true);
        } else {
            $json = [];
        }
        if (empty($tipText) && isset($json[$formName][$fieldId])) {
            unset($json[$formName][$fieldId]);
        }
        $json[$formName][$fieldId] = $tipText;

        $fileContent = 'var tips_' . $lang .'={';
        foreach ($json as $formName => $formData) {
            $lastFormData = end($json);
            $formTipsString = '"' . $formName . '":{';
            foreach ($formData as $fieldId => $tipText) {
                $formLastElement = end($formData);
                $formTipsString .= '"' . $fieldId . '":' . '"' . addslashes($tipText) . '"' . ($formLastElement !== $tipText ? ',' : '');
            }
            $formTipsString .= ($lastFormData !== $formData ? '},' : '}');
            $fileContent .= $formTipsString;
        }
        $fileContent .= '};';
        $fh = fopen($tipsFileAddress, 'w+');
        fwrite($fh, $fileContent);
        fclose($fh);

        return new JsonResponse(['success' => true]);
    }
}