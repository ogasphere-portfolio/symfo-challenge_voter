<?php

namespace App\Security\Voter;

use App\Entity\User;
use App\Entity\Question;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class QuestionVoter extends Voter
{

    // these strings are just invented: you can use anything
    const VIEW = 'question_view';
    const EDIT = 'question_edit';
    const DELETE = 'question_delete';
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports($attribute, $question)
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [self::VIEW, self::EDIT,self::DELETE])) {
            return false;
        }
         // only vote on `Post` objects
         if (!$question instanceof Question) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $question, TokenInterface $token)
    {
        
        // ROLE_SUPER_ADMIN can do anything! The power!
      
        if ($this->security->isGranted('ROLE_MODERATOR')) {
            return true;
        }
        
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        // on verifie si le User est connectée
        if (!$user instanceof UserInterface) {
            return false;
        }
        
        // on verifie si la question à un proprietaire
        if (null === $question->getUser()) return false;

        // you know $subject is a Post object, thanks to `supports()`
        /** @var Post $post */
       
        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case self::EDIT:
                // logic to determine if the user can EDIT
                // return true or false
                return $this->canEdit($question, $user);
                break;
            case self::VIEW:
                // logic to determine if the user can VIEW
                // return true or false
                return $this->canView($question, $user);
                break;
        }

        return false;
    }


    private function canView(Question $question, User $user): bool
    {
        // if they can edit, they can view
        if ($this->canEdit($question, $user)) {
            return true;
        }

        // On verifie que la question n'est pas bloqué par l'administrateur
        return !$question->getisBlocked();
    }

    private function canEdit(Question $question, User $user): bool
    {
       
        if ($this->security->isGranted('ROLE_MODERATOR') ) 
        {
            return true;
        }

        // si créateur de la question => oui
        if ($question->getUser()->getId() === $user->getId()) 
        {
            return true;
        }
        // On verifie que la question n'est pas bloqué par l'administrateur
        return !$question->getisBlocked();
    }
}
