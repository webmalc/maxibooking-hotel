<?php
/**
 * Created by PhpStorm.
 * Date: 09.06.18
 */

namespace MBH\Bundle\ClientBundle\Lib\PaymentSystem\NewRbk;



use MBH\Bundle\ClientBundle\Document\ClientConfig;
use MBH\Bundle\ClientBundle\Exception\BadSignaturePaymentSystemException;
use Symfony\Component\HttpFoundation\Request;

class CheckWebhook
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var ClientConfig
     */
    private $clientConfig;

    /**
     * @var \Closure
     */
    private $errorResponse;

    /**
     * @var string|null
     */
    private $content;

    /**
     * @var bool
     */
    private $isContentInit = false;

    public function __construct(Request $request, ClientConfig $clientConfig)
    {
        $this->request = $request;
        $this->clientConfig = $clientConfig;
    }

    /**
     * @return bool
     */
    public function verifySignature(): bool
    {
        $signature = $this->getSignatureFromHeader();

        if  (empty($signature)) {
            return false;
        }

        $newRbk = $this->clientConfig->getNewRbk();

        $publicKey =  $newRbk !== null ? $newRbk->getWebhookKey() : null;
        $data = $this->getRawContent();

        $this->errorResponse = function () {
            http_response_code(400);
            echo json_encode(['message' => 'Webhook notification signature mismatch']);
            exit();
        };

        if (empty($data) || empty($publicKey)) {
            return false;
        }

        $publicKeyId = openssl_get_publickey($publicKey);
        if (empty($publicKeyId)) {
            return false;
        }

        $verify = openssl_verify($data, $signature, $publicKeyId, OPENSSL_ALGO_SHA256);

//        return true;
        return ($verify == 1);
    }

    /**
     * @return \Closure
     */
    public function getErrorResponse(): \Closure
    {
        return $this->errorResponse;
    }

    /**
     * @return array
     */
    public function getContent(): array
    {
        return json_decode($this->getRawContent(), true);
    }

    /**
     * @return string
     */
    private function getSignatureFromHeader(): string
    {
        $contentSignature = $_SERVER['HTTP_CONTENT_SIGNATURE'] ?? '';

        $signatureFromHeader = preg_replace("/alg=(\S+);\sdigest=/", '', $contentSignature);

        if (empty($signatureFromHeader)) {
            $this->errorResponse = function () {
                throw new BadSignaturePaymentSystemException('Signature is missing');
            };

            return '';
        }

        return $this->decoderSignature($signatureFromHeader);
    }

    private function decoderSignature($string): string
    {
        return base64_decode(strtr($string, '-_,', '+/='));
    }

    private function getRawContent()
    {
        if (!$this->isContentInit) {
            $content = file_get_contents('php://input');

            if (empty($content)) {
                /** Это для тестов */
                if (isset($_ENV['MB_CLIENT']) && $_ENV['MB_CLIENT'] === 'test') {
                    $this->content = $this->request->getContent();
                } else {
                    $this->content =  null;
                }
            } else {
                $this->content = $content;
            }

            $this->isContentInit = true;
        }

        return $this->content;
    }
}