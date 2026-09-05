<?php

namespace App\Observers;

use App\Models\Category;
use Illuminate\Support\Facades\Storage;
use Spatie\ImageOptimizer\OptimizerChainFactory;
class CategoryObserver
{
    /**
     * Handle the Category "created" event.
     */
  public function created(Category $category)
    {
        $this->optimizeCover($category);
    }

    public function updated(Category $category)
    {
        if ($category->isDirty('cover_image')) {
            $this->optimizeCover($category);
        }
    }

    protected function optimizeCover(Category $category): void
    {
        if (!$category->cover_image) return;

        $path = Storage::disk('public')->path($category->cover_image);

        if (!file_exists($path)) return;

        $optimizerChain = OptimizerChainFactory::create();
        $optimizerChain->optimize($path);
    }

    /**
     * Handle the Category "deleted" event.
     */
    public function deleted(Category $category): void
    {
        //
    }

    /**
     * Handle the Category "restored" event.
     */
    public function restored(Category $category): void
    {
        //
    }

    /**
     * Handle the Category "force deleted" event.
     */
    public function forceDeleted(Category $category): void
    {
        //
    }
}
