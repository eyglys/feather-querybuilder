# SELECT Syntax

**Single column**	
```
select('column')
```

**Result**
``SELECT column``


**Multiple columns**	
```
select(['column1','column2'])
```

**Result**
``SELECT column1, column2``

**Rename columns**	
```
select(['column1'=>'total','column2'])
```

**Result**
``SELECT column1 AS total, column2``