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
        // Vérifie si l'attribut est valide pour cette entité
        if (!in_array($attribute, [self::VIEW, self::EDIT, self::DELETE])) {
            return false;
        }

        // Vérifie si le sujet est une instance de Task
        if (!$subject instanceof Task) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute(string $attribute, $task, TokenInterface $token): bool
    {
        $user = $token->getUser();

        // Vérifie si l'utilisateur est connecté
        if (!$user instanceof UserInterface) {
            return false;
        }

        // Vérifie les droits d'accès selon l'attribut
        switch ($attribute) {
            case self::VIEW:
                // Un utilisateur peut voir la tâche s'il est l'auteur ou un administrateur
                return $this->canView($task, $user);

            case self::EDIT:
                // Seul l'auteur ou un administrateur peut éditer la tâche
                return $this->canEdit($task, $user);

            case self::DELETE:
                // Un administrateur peut supprimer la tâche
                return $this->canDelete($user);
        }

        return false;
    }

    private function canView(Task $task, UserInterface $user): bool
    {
        // L'utilisateur peut voir la tâche s'il en est l'auteur ou si il est administrateur
        return $task->getAuthor() === $user || in_array('ROLE_ADMIN', $user->getRoles());
    }

    private function canEdit(Task $task, UserInterface $user): bool
    {
        // L'utilisateur peut éditer la tâche s'il en est l'auteur ou un administrateur
        return $task->getAuthor() === $user || in_array('ROLE_ADMIN', $user->getRoles());
    }

    private function canDelete(UserInterface $user): bool
    {
        // Seul un administrateur peut supprimer la tâche
        return in_array('ROLE_ADMIN', $user->getRoles());
    }
}
