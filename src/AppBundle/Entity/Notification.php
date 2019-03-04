<?php
namespace AppBundle\Entity;
use Application\Sonata\UserBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;
/**
 * ChatRoom
 *
 * @ORM\Table(name="notifications")
 * @ORM\Entity()
 */
class Notification extends Base
{
    /**
     * @ORM\Column(name="send", type="array")
     */
    protected $send;
    /**
     * @ORM\Column(name="response_status", type="string")
     */
    protected $responseStatus;
    /**
     * @ORM\Column(name="response_body", type="array")
     */
    protected $responseBody;
    /**
     * @ORM\ManyToOne(targetEntity="Application\Sonata\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="patient_id", referencedColumnName="id")
     */
    protected $user;
    /**
     * Notification constructor.
     */
    public function __construct()
    {
    }
    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->getId();
    }
    /**
     * Set send
     *
     * @param array $send
     *
     * @return Notification
     */
    public function setSend($send)
    {
        $this->send = $send;
        return $this;
    }
    /**
     * Get send
     *
     * @return array
     */
    public function getSend()
    {
        return $this->send;
    }
    /**
     * Set responseStatus
     *
     * @param string $responseStatus
     *
     * @return Notification
     */
    public function setResponseStatus($responseStatus)
    {
        $this->responseStatus = $responseStatus;
        return $this;
    }
    /**
     * Get responseStatus
     *
     * @return string
     */
    public function getResponseStatus()
    {
        return $this->responseStatus;
    }
    /**
     * Set responseBody
     *
     * @param array $responseBody
     *
     * @return Notification
     */
    public function setResponseBody($responseBody)
    {
        $this->responseBody = $responseBody;
        return $this;
    }
    /**
     * Get responseBody
     *
     * @return array
     */
    public function getResponseBody()
    {
        return $this->responseBody;
    }
    /**
     * Set patient
     *
     * @param \Application\Sonata\UserBundle\Entity\User $user
     *
     * @return Notification
     */
    public function setUser(\Application\Sonata\UserBundle\Entity\User $user = null)
    {
        $this->user = $user;
        return $this;
    }
    /**
     * Get patient
     *
     * @return \Application\Sonata\UserBundle\Entity\User $user
     */
    public function getUser()
    {
        return $this->user;
    }
}