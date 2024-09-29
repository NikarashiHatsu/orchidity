<?php

namespace App\Orchid\Screens;

use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Cropper;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Quill;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Fields\Upload;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;

class PostEditScreen extends Screen
{
    public $post;

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
        return $this->post->exists
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
                ->canSee(! $this->post->exists),

            Button::make('Edit post')
                ->icon('pen')
                ->method('createOrUpdate')
                ->canSee($this->post->exists),

            Button::make('Remove post')
                ->icon('trash')
                ->method('remove')
                ->canSee($this->post->exists),
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

                Cropper::make('post.hero')
                    ->title('Large web banner image, generally in the front and center')
                    ->width(1000)
                    ->height(500)
                    ->targetId(),

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

                Upload::make('post.attachments')
                    ->title('All files'),
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
            $this->post->fill($request->get('post'))->save();

            $this->post->attachments()->syncWithoutDetaching(
                $request->input('post.attachments', []),
            );
        } catch (\Throwable $th) {
            Alert::error('An error occurred: ' . $th->getMessage());

            return;
        }

        Alert::success($this->post->exists
            ? 'Post created'
            : 'Post updated');
    }

    public function remove()
    {
        try {
            $this->post->delete();
        } catch (\Throwable $th) {
            Alert::error('An error occurred: ' . $th->getMessage());

            return;
        }

        Alert::success('Post deleted');

        return redirect()->route('platform.post.list');
    }
}
