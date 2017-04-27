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
 * Version information
 *
 * @package    block
 * @subpackage teacherchoice
 * @copyright  Univertisé de Lausanne, RISET ( http://www.unil.ch/riset )
 * @author     Nicolas Dunand <Nicolas.Dunand@unil.ch>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

?><!doctype html>
<html>
<head>
    <meta charset="utf-8" />
    <link rel="stylesheet" type="text/css" href="<?php echo $CFG->wwwroot; ?>/theme/unil/dashboard/styles.css" />
</head>
<body>
    <div id="ndd-amintool-overlay">
        <div>
<?php

    if (isset($newcourse)) {

        echo html_writer::tag('h2', get_string('duplicate_warning', 'block_teacherchoice'));

    }
    else {

        echo html_writer::tag('h2', get_string('duplicate_error', 'block_teacherchoice'));

    }

?>
            <div>
                <ul>
<?php

    foreach ($errors as $error) {
        echo '<li>'.$error.'</li>';
    }

?>
                </ul>
            </div>
            <div>
                <p><?php print_string('youcanbrowseto', 'block_teacherchoice') ?></p>
                <ul>
                    <li><a href="<?php echo $CFG->wwwroot.'/course/view.php?id='.$importcourse->id; ?>"><?php print_string('originalcourse', 'block_teacherchoice') ?></a></li>
<?php

    if (isset($newcourse)) {

?>
                    <li><a href="<?php echo $CFG->wwwroot.'/course/view.php?id='.$newcourse['id']; ?>"><?php print_string('newcourse', 'block_teacherchoice') ?></a></li>
<?php

    }
    else {

?>
                    <li><a href="javascript:history.back();"><?php print_string('browseback', 'block_teacherchoice') ?></a></li>
<?php

    }

?>
                </ul>
            </div>
        </div>
    </div>
</body>
</html>