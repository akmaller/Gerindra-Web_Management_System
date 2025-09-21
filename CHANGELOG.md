# Changelog

## 2025-09-22

### SEO & Content Delivery
- Refactored `InteractsWithSeo` trait to centralize OpenGraph, Twitter, and JSON-LD configuration and exposed helpers for breadcrumbs and additional graphs.
- Updated Home, Post, Page, Archive, and Search controllers to rely on the refreshed trait, aligning canonical URLs, structured data, and social images.
- Broke up `HomeController@index` into focused helper methods for slides, sections, and SEO preparation to simplify maintenance.

### User Experience
- Fixed Filament “Profil Saya” page so password changes validate current credentials, prevent reuse, and provide clear success/error notifications while clearing sensitive form state.
- Restyled “Berita Berdasarkan Kategori” cards on the home page with the primary brand background, high-contrast typography, and accent link states for better readability.

### Admin & Content Management
- Enabled drag-and-drop reordering in the Filament Menu resource; moving rows now updates the `sort_order` column so frontend menus reflect the admin-defined order.

