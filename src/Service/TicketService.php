<?php


namespace App\Service;


use App\Entity\Flights;
use App\Entity\Tickets;
use App\Entity\Users;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class TicketService
{
    private $em;

    private $params;

    private $logger;

    public function __construct(EntityManagerInterface $em, ParameterBagInterface $params, LoggerInterface $logger)
    {
        $this->em = $em;

        $this->params = $params;

        $this->logger = $logger;
    }

    public function buy($params)
    {
        $user = $this->em->getRepository(Users::class)->findOneBy(['api_key' => $params['secret_key']]);
        if (!$user) {
            throw new Exception('User not found');
        }

        $flight = $this->em->getRepository(Flights::class)->find($params['flight']);
        if (!$flight) {
            throw new Exception('Flight not found');
        }

        $this->checkUserTicket($params['secret_key'], $params['flight'], $params['seat']);
        $this->checkBuy($params['secret_key'], $params['flight'], $params['seat']);
        $this->checkFlight($params['flight']);

        //if ticket is booking
        $ticket_collection = $user->getTicket();

        $keys = $ticket_collection->getKeys();
        if(!empty($keys)){
            $ticket = $ticket_collection->get($keys[0]);
        }
        if(isset($ticket)){
            $ticket->setBooking(false);
            $ticket->setBought(true);
        } else {
            $ticket = new Tickets();
            $ticket->setUser($user);
            $ticket->setBought(true);
            $ticket->setFlights($flight);
            $ticket->setSeat($params['seat']);
        }
        $this->em->persist($ticket);
        $this->em->flush();
    }

    public function cancelBuy($params)
    {
        $user = $this->em->getRepository(Users::class)->findOneBy(['api_key' => $params['secret_key']]);
        if (!$user) {
            throw new Exception('User not found');
        }

        $flight = $this->em->getRepository(Flights::class)->find($params['flight']);
        if (!$flight) {
            throw new Exception('Flight not found');
        }

        $ticket = $this->em->getRepository(Tickets::class)->findOneBy(['user'=>$user, 'flights' => $flight, 'seat' => $params['seat']]);
        if($ticket){
            if(!$ticket->getBought()){
                throw new Exception('Ticket not bought');
            }
        } else {
            throw new Exception('Ticket not found');
        }

        $this->em->remove($ticket);
        $this->em->flush();
    }

    public function booking($params)
    {
        $user = $this->em->getRepository(Users::class)->findOneBy(['api_key' => $params['secret_key']]);
        if (!$user) {
            throw new Exception('User not found');
        }

        $flight = $this->em->getRepository(Flights::class)->find($params['flight']);
        if (!$flight) {
            throw new Exception('Flight not found');
        }

        $this->checkUserTicket($params['secret_key'], $params['flight'], $params['seat']);
        $this->checkBooking($params['flight'], $params['seat']);
        $this->checkFlight($params['flight']);

        $ticket = new Tickets();
        $ticket->setUser($user);
        $ticket->setBooking(true);
        $ticket->setFlights($flight);
        $ticket->setSeat($params['seat']);
        $this->em->persist($ticket);
        $this->em->flush();
    }

    public function cancelBooking($params)
    {
        $user = $this->em->getRepository(Users::class)->findOneBy(['api_key' => $params['secret_key']]);
        if (!$user) {
            throw new Exception('User not found');
        }

        $flight = $this->em->getRepository(Flights::class)->find($params['flight']);
        if (!$flight) {
            throw new Exception('Flight not found');
        }

        $ticket = $this->em->getRepository(Tickets::class)->findOneBy(['user'=>$user, 'flights' => $flight, 'seat' => $params['seat']]);
        if($ticket){
            if(!$ticket->getBooking() && $ticket->getBought()){
                throw new Exception('Ticket bought');
            }
        } else {
            throw new Exception('Ticket not booking');
        }

        $this->em->remove($ticket);
        $this->em->flush();
    }


    //check bought or booking user another ticket
    protected function checkUserTicket(string $secret_key, int $flight, int $seat)
    {
        $user = $this->em->getRepository(Users::class)->findOneBy(['api_key' => $secret_key]);
        $tickets_collection = $user->getTicket();
        $keys = $tickets_collection->getKeys();
        foreach ($keys as $key){
            $ticket = $tickets_collection->get($key);

            if(!empty($ticket) && $ticket->getFlights()->getId() == $flight && $ticket->getSeat() != $seat ){
                if ($ticket->getBought()) {
                    throw new Exception('User bought another ticket, user can only buy one ticket');
                }

                if ($ticket->getBooking()) {
                    throw new Exception('User booking another ticket, user can only book one ticket');
                }
            }
        }
    }

    //checks, booked or bought a ticket, some user
    protected function checkBooking(int $flight, int $seat)
    {
        $ticket = $this->em->getRepository(Tickets::class)->findOneBy(['seat' => $seat, 'flights' => $flight]);
        if (!empty($ticket)) {
            if ($ticket->getBooking()) {
                throw new Exception('This ticket has already been booking');
            }

            if ($ticket->getBought()) {
                throw new Exception('This ticket has already been bought ');
            }
        }
    }

    //checks, booked the ticket another user, or bought the ticket some user
    protected function checkBuy(string $secret_key, int $flight, int $seat)
    {
        $ticket = $this->em->getRepository(Tickets::class)->findOneBy(['seat' => $seat, 'flights' => $flight]);
        if (!empty($ticket)) {
            if ($ticket->getBooking() && $ticket->getUser()->getApiKey() != $secret_key) {
                throw new Exception('This ticket booking another user');
            }

            if ($ticket->getBought()) {
                throw new Exception('This ticket has already been bought');
            }
        }
    }

    protected function checkFlight(int $flight_id)
    {
        $flight = $this->em->getRepository(Flights::class)->find($flight_id);
        if ($flight->getCanceled()) {
            throw new Exception('Flight canceled');
        }
        if ($flight->getTicketSaleEnd()) {
            throw new Exception('Ticket sales over');
        }
    }
}