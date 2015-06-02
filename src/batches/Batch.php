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
   * @var string
   */
  protected $runFile;
  
  /**
   * @var string
   */
  protected $month = '*';
  
  /**
   * @var string
   */
  protected $day = '*';
  
  /**
   * @var string
   */
  protected $hour = '*';
  
  /**
   * @var string
   */
  protected $minute = '*';

  /**
   * @var \DateTime
   */
  protected $executionDate;  
  
  /**
   * @var \DateTime
   */
  protected $lastRun;  
  
  /**
   * @param string $name
   * @param string $runFilesDir
   */
  function __construct($name, $runFilesDir)
  {
    $this->name = $name;
    if(empty($runFilesDir) || !is_string($runFilesDir))
    {
      throw new \Exception("The run files directory " . $runFilesDir . " has to be set correctly");
    }
    
    if(!is_dir($runFilesDir))
    {
      throw new \Exception("The run files directory " . $runFilesDir . " does not exist");
    }
    $this->runFile = $runFilesDir . '/batch_' . $name;    
    if(file_exists($this->runFile))
    {
      /* @var $tDate \DateTime */
      $tDate = new \DateTime();
      $this->lastRun = $tDate->setTimestamp(filemtime($this->runFile));
    }
    $this->adjustExecutionDate();
  }
  
  /**
   * @param string $month
   * @param string $day
   * @param string $hour
   * @param string $minute
   */
  public function executionPlan($month = '*', $day = '*', $hour = '*', $minute = '*')
  {
    $this->month = $month;
    $this->day = $day;
    $this->hour = $hour;
    $this->minute = $minute;
    $this->adjustExecutionDate();
  }
  
  /**
   * @return void
   */
  public function run()
  {
    if($this->lastRun instanceof \DateTime && $this->lastRun >= $this->executionDate)
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
    $jobs = $this->jobs;
    $this->jobs = array_filter($jobs, function(Job $existingJob) use ($job){
      return $existingJob !== $job;
    });
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
  
  /**
   * @return \DateTime
   */
  public function getLastRun()
  {
    return $this->lastRun;
  }
    
  /**
   * @param \DateTime $lastRun
   */
  public function setLastRun(\DateTime $lastRun)
  {
    $this->lastRun = $lastRun;
  }

  protected function adjustExecutionDate()
  {
    $tDate = date('Y') . '-' . (($this->month !== '*') ? sprintf("%02d", $this->month) : date('m')) . '-' . (($this->day !== '*') ? sprintf("%02d", $this->day) : date('d')) . ' ' . (($this->hour !== '*') ? sprintf("%02d", $this->hour) : date('H')) . ':' . (($this->minute !== '*') ? sprintf("%02d", $this->minute) : date('i')) . ':00';
    $this->executionDate = new \DateTime($tDate);
  }
}
