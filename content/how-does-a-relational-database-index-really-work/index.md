---
title: How does a relational database index really work?
date: "2024-02-24T06:22:00.000Z"
---

A common question in software engineering interviews is _how can you speed up a slow query?_ In this post I want to explain one answer to this question, which is: to add an index to the table the query is performed on.

## What is an index in a relational database?

An index in a relational database is a key-value mapping for one or many columns where the key is the data in the column and the value is the primary ID of the row that contains the data. 
A primary index also exists in every database table so querying by ID is always fast. A custom index is a reverse-lookup to that primary index.

## How does an index speed up database queries?
An index tells the database which rows contain specific values, without having to scan each row individually.

A common way to understand it is the index of a phone book.
If I was trying to find someone with the last name "Martin" in a phone book, I would skip to the back pages to the index, find names starting with M and start looking from the referenced page number.

A database does the same lookup with an index.

Let's take a look at a more concrete example. Suppose we create a new table:

```sql
CREATE TABLE `users` (
  `id`          bigint          NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name`        varchar(255)    NOT NULL,
  `status`      int             NOT NULL,
  `joined_on`   datetime        NOT NULL
);
```

A query to find the users where status is `1` would result in a full table scan.

```sql
explain select * from users where status = 1;
> ... | Extra
        Using where
```

If we add an index to the status column because we know it's a common access pattern for our application:
```sql
ALTER TABLE users ADD INDEX status(status);
```

When we run the explain again, we can see it 
```sql
explain select * from users where status = 1;
> ... | key     | .. | Extra
        status  | .. | Using index
```

When the database performs the operations for this query it will use the index instead of scanning every row, which starts making a big difference when there are millions of rows to scan.

## Handling complex queries with a composite index

Continuing with this example, let's assume we have another access pattern which is to find all the users with a specific status who joined after a certain date ordered from most to least recent.

```sql
explain select * from users where status = 1 and joined_on >= '2024-02-24' order by joined_on desc;
> ... | key     | .. | Extra
        status  | .. | Using where; Using filesort
```

Without an index on `joined_on` column the query could still benefit from the index we added on status. It may not be the best performance, however, with the addition of the `joined_on` filter and the sort, which would result in a filesort operation which could make overall performance worse.

We could go ahead and create an index for `joined_on` but the database may still choose the `status` index and perform a filesort.

What would have better performance is a composite index with both `status` and `joined_on`.


```sql
ALTER TABLE users ADD INDEX status_joined_on(status, joined_on);
```

After adding the index, this is what the explain looks like:

```sql
explain select * from users where status = 1 and joined_on >= '2024-02-24' order by joined_on desc;
> ... | key                 | .. | Extra
          status_joined_on  | .. | Using index condition; Backward index scan
```

An index can be stored in either ascending or descending order depending on the definition. We see `Backward index scan` because we need the reverse order (descending) to sort results for the query above.

If we were to create the index where `joined_on` column is sorted in descending order  we would see the `Backward index scan` removed:
```sql
status_joined_on(status, joined_on DESC)
```

Now we can run the explain again:
```sql
explain select * from users where status = 1 and joined_on >= '2024-02-24' order by joined_on desc;
```

This is an ideal index for this type of query.

## Summary

We explored creating indexes on relational databases and evaluated performance at each step. What did we learn along the way?

- An index in a relational database is a key-value mapping for one or many columns to tell the database which rows contain what values without having to scan each row.

- Indexes can speed up query performance at the cost of write performance, though the former typically outweighs the latter.

- For complex queries, it's possible to create a multi-column index. Ordering the columns is an important factor in its performance.

- A descending index can help with searches for most recent data.