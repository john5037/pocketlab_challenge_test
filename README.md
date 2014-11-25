## Developer Challenge Test

The objective of this challenge is to parse a log file and do some analysis on it.

## Used technology

* PHP

## How to use

You can simply use library with call LogReader class like below for the given url.

Given Url :

GET /api/users/{user_id}/count_pending_messages 

GET /api/users/{user_id}/get_messages 

GET /api/users/{user_id}/get_friends_progress 

GET /api/users/{user_id}/get_friends_score 

POST /api/users/{user_id} 

GET /api/users/{user_id} 


// call logreader class

$reader = new LogReader( $filePath );

// Get data for each of the required URLs (in string format)

$pendingMsgData = $reader->getUrlData('count_pending_messages', 'get', true);

$getMsgData = $reader->getUrlData('get_messages', 'get', true);

$friendProgData = $reader->getUrlData('get_friends_progress', 'get', true);

$friendScData = $reader->getUrlData('get_friends_score', 'get', true);

$usersGetData = $reader->getUrlData('users', 'get', true);

$usersPostData = $reader->getUrlData('users', 'post', true);


## Running through CLI

You can run this script through CLI like below.

- go to this test root directory
- than run command like below
  
  php  test.php  sample.log  (third arguement is to give filepath of the log)

- so you can see output in a text format for each url.

## Runing through browser

- I have already added sample.log file into test root directory. so please check it, if its not there and please put it there before run this script on browser.
- than you can directly run test.php and see output into browser.

