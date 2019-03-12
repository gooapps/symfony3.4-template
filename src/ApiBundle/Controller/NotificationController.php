<?php

namespace ApiBundle\Controller;

use Nelmio\ApiDocBundle\Annotation\Security;
use Symfony\Component\HttpFoundation\Request;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Swagger\Annotations as SWG;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Response;


class NotificationController extends FOSRestController {


    /**
     * @Route("/send",methods={"POST"})
     * @SWG\Response(
     *     response=200,
     *     description="send notifications"
     * )
     * @SWG\Response(
     *     response=404,
     *     description="Not Found"
     * )
     * @SWG\Parameter(
     *       name="notification",
     *       in="body",
     *       description="Json info to send notifications to user",
     *       required=true,
     *       @SWG\Schema(
     *              type="object",
     *              @SWG\Property(property="idUser", type="string"),
     *              @SWG\Property(property="title", type="string"),
     *              @SWG\Property(property="message", type="string")
     *          )
     *     )
     * @SWG\Tag(name="Notifications")
     * @Security(name="Bearer")
     */

    public function sendMessageAction (Request $request){
        $id = $request->get('idUser');
        $title = $request->get('title');
        $message = $request->get('message');

        if(!$id)
        {
            throw $this->createNotFoundException();
        }
        $em = $this->getDoctrine()->getEntityManager();
        $user = $em->getRepository('ApplicationSonataUserBundle:User')->find($id);

        if(!$user)
        {
            throw $this->createNotFoundException();
        }
        $notificationService = $this->get('notification_push.services');
        $notificationService->sendNotification($id,$title, $message,[]);

        $view = $this->view(['message' => 'Notificacion enviada'], Response::HTTP_OK);
        return $this->handleView($view);
    }

}