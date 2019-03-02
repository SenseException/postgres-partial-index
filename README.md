# Postgres: Comparing Normal Index with Partial Index #

This initiallization code was used for the blog post [Increase SQL performance with partial indices](https://php.budgegeria.de/blog/increase-sql-performance-with-partial-indices)

## Install ##

1. Install PHP dependencies with Composer
```bash
composer install
```
2. Create config.xml file by config.xml.dist file and insert config values
3. Create the database with the name from the config.xml
```sql
CREATE DATABASE {config_value}
```
4. Initialize tables and inserts
```bash
php console init
```

1000020 same rows are now in both tables:

 * `foo_normal` (Normal Index)
 * `foo_partial` (Partial Index)

The first 1000000 are `payment_status` = 'complete', the last 20 are on 'pending'. The table with the Partial Index only
includes data where `payment_status` is not 'complete'.

## Results ##

### Normal Index ###
```sql
postgres_test=# explain analyze select * from foo_normal WHERE payment_status = 'pending';

                                                             QUERY PLAN                                                              
-------------------------------------------------------------------------------------------------------------------------------------
 Index Scan using payment_status_normal on foo_normal  (cost=0.42..9.00 rows=33 width=28) (actual time=0.030..0.039 rows=20 loops=1)
   Index Cond: ((payment_status)::text = 'pending'::text)
 Total runtime: 0.064 ms
(3 rows)
```

### Partial Index ###
```sql
postgres_test=# explain analyze select * from foo_partial WHERE payment_status = 'pending';

                                                              QUERY PLAN                                                              
--------------------------------------------------------------------------------------------------------------------------------------
 Index Scan using payment_status_partial on foo_partial  (cost=0.12..4.14 rows=1 width=29) (actual time=0.027..0.037 rows=20 loops=1)
   Index Cond: ((payment_status)::text = 'pending'::text)
 Total runtime: 0.078 ms
(3 rows)
```