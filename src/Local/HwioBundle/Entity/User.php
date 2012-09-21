<?php

namespace Local\HwioBundle\Entity;

use FOS\UserBundle\Entity\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="fos_user")
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @ORM\Column(type="string", nullable=true, unique=true)
     */
    protected $facebookId;
    
    /**
     * @ORM\Column(type="string", nullable=true, unique=true)
     */
    protected $twitterId;

    public function __construct()
    {
        parent::__construct();
        // your own logic
    }
    
    public function setFacebookId($facebookId = null){
      $this->facebookId = $facebookId;
      if ($this->username == "") {
          $this->setUsername($facebookId);
      }
    }
    
    public function getFacebookId() {
      return $this->facebookId;
    }
    
    public function setTwitterId($twitterId = null){
      $this->twitterId = $twitterId;
      if ($this->username == "") {
          $this->setUsername($twitterId);
      }
    }
    
    public function getTwitterId() {
      return $this->twitterId;
    }
}

?>
