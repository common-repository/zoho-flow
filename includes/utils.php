<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

function zoho_flow_debug($message) {
      if(WP_ZOHO_FLOW_DEBUG){
            ini_set('log_errors', true);
            ini_set('error_log', WP_ZOHO_FLOW_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'errors.log');
            error_log(print_r($message, true));     
      }
}

function zoho_flow_execute_webhook($url, $post_params, $file_params){

	$args = array();
	if(empty($file_params)){
		$args['headers'] = array(
			'content-type' => 'application/json',
		);	
		$args['body'] = json_encode($post_params);

	}
	else{

        $mime_map = [
            'video/3gpp2'                                                               => '3g2',
            'video/3gp'                                                                 => '3gp',
            'video/3gpp'                                                                => '3gp',
            'application/x-compressed'                                                  => '7zip',
            'audio/x-acc'                                                               => 'aac',
            'audio/ac3'                                                                 => 'ac3',
            'application/postscript'                                                    => 'ai',
            'audio/x-aiff'                                                              => 'aif',
            'audio/aiff'                                                                => 'aif',
            'audio/x-au'                                                                => 'au',
            'video/x-msvideo'                                                           => 'avi',
            'video/msvideo'                                                             => 'avi',
            'video/avi'                                                                 => 'avi',
            'application/x-troff-msvideo'                                               => 'avi',
            'application/macbinary'                                                     => 'bin',
            'application/mac-binary'                                                    => 'bin',
            'application/x-binary'                                                      => 'bin',
            'application/x-macbinary'                                                   => 'bin',
            'image/bmp'                                                                 => 'bmp',
            'image/x-bmp'                                                               => 'bmp',
            'image/x-bitmap'                                                            => 'bmp',
            'image/x-xbitmap'                                                           => 'bmp',
            'image/x-win-bitmap'                                                        => 'bmp',
            'image/x-windows-bmp'                                                       => 'bmp',
            'image/ms-bmp'                                                              => 'bmp',
            'image/x-ms-bmp'                                                            => 'bmp',
            'application/bmp'                                                           => 'bmp',
            'application/x-bmp'                                                         => 'bmp',
            'application/x-win-bitmap'                                                  => 'bmp',
            'application/cdr'                                                           => 'cdr',
            'application/coreldraw'                                                     => 'cdr',
            'application/x-cdr'                                                         => 'cdr',
            'application/x-coreldraw'                                                   => 'cdr',
            'image/cdr'                                                                 => 'cdr',
            'image/x-cdr'                                                               => 'cdr',
            'zz-application/zz-winassoc-cdr'                                            => 'cdr',
            'application/mac-compactpro'                                                => 'cpt',
            'application/pkix-crl'                                                      => 'crl',
            'application/pkcs-crl'                                                      => 'crl',
            'application/x-x509-ca-cert'                                                => 'crt',
            'application/pkix-cert'                                                     => 'crt',
            'text/css'                                                                  => 'css',
            'text/x-comma-separated-values'                                             => 'csv',
            'text/comma-separated-values'                                               => 'csv',
            'application/vnd.msexcel'                                                   => 'csv',
            'application/x-director'                                                    => 'dcr',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'   => 'docx',
            'application/x-dvi'                                                         => 'dvi',
            'message/rfc822'                                                            => 'eml',
            'application/x-msdownload'                                                  => 'exe',
            'video/x-f4v'                                                               => 'f4v',
            'audio/x-flac'                                                              => 'flac',
            'video/x-flv'                                                               => 'flv',
            'image/gif'                                                                 => 'gif',
            'application/gpg-keys'                                                      => 'gpg',
            'application/x-gtar'                                                        => 'gtar',
            'application/x-gzip'                                                        => 'gzip',
            'application/mac-binhex40'                                                  => 'hqx',
            'application/mac-binhex'                                                    => 'hqx',
            'application/x-binhex40'                                                    => 'hqx',
            'application/x-mac-binhex40'                                                => 'hqx',
            'text/html'                                                                 => 'html',
            'image/x-icon'                                                              => 'ico',
            'image/x-ico'                                                               => 'ico',
            'image/vnd.microsoft.icon'                                                  => 'ico',
            'text/calendar'                                                             => 'ics',
            'application/java-archive'                                                  => 'jar',
            'application/x-java-application'                                            => 'jar',
            'application/x-jar'                                                         => 'jar',
            'image/jp2'                                                                 => 'jp2',
            'video/mj2'                                                                 => 'jp2',
            'image/jpx'                                                                 => 'jp2',
            'image/jpm'                                                                 => 'jp2',
            'image/jpeg'                                                                => 'jpeg',
            'image/pjpeg'                                                               => 'jpeg',
            'application/x-javascript'                                                  => 'js',
            'application/json'                                                          => 'json',
            'text/json'                                                                 => 'json',
            'application/vnd.google-earth.kml+xml'                                      => 'kml',
            'application/vnd.google-earth.kmz'                                          => 'kmz',
            'text/x-log'                                                                => 'log',
            'audio/x-m4a'                                                               => 'm4a',
            'audio/mp4'                                                                 => 'm4a',
            'application/vnd.mpegurl'                                                   => 'm4u',
            'audio/midi'                                                                => 'mid',
            'application/vnd.mif'                                                       => 'mif',
            'video/quicktime'                                                           => 'mov',
            'video/x-sgi-movie'                                                         => 'movie',
            'audio/mpeg'                                                                => 'mp3',
            'audio/mpg'                                                                 => 'mp3',
            'audio/mpeg3'                                                               => 'mp3',
            'audio/mp3'                                                                 => 'mp3',
            'video/mp4'                                                                 => 'mp4',
            'video/mpeg'                                                                => 'mpeg',
            'application/oda'                                                           => 'oda',
            'audio/ogg'                                                                 => 'ogg',
            'video/ogg'                                                                 => 'ogg',
            'application/ogg'                                                           => 'ogg',
            'application/x-pkcs10'                                                      => 'p10',
            'application/pkcs10'                                                        => 'p10',
            'application/x-pkcs12'                                                      => 'p12',
            'application/x-pkcs7-signature'                                             => 'p7a',
            'application/pkcs7-mime'                                                    => 'p7c',
            'application/x-pkcs7-mime'                                                  => 'p7c',
            'application/x-pkcs7-certreqresp'                                           => 'p7r',
            'application/pkcs7-signature'                                               => 'p7s',
            'application/pdf'                                                           => 'pdf',
            'application/octet-stream'                                                  => 'pdf',
            'application/x-x509-user-cert'                                              => 'pem',
            'application/x-pem-file'                                                    => 'pem',
            'application/pgp'                                                           => 'pgp',
            'application/x-httpd-php'                                                   => 'php',
            'application/php'                                                           => 'php',
            'application/x-php'                                                         => 'php',
            'text/php'                                                                  => 'php',
            'text/x-php'                                                                => 'php',
            'application/x-httpd-php-source'                                            => 'php',
            'image/png'                                                                 => 'png',
            'image/x-png'                                                               => 'png',
            'application/powerpoint'                                                    => 'ppt',
            'application/vnd.ms-powerpoint'                                             => 'ppt',
            'application/vnd.ms-office'                                                 => 'ppt',
            'application/msword'                                                        => 'ppt',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'pptx',
            'application/x-photoshop'                                                   => 'psd',
            'image/vnd.adobe.photoshop'                                                 => 'psd',
            'audio/x-realaudio'                                                         => 'ra',
            'audio/x-pn-realaudio'                                                      => 'ram',
            'application/x-rar'                                                         => 'rar',
            'application/rar'                                                           => 'rar',
            'application/x-rar-compressed'                                              => 'rar',
            'audio/x-pn-realaudio-plugin'                                               => 'rpm',
            'application/x-pkcs7'                                                       => 'rsa',
            'text/rtf'                                                                  => 'rtf',
            'text/richtext'                                                             => 'rtx',
            'video/vnd.rn-realvideo'                                                    => 'rv',
            'application/x-stuffit'                                                     => 'sit',
            'application/smil'                                                          => 'smil',
            'text/srt'                                                                  => 'srt',
            'image/svg+xml'                                                             => 'svg',
            'application/x-shockwave-flash'                                             => 'swf',
            'application/x-tar'                                                         => 'tar',
            'application/x-gzip-compressed'                                             => 'tgz',
            'image/tiff'                                                                => 'tiff',
            'text/plain'                                                                => 'txt',
            'text/x-vcard'                                                              => 'vcf',
            'application/videolan'                                                      => 'vlc',
            'text/vtt'                                                                  => 'vtt',
            'audio/x-wav'                                                               => 'wav',
            'audio/wave'                                                                => 'wav',
            'audio/wav'                                                                 => 'wav',
            'application/wbxml'                                                         => 'wbxml',
            'video/webm'                                                                => 'webm',
            'image/webp'                                                                => 'webp',
            'audio/x-ms-wma'                                                            => 'wma',
            'application/wmlc'                                                          => 'wmlc',
            'video/x-ms-wmv'                                                            => 'wmv',
            'video/x-ms-asf'                                                            => 'wmv',
            'application/xhtml+xml'                                                     => 'xhtml',
            'application/excel'                                                         => 'xl',
            'application/msexcel'                                                       => 'xls',
            'application/x-msexcel'                                                     => 'xls',
            'application/x-ms-excel'                                                    => 'xls',
            'application/x-excel'                                                       => 'xls',
            'application/x-dos_ms_excel'                                                => 'xls',
            'application/xls'                                                           => 'xls',
            'application/x-xls'                                                         => 'xls',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'         => 'xlsx',
            'application/vnd.ms-excel'                                                  => 'xlsx',
            'application/xml'                                                           => 'xml',
            'text/xml'                                                                  => 'xml',
            'text/xsl'                                                                  => 'xsl',
            'application/xspf+xml'                                                      => 'xspf',
            'application/x-compress'                                                    => 'z',
            'application/x-zip'                                                         => 'zip',
            'application/zip'                                                           => 'zip',
            'application/x-zip-compressed'                                              => 'zip',
            'application/s-compressed'                                                  => 'zip',
            'multipart/x-zip'                                                           => 'zip',
            'text/x-scriptzsh'                                                          => 'zsh',
        ];
		$boundary = wp_generate_password(24); // Just a random string, use something better than wp_generate_password() though.


		$payload = '';

		$args['headers'] = array(
		        'content-type' => 'multipart/form-data; boundary=' . $boundary
		);	

		// First, add the standard POST fields:
		foreach ( $post_params as $name => $value ) {
		        $payload .= '--' . $boundary;
		        $payload .= "\r\n";
		        $payload .= 'Content-Disposition: form-data; name="' . $name . '"' . "\r\n\r\n";
		        $payload .= $value;
		        $payload .= "\r\n";
		}

		// Upload the file
		$file_info = new finfo(FILEINFO_MIME_TYPE);
		foreach ( $file_params as $name => $file ) {
                $mime_type = $file_info->buffer($file);
                $extn = $mime_map[$mime_type];

		        $payload .= '--' . $boundary;
		        $payload .= "\r\n";
		        $payload .= 'Content-Disposition: form-data; name="' . $name . '"; filename="' . $name . '.' . $extn . '"' . "\r\n";
		       	$payload .= 'Content-Type: ' . $mime_type . "\r\n";
		        $payload .= "\r\n";
		        $payload .= $file;
		        $payload .= "\r\n";
		}	
		$payload .= '--' . $boundary . '--';
		$args['body'] = $payload;

	}
	$args['timeout'] = '5';

	$res = wp_remote_post( $url, $args );
    $response_code = wp_remote_retrieve_response_code( $res );
    if($response_code >= 400){
        //TODO:Handle error
    }
}

function zoho_flow_convert_php_java_datepattern($php_pattern){
      //https://docs.oracle.com/javase/7/docs/api/java/text/SimpleDateFormat.html
      //https://www.php.net/manual/en/function.date.php      
      $format_mapping = array(

            'd' => 'dd',    //Day of the month, 2 digits with leading zeros   01 to 31
            'D' => 'E',    //A textual representation of a day, three letters      Mon through Sun
            'j' => 'd',    //Day of the month without leading zeros    1 to 31 ; l (lowercase L) A full textual representation of the day of the week  Sunday through Saturday
            'N' => 'u',   //ISO-8601 numeric representation of the day of the week (added in PHP 5.1.0)   1 (for Monday) through 7 (for Sunday)
            'S' => '',    //English ordinal suffix for the day of the month, 2 characters     st, nd, rd or th. Works well with j
            'w' => 'F',    //Numeric representation of the day of the week   0 (for Sunday) through 6 (for Saturday)
            'z' => 'D',    //The day of the year (starting from 0)     0 through 365
            // Week  ---   ---
            'W' => 'w',    //ISO-8601 week number of year, weeks starting on Monday      Example: 42 (the 42nd week in the year)
            //Month ---   ---
            'F' => 'MMMMM',    //A full textual representation of a month, such as January or March      January through December
            'm' => 'MM',    //Numeric representation of a month, with leading zeros 01 through 12
            'M' => 'MMM',    //A short textual representation of a month, three letters    Jan through Dec
            'n' => 'M',    //Numeric representation of a month, without leading zeros    1 through 12
            't' => '',    //Number of days in the given month   28 through 31
            // Year  ---   ---
            'L' => '',    //Whether it's a leap year      1 if it is a leap year, 0 otherwise.
            'o' => 'Y',    //ISO-8601 week-numbering year. This has the same value as Y, except that if the ISO week number (W) belongs to the previous or next year, that year is used instead. (added in PHP 5.1.0)  Examples: 1999 or 2003
            'Y' => 'yyyy',    //A full numeric representation of a year, 4 digits     Examples: 1999 or 2003
            'y' => 'yy',    //A two digit representation of a year      Examples: 99 or 03
            // Time  ---   ---
            'a' => 'a',    //Lowercase Ante meridiem and Post meridiem am or pm
            'A' => 'a',    //Uppercase Ante meridiem and Post meridiem AM or PM
            'B' => '',    //Swatch Internet time    000 through 999
            'g' => 'h',    //12-hour format of an hour without leading zeros 1 through 12
            'G' => 'H',    //24-hour format of an hour without leading zeros 0 through 23
            'h' => 'hh',    //12-hour format of an hour with leading zeros    01 through 12
            'H' => 'HH',    //24-hour format of an hour with leading zeros    00 through 23
            'i' => 'mm',    //Minutes with leading zeros    00 to 59
            's' => 'ss',    //Seconds with leading zeros    00 through 59
            'u' => '',    //Microseconds (added in PHP 5.2.2). Note that date() will always generate 000000 since it takes an integer parameter, whereas DateTime::format() does support microseconds if DateTime was created with microseconds.    Example: 654321
            'v' => 'S',    //Milliseconds (added in PHP 7.0.0). Same note applies as for u.    Example: 654
            // Timezone    ---   ---
            'e' => 'z',    //Timezone identifier (added in PHP 5.1.0)  Examples: UTC, GMT, Atlantic/Azores
            'I' => '',   //(capital i)     Whether or not the date is in daylight saving time    1 if Daylight Saving Time, 0 otherwise.
            'O' => 'Z',    //Difference to Greenwich time (GMT) without colon between hours and minutes    Example: +0200
            'P' => 'XXX',    //Difference to Greenwich time (GMT) with colon between hours and minutes (added in PHP 5.1.3)    Example: +02:00
            'T' => 'z',    //Timezone abbreviation   Examples: EST, MDT ...
            'Z' => 'Z',   //Timezone offset in seconds. The offset for timezones west of UTC is always negative, and for those east of UTC is always positive.  -43200 through 50400
            // Full Date/Time    ---   ---
            'c' => "yyyy-MM-dd'T'HH:mm:ssXXX",    //ISO 8601 date (added in PHP 5)      2004-02-12T15:19:21+00:00
            'r' => '',    //» RFC 2822 formatted date     Example: Thu, 21 Dec 2000 16:01:07 +0200
            'U' => '',    //Seconds since the Unix Epoch (January 1 1970 00:00:00 GMT)  See also time()
      );

      $pattern_chars = str_split($php_pattern);
      $java_pattern = '';
      foreach ($pattern_chars as $char) {
            if(!$escape && array_key_exists($char, $format_mapping)){
                  $java_pattern = $java_pattern . $format_mapping[$char];
            }
            else if($escape){
                  $java_pattern = $java_pattern . $char;
                  $escape = false;
            }
            else if($char == '\\'){
                  $escape = true;
                  if(!$string_sequence){
                        $java_pattern = $java_pattern . "'";
                        $string_sequence = true;
                  }
            }
            else if($string_sequence){
                  if(preg_match("/^[a-zA-Z]$/", $char)){
                        $java_pattern = $java_pattern . '\\' . $char;
                  }
                  else{
                        $java_pattern = $java_pattern . "'";
                        $java_pattern = $java_pattern . $char;
                  }
                  $string_sequence = false;
            }
            else{
                  $java_pattern = $java_pattern . $char;
            }
      }
      return $java_pattern;
}