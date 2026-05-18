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

/**
 * Strings for component 'auth_telegram', language 'ar'.
 *
 * @package    auth_telegram
 * @copyright  2026 Wail Abualela <wailabualela@email.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['addlinkedlogin']           = 'ربط حساب تيليجرام';
$string['alreadylinked']            = 'حساب تيليجرام هذا مرتبط بالفعل بملفك الشخصي.';
$string['alreadylinkedother']       = 'حساب تيليجرام هذا مرتبط بالفعل بحساب مودل آخر.';
$string['botusername']              = 'اسم مستخدم البوت';
$string['botusername_help']         = 'اسم المستخدم لبوت التليجرام (بدون @).';
$string['confirmationinvalid']      = 'رابط التأكيد غير صالح أو انتهت صلاحيته. يرجى المحاولة مرة أخرى.';
$string['confirmlinkedheader']      = 'تم ربط حساب تيليجرام';
$string['confirmlinkedmessage']     = 'تم ربط حساب تيليجرام الخاص بك بنجاح. يمكنك الآن تسجيل الدخول باستخدام تيليجرام.';
$string['confirmlinkemail']         = 'مرحباً {$a->firstname}،

تم تقديم طلب لربط حساب مودل الخاص بك على {$a->sitename} بحساب تيليجرام.

لتأكيد الربط، انقر على الرابط التالي:

{$a->link}

هذا الرابط صالح لمدة 30 دقيقة.

{$a->admin}';
$string['confirmlinkemail_subject'] = 'تأكيد ربط حساب تيليجرام على {$a}';
$string['confirmlinksent']          = 'تم إرسال رسالة التأكيد';
$string['continuewith']             = 'انقر على الزر أدناه لتسجيل الدخول بحساب تيليجرام.';
$string['emailexistsmessage']       = 'يوجد حساب بعنوان البريد الإلكتروني {$a} في مودل بالفعل. تم إرسال رسالة تأكيد إلى ذلك العنوان. يرجى النقر على الرابط الوارد في البريد الإلكتروني لربط حساب تيليجرام الخاص بك.';
$string['linkedlogins']             = 'حسابات تيليجرام المرتبطة';
$string['linkedloginshelp']         = 'إدارة حسابات تيليجرام المرتبطة بحسابك في مودل.';
$string['linkedsince']              = 'مرتبط منذ';
$string['missingtelegramid']        = 'معرف التلغرام مفقود — طلب الدخول غير صالح.';
$string['nolinkedlogins']           = 'لا توجد حسابات تيليجرام مرتبطة بملفك الشخصي.';
$string['notwhileloggedinas']       = 'لا يمكن إدارة الحسابات المرتبطة أثناء تسجيل الدخول كمستخدم آخر.';
$string['notenabled']               = 'عذراً، لم يتم تفعيل إضافة مصادقة تيليجرام.';
$string['pluginname']               = 'تيليجرام';
$string['signup']                   = 'أكمل تسجيلك';
$string['signupdesc']               = 'تم التحقق من حساب تيليجرام الخاص بك. يرجى تقديم عنوان بريدك الإلكتروني لإتمام إنشاء حساب مودل.';
$string['telegramid']               = 'معرّف تيليجرام';
$string['telegrambottoken']         = 'رمز البوت';
$string['telegrambottoken_help']    = 'الرمز السري الذي يوفره BotFather. يُستخدم للتحقق من توقيعات أداة تسجيل الدخول عبر تيليجرام.';
