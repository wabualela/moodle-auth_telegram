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

$string['botusername']             = 'اسم مستخدم البوت';
$string['botusername_help']        = 'اسم المستخدم لبوت التليجرام (بدون @).';
$string['confirmationinvalid']     = 'رابط التأكيد غير صالح أو منتهي الصلاحية. يرجى المحاولة مجدداً.';
$string['confirmlinkedheader']     = 'تم ربط الحسابين';
$string['confirmlinkedmessage']    = 'تم ربط حساب تيليجرام بحسابك في مودل بنجاح. يمكنك الآن تسجيل الدخول باستخدام تيليجرام.';
$string['confirmlinkemail']        = 'مرحباً {$a->firstname}،

تم تقديم طلب لربط حساب تيليجرام بحسابك في \'{$a->sitename}\'.

لتأكيد هذا الطلب وربط الحسابين، يرجى النقر على الرابط أدناه:

{$a->link}

إذا لم تكن أنت من قدّم هذا الطلب، يرجى التواصل مع مدير الموقع فوراً.

{$a->admin}';
$string['confirmlinkemail_subject'] = '{$a}: تأكيد ربط حساب تيليجرام';
$string['confirmlinksent']         = 'تحقق من بريدك الإلكتروني';
$string['continuewith']            = 'انقر على الزر أدناه لتسجيل الدخول بحساب تيليجرام.';
$string['emailexistsheader']       = 'الحساب موجود مسبقاً';
$string['emailexistsmessage']      = 'تم العثور على حساب موجود بعنوان البريد الإلكتروني {$a}. تم إرسال بريد إلكتروني للتأكيد إلى ذلك العنوان يحتوي على تعليمات ربط حساب تيليجرام.';
$string['fieldkeycore']            = 'أساسي: {$a}';
$string['fieldkeycustom']          = 'مخصص: {$a->name} ({$a->shortname})';
$string['missingfieldsheader']     = 'مطلوب معلومات إضافية للملف الشخصي';
$string['missingfieldsmessage']    = 'يرجى تعبئة حقول الملف الشخصي المطلوبة التالية للمتابعة:';
$string['missingphone']            = 'يرجى إدخال رقم هاتفك.';
$string['missingtelegramid']       = 'معرف التلغرام مفقود — طلب الدخول غير صالح.';
$string['notenabled']              = 'عذراً، لم يتم تفعيل إضافة مصادقة تيليجرام.';
$string['pluginname']              = 'تيليجرام';
$string['requiredfields']          = 'حقول الملف الشخصي المطلوبة';
$string['requiredfields_help']     = 'اختر حقول الملف الشخصي التي يجب على المستخدمين تعبئتها بعد أول تسجيل دخول عبر تيليجرام.';
$string['signup']                  = 'أكمل تسجيلك';
$string['signupdesc']              = 'تم التحقق من حساب تيليجرام الخاص بك. يرجى تقديم عنوان بريدك الإلكتروني ورقم هاتفك لإتمام إنشاء حساب مودل.';
$string['telegrambottoken']        = 'رمز البوت';
$string['telegrambottoken_help']   = 'الرمز السري الذي يوفره BotFather. يُستخدم للتحقق من توقيعات أداة تسجيل الدخول عبر تيليجرام.';
