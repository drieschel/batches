<?php
namespace drieschel\batches;
/**
 * @author Immanuel Klinkenberg <klinkenberg@speicher-werk.de>
 */
interface Job
{
  public function execute();
}
