<?php


namespace App\Command;


use App\Entity\Flights;
use App\Entity\Users;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class FillDefaultDataDBCommand extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:default-data-create';

    private $logger;

    private $params;

    private $em;

    protected function configure()
    {
        $this
            // the short description shown while running "php bin/console list"
            ->setDescription('Create default Users, Roles, and flights')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('Create default Users, Roles, and flights')
        ;
    }

    public function __construct(
        string $name = null,
        ParameterBagInterface $params,
        LoggerInterface $logger,
        EntityManagerInterface $em
    )
    {
        $this->logger = $logger;
        $this->params = $params;
        $this->em = $em;
        parent::__construct($name);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $output->writeln('Start load Users');
            $users = $this->getUsers();
            $this->loaderUser($users);
            $output->writeln('Finish load Users');

            $output->writeln('Start load Flights');
            $flights = $this->getFlights();
            $this->loaderFlights($flights);
            $output->writeln('Finish load Flights');
        } catch (\Exception $e) {
            $output->writeln($e->getMessage());
            $output->writeln($e->getLine());
        }
        return 1;
    }

    private function loaderUser($users){
        foreach ($users as $user) {
            $user_entity = new Users();
            $user_entity->setName($user['name']);
            $user_entity->setAccessRights($user['access_lvl']);
            $user_entity->setApiKey($user['api_key']);
            $user_entity->setEmail($user['mail']);

            $this->em->persist($user_entity);
            $this->em->flush();
        }

        return 1;
    }

    private function loaderFlights($flights){
        foreach ($flights as $flight) {
            $flight_entity = new Flights();
            $flight_entity->setCanceled($flight['canceled']);
            $flight_entity->setTicketSaleEnd($flight['sale_end']);
            $flight_entity->setCode($flight['code']);

            $this->em->persist($flight_entity);
            $this->em->flush();
        }

        return 1;
    }

    private function getFlights(){
        return [
            [
                'canceled' => false,
                'sale_end' => false,
                'code' => '1451245'
            ],
            [
                'canceled' => false,
                'sale_end' => true,
                'code' => '7634224'
            ],
            [
                'canceled' => true,
                'sale_end' => false,
                'code' => '3465532'
            ],
            [
                'canceled' => true,
                'sale_end' => true,
                'code' => '9357425'
            ],
        ];
    }

    private function getUsers(){
        return [
            [
                'name' => 'User 1',
                'access_lvl' => 1,
                'api_key' => 'odHufJD7Mjf67',
                'mail' => $this->params->get('mail.from')
            ],
            [
                'name' => 'User 2',
                'access_lvl' => 2,
                'api_key' => 'odHufdfJD7Mjf67',
                'mail' => $this->params->get('mail.from')
            ],
            [
                'name' => 'User 3',
                'access_lvl' => 3,
                'api_key' => 'odHcvufJD7bbMjf67',
                'mail' => $this->params->get('mail.from')
            ],
        ];
    }

    private function getRoles(){

    }
}