<?php

namespace Botble\AIContentGenerator\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\AIContentGenerator\Services\AIContentService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class AIContentController extends BaseController
{
    protected AIContentService $aiService;

    public function __construct(AIContentService $aiService)
    {
        $this->aiService = $aiService;
    }

    /**
     * Main unified generate method for all entity types
     */
    public function generate(Request $request): JsonResponse
    {
        if (!$this->aiService->isAvailable()) {
            return response()->json([
                'success' => false,
                'message' => 'AI service is not configured or available. Please check your API settings.',
                'error' => 'Service unavailable'
            ], 503);
        }

        try {
            $entityType = $request->input('entity_type', 'post');
            $action = $request->input('action', 'generate');
            
            // Handle translation
            if ($action === 'translate' || $entityType === 'translation') {
                return $this->handleTranslation($request);
            }
            
            // Handle content generation based on entity type
            switch ($entityType) {
                case 'post':
                    return $this->handleBlogGeneration($request);
                case 'product':
                    return $this->handleProductGeneration($request);
                default:
                    return response()->json([
                        'success' => false,
                        'message' => 'Unsupported entity type: ' . $entityType
                    ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while generating content',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function handleBlogGeneration(Request $request): JsonResponse
    {
        $prompt = $request->input('prompt', '');
        $options = [
            'style' => $request->input('writing_style', 'professional'),
            'language' => $request->input('language', 'en'),
            'generate_title' => $request->boolean('generate_title', true),
            'generate_description' => $request->boolean('generate_description', true),
            'generate_content' => $request->boolean('generate_content', true),
            'generate_tags' => $request->boolean('generate_tags', false),
            'generate_seo_title' => $request->boolean('generate_seo_title', true),
            'generate_seo_description' => $request->boolean('generate_seo_description', true),
        ];

        $result = $this->aiService->generateBlogContent($prompt, '', $options);
        
        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => 'Blog content generated successfully',
                'data' => $result['data'],
                'tokens_used' => $result['tokens_used'] ?? 0
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to generate blog content',
            'error' => $result['error'] ?? 'Unknown error'
        ], 500);
    }

    private function handleProductGeneration(Request $request): JsonResponse
    {
        $prompt = $request->input('prompt', '');
        $options = [
            'style' => $request->input('writing_style', 'professional'),
            'language' => $request->input('language', 'en'),
            'generate_title' => $request->boolean('generate_title', true),
            'generate_description' => $request->boolean('generate_description', true),
            'generate_content' => $request->boolean('generate_content', true),
            'generate_tags' => $request->boolean('generate_tags', false),
            'generate_seo_title' => $request->boolean('generate_seo_title', true),
            'generate_seo_description' => $request->boolean('generate_seo_description', true),
        ];

        $result = $this->aiService->generateProductContent($prompt, '', $options);
        
        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => 'Product content generated successfully',
                'data' => $result['data'],
                'tokens_used' => $result['tokens_used'] ?? 0
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to generate product content',
            'error' => $result['error'] ?? 'Unknown error'
        ], 500);
    }

    private function handleTranslation(Request $request): JsonResponse
    {
        $sourceContent = $request->input('source_content', []);
        $sourceLanguage = $request->input('source_language', 'en');
        $targetLanguage = $request->input('target_language', 'en');
        $tone = $request->input('tone', 'professional');

        $result = $this->aiService->translateContent(
            $sourceContent,
            $sourceLanguage,
            $targetLanguage,
            ['tone' => $tone]
        );

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => 'Content translated successfully',
                'data' => $result['data'],
                'tokens_used' => $result['tokens_used'] ?? 0
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to translate content',
            'error' => $result['error'] ?? 'Unknown error'
        ], 500);
    }

    /**
     * Generate product content
     */
    public function generateProductContent(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        if (!$this->aiService->isAvailable()) {
            return response()->json([
                'success' => false,
                'message' => 'AI service is not configured or available'
            ], 503);
        }

        $result = $this->aiService->generateProductContent(
            $request->input('title'),
            $request->input('description', ''),
            $request->only(['style', 'language'])
        );

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => 'Content generated successfully',
                'data' => $result['data'],
                'tokens_used' => $result['tokens_used'] ?? 0
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to generate content: ' . $result['error']
        ], 500);
    }

    /**
     * Generate blog content
     */
    public function generateBlogContent(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        if (!$this->aiService->isAvailable()) {
            return response()->json([
                'success' => false,
                'message' => 'AI service is not configured or available'
            ], 503);
        }

        $result = $this->aiService->generateBlogContent(
            $request->input('title'),
            $request->input('description', ''),
            $request->only(['style', 'language'])
        );

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => 'Content generated successfully',
                'data' => $result['data'],
                'tokens_used' => $result['tokens_used'] ?? 0
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to generate content: ' . $result['error']
        ], 500);
    }

    /**
     * Check AI service status
     */
    public function checkStatus(): JsonResponse
    {
        $status = $this->aiService->getApiStatus();
        
        return response()->json($status);
    }



    /**
     * Translate existing content to another language
     */
    public function translateContent(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'content_type' => 'required|in:product,blog',
            'target_language' => 'required|string|max:10',
            'source_content' => 'required|array',
            'source_content.title' => 'nullable|string',
            'source_content.description' => 'nullable|string',
            'source_content.content' => 'nullable|string',
            'source_content.tags' => 'nullable|string',
            'source_content.seo_title' => 'nullable|string',
            'source_content.seo_description' => 'nullable|string',
        ], [
            'content_type.required' => 'Content type is required',
            'content_type.in' => 'Content type must be product or blog',
            'target_language.required' => 'Target language is required',
            'target_language.max' => 'Target language code must not exceed 10 characters',
            'source_content.required' => 'Source content is required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed: ' . $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

        if (!$this->aiService->isAvailable()) {
            return response()->json([
                'success' => false,
                'message' => 'AI service is not configured or available. Please check the AI settings in admin panel.'
            ], 503);
        }

        // Validate content type specifically for blog
        $contentType = $request->input('content_type');
        if ($contentType === 'blog') {
            // Additional validation for blog content
            $sourceContentArray = $request->input('source_content', []);
            $hasAnyContent = !empty($sourceContentArray['title']) || 
                           !empty($sourceContentArray['description']) || 
                           !empty($sourceContentArray['content']) ||
                           !empty($sourceContentArray['tags']) ||
                           !empty($sourceContentArray['seo_title']) ||
                           !empty($sourceContentArray['seo_description']);
            
            if (!$hasAnyContent) {
                return response()->json([
                    'success' => false,
                    'message' => "No blog post content found to translate. Please fill in at least one field (title, description, content, tags, or SEO fields) before translating."
                ], 400);
            }
        }

        // Build source content string from the form data
        $sourceContent = $this->buildSourceContentString($request->input('source_content'));
        
        if (empty(trim($sourceContent))) {
            return response()->json([
                'success' => false,
                'message' => 'No content to translate. Please fill in some content first.'
            ], 400);
        }

        try {
            $result = $this->aiService->generateTranslatedContent(
                $sourceContent,
                $request->input('target_language'),
                $request->input('content_type'),
                $request->only(['style', 'language'])
            );

            if ($result['success']) {
                // Additional validation for blog translation result
                if ($contentType === 'blog' && isset($result['data'])) {
                    $translatedData = $result['data'];
                    if (empty($translatedData) || !is_array($translatedData)) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Blog post translation completed but no valid data was generated. Please try again.'
                        ], 500);
                    }
                }

                $contentTypeName = $contentType === 'blog' ? 'blog post' : $contentType;
                return response()->json([
                    'success' => true,
                    'message' => ucfirst($contentTypeName) . ' content translated successfully',
                    'data' => $result['data'],
                    'tokens_used' => $result['tokens_used'] ?? 0
                ]);
            } else {
                $errorMessage = $result['error'] ?? 'Unknown error';
                $contentTypeName = $contentType === 'blog' ? 'blog post' : $contentType;
                $errorMessage = ucfirst($contentTypeName) . ' translation failed: ' . $errorMessage;
                
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage
                ], 500);
            }

        } catch (\Exception $e) {
            $contentTypeName = $contentType === 'blog' ? 'blog post' : $contentType;
            $errorMessage = ucfirst($contentTypeName) . ' translation error: ' . $e->getMessage();
                
            return response()->json([
                'success' => false,
                'message' => $errorMessage
            ], 500);
        }
    }

    /**
     * Build a readable source content string from form data
     */
    private function buildSourceContentString(array $sourceContent): string
    {
        $parts = [];
        
        // Handle title
        if (!empty($sourceContent['title']) && is_string($sourceContent['title'])) {
            $title = trim($sourceContent['title']);
            if ($title !== '') {
                $parts[] = "TITLE: " . $title;
            }
        }
        
        // Handle description
        if (!empty($sourceContent['description']) && is_string($sourceContent['description'])) {
            $description = trim($sourceContent['description']);
            if ($description !== '') {
                $parts[] = "DESCRIPTION: " . $description;
            }
        }
        
        // Handle content
        if (!empty($sourceContent['content']) && is_string($sourceContent['content'])) {
            $content = trim($sourceContent['content']);
            if ($content !== '') {
                $parts[] = "CONTENT: " . $content;
            }
        }
        
        // Handle tags
        if (!empty($sourceContent['tags']) && is_string($sourceContent['tags'])) {
            $tags = trim($sourceContent['tags']);
            if ($tags !== '') {
                $parts[] = "TAGS: " . $tags;
            }
        }
        
        // Handle SEO title
        if (!empty($sourceContent['seo_title']) && is_string($sourceContent['seo_title'])) {
            $seoTitle = trim($sourceContent['seo_title']);
            if ($seoTitle !== '') {
                $parts[] = "SEO TITLE: " . $seoTitle;
            }
        }
        
        // Handle SEO description
        if (!empty($sourceContent['seo_description']) && is_string($sourceContent['seo_description'])) {
            $seoDescription = trim($sourceContent['seo_description']);
            if ($seoDescription !== '') {
                $parts[] = "SEO DESCRIPTION: " . $seoDescription;
            }
        }
        
        $result = implode("\n\n", $parts);
        
        return $result;
    }
}
