<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LegacyImport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'legacy:import
                            {--dry-run : Preview only. Never writes to MySQL.}
                            {--yes : Skip the confirm() prompt in write mode.}
                            {--keep-inactive : Import records even if legacy is_active=false (default: respect legacy flag).}
                            {--wipe-tours : Wipe existing packages, categories, itineraries, prices, inclusions, and bookings before importing.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import legacy Rails/PostgreSQL data into the Laravel MySQL database via the legacy_pgsql connection.';

    protected const CONN = 'legacy_pgsql';
    protected const MAP_TABLE = 'legacy_import_maps';

    protected array $stats = [];
    protected array $warnings = [];
    protected array $duplicates = [];
    protected array $unsupported = [];
    protected array $conflicts = [];
    protected array $missingRequired = [];

    protected array $targetIds = [
        'packages' => [],
        'itineraries' => [],
        'attractions' => [],
        'articles' => [],
        'article_categories' => [],
        'package_categories' => [],
        'pages' => [],
    ];

    protected int $packageNextId = 1;
    protected int $itineraryNextId = 1;
    protected int $attractionNextId = 1;
    protected int $articleNextId = 1;
    protected int $articleCategoryNextId = 1;
    protected int $packageCategoryNextId = 1;
    protected int $pageNextId = 1;
    protected array $itineraryDays = [];

    public function handle(): int
    {
        $default = config('database.default');
        if ($default !== 'mysql') {
            $this->error('Safety guard: default connection is "' . $default . '", expected "mysql". Aborting.');

            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');

        try {
            DB::connection(self::CONN)->select('SELECT 1');
        } catch (\Throwable $e) {
            $this->error('Cannot connect to legacy_pgsql: ' . $e->getMessage());

            return self::FAILURE;
        }

        if ($dryRun) {
            $this->warn('DRY-RUN MODE: nothing will be written to MySQL.');
        } else {
            $this->line('Write mode. Target: ' . DB::connection('mysql')->getDatabaseName());
            if (! $this->option('yes') && ! $this->confirm('Import into the MySQL database now?', false)) {
                $this->warn('Cancelled.');

                return self::SUCCESS;
            }
        }

        if (! $dryRun && $this->option('wipe-tours')) {
            $this->wipeToursAndBookings();
        } else {
            $this->loadExistingMaps();
        }

        $this->runImport($dryRun);
        $this->printReport();

        return self::SUCCESS;
    }

    protected function wipeToursAndBookings(): void
    {
        $this->info('Wiping existing packages, categories, itineraries, inclusions, prices, cities links, tag links, and bookings from MySQL...');

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('booking_travelers')->truncate();
        DB::table('booking_items')->truncate();
        DB::table('bookings')->truncate();
        DB::table('inquiries')->truncate();
        DB::table('package_tag_items')->truncate();
        DB::table('package_cities')->truncate();
        DB::table('package_inclusions')->truncate();
        DB::table('package_prices')->truncate();
        DB::table('itineraries')->truncate();
        DB::table('packages')->truncate();
        DB::table('package_categories')->truncate();
        DB::table(self::MAP_TABLE)->whereIn('source_table', [
            'packages',
            'tours',
            'package_categories',
            'tour_categories',
            'package_itineraries',
            'tour_itineraries'
        ])->delete();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->info('Cleaned up tour and booking data completely.');
    }

    protected function loadExistingMaps(): void
    {
        if (! DB::getSchemaBuilder()->hasTable(self::MAP_TABLE)) {
            return;
        }

        $maps = DB::table(self::MAP_TABLE)->get();
        foreach ($maps as $m) {
            if ($m->target_table === 'packages') {
                $type = str_contains($m->source_table, 'tour') ? 'day_tour' : 'travel_package';
                $this->targetIds['packages'][$type . '_' . $m->source_id] = (int) $m->target_id;
            } elseif ($m->target_table === 'itineraries') {
                $this->targetIds['itineraries'][$m->source_table . '#' . $m->source_id] = (int) $m->target_id;
            } elseif ($m->target_table === 'package_categories') {
                $this->targetIds['package_categories_src']['public.' . $m->source_table][(int) $m->source_id] = (int) $m->target_id;
            }
        }
    }

    protected function runImport(bool $dryRun): void
    {
        $this->newLine();
        $this->info('=== Import plan execution ===');

        $this->importLanguages($dryRun);
        $this->importAdmins($dryRun);
        $this->importUsers($dryRun);
        $this->importCurrencies($dryRun);
        $this->importCountries($dryRun);
        $this->importCities($dryRun);
        $this->importPackageCategories($dryRun);
        $this->importArticleCategories($dryRun);
        $this->importTags($dryRun);
        $this->importAttractions($dryRun);
        $this->importPackages($dryRun);
        $this->importItineraries($dryRun);
        $this->importPackageCities($dryRun);
        $this->importPackageTags($dryRun);
        $this->importArticles($dryRun);
        $this->importSeoRedirects($dryRun);
        $this->importNewsletterSubscribers($dryRun);
        $this->importBlogComments($dryRun);
        $this->importResourceFaqs($dryRun);
        $this->importResourceReviews($dryRun);
        $this->importResourcePrices($dryRun);
        $this->importTestimonials($dryRun);
        $this->importPaymentMethods($dryRun);
        $this->importNotifications($dryRun);
        $this->importPages($dryRun);
        $this->importSeoMetas($dryRun);

        if (! $dryRun) {
            $this->persistMaps();
        }
    }

    protected function persistMaps(): void
    {
        $inserts = [];
        $seen = [];

        $this->collectMap('packages', $this->targetIds['packages'] ?? [], $inserts, $seen);
        $this->collectMap('itineraries', $this->targetIds['itineraries'] ?? [], $inserts, $seen);
        $this->collectMap('attractions', $this->targetIds['attractions'] ?? [], $inserts, $seen);
        $this->collectMap('articles', $this->targetIds['articles'] ?? [], $inserts, $seen);

        foreach (['article_categories_src', 'package_categories_src'] as $group) {
            foreach ($this->targetIds[$group] ?? [] as $fullTable => $ids) {
                $sourceTable = str_replace('public.', '', (string) $fullTable);
                foreach ($ids as $legacyId => $targetId) {
                    $key = $sourceTable . '#' . $legacyId;
                    if (isset($seen[$key])) {
                        continue;
                    }
                    $seen[$key] = true;
                    $inserts[] = [
                        'source_table' => $sourceTable,
                        'source_id' => $legacyId,
                        'target_table' => str_replace('_src', '', (string) $group),
                        'target_id' => $targetId,
                        'created_at' => now(),
                    ];
                }
            }
        }

        foreach ($inserts as $row) {
            DB::table(self::MAP_TABLE)->insertOrIgnore($row);
        }

        $this->info('Persisted ' . count($inserts) . ' legacy->target id mappings in ' . self::MAP_TABLE . '.');
    }

    protected function collectMap(string $targetTable, array $map, array &$inserts, array &$seen): void
    {
        if ($targetTable === '__slug_counts') {
            return;
        }

        foreach ($map as $key => $targetId) {
            if (! is_string($key)) {
                continue;
            }

            $sourceTable = null;
            $legacyId = null;

            if (str_starts_with($key, 'travel_package_')) {
                $sourceTable = 'packages';
                $legacyId = substr($key, strlen('travel_package_'));
            } elseif (str_starts_with($key, 'day_tour_')) {
                $sourceTable = 'tours';
                $legacyId = substr($key, strlen('day_tour_'));
            } elseif (str_starts_with($key, 'package_itineraries#')) {
                $sourceTable = 'package_itineraries';
                $legacyId = substr($key, strlen('package_itineraries#'));
            } elseif (str_starts_with($key, 'tour_itineraries#')) {
                $sourceTable = 'tour_itineraries';
                $legacyId = substr($key, strlen('tour_itineraries#'));
            } elseif (str_starts_with($key, 'attraction_') && ! str_starts_with($key, 'attractions_')) {
                $sourceTable = 'attractions';
                $legacyId = substr($key, strlen('attraction_'));
            } elseif (str_starts_with($key, 'sight_')) {
                $sourceTable = 'sights';
                $legacyId = substr($key, strlen('sight_'));
            } elseif (str_contains($key, '#')) {
                [$prefix, $id] = explode('#', $key, 2);
                $sourceTable = str_replace('public.', '', $prefix);
                $legacyId = $id;
            }

            if (! $sourceTable || $legacyId === null || $legacyId === '') {
                continue;
            }

            $lookup = $sourceTable . '#' . $legacyId;
            if (isset($seen[$lookup])) {
                continue;
            }
            $seen[$lookup] = true;

            $inserts[] = [
                'source_table' => $sourceTable,
                'source_id' => (int) $legacyId,
                'target_table' => $targetTable,
                'target_id' => (int) $targetId,
                'created_at' => now(),
            ];
        }
    }

    // ------------------------------------------------------------- helpers

    protected function lg(): \Illuminate\Database\Query\Builder
    {
        return DB::connection(self::CONN)->table('public.' . $this->lgTableName());
    }

    protected function lgTableName(): string
    {
        return str_replace('public.', '', 'public');
    }

    protected function pick(array $row, array $keys): mixed
    {
        foreach ($keys as $k) {
            if (isset($row[$k]) && $row[$k] !== null && $row[$k] !== '') {
                return $row[$k];
            }
        }

        return null;
    }

    protected function dec(mixed $v): ?float
    {
        if ($v === null || $v === '') {
            return null;
        }

        return (float) $v;
    }

    protected function boolVal(mixed $v): int
    {
        return $v ? 1 : 0;
    }

    protected function prop(mixed $obj, string $key): mixed
    {
        if (is_array($obj)) {
            return $obj[$key] ?? null;
        }

        return isset($obj->{$key}) ? $obj->{$key} : null;
    }

    protected function slugify(mixed $v, string $fallback, int $n): string
    {
        $s = Str::slug((string) ($v ?? ''));
        if ($s === '') {
            $s = $fallback . '-' . $n;
        }

        return $this->capText($s, 140);
    }

    protected function capText(?string $value, int $limit): ?string
    {
        if ($value === null) {
            return null;
        }

        return mb_strlen($value) > $limit ? mb_substr($value, 0, $limit) : $value;
    }

    protected function dedupe(string $slug, string $lookupKey): string
    {
        $key = $this->targetIds['__slug_counts'][$lookupKey][$slug] ?? 0;

        if ($key === 0) {
            $this->targetIds['__slug_counts'][$lookupKey][$slug] = 1;

            return $slug;
        }

        $this->targetIds['__slug_counts'][$lookupKey][$slug] = $key + 1;
        $new = $slug . '-' . ($key + 1);
        $this->duplicates[$lookupKey][] = "{$slug} -> {$new}";
        $this->targetIds['__slug_counts'][$lookupKey][$new] = 1;

        return $new;
    }

    protected function translations(string $table, string $fk, array $keys): array
    {
        $rows = DB::connection(self::CONN)
            ->table('public.' . $table)
            ->select(array_merge([$fk, 'locale'], $keys))
            ->get();

        $out = [];
        foreach ($rows as $row) {
            $out[(int) $row->{$fk}][(string) $row->locale] = (array) $row;
        }

        return $out;
    }

    protected function transJson(array $localeRows, array $keys): ?array
    {
        $result = [];
        foreach (['en', 'ar'] as $locale) {
            $row = $localeRows[$locale] ?? null;
            if (! $row) {
                continue;
            }
            $value = $this->pick($row, $keys);
            if ($value !== null && trim((string) $value) !== '') {
                $result[$locale] = $value;
            }
        }

        return $result ?: null;
    }

    protected function record(string $table, array $data, ?string $summary = null): void
    {
        $this->stats[$table]['inserted'] = ($this->stats[$table]['inserted'] ?? 0) + 1;
        if ($summary !== null) {
            $this->stats[$table]['summary'][] = $summary;
        }
    }

    protected function planned(string $table, int $delta): void
    {
        $this->stats[$table]['planned'] = ($this->stats[$table]['planned'] ?? 0) + $delta;
    }

    protected function skipped(string $table, string $reason, $summary): void
    {
        $this->stats[$table]['skipped'] = ($this->stats[$table]['skipped'] ?? 0) + 1;
        $this->stats[$table]['skip_reasons'][$reason] = ($this->stats[$table]['skip_reasons'][$reason] ?? 0) + 1;
        $this->stats[$table]['skip_summary'][] = $summary;
    }

    protected function failed(string $table, string $reason, $summary): void
    {
        $this->stats[$table]['failed'] = ($this->stats[$table]['failed'] ?? 0) + 1;
        $this->missingRequired[$table][$reason][] = $summary;
    }

    protected function connectAndInsert(string $table, array $data): void
    {
        DB::table($table)->insert($data);
    }

    // ------------------------------------------------------------- imports

    protected function importLanguages(bool $dryRun): void
    {
        $source = ['en', 'ar'];
        $target = 'languages';
        $this->line("languages: mapping legacy locales " . implode(',', $source) . ' -> ' . $target . ' (other legacy locales unsupported by app)');

        $this->stats[$target] = $this->stats[$target] ?? ['planned' => 0];
        $existing = DB::table($target)->pluck('code')->map(fn($v) => strtolower($v))->all();

        foreach ($source as $code) {
            $row = DB::connection(self::CONN)->table('public.locales')->where('locale', $code)->first();
            if (! $row) {
                $this->warn("legacy locale '{$code}' not found; generating default.");
                $row = (object) ['locale' => $code];
            }

            if (in_array($code, $existing, true)) {
                $this->skipped($target, 'already_exists', "language {$code} already present in target");
                continue;
            }

            $data = [
                'code' => $code,
                'name' => $row->name ?? $code,
                'native_name' => $row->name ?? ($code === 'ar' ? 'العربية' : 'English'),
                'direction' => $code === 'ar' ? 'rtl' : 'ltr',
                'locale' => $code,
                'is_default' => $code === 'en' ? 1 : 0,
                'is_active' => 1,
                'sort_order' => $code === 'en' ? 1 : 2,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $this->planned($target, 1);
            $this->record($target, $data, "[{$code}] -> id used: legacy locales");
            if (! $dryRun) {
                $id = DB::table($target)->insertGetId($data);
                $this->targetIds[$target][($row->id ?? (int) array_search($code, $source) + 1)] = $id;
            }
        }
    }

    protected function importAdmins(bool $dryRun): void
    {
        $target = 'admins';
        $this->stats[$target] = $this->stats[$target] ?? ['planned' => 0];
        $rows = DB::connection(self::CONN)->table('public.admins')->where('is_deleted', false)->get();

        foreach ($rows as $row) {
            if (DB::table($target)->where('email', $row->email)->exists()) {
                $this->skipped($target, 'already_exists', "admin {$row->email}");
                continue;
            }
            $name = $this->pick((array) $row, ['full_name', 'first_name', 'last_name']) ?? $row->email;
            $data = [
                'name' => $name,
                'email' => $row->email,
                'phone' => $row->phone,
                'password' => $row->encrypted_password ?? '(not-restorable)',
                'job_title' => $row->title,
                'is_active' => $this->boolVal($row->is_active ?? true),
                'remember_token' => null,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ];
            $this->planned($target, 1);
            $this->record($target, $data, "[{$row->id}] {$row->email}");
            if (! $dryRun) {
                $id = DB::table($target)->insertGetId($data);
                $this->targetIds[$target][(int) $row->id] = $id;
            }
        }
    }

    protected function importUsers(bool $dryRun): void
    {
        $target = 'users';
        $this->stats[$target] = $this->stats[$target] ?? ['planned' => 0];
        $rows = DB::connection(self::CONN)->table('public.users')->where('is_deleted', false)->get();

        foreach ($rows as $row) {
            if (DB::table($target)->where('email', $row->email)->exists()) {
                $this->skipped($target, 'already_exists', "user {$row->email}");
                continue;
            }
            $name = $this->pick((array) $row, ['full_name', 'first_name', 'last_name']) ?? $row->email;
            $data = [
                'name' => $name,
                'email' => $row->email,
                'email_verified_at' => null,
                'password' => $row->encrypted_password ?? '(not-restorable)',
                'image' => null,
                'remember_token' => null,
                'is_active' => $this->boolVal($row->is_active ?? true),
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ];
            $this->planned($target, 1);
            $this->record($target, $data, "[{$row->id}] {$row->email}");
            if (! $dryRun) {
                $id = DB::table($target)->insertGetId($data);
                $this->targetIds[$target][(int) $row->id] = $id;
            }
        }
    }

    protected function importCurrencies(bool $dryRun): void
    {
        $target = 'currencies';
        $this->stats[$target] = $this->stats[$target] ?? ['planned' => 0];
        $rows = DB::connection(self::CONN)->table('public.currencies')->get();

        foreach ($rows as $row) {
            $code = strtoupper((string) ($row->shortcut ?? ''));
            if ($code === '') {
                $this->skipped($target, 'no_code', "currency id {$row->id}");
                continue;
            }
            if (DB::table($target)->whereRaw('UPPER(code) = ?', [$code])->exists()) {
                $this->skipped($target, 'already_exists', "currency {$code}");
                continue;
            }
            $data = [
                'code' => $code,
                'symbol' => $row->icon,
                'exchange_rate' => 1.0,
                'name' => $row->name ?? $code,
                'rate_to_default' => 1.0,
                'is_default' => $code === 'USD' ? 1 : 0,
                'sort_order' => (int) $row->id,
                'is_active' => 1,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ];
            $this->planned($target, 1);
            $this->record($target, $data, "[{$row->id}] {$code}");
            if (! $dryRun) {
                $id = DB::table($target)->insertGetId($data);
                $this->targetIds[$target][(int) $row->id] = $id;
            }
        }
    }

    protected function importCountries(bool $dryRun): void
    {
        $target = 'countries';
        $this->stats[$target] = $this->stats[$target] ?? ['planned' => 0];
        $translations = $this->translations('country_translations', 'country_id', ['name', 'currency_name', 'region', 'subregion', 'locale']);
        $rows = DB::connection(self::CONN)->table('public.countries')->get();

        foreach ($rows as $row) {
            $code = strtoupper((string) ($row->code ?? $row->iso2 ?? ''));
            if ($code === '') {
                $this->skipped($target, 'no_code', "country id {$row->id}");
                continue;
            }
            $localeRows = $translations[(int) $row->id] ?? [];
            $name = $this->pick($localeRows['en'] ?? [], ['name'])
                ?? $this->pick($localeRows['ar'] ?? [], ['name'])
                ?? (trim((string) ($row->name ?? '')) !== '' ? $row->name : null)
                ?? $code;
            $data = [
                'id' => (int) $row->id,
                'code' => $code,
                'name' => $name,
                'slug' => $this->dedupe($this->slugify($name, 'country', (int) $row->id), 'country_slug'),
                'flag' => $row->emoji,
                'phone_code' => $row->phone_code,
                'is_active' => 1,
                'is_featured' => 0,
                'sort_order' => (int) $row->id,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ];
            $this->planned($target, 1);
            $this->record($target, $data, "[{$row->id}] {$name} ({$code})");

            if (! $dryRun && (int) $row->id !== 0) {
                DB::table($target)->insertOrIgnore($data);
                $this->targetIds[$target][(int) $row->id] = (int) $row->id;
            }
        }
    }

    protected function importCities(bool $dryRun): void
    {
        $target = 'cities';
        $this->stats[$target] = $this->stats[$target] ?? ['planned' => 0];
        $translations = $this->translations('state_translations', 'state_id', ['name', 'locale']);

        $rows = DB::connection(self::CONN)->table('public.states')->get();

        foreach ($rows as $row) {
            $countryId = (int) ($row->country_id ?? 0);
            if ($countryId === 0) {
                $this->skipped($target, 'no_country', "state id {$row->id}");
                continue;
            }
            if ((int) $row->id === 0) {
                $this->failed($target, 'zero_id', "state id {$row->id}");
                continue;
            }

            $localeRows = $translations[(int) $row->id] ?? [];
            $name = $this->pick($localeRows['en'] ?? [], ['name'])
                ?? $this->pick($localeRows['ar'] ?? [], ['name'])
                ?? $row->name
                ?? $this->pick((array) $row, ['code']);

            if ($name === null || trim((string) $name) === '') {
                $this->skipped($target, 'no_name', "state id {$row->id} has no translatable name");
                continue;
            }

            $slug = $this->dedupe($this->slugify($name, 'city', (int) $row->id), 'city_slug_' . $countryId);

            $data = [
                'id' => (int) $row->id,
                'country_id' => $countryId,
                'name' => mb_substr((string) $name, 0, 120),
                'short_description' => null,
                'description' => null,
                'hero_image' => null,
                'featured_image' => null,
                'latitude' => $this->isNumeric($row->lat) ? $row->lat : null,
                'longitude' => $this->isNumeric($row->long) ? $row->long : null,
                'slug' => $slug,
                'is_active' => 1,
                'seo_title' => null,
                'seo_description' => null,
                'schema_json' => null,
                'sort_order' => (int) $row->id,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
                'is_featured' => 0,
            ];

            $this->planned($target, 1);
            $this->record($target, $data, "[{$row->id}] {$name} (country {$countryId})");
            $this->targetIds[$target][(int) $row->id] = (int) $row->id;

            if (! $dryRun) {
                DB::table($target)->insertOrIgnore($data);
            }
        }
    }

    protected function importPackageCategories(bool $dryRun): void
    {
        $target = 'package_categories';
        $this->stats[$target] = $this->stats[$target] ?? ['planned' => 0];

        $sources = [
            ['public.package_categories', 'travel_package', 'package_category_translations', 'package_category_id'],
            ['public.tour_categories', 'day_tour', 'tour_category_translations', 'tour_category_id'],
        ];

        foreach ($sources as [$table, $type, $transTable, $transFk]) {
            $translations = $this->translations($transTable, $transFk, ['name', 'slug', 'overview', 'body', 'desc_seo', 'title_seo', 'h1_seo', 'tags_seo']);
            $rows = DB::connection(self::CONN)->table($table)->get();
            foreach ($rows as $row) {
                $localeRows = $translations[(int) $row->id] ?? [];
                $name = $this->pick($localeRows['en'] ?? [], ['name'])
                    ?? $this->pick($localeRows['ar'] ?? [], ['name'])
                    ?? (trim((string) ($row->name ?? '')) !== '' ? $row->name : null)
                    ?? $row->slug
                    ?? ('legacy ' . basename($table) . ' ' . $row->id);
                $id = $this->packageCategoryNextId++;
                $this->targetIds['package_categories_src'][$table][(int) $row->id] = $id;

                $data = [
                    'id' => $id,
                    'parent_id' => null,
                    'country_id' => null,
                    'slug' => $this->dedupe($this->slugify($this->pick($localeRows['en'] ?? [], ['slug']) ?? $row->slug ?? $name, 'pkg-cat', (int) $row->id), 'package_category_slug'),
                    'name' => $name,
                    'description' => $this->pick($localeRows['en'] ?? [], ['overview']) ?? $this->pick($localeRows['ar'] ?? [], ['overview']) ?? $row->overview,
                    'category_type' => $type,
                    'icon' => null,
                    'image' => null,
                    'min_days' => null,
                    'max_days' => null,
                    'price_from' => null,
                    'is_featured' => 0,
                    'is_active' => $this->boolVal($row->is_active ?? true),
                    'sort_order' => (int) ($row->placement ?? $row->id),
                    'seo_title' => $this->pick($localeRows['en'] ?? [], ['title_seo']) ?? $this->pick($localeRows['ar'] ?? [], ['title_seo']) ?? $row->title_seo,
                    'seo_description' => $this->pick($localeRows['en'] ?? [], ['desc_seo']) ?? $this->pick($localeRows['ar'] ?? [], ['desc_seo']) ?? $row->desc_seo,
                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at,
                ];

                $this->planned($target, 1);
                $this->record($target, $data, "[{$table}#{$row->id}] {$name}");
                if (! $dryRun) {
                    DB::table($target)->insert($data);
                    $this->targetIds[$target][$id] = $id;
                }
            }
        }
    }

    protected function importArticleCategories(bool $dryRun): void
    {
        $target = 'article_categories';
        $this->stats[$target] = $this->stats[$target] ?? ['planned' => 0];

        $sources = [
            ['public.blog_categories', 'blog_category_translations', 'blog_category_id'],
            ['public.news_categories', 'news_category_translations', 'news_category_id'],
            ['public.advice_categories', 'advice_category_translations', 'advice_category_id'],
        ];

        foreach ($sources as [$table, $transTable, $transFk]) {
            $translations = $this->translations($transTable, $transFk, ['name', 'slug', 'overview']);
            $rows = DB::connection(self::CONN)->table($table)->get();
            foreach ($rows as $row) {
                $localeRows = $translations[(int) $row->id] ?? [];
                $name = $this->pick($localeRows['en'] ?? [], ['name'])
                    ?? $this->pick($localeRows['ar'] ?? [], ['name'])
                    ?? (trim((string) ($row->name ?? '')) !== '' ? $row->name : null)
                    ?? $row->slug
                    ?? ('legacy ' . basename($table) . ' ' . $row->id);
                $id = $this->articleCategoryNextId++;
                $this->targetIds['article_categories_src'][$table][(int) $row->id] = $id;

                $data = [
                    'id' => $id,
                    'parent_id' => null,
                    'name' => $name,
                    'slug' => $this->dedupe($this->slugify($this->pick($localeRows['en'] ?? [], ['slug']) ?? $row->slug ?? $name, 'art-cat', (int) $row->id), 'article_category_slug'),
                    'description' => $this->pick($localeRows['en'] ?? [], ['overview']) ?? $this->pick($localeRows['ar'] ?? [], ['overview']) ?? $row->overview,
                    'is_active' => $this->boolVal($row->is_active ?? true),
                    'sort_order' => (int) ($row->placement ?? $row->id),
                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at,
                ];

                $this->planned($target, 1);
                $this->record($target, $data, "[{$table}#{$row->id}] {$name}");
                if (! $dryRun) {
                    DB::table($target)->insert($data);
                    $this->targetIds[$target][$id] = $id;
                }
            }
        }
    }

    protected function importTags(bool $dryRun): void
    {
        $target = 'tags';
        $this->stats[$target] = $this->stats[$target] ?? ['planned' => 0];
        $translations = $this->translations('tag_translations', 'tag_id', ['name', 'slug']);

        $rows = DB::connection(self::CONN)->table('public.tags')->get();

        foreach ($rows as $row) {
            $localeRows = $translations[(int) $row->id] ?? [];
            $name = $this->pick($localeRows['en'] ?? [], ['name']) ?? $row->name;
            if (! $name) {
                $this->skipped($target, 'no_name', "tag id {$row->id}");
                continue;
            }
            $slug = $this->dedupe(
                $this->slugify($this->pick($localeRows['en'] ?? [], ['slug']) ?? $row->slug ?? $name, 'tag', (int) $row->id),
                'tag_slug'
            );

            if ((int) $row->id === 0) {
                $this->failed($target, 'zero_id', "tag id {$row->id}");
                continue;
            }

            $data = [
                'id' => (int) $row->id,
                'name' => mb_substr((string) $name, 0, 120),
                'slug' => $slug,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ];

            $this->planned($target, 1);
            $this->record($target, $data, "[{$row->id}] {$name}");
            $this->targetIds[$target][(int) $row->id] = (int) $row->id;

            if (! $dryRun) {
                DB::table($target)->insertOrIgnore($data);
            }
        }
    }

    protected function importAttractions(bool $dryRun): void
    {
        $target = 'attractions';
        $this->stats[$target] = $this->stats[$target] ?? ['planned' => 0];

        $attractionTrans = $this->translations('attraction_translations', 'attraction_id', ['name', 'slug', 'overview', 'body', 'title_seo', 'desc_seo']);
        $sightTrans = $this->translations('sight_translations', 'sight_id', ['name', 'slug', 'overview', 'body', 'title_seo', 'desc_seo']);

        $sourceAttractions = DB::connection(self::CONN)->table('public.attractions')->get();
        $sourceSights = DB::connection(self::CONN)->table('public.sights')->get();

        foreach ($sourceAttractions as $row) {
            $this->mapAttraction($row, $attractionTrans, null, 'attraction', $dryRun, $target);
        }

        foreach ($sourceSights as $row) {
            $this->mapAttraction($row, $sightTrans, $row->state_id, 'sight', $dryRun, $target);
        }
    }

    protected function mapAttraction($row, array $trans, $stateId, string $kind, bool $dryRun, string $target): void
    {
        $localeRows = $trans[(int) $row->id] ?? [];
        $name = $this->pick($localeRows['en'] ?? [], ['name']) ?? $row->name;
        if (! $name) {
            $this->skipped($target, 'no_name', "{$kind} id {$row->id}");
            return;
        }

        $legacyId = ($kind === 'sight' ? 'sight_' : 'attraction_') . $row->id;
        $cityId = $stateId ? ($this->targetIds['cities'][(int) $stateId] ?? null) : null;
        if ($stateId && ! $cityId) {
            $this->stats[$target]['orphan_city_warnings'] = ($this->stats[$target]['orphan_city_warnings'] ?? 0) + 1;
            $this->warnings[] = "attraction {$legacyId} references unknown state {$stateId}; city_id left null";
        }

        $id = $this->attractionNextId++;
        $this->targetIds[$target][$legacyId] = $id;
        $this->targetIds['attraction_legacy'][$target][(int) $row->id] = $id;

        $sourceSlugRaw = $this->pick($localeRows['en'] ?? [], ['slug']) ?? $row->slug;
        $data = [
            'id' => $id,
            'city_id' => $cityId,
            'slug' => $this->dedupe($this->slugify($sourceSlugRaw ?? $name, 'attraction', $id), 'attraction_slug'),
            'name' => mb_substr((string) $name, 0, 190),
            'short_description' => $this->pick($localeRows['en'] ?? [], ['overview']),
            'description' => $this->pick($localeRows['en'] ?? [], ['body']),
            'image' => null,
            'opening_hours' => null,
            'map_url' => $this->capText($this->prop($row, 'map_source'), 250),
            'latitude' => null,
            'longitude' => null,
            'is_featured' => 0,
            'is_active' => $this->boolVal($row->is_active ?? true),
            'sort_order' => (int) ($row->placement ?? 0),
            'seo_title' => $this->jsonSingle($this->pick($localeRows['en'] ?? [], ['title_seo'])),
            'seo_description' => $this->jsonSingle($this->pick($localeRows['en'] ?? [], ['desc_seo'])),
            'created_at' => $row->created_at,
            'updated_at' => $row->updated_at,
        ];

        $this->planned($target, 1);
        $this->record($target, $data, "[{$legacyId}] {$name}" . ($cityId ? " (city {$cityId})" : ''));
        if (! $dryRun) {
            DB::table($target)->insert($data);
            $this->targetIds[$target][$id] = $id;
        }
    }

    protected function importPackages(bool $dryRun): void
    {
        $target = 'packages';
        $this->stats[$target] = $this->stats[$target] ?? ['planned' => 0];

        $packageTrans = $this->translations('package_translations', 'package_id', ['name', 'slug', 'overview', 'body', 'duration', 'included', 'excluded', 'video_url', 'title_seo', 'desc_seo', 'notes']);
        $tourTrans = $this->translations('tour_translations', 'tour_id', ['name', 'slug', 'overview', 'body', 'duration', 'included', 'excluded', 'video_url', 'title_seo', 'desc_seo', 'notes']);

        $pkgPivot = $this->pivotMap('public.package_categories_packages', 'package_id', 'package_category_id');
        $tourPivot = $this->pivotMap('public.tour_categories_tours', 'tour_id', 'tour_category_id');

        $packageRows = DB::connection(self::CONN)->table('public.packages')->where('is_deleted', false)->orderBy('id')->get();
        $tourRows = DB::connection(self::CONN)->table('public.tours')->where('is_deleted', false)->orderBy('id')->get();

        foreach ($packageRows as $row) {
            $this->mapPackages($row, $packageTrans, 'travel_package', $pkgPivot, 'public.package_categories', $dryRun, $target);
        }
        foreach ($tourRows as $row) {
            $this->mapPackages($row, $tourTrans, 'day_tour', $tourPivot, 'public.tour_categories', $dryRun, $target);
        }
    }

    protected function pivotMap(string $table, string $keyCol, string $valueCol): array
    {
        $rows = DB::connection(self::CONN)->table($table)->select($keyCol, $valueCol)->get();

        $map = [];
        foreach ($rows as $row) {
            $map[(int) $row->{$keyCol}] = (int) $row->{$valueCol};
        }

        return $map;
    }

    protected function intOrNull(mixed $v): ?int
    {
        if ($v === null || $v === '') {
            return null;
        }

        return (int) $v;
    }

    protected function mapPackages($row, array $trans, string $type, array $pivot, string $categoryTable, bool $dryRun, string $target): void
    {
        $legacyId = (int) $row->id;
        $localeRows = $trans[$legacyId] ?? [];

        $name = $this->pick($localeRows['en'] ?? [], ['name']) ?? $this->pick($localeRows['ar'] ?? [], ['name']);
        if (! $name) {
            $this->skipped($target, 'no_name', "legacy {$type} #{$legacyId} has no name translation");

            return;
        }

        $targetId = $this->packageNextId++;

        $catId = null;
        if (isset($pivot[$legacyId])) {
            $catId = $this->targetIds['package_categories_src'][$categoryTable][$pivot[$legacyId]] ?? null;
        }

        $usd = DB::table('currencies')->whereRaw('UPPER(code) = ?', ['USD'])->first();
        $currencyId = $usd->id ?? null;

        $tinyInt = fn($v) => $v ? 1 : 0;

        $duration = $this->pick($localeRows['en'] ?? [], ['duration']) ?? $this->pick($localeRows['ar'] ?? [], ['duration']) ?? $row->duration;
        preg_match('/(\d+)\s*days?/i', (string) $duration, $daysM);
        preg_match('/(\d+)\s*nights?/i', (string) $duration, $nightsM);
        preg_match('/(\d+)\s*hours?/i', (string) $duration, $hoursM);

        $amount = $this->centsToAmount($row->amount_cents);
        $nonDeletedSourceSlug = $this->pick($localeRows['en'] ?? [], ['slug']) ?? $this->pick($localeRows['ar'] ?? [], ['slug']);

        $data = [
            'id' => $targetId,
            'category_id' => $catId,
            'destination_id' => null,
            'primary_country_id' => null,
            'currency_id' => $currencyId,
            'package_type' => $type,
            'nile_cruise_type_id' => null,
            'nile_cruise_category_id' => null,
            'slug' => $this->dedupe($this->slugify($nonDeletedSourceSlug ?? $name, 'package', $targetId), 'package_slug'),
            'title' => $this->jsonEncode($this->transJson($localeRows, ['name'])) ?? $name,
            'subtitle' => null,
            'short_description' => $this->jsonEncode($this->transJson($localeRows, ['overview'])),
            'description' => $this->jsonEncode($this->transJson($localeRows, ['body'])),
            'duration_type' => $type === 'day_tour' ? 'hours' : 'days',
            'duration_days' => isset($daysM[1]) ? (int) $daysM[1] : null,
            'duration_hours' => isset($hoursM[1]) ? (int) $hoursM[1] : null,
            'duration_nights' => isset($nightsM[1]) ? (int) $nightsM[1] : null,
            'duration_text' => $this->capText($this->jsonEncode($this->transJson($localeRows, ['duration'])), 250),
            'route_text' => null,
            'start_from_price' => $amount,
            'offer_price' => null,
            'base_price' => $amount,
            'compare_price' => null,
            'adult_price' => $amount,
            'child_price' => null,
            'infant_price' => null,
            'adult_min_age' => (int) ($row->min_age ?? 0),
            'child_min_age' => 0,
            'child_max_age' => (int) ($row->max_age ?? 0),
            'infant_min_age' => 0,
            'infant_max_age' => 0,
            'price_from' => $amount,
            'price_to' => null,
            'schedule_text' => null,
            'pickup_location' => null,
            'dropoff_location' => null,
            'destinations_text' => null,
            'location_summary' => null,
            'tour_type' => $type === 'day_tour' ? ($row->is_sea ? 'boat' : ($row->is_city ? 'city' : 'general')) : null,
            'difficulty_level' => null,
            'booking_mode' => null,
            'rating_avg' => 0,
            'reviews_count' => 0,
            'min_participants' => $this->intOrNull($row->min_booking ?? $row->min_people ?? null),
            'max_participants' => $this->intOrNull($row->max_booking ?? $row->max_people ?? null),
            'booking_lead_days' => null,
            'cancellation_policy' => null,
            'terms_conditions' => $this->jsonEncode($this->transJson($localeRows, ['notes'])),
            'pricing_information' => null,
            'children_policy' => null,
            'pickup_policy' => null,
            'faq_json' => null,
            'video_url' => $this->capText($this->jsonEncode($this->transJson($localeRows, ['video_url'])), 250),
            'featured_image' => null,
            'gallery_images' => null,
            'is_featured' => $tinyInt($row->is_famous ?? false),
            'is_best_seller' => 0,
            'is_ultra_luxury' => 0,
            'is_active' => $tinyInt($row->is_active ?? true),
            'published_at' => $row->is_active ? $row->created_at : null,
            'sort_order' => (int) ($row->placement ?? $legacyId),
            'seo_title' => $this->jsonEncode($this->transJson($localeRows, ['title_seo'])),
            'seo_description' => $this->jsonEncode($this->transJson($localeRows, ['desc_seo'])),
            'breadcrumb_title' => null,
            'canonical_url' => null,
            'created_by' => null,
            'updated_by' => null,
            'source_type' => 'legacy_rails_' . $type,
            'source_remote_id' => (string) $legacyId,
            'source_remote_slug' => $this->capText($nonDeletedSourceSlug, 185),
            'source_synced_at' => now(),
            'created_at' => $row->created_at,
            'updated_at' => $row->updated_at,
        ];

        $this->planned($target, 1);
        $this->record($target, $data, "[legacy_{$type}#{$legacyId} -> packages#{$targetId}] {$name}");
        $this->targetIds['packages'][$type . '_' . $legacyId] = $targetId;

        if (! $dryRun) {
            DB::table($target)->insert($data);
        }

        // price calendar rows keep the per-package availability of legacy pricing
        if ($amount !== null) {
            $this->planned('package_prices', 1);
            $this->record('package_prices', [
                'package_id' => $targetId,
                'label' => 'Legacy base price',
                'price_type' => 'base',
                'amount' => $amount,
                'currency_id' => $currencyId,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ], "package {$targetId} base price {$amount}");
            if (! $dryRun) {
                DB::table('package_prices')->insert([
                    'package_id' => $targetId,
                    'label' => 'Legacy base price',
                    'season_name' => null,
                    'price_type' => 'base',
                    'room_type' => null,
                    'pax_min' => null,
                    'pax_max' => null,
                    'group_size_min' => null,
                    'group_size_max' => null,
                    'amount' => $amount,
                    'currency_id' => $currencyId,
                    'valid_from' => null,
                    'valid_to' => null,
                    'notes' => 'Imported from legacy amount_cents',
                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at,
                ]);
            }
        }

        $this->importPackageInclusions($targetId, $localeRows, $dryRun);
    }

    protected function importPackageInclusions(int $packageId, array $localeRows, bool $dryRun): void
    {
        $rows = [];

        foreach (['included', 'excluded'] as $type) {
            $content = [];
            foreach (['en', 'ar'] as $locale) {
                $text = $this->pick($localeRows[$locale] ?? [], [$type]);
                if ($text) {
                    $content[$locale] = $text;
                }
            }
            if ($content) {
                $rows[] = ['type' => $type, 'content' => $this->jsonEncode($content)];
            }
        }

        if (! $rows) {
            return;
        }

        $sort = 1;
        foreach ($rows as $row) {
            $this->planned('package_inclusions', 1);
            $this->record('package_inclusions', [
                'package_id' => $packageId,
                'type' => $row['type'],
                'item_type' => $row['type'],
                'title' => null,
                'content' => $row['content'],
                'sort_order' => $sort++,
                'created_at' => now(),
                'updated_at' => now(),
            ], "pkg {$packageId} {$row['type']}");

            if (! $dryRun) {
                DB::table('package_inclusions')->insert([
                    'package_id' => $packageId,
                    'type' => $row['type'],
                    'item_type' => $row['type'],
                    'title' => null,
                    'content' => $row['content'],
                    'description' => null,
                    'sort_order' => $sort - 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    protected function importItineraries(bool $dryRun): void
    {
        $target = 'itineraries';
        $this->stats[$target] = $this->stats[$target] ?? ['planned' => 0];

        $pkgActs = DB::connection(self::CONN)->table('public.package_itineraries_tour_activities')
            ->select('package_itinerary_id', 'tour_activity_id')
            ->get()
            ->groupBy('package_itinerary_id')
            ->map(fn($g) => $g->pluck('tour_activity_id')->all());

        $tourActs = DB::connection(self::CONN)->table('public.tour_activities_itineraries')
            ->select('tour_itinerary_id', 'tour_activity_id')
            ->get()
            ->groupBy('tour_itinerary_id')
            ->map(fn($g) => $g->pluck('tour_activity_id')->all());

        $activityNames = DB::connection(self::CONN)->table('public.tour_activities')
            ->pluck('name', 'id')
            ->all();

        $stateTrans = $this->translations('state_translations', 'state_id', ['name']);

        $sources = [
            ['package_itineraries', 'package_itinerary_id', 'package_id', 'travel_package', $pkgActs],
            ['tour_itineraries', 'tour_itinerary_id', 'tour_id', 'day_tour', $tourActs],
        ];

        foreach ($sources as [$table, $idKey, $fk, $type, $activityMap]) {
            $rows = DB::connection(self::CONN)->table('public.' . $table)->orderBy('placement')->orderBy('id')->get();
            foreach ($rows as $row) {
                $pkgTarget = $this->targetIds['packages'][$type . '_' . (int) $row->{$fk}] ?? null;
                if (! $pkgTarget) {
                    $this->skipped($target, 'orphan_package', "{$table}#{$row->id} -> deleted/missing package {$row->{$fk}}");
                    continue;
                }

                $targetId = $this->itineraryNextId++;
                $this->targetIds[$target][$table . '#' . $row->id] = $targetId;

                $dayNumber = (int) preg_replace('/\D+/', '', (string) $row->day);
                if (! $dayNumber) {
                    $dayNumber = (int) $row->placement ?: $targetId;
                }
                while (isset($this->itineraryDays[$pkgTarget][$dayNumber])) {
                    $dayNumber++;
                }
                $this->itineraryDays[$pkgTarget][$dayNumber] = true;

                $cityId = null;
                if ($row->state_id) {
                    $cityId = $this->targetIds['cities'][(int) $row->state_id] ?? null;
                    if (! $cityId) {
                        $this->warnings[] = "{$table}#{$row->id} references unknown state {$row->state_id}";
                    }
                }

                $overnight = $cityId ? $this->cityName($cityId) : $this->stateName($row->state_id, $stateTrans);

                $activityIds = $activityMap[$row->id] ?? [];
                $activityNamesList = [];
                foreach ($activityIds as $actId) {
                    if (isset($activityNames[$actId])) {
                        $activityNamesList[] = $activityNames[$actId];
                    }
                }

                $data = [
                    'id' => $targetId,
                    'package_id' => $pkgTarget,
                    'duration' => $this->capText($row->timing, 250),
                    'day_number' => $dayNumber,
                    'title' => $row->name,
                    'description' => $row->details ?: $row->overview,
                    'meals' => null,
                    'meals_breakfast' => 0,
                    'meals_lunch' => 0,
                    'meals_dinner' => 0,
                    'overnight_location' => $this->capText($overnight, 250),
                    'start_time' => $this->capText($row->time, 250),
                    'end_time' => null,
                    'sort_order' => (int) $row->placement,
                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at,
                    'accommodation' => null,
                    'transport_notes' => null,
                    'activities' => $activityNamesList ? json_encode($activityNamesList) : null,
                ];

                $this->planned($target, 1);
                $this->record($target, $data, "[{$table}#{$row->id} -> itineraries#{$targetId}] day {$dayNumber} of package {$pkgTarget}");
                if (! $dryRun) {
                    DB::table($target)->insert($data);
                    $this->targetIds[$target][$targetId] = $targetId;
                }
            }
        }
    }

    protected function importPackageCities(bool $dryRun): void
    {
        $target = 'package_cities';
        $this->stats[$target] = $this->stats[$target] ?? ['planned' => 0];

        $rows = DB::connection(self::CONN)->table('public.package_itineraries')
            ->select('package_id', 'state_id', DB::connection(self::CONN)->raw('MIN(placement) as min_placement'))
            ->whereNotNull('state_id')
            ->groupBy('package_id', 'state_id')
            ->get();

        foreach ($rows as $row) {
            $pkgTarget = $this->targetIds['packages']['travel_package_' . (int) $row->package_id] ?? null;
            if (! $pkgTarget) {
                continue;
            }
            $cityId = $this->targetIds['cities'][(int) $row->state_id] ?? null;
            if (! $cityId) {
                continue;
            }

            if (DB::table($target)->where('package_id', $pkgTarget)->where('city_id', $cityId)->exists()) {
                continue;
            }

            $this->planned($target, 1);
            $this->record($target, [
                'package_id' => $pkgTarget,
                'city_id' => $cityId,
                'stop_order' => (int) $row->min_placement,
                'is_primary' => (int) $row->min_placement === 1 ? 1 : 0,
                'nights' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ], "package {$pkgTarget} -> city {$cityId}");

            if (! $dryRun) {
                DB::table($target)->insert([
                    'package_id' => $pkgTarget,
                    'city_id' => $cityId,
                    'stop_order' => (int) $row->min_placement,
                    'is_primary' => (int) $row->min_placement === 1 ? 1 : 0,
                    'nights' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    protected function importPackageTags(bool $dryRun): void
    {
        $target = 'package_tag_items';
        $this->stats[$target] = $this->stats[$target] ?? ['planned' => 0];

        $sets = [
            [
                'public.packages_tags',
                'package_id',
                'tag_id',
                'travel_package',
            ],
            [
                'public.tags_tours',
                'tour_id',
                'tag_id',
                'day_tour',
            ],
        ];

        foreach ($sets as [$table, $fk, $tagKey, $type]) {
            $rows = DB::connection(self::CONN)->table($table)->get();

            foreach ($rows as $row) {
                $pkgTarget = $this->targetIds['packages'][$type . '_' . (int) $row->{$fk}] ?? null;
                $tagTarget = $this->targetIds['tags'][(int) $row->{$tagKey}] ?? null;
                if (! $pkgTarget || ! $tagTarget) {
                    $this->skipped($target, 'orphan_link', "{$table}#{$row->{$fk}}/{$row->{$tagKey}} (pkg {$row->{$fk}}, tag {$row->{$tagKey}})", "package_tag_link_{$type}_{$row->{$fk}}_{$row->{$tagKey}}");
                    continue;
                }

                $this->planned($target, 1);
                $this->record($target, [
                    'package_id' => $pkgTarget,
                    'tag_id' => $tagTarget,
                    'created_at' => now(),
                    'updated_at' => now(),
                ], "pkg {$pkgTarget} -> tag {$tagTarget}");

                if (! $dryRun) {
                    DB::table($target)->insertOrIgnore([
                        'package_id' => $pkgTarget,
                        'tag_id' => $tagTarget,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    protected function importArticles(bool $dryRun): void
    {
        $target = 'articles';
        $this->stats[$target] = $this->stats[$target] ?? ['planned' => 0];

        $sets = [
            ['public.blogs', 'blog_id', 'blog_categories#', 'public.blog_categories', 'blog'],
            ['public.news', 'news_id', 'news_categories#', 'public.news_categories', 'blog'],
            ['public.advices', 'advice_id', 'advice_categories#', 'public.advice_categories', 'guide'],
        ];

        $translations = [
            'public.blogs' => $this->translations('blog_translations', 'blog_id', ['name', 'slug', 'overview', 'body', 'title_seo', 'desc_seo', 'tags_seo']),
            'public.news' => $this->translations('news_translations', 'news_id', ['name', 'slug', 'overview', 'body', 'title_seo', 'desc_seo', 'tags_seo']),
            'public.advices' => $this->translations('advice_translations', 'advice_id', ['name', 'slug', 'overview', 'body', 'title_seo', 'desc_seo', 'tags_seo']),
        ];

        foreach ($sets as [$table, $fk, $legacyPrefix, $categoryTable, $articleType]) {
            $rows = DB::connection(self::CONN)->table($table)->where('is_deleted', false)->get();
            foreach ($rows as $row) {
                $localeRows = $translations[$table][(int) $row->id] ?? [];
                $name = $this->pick($localeRows['en'] ?? [], ['name']) ?? $this->pick($localeRows['ar'] ?? [], ['name']) ?? $row->name;
                if (! $name) {
                    $this->skipped($target, 'no_name', "{$table}#{$row->id} has no name");
                    continue;
                }

                $targetId = $this->articleNextId++;
                $catId = null;
                if ($row->{str_replace('_id', '', $fk) . '_category_id'} ?? null) {
                    $legacyCatId = $row->{str_replace('_id', '', $fk) . '_category_id'};
                    $catId = $this->targetIds['article_categories_src'][$categoryTable][(int) $legacyCatId] ?? null;
                }

                $data = [
                    'id' => $targetId,
                    'category_id' => $catId,
                    'destination_id' => null,
                    'author_id' => null,
                    'slug' => $this->dedupe($this->slugify($this->pick($localeRows['en'] ?? [], ['slug']) ?? $row->slug ?? $name, 'article', $targetId), 'article_slug'),
                    'title' => $this->capText($this->pick($localeRows['en'] ?? [], ['name']) ?? $name, 250),
                    'excerpt' => $this->pick($localeRows['en'] ?? [], ['overview']),
                    'content' => $this->pick($localeRows['en'] ?? [], ['body']) ?? $row->body,
                    'featured_image' => null,
                    'article_type' => $articleType,
                    'is_featured' => 0,
                    'views_count' => 0,
                    'is_active' => $this->boolVal($row->is_active ?? true),
                    'published_at' => $row->created_at,
                    'seo_title' => $this->capText($this->pick($localeRows['en'] ?? [], ['title_seo']), 250),
                    'seo_description' => $this->pick($localeRows['en'] ?? [], ['desc_seo']),
                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at,
                ];

                $this->planned($target, 1);
                $this->record($target, $data, "[{$table}#{$row->id} -> articles#{$targetId}] {$name}");
                $this->targetIds[$target][$table . '#' . $row->id] = $targetId;

                if (! $dryRun) {
                    DB::table($target)->insert($data);
                    $this->targetIds[$target][$targetId] = $targetId;
                }
            }
        }
    }

    protected function importSeoRedirects(bool $dryRun): void
    {
        $target = 'seo_redirects';
        $this->stats[$target] = $this->stats[$target] ?? ['planned' => 0];

        $rows = DB::connection(self::CONN)->table('public.redirects')->get();
        foreach ($rows as $row) {
            $from = $row->from;
            $to = $row->to;
            if (! $from || ! $to) {
                $this->skipped($target, 'incomplete', "redirect id {$row->id} (from/to missing)");
                continue;
            }
            $code = (int) ($row->status ?: 301);
            if (! in_array($code, [301, 302, 307, 308], true)) {
                $code = 301;
            }

            $this->planned($target, 1);
            $this->record($target, [
                'old_path' => $this->capText($from, 250),
                'new_path' => $this->capText($to, 250),
                'http_code' => $code,
                'is_active' => 1,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ], "redirect id {$row->id} {$from} -> {$to}");

            if (! $dryRun) {
                DB::table($target)->insertOrIgnore([
                    'old_path' => $this->capText($from, 250),
                    'new_path' => $this->capText($to, 250),
                    'http_code' => $code,
                    'is_active' => 1,
                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at,
                ]);
            }
        }
    }

    protected function importNewsletterSubscribers(bool $dryRun): void
    {
        $target = 'newsletter_subscribers';
        $this->stats[$target] = $this->stats[$target] ?? ['planned' => 0];

        $rows = DB::connection(self::CONN)->table('public.subscribers')->get();
        foreach ($rows as $row) {
            if (! $row->email) {
                $this->skipped($target, 'no_email', "subscriber id {$row->id}");
                continue;
            }
            if (DB::table($target)->where('email', $row->email)->exists()) {
                $this->skipped($target, 'already_exists', "subscriber {$row->email}");
                continue;
            }

            $this->planned($target, 1);
            $this->record($target, [
                'email' => $row->email,
                'name' => $row->name,
                'country_id' => null,
                'preferences' => null,
                'is_active' => $this->boolVal($row->is_active ?? false),
                'verified_at' => null,
                'unsubscribed_at' => $row->canceled_at,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ], "subscriber id {$row->id} {$row->email}");

            if (! $dryRun) {
                DB::table($target)->insert([
                    'email' => $row->email,
                    'name' => $row->name,
                    'country_id' => null,
                    'preferences' => null,
                    'is_active' => $this->boolVal($row->is_active ?? false),
                    'verified_at' => null,
                    'unsubscribed_at' => $row->canceled_at,
                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at,
                ]);
            }
        }
    }

    protected function importBlogComments(bool $dryRun): void
    {
        $target = 'article_comments';
        $this->stats[$target] = $this->stats[$target] ?? ['planned' => 0];

        $rows = DB::connection(self::CONN)->table('public.blog_comments')->orderBy('id')->get();
        $translations = $this->translations('blog_comment_translations', 'blog_comment_id', ['full_name', 'comment']);
        foreach ($rows as $row) {
            $articleId = $this->targetIds['articles']['public.blogs#' . (int) $row->blog_id] ?? null;
            if (! $articleId) {
                $this->skipped($target, 'orphan_article', "blog_comment#{$row->id} references missing blog {$row->blog_id}");
                continue;
            }
            $localeRows = $translations[(int) $row->id] ?? [];
            $name = trim((string) ($this->pick($localeRows['en'] ?? [], ['full_name'])
                ?? $this->pick($localeRows['ar'] ?? [], ['full_name'])
                ?? array_values($localeRows)[0]['full_name'] ?? $row->full_name ?? ''));
            if ($name === '') {
                $this->skipped($target, 'no_name', "blog_comment#{$row->id} has no author name");
                continue;
            }
            $comment = trim((string) ($this->pick($localeRows['en'] ?? [], ['comment'])
                ?? $this->pick($localeRows['ar'] ?? [], ['comment'])
                ?? array_values($localeRows)[0]['comment'] ?? $row->comment ?? ''));
            if ($comment === '') {
                $this->skipped($target, 'no_comment', "blog_comment#{$row->id} has empty comment");
                continue;
            }

            $this->planned($target, 1);
            $this->record($target, [
                'article_id' => $articleId,
                'parent_id' => null,
                'name' => $this->capText($name, 185),
                'email' => $row->email,
                'comment' => $comment,
                'is_approved' => $this->boolVal($row->is_active ?? false),
                'approved_at' => $row->is_active ? $row->updated_at : null,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ], "blog_comment#{$row->id} -> article {$articleId}");

            if (! $dryRun) {
                DB::table($target)->insert([
                    'article_id' => $articleId,
                    'parent_id' => null,
                    'name' => $this->capText($name, 185),
                    'email' => $row->email,
                    'comment' => $comment,
                    'is_approved' => $this->boolVal($row->is_active ?? false),
                    'approved_at' => $row->is_active ? $row->updated_at : null,
                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at,
                ]);
            }
        }
    }

    protected function importResourceFaqs(bool $dryRun): void
    {
        $target = 'faqs';
        $this->stats[$target] = $this->stats[$target] ?? ['planned' => 0];

        $rows = DB::connection(self::CONN)->table('public.resource_faqs')->orderBy('id')->get();
        $translations = $this->translations('resource_faq_translations', 'resource_faq_id', ['q', 'a']);
        foreach ($rows as $row) {
            [$contextType, $contextId] = $this->faqContext($row->sourceable_type, (int) $row->sourceable_id);
            if (! $contextType || ! $contextId) {
                $this->skipped($target, 'no_target', "resource_faq#{$row->id} has no imported entity for sourceable {$row->sourceable_type}#{$row->sourceable_id}");
                continue;
            }

            $localeRows = $translations[(int) $row->id] ?? [];
            $question = trim((string) ($this->pick($localeRows['en'] ?? [], ['q'])
                ?? $this->pick($localeRows['ar'] ?? [], ['q'])
                ?? array_values($localeRows)[0]['q'] ?? $row->q ?? ''));
            if ($question === '') {
                $this->skipped($target, 'no_question', "resource_faq#{$row->id} has empty question");
                continue;
            }
            $answer = trim((string) ($this->pick($localeRows['en'] ?? [], ['a'])
                ?? $this->pick($localeRows['ar'] ?? [], ['a'])
                ?? array_values($localeRows)[0]['a'] ?? $row->a ?? ''));

            $this->planned($target, 1);
            $this->record($target, [
                'category_id' => null,
                'question' => $this->capText($question, 250),
                'answer' => $answer,
                'context_type' => $contextType,
                'context_id' => $contextId,
                'is_active' => $this->boolVal($row->is_active ?? false),
                'sort_order' => (int) ($row->placement ?? 0),
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
                'is_featured' => 0,
            ], "resource_faq#{$row->id} -> {$contextType}#{$contextId}");

            if (! $dryRun) {
                DB::table($target)->insert([
                    'category_id' => null,
                    'question' => $this->capText($question, 250),
                    'answer' => $answer,
                    'context_type' => $contextType,
                    'context_id' => $contextId,
                    'is_active' => $this->boolVal($row->is_active ?? false),
                    'sort_order' => (int) ($row->placement ?? 0),
                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at,
                    'is_featured' => 0,
                ]);
            }
        }
    }

    protected function faqContext(mixed $type, int $sourceableId): array
    {
        switch (trim((string) $type)) {
            case 'Package':
                $id = $this->targetIds['packages']['travel_package_' . $sourceableId] ?? null;

                return $id ? ['App\\Models\\Package', $id] : [null, null];
            case 'Tour':
                $id = $this->targetIds['packages']['day_tour_' . $sourceableId] ?? null;

                return $id ? ['App\\Models\\Package', $id] : [null, null];
            case 'Sight':
                $id = $this->targetIds['attractions']['sight_' . $sourceableId] ?? null;

                return $id ? ['App\\Models\\Attraction', $id] : [null, null];
            default:
                return [null, null];
        }
    }

    protected function importResourceReviews(bool $dryRun): void
    {
        $target = 'reviews';
        $this->stats[$target] = $this->stats[$target] ?? ['planned' => 0];

        $rows = DB::connection(self::CONN)->table('public.resource_reviews')->orderBy('id')->get();
        $placeholderClientId = (int) (DB::table('users')->orderBy('id')->value('id') ?? 1);

        foreach ($rows as $row) {
            $packageTarget = match (trim((string) $row->sourceable_type)) {
                'Package' => $this->targetIds['packages']['travel_package_' . (int) $row->sourceable_id] ?? null,
                'Tour' => $this->targetIds['packages']['day_tour_' . (int) $row->sourceable_id] ?? null,
                default => null,
            };
            if (! $packageTarget) {
                $this->skipped($target, 'no_target', "resource_review#{$row->id} has no imported package for {$row->sourceable_type}#{$row->sourceable_id}");
                continue;
            }
            if ($row->comment === null || trim((string) $row->comment) === '') {
                $this->skipped($target, 'no_content', "resource_review#{$row->id} has empty comment");
                continue;
            }

            $rate = (int) ($row->rate ?? 5);
            if ($rate < 1 || $rate > 5) {
                $rate = 5;
            }

            $this->planned($target, 1);
            $this->record($target, [
                'client_id' => $placeholderClientId,
                'package_id' => $packageTarget,
                'rating' => $rate,
                'title' => null,
                'content' => $row->comment,
                'pros' => null,
                'cons' => null,
                'travel_date' => null,
                'images' => null,
                'is_approved' => $this->boolVal($row->is_active ?? false),
                'helpful_count' => 0,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ], "resource_review#{$row->id} ({$row->sourceable_type}#{$row->sourceable_id}) -> package {$packageTarget}");

            if (! $dryRun) {
                DB::table($target)->insert([
                    'client_id' => $placeholderClientId,
                    'package_id' => $packageTarget,
                    'rating' => $rate,
                    'title' => null,
                    'content' => $row->comment,
                    'pros' => null,
                    'cons' => null,
                    'travel_date' => null,
                    'images' => null,
                    'is_approved' => $this->boolVal($row->is_active ?? false),
                    'helpful_count' => 0,
                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at,
                ]);
            }
        }
    }

    protected function importResourcePrices(bool $dryRun): void
    {
        $target = 'package_prices';
        $this->stats[$target] = $this->stats[$target] ?? ['planned' => 0];

        $usd = DB::table('currencies')->whereRaw('UPPER(code) = ?', ['USD'])->value('id');

        $rows = DB::connection(self::CONN)->table('public.resource_prices')->orderBy('id')->get();
        foreach ($rows as $row) {
            $packageId = match (trim((string) $row->resource_type)) {
                'Package' => $this->targetIds['packages']['travel_package_' . (int) $row->resource_id] ?? null,
                'Tour' => $this->targetIds['packages']['day_tour_' . (int) $row->resource_id] ?? null,
                default => null,
            };
            if (! $packageId) {
                $this->skipped($target, 'no_target', "resource_price#{$row->id} has no imported package for {$row->resource_type}#{$row->resource_id}");
                continue;
            }

            $amount = $this->centsToAmount($row->amount_cents);
            if ($amount === null) {
                $this->skipped($target, 'no_amount', "resource_price#{$row->id} has no amount");
                continue;
            }
            $label = trim((string) ($row->name ?? ''));
            if ($label === '') {
                $label = 'Legacy price tier #' . $row->id;
            }

            $this->planned($target, 1);
            $this->record($target, [
                'package_id' => $packageId,
                'label' => $this->capText($label, 250),
                'season_name' => null,
                'price_type' => 'tier',
                'room_type' => null,
                'pax_min' => null,
                'pax_max' => null,
                'group_size_min' => null,
                'group_size_max' => null,
                'amount' => $amount,
                'currency_id' => $usd,
                'valid_from' => null,
                'valid_to' => null,
                'notes' => 'Imported from legacy resource_prices',
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ], "resource_price#{$row->id} ({$row->resource_type}#{$row->resource_id}) -> package {$packageId} ({$amount})");

            if (! $dryRun) {
                DB::table($target)->insert([
                    'package_id' => $packageId,
                    'label' => $this->capText($label, 250),
                    'season_name' => null,
                    'price_type' => 'tier',
                    'room_type' => null,
                    'pax_min' => null,
                    'pax_max' => null,
                    'group_size_min' => null,
                    'group_size_max' => null,
                    'amount' => $amount,
                    'currency_id' => $usd,
                    'valid_from' => null,
                    'valid_to' => null,
                    'notes' => 'Imported from legacy resource_prices',
                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at,
                ]);
            }
        }
    }

    protected function importTestimonials(bool $dryRun): void
    {
        $target = 'testimonials';
        $this->stats[$target] = $this->stats[$target] ?? ['planned' => 0];

        $rows = DB::connection(self::CONN)->table('public.testimonials')->orderBy('id')->get();
        $translations = $this->translations('testimonial_translations', 'testimonial_id', ['name', 'company', 'position', 'text']);
        foreach ($rows as $row) {
            $localeRows = $translations[(int) $row->id] ?? [];
            $name = trim((string) ($this->pick($localeRows['en'] ?? [], ['name'])
                ?? $this->pick($localeRows['ar'] ?? [], ['name'])
                ?? array_values($localeRows)[0]['name'] ?? $row->name ?? ''));
            $content = trim((string) ($this->pick($localeRows['en'] ?? [], ['text'])
                ?? $this->pick($localeRows['ar'] ?? [], ['text'])
                ?? array_values($localeRows)[0]['text'] ?? $row->text ?? ''));
            if ($name === '' && $content === '') {
                $this->skipped($target, 'empty', "testimonial#{$row->id} has no name or content");
                continue;
            }

            $countryId = null;
            if ($row->state_id) {
                $cityId = $this->targetIds['cities'][(int) $row->state_id] ?? null;
                if ($cityId) {
                    $countryId = DB::table('cities')->where('id', $cityId)->value('country_id');
                } else {
                    $this->warnings[] = "testimonial#{$row->id} references unknown state {$row->state_id}";
                }
            }

            $rate = (int) ($row->rate ?? 5);
            if ($rate < 1 || $rate > 5) {
                $rate = 5;
            }

            $this->planned($target, 1);
            $this->record($target, [
                'country_id' => $countryId,
                'package_id' => null,
                'customer_name' => $name !== '' ? $this->capText($name, 185) : 'Legacy client',
                'customer_initials' => null,
                'avatar' => null,
                'source' => null,
                'source_url' => null,
                'rating' => $rate,
                'content' => $content !== '' ? $content : 'Legacy testimonial',
                'is_verified' => 0,
                'is_featured' => 0,
                'is_active' => $this->boolVal($row->is_active ?? false),
                'published_at' => $row->is_active ? $row->created_at : null,
                'sort_order' => (int) $row->id,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ], "testimonial#{$row->id}");

            if (! $dryRun) {
                DB::table($target)->insert([
                    'country_id' => $countryId,
                    'package_id' => null,
                    'customer_name' => $name !== '' ? $this->capText($name, 185) : 'Legacy client',
                    'customer_initials' => null,
                    'avatar' => null,
                    'source' => null,
                    'source_url' => null,
                    'rating' => $rate,
                    'content' => $content !== '' ? $content : 'Legacy testimonial',
                    'is_verified' => 0,
                    'is_featured' => 0,
                    'is_active' => $this->boolVal($row->is_active ?? false),
                    'published_at' => $row->is_active ? $row->created_at : null,
                    'sort_order' => (int) $row->id,
                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at,
                ]);
            }
        }
    }

    protected function importPaymentMethods(bool $dryRun): void
    {
        $target = 'payment_methods';
        $this->stats[$target] = $this->stats[$target] ?? ['planned' => 0];

        $rows = DB::connection(self::CONN)->table('public.payment_methods')->orderBy('id')->get();
        $translations = $this->translations('payment_method_translations', 'payment_method_id', ['name']);
        foreach ($rows as $row) {
            if (DB::table($target)->where('code', 'legacy_pm_' . $row->id)->exists()) {
                $this->skipped($target, 'already_exists', "payment_method#{$row->id}");
                continue;
            }

            $localeRows = $translations[(int) $row->id] ?? [];
            $name = trim((string) ($this->pick($localeRows['en'] ?? [], ['name'])
                ?? $this->pick($localeRows['ar'] ?? [], ['name'])
                ?? array_values($localeRows)[0]['name'] ?? $row->name ?? ''));
            if ($name === '') {
                $name = 'Payment Method #' . $row->id;
            }

            $this->planned($target, 1);
            $this->record($target, [
                'name' => $this->capText($name, 115),
                'code' => 'legacy_pm_' . $row->id,
                'provider' => 'legacy_gateway_' . (int) ($row->payment_gateway_id ?? 0),
                'config' => null,
                'is_active' => $this->boolVal($row->is_active ?? false),
                'sort_order' => (int) ($row->placement ?? 0),
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ], "payment_method#{$row->id} ({$name})");

            if (! $dryRun) {
                DB::table($target)->insert([
                    'name' => $this->capText($name, 115),
                    'code' => 'legacy_pm_' . $row->id,
                    'provider' => 'legacy_gateway_' . (int) ($row->payment_gateway_id ?? 0),
                    'config' => null,
                    'is_active' => $this->boolVal($row->is_active ?? false),
                    'sort_order' => (int) ($row->placement ?? 0),
                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at,
                ]);
            }
        }
    }

    protected function importNotifications(bool $dryRun): void
    {
        $target = 'notifications';
        $this->stats[$target] = $this->stats[$target] ?? ['planned' => 0];

        $adminId = (int) (DB::table('admins')->orderBy('id')->value('id') ?? 1);

        $rows = DB::connection(self::CONN)->table('public.notifications')->orderBy('id')->get();
        foreach ($rows as $row) {
            $title = trim((string) ($row->name ?? ''));
            if ($title === '') {
                $this->skipped($target, 'no_title', "notification#{$row->id} has no title");
                continue;
            }

            $this->planned($target, 1);
            $this->record($target, [
                'admin_id' => $adminId,
                'type' => 'legacy',
                'title' => $this->capText($title, 250),
                'message' => $row->text,
                'link' => $this->capText($row->url, 250),
                'is_read' => $this->boolVal($row->is_seen ?? false),
                'read_at' => $row->is_seen ? $row->updated_at : null,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ], "notification#{$row->id} ({$title})");

            if (! $dryRun) {
                DB::table($target)->insert([
                    'admin_id' => $adminId,
                    'type' => 'legacy',
                    'title' => $this->capText($title, 250),
                    'message' => $row->text,
                    'link' => $this->capText($row->url, 250),
                    'is_read' => $this->boolVal($row->is_seen ?? false),
                    'read_at' => $row->is_seen ? $row->updated_at : null,
                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at,
                ]);
            }
        }
    }

    protected function importPages(bool $dryRun): void
    {
        $target = 'pages';
        $this->stats[$target] = $this->stats[$target] ?? ['planned' => 0];

        $conn = DB::connection(self::CONN);
        $pages = $conn->table('public.seos')
            ->select('page', $conn->raw('MAX(updated_at) as latest'))
            ->groupBy('page')
            ->orderBy('page')
            ->get();

        foreach ($pages as $row) {
            $pageKey = trim((string) $row->page);
            if ($pageKey === '') {
                $this->skipped($target, 'empty_page', 'seos row with empty page slug');
                continue;
            }

            $seo = $conn->table('public.seos')->where('page', $pageKey)->orderByDesc('updated_at')->first();

            $id = $this->pageNextId++;
            $slug = $this->dedupe($this->slugify($pageKey, 'page', $id), 'page_slug');

            $title = ucwords(str_replace(['_', '-'], ' ', $pageKey));
            $data = [
                'id' => $id,
                'slug' => $slug,
                'title' => $title,
                'template' => 'default',
                'body' => null,
                'featured_image' => null,
                'is_home' => $pageKey === 'home' ? 1 : 0,
                'is_active' => 1,
                'published_at' => $seo->created_at ?? now(),
                'seo_title' => $this->capText(($seo->title_seo ?? '') !== '' ? $seo->title_seo : $title, 250),
                'seo_description' => $seo->desc_seo,
                'created_by' => null,
                'updated_by' => null,
                'created_at' => $seo->created_at,
                'updated_at' => $seo->updated_at,
            ];

            $this->planned($target, 1);
            $this->record($target, $data, "[{$pageKey} -> pages#{$id}] {$title}");
            $this->targetIds[$target][$pageKey] = $id;

            if (! $dryRun) {
                DB::table($target)->insert($data);
                $this->targetIds[$target][$id] = $id;
            }
        }
    }

    protected function importSeoMetas(bool $dryRun): void
    {
        $target = 'seo_meta';
        $this->stats[$target] = $this->stats[$target] ?? ['planned' => 0];

        $rows = DB::connection(self::CONN)->table('public.seos')->orderBy('id')->get();
        $seen = [];

        foreach ($rows as $row) {
            $pageKey = trim((string) $row->page);
            $pageId = $this->targetIds['pages'][$pageKey] ?? null;
            if (! $pageId) {
                $this->skipped($target, 'no_page', "seos#{$row->id} page '{$row->page}' has no imported page");
                continue;
            }

            // legacy stored one seo record per agency per page; app has one seo_meta per page
            if (isset($seen[$pageId])) {
                $this->skipped($target, 'agency_duplicate', "seos#{$row->id} page '{$row->page}' (duplicate agency copy ignored)");
                continue;
            }
            $seen[$pageId] = true;

            $this->planned($target, 1);
            $this->record($target, [
                'model_type' => 'App\\Models\\Page',
                'model_id' => $pageId,
                'locale' => 'en',
                'meta_title' => $this->capText($row->title_seo, 250),
                'meta_description' => $row->desc_seo,
                'meta_keywords' => $row->tags_seo,
                'og_title' => null,
                'og_description' => null,
                'og_image' => null,
                'canonical_url' => $this->capText($row->canonical, 250),
                'schema_json' => $this->jsonOrNull($row->s_data),
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ], "seos#{$row->id} page '{$row->page}' -> Page#{$pageId}");

            if (! $dryRun) {
                DB::table($target)->insert([
                    'model_type' => 'App\\Models\\Page',
                    'model_id' => $pageId,
                    'locale' => 'en',
                    'meta_title' => $this->capText($row->title_seo, 250),
                    'meta_description' => $row->desc_seo,
                    'meta_keywords' => $row->tags_seo,
                    'og_title' => null,
                    'og_description' => null,
                    'og_image' => null,
                    'canonical_url' => $this->capText($row->canonical, 250),
                    'schema_json' => $this->jsonOrNull($row->s_data),
                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at,
                ]);
            }
        }
    }

    // ------------------------------------------------------------- misc

    protected function jsonEncode($value): ?string
    {
        if ($value === null) {
            return null;
        }
        if (is_array($value)) {
            $value = array_filter($value, fn($v) => $v !== null && trim((string) $v) !== '');
            if (! $value) {
                return null;
            }
        }

        return json_encode($value, JSON_UNESCAPED_UNICODE) ?: null;
    }

    protected function jsonSingle(mixed $value): ?string
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        return $this->jsonEncode(['en' => $value]);
    }

    protected function jsonOrNull(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (is_array($value) || is_object($value)) {
            return $this->jsonEncode($value);
        }

        json_decode((string) $value);

        return json_last_error() === JSON_ERROR_NONE ? (string) $value : null;
    }

    protected function centsToAmount(mixed $cents): ?float
    {
        if ($cents === null || $cents === '' || ! is_numeric($cents)) {
            return null;
        }

        return round(((float) $cents) / 100, 2);
    }

    protected function isNumeric(mixed $v): bool
    {
        return $v !== null && $v !== '' && is_numeric($v);
    }

    protected function cityName(int $cityId): ?string
    {
        return DB::table('cities')->where('id', $cityId)->value('name');
    }

    protected function stateName($stateId, array $stateTrans): ?string
    {
        if (! $stateId) {
            return null;
        }
        $localeRows = $stateTrans[(int) $stateId] ?? [];

        return $this->pick($localeRows['en'] ?? [], ['name']) ?? $this->pick($localeRows['ar'] ?? [], ['name']);
    }

    protected function printReport(): void
    {
        $this->newLine();
        $this->info('=============== LEGACY IMPORT REPORT ===============');

        $dryRun = (bool) $this->option('dry-run');
        $this->line('Mode: ' . ($dryRun ? 'DRY-RUN (nothing written)' : 'WRITE (MySQL rows inserted)'));

        $this->newLine();
        $this->info('Per-table summary');

        $headers = ['Step', 'Planned', 'Inserted/Skipped/Failed', 'Notes'];
        $rows = [];

        foreach ($this->stats as $table => $s) {
            $planned = $s['planned'] ?? 0;
            $inserted = $s['inserted'] ?? 0;
            $skipped = $s['skipped'] ?? 0;
            $failed = $s['failed'] ?? 0;
            $notes = count($s['summary'] ?? []);
            $counters = $dryRun ? $planned : $inserted;
            $rows[] = [$table, $counters, "ins={$inserted} skip={$skipped} fail={$failed}", "samples={$notes}"];
        }

        $this->table($headers, $rows);

        $this->newLine();
        $this->info('Skip reasons (by table)');
        $anySkips = false;
        foreach ($this->stats as $table => $s) {
            foreach ($s['skip_reasons'] ?? [] as $reason => $count) {
                $anySkips = true;
                $this->line("- {$table}: skip reason '{$reason}' x {$count}");
            }
        }
        if (! $anySkips) {
            $this->line('none');
        }

        $this->newLine();
        $this->info('Duplicate/conflicting slugs resolved (count: ' . count($this->duplicates, COUNT_RECURSIVE) . ')');
        foreach ($this->duplicates as $scope => $list) {
            $this->line("- {$scope}: " . implode(', ', array_slice($list, 0, 20)) . (count($list) > 20 ? ' (+' . (count($list) - 20) . ' more)' : ''));
        }

        $this->newLine();
        $this->info('Missing required fields / failures');
        if ($this->missingRequired) {
            foreach ($this->missingRequired as $table => $reasons) {
                foreach ($reasons as $reason => $items) {
                    $this->line("- {$table}: {$reason} => " . count($items));
                }
            }
        } else {
            $this->line('none');
        }

        $this->newLine();
        $this->info('Orphan / integrity warnings (' . count($this->warnings) . ')');
        foreach (array_slice($this->warnings, 0, 30) as $w) {
            $this->line("- {$w}");
        }
        if (count($this->warnings) > 30) {
            $this->line('... (+' . (count($this->warnings) - 30) . ' more)');
        }

        $this->newLine();
        $this->info('Unsupported legacy tables/fields (not imported)');
        $this->line('- bookings, package_bookings, tour_bookings, orders, order_items, invoices, leads: legacy has 0 rows; schema-only.');
        $this->line('- settings/agency_settings/g_mailer_settings: CMS admin config, no direct Laravel settings equivalent.');
        $this->line('- active_storage attachments (2775): image BINARIES are not present in the dump; metadata preserved in report only.');
        $this->line('- non-en/ar legacy locales (fr, de, tr, etc.): app supports only en/ar.');
        $this->line('- legacy landing_*/scripts/popups/etc: no Laravel equivalents (static CMS page SEO imported into pages/seo_meta).');

        $this->newLine();
        if ($dryRun) {
            $this->warn('DRY-RUN complete. Review this report before running `php artisan legacy:import`.');
        } else {
            $this->info('Import complete. A dry-run is always available: `php artisan legacy:import --dry-run`.');
        }
    }
}
