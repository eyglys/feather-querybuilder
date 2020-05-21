<?php
declare(strict_types=1);
namespace Feather\Builder;

use \Feather\Query\Query;
use \Feather\Exceptions\BuilderException;

class QueryBuilder extends BaseBuilder {

    protected $builderOwner;

    public function build(Query $query) {
        if ($query->isBuildable()) {
            $build = '';
            $build .= $this->buildSelectColumns($query->select);
            $build .= $this->buildFrom($query->from);
            $build .= $this->buildWhere($query->where);

            $build .= $this->buildOrderBy($query->orderBy);
            
            $build .= $this->buildPages($query->limit,$query->offset);


            return $build;
        } else throw new BuilderException();
    }
    
    protected function buildSelectColumns(array $columns) {        
        return $this->driver->select(
            $this->transformAliasable($columns)
        );
    }

    protected function buildFrom(array $tables) {
        if (!is_null($tables)) {
            return ' '.$this->driver->from(
                $this->transformAliasable($tables)
            );
        } else return '';
    }

    protected function buildPages(?int $limit, ?int $offset) {
        if (!is_null($limit) || !is_null($offset)) return ' '.$this->driver->setPage($limit,$offset);
        else return '';
    }

    protected function buildWhere(?array $conditions) {
        if (!is_null($conditions)) {
            $conditionsBuilder = new ConditionsBuilder([
                'driver'=>$this->driver,
                'paramBuilder'=>$this->paramBuilder,
                'builderOwner'=>$this->builderOwner
            ]);

            return ' '.$this->driver->where(
                $conditionsBuilder->build($conditions)
            );
        } else return '';
    }

    protected function buildOrderBy(?array $columns) {
        if (!is_null($columns)) {
            return ' '.$this->driver->orderBy($columns);
        } else return '';
    }

    protected function transformAliasable($list) {
        $newList = [];
        foreach ($list as $key => $value) {
            if (is_string($key)) {
                $newList[] = $this->driver->alias($key,$value);
            } else $newList[] = $value;
        }
        return $newList;
    }
}