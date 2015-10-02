<?php
namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class StaticFileController extends Controller
{
    
    public function staticAction($filename)
    {
        $filePath = $this->get('kernel')->getRootDir() .'/../web/' . $filename;

        if (!file_exists($filePath)) {
            throw new NotFoundHttpException();
        }

        return new BinaryFileResponse($filePath);
    }
}