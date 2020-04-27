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
            $build .= ' '.$this->buildFrom($query->from);
            if (!empty($query->where)) {
                $build .= ' '.$this->buildWhere($query->where);
            }

            return $build;
        } else throw new BuilderException();
    }
    
    protected function buildSelectColumns(array $columns) {        
        return $this->driver->select(
            $this->transformAliasable($columns)
        );
    }

    protected function buildFrom(array $tables) {
        return $this->driver->from(
            $this->transformAliasable($tables)
        );
    }

    protected function buildWhere(array $conditions) {
        $conditionsBuilder = new ConditionsBuilder([
            'driver'=>$this->driver,
            'paramBuilder'=>$this->paramBuilder,
            'builderOwner'=>$this->builderOwner
        ]);

        return $this->driver->where(
            $conditionsBuilder->build($conditions)
        );
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