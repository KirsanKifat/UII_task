<?php


namespace App\Service;


use App\Entity\Flights;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class FlightService
{
    private $em;

    private $logger;

    private $params;

    private $mailerService;

    public function __construct(EntityManagerInterface $em, LoggerInterface $logger, ParameterBagInterface $params, MailerService $mailerService)
    {
        $this->em = $em;

        $this->logger = $logger;

        $this->params = $params;

        $this->mailerService = $mailerService;
    }

    public function flightCancel(int $flight_id){
        $flight = $this->em->getRepository(Flights::class)->find($flight_id);
        if(!$flight){
            throw new \Exception('Flight not found');
        }
        if($flight->getCanceled()){
            throw new \Exception('Flight already canceled');
        }
        $flight->setCanceled(true);
        $this->em->persist($flight);
        $this->em->flush();

        //get emails any user who booking or bought ticket
        $emails = [];
        $tickets_collection = $flight->getTicketId();
        $tickets_keys = $tickets_collection->getKeys();
        foreach ($tickets_keys as $tickets_key){
            $ticket = $tickets_collection->get($tickets_key);
            $user = $ticket->getUser();
            $email = $user->getEmail();
            if(!in_array($email, $emails)){
                $emails[] = $email;
            }
        }

        $flight_code = $flight->getCode();
        $this->mailerService->sendCancelFlightMail($emails, $flight_code);
    }

    public function TicketSalesCompiled(int $flight_id){
        $flight = $this->em->getRepository(Flights::class)->find($flight_id);

        if(!$flight){
            throw new \Exception('Flight not found');
        }

        if ($flight->getTicketSaleEnd()){
            throw new \Exception('Ticket sales already completed');
        }
        $flight->setTicketSaleEnd(true);
        $this->em->persist($flight);
        $this->em->flush();
    }
}