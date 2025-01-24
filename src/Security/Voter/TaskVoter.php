<?php

namespace App\Security\Voter;

use App\Entity\Task;
use App\Entity\User;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class TaskVoter extends Voter
{
    // Définir les actions possibles
    const VIEW = 'view';
    const EDIT = 'edit';
    const DELETE = 'delete';

    protected function supports(string $attribute, $subject): bool
    {
        if (!in_array($attribute, [self::VIEW, self::EDIT, self::DELETE])) {
            return false;
        }

        if (!$subject instanceof Task) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute(string $attribute, $task, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            return false;
        }

        switch ($attribute) {
            case self::VIEW:
                return $this->canView($task, $user);

            case self::EDIT:
                return $this->canEdit($task, $user);

            case self::DELETE:
                return $this->canDelete($user);
        }

        return false;
    }

    private function canView(Task $task, UserInterface $user): bool
    {
        // L'utilisateur peut voir la tâche s'il en est l'auteur
        // L'administrateur peut voir toutes les tâches
        return $task->getAuthor() === $user || in_array('ROLE_ADMIN', $user->getRoles());
    }

    private function canEdit(Task $task, UserInterface $user): bool
    {
        // Un utilisateur peut éditer la tâche s'il en est l'auteur
        // Un administrateur peut modifier n'importe quelle tâche
        return $task->getAuthor() === $user || in_array('ROLE_ADMIN', $user->getRoles());
    }
    private function canDelete(UserInterface $user): bool
    {
        // Seul un administrateur peut supprimer une tâche
        return in_array('ROLE_ADMIN', $user->getRoles());
    }
}
