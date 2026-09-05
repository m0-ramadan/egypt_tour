<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // dashboard
            'dashboard.view',

            // admins
            'admins.view',
            'admins.create',
            'admins.edit',
            'admins.delete',
            'admins.toggle-status',
            'admins.reset-password',
            'admins.export',

            // roles
            'roles.view',
            'roles.create',
            'roles.edit',
            'roles.delete',
            'roles.assign',
            'roles.permissions',

            // permissions
            'permissions.view',
            'permissions.create',
            'permissions.edit',
            'permissions.delete',
            'permissions.generate',

            // languages
            'languages.view',
            'languages.create',
            'languages.edit',
            'languages.delete',
            'languages.toggle',
            'languages.set-default',

            // countries
            'countries.view',
            'countries.create',
            'countries.edit',
            'countries.delete',

            // cities
            'cities.view',
            'cities.create',
            'cities.edit',
            'cities.delete',

            // regions
            'regions.view',
            'regions.create',
            'regions.edit',
            'regions.delete',

            // destinations
            'destinations.view',
            'destinations.create',
            'destinations.edit',
            'destinations.delete',

            // package categories
            'package-categories.view',
            'package-categories.create',
            'package-categories.edit',
            'package-categories.delete',
            'package-categories.toggle-status',

            // packages
            'packages.view',
            'packages.create',
            'packages.edit',
            'packages.delete',
            'packages.toggle-status',
            'packages.duplicate',
            'packages.export',
            'packages.create-with-ai',
            'packages.store-with-ai',
            'packages.ai-enhance',
            'packages.ai-translate',
            'packages.ai-generate-seo',

            // package prices
            'package-prices.view',
            'package-prices.create',
            'package-prices.edit',
            'package-prices.delete',
            'package-prices.by-package',

            // bookings
            'bookings.view',
            'bookings.create',
            'bookings.edit',
            'bookings.delete',
            'bookings.print',
            'bookings.update-status',
            'bookings.export',

            // inquiries
            'inquiries.view',
            'inquiries.create',
            'inquiries.edit',
            'inquiries.delete',
            'inquiries.convert',

            // clients
            'clients.view',
            'clients.create',
            'clients.edit',
            'clients.delete',

            // users
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',
            'users.verify',
            'users.reject',
            'users.restore',
            'users.force-delete',
            'users.wallet-control',
            'users.package-control',
            'users.export',
            'users.stats',

            // articles
            'articles.view',
            'articles.create',
            'articles.edit',
            'articles.delete',
            'articles.toggle-status',
            'articles.toggle-featured',
            'articles.bulk-actions',
            'articles.statistics',
            'articles.create-with-ai',
            'articles.store-with-ai',
            'articles.ai-generate',
            'articles.ai-generate-full',
            'articles.ai-enhance',
            'articles.ai-translate',

            // article categories
            'article-categories.view',
            'article-categories.create',
            'article-categories.edit',
            'article-categories.delete',

            // static pages
            'static-pages.view',
            'static-pages.create',
            'static-pages.edit',
            'static-pages.delete',
            'static-pages.bulk-action',
            'static-pages.edit-with-ai',
            'static-pages.ai-enhance',
            'static-pages.ai-translate',
            'static-pages.ai-generate',

            // faqs
            'faqs.view',
            'faqs.create',
            'faqs.edit',
            'faqs.delete',
            'faqs.toggle-status',

            // testimonials
            'testimonials.view',
            'testimonials.create',
            'testimonials.edit',
            'testimonials.delete',
            'testimonials.approve',

            // pages
            'pages.view',
            'pages.create',
            'pages.edit',
            'pages.delete',

            // menus
            'menus.view',
            'menus.create',
            'menus.edit',
            'menus.delete',
            'menus.items.view',
            'menus.items.create',
            'menus.items.edit',
            'menus.items.delete',

            // seo meta
            'seo-meta.view',
            'seo-meta.create',
            'seo-meta.edit',
            'seo-meta.delete',
            'seo-meta.by-model',

            // seo redirects
            'seo-redirects.view',
            'seo-redirects.create',
            'seo-redirects.edit',
            'seo-redirects.delete',

            // translations
            'translations.view',
            'translations.create',
            'translations.edit',
            'translations.delete',
            'translations.by-model',

            // payment methods
            'payment-methods.view',
            'payment-methods.create',
            'payment-methods.edit',
            'payment-methods.delete',
            'payment-methods.toggle-status',

            // payments
            'payments.view',
            'payments.create',
            'payments.edit',
            'payments.delete',

            // currencies
            'currencies.view',
            'currencies.create',
            'currencies.edit',
            'currencies.delete',
            'currencies.toggle-status',
            'currencies.set-default',

            // social media
            'social-media.view',
            'social-media.edit',
            'social-media.bulk-update',

            // settings
            'settings.view',
            'settings.edit',
            'settings.pages',
            'settings.update',
            'settings.update-pages',
            'settings.smtp',
            'settings.general',
            'settings.communication',
            'settings.files',
            'settings.clear-cache',
            'settings.toggle-maintenance',
            'settings.system-status',

            // subscriptions
            'subscriptions.view',
            'subscriptions.create',
            'subscriptions.edit',
            'subscriptions.delete',

            // contact us
            'contact-us.view',
            'contact-us.show',
            'contact-us.reply',
            'contact-us.delete',
            'contact-us.status',
            'contact-us.bulk-status',
            'contact-us.bulk-destroy',

            // communications
            'communications.view',
            'communications.show',
            'communications.client',
            'communications.booking',
            'communications.inquiry',

            // notifications
            'notifications.view',
            'notifications.create',
            'notifications.edit',
            'notifications.delete',
            'notifications.send',

            // visitors
            'visitors.view',
            'visitors.chart',
            'visitors.statistics',

            // banners
            'banners.view',
            'banners.create',
            'banners.edit',
            'banners.delete',
            'banners.toggle-status',

            // banner items
            'banner-items.view',
            'banner-items.create',
            'banner-items.edit',
            'banner-items.delete',
            'banner-items.toggle-status',
            'banner-items.reorder',

            // ads
            'ads.view',
            'ads.create',
            'ads.edit',
            'ads.delete',

            // errors
            'errors.view',
            'errors.php-errors',
            'errors.search',
            'errors.download',
            'errors.delete',
            'errors.clear-all',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'admin',
            ]);
        }

        $superAdmin = Role::firstOrCreate([
            'name' => 'Super Admin',
            'guard_name' => 'admin',
        ]);

        $superAdmin->syncPermissions(
            Permission::where('guard_name', 'admin')->pluck('name')->toArray()
        );
    }
}
