<?php

namespace Botble\Ecommerce\Table\BulkChanges;

use Botble\Ecommerce\Models\ProductCategory;
use Botble\Table\Abstracts\TableBulkChangeAbstract;

class CategoryCheckboxBulkChange extends TableBulkChangeAbstract
{
    public static function make(array $data = []): static
    {
        $instance = new static();
        
        $instance->name('category_checkboxes')
                 ->title('Categories')
                 ->type('custom')
                 ->validate('array');

        return $instance;
    }

    public function toArray(): array
    {
        return [
            'name' => $this->getName(),
            'title' => $this->getTitle(),
            'type' => $this->getType(),
            'validate' => $this->getValidate(),
            'view' => 'plugins/ecommerce::bulk-changes.category-checkboxes',
            'data' => $this->getCategoryData(),
        ];
    }

    private function getCategoryData(): array
    {
        // Get all categories in hierarchical structure
        $categories = ProductCategory::query()
            ->select(['id', 'name', 'parent_id'])
            ->where('status', 'published')
            ->orderBy('parent_id', 'asc')
            ->orderBy('order', 'asc')
            ->orderBy('name', 'asc')
            ->get();

        // Build hierarchical tree
        $categoryTree = [];
        $categoriesById = $categories->keyBy('id');

        foreach ($categories as $category) {
            if ($category->parent_id == 0 || $category->parent_id == null) {
                $categoryTree[$category->id] = [
                    'id' => $category->id,
                    'name' => $category->name,
                    'level' => 0,
                    'children' => []
                ];
            }
        }

        // Add children recursively
        foreach ($categories as $category) {
            if ($category->parent_id && isset($categoriesById[$category->parent_id])) {
                $this->addToTree($categoryTree, $category, $categoriesById, 1);
            }
        }

        return [
            'categories' => $categoryTree,
            'selected' => []
        ];
    }

    private function addToTree(&$tree, $category, $categoriesById, $level)
    {
        $parent = $categoriesById[$category->parent_id];
        
        // Find parent in tree and add this category as child
        foreach ($tree as &$item) {
            if ($item['id'] == $parent->id) {
                $item['children'][$category->id] = [
                    'id' => $category->id,
                    'name' => $category->name,
                    'level' => $level,
                    'children' => []
                ];
                return;
            }
            
            // Search in children recursively
            if (!empty($item['children'])) {
                $this->addToTreeRecursive($item['children'], $category, $parent->id, $level);
            }
        }
    }

    private function addToTreeRecursive(&$children, $category, $parentId, $level)
    {
        foreach ($children as &$child) {
            if ($child['id'] == $parentId) {
                $child['children'][$category->id] = [
                    'id' => $category->id,
                    'name' => $category->name,
                    'level' => $level,
                    'children' => []
                ];
                return;
            }
            
            if (!empty($child['children'])) {
                $this->addToTreeRecursive($child['children'], $category, $parentId, $level + 1);
            }
        }
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getValidate(): ?string
    {
        return $this->validate;
    }
}