<?php

namespace App\Http\Controllers\Concerns;

use Artesaos\SEOTools\Facades\JsonLdMulti;
use Artesaos\SEOTools\Facades\SEOTools;

trait InteractsWithSeo
{
    /**
     * Set consistent SEO meta, OpenGraph, Twitter Card, and JSON-LD data.
     */
    protected function setSeo(
        string $title,
        string $description = '',
        ?string $url = null,
        array $images = [],
        array $options = []
    ): void {
        $url ??= url()->current();

        $images = array_values(array_unique(array_filter($images, function ($image) {
            if (is_string($image)) {
                return trim($image) !== '';
            }

            return ! empty($image);
        })));

        $options = array_merge([
            'type' => 'article',
            'locale' => app()->getLocale(),
            'canonical' => $url,
            'site_name' => null,
            'twitter_site' => null,
            'twitter_card' => null,
            'json_ld_type' => null,
            'json_ld_name' => $title,
            'json_ld_values' => [],
            'opengraph' => [],
            'twitter' => [],
        ], $options);

        $type = $options['type'];
        $locale = $options['locale'];
        $canonical = $options['canonical'];
        $siteName = $options['site_name'];
        $twitterSite = $options['twitter_site'];
        $twitterCard = $options['twitter_card'] ?? (! empty($images) ? 'summary_large_image' : null);
        $jsonLdType = $options['json_ld_type'] ?? ($type === 'website' ? 'WebSite' : ucfirst($type));
        $jsonLdName = $options['json_ld_name'];

        SEOTools::setTitle($title);
        SEOTools::setDescription($description);
        SEOTools::setCanonical($canonical);

        $openGraph = SEOTools::opengraph();
        $openGraph->setTitle($title)
            ->setDescription($description)
            ->setUrl($url)
            ->setType($type);

        if ($locale) {
            $openGraph->addProperty('locale', $locale);
        }

        if ($siteName) {
            $openGraph->addProperty('site_name', $siteName);
        }

        foreach ($options['opengraph'] as $property => $value) {
            if ($value !== null && $value !== '') {
                $openGraph->addProperty($property, $value);
            }
        }

        foreach ($images as $image) {
            $openGraph->addImage($image);
        }

        $twitter = SEOTools::twitter();
        $twitter->setTitle($title)
            ->setDescription($description);

        if ($twitterSite) {
            $twitter->setSite($twitterSite);
        }

        if ($twitterCard) {
            $twitter->setType($twitterCard);
        }

        if (! empty($images)) {
            $twitter->setImages($images);
        }

        foreach ($options['twitter'] as $key => $value) {
            if ($value !== null && $value !== '') {
                $twitter->addValue($key, $value);
            }
        }

        $jsonLd = SEOTools::jsonLd();
        $jsonLd->setType($jsonLdType)
            ->setTitle($title)
            ->setDescription($description)
            ->setUrl($url);

        if ($jsonLdName) {
            $jsonLd->addValue('name', $jsonLdName);
        }

        if ($siteName) {
            $jsonLd->addValue('publisher', [
                '@type' => 'Organization',
                'name' => $siteName,
            ]);
        }

        if (! empty($images)) {
            $jsonLd->setImages($images);
        }

        foreach ($options['json_ld_values'] as $key => $value) {
            if ($value !== null && $value !== '') {
                $jsonLd->addValue($key, $value);
            }
        }
    }

    /**
     * Append an extra JSON-LD graph (e.g. breadcrumb, organization).
     */
    protected function addJsonLdGraph(string $type, array $values): void
    {
        if (empty($values)) {
            return;
        }

        $graph = JsonLdMulti::newJsonLd();
        $graph->setType($type);

        foreach ($values as $key => $value) {
            if ($value !== null && $value !== '') {
                $graph->addValue($key, $value);
            }
        }
    }

    /**
     * Helper to add breadcrumb JSON-LD list.
     */
    protected function setBreadcrumbJsonLd(array $items): void
    {
        if (empty($items)) {
            return;
        }

        $elements = [];

        foreach (array_values($items) as $index => $item) {
            $name = $item['name'] ?? null;
            $url = $item['url'] ?? $item['@id'] ?? null;

            if ($name && $url) {
                $elements[] = [
                    '@type' => 'ListItem',
                    'position' => $index + 1,
                    'name' => $name,
                    'item' => $url,
                ];
            }
        }

        if (empty($elements)) {
            return;
        }

        $this->addJsonLdGraph('BreadcrumbList', [
            'itemListElement' => $elements,
        ]);
    }
}
