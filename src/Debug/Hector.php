<?php
/**
 * This file is part of Berlioz framework.
 *
 * @license   https://opensource.org/licenses/MIT MIT License
 * @copyright 2021 Ronan GIRON
 * @author    Ronan GIRON <https://github.com/ElGigi>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code, to the root.
 */

declare(strict_types=1);

namespace Berlioz\Package\Hector\Debug;

use Berlioz\Core\Core;
use Berlioz\Core\CoreAwareInterface;
use Berlioz\Core\CoreAwareTrait;
use Berlioz\Core\Debug\AbstractSection;
use Berlioz\Core\Debug\Activity;
use Berlioz\Core\Debug\Section;
use Berlioz\Core\Exception\BerliozException;
use Countable;
use Hector\Connection\Log\LogEntry;
use Hector\Orm\Orm;

class Hector extends AbstractSection implements Section, Countable, CoreAwareInterface
{
    use CoreAwareTrait;

    /** @var Orm|null Hector ORM */
    private ?Orm $orm = null;
    /** @var array Queries */
    private array $queries = [];

    /**
     * Hector constructor.
     *
     * @param Core $core
     */
    public function __construct(Core $core)
    {
        $this->setCore($core);
    }

    /**
     * Set hector.
     *
     * @param Orm $hector
     *
     * @return static
     */
    public function setHector(Orm $hector): Hector
    {
        $this->orm = $hector;

        return $this;
    }

    /////////////////////////
    /// SECTION INTERFACE ///
    /////////////////////////

    /**
     * @inheritDoc
     */
    public function __toString(): string
    {
        return var_export($this, true);
    }

    /**
     * @inheritDoc
     * @throws BerliozException
     */
    public function saveReport()
    {
        $debug = $this->getCore()->getDebug();

        if (null !== $this->orm) {
            $this->queries = $this->orm->getConnection()->getLogger()->getLogs();

            // Add queries to the timeline
            foreach ($this->queries as $query) {
                $activity =
                    (new Activity('Query', $this->getSectionName()))
                        ->start($query->getStart())
                        ->end($query->getEnd())
                        ->setDetail($query->getStatement())
                        ->setResult($query->getTrace());
                $debug->getTimeLine()->addActivity($activity);
            }
        }
    }

    /**
     * Get section name.
     *
     * @return string
     */
    public function getSectionName(): string
    {
        return 'Hector ORM';
    }

    /**
     * Get template name.
     */
    public function getTemplateName(): string
    {
        return '@Berlioz-HectorPackage/Twig/Debug/hector.html.twig';
    }

    /**
     * @inheritDoc
     */
    public function serialize()
    {
        return serialize(['queries' => $this->queries]);
    }

    /**
     * @inheritDoc
     */
    public function unserialize($serialized)
    {
        $unserialized = unserialize($serialized);

        $this->queries = $unserialized['queries'] ?? [];
    }

    ///////////////////////////
    /// COUNTABLE INTERFACE ///
    ///////////////////////////

    /**
     * @inheritDoc
     */
    public function count()
    {
        return count($this->getQueries());
    }

    ////////////////////
    /// USER DEFINED ///
    ////////////////////

    /**
     * Get queries.
     *
     * @return array
     */
    public function getQueries(): array
    {
        return $this->queries ?? [];
    }

    /**
     * Get total duration.
     *
     * @return float
     */
    public function getDuration(): float
    {
        if (empty($this->queries)) {
            return 0;
        }

        $duration = array_reduce($this->queries, fn($time, LogEntry $query) => $time + $query->getDuration());

        return floatval($duration);
    }
}