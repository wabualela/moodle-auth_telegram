<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace auth_telegram;

/**
 * Unit tests for auth_telegram\api.
 *
 * @package    auth_telegram
 * @category   test
 * @copyright  2026 Wail Abualela <wailabualela@email.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers \auth_telegram\api
 */
final class api_test extends \advanced_testcase {

    // ---------------------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------------------

    /**
     * Create a confirmed linked_login record directly (bypasses api::link_login
     * duplicate-check so we can set up precise preconditions).
     */
    private function create_link(int $userid, string $telegramid, string $token = '', int $expires = 0): linked_login {
        $record = new \stdClass();
        $record->userid              = $userid;
        $record->telegramid          = $telegramid;
        $record->confirmtoken        = $token;
        $record->confirmtokenexpires = $expires;

        $login = new linked_login(0, $record);
        $login->create();
        return $login;
    }

    // ---------------------------------------------------------------------------
    // api::get_linked_userid
    // ---------------------------------------------------------------------------

    /**
     * @covers \auth_telegram\api::get_linked_userid
     */
    public function test_get_linked_userid_returns_zero_when_no_link(): void {
        $this->resetAfterTest();

        $this->assertSame(0, api::get_linked_userid('9999999999'));
    }

    /**
     * @covers \auth_telegram\api::get_linked_userid
     */
    public function test_get_linked_userid_returns_userid_for_confirmed_link(): void {
        $this->resetAfterTest();

        $user = $this->getDataGenerator()->create_user();
        $this->create_link($user->id, '1111111111');

        $this->assertSame((int) $user->id, api::get_linked_userid('1111111111'));
    }

    /**
     * A pending (unconfirmed) record must not be treated as a valid link.
     *
     * @covers \auth_telegram\api::get_linked_userid
     */
    public function test_get_linked_userid_ignores_pending_token(): void {
        $this->resetAfterTest();

        $user = $this->getDataGenerator()->create_user();
        $this->create_link($user->id, '2222222222', 'pendingtoken123', time() + 1800);

        $this->assertSame(0, api::get_linked_userid('2222222222'));
    }

    // ---------------------------------------------------------------------------
    // api::link_login
    // ---------------------------------------------------------------------------

    /**
     * @covers \auth_telegram\api::link_login
     */
    public function test_link_login_creates_confirmed_record(): void {
        global $DB;
        $this->resetAfterTest();

        $user = $this->getDataGenerator()->create_user();
        api::link_login($user->id, '3333333333');

        $record = $DB->get_record(linked_login::TABLE, ['userid' => $user->id, 'telegramid' => '3333333333']);
        $this->assertNotFalse($record);
        $this->assertSame('', $record->confirmtoken);
        $this->assertSame('0', $record->confirmtokenexpires);
    }

    /**
     * Calling link_login a second time for the same pair must not create a duplicate.
     *
     * @covers \auth_telegram\api::link_login
     */
    public function test_link_login_is_idempotent(): void {
        global $DB;
        $this->resetAfterTest();

        $user = $this->getDataGenerator()->create_user();
        api::link_login($user->id, '4444444444');
        api::link_login($user->id, '4444444444');

        $count = $DB->count_records(linked_login::TABLE, ['userid' => $user->id, 'telegramid' => '4444444444']);
        $this->assertSame(1, $count);
    }

    // ---------------------------------------------------------------------------
    // api::user_deleted
    // ---------------------------------------------------------------------------

    /**
     * Deleting a Moodle user must remove their linked_login row so the Telegram
     * identity can be re-linked to a new account without a duplicate-key error.
     *
     * @covers \auth_telegram\api::user_deleted
     */
    public function test_user_deleted_removes_linked_login(): void {
        global $DB;
        $this->resetAfterTest();

        $user = $this->getDataGenerator()->create_user();
        $this->create_link($user->id, '5555555555');

        // Fire the event the same way Moodle core does during delete_user().
        $event = \core\event\user_deleted::create([
            'objectid' => $user->id,
            'relateduserid' => $user->id,
            'context' => \context_system::instance(),
            'other' => [
                'username'  => $user->username,
                'email'     => $user->email,
                'idnumber'  => $user->idnumber,
                'picture'   => $user->picture,
                'mnethostid' => $user->mnethostid,
            ],
        ]);
        api::user_deleted($event);

        $this->assertFalse($DB->record_exists(linked_login::TABLE, ['userid' => $user->id]));
    }

    /**
     * user_deleted must remove ALL linked logins for the user, not just one.
     *
     * @covers \auth_telegram\api::user_deleted
     */
    public function test_user_deleted_removes_all_links_for_user(): void {
        global $DB;
        $this->resetAfterTest();

        $user = $this->getDataGenerator()->create_user();
        $this->create_link($user->id, '6666666661');
        $this->create_link($user->id, '6666666662');

        $event = \core\event\user_deleted::create([
            'objectid' => $user->id,
            'relateduserid' => $user->id,
            'context' => \context_system::instance(),
            'other' => [
                'username'  => $user->username,
                'email'     => $user->email,
                'idnumber'  => $user->idnumber,
                'picture'   => $user->picture,
                'mnethostid' => $user->mnethostid,
            ],
        ]);
        api::user_deleted($event);

        $this->assertSame(0, $DB->count_records(linked_login::TABLE, ['userid' => $user->id]));
    }

    /**
     * user_deleted must not touch linked logins belonging to other users.
     *
     * @covers \auth_telegram\api::user_deleted
     */
    public function test_user_deleted_does_not_affect_other_users(): void {
        global $DB;
        $this->resetAfterTest();

        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();
        $this->create_link($user1->id, '7777777771');
        $this->create_link($user2->id, '7777777772');

        $event = \core\event\user_deleted::create([
            'objectid' => $user1->id,
            'relateduserid' => $user1->id,
            'context' => \context_system::instance(),
            'other' => [
                'username'  => $user1->username,
                'email'     => $user1->email,
                'idnumber'  => $user1->idnumber,
                'picture'   => $user1->picture,
                'mnethostid' => $user1->mnethostid,
            ],
        ]);
        api::user_deleted($event);

        $this->assertFalse($DB->record_exists(linked_login::TABLE, ['userid' => $user1->id]));
        $this->assertTrue($DB->record_exists(linked_login::TABLE, ['userid' => $user2->id]));
    }

    // ---------------------------------------------------------------------------
    // api::confirm_link_login
    // ---------------------------------------------------------------------------

    /**
     * @covers \auth_telegram\api::confirm_link_login
     */
    public function test_confirm_link_login_activates_pending_record(): void {
        global $DB;
        $this->resetAfterTest();

        $user    = $this->getDataGenerator()->create_user();
        $token   = random_string(32);
        $expires = time() + 1800;
        $this->create_link($user->id, '8888888888', $token, $expires);

        $result = api::confirm_link_login($user->id, '8888888888', $token);

        $this->assertTrue($result);

        $record = $DB->get_record(linked_login::TABLE, ['userid' => $user->id, 'telegramid' => '8888888888']);
        $this->assertSame('', $record->confirmtoken);
        $this->assertSame('0', $record->confirmtokenexpires);
    }

    /**
     * An expired token must be rejected and its record deleted.
     *
     * @covers \auth_telegram\api::confirm_link_login
     */
    public function test_confirm_link_login_rejects_expired_token(): void {
        global $DB;
        $this->resetAfterTest();

        $user    = $this->getDataGenerator()->create_user();
        $token   = random_string(32);
        $expired = time() - 1;
        $this->create_link($user->id, '9999999991', $token, $expired);

        $result = api::confirm_link_login($user->id, '9999999991', $token);

        $this->assertFalse($result);
        $this->assertFalse($DB->record_exists(linked_login::TABLE, ['userid' => $user->id, 'telegramid' => '9999999991']));
    }

    /**
     * A wrong token must be rejected and the record must remain untouched.
     *
     * @covers \auth_telegram\api::confirm_link_login
     */
    public function test_confirm_link_login_rejects_wrong_token(): void {
        global $DB;
        $this->resetAfterTest();

        $user  = $this->getDataGenerator()->create_user();
        $token = random_string(32);
        $this->create_link($user->id, '9999999992', $token, time() + 1800);

        $result = api::confirm_link_login($user->id, '9999999992', 'wrongtoken');

        $this->assertFalse($result);
        $this->assertTrue($DB->record_exists(linked_login::TABLE, [
            'userid'       => $user->id,
            'telegramid'   => '9999999992',
            'confirmtoken' => $token,
        ]));
    }

    /**
     * Empty / missing parameters must be rejected immediately.
     *
     * @covers \auth_telegram\api::confirm_link_login
     */
    public function test_confirm_link_login_rejects_empty_params(): void {
        $this->resetAfterTest();

        $this->assertFalse(api::confirm_link_login(0, '1234567890', 'token'));
        $this->assertFalse(api::confirm_link_login(1, '', 'token'));
        $this->assertFalse(api::confirm_link_login(1, '1234567890', ''));
    }

    // ---------------------------------------------------------------------------
    // api::send_confirm_link_login_email
    // ---------------------------------------------------------------------------

    /**
     * Sending a confirm-link email must create a pending linked_login record
     * with a non-empty token expiring ~30 minutes in the future.
     *
     * @covers \auth_telegram\api::send_confirm_link_login_email
     */
    public function test_send_confirm_link_login_email_creates_pending_record(): void {
        global $DB;
        $this->resetAfterTest();

        $sink = $this->redirectEmails();

        $user        = $this->getDataGenerator()->create_user();
        $telegramdata = ['id' => '1234509876', 'first_name' => 'Test'];

        $before = time();
        $result = api::send_confirm_link_login_email($telegramdata, $user);
        $after  = time();

        $this->assertTrue($result);

        $record = $DB->get_record(linked_login::TABLE, ['userid' => $user->id, 'telegramid' => '1234509876']);
        $this->assertNotFalse($record);
        $this->assertNotEmpty($record->confirmtoken);
        $this->assertGreaterThanOrEqual($before + 1799, (int) $record->confirmtokenexpires);
        $this->assertLessThanOrEqual($after + 1801, (int) $record->confirmtokenexpires);

        $emails = $sink->get_messages();
        $this->assertCount(1, $emails);
        $this->assertSame($user->email, $emails[0]->to);

        $sink->close();
    }

    // ---------------------------------------------------------------------------
    // api::get_linked_logins
    // ---------------------------------------------------------------------------

    /**
     * get_linked_logins returns only confirmed (empty-token) records for the user.
     *
     * @covers \auth_telegram\api::get_linked_logins
     */
    public function test_get_linked_logins_returns_confirmed_only(): void {
        $this->resetAfterTest();

        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $this->create_link($user->id, '1010101010');
        $this->create_link($user->id, '2020202020', 'pendingtoken', time() + 1800);

        $logins = api::get_linked_logins($user->id);

        $this->assertCount(1, $logins);
        $this->assertSame('1010101010', reset($logins)->get('telegramid'));
    }

    /**
     * get_linked_logins returns an empty array when no links exist.
     *
     * @covers \auth_telegram\api::get_linked_logins
     */
    public function test_get_linked_logins_returns_empty_for_no_links(): void {
        $this->resetAfterTest();

        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $this->assertCount(0, api::get_linked_logins($user->id));
    }

    /**
     * get_linked_logins throws when called while logged in as another user.
     *
     * @covers \auth_telegram\api::get_linked_logins
     */
    public function test_get_linked_logins_throws_while_loggedinas(): void {
        $this->resetAfterTest();

        $admin  = get_admin();
        $user   = $this->getDataGenerator()->create_user();
        $this->setAdminUser();
        \core\session\manager::loginas($user->id, \context_system::instance());

        $this->expectException(\moodle_exception::class);
        api::get_linked_logins($user->id);
    }

    // ---------------------------------------------------------------------------
    // api::delete_linked_login
    // ---------------------------------------------------------------------------

    /**
     * @covers \auth_telegram\api::delete_linked_login
     */
    public function test_delete_linked_login_removes_record(): void {
        global $DB;
        $this->resetAfterTest();

        $user  = $this->getDataGenerator()->create_user();
        $this->setUser($user);
        $login = $this->create_link($user->id, '3030303030');

        api::delete_linked_login($login->get('id'));

        $this->assertFalse($DB->record_exists(linked_login::TABLE, ['id' => $login->get('id')]));
    }

    /**
     * Attempting to delete another user's linked login must fail.
     *
     * @covers \auth_telegram\api::delete_linked_login
     */
    public function test_delete_linked_login_rejects_other_users_record(): void {
        $this->resetAfterTest();

        $owner      = $this->getDataGenerator()->create_user();
        $attacker   = $this->getDataGenerator()->create_user();
        $login      = $this->create_link($owner->id, '4040404040');

        $this->setUser($attacker);

        $this->expectException(\moodle_exception::class);
        api::delete_linked_login($login->get('id'));
    }

    /**
     * delete_linked_login throws while logged in as another user.
     *
     * @covers \auth_telegram\api::delete_linked_login
     */
    public function test_delete_linked_login_throws_while_loggedinas(): void {
        $this->resetAfterTest();

        $user  = $this->getDataGenerator()->create_user();
        $login = $this->create_link($user->id, '5050505050');

        $this->setAdminUser();
        \core\session\manager::loginas($user->id, \context_system::instance());

        $this->expectException(\moodle_exception::class);
        api::delete_linked_login($login->get('id'));
    }
}
