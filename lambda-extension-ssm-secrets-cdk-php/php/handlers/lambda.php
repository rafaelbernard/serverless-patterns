<?php

use Bref\Context\Context;
use Bref\Event\Http\HttpResponse;
use GuzzleHttp\Client;
use Symfony\Component\HttpFoundation\JsonResponse;

function getParam(string $parameterPath): string
{
    // Set `withDecryption=true if you also want to retrieve SecureString SSMs
    $url = "http://localhost:2773/systemsmanager/parameters/get?name={$parameterPath}&withDecryption=true";

    try {
        $client = new Client();

        $response = $client->get($url, [
            'headers' => [
                'X-Aws-Parameters-Secrets-Token' => getenv('AWS_SESSION_TOKEN'),
            ]
        ]);

        $data = json_decode($response->getBody());
        return $data->Parameter->Value;
    } catch (\Exception $e) {
        error_log('Error getting parameter => ' . print_r($e, true));
    }
}

return function ($request, Context $context) {
    $response = new JsonResponse([
        'status' => 'OK',
        getenv('THE_SSM_PARAM_PATH') => getParam(getenv('THE_SSM_PARAM_PATH')),
    ]);

    return (new HttpResponse($response->getContent(), $response->headers->all()))->toApiGatewayFormatV2();
};
