<?php

namespace Local\HwioBundle\Security\Provider;

use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthAwareUserProviderInterface;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use HWI\Bundle\OAuthBundle\Security\Core\Exception\AccountNotLinkedException;

class HwiProvider implements UserProviderInterface, OAuthAwareUserProviderInterface{

    protected $userManager;
    protected $properties;

    public function __construct($userManager, array $properties)
    {
        $this->userManager = $userManager;
        $this->properties  = $properties;
    }
    
    public function loadUserByUsername($username)
    {
        $user = $this->userManager->findUserByUsername($username);

        if (!$user) {
            throw new UsernameNotFoundException(sprintf('No user with name or email "%s" was found.', $username));
        }

        return $user;
    }
    
    public function findUserByFbId($fbId)
    {
        return $this->userManager->findUserBy(array('facebookId' => $fbId));
    }
    
    public function findUserByTweetId($TweetId)
    {
        return $this->userManager->findUserBy(array('twitterId' => $TweetId));
    }
    
    public function findUserByEmail($email)
    {
        return $this->userManager->findUserBy(array('email' => $email));
    }
    
    public function loadUserByOAuthUserResponse(UserResponseInterface $response) {
      $resourceOwnerName = $response->getResourceOwner()->getName();
      //ladybug_dump_die($this->countFriends($this->getFriends($response)));
      
      switch ($resourceOwnerName) {
        case "facebook_custom":
          $FbId = $response->getId();
          $user = $this->findUserByFbId($FbId);
          
          if ( empty($user) ){
            $user = $this->findUserByEmail($email = $response->getEmail());
            if ( !empty($user) ){
              $user->setFacebookId($FbId);
            }
            else {
              $user = $this->userManager->createUser();
              $user->setEnabled(true);
              $user->addRole('ROLE_USER');
              $user->setPassword('');
              $user->setUsername($response->getUsername());
              $user->setFacebookId($FbId);
              $user->setEmail($response->getEmail());      
            }
            $this->userManager->updateUser($user);
          }
          return $this->loadUserByUsername($user->getUsername());
      
          break;
        case "twitter_custom":
          /*   
          $TweetId = $response->getId();
          $user = $this->findUserByTweetId($TweetId);
          
          if ( empty($user) ){
              $user = $this->userManager->createUser();
              $user->setEnabled(true);
              $user->addRole('ROLE_USER');
              $user->setPassword('');
              $user->setUsername($response->getUsername());
              $user->setTwitterId($TweetId);
              $this->userManager->updateUser($user);
          }
          
          return $this->loadUserByUsername($user->getUsername());
          */
          $username = $response->getUsername();
          $id = $response->getId();
          $user = $this->userManager->findUserBy(array($this->getProperty($response) => $id));

          if (null === $user) {
              throw new AccountNotLinkedException(sprintf("User '%s' not found.", $username));
          }

          return $user;
          
          break;
        default:
        break;
      }
    }
    
    public function connect($user, UserResponseInterface $response)
    {
        $property = $this->getProperty($response);
        $setter = 'set'.ucfirst($property);
        
        if (!method_exists($user, $setter)) {
            throw new \RuntimeException(sprintf("Class '%s' should have a method '%s'.", get_class($user), $setter));
        }

        $id = $response->getId();

        if (null !== $previousUser = $this->userManager->findUserBy(array($property => $id))) {
            $previousUser->$setter(null);
            $this->userManager->updateUser($previousUser);
        }

        $user->$setter($id);

        $this->userManager->updateUser($user);
    }

    public function refreshUser(UserInterface $user)
    {
        if (!$this->supportsClass(get_class($user))) {
            throw new UnsupportedUserException(sprintf('Unsupported user class "%s"', get_class($user)));
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    public function supportsClass($class)
    {
        return $class === 'HWI\\Bundle\\OAuthBundle\\Security\\Core\\User\\OAuthUser';
    }
    
    protected function getProperty(UserResponseInterface $response)
    {
        $resourceOwnerName = $response->getResourceOwner()->getName();
        if (!isset($this->properties[$resourceOwnerName])) {
            throw new \RuntimeException(sprintf("No property defined for entity for resource owner '%s'.", $resourceOwnerName));
        }

        return $this->properties[$resourceOwnerName];
    }
    
    protected function getFriends(UserResponseInterface $response)
    {
      $accessToken = $response->getAccessToken();
      $url = 'https://graph.facebook.com/me/friends';
      $url .= (false !== strpos($url, '?') ? '&' : '?').http_build_query(array(
          'access_token' => $accessToken
          ));
      $content = file_get_contents($url);
      $friends_data = json_decode($content, true);
      return $friends_data;
    }
    
    protected function countFriends($friendsData)
    {
      $number = count($friendsData['data']);
      return $number;
    }
}

?>
