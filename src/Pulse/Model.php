<?php
/**
 * The Pulse model.
 *
 * @copyright  Copyright (C) 2013 New Life in IT Pty Ltd. All rights reserved.
 * @license    http://opensource.org/licenses/MIT  MIT
 */

namespace Pulse;

use Joomla\Github\Github;
use Joomla\Registry\Registry;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Joomla\Model\AbstractModel;
use Joomla\Date\Date;

/**
 * The Pulse model class.
 *
 * Required state settings:
 *
 * * user - The owner/user of the Github repository to scan.
 * * repo - The name of the Github repository.
 *
 * @since  1.0
 */
class Model extends AbstractModel implements LoggerAwareInterface
{
	/**
	 * A Github connector.
	 *
	 * @var    Github
	 * @since  1.0
	 */
	private $github;

	/**
	 * A logger.
	 *
	 * @var    LoggerInterface
	 * @since  1.0
	 */
	private $logger;

	/**
	 * An array of the tags in the repository
	 *
	 * @var    array
	 * @since  1.0
	 */
	private $tags;

	/**
	 * Class constructor.
	 *
	 * Overrides the parent class to directly inject the Github connector.
	 *
	 * @param   Github    $github  A Github connector object.
	 * @param   Registry  $state   The model state.
	 *
	 * @since   1.0
	 */
	public function __construct(Github $github, Registry $state)
	{
		parent::__construct($state);

		$this->github = $github;

		$this->since = new \DateTime;
		$this->since->setTimezone(new \DateTimeZone('GMT'));
		$this->since->modify('-' . $this->state->get('months', 3) . ' months');
		$this->since->modify('first day of this month');
		$this->since->modify('midnight');
	}

	/**
	 * Get metrics for issues in a Github repository.
	 *
	 * @param   boolean  $cache  If true, cached data from a previous run will be used.
	 *
	 * @return  \StdClass[]  An array of data nodes.
	 *
	 * @since   1.0
	 */
	public function getIssues($cache = true)
	{
		$now = new \DateTime;
		$now->setTimezone(new \DateTimeZone('GMT'));
		$since = $this->since->format('Y-m-d\TH:i:s\Z');

		$this->logger->debug(sprintf('Analysing issues between %s and %s', $this->since->format('Y-m-d\TH:i:s\Z'), $now->format('Y-m-d\TH:i:s\Z')));

		if ($cache)
		{
			$this->loadIssuesStatic();
		}
		else
		{
			$this->openIssues = $this->loadIssues();
			$this->save($this->openIssues, 'openIssues');

			$this->closedIssues = $this->loadIssues('closed', 'updated', new Date($since));
			$this->save($this->closedIssues, 'closedIssues');
		}

		$openIssues = 0;
		$openPulls = 0;
		$result = array();

		foreach ($this->openIssues as $issue)
		{
			$isPull = ($issue->pull_request->html_url !== null);
			$createdAt = new \DateTime($issue->created_at);
			$created = $createdAt->format('Y-m');
			$skipped = ($issue->created_at < $since);

			$diff = $createdAt->diff($now);
			$days = $diff->format('%d') + $diff->format('%h') / 24 + $diff->format('%i') / 1440;

			if (!$skipped && !isset($result[$created]))
			{
				$result[$created] = $this->getDataNode($created);
			}

			// Check for pull request.
			if ($isPull)
			{
				$openPulls++;

				if (!$skipped)
				{
					$result[$created]->newPulls++;
				}
			}
			else
			{
				$openIssues++;

				if (!$skipped)
				{
					$result[$created]->newIssues++;
				}
			}
		}

		foreach ($this->closedIssues as $issue)
		{
			$isPull = ($issue->pull_request->html_url !== null);
			$createdAt = new \DateTime($issue->created_at);
			$created = $createdAt->format('Y-m');
			$closedAt = new \DateTime($issue->closed_at);
			$closed = $closedAt->format('Y-m');

			$diff = $createdAt->diff($closedAt);
			$days = $diff->format('%d') + $diff->format('%h') / 24 + $diff->format('%i') / 1440;

			if ($issue->created_at >= $since)
			{
				if (!isset($result[$created]))
				{
					$result[$created] = $this->getDataNode($created);
				}

				if ($isPull)
				{
					$result[$created]->newPulls++;
				}
				else
				{
					$result[$created]->newIssues++;
				}
			}

			if ($issue->closed_at >= $since)
			{
				if (!isset($result[$closed]))
				{
					$result[$closed] = $this->getDataNode($closed);
				}

				if ($isPull)
				{
					$result[$closed]->closedPulls->add($days);
				}
				else
				{
					$result[$closed]->closedIssues->add($days);
				}
			}
		}

		// Compute carried forward.
		$this->logger->debug('Open issues: ' . $openIssues);

		$carriedIssues = $openIssues;
		$carriedPulls = $openPulls;

		foreach ($result as &$month)
		{
			$month->openIssues = $carriedIssues;
			$month->openPulls = $carriedPulls;
			$month->carriedIssues = $carriedIssues + $month->closedIssues->count() - $month->newIssues;
			$month->carriedPulls = $carriedPulls + $month->closedPulls->count() - $month->newPulls;

			$carriedIssues = $month->carriedIssues;
			$carriedPulls = $month->carriedPulls;
		}

		return array_values($result);
	}

	/**
	 * Sets a logger instance in the object.
	 *
	 * @param   LoggerInterface  $logger  A logger.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function setLogger(LoggerInterface $logger)
	{
		$this->logger = $logger;
	}

	/**
	 * Get a data node for a result set.
	 *
	 * @param   string  $date  The date stamp for the node.
	 *
	 * @return  \StdClass
	 *
	 * @since   1.0
	 */
	private function getDataNode($date)
	{
		return (object) array(
			'newIssues' => 0,
			'newPulls' => 0,
			'closedIssues' => new Series,
			'closedPulls' => new Series,
			'date' => $date,
		);
	}

	/**
	 * Save captured JSON data to a file.
	 *
	 * @param   string  $data      JSON data.
	 * @param   string  $fileName  The file name (without the .json extension).
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	private function save($data, $fileName)
	{
		file_put_contents(__DIR__ . '/../../etc/' . $fileName . '.json', json_encode($data));
	}

	/**
	 * Load issues from the static source instead of polling the server.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	private function loadIssuesStatic()
	{
		$since = $this->since->format('Y-m-d\TH:i:s\Z');

		$this->openIssues = json_decode(file_get_contents(realpath(__DIR__ . '/../../etc/openIssues.json')));

		$this->closedIssues = json_decode(file_get_contents(realpath(__DIR__ . '/../../etc/closedIssues.json')));

		$this->logger->debug(count($this->openIssues) . ' open issues.');
		$this->logger->debug(count($this->closedIssues) . ' closed issues.');
	}

	/**
	 * Get a page of pull requests from the repository.
	 *
	 * @param   string  $state  The issue state: open (default) | closed.
	 * @param   string  $sort   The sort field.
	 * @param   string  $since  An optional date from which to include reports for analytics.
	 *
	 * @return  array
	 *
	 * @since   1.0
	 */
	private function loadIssues($state = 'open', $sort = 'created', Date $since = null)
	{
		$user = $this->state->get('user');
		$repo = $this->state->get('repo');

		// Set the maximum number of pages (and runaway failsafe).
		$cutoff = 100;
		$page = 1;
		$result = array();

		while ($cutoff--)
		{
			$this->logger->info(sprintf('Getting %s issues page #%02d.', $state, $page));
			$this->logger->info(str_pad('', 40, '-'));

			$issues = $this->github->issues
				->getListByRepository($user, $repo, null, $state, null, null, null, $sort, 'desc', $since, $page++, 100);

			$this->logger->info(sprintf('Got %s issues.', count($issues)));

			// Check if we've gone past the last page.
			if (empty($issues))
			{
				break;
			}

			$result = array_merge($result, $issues);
		}

		return $result;
	}
}
