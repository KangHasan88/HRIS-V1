<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Company;
use App\Models\LegalEntity;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $superAdminRole = Role::where('name', 'Super Admin')->first();
        $hrAdminRole = Role::where('name', 'HR Admin')->first();
        $payrollAdminRole = Role::where('name', 'Payroll Admin')->first();
        $managerRole = Role::where('name', 'Manager')->first();
        $employeeRole = Role::where('name', 'Employee')->first();
        $company = Company::where('code', 'BMP')->first();
        $legalEntity = LegalEntity::where('code', 'BMP-PT')->first();
        $headOffice = Branch::where('code', 'BMP-HO')->first();
        $siteBranch = Branch::where('code', 'BMP-SITE-01')->first();

        // 1. Super Admin
        User::updateOrCreate(
            ['email' => 'admin@kurmigo.id'],
            [
                'name' => 'Raka Mahendra',
                'password' => Hash::make('password123'),
                'role_id' => $superAdminRole->id,
                'company_id' => $company?->id,
                'legal_entity_id' => $legalEntity?->id,
                'branch_id' => $headOffice?->id,
            ]
        );

        // 2. HR Admin
        User::updateOrCreate(
            ['email' => 'ayu@kurmigo.id'],
            [
                'name' => 'Ayu Prameswari',
                'password' => Hash::make('password123'),
                'role_id' => $hrAdminRole->id,
                'company_id' => $company?->id,
                'legal_entity_id' => $legalEntity?->id,
                'branch_id' => $headOffice?->id,
            ]
        );

        // 3. Payroll Admin
        User::updateOrCreate(
            ['email' => 'citra@kurmigo.id'],
            [
                'name' => 'Citra Lestari',
                'password' => Hash::make('password123'),
                'role_id' => $payrollAdminRole->id,
                'company_id' => $company?->id,
                'legal_entity_id' => $legalEntity?->id,
                'branch_id' => $headOffice?->id,
            ]
        );

        // 4. Manager
        User::updateOrCreate(
            ['email' => 'bima@kurmigo.id'],
            [
                'name' => 'Bima Santoso',
                'password' => Hash::make('password123'),
                'role_id' => $managerRole->id,
                'company_id' => $company?->id,
                'legal_entity_id' => $legalEntity?->id,
                'branch_id' => $siteBranch?->id,
            ]
        );

        // 5. Staf / Employee (Dimas Wirawan)
        User::updateOrCreate(
            ['email' => 'dimas@kurmigo.id'],
            [
                'name' => 'Dimas Wirawan',
                'password' => Hash::make('password123'),
                'role_id' => $employeeRole->id,
                'company_id' => $company?->id,
                'legal_entity_id' => $legalEntity?->id,
                'branch_id' => $siteBranch?->id,
            ]
        );

        // 6. Staf / Employee (Nadia Putri)
        User::updateOrCreate(
            ['email' => 'nadia@kurmigo.id'],
            [
                'name' => 'Nadia Putri',
                'password' => Hash::make('password123'),
                'role_id' => $employeeRole->id,
                'company_id' => $company?->id,
                'legal_entity_id' => $legalEntity?->id,
                'branch_id' => $siteBranch?->id,
            ]
        );
    }
}
