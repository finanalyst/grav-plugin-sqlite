# v1.5.4
## 22-12-2020
1. [](#enhancement)
   * fixed readme

# v1.5.3
## 18-07-2020
1. [](#bugfix) 
   * Extra security measure added - credit @hughbris

# v1.5.2
## 19-12-2018
1. [](#bugfix)
   * propagate id in shortcode to table,
   * credit to Matt Marsh @marshmn
   * log function at line 16 SqlTableShortcode requires two parameters, not one.
   * credit to @dlannan

# v1.5.1
## 01-08-2018
1. [](#enhancement)
   * A more elaborate example is added to show how to obtain data from a database, change data by  creating a form, then update the row in the database.

# v1.5.0
## 16-06-2018
1. [](#enhancement)
   * Extra security option. Allows for more paranoia
   * When enabled, each page with an [sql-table] shortcode must have explicit header permission
   * This is to prevent shortcode being added in the front end by an editor
1. [](#bugfix)
   * fix file permission from 0666 to 0664

# v1.4.0
## 23-06-2018
1. [](#enhancement)
   * More logging options, allowing for the SELECT, INSERT and UPDATE stanzas (in addition to Errors)
   to be trapped.
   * Append the logged data to the directory `user/data/sqlite` as a 'log.txt' file
   * This allows an Administrator to view the data from within the Admin panel using the DataManager plugin.
   * The default configuration for the placement of the sqlite3 database is now `user/data/sqlite`

# v1.3.0
## 11-06-2018
1. [](#enhancement)
   * Add `ignore` field in `sql-insert` and `sql-update`
   * Add `error_logging` configuration option, so that SQL errors are written to hard drive

# v1.2.3
## 07-07-2018
1. [](#enhancement)
   * Add shortcode dependency to blueprints

# v1.2.2
## 05-05-2018
1. [](#bugfix)
   * Process `sqlite-insert` corrected to `sql-insert`
   * Removed fields provided by `Form` plugin, viz. `_xxx` & `form-nonce`

# v1.2.1
## 05-05-2018
1. [](#bugfix)
  * error in translate call

# v1.2.0
## 28-04-2018
1. [](#bugfix)
   * Allow for Twig variables to be used in SELECT stanza in shortcode, and in 'where' field of UPDATE Form process.

# v1.0.0
## 31-01-2018

1. [](#new)
   * Initial work
   * Removal of debug code/messages
   * refactor in case of zero data in form.
   * Allow for `where` to be a Form Field as well as a Process parameters
   * New sql option to provide json serialisation instead of HTML serialisation of data
