<?php

namespace App\Orchid\Screens;

use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Quill;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;

class PostEditScreen extends Screen
{
    public ?Post $post = null;

    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(Post $post): iterable
    {
        return [
            'post' => $post,
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return ! empty($this->post)
            ? 'Edit post'
            : 'Create a new post';
    }

    public function description(): ?string
    {
        return "Blog posts";
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            Button::make('Create post')
                ->icon('plus')
                ->method('createOrUpdate')
                ->canSee(empty($this->post)),

            Button::make('Edit post')
                ->icon('pen')
                ->method('createOrUpdate')
                ->canSee(! empty($this->post)),

            Button::make('Remove post')
                ->icon('trash')
                ->method('remove')
                ->canSee(! empty($this->post)),
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
            Layout::rows([
                Input::make('post.title')
                    ->title('Title')
                    ->placeholder('Attractive but mysterious title')
                    ->help('Specify a short descriptive list for this post.'),

                Input::make('post.description')
                    ->title('Description')
                    ->rows(3)
                    ->maxlength(200)
                    ->placeholder('Brief description for preview.'),

                Relation::make('post.author_id')
                    ->title('Author')
                    ->fromModel(User::class, 'name'),

                Quill::make('post.body')
                    ->title('Main text'),
            ]),
        ];
    }

    public function createOrUpdate(Request $request)
    {
        $request->validate([
            'post.title' => ['required', 'string', 'max:60'],
            'post.description' => ['required', 'string', 'max:200'],
            'post.author_id' => ['required', 'exists:users,id'],
            'post.body' => ['required', 'string'],
        ]);

        try {
            empty($this->post)
                ? Post::create($request->get('post'))
                : $this->post->update($request->get('post'));
        } catch (\Throwable $th) {
            Alert::error('An error occurred: ' . $th->getMessage());

            return;
        }

        Alert::success(empty($this->post)
            ? 'Post created'
            : 'Post updated');
    }

    public function remove()
    {
        try {
            $this->post?->delete();
        } catch (\Throwable $th) {
            Alert::error('An error occurred: ' . $th->getMessage());

            return;
        }

        Alert::success('Post deleted');

        return redirect()->route('platform.post.list');
    }
}
