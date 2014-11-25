<?php 

/**
 * Parser
 *
 * Class to parse the log file as per the following format
 *
 * Format  : {timestamp} {source}[{process}]: at={log_level} method={http_method} path={http_path} host={http_host} fwd={client_ip} dyno={responding_dyno} connect={connection_time}ms service={processing_time}ms status={http_status} bytes={bytes_sent}
 *
 * Example : 2014-01-09T06:16:53.916977+00:00 heroku[router]: at=info method=GET path=/api/users/1538823671/count_pending_messages host=services.pocketplaylab.com fwd="208.54.86.162" dyno=web.11 connect=7ms service=9ms status=200 bytes=33
 *
 * @author Kamal Nayak
 */
class Parser
{
	/**
	 * Regular expressions for each of the expression chunk
	 */ 
	private $patternChunks = array(
			'time' 		=> '(?P<timestamp>(?:[\S]+))',
			'source' 	=> '\s(?P<source>(?:[^\[]+))',
			'process' 	=> '\[(?P<process>[^\]]+)',
			'log_level' => '\]:\sat=(?P<log_level>[\S]+)',
			'method' 	=> '\smethod=(?P<method>[\S]+)',
			'path' 		=> '\spath=(?P<path>[\S]+)',
			'host' 		=> '\shost=(?P<host>[\S]+)',
			'fwd' 		=> '\sfwd=(?P<fwd>[\S]+)',
			'dyno' 		=> '\sdyno=(?P<dyno>[\S]+)',
			'connect' 	=> '\sconnect=(?P<connect>[^m]+)ms',
			'service' 	=> '\sservice=(?P<service>[^m]+)ms',
			'status' 	=> '\sstatus=(?P<status>[\S]+)',
			'bytes' 	=> '\sbytes=(?P<bytes>[\S]+)'
		);

	/**
	 * Dynamically create log format regular expression from the pattern chunks
	 */ 
	private $logFormatRegex = '';

	/**
	 * Array containing the data contained by each of the log line 
	 */ 
	private $logData = array();

	/**
	 * Parser class constructor
	 * 
	 * @param string $logStr Single log line/string from the log file
	 */ 
	public function __construct( $logStr = '' )
	{
		$this->generateLogFormatRegex();
		if ( $logStr ) {
			$this->parseLogString( $logStr );
		}
	}

	/**
	 * Generates the log format regular expression and caches it inside the class
	 *
	 * @return string Regular expression that can be used to parse the log string
	 */ 
	private function generateLogFormatRegex()
	{
		$chunks = array_values( $this->patternChunks );
		$this->logFormatRegex = '/^' . implode('', $chunks) . '$/';
		return $this->logFormatRegex;
	}

	/**
	 * Parses the log string or line
	 * 
	 * @param string $logStr Log string that is to be parsed
	 *
	 * @return boolean True or False depending upon the success or failure of the parsing
	 */ 
	public function parseLogString( $logStr )
	{
		// Parse the log and populate the results in logData
		$parseResult = preg_match( $this->logFormatRegex, $logStr, $this->logData );
		return $parseResult === 1;
	}

	/**
	 * Get some specific log value generated after parsing the log line
	 * 
	 * @param string $str Log value that required
	 *
	 * @return mixed False if the value wasn't found or value otherwise
	 */ 
	public function getLogValue( $str )
	{
		if ( $this->logData[ $str ] ) {
			return $this->logData[ $str ];
		} 
		return false;
	}
}
