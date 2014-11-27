Web service Completion
======================
Moodle webservice that returns course completion information.

Installation
------------
1. Copy files to <moodle_dir>/local/ws_association_online
2. Go to <moodle_url>/admin/localplugins.php
3. Install the plugin
4. Add the functions to the relevant webservice user via <moodle_url>/admin/settings.php?section=externalservices
5. Go to Site Administration / Users / Permissions / Define roles
 * Add new role
 * Archtype: No role
 * Use role preset: Upload aowebservice.xml
 * Continue
