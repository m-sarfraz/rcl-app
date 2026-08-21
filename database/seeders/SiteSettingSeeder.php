<?php

namespace Database\Seeders;

use App\Models\SiteSetting;
use App\Services\ScoringAccessService;
use Illuminate\Database\Seeder;

/**
 * System settings, including the Scoring Access Passkey.
 *
 * The passkey is the single credential that unlocks live scoring in the mobile
 * app. It is stored in plaintext deliberately — an administrator has to be able
 * to read it back out to a scorer standing at the ground — and is *verified*
 * with `hash_equals` behind a 5-per-minute throttle, which is where the
 * protection actually belongs.
 */
class SiteSettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ScoringAccessService::SETTING_KEY => ScoringAccessService::DEFAULT_KEY,
            'league_name'      => 'Royal Champions League',
            'governing_body'   => 'Village Cricket Council',
            'contact_email'    => 'info@rcl.local',
            'default_overs'    => '10',
            'ball_fraction'    => '0.17',
        ];

        foreach ($settings as $key => $value) {
            // Never clobber a key an administrator has already changed.
            if (SiteSetting::where('key', $key)->exists()) {
                continue;
            }
            SiteSetting::set($key, $value);
        }

        if (! SiteSetting::get('meeting_content')) {
            SiteSetting::set('meeting_content', $this->meetingNotice());
        }

        $key = SiteSetting::get(ScoringAccessService::SETTING_KEY);

        $this->command?->info('✅ Site settings seeded.');
        $this->command?->line('   Scoring passkey : '.$key.'  ('.mb_strlen((string) $key).' characters)');
        $this->command?->line('   Change it at    : /admin/settings/scoring-key');
    }

    private function meetingNotice(): string
    {
        return <<<'HTML'
<h2 style="color:#0F5230;">📢 RCL — Notice Board</h2>
<p>Live scoring now runs entirely from the <strong>RCL mobile app</strong>. Match officials should:</p>
<ol>
  <li>Open the app and go to <strong>Score</strong>.</li>
  <li>Enter the 10-character Scoring Passkey issued by the VCC office.</li>
  <li>Pick the fixture, confirm the toss, and name both playing XIs.</li>
  <li>Score ball by ball. The app keeps working with no signal and syncs when it reconnects.</li>
  <li>Tap <strong>Finalize Match</strong> when play ends — stats and the points table update immediately.</li>
</ol>
<p style="color:#B45309;font-weight:600;">The passkey is per-season. Do not share it outside the appointed scorers.</p>
HTML;
    }
}
