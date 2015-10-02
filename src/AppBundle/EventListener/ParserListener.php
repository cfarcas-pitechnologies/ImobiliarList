<?php

namespace AppBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Doctrine\DBAL\Exception\ConnectionException;

class ParserListener
{

    private $logger;

    public function setParserLoggerService($logger)
    {
        $this->logger = $logger;
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        // Get the exception object from the received event
        $exception = $event->getException();
        $message = sprintf('Error %s : %s', $exception->getCode(), $exception->getMessage());

        // Check if it is an instance of Doctrine\DBAL\Exception\ConnectionException
        // and if that is the case, logg the error message
        if ($exception instanceof ConnectionException) {
            $message = sprintf('Error %s : %s', $exception->getErrorCode(), $exception->getMessage());
            $this->logger->addError("Problème / impossibilité de se Connecter à la bdd. $message");
        }
    }

}