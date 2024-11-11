<?php

namespace App\Controller;

use App\DTO\TaskDTO;
use App\Repository\TaskRepository;
use App\Service\TaskService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

class TaskController extends AbstractController
{

    private TaskService $taskService;
    private TaskRepository $taskRepository;
    private SerializerInterface $serializer;

    public function __construct(
        TaskService $taskService,
        TaskRepository $taskRepository,
        SerializerInterface $serializer
    )
    {
        $this->taskService = $taskService;
        $this->taskRepository = $taskRepository;
        $this->serializer = $serializer;
    }

    /**
     * @Route("/task/tasksInProgress", name="tasks_in_progress", methods={"GET"})
     */
    public function tableTasksInProgress(): JsonResponse
    {
        $activeTasks = $this->taskRepository->findActiveTasks();
        return $this->json($activeTasks);
    }

    /**
     * @Route("/task/summaryOfTodaysTasks", name="summary_of_todays_tasks", methods={"GET"})
     */
    public function tableSummaryOfTodaysTasks(): JsonResponse
    {
        $tasksForToday = $this->taskRepository->findTasksForToday();

        $tasksGroupedByName = [];
        $totalTimeToday = 0;

        foreach ($tasksForToday as $task) {
            $taskName = $task->getName();
            $duration = $this->taskService->calculateDuration($task);

            if (!isset($tasksGroupedByName[$taskName])) {
                $tasksGroupedByName[$taskName] = [
                    'task' => $task,
                    'total_duration' => 0,
                ];
            }

            $tasksGroupedByName[$taskName]['total_duration'] += $duration;
            if ($duration !== null) {
                $totalTimeToday += $duration;
            }
        }

        return $this->json([
            'tasks_grouped_by_name' => $tasksGroupedByName,
            'total_time_today' => $totalTimeToday
        ]);
    }

    /**
     * @Route("/task/start", name="task_start", methods={"POST"})
     */
    public function start(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $taskDTO = $this->serializer->denormalize($data, TaskDTO::class);

        $this->taskService->startTask($taskDTO);

        return $this->json([
            'message' => 'Tarea iniciada correctamente',
            'status' => 200
        ]);
    }


    /**
     * @Route("/task/stop/{id}", name="task_stop", methods={"POST"})
     */
    public function stop(int $id): JsonResponse
    {
        $this->taskService->stopTask($id);

        return $this->json([
            'message' => 'Tarea detenida correctamente',
            'status' => 200
        ]);
    }

    /**
     * @Route("/task/{name}", name="task_by_name", methods={"GET"})
     */
    public function getTaskByName(string $name): JsonResponse
    {
        $task = $this->taskRepository->findOneBy(['name' => $name]);
        if (!$task) {
            return $this->json([
                'message' => 'Tarea no encontrada',
                'status' => 404
            ], 404);
        }


        return $this->json([
            'task' => $task,
            'status' => 200
        ]);
    }

    /**
     * @Route("/tasks/list", name="list_task", methods={"GET"})
     */
    public function getTaskList(): JsonResponse
    {
        $tasks = $this->taskRepository->findAll();
        if (!$tasks) {
            return $this->json([
                'message' => 'Tarea no encontrada',
                'status' => 404
            ], 404);
        }


        return $this->json([
            'tasks' => $tasks,
            'status' => 200
        ]);
    }
}
