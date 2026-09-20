<?php

namespace Database\Seeders;

use App\Models\Attraction;
use Illuminate\Database\Seeder;

class AttractionEnricherSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            'ben-ezra-synagogue' => [
                'city_id' => 1,
                'name' => ['en' => 'Ben Ezra Synagogue', 'ar' => 'معبد بن عزرا اليهودي'],
                'opening_hours' => ['en' => '9:00 AM - 4:30 PM', 'ar' => '9:00 ص - 4:30 م'],
                'short_description' => [
                    'en' => 'The oldest Jewish synagogue in Cairo, famous for the historic Cairo Genizah discovery and its rich heritage in Coptic Cairo.',
                    'ar' => 'أقدم كنيس يهودي في القاهرة، واشتهر باكتشاف وثائق الجنيزة التاريخية وموقعه الفريد في مجمع الأديان.'
                ],
                'description' => [
                    'en' => '<p class="lead">The Ben Ezra Synagogue, nestled in the heart of Old Cairo\'s Religious Complex, is one of Egypt\'s oldest and most historically significant Jewish places of worship. Famed worldwide for the historic discovery of the Cairo Genizah, this architectural treasure stands as a testament to Cairo\'s rich multi-faith heritage.</p>

<h3>History & Sacred Origins</h3>
<p>According to local tradition, the synagogue stands on the site where baby Moses was found floating in a basket among the reeds of the Nile by Pharaoh\'s daughter. Originally constructed in the 4th century AD as the Coptic Christian Church of St. Michael, the property was sold in 882 AD to Abraham Ben Ezra, who converted it into a synagogue. Over the centuries, it underwent several restorations, most notably during the Mamluk and Ottoman eras, and was recently meticulously restored by the Egyptian Ministry of Tourism and Antiquities.</p>

<h3>The Discovery of the Cairo Genizah</h3>
<p>The Ben Ezra Synagogue achieved international renown in the late 19th century following the discovery of the <strong>Cairo Genizah</strong>—a hidden storeroom containing over 300,000 manuscript fragments spanning more than a millennium (870 AD to 1880s). These documents, written in Hebrew, Arabic, and Aramaic, provided unparalleled historical insights into medieval Mediterranean trade, daily life, philosophy, and interfaith relations.</p>

<h3>Architectural Highlights</h3>
<ul>
  <li><strong>Basilica Design:</strong> Designed in a two-story basilica style with marble columns and two rows of arches.</li>
  <li><strong>Intricate Decoration:</strong> Adorned with exquisite Turkish-style geometric patterns, floral wood carvings, and mother-of-pearl inlays.</li>
  <li><strong>The Bema & Torah Ark:</strong> Features a central marble pulpit (Bema) and a beautifully carved wooden Torah Ark facing Jerusalem.</li>
</ul>

<h3>Visitor Tips & Location</h3>
<p>Located within the walled Coptic Cairo enclosure, the synagogue is surrounded by iconic monuments including the Hanging Church, the Church of St. Sergius and Bacchus, and the Fortress of Babylon. Entry is accessible as part of your historic Old Cairo tour.</p>',
                    'ar' => '<p class="lead">يُعد كنيس معبد بن عزرا (Ben Ezra Synagogue)، الواقع في قلب مجمع الأديان بمصر القديمة، أحد أقدم وأهم المعالم اليهودية التاريخية في مصر والعالم. اشتهر عالمياً باكتشاف وثائق "الجنيزة" التاريخية، ويثبت مكانته كشاهد على التنوع الثقافي والديني في تاريخ القاهرة.</p>

<h3>التاريخ والأصول المقدسة</h3>
<p>وفقاً للروايات التاريخية المحلية، يقع المعبد في البقعة التي عُثر فيها على طفل النبي موسى عليه السلام على ضفاف النيل. بُني المكان في القرن الرابع الميلادي ككنيسة قبطية باسم كنيسة القديس ميخائيل، ثم بيع في عام 882 م إلى أبراهام بن عزرا، والذي حوله إلى كنيس يهودي. على مر القرون خضع المعبد لعدة ترميمات وتجديدات، آخرها مشروع الترميم الشامل الذي قامت به وزارة السياحة والآثار المصرية.</p>

<h3>كنوز وثائق "الجنيزة"</h3>
<p>نال معبد بن عزرا شهرة عالمية استثنائية في نهاية القرن التاسع عشر بعد اكتشاف مخزن "الجنيزة" الملحق بالمعبد، والذي ضم أكثر من 300,000 وثيقة ومخطوطة نادرة يرجع تاريخها من عام 870 م وحتى القرن التاسع عشر. شملت الوثائق عقود تجارية ورسائل شخصية ومخطوطات فلسفية ودينية أعطت المؤرخين رؤية فريدة عن الحياة الاجتماعية والاقتصادية في العصور الوسطى.</p>

<h3>العمارة والتصميم الفني</h3>
<ul>
  <li><strong>التخطيط البازيليكي:</strong> مصمم على الطراز البازيليكي المكون من طابقين مع أعمدة رخامية وأقواس متناسقة.</li>
  <li><strong>الزخارف الرفيعة:</strong> يتميز بسقف خشبي مزين بزخارف هندسية ونباتية مطعمة بالصدف والعاج على الطراز العثماني والأندلسي.</li>
  <li><strong>منبر المنبار وهيكل التوراة:</strong> يحتوي على منبر رخامي في المنتصف مع هيكل خشبي منقوش بعناية لحفظ أسفار التوراة.</li>
</ul>

<h3>معلومات الزيارة والتجول</h3>
<p>يقع المعبد داخل حصن بابليون بمجمع الأديان بالقاهرة القديمة، بجوار الكنيسة المعلقة وكنيسة أبي سرجة. الزيارة مجهزة ومتاحة للزوار يومياً.</p>'
                ]
            ],
            'abydos-temple' => [
                'city_id' => 2,
                'name' => ['en' => 'Abydos Temple of Seti I', 'ar' => 'معبد أبيدوس (سيتي الأول)'],
                'opening_hours' => ['en' => '6:00 AM - 5:00 PM', 'ar' => '6:00 ص - 5:00 م'],
                'short_description' => [
                    'en' => 'One of Egypt\'s most holy ancient sites, renowned for the Osireion, breathtaking wall reliefs, and the famous Abydos King List.',
                    'ar' => 'أحد أقدس المواقع الفرعونية في مصر، والمشهور بنقوشه الملونة الاستثنائية وقائمة ملوك أبيدوس التاريخية.'
                ],
                'description' => [
                    'en' => '<p class="lead">Abydos is one of the most important archaeological sites in Upper Egypt, sacred to the god Osiris, lord of the underworld. The Temple of Seti I stands out for having some of the finest, most delicate carved reliefs surviving from the New Kingdom.</p>
<h3>Historical Significance</h3>
<p>Abydos served as the principal cult center for Osiris. Ancient Egyptians believed that burial or pilgrimage to Abydos ensured resurrection in the afterlife. The temple houses the famous <strong>Abydos King List</strong>, a chronological list of 76 pharaohs that provided modern Egyptologists with invaluable historical chronology.</p>
<h3>Architectural Highlights</h3>
<ul>
  <li><strong>Hypostyle Halls:</strong> Magnificent halls supported by carved columns depicting Pharaoh Seti I offering to various deities.</li>
  <li><strong>Seven Chapels:</strong> Dedicated to Osiris, Isis, Horus, Amun-Ra, Ra-Horakhty, Ptah, and Seti I himself.</li>
  <li><strong>The Osireion:</strong> A subterranean structure located behind the main temple, built to simulate an island surrounded by water.</li>
</ul>',
                    'ar' => '<p class="lead">تُعتبر أبيدوس واحدة من أهم المناطق الأثرية في مصر العليا، وكانت المركز الرئيسي لعبادة الإله أوزيريس إله العالم الآخر. يتميز معبد الملك سيتي الأول بنقوشه الجدارية البارزة والدقيقة التي تُعد أجمل ما أنجبته الفنون المصرية في الدولة الحديثة.</p>
<h3>الأهمية التاريخية والدينية</h3>
<p>كانت أبيدوس مقصد الحج المقدس للمصريين القدماء. يحتوي المعبد على <strong>قائمة ملوك أبيدوس</strong> الشهيرة، وهي لوحة جدارية تضم أسماء 76 ملكاً فرعونياً ساهمت بشكل رئيسي في ترتيب الأسرة المصرية القديمة.</p>
<h3>أبرز معالم المعبد</h3>
<ul>
  <li><strong>صالة الأعمدة:</strong> صالات عملاقة تحتوي على أعمدة مزخرفة بنقوش زاهية تُظهر الملك سيتي الأول يقدم القرابين للآلهة.</li>
  <li><strong>المقصورات السبع:</strong> مقصورات مخصصة لآلهة مصر الكبرى: أوزيريس، إيزيس، حورس، آمون رع، رع حور أختي، وبتاح، ومقصورة الملك.</li>
  <li><strong>الأوزيريون:</strong> مبنى غائر خلف المعبد شُيد ليمثل جزيرة أسطورية محاطة بالمياه.</li>
</ul>'
                ]
            ],
            'temple-at-dendara' => [
                'city_id' => 2,
                'name' => ['en' => 'Dendera Temple of Hathor', 'ar' => 'معبد دندرة للآلهة حتحور'],
                'opening_hours' => ['en' => '7:00 AM - 5:00 PM', 'ar' => '7:00 ص - 5:00 م'],
                'short_description' => [
                    'en' => 'A remarkably preserved temple complex dedicated to Hathor, goddess of love and joy, featuring vivid blue ceiling astronomical reliefs.',
                    'ar' => 'أحد أفضل المعابد الفرعونية احتفاظاً بألوانها وسقفها، والمكرس للآلهة حتحور إلهة الحب والجمال.'
                ],
                'description' => [
                    'en' => '<p class="lead">Located north of Luxor near Qena, the Dendera Temple Complex is renowned as one of the best-preserved temple structures in Egypt. Dedicated to Hathor, goddess of maternal care, music, and joy, the temple stuns visitors with its rich blue painted ceilings and intricate relief carving.</p>
<h3>Key Highlights</h3>
<ul>
  <li><strong>Hathor Columned Hall:</strong> Supported by 24 massive columns shaped with the face of Goddess Hathor.</li>
  <li><strong>Astronomical Ceilings:</strong> Vibrant wall paintings portraying the signs of the zodiac, constellations, and the goddess Nut stretching across the sky.</li>
  <li><strong>Crypts & Crypt Reliefs:</strong> Secret underground chambers decorated with mystifying relief panels.</li>
  <li><strong>Roof Shrine:</strong> Offering panoramic views of the surrounding countryside and the site of the famous replica of the Dendera Zodiac.</li>
</ul>',
                    'ar' => '<p class="lead">يقع معبد دندرة شمال الأقصر قرب قنا، وهو أحد أروع المعابد المصرية القديمة المحتفظة بسقفها وألوانها الأصلية. كُرّس المعبد لعبادة الإلهة حتحور إلهة الحب والموسيقى والجمال، ويبهر الزوار بألوانه الزرقاء الخلابة.</p>
<h3>أبرز المعالم والتفاصيل</h3>
<ul>
  <li><strong>صالة أعمدة حتحور:</strong> تضم 24 عموداً ضخماً تعلوها رؤوس الإلهة حتحور ذات الأذنين البقريتين.</li>
  <li><strong>النقوش الفلكية بالسقف:</strong> سقوف مزخرفة بألوان زاهية تمثل أبراج البروج السماوية والإلهة نوت إلهة السماء.</li>
  <li><strong>السراديب المخبأة:</strong> غرف سرية تحت الأرض تتميز بنقوشها الدقيقة النادرة.</li>
  <li><strong>سطح المعبد:</strong> يوفر إطلالة رائعة على النيل والنواحي المحيطة، وكان يضم دائرة البروج السماوية الشهيرة.</li>
</ul>'
                ]
            ],
            'the-nile-valley' => [
                'city_id' => 3,
                'name' => ['en' => 'The Nile Valley Experience', 'ar' => 'تجربة وادي النيل'],
                'opening_hours' => ['en' => 'Open 24/7', 'ar' => 'متاح 24 ساعة'],
                'short_description' => [
                    'en' => 'Discover the beating heart of Egypt on a journey along the Nile River, passing ancient temples, fertile banks, and traditional feluccas.',
                    'ar' => 'استكشف شريان الحياة في مصر عبر رحلة في وادي النيل بين المعابد الخالدة والطبيعة الخضراء وتجارب الفلوكة.'
                ],
                'description' => [
                    'en' => '<p class="lead">The Nile Valley is the cradle of Egyptian civilization. Stretching along the banks of the world\'s longest river, this fertile corridor connects Cairo, Luxor, and Aswan, offering unmatched cultural and natural beauty.</p>
<h3>Why Explore the Nile Valley?</h3>
<p>Sailing the Nile Valley on a luxury river cruise or a traditional wooden felucca provides front-row seats to thousands of years of human history. Watch timeless scenes of rural life pass by as you journey between iconic monuments.</p>',
                    'ar' => '<p class="lead">يعتبر وادي النيل مهد الحضارة الإنسانية. يمتد الشريط الأخضر الخصيب على ضفاف أطول نهر في العالم، ليحيي المدن العريقة مثل القاهرة والأقصر وأسوان ويقدم تجربة سياحية لا تُنسى.</p>
<h3>لماذا تخوض رحلة وادي النيل؟</h3>
<p>الإبحار في وادي النيل على متن نايل كروز أو فلوكة خشبية تقليدية يتيح لك مشاهدة أروع المناظر الطبيعية والآثار الفرعونية الخالدة على ضفاف النيل.</p>'
                ]
            ],
            'qasr-ibrim' => [
                'city_id' => 7,
                'name' => ['en' => 'Qasr Ibrim Citadel', 'ar' => 'حصن قصر إبريم'],
                'opening_hours' => ['en' => 'Lake Nasser Cruises', 'ar' => 'رحلات بحيرة ناصر'],
                'short_description' => [
                    'en' => 'An island fortress in Lake Nasser holding thousands of years of Nubian, Pharaonic, Roman, and Christian history.',
                    'ar' => 'جزيرة وأثري تاريخي مجيد ببحيرة ناصر يعكس حضارات النوبة والفرعونية والرومانية والقبطية.'
                ],
                'description' => [
                    'en' => '<p class="lead">Qasr Ibrim is a major archaeological site in Lower Nubia. Once a formidable hilltop fortress overlooking the Nile, the creation of Lake Nasser transformed it into a dramatic island citadel.</p>
<h3>Historical Legacy</h3>
<p>Occupied continuously from the New Kingdom through the Ottoman era, Qasr Ibrim preserves remains of Egyptian temples, Roman fortifications, a Christian cathedral, and Islamic dwellings.</p>',
                    'ar' => '<p class="lead">يُعد قصر إبريم موقعاً أثرياً فريداً في النوبة السفلية. كان حصناً منيعاً يتوج تلة مطلة على النيل، وبعد بناء السد العالي وتكون بحيرة ناصر تحول إلى جزيرة تاريخية ساحرة.</p>
<h3>التراث التاريخي والمعماري</h3>
<p>تعاقبت عليه الحضارات من الدولة الحديثة والرومان والقبطية وحتى العصر العثماني، ولا يزال يحتفظ ببقايا كاتدرائية قبطية ومعابد فرعونية.</p>'
                ]
            ],
            'solar-boat-museum' => [
                'city_id' => 1,
                'name' => ['en' => 'Solar Boat of Khufu', 'ar' => 'مركب الشمس للملك خوفو'],
                'opening_hours' => ['en' => 'GEM Exhibition Hours', 'ar' => 'ساعات عرض المتحف الكبير'],
                'short_description' => [
                    'en' => 'The ancient cedarwood funeral barge of Pharaoh Khufu, constructed over 4,500 years ago to carry his soul across the heavens.',
                    'ar' => 'مركب الملك خوفو الخشبي الأثري، البالغ عمره أكثر من 4500 عام والمصمم لنقل روح الملك في رحلته الأبدية.'
                ],
                'description' => [
                    'en' => '<p class="lead">The Khufu Solar Boat is an intact full-sized vessel from Ancient Egypt that was sealed into a pit at the foot of the Great Pyramid of Giza around 2500 BC. Made of Lebanese cedar, it is one of the oldest, largest, and best-preserved vessels from antiquity.</p>
<h3>Symbolism & Engineering</h3>
<p>Solar boats played a ritual role in Egyptian mythology, symbolic of the sun god Ra\'s journey across the sky. The boat was assembled without a single nail, held together by wooden pegs and ropes.</p>',
                    'ar' => '<p class="lead">تعتبر مركب الشمس للملك خوفو أقدم وأكبر وأكمل سفينة خشبية عُثر عليها من العالم القديم. تم دفنها بجوار الهرم الأكبر بالجيزة عام 2500 قبل الميلاد تقريباً مصنوعة من أرز لبنان.</p>
<h3>الهندسة والرمزية</h3>
<p>ترتبط مراكب الشمس برحلة إله الشمس "رع" عبر السماء في الأسطورة المصرية. تم تجميع المركب بدون حبة مسمار واحدة باستخدام الأوتاد الخشبية والحبال.'
                ]
            ],
        ];

        foreach ($data as $slug => $item) {
            $attraction = Attraction::where('slug', $slug)->first();
            if ($attraction) {
                $attraction->city_id = $item['city_id'];
                if (isset($item['name'])) $attraction->name = $item['name'];
                if (isset($item['opening_hours'])) $attraction->opening_hours = $item['opening_hours'];
                if (isset($item['short_description'])) $attraction->short_description = $item['short_description'];
                if (isset($item['description'])) $attraction->description = $item['description'];
                $attraction->save();
            }
        }
    }
}
