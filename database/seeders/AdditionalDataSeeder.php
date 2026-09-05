<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Itinerary;
use App\Models\CruiseSchedule;
use App\Models\TourGuide;
use App\Models\Transfer;
use Illuminate\Support\Facades\DB;

class AdditionalDataSeeder extends Seeder
{
    /**
     * تشغيل Seeder للبيانات الإضافية
     */
    public function run()
    {
        $this->command->info('🚀 Starting additional data import...');
        
        DB::beginTransaction();
        
        try {
            // 1. إنشاء مسارات الرحلات (Itineraries)
            $this->createItineraries();
            
            // 2. إنشاء جداول الإبحار (Cruise Schedules)
            $this->createCruiseSchedules();
            
            // 3. إنشاء المرشدين السياحيين (Tour Guides)
            $this->createTourGuides();
            
            // 4. إنشاء وسائل النقل (Transfers)
            $this->createTransfers();
            
            DB::commit();
            $this->command->info('✅ Additional data import completed successfully!');
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('❌ Failed to import additional data: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * إنشاء مسارات الرحلات
     */
    private function createItineraries()
    {
        $itineraries = [
            // Classical Egypt Tour - 8 Days (Tour ID: 1)
            [
                'tour_id' => 1,
                'day_number' => 1,
                'title' => 'Arrival in Cairo',
                'description' => 'Arrive at Cairo International Airport, meet and assist, transfer to hotel.',
                'activities' => 'Airport transfer, hotel check-in',
                'meals_included' => 'none',
                'overnight_location' => 'Cairo',
                'accommodation_type' => 'hotel',
            ],
            [
                'tour_id' => 1,
                'day_number' => 2,
                'title' => 'Pyramids and Egyptian Museum',
                'description' => 'Visit the Pyramids of Giza, Sphinx, and Egyptian Museum.',
                'activities' => 'Guided tour, entrance fees included',
                'meals_included' => 'breakfast',
                'overnight_location' => 'Cairo',
                'accommodation_type' => 'hotel',
            ],
            [
                'tour_id' => 1,
                'day_number' => 3,
                'title' => 'Fly to Luxor, Karnak Temple',
                'description' => 'Morning flight to Luxor, visit Karnak Temple.',
                'activities' => 'Domestic flight, guided tour',
                'meals_included' => 'breakfast',
                'overnight_location' => 'Luxor',
                'accommodation_type' => 'hotel',
            ],
            [
                'tour_id' => 1,
                'day_number' => 4,
                'title' => 'Valley of the Kings',
                'description' => 'Visit Valley of the Kings, Temple of Hatshepsut, Colossi of Memnon.',
                'activities' => 'Guided tour, entrance fees',
                'meals_included' => 'breakfast',
                'overnight_location' => 'Luxor',
                'accommodation_type' => 'hotel',
            ],
            
            // Nile Cruise 5 Days (Tour ID: 3)
            [
                'tour_id' => 3,
                'day_number' => 1,
                'title' => 'Embarkation in Luxor',
                'description' => 'Board the cruise ship, visit Karnak and Luxor Temples.',
                'activities' => 'Temple visits, boarding',
                'meals_included' => 'lunch',
                'overnight_location' => 'Luxor',
                'accommodation_type' => 'cruise',
            ],
            [
                'tour_id' => 3,
                'day_number' => 2,
                'title' => 'West Bank of Luxor',
                'description' => 'Visit Valley of the Kings, Temple of Hatshepsut.',
                'activities' => 'Guided tour, entrance fees',
                'meals_included' => 'all',
                'overnight_location' => 'Edfu',
                'accommodation_type' => 'cruise',
            ],
            [
                'tour_id' => 3,
                'day_number' => 3,
                'title' => 'Edfu and Kom Ombo',
                'description' => 'Visit Edfu Temple, sail to Kom Ombo, visit Kom Ombo Temple.',
                'activities' => 'Temple visits, sailing',
                'meals_included' => 'all',
                'overnight_location' => 'Kom Ombo',
                'accommodation_type' => 'cruise',
            ],
        ];
        
        foreach ($itineraries as $itineraryData) {
            Itinerary::create($itineraryData);
            $this->command->info("  ✅ Created itinerary for tour {$itineraryData['tour_id']} - Day {$itineraryData['day_number']}");
        }
    }

    /**
     * إنشاء جداول الإبحار
     */
    private function createCruiseSchedules()
    {
        $schedules = [];
        
        // إنشاء جدول إبحار للـ 6 أشهر القادمة
        for ($i = 0; $i < 6; $i++) {
            $month = now()->addMonths($i);
            
            // MS Nile Premium (Cruise ID: 1)
            $schedules[] = [
                'cruise_id' => 1,
                'departure_date' => $month->copy()->startOfMonth()->addDays(5),
                'end_date' => $month->copy()->startOfMonth()->addDays(10),
                'direction' => 'luxor_to_aswan',
                'available_cabins' => 35,
                'total_cabins' => 45,
                'is_full' => false,
            ];
            
            $schedules[] = [
                'cruise_id' => 1,
                'departure_date' => $month->copy()->startOfMonth()->addDays(15),
                'end_date' => $month->copy()->startOfMonth()->addDays(20),
                'direction' => 'aswan_to_luxor',
                'available_cabins' => 40,
                'total_cabins' => 45,
                'is_full' => false,
            ];
            
            // MS Royal Princess (Cruise ID: 2)
            $schedules[] = [
                'cruise_id' => 2,
                'departure_date' => $month->copy()->startOfMonth()->addDays(10),
                'end_date' => $month->copy()->startOfMonth()->addDays(15),
                'direction' => 'luxor_to_aswan',
                'available_cabins' => 45,
                'total_cabins' => 52,
                'is_full' => false,
            ];
        }
        
        foreach ($schedules as $scheduleData) {
            // تحقق من عدم وجود جدول مكرر
            $existing = CruiseSchedule::where('cruise_id', $scheduleData['cruise_id'])
                ->where('departure_date', $scheduleData['departure_date'])
                ->where('direction', $scheduleData['direction'])
                ->first();
            
            if (!$existing) {
                CruiseSchedule::create($scheduleData);
                $this->command->info("  ✅ Created schedule for cruise {$scheduleData['cruise_id']} on {$scheduleData['departure_date']}");
            }
        }
    }

    /**
     * إنشاء المرشدين السياحيين
     */
    private function createTourGuides()
    {
        $guides = [
            [
                'name' => 'Ahmed Hassan',
                'email' => 'ahmed.guide@example.com',
                'phone' => '+201023456789',
                'languages' => json_encode(['English', 'Arabic', 'French']),
                'specialization' => json_encode(['Ancient Egypt', 'Archaeology', 'Nile History']),
                'experience_years' => 15,
                'license_number' => 'EGG-2023-001',
                'license_expiry' => '2025-12-31',
                'bio' => 'Professional tour guide with 15 years experience in Egyptology.',
                'hourly_rate' => 50.00,
                'daily_rate' => 300.00,
                'destinations' => json_encode(['Luxor', 'Aswan', 'Cairo']),
                'rating' => 4.8,
                'total_reviews' => 127,
                'active' => true,
            ],
            [
                'name' => 'Fatima Mahmoud',
                'email' => 'fatima.guide@example.com',
                'phone' => '+201098765432',
                'languages' => json_encode(['English', 'Arabic', 'Spanish', 'German']),
                'specialization' => json_encode(['Islamic History', 'Coptic Egypt', 'Modern Egypt']),
                'experience_years' => 8,
                'license_number' => 'EGG-2023-002',
                'license_expiry' => '2025-12-31',
                'bio' => 'Multilingual guide specializing in cultural and religious sites.',
                'hourly_rate' => 45.00,
                'daily_rate' => 280.00,
                'destinations' => json_encode(['Cairo', 'Alexandria']),
                'rating' => 4.9,
                'total_reviews' => 89,
                'active' => true,
            ],
            [
                'name' => 'Mohamed Ali',
                'email' => 'mohamed.guide@example.com',
                'phone' => '+201077777777',
                'languages' => json_encode(['English', 'Arabic', 'Italian']),
                'specialization' => json_encode(['Nile Cruises', 'Desert Tours', 'Photography Tours']),
                'experience_years' => 12,
                'license_number' => 'EGG-2023-003',
                'license_expiry' => '2025-12-31',
                'bio' => 'Expert in Nile cruise tours and desert expeditions.',
                'hourly_rate' => 55.00,
                'daily_rate' => 320.00,
                'destinations' => json_encode(['Luxor', 'Aswan', 'Western Desert']),
                'rating' => 4.7,
                'total_reviews' => 156,
                'active' => true,
            ],
        ];
        
        foreach ($guides as $guideData) {
            TourGuide::create($guideData);
            $this->command->info("  ✅ Created tour guide: {$guideData['name']}");
        }
    }

    /**
     * إنشاء وسائل النقل
     */
    private function createTransfers()
    {
        $transfers = [
            [
                'name' => 'Cairo Airport to Hotel',
                'transfer_type' => 'airport',
                'vehicle_type' => 'sedan',
                'capacity' => 3,
                'from_location' => 'Cairo International Airport',
                'to_location' => 'Cairo Hotel',
                'approximate_duration_minutes' => 45,
                'description' => 'Private transfer from Cairo Airport to your hotel in Cairo.',
                'included_services' => 'Meet and greet, luggage assistance, bottled water',
                'base_price' => 35.00,
                'price_per_person' => 0.00,
                'active' => true,
            ],
            [
                'name' => 'Luxor Airport to Nile Cruise',
                'transfer_type' => 'airport',
                'vehicle_type' => 'van',
                'capacity' => 8,
                'from_location' => 'Luxor International Airport',
                'to_location' => 'Nile Cruise Dock',
                'approximate_duration_minutes' => 30,
                'description' => 'Group transfer from Luxor Airport to Nile Cruise ship.',
                'included_services' => 'Group transfer, luggage handling',
                'base_price' => 50.00,
                'price_per_person' => 15.00,
                'active' => true,
            ],
            [
                'name' => 'Private Tour Van',
                'transfer_type' => 'private',
                'vehicle_type' => 'van',
                'capacity' => 12,
                'from_location' => 'Hotel',
                'to_location' => 'Tour Sites',
                'approximate_duration_minutes' => 480,
                'description' => 'Full day private van with driver for tour groups.',
                'included_services' => 'Driver, fuel, parking fees',
                'base_price' => 120.00,
                'price_per_person' => 0.00,
                'active' => true,
            ],
            [
                'name' => 'Hotel to Train Station',
                'transfer_type' => 'hotel',
                'vehicle_type' => 'sedan',
                'capacity' => 3,
                'from_location' => 'Cairo Hotel',
                'to_location' => 'Cairo Railway Station',
                'approximate_duration_minutes' => 30,
                'description' => 'Transfer from hotel to train station.',
                'included_services' => 'Luggage assistance',
                'base_price' => 25.00,
                'price_per_person' => 0.00,
                'active' => true,
            ],
        ];
        
        foreach ($transfers as $transferData) {
            Transfer::create($transferData);
            $this->command->info("  ✅ Created transfer: {$transferData['name']}");
        }
    }
}