<?php

class Docent extends CI_Controller {

//    private /*Object*/ $group;
    
    function __construct() {
        
        parent::__construct();
        
        $this->load->model("User_model");
        $this->load->model("Group_model");
        $this->load->model("Assignment_model");
        $this->load->model("Homework_model");
        
        $this->authenticate();
        
    }
    
    public function index($page = "docent_group_overview") {
        
        $this->controls();
        $this->page($page);
        
    }
    
    private function authenticate() {
         
        
        if(!$this->session->userdata("email")) {
            
            header("Location: " . base_url() . "index.php/login");
            exit();
            
        }
        
        if( ! $this->User_model->isDocent()) {
           
            $this->session->set_userdata("warning", "You are no docent.");
            header("Location: " . base_url() . "index.php/student");
            exit();
            
            
        }
            
    }
    
    private function controls() {
        
        $this->authenticate();
                
        if(isset($_POST["submit_groep"])) {
            
            $name = $_POST["param_groep_name"];
            $description = $_POST["param_groep_description"];
            
            $this->Group_model->create($name, $description);
            
            $this->session->set_userdata("warning", "Group successfully created.");
            
        }
        
        if(isset($_POST["submit_changepassword"])) {
            
            $email = $this->session->userdata("email");
            $oldpassword = $_POST["param_oldpassword"];
            $newpassword = $_POST["param_newpassword"];
            
            if($this->User_model->authenticateCurrentUser($oldpassword)) {
            
                $this->User_model->changePasswordByEmail($email, $newpassword);
            
            }
            
        }
        
        if(isset($_POST["submit_editgroup"])) {
            
            die("EDIT GROUP");
            
            $name           = $_POST["param_group_name"];
            $description    = $_POST["param_group_description"];
            
//            die(var_dump($param_id));
            
            $this->Group_model->updateNameAndDescriptionById($param_id, $name, $description);
            $this->session->set_userdata("warning", "Group successfully edited.");
            
        }
        
    }
    
    public function logout() {
        
        $this->session->unset_userdata("email");
        $this->session->set_userdata("warning", "You've successfully logged out!");
        header("Location: " . base_url() . "index.php");
        exit();
        
    }
    
    public function deleteStudentFromGroup($group_id, $param_id) {
        
        $this->Group_model->leave($group_id, $param_id);
        $this->session->set_userdata("warning", "Student successfully deleted from group.");
        header("Location: " . base_url() . "index.php/docent/groupprofile/" . $group_id);
        exit(0);
        
    }
    
    public function groupSettings($param_id) {
        
        if( ! file_exists(APPPATH . "views/pages/docent_group_settings.php")) {
            
            show_404();
            
        } else {
            
            $data["group"] = $this->Group_model->getGroupById($param_id);
            
            $this->load->view("templates/header.php");
            $this->load->view("pages/docent_group_settings.php", $data);
            $this->load->view("templates/footer.php");
            
        }
        
    }
    
    public function deleteGroup($param_id) {
        
        if(isset($param_id)) {

            $this->Group_model->deleteById($param_id);
            $this->session->set_userdata("warning", "Group successfully deleted!");
            header("Location: " . base_url() . "index.php/docent");
            exit(0);
            
        }
        
    }
    
    public function deleteHomework($param_id) {
        
        $homework = $this->Homework_model->getById($param_id);
        $this->Homework_model->deleteById($param_id);
        $this->session->set_userdata("warning", "Homework deleted.");
        header("Location: " . base_url() . "index.php/docent/assignmentprofile/" . $homework["assignment_id"]);
        exit(0);
                
    }
    
    public function approveHomework($param_id) {
        
        $param_status = "approved";
        
        $homework = $this->Homework_model->getById($param_id);
        $this->Homework_model->updateStatusById($param_id, $param_status);
        $this->session->set_userdata("warning", "Homework status updated to approved!");
        header("Location: " . base_url() . "index.php/docent/assignmentprofile/" . $homework["assignment_id"]);
        exit(0);
        
    }
    
    public function rejectHomework($param_id) {
        
        $param_status = "rejected";
        
        $homework = $this->Homework_model->getById($param_id);
        $this->Homework_model->updateStatusById($param_id, $param_status);
        $this->session->set_userdata("warning", "Homework status updated to rejected!");
        header("Location: " . base_url() . "index.php/docent/assignmentprofile/" . $homework["assignment_id"]);
        exit(0);
        
    }
    
    public function deleteAssignment($param_id) {
        
        if(isset($param_id)) {
            
            $assignment = $this->Assignment_model->getAssignmentById($param_id);
            $this->Assignment_model->deleteById($param_id);
            $this->session->set_userdata("warning", "Assignment successfully deleted.");
            header("Location: " . base_url() . "index.php/docent/groupprofile/" . $assignment["group_id"]);
            exit(0);
            
        }
        
    }
    
    public function assignmentProfile($param_id) {
        
        //CONTROLS
        
        //CONTROLS END
        
        if(isset($param_id)) {
        
            if(! file_exists(APPPATH . "views/pages/docent_assignment_profile.php")) {

                show_404();

            } else {

                if($data["assignment"] = $this->Assignment_model->getAssignmentById($param_id)) {
    
                    $data["homework"] = 
                    $this
                    ->Homework_model
                    ->getAllHomeworkByAssignmentId($param_id);

                    $this->load->view("templates/header.php");
                    $this->load->view("pages/docent_assignment_profile.php", $data);
                    $this->load->view("templates/footer.php");

                } else {
                    
                    $this->session->set_userdata("warning", "There is no assignment with this id.");
                    header("Location: " . base_url() . "/index.php");
                    
                }
                
            }

        }
        
    }
    
    public function groupProfile($param_id) {
        
//        CONTROLS
        
        if(isset($_POST["submit_create_assignment"])) {
            
            $title = $_POST["param_title"];
            $description = $_POST["param_description"];
            
            $this->Assignment_model->create($title, $description, $param_id);
            
            $this->session->set_userdata("warning", "Assignment successfully created.");
            
        }
        
//        CONTROLS END
        
        if(isset($param_id)) {
        
            if( ! file_exists(APPPATH . "views/pages/docent_group_profile.php")) {

                show_404();

            } else {

                $me = $this->User_model->getIdByEmail($this->session->userdata("email"));
                $m = $this->Group_model->getAllMembersById($param_id);
                $data["group"] = $this->Group_model->getGroupById($param_id);
                $data["members"] = unserialize($m["student_ids"]);

                if($data["group"]) {
                    
//                    die(var_dump($data["group"]["docent_id"]));
//                    die(var_dump($me));
                    
                    if($data["group"]["docent_id"] != $me) {
                        
                        $this->session->set_userdata("warning", "This group is not yours.");
                        header("Location: " . base_url() . "index.php/docent");
                        exit(0);
                        
                    } else {
                        
                        $this->load->view("templates/header.php");
                        $this->load->view("pages/docent_group_profile.php", $data);
                        $this->load->view("templates/footer.php");

                    }

                } else {
                    
                    $this->session->set_userdata("warning", "There is no such group as one with this ID (" . $param_id . ").");
                    header("Location: " . base_url() . "index.php/docent");
                    exit(0);
                    
                }
                
            }

        }
        
    }
    
    public function page($page) {
        
        $allowed_pages = array(
          
            0 => "docent_group_overview",
            1 => "docent_settings",
            2 => "docent_group_settings"
            
        );
        
        if( !in_array($page, $allowed_pages)) {
            
            $page = $allowed_pages[0];
            $this->session->set_userdata
                    ("warning", "You cannot visit this page from here.");
            
        }
            
        if( ! file_exists(APPPATH . "views/pages/" . $page . ".php")) {

            show_404();

        } else {

            $this->load->view("templates/header.php");
            $this->load->view("pages/" .  $page . ".php");
            $this->load->view("templates/footer.php");
                   
        }
            
        
        
    }
    
}