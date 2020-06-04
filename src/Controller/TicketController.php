<?php


namespace App\Controller;


use App\Security\ApiKeyAuthenticator;
use App\Service\TicketService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class TicketController
{
    private $params;

    private $logger;

    private $service;

    private $auth;

    public function __construct(
        ParameterBagInterface $params,
        LoggerInterface $logger,
        TicketService $ticketService,
        ApiKeyAuthenticator $apiKeyAuthenticator)
    {
        $this->params = $params;

        $this->logger = $logger;

        $this->service = $ticketService;

        $this->auth = $apiKeyAuthenticator;
    }

    public function booking(Request $request)
    {
        $this->logger->info('Start process booking');
        $body = $request->query->all();

        $check_param = $this->check_params($body);
        if ($check_param['success']) {
            try {
                $this->auth->createToken($request, $this->params->get('provider_key'));

                $this->service->booking($body);
            } catch (\Exception $e) {
                $this->logger->error('Err mess:' . $e->getMessage() . '; request params:' . json_encode($body));
                return new JsonResponse(['success' => false, 'message' => $e->getMessage()]);
            }
        } else {
            $this->logger->info('Not success params. Request params:' . json_encode($body));
            return new JsonResponse($check_param);
        }
        $this->logger->info('Success booking. Request params:' . json_encode($body));
        return new JsonResponse(['success' => true]);
    }

    public function cancelBooking(Request $request)
    {
        $this->logger->info('Start process cancel booking');
        $body = $request->query->all();

        $check_param = $this->check_params($body);
        if ($check_param['success']) {
            try {
                $this->auth->createToken($request, $this->params->get('provider_key'));

                $this->service->cancelBooking($body);
            } catch (\Exception $e) {
                $this->logger->error('Err mess:' . $e->getMessage() . '; request params:' . json_encode($body));
                return new JsonResponse(['success' => false, 'message' => $e->getMessage()]);
            }
        } else {
            $this->logger->info('Not success params. Request params:' . json_encode($body));
            return new JsonResponse($check_param);
        }

        $this->logger->info('Success cancel booking. Request params:' . json_encode($body));
        return new JsonResponse(['success' => true]);
    }

    public function sale(Request $request)
    {
        $this->logger->info('Start process sale');
        $body = $request->query->all();

        $check_param = $this->check_params($body);
        if ($check_param['success']) {
            try {
                $this->auth->createToken($request, $this->params->get('provider_key'));

                $this->service->buy($body);
            } catch (\Exception $e) {
                $this->logger->error('Err mess:' . $e->getMessage() . '; request params:' . json_encode($body));
                return new JsonResponse(['success' => false, 'message' => $e->getMessage()]);
            }
        } else {
            $this->logger->info('Not success params. Request params:' . json_encode($body));
            return new JsonResponse($check_param);
        }

        $this->logger->info('Success sale. Request params:' . json_encode($body));
        return new JsonResponse(['success' => true]);
    }

    public function cancelSale(Request $request)
    {
        $this->logger->info('Start process cancel sale');
        $body = $request->query->all();

        $check_param = $this->check_params($body);
        if ($check_param['success']) {
            try {
                $this->auth->createToken($request, $this->params->get('provider_key'));

                $this->service->cancelBuy($body);
            } catch (\Exception $e) {
                $this->logger->error('Err mess:' . $e->getMessage() . '; request params:' . json_encode($body));
                return new JsonResponse(['success' => false, 'message' => $e->getMessage()]);
            }
        } else {
            $this->logger->info('Not success params. Request params:' . json_encode($body));
            return new JsonResponse($check_param);
        }

        $this->logger->info('Success cancel sale. Request params:' . json_encode($body));
        return new JsonResponse(['success' => true]);
    }

    private function check_params($params)
    {
        if (!isset($params['secret_key'])) {
            return ['success' => false, 'message' => "Missing parameter: secret key"];
        }

        if (gettype($params['secret_key']) != 'string') {
            return ['success' => false, 'message' => "Type parameter secret_key must be string"];
        }

        if (!isset($params['seat'])) {
            return ['success' => false, 'message' => "Missing parameter: seat"];
        }

        if (isset($params['seat']) && ((int)$params['seat'] > 150 || (int)$params['seat'] < 0)) {
            return ['success' => false, 'message' => "Parameter 'seat' must be < 150"];
        }

        if (!is_numeric($params['seat'])) {
            return ['success' => false, 'message' => "Type parameter seat must be integer"];
        }

        if (!isset($params['flight'])) {
            return ['success' => false, 'message' => "Missing parameter: flight"];
        }

        if (!is_numeric($params['flight'])) {
            return ['success' => false, 'message' => "Type parameter flight must be integer"];
        }

        return ['success' => true];
    }
}