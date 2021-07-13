<?php

use core_completion\progress;
require_once(__DIR__.'/../../config.php');
require_once($CFG->libdir.'/externallib.php');
require_once($CFG->dirroot.'/user/lib.php');
require_once($CFG->dirroot.'/course/lib.php');

class local_custom_service_external extends external_api {

    public static function update_courses_lti_parameters() {
        return new external_function_parameters(
            array(
                'courseids' => new external_value(PARAM_TEXT, 'Course Ids')                
            )
        );
    }
    public static function update_courses_lti($courseids) {
        global $DB,$CFG;
        $lti_updated = [];
        $status = false;
        //print_object($courseids);
        $sql = "SELECT cm.id as moduleid,cm.instance ltiid,cm.section as section,lt.name as ltiname,lt.grade as grade,lt.timecreated,lt.timemodified,c.id as courseid,gd.id as category
            FROM {course} c 
            JOIN {course_modules} cm ON c.id = cm.course 
            JOIN {lti} lt ON cm.instance = lt.id 
            JOIN {grade_categories} gd ON gd.courseid = c.id
            WHERE cm.module =15 AND c.id in (".$courseids.")";
        $modules = $DB->get_records_sql($sql);
        $all_module = array();
        $count = 0;
        foreach ($modules as $key => $value) {
            if($DB->record_exists('grade_items',array('courseid'=>$value->courseid,'categoryid'=>$value->category,'itemtype'=>'mod','itemmodule'=>'lti','iteminstance'=>$value->ltiid))){
                //$all_module[] = $value;
            }else{
                $new_grade_item = new stdClass();
                $new_grade_item->courseid = $value->courseid;
                $new_grade_item->categoryid = $value->category;
                $new_grade_item->itemname = $value->ltiname;
                $new_grade_item->itemtype = 'mod';
                $new_grade_item->itemmodule = 'lti';
                $new_grade_item->iteminstance = $value->ltiid;
                $new_grade_item->itemnumber = 0;
                $new_grade_item->grademax = $value->grade;
                $new_grade_item->timecreated = $value->timecreated;
                $new_grade_item->timemodified = $value->timemodified;

                $insert_new_gradeitem = $DB->insert_record('grade_items',$new_grade_item);
                $count++;
            }
        }
        
        $lti_updated = [
                        'ids'=>$courseids,
                        'message'=>'Success',
                        'updated'=>$count
                        ];
        return $lti_updated;
    }
    public static function update_courses_lti_returns() {
        return new external_single_structure(
                array(
                    'ids' => new external_value(PARAM_TEXT, 'course ids'),
                    'message'=> new external_value(PARAM_TEXT, 'success message'),
                    'updated'=>new external_value(PARAM_TEXT,'Items Updated')
                )
            );
    }

    public static function update_courses_sections_parameters() {
        return new external_function_parameters(
            array(
                'courseids' => new external_value(PARAM_TEXT, 'Course Ids')
            )
        );
    }
    public static function update_courses_sections($courseids) {
        global $DB,$CFG;
        require_once($CFG->libdir . '/filelib.php');
        require_once($CFG->dirroot . '/course/lib.php');
        
        $course = $DB->get_record('course', array('id' => $courseids), '*', MUST_EXIST);
        $sections = $DB->get_records('course_sections', array('course' => $courseids));
        
        $count = 0;

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
            $count ++;
        }
        
        $lti_updated = [
                        'ids'=>$courseids,
                        'message'=>'Success',
                        'updated'=>$count
                        ];
        return $lti_updated;
    }
    public static function update_courses_sections_returns() {
        return new external_single_structure(
                array(
                    'ids' => new external_value(PARAM_TEXT, 'course ids'),
                    'message'=> new external_value(PARAM_TEXT, 'success message'),
                    'updated'=>new external_value(PARAM_TEXT,'Items Updated')
                )
            );
    }





    public static function unenrol_bulk_users_parameters() {
        return new external_function_parameters(
            array(
                'categoryids' => new external_value(PARAM_TEXT, 'Category Ids'),
                'roleid' => new external_value(PARAM_TEXT, 'Role Ids')
            )
        );
    }
    public static function unenrol_bulk_users($categoryids, $roleid) {
        // echo $categoryids;
        global $DB,$CFG;
        require_once($CFG->libdir . '/filelib.php');
        require_once($CFG->dirroot . '/course/lib.php'); 
        require_once($CFG->dirroot . '/enrol/locallib.php'); 
        require_once($CFG->dirroot . '/enrol/externallib.php'); 
        
        $sql = "DELETE ue FROM mdl_user_enrolments ue
        JOIN mdl_enrol e ON (e.id = ue.enrolid)
        JOIN mdl_course course ON (course.id = e.courseid )
        JOIN mdl_context c ON (c.contextlevel = 50 AND c.instanceid = e.courseid)
        JOIN mdl_role_assignments ra ON (ra.contextid = c.id  AND ra.userid = ue.userid AND ra.roleid=$roleid)
        WHERE course.category IN (?)
        ";
            //echo $categoryids;
            $param=explode(',',$categoryids);
            //print_r($param);
            $result = $DB->execute($sql,$param);
            if($result) {
                $response = [
                    'message'=>'Success'                        
                    ];

            }else{
                $response = [
                    'message'=>'Failed'                        
                    ];

            }        
            
        return $response;
    }
    public static function unenrol_bulk_users_returns() {
        return new external_single_structure(
                array(                   
                    'message'=> new external_value(PARAM_TEXT, 'success message')                   
                )
            );
    }

}