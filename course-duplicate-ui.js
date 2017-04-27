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


(function($){

    var restore_a = $('.block_settings.block')
        .find('a[href*="restorefile.php"]');

    if (restore_a.length < 1) {
        return;
    }

    var restore_li = restore_a.eq(0)
        .parent().parent();

    var duplicate_li = $('<li>');

    duplicate_li
        .attr('class', restore_li.attr('class'))
        .html(restore_li.html());

    var text = restore_li.text(),
        thtml = restore_li.find('a').html();

    var newtext = (text == 'Restauration') ?
        'Duplication' : 'Duplicate';

    duplicate_li
        .find('a')
        .html(thtml.replace(text, newtext).replace('i\/restore', 'i\/course'))
        .attr('href', '#')
        .click(function(){
            var jsCode = $('<script>');
            jsCode.attr('src', M.cfg.wwwroot + '/blocks/teacherchoice/duplicate/course-duplicate.js');
            $('body').append(jsCode);
            return false;
        });

    restore_li
        .after(duplicate_li);

})(jQuery)
