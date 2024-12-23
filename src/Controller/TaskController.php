<?php

namespace App\Controller;

use App\Entity\Task;
use App\Form\TaskType;
use App\Repository\TaskRepository;
use App\Security\Voter\TaskVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TaskController extends AbstractController
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    #[Route('/tasks', name: 'task_index')]
    public function index(TaskRepository $taskRepository): Response
    {
        return $this->render('task/list.html.twig', [
            'tasks' => $taskRepository->findAll(),
        ]);
    }

    #[Route('/tasks/create', name: 'task_create')]
    public function create(Request $request): Response
    {
        $task = new Task();
        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $task->setCreatedAt(new \DateTimeImmutable());
            $task->setUpdatedAt(new \DateTimeImmutable());
            $task->setAuthor($this->getUser());
            $this->entityManager->persist($task);
            $this->entityManager->flush();

            return $this->redirectToRoute('task_index');
        }

        return $this->render('task/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/tasks/{id}/edit', name: 'task_edit')]
    public function edit(Task $task, Request $request): Response
    {
        if (!$this->isGranted(TaskVoter::EDIT, $task)) {
            $this->addFlash('error', 'Vous n\'avez pas les droits nécessaires pour modifier cette tâche.');
            return $this->redirectToRoute('task_index');
        }

        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $task->setUpdatedAt(new \DateTimeImmutable());
            $this->entityManager->flush();
            return $this->redirectToRoute('task_index');
        }

        return $this->render('task/edit.html.twig', [
            'form' => $form->createView(),
            'task' => $task,
        ]);
    }

    #[Route('/tasks/{id}/delete', name: 'task_delete')]
    public function delete(Task $task): Response
    {
        if (!$this->isGranted(TaskVoter::DELETE, $task)) {
            $this->addFlash('error', 'Vous n\'avez pas les droits nécessaires pour supprimer cette tâche.');
            return $this->redirectToRoute('task_index');
        }

        $this->entityManager->remove($task);
        $this->entityManager->flush();

        return $this->redirectToRoute('task_index');
    }

    #[Route('/task/{id}', name: 'task_view')]
    public function view(Task $task): Response
    {
        if (!$this->isGranted(TaskVoter::VIEW, $task)) {
            $this->addFlash('error', 'Vous n\'avez pas les droits nécessaires pour voir cette tâche.');
            return $this->redirectToRoute('task_index');
        }

        return $this->render('task/view.html.twig', [
            'task' => $task,
        ]);
    }
}
