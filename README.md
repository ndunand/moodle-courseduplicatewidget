# Moodle course duplicate widget

**To install:**

1. Deploy files somewhere in your Moodle installation (e.g. /local/duplicate)
2. Add the following to your `$CFG->additionalhtmlfooter` setting: `<script type="text/javascript" src="//yourmoodle.example.edu/local/duplicate/course-duplicate-ui.js"></script>`
3. Now you have an extra "Duplicate" entry in your course admin block

**Display logic**

The item is displayed if you have course restore permission in the current context. 
