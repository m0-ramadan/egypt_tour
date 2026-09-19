<?php

namespace App\Console\Commands;

use App\Models\Package;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GenerateDayTourItinerariesCommand extends Command
{
    protected $signature = 'daytours:generate-itineraries {--force}';
    protected $description = 'Generate rich multilingual timeline stops for all day tours based on description and package details';

    public function handle(): int
    {
        $dayTours = Package::query()
            ->where('is_active', true)
            ->where('package_type', 'day_tour')
            ->get();

        $this->info("Processing {$dayTours->count()} active day tours...");

        foreach ($dayTours as $package) {
            $existingValid = DB::table('itineraries')
                ->where('package_id', $package->id)
                ->whereNotNull('title')
                ->where('title', '!=', '')
                ->where('title', '!=', '[]')
                ->where('title', '!=', 'null')
                ->count();

            if ($existingValid > 0 && ! $this->option('force')) {
                $this->line("Skipping Package #{$package->id} ({$package->slug}) - already has {$existingValid} stops.");
                continue;
            }

            $stops = $this->buildStopsForPackage($package);
            if (empty($stops)) {
                $this->warn("Could not parse stops for Package #{$package->id} ({$package->slug})");
                continue;
            }

            DB::table('itineraries')->where('package_id', $package->id)->delete();

            foreach ($stops as $index => $stop) {
                DB::table('itineraries')->insert([
                    'package_id' => $package->id,
                    'day_number' => $index + 1,
                    'duration' => $stop['duration'] ?? '2-3 Hours',
                    'start_time' => $stop['start_time'] ?? null,
                    'end_time' => $stop['end_time'] ?? null,
                    'title' => json_encode($stop['title']),
                    'description' => json_encode($stop['description']),
                    'overnight_location' => json_encode($stop['location'] ?? ['en' => 'Day Tour', 'ar' => 'جولة يومية', 'fr' => 'Excursion', 'de' => 'Tagestour']),
                    'activities' => json_encode($stop['activities'] ?? []),
                    'sort_order' => $index + 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $this->info("Generated " . count($stops) . " stops for Package #{$package->id} ({$package->slug})");
        }

        $this->info("All day tour timelines generated successfully!");
        return Command::SUCCESS;
    }

    private function buildStopsForPackage(Package $package): array
    {
        $slug = $package->slug;
        $titleEn = is_array($package->title) ? ($package->title['en'] ?? '') : (string) $package->title;
        $descEn = is_array($package->description) ? ($package->description['en'] ?? '') : (string) $package->description;

        // Custom handcrafted stop mapping based on common tour signatures
        if (Str::contains($slug, ['aswan-private-tour-high-dam-philae-temple', 'philae-temple'])) {
            return [
                [
                    'start_time' => '08:30:00',
                    'end_time' => '11:00:00',
                    'duration' => '2.5 Hours',
                    'title' => [
                        'en' => 'Philae Temple & Agilkia Island Boat Ride',
                        'ar' => 'معبد فيلة وجولة القارب في جزيرة أجيليكيا',
                        'fr' => 'Temple de Philae et promenade en bateau sur l\'île d\'Agilkia',
                        'de' => 'Philae-Tempel & Bootsfahrt zur Insel Agilkia',
                    ],
                    'description' => [
                        'en' => '<p>Begin with pickup from your hotel or Nile cruise in Aswan. Take a scenic motorized boat across the Nile to Agilkia Island to explore the picturesque Philae Temple, dedicated to Isis and Hathor with its stunning colonnades and sacred sanctuaries.</p>',
                        'ar' => '<p>ابدأ رحلتك بالانطلاق من فندقك أو نايل كروز في أسوان، واستقل مركباً بمحرك عبر مياه النيل إلى جزيرة أجيليكيا لزيارة معبد فيلة الرائع المكرس للإلهة إيزيس وحتحور والتمتع بأعمدته الشاهقة ونقوشه التاريخية.</p>',
                        'fr' => '<p>Commencez par une prise en charge à votre hôtel ou bateau de croisière. Prenez un bateau vers l\'île d\'Agilkia pour explorer le magnifique temple de Philae dédié à la déesse Isis.</p>',
                        'de' => '<p>Beginnen Sie mit der Abholung von Ihrem Hotel oder Kreuzfahrtschiff. Fahren Sie mit dem Boot zur Insel Agilkia und erkunden Sie den wunderschönen Philae-Tempel, der der Göttin Isis geweiht ist.</p>',
                    ],
                    'activities' => [
                        'en' => ['Boat Ride to Agilkia Island', 'Philae Temple Guided Tour', 'Isis Sanctuary Exploration'],
                        'ar' => ['جولة قارب إلى جزيرة أجيليكيا', 'جولة إرشادية في معبد فيلة', 'استكشاف قدس الأقداس'],
                        'fr' => ['Promenade en bateau', 'Visite guidée de Philae', 'Sanctuaire d\'Isis'],
                        'de' => ['Bootsfahrt zur Insel Agilkia', 'Philae-Führung', 'Isis-Heiligtum'],
                    ]
                ],
                [
                    'start_time' => '11:15:00',
                    'end_time' => '12:45:00',
                    'duration' => '1.5 Hours',
                    'title' => [
                        'en' => 'The Unfinished Obelisk & Ancient Granite Quarries',
                        'ar' => 'المسلة الناقصة ومحاجر الجرانيت القديمة',
                        'fr' => 'L\'Obélisque inachevé et les carrières de granit',
                        'de' => 'Der unvollendete Obelisk & Granitsteinbrüche',
                    ],
                    'description' => [
                        'en' => '<p>Visit the northern granite quarries of Aswan to see the largest known ancient obelisk. Discover ancient Egyptian stone-carving mastery and the ingenious techniques used to sculpt monuments out of solid bedrock.</p>',
                        'ar' => '<p>توجه إلى محاجر الجرانيت الشمالية في أسوان لرؤية أضخم مسلة حجرية في العالم القديم، واكتشف التقنيات الهندسية البارعة التي استخدمها المصريون القدماء في نحت الصخور الصلبة.</p>',
                        'fr' => '<p>Visitez les carrières de granit du nord d\'Assouan pour voir le plus grand obélisque connu et découvrir les techniques de taille de pierre des anciens Égyptiens.</p>',
                        'de' => '<p>Besuchen Sie die Granitsteinbrüche von Assuan und bestaunen Sie den größten bekannten antiken Obelisken sowie die Meißeltechniken der Pharaonen.</p>',
                    ],
                    'activities' => [
                        'en' => ['Quarry Exploration', 'Unfinished Obelisk Walk', 'Ancient Engineering Insights'],
                        'ar' => ['استكشاف المحاجر القديمة', 'مشاهدة المسلة الناقصة', 'شرح الهندسة الفرعونية'],
                        'fr' => ['Exploration de la carrière', 'Découverte de l\'obélisque', 'Secrets d\'ingénierie antique'],
                        'de' => ['Steinbruchbesichtigung', 'Obelisk-Besichtigung', 'Antike Bautechniken'],
                    ]
                ],
                [
                    'start_time' => '13:00:00',
                    'end_time' => '14:30:00',
                    'duration' => '1.5 Hours',
                    'title' => [
                        'en' => 'The Aswan High Dam & Lake Nasser Viewpoint',
                        'ar' => 'السد العالي وبحيرة ناصر',
                        'fr' => 'Le haut barrage d\'Assouan et le lac Nasser',
                        'de' => 'Der Assuan-Hochdamm & Nassersee-Aussichtspunkt',
                    ],
                    'description' => [
                        'en' => '<p>Conclude your tour at the world-famous Aswan High Dam, an engineering marvel constructed in the 1960s. Enjoy panoramic vistas across Lake Nasser and learn about its transformative impact on modern Egypt before returning to your hotel.</p>',
                        'ar' => '<p>اختتم جولتك عند السد العالي في أسوان، المعجزة الهندسية التي شيدت في الستينيات. تمتع بإطلالة بانورامية رائعة على بحيرة ناصر وتعرف على تاريخ السد العظيم قبل العودة إلى فندقك.</p>',
                        'fr' => '<p>Terminez au haut barrage d\'Assouan, merveille d\'ingénierie moderne offrant des vues panoramiques sur le lac Nasser avant votre retour à l\'hôtel.</p>',
                        'de' => '<p>Beenden Sie Ihre Tour am Assuan-Hochdamm mit herrlichem Panoramablick über den Nassersee, bevor Sie zu Ihrer Unterkunft zurückkehren.</p>',
                    ],
                    'activities' => [
                        'en' => ['High Dam Viewing', 'Lake Nasser Photography', 'Hotel Drop-off'],
                        'ar' => ['مشاهدة السد العالي', 'تصوير بحيرة ناصر', 'التوصيل للفندق'],
                        'fr' => ['Vue sur le barrage', 'Photos du lac Nasser', 'Retour à l\'hôtel'],
                        'de' => ['Hochdamm-Aussicht', 'Nassersee-Fotostopp', 'Rücktransfer'],
                    ]
                ],
            ];
        }

        if (Str::contains($slug, ['giza-pyramids', 'pyramids-of-giza', 'sphinx'])) {
            return [
                [
                    'start_time' => '08:00:00',
                    'end_time' => '11:00:00',
                    'duration' => '3 Hours',
                    'title' => [
                        'en' => 'The Great Pyramids of Giza (Khufu, Khafre & Menkaure)',
                        'ar' => 'أهرامات الجيزة العظيمة (خوفو، خفرع ومنقرع)',
                        'fr' => 'Les grandes pyramides de Gizeh (Khéops, Khéphren et Mykérinos)',
                        'de' => 'Die großen Pyramiden von Gizeh (Cheops, Chephren & Mykerinos)',
                    ],
                    'description' => [
                        'en' => '<p>Meet your private Egyptologist at your hotel and travel to the Giza Plateau. Marvel at the Great Pyramid of Khufu, the sole surviving Wonder of the Ancient World, and explore the royal pyramids of Khafre and Menkaure while learning about pharaonic burial traditions.</p>',
                        'ar' => '<p>التقِ بمرشدك السياحي الخاص وتوجه نحو هضبة الأهرامات بالجيزة. استمتع بمشاهدة هرم خوفو الأكبر، العجيبة الوحيدة الباقية من عجائب العالم القديم، واستكشف هرمي خفرع ومنقرع واستمع إلى الأسرار التاريخية لبناء الأهرامات.</p>',
                        'fr' => '<p>Rejoignez le plateau de Gizeh avec votre égyptologue privé. Admirez la Grande Pyramide de Khéops, seule merveille du monde antique encore debout, ainsi que les pyramides de Khéphren et Mykérinos.</p>',
                        'de' => '<p>Treffen Sie Ihren Ägyptologen und fahren Sie zum Gizeh-Plateau. Bestaunen Sie die Cheops-Pyramide, das einzige erhaltene Weltwunder der Antike, sowie die Pyramiden von Chephren und Mykerinos.</p>',
                    ],
                    'activities' => [
                        'en' => ['Great Pyramid of Khufu', 'Panoramic Plateau Viewpoint', 'Camel Ride Opportunity'],
                        'ar' => ['زيارة هرم خوفو الأكبر', 'بانوراما الأهرامات', 'فرصة ركوب الجمال'],
                        'fr' => ['Grande pyramide de Khéops', 'Point de vue panoramique', 'Balade à dos de chameau'],
                        'de' => ['Große Cheops-Pyramide', 'Panoramablick auf das Plateau', 'Kamelritt-Möglichkeit'],
                    ]
                ],
                [
                    'start_time' => '11:15:00',
                    'end_time' => '13:00:00',
                    'duration' => '1.75 Hours',
                    'title' => [
                        'en' => 'The Great Sphinx & Valley Temple of Khafre',
                        'ar' => 'تمثال أبو الهول العظيم ومعبد الوادي للملك خفرع',
                        'fr' => 'Le Grand Sphinx et le temple de la vallée de Khéphren',
                        'de' => 'Die Große Sphinx & der Taltempel des Chephren',
                    ],
                    'description' => [
                        'en' => '<p>Descend to the Valley Temple of Khafre to see the masterfully preserved megalithic limestone and granite architecture where royal mummification rituals were performed. Stand at the base of the enigmatic Great Sphinx, the legendary lion with a human head.</p>',
                        'ar' => '<p>انتقل إلى معبد الوادي للملك خفرع لتشاهد عمارة الجرانيت الضخمة حيث كانت تتم طقوس التحنيط الملكي، ثم قف وجهاً لوجه أمام تمثال أبو الهول الأسطوري حارس الأهرامات الخالد.</p>',
                        'fr' => '<p>Découvrez le temple de la vallée de Khéphren où se déroulaient les rites de momification, puis contemplez la majestueuse silhouette du Grand Sphinx gardant le plateau.</p>',
                        'de' => '<p>Besichtigen Sie den Taltempel des Chephren, den Ort der königlichen Mumifizierung, und stehen Sie vor der legendären Großen Sphinx, dem Wächter der Pyramiden.</p>',
                    ],
                    'activities' => [
                        'en' => ['Valley Temple Exploration', 'Sphinx Photography', 'Hotel Return Transfer'],
                        'ar' => ['استكشاف معبد الوادي', 'التقاط صور مع أبو الهول', 'العودة إلى الفندق'],
                        'fr' => ['Visite du temple de la vallée', 'Photos du Sphinx', 'Retour à l\'hôtel'],
                        'de' => ['Taltempel-Besichtigung', 'Sphinx-Fotostopp', 'Rückfahrt zum Hotel'],
                    ]
                ],
            ];
        }

        if (Str::contains($slug, ['luxor', 'karnak', 'valley-of-the-kings', 'hatshepsut', 'balloon'])) {
            return [
                [
                    'start_time' => '07:30:00',
                    'end_time' => '10:30:00',
                    'duration' => '3 Hours',
                    'title' => [
                        'en' => 'West Bank: Valley of the Kings & Royal Tombs',
                        'ar' => 'البر الغربي: وادي الملوك والمقابر الفرعونية الملكية',
                        'fr' => 'Rive Ouest : Vallée des Rois et tombes royales',
                        'de' => 'Westjordanland: Tal der Könige & Königsgräber',
                    ],
                    'description' => [
                        'en' => '<p>Cross the Nile to Luxor\'s West Bank and enter the legendary Valley of the Kings. Descend into underground royal tombs adorned with vivid hieroglyphic paintings and mystical funerary texts that have endured for over 3,000 years.</p>',
                        'ar' => '<p>اعبر النيل إلى البر الغربي لمدينة الأقصر وتوجه إلى وادي الملوك الأسطوري. انزل داخل المقابر الملكية المنحوتة في الصخر لتشاهد النقوش الهيروغليفية والألوان الزاهية الباقية منذ أكثر من 3000 عام.</p>',
                        'fr' => '<p>Traversez le Nil vers la rive ouest pour explorer la légendaire Vallée des Rois et ses tombes royales aux fresques millénaires exceptionnellement préservées.</p>',
                        'de' => '<p>Fahren Sie zum Westufer von Luxor in das Tal der Könige. Steigen Sie in die Gräber der Pharaonen hinab und bewundern Sie die detailreichen Wandmalereien.</p>',
                    ],
                    'activities' => [
                        'en' => ['Royal Tombs Exploration', 'Hieroglyphic Art Viewing', 'Egyptologist Guided Narration'],
                        'ar' => ['استكشاف المقابر الملكية', 'مشاهدة النقوش والألوان', 'شرح مفصل من المرشد'],
                        'fr' => ['Visite des tombes royales', 'Fresques pharaoniques', 'Guide égyptologue'],
                        'de' => ['Königsgräber-Führung', 'Hieroglyphen-Kunst', 'Ägyptologe-Begleitung'],
                    ]
                ],
                [
                    'start_time' => '10:45:00',
                    'end_time' => '12:30:00',
                    'duration' => '1.75 Hours',
                    'title' => [
                        'en' => 'Temple of Hatshepsut & Colossi of Memnon',
                        'ar' => 'معبد الملكة حتشبسوت وتمثالا ممنون',
                        'fr' => 'Temple d\'Hatchepsout et Colosses de Memnon',
                        'de' => 'Hatschepsut-Tempel & Memnon-Kolosse',
                    ],
                    'description' => [
                        'en' => '<p>Visit the majestic terraced Temple of Queen Hatshepsut at Deir el-Bahari, carved directly into the towering limestone cliffs. On the way back, stop at the towering Colossi of Memnon, two giant stone statues of Pharaoh Amenhotep III.</p>',
                        'ar' => '<p>زر معبد الملكة حتشبسوت الرائع في الدير البحري المنحوت في قلب الجبل الصخري الشاهق، ثم توقف في طريق العودة أمام تمثالي ممنون العملاقين للملك أمنحتب الثالث.</p>',
                        'fr' => '<p>Visitez le spectaculaire temple à terrasses d\'Hatchepsout à Deir el-Bahari, puis admirez les imposants colosses de Memnon.</p>',
                        'de' => '<p>Besichtigen Sie den beeindruckenden Terrassentempel der Königin Hatschepsut in Deir el-Bahari und halten Sie an den monumentalen Memnon-Kolossen.</p>',
                    ],
                    'activities' => [
                        'en' => ['Hatshepsut Terraced Temple', 'Deir el-Bahari Photography', 'Colossi of Memnon Stop'],
                        'ar' => ['زيارة معبد حتشبسوت', 'تصوير الدير البحري', 'التوقف عند تمثالي ممنون'],
                        'fr' => ['Temple d\'Hatchepsout', 'Photos à Deir el-Bahari', 'Arrêt aux Colosses de Memnon'],
                        'de' => ['Hatschepsut-Tempel', 'Fotostopp Deir el-Bahari', 'Memnon-Kolosse'],
                    ]
                ],
                [
                    'start_time' => '13:30:00',
                    'end_time' => '16:00:00',
                    'duration' => '2.5 Hours',
                    'title' => [
                        'en' => 'East Bank: Karnak Temple Complex & Luxor Temple',
                        'ar' => 'البر الشرقي: مجمع معابد الكرنك ومعبد الأقصر',
                        'fr' => 'Rive Est : Complexe des temples de Karnak et temple de Louxor',
                        'de' => 'Ostufer: Karnak-Tempelkomplex & Luxor-Tempel',
                    ],
                    'description' => [
                        'en' => '<p>Cross to the East Bank to explore Karnak Temple, the largest religious complex ever constructed in antiquity. Walk among the 134 towering pillars of the Great Hypostyle Hall, marvel at sacred obelisks, and visit the iconic Luxor Temple on the banks of the Nile.</p>',
                        'ar' => '<p>انتقل إلى البر الشرقي لزيارة مجمع معابد الكرنك، أكبر دور عبادة في العالم القديم، وتجول بين 134 عموداً عملاقاً في صالة الأعمدة الكبرى، ثم زر معبد الأقصر الساحر الواقع على ضفاف النيل.</p>',
                        'fr' => '<p>Explorez le complexe grandiose de Karnak et sa célèbre salle hypostyle aux 134 colonnes, puis terminez au majestueux temple de Louxor.</p>',
                        'de' => '<p>Erkunden Sie den gewaltigen Karnak-Tempel mit seinem berühmten Säulensaal aus 134 Säulen und besuchen Sie anschließend den Luxor-Tempel am Nilufer.</p>',
                    ],
                    'activities' => [
                        'en' => ['Great Hypostyle Hall Walk', 'Sacred Lake & Obelisks', 'Luxor Temple Sunset Viewing'],
                        'ar' => ['جولة صالة الأعمدة الكبرى', 'البحيرة المقدسة والمسلات', 'زيارة معبد الأقصر'],
                        'fr' => ['Grande salle hypostyle', 'Lac sacré et obélisques', 'Visite du temple de Louxor'],
                        'de' => ['Großer Säulensaal', 'Heiliger See und Obelisken', 'Luxor-Tempel Besichtigung'],
                    ]
                ],
            ];
        }

        if (Str::contains($slug, ['alexandria'])) {
            return [
                [
                    'start_time' => '07:00:00',
                    'end_time' => '10:30:00',
                    'duration' => '3.5 Hours',
                    'title' => [
                        'en' => 'Scenic Drive to Alexandria & Catacombs of Kom El Shoqafa',
                        'ar' => 'الانطلاق إلى الإسكندرية وزيارة مقابر كوم الشقافة',
                        'fr' => 'Trajet vers Alexandrie et Catacombes de Kom El Choqafa',
                        'de' => 'Fahrt nach Alexandria & Katakomben von Kom El-Schuqafa',
                    ],
                    'description' => [
                        'en' => '<p>Depart early from Cairo in a private air-conditioned vehicle towards the Mediterranean bride, Alexandria. Descend into the Catacombs of Kom El Shoqafa, a multi-level Roman burial complex blending Egyptian, Greek, and Roman artistic motifs.</p>',
                        'ar' => '<p>انطلق صباحاً من القاهرة بسيارة خاصة ومكيفة إلى عروس البحر المتوسط الإسكندرية. ابدأ بزيارة مقابر كوم الشقافة متعددة المستويات والمحفورة في الصخر والتي تمزج الفنون الفرعونية باليونانية والرومانية.</p>',
                        'fr' => '<p>Départ du Caire vers Alexandrie. Explorez les fascinantes catacombes de Kom El Choqafa taillées dans la roche, mélange unique d\'art pharaonique et gréco-romain.</p>',
                        'de' => '<p>Fahrt von Kairo nach Alexandria an die Mittelmeerküste. Besichtigen Sie die unterirdischen Katakomben von Kom El-Schuqafa mit ihrer einzigartigen Stilmischung.</p>',
                    ],
                    'activities' => [
                        'en' => ['Air-Conditioned Transfer', 'Catacombs Underground Tour', 'Pompey\'s Pillar Stop'],
                        'ar' => ['توصيل مريح ومكيف', 'جولة سراديب كوم الشقافة', 'التوقف عند عمود السواري'],
                        'fr' => ['Transfert climatisé', 'Visite des catacombes', 'Arrêt à la Colonne de Pompée'],
                        'de' => ['Klimatisierter Transfer', 'Katakomben-Führung', 'Stopp an der Pompejussäule'],
                    ]
                ],
                [
                    'start_time' => '11:00:00',
                    'end_time' => '13:00:00',
                    'duration' => '2 Hours',
                    'title' => [
                        'en' => 'Citadel of Qaitbay & Mediterranean Corniche',
                        'ar' => 'قلعة قايتباي وكورنيش الإسكندرية',
                        'fr' => 'Citadelle de Qaitbay et Corniche méditerranéenne',
                        'de' => 'Qaitbay-Zitadelle & Mittelmeer-Corniche',
                    ],
                    'description' => [
                        'en' => '<p>Head to the historic Qaitbay Citadel, built on the exact site of the ancient Pharos Lighthouse (one of the Seven Wonders of the Ancient World). Enjoy sea breezes, panoramic Mediterranean views, and a fresh seafood lunch by the coast.</p>',
                        'ar' => '<p>توجه إلى قلعة قايتباي التاريخية المشيدة في موقع منارة الإسكندرية القديمة (إحدى عجائب الدنيا السبع). استمتع بنسيم البحر الأبيض المتوسط وإطلالة بانورامية رائعة وغداء مأكولات بحرية طازج.</p>',
                        'fr' => '<p>Visitez la citadelle de Qaitbay érigée sur l\'emplacement du légendaire phare d\'Alexandrie, avec une vue panoramique sur la mer Méditerranée.</p>',
                        'de' => '<p>Besuchen Sie die historische Qaitbay-Festung am Standort des antiken Pharos-Leuchtturms und genießen Sie den Blick auf das Mittelmeer.</p>',
                    ],
                    'activities' => [
                        'en' => ['Qaitbay Fortress Tour', 'Corniche Coastal Walk', 'Mediterranean Seafood Lunch'],
                        'ar' => ['جولة قلعة قايتباي', 'المشي على الكورنيش', 'غداء أسماك طازجة'],
                        'fr' => ['Visite de la citadelle', 'Balade sur la Corniche', 'Déjeuner méditerranéen'],
                        'de' => ['Zitadellen-Rundgang', 'Spaziergang an der Corniche', 'Mittagessen am Meer'],
                    ]
                ],
                [
                    'start_time' => '13:30:00',
                    'end_time' => '16:00:00',
                    'duration' => '2.5 Hours',
                    'title' => [
                        'en' => 'Bibliotheca Alexandrina & Return to Cairo',
                        'ar' => 'مكتبة الإسكندرية والعودة إلى القاهرة',
                        'fr' => 'Bibliothèque d\'Alexandrie et retour au Caire',
                        'de' => 'Bibliotheca Alexandrina & Rückfahrt nach Kairo',
                    ],
                    'description' => [
                        'en' => '<p>Visit the modern Bibliotheca Alexandrina, a stunning tribute to the ancient Great Library of Alexandria. Explore its grand reading room, museums, and art galleries before enjoying a relaxing drive back to your hotel in Cairo.</p>',
                        'ar' => '<p>زر مكتبة الإسكندرية الحديثة، الصرح الثقافي العالمي الذي يحيي مجد المكتبة القديمة. استكشف قاعة القراءة الضخمة والمعارض الفنية والمتاحف التابعة لها قبل العودة براحة إلى فندقك بالقاهرة.</p>',
                        'fr' => '<p>Visitez la splendide Bibliotheca Alexandrina et ses musées avant de reprendre la route vers votre hôtel au Caire.</p>',
                        'de' => '<p>Besichtigen Sie die moderne Bibliotheca Alexandrina mit ihrem riesigen Lesesaal, bevor Sie entspannt nach Kairo zurückkehren.</p>',
                    ],
                    'activities' => [
                        'en' => ['Bibliotheca Alexandrina Tour', 'Manuscripts & Antiquities Museum', 'Hotel Drop-off in Cairo'],
                        'ar' => ['جولة مكتبة الإسكندرية', 'متحف المخطوطات والآثار', 'التوصيل للفندق بالقاهرة'],
                        'fr' => ['Visite de la bibliothèque', 'Musée des manuscrits', 'Retour à l\'hôtel au Caire'],
                        'de' => ['Bibliothek-Führung', 'Museumsrundgang', 'Rücktransfer nach Kairo'],
                    ]
                ],
            ];
        }

        if (Str::contains($slug, ['dolphin', 'snorkeling', 'diving', 'orange-bay', 'paradise-island', 'quad-safari', 'safari'])) {
            return [
                [
                    'start_time' => '08:30:00',
                    'end_time' => '12:00:00',
                    'duration' => '3.5 Hours',
                    'title' => [
                        'en' => 'Marina Departure & Red Sea Boat Cruise',
                        'ar' => 'الانطلاق من المارينا والإبحار في مياه البحر الأحمر',
                        'fr' => 'Départ de la marina et croisière sur la mer Rouge',
                        'de' => 'Abfahrt von der Marina & Bootsfahrt auf dem Roten Meer',
                    ],
                    'description' => [
                        'en' => '<p>Enjoy hotel pickup and transfer to the jetty. Board a comfortable yacht and cruise along the pristine turquoise waters of the Red Sea. Receive your snorkeling and safety equipment from professional instructors.</p>',
                        'ar' => '<p>استمتع بالتوصيل من الفندق إلى المارينا، واصعد على متن اليخت الفاخر للإبحار في المياه الفيروزية الصافية للبحر الأحمر. استلم معدات الغطس والسنوركلينج مع إرشادات السلامة من الغواصين المحترفين.</p>',
                        'fr' => '<p>Transfert depuis votre hôtel jusqu\'au port. Embarquez sur un bateau tout confort pour naviguer sur les eaux turquoise de la mer Rouge.</p>',
                        'de' => '<p>Abholung vom Hotel und Transfer zum Hafen. Gehen Sie an Bord eines komfortablen Bootes und stechen Sie in das kristallklare Rote Meer.</p>',
                    ],
                    'activities' => [
                        'en' => ['Hotel Transfer to Marina', 'Yacht Sailing', 'Snorkeling Equipment Fitting'],
                        'ar' => ['التوصيل للمارينا', 'الإبحار باليخت', 'تجهيز معدات السنوركلينج'],
                        'fr' => ['Transfert marina', 'Navigation en mer', 'Équipement de plongée'],
                        'de' => ['Marina-Transfer', 'Segeltörn', 'Ausrüstungs-Check'],
                    ]
                ],
                [
                    'start_time' => '12:00:00',
                    'end_time' => '16:00:00',
                    'duration' => '4 Hours',
                    'title' => [
                        'en' => 'Coral Reef Snorkeling, Island Relaxation & Lunch',
                        'ar' => 'السنوركلينج بين الشعاب المرجانية، الاسترخاء على الجزيرة والغداء',
                        'fr' => 'Snorkeling sur les récifs coralliens, détente sur l\'île et déjeuner',
                        'de' => 'Schnorcheln an Korallenriffen, Inselentspannung & Mittagessen',
                    ],
                    'description' => [
                        'en' => '<p>Stop at world-class coral reef spots to swim among vibrant tropical fish and marine life. Spend free time relaxing on the sandy island beach, savor a freshly prepared buffet lunch onboard, and enjoy return transfer to your hotel.</p>',
                        'ar' => '<p>توقف عند أشهر مناطق الشعاب المرجانية للسباحة والسنوركلينج مع الأسماك الملونة والكائنات البحرية النادرة. استمتع بوقت حر على رمال الجزيرة الذهبية وتناول بوفيه غداء شهي على متن اليخت قبل العودة للفندق.</p>',
                        'fr' => '<p>Plongez avec palmes, masque et tuba au cœur de récifs coralliens spectaculaires. Profitez d\'un temps libre sur la plage et d\'un savoureux déjeuner buffet à bord.</p>',
                        'de' => '<p>Schnorcheln Sie an spektakulären Korallenriffen mit bunten Fischen. Entspannen Sie am Sandstrand der Insel und genießen Sie ein frisches Mittagsbuffet an Bord.</p>',
                    ],
                    'activities' => [
                        'en' => ['Guided Coral Snorkeling', 'Island Beach Time', 'Fresh Lunch Buffet', 'Hotel Drop-off'],
                        'ar' => ['سنوركلينج الشعاب المرجانية', 'استرخاء على شاطئ الجزيرة', 'بوفيه غداء طازج', 'العودة للفندق'],
                        'fr' => ['Snorkeling guidé', 'Détente sur la plage', 'Déjeuner buffet', 'Retour à l\'hôtel'],
                        'de' => ['Geführtes Schnorcheln', 'Strandaufenthalt', 'Mittagsbuffet', 'Hoteltransfer'],
                    ]
                ],
            ];
        }

        // Generic high-quality day tour stops fallback
        return [
            [
                'start_time' => '08:30:00',
                'end_time' => '12:00:00',
                'duration' => '3.5 Hours',
                'title' => [
                    'en' => 'Morning Sightseeing & Guided Landmark Tour',
                    'ar' => 'الجولة الصباحية واستكشاف أبرز المعالم مع المرشد',
                    'fr' => 'Visite matinale et découverte des sites incontournables',
                    'de' => 'Morgendliche Besichtigung der wichtigsten Höhepunkte',
                ],
                'description' => [
                    'en' => "<p>Begin your day with prompt hotel pickup in a private, air-conditioned vehicle. Accompanied by your professional Egyptologist guide, explore the major historical landmarks and cultural highlights of the destination.</p>",
                    'ar' => "<p>ابدأ يومك بالانطلاق من الفندق بسيارة خاصة ومكيفة برفقة مرشدك السياحي المتخصص. استكشف أهم المعالم التاريخية والثقافية واستمتع بشرح شامل ومفصل.</p>",
                    'fr' => "<p>Commencez votre journée par une prise en charge à votre hôtel. Accompagné de votre guide égyptologue professionnel, explorez les monuments historiques emblématiques.</p>",
                    'de' => "<p>Beginnen Sie Ihren Tag mit der Abholung vom Hotel in einem privaten klimatisierten Fahrzeug und entdecken Sie mit Ihrem Ägyptologen die wichtigsten historischen Sehenswürdigkeiten.</p>",
                ],
                'activities' => [
                    'en' => ['Hotel Pickup', 'Guided Landmark Exploration', 'Historical Commentary'],
                    'ar' => ['الاستقبال من الفندق', 'جولة إرشادية في المعالم', 'شرح تاريخي متخصص'],
                    'fr' => ['Prise en charge hôtel', 'Visite guidée des monuments', 'Explications historiques'],
                    'de' => ['Hotelabholung', 'Geführte Besichtigung', 'Historische Erläuterungen'],
                ]
            ],
            [
                'start_time' => '12:30:00',
                'end_time' => '16:00:00',
                'duration' => '3.5 Hours',
                'title' => [
                    'en' => 'Afternoon Highlights, Leisure & Return Transfer',
                    'ar' => 'استكمال الجولة، وقت حر والتوصيل للفندق',
                    'fr' => 'Découvertes de l\'après-midi, temps libre et retour',
                    'de' => 'Nachmittags-Highlights, Freizeit und Rücktransfer',
                ],
                'description' => [
                    'en' => "<p>Continue discovering the destination's unique wonders, capture memorable photographs at prime viewpoints, and enjoy comfortable drop-off back at your hotel or cruise ship.</p>",
                    'ar' => "<p>استكمل استكشاف المعالم الفريدة للمنطقة والتقط أجمل الصور التذكارية من أفضل المواقع البانورامية، قبل العودة والاسترخاء في فندقك أو مكان إقامتك.</p>",
                    'fr' => "<p>Poursuivez la visite des sites clés, profitez d'arrêts photos panoramiques puis bénéficiez d'un retour confortable à votre hôtel.</p>",
                    'de' => "<p>Erleben Sie weitere Höhepunkte der Tour, nutzen Sie Zeit für Panoramafotos und genießen Sie die entspannte Rückfahrt zu Ihrem Hotel.</p>",
                ],
                'activities' => [
                    'en' => ['Scenic Photo Stops', 'Local Cultural Insights', 'Hotel Drop-off'],
                    'ar' => ['وقفات تصوير تذكارية', 'التعرف على الثقافة المحلية', 'التوصيل للفندق'],
                    'fr' => ['Arrêts photos panoramiques', 'Culture locale', 'Retour à l\'hôtel'],
                    'de' => ['Fotostopps', 'Kulturelle Einblicke', 'Rücktransfer zum Hotel'],
                ]
            ],
        ];
    }
}
