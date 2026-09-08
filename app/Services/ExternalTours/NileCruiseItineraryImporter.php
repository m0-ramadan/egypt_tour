<?php

namespace App\Services\ExternalTours;

use App\Models\Package;

class NileCruiseItineraryImporter
{
    public function sync(Package $package, array $itinerary): bool
    {
        if ($package->package_type !== 'nile_cruise' || empty($itinerary[0]['program'])) {
            return false;
        }

        $programs = collect($itinerary)->groupBy('program.id');
        // Validate all alternatives before replacing any stored itinerary.
        foreach ($programs as $days) {
            $program = $days->first()['program'];
            if (!$program || $days->pluck('day_number')->all() !== range(1, $program['days'])) {
                throw new \RuntimeException('Incomplete cruise program; existing itinerary was preserved.');
            }
        }

        foreach ($programs->values() as $index => $days) {
            $program = $days->first()['program'];
            $duration = $package->nileCruiseDurations()->firstOrNew([
                'title' => $program['title'],
                'days' => $program['days'],
                'nights' => $program['nights'],
                'departure_day' => $program['departure_day'],
            ]);
            $duration->fill([
                'title' => $program['title'],
                'is_active' => true,
                'sort_order' => $index + 1,
            ]);
            if (!$duration->exists) {
                $duration->is_default = !$package->nileCruiseDurations()->where('is_default', true)->exists();
            }
            $duration->save();
            $duration->itineraryDays()->delete();

            foreach ($days as $day) {
                $duration->itineraryDays()->create([
                    'day_number' => $day['day_number'],
                    'title' => ['en' => $day['title'], 'ar' => ''],
                    'description' => ['en' => $day['description'], 'ar' => ''],
                    'meals' => $day['meals'] ?? [],
                    'overnight' => ['en' => $day['overnight_location'] ?? '', 'ar' => ''],
                    'sort_order' => $day['day_number'],
                ]);
            }
        }

        // The generic itinerary cannot represent alternative cruise programs.
        $package->itineraries()->delete();

        return true;
    }
}
