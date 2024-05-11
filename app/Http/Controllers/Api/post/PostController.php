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
use Illuminate\Support\Facades\Cache;
use Intervention\Image\Image;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class PostController extends Controller
{
    public function index()
    {

        $posts = Cache::tags(['posts'])->remember('posts', 60, function () {
            return Post::all();
        });
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
            Cache::tags(['posts'])->forget("postsByClass_{$validatedData['class_id']}");

        }


        if (isset($validatedData['school_id'])) {
            $post->school_id = $validatedData['school_id'];
            Cache::tags(['posts'])->forget("postsBySchool_{$validatedData['school_id']}");

        }
        $adminId = auth()->id();
        Cache::tags(['posts'])->forget("postsBySchoolAdmin_{$adminId}");
        Cache::tags(['posts'])->forget("explorePosts_user_{$adminId}");

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
                            $title = $file->getClientOriginalName();

                            $post->{$type . 's'}()->create(['url' => $path,
                                'name' => htmlentities(trim($title), ENT_QUOTES, 'UTF-8'),
                            ]);
                        }
                    } else {
                        $path = $files->store('posts/' . $type, 'public');
                        $post->{$type . 's'}()->create(['url' => $path]);
                    }
                }
            }
        }
//        Cache::forget('posts');
        Cache::tags(['posts'])->flush();

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
                $title = $file->getClientOriginalName();
                $post->{$type . 's'}()->create(['url' => $path, 'name' => htmlentities(trim($title), ENT_QUOTES, 'UTF-8')]); // Create a new file with name
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
    Cache::tags(['posts'])->flush();

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
        Cache::tags(['posts'])->flush();

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
        Cache::tags(['posts'])->flush();

        // Encode the results array and save it back to the database
        $poll->results = json_encode($results);
        $poll->save();

        // Return a success response
        return $vote ? response()->json(['success' => 'Vote revoked.'], 200) : response()->json(['success' => 'Vote counted.'], 200);
    }public function postsByClass($classId, Request $request)
{
    $user = $request->user();

    // Check if the user is part of the class
    if (!$user->classes->contains($classId)) {
        return response()->json(['error' => 'User is not part of this class'], 403);
    }

    $pageNumber = $request->get('page', 1);
    $lastPost = Post::where('class_id', $classId)->latest()->first();
    $lastPostUpdate = $lastPost ? $lastPost->updated_at : now();

    $posts = Cache::tags(['posts'])->remember("postsByClass_{$classId}_{$lastPostUpdate}_page_{$pageNumber}", 60, function () use ($classId) {
        return Post::where('class_id', $classId)->paginate(6);
    });

    $posts->load('videos', 'pictures', 'poll', 'attachments');

    // Return the posts as a JSON response
    return PostResource::collection($posts);
}

public function postsBySchool(Request $request, $schoolId)
{
    $user = $request->user();

    // Check if the user is part of the school
    if (!$user->schools->contains($schoolId)) {
        return response()->json(['error' => 'User is not part of this school'], 403);
    }

    $pageNumber = $request->get('page', 1);
    $lastPost = Post::where('school_id', $schoolId)->latest()->first();
    $lastPostUpdate = $lastPost ? $lastPost->updated_at : now();

    $posts = Cache::tags(['posts'])->remember("postsBySchool_{$schoolId}_{$lastPostUpdate}_page_{$pageNumber}", 60, function () use ($schoolId) {
        return Post::where('school_id', $schoolId)->paginate(6);
    });

    $posts->load('videos', 'pictures', 'poll', 'attachments');

    // Return the posts as a JSON response
    return PostResource::collection($posts);
}
    // posts from all user classes
    public function postsByUserClasses(Request $request)
    {
        $pageNumber = $request->get('page', 1);

        // Retrieve the classes the user is in
        $userClasses = $request->user()->classes;

        // Check if the user has classes
        if ($userClasses === null) {
            return response()->json(['error' => 'User has no classes.'], 400);
        }

        $userId = $request->user()->id;


        $posts = Cache::tags(['posts'])->remember("postsByUserClasses_{$userId}_page_{$pageNumber}", 60, function () use ($request) {
            $userClasses = $request->user()->classes;
            return Post::whereIn('class_id', $userClasses->pluck('id'))->paginate(6);
        });
        // Eager load the relationships
        $posts->load('videos', 'pictures', 'poll', 'attachments');

        // Return the posts as a JSON response
        return PostResource::collection($posts);
    }
public function postsBySchoolAdmin(Request $request)
{
    $pageNumber = $request->get('page', 1);

    $adminId = $request->user()->id;

    $posts = Cache::tags(['posts'])->remember("postsBySchoolAdmin_{$adminId}_page_{$pageNumber}", 60, function () use ($request) {
        $admin = $request->user();
        if ($admin->role != 'admin') {
            return response()->json(['error' => 'Only admins can view all school posts'], 403);
        }
        $school = School::where('admin_id', $admin->id)->first();
        if (!$school) {
            return response()->json(['error' => 'Admin does not administer any school'], 404);
        }
        $postsQuery = Post::whereHas('class', function ($query) use ($school) {
            $query->where('school_id', $school->id);
        });

        if ($postsQuery->exists()) {
            return $postsQuery->paginate(10);
        } else {
            return collect();
        }
    });

    if ($posts instanceof \Illuminate\Http\JsonResponse) {
        return $posts;
    } else {
        return PostResource::collection($posts);
    }
}

    public function toggleSave(Post $post)
    {
        $user = auth()->user();

        if ($user->savedPosts()->where('post_id', $post->id)->exists()) {
            $user->savedPosts()->detach($post->id);
            Cache::tags(['posts'])->flush();

            return response()->json(['message' => 'Post unsaved '], 204);

        } else {
            $user->savedPosts()->attach($post->id);
            Cache::tags(['posts'])->flush();

            return response()->json(['message' => 'Post saved successfully'], 200);

        }

    }
 public function explorePosts(Request $request)
{
    $user = $request->user();
    $pageNumber = $request->get('page', 1);

    $userSchools = $user->schools->pluck('id');
    $userClasses = $user->classes->pluck('id');

    // Get the latest post update time
    $lastPostUpdate = Post::where(function ($query) use ($userSchools, $userClasses) {
        $query->whereIn('school_id', $userSchools)
              ->orWhereIn('class_id', $userClasses);
    })->latest()->first()->updated_at ?? now();

    $posts = Cache::tags(['posts'])->remember("explorePosts_user_{$user->id}_{$lastPostUpdate}_page_{$pageNumber}", 60, function () use ($userSchools, $userClasses) {
        return Post::where(function ($query) use ($userSchools, $userClasses) {
            $query->whereIn('school_id', $userSchools)
                  ->orWhereIn('class_id', $userClasses);
        })->paginate(6);
    });

    // Eager load the relationships
    $posts->load('videos', 'pictures', 'poll', 'attachments');

    // Return the posts as a JSON response
    return PostResource::collection($posts);
}
public function postsByAdminSchool(Request $request)
{
    $admin = $request->user();

    // Check if the user is an admin
    if ($admin->role != 'admin') {
        return response()->json(['error' => 'Only admins can view all school posts'], 403);
    }

    // Retrieve the school administered by the user
    $school = School::where('admin_id', $admin->id)->first();

    if (!$school) {
        return response()->json(['error' => 'Admin does not administer any school'], 404);
    }

    // Retrieve all posts from the classes of the school and the school itself
    $posts = Post::where('school_id', $school->id)
        ->orWhereHas('class', function ($query) use ($school) {
            $query->where('school_id', $school->id);
        })->paginate(10);



    // Eager load the relationships
//    $posts->load('videos', 'pictures', 'poll', 'attachments');

    // Return the posts as a JSON response
    return PostResource::collection($posts);
}
}
