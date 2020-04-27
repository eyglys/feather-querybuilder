<?php
declare(strict_types=1);
namespace Feather\Builder;

use \Feather\Query\Query;

class QueryBuilder extends BaseBuilder {
    public function build(Query $query) {
        $conditions = $query->getWhere();
            
        $conditionsBuilder = new ConditionsBuilder([
            'driver'=>$this->driver,
            'paramBuilder'=>$this->paramBuilder,
        ]);

        $conditions = $conditionsBuilder->build($conditions);

        return $conditions;
    }
}