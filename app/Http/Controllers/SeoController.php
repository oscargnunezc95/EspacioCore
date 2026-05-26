<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use App\Models\Workshop;
use App\Models\Area;

class SeoController extends Controller
{
    /**
     * robots.txt dinamico.
     */
    public function robots()
    {
        $baseUrl = config('app.url');

        $content = "User-agent: *\n";
        $content .= "Allow: /\n";
        $content .= "Allow: /explorar\n\n";
        $content .= "Disallow: /mis-reservas\n";
        $content .= "Disallow: /mis-estudios\n";
        $content .= "Disallow: /mis-clases\n";
        $content .= "Disallow: /mis-pagos\n";
        $content .= "Disallow: /profile\n";
        $content .= "Disallow: /pagos\n\n";
        $content .= "Sitemap: {$baseUrl}/sitemap.xml\n";

        return Response::make($content, 200, ['Content-Type' => 'text/plain']);
    }

    /**
     * Sitemap XML con URLs principales y combinaciones ciudad+area.
     */
    public function sitemap()
    {
        $baseUrl = config('app.url');

        $urls = [];

        // 1. Home
        $urls[] = ['loc' => "{$baseUrl}/", 'priority' => '1.0', 'changefreq' => 'weekly'];

        // 2. Explore base
        $urls[] = ['loc' => "{$baseUrl}/explorar", 'priority' => '0.9', 'changefreq' => 'daily'];

        // 3. Combinaciones ciudad+area con contenido real
        $cities = Workshop::withoutGlobalScopes()
            ->whereNotNull('city')
            ->distinct()
            ->pluck('city');

        $areas = Area::withoutGlobalScopes()->pluck('name');

        foreach ($cities as $city) {
            // Pagina por ciudad
            $urls[] = [
                'loc'        => "{$baseUrl}/explorar?city=" . urlencode($city),
                'priority'   => '0.8',
                'changefreq' => 'daily',
            ];

            foreach ($areas as $area) {
                // Pagina por ciudad + area
                $urls[] = [
                    'loc'        => "{$baseUrl}/explorar?city=" . urlencode($city) . "&area=" . urlencode($area),
                    'priority'   => '0.7',
                    'changefreq' => 'daily',
                ];
            }
        }

        // 4. Paginas estaticas
        $staticPages = [
            ['loc' => "{$baseUrl}/login", 'priority' => '0.3'],
            ['loc' => "{$baseUrl}/register", 'priority' => '0.5'],
        ];
        $urls = array_merge($urls, $staticPages);

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($urls as $url) {
            $xml .= "  <url>\n";
            $xml .= "    <loc>{$url['loc']}</loc>\n";
            $xml .= "    <changefreq>{$url['changefreq']}</changefreq>\n";
            $xml .= "    <priority>{$url['priority']}</priority>\n";
            $xml .= "  </url>\n";
        }

        $xml .= '</urlset>';

        return Response::make($xml, 200, ['Content-Type' => 'application/xml']);
    }
}
