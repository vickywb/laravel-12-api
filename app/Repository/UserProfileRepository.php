<?php

namespace App\Repository;

use App\Models\UserProfile;

;

class UserProfileRepository
{
    private $userProfile;

    public function __construct(UserProfile $userProfile) {
        $this->userProfile = $userProfile;
    }

    public function store(UserProfile $userProfile)
    {
        $userProfile->save();
        
        return $userProfile;
    }
}