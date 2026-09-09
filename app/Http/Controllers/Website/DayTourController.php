<?php

namespace App\Http\Controllers\Website;

use App\Models\City;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DayTourController extends BaseWebsiteController
{
    /**
     * Display the Day Tours & Excursions landing page.
     */
    public function index(Request $request): View
    {
        $heroImage = asset('website/images/day-tours/cairo-day-tours.jpg');

        $pageContent = [
            'badge' => __('Egypt Excursions & Sightseeing'),
            'title' => __('Egypt Excursions and Day Tours'),
            'subtitle' => __('If you ever find yourself in Egypt, there are always many exciting tours you can go on. We offer a wide range of Egypt day tours and excursions to the most famous attractions and sights in Egypt.'),
            'overview_title' => __('EGYPT DAY TOURS, ONE DAY TRIP IN EGYPT'),
            'overview_text' => __('Discover the finest day tours and excursions across Egypt. From the Great Pyramids of Giza and the Egyptian Museum in Cairo, to the majestic temples of Luxor and Aswan, and the sun-soaked Red Sea shores of Hurghada, Sharm El Sheikh, Marsa Alam, and Dahab — experience Egypt in absolute comfort with private licensed Egyptologists.'),
        ];

        // Total active day tours
        $totalDayTours = Package::query()
            ->where('is_active', true)
            ->whereIn('package_type', ['day_tour', 'shore_excursion'])
            ->count();

        // The 7 destination excursion cards matching reference structure
        $destinationCards = [
            [
                'city_slug' => 'cairo',
                'title' => __('Cairo Day Tours'),
                'badge' => __('Top Destination'),
                'image' => 'website/images/day-tours/cairo-destination.jpg',
                'desc' => __('There are a lot of things you can do in Cairo. Egypt Tour Pro will assist you to have a great time in Cairo. Our Cairo Excursions will take you to Giza Pyramids, Sphinx, the Egyptian Museum, Old Cairo, and Khan El Khalili market.'),
                'url' => route('website.tours.all', ['destination' => 'cairo', 'type' => 'day_tour']),
            ],
            [
                'city_slug' => 'luxor',
                'title' => __('Top Luxor Day Tours'),
                'badge' => __('Open-Air Museum'),
                'image' => 'website/images/day-tours/luxor-destination.jpg',
                'desc' => __('Luxor Tours has a rich heritage of attractions that can keep you busy for days. Our Luxor excursions & day tours are private, ensuring you make maximum use of your time visiting Karnak, Luxor Temple, and the Valley of the Kings.'),
                'url' => route('website.tours.all', ['destination' => 'luxor', 'type' => 'day_tour']),
            ],
            [
                'city_slug' => 'aswan',
                'title' => __('Aswan Day Tours'),
                'badge' => __('Nubian Heritage'),
                'image' => 'website/images/day-tours/aswan-destination.jpg',
                'desc' => __('Aswan is a serene Egyptian city located along the Nile near Luxor. Aswan has a sunny atmosphere perfect for tours all year round visiting Philae Temple, the High Dam, the Unfinished Obelisk, and breathtaking Abu Simbel.'),
                'url' => route('website.tours.all', ['destination' => 'aswan', 'type' => 'day_tour']),
            ],
            [
                'city_slug' => 'hurghada',
                'title' => __('Hurghada Day Tours - Tours from Hurghada'),
                'badge' => __('Red Sea & Desert'),
                'image' => 'website/images/day-tours/hurghada-destination.jpg',
                'desc' => __('Hurghada is not only for relaxing and beach holidays; there are plenty of exciting activities including Red Sea snorkeling, Giftun Island boat trips, Quad desert safaris, and day trips to Luxor and Cairo.'),
                'url' => route('website.tours.all', ['destination' => 'hurghada', 'type' => 'day_tour']),
            ],
            [
                'city_slug' => 'sharm-el-sheikh',
                'title' => __('Sharm El Sheikh Day Tours'),
                'badge' => __('Sinai Coast'),
                'image' => 'website/images/day-tours/sharm-el-sheikh-destination.jpg',
                'desc' => __('Sharm El Sheikh offers an array of activities: dive in Ras Mohamed National Park, climb Mount Sinai for sunrise, visit the ancient Saint Catherine Monastery, or enjoy vibrant desert stargazing dinner excursions.'),
                'url' => route('website.tours.all', ['destination' => 'sharm-el-sheikh', 'type' => 'day_tour']),
            ],
            [
                'city_slug' => 'marsa-alam',
                'title' => __('Marsa Alam Day Tours'),
                'badge' => __('Pristine Coral Reefs'),
                'image' => 'website/images/day-tours/marsa-alam-destination.jpg',
                'desc' => __('First enjoy the unspoilt beaches and coral reefs of Marsa Alam, then explore more of Egypt with guided day excursions to Luxor, Abu Simbel, and Sataya Dolphin Reef.'),
                'url' => route('website.tours.all', ['destination' => 'marsa-alam', 'type' => 'day_tour']),
            ],
            [
                'city_slug' => 'dahab',
                'title' => __('Dahab Day Tours'),
                'badge' => __('Bohemian Charm'),
                'image' => 'website/images/day-tours/dahab-day-tours.jpg',
                'desc' => __('Are you looking for Dahab Day Tours! Choose from a wide range of day tours to visit the world-famous Blue Hole, Three Pools, Colored Canyon, Saint Catherine, and day trips to Petra.'),
                'url' => route('website.tours.all', ['destination' => 'dahab', 'type' => 'day_tour']),
            ],
        ];

        // Attach actual day tour counts per city if available
        $cityCounts = City::query()
            ->withCount(['packages' => fn($q) => $q->where('is_active', true)->whereIn('package_type', ['day_tour', 'shore_excursion'])])
            ->pluck('packages_count', 'slug');

        foreach ($destinationCards as &$card) {
            $card['count'] = (int) ($cityCounts[$card['city_slug']] ?? 0);
        }
        unset($card);

        $features = [
            [
                'icon' => 'la la-user-tie',
                'title' => __('Expert Egyptologist Guides'),
                'desc' => __('Every excursion is led by certified, highly knowledgeable English-speaking Egyptologists.'),
            ],
            [
                'icon' => 'la la-car',
                'title' => __('Private VIP Transportation'),
                'desc' => __('Door-to-door luxury air-conditioned vehicle transfers tailored to your personal schedule.'),
            ],
            [
                'icon' => 'la la-calendar-check',
                'title' => __('100% Guaranteed Departures'),
                'desc' => __('Daily departures with flexible pick-up times and customized itineraries built just for you.'),
            ],
            [
                'icon' => 'la la-shield-alt',
                'title' => __('Safe & Secure Travel'),
                'desc' => __('Transparent pricing with no hidden fees and dedicated 24/7 travel support throughout Egypt.'),
            ],
        ];

        $faqs = [
            [
                'question' => __('What are the Official Languages of Egypt?'),
                'answer' => __('Modern Standard Arabic is the official language of Egypt, while Egyptian Arabic (Ammiya) is the most widely spoken vernacular. In all tourist destinations, hotels, and excursion sites, English is widely spoken and understood, and our tour guides are fluent in English and multiple international languages.'),
            ],
            [
                'question' => __('What are the Best Activities to Do in Egypt?'),
                'answer' => __('Egypt offers a vast array of activities including exploring the Giza Pyramids and Sphinx, visiting the Grand Egyptian Museum, cruising the Nile River between Luxor and Aswan, taking a hot air balloon flight over Luxor’s Valley of the Kings, snorkeling or scuba diving in the Red Sea, and experiencing desert quad bike safaris.'),
            ],
            [
                'question' => __('What to Wear While on Day Tours in Egypt?'),
                'answer' => __('Light, breathable cotton or linen clothing is recommended for daytime tours. Comfortable walking shoes and sun protection (hat, sunglasses, sunscreen) are essential. When visiting mosques, churches, or traditional villages, modest attire covering shoulders and knees is respectful and recommended.'),
            ],
            [
                'question' => __('Is it Safe to Travel and Take Day Tours in Egypt?'),
                'answer' => __('Yes, Egypt is a safe and welcoming destination for international travelers. Millions of visitors enjoy tours across Egypt every year. Tourist sites, airports, and roads maintain strict safety and security standards, and our private drivers and guides accompany you every step of the way.'),
            ],
            [
                'question' => __('What is the most visited destination in Egypt?'),
                'answer' => __('Cairo is the most visited city due to the Great Pyramids of Giza, the Sphinx, and world-class museums. Luxor closely follows as the world’s greatest open-air museum with the Valley of the Kings and Karnak Temple.'),
            ],
            [
                'question' => __('How many days do I need to explore Egypt?'),
                'answer' => __('A 7 to 10 day itinerary is ideal to experience Cairo, a 4-night Nile Cruise between Luxor and Aswan, and brief relaxation on the Red Sea coast. However, for travelers with limited time, 1-day to 3-day private tours provide comprehensive highlights.'),
            ],
            [
                'question' => __('What Are the Finest Destinations to Visit in Egypt?'),
                'answer' => __('The top destinations include Cairo & Giza, Luxor, Aswan, Alexandria, Hurghada, Sharm El Sheikh, Marsa Alam, Dahab, and Siwa Oasis.'),
            ],
            [
                'question' => __('What to Pack for Your Egypt Tour?'),
                'answer' => __('We recommend packing lightweight clothing, comfortable walking sneakers, sunglasses, sun hat, high-SPF sunscreen, universal power adapter (Type C/F), camera, and any personal medication.'),
            ],
        ];

        return view('website.pages.day-tours.index', compact(
            'heroImage',
            'pageContent',
            'totalDayTours',
            'destinationCards',
            'features',
            'faqs'
        ));
    }
}
