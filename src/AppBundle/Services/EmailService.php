<?php

namespace AppBundle\Services;
use AppBundle\Entity\Suggestion;
use Application\Sonata\UserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;

/* * */
class EmailService
{
    protected $twig, $email, $mailer, $contactMail, $projectName, $container;
    public function __construct(\Swift_Mailer $mailer, EngineInterface $templating, $contactMail, $projectName,  $container)
    {
        $this->mailer = $mailer;
        $this->twig = $templating;
        $this->contactMail = $contactMail;
        $this->projectName = $projectName;
        $this->container = $container;
    }

    public function sendRecoveryPasswordEmail(User $user, $password)
    {
        $template = $this->twig->render('AppBundle:email:emailResetPassword.email.twig', array('user' => $user->getUsername(), 'password' => $password));
        return $this->sendEmail($user, $template, "Recuperar contraseña");

    }

    public function sendWelcomeEmail(User $user)
    {
        $template = $this->twig->render('AppBundle:email:welcome.email.twig', array('username' => $user->getUsername(), 'password' => $user->getPlainPassword()));
        return $this->sendEmail($user, $template, "Datos de acceso");
    }

    public function sendEmail($user, $template, $sub = "Anuncio")
    {
        $renderedLines = explode("\n", trim($template));
        if (substr($sub, 0, 9) === "<!DOCTYPE") {
            $body = $template;
        } else {
            $body = implode("\n", array_slice($renderedLines, 1));
        }

        $email = \Swift_Message::newInstance()
            ->setSubject("Copese | ".$sub)
            ->setFrom(array($this->contactMail => $this->projectName))
            ->setTo($user->getEmail())
            ->setBody($body, 'text/html');
        return $this->mailer->send($email);
    }

}