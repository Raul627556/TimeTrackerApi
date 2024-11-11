<?php

namespace App\Service;

use App\DTO\TaskDTO;
use App\Entity\Task;
use App\Repository\TaskRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;

class TaskService
{
    /** @var TaskRepository $taskRepository */
    private TaskRepository $taskRepository;

    /** @var EntityManagerInterface $entityManager */
    private EntityManagerInterface $entityManager;

    /**
     * @param TaskRepository $taskRepository
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        TaskRepository $taskRepository,
        EntityManagerInterface $entityManager
    )
    {
        $this->taskRepository = $taskRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * @param TaskDTO $taskDTO
     * @return void
     */
    public function startTask(TaskDTO $taskDTO): void
    {
        $task = (new Task());
        $task->setName($taskDTO->getName());

        if ($taskDTO->getStartTime() !== null) {
            $task->setStartTime($taskDTO->getStartTime());
        } else {
            $task->setStartTime(new DateTime());
        }
        if ($taskDTO->getEndTime() !== null) {
            $task->setEndTime($taskDTO->getEndTime());
        }

        $this->entityManager->persist($task);
        $this->entityManager->flush();

    }

    /**
     * @param int $taskId
     * @return void
     */
    public function stopTask(int $taskId): void
    {
        $task = $this->taskRepository->find($taskId);
        if ($task !== null) {
            $task->setEndTime(new DateTime());
            $this->entityManager->flush();
        }
    }

    /**
     * @param Task $task
     * @return int|null
     */
    public function calculateDuration(Task $task): ?int
    {
        $startTime = $task->getStartTime();
        $endTime = $task->getEndTime();

        if ($startTime !== null && $endTime !== null) {
            return $endTime->getTimestamp() - $startTime->getTimestamp();
        }

        return null;
    }
}
