<?php
/**
 * Created by PhpStorm.
 * User: osvaldo
 * Date: 19/12/17
 * Time: 12:19
 */
namespace ApiBundle\Security;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException;
use Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor\AuthorizationHeaderTokenExtractor;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Doctrine\ORM\EntityManager;
class JWTAuthenticator extends AbstractGuardAuthenticator
{
    private $em;
    private $jwtEncoder;
    public function __construct(EntityManager $em, JWTEncoderInterface $jwtEncoder)
    {
        $this->em = $em;
        $this->jwtEncoder = $jwtEncoder;
    }
    /**
     * This will be called on every request and your job is to read the token from the request and return it.
     * If you return null, the rest of the authentication process is skipped.
     * Otherwise, getUser() will be called and the return value is passed as the first argument.
     */
    public function getCredentials(Request $request)
    {
        $extractor = new AuthorizationHeaderTokenExtractor(
            'Bearer',
            'Authorization'
        );
        if (!$token = $token = $extractor->extract($request)) {
            return null;
        }
        return array(
            'token' => $token,
        );
    }
    /**
     * If getCredentials() returns a non-null value, then this method is called and its return value
     * is passed here as the $credentials argument. Your job is to return an object that implements UserInterface.
     * If you do, then checkCredentials() will be called. If you return null authentication will fail.
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        try {
            $data = $this->jwtEncoder->decode($credentials['token']);
        } catch (JWTDecodeFailureException $e) {
            throw new CustomUserMessageAuthenticationException('Invalid Token');
        }
        if(!$data){
            return null;
        }
        $username = $data['username'];

        $user = $this->em->getRepository('ApplicationSonataUserBundle:User')->findOneByUsername($username);

        if(!$user){
            $user = $this->em->getRepository('ApplicationSonataUserBundle:User')->findOneByEmail($username);
            if(!$user) {
                return null;
            }
        }

        if(!$user->isEnabled()){
            return null;
        }

        return $user;
    }
    /**
     * If getUser() returns a User object, this method is called.
     * Your job is to verify if the credentials are correct.
     */
    public function checkCredentials($credentials, UserInterface $user)
    {
        return true;
    }
    /**
     * This is called after successful authentication and your job is to either return a Response object
     * that will be sent to the client or null to continue the request.
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        return null;
    }
    /**
     * This is called if authentication fails.
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $data = array(
            'message' => strtr($exception->getMessageKey(), $exception->getMessageData())
        );
        return new JsonResponse($data, 403);
    }
    /**
     * Is called when an anonymous request accesses a resource that requires authentication. In case of API we just need to return 401
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        $data = array(
            'message' => 'Authentication Required'
        );
        return new JsonResponse($data, 401);
    }
    /**
     * If you want to support "remember me" functionality, return true from this method.
     */
    public function supportsRememberMe()
    {
        return false;
    }
}