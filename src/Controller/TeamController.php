<?php

namespace App\Controller;

use App\Entity\Team;
use App\Form\TeamType;
use App\Repository\TeamRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\File;
/**
 * @Route("/team")
 */
class TeamController extends AbstractController
{

  #  /**
  #   * @Route("/", name="team_index", methods={"GET"})
  #   */
   /* public function index(TeamRepository $teamRepository): Response
    {
        return $this->render('team/index.html.twig', [
            'teams' => $teamRepository->findAll(),
        ]);
   }
   */

    /**
     * @Route("/new", name="team_new", methods={"GET","POST"})
     */
    public function new(Request $request, SluggerInterface $slugger): Response
    {
        $team = new Team();
        $form = $this->createForm(TeamType::class, $team);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $image = $form->get('image')->getData();
            if($image){
              $originalname = pathinfo($image->getClientoriginalName(), PATHINFO_FILENAME);
              $safename = $slugger->slug($originalname);
              $newname = $safename.'-'.uniqid().'.'.$image->guessExtension();

              try{
                $image->move(
                  $this->getParameter('image_dir'),$newname
                );
              } catch(FileException $e){

              }
              $team->setImage($newname);
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($team);
            $entityManager->flush();

            return $this->redirectToRoute('team_index');
        }

        return $this->render('team/new.html.twig', [
            'team' => $team,
            'form' => $form->createView(),
        ]);
    }

  #  /**
  #   * @Route("/{id}", name="team_show", methods={"GET"})
  #   */
  /*  public function show(Team $team): Response
    {
        return $this->render('team/show.html.twig', [
            'team' => $team,
        ]);
    }
    */

    /**
     * @Route("/{id}/edit", name="team_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Team $team, SluggerInterface $slugger): Response
    {
    
        $form = $this->createForm(TeamType::class, $team);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

          $image = $form->get('image')->getData();
          if($image){
            $originalname = pathinfo($image->getClientoriginalName(), PATHINFO_FILENAME);
            $safename = $slugger->slug($originalname);
            $newname = $safename.'-'.uniqid().'.'.$image->guessExtension();

            try{
              $image->move(
                $this->getParameter('image_dir'),$newname
              );
            } catch(FileException $e){

            }
            $team->setImage($newname);
          }

            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('app_login');
        }

        return $this->render('team/edit.html.twig', [
            'team' => $team,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="team_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Team $team): Response
    {
        if ($this->isCsrfTokenValid('delete'.$team->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($team);
            $entityManager->flush();
        }

        return $this->redirectToRoute('team_index');
    }
}
