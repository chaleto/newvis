<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Team;
use App\Repository\TeamRepository;


class MainController extends AbstractController
{
    /**
     * @Route("/", name="main")
     */
    public function index(): Response
    {
        return $this->render('main/index.html.twig');
    }
    /**
    *@Route("/about", name="about")
    */
    public function about(TeamRepository $teamrepo): Response{

      return $this->render('main/about.html.twig',[
        'users' => $teamrepo->findAll()
      ]);
    }
    /**
    *@Route("/contacts", name="contacts")
    */
    public function contact(): Response{
      return $this->render('main/contact.html.twig');
    }
    /**
    *@Route("/support", name="support")
    */
    public function support(): Response{
      return $this->render('support/support.html.twig');
    }
    /**
    *@Route("/hardware", name="hardware")
    */
    public function hardware(): Response{
      return $this->render('services/hardware.html.twig');
    }
    /**
    *@Route("/software", name="software")
    */
    public function software(): Response{
      return $this->render('services/software.html.twig');
    }
    /**
    *@Route("/biography/{id}", name="biography")
    */
    public function biography(TeamRepository $teamrepo, $id): Response{
      return $this->render('team/team.html.twig', [
        'users'=> $teamrepo->findBy(['id' => $id])
      ]);
    }
}
