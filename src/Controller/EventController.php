<?php


namespace App\Controller;


use App\Security\ApiKeyAuthenticator;
use App\Service\FlightService;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class EventController
{
    private $params;

    private $logger;

    private $service;

    private $auth;

    public function __construct(
        ParameterBagInterface $params,
        LoggerInterface $logger,
        FlightService $flightService,
        ApiKeyAuthenticator $apiKeyAuthenticator
    )
    {
        $this->params = $params;

        $this->logger = $logger;

        $this->service = $flightService;

        $this->auth = $apiKeyAuthenticator;
    }

    public function test(){
        return new Response('<head><script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script></head>');
    }

    public function index(Request $request)
    {
        $this->logger->info('Start process event handling');

        $body = $request->query->all();

        $check_param = $this->check_params($body);

        if ($check_param['success']) {
            try {
                $this->auth->createToken($request, $this->params->get('provider_key'));

                $this->auth->checkRootAccess($body['secret_key']);
                if ($body['event'] == 'flight_ticket_sales_completed') {
                    $this->service->TicketSalesCompiled($body['flight']);
                } elseif ($body['event'] == 'flight_canceled') {
                    $this->service->flightCancel($body['flight']);
                } else {
                    return new JsonResponse(['success' => false, 'message' => 'Event no correct']);
                }
            } catch (\Exception $e) {
                $this->logger->error('Err mess:' . $e->getMessage() . '; request params:' . json_encode($body));
                return new JsonResponse(['success' => false, 'message' => $e->getMessage()]);
            }
        } else {
            $this->logger->info('Not success params. Request params:' . json_encode($body));
            return new JsonResponse($check_param);
        }
        $this->logger->info('Success end event handling. Request params:' . json_encode($body));
        return new JsonResponse(['success' => true]);
    }

    private function check_params($params)
    {
        if (!isset($params['secret_key'])) {
            return ['success' => false, 'message' => "Missing parameter: secret key"];
        }

        if (gettype($params['secret_key']) != 'string'){
            return ['success' => false, 'message' => "Type parameter secret_key must be string"];
        }

        if (!isset($params['flight'])) {
            return ['success' => false, 'message' => "Missing parameter: flight"];
        }

        if (!is_numeric($params['flight'])){
            return ['success' => false, 'message' => "Type parameter flight must be integer"];
        }

        if (!isset($params['event'])) {
            return ['success' => false, 'message' => "Missing parameter: event"];
        }

        if (gettype($params['event']) != 'string'){
            return ['success' => false, 'message' => "Type parameter event must be string"];
        }

        return ['success' => true];
    }

}