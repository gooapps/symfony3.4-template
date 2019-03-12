<?php
namespace AppBundle\Services;

use Doctrine\ORM\EntityManager;
use Kreait\Firebase\Exception\Messaging\InvalidMessage;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\AndroidConfig;
use Kreait\Firebase\Messaging\ApnsConfig;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\ServiceAccount;

class FirebaseService {

    public $message;
    private $em;

    /**
     * Required google-service firebase file config
     * FirebaseService constructor.
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
       $serviceAccount = ServiceAccount::fromJsonFile(__DIR__."/google-service.json");
        $firebase = (new Factory)
            ->withServiceAccount($serviceAccount)
            ->create();
        $this->message = $firebase->getMessaging();

        $this->em = $entityManager;

    }

    public function sendNotification($userId, $title = "", $body = "", $data = array())
    {

        $user = $this->em->getRepository('ApplicationSonataUserBundle:User')->find($userId);

        $deviceToken = $user->getFirebaseToken();

        // notifications
        $notification = Notification::fromArray([
            'title' => $title,
            'body' => $body
        ]);

        // Android
        $configAndroid = AndroidConfig::fromArray([
            'ttl' => '3600s',
            'priority' => 'normal',
            'notification' => [
                'title' => $title,
                'body' => $body,
            ],
        ]);

        // APNS
        $configAPNS = ApnsConfig::fromArray([
            'headers' => [
                'apns-priority' => '10',
            ],
            'payload' => [
                'aps' => [
                    'alert' => [
                        'title' => $title,
                        'body' => $body,
                    ],
                    'badge' => 1,
                ],
            ],
        ]);



        $message = CloudMessage::withTarget('token', $deviceToken)
            ->withNotification($notification)// optional
            ->withData($data) // optional
            ->withAndroidConfig($configAndroid)
            ->withApnsConfig($configAPNS)
        ;

        $response = null;
        try {
            $this->message->validate($message);
            $response = $this->message->send($message);
        } catch (InvalidMessage $e) {
            print_r($e->errors());
            $response = $e->errors();
        }

        // Save notifications
        $notification = new \AppBundle\Entity\Notification();
        $notification->setUser($user);
        $notification->setSend($message->jsonSerialize());
        //$notification->setResponseBody($response->getBody()->getContents());
        //$notification->setResponseStatus($response->getStatusCode());

        $this->em->persist($notification);
        $this->em->flush();

    }
}