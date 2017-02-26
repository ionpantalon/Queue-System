#! /usr/bin/php
<?php
#https://github.com/chrisboulton/php-resque
require __DIR__ . '/init.php';
#$status = new Resque_Job_Status();
#echo"status ".$status->get()."\n";

$error = "Please type 'enqueue' or 'dequeue' or 'keys' or \n'waiting' or 'running' or 'processed' or 'failed' or \n're-queue' or 'status' or 'queues' or 'workers' or 'flushall' \n";
if(empty($argv[1])){
   die($error);
}
 //Connecting to Redis server on localhost
 $redis = new Redis();
 $redis->connect('127.0.0.1', 6379);
 
if($argv[1] == "flushall")
{
    $redis->flushall();
    echo "All keys were deleted succesfully";

}else if($argv[1] == "keys"){
    $keys = $redis->keys("*");
    print_r($keys);
}else if($argv[1] == "processed"){
    $pr = $redis->get("resque:stat:processed \n");
if($pr=="0" || $pr == ""){
    echo "No jobs were processed yet! \n";
 }else{ 
    get_Status('4');
 }
}else if ($argv[1] == "failed"){
    $list = $redis->lrange("resque:failed", 0, 100);
    echo print_r($list);
    get_Status('3');
}else if($argv[1] == "queues"){
    $mem = $redis->smembers("resque:queues");
    print_r($mem);
}else if($argv[1] == "workers"){
    $wks = $redis->smembers("resque:workers");
    print_r($wks);
}else if ($argv[1] == "all"){
    $all = shell_exec('redis-cli KEYS \* | xargs -n 1 redis-cli dump');
    print($all); 
}else if($argv[1] == "status"){
    $keys = $redis->keys('*resque:job:*');
    
    $n=1;
    foreach($keys as $k){
       echo $n++." ".$redis->get($k)."\n";
    }
}else if($argv[1] == "waiting"){
    get_Status('1');

}else if($argv['1'] == "running"){
    get_Status('2');
}else if($argv['1'] == "requeue"){
    $job = new Resque_Job('BAD_JOB',array('bad_job_q'));
    $job->recreate();
    echo "Job has been requeued";
    $new_status = $job->getStatus('b0d81c4dcb6d848dc645f7997e0e7dbd');
    get_Status($new_status);
}else if ($argv['1'] == "enqueue"){
 $args = array(
        'time' => time(),
        'name' => 'Rob',
        'array' => array(
                'test' => 'testing the php queue',
        ),
 );
 if(!empty($argv[2])){
     $job_id = Resque::enqueue('default', $argv[2], $args, true);
     $keys_list = $redis->hmset('keys_list', array($argv[2]=>$job_id));
     print_r($get_keys_list = $redis->hgetall('keys_list')); 
 }else{
     echo "Type the job name e.g BAD_JOB";
 }
}else if($argv[1] == "dequeue"){
     echo 'Dequeing..';
}
else
{
     echo $error;
}

//get status
function get_Status($stat){
    $redis = new Redis();
    $redis->connect('127.0.0.1', 6379);
    $keys = $redis->keys('*resque:job:*');

    foreach($keys as $k){
       $key_val = $redis->get($k);
       $st = json_decode($key_val, true);

       $run[] = $st['status'];
 }

 $array_total = array_count_values($run);
 print_r($array_total); 

 switch($stat){
	case 1:
		echo "\n".$array_total[$stat]." jobs are waiting.\n";
		break;
	case 2:
		echo "\n".$array_total[$stat]." jobs are running.\n";
		break;
	case 3:
		echo "\n".$array_total[$stat]." jobs have failed.\n";
		break;
	case 4:
		echo "\n".$array_total[$stat]." jobs are processed.\n";
		break;
 }
}

