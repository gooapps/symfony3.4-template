<?php

namespace AppBundle\Services;
use AppBundle\Entity\Suggestion;
use Application\Sonata\UserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;

/* * */
class EmailService
{
    protected $twig, $email, $mailer, $contactMail, $projectName, $emailCopese, $container;
    public function __construct(\Swift_Mailer $mailer, EngineInterface $templating, $contactMail, $projectName, $emailCopese, $container)
    {
        $this->mailer = $mailer;
        $this->twig = $templating;
        $this->contactMail = $contactMail;
        $this->projectName = $projectName;
        $this->emailCopese = $emailCopese;
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

    public function sendCongratulationAndSuggestions(User $user, Suggestion $suggestion)
    {
        $data = [];
        $data["username"] = $user->getUsername();
        $data["full_name"] = $user->getFullname();
        if(!is_null($user->getCategory())){
            $data["group"] = $user->getCategory()->getDepartment()->getGroup();
            $data["departament"] = $user->getCategory()->getDepartment()->getName();
            $data["category"] = $user->getCategory()->getName();
        }else{
            $data["group"] = "";
            $data["departament"] = "";
            $data["category"] = "";
        }

        $data["message"] = $suggestion->getMessage();


        $template = $this->twig->render('AppBundle:email:congratulationsAndSuggestion.email.twig', array("data" => $data));

        $sub = "Nueva sugerencia y/o felicitación recibida " . $user->getFullname();

        // get url from images if exixst
        $url = [];
        foreach ($suggestion->getImage() as $media) {

            $provider = $this->container->get($media->getProviderName());
            $format = $provider->getFormatName($media, 'reference');
            $url[] = $provider->generatePublicUrl($media, $format);
        }

        return $this->sendEmailWithoutUser($this->emailCopese, $template, $sub, $url);
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

    public function sendEmailWithoutUser($emailTo, $template, $sub = "", $imagesUrl = [])
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
            ->setTo($emailTo)
            ->setBody($body, 'text/html');

        foreach ($imagesUrl as $url) {
            $email->attach(\Swift_Attachment::fromPath($url));
        }
        return $this->mailer->send($email);
    }
}