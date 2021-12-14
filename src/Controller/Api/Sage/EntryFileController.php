<?php
namespace App\Controller\Api\UserCsb;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class EntryFileController extends AbstractController
{
    private $em;
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function __invoke(User $data)
    {
        $responseData = $this->em->getRepository(User::class)
            ->findOneBy(array("id" => $data, "userTypeId" => 2));
        return $responseData;
    }

}