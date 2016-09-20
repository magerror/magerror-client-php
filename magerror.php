<?php
/**
 * @copyright  2016 Magerror (https://www.magerror.com/)
 */

class Magerror
{

    /**
     * Client version
     *
     * @var string
     */
    private $version = '0.1.0';

    /**
     * API token
     *
     * @var
     */
    private $token;

    /**
     * API url
     *
     * @var string
     */
    private $url = 'https://api.magerror.com/';

    /**
     * @var int
     */
    private $quantity = 10;

    /**
     * Reports directory
     *
     * @var
     */
    private $path;

    /**
     * @param $token
     * @param $path
     */
    public function __construct($token, $path)
    {
        $this->token = $token;
        $this->path = $path;
    }

    /**
     * Execute process
     */
    public function run()
    {
        $directory = dir($this->path);
        $counter = 0;
        while ($file = $directory->read()) {
            if (is_file($this->path . DIRECTORY_SEPARATOR . $file) && $counter < $this->quantity) {
                $stat = stat($this->path . DIRECTORY_SEPARATOR . $file);

                $file_name = $file;
                $file_content = file_get_contents($this->path . DIRECTORY_SEPARATOR . $file);
                $file_date = date('Y-m-d H:i:s', $stat['ctime']);

                $request = array(
                    'name' => $file_name,
                    'content' => $file_content,
                    'date' => $file_date,
                    'version' => $this->version
                );
                $response = $this->postReport('report', $request);
                if ($response['code'] == '204') {
                    unlink($this->path . DIRECTORY_SEPARATOR . $file);
                }
                $counter++;
            } else {
                continue;
            }
        }
        if ($counter === 0) {
            $this->callHeartbeat();
        }
    }

    /**
     * Perform post.
     *
     * @param $action
     * @param array $fields
     * @return mixed
     */
    private function postReport($action, array $fields)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('token: ' . $this->token, 'Accept: application/json', 'Content-Type: application/json'));
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER,0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST,0);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
        curl_setopt($curl, CURLINFO_HTTP_CODE, true);
        curl_setopt($curl, CURLOPT_URL, $this->url . $action);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($fields));

        $response['message'] = curl_exec($curl);
        $response['code'] = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);
        return $response;
    }

    /**
     * @param $action
     * @return mixed
     */
    private function callHeartbeat()
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('token: ' . $this->token, 'Accept: application/json', 'Content-Type: application/json'));
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER,0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST,0);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
        curl_setopt($curl, CURLINFO_HTTP_CODE, true);
        curl_setopt($curl, CURLOPT_URL, $this->url . 'heartbeat');
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');

        $response['message'] = curl_exec($curl);
        $response['code'] = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);
        return $response;
    }


}

//Your API Token string
$token = '';
//Full path to your magento reports directory
$directory = '';


$client = new Magerror($token, $directory);
$client->run();
