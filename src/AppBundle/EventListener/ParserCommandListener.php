<?php

namespace AppBundle\EventListener;

use Doctrine\DBAL\Exception\ConnectionException;
use Symfony\Component\Console\Event\ConsoleExceptionEvent;
use Psr\Log\LoggerInterface;

class ParserCommandListener
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function onConsoleException(ConsoleExceptionEvent $event)
    {
        $event->getCommand();
        $exception = $event->getException();
        $statusCode = $event->getExitCode();

        // Check if it is an instance of Doctrine\DBAL\Exception\ConnectionException
        // and if that is the case, logg the error message
        if ($exception instanceof ConnectionException) {
            $exceptionMessage = sprintf('Error %s : %s', $exception->getErrorCode(), $exception->getMessage());
            $this->logger->addError("Problème / impossibilité de se Connecter à la bdd. $exceptionMessage");
        }

        if ($statusCode === 0) {
            return;
        }

        if ($statusCode > 255) {
            $statusCode = 255;
            $event->setExitCode($statusCode);
        }
    }
}