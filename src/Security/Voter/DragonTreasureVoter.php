<?php

namespace App\Security\Voter;

use App\Entity\DragonTreasure;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class DragonTreasureVoter extends Voter
{
    public const EDIT = 'EDIT';

//    public const VIEW = 'POST_VIEW';

    public function __construct(private Security $security)
    {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return $attribute == self::EDIT
            && $subject instanceof DragonTreasure;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }
        assert($subject instanceof DragonTreasure);
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }
        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case self::EDIT:
                return $this->security->isGranted('ROLE_TREASURE_EDIT') &&
                    $subject->getOwner() === $user;

//            case self::VIEW:
//                // logic to determine if the user can VIEW
//                // return true or false
//                break;
        }

        return false;
    }
}
