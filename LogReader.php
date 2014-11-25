<?php 


/**
 * LogReader class to read the log file
 * 
 * @author Kamal Nayak
 */

/**
 *
 * Include parser For Read the Log File and Extract the value From it.
 *
 */

include_once('Parser.php');

class LogReader
{
	/**
	 * Paths related data that will be needed
	 */ 
	private $paths_data = array(
		'count_pending_messages' => array(
			'pretty_url' => '/api/users/{user_id}/count_pending_messages',
			'regex' => '/^\/api\/users\/(?:[^\/]+)\/count_pending_messages\/?$/',
			'get_response_times' => array(),
			'post_response_times' => array(),
			'dynos' => array()
		),
		
		'get_messages' => array(
			'pretty_url' => '/api/users/{user_id}/get_messages',
			'regex' => '/^\/api\/users\/(?:[^\/]+)\/get_messages$/',
			'get_response_times' => array(),
			'post_response_times' => array(),
			'dynos' => array()
		),
		
		'get_friends_progress' => array(
			'pretty_url' => ' /api/users/{user_id}/get_friends_progress',
			'regex' => '/^\/api\/users\/(?:[^\/]+)\/get_friends_progress$/',
			'get_response_times' => array(),
			'post_response_times' => array(),
			'dynos' => array()
		),
		
		'get_friends_score' => array(
			'pretty_url' => '/api/users/{user_id}/get_friends_score',
			'regex' => '/^\/api\/users\/(?:[^\/]+)\/get_friends_score$/',
			'get_response_times' => array(),
			'post_response_times' => array(),
			'dynos' => array()
		),
		'users' => array(
			'pretty_url' => '/api/users/{user_id}',
			'regex' => '/^\/api\/users\/(?:[^\/]+)$/',
			'get_response_times' => array(),
			'post_response_times' => array(),
			'dynos' => array()
		)
	);
	
	/**
	 * File handler to read the log file
	 */ 
	private $fileHandle = '';

	/**
	 * Constructor
	 * 
	 * @param string $filePath Path to the log file
	 */ 
	function __construct( $filePath = '' )
	{
		if ( $filePath && file_exists( $filePath )) {
			$this->initiateReadFile( $filePath );
		} else {
			exit('Invalid log file ' . PHP_EOL);
		}
	}

	/**
	 * Initiates the process to read the log file
	 * 
	 * @param string $filePath Path to the file that needs to be read
	 */ 
	public function initiateReadFile( $filePath )
	{
		$this->fileHandle = fopen( $filePath, "r" );
		if ( !$this->fileHandle ) {
			throw new Exception("Error opening the log file. Make sure the file exists and is readable.", 1);
		}
		$this->parser = new Parser();
		$this->processLogFile();
	}

	/**
	 * Processes the log file and populates the data inside the class
	 */ 
	public function processLogFile()
	{
		while (($line = fgets($this->fileHandle)) !== false) {
			
			$this->parser->parseLogString( $line );
			$path = $this->parser->getLogValue('path');
			$method = strtolower( $this->parser->getLogValue('method') );
			$dyno = $this->parser->getLogValue('dyno');
			$connect_time = floatval( $this->parser->getLogValue('connect') );
			$service_time = floatval( $this->parser->getLogValue('service') );

			foreach ($this->paths_data as $path_name => $content) {
				if ( preg_match( $content['regex'], $path ) ) {
					$this->paths_data[ $path_name ][$method . '_response_times'][] = ( $connect_time  + $service_time );
					if ( isset($this->paths_data[$path_name][ 'dynos' ][ $dyno ] ) ) {
						$this->paths_data[$path_name][ 'dynos' ][ $dyno ]++;
					} else {
						$this->paths_data[$path_name][ 'dynos' ][ $dyno ] = 1;
					}
					break;
				}
			}
		}
	}

	/**
	 * Returns an array containing the pretty URL, number of times the URL was called, mean response time, median response time, mode, method and the most active dyno to the URL
	 * 
	 * @param string $urlTitle Title of the URL that is required
	 * @param string GET or POST
	 * @param boolean $formatted If false, then array containing the required data is returned and a formatted string otherwise
	 *
	 * @return array|string Formatted stats string if $formatted was true, otherwise an associative array with keys of pretty_url, called_count, mean_response_time, median, method, mode, method, most_active_dyno
	 */ 
	public function getUrlData( $urlTitle, $method, $formatted = false )
	{
		$temp_data = array();
		$path_data = $this->paths_data[ $urlTitle ];
		$method = strtolower( $method );
		$temp_data['pretty_url'] = $path_data['pretty_url'];
		$temp_data['called_count'] = count( $path_data[$method . '_response_times'] );
		$divisibleVal = ($temp_data['called_count'] == 0) ? 1 : $temp_data['called_count'];
		$temp_data['mean_response_time'] =  array_sum( $path_data[$method . '_response_times'] ) / $divisibleVal;
		$temp_data['median'] = $this->calculateMedian( $path_data[$method . '_response_times'] );
		$temp_data['mode'] = $this->calculateMode( $path_data[$method . '_response_times'] );
		$temp_data['method'] = $method;
		$activeDyno = array_keys($path_data['dynos'], max($path_data['dynos'])); 
		$temp_data['most_active_dyno'] = $activeDyno[0];
		// If the formatted string is demanded and not the array
		if ( $formatted ) {
			return '[' . $temp_data['method'] . $temp_data['pretty_url'] . '] was called ' . $temp_data['called_count'] . ' times with the details of response times as follows. Mean was ' . $temp_data['mean_response_time'] . '. Median was ' . $temp_data['median'] . '. Mode was ' . $temp_data['mode'] . '. And the dyno that responded the most was ' . $temp_data['most_active_dyno'];
		}
		return $temp_data;
	}

	/**
	 * Calculates the mode of the passed array. Is being used to calculate the mode of the response times
	 *
	 * @param array $array Array whose mode is to be calculated
	 *
	 * @return int   Integer value representing the mode of the array
	 */ 
	private function calculateMode( $array )
	{
		if (!empty($array)) {
			// array_count_values works only on integers and strings
			// convert the float values to ints
			array_walk($array, function( &$value ) {
				$value = intval( $value );
			});
			$values = array_count_values( $array );
			$mode = array_search(max($values), $values);
			return $mode;
		} else {
		    return 0;
		}
	}

	/**
	 * Calculates and returns the median of the array
	 *
	 * @param array $array Array whose median is to be calculated
	 *
	 * @return mixed Median of the array
	 */ 
	private function calculateMedian( $array ) 
	{
		$count = count( $array );
		if ($count > 0) {
			$middle_index = floor( $count / 2 );
			sort( $array, SORT_NUMERIC );
			$median = $array[ $middle_index ]; // assume an odd # of items
			// Handle the even case by averaging the middle 2 items
			if ($count % 2 == 0) {
				$median = ( $median + $array[ $middle_index - 1 ] ) / 2;
			}
			return $median;
		} else {
		    return 0;
		}
	}
}
