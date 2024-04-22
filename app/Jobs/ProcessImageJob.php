<?php

namespace App\Jobs;

use App\Models\Post;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Intervention\Image\ImageManager;

class ProcessImageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $file;
    protected $post;

    public function __construct($file, Post $post)
    {
        $this->file = $file;
        $this->post = $post;
    }

    public function handle()
    {
        $manager = new ImageManager(new Driver());

        $image = $manager->read($this->file->getRealPath());

        $image->resize(700, 500);

        $filename = uniqid() . '.jpg';

        $path = 'posts/picture/' . $filename;
        $image->save(storage_path('app/public/' . $path), 75);

        $this->post->pictures()->create(['url' => $path]);
    }
}
