<?php

namespace AppBundle\Security;

use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpFoundation\Response;

/**
 *
 */
class CsrfExceptionListener
{
  public function onKernelException(GetResponseForExceptionEvent $event)
  {
    $exception = $event->getException();
    if($exception->getCode() === 419) {
      $response = new Response("CSRF Token not valid", 401);
      $event->setResponse($response);
    }
  }
}
