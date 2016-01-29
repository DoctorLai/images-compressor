<?php
/**
 * ImagesCompressor Class
 *
 * @author Oh@SteakOverCooked.com
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */
 
/*
Example: 

$obj = new Optimizer("$app_key", "$app_secret");
$response = $obj->optimize("/var/www/imagerecycle/test.jpg");
echo ($response->errCode);
echo ($response->result->optimize); 
*/
 

/* 
  URL:  https://helloacm.com/images-compressor/ 
*/
class Optimizer {
    /**
     * The ImagesCompressor API endpoint
     */
    const API_ENDPOINT = 'https://helloacm.com/api/images-compressor';

    /**
     * The ImagesCompressor API key
     */
    private $api_key = '';
    
    /**
     * The ImagesCompressor API Secret
     */
    private $api_secret = '';
    
    /**
     * HTTP headers
     */
    private $headers = array();

    /**
     * The constructor
     *
     * @return void
     **/
    public function __construct( $api_key = '', $api_secret = '' ) {
        if ( ! empty( $api_key ) ) {
            $this->api_key = $api_key;
        }
        
        if ( ! empty( $api_key ) ) {
            $this->api_secret = $api_secret;
        }        

        // Check if php-curl is enabled
        if ( ! function_exists( 'curl_init' ) || ! function_exists( 'curl_exec' ) ) {
            die('cURL isn\'t installed on the server.');
        }
    }


    /**
     * Optimize an image from its binary content.
     *
     * @param  string $image   Image path
     * @param  array  $options (optional) Optimization options
     *                         array(
     *                             'level'     => integer (0 to 100),
     *                         ) 
     * @return array
     **/
    public function optimize( $image, $options = array() ) {

        if ( !is_string($image) || !is_file($image) ) {
            return (object) array('success' => false, 'message' => 'Image incorrect!');
        } else if ( !is_readable($image) ) {
            return (object) array('success' => false, 'message' => 'Image not readable!');
        }

        if ( !function_exists('curl_file_create') ) {
            function curl_file_create($filename, $mimetype = '', $postname = '') {
                return "@$filename;filename=" . ( $postname ?: basename($filename) ) . ( $mimetype ? ";type=$mimetype" : '' );
            }
        }

        $default = array(
            'level'     => '90'
        );

        $options = array_merge( $default, $options );
        
        $data = array(
            'file' => curl_file_create( $image ),
            'm'  => $options['level'],
            'key' => $this->api_key,
            'secret' => $this->api_secret
        );

        return $this->request( '/', array( 'post_data' => $data ) );
    }

    /**
     * Make an HTTP call using curl.
     *
     * @param  string $url       The URL to call
     * @param  array  $options   Optional request options
     * @return object
     **/
    private function request( $url, $options = array() ) {

        $default = array( 'method' => 'POST', 'post_data' => null, 'timeout' => 60 );
        $options = array_merge( $default, $options );

        try {

            $ch      = curl_init();
            $is_ssl  = ( isset( $_SERVER['HTTPS'] ) && ( 'on' == strtolower( $_SERVER['HTTPS'] ) || '1' == $_SERVER['HTTPS'] ) ) || ( isset( $_SERVER['SERVER_PORT'] ) && ( '443' == $_SERVER['SERVER_PORT'] ) );

            if ( 'POST' == $options['method'] ) {
                curl_setopt( $ch, CURLOPT_POST, true );
                curl_setopt( $ch, CURLOPT_POSTFIELDS, $options['post_data'] );
            }

            curl_setopt( $ch, CURLOPT_URL, self::API_ENDPOINT . $url );
            curl_setopt( $ch, CURLOPT_USERAGENT, 'ImagesCompressor PHP Class');
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
            curl_setopt( $ch, CURLOPT_HTTPHEADER, $this->headers );
            curl_setopt( $ch, CURLOPT_TIMEOUT, $options['timeout'] );
            @curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
            curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, $is_ssl );
            
            $response  = json_decode( curl_exec( $ch ) );
            $error     = curl_error( $ch );
            $http_code = curl_getinfo( $ch, CURLINFO_HTTP_CODE );

            curl_close( $ch );

        } catch( Exception $e ) {
            return (object) array('success' => false, 'message' => 'Unknown error occurred');
        }

        if ( 200 != $http_code && isset( $response->code, $response->detail ) ) {
            return $response;
        } elseif ( 200 != $http_code ) {
            return (object) array('success' => false, 'message' => 'Unknown error occurred');
        }

        return $response;
    }
};

