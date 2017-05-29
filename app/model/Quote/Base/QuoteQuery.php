<?php

namespace Quote\Base;

use \Exception;
use \PDO;
use Propel\Runtime\Propel;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Propel\Runtime\ActiveQuery\ModelJoin;
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Exception\PropelException;
use Quote\Quote as ChildQuote;
use Quote\QuoteQuery as ChildQuoteQuery;
use Quote\Map\QuoteTableMap;

/**
 * Base class that represents a query for the 'quote' table.
 *
 *
 *
 * @method     ChildQuoteQuery orderById($order = Criteria::ASC) Order by the id column
 * @method     ChildQuoteQuery orderByTitle($order = Criteria::ASC) Order by the title column
 * @method     ChildQuoteQuery orderByQuote($order = Criteria::ASC) Order by the quote column
 * @method     ChildQuoteQuery orderByPublished($order = Criteria::ASC) Order by the published column
 *
 * @method     ChildQuoteQuery groupById() Group by the id column
 * @method     ChildQuoteQuery groupByTitle() Group by the title column
 * @method     ChildQuoteQuery groupByQuote() Group by the quote column
 * @method     ChildQuoteQuery groupByPublished() Group by the published column
 *
 * @method     ChildQuoteQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method     ChildQuoteQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method     ChildQuoteQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method     ChildQuoteQuery leftJoinWith($relation) Adds a LEFT JOIN clause and with to the query
 * @method     ChildQuoteQuery rightJoinWith($relation) Adds a RIGHT JOIN clause and with to the query
 * @method     ChildQuoteQuery innerJoinWith($relation) Adds a INNER JOIN clause and with to the query
 *
 * @method     ChildQuoteQuery leftJoinQuoteTag($relationAlias = null) Adds a LEFT JOIN clause to the query using the QuoteTag relation
 * @method     ChildQuoteQuery rightJoinQuoteTag($relationAlias = null) Adds a RIGHT JOIN clause to the query using the QuoteTag relation
 * @method     ChildQuoteQuery innerJoinQuoteTag($relationAlias = null) Adds a INNER JOIN clause to the query using the QuoteTag relation
 *
 * @method     ChildQuoteQuery joinWithQuoteTag($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the QuoteTag relation
 *
 * @method     ChildQuoteQuery leftJoinWithQuoteTag() Adds a LEFT JOIN clause and with to the query using the QuoteTag relation
 * @method     ChildQuoteQuery rightJoinWithQuoteTag() Adds a RIGHT JOIN clause and with to the query using the QuoteTag relation
 * @method     ChildQuoteQuery innerJoinWithQuoteTag() Adds a INNER JOIN clause and with to the query using the QuoteTag relation
 *
 * @method     \Quote\QuoteTagQuery endUse() Finalizes a secondary criteria and merges it with its primary Criteria
 *
 * @method     ChildQuote findOne(ConnectionInterface $con = null) Return the first ChildQuote matching the query
 * @method     ChildQuote findOneOrCreate(ConnectionInterface $con = null) Return the first ChildQuote matching the query, or a new ChildQuote object populated from the query conditions when no match is found
 *
 * @method     ChildQuote findOneById(int $id) Return the first ChildQuote filtered by the id column
 * @method     ChildQuote findOneByTitle(string $title) Return the first ChildQuote filtered by the title column
 * @method     ChildQuote findOneByQuote(string $quote) Return the first ChildQuote filtered by the quote column
 * @method     ChildQuote findOneByPublished(string $published) Return the first ChildQuote filtered by the published column *

 * @method     ChildQuote requirePk($key, ConnectionInterface $con = null) Return the ChildQuote by primary key and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildQuote requireOne(ConnectionInterface $con = null) Return the first ChildQuote matching the query and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildQuote requireOneById(int $id) Return the first ChildQuote filtered by the id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildQuote requireOneByTitle(string $title) Return the first ChildQuote filtered by the title column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildQuote requireOneByQuote(string $quote) Return the first ChildQuote filtered by the quote column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildQuote requireOneByPublished(string $published) Return the first ChildQuote filtered by the published column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildQuote[]|ObjectCollection find(ConnectionInterface $con = null) Return ChildQuote objects based on current ModelCriteria
 * @method     ChildQuote[]|ObjectCollection findById(int $id) Return ChildQuote objects filtered by the id column
 * @method     ChildQuote[]|ObjectCollection findByTitle(string $title) Return ChildQuote objects filtered by the title column
 * @method     ChildQuote[]|ObjectCollection findByQuote(string $quote) Return ChildQuote objects filtered by the quote column
 * @method     ChildQuote[]|ObjectCollection findByPublished(string $published) Return ChildQuote objects filtered by the published column
 * @method     ChildQuote[]|\Propel\Runtime\Util\PropelModelPager paginate($page = 1, $maxPerPage = 10, ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 *
 */
abstract class QuoteQuery extends ModelCriteria
{
    protected $entityNotFoundExceptionClass = '\\Propel\\Runtime\\Exception\\EntityNotFoundException';

    /**
     * Initializes internal state of \Quote\Base\QuoteQuery object.
     *
     * @param     string $dbName The database name
     * @param     string $modelName The phpName of a model, e.g. 'Book'
     * @param     string $modelAlias The alias for the model in this query, e.g. 'b'
     */
    public function __construct($dbName = 'default', $modelName = '\\Quote\\Quote', $modelAlias = null)
    {
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new ChildQuoteQuery object.
     *
     * @param     string $modelAlias The alias of a model in the query
     * @param     Criteria $criteria Optional Criteria to build the query from
     *
     * @return ChildQuoteQuery
     */
    public static function create($modelAlias = null, Criteria $criteria = null)
    {
        if ($criteria instanceof ChildQuoteQuery) {
            return $criteria;
        }
        $query = new ChildQuoteQuery();
        if (null !== $modelAlias) {
            $query->setModelAlias($modelAlias);
        }
        if ($criteria instanceof Criteria) {
            $query->mergeWith($criteria);
        }

        return $query;
    }

    /**
     * Find object by primary key.
     * Propel uses the instance pool to skip the database if the object exists.
     * Go fast if the query is untouched.
     *
     * <code>
     * $obj  = $c->findPk(12, $con);
     * </code>
     *
     * @param mixed $key Primary key to use for the query
     * @param ConnectionInterface $con an optional connection object
     *
     * @return ChildQuote|array|mixed the result, formatted by the current formatter
     */
    public function findPk($key, ConnectionInterface $con = null)
    {
        if ($key === null) {
            return null;
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getReadConnection(QuoteTableMap::DATABASE_NAME);
        }

        $this->basePreSelect($con);

        if (
            $this->formatter || $this->modelAlias || $this->with || $this->select
            || $this->selectColumns || $this->asColumns || $this->selectModifiers
            || $this->map || $this->having || $this->joins
        ) {
            return $this->findPkComplex($key, $con);
        }

        if ((null !== ($obj = QuoteTableMap::getInstanceFromPool(null === $key || is_scalar($key) || is_callable([$key, '__toString']) ? (string) $key : $key)))) {
            // the object is already in the instance pool
            return $obj;
        }

        return $this->findPkSimple($key, $con);
    }

    /**
     * Find object by primary key using raw SQL to go fast.
     * Bypass doSelect() and the object formatter by using generated code.
     *
     * @param     mixed $key Primary key to use for the query
     * @param     ConnectionInterface $con A connection object
     *
     * @throws \Propel\Runtime\Exception\PropelException
     *
     * @return ChildQuote A model object, or null if the key is not found
     */
    protected function findPkSimple($key, ConnectionInterface $con)
    {
        $sql = 'SELECT id, title, quote, published FROM quote WHERE id = :p0';
        try {
            $stmt = $con->prepare($sql);
            $stmt->bindValue(':p0', $key, PDO::PARAM_INT);
            $stmt->execute();
        } catch (Exception $e) {
            Propel::log($e->getMessage(), Propel::LOG_ERR);
            throw new PropelException(sprintf('Unable to execute SELECT statement [%s]', $sql), 0, $e);
        }
        $obj = null;
        if ($row = $stmt->fetch(\PDO::FETCH_NUM)) {
            /** @var ChildQuote $obj */
            $obj = new ChildQuote();
            $obj->hydrate($row);
            QuoteTableMap::addInstanceToPool($obj, null === $key || is_scalar($key) || is_callable([$key, '__toString']) ? (string) $key : $key);
        }
        $stmt->closeCursor();

        return $obj;
    }

    /**
     * Find object by primary key.
     *
     * @param     mixed $key Primary key to use for the query
     * @param     ConnectionInterface $con A connection object
     *
     * @return ChildQuote|array|mixed the result, formatted by the current formatter
     */
    protected function findPkComplex($key, ConnectionInterface $con)
    {
        // As the query uses a PK condition, no limit(1) is necessary.
        $criteria = $this->isKeepQuery() ? clone $this : $this;
        $dataFetcher = $criteria
            ->filterByPrimaryKey($key)
            ->doSelect($con);

        return $criteria->getFormatter()->init($criteria)->formatOne($dataFetcher);
    }

    /**
     * Find objects by primary key
     * <code>
     * $objs = $c->findPks(array(12, 56, 832), $con);
     * </code>
     * @param     array $keys Primary keys to use for the query
     * @param     ConnectionInterface $con an optional connection object
     *
     * @return ObjectCollection|array|mixed the list of results, formatted by the current formatter
     */
    public function findPks($keys, ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getReadConnection($this->getDbName());
        }
        $this->basePreSelect($con);
        $criteria = $this->isKeepQuery() ? clone $this : $this;
        $dataFetcher = $criteria
            ->filterByPrimaryKeys($keys)
            ->doSelect($con);

        return $criteria->getFormatter()->init($criteria)->format($dataFetcher);
    }

    /**
     * Filter the query by primary key
     *
     * @param     mixed $key Primary key to use for the query
     *
     * @return $this|ChildQuoteQuery The current query, for fluid interface
     */
    public function filterByPrimaryKey($key)
    {

        return $this->addUsingAlias(QuoteTableMap::COL_ID, $key, Criteria::EQUAL);
    }

    /**
     * Filter the query by a list of primary keys
     *
     * @param     array $keys The list of primary key to use for the query
     *
     * @return $this|ChildQuoteQuery The current query, for fluid interface
     */
    public function filterByPrimaryKeys($keys)
    {

        return $this->addUsingAlias(QuoteTableMap::COL_ID, $keys, Criteria::IN);
    }

    /**
     * Filter the query on the id column
     *
     * Example usage:
     * <code>
     * $query->filterById(1234); // WHERE id = 1234
     * $query->filterById(array(12, 34)); // WHERE id IN (12, 34)
     * $query->filterById(array('min' => 12)); // WHERE id > 12
     * </code>
     *
     * @param     mixed $id The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildQuoteQuery The current query, for fluid interface
     */
    public function filterById($id = null, $comparison = null)
    {
        if (is_array($id)) {
            $useMinMax = false;
            if (isset($id['min'])) {
                $this->addUsingAlias(QuoteTableMap::COL_ID, $id['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($id['max'])) {
                $this->addUsingAlias(QuoteTableMap::COL_ID, $id['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(QuoteTableMap::COL_ID, $id, $comparison);
    }

    /**
     * Filter the query on the title column
     *
     * Example usage:
     * <code>
     * $query->filterByTitle('fooValue');   // WHERE title = 'fooValue'
     * $query->filterByTitle('%fooValue%'); // WHERE title LIKE '%fooValue%'
     * </code>
     *
     * @param     string $title The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildQuoteQuery The current query, for fluid interface
     */
    public function filterByTitle($title = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($title)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $title)) {
                $title = str_replace('*', '%', $title);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(QuoteTableMap::COL_TITLE, $title, $comparison);
    }

    /**
     * Filter the query on the quote column
     *
     * Example usage:
     * <code>
     * $query->filterByQuote('fooValue');   // WHERE quote = 'fooValue'
     * $query->filterByQuote('%fooValue%'); // WHERE quote LIKE '%fooValue%'
     * </code>
     *
     * @param     string $quote The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildQuoteQuery The current query, for fluid interface
     */
    public function filterByQuote($quote = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($quote)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $quote)) {
                $quote = str_replace('*', '%', $quote);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(QuoteTableMap::COL_QUOTE, $quote, $comparison);
    }

    /**
     * Filter the query on the published column
     *
     * Example usage:
     * <code>
     * $query->filterByPublished('2011-03-14'); // WHERE published = '2011-03-14'
     * $query->filterByPublished('now'); // WHERE published = '2011-03-14'
     * $query->filterByPublished(array('max' => 'yesterday')); // WHERE published > '2011-03-13'
     * </code>
     *
     * @param     mixed $published The value to use as filter.
     *              Values can be integers (unix timestamps), DateTime objects, or strings.
     *              Empty strings are treated as NULL.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildQuoteQuery The current query, for fluid interface
     */
    public function filterByPublished($published = null, $comparison = null)
    {
        if (is_array($published)) {
            $useMinMax = false;
            if (isset($published['min'])) {
                $this->addUsingAlias(QuoteTableMap::COL_PUBLISHED, $published['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($published['max'])) {
                $this->addUsingAlias(QuoteTableMap::COL_PUBLISHED, $published['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(QuoteTableMap::COL_PUBLISHED, $published, $comparison);
    }

    /**
     * Filter the query by a related \Quote\QuoteTag object
     *
     * @param \Quote\QuoteTag|ObjectCollection $quoteTag the related object to use as filter
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildQuoteQuery The current query, for fluid interface
     */
    public function filterByQuoteTag($quoteTag, $comparison = null)
    {
        if ($quoteTag instanceof \Quote\QuoteTag) {
            return $this
                ->addUsingAlias(QuoteTableMap::COL_ID, $quoteTag->getQuoteId(), $comparison);
        } elseif ($quoteTag instanceof ObjectCollection) {
            return $this
                ->useQuoteTagQuery()
                ->filterByPrimaryKeys($quoteTag->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByQuoteTag() only accepts arguments of type \Quote\QuoteTag or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the QuoteTag relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this|ChildQuoteQuery The current query, for fluid interface
     */
    public function joinQuoteTag($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('QuoteTag');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'QuoteTag');
        }

        return $this;
    }

    /**
     * Use the QuoteTag relation QuoteTag object
     *
     * @see useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \Quote\QuoteTagQuery A secondary query class using the current class as primary query
     */
    public function useQuoteTagQuery($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinQuoteTag($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'QuoteTag', '\Quote\QuoteTagQuery');
    }

    /**
     * Filter the query by a related Tag object
     * using the quote_tag table as cross reference
     *
     * @param Tag $tag the related object to use as filter
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildQuoteQuery The current query, for fluid interface
     */
    public function filterByTag($tag, $comparison = Criteria::EQUAL)
    {
        return $this
            ->useQuoteTagQuery()
            ->filterByTag($tag, $comparison)
            ->endUse();
    }

    /**
     * Exclude object from result
     *
     * @param   ChildQuote $quote Object to remove from the list of results
     *
     * @return $this|ChildQuoteQuery The current query, for fluid interface
     */
    public function prune($quote = null)
    {
        if ($quote) {
            $this->addUsingAlias(QuoteTableMap::COL_ID, $quote->getId(), Criteria::NOT_EQUAL);
        }

        return $this;
    }

    /**
     * Deletes all rows from the quote table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public function doDeleteAll(ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(QuoteTableMap::DATABASE_NAME);
        }

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con) {
            $affectedRows = 0; // initialize var to track total num of affected rows
            $affectedRows += parent::doDeleteAll($con);
            // Because this db requires some delete cascade/set null emulation, we have to
            // clear the cached instance *after* the emulation has happened (since
            // instances get re-added by the select statement contained therein).
            QuoteTableMap::clearInstancePool();
            QuoteTableMap::clearRelatedInstancePool();

            return $affectedRows;
        });
    }

    /**
     * Performs a DELETE on the database based on the current ModelCriteria
     *
     * @param ConnectionInterface $con the connection to use
     * @return int             The number of affected rows (if supported by underlying database driver).  This includes CASCADE-related rows
     *                         if supported by native driver or if emulated using Propel.
     * @throws PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     */
    public function delete(ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(QuoteTableMap::DATABASE_NAME);
        }

        $criteria = $this;

        // Set the correct dbName
        $criteria->setDbName(QuoteTableMap::DATABASE_NAME);

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con, $criteria) {
            $affectedRows = 0; // initialize var to track total num of affected rows

            QuoteTableMap::removeInstanceFromPool($criteria);

            $affectedRows += ModelCriteria::delete($con);
            QuoteTableMap::clearRelatedInstancePool();

            return $affectedRows;
        });
    }

} // QuoteQuery
