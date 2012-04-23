<?php

class Nextbus_model extends CI_Model {

   private $URL = "http://webservices.nextbus.com/service/publicXMLFeed";
   private $PARAMS = "";

   public function __construct()
   {
      parent::__construct();
      $this->load->driver('cache', array('adapter' => 'file'));
   }

   private function request()
   {
      $this->load->library('format');
      return $this->format->factory(file_get_contents($this->URL . $this->PARAMS), 'xml')->to_array();
   }

   private function clear_params()
   {
      $this->PARAMS = "";
   }

   private function add_param($param, $value)
   {
      if (empty($this->PARAMS))
         $this->PARAMS .= "?";
      else
         $this->PARAMS .= "&";

      $this->PARAMS .= $param."=".$value;
   }

   public function get_config($agency)
   {
      $this->add_param('a', $agency);
      $this->add_param('command', 'routeConfig');
      
      if (!$data = $this->cache->get($this->PARAMS))
      {
         $req = $this->request();
         $lines = array();
         $stops = array();
         $temp = array();

         foreach ($req['route'] as &$route)
         {
            $line = array();

            $line['tag'] = $route['@attributes']['tag'];
            $line['title'] = $route['@attributes']['title'];
            
            foreach ($route['stop'] as $stop)
            {
               //$stop_id = $stop['@attributes']['stopId'];
               $stop_tag = $stop['@attributes']['tag'];
               $stop_title = $stop['@attributes']['title'];

               $line['stops'][$stop_tag] = array(
               //   'id' => $stop_id, 
                  'tag' => $stop_tag,
                  'title' => $stop_title);
            }

            if (!isset($route['direction'][0]))
               $route['direction'] = array(0 => $route['direction']);

            foreach ($route['direction'] as $direction)
            {
               $dir_data = array('title' => $direction['@attributes']['title'],
                  'tag' => $direction['@attributes']['tag']);
               
               foreach ($direction['stop'] as $stop)
               {
                  $stop_tag = $stop['@attributes']['tag'];
                  $line['stops'][$stop_tag]['direction'] = $dir_data;
               }
            }
            
            ksort($line['stops']);
            $lines[$line['title']] = $line;
         }

         foreach ($lines as &$line)
         {
            $line_tag = $line['tag'];
            $line_title = $line['title'];
            
            foreach ($line['stops'] as $stop)
            {
               //$stops[$stop['title']][$stop['tag']]['id'] = $stop['id'];
               $stops[$stop['title']][$stop['tag']]['tag'] = $stop['tag'];
               $stops[$stop['title']][$stop['tag']]['title'] = $stop['title'];

               $stops[$stop['title']][$stop['tag']]['lines'][$line['tag']] = array(
                  'tag' => $line['tag'],
                  'title' => $line['title'],
                  'direction' => $stop['direction']);
            }
         }

         $data['lines'] = $lines;

         ksort($stops);
         $data['stops'] = $stops;
         $this->cache->save($this->PARAMS, $data, 86400); 
      }

      $this->clear_params();
      return $data;
   }

   private function process_predictions($req)
   {
      $data = array();
      foreach ($req['predictions'] as $set)
      {
         if (isset($set['@attributes']['dirTitleBecauseNoPredictions']))
            continue;

         $predictions = array();
         
         if (!isset($set['direction']['prediction'][0]))
            $set['direction']['prediction'] = array(0 => $set['direction']['prediction']);

         foreach ($set['direction']['prediction'] as $time)
         {
            $time = $time['@attributes'];
            $timeset = array(
               'seconds' => $time['seconds'],
               'minutes' => $time['minutes'],
               'time' => $time['epochTime']
            );

            $predictions[] = $timeset;
         }

         $set = $set['@attributes'];
         $data[$set['routeTag']][$set['stopTag']] = $predictions;
      }

      return $data;
   }

   public function get_predictions($agency)
   {
      $config = $this->get_config($agency);

      if (!$data = $this->cache->get('all-predictions-'.$agency))
      {
         $this->add_param('command', 'predictionsForMultiStops');
         $this->add_param('a', $agency);

         foreach ($config['stops'] as $stop)
         {
            foreach ($stop as $stop_tag)
            {
               $tag = $stop_tag['tag'];
               foreach ($stop_tag['lines'] as $line)
               {
                  $line_tag = $line['tag'];
                  $direction = $line['direction']['tag'];

                  $this->add_param('stops', $line_tag.'|'.$direction.'|'.$tag);
               }
            }
         }
   
         $data = $this->process_predictions($this->request());
         $this->cache->save('all-predictions-'.$agency, $data, 20);
      }

      return $data;
   }

}
