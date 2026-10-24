<?php

declare(strict_types=1);

/**
 * Returns the importmap for this application.
 *
 * - "path" is a path inside the asset mapper system. Use the
 *     "debug:asset-map" command to see the full list of paths.
 *
 * - "entrypoint" (JavaScript only) set to true for any module that will
 *     be used as an "entrypoint" (and passed to the importmap() Twig function).
 *
 * The "importmap:require" command can be used to add new entries to this file.
 *
 * @return array<string, array{    // Import name as key, description of the imported file as value
 *     path: string,               // Logical, relative or absolute path to the file
 *     type?: 'js'|'css'|'json',   // Type of the file, defaults to 'js'
 *     entrypoint?: bool,          // Whether the file is an entrypoint, for 'js' only
 * }|array{
 *     version: string,            // Version of the remote package
 *     package_specifier?: string, // Remote "package-name/path" specifier, defaults to the import name
 *     type?: 'js'|'css'|'json',
 *     entrypoint?: bool,
 * }>
 */
return [
    'app' => ['path' => '../assets/js/empty.js', 'entrypoint' => true],
    'admin' => ['path' => '../assets/js/admin/admin.js'],
    'category-filter-select2' => ['path' => '../assets/js/admin/category-filter-select2.js'],
    'form-category-select2' => ['path' => '../assets/js/admin/form-category-filter-select2.js'],
    'preview-image' => ['path' => '../assets/js/admin/preview-image.js'],
    'form-product-select2' => ['path' => '../assets/js/admin/form-product-select2.js'],
    'form-product-images' => ['path' => '../assets/js/admin/form-product-images.js'],
    '@hotwired/stimulus' => ['version' => '3.2.2'],
    'sortablejs' => ['version' => '1.15.7'],
    'jquery' => ['version' => '4.0.0'],
    '@popperjs/core' => ['version' => '2.11.8'],
    'bootstrap' => ['version' => '5.3.8'],
    'overlayscrollbars' => ['version' => '2.16.0'],
    'bootstrap/dist/css/bootstrap.min.css' => ['version' => '5.3.8', 'type' => 'css'],
    'select2' => ['version' => '4.1.0'],
    'select2/dist/css/select2.min.css' => ['version' => '4.1.0', 'type' => 'css'],
    'admin-lte' => ['version' => '4.8.5'],
    'admin-lte/dist/css/adminlte.min.css' => ['version' => '4.8.5', 'type' => 'css'],
];
