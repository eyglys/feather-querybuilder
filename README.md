# php-feather-orm
ORM PHP library

  

## Condition syntax

  

### Relational operators

  

#### Syntax

| Syntax | Result |
|---|------ |
| ``['column[OPERATOR]'=>$value]`` | ``(column OPERATOR :value)`` |
| ``['column1[OPERATOR]column2']`` | ``(column1 OPERATOR column2)`` |

  

#### Examples
| Syntax | Result |
|---|------ |
| ``['column[=]'=>$value]`` | ``(column = :value)`` |
| ``['column[>]'=>$value]`` | ``(column > :value)`` |
| ``['column[<]'=>$value]`` | ``(column <> :value)`` |
| ``['column[>=]'=>$value]`` | ``(column >= :value)`` |
| ``['column[<=]'=>$value]`` | ``(column <= :value)`` |
| ``['column[!=]'=>$value]`` | ``(column != :value)`` |

  

#### IS
| Syntax | Result |
|---|------ |
| ``['column[is]'=>$value]``  | ``(column IS $value)`` |
| ``['column[is]'=>true]``  |  ``(column IS TRUE)`` |
| ``['column[is]'=>null]``  |  ``(column IS NULL)`` |
| ``['column[is!]'=>null]``  |  ``(column IS NOT NULL)`` |

  

#### Like
| Syntax | Result |
|---|------ |
| ``['column[%like%]'=>$value]`` |  ``(column LIKE '%:value%')`` |
| ``['column[%like]'=>$value]``  |  ``(column LIKE '%:value')`` |
| ``['column[like%]'=>$value]``  |  ``(column LIKE ':value%')`` |
| ``['column[like]'=>$value]``  |  ``(column LIKE '%string%to%search%')`` *replace spaces by search character* |

  

#### IN
| Syntax | Result |
|---|------ |
| ``['column[in]'=>[1,2,3]]`` |  ``(column IN (1,2,3))`` |
| ``['column[!in]'=>[1,2,3]]`` |  ``(column NOT IN (1,2,3))`` |
| ``['column[in]'=>$queryObject]`` |  ``(column IN (SELECT ...))`` |

  

#### Between
| Syntax | Result |
|---|------ |
| ``['column[btw]'=>[$value1,$value2]]``  | ``(column BETWEEN :value1 AND :value2)`` |
| ``['column[!btw]'=>[$value1,$value2]]``  |  ``(column NOT BETWEEN :value1 AND :value2)`` |
| ``['column[btw]'=>[$queryObject1,$queryObject1]]`` |  ``(column BETWEEN (SELECT ...) AND (SELECT ...))`` |
| ``['column1[btw]column2[and]column3']`` |  ``(column1 BETWEEN column2 AND column3)`` |

  
  

### Logical Operators

**Syntax**	
```
[
	'and'=>[
		['column1[=]'=>$value1],
		['column2[>]'=>$value2]
	]
]
```
**Result**
``WHERE (column1 = :value1) AND (column2 = :value2)``

**Syntax**	
```
[
	'or'=>[
		['column1[!=]'=>$value1],
		['column2[like]'=>$value2]
	]
]
```
**Result**
``WHERE (column1 != :value1) AND (column2 LIKE '%:value2%')``

**Syntax**	
```
[
	'or'=>[
		['vip[is]'=>true],
		'and'=>[
			['column1[!=]'=>$value1],
			['column2[like]'=>$value2],
		]
	]
]
```
**Result**
```
WHERE (vip IS TRUE) OR (
	(column1 != :value1) AND (column2 LIKE '%:value2%')
)
```