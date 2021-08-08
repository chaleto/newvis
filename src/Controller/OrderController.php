<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Orders;
use App\Form\OrdersType;
use App\Repository\OrdersRepository;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

class OrderController extends AbstractController
{
    /**
     * @Route("/user", name="user")
     */
    public function findOrder(Request $request, OrdersRepository $orderRepository)
    {
      $orders = new Orders();
      $form = $this->createFormBuilder($orders)
      ->add('userid')
      ->add('check', SubmitType::class)
      ->getForm();

        $search = $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

        $userid = $form->get('userid')->getData();

        $result = $this->getDoctrine()->getRepository(Orders::class)->findOrder($userid);


        return $this->render('user/orders.html.twig',[
          'result' => $result
        ]);
}

      return $this->render('user/dash.html.twig', [
        'form' => $form->createView()
      ]);
    }


}
