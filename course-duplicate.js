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

    // check we're running this from Moodle
    var moodle = M.cfg.wwwroot;
    if (document.location.href.indexOf(moodle) !== 0) {
        alert('Only supporting '+moodle);
        return;
    }

    // check we're running this from within a course
    var bodyclasses = $('body').attr('class').split(' ');
    for(var i=0; i < bodyclasses.length; i++) {
        if (bodyclasses[i].indexOf('course-') == 0) {
            var courseid = bodyclasses[i].replace(/^course-/, '');
        }
        else if (bodyclasses[i].indexOf('category-') == 0) {
            var categoryid = bodyclasses[i].replace(/^category-/, '');
            break;
        }
    }
    if ((typeof courseid == 'undefined' || courseid == 1) && typeof categoryid == 'undefined') {
        // try the teacherchoice report as a source
        var $region = $('#page-blocks-teacherchoice-report #region-main'),
            courseid = $region.find('input[name=duplicate-courseid]').val(),
            categoryid = $region.find('input[name=duplicate-categoryid]').val(),
            shortname = $region.find('input[name=duplicate-shortname]').val(),
            fullname = $region.find('input[name=duplicate-fullname]').val();
    }
    else {
        var coursenavlink = $('#page-navbar div.breadcrumb-nav a[href="'+moodle+'/course/view.php?id='+courseid+'"]'),
            shortname = coursenavlink.text(),
            fullname = coursenavlink.attr('title');

    }
    if (typeof courseid == 'undefined' || typeof categoryid == 'undefined' || courseid == 1) {
        alert('Parameters missing – are you in a course?');
        return;
    }

    // check the overlay isn't already displayed
    if ($('#ndd-amintool-overlay').length) {
        return;
    }

    // create the overlay
    var overlay = $('<div id="ndd-amintool-overlay">');
    $('body').append(overlay);
    overlay.append($('<div>'));

    // load HTML into the overlay
    overlay.find('> div').load(moodle+'/blocks/teacherchoice/duplicate/course-duplicate.html?v=20160926', function(){
        // find course attributes
        $('#ndd-amintool-shortname').val(shortname + '-copy');
        $('#ndd-amintool-fullname').val(fullname + ' (copy)');
        $.getJSON(moodle+'/blocks/teacherchoice/duplicate/course-duplicate-courseinfo.php?courseid='+courseid, function(data){
            $.each(data, function(key, value){
                if (key == 'enrolpassword') {
                    var newvalue_int = parseInt(value.replace(/^(.*[^0-9]+)([0-9]+)$/, '$2'));
                    var newvalue = value.replace(/^(.*[^0-9]+)([0-9]+)$/, '$1') + ++newvalue_int;
                    if (isNaN(newvalue_int)) {
                        // we weren't able to compute newvalue after all
                        newvalue = value;
                    }
                    $('#ndd-amintool-enrolpassword').val(newvalue)
                        .parent().show();
                }
            });
        });
        $('#ndd-amintool-users').change(function(){
            if ($(this).is(':checked')) {
                $('#ndd-amintool-keepteachers').attr({'checked': 'checked', 'disabled': 'disabled'});
            }
            else {
                $('#ndd-amintool-keepteachers').removeAttr('checked').removeAttr('disabled');
            }
        });
        // destroy overlay on "Cancel"
        $('#ndd-amintool-cancel').click(function(){
            overlay.remove();
        });
        // proceed on "OK"
        $('#ndd-amintool-ok').click(function(){
            $('#ndd-amintool-overlay button').add('#ndd-amintool-overlay input').attr('disabled', 'disabled');
            var users               = $('#ndd-amintool-users:checked').length,
                gotonewcourse       = $('#ndd-amintool-goto:checked').length,
                archiveoldcourse    = $('#ndd-amintool-arch:checked').length,
                keepteachers        = $('#ndd-amintool-keepteachers:checked').length,
                fullname            = encodeURIComponent($('#ndd-amintool-fullname').val()),
                shortname           = encodeURIComponent($('#ndd-amintool-shortname').val()),
                opennewcourse       = $('#ndd-amintool-open:checked').length,
                enrolpassword       = encodeURIComponent($('#ndd-amintool-enrolpassword').val()),
                returnurl           = '';

            if (document.location.href.indexOf('/blocks/teacherchoice/report.php') !== -1) {
                // redirect to /blocks/teacherchoice/report.php
                returnurl = encodeURIComponent(moodle+'/blocks/teacherchoice/report.php?courseid=' + courseid);
            }

            document.location.href = moodle+'/blocks/teacherchoice/duplicate/course-duplicate.php?courseid='+courseid+'&users='+users+'&keepteachers='+keepteachers+'&categoryid='+categoryid+'&shortname='+shortname+'&fullname='+fullname+'&gotonewcourse='+gotonewcourse+'&archiveoldcourse='+archiveoldcourse+'&opennewcourse='+opennewcourse+'&enrolpassword='+enrolpassword+'&returnurl='+returnurl;
        });
    });

})(jQuery)