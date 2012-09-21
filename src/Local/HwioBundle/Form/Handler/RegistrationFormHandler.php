<?php

namespace Local\HwioBundle\Form\Handler;

use FOS\UserBundle\Form\Handler\RegistrationFormHandler as BaseHandler;
//use HWI\Bundle\OAuthBundle\Form\RegistrationFormHandlerInterface;
//use HWI\Bundle\OAuthBundle\Form\FOSUBRegistrationFormHandler;

class RegistrationFormHandler extends BaseHandler
{
    public function process($confirmation = false)
    {
        $user = $this->userManager->createUser();
        $this->form->setData($user);

        if ('POST' == $this->request->getMethod()) {
            $this->form->bindRequest($this->request);
            if ($this->form->isValid()) {

                // do your custom logic here

                return true;
            }
        }

        return false;
    }
}
