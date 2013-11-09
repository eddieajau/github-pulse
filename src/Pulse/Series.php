<?php
/**
 * The Series data class.
 *
 * @copyright  Copyright (C) 2013 New Life in IT Pty Ltd. All rights reserved.
 * @license    http://opensource.org/licenses/MIT  MIT
 */

namespace Pulse;

/**
 * Perform simple statistics on a series stream.
 *
 * Based on https://gist.github.com/kmpm/1211724
 *
 * @since  1.0
 */
class Series
{
	/**
	 * The number of data points.
	 *
	 * @var    integer
	 * @since  1.0
	 */
	protected $count = 0;

	/**
	 * The minimum value in the series.
	 *
	 * @var    double
	 * @since  1.0
	 */
	protected $min;

	/**
	 * The maximum value in the series.
	 *
	 * @var    double
	 * @since  1.0
	 */
	protected $max;

	/**
	 * Intermediate calculation value.
	 *
	 * @var    double
	 * @since  1.0
	 */
	private $oldMean;

	/**
	 * Intermediate calculation value.
	 *
	 * @var    double
	 * @since  1.0
	 */
	private $newMean;

	/**
	 * Intermediate calculation value.
	 *
	 * @var    double
	 * @since  1.0
	 */
	private $oldSum = 0.0;

	/**
	 * Intermediate calculation value.
	 *
	 * @var    double
	 * @since  1.0
	 */
	private $newSum;

	/**
	 * Add a data point to the series.
	 *
	 * @param   double  $x  The data value.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function add($x)
	{
		$this->count++;

		if ($this->count == 1)
		{
			$this->oldMean = $x;
			$this->newMean = $x;
			$this->min = $x;
			$this->max = $x;
		}
		else
		{
			$this->newMean = $this->oldMean + ($x - $this->oldMean) / $this->count;
			$this->newSum = $this->oldSum + ($x - $this->oldMean) * ($x - $this->newMean);

			$this->oldMean = $this->newMean;
			$this->oldSum = $this->newSum;

			$this->min = min($this->min, $x);
			$this->max = max($this->max, $x);
		}
	}

	/**
	 * Gets the number of data points in the series.
	 *
	 * @return  number
	 *
	 * @since   1.0
	 */
	public function count()
	{
		return $this->count;
	}

	/**
	 * Computes the mean of data points in the series.
	 *
	 * @return  number
	 *
	 * @since   1.0
	 */
	public function mean()
	{
		return ($this->count > 0) ? $this->newMean : 0.0;
	}

	/**
	 * Gets the minimum values in the series.
	 *
	 * @return  number
	 *
	 * @since   1.0
	 */
	public function min()
	{
		return $this->min;
	}

	/**
	 * Gets the maximum values in the series.
	 *
	 * @return  number
	 *
	 * @since   1.0
	 */
	public function max()
	{
		return $this->max;
	}

	/**
	 * Gets the variance of the series.
	 *
	 * @return  number
	 *
	 * @since   1.0
	 */
	public function variance()
	{
		return  ($this->count > 1) ? ($this->newSum / ($this->count - 1)) : 0.0;
	}

	/**
	 * Gets the standard deviation of the series.
	 *
	 * @return  number
	 *
	 * @since   1.0
	 */
	public function standardDeviation()
	{
		return sqrt($this->variance());
	}
}
