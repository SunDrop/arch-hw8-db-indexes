# TASK
1. Create user table with 40M records
2. Check performance in queries:
   1. without indexes
   2. with btree index
   3. with hash index
3. Check insert performance with different value of innodb_flush_log_at_trx_commit variable.

# Setup
## Database structure
```mysql
CREATE TABLE IF NOT EXISTS user_base
(
    id int auto_increment primary key,
    username varchar(20) null,
    birthday datetime null
) ENGINE=InnoDB;
```
```mysql
create table user_test
(
    id             int auto_increment
        primary key,
    username       varchar(20) null,
    birthday       datetime    null,
    birthday_btree datetime    null,
    birthday_hash  datetime    null
) engine = MEMORY;

create index i_birthday_hash
    on user_test (birthday_hash)
    using hash;

create index i_birthday_btree
    on user_test (birthday_btree)
    using btree;
```
## Indexes
```mysql
SHOW INDEXES FROM user_test \G

*************************** 1. row ***************************
Table: user_test
Non_unique: 0
Key_name: PRIMARY
Seq_in_index: 1
Column_name: id
Collation: NULL
Cardinality: 40010536
Sub_part: NULL
Packed: NULL
Null:
Index_type: HASH
Comment:
Index_comment:
Visible: YES
Expression: NULL
*************************** 2. row ***************************
Table: user_test
Non_unique: 1
Key_name: i_birthday_hash
Seq_in_index: 1
Column_name: birthday_hash
Collation: NULL
Cardinality: 20005268
Sub_part: NULL
Packed: NULL
Null: YES
Index_type: HASH
Comment:
Index_comment:
Visible: YES
Expression: NULL
*************************** 3. row ***************************
Table: user_test
Non_unique: 1
Key_name: i_birthday_btree
Seq_in_index: 1
Column_name: birthday_btree
Collation: A
Cardinality: NULL
Sub_part: NULL
Packed: NULL
Null: YES
Index_type: BTREE
Comment:
Index_comment:
Visible: YES
Expression: NULL
```



## System variables (update memory tables limit to 8Gb)
```mysql
SET GLOBAL tmp_table_size = 1024 * 1024 * 1024 * 8;
SET GLOBAL max_heap_table_size = 1024 * 1024 * 1024 * 8;
```

## Insert data from base table
```mysql
INSERT INTO user_test (id, username, birthday, birthday_btree, birthday_hash) 
SELECT id, username, birthday, birthday, birthday FROM user_base;
```

# Report
## HOWTO generate report
```mysql
set profiling=1;
# RUN SQL ... ;
show profiles;
```
## Result
| SQL                                                                                                                   |      clean (sec) | btree (sec) | hash (sec) |
|:----------------------------------------------------------------------------------------------------------------------|-----------------:|------------:|-----------:|
| SELECT SQL_NO_CACHE count(*) FROM user_test WHERE birthday_? = '1921-03-21 17:36:46';                                 | more than 30 min |    0.003453 |   0.006234 |
| SELECT SQL_NO_CACHE count(*) FROM user_test WHERE birthday_? > '1999-04-07 14:34:41';                                 | more than 30 min |  303.299789   | 370.753075 |
| SELECT SQL_NO_CACHE count(*) FROM user_test WHERE birthday_? <> '1999-04-07 14:34:41' LIMIT 100000;                   | more than 30 min |  5.137924   | 1.539777 |
| SELECT SQL_NO_CACHE count(*) FROM user_test WHERE birthday_? BETWEEN '1920-01-30 09:49:41' AND '1921-03-21 17:36:46'; | more than 30 min |  24.128456   | 211.255312 |

## Explains
```mysql
EXPLAIN SELECT SQL_NO_CACHE count(*) FROM user_test WHERE birthday_btree = '1921-03-21 17:36:46' \G
*************************** 1. row ***************************
           id: 1
  select_type: SIMPLE
        table: user_test
   partitions: NULL
         type: ref
possible_keys: i_birthday_btree
          key: i_birthday_btree
      key_len: 6
          ref: const
         rows: 8
     filtered: 100.00
        Extra: NULL
```
```mysql
EXPLAIN SELECT SQL_NO_CACHE count(*) FROM user_test WHERE birthday_hash = '1921-03-21 17:36:46' \G
*************************** 1. row ***************************
           id: 1
  select_type: SIMPLE
        table: user_test
   partitions: NULL
         type: ref
possible_keys: i_birthday_hash
          key: i_birthday_hash
      key_len: 6
          ref: const
         rows: 2
     filtered: 100.00
        Extra: NULL
```
```mysql
EXPLAIN SELECT SQL_NO_CACHE count(*) FROM user_test WHERE birthday_btree > '1999-04-07 14:34:41' \G
*************************** 1. row ***************************
           id: 1
  select_type: SIMPLE
        table: user_test
   partitions: NULL
         type: range
possible_keys: i_birthday_btree
          key: i_birthday_btree
      key_len: 6
          ref: NULL
         rows: 7824929
     filtered: 100.00
        Extra: Using where
```
```mysql
EXPLAIN SELECT SQL_NO_CACHE count(*) FROM user_test WHERE birthday_hash > '1999-04-07 14:34:41' \G
*************************** 1. row ***************************
           id: 1
  select_type: SIMPLE
        table: user_test
   partitions: NULL
         type: ALL
possible_keys: i_birthday_hash
          key: NULL
      key_len: NULL
          ref: NULL
         rows: 40010534
     filtered: 33.33
        Extra: Using where
```
```mysql
EXPLAIN SELECT SQL_NO_CACHE count(*) FROM user_test WHERE birthday_btree BETWEEN '1920-01-30 09:49:41' AND '1921-03-21 17:36:46' \G
*************************** 1. row ***************************
           id: 1
  select_type: SIMPLE
        table: user_test
   partitions: NULL
         type: range
possible_keys: i_birthday_btree
          key: i_birthday_btree
      key_len: 6
          ref: NULL
         rows: 299995
     filtered: 100.00
        Extra: Using where
```
```mysql
EXPLAIN SELECT SQL_NO_CACHE count(*) FROM user_test WHERE birthday_hash BETWEEN '1920-01-30 09:49:41' AND '1921-03-21 17:36:46' \G
*************************** 1. row ***************************
           id: 1
  select_type: SIMPLE
        table: user_test
   partitions: NULL
         type: ALL
possible_keys: i_birthday_hash
          key: NULL
      key_len: NULL
          ref: NULL
         rows: 40010534
     filtered: 11.11
        Extra: Using where
```

## Conclusion
* Hash index provides fast work with = or <> operations
* Hash does not work with ranges, in which case we are scanning virtually the entire table.
* BTree index works like log(N) to find the value in the key, compared to iterating over the entire table (N)

# innodb_flush_log_at_trx_commit
```mysql
SET GLOBAL innodb_flush_log_at_trx_commit=0;
SET GLOBAL innodb_flush_log_at_trx_commit=1;
SET GLOBAL innodb_flush_log_at_trx_commit=2;
```
```my.cnf
# Custom config should go here
innodb_flush_log_at_trx_commit=0
innodb_flush_log_at_trx_commit=1
innodb_flush_log_at_trx_commit=2
```

| Operations per sec (2500 inserts) | innodb_flush_log_at_trx_commit = 0 | innodb_flush_log_at_trx_commit = 1 | innodb_flush_log_at_trx_commit = 2 |
|-----------------------------------|------------------------------------|------------------------------------|------------------------------------|
| 1 user                            | 1.10 secs                          | 1.28 secs                          | 1.23 secs                          |
| 5 users                           | 1.44 secs                          | 2.07 secs                          | 1.36 secs                          |
| 50 users                          | 4.96 secs                          | 5.52 secs                          | 5.56 secs                          |
| 100 users                         | 5.46 secs                          | 5.26 secs                          | 5.28 secs                          |
