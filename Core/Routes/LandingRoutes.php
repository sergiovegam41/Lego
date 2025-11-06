<?php
/**
 * Landing Page API Routes
 *
 * Provides aggregated data for landing page sections
 */

use App\Models\Hero;
use App\Models\FeaturedProduct;
use App\Models\Testimonial;
use App\Models\Category;

/**
 * GET /api/landing
 *
 * Returns all landing page data in a single request:
 * - Hero section
 * - Popular products (from featured_products with "most-popular" or "best-seller" tags)
 * - Categories
 * - Testimonials
 */
Flight::route('GET /landing', function () {
    try {
        // 1. Hero Section
        $hero = Hero::getActive();
        $heroData = null;

        if ($hero) {
            $heroData = [
                'title' => $hero->title,
                'subtitle' => $hero->subtitle,
                'background_image' => $hero->background_image,
                'cta' => [
                    'label' => $hero->cta_label,
                    'link' => $hero->cta_link
                ]
            ];
        }

        // 2. Popular Products (Featured Products with specific tags)
        $featuredProducts = FeaturedProduct::with(['product'])
            ->where('is_active', true)
            ->whereIn('tag', ['most-popular', 'best-seller'])
            ->orderBy('sort_order', 'asc')
            ->limit(6)
            ->get();

        $popularProductsData = [];
        foreach ($featuredProducts as $featured) {
            if (!$featured->product) continue;

            $product = $featured->product;

            $popularProductsData[] = [
                'id' => 'prod-' . str_pad($product->id, 3, '0', STR_PAD_LEFT),
                'name' => $product->name,
                'description' => $product->description ?? 'Hermoso arreglo floral',
                'price' => (float) $product->price,
                'currency' => 'USD',
                'image' => $product->primary_image,
                'images' => $product->all_images,
                'tag' => $featured->tag === 'best-seller' ? 'Más Vendido' : null,
                'available' => $product->is_active
            ];
        }

        // 3. Categories
        $categories = Category::where('is_active', true)
            ->orderBy('name', 'asc')
            ->get();

        $categoriesData = [];
        foreach ($categories as $category) {
            $categoriesData[] = [
                'id' => 'cat-' . str_pad($category->id, 3, '0', STR_PAD_LEFT),
                'name' => $category->name,
                'description' => $category->description,
                'slug' => strtolower(str_replace(' ', '-', $category->name)),
                'image' => $category->primary_image,
                'images' => $category->all_images
            ];
        }

        // 4. Testimonials
        $testimonials = Testimonial::where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get(['id', 'author', 'message']);

        $testimonialsData = [];
        foreach ($testimonials as $testimonial) {
            $testimonialsData[] = [
                'id' => 't-' . str_pad($testimonial->id, 3, '0', STR_PAD_LEFT),
                'author' => $testimonial->author,
                'message' => $testimonial->message
            ];
        }

        // Build final response
        $response = [
            'status' => 'success',
            'timestamp' => date('c'), // ISO 8601 format
            'data' => [
                'hero' => $heroData,
                'popularProducts' => [
                    'title' => 'Nuestros Arreglos Más Populares',
                    'products' => $popularProductsData
                ],
                'categories' => [
                    'title' => 'Explora Nuestras Categorías',
                    'items' => $categoriesData
                ],
                'testimonials' => [
                    'title' => 'Lo que dicen nuestros clientes',
                    'items' => $testimonialsData
                ]
            ]
        ];

        Flight::json($response);

    } catch (Exception $e) {
        error_log("Error in /api/landing: " . $e->getMessage());

        Flight::json([
            'status' => 'error',
            'message' => 'Error loading landing page data',
            'error' => $e->getMessage()
        ], 500);
    }
});
