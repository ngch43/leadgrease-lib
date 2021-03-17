<?php

namespace LeadgreaseLib;

class Client
{
    private $url;
    private $method;


    public function setUrl(string $url)
    {
        $this->url = $url;
    }
    public function getUrl()
    {
        return $this->url;
    }
    public function setMethod(string $method)
    {
        $this->method = $method;
    }
    public function getMethod()
    {
        return $this->method;
    }

    public function getHeaders()
    {
        return getallheaders();
    }

    public function getFields()
    {
        if(!empty($_POST)){
            $fields = $_POST;
        }else {
            $json = file_get_contents('php://input');
            $fields = json_decode($json,true);
        }

        return ($fields) ? $fields:[];
    }


    public function getQuery()
    {
        $query = [];
        if(!empty($_GET))
            $query = $_GET;
        
        return $query;
    }

    

    public function getInfo()
    {   
        $info = [];

        $info['headers'] = $this->getHeaders();
        $info['fields'] = $this->getFields();
        $info['query'] = $this->getQuery();
        
        return $info;
    }

    public function sendInfo($data, $url, $method){

        $curl_cliente = curl_init();
        curl_setopt($curl_cliente, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl_cliente, CURLOPT_VERBOSE, true);
        curl_setopt($curl_cliente, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl_cliente, CURLOPT_SSL_VERIFYPEER, 0);

       
        $headers = $data['headers'];
        $method = strtoupper($method);
        
        
        $pixel = $this->getPixelResponse();

        // $data['fields']['pixel'] = $this->getPixelResponse();
        $query = http_build_query([
            'client_response' => json_encode($data['fields']),
            'pixel' => $pixel
        ]);
        
        if( $method == 'GET'){
            $url = $url.'?'.$query;
            unset($headers['Content-Type']);
            
        }else{
            if ($method == 'POST'){
                curl_setopt($curl_cliente, CURLOPT_POST, 1);
            }else if($method == 'PUT'){
                curl_setopt($curl_cliente, CURLOPT_CUSTOMREQUEST, "PUT"); 
            }

            if($headers['Content-Type'] == 'application/x-www-form-urlencoded'){
                curl_setopt($curl_cliente, CURLOPT_POSTFIELDS,$query);
                $headers['Content-Length'] = strlen($query); 
            }else{
                $json = json_encode([
                    'client_response' => $data['fields'],
                    'pixel' => $pixel
                ]);
                curl_setopt($curl_cliente, CURLOPT_POSTFIELDS,$json);
                $headers['Content-Length'] = strlen($json);  
            }
            
        }
         
        $request_headers = [];
        foreach ($headers as $key => $value) {
            array_push($request_headers, $key.": ".$value);
        }
        curl_setopt($curl_cliente, CURLOPT_HTTPHEADER, $request_headers);

        curl_setopt($curl_cliente, CURLOPT_URL, $url);
        $response = curl_exec($curl_cliente);
        curl_close($curl_cliente);

        return $response;
    }

}
