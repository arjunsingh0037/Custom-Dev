<?php
/*http://localhost/moodle310/webservice/rest/server.php?wstoken=5c0db9d3d86dac24650930f94402e58d&wsfunction=local_custom_service_update_courses_sections&moodlewsrestformat=json&courseids=6*/
require_once('../../config.php');
global $CFG,$DB;
require_once($CFG->libdir . '/filelib.php');
require_once($CFG->dirroot . '/course/lib.php');
//$course = 7;
/*print_object('check');
$course_id = 3;
$token = $DB->get_record_select("external_tokens", "externalserviceid in (select externalserviceid from {external_services_functions} where functionname = 'local_custom_service_update_courses_lti') and userid in (select value from {config} where name = 'siteadmins')",null,'token',MUST_EXIST)->token; 
print_object('here');
print_object($token);
$url = $CFG->wwwroot . "/webservice/rest/server.php?wstoken=$token&wsfunction=local_custom_service_update_courses_lti&moodlewsrestformat=json";
$params = "courseids={$course_id}";
$resp = (new curl)->post($url, $params);
$clone = json_decode($resp);

print_object($clone);*/

$course_id = 7;
$course = $DB->get_record('course', array('id' => $course_id), '*', MUST_EXIST);

$sections = $DB->get_records('course_sections', array('course' => $course_id));
//print_object($sections);die();

foreach ($sections as $key => $value) {
	$section = $DB->get_record('course_sections', array('id' => $key), '*', MUST_EXIST);

	$data = new stdClass();
	$data->id = $section->id;
	$data->name = $section->summary;
	$data->availability = '{"op":"&","c":[],"showc":[]}';

	//check if section is empty-then update
	if($section->name == NULL){
		$done = course_update_section($course, $section, $data);
	}
}