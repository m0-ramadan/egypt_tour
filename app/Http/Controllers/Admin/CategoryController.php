<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Language;
use App\Traits\HasAiTranslation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\ImageOptimizer\OptimizerChainFactory;

class CategoryController extends Controller
{
    /**
     * قائمة اللغات المدعومة
     */
    protected $supportedLanguages;
    use HasAiTranslation;
    public function __construct()
    {
        $this->supportedLanguages = Language::all();
    }

    /**
     * عرض قائمة الأقسام.
     */
    public function index(Request $request)
    {
        try {
            $query = Category::with(['parent', 'children'])
                ->withCount(['products', 'children']);

            // فلترة حسب البحث
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name->ar', 'like', "%{$search}%")
                        ->orWhere('description->ar', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%");
                });
            }

            // فلترة حسب القسم الرئيسي
            if ($request->filled('parent_id')) {
                if ($request->parent_id === 'null') {
                    $query->whereNull('parent_id');
                } else {
                    $query->where('parent_id', $request->parent_id);
                }
            }

            // فلترة حسب الحالة
            if ($request->filled('status_id')) {
                $query->where('status_id', $request->status_id);
            }

            // الترتيب
            $query->orderBy('parent_id')
                ->orderBy('order');

            $categories = $query->paginate(20)->withQueryString();

            $parentCategories = Category::whereNull('parent_id')
                ->orderBy('order')
                ->get();

            return view('Admin.category.index', compact('categories', 'parentCategories'));
        } catch (\Exception $e) {
            Log::error('خطأ في عرض الأقسام: ' . $e->getMessage());
            return back()->with('error', 'حدث خطأ أثناء تحميل الأقسام');
        }
    }

    /**
     * عرض نموذج إضافة قسم جديد.
     */
    public function create(Request $request)
    {
        $parentCategories = Category::whereNull('parent_id')
            ->orderBy('order')
            ->withCount('children')
            ->get();

        $recentCategories = Category::latest()
            ->take(5)
            ->get();

        $languages = $this->supportedLanguages;

        $parentId = $request->get('parent_id');
        $parentCategory = null;

        if ($parentId) {
            $parentCategory = Category::find($parentId);
        }

        return view('Admin.category.create', compact(
            'parentCategories',
            'recentCategories',
            'parentId',
            'parentCategory',
            'languages'
        ));
    }

    /**
     * حفظ قسم جديد.
     */
    /**
     * حفظ قسم جديد.
     */
    public function store(Request $request)
    {
        // التحقق من صحة البيانات
        $validated = $this->validateCategoryRequest($request);

        try {
            DB::beginTransaction();

            $category = new Category();

            // ✅ تعيين الترجمات - باستخدام البيانات القادمة كمصفوفات
            if ($request->has('name') && is_array($request->name)) {
                foreach ($request->name as $code => $value) {
                    if (!empty($value)) {
                        $category->setTranslation('name', $code, $value);
                    }
                }
            }

            // ✅ تعيين الوصف
            if ($request->has('description') && is_array($request->description)) {
                foreach ($request->description as $code => $value) {
                    if (!is_null($value) && $value !== '') {
                        $category->setTranslation('description', $code, $value);
                    }
                }
            }

            // ✅ تعيين meta_title
            if ($request->has('meta_title') && is_array($request->meta_title)) {
                foreach ($request->meta_title as $code => $value) {
                    if (!empty($value)) {
                        $category->setTranslation('meta_title', $code, $value);
                    }
                }
            }

            // ✅ تعيين meta_description
            if ($request->has('meta_description') && is_array($request->meta_description)) {
                foreach ($request->meta_description as $code => $value) {
                    if (!empty($value)) {
                        $category->setTranslation('meta_description', $code, $value);
                    }
                }
            }

            // ✅ تعيين meta_keywords
            if ($request->has('meta_keywords') && is_array($request->meta_keywords)) {
                foreach ($request->meta_keywords as $code => $value) {
                    if (!empty($value)) {
                        $category->setTranslation('meta_keywords', $code, $value);
                    }
                }
            }

            // التأكد من وجود الاسم العربي
            if (!$category->getTranslation('name', 'ar')) {
                throw new \Exception('الاسم العربي مطلوب');
            }

            // تعيين البيانات الأساسية
            $category->parent_id = $request->parent_id ?? null;
            $category->order = $request->order ?? 0;
            $category->appear_in_home = $request->has('appear_in_home') ? 1 : 0;
            $category->status_id = $request->status_id ?? 1;

            // تعيين slug إذا كان موجوداً، أو توليده تلقائياً
            if ($request->filled('slug')) {
                $category->slug = $request->slug;
            } else {
                $category->slug = $this->generateUniqueSlug($category->getTranslation('name', 'ar'));
            }

            // رفع الصور
            if ($request->hasFile('image')) {
                $category->image = $this->uploadImage($request->file('image'), 'categories');
            }

            if ($request->hasFile('sub_image')) {
                $category->sub_image = $this->uploadImage($request->file('sub_image'), 'categories/sub');
            }

            $category->save();

            DB::commit();

            $message = 'تم إضافة القسم بنجاح';

            // إذا كان المستخدم اختار "حفظ وإضافة جديد"
            if ($request->has('save_and_add_another')) {
                return redirect()->route('admin.categories.create')
                    ->with('success', $message . '، يمكنك إضافة قسم آخر');
            }

            return redirect()->route('admin.categories.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('خطأ في إضافة القسم: ' . $e->getMessage());

            return back()->with('error', 'حدث خطأ أثناء إضافة القسم: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * عرض تفاصيل قسم.
     */
    public function show(Category $category)
    {
        $category->load([
            'parent',
            'children' => function ($query) {
                $query->orderBy('order')
                    ->withCount('products');
            },
            'products' => function ($query) {
                $query->latest()->limit(10);
            }
        ]);

        $category->loadCount(['products', 'children']);

        return view('Admin.category.show', compact('category'));
    }

    /**
     * عرض نموذج تعديل قسم.
     */
    public function edit(Category $category)
    {
        $parentCategories = Category::whereNull('parent_id')
            ->where('id', '!=', $category->id)
            ->orderBy('order')
            ->get();

        $category->loadCount(['products', 'children']);
        $category->load(['children' => function ($query) {
            $query->orderBy('order');
        }]);

        $languages = $this->supportedLanguages;

        return view('Admin.category.edit', compact('category', 'parentCategories', 'languages'));
    }

    /**
     * تحديث قسم.
     */
    public function update(Request $request, Category $category)
    {
        // التحقق من صحة البيانات
        $validated = $this->validateCategoryRequest($request, $category->id);

        try {
            DB::beginTransaction();

            // ✅ تعيين الترجمات - باستخدام البيانات القادمة كمصفوفات
            if ($request->has('name') && is_array($request->name)) {
                foreach ($request->name as $code => $value) {
                    if (!empty($value)) {
                        $category->setTranslation('name', $code, $value);
                    }
                }
            }

            // ✅ تعيين الوصف
            if ($request->has('description') && is_array($request->description)) {
                foreach ($request->description as $code => $value) {
                    if (!is_null($value) && $value !== '') {
                        $category->setTranslation('description', $code, $value);
                    }
                }
            }

            // ✅ تعيين meta_title
            if ($request->has('meta_title') && is_array($request->meta_title)) {
                foreach ($request->meta_title as $code => $value) {
                    if (!empty($value)) {
                        $category->setTranslation('meta_title', $code, $value);
                    }
                }
            }

            // ✅ تعيين meta_description
            if ($request->has('meta_description') && is_array($request->meta_description)) {
                foreach ($request->meta_description as $code => $value) {
                    if (!empty($value)) {
                        $category->setTranslation('meta_description', $code, $value);
                    }
                }
            }

            // ✅ تعيين meta_keywords
            if ($request->has('meta_keywords') && is_array($request->meta_keywords)) {
                foreach ($request->meta_keywords as $code => $value) {
                    if (!empty($value)) {
                        $category->setTranslation('meta_keywords', $code, $value);
                    }
                }
            }

            // ✅ تعيين البيانات الأساسية
            $category->parent_id = $request->parent_id ?? null;
            $category->order = $request->order ?? 0;
            $category->appear_in_home = $request->has('appear_in_home') ? 1 : 0;

            $category->status_id = $request->status_id ?? 1;

            // ✅ تعيين slug إذا كان موجوداً
            if ($request->filled('slug')) {
                $category->slug = $request->slug;
            }

            // رفع الصور الجديدة
            if ($request->hasFile('image')) {
                $category->image = $this->uploadImage($request->file('image'), 'categories', $category->image);
            }

            if ($request->hasFile('sub_image')) {
                $category->sub_image = $this->uploadImage($request->file('sub_image'), 'categories/sub', $category->sub_image);
            }

            // حذف الصور
            if ($request->has('delete_image') && $request->delete_image) {
                $this->deleteImage($category->image);
                $category->image = null;
            }

            if ($request->has('delete_sub_image') && $request->delete_sub_image) {
                $this->deleteImage($category->sub_image);
                $category->sub_image = null;
            }

            // حفظ التغييرات
            $category->save();

            DB::commit();

            return redirect()->route('admin.categories.index')
                ->with('success', 'تم تحديث القسم بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('خطأ في تحديث القسم: ' . $e->getMessage());

            return back()->with('error', 'حدث خطأ أثناء تحديث القسم: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * تحديث دالة التحقق لتستقبل المصفوفات بشكل صحيح
     */
    private function validateCategoryRequest(Request $request, $categoryId = null)
    {
        $rules = [
            'name' => 'required|array',
            'name.ar' => 'required|string|max:255',
            'name.*' => 'nullable|string|max:255',
            'description' => 'nullable|array',
            'description.*' => 'nullable|string',
            'meta_title' => 'nullable|array',
            'meta_title.*' => 'nullable|string|max:255',
            'meta_description' => 'nullable|array',
            'meta_description.*' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|array',
            'meta_keywords.*' => 'nullable|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
            'order' => 'nullable|integer|min:0',
            'status_id' => 'required|integer|in:1,2',
            'slug' => 'nullable|string|max:255|unique:categories,slug,' . $categoryId,
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'sub_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'delete_image' => 'nullable|boolean',
            'delete_sub_image' => 'nullable|boolean',
        ];

        return $request->validate($rules);
    }
    /**
     * التحقق من صحة البيانات مع دعم جميع اللغات
     */

    /**
     * حذف قسم.
     */
    public function destroy(Category $category)
    {
        try {
            // التحقق من وجود منتجات
            if ($category->products()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'لا يمكن حذف القسم لأنه يحتوي على منتجات.'
                ], 400);
            }

            // التحقق من وجود أقسام فرعية
            if ($category->children()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'لا يمكن حذف القسم لأنه يحتوي على أقسام فرعية.'
                ], 400);
            }

            // حذف الصور
            $this->deleteImage($category->image);
            $this->deleteImage($category->sub_image);

            $category->delete();

            return response()->json([
                'success' => true,
                'message' => 'تم حذف القسم بنجاح.'
            ]);
        } catch (\Exception $e) {
            Log::error('خطأ في حذف القسم: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء حذف القسم: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * التحقق من صحة البيانات.
     */
    // private function validateCategoryRequest(Request $request, $categoryId = null)
    // {
    //     $rules = [
    //         'name_ar' => 'required|string|max:255',
    //         'parent_id' => 'nullable|exists:categories,id',
    //         'order' => 'nullable|integer|min:0',
    //         'status_id' => 'required|integer|in:1,2',
    //         'slug' => 'nullable|string|max:255|unique:categories,slug,' . $categoryId,
    //         'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
    //         'sub_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
    //         'delete_image' => 'nullable|boolean',
    //         'delete_sub_image' => 'nullable|boolean',
    //     ];

    //     // قواعد التحقق للغات الأخرى
    //     foreach ($this->supportedLanguages as $language) {
    //         $code = $language->code;
    //         if ($code != 'ar') {
    //             $rules["name_{$code}"] = 'nullable|string|max:255';
    //             $rules["description_{$code}"] = 'nullable|string';
    //             $rules["meta_title_{$code}"] = 'nullable|string|max:255';
    //             $rules["meta_description_{$code}"] = 'nullable|string|max:500';
    //             $rules["meta_keywords_{$code}"] = 'nullable|string|max:255';
    //         }
    //     }

    //     // الوصف والـ SEO للعربية
    //     $rules['description_ar'] = 'nullable|string';
    //     $rules['meta_title_ar'] = 'nullable|string|max:255';
    //     $rules['meta_description_ar'] = 'nullable|string|max:500';
    //     $rules['meta_keywords_ar'] = 'nullable|string|max:255';

    //     return $request->validate($rules);
    // }

    /**
     * رفع صورة.
     */
    private function uploadImage($file, $directory, $oldImage = null)
    {
        // حذف الصورة القديمة
        if ($oldImage) {
            $this->deleteImage($oldImage);
        }

        // إنشاء اسم فريد
        $filename = Str::uuid() . '.webp';
        $path = $directory . '/' . $filename;

        // حفظ الصورة
        Storage::disk('public')->put($path, file_get_contents($file));

        // تحسين الصورة
        try {
            $optimizer = OptimizerChainFactory::create();
            $optimizer->optimize(Storage::disk('public')->path($path));
        } catch (\Exception $e) {
            Log::warning('فشل في تحسين الصورة: ' . $e->getMessage());
        }

        return $path;
    }

    /**
     * حذف صورة.
     */
    private function deleteImage($path)
    {
        if ($path && Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->delete($path);
        }
        return false;
    }

    /**
     * توليد رابط فريد من الاسم العربي
     */
    private function generateUniqueSlug($name, $excludeId = null)
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $count = 1;

        while (Category::where('slug', $slug)
            ->when($excludeId, function ($query) use ($excludeId) {
                $query->where('id', '!=', $excludeId);
            })
            ->exists()
        ) {
            $slug = $originalSlug . '-' . $count++;
        }

        return $slug;
    }

    /**
     * توليد Slug تلقائياً (AJAX).
     */
    public function generateSlug(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'category_id' => 'nullable|exists:categories,id'
        ]);

        $slug = Str::slug($request->name);
        $slug = $this->generateUniqueSlug($request->name, $request->category_id);

        return response()->json([
            'success' => true,
            'slug' => $slug
        ]);
    }

    /**
     * تحسين النص بالذكاء الاصطناعي.
     */
    /**
     * تحسين النص بالذكاء الاصطناعي.
     */
    public function aiEnhance(Request $request)
    {
        $request->validate([
            'text' => 'required|string',
            'type' => 'required|string|in:title,description,meta_title,meta_description,meta_keywords',
            'action' => 'required|string|in:enhance,complete,seo',
            'tone' => 'nullable|string',
            'style' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id'
        ]);

        try {
            // للاختبار، سنعيد نفس النص مع تحسين بسيط
            $enhancedText = $request->text;

            if ($request->action == 'enhance') {
                $enhancedText = '✨ ' . $request->text . ' (نص محسن)';
            } elseif ($request->action == 'complete') {
                $enhancedText = $request->text . '... هذا النص تم إكماله تلقائياً ليكون أكثر شمولاً وفائدة للزوار.';
            } elseif ($request->action == 'seo') {
                if ($request->type == 'meta_title') {
                    $enhancedText = $request->text . ' - أفضل العروض والأسعار';
                } elseif ($request->type == 'meta_description') {
                    $enhancedText = 'اكتشف ' . $request->text . ' في متجرنا. أفضل الأسعار والجودة العالية مع ضمان الرضا التام. تسوق الآن!';
                }
            }

            return response()->json([
                'success' => true,
                'enhanced_text' => $enhancedText
            ]);
        } catch (\Exception $e) {
            Log::error('خطأ في تحسين النص: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تحسين النص: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ترجمة النص بالذكاء الاصطناعي.
     */
    /**
     * ترجمة النص بالذكاء الاصطناعي.
     */
    public function aiTranslate(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'description' => 'nullable|string',
            'target_lang' => 'required|string|in:en,fr,de,es,it,tr,ur,fa', // أضف كل اللغات المدعومة
            'tone' => 'nullable|string',
            'style' => 'nullable|string'
        ]);

        try {
            // ✅ استخدام دالة الترجمة من الـ Trait
            $translatedName = $this->translateWithAI($request->name, $request->target_lang);

            $translatedDescription = null;
            if ($request->description && !empty(trim($request->description))) {
                $translatedDescription = $this->translateWithAI($request->description, $request->target_lang);
            }

            return response()->json([
                'success' => true,
                'translated' => [
                    'name' => $translatedName,
                    'description' => $translatedDescription
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('خطأ في الترجمة: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء الترجمة: ' . $e->getMessage()
            ], 500);
        }
    }
    private function getTranslation($text, $targetLang)
    {
        // للعرض فقط - استبدل هذا بـ API حقيقي

        // قائمة الترجمات التجريبية
        $translations = [
            'en' => [
                'إيباي' => 'eBay',
                'منتجات' => 'Products',
                'متميزة' => 'Premium',
                'مجموعة' => 'Collection',
            ],
            'fr' => [
                'إيباي' => 'eBay11',
                'منتجات' => 'Produits',
                'متميزة' => 'Premium',
                'مجموعة' => 'Collection',
            ],
        ];

        $translated = $text;
        if (isset($translations[$targetLang])) {
            foreach ($translations[$targetLang] as $ar => $trans) {
                $translated = str_replace($ar, $trans, $translated);
            }
        }

        return $translated;
    }
    /**
     * إنشاء SEO بالذكاء الاصطناعي.
     */
    public function aiGenerateSeo(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'description' => 'nullable|string',
            'tone' => 'nullable|string',
            'style' => 'nullable|string'
        ]);

        try {
            // توليد SEO تلقائي
            $metaTitle = $request->name . ' - أفضل الأسعار والجودة';

            $metaDescription = $request->description
                ? Str::limit($request->description, 150)
                : 'اكتشف ' . $request->name . ' في متجرنا. أفضل الأسعار والجودة العالية مع ضمان الرضا التام. تسوق الآن واستفد من العروض الحصرية!';

            $metaKeywords = str_replace(' ', ', ', $request->name) . ', تسوق, عروض, تخفيضات';

            return response()->json([
                'success' => true,
                'data' => [
                    'meta_title' => $metaTitle,
                    'meta_description' => $metaDescription,
                    'meta_keywords' => $metaKeywords
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('خطأ في إنشاء SEO: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إنشاء SEO: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * تحسين كامل للقسم.
     */
    /**
     * تحسين كامل للقسم.
     */
    public function aiEnhanceFull(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'tone' => 'nullable|string',
            'style' => 'nullable|string'
        ]);

        try {
            // للاختبار، سنعيد نفس البيانات مع تحسين بسيط
            $enhancedName = $request->name . ' (محسن)';
            $enhancedDescription = $request->description
                ? $request->description . ' - هذا وصف محسن بالذكاء الاصطناعي لجعل المحتوى أكثر جاذبية وفعالية.'
                : 'هذا وصف محسن بالذكاء الاصطناعي للقسم ' . $request->name;

            $enhancedSlug = Str::slug($request->name);
            $metaTitle = $request->name . ' - أفضل الأسعار والجودة';
            $metaDescription = $enhancedDescription
                ? Str::limit($enhancedDescription, 150)
                : 'اكتشف ' . $request->name . ' في متجرنا. أفضل الأسعار والجودة العالية مع ضمان الرضا التام.';
            $metaKeywords = str_replace(' ', ', ', $request->name);

            return response()->json([
                'success' => true,
                'data' => [
                    'name' => $enhancedName,
                    'description' => $enhancedDescription,
                    'slug' => $enhancedSlug,
                    'meta_title' => $metaTitle,
                    'meta_description' => $metaDescription,
                    'meta_keywords' => $metaKeywords
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('خطأ في تحسين القسم: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تحسين القسم: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * تبديل حالة القسم.
     */
    public function toggleStatus(Category $category)
    {
        try {
            $oldStatus = $category->status_id;
            $newStatus = $category->status_id == 1 ? 2 : 1;

            $category->status_id = $newStatus;
            $category->save();

            $statusText = $newStatus == 1 ? 'تفعيل' : 'تعطيل';

            return response()->json([
                'success' => true,
                'message' => "تم {$statusText} القسم بنجاح",
                'status_id' => $newStatus,
                'status_text' => $newStatus == 1 ? 'نشط' : 'غير نشط',
                'status_class' => $newStatus == 1 ? 'success' : 'danger'
            ]);
        } catch (\Exception $e) {
            Log::error('خطأ في تبديل حالة القسم: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تغيير حالة القسم'
            ], 500);
        }
    }

    /**
     * نسخ القسم.
     */
    public function duplicate(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        try {
            DB::beginTransaction();

            // نسخ القسم
            $newCategory = $category->replicate();

            // تعيين الاسم الجديد
            $newCategory->setTranslation('name', 'ar', $request->name);

            // توليد slug جديد
            $newCategory->slug = $this->generateUniqueSlug($request->name);

            // ترتيب جديد
            $newCategory->order = Category::max('order') + 1;
            $newCategory->created_at = now();
            $newCategory->updated_at = now();

            // نسخ الترجمات الأخرى
            foreach ($this->supportedLanguages as $language) {
                $code = $language->code;
                if ($code != 'ar') {
                    $oldName = $category->getTranslation('name', $code);
                    if ($oldName) {
                        $newCategory->setTranslation('name', $code, $oldName . ' (نسخة)');
                    }
                }
            }

            $newCategory->save();

            // نسخ الصورة الرئيسية
            if ($category->image) {
                $newImagePath = $this->duplicateImage($category->image, 'categories');
                $newCategory->image = $newImagePath;
            }

            // نسخ الصورة الفرعية
            if ($category->sub_image) {
                $newSubImagePath = $this->duplicateImage($category->sub_image, 'categories/sub');
                $newCategory->sub_image = $newSubImagePath;
            }

            $newCategory->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'تم نسخ القسم بنجاح',
                'data' => $newCategory
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('خطأ في نسخ القسم: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء نسخ القسم: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * نسخ صورة.
     */
    private function duplicateImage($imagePath, $folder)
    {
        if (!Storage::disk('public')->exists($imagePath)) {
            return null;
        }

        $extension = pathinfo($imagePath, PATHINFO_EXTENSION);
        $newFileName = Str::uuid() . '.' . $extension;
        $newPath = $folder . '/' . $newFileName;

        Storage::disk('public')->copy($imagePath, $newPath);

        return $newPath;
    }

    /**
     * تحديث ترتيب الأقسام.
     */
    public function updateOrder(Request $request)
    {
        $request->validate([
            'categories' => 'required|array',
            'categories.*.id' => 'required|exists:categories,id',
            'categories.*.order' => 'required|integer|min:0',
            'categories.*.parent_id' => 'nullable|exists:categories,id'
        ]);

        try {
            DB::beginTransaction();

            foreach ($request->categories as $item) {
                Category::where('id', $item['id'])
                    ->update([
                        'order' => $item['order'],
                        'parent_id' => $item['parent_id'] ?? null
                    ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'تم تحديث ترتيب الأقسام بنجاح'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('خطأ في تحديث ترتيب الأقسام: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تحديث ترتيب الأقسام'
            ], 500);
        }
    }

    /**
     * تصدير الأقسام.
     */
    public function export(Request $request)
    {
        try {
            $query = Category::with(['parent', 'children']);

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where('name->ar', 'like', "%{$search}%");
            }

            if ($request->filled('status_id')) {
                $query->where('status_id', $request->status_id);
            }

            $categories = $query->orderBy('parent_id')
                ->orderBy('order')
                ->get();

            $filename = 'categories_export_' . now()->format('Y-m-d_H-i-s') . '.csv';
            $handle = fopen('php://temp', 'w+');

            // إضافة BOM للغة العربية
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // العناوين
            fputcsv($handle, [
                'ID',
                'اسم القسم (عربي)',
                'الرابط',
                'الوصف (عربي)',
                'القسم الرئيسي',
                'الترتيب',
                'الحالة',
                'عدد المنتجات',
                'عدد الأقسام الفرعية',
                'تاريخ الإضافة'
            ]);

            // البيانات
            foreach ($categories as $category) {
                fputcsv($handle, [
                    $category->id,
                    $category->getTranslation('name', 'ar'),
                    $category->slug,
                    $category->getTranslation('description', 'ar'),
                    $category->parent ? $category->parent->getTranslation('name', 'ar') : 'قسم رئيسي',
                    $category->order,
                    $category->status_id == 1 ? 'نشط' : 'غير نشط',
                    $category->products_count ?? 0,
                    $category->children_count ?? 0,
                    $category->created_at->format('Y-m-d H:i:s')
                ]);
            }

            rewind($handle);
            $content = stream_get_contents($handle);
            fclose($handle);

            return response($content)
                ->header('Content-Type', 'text/csv; charset=utf-8')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
        } catch (\Exception $e) {
            Log::error('خطأ في تصدير الأقسام: ' . $e->getMessage());

            return back()->with('error', 'حدث خطأ أثناء تصدير الأقسام');
        }
    }

    /**
     * استدعاء خدمة الذكاء الاصطناعي (مثال - استبدل بالخدمة الفعلية).
     */
    private function callAIService($text, $action, $type)
    {
        // هنا يمكنك دمج OpenAI أو أي خدمة ذكاء اصطناعي أخرى
        // هذا مجرد مثال بسيط

        switch ($action) {
            case 'enhance':
                // تحسين النص
                return trim($text);

            case 'complete':
                // إكمال النص
                return $text . '... هذا هو النص المكمل.';

            case 'seo':
                // تحسين SEO
                if ($type == 'meta_title') {
                    return $text . ' - متجرنا';
                } elseif ($type == 'meta_description') {
                    return 'اكتشف ' . $text . ' في متجرنا. أفضل الأسعار والجودة العالية مع ضمان الرضا التام.';
                }
                return $text;

            default:
                return $text;
        }
    }

    /**
     * استدعاء خدمة الترجمة (مثال - استبدل بالخدمة الفعلية).
     */
    private function callTranslationService($text, $targetLang)
    {
        // هنا يمكنك دمج Google Translate API أو أي خدمة ترجمة أخرى
        // هذا مجرد مثال بسيط
        return $text . ' [' . $targetLang . ']';
    }

    /**
     * حذف مجموعة أقسام.
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'category_ids' => 'required|array',
            'category_ids.*' => 'exists:categories,id'
        ]);

        try {
            $deleted = 0;
            $skipped = [];

            foreach ($request->category_ids as $categoryId) {
                $category = Category::find($categoryId);

                if ($category->products()->exists()) {
                    $skipped[] = $category->getTranslation('name', 'ar') . ' (يحتوي على منتجات)';
                    continue;
                }

                if ($category->children()->exists()) {
                    $skipped[] = $category->getTranslation('name', 'ar') . ' (يحتوي على أقسام فرعية)';
                    continue;
                }

                $this->deleteImage($category->image);
                $this->deleteImage($category->sub_image);

                $category->delete();
                $deleted++;
            }

            $message = "تم حذف {$deleted} أقسام بنجاح";
            if (count($skipped) > 0) {
                $message .= "، وتخطي " . count($skipped) . " أقسام: " . implode('، ', $skipped);
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'deleted' => $deleted,
                'skipped' => $skipped
            ]);
        } catch (\Exception $e) {
            Log::error('خطأ في الحذف المتعدد: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء حذف الأقسام'
            ], 500);
        }
    }

    /**
     * تفعيل مجموعة أقسام.
     */
    public function bulkActivate(Request $request)
    {
        $request->validate([
            'category_ids' => 'required|array',
            'category_ids.*' => 'exists:categories,id'
        ]);

        try {
            $count = Category::whereIn('id', $request->category_ids)
                ->update(['status_id' => 1]);

            return response()->json([
                'success' => true,
                'message' => "تم تفعيل {$count} أقسام بنجاح",
                'count' => $count
            ]);
        } catch (\Exception $e) {
            Log::error('خطأ في التفعيل المتعدد: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تفعيل الأقسام'
            ], 500);
        }
    }

    /**
     * تعطيل مجموعة أقسام.
     */
    public function bulkDeactivate(Request $request)
    {
        $request->validate([
            'category_ids' => 'required|array',
            'category_ids.*' => 'exists:categories,id'
        ]);

        try {
            $count = Category::whereIn('id', $request->category_ids)
                ->update(['status_id' => 2]);

            return response()->json([
                'success' => true,
                'message' => "تم تعطيل {$count} أقسام بنجاح",
                'count' => $count
            ]);
        } catch (\Exception $e) {
            Log::error('خطأ في التعطيل المتعدد: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تعطيل الأقسام'
            ], 500);
        }
    }

    /**
     * نقل مجموعة أقسام.
     */
    public function bulkMove(Request $request)
    {
        $request->validate([
            'category_ids' => 'required|array',
            'category_ids.*' => 'exists:categories,id',
            'parent_id' => 'required|exists:categories,id'
        ]);

        try {
            // التحقق من عدم نقل قسم إلى نفسه
            if (in_array($request->parent_id, $request->category_ids)) {
                return response()->json([
                    'success' => false,
                    'message' => 'لا يمكن نقل القسم إلى نفسه'
                ], 400);
            }

            $count = Category::whereIn('id', $request->category_ids)
                ->where('id', '!=', $request->parent_id)
                ->update(['parent_id' => $request->parent_id]);

            $parentCategory = Category::find($request->parent_id);

            return response()->json([
                'success' => true,
                'message' => "تم نقل {$count} أقسام إلى {$parentCategory->getTranslation('name', 'ar')} بنجاح",
                'count' => $count
            ]);
        } catch (\Exception $e) {
            Log::error('خطأ في النقل المتعدد: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء نقل الأقسام'
            ], 500);
        }
    }

    /**
     * الحصول على شجرة الأقسام.
     */
    public function getTree()
    {
        try {
            $categories = Category::whereNull('parent_id')
                ->with(['children' => function ($query) {
                    $query->orderBy('order')
                        ->withCount('products', 'children');
                }])
                ->withCount('products', 'children')
                ->orderBy('order')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $categories
            ]);
        } catch (\Exception $e) {
            Log::error('خطأ في تحميل شجرة الأقسام: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'فشل في تحميل شجرة الأقسام'
            ], 500);
        }
    }
}
