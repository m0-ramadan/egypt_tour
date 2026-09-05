<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\TranslateContentUnitJob;
use App\Models\Package;
use App\Services\Translation\AiTranslationService;
use App\Services\Translation\DTOs\TranslationOptions;
use App\Services\Translation\DTOs\TranslationUnit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TranslationController extends Controller
{
    public function __construct(protected AiTranslationService $translationService) {}

    /**
     * Handle AJAX request to translate all missing content for a package.
     */
    public function translateMissing(Request $request): JsonResponse
    {
        $request->validate([
            'package_id' => 'required|exists:packages,id',
            'source_lang' => 'nullable|string|max:10',
            'async' => 'nullable|boolean',
        ]);

        $packageId = (int) $request->input('package_id');
        $sourceLang = $request->input('source_lang');
        $async = $request->boolean('async', false);

        $package = Package::findOrFail($packageId);

        if ($async) {
            TranslateContentUnitJob::dispatch($packageId, $sourceLang, true);
            return response()->json([
                'success' => true,
                'message' => __('Translation job queued successfully in background.'),
                'async' => true,
            ]);
        }

        $summary = $this->translationService->translatePackage($package, $sourceLang, true);

        return response()->json([
            'success' => true,
            'message' => __('Missing content translated successfully.'),
            'summary' => $summary,
            'package' => $package->fresh(),
        ]);
    }

    /**
     * Handle single field translation request.
     */
    public function translateField(Request $request): JsonResponse
    {
        $request->validate([
            'source_text' => 'required|string',
            'source_lang' => 'required|string|max:10',
            'target_lang' => 'required|string|max:10',
            'structured_type' => 'nullable|string|in:text,html,json_array,faq_json',
        ]);

        $unit = new TranslationUnit(
            entityType: 'adhoc_field',
            entityId: null,
            field: 'field',
            sourceLanguage: $request->input('source_lang'),
            targetLanguage: $request->input('target_lang'),
            sourceText: $request->input('source_text'),
            structuredType: $request->input('structured_type', 'text')
        );

        $options = new TranslationOptions(structuredType: $unit->structuredType);
        $result = $this->translationService->translateUnit($unit, $options);

        if (!$result->isSuccess) {
            return response()->json([
                'success' => false,
                'message' => $result->errorMessage ?: __('Translation failed.'),
            ], 422);
        }

        return response()->json([
            'success' => true,
            'translated_text' => $result->translatedText,
            'provider' => $result->provider,
            'model' => $result->model,
        ]);
    }
}
