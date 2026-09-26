<?php

namespace App\Http\Controllers\Website;

use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TravelPackageController extends BaseWebsiteController
{
    /**
     * Display the Egypt Travel Packages landing page.
     */
    public function index(Request $request): View
    {
        $heroImage = asset('website/images/travel-packages/7-days-egypt-vacation.jpg');
        $heroImage = asset('website/images/travel-packages/hero-travel-packages.jpg');

        $pageContent = [
            'badge' => __('Egypt Vacation Packages'),
            'title' => __('Best Egypt Vacation & Travel Packages'),
            'subtitle' => __('Browse our curated selection of private Egypt travel packages, multi-day vacations, and tailor-made journeys.'),
            'overview_title' => __('Discover Egypt Vacation Packages & Guided Tours'),
            'overview_text' => __('Egypt Tours Packages will give you the chance to experience all that Egypt has to offer with our well-crafted itineraries. From short city breaks in Cairo to comprehensive 15-day journeys covering Cairo, the Pyramids, Luxor, Aswan, and relaxing Red Sea resorts, we tailor each vacation package to match your budget and travel style with private guides and 5-star service.'),
        ];

        $totalPackages = Package::query()
            ->where('is_active', true)
            ->where('package_type', 'travel_package')
            ->count();

        $featuredCategories = [
            [
                'title' => __('Egypt Vacation Packages'),
                'badge' => __('Vacation Packages'),
                'image' => 'website/images/travel-packages/7-days-egypt-vacation.jpg',
                'desc' => __('Explore thoughtfully planned Egypt vacations combining iconic landmarks, Nile journeys, and relaxing stays.'),
                'url' => route('website.tour_packages.category', ['category' => 'egypt-vacation-packages']),
            ],
            [
                'title' => __('Private Egypt Tours'),
                'badge' => __('Private Tours'),
                'image' => 'website/images/travel-packages/10-days-egypt-vacation.jpg',
                'desc' => __('Travel at your own pace with a private Egyptologist, dedicated vehicle, and a personalized itinerary.'),
                'url' => route('website.tour_packages.category', ['category' => 'private-egypt-tours']),
            ],
            [
                'title' => __('Egypt Luxury Tour - Luxury Egypt Vacations'),
                'badge' => __('Ultra Luxury'),
                'image' => 'website/images/travel-packages/luxury-egypt-tours.jpg',
                'desc' => __('Discover Egypt in exceptional comfort with premier hotels, private guides, and luxury Nile cruises.'),
                'url' => route('website.tour_packages.category', ['category' => 'egypt-luxury-tours']),
            ],
            [
                'title' => __('Family Egypt Tours'),
                'badge' => __('Family Tours'),
                'image' => 'website/images/travel-packages/5-days-egypt-vacation.jpg',
                'desc' => __('Enjoy family-friendly Egypt adventures with flexible pacing, engaging experiences, and seamless logistics.'),
                'url' => route('website.tour_packages.category', ['category' => 'family-egypt-tours']),
            ],
        ];

        $categorySlugs = [
            'egypt-vacation-packages',
            'private-egypt-tours',
            'egypt-luxury-tours',
            'family-egypt-tours',
        ];
        $dbCategories = \App\Models\PackageCategory::whereIn('slug', $categorySlugs)->get()->keyBy('slug');

        foreach ($featuredCategories as &$catCard) {
            $catSlug = basename($catCard['url']);
            if (isset($dbCategories[$catSlug])) {
                $categoryModel = $dbCategories[$catSlug];
                if ($categoryModel->image) {
                    $catCard['image'] = $categoryModel->image_url;
                }
            }
        }
        unset($catCard);

        // Duration cards matching reference page
        $packageCards = [
            [
                'days' => 2,
                'title' => __('2-Day Egypt Tours'),
                'badge' => __('2 Days / 1 Night'),
                'image' => 'website/images/travel-packages/2-days-egypt-vacation.jpg',
                'desc' => __('Cairo tour packages & city breaks designed for a stopover or a long weekend in Cairo. Packed with exciting sites & activities including the Great Pyramids and Egyptian Museum.'),
                'url' => route('website.tour_packages.duration', ['days' => 2]),
            ],
            [
                'days' => 3,
                'title' => __('3-Day Egypt Tours'),
                'badge' => __('3 Days / 2 Nights'),
                'image' => 'website/images/travel-packages/3-days-egypt-vacation.jpg',
                'desc' => __('We offer Egypt vacation packages designed for you to enjoy a long weekend in Egypt. Visit Cairo, Alexandria, or take quick private excursions in Luxor.'),
                'url' => route('website.tour_packages.duration', ['days' => 3]),
            ],
            [
                'days' => 4,
                'title' => __('4-Day Egypt Tours'),
                'badge' => __('4 Days / 3 Nights'),
                'image' => 'website/images/travel-packages/4-days-egypt-vacation.jpg',
                'desc' => __('Explore the history of Egypt through our short holiday packages. Discover Cairo pyramids, ancient Memphis, Saqqara, and optional Nile cruise highlights.'),
                'url' => route('website.tour_packages.duration', ['days' => 4]),
            ],
            [
                'days' => 5,
                'title' => __('5-Day Egypt Tours'),
                'badge' => __('5 Days / 4 Nights'),
                'image' => 'website/images/travel-packages/5-days-egypt-vacation.jpg',
                'desc' => __('There are a lot of things you can experience with Egypt 5-day tours. A perfect combination of Cairo sightseeing with a flight to Luxor or Alexandria day trips.'),
                'url' => route('website.tour_packages.duration', ['days' => 5]),
            ],
            [
                'days' => 6,
                'title' => __('6-Day Egypt Tours'),
                'badge' => __('6 Days / 5 Nights'),
                'image' => 'website/images/travel-packages/6-days-egypt-vacation.jpg',
                'desc' => __('Egypt travel and vacation packages for 6 Days 5 Nights including Cairo and Luxor, Cairo and Alexandria, or Cairo and a relaxing Nile river cruise.'),
                'url' => route('website.tour_packages.duration', ['days' => 6]),
            ],
            [
                'days' => 7,
                'title' => __('7-Day Egypt Tours'),
                'badge' => __('7 Days / 6 Nights'),
                'image' => 'website/images/travel-packages/7-days-egypt-vacation.jpg',
                'desc' => __('Discover captivating pharaonic history through an exciting tour visiting the Great Pyramids, Egyptian Museum, and sailing the Nile between Luxor and Aswan.'),
                'url' => route('website.tour_packages.duration', ['days' => 7]),
            ],
            [
                'days' => 8,
                'title' => __('8-Day Egypt Tours'),
                'badge' => __('8 Days / 7 Nights'),
                'image' => 'website/images/travel-packages/8-days-egypt-vacation.jpg',
                'desc' => __('Special Egypt vacation packages for travelers to discover Pharaonic history visiting Cairo, a 4-night 5-star Nile cruise, and optional Red Sea beaches.'),
                'url' => route('website.tour_packages.duration', ['days' => 8]),
            ],
            [
                'days' => 9,
                'title' => __('9-Day Egypt Tours'),
                'badge' => __('9 Days / 8 Nights'),
                'image' => 'website/images/travel-packages/9-days-egypt-vacation.jpg',
                'desc' => __('Fantastic budget & luxury Egypt vacation packages enjoying Cairo day trips, Luxor sightseeing, Aswan Nile cruises, and coastal relaxation in Hurghada.'),
                'url' => route('website.tour_packages.duration', ['days' => 9]),
            ],
            [
                'days' => 10,
                'title' => __('10-Day Egypt Tours'),
                'badge' => __('10 Days / 9 Nights'),
                'image' => 'website/images/travel-packages/10-days-egypt-vacation.jpg',
                'desc' => __('Enjoy your Egypt private vacation packages with knowledgeable Egyptologists. Experience Cairo, Nile cruise, Sharm El Sheikh or Hurghada.'),
                'url' => route('website.tour_packages.duration', ['days' => 10]),
            ],
            [
                'days' => 11,
                'title' => __('11-Day Egypt Tours'),
                'badge' => __('11 Days / 10 Nights'),
                'image' => 'website/images/travel-packages/11-days-egypt-vacation.jpg',
                'desc' => __('Explore the full spectrum of Egyptian history starting in Cairo, heading south through Aswan and Luxor, and finishing with Red Sea tranquility.'),
                'url' => route('website.tour_packages.duration', ['days' => 11]),
            ],
            [
                'days' => 12,
                'title' => __('12-Day Egypt Tours'),
                'badge' => __('12 Days / 11 Nights'),
                'image' => 'website/images/travel-packages/12-days-egypt-vacation.jpg',
                'desc' => __('Comprehensive Egypt vacation packages covering Cairo, Alexandria, Nile cruise from Aswan to Luxor, and relaxing beach stay in Hurghada.'),
                'url' => route('website.tour_packages.duration', ['days' => 12]),
            ],
            [
                'days' => 13,
                'title' => __('13-Day Egypt Tours'),
                'badge' => __('13 Days / 12 Nights'),
                'image' => 'website/images/travel-packages/13-days-egypt-vacation.jpg',
                'desc' => __('Experience Egypt’s rich ancient history and natural wonders. From the Pyramids and Valley of the Kings to Nubian villages and Red Sea reefs.'),
                'url' => route('website.tour_packages.duration', ['days' => 13]),
            ],
            [
                'days' => 14,
                'title' => __('14-Day Egypt Tours'),
                'badge' => __('14 Days / 13 Nights'),
                'image' => 'website/images/travel-packages/14-days-egypt-vacation.jpg',
                'desc' => __('Two weeks in Egypt allowing visitors to experience all the finest attractions at a relaxed and comfortable pace with private VIP treatment.'),
                'url' => route('website.tour_packages.duration', ['days' => 14]),
            ],
            [
                'days' => 15,
                'title' => __('15-Day Egypt Tours'),
                'badge' => __('15 Days / 14 Nights'),
                'image' => 'website/images/travel-packages/15-days-egypt-vacation.jpg',
                'desc' => __('The ultimate grand tour of Egypt! Experience every iconic landmark from Alexandria in the north to Abu Simbel in the south, plus luxury Nile cruise and Red Sea.'),
                'url' => route('website.tour_packages.duration', ['days' => 15]),
            ],
        ];

        $features = [
            [
                'icon' => 'la la-compass',
                'title' => __('Handcrafted Itineraries'),
                'desc' => __('Carefully balanced itineraries combining iconic pharaonic wonders with relaxing downtime.'),
            ],
            [
                'icon' => 'la la-gem',
                'title' => __('5-Star Luxury Accommodations'),
                'desc' => __('Stay in handpicked 5-star luxury hotels and sail aboard premier Nile cruise ships.'),
            ],
            [
                'icon' => 'la la-plane-departure',
                'title' => __('Domestic Flights Included'),
                'desc' => __('Seamless domestic air travel between Cairo, Luxor, Aswan, and Red Sea resorts.'),
            ],
            [
                'icon' => 'la la-user-shield',
                'title' => __('Private Egyptologist Guides'),
                'desc' => __('Personalized tours with your own private certified guide and luxury private vehicle.'),
            ],
        ];

        $faqs = [
            [
                'question' => __('Egypt Tours Ideas?'),
                'answer' => __('Our Egypt vacation ideas include tours to Cairo, Luxor, Aswan, and Alexandria. We offer 5-day Cairo and Luxor tour packages, 7-day Cairo and Nile Cruise tour packages, 8-day Cairo, Nile Cruise, and Alexandria vacations, and 10 to 15-day complete Egypt holiday itineraries covering all historical highlights and Red Sea beaches.'),
            ],
            [
                'question' => __('Is it Safe to do Tours in Egypt?'),
                'answer' => __('Yes, Egypt is a safe country with a wonderful ancient civilization and always welcomes international travelers warmly. Egypt is one of the most protected and tourist-friendly countries in the Middle East with dedicated tourism police, secure modern airports, and round-the-clock support for all visitors.'),
            ],
            [
                'question' => __('How Much Does it Cost to Vacation in Egypt?'),
                'answer' => __('Our prices for a vacation in Egypt start from $499 USD per person depending on the duration, hotel tier, and season. We provide exceptional value with 5-star accommodations, private transportation, guided tours, and domestic flights included in all comprehensive packages.'),
            ],
            [
                'question' => __('What is the Best Time to Visit Egypt?'),
                'answer' => __('The best time to visit Egypt is from October to April when the weather is pleasantly mild and comfortable for sightseeing at the pyramids, temples, and outdoor monuments. For Red Sea beach destinations like Hurghada and Sharm El Sheikh, travel is enjoyable all year round.'),
            ],
            [
                'question' => __('What are the Must Visited Places in Egypt?'),
                'answer' => __('The must-see places in Egypt include the Great Pyramids of Giza and Sphinx, the Grand Egyptian Museum in Cairo, Karnak Temple and the Valley of the Kings in Luxor, Philae Temple and Abu Simbel in Aswan, and the vibrant coral reefs of Hurghada or Sharm El Sheikh.'),
            ],
            [
                'question' => __('What to Wear While in Egypt?'),
                'answer' => __('Pack comfortable, lightweight, and breathable clothing such as cotton or linen. Modest clothing covering knees and shoulders is recommended when visiting mosques, religious sites, or rural towns. Don’t forget comfortable walking shoes, sunglasses, and a sunhat.'),
            ],
            [
                'question' => __('Is there any special advice for women travelers while visiting Egypt?'),
                'answer' => __('Women travelers can enjoy Egypt comfortably and safely. Dressing modestly in public areas and religious monuments is appreciated and respectful. On our private tours, your dedicated guide and driver accompany you throughout your sightseeing for total peace of mind.'),
            ],
            [
                'question' => __('How do I obtain an Egyptian tourist visa?'),
                'answer' => __('Citizens of the USA, UK, Canada, EU countries, Australia, and many others can easily obtain an e-Visa online before arrival or receive a Visa on Arrival at Cairo, Hurghada, or Luxor international airports for $25 USD.'),
            ],
            [
                'question' => __('What is the difference between standard and luxury Egypt vacation packages?'),
                'answer' => __('Standard packages include 5-star standard hotels and premier Nile cruises with small group or private guides. Luxury packages feature world-renowned ultra-luxury properties (such as Mena House, Old Cataract, Winter Palace, and Four Seasons), high-end suite Nile cruises, VIP airport meet-and-greet, and private master Egyptologists.'),
            ],
        ];

        return view('website.pages.travel-packages.index', compact(
            'heroImage',
            'pageContent',
            'totalPackages',
            'packageCards',
            'featuredCategories',
            'features',
            'faqs'
        ));
    }
}