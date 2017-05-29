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
use Quote\QuoteTag as ChildQuoteTag;
use Quote\QuoteTagQuery as ChildQuoteTagQuery;
use Quote\Map\QuoteTagTableMap;

/**
 * Base class that represents a query for the 'quote_tag' table.
 *
 *
 *
 * @method     ChildQuoteTagQuery orderByTagId($order = Criteria::ASC) Order by the tag_id column
 * @method     ChildQuoteTagQuery orderByQuoteId($order = Criteria::ASC) Order by the quote_id column
 *
 * @method     ChildQuoteTagQuery groupByTagId() Group by the tag_id column
 * @method     ChildQuoteTagQuery groupByQuoteId() Group by the quote_id column
 *
 * @method     ChildQuoteTagQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method     ChildQuoteTagQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method     ChildQuoteTagQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method     ChildQuoteTagQuery leftJoinWith($relation) Adds a LEFT JOIN clause and with to the query
 * @method     ChildQuoteTagQuery rightJoinWith($relation) Adds a RIGHT JOIN clause and with to the query
 * @method     ChildQuoteTagQuery innerJoinWith($relation) Adds a INNER JOIN clause and with to the query
 *
 * @method     ChildQuoteTagQuery leftJoinTag($relationAlias = null) Adds a LEFT JOIN clause to the query using the Tag relation
 * @method     ChildQuoteTagQuery rightJoinTag($relationAlias = null) Adds a RIGHT JOIN clause to the query using the Tag relation
 * @method     ChildQuoteTagQuery innerJoinTag($relationAlias = null) Adds a INNER JOIN clause to the query using the Tag relation
 *
 * @method     ChildQuoteTagQuery joinWithTag($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the Tag relation
 *
 * @method     ChildQuoteTagQuery leftJoinWithTag() Adds a LEFT JOIN clause and with to the query using the Tag relation
 * @method     ChildQuoteTagQuery rightJoinWithTag() Adds a RIGHT JOIN clause and with to the query using the Tag relation
 * @method     ChildQuoteTagQuery innerJoinWithTag() Adds a INNER JOIN clause and with to the query using the Tag relation
 *
 * @method     ChildQuoteTagQuery leftJoinQuote($relationAlias = null) Adds a LEFT JOIN clause to the query using the Quote relation
 * @method     ChildQuoteTagQuery rightJoinQuote($relationAlias = null) Adds a RIGHT JOIN clause to the query using the Quote relation
 * @method     ChildQuoteTagQuery innerJoinQuote($relationAlias = null) Adds a INNER JOIN clause to the query using the Quote relation
 *
 * @method     ChildQuoteTagQuery joinWithQuote($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the Quote relation
 *
 * @method     ChildQuoteTagQuery leftJoinWithQuote() Adds a LEFT JOIN clause and with to the query using the Quote relation
 * @method     ChildQuoteTagQuery rightJoinWithQuote() Adds a RIGHT JOIN clause and with to the query using the Quote relation
 * @method     ChildQuoteTagQuery innerJoinWithQuote() Adds a INNER JOIN clause and with to the query using the Quote relation
 *
 * @method     \Quote\TagQuery|\Quote\QuoteQuery endUse() Finalizes a secondary criteria and merges it with its primary Criteria
 *
 * @method     ChildQuoteTag findOne(ConnectionInterface $con = null) Return the first ChildQuoteTag matching the query
 * @method     ChildQuoteTag findOneOrCreate(ConnectionInterface $con = null) Return the first ChildQuoteTag matching the query, or a new ChildQuoteTag object populated from the query conditions when no match is found
 *
 * @method     ChildQuoteTag findOneByTagId(int $tag_id) Return the first ChildQuoteTag filtered by the tag_id column
 * @method     ChildQuoteTag findOneByQuoteId(int $quote_id) Return the first ChildQuoteTag filtered by the quote_id column *

 * @method     ChildQuoteTag requirePk($key, ConnectionInterface $con = null) Return the ChildQuoteTag by primary key and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildQuoteTag requireOne(ConnectionInterface $con = null) Return the first ChildQuoteTag matching the query and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildQuoteTag requireOneByTagId(int $tag_id) Return the first ChildQuoteTag filtered by the tag_id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildQuoteTag requireOneByQuoteId(int $quote_id) Return the first ChildQuoteTag filtered by the quote_id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildQuoteTag[]|ObjectCollection find(ConnectionInterface $con = null) Return ChildQuoteTag objects based on current ModelCriteria
 * @method     ChildQuoteTag[]|ObjectCollection findByTagId(int $tag_id) Return ChildQuoteTag objects filtered by the tag_id column
 * @method     ChildQuoteTag[]|ObjectCollection findByQuoteId(int $quote_id) Return ChildQuoteTag objects filtered by the quote_id column
 * @method     ChildQuoteTag[]|\Propel\Runtime\Util\PropelModelPager paginate($page = 1, $maxPerPage = 10, ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 *
 */
abstract class QuoteTagQuery extends ModelCriteria
{
    protected $entityNotFoundExceptionClass = '\\Propel\\Runtime\\Exception\\EntityNotFoundException';

    /**
     * Initializes internal state of \Quote\Base\QuoteTagQuery object.
     *
     * @param     string $dbName The database name
     * @param     string $modelName The phpName of a model, e.g. 'Book'
     * @param     string $modelAlias The alias for the model in this query, e.g. 'b'
     */
    public function __construct($dbName = 'default', $modelName = '\\Quote\\QuoteTag', $modelAlias = null)
    {
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new ChildQuoteTagQuery object.
     *
     * @param     string $modelAlias The alias of a model in the query
     * @param     Criteria $criteria Optional Criteria to build the query from
     *
     * @return ChildQuoteTagQuery
     */
    public static function create($modelAlias = null, Criteria $criteria = null)
    {
        if ($criteria instanceof ChildQuoteTagQuery) {
            return $criteria;
        }
        $query = new ChildQuoteTagQuery();
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
     * $obj = $c->findPk(array(12, 34), $con);
     * </code>
     *
     * @param array[$tag_id, $quote_id] $key Primary key to use for the query
     * @param ConnectionInterface $con an optional connection object
     *
     * @return ChildQuoteTag|array|mixed the result, formatted by the current formatter
     */
    public function findPk($key, ConnectionInterface $con = null)
    {
        if ($key === null) {
            return null;
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getReadConnection(QuoteTagTableMap::DATABASE_NAME);
        }

        $this->basePreSelect($con);

        if (
            $this->formatter || $this->modelAlias || $this->with || $this->select
            || $this->selectColumns || $this->asColumns || $this->selectModifiers
            || $this->map || $this->having || $this->joins
        ) {
            return $this->findPkComplex($key, $con);
        }

        if ((null !== ($obj = QuoteTagTableMap::getInstanceFromPool(serialize([(null === $key[0] || is_scalar($key[0]) || is_callable([$key[0], '__toString']) ? (string) $key[0] : $key[0]), (null === $key[1] || is_scalar($key[1]) || is_callable([$key[1], '__toString']) ? (string) $key[1] : $key[1])]))))) {
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
     * @return ChildQuoteTag A model object, or null if the key is not found
     */
    protected function findPkSimple($key, ConnectionInterface $con)
    {
        $sql = 'SELECT tag_id, quote_id FROM quote_tag WHERE tag_id = :p0 AND quote_id = :p1';
        try {
            $stmt = $con->prepare($sql);
            $stmt->bindValue(':p0', $key[0], PDO::PARAM_INT);
            $stmt->bindValue(':p1', $key[1], PDO::PARAM_INT);
            $stmt->execute();
        } catch (Exception $e) {
            Propel::log($e->getMessage(), Propel::LOG_ERR);
            throw new PropelException(sprintf('Unable to execute SELECT statement [%s]', $sql), 0, $e);
        }
        $obj = null;
        if ($row = $stmt->fetch(\PDO::FETCH_NUM)) {
            /** @var ChildQuoteTag $obj */
            $obj = new ChildQuoteTag();
            $obj->hydrate($row);
            QuoteTagTableMap::addInstanceToPool($obj, serialize([(null === $key[0] || is_scalar($key[0]) || is_callable([$key[0], '__toString']) ? (string) $key[0] : $key[0]), (null === $key[1] || is_scalar($key[1]) || is_callable([$key[1], '__toString']) ? (string) $key[1] : $key[1])]));
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
     * @return ChildQuoteTag|array|mixed the result, formatted by the current formatter
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
     * $objs = $c->findPks(array(array(12, 56), array(832, 123), array(123, 456)), $con);
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
     * @return $this|ChildQuoteTagQuery The current query, for fluid interface
     */
    public function filterByPrimaryKey($key)
    {
        $this->addUsingAlias(QuoteTagTableMap::COL_TAG_ID, $key[0], Criteria::EQUAL);
        $this->addUsingAlias(QuoteTagTableMap::COL_QUOTE_ID, $key[1], Criteria::EQUAL);

        return $this;
    }

    /**
     * Filter the query by a list of primary keys
     *
     * @param     array $keys The list of primary key to use for the query
     *
     * @return $this|ChildQuoteTagQuery The current query, for fluid interface
     */
    public function filterByPrimaryKeys($keys)
    {
        if (empty($keys)) {
            return $this->add(null, '1<>1', Criteria::CUSTOM);
        }
        foreach ($keys as $key) {
            $cton0 = $this->getNewCriterion(QuoteTagTableMap::COL_TAG_ID, $key[0], Criteria::EQUAL);
            $cton1 = $this->getNewCriterion(QuoteTagTableMap::COL_QUOTE_ID, $key[1], Criteria::EQUAL);
            $cton0->addAnd($cton1);
            $this->addOr($cton0);
        }

        return $this;
    }

    /**
     * Filter the query on the tag_id column
     *
     * Example usage:
     * <code>
     * $query->filterByTagId(1234); // WHERE tag_id = 1234
     * $query->filterByTagId(array(12, 34)); // WHERE tag_id IN (12, 34)
     * $query->filterByTagId(array('min' => 12)); // WHERE tag_id > 12
     * </code>
     *
     * @see       filterByTag()
     *
     * @param     mixed $tagId The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildQuoteTagQuery The current query, for fluid interface
     */
    public function filterByTagId($tagId = null, $comparison = null)
    {
        if (is_array($tagId)) {
            $useMinMax = false;
            if (isset($tagId['min'])) {
                $this->addUsingAlias(QuoteTagTableMap::COL_TAG_ID, $tagId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($tagId['max'])) {
                $this->addUsingAlias(QuoteTagTableMap::COL_TAG_ID, $tagId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(QuoteTagTableMap::COL_TAG_ID, $tagId, $comparison);
    }

    /**
     * Filter the query on the quote_id column
     *
     * Example usage:
     * <code>
     * $query->filterByQuoteId(1234); // WHERE quote_id = 1234
     * $query->filterByQuoteId(array(12, 34)); // WHERE quote_id IN (12, 34)
     * $query->filterByQuoteId(array('min' => 12)); // WHERE quote_id > 12
     * </code>
     *
     * @see       filterByQuote()
     *
     * @param     mixed $quoteId The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildQuoteTagQuery The current query, for fluid interface
     */
    public function filterByQuoteId($quoteId = null, $comparison = null)
    {
        if (is_array($quoteId)) {
            $useMinMax = false;
            if (isset($quoteId['min'])) {
                $this->addUsingAlias(QuoteTagTableMap::COL_QUOTE_ID, $quoteId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($quoteId['max'])) {
                $this->addUsingAlias(QuoteTagTableMap::COL_QUOTE_ID, $quoteId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(QuoteTagTableMap::COL_QUOTE_ID, $quoteId, $comparison);
    }

    /**
     * Filter the query by a related \Quote\Tag object
     *
     * @param \Quote\Tag|ObjectCollection $tag The related object(s) to use as filter
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @throws \Propel\Runtime\Exception\PropelException
     *
     * @return ChildQuoteTagQuery The current query, for fluid interface
     */
    public function filterByTag($tag, $comparison = null)
    {
        if ($tag instanceof \Quote\Tag) {
            return $this
                ->addUsingAlias(QuoteTagTableMap::COL_TAG_ID, $tag->getId(), $comparison);
        } elseif ($tag instanceof ObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(QuoteTagTableMap::COL_TAG_ID, $tag->toKeyValue('PrimaryKey', 'Id'), $comparison);
        } else {
            throw new PropelException('filterByTag() only accepts arguments of type \Quote\Tag or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the Tag relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this|ChildQuoteTagQuery The current query, for fluid interface
     */
    public function joinTag($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('Tag');

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
            $this->addJoinObject($join, 'Tag');
        }

        return $this;
    }

    /**
     * Use the Tag relation Tag object
     *
     * @see useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \Quote\TagQuery A secondary query class using the current class as primary query
     */
    public function useTagQuery($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinTag($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'Tag', '\Quote\TagQuery');
    }

    /**
     * Filter the query by a related \Quote\Quote object
     *
     * @param \Quote\Quote|ObjectCollection $quote The related object(s) to use as filter
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @throws \Propel\Runtime\Exception\PropelException
     *
     * @return ChildQuoteTagQuery The current query, for fluid interface
     */
    public function filterByQuote($quote, $comparison = null)
    {
        if ($quote instanceof \Quote\Quote) {
            return $this
                ->addUsingAlias(QuoteTagTableMap::COL_QUOTE_ID, $quote->getId(), $comparison);
        } elseif ($quote instanceof ObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(QuoteTagTableMap::COL_QUOTE_ID, $quote->toKeyValue('PrimaryKey', 'Id'), $comparison);
        } else {
            throw new PropelException('filterByQuote() only accepts arguments of type \Quote\Quote or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the Quote relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this|ChildQuoteTagQuery The current query, for fluid interface
     */
    public function joinQuote($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('Quote');

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
            $this->addJoinObject($join, 'Quote');
        }

        return $this;
    }

    /**
     * Use the Quote relation Quote object
     *
     * @see useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \Quote\QuoteQuery A secondary query class using the current class as primary query
     */
    public function useQuoteQuery($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinQuote($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'Quote', '\Quote\QuoteQuery');
    }

    /**
     * Exclude object from result
     *
     * @param   ChildQuoteTag $quoteTag Object to remove from the list of results
     *
     * @return $this|ChildQuoteTagQuery The current query, for fluid interface
     */
    public function prune($quoteTag = null)
    {
        if ($quoteTag) {
            $this->addCond('pruneCond0', $this->getAliasedColName(QuoteTagTableMap::COL_TAG_ID), $quoteTag->getTagId(), Criteria::NOT_EQUAL);
            $this->addCond('pruneCond1', $this->getAliasedColName(QuoteTagTableMap::COL_QUOTE_ID), $quoteTag->getQuoteId(), Criteria::NOT_EQUAL);
            $this->combine(array('pruneCond0', 'pruneCond1'), Criteria::LOGICAL_OR);
        }

        return $this;
    }

    /**
     * Deletes all rows from the quote_tag table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public function doDeleteAll(ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(QuoteTagTableMap::DATABASE_NAME);
        }

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con) {
            $affectedRows = 0; // initialize var to track total num of affected rows
            $affectedRows += parent::doDeleteAll($con);
            // Because this db requires some delete cascade/set null emulation, we have to
            // clear the cached instance *after* the emulation has happened (since
            // instances get re-added by the select statement contained therein).
            QuoteTagTableMap::clearInstancePool();
            QuoteTagTableMap::clearRelatedInstancePool();

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
            $con = Propel::getServiceContainer()->getWriteConnection(QuoteTagTableMap::DATABASE_NAME);
        }

        $criteria = $this;

        // Set the correct dbName
        $criteria->setDbName(QuoteTagTableMap::DATABASE_NAME);

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con, $criteria) {
            $affectedRows = 0; // initialize var to track total num of affected rows

            QuoteTagTableMap::removeInstanceFromPool($criteria);

            $affectedRows += ModelCriteria::delete($con);
            QuoteTagTableMap::clearRelatedInstancePool();

            return $affectedRows;
        });
    }

} // QuoteTagQuery
