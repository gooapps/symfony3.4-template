<?php

namespace AppBundle\Services;
use Doctrine\ORM\EntityManager;
use GuzzleHttp\Client as ClientHttp;
use sngrl\PhpFirebaseCloudMessaging\Client;
use sngrl\PhpFirebaseCloudMessaging\Message;
use sngrl\PhpFirebaseCloudMessaging\Notification;
use sngrl\PhpFirebaseCloudMessaging\Recipient\Device;



class NotificationsPushService
{
    private $key;
    private $em;
    /**
     * RecomendationService constructor.
     */
    public function __construct($key, EntityManager $entityManager)
    {
        $this->key = $key;
        $this->em = $entityManager;
    }
    /**
     * @param $userId
     * @param string $title
     * @param string $messageText
     * @param int $type
     * @param array $data
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function sendNotification($userId, $title = "", $messageText = "", $type = 1, $data = array())
    {




        $user = $this->em->getRepository('ApplicationSonataUserBundle:User')->find($userId);
        if (!$user) {
            return;
        }

        if (is_null($user->getFirebaseToken())) {
            return;
        }

        $data["type"] = $type;
        $data["title"] = $title;
        $data["body"] = $messageText;
        $data["priority"] = 'high';


        $client = new Client();
        $client->setApiKey($this->key);
        $client->injectGuzzleHttpClient(new ClientHttp());


        $message = new Message();
        $message->setPriority('high');


        $notif = new Notification($title, $messageText);
        $notif->setClickAction('FCM_PLUGIN_ACTIVITY');
        $notif->setSound("default");



        $message->addRecipient(new Device($user->getFirebaseToken()));
        $message
            ->setNotification($notif)
            ->setData($data);


        $response = $client->send($message);

        $notification = new \AppBundle\Entity\Notification();
        $notification->setUser($user);
        $notification->setSend($message->jsonSerialize());
        $notification->setResponseBody($response->getBody()->getContents());
        $notification->setResponseStatus($response->getStatusCode());


        $this->em->persist($notification);
        $this->em->flush();
    }
}