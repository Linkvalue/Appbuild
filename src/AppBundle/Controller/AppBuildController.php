<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\Application;

class AppBuildController extends Controller
{

    /**
     * Upload file
     * @param  Request $request 
     * @return [Response]           
     */
    public function uploadAction(Request $request)
    {
        $application = new Application();
        $form = $this->createFormBuilder($application)
        ->add('name')
        ->add('file')
        ->add('submit','submit')
        ->getForm();

        if ($this->getRequest()->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
               $application->upload(); 
            }
        }

        return $this->render('AppBundle:Upload:upload.html.twig', [
            'form' => $form->createView()
        ]);
    }

    public function download(Application $application)
    {

    }
}

