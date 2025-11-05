<?php
/**
 * Flowers Public API Routes
 *
 * Public endpoints for querying flowers with filters, search and pagination
 */

use App\Models\Flower;

/**
 * GET /flowers-catalog
 *
 * Search and filter flowers with pagination
 *
 * Query Parameters:
 * - category (optional): Filter by category ID
 * - q (optional): Search query (searches in name and description)
 * - page (optional): Page number (default: 1)
 * - per_page (optional): Items per page (default: 15, max: 50)
 * - sort (optional): Sort field (default: name)
 * - order (optional): Sort order - asc|desc (default: asc)
 *
 * Examples:
 * - /api/flowers-catalog                           → All flowers, page 1
 * - /api/flowers-catalog?category=2                → Flowers from category 2
 * - /api/flowers-catalog?q=rosa                    → Search "rosa" in name/description
 * - /api/flowers-catalog?category=2&q=roja         → Category 2 + search "roja"
 * - /api/flowers-catalog?page=2&per_page=20        → Page 2, 20 items per page
 * - /api/flowers-catalog?sort=price&order=desc     → Sort by price descending
 */
Flight::route('GET /flowers-catalog', function () {
    try {
        // Get query parameters
        $categoryId = isset($_GET['category']) && is_numeric($_GET['category'])
            ? (int)$_GET['category']
            : null;

        $searchQuery = isset($_GET['q']) && !empty(trim($_GET['q']))
            ? trim($_GET['q'])
            : null;

        $page = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0
            ? (int)$_GET['page']
            : 1;

        $perPage = isset($_GET['per_page']) && is_numeric($_GET['per_page'])
            ? min(max((int)$_GET['per_page'], 1), 50)  // Min 1, Max 50
            : 15;

        $sortField = isset($_GET['sort']) && in_array($_GET['sort'], ['name', 'price', 'created_at'])
            ? $_GET['sort']
            : 'name';

        $sortOrder = isset($_GET['order']) && strtolower($_GET['order']) === 'desc'
            ? 'desc'
            : 'asc';

        // Build query
        $query = Flower::with(['category', 'images'])
            ->where('is_active', true);

        // Apply category filter
        if ($categoryId !== null) {
            $query->where('category_id', $categoryId);
        }

        // Apply search query
        if ($searchQuery !== null) {
            $query->where(function($q) use ($searchQuery) {
                $q->where('name', 'ILIKE', "%{$searchQuery}%")
                  ->orWhere('description', 'ILIKE', "%{$searchQuery}%");
            });
        }

        // Get total count before pagination
        $total = $query->count();

        // Apply sorting and pagination
        $flowers = $query->orderBy($sortField, $sortOrder)
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();

        // Format response
        $flowersData = [];
        foreach ($flowers as $flower) {
            $images = [];
            foreach ($flower->images as $image) {
                $images[] = [
                    'id' => $image->id,
                    'url' => $image->url,
                    'thumbnail' => $image->thumbnail ?? $image->url
                ];
            }

            $flowersData[] = [
                'id' => $flower->id,
                'name' => $flower->name,
                'description' => $flower->description,
                'price' => (float) $flower->price,
                'currency' => 'USD',
                'category' => $flower->category ? [
                    'id' => $flower->category->id,
                    'name' => $flower->category->name
                ] : null,
                'images' => $images,
                'main_image' => !empty($images) ? $images[0]['url'] : null,
                'available' => $flower->is_active
            ];
        }

        // Calculate pagination metadata
        $totalPages = (int) ceil($total / $perPage);
        $hasNextPage = $page < $totalPages;
        $hasPrevPage = $page > 1;

        // Build response
        $response = [
            'status' => 'success',
            'timestamp' => date('c'),
            'data' => [
                'flowers' => $flowersData,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'total_items' => $total,
                    'total_pages' => $totalPages,
                    'has_next_page' => $hasNextPage,
                    'has_prev_page' => $hasPrevPage,
                    'next_page' => $hasNextPage ? $page + 1 : null,
                    'prev_page' => $hasPrevPage ? $page - 1 : null
                ],
                'filters' => [
                    'category' => $categoryId,
                    'search_query' => $searchQuery,
                    'sort_by' => $sortField,
                    'sort_order' => $sortOrder
                ]
            ]
        ];

        Flight::json($response);

    } catch (Exception $e) {
        error_log("Error in /api/flowers/search: " . $e->getMessage());

        Flight::json([
            'status' => 'error',
            'message' => 'Error searching flowers',
            'error' => $e->getMessage()
        ], 500);
    }
});

/**
 * GET /flower-detail/{id}
 *
 * Get a single flower by ID with all its details
 *
 * Example:
 * - /api/flower-detail/5 → Get flower with ID 5
 */
Flight::route('GET /flower-detail/@id', function ($id) {
    try {
        if (!is_numeric($id)) {
            Flight::json([
                'status' => 'error',
                'message' => 'Invalid flower ID'
            ], 400);
            return;
        }

        $flower = Flower::with(['category', 'images'])
            ->where('id', (int)$id)
            ->where('is_active', true)
            ->first();

        if (!$flower) {
            Flight::json([
                'status' => 'error',
                'message' => 'Flower not found'
            ], 404);
            return;
        }

        // Format images
        $images = [];
        foreach ($flower->images as $image) {
            $images[] = [
                'id' => $image->id,
                'url' => $image->url,
                'thumbnail' => $image->thumbnail ?? $image->url
            ];
        }

        // Build response
        $response = [
            'status' => 'success',
            'timestamp' => date('c'),
            'data' => [
                'id' => $flower->id,
                'name' => $flower->name,
                'description' => $flower->description,
                'price' => (float) $flower->price,
                'currency' => 'USD',
                'category' => $flower->category ? [
                    'id' => $flower->category->id,
                    'name' => $flower->category->name,
                    'description' => $flower->category->description
                ] : null,
                'images' => $images,
                'main_image' => !empty($images) ? $images[0]['url'] : null,
                'available' => $flower->is_active,
                'created_at' => $flower->created_at,
                'updated_at' => $flower->updated_at
            ]
        ];

        Flight::json($response);

    } catch (Exception $e) {
        error_log("Error in /api/flowers/{id}: " . $e->getMessage());

        Flight::json([
            'status' => 'error',
            'message' => 'Error loading flower',
            'error' => $e->getMessage()
        ], 500);
    }
});
