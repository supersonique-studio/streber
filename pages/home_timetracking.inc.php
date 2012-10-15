<?php if(!function_exists('startedIndexPhp')) { header("location:../index.php"); exit();}
# streber - a php5 based project management system  (c) 2005-2007  / www.streber-pm.org
# Distributed under the terms and conditions of the GPL as stated in lang/license.html

/**\file
 * Timetracking 
 *
 * @author Thomas Mann
 */


require_once(confGet('DIR_STREBER') . 'db/class_task.inc.php');
require_once(confGet('DIR_STREBER') . 'db/class_project.inc.php');
require_once(confGet('DIR_STREBER') . 'render/render_list.inc.php');
require_once(confGet('DIR_STREBER') . 'render/render_form.inc.php');
require_once(confGet('DIR_STREBER') . 'lists/list_tasks.inc.php');
require_once(confGet('DIR_STREBER') . 'lists/list_project_team.inc.php');


/**
* display efforts for current user  @ingroup pages
*
* @NOTE 
* - actually this is an excact copy for personViewEfforts
* - this is of course not a good solution, but otherwise the breadcrumbs would change
*
*/
function homeTimetracking()
{
    global $PH;
    global $auth;
    require_once(confGet('DIR_STREBER') . 'db/class_effort.inc.php');
    require_once(confGet('DIR_STREBER') . 'lists/list_efforts.inc.php');

    ### get current project ###
    $person= $auth->cur_user;

    ### create from handle ###
    $PH->defineFromHandle(array('person'=>$person->id));

    ### set up page ####
    {
        $page= new Page();
        $page->cur_tab='home';
        $page->title=__("Time tracking");

        $page->extra_header_html  = '<script type="text/javascript" src="js/ninja.js"></script>';
        $page->extra_header_html .= '<script type="text/javascript" src="js/ninja-autocomplete.js"></script>';
        $page->extra_header_html .= '<script type="text/javascript" src="js/timetracking.js'  . "?v=" . confGet('STREBER_VERSION'). '"></script>';
        $page->extra_header_html .= '<link rel="stylesheet" href="themes/clean/ninja-autocomplete.css" />';

        $page->extra_onload_js .= "new TimeTrackingTable();";        
        $page->extra_onload_js .= "new TimeTrackingForm();";
                
        $page->type=__("Person");
        $page->options= build_home_options($person);

        echo(new PageHeader);
        build_history_table();
        build_effort_edit_form();

        
    }
    echo (new PageContentOpen);
    
    echo '<input type="hidden" name="person" value="'.$person->id.'">';

    echo (new PageContentClose);
    echo (new PageHtmlEnd());
}

function build_history_table() {
    echo "<div class='doclear'></div>";
    echo "<div class=timetable>";
        echo '<canvas id="myCanvas" width="100%" height="100"></canvas>';
        echo "<div class=container>Container</div>";        
        echo "<div class=currentTime></div>";
    echo "</div>";
}


function build_effort_edit_form() 
{
    $effort = new Effort();
    global $g_effort_status_names;
    global $PH;
    global $auth;
    
    echo "<h3>" . __("New Effort") . "</h3>"
            . "<div class='timetracking'>"
            . "<p>"
            . "<p>"
            .  "<input placeholder='Start' class='from' id='effort_start' >"
            .  "<input placeholder='Time' class='for' id='effort_duration' >"
            .  "<input placeholder='Now' class='to' id='effort_end' >"
            . "</p><p>"
            .  "<input placeholder='Project' class='project' id='effort_project' >"
            .  "<input placeholder='Task' class='task' id='effort_task'>"
            .  "</p>"
            .  "<p><textarea name='comment' id='description' placeholder='Comment'></textarea></p>"
            . "</div>";
    
    require_once(confGet('DIR_STREBER') . 'render/render_form.inc.php');    

    $PH->go_submit='effortNewFromTimetracking';
    echo "<input type=hidden id='effort_project_id' name='effort_project_id' value=''>";
    echo "<input type=hidden id='effort_task_id'  name='effort_task_id' value=''>";
    echo "<input type=hidden id='effort_start_seconds' name='effort_start_seconds' value=''>";
    echo "<input type=hidden id='effort_end_seconds'  name='effort_end_seconds' value=''>";
    echo "<input type=submit>";
}


function ajaxUserEfforts()
{
    global $PH;
    global $auth;
    require_once(confGet('DIR_STREBER') . 'db/class_effort.inc.php');
    
    $result = array();
    foreach( $auth->cur_user->getEfforts() as $e ) {
        $p= Project::getById($e->project);
        
        $result[$e->id] = array('start'=>strToClientTime($e->time_start), 
                                'duration'=> (strToClientTime($e->time_end) - strToClientTime($e->time_start)) , 
                                'id'=> $e->id,
                                'title'=> $p->name);
    }
    echo json_encode($result);
}


function ajaxUserProjects()
{
    global $PH;
    global $auth;
    if($q= getOnePassedId("q")) {
         $projects = Project::getAll(array('search'=>$q))   ;
    }
    else {
         $projects = Project::getAll();
    }
    
    $result = array();
    foreach($projects  as $t) {        
        $result[] = array('name'=> $t->name, 'id'=>$t->id);    
    }
    echo json_encode($result);
}



function ajaxUserTasks()
{
    $q= asCleanString(getOnePassedId("q"));
    $prj= intval(getOnePassedId("prj"));
    
    if ($prj == 0) $prj = NULL;
    if ($q == "") $q = NULL;
    
    $tasks = Task::getAll(array('search'=>$q, 'project'=>$prj));

    $result = array();
    foreach( $tasks as $t) {        
        $result[] = array('name'=> $t->name, 'id'=>$t->id);    
    }
    echo json_encode($result);
}

?>