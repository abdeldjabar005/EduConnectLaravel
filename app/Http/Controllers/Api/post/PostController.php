<?php

namespace App\Http\Controllers\Api\post;

use App\Http\Controllers\Controller;
use App\Http\Requests\PostRequest;
use App\Http\Resources\PostResource;
use App\Models\Poll;
use App\Models\Post;
use App\Models\School;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Http\Request;
use Intervention\Image\Image;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::all();
        return response()->json($posts, 200);
    }

    public function show(Post $post)
    {
        $post->load('videos', 'pictures', 'poll', 'attachments');
        return new PostResource($post);
    }

    public function store(PostRequest $request)
    {
        $validatedData = $request->validated();

        if (isset($validatedData['class_id'])) {
            $classId = $validatedData['class_id'];
            $user = auth()->user();

            // Check if the user is a member of the class
            if (!$user->classes->contains($classId)) {
                return response()->json(['error' => 'User is not a member of this class'], 403);
            }
        }
        if (isset($validatedData['school_id'])) {
            $school = School::findOrFail($validatedData['school_id']);
            if ($school->admin_id != auth()->id()) {
                return response()->json(['error' => 'Only the admin of the school can post in the school'], 403);
            }
        }
        $post = Post::create([
            'user_id' => auth()->id(),
            'text' => $validatedData['text'],
            'type' => $validatedData['type'],
        ]);

        if (isset($validatedData['class_id'])) {
            $post->class_id = $validatedData['class_id'];
        }


        if (isset($validatedData['school_id'])) {
            $post->school_id = $validatedData['school_id'];
        }
        $post->save();


        if ($request->hasFile('picture')) {
            $files = $request->file('picture');
            foreach ($files as $file) {
                $manager = new ImageManager(new Driver());

                $image = $manager->read($file->getRealPath());

                $image->resize(700, 500);

                $filename = uniqid() . '.jpg';

                $path = 'posts/picture/' . $filename;
                $image->save(storage_path('app/public/' . $path), 75);

                $post->pictures()->create(['url' => $path]);
            }
        }



        if($request->type ==='text') {
            return response()->json(new PostResource($post), 201);
        }elseif ($request->type === 'poll') {
            // Create the poll
            $options = $request->options;
            $poll = new Poll([
                'question' => $request->question,
                'options' => json_encode($options), // Convert the options array to a JSON string
                'results' => json_encode(array_fill_keys($options, 0)), // Convert the results array to a JSON string
            ]);
            $post->poll()->save($poll);
        } else {
            foreach (['video', 'attachment'] as $type) {
                if ($request->has($type)) {
                    $files = $request->{$type};
                    if (is_array($files)) {
                        foreach ($files as $file) {
                            $path = $file->store('posts/' . $type, 'public');
                            $post->{$type . 's'}()->create(['url' => $path]);
                        }
                    } else {
                        $path = $files->store('posts/' . $type, 'public');
                        $post->{$type . 's'}()->create(['url' => $path]);
                    }
                }
            }
        }

        $post->load('videos', 'pictures', 'poll', 'attachments');

        return response()->json(new PostResource($post), 201);
    }

    public function update(Request $request, Post $post)
    {
        // Validate the request data
        $request->validate([
            'text' => 'sometimes|string',
            'type' => 'sometimes|string',
            'video' => 'sometimes|array',
            'video.*' => 'file',
            'picture' => 'sometimes|array',
            'picture.*' => 'file',
            'attachment' => 'sometimes|array',
            'attachment.*' => 'file',
            'poll' => 'sometimes|array',
            'poll.question' => 'sometimes|string',
            'poll.options' => 'sometimes|array',
        ]);

        // Check if the authenticated user is the owner of the post
        if (auth()->id() !== $post->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        if ($request->hasFile('picture')) {
            $files = $request->file('picture');
            foreach ($files as $file) {
                $manager = new ImageManager(new Driver());

                $image = $manager->read($file->getRealPath());

                $image->resize(700, 500);

                $filename = uniqid() . '.jpg';

                $path = 'posts/picture/' . $filename;
                $image->save(storage_path('app/public/' . $path), 75);

                $post->pictures()->create(['url' => $path]);
            }
        }

        // Update the post
        $post->update($request->only(['text', 'type']));

        // Update the video, picture, attachment, and poll if they are present in the request
        foreach (['video', 'attachment'] as $type) {
            if ($request->has($type)) {
                $files = $request->{$type};
                foreach ($files as $file) {
                    $path = $file->store('posts/' . $type, 'public');
                    $post->{$type . 's'}()->create(['url' => $path]); // Create a new file
                }
            }
        }

        if ($request->has('poll')) {
            $pollData = $request->poll;
            $poll = $post->poll;
            if ($poll) {
                $poll->update($pollData); // Update the existing poll
            } else {
                $post->poll()->create($pollData); // Create a new poll
            }
        }

        return response()->json(new PostResource($post), 200);
    }
    public function destroy(Post $post)
    {
        // Check if the authenticated user is the owner of the post
        if (auth()->id() !== $post->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Delete the post
        $post->delete();

        return response()->json(null, 204);
    }


    public function vote(Request $request, $id)
    {
        // Validate the request
        $request->validate([
            'option' => 'required|string',
        ]);

        // Retrieve the poll
        $poll = Poll::findOrFail($id);

        // Decode the results array
        $results = json_decode($poll->results, true);

        // Check if the option exists
        if (!array_key_exists($request->option, $results)) {
            return response()->json(['error' => 'Invalid option.'], 400);
        }

        // Check if the user has already voted
        $vote = Vote::where('user_id', auth()->id())->where('poll_id', $poll->id)->first();

        if ($vote) {
            // If the user has already voted, remove the vote
            $results[$vote->option]--;
            $vote->delete();
        } else {
            // If the user hasn't voted, add the vote
            $results[$request->option]++;
            Vote::create([
                'user_id' => auth()->id(),
                'poll_id' => $poll->id,
                'option' => $request->option,
            ]);
        }

        // Encode the results array and save it back to the database
        $poll->results = json_encode($results);
        $poll->save();

        // Return a success response
        return $vote ? response()->json(['success' => 'Vote revoked.'], 200) : response()->json(['success' => 'Vote counted.'], 200);
    }

    public function postsByClass($classId)
    {
        // Retrieve the posts that belong to the specified class
        $posts = Post::where('class_id', $classId)->paginate(6);

        $posts->load('videos', 'pictures', 'poll', 'attachments');

        // Return the posts as a JSON response
        return PostResource::collection($posts);
    }

    // posts from all user classes
    public function postsByUserClasses(Request $request)
    {
        // Retrieve the classes the user is in
        $userClasses = $request->user()->classes;

        // Check if the user has classes
        if ($userClasses === null) {
            return response()->json(['error' => 'User has no classes.'], 400);
        }

        // Retrieve the posts that belong to the user's classes
        $posts = Post::whereIn('class_id', $userClasses->pluck('id'))->paginate(6);

        // Eager load the relationships
        $posts->load('videos', 'pictures', 'poll', 'attachments');

        // Return the posts as a JSON response
        return PostResource::collection($posts);
    }
    //posts posted by specific school
    public function postsBySchool(Request $request, $schoolId)
    {
        // Retrieve the user
        $user = $request->user();

        // Retrieve the school
        $school = School::findOrFail($schoolId);

        // Check if the user is part of the school
        if (!$user->schools->contains($school)) {
            return response()->json(['error' => 'User is not part of this school.'], 403);
        }
        // Retrieve the posts that belong to the school
        $posts = $school->posts()->paginate(10); // 10 is the number of items per page

        // Eager load the relationships
        $posts->load('videos', 'pictures', 'poll', 'attachments');

        // Return the posts as a JSON response
        return PostResource::collection($posts);
    }

//all school posts that admin receives
    public function postsBySchoolAdmin(Request $request)
    {
        // Retrieve the admin
        $admin = $request->user();

        // Check if the user is an admin
        if ($admin->role != 'admin') {
            return response()->json(['error' => 'Only admins can view all school posts'], 403);
        }

        // Retrieve the school that the admin administers
        $school = School::where('admin_id', $admin->id)->first();

        // If the admin does not administer any school
        if (!$school) {
            return response()->json(['error' => 'Admin does not administer any school'], 404);
        }
        // Retrieve the posts that belong to the school's classes
        $posts = Post::whereHas('class', function ($query) use ($school) {
            $query->where('school_id', $school->id);
        })->paginate(10); // 10 is the number of items per page

        // Eager load the relationships
        $posts->load('videos', 'pictures', 'poll', 'attachments');

        // Return the posts as a JSON response
        return PostResource::collection($posts);
    }

    public function toggleSave(Post $post)
    {
        $user = auth()->user();

        if ($user->savedPosts()->where('post_id', $post->id)->exists()) {
            $user->savedPosts()->detach($post->id);
            return response()->json(['message' => 'Post unsaved '], 204);

        } else {
            $user->savedPosts()->attach($post->id);
            return response()->json(['message' => 'Post saved successfully'], 200);

        }

    }
}
