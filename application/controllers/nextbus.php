<?php

class Nextbus extends CI_Controller {

   public function get_config($agency)
   {
      $this->load->model('nextbus_model');
      echo json_encode($this->nextbus_model->get_config($agency)); 
   }

   public function get_predictions($agency)
   {
      $this->load->model('nextbus_model');
      echo json_encode($this->nextbus_model->get_predictions($agency));
   }

}
