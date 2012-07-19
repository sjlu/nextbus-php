<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Frontpage extends CI_Controller {

   public function index($agency = 'rutgers')
	{
      $this->load->view('include/header');

      $this->load->model('nextbus_model');
      $config = $this->nextbus_model->get_config($agency); 
      $predictions = $this->nextbus_model->get_predictions($agency);

      $this->load->view('frontpage', array(
         'config' => $config,
         'predictions' => $predictions
      ));

      $this->load->view('include/footer');
   }

}

/* End of file frontpage.php */
/* Location: ./application/controllers/frontpage.php */
