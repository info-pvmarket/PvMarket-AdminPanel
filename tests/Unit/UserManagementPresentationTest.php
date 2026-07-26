<?php

namespace Tests\Unit;

use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use PHPUnit\Framework\TestCase;

class UserManagementPresentationTest extends TestCase
{
    public function test_role_display_name_uses_the_assigned_role(): void
    {
        $user = new User();
        $user->setRelation('role', new Role(['role' => 'EPC Company']));

        $this->assertSame('EPC Company', $user->role_display_name);
    }

    public function test_role_display_name_has_a_clear_fallback(): void
    {
        $user = new User();
        $user->setRelation('role', null);

        $this->assertSame('No Role', $user->role_display_name);
    }

    public function test_company_record_is_authoritative_for_verification_status(): void
    {
        $user = new User(['company_verified' => false]);
        $user->setRelation('company', new Company(['seller_verified' => true]));

        $this->assertTrue($user->isCompanyVerified());
    }

    public function test_user_verification_status_is_used_when_no_company_exists(): void
    {
        $user = new User(['company_verified' => true]);
        $user->setRelation('company', null);

        $this->assertTrue($user->isCompanyVerified());
    }

    public function test_admin_roles_are_explicitly_excluded_from_user_management(): void
    {
        $this->assertSame(['super-admin', 'admin'], Role::ADMIN_SLUGS);
    }

    public function test_management_status_updates_both_user_status_fields(): void
    {
        $user = new User(['is_active' => true, 'c_active' => true]);

        $user->syncActiveStatus(false);

        $this->assertFalse($user->is_active);
        $this->assertFalse($user->c_active);
        $this->assertFalse($user->isActiveForManagement());
    }

    public function test_management_status_uses_the_legacy_status_as_a_fallback(): void
    {
        $user = new User(['c_active' => false]);

        $this->assertFalse($user->isActiveForManagement());
    }
}
