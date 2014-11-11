<?php
// We defined the web service functions to install.
$functions = array(
    'local_ws_association_online_get_for_course' => array(
        'classname' => 'local_ws_association_online_external',
        'methodname' => 'get_for_course',
        'classpath' => 'local/ws_association_online/externallib.php',
        'description' => 'Returns the completion status for all users enrolled in a course',
        'type' => 'read'
    ),
    'local_ws_association_online_get_roles' => array(
        'classname' => 'local_ws_association_online_external',
        'methodname' => 'get_roles',
        'classpath' => 'local/ws_association_online/externallib.php',
        'description' => 'Returns a list of moodle roles',
        'type' => 'read'
    ),
    'local_ws_association_online_get_user_by_custom_field_value' => array(
        'classname' => 'local_ws_association_online_external',
        'methodname' => 'get_user_by_custom_field_value',
        'classpath' => 'local/ws_association_online/externallib.php',
        'description' => 'Returns users with a specific custom field value',
        'type' => 'read'
    )
);

// We define the services to install as pre-build services. A pre-build service is not editable by administrator.
$services = array(
    'Web service completion' => array(
        'functions' => array('local_ws_association_online_get_for_course', 'local_ws_association_online_get_roles', 'local_ws_assocation_online_get_user_by_custom_field_value'),
        'restrictedusers' => 0,
        'enabled' => 1,
    )
);
