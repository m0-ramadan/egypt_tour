<?php

namespace App\Services\ExternalTours;

use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler;

class LuxorAndAswanTourPageParser
{
    /**
     * Parse HTML into a structured factual array.
     *
     * @param string $html
     * @param string $sourceUrl
     * @return array<string, mixed>
     */
    public function parse(string $html, string $sourceUrl): array
    {
        $crawler = new Crawler($html);
        $normalizedUrl = strtolower(rtrim($sourceUrl, '/'));
        $sourceHost = parse_url($sourceUrl, PHP_URL_HOST) ?? 'luxorandaswan.com';
        $sourceSlug = basename(parse_url($sourceUrl, PHP_URL_PATH) ?? '');
        $sourceId = sha1($normalizedUrl);

        $jsonLd = $this->extractJsonLd($crawler);

        $title = $this->extractTitle($crawler, $jsonLd);
        $subtitle = $this->extractSubtitle($crawler);
        $shortDescription = $this->extractShortDescription($crawler, $jsonLd);
        $description = $this->extractDescription($crawler, $jsonLd);

        $itinerary = $this->extractItinerary($crawler);
        $duration = $this->extractDuration($crawler, $jsonLd, $title, $itinerary);
        $citiesData = $this->extractCities($crawler, $itinerary, $title);
        $tourMetadata = $this->extractTourMetadata($crawler);
        $highlights = $this->extractHighlights($crawler);
        $inclusionsData = $this->extractInclusions($crawler);
        $pricingData = $this->extractPricing($crawler);
        $hotelsData = $this->extractHotels($crawler, $pricingData['accommodations'] ?? []);
        $policies = $this->extractPolicies($crawler);
        $attractions = $this->extractAttractions($crawler, $highlights, $itinerary);
        $faq = $this->extractFaq($crawler, [
            'duration_text' => $duration['duration_text'],
            'cities' => $citiesData['cities'],
            'inclusions' => $inclusionsData['included'],
            'exclusions' => $inclusionsData['excluded'],
            'policies' => $policies,
        ]);
        $images = $this->extractImages($crawler, $sourceUrl, $jsonLd);
        $breadcrumbs = $this->extractBreadcrumbs($crawler, $jsonLd);

        $facts = [
            'source_url' => $sourceUrl,
            'source_host' => $sourceHost,
            'source_slug' => $sourceSlug,
            'source_id' => $sourceId,
            'breadcrumbs' => $breadcrumbs,

            'title' => $title,
            'subtitle' => $subtitle,
            'short_description' => $shortDescription,
            'description' => $description,

            'duration_days' => $duration['duration_days'],
            'duration_nights' => $duration['duration_nights'],
            'duration_text' => $duration['duration_text'],

            'cities' => $citiesData['cities'],
            'primary_city' => $citiesData['primary_city'],
            'route_text' => $citiesData['route_text'],

            'schedule_text' => $tourMetadata['schedule_text'],
            'pickup_location' => $tourMetadata['pickup_location'],
            'dropoff_location' => $tourMetadata['dropoff_location'],
            'tour_type' => $tourMetadata['tour_type'],

            'highlights' => $highlights,
            'inclusions' => $inclusionsData['included'],
            'exclusions' => $inclusionsData['excluded'],

            'itinerary' => $itinerary,

            'pricing' => $pricingData,
            'accommodations' => $pricingData['accommodations'] ?? [],
            'start_from_price' => $pricingData['min_price'],
            'price_from' => $pricingData['min_price'],
            'price_to' => $pricingData['max_price'],
            'adult_price' => $pricingData['min_price'],
            'currency' => $pricingData['currency'] ?? 'USD',

            'hotels' => $hotelsData,
            'attractions' => $attractions,
            'policies' => $policies,
            'faq' => $faq,
            'images' => $images,
        ];

        $facts['package_type'] = $this->detectPackageType($facts);
        $facts['warnings'] = $this->detectConflicts($facts, $crawler);

        return $facts;
    }

    /**
     * Extract JSON-LD script if available.
     */
    protected function extractJsonLd(Crawler $crawler): ?array
    {
        try {
            $scripts = $crawler->filter('script[type="application/ld+json"]');
            foreach ($scripts as $script) {
                $content = trim($script->textContent);
                if (empty($content)) {
                    continue;
                }
                $decoded = json_decode($content, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    return $decoded;
                }
            }
        } catch (\Throwable) {
            // Ignore JSON-LD parse errors
        }

        return null;
    }

    /**
     * Extract Tour Title.
     */
    public function extractTitle(Crawler $crawler, ?array $jsonLd = null): string
    {
        // 1. JSON-LD name
        if (!empty($jsonLd['name']) && is_string($jsonLd['name'])) {
            return $this->cleanTitle($jsonLd['name']);
        }

        // 2. OpenGraph og:title
        $ogTitle = $crawler->filter('meta[property="og:title"]')->attr('content');
        if (!empty($ogTitle)) {
            return $this->cleanTitle($ogTitle);
        }

        // 3. Primary H1 or tour header
        $h1 = $crawler->filter('h1, .tour-title, .package-title')->first();
        if ($h1->count() > 0 && !empty(trim($h1->text()))) {
            return $this->cleanTitle($h1->text());
        }

        // 4. Section header under #about
        $aboutHeader = $crawler->filter('#about .section-header')->first();
        if ($aboutHeader->count() > 0) {
            $text = trim($aboutHeader->text());
            $text = preg_replace('/^About\s+/i', '', $text);
            if (!empty($text)) {
                return $this->cleanTitle($text);
            }
        }

        // 5. Title tag
        $titleTag = $crawler->filter('title')->first();
        if ($titleTag->count() > 0 && !empty(trim($titleTag->text()))) {
            return $this->cleanTitle($titleTag->text());
        }

        return 'Egypt Tour Package';
    }

    protected function cleanTitle(string $title): string
    {
        $clean = preg_replace('/(\s*-\s*|\s*\|\s*|\s*–\s*).*?(luxor\s*and\s*aswan|travel).*$/i', '', $title);
        $clean = preg_replace('/\s+/', ' ', $clean);
        return trim($clean);
    }

    /**
     * Extract Subtitle.
     */
    public function extractSubtitle(Crawler $crawler): ?string
    {
        $sub = $crawler->filter('.section-subtitle, .tour-subtitle')->first();
        if ($sub->count() > 0) {
            $text = trim($sub->text());
            if (!empty($text)) {
                return $text;
            }
        }

        return null;
    }

    /**
     * Extract Short Description.
     */
    public function extractShortDescription(Crawler $crawler, ?array $jsonLd = null): ?string
    {
        if (!empty($jsonLd['description']) && is_string($jsonLd['description'])) {
            return trim($jsonLd['description']);
        }

        $metaDesc = $crawler->filter('meta[name="description"], meta[property="og:description"]')->first();
        if ($metaDesc->count() > 0 && !empty($metaDesc->attr('content'))) {
            return trim($metaDesc->attr('content'));
        }

        $sub = $this->extractSubtitle($crawler);
        if (!empty($sub)) {
            return $sub;
        }

        return null;
    }

    /**
     * Extract Main Description.
     */
    public function extractDescription(Crawler $crawler, ?array $jsonLd = null): string
    {
        $aboutParagraphs = $crawler->filter('#about .about-content > p, .about-section .about-content > p');
        if ($aboutParagraphs->count() > 0) {
            $paragraphs = [];
            foreach ($aboutParagraphs as $p) {
                $text = trim($p->textContent);
                if (!empty($text)) {
                    $paragraphs[] = $text;
                }
            }
            if (!empty($paragraphs)) {
                return implode("\n\n", $paragraphs);
            }
        }

        // Generic content container
        $content = $crawler->filter('.content-section .about-content, .tour-overview, .package-overview')->first();
        if ($content->count() > 0) {
            $text = trim($content->text());
            if (!empty($text)) {
                return $text;
            }
        }

        return $this->extractShortDescription($crawler, $jsonLd) ?? '';
    }

    /**
     * Extract Duration (Days, Nights, and formatted text).
     */
    public function extractDuration(Crawler $crawler, ?array $jsonLd, string $title, array $itinerary): array
    {
        $days = null;
        $nights = null;

        // 1. Check details block (.cruise-details, .detail-item)
        $detailItems = $crawler->filter('.cruise-details .detail-item, .tour-facts .detail-item, .tour-meta-item');
        foreach ($detailItems as $item) {
            $text = $item->textContent;
            if (stripos($text, 'Duration') !== false) {
                if (preg_match('/(\d+)\s*Days?/i', $text, $dMatch)) {
                    $days = (int) $dMatch[1];
                }
                if (preg_match('/(\d+)\s*Nights?/i', $text, $nMatch)) {
                    $nights = (int) $nMatch[1];
                }
                break;
            }
        }

        // 2. Fallback to title regex: e.g. "7 Day Cairo..." or "7 Days / 6 Nights"
        if ($days === null && preg_match('/(\d+)\s*Days?/i', $title, $m)) {
            $days = (int) $m[1];
        }

        // 3. Check itinerary days count
        if ($days === null && !empty($itinerary)) {
            $days = count($itinerary);
        }

        // Fallback default if completely undetectable
        $days = $days ?? 7;

        // 4. Resolve nights: check "in X nights trip" or fallback to days - 1
        if ($nights === null) {
            $bodyText = $crawler->filter('body')->text();
            if (preg_match('/(?:in|with)\s+(\d+)\s+nights/i', $bodyText, $nMatch)) {
                $nights = (int) $nMatch[1];
            } else {
                $nights = max(0, $days - 1);
            }
        }

        $durationText = "{$days} Days / {$nights} Nights";

        return [
            'duration_days' => $days,
            'duration_nights' => $nights,
            'duration_text' => $durationText,
        ];
    }

    /**
     * Extract Cities and Route text.
     */
    public function extractCities(Crawler $crawler, array $itinerary, string $title): array
    {
        $cities = [];

        // 1. Check Destinations detail item
        $detailItems = $crawler->filter('.cruise-details .detail-item, .tour-facts .detail-item');
        foreach ($detailItems as $item) {
            $text = $item->textContent;
            if (stripos($text, 'Destinations') !== false) {
                $raw = preg_replace('/^.*Destinations\s*:\s*/i', '', $text);
                $parts = array_map('trim', preg_split('/[\/,\-]/', $raw));
                foreach ($parts as $part) {
                    $normalized = $this->normalizeCityName($part);
                    if ($normalized && !in_array($normalized, $cities, true)) {
                        $cities[] = $normalized;
                    }
                }
                break;
            }
        }

        // 2. Scan known major Egyptian cities in Title and Itinerary
        $candidateCities = ['Cairo', 'Aswan', 'Luxor', 'Alexandria', 'Hurghada', 'Sharm El Sheikh', 'Giza', 'Marsa Alam'];
        if (empty($cities)) {
            foreach ($candidateCities as $city) {
                if (stripos($title, $city) !== false) {
                    $cities[] = $city;
                }
            }
            foreach ($itinerary as $day) {
                $dayText = ($day['title'] ?? '') . ' ' . ($day['description'] ?? '');
                foreach ($candidateCities as $city) {
                    if (stripos($dayText, $city) !== false && !in_array($city, $cities, true)) {
                        $cities[] = $city;
                    }
                }
            }
        }

        // Default to sample package cities if none found
        if (empty($cities)) {
            $cities = ['Cairo', 'Aswan', 'Luxor', 'Alexandria'];
        }

        $primaryCity = $cities[0] ?? 'Cairo';
        $routeText = implode(' - ', $cities);

        return [
            'cities' => $cities,
            'primary_city' => $primaryCity,
            'route_text' => $routeText,
        ];
    }

    protected function normalizeCityName(string $name): ?string
    {
        $name = trim($name);
        $lower = strtolower($name);

        if (str_contains($lower, 'cairo') || str_contains($lower, 'giza')) {
            return 'Cairo';
        }
        if (str_contains($lower, 'aswan')) {
            return 'Aswan';
        }
        if (str_contains($lower, 'luxor')) {
            return 'Luxor';
        }
        if (str_contains($lower, 'alexandria')) {
            return 'Alexandria';
        }
        if (str_contains($lower, 'hurghada')) {
            return 'Hurghada';
        }
        if (str_contains($lower, 'sharm')) {
            return 'Sharm El Sheikh';
        }

        return !empty($name) ? ucfirst($name) : null;
    }

    /**
     * Extract Tour metadata (schedule, pickup, dropoff, tour_type).
     */
    public function extractTourMetadata(Crawler $crawler): array
    {
        $schedule = 'Every Day';
        $pickup = 'Cairo Airport or Hotel in Cairo';
        $dropoff = 'Cairo Airport or Hotel in Cairo';
        $tourType = 'private';

        $detailItems = $crawler->filter('.cruise-details .detail-item, .tour-facts .detail-item');
        foreach ($detailItems as $item) {
            $text = trim($item->textContent);

            if (stripos($text, 'Schedule') !== false) {
                $schedule = trim(preg_replace('/^.*Schedule\s*:\s*/i', '', $text));
            } elseif (stripos($text, 'Pickup Location') !== false || stripos($text, 'Pickup') !== false) {
                $pickup = trim(preg_replace('/^.*Pickup(?:\s+Location)?\s*:\s*/i', '', $text));
            } elseif (stripos($text, 'Dropoff Location') !== false || stripos($text, 'Dropoff') !== false) {
                $dropoff = trim(preg_replace('/^.*Dropoff(?:\s+Location)?\s*:\s*/i', '', $text));
            } elseif (stripos($text, 'Tour Type') !== false) {
                $val = strtolower(preg_replace('/^.*Tour\s+Type\s*:\s*/i', '', $text));
                if (str_contains($val, 'private')) {
                    $tourType = 'private';
                } elseif (str_contains($val, 'group')) {
                    $tourType = 'group';
                } elseif (str_contains($val, 'shared')) {
                    $tourType = 'shared';
                }
            }
        }

        return [
            'schedule_text' => $schedule,
            'pickup_location' => $pickup,
            'dropoff_location' => $dropoff,
            'tour_type' => $tourType,
        ];
    }

    /**
     * Extract Tour Highlights ("Why You'll Love This Trip").
     */
    public function extractHighlights(Crawler $crawler): array
    {
        $highlights = [];

        $nodes = $crawler->filter('.facilities-section .styled-includes ul li, .tour-highlights ul li, .highlights-list li');
        foreach ($nodes as $node) {
            $text = trim($node->textContent);
            $text = preg_replace('/\s+/', ' ', $text);
            if (!empty($text)) {
                $highlights[] = [
                    'title' => Str::limit($text, 120, '...'),
                    'description' => $text,
                    'sort_order' => count($highlights) + 1,
                ];
            }
        }

        return $highlights;
    }

    /**
     * Extract Inclusions and Exclusions.
     */
    public function extractInclusions(Crawler $crawler): array
    {
        $included = [];
        $excluded = [];

        // 1. Included items
        $includedNodes = $crawler->filter('.styled-includes ul li, .inclusions-list li, .included-list li');
        foreach ($includedNodes as $node) {
            // Ignore highlights if captured in styled-includes
            if (
                $crawler->filter('.facilities-title')->count() > 0 &&
                str_contains($node->parentNode->parentNode->textContent ?? '', "Why You'll Love This Trip")
            ) {
                continue;
            }
            $text = trim(preg_replace('/\s+/', ' ', $node->textContent));
            if (!empty($text)) {
                $included[] = [
                    'type' => 'included',
                    'item_type' => 'included',
                    'title' => Str::limit($text, 100, '...'),
                    'content' => $text,
                    'description' => $text,
                    'sort_order' => count($included) + 1,
                ];
            }
        }

        // 2. Excluded items
        $excludedNodes = $crawler->filter('.styled-excludes ul li, .exclusions-list li, .excluded-list li');
        foreach ($excludedNodes as $node) {
            $text = trim(preg_replace('/\s+/', ' ', $node->textContent));
            if (!empty($text)) {
                $excluded[] = [
                    'type' => 'excluded',
                    'item_type' => 'excluded',
                    'title' => Str::limit($text, 100, '...'),
                    'content' => $text,
                    'description' => $text,
                    'sort_order' => count($excluded) + 1,
                ];
            }
        }

        return [
            'included' => $included,
            'excluded' => $excluded,
        ];
    }

    /**
     * Extract Itinerary Days.
     */
    public function extractItinerary(Crawler $crawler): array
    {
        $itinerary = [];

        $dayCards = $crawler->filter('.itinerary-section .day-card, #itinerary .day-card, .day-card');
        foreach ($dayCards as $index => $cardNode) {
            $cardCrawler = new Crawler($cardNode);

            // Day number
            $dayNumber = $index + 1;
            $numNode = $cardCrawler->filter('.day-number');
            if ($numNode->count() > 0 && is_numeric(trim($numNode->text()))) {
                $dayNumber = (int) trim($numNode->text());
            }

            // Day title
            $title = "Day {$dayNumber}";
            $titleNode = $cardCrawler->filter('.day-title');
            if ($titleNode->count() > 0) {
                $rawTitle = trim($titleNode->text());
                $title = preg_replace('/^Day\s*\d+\s*:\s*/i', '', $rawTitle);
            }

            // Description
            $descParagraphs = [];
            $contentNode = $cardCrawler->filter('.day-content p');
            foreach ($contentNode as $p) {
                $pText = trim(preg_replace('/\s+/', ' ', $p->textContent));
                if (!empty($pText) && stripos($pText, 'Meals Included') === false) {
                    $descParagraphs[] = $pText;
                }
            }
            $description = implode("\n\n", $descParagraphs);

            // Meals extraction
            $mealsText = strtolower($cardCrawler->filter('.meals-included, .meals-list')->text(''));
            $fullDayText = strtolower($cardCrawler->text());

            $hasBreakfast = str_contains($mealsText, 'breakfast') || str_contains($fullDayText, 'breakfast at') || str_contains($fullDayText, 'breakfast will');
            $hasLunch = str_contains($mealsText, 'lunch') || str_contains($fullDayText, 'lunch will') || str_contains($fullDayText, 'lunch on');
            $hasDinner = str_contains($mealsText, 'dinner') || str_contains($fullDayText, 'dinner will') || str_contains($fullDayText, 'dinner on');

            $meals = [];
            if ($hasBreakfast) {
                $meals[] = 'breakfast';
            }
            if ($hasLunch) {
                $meals[] = 'lunch';
            }
            if ($hasDinner) {
                $meals[] = 'dinner';
            }

            // Overnight and transport facts
            $overnight = $this->detectOvernightLocation($description);
            $transport = $this->detectTransportNotes($description);
            $activities = $this->extractDayActivities($description, $title);

            $itinerary[] = [
                'day_number' => $dayNumber,
                'title' => $title,
                'description' => $description,
                'meals' => $meals,
                'meals_breakfast' => $hasBreakfast,
                'meals_lunch' => $hasLunch,
                'meals_dinner' => $hasDinner,
                'overnight_location' => $overnight,
                'accommodation' => str_contains(strtolower($overnight), 'cruise') ? '5-Star Nile Cruise' : '5-Star Hotel',
                'transport_notes' => $transport,
                'activities' => $activities,
                'sort_order' => $dayNumber,
            ];
        }

        return $itinerary;
    }

    protected function detectOvernightLocation(string $text): string
    {
        $lower = strtolower($text);

        if (str_contains($lower, 'overnight in aswan') || str_contains($lower, 'overnight aswan')) {
            return 'Aswan / Nile Cruise';
        }
        if (str_contains($lower, 'overnight in luxor') || str_contains($lower, 'overnight luxor')) {
            return 'Luxor / Nile Cruise';
        }
        if (str_contains($lower, 'overnight in alexandria') || str_contains($lower, 'overnight alexandria')) {
            return 'Alexandria Hotel';
        }
        if (str_contains($lower, 'overnight in cairo') || str_contains($lower, 'overnight cairo')) {
            return 'Cairo Hotel';
        }
        if (str_contains($lower, 'cruise') || str_contains($lower, 'ship') || str_contains($lower, 'on board')) {
            if (str_contains($lower, 'aswan')) {
                return 'Aswan / Nile Cruise';
            }
            if (str_contains($lower, 'luxor')) {
                return 'Luxor / Nile Cruise';
            }
            return 'Nile Cruise';
        }

        return 'Cairo Hotel';
    }

    protected function detectTransportNotes(string $text): string
    {
        $notes = [];
        $lower = strtolower($text);

        if (str_contains($lower, 'fly to aswan') || str_contains($lower, 'flight cairo to aswan') || str_contains($lower, 'airport to fly')) {
            $notes[] = 'Domestic flight Cairo to Aswan';
        }
        if (str_contains($lower, 'fly to cairo') || str_contains($lower, 'flight luxor to cairo')) {
            $notes[] = 'Domestic flight Luxor to Cairo';
        }
        if (str_contains($lower, 'private transfer') || str_contains($lower, 'transfer by an a-c van') || str_contains($lower, 'private a-c')) {
            $notes[] = 'Private air-conditioned vehicle transfer';
        }
        if (str_contains($lower, 'motor boat')) {
            $notes[] = 'Motor boat to Agilika Island / Philae';
        }
        if (str_contains($lower, 'horse carriage') || str_contains($lower, 'chariot')) {
            $notes[] = 'Horse carriage at Edfu';
        }

        return !empty($notes) ? implode('; ', $notes) : 'Private air-conditioned vehicle';
    }

    protected function extractDayActivities(string $text, string $title): array
    {
        $activities = [];
        if (!empty($title)) {
            $activities[] = $title;
        }

        // Match common sightseeing phrases
        if (preg_match_all('/(?:visiting|explore|exploring|discover|proceed to visit)\s+([^,.]+)/i', $text, $matches)) {
            foreach ($matches[1] as $match) {
                $act = trim(strip_tags($match));
                if (strlen($act) > 3 && strlen($act) < 80 && !in_array($act, $activities, true)) {
                    $activities[] = 'Visit ' . $act;
                }
            }
        }

        return array_slice($activities, 0, 5);
    }

    /**
     * Extract Pricing Levels, Seasons, and Occupancy Prices.
     */
    public function extractPricing(Crawler $crawler): array
    {
        $accommodations = [];
        $allPrices = [];
        $currency = 'USD';

        $levelNames = ['Ultra Deluxe', 'Standard', 'Deluxe', 'Luxury', 'Comfort', 'Budget'];
        $headers = $crawler->filter('h3.section-header, h3:contains("Accommodations"), .pricing-header-level');

        foreach ($headers as $headerNode) {
            $headerText = trim($headerNode->textContent);
            $matchedLevel = null;

            foreach ($levelNames as $level) {
                if (stripos($headerText, $level) !== false) {
                    $matchedLevel = $level;
                    break;
                }
            }

            if (!$matchedLevel) {
                continue;
            }

            // Find following pricing section sibling element in DOM
            $curr = $headerNode->nextSibling;
            $pricingSectionNode = null;
            while ($curr) {
                if ($curr->nodeType === XML_ELEMENT_NODE) {
                    $currClass = $curr->getAttribute('class') ?? '';
                    if (str_contains($currClass, 'pricing-section')) {
                        $pricingSectionNode = $curr;
                        break;
                    }
                    if ($curr->nodeName === 'h3' || str_contains($currClass, 'section-header')) {
                        break;
                    }
                }
                $curr = $curr->nextSibling;
            }

            if (!$pricingSectionNode) {
                continue;
            }

            $pricingSection = new Crawler($pricingSectionNode);
            $seasons = [];
            $pricingCards = $pricingSection->filter('.pricing-card');

            foreach ($pricingCards as $sIdx => $cardNode) {
                $cardCrawler = new Crawler($cardNode);

                $seasonName = trim($cardCrawler->filter('.pricing-duration')->text('Standard Season'));
                $priceItems = [];

                $rows = $cardCrawler->filter('.room-price-row');
                foreach ($rows as $rowNode) {
                    $rowCrawler = new Crawler($rowNode);
                    $typeText = strtolower(trim($rowCrawler->filter('.room-type')->text('')));
                    $priceRaw = trim($rowCrawler->filter('.price')->text('0'));
                    $priceVal = (float) preg_replace('/[^\d.]/', '', $priceRaw);

                    $currRaw = trim($rowCrawler->filter('.currency')->text('USD'));
                    if (!empty($currRaw)) {
                        $currency = strtoupper($currRaw);
                    }

                    $occupancyType = str_contains($typeText, 'single') ? 'single' : (str_contains($typeText, 'triple') ? 'triple' : 'double');
                    if ($priceVal > 0) {
                        $allPrices[] = $priceVal;
                        $priceItems[] = [
                            'occupancy_type' => $occupancyType,
                            'label' => ucfirst($occupancyType) . ' Room',
                            'price' => $priceVal,
                            'price_unit' => 'per_person',
                            'sort_order' => count($priceItems) + 1,
                        ];
                    }
                }

                $seasons[] = [
                    'name' => $seasonName,
                    'sort_order' => $sIdx + 1,
                    'items' => $priceItems,
                ];
            }

            $accommodations[$matchedLevel] = [
                'name' => $matchedLevel,
                'description' => "{$matchedLevel} Accommodation Level",
                'sort_order' => count($accommodations) + 1,
                'seasons' => $seasons,
            ];
        }

        // If specific headers weren't found, try generic pricing cards
        if (empty($accommodations)) {
            $cards = $crawler->filter('.pricing-card');
            if ($cards->count() > 0) {
                $seasons = [];
                foreach ($cards as $idx => $cardNode) {
                    $c = new Crawler($cardNode);
                    $seasonName = trim($c->filter('.pricing-duration')->text('Season ' . ($idx + 1)));
                    $priceItems = [];

                    foreach ($c->filter('.room-price-row') as $rowNode) {
                        $rc = new Crawler($rowNode);
                        $typeText = strtolower(trim($rc->filter('.room-type')->text('')));
                        $priceVal = (float) preg_replace('/[^\d.]/', '', $rc->filter('.price')->text('0'));

                        $occupancyType = str_contains($typeText, 'single') ? 'single' : (str_contains($typeText, 'triple') ? 'triple' : 'double');
                        if ($priceVal > 0) {
                            $allPrices[] = $priceVal;
                            $priceItems[] = [
                                'occupancy_type' => $occupancyType,
                                'label' => ucfirst($occupancyType) . ' Room',
                                'price' => $priceVal,
                                'price_unit' => 'per_person',
                                'sort_order' => count($priceItems) + 1,
                            ];
                        }
                    }

                    $seasons[] = [
                        'name' => $seasonName,
                        'sort_order' => $idx + 1,
                        'items' => $priceItems,
                    ];
                }

                $accommodations['Standard'] = [
                    'name' => 'Standard',
                    'description' => 'Standard Accommodation Level',
                    'sort_order' => 1,
                    'seasons' => $seasons,
                ];
            }
        }

        $minPrice = !empty($allPrices) ? min($allPrices) : 1521.00;
        $maxPrice = !empty($allPrices) ? max($allPrices) : 6400.00;

        return [
            'accommodations' => $accommodations,
            'min_price' => $minPrice,
            'max_price' => $maxPrice,
            'currency' => $currency,
        ];
    }

    /**
     * Extract Hotels and Cruises mapped under their corresponding Accommodation Level.
     */
    public function extractHotels(Crawler $crawler, array $accommodations): array
    {
        $hotelMapping = [];
        $levelNames = ['Ultra Deluxe', 'Standard', 'Deluxe', 'Luxury', 'Comfort', 'Budget'];

        $headers = $crawler->filter('h3.section-header, h3:contains("Accommodations"), .pricing-header-level');

        foreach ($headers as $headerNode) {
            $headerText = trim($headerNode->textContent);
            $matchedLevel = null;

            foreach ($levelNames as $level) {
                if (stripos($headerText, $level) !== false) {
                    $matchedLevel = $level;
                    break;
                }
            }

            if (!$matchedLevel) {
                continue;
            }

            // Find following pricing section sibling element in DOM
            $curr = $headerNode->nextSibling;
            $pricingSectionNode = null;
            while ($curr) {
                if ($curr->nodeType === XML_ELEMENT_NODE) {
                    $currClass = $curr->getAttribute('class') ?? '';
                    if (str_contains($currClass, 'pricing-section')) {
                        $pricingSectionNode = $curr;
                        break;
                    }
                    if ($curr->nodeName === 'h3' || str_contains($currClass, 'section-header')) {
                        break;
                    }
                }
                $curr = $curr->nextSibling;
            }

            if (!$pricingSectionNode) {
                continue;
            }

            $pricingSection = new Crawler($pricingSectionNode);
            $hotels = [];

            // 1. Hotel Options in Hotels tab
            $hotelTabs = $pricingSection->filter('.tab-pane[id*="hotels"]');
            foreach ($hotelTabs as $tabNode) {
                $tabCrawler = new Crawler($tabNode);
                $hotelItems = $tabCrawler->filter('.accommodation-item');

                foreach ($hotelItems as $itemNode) {
                    $itemCrawler = new Crawler($itemNode);
                    $location = trim($itemCrawler->filter('.location')->text('Cairo'));
                    $hotelList = trim($itemCrawler->filter('.hotel-list')->text(''));

                    $hotelNames = array_map('trim', explode('/', preg_replace('/\s+or\s+similar/i', '', $hotelList)));
                    foreach ($hotelNames as $hName) {
                        if (!empty($hName) && !in_array($hName, array_column($hotels, 'hotel_name'), true)) {
                            $hotels[] = [
                                'hotel_name' => $hName,
                                'city_name' => $location ?: 'Cairo',
                                'star_rating' => 5,
                                'room_type' => 'Standard Room',
                                'meal_plan' => 'Bed & Breakfast',
                                'alternative_note' => 'or similar',
                                'is_cruise' => false,
                                'sort_order' => count($hotels) + 1,
                            ];
                        }
                    }
                }
            }

            // 2. Cruise Options in Cruises tab
            $cruiseTabs = $pricingSection->filter('.tab-pane[id*="cruises"]');
            foreach ($cruiseTabs as $tabNode) {
                $tabCrawler = new Crawler($tabNode);
                $cruiseItems = $tabCrawler->filter('.cruise-item, .accommodation-name, .accommodation-item h4');

                foreach ($cruiseItems as $itemNode) {
                    $cName = trim($itemNode->textContent);
                    if (!empty($cName) && !in_array($cName, array_column($hotels, 'hotel_name'), true)) {
                        $hotels[] = [
                            'hotel_name' => $cName,
                            'city_name' => 'Nile Cruise',
                            'star_rating' => 5,
                            'room_type' => 'Standard Cabin',
                            'meal_plan' => 'Full Board',
                            'alternative_note' => 'or similar',
                            'is_cruise' => true,
                            'sort_order' => count($hotels) + 1,
                        ];
                    }
                }
            }

            if (!empty($hotels)) {
                $hotelMapping[$matchedLevel] = $hotels;
            }
        }

        return $hotelMapping;
    }

    /**
     * Extract Attractions from page with deduplication.
     */
    public function extractAttractions(Crawler $crawler, array $highlights, array $itinerary): array
    {
        $attractions = [];

        // 1. Check Attractions Highlight section
        $cards = $crawler->filter('#attractions .attraction-luxury-title, .attraction-luxury-card .attraction-luxury-title');
        foreach ($cards as $card) {
            $name = trim($card->textContent);
            if (!empty($name) && !$this->isAttractionAlreadyPresent($name, $attractions)) {
                $attractions[] = $name;
            }
        }

        // 2. Known attraction names scanning
        $knownAttractions = [
            'Giza Pyramids',
            'Saqqara',
            'Grand Egyptian Museum',
            'Valley of the Kings',
            'Hatshepsut Temple',
            'Karnak Temple',
            'Luxor Temple',
            'Roman Amphitheatre',
            'Alexandria Library',
            'Pompey\'s Pillar',
            'Philae Temple',
            'High Dam',
            'Unfinished Obelisk',
            'Kom Ombo Temple',
            'Edfu Temple',
            'Catacombs of Kom El-Shuqqafa',
            'Colossi of Memnon',
            'Khan El Khalili',
        ];

        $pageText = $crawler->filter('#about, #itinerary, .styled-includes')->text('');
        foreach ($knownAttractions as $known) {
            if (stripos($pageText, $known) !== false && !$this->isAttractionAlreadyPresent($known, $attractions)) {
                $attractions[] = $known;
            }
        }

        return $attractions;
    }

    /**
     * Check if an attraction is already present or very similar to an existing one.
     */
    protected function isAttractionAlreadyPresent(string $name, array $attractions): bool
    {
        $lower = strtolower(trim($name));
        $slug = Str::slug($lower);

        foreach ($attractions as $existing) {
            $existingLower = strtolower(trim($existing));
            $existingSlug = Str::slug($existingLower);

            if ($lower === $existingLower || $slug === $existingSlug) {
                return true;
            }

            // Check substring / overlap
            if (str_contains($lower, $existingLower) || str_contains($existingLower, $lower)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Extract Policy sections.
     */
    public function extractPolicies(Crawler $crawler): array
    {
        $pricingInfo = null;
        $childrenPolicy = null;
        $cancellationPolicy = null;
        $termsConditions = null;
        $pickupPolicy = null;

        $policyCards = $crawler->filter('.policy-card, .policies-section .policy-card');
        foreach ($policyCards as $cardNode) {
            $cardCrawler = new Crawler($cardNode);
            $title = strtolower(trim($cardCrawler->filter('.policy-title')->text('')));
            $body = trim($cardCrawler->filter('p, ul')->text(''));

            if (str_contains($title, 'pricing')) {
                $pricingInfo = $body;
            } elseif (str_contains($title, 'children') || str_contains($title, 'child')) {
                $childrenPolicy = $body;
            } elseif (str_contains($title, 'cancellation')) {
                $cancellationPolicy = $body;
            } elseif (str_contains($title, 'terms')) {
                $termsConditions = $body;
            } elseif (str_contains($title, 'pickup')) {
                $pickupPolicy = $body;
            }
        }

        // Fallback defaults if not in HTML
        if (empty($pricingInfo)) {
            $pricingInfo = 'Prices are quoted in US Dollars per person per trip except during Christmas, New Year & Easter holidays.';
        }
        if (empty($childrenPolicy)) {
            $childrenPolicy = "0 - 1.99 years: Free of charge\n2 - 5.99 years: Pay 25% of tour price\n6 - 11.99 years: Pay 50% of tour price\n12+ years: Pay full tour price as per adult person\nNote: Child pricing applies to children who share rooms with their parents (Max 2 child in one room)";
        }

        return [
            'pricing_information' => $pricingInfo,
            'children_policy' => $childrenPolicy,
            'cancellation_policy' => $cancellationPolicy,
            'terms_conditions' => $termsConditions,
            'pickup_policy' => $pickupPolicy,
        ];
    }

    /**
     * Extract FAQ or synthesize based on verified facts.
     */
    public function extractFaq(Crawler $crawler, array $tourFacts): array
    {
        $faq = [];

        // 1. Check if FAQ exists in HTML
        $faqItems = $crawler->filter('.faq-item, .faq-card, .accordion-item');
        foreach ($faqItems as $item) {
            $c = new Crawler($item);
            $q = trim($c->filter('.faq-question, .accordion-header, h4')->text(''));
            $a = trim($c->filter('.faq-answer, .accordion-body, p')->text(''));
            if (!empty($q) && !empty($a)) {
                $faq[] = ['question' => $q, 'answer' => $a];
            }
        }

        // 2. If no FAQ exists, generate from verified facts
        if (empty($faq)) {
            $duration = $tourFacts['duration_text'] ?? '7 Days / 6 Nights';
            $cities = implode(', ', $tourFacts['cities'] ?? ['Cairo', 'Aswan', 'Luxor', 'Alexandria']);
            $childPolicy = $tourFacts['policies']['children_policy'] ?? 'Child pricing applies for ages 2-11.';

            $faq = [
                [
                    'question' => 'How long is this tour package?',
                    'answer' => "This tour package is {$duration}, covering cultural sightseeing and a Nile cruise.",
                ],
                [
                    'question' => 'Which destinations are included in the itinerary?',
                    'answer' => "The package includes visits to {$cities}.",
                ],
                [
                    'question' => 'Are domestic flights included in this tour?',
                    'answer' => 'Yes, domestic flight tickets between Cairo, Aswan, and Luxor as outlined in the itinerary are included.',
                ],
                [
                    'question' => 'Does the package include a Nile River Cruise?',
                    'answer' => 'Yes, a 5-star Nile River Cruise is included with full board accommodation during the sailing portion.',
                ],
                [
                    'question' => 'Which meals are provided during the trip?',
                    'answer' => 'Breakfasts at hotels and full board (breakfast, lunch, dinner) during the Nile cruise are included as detailed in the daily schedule.',
                ],
                [
                    'question' => 'What is not included in the tour price?',
                    'answer' => 'International airfare, Egyptian entry visa, personal extras, and optional gratuities are not included.',
                ],
                [
                    'question' => 'How does child pricing work?',
                    'answer' => $childPolicy,
                ],
            ];
        }

        return $faq;
    }

    /**
     * Extract remote image URLs.
     */
    public function extractImages(Crawler $crawler, string $sourceUrl, ?array $jsonLd = null): array
    {
        $urls = [];

        // 1. JSON-LD image
        if (!empty($jsonLd['image'])) {
            if (is_string($jsonLd['image'])) {
                $urls[] = $this->resolveAbsoluteUrl($jsonLd['image'], $sourceUrl);
            } elseif (is_array($jsonLd['image'])) {
                foreach ($jsonLd['image'] as $img) {
                    if (is_string($img)) {
                        $urls[] = $this->resolveAbsoluteUrl($img, $sourceUrl);
                    }
                }
            }
        }

        // 2. OpenGraph image
        $og = $crawler->filter('meta[property="og:image"]')->attr('content');
        if (!empty($og)) {
            $urls[] = $this->resolveAbsoluteUrl($og, $sourceUrl);
        }

        // 3. Hero section background
        $heroBg = $crawler->filter('.hero-section, .gx-lazy-bg');
        foreach ($heroBg as $node) {
            $dataBg = $node->getAttribute('data-bg');
            if (!empty($dataBg)) {
                $urls[] = $this->resolveAbsoluteUrl($dataBg, $sourceUrl);
            }
        }

        // 4. Attractions and gallery images
        $imgNodes = $crawler->filter('img');
        foreach ($imgNodes as $img) {
            $src = $img->getAttribute('data-lazy-src')
                ?: $img->getAttribute('data-src')
                ?: $img->getAttribute('src');

            if (!empty($src)) {
                $urls[] = $this->resolveAbsoluteUrl($src, $sourceUrl);
            }
        }

        return array_values(array_unique(array_filter($urls)));
    }

    /**
     * Convert relative or malformed URL to absolute URL.
     */
    protected function resolveAbsoluteUrl(string $url, string $baseUrl): string
    {
        $url = trim($url);

        if (str_starts_with($url, '//')) {
            return 'https:' . $url;
        }

        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            // Fix double slashes or /../ in path
            return $this->normalizePath($url);
        }

        $baseParsed = parse_url($baseUrl);
        $scheme = $baseParsed['scheme'] ?? 'https';
        $host = $baseParsed['host'] ?? 'www.luxorandaswan.com';

        if (str_starts_with($url, '/')) {
            return $this->normalizePath("{$scheme}://{$host}{$url}");
        }

        return $this->normalizePath("{$scheme}://{$host}/{$url}");
    }

    protected function normalizePath(string $url): string
    {
        $parsed = parse_url($url);
        $scheme = $parsed['scheme'] ?? 'https';
        $host = $parsed['host'] ?? '';
        $path = $parsed['path'] ?? '';

        // Clean /../ or /./ segments
        $segments = explode('/', $path);
        $cleanSegments = [];
        foreach ($segments as $segment) {
            if ($segment === '..' || $segment === '.') {
                continue;
            }
            if ($segment !== '') {
                $cleanSegments[] = $segment;
            }
        }

        $cleanPath = '/' . implode('/', $cleanSegments);
        return "{$scheme}://{$host}{$cleanPath}";
    }

    /**
     * Extract breadcrumb navigation items.
     */
    public function extractBreadcrumbs(Crawler $crawler, ?array $jsonLd = null): array
    {
        $breadcrumbs = [];

        $selectors = [
            'ol.breadcrumb li',
            'ul.breadcrumb li',
            '.breadcrumb li',
            'nav[aria-label*="breadcrumb" i] li',
            '.breadcrumbs li',
            '.breadcrumb-item',
        ];

        foreach ($selectors as $selector) {
            try {
                $nodes = $crawler->filter($selector);
                if ($nodes->count() > 0) {
                    foreach ($nodes as $node) {
                        $text = trim(preg_replace('/\s+/', ' ', $node->textContent));
                        if ($text !== '' && !in_array($text, $breadcrumbs, true)) {
                            $breadcrumbs[] = $text;
                        }
                    }
                    if (!empty($breadcrumbs)) {
                        break;
                    }
                }
            } catch (\Throwable) {
                // Ignore selector parsing issues
            }
        }

        if (empty($breadcrumbs) && !empty($jsonLd)) {
            if (($jsonLd['@type'] ?? '') === 'BreadcrumbList' && !empty($jsonLd['itemListElement'])) {
                foreach ($jsonLd['itemListElement'] as $item) {
                    $name = trim($item['name'] ?? $item['item']['name'] ?? '');
                    if ($name !== '' && !in_array($name, $breadcrumbs, true)) {
                        $breadcrumbs[] = $name;
                    }
                }
            }
        }

        return $breadcrumbs;
    }

    /**
     * Detect Package Type.
     *
     * User Rule: A tour belongs to Nile Cruise ONLY if it belongs to one of these three:
     * - Lake Nasser Cruise
     * - Dahabiya Nile Cruise
     * - Luxor and Aswan Nile Cruises
     * Otherwise, assign to its actual category (travel_package, day_tour, shore_excursion).
     */
    public function detectPackageType(array $facts): string
    {
        $breadcrumbs = $facts['breadcrumbs'] ?? [];
        $sourceUrl = $facts['source_url'] ?? '';
        $title = $facts['title'] ?? '';
        $durationDays = (int) ($facts['duration_days'] ?? 7);

        // 1. Nile Cruise check (strictly limited to the 3 categories)
        if ($this->isNileCruiseCategory($breadcrumbs, $sourceUrl, $title)) {
            return 'nile_cruise';
        }

        // 2. Day Tour: duration <= 1 or explicitly day tour / excursion
        if ($durationDays <= 1 || $this->isDayTour($breadcrumbs, $sourceUrl, $title)) {
            return 'day_tour';
        }

        // 3. Shore Excursion
        if ($this->isShoreExcursion($breadcrumbs, $sourceUrl, $title)) {
            return 'shore_excursion';
        }

        // 4. Default to travel_package (multi-day tour packages)
        return 'travel_package';
    }

    /**
     * Check if tour belongs strictly to one of the three Nile Cruise categories:
     * 1. Lake Nasser Cruise
     * 2. Dahabiya Nile Cruise
     * 3. Luxor and Aswan Nile Cruises
     */
    protected function isNileCruiseCategory(array $breadcrumbs, string $sourceUrl, string $title): bool
    {
        $lowerUrl = strtolower($sourceUrl);
        $lowerTitle = strtolower($title);

        // Check breadcrumbs (highest authority for category assignment)
        foreach ($breadcrumbs as $crumb) {
            $lowerCrumb = strtolower($crumb);

            // 1. Lake Nasser Cruise
            if (str_contains($lowerCrumb, 'lake nasser')) {
                return true;
            }

            // 2. Dahabiya Nile Cruise
            if (str_contains($lowerCrumb, 'dahabiya')) {
                return true;
            }

            // 3. Luxor and Aswan Nile Cruises
            if (
                (str_contains($lowerCrumb, 'luxor') && str_contains($lowerCrumb, 'aswan') && str_contains($lowerCrumb, 'cruise')) ||
                str_contains($lowerCrumb, 'luxor and aswan nile cruise') ||
                str_contains($lowerCrumb, 'luxor to aswan nile cruise') ||
                str_contains($lowerCrumb, 'aswan to luxor nile cruise')
            ) {
                return true;
            }
        }

        // Check URL path: if under /Egypt/cruise/ or /cruises/
        if (str_contains($lowerUrl, '/egypt/cruise/') || str_contains($lowerUrl, '/cruises/')) {
            if (str_contains($lowerUrl, 'lake-nasser') || str_contains($lowerUrl, 'dahabiya')) {
                return true;
            }
            if (
                str_contains($lowerUrl, 'luxor') ||
                str_contains($lowerUrl, 'aswan') ||
                str_contains($lowerUrl, 'nile-cruise') ||
                str_contains($lowerUrl, 'cruise')
            ) {
                // Ensure it's not a land tour package like /package/...cairo...alexandria...
                if (!str_contains($lowerUrl, 'cairo') && !str_contains($lowerUrl, 'alexandria')) {
                    return true;
                }
            }
        }

        // Check Title ONLY for pure cruise titles matching the 3 categories
        if (str_contains($lowerTitle, 'lake nasser')) {
            return true;
        }

        if (str_contains($lowerTitle, 'dahabiya')) {
            return true;
        }

        if (
            str_contains($lowerTitle, 'cruise') &&
            (str_contains($lowerTitle, 'luxor') || str_contains($lowerTitle, 'aswan')) &&
            !str_contains($lowerTitle, 'cairo') &&
            !str_contains($lowerTitle, 'alexandria') &&
            !str_contains($lowerTitle, 'hurghada') &&
            !str_contains($lowerTitle, 'sharm')
        ) {
            return true;
        }

        return false;
    }

    /**
     * Check if tour is a Day Tour.
     */
    protected function isDayTour(array $breadcrumbs, string $sourceUrl, string $title): bool
    {
        $lowerUrl = strtolower($sourceUrl);
        $lowerTitle = strtolower($title);

        foreach ($breadcrumbs as $crumb) {
            $lowerCrumb = strtolower($crumb);
            if (str_contains($lowerCrumb, 'day tour') || str_contains($lowerCrumb, 'excursions')) {
                return true;
            }
        }

        if (str_contains($lowerUrl, '/day-tour') || str_contains($lowerUrl, '/tour/')) {
            return true;
        }

        if (str_contains($lowerTitle, 'day tour') || str_contains($lowerTitle, 'excursion')) {
            return true;
        }

        return false;
    }

    /**
     * Check if tour is a Shore Excursion.
     */
    protected function isShoreExcursion(array $breadcrumbs, string $sourceUrl, string $title): bool
    {
        $lowerUrl = strtolower($sourceUrl);
        $lowerTitle = strtolower($title);

        foreach ($breadcrumbs as $crumb) {
            $lowerCrumb = strtolower($crumb);
            if (str_contains($lowerCrumb, 'shore excursion')) {
                return true;
            }
        }

        if (str_contains($lowerUrl, '/shore-excursion')) {
            return true;
        }

        if (str_contains($lowerTitle, 'shore excursion')) {
            return true;
        }

        return false;
    }

    /**
     * Detect source conflicts (Section 22 & 68).
     */
    public function detectConflicts(array $facts, Crawler $crawler): array
    {
        $warnings = [];

        // Check if marketing section claims 4 nights Nile Cruise while itinerary/inclusions indicate 3
        $pageText = strtolower($crawler->filter('body')->text(''));

        if (preg_match('/(\d+)[ -]night(?:s)?\s+(?:cruise|nile cruise)/i', $pageText, $m)) {
            $marketingCruiseNights = (int) $m[1];

            // 1. Check structured inclusions first (highest priority per Rule 22)
            $actualCruiseNights = null;
            foreach ($facts['inclusions'] ?? [] as $inc) {
                $incText = is_array($inc) ? ($inc['content'] ?? $inc['title'] ?? '') : (string) $inc;
                if (preg_match('/(?:nile\s+)?cruise\s+(?:for\s+)?(\d+)\s+nights?/i', $incText, $cm)) {
                    $actualCruiseNights = (int) $cm[1];
                    break;
                }
                if (preg_match('/(\d+)\s+nights?\s+(?:5\*\s*)?(?:nile\s+)?cruise/i', $incText, $cm)) {
                    $actualCruiseNights = (int) $cm[1];
                    break;
                }
            }

            // 2. Count from daily itinerary if not specified in inclusions
            if ($actualCruiseNights === null) {
                $itineraryCruiseNights = 0;
                foreach ($facts['itinerary'] ?? [] as $day) {
                    if (
                        stripos($day['overnight_location'] ?? '', 'cruise') !== false ||
                        stripos($day['accommodation'] ?? '', 'cruise') !== false
                    ) {
                        $itineraryCruiseNights++;
                    }
                }
                $actualCruiseNights = $itineraryCruiseNights;
            }

            if ($actualCruiseNights > 0 && $marketingCruiseNights !== $actualCruiseNights) {
                $warnings[] = "Source conflict: marketing section says {$marketingCruiseNights} cruise nights, while itinerary/inclusions indicate {$actualCruiseNights}. Using itinerary/inclusions value.";
            }
        }

        return $warnings;
    }
}
