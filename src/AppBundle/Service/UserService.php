<?php

namespace AppBundle\Service;

use AppBundle\Entity\User\Address;
use AppBundle\Entity\User\Child;
use AppBundle\Entity\User\Conjoint;

class UserService
{

    private $em;
    private $serviceUrl;
    private $communicationService;

    public function __construct($em, $serviceUrl, $communicationService)
    {
        $this->em = $em;
        $this->serviceUrl = $serviceUrl;
        $this->communicationService = $communicationService;
    }

    public function hidrateUserTokens($user, $tokens)
    {
        $user->setAccessToken($tokens['access_token']);
        $user->setTokenType($tokens['token_type']);
        $user->setExpiresIn($tokens['expires_in']);
    }

    public function hydrateUser($user, $withPreferences = false, $withSubscriptions = false)
    {
        $this->hidrateUserInfo($user);

        !$withPreferences ? : $this->hidrateUserPreferences($user);
        !$withSubscriptions ? : $this->hidrateUserSubscriptions($user);

        return $user;
    }

    public function hidrateUserInfo($user)
    {
        $userInfoFromWs = $this->getUserInfo($user);
        $conjointObject = new Conjoint();

        foreach ($userInfoFromWs as $key => $info) {
            if (strstr(lcfirst($key), 'conjoint')) {
                if (strstr(lcfirst($key), 'date') && $info) {
                    $conjointObject->{'set' . $key}(new \DateTime($info));
                    continue;
                }
                $conjointObject->{'set' . $key}($info);
            }
        }

        $this->hidrateUserChildren($user, $userInfoFromWs['Children']);
        $this->hidrateUserAddress($user, $userInfoFromWs);

        $user->setConjoint($conjointObject);
        $user->setIdContact($userInfoFromWs['IdContact']);
        $user->setIdTitle($userInfoFromWs['IdTitle']);
        $user->setLastName($userInfoFromWs['LastName']);
        $user->setFirstName($userInfoFromWs['FirstName']);
        $user->setBirthdate($userInfoFromWs['Birthdate'] ? new \DateTime($userInfoFromWs['Birthdate']) : null);
        $user->setWeddingDate($userInfoFromWs['WeddingDate'] ? new \DateTime($userInfoFromWs['WeddingDate']) : null);
        $user->setJob($userInfoFromWs['Job']);
        $user->setMobilePhone($userInfoFromWs['MobilePhone']);
        $user->setCustomerType($userInfoFromWs['CustomerType']);
        $user->setIsFirstLogin($userInfoFromWs['IsFirstLogin']);
        $user->setLanguage($userInfoFromWs['Language']);
        $user->setRole('ROLE_USER');
    }

    public function getUserInfo($user)
    {
        $url = $this->serviceUrl . "users/" . $user->getEmail();
        $header = array("Authorization" => $user->getTokenType() . ' ' . $user->getAccessToken());
        $responseArray = $this->communicationService->callService("GET", $url, $header);

        return $responseArray;
    }

    public function hidrateUserPreferences($user, $responseArray = null)
    {
        $url = $this->serviceUrl . "users/" . $user->getEmail() . "/preferences";
        $header = array("Authorization" => $user->getTokenType() . ' ' . $user->getAccessToken());
        $responseArray = $responseArray ? $responseArray : $this->communicationService->callService("GET", $url, $header);

        if (empty($responseArray['errors'])) {
            //empty array collection so we dont get the values doubled
            $user->clearPreferences();
            $allPreferences = $this->em->getRepository('AppBundle:User\Preference')->findAll();
            $userPreferences = array();
            foreach ($responseArray['Preferences'] as $preference) {
                $userPreferences[] = $preference['IdPref'];
            }
            foreach ($allPreferences as $preference) {
                if (in_array($preference->getId(), $userPreferences)) {
                    $preference->getTarget() == 'profil' ? $user->addOwnPreference($preference) : $user->addConjointPreference($preference);
                }
            }
        }
    }

    public function hidrateUserSubscriptions($user, $responseArray = null)
    {
        $url = $this->serviceUrl . "public/" . $user->getEmail() . "/subscriptions";
        $responseArray = $responseArray ? $responseArray : $this->communicationService->callService("GET", $url);

        if (empty($responseArray['errors'])) {
            //empty array collection so we dont get the values doubled
            $user->clearSubscriptions();
            $allSubscriptions = $this->em->getRepository('AppBundle:User\Subscription')->findAll();
            $userSubscriptions = array();
            foreach ($responseArray['Subscriptions'] as $subscription) {
                $userSubscriptions[] = $subscription['IdSubscription'];
            }

            foreach ($allSubscriptions as $subscription) {
                if (in_array($subscription->getId(), $userSubscriptions)) {
                    $user->addSubscription($subscription);
                }
            }
        }
    }

    public function refreshUserEntities($user)
    {
        $userSubscriptions = array();
        foreach($user->getSubscriptions() as $subscription) {
            $userSubscriptions['Subscriptions'][] = array('IdSubscription' => $subscription->getId());
        }

        $userPreferences = array();
        foreach($user->getOwnPreferences() as $preference) {
            $userPreferences['Preferences'][] = array('IdPref' => $preference->getId());
        }
        foreach($user->getConjointPreferences() as $preference) {
            $userPreferences['Preferences'][] = array('IdPref' => $preference->getId());
        }

        $this->hidrateUserSubscriptions($user, $userSubscriptions);
        $this->hidrateUserPreferences($user, $userPreferences);
    }

    public function hidrateUserChildren($user, $childrenFromWs)
    {
        if (empty($childrenFromWs)) {
            return;
        }

        $user->clearChildren();
        //add children from WS to user object
        foreach ($childrenFromWs as $childFromWs) {
            $this->hidrateUserChild($user, $childFromWs);
        }
    }

    public function hidrateUserChild($user, $child)
    {
        $childObject = new Child();

        $childObject->setChildId($child['ChildId']);
        $childObject->setChildLastName($child['ChildLastName']);
        $childObject->setChildFirstName($child['ChildFirstName']);
        $childObject->setChildSex($child['ChildSex']);
        $childObject->setChildBirthdate(new \DateTime($child['ChildBirthdate']));

        $user->addChild($childObject);
    }

    public function hidrateUserAddress($user, $userInfo)
    {
        $addressObject = new Address();

        $addressObject->setAdrLine1($userInfo['AdrLine1']);
        $addressObject->setAdrLine2($userInfo['AdrLine2']);

        if ($userInfo['AdrLine1'] == '' && $userInfo['AdrLine2'] != '') {
            $addressObject->setAdrLine1($userInfo['AdrLine2']);
            $addressObject->setAdrLine2('');
        }

        $addressObject->setAdrLine3($userInfo['AdrLine3']);
        $addressObject->setAdrLine4($userInfo['AdrLine4']);
        $addressObject->setZipCode($userInfo['ZipCode']);
        $addressObject->setCity($userInfo['City']);
        $addressObject->setCountry($userInfo['IdCountry']);

        $user->setAddress($addressObject);
    }

}