<?php
namespace drieschel\batches;
/**
 * @author Immanuel Klinkenberg <klinkenberg@speicher-werk.de>
 */
class Batch
{
  /**
   * @var Job[]
   */
  protected $jobs = array();
 
  /**
   * @var string
   */
  protected $name;
  
  /**
   * @var \DateInterval
   */
  protected $interval;
  
  /**
   * @var string
   */
  protected $runFile;
  
  /**
   * @var \DateTime
   */
  protected $lastRun;  
  
  /**
   * @param string $name
   * @param \DateInterval $interval
   * @param string $runFilesDir
   */
  function __construct($name, \DateInterval $interval, $runFilesDir)
  {
    $this->name = $name;
    $this->interval = $interval;
    if(empty($runFilesDir) || !is_string($runFilesDir))
    {
      throw new \Exception("The run files directory " . $runFilesDir . " has to be set correctly");
    }
    
    if(!is_dir($runFilesDir))
    {
      throw new \Exception("The run files directory " . $runFilesDir . " does not exist");
    }
    $this->runFile = $runFilesDir . '/batch_' . md5($name);    
    if(!file_exists($this->runFile))
    {
      /* @var $tDate \DateTime */
      $tDate = new \DateTime();
      $this->lastRun = $tDate->sub($interval);
    }
    else 
    {
      $tDate = new \DateTime();
      $this->lastRun = $tDate->setTimestamp(filemtime($this->runFile));
    }
  }
  
  /**
   * @return void
   */
  public function run()
  {
    if($this->lastRun->add($this->interval) > new \DateTime())
    {
      return;
    }
    
    $startTime = time();
    foreach($this->jobs as $job)
    {
      $job->execute();
    }
    $endTime = time();
    $result = array('executed_at' => date('d.m.Y H:i:s'), 'status' => 'success', 'job_amount' => count($this->jobs), 'runtime' => $endTime - $startTime);    
    file_put_contents($this->runFile, json_encode($result));
  }
  
  /**
   * @param \drieschel\batches\Job $job
   */
  public function addJob(Job $job)
  {
    if(!$this->containsJob($job))
    {
      $this->jobs[] = $job;
    }
  }
  
  /**
   * @param \drieschel\batches\Job $job
   */
  public function removeJob(Job $job)
  {
    $this->jobs = array_diff($this->jobs, array($job));
  }
  
  /**
   * @param \drieschel\batches\Job $job
   * @return boolean
   */
  public function containsJob(Job $job)
  {
    foreach($this->jobs as $existingJob)
    {
      if($job === $existingJob)
      {
        return true;
      }
    }
    return false;
  }
  
  /**
   * @return Job[]
   */
  public function getJobs()
  {
    return $this->jobs;
  }
}
