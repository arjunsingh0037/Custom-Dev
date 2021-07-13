<?php

defined('MOODLE_INTERNAL') || die();
$functions = array(
    'local_custom_service_update_courses_lti' => array(
        'classname' => 'local_custom_service_external',
        'methodname' => 'update_courses_lti',
        'classpath' => 'local/custom_service/externallib.php',
        'description' => 'Update courses LTI to show in Gradebook',
        'type' => 'write',
        'ajax' => true,
    ),
    'local_custom_service_update_courses_sections' => array(
        'classname' => 'local_custom_service_external',
        'methodname' => 'update_courses_sections',
        'classpath' => 'local/custom_service/externallib.php',
        'description' => 'Update courses sections title in DB',
        'type' => 'write',
        'ajax' => true,
    )
);

$services = array(
    'M-Star Custom Services' => array(
        'functions' => array(
            'local_custom_service_update_courses_lti',
            'local_custom_service_update_courses_sections'
        ),
        'restrictedusers' => 0,
        'enabled' => 1,
    )
);