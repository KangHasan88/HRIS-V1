<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Company;
use App\Models\LegalEntity;
use Illuminate\Database\Seeder;

class OrganizationSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::updateOrCreate(
            ['code' => 'BMP'],
            [
                'name' => 'PT Bumi Mineral Persada',
                'brand_name' => 'BMP',
                'tax_id' => '01.234.567.8-901.000',
                'address' => 'Jakarta, Indonesia',
                'is_active' => true,
            ]
        );

        $legalEntity = LegalEntity::updateOrCreate(
            ['code' => 'BMP-PT'],
            [
                'company_id' => $company->id,
                'name' => 'PT Bumi Mineral Persada',
                'npwp' => '01.234.567.8-901.000',
                'nib' => '9120000000001',
                'address' => 'Jakarta, Indonesia',
                'is_primary' => true,
                'is_active' => true,
            ]
        );

        Branch::updateOrCreate(
            ['code' => 'BMP-HO'],
            [
                'company_id' => $company->id,
                'legal_entity_id' => $legalEntity->id,
                'name' => 'Head Office',
                'city' => 'Jakarta',
                'address' => 'Jakarta, Indonesia',
                'is_head_office' => true,
                'is_active' => true,
            ]
        );

        Branch::updateOrCreate(
            ['code' => 'BMP-SITE-01'],
            [
                'company_id' => $company->id,
                'legal_entity_id' => $legalEntity->id,
                'name' => 'Site Operasional 01',
                'city' => 'Morowali',
                'address' => 'Morowali, Sulawesi Tengah',
                'is_head_office' => false,
                'is_active' => true,
            ]
        );
    }
}
