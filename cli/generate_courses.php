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
 * CLI script to create course.
 *
 * @package    local_moodlefaker
 * @subpackage cli
 * @copyright  2022 Huong Nguyen <huongnv13@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);

require(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/clilib.php');
require_once('../vendor/autoload.php');
require_once($CFG->dirroot . '/lib/phpunit/classes/util.php');

$longoptions = [
    'help' => false,
    'total' => 0
];
list($options, $unrecognized) = cli_get_params($longoptions, ['h' => 'help']);

if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized), 2);
}

if ($options['help']) {
    // The indentation of this string is "wrong" but this is to avoid a extra whitespace in console output.
    $help = <<<EOF
Generates Moodle course

Options:
-h, --help            Print out this help
    --total           Total of courses to generate

Example:
\$ sudo -u www-data /usr/bin/php local/moodlefaker/cli/generate_courses.php

EOF;

    echo $help;
    exit(0);
}

if ($options['total']) {
    // Switch to admin user account.
    \core\session\manager::set_user(get_admin());

    // Create Faker factory.
    $faker = Faker\Factory::create();

    // Get generator.
    $generator = phpunit_util::get_data_generator();
    $coursecat = $generator->create_category(['name' => 'Faker']);

    for ($i = 1; $i <= $options['total']; $i++) {
        $coursename = 'Faker-' . $faker->text(10) . '-' . $i;
        $courseshortname = 'Faker-' . $faker->text(5) . '-' . $i;

        raise_memory_limit(MEMORY_EXTRA);

        // Get generator.
        $generator = phpunit_util::get_data_generator();

        //Create course.
        $courserecord = [
            'category' => $coursecat->id,
            'shortname' => $courseshortname,
            'fullname' => $coursename,
            'summary' => 'Faker test course',
            'startdate' => usergetmidnight(time())
        ];

        $course = $generator->create_course($courserecord);

        // Enrol admin user.
        $generator->enrol_user(get_admin()->id, $course->id);

        echo PHP_EOL . 'Generated course: ' . $coursename . ' at: ' . course_get_url($course) . PHP_EOL;
    }
}

exit(0);

