<?php

namespace MBH\Bundle\BaseBundle\Service;

use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;

class GuidesDataService
{
    const GUIDES = [
        'first-guide-1' => [
            'guides' => ['room-cache-1', 'price-cache-1', 'search-guide-1']
        ],
        'room-cache-1' => [
            'rights' => ['ROLE_ROOM_CACHE_VIEW', 'ROLE_ROOM_CACHE_EDIT', 'ROLE_ROOM_CACHE_EDIT']
        ],
        'price-cache-1' => [
            'rights' => ['ROLE_PRICE_CACHE_VIEW', 'ROLE_PRICE_CACHE_EDIT', 'ROLE_PRICE_CACHE_EDIT']
        ],
        'search-guide-1' => [
            'rights' => ['ROLE_SEARCH', 'ROLE_PACKAGE_NEW']
        ]
    ];

    private $authorizationChecker;

    public function __construct(AuthorizationChecker $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    public function getAllowedGuides()
    {
        $allowedGuides = [];
        foreach (array_keys(self::GUIDES) as $guideName) {
            if ($this->isGuideAllowed($guideName)) {
                $allowedGuides[] = $guideName;
            }
        }

        return $allowedGuides;
    }

    /**
     * @param $guideName
     * @return bool
     */
    public function isGuideAllowed($guideName)
    {
        $isAllowed = true;
        if (!isset(self::GUIDES[$guideName])) {
            throw new \InvalidArgumentException('Incorrect guide name: ' . $guideName);
        }

        $guideData = self::GUIDES[$guideName];
        if (isset($guideData['rights'])) {
            foreach ($guideData['rights'] as $right) {
                if (!$this->authorizationChecker->isGranted($right)) {
                    $isAllowed = false;
                }
            }
        }

        if (isset($guideData['guides'])) {
            foreach ($guideData['guides'] as $guide) {
                $isAllowed = $this->isGuideAllowed($guide);
                if (!$isAllowed) {
                    $isAllowed = false;
                }
            }
        }

        return $isAllowed;
    }
}