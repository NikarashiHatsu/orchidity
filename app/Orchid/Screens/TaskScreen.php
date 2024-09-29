<?php

namespace App\Orchid\Screens;

use App\Models\Task;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class TaskScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [
            'tasks' => Task::query()
                ->latest()
                ->get(),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Tasks';
    }

    public function description(): ?string
    {
        return "All of your tasks are all here.";
    }

    public function create(Request $request)
    {
        $request->validate([
            'task.name' => ['required', 'max:255'],
        ]);

        try {
            $task = new Task;
            $task->name = $request->input('task.name');
            $task->save();
        } catch (\Throwable $th) {
            //throw $th;
        }
    }

    public function complete(Request $request)
    {
        $request->validate([
            'taskId' => ['required', 'exists:tasks,id'],
        ]);

        try {
            $task = Task::find($request->taskId);
            $task->active = ! $task->active;
            $task->save();
        } catch (\Throwable $th) {
            //throw $th;
        }
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            ModalToggle::make('Add Task')
                ->modal('taskModal')
                ->method('create')
                ->icon('plus'),
        ];
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {
        return [
            Layout::table('tasks', [
                TD::make('name'),

                TD::make('status')
                    ->render(fn (Task $task) => $task->active
                        ? '<i class="text-success">●</i> Done'
                        : '<i class="text-danger">●</i> Incomplete'
                    ),

                TD::make('Actions')
                    ->alignRight()
                    ->render(function (Task $task) {
                        $toggleCompleteButtonLabel = $task->active
                            ? 'Mark as Incomplete'
                            : 'Mark as Complete';

                        $deleteButton = Button::make('Delete Task')
                            ->confirm('After deleting, the task will be gone forever.')
                            ->method('delete', ['task' => $task->id]);

                        $toggleCompleteButton = Button::make($toggleCompleteButtonLabel)
                            ->confirm('Complete this task?')
                            ->method('complete', ['taskId' => $task->id]);

                        return <<<HTML
                            <div class="flex justify-end items-center">
                                {$toggleCompleteButton}
                                {$deleteButton}
                            </div>
                        HTML;
                    }),
            ]),

            Layout::modal('taskModal', Layout::rows([
                    Input::make('task.name')
                        ->title('Name')
                        ->placeholder('Enter task name')
                        ->help('The name of the task to be created'),
                ]))
                ->title('Create Task')
                ->applyButton('Add Task')
                ->closeButton('Close'),
        ];
    }
}
