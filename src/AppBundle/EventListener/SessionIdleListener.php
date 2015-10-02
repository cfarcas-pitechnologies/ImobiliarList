<?php


namespace AppBundle\EventListener;


use AppBundle\Entity\User\User;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;

class SessionIdleListener
{

    protected $session;
    protected $router;
    protected $securityContext;
    protected $idletime;

    public function __construct(SessionInterface $session, SecurityContextInterface $securityContext, RouterInterface $router, $idletime)
    {
        $this->session = $session;
        $this->router = $router;
        $this->securityContext = $securityContext;
        $this->idletime = $idletime;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        if (HttpKernelInterface::MASTER_REQUEST != $event->getRequestType()) {

            return;
        }

        if ($this->securityContext->getToken() && $this->securityContext->getToken()->getUser() instanceof User) {
            $lapse = time() - $this->session->getMetadataBag()->getLastUsed();

            if ($lapse > $this->idletime) {
                $this->session->invalidate();
                $this->securityContext->setToken(null);
                $this->session->getFlashBag()->set('user_logout', 'text.logout');

                $event->setResponse(new RedirectResponse($this->router->generate('login')));
            }
        }
    }
}