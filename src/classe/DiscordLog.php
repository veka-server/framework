<?php

namespace App\classe;

use DateTime;
use DateTimeZone;
use Error;
use ErrorException;
use Exception;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class DiscordLog implements MiddlewareInterface
{

    /**
     * @var ResponseFactoryInterface
     */
    private $responseFactory;

    // webhooks des channels
    public static $defaut_chanel ;

    public static $applicationName ;

    // les types disponible
    const ERROR = 'error';
    const WARNING = 'warning';
    const GOOD = 'good';
    const NOTICE = 'notice';
    const INFO = 'info';
    private static $PieceJointeNameForNextSend =  'piece_jointe.log';

    public function __construct(ResponseFactoryInterface $responseFactory, string $defaut_channel, String $applicationName) {
        $this->responseFactory = $responseFactory;
        
        self::$defaut_chanel = $defaut_channel;
        self::$applicationName = $applicationName;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {

        ob_start();
        $level = ob_get_level();

        try {
            $response = $handler->handle($request);
        } catch (\Throwable $exception) {
            self::sendLog($exception);
            throw $exception;
        } finally {
            while (ob_get_level() >= $level) {
                ob_end_clean();
            }
        }

        return $response;
    }

    /**
     * @param             $message
     * @param null|string $type
     *
     * @throws Exception
     */
    public static function sendLog($message, $type = null) {
        $channel = self::$defaut_chanel;
        self::sendMessage($channel, $message, $type);
    }

    /**
     * @param             $message
     * @param null|string $type
     *
     * @throws Exception
     */
    public static function sendLogWithBacktrace($message, $type = null) {
        $channel = self::$defaut_chanel;
        $message .= "\n".print_r(debug_backtrace(), true);
        self::sendMessage($channel, $message, $type);
    }

    /**
     * @param             $message
     * @param null|string $type
     *
     * @throws Exception
     */
    public static function sendRapport($message, $type = null) {
        self::sendMessage(self::$defaut_chanel, $message, $type);
    }

    /**
     * @param string $channel
     * @param $message
     * @param null|string $type
     * @throws Exception
     */
    public static function sendMessage($channel, $message, $type = null) {

        $description = '';
        if(!is_string($message)){
            list($message, $title, $description, $type) = self::getMessageFromException($message);
        }

        if(strlen($message) > 200){
            $description = $message;
            $title = 'Message" trop longs';
            $message = 's';
        }

        switch($type){

            case self::ERROR :
                $color = hexdec("ff0000");
                $error_name = 'ERROR';
                break;

            case self::WARNING :
                $color = hexdec("e8a92c");
                $error_name = 'WARNING';
                break;

            case self::GOOD :
                $color = hexdec("5bce44");
                $error_name = 'SUCCESS';
                break;

            case self::NOTICE :
                $color = hexdec("00aeff");
                $error_name = 'NOTICE';
                break;

            case self::INFO :
                $color = hexdec("d3d3d3");
                $error_name = 'INFO';
                break;

            default :
                $color = null;
                $error_name = null;
                break;
        }

        $hookObject = json_encode([
            /*
             * The general "message" shown above your embeds
             */
            "content" => null,
            /*
             * The username shown in the message
             */
            "username" => self::$applicationName,
            /*
             * The image location for the senders image
             */
            "avatar_url" => null,

            /*
             * Whether or not to read the message in Text-to-speech
             */
            "tts" => false,
            /*
             * An array of Embeds
             */
            "embeds" => [
                /*
                 * Our first embed
                 */
                [
                    "title" => $title ?? null,

                    "description" => preg_replace( '/[^[:print:]\r\n]/', '',$description) ?? null,

                    // The type of your embed, will ALWAYS be "rich"
                    "type" => "rich",

                    // The integer color to be used on the left side of the embed
                    "color" => $color,

                    // Author object
                    "author" => [
                        "name" => $error_name,
                    ],
                    "content" => $message,
                ]
            ]

        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );

        $hookObject = array (
            'payload_json' => $hookObject
        );

        self::sendCurl($channel, $hookObject);

    }

    /**
     * @param ErrorException $exception
     * @return string
     */
    private static function getExceptionTraceAsString($exception) {
        $rtn = "";
        $count = 0;
        foreach ($exception->getTrace() as $frame) {
            $args = "";
            if (isset($frame['args'])) {
                $args = array();
                foreach ($frame['args'] as $arg) {
                    if (is_string($arg)) {
                        $args[] = "'" . $arg . "'";
                    } elseif (is_array($arg)) {
                        $args[] = "Array";
                    } elseif (is_null($arg)) {
                        $args[] = 'NULL';
                    } elseif (is_bool($arg)) {
                        $args[] = ($arg) ? "true" : "false";
                    } elseif (is_object($arg)) {
                        $args[] = get_class($arg);
                    } elseif (is_resource($arg)) {
                        $args[] = get_resource_type($arg);
                    } else {
                        $args[] = $arg;
                    }
                }
                $args = join(", ", $args);
            }
            $current_file = "[internal function]";
            if(isset($frame['file'])) {
                $current_file = $frame['file'];
            }
            $current_line = "";
            if(isset($frame['line'])) {
                $current_line = $frame['line'];
            }
            $rtn .= sprintf( "#%s %s(%s): %s(%s)\n",
                $count,
                $current_file,
                $current_line,
                $frame['function'],
                $args );
            $count++;
        }
        return $rtn;
    }

    /**
     * @param $message
     * @return array
     * @throws Exception
     */
    private static function getMessageFromException($message)
    {
        if(method_exists($message,'getTrace')){
            $trace = self::getExceptionTraceAsString($message);

            if(empty($trace)){
                if($message instanceof Error) {
                    $trace = $message->getFile().' ('.$message->getLine().')';
                } else {
                    $trace = 'Aucun detail sur l\'erreur disponible';
                }
            }

            $msg = "```\r\n";
            $msg .= $trace."\r\n";
            $msg .= "```\r\n";
        }

        $tz = 'Europe/Paris';
        $timestamp = time();
        $dt = new DateTime("now", new DateTimeZone($tz)); //first argument "must" be a string
        $dt->setTimestamp($timestamp); //adjust the object to correct timestamp

        $date = 'Emis le '.$dt->format('d-m-Y à H:i:s');

        if(method_exists($message,'getSeverity')){
            $type = self::getTypeFromException($message->getSeverity());
        }

        return array( $date, $message->getMessage(), $msg ?? null, $type ?? self::ERROR);
    }


    private static function getTypeFromException($severity){

        switch($severity) {

            default :
            case E_USER_ERROR :
            case E_COMPILE_ERROR :
            case E_PARSE :
            case E_ERROR :
                $type = self::ERROR;
                break;

            case E_USER_WARNING :
            case E_WARNING :
            case E_COMPILE_WARNING :
            case E_CORE_WARNING :
                $type = self::WARNING;
                break;

            case E_USER_NOTICE :
            case E_NOTICE :
                $type = self::NOTICE;
                break;

            case E_DEPRECATED :
            case E_USER_DEPRECATED :
            case E_STRICT :
                $type = self::INFO;
                break;
        }

        return $type;
    }

    public static function sendDump($var)
    {
        $channel = self::$defaut_chanel;

        $color = hexdec("d3d3d3");
        $error_name = 'Dump';

        $message = 'ttt';
        $description = '```'.print_r($var, true).'```';

        $hookObject = json_encode([
            /*
             * The general "message" shown above your embeds
             */
            "content" => null,
            /*
             * The username shown in the message
             */
            "username" => self::$applicationName,
            /*
             * The image location for the senders image
             */
            "avatar_url" => null,
            /*
             * Whether or not to read the message in Text-to-speech
             */
            "tts" => false,
            /*
             * An array of Embeds
             */
            "embeds" => [
                /*
                 * Our first embed
                 */
                [
                    "title" => $title ?? null,

                    "description" => $description ?? null,

                    // The type of your embed, will ALWAYS be "rich"
                    "type" => "rich",

                    // The integer color to be used on the left side of the embed
                    "color" => $color,

                    // Author object
                    "author" => [
                        "name" => $error_name,
                    ],
                    "content" => $message,
                ]
            ]

        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );

        $hookObject = array (
            'payload_json' => $hookObject
        );

        self::sendCurl($channel, $hookObject);
    }

    public static function forcePieceJointeNameForNextSend($new_name = 'piece_jointe.log'){
        self::$PieceJointeNameForNextSend = $new_name;
    }

    public static function sendCurl($channel, $hookObject, $whith_retry = true, $file_data = null){
        $filepath = '';
        try{
            if(!empty($file_data)){
                $filepath = '/tmp/discord_log_file_'.microtime();
                file_put_contents($filepath, $file_data);

                // truncate le fichier s'il depasse 7.9Mo ( limit discord = 8Mo )
                $limit_upload_discord = 7900000; /* 7.9Mo */
                if(filesize($filepath) > $limit_upload_discord) {
                    $fp = fopen($filepath, "r+");
                    ftruncate($fp, $limit_upload_discord);
                    fclose($fp);
                }

                $hookObject['file'] = new CURLFile($filepath,null, self::$PieceJointeNameForNextSend );

                self::$PieceJointeNameForNextSend = 'piece_jointe.log';
            }
        }catch(Exception $e){}

        $ch = curl_init( $channel );
        curl_setopt( $ch, CURLOPT_POST, 1);
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $hookObject);
        curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt( $ch, CURLOPT_HEADER, 0);
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type: multipart/form-data'));
        curl_exec( $ch );

        // check the HTTP status code of the request
        $resultStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch) ) {
            if($whith_retry) {
                self::retry_mode_light($channel, $hookObject);
            }
        } else {
            if ($resultStatus < 200 || $resultStatus > 299 ) {
                if($whith_retry) {
                    self::retry_mode_light($channel, $hookObject);
                }
            }
        }

        try{
            if(!empty($filepath)) {
                unlink($filepath);
            }
        }catch(Exception $e) {}
    }

    public static function retry_mode_light($channel, $hookObject_original){
        $limit_size_message = 240;
        $hookObject = json_decode($hookObject_original['payload_json']);
        $uncut_hookObject = '';
        try{
            $uncut_hookObject = $hookObject->embeds[0]->description;
        }catch(Exception $e){}

        foreach ( $hookObject->embeds as &$embed) {
            if(strlen($embed->content) > $limit_size_message){
                $embed->content = mb_strimwidth($embed->content, 0, $limit_size_message, "...");
            }

            if(strlen($embed->description) > $limit_size_message){
                $embed->description = mb_strimwidth($embed->description, 0, $limit_size_message, "...").'```';
            }

            if(strlen($embed->title) > $limit_size_message){
                $embed->title = mb_strimwidth($embed->title, 0, $limit_size_message, "...");
            }

            if(isset($embed->fields)){
                foreach ( $embed->fields as &$fields) {
                    if(strlen($fields->name) > $limit_size_message){
                        $fields->name = mb_strimwidth($fields->name, 0, $limit_size_message, "...");
                    }
                }
            }
        }

        $hookObject_original['payload_json'] = json_encode($hookObject);
        self::sendCurl($channel, $hookObject_original, false, $uncut_hookObject);
    }

}