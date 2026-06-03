<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Define all system permissions grouped by module
        $permissions = [
            'matches'  => ['view_matches','create_matches','edit_matches','delete_matches','score_matches','manage_results'],
            'editions' => ['view_editions','create_editions','edit_editions','delete_editions'],
            'teams'    => ['view_teams','create_teams','edit_teams','delete_teams'],
            'players'  => ['view_players','create_players','edit_players','delete_players','manage_suspensions'],
            'fines'    => ['view_fines','create_fines','edit_fines','delete_fines','manage_fine_status'],
            'finance'  => ['view_finance','create_finance','delete_finance'],
            'vcc'      => ['view_vcc','manage_vcc'],
            'polls'    => ['view_polls','manage_polls'],
            'roles'    => ['view_roles','manage_roles','manage_users'],
            'notifications' => ['manage_notifications'],
            'reports'  => ['view_reports','export_reports'],
        ];

        foreach ($permissions as $module => $perms) {
            foreach ($perms as $perm) {
                $label = ucwords(str_replace('_', ' ', $perm));
                Permission::firstOrCreate(
                    ['name' => $perm],
                    ['module' => $module, 'display_name' => $label, 'label' => $label]
                );
            }
        }

        // System Roles
        $roles = [
            ['name' => 'super_admin',           'display_name' => 'Super Admin',             'description' => 'Full system access — all permissions granted.', 'is_system' => true, 'slug' => 'super-admin'],
            ['name' => 'scorer',                'display_name' => 'Match Scorer',            'description' => 'Can access live scoring console only.',           'is_system' => true, 'slug' => 'scorer'],
            ['name' => 'umpire',                'display_name' => 'Umpire',                  'description' => 'Assigned to matches as official umpire.',          'is_system' => true, 'slug' => 'umpire'],
            ['name' => 'treasurer',             'display_name' => 'Treasury Auditor',        'description' => 'Finance & fine management access.',               'is_system' => true, 'slug' => 'treasurer'],
            ['name' => 'team_manager',          'display_name' => 'Team Manager',            'description' => 'Manage team roster and players.',                  'is_system' => true, 'slug' => 'team-manager'],
            ['name' => 'disciplinary_committee','display_name' => 'Disciplinary Committee',  'description' => 'Manage fines and player suspensions.',             'is_system' => true, 'slug' => 'disciplinary-committee'],
        ];

        foreach ($roles as $roleData) {
            Role::firstOrCreate(['name' => $roleData['name']], $roleData);
        }

        // Assign permissions to scorer role
        $scorerRole = Role::where('name','scorer')->first();
        $scorerRole?->permissions()->sync(
            Permission::whereIn('name',['view_matches','score_matches'])->pluck('id')
        );

        // Assign permissions to treasurer role
        $treasurerRole = Role::where('name','treasurer')->first();
        $treasurerRole?->permissions()->sync(
            Permission::whereIn('module',['finance','fines'])->pluck('id')
        );

        // Assign permissions to disciplinary committee
        $discRole = Role::where('name','disciplinary_committee')->first();
        $discRole?->permissions()->sync(
            Permission::whereIn('name',['view_fines','create_fines','edit_fines','manage_fine_status','manage_suspensions','view_players'])->pluck('id')
        );

        // Assign permissions to team_manager
        $tmRole = Role::where('name','team_manager')->first();
        $tmRole?->permissions()->sync(
            Permission::whereIn('module',['teams','players'])->pluck('id')
        );
    }
}
