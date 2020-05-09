# SELECT Syntax

**Single column**	
```
$query = new Query();
$query->select('column')
```

**Result**
``SELECT column``


**Multiple columns**	
```
$query->select(['column1','column2'])
```

**Result**
``SELECT column1, column2``

**Rename columns**	
```
$query->select(['column1'=>'total','column2'])
```

**Result**
``SELECT column1 AS total, column2``

## FROM clause
**Single table**
```
$query->select('column1')->from('table')
```

**Result**
```
SELECT column1 FROM table
```

**Multiple tables and optional alias**
```
$query->select(['table1.column1','b.name'])->from(['table1','table2'=>'b')
```

**Result**
```
SELECT table1.column1, b.name FROM table1, table2 AS b
```

## WHERE clause

The conditions are better detailed in link [conditions section](CONDITIONS.md)

**Simple condition**
```
$query->select('column1')
    ->from('table')
    ->where(['age[>]'=>18])
```
**Result**
```
SELECT column1 FROM table WHERE (age > 18)
```

**with logical operator**
```
$query->select('column1')
    ->from('table')
    ->where([
        'plan[in]'=>['gold','platinum'],
        'active[is]'=>true
    ])
```
**Result**
```
SELECT column1 FROM table 
    WHERE 
    (plan IN ('gold','platinum'))
    AND 
    (active IS TRUE)
```
