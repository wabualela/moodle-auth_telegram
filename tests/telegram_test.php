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
 * Unit tests for auth_telegram\telegram.
 *
 * @package    auth_telegram
 * @category   test
 * @copyright  2026 Wail Abualela <wailabualela@email.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers \auth_telegram\telegram
 */
final class telegram_test extends \advanced_testcase {

    /** Minimal Telegram userinfo payload. */
    private const TGDATA = [
        'id'         => '9876543210',
        'first_name' => 'Test',
        'last_name'  => 'User',
    ];

    // ---------------------------------------------------------------------------
    // telegram::create_user
    // ---------------------------------------------------------------------------

    /**
     * create_user must insert a real Moodle user and return a record with ->id.
     *
     * @covers \auth_telegram\telegram::create_user
     */
    public function test_create_user_creates_moodle_account(): void {
        global $DB;
        $this->resetAfterTest();

        $email = 'tgtest@example.com';
        $user  = telegram::create_user($email, self::TGDATA);

        $this->assertNotEmpty($user->id);
        $this->assertTrue($DB->record_exists('user', ['id' => $user->id]));
    }

    /**
     * auth must be set to 'telegram' and the account must be confirmed.
     *
     * @covers \auth_telegram\telegram::create_user
     */
    public function test_create_user_sets_auth_and_confirmed(): void {
        global $DB;
        $this->resetAfterTest();

        $user   = telegram::create_user('authcheck@example.com', self::TGDATA);
        $record = $DB->get_record('user', ['id' => $user->id]);

        $this->assertSame('telegram', $record->auth);
        $this->assertSame('1', $record->confirmed);
    }

    /**
     * The username is derived from the email via clean_param(PARAM_USERNAME).
     *
     * @covers \auth_telegram\telegram::create_user
     */
    public function test_create_user_derives_username_from_email(): void {
        global $DB;
        $this->resetAfterTest();

        $email  = 'User+Test@Example.COM';
        $user   = telegram::create_user($email, self::TGDATA);
        $record = $DB->get_record('user', ['id' => $user->id]);

        $this->assertSame(clean_param($email, PARAM_USERNAME), $record->username);
    }

    /**
     * First and last names are populated from Telegram data.
     *
     * @covers \auth_telegram\telegram::create_user
     */
    public function test_create_user_sets_names_from_telegram(): void {
        global $DB;
        $this->resetAfterTest();

        $tgdata = ['id' => '111', 'first_name' => 'Alice', 'last_name' => 'Wonderland'];
        $user   = telegram::create_user('alice@example.com', $tgdata);
        $record = $DB->get_record('user', ['id' => $user->id]);

        $this->assertSame('Alice', $record->firstname);
        $this->assertSame('Wonderland', $record->lastname);
    }

    /**
     * Missing optional Telegram fields (last_name) must not cause errors.
     *
     * @covers \auth_telegram\telegram::create_user
     */
    public function test_create_user_handles_missing_optional_fields(): void {
        global $DB;
        $this->resetAfterTest();

        $tgdata = ['id' => '222', 'first_name' => 'NoLastName'];
        $user   = telegram::create_user('nolastname@example.com', $tgdata);
        $record = $DB->get_record('user', ['id' => $user->id]);

        $this->assertSame('NoLastName', $record->firstname);
        $this->assertSame('', $record->lastname);
    }

    /**
     * The returned object must have ->id set (required by the caller for link_login).
     *
     * @covers \auth_telegram\telegram::create_user
     */
    public function test_create_user_returns_object_with_id(): void {
        $this->resetAfterTest();

        $user = telegram::create_user('returnid@example.com', self::TGDATA);

        $this->assertIsObject($user);
        $this->assertObjectHasProperty('id', $user);
        $this->assertGreaterThan(0, $user->id);
    }

    // ---------------------------------------------------------------------------
    // telegram::update_picture
    // ---------------------------------------------------------------------------

    /**
     * update_picture must return false immediately for an empty photo URL.
     *
     * @covers \auth_telegram\telegram::update_picture
     */
    public function test_update_picture_returns_false_for_empty_url(): void {
        $this->resetAfterTest();

        $user = $this->getDataGenerator()->create_user();
        $this->assertFalse(telegram::update_picture($user, ''));
    }

    /**
     * update_picture must return false when the user already has a picture set.
     *
     * @covers \auth_telegram\telegram::update_picture
     */
    public function test_update_picture_returns_false_when_picture_already_set(): void {
        $this->resetAfterTest();

        $user          = $this->getDataGenerator()->create_user();
        $user->picture = 1;

        $this->assertFalse(telegram::update_picture($user, 'https://t.me/photo.jpg'));
    }

    /**
     * update_picture must return false when Gravatar is enabled (no override needed).
     *
     * @covers \auth_telegram\telegram::update_picture
     */
    public function test_update_picture_returns_false_when_gravatar_enabled(): void {
        global $CFG;
        $this->resetAfterTest();

        $CFG->enablegravatar = 1;
        $user = $this->getDataGenerator()->create_user(['picture' => 0]);

        $this->assertFalse(telegram::update_picture($user, 'https://t.me/photo.jpg'));
    }
}
