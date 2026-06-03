<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MissingDataSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = DB::table('users')->value('id') ?? 1;
        $now = now();

        // ── Banned Bowlers ───────────────────────────────────────────
        $bans = [
            [
                'player_id'    => 3,
                'reason'       => 'Illegal bowling action confirmed by match officials after video analysis',
                'banned_from'  => '2025-04-10',
                'banned_until' => '2025-07-10',
                'is_active'    => false,
                'notes'        => 'Ban lifted after remedial coaching and re-testing. Cleared to bowl.',
                'issued_by'    => $adminId,
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
            [
                'player_id'    => 7,
                'reason'       => 'Reported for suspect action in two consecutive matches — elbow bend exceeds 15° limit',
                'banned_from'  => '2025-09-01',
                'banned_until' => null,
                'is_active'    => true,
                'notes'        => 'Player must undergo ICC-certified biomechanical assessment before returning.',
                'issued_by'    => $adminId,
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
            [
                'player_id'    => 12,
                'reason'       => 'Action reported by two umpires; formal review panel confirmed suspect delivery',
                'banned_from'  => '2025-11-15',
                'banned_until' => '2026-02-15',
                'is_active'    => true,
                'notes'        => null,
                'issued_by'    => $adminId,
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
            [
                'player_id'    => 18,
                'reason'       => 'Threw a delivery — umpire called a no-ball and reported the action on-field',
                'banned_from'  => '2024-07-20',
                'banned_until' => '2024-10-20',
                'is_active'    => false,
                'notes'        => 'Completed ban. Cleared after retest. No further action required.',
                'issued_by'    => $adminId,
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
        ];

        foreach ($bans as $ban) {
            $playerId = $ban['player_id'];
            if (!DB::table('players')->where('id', $playerId)->exists()) {
                continue;
            }
            DB::table('banned_bowlers')->insertOrIgnore($ban);
            if ($ban['is_active']) {
                DB::table('players')->where('id', $playerId)->update(['bowling_action_status' => 'banned']);
            }
        }

        // ── Meeting / Notice Board Content ───────────────────────────
        DB::table('site_settings')->updateOrInsert(
            ['key' => 'meeting_content'],
            [
                'value' => '<h2 style="color:#1B8A4E;">📢 RCL Season 35 — Important Announcement</h2>
<p>Dear players, officials, and supporters,</p>
<p>The <strong>35th Edition</strong> of the Royal Champions League is officially underway! The VCC is pleased to announce the following updates for this season:</p>
<ul>
  <li>🏏 <strong>16 teams</strong> are participating across two groups.</li>
  <li>📅 All matches will be played on <strong>Sundays</strong> from 8:00 AM onwards.</li>
  <li>🏆 The <strong>Final</strong> is scheduled for <strong>August 2026</strong>.</li>
  <li>💰 Registration fee: <strong>PKR 5,000 per team</strong> — due by 30 May 2026.</li>
</ul>
<hr>
<h3>🤝 Meeting Notice</h3>
<p>A <strong>general body meeting</strong> of all team captains is called on <strong>25 May 2026 at 6:00 PM</strong> at the VCC Office. Attendance is mandatory. Agenda:</p>
<ol>
  <li>Fixture draw for Group Stage</li>
  <li>Umpiring panel introduction</li>
  <li>Code of conduct reminder</li>
  <li>Any other business</li>
</ol>
<p style="color:#DC2626;font-weight:700;">⚠️ Teams with unpaid fines from Edition 34 must clear dues before registration is confirmed.</p>
<p style="color:#6B8F74;font-size:.85em;">— Village Cricket Council (VCC), ' . date('d M Y') . '</p>',
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );

        $this->command->info('✅ Missing data seeded: banned bowlers + meeting content.');
    }
}
