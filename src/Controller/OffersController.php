<?php

namespace App\Controller;

use App\Entity\Offers;
use App\Entity\Orders;
use App\Entity\User;
use App\Form\OffersType;
use App\Form\OrdersType;
use App\Repository\OffersRepository;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mime\Email;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Symfony\Component\Mailer\MailerInterface;


/**
 * @Route("/offers")
 */
class OffersController extends AbstractController
{
    /**
     * @Route("/", name="offers_index", methods={"GET"})
     */
    public function index(OffersRepository $offersRepository): Response
    {
        return $this->render('offers/index.html.twig', [
            'offers' => $offersRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="offers_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $offer = new Offers();
        $form = $this->createForm(OffersType::class, $offer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($offer);
            $entityManager->flush();

            return $this->redirectToRoute('offers_index');
        }

        return $this->render('offers/new.html.twig', [
            'offer' => $offer,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="offers_show", methods={"GET"})
     */
    public function show(Offers $offer): Response
    {
        return $this->render('offers/show.html.twig', [
            'offer' => $offer,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="offers_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Offers $offer): Response
    {
        $form = $this->createForm(OffersType::class, $offer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('offers_index');
        }

        return $this->render('offers/edit.html.twig', [
            'offer' => $offer,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="offers_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Offers $offer): Response
    {
        if ($this->isCsrfTokenValid('delete'.$offer->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($offer);
            $entityManager->flush();
        }

        return $this->redirectToRoute('offers_index');
    }
    /**
     * @Route("/buy/{id}", name="offers_buy", methods={"GET","POST"})
     */
    public function buy(Offers $offer, Request $request, MailerInterface $mailer): Response{

    $order = new Orders();
    $user = new User();
    $form = $this->createForm(OrdersType::class, $order);
    $form->handleRequest($request);


     if($form->isSubmitted() && $form->isValid()){

       $entityManager = $this->getDoctrine()->getManager();

       $userid = $form->get('userid')->getData();
       $usermail = $form->get('usermail')->getData();
       $offerid = $form->get('packid')->getData();
       $offername = $form->get('offername')->getData();
       $price = $form->get('price')->getData();


       $order->setUserid($userid);
       $order->setPackid($offerid);
       $order->setOffername($offername);
       $order->setCreatedAt(new \DateTime('now'));
       $order->setPrice($price);

       $entityManager->persist($order);
       $entityManager->flush();



       $buyemail = (new Email())
           ->from('newvis.design@abv.bg')
           ->to($usermail)
           //->cc('cc@example.com')
           //->bcc('bcc@example.com')
           //->replyTo('fabien@example.com')
           //->priority(Email::PRIORITY_HIGH)
           ->subject('NEWVIS ORDER!')
           ->html('Your ordered a '.$offername.' package , Your order detail are:'.$offer->getName().' price: '.$offer->getPrice().' USD ');

           $myemail = (new Email())
           ->from('newvis.design@abv.bg')
           ->to('milev981@abv.bg')
           ->subject('NEWVIS ORDER')
           ->html('New Order with id:'.$order->getId().' '.$order->getUserMail().' Package: '.$offer->getName());

       $mailer->send($myemail);
       $mailer->send($buyemail);
     }

      return $this->render('offers/buy.html.twig', [

        'offer' => $offer,
        'form' => $form->createView()
      ]);
    }

}
