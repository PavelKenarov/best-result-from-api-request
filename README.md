# Parse employees list from API

- The challenge was to consume the [API](http://hiring.rewardgateway.net/docs/) and output a list of employees using php. 
- Final result is available on index.php. 
- API raw result can see inside file test.php.

I didn't use any additional frameworks, libraries or modules, just pure php code developed to fix problem avatars inside api list.

* * *

There are too many problems with loading avatars and broken images to start develop a real time parser. Also the API endpoint randomly failing. My solution is to store the result into db and additionally download avatars with cron tasks. In order to display good API results, first they must be cached and processed into database. The process has described in section "Cron jobs"

## Usage

This application using:

-   php 7+, mysql, mysqli, curl
-   html, css(Bootstrap v4) and javascrtipt(jquery)

## Install

Clone the project to root directory. 
Import database structure from file "employees.sql". 
Install all cron tasks which are described in section "Cron jobs"

## Cron jobs

Need to create three cron jobs:

- The first must set up several times per day. This will store entirely api results ( all 1000 rows ):
```shell
0 6,11,18,22 * * * /php_path/php /root_path/cron.php get
```
- Second after him. This will check if avatars can be downloaded ( just checking if the image exists ) but without to download content:
```shell
0 7,12,19,23 * * * /php_path/php /root_path/cron.php canBeDownload
```
- And the last crontab ( will download avatars ) should be turned more often. There is two variants to run the last crontab. The easiest way is to run on every 2 - 5 minutes:
```shell
*/5 * * * * /php_path/php /root_path/cron.php getFromBadHost
```
but if the pictures are too big and slow to download, there is a danger of overloading the server with too many running processes. So let's do something like modern queue. Here we get the process id and store it: 
```shell
php /root_path/cron.php canBeDownload > /dev/null 2>&1 & echo $!;
```
Let me explain how we get process id. This part will reset the output:
```shell
> /dev/null 2>&1 
```
and the last part will return only cleen process id:
```shell
& echo $!;
```
After that need to check if the process exist inside "/proc/" folder ( !file_exists("/proc/{$current_process_id}") ). Here is the whole idea in action:
```php
$current_process_id = $this->getProcess();	
if(empty($current_process_id)){
	$processId = exec("php /root_path/cron.php canBeDownload > /dev/null 2>&1 & echo $!;");
	$this->setProcess($process_id);					
}else{
	if (!file_exists("/proc/{$current_process_id}")) {
		$processId = exec("php /root_path/cron.php canBeDownload > /dev/null 2>&1 & echo $!;");
		$this->setProcess($process_id);	
	}
}
``` 
Now we can start downloading avatar in maximum speed without server overload because the running process will be only one.
```shell
*/1 * * * * /php_path/php /root_path/cron.php processCheckAndGetFromBadHost
```


## Challenges I faced when completing this task:

- Avatar pictures

Major problem are avatars - many of them are not loading or loading process take too much time. There are too many problems with loading avatars and broken images to create a real time parser. For that reason, I think the only solution is keep the result in mysql or some NoSQL db, using cron jobs. After each cron the field "status" has changed and in the end will see only good clean records. The process of avatar extraction can see on section "Cron jobs". 

* * *

- Endpoint failure

To fix API randomly failing, I made a loop on curl request until the results come:
```php
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, self::URL);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_USERPWD, self::USER.":".self::PASS);
	curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 6); 
	curl_setopt($ch, CURLOPT_TIMEOUT, 30);

	for ( $i = 0; $i < $attempts; $i ++ ) {
		
		$response  = curl_exec( $ch );
		$curl_info = curl_getinfo( $ch );

		// error, try again
		if ( curl_errno( $ch ) )
			continue; 

		// only accepting 200 as a status code.
		if ( $curl_info['http_code'] != 200 )
			continue; 

		if ( empty( $response ) )
			continue;

		return json_decode($response);
	} 
```
