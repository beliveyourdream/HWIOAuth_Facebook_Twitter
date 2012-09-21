<?php

namespace Local\HwioBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('LocalHwioBundle:Default:index.html.twig');
    }
}
