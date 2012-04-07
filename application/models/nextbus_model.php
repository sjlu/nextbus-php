<?php

class Nextbus_model extends CI_Model {

   private $URL = "http://webservices.nextbus.com/service/publicXMLFeed";
   private $PARAMS = "";

   public function __construct()
   {
      parent::__construct();
   }

   private function request()
   {
      $this->load->library('format');
      return $this->format->factory(file_get_contents($this->URL . $this->PARAMS), 'xml')->to_array();
   }

   private function add_param($param, $value)
   {
      if (empty($this->PARAMS))
         $this->PARAMS .= "?";
      else
         $this->PARAMS .= "&";

      $this->PARAMS .= $param."=".$value;
   }

   public function get_route_config($agency)
   {
      $this->add_param('a', $agency);
      $this->add_param('command', 'routeConfig');
         
      $this->load->driver('cache', array('adapter' => 'file'));
      
      if (!$data = $this->cache->get($this->PARAMS))
      {
         $req = $this->request();
         $data = array();

         foreach ($req['route'] as $route)
         {
            $line = array();

            $line['id'] = $route['@attributes']['tag'];
            $line['title'] = $route['@attributes']['title'];
            
            foreach ($route['stop'] as $stop)
            {
               $line['id']['stops'][] = array(
                  'id' => $stop['@attributes']['stopId'], 
                  'title' => $stop['@attributes']['title']);
            }

            $data[] = $line;
         }

         $this->cache->save($this->PARAMS, $data, 60); 
      }

      return $data;
   }

}
