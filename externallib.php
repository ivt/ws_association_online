<?php

require_once($CFG->libdir . "/externallib.php");
require_once($CFG->dirroot . "/user/externallib.php");
require_once($CFG->dirroot . "/config.php");
require_once($CFG->libdir . "/completionlib.php");

class local_ws_association_online_external extends external_api
{
    /** **************************** Completion ***************************** **/

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function get_for_course_parameters()
    {
        return new external_function_parameters(
            array('courseid' => new external_value(PARAM_INT, 'id of course'))
        );
    }

    /**
     * Returns user completion data for a particular course
     * @return array
     */
    public static function get_for_course($courseid)
    {
        global $DB;
        $course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
        $completion = new completion_info($course);
        $users = $completion->get_tracked_users();

        $results = array();

        foreach ($users as $user) {
            array_push($results, array(
                    'courseid' => $courseid,
                    'userid' => $user->id,
                    'complete' => $completion->is_course_complete($user->id),
                )
            );
        }

        return (array)$results;
    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function get_for_course_returns()
    {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'courseid' => new external_value(PARAM_INT, 'id of course'),
                    'userid' => new external_value(PARAM_INT, 'id of user'),
                    'complete' => new external_value(PARAM_BOOL, 'whether user has completed course'),
                )
            )
        );
    }

    /** ******************************* Roles ******************************** **/

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function get_roles_parameters()
    {
        return new external_function_parameters(
            array()
        );
    }

    /**
     * Returns all of moodles roles
     * @return array
     */
    public static function get_roles()
    {
        $allRoles = role_get_names();

        $roles = array();
        foreach ($allRoles as $roleid => $role) {
            $roles[] = array(
                'roleid' => $roleid,
                'rolename' => $role->localname
            );
        }

        return (array)$roles;
    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function get_roles_returns()
    {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'roleid' => new external_value(PARAM_INT, 'id of role'),
                    'rolename' => new external_value(PARAM_TEXT, 'name of role'),
                )
            )
        );
    }

    /** ***************************** Custom Field ****************************** **/

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function get_user_by_custom_field_value_parameters()
    {
        return new external_function_parameters(
            array(
                'fieldname' => new external_value(PARAM_TEXT, 'name of customfield to search'),
                'fieldvalue' => new external_value(PARAM_TEXT, 'value of customfield to look for')
            )
        );
    }

    /**
     * Returns all of moodles roles
     * @return array
     */
    public static function get_user_by_custom_field_value($fieldname, $fieldvalue)
    {
        global $DB;

        $prefix = $DB->get_prefix();

        $dataTable = $prefix . 'user_info_data';
        $fieldTable = $prefix . 'user_info_field';

        $sql = "SELECT
                  {$dataTable}.userid
                FROM
                  {$fieldTable}
                JOIN
                  {$dataTable} ON {$dataTable}.fieldid = {$fieldTable}.id
                WHERE
                  {$fieldTable}.name = ?
                AND
                  {$dataTable}.data = ?";

        $params = array($fieldname, $fieldvalue);
        $result = $DB->get_recordset_sql($sql, $params);

        $userid = -1;

        $user = $result->current();

        if ($user->userid) {
            $userid = $user->userid;
        }

        return array(array('userid' => $userid));
    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function get_user_by_custom_field_value_returns()
    {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'userid' => new external_value(PARAM_INT, 'userid'),
                )
            )
        );
    }

    /** ************************** CREATE USER ************************** **/

    public static function create_users_from_ao_parameters() {
        return core_user_external::create_users_parameters();
    }

    /**
     * Delegates to core_user_external::create_users(), but randomises emails before doing so.
     * Then, after invoking create_users(), it will restore the actual emails from $usersToCreate.
     *
     * While not ideal, the alternative would be to copy and paste create_users() and then change
     * the one line which was causing trouble.
     *
     * @param array $usersToCreate
     * @return array
     * @throws invalid_parameter_exception
     * @throws moodle_exception
     */
    public static function create_users_from_ao($usersToCreate) {

        // Make emails random so they shouldn't conflict with others...
        $safeEmailUsersToCreate = array();
        foreach( $usersToCreate as $user ) {
            $user['email']            = random_string(20) . "-" . $user['email'];
            $safeEmailUsersToCreate[] = $user;
        }

        $ids = core_user_external::create_users($safeEmailUsersToCreate);

        // Overwrite the random emails created above with the original emails...
        foreach( $usersToCreate as $user ) {
            $id = null;
            foreach( $ids as $savedData ) {
                if ( $savedData[ 'username' ] == $user[ 'username' ] ) {
                    $id = $savedData[ 'id' ];
                    break;
                }
            }

            if ( $id == null )
            {
                throw new invalid_state_exception( "User did not save correctly." );
            }

            $userObj = new stdClass();
            $userObj->id = $id;
            $userObj->email = $user[ 'email' ];

            user_update_user( $userObj, false, false );
        }

        return $ids;

    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     * @since Moodle 2.2
     */
    public static function create_users_from_ao_returns() {
        return core_user_external::create_users_returns();
    }

}