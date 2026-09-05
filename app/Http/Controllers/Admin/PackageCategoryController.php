<?php

namespace App\Http\Controllers\Admin;

use App\Models\Country;
use App\Models\PackageCategory;
use App\Traits\HandlesTranslatedFields;
use App\Traits\UploadFileTrait;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PackageCategoryController extends Controller
{
    use HandlesTranslatedFields, UploadFileTrait;

    public function index(Request $request): View
    {
        $search = trim((string) $request->input('search', $request->input('q', '')));

        $packageCategories = PackageCategory::with(['country', 'parent'])
            ->withCount(['packages', 'children'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $this->applyTranslatedSearch($query, ['name', 'description', 'seo_title', 'seo_description'], $search);
                    $query->orWhere('slug', 'like', '%'.$search.'%');
                });
            })
            ->when($request->input('status') === 'active', fn ($query) => $query->where('is_active', true))
            ->when($request->input('status') === 'inactive', fn ($query) => $query->where('is_active', false))
            ->orderBy('sort_order')
            ->latest('id')
            ->paginate($this->perPage($request))
            ->withQueryString();

        return $this->view('admin.package-categories.index', [
            'categories' => $packageCategories,
            'statistics' => [
                'total' => PackageCategory::count(),
                'active' => PackageCategory::where('is_active', true)->count(),
                'inactive' => PackageCategory::where('is_active', false)->count(),
                'featured' => PackageCategory::where('is_featured', true)->count(),
            ],
        ]);
    }

    public function create(): View
    {
        $countries = Country::where('is_active', true)->orderBy('sort_order')->get();
        $parents = PackageCategory::where('is_active', true)->orderBy('sort_order')->get();

        return $this->view('admin.package-categories.create', compact('countries', 'parents'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);
        unset($data['remove_image']);

        $data = $this->translateModelFields($data, [
            'name',
            'description',
            'seo_title',
            'seo_description',
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = $this->uploadImage('package-categories', $request->file('image'));
        }

        $data['is_active'] = $request->boolean('is_active');
        $data['is_featured'] = $request->boolean('is_featured');
        $data['sort_order'] = $data['sort_order'] ?? 0;

        PackageCategory::create($data);

        return $this->success('admin.package-categories.index', 'تم إضافة تصنيف الباقات بنجاح.');
    }

    public function show(PackageCategory $packageCategory): View
    {
        $category = $packageCategory->load(['country', 'parent'])->loadCount(['packages', 'children']);

        return $this->view('admin.package-categories.show', compact('category'));
    }

    public function edit(PackageCategory $packageCategory): View
    {
        $category = $packageCategory;
        $excludedParentIds = array_merge([$category->id], $this->descendantIds($category));
        $countries = Country::query()
            ->where(function ($query) use ($category) {
                $query->where('is_active', true)
                    ->when($category->country_id, fn ($query) => $query->orWhereKey($category->country_id));
            })
            ->orderBy('sort_order')
            ->get();
        $parents = PackageCategory::whereNotIn('id', $excludedParentIds)
            ->where(function ($query) use ($category) {
                $query->where('is_active', true)
                    ->when($category->parent_id, fn ($query) => $query->orWhereKey($category->parent_id));
            })
            ->orderBy('sort_order')
            ->get();

        return $this->view('admin.package-categories.edit', compact('category', 'countries', 'parents'));
    }

    public function update(Request $request, PackageCategory $packageCategory): RedirectResponse
    {
        $data = $this->validatedData($request, $packageCategory);
        unset($data['remove_image']);

        $data = $this->translateModelFields($data, [
            'name',
            'description',
            'seo_title',
            'seo_description',
        ]);

        $oldImage = $packageCategory->image;

        if ($request->hasFile('image')) {
            $data['image'] = $this->uploadImage('package-categories', $request->file('image'));
        } elseif ($request->boolean('remove_image')) {
            $data['image'] = null;
        }

        $data['is_active'] = $request->boolean('is_active');
        $data['is_featured'] = $request->boolean('is_featured');
        $data['sort_order'] = $data['sort_order'] ?? 0;

        $packageCategory->update($data);

        if ($oldImage && array_key_exists('image', $data) && $oldImage !== $data['image']) {
            $this->deletePublicFile($oldImage);
        }

        return $this->success('admin.package-categories.index', 'تم تحديث تصنيف الباقات بنجاح.');
    }

    public function destroy(PackageCategory $packageCategory): RedirectResponse
    {
        $image = $packageCategory->image;

        DB::transaction(function () use ($packageCategory) {
            $packageCategory->children()->update(['parent_id' => null]);
            $packageCategory->packages()->update(['category_id' => null]);
            $packageCategory->faqs()->update(['category_id' => null]);
            $packageCategory->translations()->delete();
            $packageCategory->seoMeta()->delete();
            $packageCategory->delete();
        });

        $this->deletePublicFile($image);

        return $this->success('admin.package-categories.index', 'تم حذف التصنيف بنجاح دون حذف الباقات المرتبطة.');
    }

    private function validatedData(Request $request, ?PackageCategory $category = null): array
    {
        $parentRule = ['nullable', 'integer', 'exists:package_categories,id'];

        if ($category) {
            $parentRule[] = Rule::notIn(array_merge([$category->id], $this->descendantIds($category)));
        }

        $maxDaysRules = ['nullable', 'integer', 'min:0'];

        if ($request->filled('min_days')) {
            $maxDaysRules[] = 'gte:min_days';
        }

        $uniqueSlugRule = Rule::unique('package_categories', 'slug');

        if ($category) {
            $uniqueSlugRule->ignore($category);
        }

        return $request->validate([
            'parent_id' => $parentRule,
            'country_id' => ['nullable', 'integer', 'exists:countries,id'],
            'slug' => ['required', 'string', 'max:190', $uniqueSlugRule],
            'name' => ['required', 'string', 'max:190'],
            'description' => ['nullable', 'string'],
            'category_type' => ['required', Rule::in(array_keys(PackageCategory::TYPES))],
            'icon' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_image' => ['nullable', 'boolean'],
            'min_days' => ['nullable', 'integer', 'min:0'],
            'max_days' => $maxDaysRules,
            'price_from' => ['nullable', 'numeric', 'min:0'],
            'is_featured' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string'],
        ], [
            'parent_id.not_in' => 'لا يمكن اختيار التصنيف نفسه أو أحد التصنيفات الفرعية كتصنيف أب.',
            'max_days.gte' => 'يجب أن يكون الحد الأقصى للأيام أكبر من أو يساوي الحد الأدنى.',
        ]);
    }

    private function descendantIds(PackageCategory $category): array
    {
        $descendantIds = [];
        $parentIds = [$category->id];

        while ($parentIds !== []) {
            $parentIds = PackageCategory::whereIn('parent_id', $parentIds)
                ->whereNotIn('id', $descendantIds)
                ->pluck('id')
                ->all();
            $descendantIds = array_values(array_unique(array_merge($descendantIds, $parentIds)));
        }

        return $descendantIds;
    }
}
